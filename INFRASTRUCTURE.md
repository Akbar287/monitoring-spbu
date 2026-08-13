# INFRASTRUCTURE.md — Build, Container, Deploy & Operasional

> Pasangan dari [ARCHITECTURE.md](ARCHITECTURE.md) (kode & alur) dan
> [DATABASE.md](DATABASE.md) (skema & data).
> Klien mobile & API-nya: [MOBILE_API.md](MOBILE_API.md) · [mobile/README.md](mobile/README.md).

---

## 1. Ringkasan Topologi

**Produksi (Docker Compose)** — [docker-compose.yml](docker-compose.yml)

```
                 host:${APP_PORT:-8080}
                          │
            ┌─────────────▼──────────────┐        ┌────────────────────┐
            │  service: app              │        │ service: postgres  │
            │  monitoring-spbu:latest    │◀──────▶│ postgres:16-alpine │
            │                            │  5432  │                    │
            │  supervisord               │        │ vol postgres-data  │
            │   ├─ nginx      :80        │        └────────────────────┘
            │   └─ php-fpm    :9000      │
            │                            │
            │  vol app-storage           │
            │   → /var/www/html/storage  │
            └────────────────────────────┘
                     network: app-network (bridge)
```

Satu container aplikasi berisi nginx + php-fpm di bawah supervisord (bukan
dua container terpisah). Tidak ada Redis, tidak ada queue worker, tidak ada
scheduler container — `QUEUE_CONNECTION=sync` dan cron harus dijalankan dari
host jika diperlukan (§7).

Aplikasi mobile ([mobile/](mobile/)) adalah klien terpisah yang tidak ikut
di-deploy dalam container ini; ia hanya memanggil `https://<APP_URL>/api/v1`
(§13).

**Lokal (dev)** — `php artisan serve` (atau Valet/Herd/Sail) + PostgreSQL lokal
+ dua proses build aset.

---

## 2. Build Aset — Dua Sistem Berdampingan

Ini bagian yang paling sering menjebak. Transisi Blade → Inertia belum selesai,
jadi **kedua build wajib dijalankan**.

| | Laravel Mix (webpack) | Vite |
|---|---|---|
| Konfigurasi | [webpack.mix.js](webpack.mix.js) | [vite.config.js](vite.config.js) |
| Input | `resources/js/app.js`, `resources/sass/app.scss` | `resources/js/inertia/app.jsx` |
| Output | `public/js/app.js`, `public/css/app.css` (+ `mix-manifest.json`) | `public/build/` (+ manifest) |
| Isi | jQuery, Bootstrap 4, Stisla, DataTables jQuery, FontAwesome, seluruh SCSS tema | React 18 + Inertia + chart.js + react-dropzone |
| Perintah | `npx mix` / `npx mix --production` | `npm run dev` / `npm run build` |

**Ketergantungan silang:** [resources/views/inertia.blade.php](resources/views/inertia.blade.php)
memuat `public/css/app.css` (hasil Mix) dan `public/css/theme.css` sebelum
`@vite(...)`. Jadi **halaman React tidak akan tampil benar tanpa build Mix**.

`public/css/theme.css` dirujuk dengan cache-buster
`?v={{ filemtime(public_path('css/theme.css')) }}` — berkas ini **wajib ada**
saat render, kalau tidak `filemtime()` melempar error. Berkas ini di-commit
langsung (bukan hasil build).

`package.json` hanya mendefinisikan skrip `dev` dan `build` (keduanya Vite);
Mix dipanggil manual via `npx mix`.

### Di dalam Docker

[Dockerfile](Dockerfile) stage `assets` **hanya menjalankan `npm run build`
(Vite)**. Ia menyalin `public/` apa adanya, sehingga `public/css/app.css` dan
`public/js/app.js` yang masuk image adalah **hasil build Mix yang sudah
di-commit ke repositori**.

