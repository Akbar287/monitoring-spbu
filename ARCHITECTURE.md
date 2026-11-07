# ARCHITECTURE.md — Peta Arsitektur Monitoring SPBU

> Dokumen pintu masuk untuk memahami sistem dengan cepat.
> Pasangannya: [DATABASE.md](DATABASE.md) (skema & data) dan
> [INFRASTRUCTURE.md](INFRASTRUCTURE.md) (build, container, deploy, ops).
> Dokumen domain khusus: [docs/keuangan.md](docs/keuangan.md) dan
> [MOBILE_API.md](MOBILE_API.md) (API + aplikasi mobile).

---

## 1. Ringkasan Sistem

Aplikasi manajemen operasional SPBU (Stasiun Pengisian Bahan Bakar Umum) untuk
SPBU Bernah. Cakupan fungsional:

| Domain | Isi |
|---|---|
| **Master Data** | SPBU, produk BBM, harga, aset, fasilitas, pegawai (member), dombak (tangki), payung, dispenser, nozzle, tabel konversi tinggi→volume |
| **Procurement** | Perencanaan pembelian → pembelian (PO) → pembayaran (invoice + pajak) → MS2 (alokasi stok Pertamina) → delivery (LO/DO) |
| **Manajemen Persediaan** | Monitoring stok, penerimaan BBM, stok taking, stok transfer antar dombak, stok summary, totalisator (stand meter), losses |
| **Tera** | Peminjaman & pengembalian stok untuk keperluan tera |
| **Setoran** | Open penjualan → penitipan setoran → closing penjualan → setoran ke bank |
| **Financial** | Mini general ledger: COA, jurnal umum, saldo awal, tutup buku, petty cash, rekap pajak, margin, laba rugi, neraca, buku besar, neraca saldo, arus kas, perubahan modal |
| **Jadwal & Absensi** | Penjadwalan pegawai, absensi via QR + GPS + selfie, laporan rekap |
| **Artikel** | Post/kategori/tag + halaman publik (landing) |
| **Konfigurasi** | SPBU, divisi, level, jabatan, hari, jam kerja, library pajak, import Excel |
| **Mobile** | Aplikasi Expo/React Native di [mobile/](mobile/): absensi lapangan, beranda, jadwal, notifikasi, profil, CRUD Master Data + Konfigurasi untuk PJ, dan alur Procurement lima tahap — memakai API terpisah `/api/v1` |

Bahasa domain adalah **Bahasa Indonesia** — nama tabel, kolom, route, dan
komentar semuanya Indonesia. Ikuti konvensi ini saat menambah kode.

---

## 2. Stack Teknologi

| Lapisan | Teknologi | Catatan |
|---|---|---|
| Runtime | PHP 8.1+ (container: PHP 8.2 FPM) | |
| Framework | Laravel 10 | struktur skeleton standar (Kernel/Providers, bukan Laravel 11 bootstrap) |
| Database | PostgreSQL (default dev/prod), `config/database.php` masih default `mysql` | lihat [DATABASE.md](DATABASE.md) |
| Auth | web: `laravel/ui` (`AuthenticatesUsers`) + session guard · mobile: `laravel/sanctum` token | |
| Frontend utama | **Inertia.js v1 + React 18** (dibangun Vite) | ~98% halaman |
| Frontend legacy | Blade + jQuery + Stisla (Bootstrap 4) dibangun **Laravel Mix** | sisa halaman + seluruh CSS/font |
| Tabel data | `yajra/laravel-datatables-oracle` (server-side) + komponen React `DataTable` | |
| Excel | `maatwebsite/excel` (import & export) | |
| QR | `simplesoftwareio/simple-qrcode`, `qr-scanner` (browser) | |
| Chart | `chart.js` v4 via komponen `ChartCard` | |
| Debug | `itsgoingd/clockwork` | |
| Mobile | Expo SDK 57 + React Native 0.86 + React Navigation 7 | klien terpisah di [mobile/](mobile/), lihat [MOBILE_API.md](MOBILE_API.md) |
| Auth mobile | Sanctum personal access token | `UserLogin` memakai `HasApiTokens` |

**Dua build system berjalan berdampingan** (transisi Blade → Inertia belum
selesai). Detail perintah build ada di [INFRASTRUCTURE.md](INFRASTRUCTURE.md).

---

## 3. Alur Request

```
Browser
  │
  ├─ Navigasi awal / hard reload
  │     nginx → php-fpm → public/index.php
  │       → middleware global (TrustProxies, HandleCors, TrimStrings, …)
  │       → grup 'web' (session, CSRF, SubstituteBindings, HandleInertiaRequests)
  │       → middleware 'auth' + 'cache.header'
  │       → Controller → Inertia::render('Table/Index', [...props])
  │       → resources/views/inertia.blade.php (root view) + @vite(app.jsx)
  │
  ├─ Navigasi SPA (klik link internal yang route-nya Inertia)
  │     XHR dengan header X-Inertia → Controller yang sama
  │       → JSON { component, props, url, version } → React swap halaman
  │
  └─ Data tabel (DataTables)
        XHR X-Requested-With ke URL index yang sama, TANPA header X-Inertia
          → cabang `if ($request->ajax() && ! $request->inertia())`
          → Yajra DataTables JSON { draw, data, recordsTotal, recordsFiltered }
```

