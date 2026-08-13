# SPBU 24.345.20 — Aplikasi Mobile

Aplikasi Expo (React Native) untuk pegawai SPBU: **absensi QR + GPS + selfie**
untuk operator di lapangan, **Procurement** lima tahap untuk pengadaan BBM, dan
**CRUD Master Data + Konfigurasi** untuk Penanggung Jawab — ditambah beranda,
jadwal kerja, notifikasi, dan profil.

Backend-nya adalah API Laravel di repositori yang sama —
lihat [../MOBILE_API.md](../MOBILE_API.md).

---

## Fitur

| Layar | Isi |
|---|---|
| **Masuk** | Login username + password, token disimpan di SecureStore |
| **Beranda** | Shift berjalan, jadwal hari ini, jumlah notifikasi, ringkasan operasional (khusus PJ/direksi) |
| **Jadwal** | Daftar jadwal bulan berjalan dengan filter Semua / Belum absen / Bertugas / Selesai |
| **Detail jadwal** | Rincian shift, riwayat kehadiran, bukti selfie & lokasi, tombol absen |
| **Absen masuk** | Pindai QR → ambil selfie → kunci titik GPS → kirim |
| **Absen pulang** | Konfirmasi, lokasi ikut dicatat bila tersedia |
| **Procurement** | Lima tahap: Perencanaan → Pembelian → Pembayaran → MS2 → Delivery, lengkap dengan baris produk, pajak, bukti bayar, dan dokumen LO |
| **Notifikasi** | Daftar, tandai satu / semua sudah dibaca (dibuka dari kartu Beranda) |
| **Kelola Data** *(khusus PJ)* | **Master Data** — CRUD aset, fasilitas, produk, harga, dombak, payung, dispenser, nozzle, pegawai (termasuk unggah foto) |
| | **Konfigurasi** — CRUD SPBU, divisi, level, jabatan, hari, jam kerja, tarif pajak |
| **Profil** | Data pegawai, ganti foto, ganti password, keluar |

---

## Menjalankan

```bash
cd mobile
npm install
cp .env.example .env      # lalu sesuaikan EXPO_PUBLIC_API_URL
npm start
```

Scan QR yang muncul dengan **Expo Go**, atau tekan `a` (Android) / `i` (iOS).

### Mengarahkan ke backend

`EXPO_PUBLIC_API_URL` diisi **tanpa** `/api/v1` — bagian itu ditambahkan
otomatis oleh `src/api/client.js`.

| Lingkungan | Nilai |
|---|---|
| Emulator Android | `http://10.0.2.2:8000` |
| Simulator iOS | `http://127.0.0.1:8000` |
| Perangkat fisik (Expo Go) | `http://<IP-LAN-komputer>:8000` |
| Produksi | `https://domain-anda.com` |

Untuk perangkat fisik, jalankan backend agar bisa diakses dari jaringan:

```bash
php artisan serve --host=0.0.0.0 --port=8000
```

Variabel `EXPO_PUBLIC_*` di-inline saat bundling — **restart `npm start`
setelah mengubah `.env`.**

---

## Tidak Bisa Login? Cek Ini Dulu

Gejala paling umum: layar login menampilkan *"Tidak dapat terhubung ke server"*.
Tekan **"Tes koneksi ke server"** di bawah tombol Masuk — pesannya menunjuk
penyebab konkret. Penyebab tersering, berurutan:

1. **`EXPO_PUBLIC_API_URL` masih `127.0.0.1`.** Di ponsel, `127.0.0.1` berarti
   *ponsel itu sendiri*, bukan komputer Anda. Isi dengan IP LAN komputer:

   ```bash
   ipconfig getifaddr en0          # macOS → mis. 192.168.1.102
   ```

2. **Lupa restart Metro setelah mengubah `.env`.** Variabel `EXPO_PUBLIC_*`
   ditanam ke dalam bundel saat start, jadi perubahan `.env` tidak berpengaruh
   sampai:

   ```bash
   npm start -- --clear
   ```

3. **Backend tidak mendengarkan di semua antarmuka.** Harus:

   ```bash
   php artisan serve --host=0.0.0.0        # bukan sekadar `php artisan serve`
   ```

4. **Ponsel dan komputer beda jaringan** (mis. ponsel di seluler, laptop di
   Wi-Fi), atau Wi-Fi memakai *client isolation* (umum di jaringan tamu/kantor).

5. **Firewall memblokir port 8000.** Di macOS, izinkan `php` menerima koneksi
   masuk pada System Settings → Network → Firewall → Options.