> **Konsekuensi operasional:** kalau Anda mengubah SCSS atau JS legacy, jalankan
> `npx mix --production` dan **commit hasilnya di `public/`**, kalau tidak
> perubahan itu tidak akan pernah sampai ke image produksi.

---

## 3. Setup Lokal

```bash
# 1. Dependensi
composer install
npm install

# 2. Environment
cp .env.example .env
php artisan key:generate
# lalu set di .env:
#   DB_CONNECTION=pgsql
#   DB_HOST=127.0.0.1
#   DB_PORT=5432
#   DB_DATABASE=spbu
#   DB_USERNAME=... DB_PASSWORD=...

# 3. Database (pilih salah satu)
php artisan migrate                       # skema kosong
# atau restore dump PostgreSQL yang ada (jalur yang dipakai tim saat ini)

php artisan db:seed                       # data referensi dasar
php artisan db:seed --class=CoaSeeder     # WAJIB agar modul keuangan aktif

# 4. Storage symlink
php artisan storage:link

# 5. Aset — jalankan KEDUANYA
npx mix                                   # sekali; ulangi bila SCSS/JS legacy berubah
npm run dev                               # dev server Vite (biarkan berjalan)

# 6. Jalankan
php artisan serve
```

Catatan `.env` lokal saat ini: `DB_CONNECTION=pgsql`, `DB_DATABASE=spbu`,
`CACHE_DRIVER=file`, `SESSION_DRIVER=file`, `QUEUE_CONNECTION=sync`,
`FILESYSTEM_DISK=local`. `.env.example` masih menyebut MySQL — **abaikan**,
sistem berjalan di PostgreSQL (lihat [DATABASE.md §8](DATABASE.md)).

`itsgoingd/clockwork` aktif di dev; buka `/clockwork` untuk inspeksi query.

---

## 4. Variabel Lingkungan

### Dipakai aplikasi

| Variabel | Nilai umum | Catatan |
|---|---|---|
| `APP_NAME` | `Monitoring SPBU` | judul `<title>` root view |
| `APP_ENV` | `local` / `production` | di `production`, [AppServiceProvider](app/Providers/AppServiceProvider.php) memaksa `URL::forceScheme('https')` |
| `APP_KEY` | `base64:…` | wajib |
| `APP_DEBUG` | `false` di produksi | |
| `APP_URL` | URL publik | dipakai disk `public` untuk membentuk URL storage |
| `DB_CONNECTION` | `pgsql` | default di `config/database.php` masih `mysql` |
| `DB_HOST` / `DB_PORT` / `DB_DATABASE` / `DB_USERNAME` / `DB_PASSWORD` | | di compose: host `postgres` |
| `CACHE_DRIVER` | `file` | |
| `SESSION_DRIVER` | `file` | ⚠️ sesi disimpan di volume `app-storage` — tidak bisa multi-replika tanpa diubah ke Redis/DB |
| `SESSION_LIFETIME` | `120` | |
| `QUEUE_CONNECTION` | `sync` | tidak ada worker |
| `FILESYSTEM_DISK` | `local` | |
| `LOG_CHANNEL` / `LOG_LEVEL` | `stack` / `info` | ke stdout di container |

### Khusus container (dibaca [docker/entrypoint.sh](docker/entrypoint.sh))

| Variabel | Default | Efek |
|---|---|---|
| `CREATE_STORAGE_LINK` | `true` | membuat/memperbaiki symlink `public/storage` saat start |
| `LARAVEL_OPTIMIZE` | `true` | `php artisan config:cache` + `view:cache` saat start |
| `RUN_MIGRATIONS` | `false` | `php artisan migrate --force` saat start — **biarkan false**, jalankan migrasi manual dan terkendali |

### Khusus compose

`DOCKER_IMAGE`, `APP_PORT`, `POSTGRES_DB`, `POSTGRES_USER`, `POSTGRES_PASSWORD`.

Template lengkap: [.env.docker.example](.env.docker.example).