Titik-titik penting:

- **Root view Inertia**: [resources/views/inertia.blade.php](resources/views/inertia.blade.php).
  Di-set dua kali (defensif) — `$rootView` di
  [HandleInertiaRequests](app/Http/Middleware/HandleInertiaRequests.php) dan
  `Inertia::setRootView('inertia')` di
  [AppServiceProvider](app/Providers/AppServiceProvider.php).
- **CSS tetap dari Mix**: root view memuat `public/css/app.css` (hasil Mix) dan
  `public/css/theme.css`, lalu `@vite('resources/js/inertia/app.jsx')`.
  Jadi **React tetap bergantung pada build Mix untuk styling**.
- **Tema gelap/terang** di-set sebelum React mount oleh script inline di root
  view (`window.spbuTheme`, key localStorage `spbu-theme`) agar tidak ada FOUC.

---

## 4. Routing

| File | Prefix | Middleware | Isi |
|---|---|---|---|
| [routes/web.php](routes/web.php) (642 baris) | — | `web`, mayoritas dalam grup `['auth','cache.header']` | seluruh aplikasi |
| [routes/api.php](routes/api.php) | `/api` | **`web`** (bukan grup `api`!) — lihat [RouteServiceProvider](app/Providers/RouteServiceProvider.php) | endpoint AJAX internal halaman web |
| [routes/mobile.php](routes/mobile.php) | `/api/v1` | `api` + `auth:sanctum` | API aplikasi mobile — stateless, token Bearer |
| [routes/channels.php](routes/channels.php) | — | — | channel broadcast default (belum dipakai) |
| [routes/console.php](routes/console.php) | — | — | hanya `inspire` bawaan |

Catatan penting soal routing:

1. **`routes/api.php` memakai middleware `web`.** Artinya endpoint `/api/*`
   ikut session + CSRF, bukan token. Ini disengaja karena dipanggil dari
   halaman yang sudah login. Jangan asumsikan stateless.
   Yang benar-benar stateless hanyalah `/api/v1/*` dari
   [routes/mobile.php](routes/mobile.php); keduanya didaftarkan terpisah di
   RouteServiceProvider, dengan grup mobile lebih dulu.
2. **Route publik ada di akhir file** `web.php` dan diakhiri catch-all
   `Route::get('/{post}', WelcomeController@show)`. Setiap route publik baru
   harus didaftarkan **sebelum** baris itu, kalau tidak akan tertelan.
