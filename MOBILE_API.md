# MOBILE_API.md — API Backend untuk Aplikasi Mobile

> Klien resmi: [mobile/](mobile/) (Expo React Native).
> Konteks arsitektur: [ARCHITECTURE.md](ARCHITECTURE.md) · skema: [DATABASE.md](DATABASE.md).

---

## 1. Dasar

| | |
|---|---|
| Base URL | `{APP_URL}/api/v1` |
| Berkas route | [routes/mobile.php](routes/mobile.php) |
| Controller | [app/Http/Controllers/Api/V1/](app/Http/Controllers/Api/V1/) |
| Middleware group | `api` (stateless + throttle) — **bukan** `web` seperti [routes/api.php](routes/api.php) |
| Autentikasi | Laravel Sanctum personal access token |
| Format | JSON; unggahan berkas memakai `multipart/form-data` |

Header wajib pada setiap permintaan:

```http
Accept: application/json
Authorization: Bearer <token>      # kecuali /auth/login dan /ping
```

### Amplop respons

Semua respons sukses memakai bentuk yang sama ([App\Support\ApiResponse](app/Support/ApiResponse.php)):

```json
{ "status": true, "message": "OK", "data": { } }
```

Kegagalan yang ditangani aplikasi:

```json
{ "status": false, "message": "Jadwal ini bukan milik Anda.", "data": [] }
```

Kegagalan **validasi** memakai bentuk bawaan Laravel (HTTP 422):

```json
{ "message": "The username field is required.", "errors": { "username": ["..."] } }
```

Klien di `mobile/src/api/client.js` menormalkan ketiganya menjadi `ApiError`.

### Kode status

| Kode | Arti |
|---|---|
| 200 | Sukses |
| 401 | Token tidak ada / kedaluwarsa / dicabut → klien wajib logout lokal |
| 403 | Terautentikasi tapi tidak berhak (mis. jadwal milik orang lain, akun nonaktif) |
| 422 | Validasi gagal atau aturan bisnis ditolak (QR salah, belum absen masuk, dsb.) |
| 429 | Terlalu banyak percobaan login (`throttle:10,1`) |

<a id="hak-akses-menu"></a>
### Hak akses menu

Pembagian hak akses memakai **nama jabatan**, bukan level — beberapa jabatan
berbagi level yang sama tetapi haknya sengaja berbeda (mis. Admin, Manajer,
dan Supervisor sama-sama berlevel *Penanggung Jawab*).

Satu-satunya sumber kebenaran: [`App\Support\Akses`](app/Support/Akses.php).

| Jabatan | Menu |
|---|---|
| Direktur Utama · Direktur · Komisaris · Admin · Manajer | **seluruh menu** — Beranda, Jadwal, Procurement, Kelola Data (Master + Konfigurasi), Profil |
| jabatan lain (Operator, Security, Supervisor, …) | Beranda, Jadwal, Profil |

Perbandingan nama jabatan **tidak peka huruf besar/kecil**, dan satu pegawai
bisa memegang lebih dari satu jabatan — cukup salah satunya masuk daftar.

Klien tidak perlu menyalin daftar ini: `/auth/login`, `/auth/me`, dan `/profil`
mengembalikan flag `akses_penuh` (boolean) beserta `daftar_jabatan` (array).
Aplikasi mobile memakai flag itu untuk menyembunyikan tab Procurement dan
Kelola. Penyembunyian tab hanyalah kenyamanan — penegakan sebenarnya ada di
middleware server.

Aturan yang sama juga menentukan apakah `GET /dashboard` mengisi bagian
`ringkasan` (angka penjualan & stok) atau membiarkannya kosong.

### Endpoint daftar (paginasi)

```json
{
  "items": [ ... ],
  "meta": { "current_page": 1, "last_page": 3, "per_page": 20, "total": 45, "has_more": true }
}
```

---

## 2. Daftar Endpoint

### Publik

| Method | Path | Keterangan |
|---|---|---|
| GET | `/ping` | Cek konektivitas; balikan `{ app, time }` |
| POST | `/auth/login` | Terbitkan token. Body: `username`, `password`, `device_name?` |

`POST /auth/login` → `data`:

```json
{
  "token": "3|tF9e7OBb…",
  "token_type": "Bearer",
  "user": { "member_id": "…", "nama": "H Ansori", "level": "Penanggung Jawab", "…": "…" }
}
```

Login menghapus token lama dengan `device_name` yang sama (satu perangkat satu
token) dan memperbarui `userlogin.last_login`.

### Autentikasi

| Method | Path | Keterangan |
|---|---|---|
| GET | `/auth/me` | Profil pengguna saat ini |
| POST | `/auth/logout` | Cabut token yang sedang dipakai |
| POST | `/auth/logout-all` | Cabut seluruh token milik pengguna |

### Beranda

| Method | Path | Keterangan |
|---|---|---|
| GET | `/dashboard` | Ringkasan: profil, shift berjalan, jadwal hari ini, jumlah notifikasi belum dibaca, dan `ringkasan` operasional |

`ringkasan` hanya terisi untuk level **Penanggung Jawab, Komisaris, Direktur
Utama, Direktur** (berisi penjualan hari ini/bulan ini, jumlah setoran menunggu
persetujuan, dan stok berjalan). Untuk level lain nilainya `[]`.

