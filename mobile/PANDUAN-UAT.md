# Panduan APK UAT — SPBU 24.345.20

## Tentang APK ini

| | |
|---|---|
| Nama aplikasi | **SPBU 24.345.20** |
| Package | `id.spbubernah.monitoring` |
| Backend | `https://spbu.seni.asia` (tertanam di dalam APK, dari `.env.production`) |
| Tipe build | `release` **ditandatangani kunci debug** — bukan rilis produksi |
| Kebutuhan tester | Hanya koneksi internet. **Tidak** perlu Metro/laptop developer |
| Berkas | `mobile/dist/SPBU-24.345.20-uat.apk` — 52,7 MB |
| Arsitektur | `arm64-v8a` + `armeabi-v7a` (semua HP Android; kode emulator x86 dibuang) |
| versionCode | 10 (versionName 1.0.9) |

### Kenapa bukan APK debug?

APK *debug* tidak memuat kode JavaScript di dalamnya — ia mengunduhnya dari
Metro (server pengembangan di laptop) setiap kali dibuka. Diberikan ke tester,
aplikasinya hanya menampilkan layar merah "Could not connect to development
server".

APK ini memakai varian `release` supaya JavaScript ikut terbungkus, tetapi
**ditandatangani dengan kunci debug bawaan** — jadi tetap bukan rilis produksi:
tidak untuk Play Store, dan tidak memakai keystore rahasia apa pun.

## Cara memasang (untuk tester)

1. Unduh `app-release.apk` ke ponsel Android.
2. Buka berkasnya. Android akan meminta izin **"Install unknown apps"** —
   izinkan untuk aplikasi yang dipakai membuka berkas (Files/Chrome/WhatsApp).
3. Bila muncul peringatan Play Protect, pilih **"Install anyway"**. Ini wajar
   untuk aplikasi di luar Play Store.
4. Masuk dengan **username & password pegawai** (bukan email).

Minimal Android 7.0. Aplikasi meminta izin **kamera** (QR absensi & foto) dan
**lokasi** saat fitur absensi dipakai.

## Yang bisa diuji

| Modul | Cakupan |
|---|---|
| Beranda | Ringkasan shift, jadwal hari ini, notifikasi, angka operasional (PJ/direksi) |
| Jadwal & Absensi | Daftar jadwal, absen masuk (scan QR + selfie + GPS), absen pulang |
| Procurement | Perencanaan → Pembelian → Pembayaran → MS2 → Delivery |
| Kelola Data *(khusus Penanggung Jawab)* | Master Data (9 resource) & Konfigurasi (7 resource) |
| Profil | Data pegawai, ganti foto, ganti password |

Tab **Kelola** hanya muncul untuk pegawai berlevel *Penanggung Jawab*.

## Alamat backend saat build

Ada dua berkas env, sengaja dipisah:

| Berkas | Dipakai kapan | Isi |
|---|---|---|
| `.env` | pengembangan lokal (Expo Go / dev build) | bebas — IP LAN, `10.0.2.2`, dsb. |
| `.env.production` | **otomatis saat build rilis** (`expo export`, `gradlew assembleRelease`) | `https://spbu.seni.asia` |

`.env.production` menimpa `.env` pada build produksi, jadi mengubah `.env`
untuk keperluan lokal **tidak lagi berisiko** membuat APK UAT menunjuk ke
localhost. Setelah build, selalu pastikan lagi:

```bash
unzip -p android/app/build/outputs/apk/release/app-release.apk \
  assets/index.android.bundle | grep -ao 'https://spbu\.seni\.asia' | head -1
```

> Task bundling Gradle bisa ter-cache dan diam-diam memakai bundel lama. Bila
> URL-nya salah, hapus dulu keluarannya lalu build ulang:
> `rm -rf android/app/build/generated/assets/react android/app/build/intermediates/assets`
> (jangan pakai `gradlew clean` — task clean native `.cxx` sering gagal).

## Hak akses per jabatan

Menu yang tampil menyesuaikan **jabatan** pegawai (bukan level):