3. **`Route::get('/foo')` memanggil `Artisan::call('storage:link')` tanpa auth**
   ([routes/web.php:19](routes/web.php#L19)). Ini utilitas sisa masa deploy
   manual; sekarang entrypoint container sudah menangani storage link, jadi
   route ini kandidat kuat untuk dihapus.
4. Registrasi, reset password, dan verifikasi email **dimatikan**:
   `Auth::routes(['register' => false, 'reset' => false, 'verify' => false])`.
5. Pola penamaan route konsisten `modul.submodul.aksi`
   (`aset.file.index`, `setoran.setoran_bank.bank.store`, …). Halaman "menu"
   tiap modul memakai invokable controller: `menu.master`, `menu.procurement`,
   `menu.stok`, `menu.setoran`, `menu.financial`, `menu.konfigurasi`, dst.

---

## 5. Autentikasi & Otorisasi

### Rantai identitas

```
userlogin (tabel login)  ──belongsTo──▶  member (data pegawai)  ──belongsTo──▶  ktp (nama, NIK)
     ▲                                       │
     │                                       ├──belongsToMany──▶ jabatan ──belongsTo──▶ level ──belongsTo──▶ divisi
  guard 'web'                                └──belongsToMany──▶ spbu (pivot spbu_member)
```

- Model auth adalah [`App\Models\UserLogin`](app/Models/UserLogin.php)
  (tabel `userlogin`), dikonfigurasi di `config/auth.php` sebagai provider
  `users`. Tabel `users` bawaan Laravel **tidak dipakai** — model
  [`App\Models\User`](app/Models/User.php) masih ada tapi yatim.
- Field login adalah **`username`**, bukan email
  ([LoginController::username()](app/Http/Controllers/Auth/LoginController.php)).
- Halaman login sudah React: `Inertia::render('Auth/Login')`, tetapi POST
  tetap ditangani trait `AuthenticatesUsers` bawaan.
- **Aplikasi mobile memakai jalur kedua**: token Sanctum lewat
  `POST /api/v1/auth/login`, model yang sama (`UserLogin` + trait
  `HasApiTokens`). Detail: [MOBILE_API.md](MOBILE_API.md).

### Level (peran)

Nilai `level.nama_level` yang dipakai kode:
`Penanggung Jawab` (PJ / admin penuh), `Komisaris`, `Direktur Utama`,
`Direktur` (kelompok "direksi", read-oriented), `Operator`, `Keamanan`.

### ⚠️ Model otorisasi saat ini

**Tidak ada Gate, Policy, middleware role, atau `abort(403)` di seluruh
codebase.** Semua route hanya dilindungi `auth`. Pembatasan peran dilakukan
dengan dua cara yang keduanya **kosmetik**:

1. [`App\Support\Navigation::for($level)`](app/Support/Navigation.php) menyusun
   menu sidebar berbeda per level → menu yang tak relevan tidak ditampilkan.
2. Cek ad-hoc di dalam controller, misalnya
   `...->nama_level == 'Penanggung Jawab'` untuk memutuskan apakah tombol
   Ubah/Konfirmasi ikut dikirim ke props.

Konsekuensinya: **user level manapun yang tahu URL-nya bisa memanggil route
apa pun.** Pengecualiannya hanya di API mobile: endpoint jadwal/kehadiran
memverifikasi kepemilikan `penjadwalan`, dan seluruh endpoint Master Data
serta Konfigurasi dan Procurement dijaga middleware `akses.penuh`
([EnsureAksesPenuh](app/Http/Middleware/EnsureAksesPenuh.php), alias
`akses.penuh` di [Kernel](app/Http/Kernel.php)) yang membaca daftar jabatan
di [App\Support\Akses](app/Support/Akses.php). Jika perlu pengamanan sungguhan, tempat yang benar adalah middleware
route atau Policy — bukan menambah cek di view. Catat ini sebelum menyimpulkan
"fitur X hanya untuk PJ".

---

## 6. Pola Inti: Server-Driven UI

Ini pola paling penting untuk dipahami. Alih-alih satu komponen React per
halaman, controller mengirim **deskripsi UI berupa array PHP** ke sejumlah
kecil halaman React generik.

Distribusi pemakaian (`Inertia::render`):

| Komponen | Jumlah render | Peran |
|---|---|---|
| `Form/Index` | 86 | form create/edit generik |
| `Table/Index` | 75 | halaman daftar + DataTable server-side |
| `Show/Index` | 64 | halaman detail + aksi |
| `Menu/Index` | 11 | landing tiap modul (grid tile) |
| `Financial/Laporan` | 6 | laporan keuangan generik |
| sisanya (~25) | 1–4 | halaman khusus (Setoran, Totalisator, Kehadiran, dsb.) |

### Kontrak props

**`Table/Index`** — [resources/js/inertia/Pages/Table/Index.jsx](resources/js/inertia/Pages/Table/Index.jsx)

```php
Inertia::render('Table/Index', [
    'title' => 'Aset',
    'lead' => null, 'cardTitle' => null,
    'breadcrumbs' => [['label' => 'Master Data', 'url' => route('menu.master')]],
    'createUrl' => route('aset.create'), 'createLabel' => 'Tambah',
    'headerActions' => [['label' => …, 'url' => …, 'color' => …, 'icon' => …]],
    'dateFilter' => ['mulai' => 'Y-m-d', 'selesai' => 'Y-m-d'],   // opsional
    'selectFilters' => [['name','label','value','options','nullable']], // opsional
    'table' => [
        'url' => $request->url(),          // endpoint DataTables = URL index ini juga
        'columns' => [
            ['label' => 'Nama Aset', 'data' => 'nama_aset'],
            ['label' => 'Aksi', 'data' => 'action', 'orderable' => false,
             'searchable' => false, 'raw' => true],
        ],
    ],
]);
```

**`Form/Index`** — [resources/js/inertia/Pages/Form/Index.jsx](resources/js/inertia/Pages/Form/Index.jsx)

```php
'method' => 'post'|'put', 'action' => route(...), 'backUrl' => …,
'downloads' => [['label','url']],                     // opsional
'fields' => [[
    'name', 'label',
    'type' => 'text|number|date|time|textarea|select|multiselect|file|hidden',
    'value', 'options' => [['value','label']], 'placeholder', 'hint',
    'rows', 'col' => 'col-md-6', 'readonly', 'accept', 'size',
]]
```

Detail perilaku: error validasi Laravel otomatis muncul per-field lewat shared
prop `errors`; upload memakai `FileDropzone` (react-dropzone) dan untuk
`method=put` + file otomatis dikirim `POST` + `_method=put` (multipart tidak
bisa PUT asli); double-submit dicegah lewat flag `processing`.

**`Show/Index`** — [resources/js/inertia/Pages/Show/Index.jsx](resources/js/inertia/Pages/Show/Index.jsx)

```php
'fields' => [['label','value']],
'actions' => [['label','url','color','icon',
               'method' => 'put|post|delete',   // tanpa method = link biasa
               'confirm' => 'pesan konfirmasi']],
'deleteUrl' => …, 'deleteConfirm' => …,
'notice' => ['text','color'],
'image' => url,                                   // preview lampiran
'tables' => [['title','columns','rows']],         // tabel statis
'table' => ['title','url','columns'],             // DataTable server-side embedded
```

### Konsekuensi pola ini

- **Menambah halaman CRUD baru biasanya tidak butuh file React baru.** Cukup
  controller + route. Contoh lengkap yang bisa disalin:
  [AsetController](app/Http/Controllers/AsetController.php).
- Perubahan pada `Form/Index` / `Table/Index` / `Show/Index` berdampak ke
  **puluhan halaman sekaligus** — perlakukan sebagai perubahan berisiko tinggi.
- Halaman React khusus hanya dibuat kalau interaksinya benar-benar tidak muat
  di kontrak generik (mis. `Setoran/OpenCreate`, `Totalisator/Final`,
  `Kehadiran/Show` dengan kamera+GPS, `StokSummary/Index`).

### Struktur satu controller CRUD

```
index()   → cabang DataTables (ajax non-inertia) ATAU Inertia 'Table/Index'
create()  → Inertia 'Form/Index' (method post)
store()   → validasi via App\Http\Requests\*Request → simpan → redirect()->with('msg', '<div class="alert…')
show()    → Inertia 'Show/Index'
edit()    → Inertia 'Form/Index' (method put)
update()  → simpan → redirect()->with('msg', …)
destroy() → hapus → redirect()->with('msg', …)
```

`msg` adalah **HTML alert Bootstrap mentah** yang dirender lewat
`dangerouslySetInnerHTML` di layout. Konvensi lama; ikuti bentuknya agar
konsisten, dan jangan pernah menaruh input user tanpa escaping di dalamnya.

Pola idempotensi yang sering muncul di `store()`: cari dulu record dengan
kombinasi field yang sama, buat hanya jika `is_null` — pencegah duplikat akibat
double submit.

---

## 7. Lapisan Frontend

### Struktur `resources/js/inertia/`

```
app.jsx                     createInertiaApp + glob eager ./Pages/**/*.jsx + NProgress
Layouts/
  AppLayout.jsx   (599 baris) shell aplikasi: sidebar, topbar, tema, click-delegation SPA
  PublicLayout.jsx           layout halaman publik/artikel
Components/
  DataTable.jsx   (250)      klien protokol DataTables/Yajra (fetch, bukan jQuery)
  FileDropzone.jsx           upload drag-n-drop
  ChartCard.jsx              wrapper chart.js
  MenuTile.jsx               tile di Menu/Index
  MonthCalendar.jsx          kalender absensi
Pages/…                      lihat tabel di §6
lib/
  inertiaRoutes.js           pencocokan URL → apakah route ini Inertia
  charts.js                  helper chart
```

### Navigasi hibrida (bagian paling halus)

Karena sebagian route masih Blade+jQuery, navigasi tidak boleh selalu SPA.
Mekanismenya:

1. [HandleInertiaRequests](app/Http/Middleware/HandleInertiaRequests.php)
   membangun daftar **semua route GET** beserta flag `inertia`. Flag ditentukan
   dengan **refleksi + membaca source code method controller** dan mencari
   string `Inertia::render` (`sourceUsesInertia()`), lalu di-cache statis
   per-proses. Dibagikan sebagai shared prop `spa.routes`.
2. Di React, [lib/inertiaRoutes.js](resources/js/inertia/lib/inertiaRoutes.js)
   mengubah template URI (`/master/aset/{aset}`) menjadi regex dan mencocokkan
   pathname.
3. [AppLayout](resources/js/inertia/Layouts/AppLayout.jsx) memasang **click
   delegation**: semua link dirender sebagai `<a>` biasa; kalau target cocok
   route Inertia → `router.visit()`, kalau tidak → navigasi browser penuh
   sehingga halaman Blade lama tetap berfungsi.

Implikasi: **mengubah sebuah controller dari Blade ke Inertia otomatis
mengaktifkan navigasi SPA ke sana** tanpa perubahan frontend. Sebaliknya,
menaruh `Inertia::render` di dalam string/komentar bisa membuat deteksi
false-positive.

### Shared props (tersedia di semua halaman)

Dari `HandleInertiaRequests::share()`:

```
auth        { nama, level, avatar }
navigation  hasil Navigation::for($level)
urls        { home, profil, notifikasi, logout }
flash.msg   HTML alert dari session (lazy)
spa.routes  [{ uri, inertia }]
uploadMaxKb min(upload_max_filesize, post_max_size) dari App\Support\Upload
errors      bawaan Inertia
```

`uploadMaxKb` dipakai **bersama** oleh rule validasi server
(`'max:' . \App\Support\Upload::maxKb()`) dan `FileDropzone` — menaikkan limit
di `php.ini` otomatis menaikkan keduanya.

### Komponen `DataTable`

Bicara protokol DataTables persis seperti klien jQuery lama (`draw`, `start`,
`length`, `search[value]`, `order[0][column]`, `columns[i][*]`), mengirim
header `X-Requested-With` agar `$request->ajax()` bernilai true di server, dan
mendukung debounce pencarian 400 ms serta kolom `raw` (HTML tombol dari Yajra).
Karena itu **backend Yajra lama tidak perlu diubah** saat memigrasikan halaman.

### Legacy Blade

332 file `.blade.php` masih ada; 10 controller masih memakai `return view(...)`:
`StokPengembalianController`, `StokPeminjamanController`, `LogController`,
`FileLoController`, `MesinPresensiController`, `ActivityController`,
`BonusController`, `AreaController`, `FilePenitipanSetoranController` (sebagian),
`PresensiController`. Banyak view Blade lain sudah tidak terpakai tetapi belum
dihapus — **jangan menganggap keberadaan file Blade sebagai bukti halaman aktif**;
cek route + controller.

---

## 8. Lapisan Domain

### 8.1 `app/Support/` — logika lintas-controller

| Kelas | Peran |
|---|---|
| [Navigation](app/Support/Navigation.php) | struktur menu sidebar web per level |
| [Akses](app/Support/Akses.php) | jabatan yang boleh membuka seluruh menu di aplikasi mobile |
| [Upload](app/Support/Upload.php) | batas unggah efektif (KB) |
| [Charts](app/Support/Charts.php) | agregasi bulanan `{labels, data}` untuk chart.js (query `to_char(...)` → **PostgreSQL-specific**) |
| [SetoranShow](app/Support/SetoranShow.php) | serialisasi props halaman setoran (info, PJ, stand meter) |
| [Akun](app/Support/Akun.php) | konstanta kode akun COA baku |
| [JurnalPosting](app/Support/JurnalPosting.php) | satu-satunya pintu pencatatan jurnal |
| [PostingKeuangan](app/Support/PostingKeuangan.php) | peta transaksi operasional → jurnal |
| [Import/MasterResolver](app/Support/Import/MasterResolver.php) | resolusi nama master (dombak, nozzle, jam kerja) saat import Excel |
| [Uang](app/Support/Uang.php) | aritmetika desimal presisi tetap (bcmath skala 6) untuk API Procurement — hindari float pada kolom `decimal(16,6)` |

Tidak ada lapisan Service/Repository formal — logika bisnis mayoritas ada di
controller, kecuali keuangan dan import yang sudah diekstrak ke `Support`.

### 8.2 Alur Procurement

```
RencanaPembelian (status: Rencana)
  └ DetailRencanaPembelian (per produk)
        │ konfirmasi harga
        ▼
   status: Pembelian  ──▶  PajakPembelian (net, PPN, PPBKB, PPh, metode_posting_pajak)
        │ pembayaran
        ▼
   Pembayaran (is_confirmation_admin + is_confirmation_top)  ── FilePembayaran
        │
        ├─▶ MS2 (alokasi stok Pertamina, detail_rencana_pembelian_ms2)
        ▼
   Pengiriman (no_do, no_pol, Supir)  ── FileLo (LO/faktur) ── FileLampiranLo, Segel
        │ konfirmasi admin + top
        ▼
   Penerimaan (file_lo → dokumen_stok)  ── FilePenerimaan
        │
        └─▶ menambah StokInventory / StokInventoryDombak
```

Status berjenjang disimpan di `status_purchase`
(Rencana → Pembelian → Pembayaran → MS2 → Pengiriman → Penerimaan) dan pada
pasangan boolean `is_confirmation_admin` / `is_confirmation_top` — pola
**dual approval** yang berulang di seluruh sistem.

### 8.3 Alur Stok

Semua pergerakan stok melewati satu tabel: **`dokumen_stok`** (stok_awal →
stok_akhir per dombak per jam kerja), dengan jenis gerakan di
`type_dokumen_stok.type_movement`:

```
Good Receive Penerimaan | Good Issue Sales | Good Issue Tera | Good Return Tera
Stok Taking Good Receive Penerimaan | Stok Taking | Loss
Commit Change From User | Good Transfer Send | Good Transfer Receive
```

Tabel pendamping:
- `konversi` — tabel konversi tinggi cairan → volume per dombak (diunggah Excel).
- `stok_inventory` (per produk) dan `stok_inventory_dombak` (per dombak) —
  saldo berjalan, di-update saat dokumen stok **dikonfirmasi**
  (`is_confirmation`).
- `losses` — selisih lebih/kurang, terkait ke `dokumen_stok`.
- `stand_meter` — angka totalisator awal/akhir per nozzle per shift; menjadi
  dasar `detail_penjualan`.

Pola penting: dokumen dibuat dulu dengan `is_confirmation = false`, dan
**baru memengaruhi saldo saat dikonfirmasi**. Aksi ubah/hapus umumnya ditutup
setelah konfirmasi.

### 8.4 Alur Setoran (siklus penjualan harian)

```
status_setoran: Open Penjualan → Penitipan Setoran → Closing Penjualan → Setoran Ke Bank → Selesai

Open Penjualan   : buat Penjualan untuk shift (jam_kerja), pilih stand meter
                   (DetailPenjualan: liter, harga_id, total) + PJ operasional
Penitipan        : uang kotak dititipkan, closing_to / closing_from
Closing          : konfirmasi per-detail lalu konfirmasi setoran
Setoran Ke Bank  : Penarikan (bank, no_referensi, total_uang) + FilePenarikan
                   dengan konfirmasi admin (0x002) & top (0x003)
```

Flag akhir: `penjualan.is_accepted_admin` + `is_accepted_top`;
`penarikan.is_accepted`. **Kedua flag inilah pemicu posting jurnal.**

### 8.5 Absensi berbasis QR

```
Penjadwalan (member × jam_kerja × tanggal, opsional nozzle via penjadwalan_nozzle)
   │ PJ generate QR
   ▼
ValidateBarcode (konfigurasi masa berlaku, default 15) → RandomStringBarcode (barcode, start, finish)
   │ operator scan (qr-scanner) di halaman Kehadiran/Show
   ▼
Penjadwalan.start_time / finish_time / is_attended
   + Devices (ipv4, OS, browser), Location (lat/long/gmt), ImageSelfie
   │ PJ konfirmasi
   ▼
is_confirm → masuk laporan rekap absensi
```

Jadwal bisa diunggah massal lewat Excel (`JadwalKerjaImport`).

### 8.6 Modul Keuangan (mini General Ledger)

Ringkas di sini; **detail otoritatif ada di [docs/keuangan.md](docs/keuangan.md)**.

Prinsip: seluruh laporan keuangan diturunkan dari `jurnal_detail`, bukan
dihitung ulang dari tabel operasional.

```
Transaksi operasional (Penjualan, Penarikan, PajakPembelian, Pembayaran,
PettyCash, Losses, GajiMember)
   │  model event created/updated/deleted  (di boot() masing-masing model)
   ▼
App\Support\PostingKeuangan          ← aturan bisnis: kapan & jurnal apa
   ▼
App\Support\JurnalPosting            ← satu-satunya pintu tulis
   │  validasi: ≥2 baris, debit = kredit (toleransi 1e-4), tidak negatif,
   │            akun ada di COA, bukan header, aktif, periode belum ditutup,
   │            anti double-posting via unique (tipe_sumber, sumber_id)
   ▼
jurnal + jurnal_detail  →  Buku Besar, Neraca Saldo, Laba Rugi, Neraca,
                           Arus Kas, Perubahan Modal
```

Hal-hal yang mudah membuat salah paham:

- **Hook lewat model event, bukan controller.** Semua jalur tulis (controller,
  import, seeder, tinker) ikut terposting. Contoh:
  [Penjualan::boot()](app/Models/Penjualan.php).
- **Kill-switch implisit**: `PostingKeuangan::aktif()` mengembalikan false jika
  tabel `coa` belum ada atau belum berisi akun non-header. Sebelum
  `CoaSeeder` dijalankan, seluruh posting dilewati diam-diam. Transaksi masa
  itu disusulkan dengan `php artisan keuangan:backfill`.
- **Penjualan menghasilkan dua jurnal**: tipe `penjualan` (sisi kas) dan
  `penjualan_hpp` (HPP/persediaan, non-kas). Dipisah supaya laporan arus kas
  metode langsung tidak terkontaminasi baris non-kas.
- **Pajak pembelian fleksibel per transaksi** lewat
  `pajak_pembelian.metode_posting_pajak` (`hpp` | `terpisah`); PPh 22 selalu ke
  Beban PPh Final.
- **Pemetaan `petty_cash.no_kode` → akun beban belum dibuat**; semua pengeluaran
  petty cash masuk `6-1400` Beban Operasional Lainnya.
- Posting losses **dilewati** kalau produk belum punya harga default (nilai 0),
  supaya alur stok tidak terblokir.
- Periode tertutup (`periode_akuntansi.is_closed`) menolak posting **maupun**
  pembatalan.

Perintah artisan terkait ada di [INFRASTRUCTURE.md §7](INFRASTRUCTURE.md).

### 8.7 Import & Export Excel

```
app/Imports/
  JadwalKerjaImport.php, KonversiImport.php      (maatwebsite langsung)
  Transaksi/
    ImporterRegistry.php          'Stok Taking'|'Penerimaan BBM'|'Stok Transfer'|'Totalisator'
    AbstractTransaksiImporter.php  header→row mapping, satu transaksi DB,
                                   rollback total jika ada baris gagal,
                                   error dilaporkan per nomor baris Excel
    StokTakingImporter / PenerimaanImporter / StokTransferImporter / TotalisatorImporter
app/Exports/
  TemplateTransaksiExport (template + sheet "Referensi" berisi nama master),
  PenerimaanExport, TotalisatorExport, JadwalKerjaExport, StokDombakExport,
  TotalPenjualanExport, PajakPembelianExport, ArraySheet
```

Menambah jenis import baru = buat importer turunan `AbstractTransaksiImporter`
+ daftarkan di `ImporterRegistry::MAP`; template dan sheet referensi otomatis
mengikuti.

---

## 9. Konvensi Kode

**Model** ([app/Models/](app/Models/), 89 file):

```php
protected $table = 'penjualan';          // nama tabel eksplisit, singular
protected $primaryKey = 'penjualan_id';  // <tabel>_id
public $incrementing = false;
protected $keyType = 'string';
protected static function boot() {
    parent::boot();
    static::creating(fn ($m) => $m->{$m->getKeyName()} = Uuid::uuid4()->toString());
}
```

UUID di-generate manual dengan `Ramsey\Uuid\Uuid` di `boot()`, **bukan** trait
`HasUuids` Laravel. Relasi selalu menyebut kunci secara eksplisit
(`belongsTo(Spbu::class, 'spbu_id', 'spbu_id')`) dan dinamai `snake_case`
(`detail_penjualan()`, `jam_kerja()`), bukan camelCase.

**Controller**: `App\Http\Requests\*Request` untuk validasi (banyak dihasilkan
`moh4mmad/request-validation-generator`), redirect + flash `msg` HTML,
`Auth::user()->member()->first()` untuk mendapatkan member aktif.

**Anti-pola yang sering muncul** (kenali, jangan tiru saat menulis kode baru):
- rantai `->first()` berulang, mis.
  `Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level`
  — rawan N+1 dan null pointer;
- query di dalam closure Yajra `addColumn` — N+1 per baris;
- `str_replace(',', '', request('harga'))` tersebar untuk membersihkan format
  ribuan dari Cleave.js;
- HTML alert panjang di-inline pada setiap redirect.

**Middleware kustom** ([app/Http/Middleware/](app/Http/Middleware/)):
- `HandleInertiaRequests` — shared props + deteksi route SPA (§7).
- `AddCacheControlHeaders` (alias `cache.header`) — ⚠️ **memanggil
  `$next($request)` dua kali** ([AddCacheControlHeaders.php](app/Http/Middleware/AddCacheControlHeaders.php)),
  sehingga seluruh pipeline berjalan ganda untuk setiap route dalam grup
  `['auth','cache.header']` — yaitu hampir seluruh aplikasi. Header yang
  di-set pun dibuang karena response kedua yang dikembalikan. Ini bug nyata
  dengan dampak performa dan efek samping ganda (mis. counter/log), bukan
  sekadar kode mati.

---

## 10. Testing

[tests/Feature/](tests/Feature/) berisi empat suite smoke test yang
menyusuri daftar URL dan memastikan halaman Inertia yang benar dirender:
`InertiaMenuPagesTest`, `InertiaIndexPagesTest`, `InertiaShowPagesTest`,
`InertiaFormPagesTest`. Test **berjalan terhadap database nyata** — konfigurasi
sqlite in-memory di [phpunit.xml](phpunit.xml) sengaja dikomentari, dan test
login sebagai `UserLogin` yang ada di database. Tidak ada unit test untuk
logika keuangan.

---

## 10b. Set Up Awal (`/setup`)

Pengisian data pondasi saat aplikasi dipakai pertama kali. Sumber kebenarannya
[app/Support/Setup.php](app/Support/Setup.php) — daftar langkah, prasyarat, dan
syarat "selesai" semuanya di situ; controller dan halaman React hanya
menampilkan hasilnya.

**Prinsip yang penting dipahami sebelum mengubahnya:**

- **Tidak ada jalur pintas penulisan data.** Set up hanya mengantar admin ke
  form asli tiap modul. Satu-satunya yang ditulis `SetupController` adalah
  identitas SPBU dan `tanggal_mulai_operasi`. Alasannya, menulis langsung ke
  tabel akan melewati event model yang memicu jurnal dan pergerakan stok.
- **Urutan langkah adalah dependensi, bukan tampilan.** Tiap langkah menyebut
  `butuh`; yang prasyaratnya belum beres dikirim `terkunci: true`. Langkah yang
  datanya sudah terisi tidak pernah dikunci.
- **Rantai pengadaan wajib berurutan**: perencanaan → MS2 → LO. Penerimaan BBM
  hanya bisa diproses dari LO hasil rantai ini, sehingga kondisi awal pengadaan
  (pembelian/pengiriman yang sudah terjadi sebelum go-live) pun harus diisi
  lewat rantai yang sama.
- **Instalasi lama tidak boleh terkunci.** Migrasi
  `2026_08_15_000001_create_pengaturan_sistem_table` langsung menandai set up
  selesai bila `spbu`, `produk`, dan `member` sudah terisi.

| Bagian | Berkas |
|---|---|
| Definisi langkah & kemajuan | [app/Support/Setup.php](app/Support/Setup.php) |
| Halaman & simpan identitas | [SetupController](app/Http/Controllers/SetupController.php) |
| Pengalihan admin ke `/setup` | [PastikanSetupSelesai](app/Http/Middleware/PastikanSetupSelesai.php), alias `setup.selesai` |
| Tampilan | [Pages/Setup/Index.jsx](resources/js/inertia/Pages/Setup/Index.jsx) |
| Penyimpanan status | tabel `pengaturan_sistem` via [PengaturanSistem](app/Models/PengaturanSistem.php) |

---

## 11. Peta Cepat "Mau Ubah Apa, Sentuh Mana"

| Tujuan | Berkas |
|---|---|
| Tambah menu sidebar | [app/Support/Navigation.php](app/Support/Navigation.php) |
| Ubah langkah set up awal | [app/Support/Setup.php](app/Support/Setup.php) — definisi langkah, prasyarat, syarat selesai |
| Tambah halaman CRUD | route di [routes/web.php](routes/web.php) + controller baru meniru [AsetController](app/Http/Controllers/AsetController.php) |
| Ubah tampilan semua form / tabel / detail | `resources/js/inertia/Pages/{Form,Table,Show}/Index.jsx` (**berdampak luas**) |
| Ubah shell/sidebar/tema | [AppLayout.jsx](resources/js/inertia/Layouts/AppLayout.jsx) + `resources/sass/` (Mix) + `public/css/theme.css` |
| Tambah props global | `HandleInertiaRequests::share()` |
| Ubah aturan jurnal | [PostingKeuangan](app/Support/PostingKeuangan.php); mekanika di [JurnalPosting](app/Support/JurnalPosting.php); kode akun di [Akun](app/Support/Akun.php) + [CoaSeeder](database/seeders/CoaSeeder.php) |
| Tambah jenis import Excel | `app/Imports/Transaksi/` + `ImporterRegistry::MAP` |
| Ubah skema | migrasi baru di `database/migrations/` (jangan edit migrasi lama — lihat [DATABASE.md](DATABASE.md)) |
| Tambah endpoint mobile | [routes/mobile.php](routes/mobile.php) + `app/Http/Controllers/Api/V1/`; dokumentasikan di [MOBILE_API.md](MOBILE_API.md) |
| Tambah resource CRUD mobile (API) | controller turunan [`Api\V1\CrudController`](app/Http/Controllers/Api/V1/CrudController.php) + satu entri pada pemanggilan `$crud(...)` di [routes/mobile.php](routes/mobile.php) |
| Tambah resource CRUD mobile (klien) | satu entri di [mobile/src/master/registry.js](mobile/src/master/registry.js) — layar sudah generik |
| Ubah aplikasi mobile | [mobile/](mobile/) — mulai dari [mobile/README.md](mobile/README.md) |
| Ubah build/deploy | [INFRASTRUCTURE.md](INFRASTRUCTURE.md) |

---

## 12. Utang Teknis yang Perlu Diketahui Lebih Dulu

1. **Otorisasi kosmetik** — tidak ada Gate/Policy; semua route hanya `auth` (§5).
2. **`cache.header` menjalankan pipeline dua kali** (§9).
3. **Route `/foo` publik memicu `storage:link`** (§4).
4. **Dua build system** Mix + Vite; React bergantung pada CSS hasil Mix (§2, §3).
5. **Catch-all `/{post}`** di akhir `routes/web.php` (§4).
6. **`config/database.php` default masih `mysql`** padahal query di `Charts`
   dan sebagian controller memakai sintaks PostgreSQL (`to_char`).
7. **Migrasi `create_status_kehadirans_table` belum pernah jalan di DB dev**
   (lihat [docs/keuangan.md](docs/keuangan.md) bagian "Catatan lingkungan" dan
   [DATABASE.md](DATABASE.md)).
8. **Blade mati** — ratusan view tak terpakai belum dibersihkan (§7).
9. **Tanpa queue** (`QUEUE_CONNECTION=sync`) — import Excel besar, generate QR,
   dan export berjalan sinkron dalam request.
10. **Test membutuhkan database nyata** (§10).