### Profil

| Method | Path | Keterangan |
|---|---|---|
| GET | `/profil` | Sama dengan `/auth/me` |
| PUT | `/profil` | Ubah `email`, `no_hp`, `no_wa` |
| POST | `/profil/password` | `password_lama`, `password`, `password_confirmation`. Mencabut semua token lalu **mengembalikan token baru** untuk perangkat ini |
| POST | `/profil/avatar` | multipart, field `avatar` (image) |

### Jadwal & Kehadiran

| Method | Path | Keterangan |
|---|---|---|
| GET | `/jadwal` | Query: `dari`, `sampai` (Y-m-d, default bulan berjalan), `status` = `semua\|belum\|berjalan\|selesai`, `per_page` |
| GET | `/jadwal/berjalan` | Shift yang sudah absen masuk & belum pulang, atau `null` |
| GET | `/jadwal/{penjadwalan}` | Detail + `bukti` (selfie, lokasi, perangkat) |
| POST | `/jadwal/{penjadwalan}/masuk` | Absen masuk (multipart) |
| POST | `/jadwal/{penjadwalan}/pulang` | Absen pulang (multipart, semua field opsional) |

Objek jadwal:

```json
{
  "penjadwalan_id": "546c1c05-…",
  "tanggal": "2024-12-04",
  "mode": "out",
  "status_kehadiran": "Hadir",
  "jabatan": "Security",
  "jam_kerja": { "nama": "Shift 2", "urutan": 2, "jam_datang": "14:00:00", "jam_pulang": "22:00:00", "batas_telat": "14:15:00" },
  "deskripsi": null,
  "is_attended": true,
  "is_confirm": true,
  "start_time": "2024-12-04 14:37:02",
  "finish_time": null,
  "nozzles": []
}
```

`mode` adalah aksi berikutnya yang relevan bagi klien:
`in` = perlu absen masuk · `out` = sedang bertugas, perlu absen pulang ·
`done` = selesai.

**`POST /jadwal/{id}/masuk`** — `multipart/form-data`:

| Field | Wajib | Keterangan |
|---|---|---|
| `barcode` | ✔ | Isi QR code yang dipindai |
| `latitude`, `longitude` | ✔ | Koordinat GPS perangkat |
| `selfie` | ✔ | Berkas gambar |
| `gmt` | | Offset jam, default `7` (WIB) |
| `deskripsi_lokasi` | | Catatan bebas |
| `device[tipe]`, `device[model]`, `device[os]`, `device[versi_os]`, `device[aplikasi]`, `device[versi_aplikasi]` | | Metadata perangkat |

Efek: menonaktifkan barcode (`is_done=1, is_active=0`), mengisi
`penjadwalan.is_attended / start_time / attended / kode_penjadwalan`, membuat
baris `image_selfie`, `location`, dan `devices`. Seluruhnya dalam satu transaksi.

**Idempoten**: jika jadwal sudah pernah diabsen masuk, endpoint mengembalikan
`200` dengan pesan "Anda sudah absen masuk untuk jadwal ini" — bukan error.
Ini penting karena jaringan di SPBU sering putus setelah request berhasil.

Penolakan yang mungkin (422): `QR code untuk jadwal ini belum tersedia atau
sudah terpakai…`, `QR code tidak cocok dengan jadwal ini.`

### Notifikasi

| Method | Path | Keterangan |
|---|---|---|
| GET | `/notifikasi` | Query: `hanya_belum_dibaca`, `per_page` |
| GET | `/notifikasi/belum-dibaca` | `{ total }` |
| POST | `/notifikasi/{notifikasi}/baca` | Tandai satu |
| POST | `/notifikasi/baca-semua` | Tandai semua |

### Procurement

Alur lima tahap: **Perencanaan → Pembelian → Pembayaran → MS2 → Delivery**.
Terbuka untuk semua pengguna terautentikasi — sepadan dengan menu Procurement
di web yang tampil untuk PJ, direksi, dan operator.

> **Presisi angka.** Seluruh nilai uang dan kuantitas pada modul ini dikirim
> dan diterima sebagai **string dengan tepat 6 angka di belakang koma**
> (mis. `"156838621.000000"`). Perhitungan server memakai bcmath lewat
> [`App\Support\Uang`](app/Support/Uang.php), bukan float. Klien **tidak boleh**
> membungkusnya dengan `Number()`/`parseFloat` — nilai seperti
> `207025633.303703` melampaui presisi aman double JavaScript. Saat mengirim,
> kirimkan juga sebagai string.

| Method | Path | Keterangan |
|---|---|---|
| GET | `/procurement/ringkasan` | Jumlah dokumen per tahap + tarif pajak berlaku |

**Tahap 1 — Perencanaan**

