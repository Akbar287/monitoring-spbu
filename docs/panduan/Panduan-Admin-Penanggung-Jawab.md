# Buku Panduan Aplikasi Monitoring SPBU
## Role: Admin (Penanggung Jawab)

> Versi panduan: 1.0 · Untuk pengguna dengan jabatan **Penanggung Jawab (PJ)**.
> Penanggung Jawab adalah role dengan **akses penuh** ke seluruh modul aplikasi, sehingga panduan ini sekaligus menjadi acuan paling lengkap. Panduan untuk role lain (Operator, Keamanan, Direksi) disusun terpisah.

---

## Daftar Isi

1. [Tentang Aplikasi & Peran Anda](#1-tentang-aplikasi--peran-anda)
2. [Konsep & Istilah Penting](#2-konsep--istilah-penting)
3. [Memulai: Login, Dashboard, Navigasi](#3-memulai-login-dashboard-navigasi)
4. [Urutan Penyiapan Sistem (Wajib Dibaca Pertama)](#4-urutan-penyiapan-sistem)
5. [Modul Konfigurasi](#5-modul-konfigurasi)
6. [Modul Master Data](#6-modul-master-data)
7. [Manajemen Pegawai & Akun](#7-manajemen-pegawai--akun)
8. [Modul Procurement (Pengadaan BBM)](#8-modul-procurement-pengadaan-bbm)
9. [Modul Manajemen Persediaan (Stok)](#9-modul-manajemen-persediaan-stok)
10. [Totalisator (Pencatatan Penjualan)](#10-totalisator-pencatatan-penjualan)
11. [Modul Setoran](#11-modul-setoran)
12. [Modul Financial](#12-modul-financial)
13. [Jadwal & Absensi](#13-jadwal--absensi)
14. [Kehadiran (Presensi)](#14-kehadiran-presensi)
15. [Modul Artikel (Portal Publik)](#15-modul-artikel-portal-publik)
16. [Modul Tera](#16-modul-tera)
17. [Mengunggah File & Dokumen](#17-mengunggah-file--dokumen)
18. [Tips, Masalah Umum, & FAQ](#18-tips-masalah-umum--faq)

---

## 1. Tentang Aplikasi & Peran Anda

**Monitoring SPBU** adalah sistem terpadu untuk memantau dan mengelola operasional Stasiun Pengisian Bahan Bakar Umum (SPBU): mulai dari **pengadaan BBM** (procurement), **persediaan/stok** di tangki pendam, **penjualan harian**, **setoran uang**, **kepegawaian & absensi**, hingga **laporan keuangan**.

### Peran (Role) dalam sistem

Akses menu ditentukan oleh **Level Jabatan** akun Anda:

| Role / Level | Cakupan akses |
|---|---|
| **Penanggung Jawab (PJ)** ← *Anda / Admin* | **Semua modul** — master data, konfigurasi, procurement, stok, setoran, financial, kepegawaian, artikel |
| Operator | Procurement, stok harian, totalisator, setoran, kehadiran |
| Direksi (Komisaris / Direktur Utama / Direktur) | Procurement, stok, totalisator, setoran, financial (mode pemantauan/persetujuan) |
| Keamanan | Procurement, stok, setoran, kehadiran (terbatas) |

> **Catatan:** Sepanjang panduan ini, istilah **"Admin"** mengacu pada role **Penanggung Jawab**.

---

## 2. Konsep & Istilah Penting

Pahami istilah berikut sebelum menggunakan aplikasi — ini menjadi dasar hampir semua modul.

| Istilah | Penjelasan |
|---|---|
| **SPBU** | Entitas stasiun yang dimonitor. Semua data master bernaung di bawah satu/lebih SPBU. |
| **Produk** | Jenis BBM yang dijual (mis. Pertamax, Pertalite, Bio Solar, Dexlite, Pertamina Dex). |
| **Dombak** | **Tangki pendam** tempat BBM disimpan. Stok BBM fisik berada di dombak. |
| **Payung** | Kanopi/island tempat dispenser berdiri. |
| **Dispenser** | Mesin pompa BBM (berada di bawah payung). |
| **Nozzle** | Selang/keran pada dispenser yang mengalirkan satu produk. Satu nozzle = satu produk. |
| **Stand Meter** | Angka odometer pada nozzle. **Selisih** stand meter akhir − awal = liter terjual. |
| **Totalisator** | Proses mencatat stand meter awal/akhir per nozzle per shift → menghasilkan angka penjualan. |
| **Tabel Konversi (Dombak)** | Tabel pemetaan **tinggi cairan (cm) → volume (liter)** untuk setiap dombak. Dipakai mengubah hasil pengukuran tongkat (tinggi) menjadi volume. |
| **Stok Taking** | Pengukuran/penyesuaian stok fisik dombak pada waktu tertentu. |
| **Stok Transfer** | Pemindahan BBM antar dombak (produk sama). |
| **Penerimaan BBM** | Penerimaan/bongkar BBM dari pengiriman ke dalam dombak (stok bertambah). |
| **MS2** | Tahap pengajuan pengiriman dari stok yang berada "di Pertamina" menuju pengiriman fisik. |
| **Jam Kerja / Shift** | Periode kerja (mis. Shift 1, 2, 3) — dasar pencatatan totalisator, setoran, dan absensi. |
| **Losses** | Selisih volume (kurang/lebih) antara catatan sistem dan pengukuran fisik. |

### Alur besar data BBM

```
PENGADAAN                         PERSEDIAAN                 PENJUALAN
Perencanaan → Pembelian →         Penerimaan BBM →           Totalisator →
Pembayaran → MS2 → Pengiriman →   (stok masuk ke Dombak)     Setoran (uang)
                                  Stok Taking / Transfer
                                  Monitoring Stok
```

---

## 3. Memulai: Login, Dashboard, Navigasi

### 3.1 Login
1. Buka alamat aplikasi (mis. `http://localhost:8000` atau domain SPBU Anda).
2. Pada halaman publik, klik tombol **Login**.
3. Masukkan **Username** dan **Password**, lalu klik **Login**.
   - Gunakan ikon **mata** untuk menampilkan/menyembunyikan password.
   - Jika username/password salah, pesan kesalahan tampil di bawah kolom.

### 3.2 Dashboard
Setelah login sebagai PJ, Anda diarahkan ke **Dashboard** yang menampilkan:
- **Kartu statistik**: Total Member, Produk, Pengiriman berjalan, Dombak aktif.
- **Grafik Stok Hari Ini**: komposisi stok per produk (doughnut) + penjualan stok per shift.
- **Grafik Penjualan Hari Ini**: penjualan per nozzle & per shift (Rupiah).
- **Daftar Menu** dan **tabel Pembayaran** yang menunggu + daftar **pengiriman berjalan**.

### 3.3 Navigasi
- **Sidebar kiri** memuat seluruh modul. Klik judul modul untuk membuka sub-menu; sub-item **"Ringkasan"** membuka halaman ringkasan + grafik modul tersebut.
- Tombol **☰** (hamburger) di kiri-atas: di desktop mengecilkan sidebar; di **ponsel** memunculkan/menyembunyikan sidebar (tap area gelap untuk menutup).
- **Ikon kanan-atas**: foto profil (→ Profil), lonceng (→ Notifikasi), keluar (Logout).

### 3.4 Profil & Ganti Password
- Klik **foto profil** → halaman Profil. Dari sini Anda dapat mengubah data diri dan mengganti password.

### 3.5 Logout
- Klik ikon **keluar (sign-out)** di kanan-atas.

---

## 4. Urutan Penyiapan Sistem

> **PENTING.** Saat aplikasi baru pertama kali dipakai, isi data dengan urutan ini. Banyak modul saling bergantung — mengisi di luar urutan akan membuat pilihan dropdown kosong.

```
1. Konfigurasi → SPBU
2. Konfigurasi → Divisi → Level → Jabatan
3. Konfigurasi → Hari → Jam Kerja
4. Konfigurasi → Pajak
5. Master → Produk → Harga
6. Master → Dombak (lalu isi Tabel Konversi tiap dombak)
7. Master → Payung → Dispenser → Nozzle
8. Master → Pegawai (buat akun Operator/Keamanan/Direksi)
9. Master → Aset & Fasilitas (opsional)
```

Setelah langkah 1–8 selesai, modul operasional (Procurement, Stok, Totalisator, Setoran, Absensi) siap digunakan.

---

## 5. Modul Konfigurasi

Menu: **Konfigurasi**. Berisi data pondasi sistem. Pola setiap sub-menu sama: halaman **daftar (tabel)** dengan tombol **Tambah**, tombol **Detail** per baris, lalu **Ubah** / **Hapus** di halaman detail.

### 5.1 SPBU
Identitas stasiun. **Tambah** → isi: Nama SPBU, Nomor SPBU, Tanggal Pendirian, Luas Lahan + Satuan, Alamat → **Simpan**.

### 5.2 Divisi
Pengelompokan organisasi. Field: SPBU, Nama Divisi, Status (Aktif/Tidak), Keterangan.

### 5.3 Level
Tingkat jabatan **yang menentukan hak akses menu** (lihat tabel role di bagian 1). Field: Divisi, Nama Level, Status, Keterangan.
> Nama level harus persis: **Penanggung Jawab**, **Operator**, **Keamanan**, **Komisaris**, **Direktur Utama**, **Direktur** — karena sistem mengatur menu berdasarkan nama level ini.

### 5.4 Jabatan
Posisi spesifik di bawah sebuah Level. Field: Level, Nama Jabatan, Status, Keterangan. Pegawai nantinya ditautkan ke Jabatan (bukan langsung ke Level).

### 5.5 Hari
Definisi hari + penanda **hari kerja / libur**. Field: Nama Hari, Jenis (Hari Kerja / Hari Libur), Keterangan.

### 5.6 Jam Kerja (Shift)
Definisi shift. Field penting: SPBU, Nama Jam Kerja (mis. "Shift 1"), Batas Awal/Akhir Masuk, Jam Datang, Jam Pulang, Batas Akhir Pulang, Jam Istirahat, Masuk Istirahat, Batas Telat, **Urutan** (untuk pengurutan shift). Shift adalah dasar Totalisator, Setoran, dan Absensi.

### 5.7 Pajak
Library tarif pajak pembelian default. Field: PPN, PPH, PPBKB, Status (Aktif). Tarif **Aktif** dipakai sebagai nilai awal saat menghitung pajak pembelian.

### 5.8 Import Excel
Untuk mengunggah data massal. Pilih **Jenis File** (Penerimaan BBM / Stok Taking / Totalisator / Penarikan), pilih **File Excel**, lalu **Simpan**. Ukuran file dibatasi (lihat bagian 17).

---

## 6. Modul Master Data

Menu: **Master Data**. Semua sub-menu memakai pola daftar → tambah/detail → ubah/hapus yang sama.

### 6.1 Produk
BBM yang dijual. Field: SPBU, Nama Produk, Singkatan, Jenis, Oktan, Status (Aktif). Hanya produk **Aktif** yang muncul di transaksi.

### 6.2 Harga
Harga jual & beli per produk per shift. Field: Produk, Jam Kerja, Tanggal, Harga Jual, Harga Beli.
- Halaman ini punya tombol **Riwayat** untuk melihat seluruh histori perubahan harga.
- Harga jual dipakai otomatis untuk menghitung nilai penjualan di Totalisator dan dashboard.

### 6.3 Dombak (Tangki) + Tabel Konversi
1. **Tambah Dombak**: SPBU, Nama Dombak, Status, Keterangan.
2. Buka **Detail** dombak → isi **Tabel Konversi**: daftar pemetaan **tinggi (cm) → volume (liter)**.
   - Tabel ini **wajib** agar Stok Taking, Stok Transfer, dan Penerimaan BBM bisa menghitung volume dari hasil pengukuran tongkat ukur.
   - Konversi juga bisa diisi massal via **Import** (upload Excel).

### 6.4 Payung → Dispenser → Nozzle
Isi berurutan karena saling bergantung:
1. **Payung**: SPBU, Nama Payung, Status, Keterangan.
2. **Dispenser**: pilih Payung induk, Nama Dispenser, Status, Keterangan.
3. **Nozzle**: pilih Dispenser + **Produk** yang dialirkan, Nama Nozzle, Status, Keterangan.
   - Nozzle adalah titik penjualan; stand meternya dicatat di Totalisator.

### 6.5 Aset & Fasilitas
Pencatatan inventaris SPBU.
- **Aset**: SPBU, Nama Aset, Jumlah, Harga, Tanggal Digunakan, Penyusutan per Hari, Status Penggunaan, Keterangan. Mendukung lampiran **Dokumen** (foto/berkas).
- **Fasilitas**: SPBU, Nama Fasilitas, Jumlah, Tanggal Digunakan, Keterangan. Juga mendukung lampiran dokumen.

---

## 7. Manajemen Pegawai & Akun

Menu: **Master Data → Pegawai**.

### 7.1 Menambah Pegawai (sekaligus akun login)
**Tambah** → isi data KTP & akun: NIK, Nama, Tempat & Tanggal Lahir, Jenis Kelamin, Email, No HP, No WA, Status Member, **Username**, **Password**, Verifikasi, Status Akun (Aktif/Tidak) → **Simpan**.

### 7.2 Menetapkan Jabatan
Buka **Detail** pegawai → tombol **Jabatan** → tambahkan jabatan untuk pegawai. **Jabatan inilah yang menentukan Level → menu yang bisa diakses pegawai tersebut.** Satu pegawai bisa memiliki lebih dari satu jabatan.

### 7.3 Reset Password
Di halaman Detail pegawai, tombol **Reset Password** mengembalikan password ke nilai default sistem (konfirmasi diminta sebelum dijalankan).

### 7.4 Edit / Hapus
Tombol **Ubah** dan **Hapus** tersedia di halaman detail. Saat mengubah, kolom Password boleh dikosongkan jika tidak ingin menggantinya.

---

## 8. Modul Procurement (Pengadaan BBM)

Menu: **Procurement**. Ini adalah **alur berurutan** — sebuah rencana pembelian bergerak melewati enam status:

```
Rencana → Pembelian → Pembayaran → MS2 → Pengiriman → Penerimaan
```

Setiap tahap "mendorong" data ke tahap berikutnya setelah dikonfirmasi.

### 8.1 Perencanaan (status: Rencana)
1. **Perencanaan → Tambah**: pilih SPBU, isi Keterangan (Kode Pembelian dibuat otomatis) → **Simpan**.
2. Buka **Detail** rencana → tambahkan **Produk** yang akan dibeli beserta jumlah.
3. **Konfirmasi** rencana:
   - **Setujui** → rencana naik ke status **Pembelian**.
   - **Tolak** → masuk daftar **Perencanaan Ditolak** (tombol di kanan-atas daftar).

### 8.2 Pembelian (status: Pembelian)
1. **Procurement → Pembelian** → buka **Detail**.
2. Tinjau item & harga, dan atur **Pajak** pembelian (tombol Pajak → PPN/PPH/PPBKB, Net Price, Total Amount dihitung otomatis dari nilai pajak aktif).
3. Tombol aksi:
   - **Konfirmasi Harga** → naik ke status **Pembayaran**.
   - **Batalkan Pembelian** → dikembalikan (ditolak).

### 8.3 Pembayaran (status: Pembayaran)
1. **Procurement → Pembayaran** → buka **Detail** rencana.
2. **Tambah Pembayaran**: Tanggal, Penyetor (pegawai), No Rekening, Nama Rekening, No Cek/BG, Total Setor → **Simpan**.
3. Pada detail tiap pembayaran, lakukan **dua konfirmasi**:
   - **Konfirmasi Admin** (oleh PJ)
   - **Konfirmasi Top Manajemen**
   - Setiap pembayaran juga bisa dilampiri **bukti transfer** (menu File pada detail pembayaran).
4. Setelah ada pembayaran yang terkonfirmasi Admin **dan** Top, tombol **Konfirmasi Pembayaran** aktif → menaikkan rencana ke tahap **MS2**. Tombol **Batalkan** mengembalikan ke daftar ditolak.

### 8.4 MS2 (status: MS2)
"MS2" = stok yang sudah dibeli & berada di Pertamina, siap diajukan pengirimannya.
1. **Procurement → MS2**: daftar produk dengan jumlah stok MS2 (dalam Kl). Tombol **MS2 Pengiriman** membuka daftar pengajuan.
2. **Ajukan MS2** (tombol Tambah pada MS2 Pengiriman): pilih Tanggal, lalu centang **detail pembelian** yang akan dikirim (per produk) dan pilih **shift** untuk tiap item → **Simpan**.
3. Buka **Detail** pengajuan MS2 → **Lanjutkan ke Pengiriman** (mengisi No DO, No Polisi, catatan, dan memilih item yang benar-benar dikirim) → data turun ke modul **Delivery/Pengiriman**.

### 8.5 Delivery / Pengiriman (status: Pengiriman)
1. **Procurement → Delivery** → buka **Detail** pengiriman.
2. **Tambah File LO** untuk setiap produk (pilih item, No LO, No Faktur, + unggah gambar LO). Tiap File LO bisa diberi **Lampiran** tambahan.
3. Setelah seluruh produk punya File LO, lakukan konfirmasi:
   - **Konfirmasi Admin** (PJ) dan **Konfirmasi Top Manajemen**.
   - **Ubah Catatan** tersedia kapan saja.
4. Bila Admin + Top sudah konfirmasi → tombol **Tandai Selesai** muncul → pengiriman selesai dan diteruskan ke **Penerimaan BBM**.

### 8.6 Penerimaan (status: Penerimaan)
Lihat [bagian 9.2](#92-penerimaan-bbm) — penerimaan fisik BBM ke dombak menutup rantai procurement dan **menambah stok**.

---

## 9. Modul Manajemen Persediaan (Stok)

Menu: **Manajemen Persediaan**.

### 9.1 Monitoring Stok
Daftar stok terkini per produk (tombol **Tambah** untuk inisialisasi). Buka **Detail** stok untuk melihat **Riwayat pergerakan** (Stok Taking, Penerimaan, Transfer, Losses) lengkap dengan stok awal/akhir dan total loss per kejadian.

### 9.2 Penerimaan BBM
Proses bongkar BBM dari pengiriman ke dombak.
1. Daftar Penerimaan punya tombol **Verifikasi** (antrian menunggu) dan **Riwayat**.
2. Pada **proses penerimaan** (dari File LO pengiriman): pilih dombak tujuan, isi **tinggi dombak sebelum & sesudah** penerimaan untuk tiap dombak → sistem mengubah tinggi → volume (via Tabel Konversi) dan menghitung **Volume Diterima** serta **selisih (Loss/Profit)** terhadap volume dibeli. Isi tanggal, jam kerja, jam penerimaan → **Simpan**.
3. Di halaman **Verifikasi → Detail**, PJ menekan **Konfirmasi** untuk mengesahkan penerimaan; stok dombak resmi bertambah. Lampiran dokumen tersedia.

### 9.3 Stok Taking
Pengukuran/penyesuaian stok dombak.
1. **Tambah**: pilih Tipe Dokumen, **Dombak**, Tanggal, Jam Kerja, lalu isi **Tinggi Dombak (cm)**.
2. Sistem otomatis mengonversi tinggi → **Volume**, lalu menghitung **Volume Selisih** terhadap stok tercatat. Tombol Simpan aktif setelah konversi berhasil.
3. Tombol **Riwayat** menampilkan histori stok taking. Tiap entri mendukung lampiran **Dokumen/foto**.

### 9.4 Stok Transfer
Memindahkan BBM antar dombak berproduk sama.
1. **Tambah**: isi Tanggal, Jam Kerja, Waktu.
2. **Dombak Pertama (asal)**: pilih dombak → muncul daftar **Dombak Kedua (tujuan)** yang seproduk. Isi tinggi sebelum/sesudah di kedua dombak; sistem menghitung volume sebelum/sesudah, **volume dipindahkan**, dan **loss/profit**.
   - Jika tidak ada dombak lain berproduk sama, sistem memberi peringatan — tautkan dulu dombak lain ke produk tersebut di Master → Dombak.
3. **Riwayat** tersedia, beserta lampiran dokumen.

### 9.5 Stok Summary
Tampilan **kalender** bulanan. Klik sebuah **tanggal** untuk melihat ringkasan seluruh pergerakan stok pada hari itu (timeline per dombak: stok taking, penerimaan, transfer, penjualan, losses, beserta urutan jenis pergerakan).

---

## 10. Totalisator (Pencatatan Penjualan)

Menu: **Totalisator**. Inilah pencatatan penjualan harian berbasis stand meter nozzle.

1. **Tambah**: pilih Dombak → Nozzle (otomatis memuat tanggal/shift & **stand meter awal** dari catatan sebelumnya). Sistem mendeteksi bila ada Penerimaan BBM dan menyesuaikan tanggal/shift.
2. Isi **Stand Meter Akhir**; **selisih** (liter terjual) dihitung otomatis.
3. **Simpan**. Nilai penjualan = selisih × harga jual default produk.
4. Tombol **Riwayat** + filter (Produk, Jam Kerja, rentang tanggal). Tiap entri mendukung lampiran dokumen.

> Data totalisator menjadi sumber angka **Setoran** dan grafik penjualan di Dashboard.

---

## 11. Modul Setoran

Menu: **Setoran**. Mengelola uang hasil penjualan, dengan alur status:

```
Open Penjualan → Penitipan Setoran → Closing Penjualan → Setoran Ke Bank → Selesai
```

### 11.1 Open Penjualan
1. **Tambah**: buka sesi penjualan (SPBU, Nozzle, Jam Kerja) — mengaitkan stand meter penjualan ke sesi setoran.
2. Buka **Detail** untuk melihat/menambah stand meter yang ditautkan. Tetapkan **PJ Operasional** (penanggung jawab shift) bila perlu.
3. Lanjutkan sesi → naik ke **Penitipan Setoran**.

### 11.2 Penitipan Setoran
Daftar sesi berstatus Penitipan. Buka **Detail** untuk meninjau total uang penjualan vs total stand meter. Aksi (oleh PJ):
- **Kembali ke Opening** (turunkan status), atau
- **Lanjut ke Closing** (naikkan ke Closing Penjualan).

### 11.3 Closing Penjualan
Tinjau & finalisasi penjualan shift. Aksi konfirmasi menaikkan status ke **Setoran Ke Bank**.

### 11.4 Setoran Bank
Daftar setoran yang siap disetor ke bank, dengan filter **Status Setoran**. Pada detail, lakukan konfirmasi **Admin** dan **Top** untuk menuntaskan setoran (status **Selesai**). Mendukung lampiran bukti setor bank.

---

## 12. Modul Financial

Menu: **Financial**.

### 12.1 Petty Cash (Kas Kecil) — *aktif*
Pencatatan kas kecil. **Tambah**: SPBU, Tanggal, Jenis (**Debit/Kredit**), No Kode, No Bukti, Jumlah, Keterangan. Tiap entri mendukung lampiran **Dokumen**. Daftar bisa difilter.

### 12.2 Rekap Pajak — *aktif*
Rekap pajak pembelian per rentang tanggal, dengan filter **SPBU** (kosongkan = semua SPBU). Menampilkan PPN/PPH/PPBKB & total pajak. Tersedia tombol **Export**.

### 12.3 Margin — *aktif*
Generator laporan unduhan. Pilih **SPBU, Produk, Bulan, Jenis Laporan** (Totalisator / Total Penjualan BBM / Setoran-Penerimaan / Bongkaran Aktual / Stok BBM di Dombak) → **Unduh Laporan** (file Excel).

### 12.4 Laporan lain — *dalam pengembangan*
**Laba Rugi, Neraca, Buku Besar, Arus Kas, Perubahan Modal** saat ini masih dalam tahap pengembangan; menu tersedia namun halaman menampilkan informasi "dalam pengembangan".

---

## 13. Jadwal & Absensi

Menu: **Jadwal & Absensi** (akses PJ).

### 13.1 Penjadwalan Pegawai
Halaman dengan **daftar kartu pegawai + kalender**.
1. Klik **kartu pegawai** → kalender menampilkan jadwal kerja pegawai itu.
2. Tombol **Jadwalkan** → pilih Jabatan, lalu tambahkan satu/beberapa baris **Tanggal + Jam Kerja + Nozzle** (tombol Tambah/Hapus Pengaturan) → **Simpan Jadwal**. Jadwal langsung muncul di kalender.
3. Klik **event** di kalender → konfirmasi untuk **menghapus** jadwal tersebut.
4. Tombol **Kode QR** (cetak QR presensi) dan **Upload File** (impor jadwal massal) tersedia di kanan-atas.

### 13.2 Absensi
Daftar rekap kehadiran terjadwal dengan filter **rentang tanggal, Status Kehadiran, Jam Kerja, Jabatan**. Status kehadiran: *Dijadwalkan, Hadir, Cuti, Sakit, Tanpa Keterangan*. Buka detail untuk meninjau/menyunting status & konfirmasi.

### 13.3 Laporan Rekap — *dalam pengembangan*
Menu tersedia; halaman laporan rekap masih dalam pengembangan.

---

## 14. Kehadiran (Presensi)

Menu: **Kehadiran**. Halaman ini dipakai oleh pegawai (termasuk PJ) untuk presensi:
- Jika ada jadwal yang sedang berjalan, muncul **kartu hijau** "Jadwal kerja sedang berjalan" + tombol **Lanjutkan Presensi**.
- Tabel jadwal kehadiran dengan filter tanggal; tombol **Detail** menuju halaman presensi (pemindaian **QR** kamera untuk check-in/out). Status presensi: *On Time, Terlambat, Sakit, Izin, Alpa*.

---

## 15. Modul Artikel (Portal Publik)

Menu: **Artikel**. Mengelola konten pengumuman yang tampil di **portal publik** (halaman depan sebelum login).

1. **Kategori** & **Tag**: buat label penggolongan artikel terlebih dahulu.
2. **Post**:
   - **Tambah**: isi Judul, Konten, pilih **Kategori** & **Tag** (boleh lebih dari satu — tahan Ctrl/Cmd untuk memilih banyak), Status (Aktif/Nonaktif) → **Simpan**.
   - Hanya post **Aktif** yang tampil di portal publik.
   - Tiap post mendukung lampiran **File**.
3. Pengunjung dapat menelusuri artikel berdasarkan kategori, tag, atau penulis di portal publik.

---

## 16. Modul Tera

Menu: **Tera** (Stok Peminjaman / Stok Pengembalian).

> **Status: dalam pengembangan.** Menu sudah tampil di sidebar, namun fungsinya belum aktif. Tera ditujukan untuk mencatat BBM yang dikeluarkan untuk keperluan **tera/kalibrasi** meter (peminjaman) dan pengembaliannya. Panduan rinci akan ditambahkan setelah modul ini dirilis.

---

## 17. Mengunggah File & Dokumen

Banyak halaman mendukung lampiran (dokumen aset/fasilitas, bukti pembayaran, File LO + lampiran, dokumen stok taking/transfer/penerimaan/totalisator, bukti setoran, file post, import Excel).

- **Cara unggah**: pada kolom unggah, **seret-dan-lepas (drag & drop)** file ke kotak, atau klik **"pilih file"** untuk memilih dari perangkat.
- **Batas ukuran**: ditampilkan pada kotak unggah (mis. "Maks 2 MB"). Jika file melebihi batas, muncul pesan kesalahan **sebelum** dikirim dan file ditolak.
- **Jika file gambar besar gagal**: kompres dulu, atau minta administrator server menaikkan batas (`upload_max_filesize` & `post_max_size` di PHP). Setelah dinaikkan, batas pada aplikasi ikut menyesuaikan otomatis.

---

## 18. Tips, Masalah Umum, & FAQ

**Q: Dropdown pada form kosong (mis. tidak ada pilihan Nozzle/Dombak/Produk).**
A: Data induknya belum diisi. Ikuti [urutan penyiapan](#4-urutan-penyiapan-sistem). Contoh: Nozzle butuh Dispenser + Produk; Dispenser butuh Payung.

**Q: Pilihan "Dombak Kedua" pada Stok Transfer tidak muncul.**
A: Tidak ada dombak lain dengan **produk yang sama** sebagai tujuan. Tautkan dombak lain ke produk tersebut di Master → Dombak.

**Q: Stok Taking/Transfer/Penerimaan tidak menghitung volume / tombol Simpan nonaktif.**
A: Dombak belum punya **Tabel Konversi**. Isi konversi tinggi→volume di Detail Dombak (atau via Import).

**Q: Sebuah rencana pembelian tidak bisa lanjut ke tahap berikut.**
A: Pastikan tahap sebelumnya tuntas: rencana **dikonfirmasi**, harga+pajak **dikonfirmasi**, pembayaran **dikonfirmasi Admin & Top**, pengiriman punya **File LO lengkap** + konfirmasi Admin & Top sebelum **Tandai Selesai**.

**Q: Menu tertentu tidak terlihat pada akun pegawai.**
A: Menu mengikuti **Level** dari Jabatan pegawai. Periksa penetapan Jabatan di Master → Pegawai → Detail → Jabatan, dan pastikan nama Level sesuai (lihat 5.3).

**Q: Halaman tampak memuat ulang penuh saat klik beberapa menu.**
A: Sebagian kecil halaman (mis. pemindai QR presensi, halaman cetak QR jadwal) memang masih dimuat penuh; ini normal dan tidak memengaruhi fungsi.

**Q: Lupa password pegawai.**
A: PJ dapat melakukan **Reset Password** dari halaman Detail pegawai.

---

### Lampiran: Peta Menu Admin (Penanggung Jawab)

```
Dashboard
Master Data ── Aset · Fasilitas · Produk · Pegawai · Harga · Dombak · Payung · Dispenser · Nozzle
Procurement ── Perencanaan · Pembelian · Pembayaran · MS2 · Delivery
Manajemen Persediaan ── Monitoring Stok · Penerimaan BBM · Stok Taking · Stok Transfer · Stok Summary
Tera ── Stok Peminjaman · Stok Pengembalian            (dalam pengembangan)
Totalisator
Setoran ── Open Penjualan · Penitipan Setoran · Closing Penjualan · Setoran Bank
Artikel ── Kategori · Tag · Post
Financial ── Petty Cash · Rekap Pajak · Margin · (Laba Rugi · Neraca · Buku Besar · Arus Kas · Perubahan Modal — dalam pengembangan)
Jadwal & Absensi ── Penjadwalan Pegawai · Absensi · Laporan Rekap
Konfigurasi ── SPBU · Divisi · Level · Jabatan · Pajak · Hari · Jam Kerja · Import Excel
Kehadiran
```

---

*Dokumen ini menjelaskan fungsi sebagaimana tersedia pada aplikasi saat panduan disusun. Untuk role Operator, Keamanan, dan Direksi, lihat buku panduan masing-masing.*