> ⚠️ `LARAVEL_OPTIMIZE=true` menjalankan `config:cache`. Setelah itu
> `env()` di luar file `config/*` **mengembalikan null**. Semua pembacaan env
> harus lewat `config()`.

---

## 5. Image Docker

[Dockerfile](Dockerfile) — multi-stage:

**Stage 1 `assets`** (`node:20-alpine`)
`npm ci` → salin `vite.config.js`, `resources/`, `public/` → `npm run build`
→ menghasilkan `public/build/`.

**Stage 2 `app`** (`php:8.2-fpm-alpine`)

- Paket runtime: `nginx`, `supervisor`, `bash`, `curl`, `postgresql-libs`,
  `icu-libs`, `libzip`, `freetype`, `libjpeg-turbo`, `libpng`, `oniguruma`
- Ekstensi PHP: `bcmath`, `exif`, `gd` (freetype+jpeg), `intl`, `opcache`,
  `pcntl`, `pdo_pgsql`, `pgsql`, `zip`
- Composer dari image `composer:2`;
  `composer install --no-dev --optimize-autoloader --no-scripts`
- Salin source, lalu:
  - `storage/app/public` → `/usr/local/share/monitoring-spbu/storage-public`
    (**benih** berkas publik yang ikut repo)
  - `public/build` dari stage assets
  - `docker/nginx/default.conf` → `/etc/nginx/http.d/default.conf`
  - `docker/supervisord.conf` → `/etc/supervisord.conf`
  - `docker/entrypoint.sh` → `/usr/local/bin/docker-entrypoint`
- `composer dump-autoload --optimize --no-dev`, buat direktori storage,
  `chown www-data`, `EXPOSE 80`

**Entrypoint** ([docker/entrypoint.sh](docker/entrypoint.sh)) tiap start:

1. Buat direktori `storage/**` dan `bootstrap/cache` bila belum ada.
2. **Seed berkas publik**: salin isi `storage-public` ke `storage/app/public`
   **hanya untuk berkas yang belum ada** — sehingga volume persisten tidak
   tertimpa, tapi aset baru dari image tetap masuk.
3. `chown -R www-data:www-data storage bootstrap/cache`.
4. Perbaiki symlink `public/storage` bila `CREATE_STORAGE_LINK=true`
   (menangani kasus symlink lama yang menunjuk target salah).
5. `config:cache` + `view:cache` bila `LARAVEL_OPTIMIZE=true`.
6. `migrate --force` bila `RUN_MIGRATIONS=true`.
7. `exec supervisord`.

**nginx** ([docker/nginx/default.conf](docker/nginx/default.conf)):
root `public/`, `client_max_body_size 50M`, header `X-Frame-Options: SAMEORIGIN`
dan `X-Content-Type-Options: nosniff`, alias `/storage/` langsung ke
`storage/app/public/` (melewati PHP), blokir dotfile kecuali `.well-known`,
fastcgi ke `127.0.0.1:9000`.

> Batas upload dikendalikan **tiga tempat**: `client_max_body_size` nginx (50M),
> `upload_max_filesize`/`post_max_size` PHP (default image, belum di-override),
> dan `App\Support\Upload::maxKb()` yang membaca dua nilai PHP tadi untuk rule
> validasi + `FileDropzone`. Menaikkan limit berarti menyentuh nginx **dan**
> php.ini.

**supervisord** ([docker/supervisord.conf](docker/supervisord.conf)):
dua program (`php-fpm -F`, `nginx -g "daemon off;"`), `autorestart=true`,
semua log ke stdout/stderr. Tidak ada healthcheck pada service `app`.

**[.dockerignore](.dockerignore)** mengecualikan `.git`, `node_modules`,
`vendor`, semua `.env*` (kecuali contoh), log, cache, `public/build`,
`public/storage` — tetapi **mempertahankan `storage/app/public/**`** sebagai
benih berkas.

---

## 6. CI/CD

[.github/workflows/](.github/workflows/) — satu workflow: **Build and Push Docker**.