| Method | Path | Keterangan |
|---|---|---|
| GET | `/procurement/perencanaan` | `?status=aktif\|ditolak\|semua`, `cari`, `page` |
| POST | `/procurement/perencanaan` | `spbu_id`, `kode_pembelian?` (auto `A-YYYYMMDD-n`), `keterangan?` |
| GET / PUT / DELETE | `/procurement/perencanaan/{id}` | Detail / ubah keterangan / hapus |
| POST | `/procurement/perencanaan/{id}/konfirmasi` | `setuju` (bool), `keterangan?` → naik ke Pembelian atau ditandai ditolak |
| POST | `/procurement/perencanaan/{id}/produk` | `produk_id`, `jumlah`, `satuan_jumlah` |
| PUT / DELETE | `/procurement/perencanaan/{id}/produk/{detail}` | Ubah / hapus baris |

Saat baris ditambah/diubah/dihapus, server menghitung ulang:

```
harga        = harga_beli default produk (master Harga)
rit          = ceil(jumlah / 8000)
total_harga  = jumlah × harga
grand_total  = Σ total_harga
```

**Tahap 2 — Pembelian**

| Method | Path | Keterangan |
|---|---|---|
| GET | `/procurement/pembelian` · `/{id}` | Daftar & detail |
| PUT | `/procurement/pembelian/{id}/produk/{detail}` | `harga`, `rit?`, `jumlah?` — kunci harga tebus, semua turunannya dihitung ulang |
| PUT | `/procurement/pembelian/{id}/pajak` | `net_price`, `ppn`, `ppbkb`, `pph`, `total_amount?`, `metode_posting_pajak` (`hpp\|terpisah`) |
| POST | `/procurement/pembelian/{id}/pajak/hitung-ulang` | Kembalikan pajak ke hasil tarif aktif |
| PUT | `/procurement/pembelian/{id}/lanjut` · `/kembalikan` | Naik ke Pembayaran / turun ke Perencanaan |

Rumus pajak (identik dengan web, dijalankan pada bcmath skala 6):

```
net_price    = grand_total
ppn          = grand_total × ppn%      ppbkb = grand_total × ppbkb%
pph          = grand_total × pph%
total_amount = net_price + ppn + ppbkb + pph
```

Bila `total_amount` dikirim dan berbeda dari penjumlahan komponennya, server
tetap menyimpannya lalu mengembalikan `data.selisih_total` sebagai peringatan.

**Tahap 3 — Pembayaran**

| Method | Path | Keterangan |
|---|---|---|
| GET | `/procurement/pembayaran` · `/{id}` | Daftar & detail + `rekap_pembayaran` |
| POST | `/procurement/pembayaran/{id}` | `tanggal`, `no_cek_bg`, `no_rekening`, `nama_rekening`, `total_setoran` |
| PUT / DELETE | `/procurement/pembayaran/{id}/{bayar}` | Hanya selama belum dikonfirmasi |
| PUT | `/procurement/pembayaran/{id}/{bayar}/konfirmasi` | `peran` (`admin\|top`), `setuju` (bool) |
| GET / POST | `/procurement/pembayaran/{id}/{bayar}/lampiran` | Bukti transfer (multipart `berkas`) |
| DELETE | `/procurement/pembayaran/{id}/{bayar}/lampiran/{file}` | |
| PUT | `/procurement/pembayaran/{id}/lanjut` · `/tolak` | Naik ke MS2 / kembali ke Perencanaan |

`rekap_pembayaran` berisi `tagihan`, `dibayar`, `dibayar_terkonfirmasi`,
`sisa`, `sisa_terkonfirmasi`, `jumlah_transaksi`, `menunggu_konfirmasi` —
semuanya string 6 desimal.

**Tahap 4 — MS2**

| Method | Path | Keterangan |
|---|---|---|
| GET | `/procurement/ms2` · `/{id}` | Daftar & detail |
| GET | `/procurement/ms2/kandidat` | Baris yang sudah di tahap MS2 dan belum dialokasikan |
| POST | `/procurement/ms2` | `tanggal`, `items[]` = `{detail_rencana_pembelian_id, jam_kerja_id}` |
| DELETE | `/procurement/ms2/{id}` | Kembalikan produk ke daftar kandidat |
| POST | `/procurement/ms2/{id}/kirim` | `tanggal`, `no_pol`, `no_do?`, `nama_supir?`, `catatan?`, `detail_ids[]` → membuat dokumen Pengiriman |

**Tahap 5 — Delivery**

| Method | Path | Keterangan |
|---|---|---|
| GET | `/procurement/delivery` | `?status=berjalan\|selesai\|semua` |
| GET | `/procurement/delivery/{id}` | Detail + `lo`, `kandidat_lo`, `kalkulasi` |
| PUT | `/procurement/delivery/{id}` | `catatan`, `no_do`, `no_pol` |
| PUT | `/procurement/delivery/{id}/konfirmasi` | `peran`, `setuju` |
| PUT | `/procurement/delivery/{id}/selesai` | Butuh minimal satu LO |
| GET / POST | `/procurement/delivery/{id}/lo` | multipart: `detail_rencana_pembelian_id`, `no_faktur`, `no_lo`, `berkas` |
| DELETE | `/procurement/delivery/{id}/lo/{lo}` | Ditolak bila LO sudah dipakai Penerimaan BBM |

Kuantitas dan satuan pada LO **disalin server** dari baris rencana pembelian —
tidak diketik ulang klien — agar angka LO tidak menyimpang dari dokumen asal.

#### Catatan implementasi