Verifikasi cepat dari komputer lain di jaringan yang sama:

```bash
curl http://<IP-LAN>:8000/api/v1/ping
# {"status":true,"message":"OK","data":{...}}
```

## Bahasa Rupa

Aplikasi **tidak memakai AppBar** React Navigation — seluruh stack disetel
`headerShown: false`. Judul, tombol kembali, dan aksi menjadi bagian dari
komponen `Halaman` (`src/components/ui.js`), yang merender pita gradien
berwarna di atas lalu "lembar" konten dengan sudut membulat menutupinya.
Karena headernya milik halaman, warnanya bisa berbeda per modul.

```jsx
<Halaman judul="Detail Pembelian" subjudul={kode} warna="oranye">
    <Card tone="hijau">…</Card>       {/* kartu lembut + pita aksen kiri */}
    <Card gradien="biru">…</Card>     {/* kartu penuh warna, teks putih */}
</Halaman>
```

Warna aksen didefinisikan sekali di `src/theme.js` (`aksen`): `indigo`,
`ungu`, `biru`, `teal`, `hijau`, `oranye`, `merah`, `pink`, `abu` — masing-masing
punya gradien, warna solid, versi lembut, dan warna teks. Nama tone lama
(`primary`, `success`, `warning`, `danger`, `muted`, `info`) tetap dikenali.

Warna per modul:

| Modul | Warna |
|---|---|
| Beranda | indigo |
| Jadwal & absensi | teal / hijau |
| Procurement | oranye (tiap tahap punya warna sendiri) |
| Kelola Data | ungu (tiap resource punya warna sendiri di `registry.js`) |
| Profil | biru |
| Notifikasi | merah |

Komponen yang tersedia: `Halaman`, `Card`, `Button`, `Field`, `Row`, `Badge`,
`Notice`, `Statistik`, `SectionTitle`, `Loading`, `EmptyState`, `ErrorState`.
Layar QR scanner sengaja tetap layar penuh gelap tanpa pita gradien, dengan
tombol kembali mengambang.

## Struktur

```
App.js                      SafeAreaProvider + AuthProvider + RootNavigator
src/
  api/
    client.js               fetch terbungkus: base URL, token, timeout, ApiError
    endpoints.js            seluruh endpoint backend (satu-satunya tempat URL)
  auth/AuthContext.js       status login, pemulihan sesi, logout paksa saat 401
  navigation/RootNavigator.js   tab (Beranda/Jadwal/Notifikasi/Profil) + stack
  components/ui.js          Screen, Card, Button, Field, Row, Badge, state kosong/error
  hooks/useMuat.js          pola muat + pull-to-refresh + error
  master/
    registry.js             definisi 16 resource (9 master + 7 konfigurasi)
    OpsiContext.js          cache daftar dropdown dari GET /opsi
  procurement/
    tahap.js                konfigurasi 5 tahap procurement
  screens/                  satu berkas per layar
  screens/master/           layar generik Kelola Data (Home/List/Detail/Form/Lampiran)
  screens/procurement/      layar alur pengadaan (Home/List/Dokumen/Pembayaran/MS2/Delivery)
  utils/                    format tanggal & rupiah, GPS, info perangkat
  theme.js                  palet Stisla (#6777ef) agar seragam dengan web
```

Aturan yang dijaga:

- **Layar tidak memanggil `fetch` atau `client.js` langsung** — selalu lewat
  `src/api/endpoints.js`.
- **Semua respons dibaca sebagai `data`**; amplop `{status,message,data}` sudah
  dibuka oleh `client.js`.
- **Error selalu berupa `ApiError`** dengan `.message` siap tampil,
  `.status`, dan `.fieldError('nama_field')` untuk error validasi.
- **Angka Procurement diperlakukan sebagai string.** Nilai uang & kuantitas
  datang sebagai string 6 desimal dan diformat dengan `desimal6()`/`rupiah6()`
  di `src/utils/format.js` — tanpa `Number()`, karena nilai seperti
  `207025633.303703` melampaui presisi aman double JavaScript. Saat mengirim ke
  server pun dikirim sebagai string.
- **HTTP 401 otomatis memicu logout** lewat handler yang dipasang AuthContext.

### Menambah resource yang bisa dikelola

Layar Kelola Data bersifat generik: `MasterListScreen`, `MasterDetailScreen`,
dan `MasterFormScreen` membaca bentuknya dari `src/master/registry.js`.
Menambah satu resource baru berarti:

1. tambah endpoint di backend (`routes/mobile.php` + controller turunan
   `Api\V1\CrudController`);