- Pemicu: push ke `main`, atau `workflow_dispatch`.
- Login Docker Hub dengan secrets `DOCKERHUB_USERNAME` / `DOCKERHUB_TOKEN`.
- Build + push dua tag: `<user>/monitoring-spbu:<git-sha>` dan `:latest`.
- Cache Buildx via GitHub Actions cache (`type=gha`, `mode=max`).

**Tidak ada** langkah test, lint, atau deploy otomatis di CI. Deploy dilakukan
manual dengan menarik image baru di server.

---

## 7. Perintah Artisan Kustom

Semua di [app/Console/Commands/](app/Console/Commands/) dan **belum terdaftar di
scheduler** — [app/Console/Kernel.php](app/Console/Kernel.php) masih kosong.
Jalankan manual atau daftarkan ke cron host.

| Perintah | Fungsi |
|---|---|
| `php artisan keuangan:penyusutan [--bulan=Y-m] [--dry-run]` | Posting jurnal penyusutan aset bulanan per SPBU (Dr Beban Penyusutan / Cr Akumulasi Penyusutan). Tanggal posting = akhir bulan; kumulatif dibatasi nilai perolehan. Idempotent per SPBU per bulan. **Jalankan tiap akhir bulan.** |
| `php artisan keuangan:backfill [--dari=] [--sampai=] [--dry-run]` | Posting jurnal untuk transaksi historis yang sudah terkonfirmasi tetapi belum berjurnal (mis. transaksi sebelum `CoaSeeder` dijalankan). Idempotent; kegagalan per-record dilaporkan tanpa menghentikan sisanya. |
| `php artisan keuangan:tutup-tahun {tahun} [--dry-run]` | Jurnal penutup tahunan: saldo akun nominal per 31 Desember dipindah ke Laba Ditahan per SPBU. Idempotent per SPBU per tahun. |

**Urutan tutup tahun yang benar:** selesaikan seluruh transaksi tahun berjalan →
`keuangan:tutup-tahun {tahun}` → baru tutup periode Desember lewat menu
Financial → Tutup Buku. Membalik urutan akan membuat jurnal penutup ditolak
karena periodenya sudah terkunci.

Contoh entri cron host (bila diinginkan):

```cron
# 23:30 tanggal terakhir tiap bulan
30 23 28-31 * * [ "$(date -d tomorrow +\%d)" = "01" ] && \
  docker compose exec -T app php artisan keuangan:penyusutan >> /var/log/spbu-penyusutan.log 2>&1
```

---

## 8. Deploy

### Pertama kali

```bash
# di server
cp .env.docker.example .env            # isi APP_KEY, POSTGRES_PASSWORD, APP_URL, DOCKER_IMAGE
docker compose pull
docker compose up -d postgres          # tunggu healthy
docker compose run --rm app php artisan migrate --force
docker compose run --rm app php artisan db:seed --force
docker compose run --rm app php artisan db:seed --class=CoaSeeder --force
docker compose up -d
```

`APP_KEY` dihasilkan dengan `php artisan key:generate --show` (lokal), lalu
disalin ke `.env` server. Mengganti `APP_KEY` pada instalasi berjalan akan
membuat semua sesi dan data terenkripsi tidak terbaca.

### Rilis berikutnya

```bash
docker compose pull app
docker compose run --rm app php artisan migrate --force   # bila ada migrasi baru
docker compose up -d app
```

> ⚠️ **Jangan jalankan `php artisan migrate` polos di environment yang datanya
> hasil restore dump.** Migrasi `2024_04_03_123931_create_status_kehadirans_table`
> sengaja tertinggal dan akan gagal karena tabelnya sudah ada. Jalankan migrasi
> tertentu dengan `--path=...` seperti pada dua migrasi mobile di §13.

Entrypoint akan me-refresh `config:cache`/`view:cache` otomatis. Tidak ada
zero-downtime — ada jeda restart singkat.

### Rollback