| Jabatan | Tab yang muncul |
|---|---|
| Direktur Utama, Direktur, Komisaris, Admin, Manajer | Beranda · Jadwal · **Procurement** · **Persediaan** · **Kelola** · Profil |
| Jabatan lain (Operator, Security, Supervisor, …) | Beranda · Jadwal · Profil |

Tab yang tidak berhak **tidak ditampilkan**, dan server juga menolak
endpoint-nya dengan 403 — jadi tidak bisa ditembus meski URL-nya diketahui.

Kalau tester merasa menunya kurang, periksa jabatannya di
**Kelola → Pegawai** (harus dibuka oleh akun berakses penuh). Satu pegawai
boleh punya lebih dari satu jabatan; cukup salah satunya masuk daftar di atas.

## Catatan tampilan: Android edge-to-edge

Aplikasi digambar sampai ke belakang bilah sistem (`navigationBarColor`
transparan, wajib sejak Android 15/targetSdk 35+). Karena itu tab bar bawah
**tidak boleh** memakai `height`/`paddingBottom` tetap — nilainya harus
ditambah `useSafeAreaInsets().bottom`, kalau tidak label Beranda/Jadwal/
Procurement/Profil tertimpa tombol Home/Back/Menu.

Perbaikannya ada di `src/navigation/RootNavigator.js`. Ini berlaku untuk
navigasi 3 tombol maupun navigasi gestur — insets menyesuaikan sendiri.

## Kalau tester tidak bisa login

Di layar login ada tautan **"Tes koneksi ke server"** — tekan itu lebih dulu,
pesannya menyebut penyebabnya. Penyebab tersering:

1. Ponsel tidak punya internet.
2. Akun tidak aktif (`userlogin.is_aktif` = 0) atau belum tertaut ke data pegawai.
3. Server sedang tidak melayani `/api/v1` — cek cepat dari peramban mana pun:
   <https://spbu.seni.asia/api/v1/ping> harus menampilkan
   `{"status":true,...}`.

## Membangun ulang APK

Diperlukan JDK 17 (bukan 25 — Gradle belum mendukungnya) dan Android SDK.

```bash
cd mobile
# alamat produksi diambil dari .env.production — .env bebas dipakai untuk lokal
export JAVA_HOME=$(/usr/libexec/java_home -v 17)
export ANDROID_HOME=$HOME/Library/Android/sdk

npx expo prebuild --clean --platform android   # hanya bila app.json berubah
# hanya ABI ponsel — tanpa flag ini APK membengkak jadi ~99 MB karena
# ikut membawa kode emulator x86/x86_64
cd android && ./gradlew assembleRelease -PreactNativeArchitectures=arm64-v8a,armeabi-v7a
```

Hasil: `android/app/build/outputs/apk/release/app-release.apk`
(disalin ke `mobile/dist/` untuk dibagikan; folder itu diabaikan git)

> Menaikkan versi: ubah `expo.version` di `app.json`, lalu prebuild ulang.
> Android menolak memasang APK dengan `versionCode` lebih rendah dari yang
> sudah terpasang.

## Sebelum membagikan — verifikasi sekali

Lakukan **satu login sungguhan** dari APK ini sebelum dibagikan ke banyak
tester. Alasannya: dua migrasi mobile
(`2026_08_13_000001` dan `2026_08_13_000002`) menyiapkan tabel
`personal_access_tokens` untuk menyimpan token. Login yang *gagal* tidak
menyentuh tabel itu, jadi respons 422 tidak membuktikan migrasi sudah jalan —
hanya login yang *berhasil* yang membuktikannya.

Bila login sukses berujung error 500, jalankan di server:

```bash
php artisan migrate --path=database/migrations/2026_08_13_000001_alter_personal_access_tokens_tokenable_id_to_string.php
php artisan migrate --path=database/migrations/2026_08_13_000002_repair_bigint_id_sequences.php
```

## Sebelum benar-benar produksi

- [ ] Buat keystore rilis sendiri dan tanda tangani dengan itu
      (`android/app/build.gradle` saat ini memakai `signingConfigs.debug`).
- [ ] Aktifkan minify/shrink untuk memperkecil ukuran.
- [ ] Tinjau kembali otorisasi API — di luar modul Kelola Data, endpoint hanya
      dijaga autentikasi (lihat ARCHITECTURE.md §5).