- **Kolom boolean di database bertipe `varchar(5)`**, bukan boolean
  (`is_confirmation`, `ms2`, `delivery`, `is_denied`, `is_confirmation_admin`, …).
  Server menulis `'0'`/`'1'` dan membacanya dengan `filter_var(...)`; API selalu
  mengembalikannya sebagai boolean JSON asli.
- **`detail_rencana_pembelian.kode_detail`** ada di database tetapi tidak ada di
  migrasi mana pun (drift dari dump) dan dibatasi `varchar(16)`; diisi penanda
  pendek `D-XXXXXXXXXXXX`.
- **Float JSON tetap aman.** `Uang::bersih()` mengonversi float dengan
  `sprintf('%.12F')`, bukan cast `(string)` — cast memakai `precision=14` PHP
  dan akan memotong nilai seperti `156837600.987654` menjadi `…98765`.

### Master Data & Konfigurasi — **hanya jabatan berakses penuh**

Route berprefiks `/master`, `/konfigurasi`, `/procurement`, ditambah `/opsi`,
dijaga middleware `akses.penuh`
([EnsureAksesPenuh](app/Http/Middleware/EnsureAksesPenuh.php)). Jabatan lain
menerima 403 dengan `data.jabatan_anda` berisi jabatan pengguna.
Daftar jabatannya lihat [§ Hak akses menu](#hak-akses-menu).

| Method | Path | Keterangan |
|---|---|---|
| GET | `/opsi` | Semua daftar dropdown sekaligus: `spbu`, `produk`, `dombak`, `payung`, `dispenser`, `jam_kerja`, `divisi`, `level`, `jabatan`, `hari`, `status_member`, `jenis_kelamin`, `satuan_luas` |

#### Master Data

Sembilan resource memakai bentuk route yang identik, dengan `{resource}` ∈
`aset` · `fasilitas` · `produk` · `harga` · `dombak` · `payung` · `dispenser` ·
`nozzle` · `member`:

| Method | Path | Keterangan |
|---|---|---|
| GET | `/master/{resource}` | Daftar. Query: `cari`, `spbu_id`, `page`, `per_page` |
| POST | `/master/{resource}` | Tambah |
| GET | `/master/{resource}/{id}` | Detail |
| PUT | `/master/{resource}/{id}` | Ubah |
| DELETE | `/master/{resource}/{id}` | Hapus |

Setiap item daftar selalu memuat `id`, `judul`, dan `subjudul` selain
kolom aslinya, supaya klien bisa merender daftar apa pun tanpa logika khusus.

Khusus `harga`: default hanya menampilkan harga yang **berlaku**
(`is_default`); tambahkan `?semua=1` untuk melihat riwayat, atau
`?produk_id=...` untuk memfilter per produk.

#### Konfigurasi

Tujuh resource dengan bentuk route yang sama persis, prefiks `/konfigurasi`,
`{resource}` ∈ `spbu` · `divisi` · `level` · `jabatan` · `hari` ·
`jam-kerja` · `pajak`:

| Method | Path |
|---|---|
| GET / POST | `/konfigurasi/{resource}` |
| GET / PUT / DELETE | `/konfigurasi/{resource}/{id}` |

Hierarkinya berjenjang: **SPBU → Divisi → Level → Jabatan**, dan
`level.nama_level` inilah yang menentukan hak akses
(`Penanggung Jawab`, `Komisaris`, `Direktur Utama`, `Direktur`, `Operator`,
`Keamanan` — lihat [App\Support\Navigation](app/Support/Navigation.php)).

Catatan per resource:

- **`jam-kerja`** memuat delapan kolom waktu (`batas_awal_in`,
  `batas_akhir_in`, `jam_datang_in`, `jam_pulang_out`, `akhir_out`,
  `jam_istirahat`, `masuk_istirahat`, `batas_telat`). Server menerima
  `HH:MM` maupun `HH:MM:SS`, dan selalu mengembalikan `HH:MM`.
  Field opsional `hari_ids` (array) menyinkronkan pivot `hari_jam_kerja`;
  pivot itu **belum dikonsumsi logika operasional mana pun** dan hanya
  disentuh bila kunci `hari_ids` dikirim.
- **`pajak`** (`pajak_pembelian_lib`) hanya boleh punya **satu baris aktif** —
  alur pembelian mengambilnya dengan
  `PajakPembelianLib::where('is_aktif', 1)->first()` di enam tempat.
  Menyimpan tarif dengan `is_aktif: true` otomatis menonaktifkan tarif lain.
- **`spbu`** adalah akar hampir semua data; penghapusannya dijaga sangat
  ketat (divisi, produk, dombak, payung, jam kerja, aset, fasilitas,
  penjualan, rencana pembelian, petty cash).
- **Import Excel** (menu Konfigurasi → Import di web) **tidak** disediakan di
  API mobile; unggah berkas Excel tetap lewat web.

#### Foto & lampiran

Hanya `aset` dan `fasilitas` yang punya tabel berkas di skema
(`file_aset`, `file_fasilitas`):

| Method | Path | Keterangan |
|---|---|---|
| GET | `/master/{jenis}/{id}/lampiran` | Daftar lampiran; tiap item punya `url` dan `is_gambar` |
| POST | `/master/{jenis}/{id}/lampiran` | multipart: `berkas` (wajib), `nama_dokumen` (opsional) |
| DELETE | `/master/{jenis}/{id}/lampiran/{lampiran}` | Hapus baris **dan** berkas fisiknya |

`{jenis}` dibatasi `aset|fasilitas`. Berkas disimpan di
`storage/app/public/{aset,fasilitas}/` dengan pola nama
`<nama-asli>_<timestamp>.<ext>` — sama seperti alur web.

Foto pegawai memakai kolom `member.avatar`, bukan tabel lampiran:

| Method | Path | Keterangan |
|---|---|---|
| POST | `/master/member/{id}/avatar` | multipart: `avatar` (image). Menghapus foto lama, kecuali `default.png` |

#### Aksi khusus pegawai

| Method | Path | Keterangan |
|---|---|---|
| PUT | `/master/member/{id}/reset-password` | Reset ke `12345678` (mengikuti web) dan cabut semua token pegawai tersebut |
| POST | `/master/member/{id}/jabatan` | Body `jabatan_id` |
| DELETE | `/master/member/{id}/jabatan/{jabatan}` | Lepas jabatan |
| POST | `/master/member/{id}/spbu` | Body `spbu_id` |
| DELETE | `/master/member/{id}/spbu/{spbu}` | Lepas penempatan |

#### Perilaku yang perlu diketahui

Kedua modul memakai kerangka yang sama,
[`Api\V1\CrudController`](app/Http/Controllers/Api/V1/CrudController.php):
subkelas hanya mendeklarasikan model, validasi, pemetaan payload, serialisasi,
dan penghalang penghapusan.


- **Penghapusan dijaga.** Hampir semua FK memakai `cascadeOnDelete`
  ([DATABASE.md §8](DATABASE.md)), jadi menghapus satu produk bisa merontokkan
  harga, nozzle, stok, hingga detail penjualan. Endpoint DELETE menolak lebih
  dulu (422) bila masih ada relasi pemakai, dengan pesan seperti:
  `Produk tidak bisa dihapus karena masih dipakai: 2 nozzle, 1 riwayat harga.`
  dan rincian di `data.penghalang`.
- **Pegawai memakai soft delete.** `member`, `ktp`, dan `userlogin`
  di-soft-delete bersama sehingga riwayat transaksi tetap utuh. Menghapus akun
  sendiri ditolak.
- **Produk selalu punya satu `detail_produk`** (jenis + oktan) yang
  dibuat/diperbarui bersamaan — form mengirim `nama_jenis` dan `oktan`.
- **Harga baru otomatis menjadi harga berlaku**; harga lama diturunkan menjadi
  riwayat dan **tidak** dihapus karena `detail_penjualan.harga_id` merujuknya
  sebagai dasar HPP perpetual ([docs/keuangan.md](docs/keuangan.md)).
- **Password pegawai tidak pernah dikembalikan** dan hanya diubah bila field
  `password` dikirim terisi.

### Manajemen Persediaan — **hanya jabatan berakses penuh**

Prefiks `/persediaan`, di bawah middleware `akses.penuh` yang sama.
Logika bersama: [`App\Support\Persediaan`](app/Support/Persediaan.php).

| Method | Path | Keterangan |
|---|---|---|
| GET | `/persediaan/ringkasan` | Saldo total, saldo per produk & dombak, antrean konfirmasi, losses hari ini |
| GET | `/persediaan/dombak` | Dombak + saldo + apakah siap dipakai (punya produk & tabel konversi) |
| GET | `/persediaan/dombak/{id}/konversi?cari=` | Cari tinggi pada tabel konversi (maks 60 baris) |

**Monitoring Stok**

| Method | Path |
|---|---|
| GET / POST | `/persediaan/monitoring` |
| GET | `/persediaan/monitoring/{id}` · `/persediaan/monitoring/{id}/riwayat` |
| POST / DELETE | `/persediaan/monitoring/{id}/dombak` · `/persediaan/monitoring/{id}/dombak/{dombak}` |
| POST | `/persediaan/monitoring/{id}/koreksi` — koreksi manual, tercatat sebagai dokumen *Commit Change From User* |

**Stok Taking** — `GET|POST /persediaan/stok-taking`, `GET .../riwayat`,
`GET|DELETE .../{id}`, `POST .../{id}/konfirmasi`.
Body simpan: `dombak_id`, `jam_kerja_id`, `tanggal`, `tinggi`.

**Penerimaan BBM** — `GET /persediaan/penerimaan/lo` (LO belum dibongkar),
`GET .../lo/{id}`, `POST .../lo/{id}` (body: `tanggal`, `waktu`,
`jam_kerja_id`, `alokasi[]` berisi `dombak_id`, `tinggi_sebelum`,
`tinggi_sesudah`, `jumlah`), `GET .../verifikasi`, `GET .../riwayat`,
`GET|DELETE .../{id}`, `POST .../{id}/konfirmasi`.

**Stok Transfer** — `GET|POST /persediaan/stok-transfer`, `GET .../riwayat`,
`GET|DELETE .../{id}`, `POST .../{id}/konfirmasi`. Body simpan memuat
tinggi sebelum & sesudah untuk dombak asal dan tujuan.

**Stok Summary** — `GET /persediaan/summary/{Y-m-d}`.

#### Aturan yang berlaku di seluruh modul

- **Saldo hanya berubah saat konfirmasi.** `POST .../store` membuat dokumen
  `is_confirmation = false`; saldo dombak baru diperbarui oleh
  `POST .../konfirmasi`. Dokumen yang sudah dikonfirmasi tidak bisa dihapus.
- **Tinggi → volume lewat tabel konversi.** Tinggi yang tidak ada di tabel
  ditolak 422 dengan pesan jelas, bukan disimpan sebagai nol.
- **Semua angka stok adalah STRING 6 desimal** (`"12674.000000"`), dihitung
  dengan bcmath lewat `App\Support\Uang`. Jangan melewatkannya lewat
  `Number()` di klien bila presisinya penting.
- **Total produk selalu diturunkan ulang** dari penjumlahan seluruh
  `stok_inventory_dombak` (`Persediaan::sinkronkanTotal`), tidak dihitung
  bertahap. Alur web menghitungnya ad-hoc di tiap titik konfirmasi — dan di
  satu tempat memakai dokumen yang keliru — sehingga totalnya bisa menyimpang
  dari jumlah per dombak.
- **Selisih dibukukan sebagai losses/profit** pada `losses` sesuai jenisnya:
  *Stok Taking*, *Good Issue Penerimaan*, atau *Good Transfer*.
- **Idempoten**: mengirim ulang data yang sama persis tidak membuat dokumen
  kedua.

### Totalisator — **hanya jabatan berakses penuh**

Prefiks `/totalisator`, di bawah `akses.penuh`. Tabel `stand_meter`: angka
meter tiap nozzle per shift.

| Method | Path | Keterangan |
|---|---|---|
| GET | `/totalisator/ringkasan` | Shift terbuka, siap konfirmasi, belum diisi, liter hari ini |
| GET | `/totalisator` | Daftar **belum dikonfirmasi**, berpaginasi (`page`, `per_page`, filter `dari`/`sampai`/`nozzle_id`/`dombak_id`) |
| GET | `/totalisator/riwayat` | Daftar sudah dikonfirmasi, berpaginasi |
| GET | `/totalisator/nozzle/{dombak}` | Nozzle yang melayani sebuah dombak (lewat payung → dispenser) |
| GET | `/totalisator/saran/{nozzle}` | Tanggal, shift berikutnya, dan angka awal terbawa dari shift sebelumnya |
| POST | `/totalisator` | Buka shift: `nozzle_id`, `dombak_id`, `jam_kerja_id`, `tanggal`, `stand_meter_awal` |
| GET | `/totalisator/{id}` | Detail |
| PUT | `/totalisator/{id}` | Ubah tanggal/shift/angka (hanya sebelum dikonfirmasi) |
| PUT | `/totalisator/{id}/akhir` | Isi angka meter akhir |
| POST | `/totalisator/{id}/konfirmasi` | Potong stok + buat dokumen *Good Issue Sales* |
| DELETE | `/totalisator/{id}` | Hapus (ditolak bila sudah dikonfirmasi atau dipakai setoran) |

#### Perilaku

- **Angka awal terbawa otomatis.** `/saran/{nozzle}` membaca shift terakhir
  nozzle tersebut, menaikkan urutan shift (berganti hari bila sudah shift
  terakhir), dan mengembalikan `stand_meter_akhir` sebelumnya sebagai angka
  awal dengan `awal_terkunci: true`. Bila shift sebelumnya belum ditutup,
  angka awal dikembalikan `0` dan `awal_terkunci: false` agar diisi manual.
- **Validasi**: satu nozzle hanya boleh punya satu catatan per tanggal+shift;
  angka akhir tidak boleh lebih kecil dari angka awal; konfirmasi ditolak bila
  angka akhir masih nol.
- **Konfirmasi memotong stok.** Liter terjual = `akhir − awal`. Saldo dombak
  berkurang sebesar itu, total produk disinkronkan, lalu dibuat dokumen
  `Good Issue Sales` (sudah `is_confirmation = 1`) yang tertaut ke
  `stand_meter_dokumen_stok` dan muncul di Stok Summary.
- **Penyesuaian shift dengan penerimaan.** Bila pada shift yang sama ada
  penerimaan BBM tetapi belum ada dokumen penjualan, angka pemotongan stok
  direkonsiliasi terhadap isi tangki — **formulanya disalin apa adanya dari
  `StandMeterController` web**, karena angka ini menjadi dasar penjualan & HPP.
- Semua angka meter STRING 6 desimal.

### Setoran — **hanya jabatan berakses penuh**

Prefiks `/setoran`. Satu record `penjualan` per shift berjalan melewati lima
tahap `status_setoran`:

`Open Penjualan → Penitipan Setoran → Closing Penjualan → Setoran Ke Bank → Selesai`

| Method | Path | Keterangan |
|---|---|---|
| GET | `/setoran/ringkasan` | Jumlah per tahap, total bulan berjalan, jumlah menunggu persetujuan |
| GET | `/setoran` | **Daftar berpaginasi** dengan pencarian & filter (lihat bawah) |
| POST | `/setoran` | Buka setoran: `spbu_id`, `jam_kerja_id`, `tanggal`, `stand_meter_id[]` |
| GET / DELETE | `/setoran/{id}` | Detail / hapus |
| POST | `/setoran/{id}/maju` · `/mundur` · `/selesai` | Pindah tahap |
| POST | `/setoran/{id}/persetujuan` | `peran` = `admin\|top`, `setuju` = boolean |
| POST | `/setoran/{id}/detail/{detail}/konfirmasi` | Konfirmasi satu baris stand meter |
| GET | `/setoran/stand-meter-tersedia?tanggal=&jam_kerja_id=` | Stand meter yang belum dipakai setoran |
| POST / DELETE | `/setoran/{id}/stand-meter` · `/{detail}` | Tambah / hapus baris |
| GET | `/setoran/petugas/{jabatan}` | Pegawai pemegang jabatan (untuk PJ & penyetor) |
| POST / DELETE | `/setoran/{id}/pj` · `/{pj}` | Penanggung jawab shift |

**Setoran ke bank** (tabel `penarikan`):

| Method | Path |
|---|---|
| GET / POST | `/setoran/{id}/bank` |
| GET / PUT / DELETE | `/setoran/{id}/bank/{bank}` |
| POST | `/setoran/{id}/bank/{bank}/konfirmasi` — body `setuju` |
| GET / POST | `/setoran/{id}/bank/{bank}/lampiran` — bukti setor (multipart `berkas`) |
| DELETE | `/setoran/{id}/bank/{bank}/lampiran/{file}` |

#### Pencarian & filter pada `GET /setoran`

| Parameter | Isi |
|---|---|
| `cari` | Pencarian bebas: tanggal, nama shift, nama SPBU, nama nozzle, nama bank, no. referensi, metode transfer |
| `tahap` | `semua` (default) · `open` · `penitipan` · `closing` · `bank` · `selesai` |
| `dari`, `sampai` | Rentang tanggal — **default bulan berjalan**, sama seperti web |
| `spbu_id`, `jam_kerja_id` | Filter langsung |
| `diterima_admin`, `diterima_top` | Boolean |
| `nama_bank`, `metode_transfer`, `no_referensi`, `member_id` | Menyaring lewat catatan penarikan |
| `page`, `per_page` | Paginasi (default 20, maks 100) |

#### Perilaku

- **`total_penjualan` selalu diturunkan ulang** dari seluruh baris detail.
  Alur web menjumlahkannya sambil berjalan **dan menambahkan nilai baris baru
  dua kali**, sehingga totalnya bisa menggelembung.
- **`total_uang_kotak` selalu diturunkan ulang** dari jumlah penarikan pada
  shift itu — angka ini dipakai jurnal penjualan untuk menghitung selisih
  kurang/lebih setor.
- **Persetujuan memicu jurnal.** `is_accepted_admin` **dan** `is_accepted_top`
  bernilai true → terbentuk jurnal `penjualan` (kas) dan `penjualan_hpp`;
  mencabut salah satunya membatalkan keduanya. Konfirmasi penarikan memicu
  jurnal Bank/Kas. Bila periode akuntansi sudah ditutup, aksinya ditolak 422.
- **Penghapusan dijaga**: setoran yang sudah disetujui atau masih punya catatan
  bank tidak bisa dihapus; penarikan yang sudah dikonfirmasi juga tidak.
- Semua nilai uang STRING 6 desimal.

### Referensi

| Method | Path | Keterangan |
|---|---|---|
| GET | `/referensi/stok` | Stok per produk + rincian per dombak |
| GET | `/referensi/harga` | Harga jual & beli aktif per produk |

Keduanya dibatasi SPBU tempat pegawai ditempatkan (`spbu_member`). Bila pegawai
belum punya penempatan, data yang dikembalikan mencakup seluruh SPBU.

---

## 3. Perbedaan Sengaja dari Alur Web

Endpoint mobile mencerminkan aturan bisnis
[KehadiranOperatorController](app/Http/Controllers/KehadiranOperatorController.php),
dengan tiga penyimpangan yang disengaja:

1. **Verifikasi kepemilikan jadwal.** Alur web tidak memeriksa apakah
   `penjadwalan` milik pengguna yang login — siapa pun yang tahu ID bisa
   mengabsenkan orang lain. Endpoint mobile menolaknya dengan 403.
2. **Lokasi dari GPS perangkat.** Web memanggil `geoplugin.net` berdasarkan IP
   (akurasi tingkat kota, dan gagal bila server tidak punya akses internet
   keluar). Mobile mengirim koordinat GPS, dan menandai `mocked location`
   Android ke kolom `location.deskripsi`.
3. **Selfie benar-benar disimpan.** Tabel `image_selfie` sudah ada sejak 2024
   tetapi **tidak pernah diisi oleh alur web mana pun**; mobile adalah konsumen
   pertamanya. Berkas disimpan di `storage/app/public/image_selfie/`.
4. **Master Data ditegakkan per level.** Di web, menu Master Data hanya
   *disembunyikan* dari non-PJ; route-nya sendiri tetap bisa dipanggil siapa
   pun yang login. Di API, `level:Penanggung Jawab` benar-benar memblokir.
5. **Penghapusan master dijaga relasi.** Web langsung menghapus dan
   membiarkan cascade berjalan; API menolak bila data masih dipakai.
6. **Password pegawai di-hash lewat cast `hashed`.** `MemberController::update`
   versi web memanggil `Hash::create()` yang tidak ada di facade `Hash` —
   jalur itu akan fatal error bila password diisi. API tidak menirunya.

### Hal yang sengaja TIDAK diubah

**Masa berlaku QR tidak diperiksa.** Barcode dibuat dengan `finish = now +
validate_barcode.is_expired` (default 15 detik), tetapi barcode baru hanya
dibuat saat PJ membuka halaman QR (`JadwalKerjaController::checkAndCreateQrCode`).
Alur web menerima barcode aktif mana pun tanpa memeriksa `finish`, dan endpoint
mobile mengikuti perilaku itu — menambahkan pemeriksaan kedaluwarsa sepihak akan
memblokir absensi di lapangan setiap kali halaman QR tidak sedang di-refresh.
Kalau penegakan kedaluwarsa memang diinginkan, perubahannya harus dilakukan
bersamaan di sisi web (regenerasi barcode terjadwal) dan mobile.

---

## 4. Perubahan Infrastruktur yang Dibutuhkan

Tiga hal berikut sudah dikerjakan dan perlu dijalankan di setiap environment:

1. **`UserLogin` memakai trait `HasApiTokens`**
   ([app/Models/UserLogin.php](app/Models/UserLogin.php)).
2. **Migrasi `2026_08_13_000001`** — `personal_access_tokens.tokenable_id`
   diubah dari `bigint` ke `varchar(36)`. Tanpa ini token tidak bisa disimpan
   karena seluruh PK aplikasi adalah UUID.
3. **Migrasi `2026_08_13_000002`** — memulihkan sequence `id` pada
   `personal_access_tokens` dan `failed_jobs`. Sama seperti masalah yang sudah
   diperbaiki untuk tabel `migrations`: database hasil restore dump kehilangan
   `DEFAULT nextval(...)`, sehingga insert gagal dengan
   *"null value in column id violates not-null constraint"*.

Jalankan **hanya dua migrasi ini** (jangan `php artisan migrate` polos — masih
ada migrasi lama yang sengaja tertinggal, lihat [DATABASE.md §8](DATABASE.md)):

```bash
php artisan migrate --path=database/migrations/2026_08_13_000001_alter_personal_access_tokens_tokenable_id_to_string.php
php artisan migrate --path=database/migrations/2026_08_13_000002_repair_bigint_id_sequences.php
```

CORS sudah mengizinkan `api/*` dari semua origin (`config/cors.php`), jadi tidak
ada perubahan tambahan untuk klien mobile.

---

## 5. Uji Cepat dengan curl

```bash
BASE=http://127.0.0.1:8000/api/v1

curl -s "$BASE/ping" -H 'Accept: application/json'

TOKEN=$(curl -s -X POST "$BASE/auth/login" -H 'Accept: application/json' \
  -d 'username=MGSJ-004&password=RAHASIA&device_name=curl' |
  python3 -c 'import sys,json; print(json.load(sys.stdin)["data"]["token"])')

curl -s "$BASE/dashboard" -H 'Accept: application/json' -H "Authorization: Bearer $TOKEN"
curl -s "$BASE/jadwal?status=belum" -H 'Accept: application/json' -H "Authorization: Bearer $TOKEN"
```

Untuk absen masuk (perlu berkas gambar):

```bash
curl -s -X POST "$BASE/jadwal/<PENJADWALAN_ID>/masuk" \
  -H 'Accept: application/json' -H "Authorization: Bearer $TOKEN" \
  -F 'barcode=<isi-qr>' -F 'latitude=-5.4291' -F 'longitude=105.2615' \
  -F 'selfie=@/path/ke/selfie.jpg'
```

---

## 6. Batasan yang Diketahui

- **Belum ada push notification.** Klien menarik data saat layar difokuskan;
  belum ada Expo Push token maupun tabel penyimpanannya.
- **Token tidak kedaluwarsa** (`config/sanctum.php` → `'expiration' => null`).
  Pencabutan hanya lewat logout, ganti password, atau `logout-all`.
- **Tanpa otorisasi berbasis peran di route.** Sama seperti web, seluruh
  endpoint hanya dijaga autentikasi; pembatasan level dilakukan pada isi data
  (mis. `ringkasan` dashboard), bukan pada akses route.
- **Belum mencakup transaksi operasional harian.** Setoran, stok taking, dan
  totalisator belum punya endpoint.
- **Penerimaan BBM belum ada di mobile.** Dokumen LO hasil Delivery baru bisa
  diproses menjadi penerimaan lewat web.
- **Import Excel belum ada di API.** Menu Konfigurasi → Import di web
  (stok taking, penerimaan, stok transfer, totalisator) tetap web-only.
- **Procurement, Master Data, dan Konfigurasi adalah satu-satunya bagian API
  dengan otorisasi berbasis jabatan.** Endpoint lain tetap hanya dijaga
  autentikasi.
- **Unggahan berjalan sinkron** (`QUEUE_CONNECTION=sync`); selfie sebaiknya
  dikompres di klien (aplikasi memakai `quality: 0.5`).