Ubah `DOCKER_IMAGE` ke tag SHA sebelumnya, `docker compose up -d app`.
⚠️ Migrasi **tidak** ikut ter-rollback; migrasi yang mengubah skema secara
tak-kompatibel perlu turun manual.

---

## 9. Backup & Data Persisten

Dua volume, keduanya wajib di-backup:

| Volume | Isi | Risiko bila hilang |
|---|---|---|
| `postgres-data` | seluruh basis data | total |
| `app-storage` | `storage/` — **berkas unggahan** (`app/public/*`), sesi, cache, log | seluruh lampiran, LO, bukti bayar, foto selfie hilang; semua user logout |

```bash
# dump database
docker compose exec -T postgres pg_dump -U "$POSTGRES_USER" "$POSTGRES_DB" | gzip > spbu-$(date +%F).sql.gz

# arsip berkas unggahan
docker compose exec -T app tar -czf - -C /var/www/html/storage/app/public . > storage-$(date +%F).tar.gz
```

Karena `SESSION_DRIVER=file` tinggal di volume yang sama, memulihkan `app-storage`
dari backup lama akan memulihkan sesi lama juga — tidak berbahaya, tapi jangan
kaget.

---

## 10. Testing

```bash
php artisan test                      # atau ./vendor/bin/phpunit
php artisan test --filter=InertiaIndexPagesTest
```

- Suite: `tests/Unit` dan `tests/Feature` ([phpunit.xml](phpunit.xml)).
- Isi utama: empat smoke test Inertia (`InertiaMenuPagesTest`,
  `InertiaIndexPagesTest`, `InertiaShowPagesTest`, `InertiaFormPagesTest`) yang
  menyusuri daftar URL dan memastikan komponen Inertia yang benar dirender.
- ⚠️ **Test berjalan terhadap database nyata.** Konfigurasi
  `DB_CONNECTION=sqlite` / `:memory:` di `phpunit.xml` sengaja dikomentari, dan
  test login sebagai `UserLogin` yang harus sudah ada. Jangan jalankan test
  terhadap database produksi.
- Tidak ada test untuk logika keuangan.

Linting: `laravel/pint` tersedia (`./vendor/bin/pint`), belum dipasang di CI.

---

## 11. Observability

| Aspek | Status |
|---|---|
| Log aplikasi | `LOG_CHANNEL=stack` → `storage/logs/laravel.log`; supervisord meneruskan log nginx & php-fpm ke stdout container |
| Error tracking | tidak ada (Sentry/Bugsnag belum dipasang) |
| APM / metrics | tidak ada |
| Profiler | Clockwork (`/clockwork`) — hanya berguna di dev |
| Healthcheck | hanya pada service `postgres` (`pg_isready`). Service `app` **tanpa healthcheck** — tambahkan bila memakai orchestrator |
| Audit trail | tabel `activity_log` (diisi sebagian alur saja, bukan menyeluruh) |

Melihat log:

```bash
docker compose logs -f app
docker compose exec app tail -f storage/logs/laravel.log
```

---

## 12. Catatan & Batasan Operasional

1. **Tidak bisa diskalakan horizontal apa adanya.** `SESSION_DRIVER=file` dan
   `CACHE_DRIVER=file` menyimpan state di volume container. Untuk >1 replika,
   pindahkan sesi & cache ke Redis atau database lebih dulu.
2. **Tanpa queue.** `QUEUE_CONNECTION=sync`: import Excel besar, generate QR,
   dan export dieksekusi di dalam request. Waspadai timeout php-fpm/nginx pada
   file besar.
3. **Aset Mix harus di-commit.** Image tidak menjalankan Mix (§2).
4. **`public/css/theme.css` wajib ada** — dirujuk `filemtime()` di root view.
5. **`config:cache` aktif di container** — `env()` di luar `config/*` bernilai
   null.