2. tambah satu entri di `registry.js` — termasuk `endpoint` (mis.
   `konfigurasi/jam-kerja`) dan `modul`.

Tidak perlu membuat layar baru. Tipe field yang tersedia: `text`, `textarea`,
`number`, `uang`, `date`, `time`, `select`, `multiselect`, `switch`,
`password`.

---

## Izin Perangkat

| Izin | Dipakai untuk |
|---|---|
| Kamera | Memindai QR absensi (`expo-camera`), selfie, dan foto lampiran master (`expo-image-picker`) |
| Galeri foto | Memilih foto lampiran yang sudah ada (`expo-image-picker`) |
| Lokasi (foreground) | Titik GPS saat absen (`expo-location`) |

Teks izin sudah diatur di `app.json`. Android mock location terdeteksi dan
dikirim sebagai catatan pada data lokasi.

---

## Ikon & Identitas Aplikasi

Nama aplikasi (`expo.name` di `app.json`) adalah **SPBU 24.345.20**. Slug
(`monitoring-spbu`) sengaja dipertahankan karena menjadi identitas proyek EAS —
mengubahnya memutus riwayat build.

Seluruh aset ikon dibangkitkan dari `public/images/logo.jpg` di repositori
induk. Logo sumber itu berlatar **pola papan catur** (`#EFEFEF`/`#FFFFFF`) —
indikator transparansi yang ikut ter-render saat ekspor — jadi tidak boleh
dipakai apa adanya. Untuk membangkitkan ulang:

```bash
php mobile/scripts/buat-ikon.php     # dijalankan dari akar repositori
```

Skrip itu membuang latar berdasarkan saturasi warna, memangkas ke kotak logo,
lalu menghasilkan:

| Berkas | Ukuran | Sifat |
|---|---|---|
| `assets/icon.png` | 1024² | Buram di atas putih (wajib untuk iOS) |
| `assets/android-icon-foreground.png` | 1024² | Transparan, isi dalam zona aman adaptive icon |
| `assets/android-icon-background.png` | 1024² | Putih polos |
| `assets/android-icon-monochrome.png` | 1024² | Siluet untuk themed icon Android 13+ |
| `assets/splash-icon.png` | 1024² | Transparan untuk splash |
| `assets/favicon.png` | 48² | Web |

## Ikon Tidak Berubah di HP?

**Di Expo Go, ikon home screen selalu ikon Expo Go** — itu memang tidak bisa
diubah. Ikon logo hanya muncul pada *development build* atau *production build*.

Selain itu, begitu folder `android/` (atau `ios/`) ada di proyek, perubahan
`app.json` — nama aplikasi, ikon, izin — **tidak otomatis ikut**. Native harus
dibangun ulang:

```bash
npm run prebuild            # regenerasi android/ + ios/ dari app.json
npm run android:bersih      # prebuild --clean lalu langsung pasang ke perangkat
```

Verifikasi hasilnya benar-benar terpakai:

```bash
cat android/app/src/main/res/values/strings.xml     # harus "SPBU 24.345.20"
ls android/app/src/main/res/mipmap-xxxhdpi/         # ic_launcher*.webp
```

EAS Build selalu menjalankan prebuild sendiri, jadi build cloud tidak
terpengaruh masalah ini.

> Catatan: `npx expo-doctor` masih melaporkan `.expo` tidak diabaikan git.
> Itu positif palsu — `git check-ignore .expo` membuktikan sudah diabaikan
> lewat `mobile/.gitignore`.

## Catatan

- **Target iOS & Android.** Skrip `npm run web` ada karena bawaan template,
  tetapi `expo-secure-store` tidak tersedia di web sehingga login tidak akan
  berfungsi di sana.
- **Belum ada push notification.** Notifikasi ditarik saat layar dibuka atau
  ditarik ke bawah.
- **Belum ada mode offline.** Absen, CRUD master, dan procurement membutuhkan
  koneksi saat itu juga.
- **Tab Procurement terbuka untuk semua level** (sama seperti menu web);
  Kelola Data tetap khusus Penanggung Jawab.
- **Tab Kelola hanya muncul untuk Penanggung Jawab.** Level lain tidak melihat
  tab-nya, dan backend juga menolak endpoint-nya (403).
- **Import Excel tidak tersedia di mobile** — tetap lewat web.
- **Foto lampiran hanya untuk Aset & Fasilitas**, plus foto profil pegawai —
  master lain tidak punya kolom berkas di skema database.
- Untuk build APK/IPA gunakan EAS Build (`npx eas build`); konfigurasi EAS
  belum dibuat.