6. **Route `/foo` tanpa autentikasi memicu `Artisan::call('storage:link')`**
   ([routes/web.php:19](routes/web.php#L19)). Sudah tidak diperlukan karena
   entrypoint menanganinya; kandidat penghapusan.
7. **Middleware `cache.header` menjalankan pipeline dua kali** — bug dengan
   dampak performa nyata di hampir semua route. Detail:
   [ARCHITECTURE.md §9](ARCHITECTURE.md).
8. **Otorisasi tidak ditegakkan di server** — semua route hanya `auth`.
   Detail: [ARCHITECTURE.md §5](ARCHITECTURE.md).
9. **`RUN_MIGRATIONS` biarkan `false`.** Migrasi otomatis saat start berbahaya
   bila beberapa container start bersamaan.
10. **`CoaSeeder` wajib dijalankan** di environment baru, kalau tidak seluruh
    posting jurnal dilewati tanpa peringatan
    ([DATABASE.md §6](DATABASE.md)).
11. **Tanpa TLS di dalam container.** nginx hanya mendengarkan port 80;
    HTTPS diasumsikan diterminasi oleh reverse proxy di depannya — yang juga
    harus mengirim header `X-Forwarded-*`, karena `AppServiceProvider` memaksa
    `URL::forceScheme('https')` di `production`.
    ⚠️ [TrustProxies](app/Http/Middleware/TrustProxies.php) memakai
    `$proxies = '*'` (percaya **semua** proxy). Aman hanya jika container tidak
    pernah bisa dijangkau langsung dari internet; kalau port 8080 terekspos,
    klien mana pun bisa memalsukan IP dan skema lewat header `X-Forwarded-*`.


---

## 13. Aplikasi Mobile (Expo)

Klien React Native ada di [mobile/](mobile/) dan **tidak dibangun oleh CI
maupun image Docker** — siklus rilisnya terpisah.

### Menjalankan saat pengembangan

```bash
cd mobile
npm install
cp .env.example .env        # sesuaikan EXPO_PUBLIC_API_URL
npm start
```

`EXPO_PUBLIC_API_URL` diisi tanpa `/api/v1` (ditambahkan otomatis klien):

| Sasaran | Nilai |
|---|---|
| Emulator Android | `http://10.0.2.2:8000` |
| Simulator iOS | `http://127.0.0.1:8000` |
| Perangkat fisik | `http://<IP-LAN>:8000` — jalankan `php artisan serve --host=0.0.0.0` |
| Produksi | `https://domain-anda.com` |

Variabel `EXPO_PUBLIC_*` di-inline saat bundling; **restart `npm start`
setelah mengubah `.env`**.

Verifikasi bundel tanpa perangkat:

```bash
npx expo export --platform android --output-dir /tmp/expo-cek
```

### Build rilis

Belum ada konfigurasi EAS (`eas.json`). Untuk menghasilkan APK/IPA:

```bash
npx eas build --platform android
```

### Prasyarat di sisi server

API mobile memerlukan dua migrasi yang **harus dijalankan per-path**, bukan
lewat `migrate` polos:

```bash
php artisan migrate --path=database/migrations/2026_08_13_000001_alter_personal_access_tokens_tokenable_id_to_string.php
php artisan migrate --path=database/migrations/2026_08_13_000002_repair_bigint_id_sequences.php
```

Keduanya idempotent dan aman diulang. Alasannya ada di
[MOBILE_API.md §4](MOBILE_API.md).

Selain itu, folder unggahan baru `storage/app/public/image_selfie/` dibuat
otomatis oleh Laravel saat selfie pertama diunggah, dan ikut tersimpan di
volume `app-storage` (§9) — masuk cakupan backup yang sama.

### Batasan operasional

- **Token Sanctum tidak kedaluwarsa** (`config/sanctum.php` → `expiration: null`).
  Pencabutan hanya lewat logout, ganti password, atau `POST /api/v1/auth/logout-all`.
- **Tidak ada push notification** — belum ada Expo push token maupun tabelnya.
- **Unggahan selfie berjalan sinkron** (`QUEUE_CONNECTION=sync`) dan melewati
  batas `client_max_body_size 50M` nginx (§5).
