# DATABASE.md — Skema & Data Monitoring SPBU

> Pasangan dari [ARCHITECTURE.md](ARCHITECTURE.md) (kode & alur) dan
> [INFRASTRUCTURE.md](INFRASTRUCTURE.md) (build & deploy).
> Aturan akuntansi rinci: [docs/keuangan.md](docs/keuangan.md).

Sumber kebenaran skema: **108 file migrasi** di
[database/migrations/](database/migrations/). Dokumen ini adalah katalog
terstruktur dari isi migrasi tersebut, bukan pengganti.

---

## 1. Konvensi Skema

| Aspek | Konvensi |
|---|---|
| DBMS | PostgreSQL (dev & container). `config/database.php` masih default `mysql` — nilai efektif datang dari `DB_CONNECTION=pgsql` di `.env` |
| Nama tabel | **singular, Bahasa Indonesia**, snake_case: `member`, `penjualan`, `dokumen_stok`. Pengecualian bawaan Laravel: `failed_jobs`, `personal_access_tokens`, `password_reset_tokens` |
| Primary key | UUID bernama `<nama_tabel>_id` (`spbu_id`, `detail_penjualan_id`) |
| Tipe PK | Migrasi lama: `uuid()`. Migrasi keuangan (2026): `char(36)` — **sengaja**, karena DB dev hasil restore dump menyimpan semua id sebagai `character(36)`, bukan tipe `uuid` native. FK dan join hanya valid jika tipenya senada |
| Generasi UUID | Di aplikasi, `Ramsey\Uuid\Uuid::uuid4()` pada `boot()` model — **bukan** default database, bukan trait `HasUuids` |
| Tabel pivot | `<a>_<b>` tanpa id sendiri, composite primary key: `jabatan_member`, `spbu_member`, `dombak_payung`, `hari_jam_kerja`, `tag_post`, `kategori_post`, `penjadwalan_nozzle`, `tera_petty_cash`, `tera_return`, `pengiriman_ms2`, `penerimaan_petty_cash`, `stand_meter_dokumen_stok`, `konversi_dokumen_stok`, `dombak_transfer_dokumen_stok`, `detail_rencana_file` |
| Foreign key | Hampir semua `cascadeOnDelete()`. Pengecualian: `jurnal.posted_by`, `periode_akuntansi.closed_by`, `coa.parent_coa_id` → `nullOnDelete()`; `jurnal_detail.coa_id` → `restrictOnDelete()` |
| Uang & volume | `decimal(16,6)`; nominal besar `decimal(18,6)`. Tabel pajak memakai `double(16,6)` (warisan) |
| Boolean status | `is_aktif`, `is_confirmation`, `is_confirmation_admin`, `is_confirmation_top`, `is_accepted`, `is_accepted_admin`, `is_accepted_top`, `is_done`, `is_denied`, `is_read`, `is_default`, `is_primary`, `is_closed`, `is_posted`, `is_kas`, `is_header` |
| Enum | `jenis_kelamin` (pria/wanita), `type` detail gaji (lebih/kurang), `simbol` losses (lebih/kurang), `jenis` petty cash (debit/kredit), `tipe_akun`, `saldo_normal`, `kategori_arus_kas` |
| Timestamps | `timestamps()` pada sebagian besar tabel; **beberapa tabel tidak punya** (mis. `produk`, `dombak`, `payung`, `dispenser`, `nozzle`, `hari`, `konversi`, `segel`, `pengiriman`, `file_petty_cash`, `file_penerimaan`, `file_lampiran_lo`, `image_selfie`, `pj_operasional_setoran`, `bonus`, `gaji_member`, `detail_gaji`, `stok_inventory_dombak`, `status_setoran`, `status_kehadiran`, `tag`, `kategori`, `type_dokumen_stok`, `jenis_losses`, `satuan_ukur_*`, `penerimaan_stok_taking*`) |
| Soft delete | Hanya: `spbu`, `ktp`, `member`, `userlogin`, `divisi`, `level`, `jabatan`, `petty_cash`, `post` |
| Indeks | Nyaris tidak ada indeks eksplisit selain PK/FK/unique. Indeks eksplisit hanya di modul keuangan: `jurnal (spbu_id, tanggal)`, `jurnal_detail (coa_id)` |

**Urutan migrasi memakai tanggal palsu** (`2009_…`, `2010_…`) untuk memaksa
tabel akar dibuat lebih dulu dari `password_reset_tokens` bawaan Laravel.
Migrasi baru cukup pakai tanggal sekarang.

**Jangan mengedit migrasi lama** — database produksi & dev sudah berjalan dari
dump. Tambahkan migrasi baru (`Schema::table(...)`).

---

## 2. Peta Domain

```
                              ┌────────┐
                              │  spbu  │  akar multi-tenant lunak
                              └───┬────┘
      ┌──────────────┬────────────┼──────────────┬───────────────┐
      ▼              ▼            ▼              ▼               ▼
   produk         dombak        divisi        jam_kerja        aset
      │              │            │              │           fasilitas
  detail_produk   payung        level         hari_jam_kerja
   harga          dispenser     jabatan
   stok_inventory nozzle        jabatan_member ── member ── ktp
                  konversi                         │
                                                userlogin
```

`spbu` adalah akar konseptual, tapi **bukan tenant yang ditegakkan**: tidak ada
global scope; sebagian tabel transaksi (mis. `pengiriman`, `ms2`, `dombak_transfer`)
tidak menyimpan `spbu_id` sama sekali dan harus ditelusuri lewat relasi.

---

## 3. Katalog Tabel

Format: `kolom` — keterangan. FK ditandai `→ tabel`.

### 3.1 Organisasi & Identitas

**`spbu`** — SPBU yang dikelola.
`spbu_id` PK · `nama_spbu` · `nomor_spbu(32)` · `tanggal_pendirian` · `luas_lahan` double · `satuan_luas(8)` · `alamat` · timestamps · softDeletes

**`ktp`** — identitas pegawai.
`ktp_id` PK · `nik(16)` · `nama(128)` · `jenis_kelamin` enum(pria,wanita) · `tempat_lahir(128)` · `tanggal_lahir` · `verified` bool · timestamps · softDeletes

**`status_member`** — `status_member_id` PK · `nama_status(32)` · `keterangan`

**`member`** — pegawai.
`member_id` PK · `ktp_id` → ktp · `status_member_id` → status_member · `email` unique · `no_hp(16)` · `no_wa(16)` · `avatar(128)` (nama file di `storage/app/public/profile_img/`) · `bergabung_sejak` · timestamps · softDeletes

**`userlogin`** — kredensial (model auth guard `web`).
`user_login_id` PK · `member_id` → member · `username(32)` · `password(255)` hashed · `is_aktif` · `access_token` · `access` · `last_login` · timestamps · softDeletes

**`spbu_member`** (pivot) — `spbu_id` + `member_id`. Menentukan penempatan; dipakai posting jurnal gaji untuk menebak SPBU.

**`area_member`** — alamat pegawai. `area_member_id` PK · `member_id` → member · `provinsi` · `kabupaten` · `kecamatan` · `desa(128)` · `rw(6)` · `rt(6)` · `no(6)` · `alamat` · `is_primary` · timestamps

**`divisi`** → spbu · **`level`** → divisi · **`jabatan`** → level — hierarki organisasi tiga tingkat, masing-masing `nama_*`, `keterangan`, `is_aktif`, timestamps, softDeletes.
**Nilai `level.nama_level` yang dikenali kode**: `Penanggung Jawab`, `Komisaris`, `Direktur Utama`, `Direktur`, `Operator`, `Keamanan` (dipakai [Navigation](app/Support/Navigation.php) dan cek ad-hoc di controller — string literal, tidak ada enum).

**`jabatan_member`** (pivot) — `jabatan_id` + `member_id`.

**`bonus`** — `bonus_id` PK · `member_id` → member · `persentase` int · `total_bonus` decimal(16,2) · `tanggal` · `deksripsi` *(typo asli di skema)*

**`gaji`** — `gaji_id` PK · `jabatan_id` → jabatan · `tanggal_berlaku` · `total_gaji` · `potongan_pajak` · `keterangan` · `is_aktif` · timestamps
**`gaji_member`** — `gaji_member_id` PK · `gaji_id` → gaji · `member_id` → member · `tanggal` · `total_gaji_bersih` → **memicu jurnal gaji**
**`detail_gaji`** — `detail_gaji_id` PK · `gaji_member_id` → gaji_member · `nama_gaji` (bertipe uuid, dipakai sebagai teks) · `type` enum(lebih,kurang) · `total`

**`activity_log`** — `activity_log_id` PK · `member_id` → member · `nama_aktivitas` · `class(64)` · `function(64)` · `input(64)` · `output(64)` · timestamps

**`notifikasi`** — `notifikasi_id` PK · `member_id` → member · `judul(64)` · `content` · `is_read` · timestamps

### 3.2 Aset & Fasilitas

**`aset`** — `aset_id` PK · `spbu_id` → spbu · `nama_aset` · `jumlah` int · `tanggal_digunakan` · `harga` decimal(16,6) *(nilai perolehan baris)* · `penyusutan_per_hari` decimal(16,6) · `is_digunakan` · `keterangan` · timestamps
→ dipakai `php artisan keuangan:penyusutan`.

**`fasilitas`** — `fasilitas_id` PK · `spbu_id` → spbu · `nama_fasilitas` · `jumlah` · `tanggal_digunakan` · `keterangan` · timestamps

**`file_aset`** / **`file_fasilitas`** — lampiran: `<x>_id` PK · FK induk · `nama_file` (nama tersimpan) · `nama_dokumen` (nama asli) · `tanggal_upload` · timestamps

### 3.3 Infrastruktur Fisik SPBU

**`produk`** — `produk_id` PK · `spbu_id` → spbu · `nama_produk(64)` · `singkatan(4)` · `is_aktif`
**`detail_produk`** — `detail_produk_id` PK · `produk_id` → produk · `nama_jenis` · `oktan` int

**`harga`** — harga berlaku per produk per shift.
`harga_id` PK · `produk_id` → produk · `jam_kerja_id` → jam_kerja · `tanggal` · `harga_jual` · `harga_beli` · `is_default` · timestamps
→ `harga_beli` pada `harga_id` yang direferensikan `detail_penjualan` menjadi **basis HPP perpetual**; `is_default` dipakai penilaian losses.

**`dombak`** (tangki) — `dombak_id` PK · `spbu_id` → spbu · `nama_dombak(16)` · `keterangan` · `is_aktif`
**`payung`** — `payung_id` PK · `spbu_id` → spbu · `nama_payung(16)` · `keterangan` · `is_aktif`
**`dombak_payung`** (pivot) — dombak ↔ payung
**`dispenser`** — `dispenser_id` PK · `payung_id` → payung · `nama_dispenser(16)` · `keterangan` · `is_aktif`
**`nozzle`** — `nozzle_id` PK · `dispenser_id` → dispenser · `produk_id` → produk · `nama_nozzle(16)` · `keterangan` · `is_aktif`

**`satuan_ukur_volume`** / **`satuan_ukur_tinggi`** — `nama_satuan(64)` · `singkatan(8)`
**`konversi`** — tabel kalibrasi tangki (diunggah Excel).
`konversi_id` PK · `satuan_ukur_tinggi_id` · `satuan_ukur_volume_id` · `dombak_id` → dombak · `tinggi` decimal(16,6) · `volume` decimal(16,6)

### 3.4 Jam Kerja, Presensi, Penjadwalan

**`hari`** — `hari_id` PK · `nama_hari(32)` · `hari_kerja` bool · `keterangan`
**`jam_kerja`** (shift) — `jam_kerja_id` PK · `spbu_id` → spbu · `nama_jam_kerja(32)` · `batas_awal_in` · `batas_akhir_in` · `jam_datang_in` · `jam_pulang_out` · `akhir_out` · `jam_istirahat` · `masuk_istirahat` · `batas_telat` (semua `time`) · `urutan` int · timestamps
**`hari_jam_kerja`** (pivot)

**`status_presensi`** · **`mesin_presensi`** (`nama_mesin`, `tipe_koneksi`, `ip_address`, `com_port`) — infrastruktur presensi mesin (belum aktif dipakai UI Inertia).
**`presensi`** — `presensi_id` PK · FK ke status_presensi, mesin_presensi, jam_kerja, member · `tanggal` · `waktu_datang` · `waktu_selesai` · `is_verified` · `is_aktif` · `terlambat` · `keterangan` · timestamps

**`status_kehadiran`** — `status_kehadiran_id` PK · `nama_kehadiran(64)`
⚠️ Migrasi `2024_04_03_123931_create_status_kehadirans_table` **tercatat belum pernah dijalankan di DB dev; tabelnya tidak ada** (lihat [docs/keuangan.md](docs/keuangan.md)). Perlu ditinjau terpisah sebelum menyentuh modul absensi.

**`penjadwalan`** — inti absensi.
`penjadwalan_id` PK · `status_kehadiran_id` → status_kehadiran · `jabatan_id` → jabatan · `member_id` → member · `jam_kerja_id` → jam_kerja · `tanggal` · `deskripsi` · `attended` timestamp · `is_attended` default false · `is_confirm` default false · `start_time` · `finish_time` · `kode_penjadwalan(36)` unique nullable · timestamps
**`penjadwalan_nozzle`** (pivot) — penugasan nozzle per jadwal

**`validate_barcode`** — konfigurasi QR: `is_expired` int default 15 (menit) · `is_active` default false
**`random_string_barcode`** — `random_string_barcode_id` PK · `validate_barcode_id` · `penjadwalan_id` · `barcode(255)` · `start` · `finish` · `is_done` · `is_active` · timestamps

Bukti kehadiran (semua → penjadwalan, cascade):
**`devices`** (`ipv4`, `type_devices`, `model_devices`, `os`, `versi_os`, `browser`, `versi_browser`) ·
**`location`** (`lattitude` decimal(8,6) *(typo asli)*, `longitude` decimal(9,6), `gmt` tinyint, `deskripsi`) ·
**`image_selfie`** (`nama_file(128)`)

### 3.5 Procurement

**`status_purchase`** — tahap pengadaan. Diisi [StatusPurchaseSeeder](database/seeders/StatusPurchaseSeeder.php):
`Rencana` (PR) → `Pembelian` (PO) → `Pembayaran` (Invoice) → `MS2` (stok Pertamina) → `Pengiriman` (Delivery) → `Penerimaan` (Good Receive).

**`rencana_pembelian`** — dokumen pengadaan (bergerak melewati semua status di atas).
`rencana_pembelian_id` PK · `status_purchase_id` → status_purchase · `spbu_id` → spbu · `member_id` → member · `pembelian_id` nullable *(tanpa FK)* · `kode_pembelian(32)` · `tanggal_pembelian` · `grand_total` · `keterangan` · `is_confirmation` · `is_denied` · `keterangan_denied` · timestamps

**`detail_rencana_pembelian`** — `detail_rencana_pembelian_id` PK · `rencana_pembelian_id` → rencana_pembelian · `produk_id` → produk · `jumlah` · `satuan_jumlah(16)` · `rit` decimal(6,2) · `harga` · `total_harga` · `is_confirmation` · `ms2` bool · `delivery` bool · timestamps

**`pajak_pembelian`** — rincian faktur; **memicu jurnal pengakuan persediaan + utang**.
`pajak_pembelian_id` PK · `rencana_pembelian_id` → rencana_pembelian · `pajak_pembelian_lib_id` → pajak_pembelian_lib · `net_price` · `ppn` · `ppbkb` · `pph` · `total_amount` (double 16,6) · `metode_posting_pajak(16)` default `'hpp'` (`hpp` | `terpisah`) · timestamps

**`pajak_pembelian_lib`** — tarif pajak aktif: `ppn` · `ppbkb` · `pph` · `is_aktif` · timestamps

**`pembayaran`** — `pembayaran_id` PK · `rencana_pembelian_id` → rencana_pembelian · `member_id` → member · `tanggal` · `no_cek_bg(64)` · `no_rekening(32)` · `nama_rekening(128)` · `total_setoran` decimal(18,6) · `is_confirmation_admin` · `is_confirmation_top` · timestamps → **dual approval memicu jurnal pelunasan**
**`file_pembayaran`** — lampiran bukti bayar

**`ms2`** — alokasi stok Pertamina. `ms2_id` PK · `member_id` → member · `tanggal` · `kode_sms` text · `is_done` · timestamps
**`detail_rencana_pembelian_ms2`** — `ms2_id` · `detail_rencana_pembelian_id` · `jam_kerja_id` · `is_delivery` · timestamps
**`pengiriman_ms2`** (pivot) — pengiriman ↔ ms2

**`pengiriman`** — `pengiriman_id` PK · `member_id` → member · `tanggal` · `no_do(32)` · `no_pol(16)` · `is_confirmation_admin` · `is_confirmation_top` · `is_done` · `catatan` *(tanpa timestamps)*
**`supir`** — `supir_id` PK · `pengiriman_id` → pengiriman · `nama_supir` · timestamps

**`file_lo`** — dokumen Loading Order; jembatan procurement → stok.
`file_lo_id` PK · `pengiriman_id` → pengiriman · `produk_id` → produk · `jumlah` · `satuan_jumlah(16)` · `no_faktur(16)` · `no_lo(16)` · `nama_dokumen(128)` · `nama_file(128)` · `tanggal_upload` · timestamps
**`file_lampiran_lo`** — lampiran tambahan per LO
**`segel`** — `segel_id` PK · `file_lo_id` → file_lo · `no_segel`
**`detail_rencana_file`** (pivot) — `detail_rencana_pembelian_id` + `file_lo_id`

### 3.6 Persediaan / Stok

**`stok_inventory`** — saldo per produk. `stok_inventory_id` PK · `produk_id` → produk · `stok` decimal(16,6) · timestamps
**`stok_inventory_dombak`** — saldo per dombak. `stok_inventory_dombak_id` PK · `stok_inventory_id` → stok_inventory · `dombak_id` → dombak · `stok`

**`type_dokumen_stok`** — jenis pergerakan. Diisi [TypeDokumenStokSeeder](database/seeders/TypeDokumenStokSeeder.php):

| `type_movement` | Arah |
|---|---|
| Good Receive Penerimaan | + |
| Good Issue Sales | − |
| Good Issue Tera | − |
| Good Return Tera | + |
| Stok Taking Good Receive Penerimaan | perhitungan sebelum penerimaan |
| Stok Taking | perhitungan |
| Loss | selisih |
| Commit Change From User | koreksi manual |
| Good Transfer Send | − (kirim antar dombak) |
| Good Transfer Receive | + (terima antar dombak) |

**`dokumen_stok`** — **tabel jurnal pergerakan stok; semua alur stok melewati sini.**
`dokumen_stok_id` PK · `stok_inventory_id` → stok_inventory · `member_id` → member · `dombak_id` → dombak · `jam_kerja_id` → jam_kerja · `type_dokumen_stok_id` → type_dokumen_stok · `tanggal` · `waktu` timestamp · `stok_awal` · `stok_akhir` · `is_confirmation` · timestamps
→ saldo `stok_inventory` / `stok_inventory_dombak` baru berubah saat `is_confirmation` menjadi true.

**`file_dokumen_stok`** — lampiran dokumen stok
**`konversi_dokumen_stok`** (pivot) — dokumen stok ↔ baris konversi (tinggi terukur)
**`stand_meter_dokumen_stok`** (pivot) — stand meter ↔ dokumen stok
**`dombak_transfer`** — `dombak_transfer_id` PK · `produk_id` · `jam_kerja_id` · `tanggal` · `waktu` · `is_confirmation` · timestamps; **`dombak_transfer_dokumen_stok`** (pivot) menghubungkan sisi kirim & terima

**`jenis_losses`** — `jenis_loss` · `deskripsi`
**`losses`** — `losses_id` PK · `jenis_losses_id` → jenis_losses · `dokumen_stok_id` → dokumen_stok · `tanggal` · `simbol` enum(lebih,kurang) · `jumlah` decimal(16,6) · timestamps → **memicu jurnal susut**

**`penerimaan`** — `penerimaan_id` PK · `file_lo_id` → file_lo · `dokumen_stok_id` → dokumen_stok · `tanggal` · timestamps
**`file_penerimaan`** — lampiran
**`penerimaan_stok_taking`** — kaitan penerimaan ↔ dokumen stok taking sebelum bongkar
**`penerimaan_stok_taking_penyesuaian`** — kaitan penerimaan ↔ dokumen stok penyesuaian
**`penerimaan_petty_cash`** (pivot) — biaya bongkar dari kas kecil

**`stand_meter`** (totalisator) — `stand_meter_id` PK · `nozzle_id` → nozzle · `jam_kerja_id` → jam_kerja · `dombak_id` → dombak · `tanggal` · `stand_meter_awal` · `stand_meter_akhir` nullable · `urutan` tinyint · `is_confirmation` · timestamps
**`file_stand_meter`** — lampiran foto totalisator

### 3.7 Tera

**`tera`** — `tera_id` PK · `spbu_id` → spbu · `tanggal` · `grand_total` · `keterangan` · timestamps
**`detail_tera`** — `detail_tera_id` PK · `dokumen_stok_id` → dokumen_stok · `tera_id` → tera · `quantity` · `harga` · `keterangan` · timestamps
**`tera_petty_cash`** (pivot) · **`tera_return`** (pivot self-referensial `tera_id` + `tera_return_id`)

### 3.8 Penjualan & Setoran

**`status_setoran`** — siklus setoran. Diisi [StatusSetoranSeeder](database/seeders/StatusSetoranSeeder.php):
`Open Penjualan` → `Penitipan Setoran` → `Closing Penjualan` → `Setoran Ke Bank` → `Selesai`.

**`penjualan`** — satu shift penjualan.
`penjualan_id` PK · `status_setoran_id` → status_setoran · `spbu_id` → spbu · `jam_kerja_id` → jam_kerja · `tanggal` · `total_penjualan` decimal(16,6) · `total_uang_kotak` decimal(16,6) · `is_accepted_admin` · `is_accepted_top` · timestamps
→ **dual approval memicu dua jurnal**: `penjualan` (kas) dan `penjualan_hpp`.
Selisih `total_penjualan` vs `total_uang_kotak` diposting ke Beban Lain-lain (kurang setor) atau Pendapatan Lain-lain (lebih setor).

**`detail_penjualan`** — `detail_penjualan_id` PK · `penjualan_id` → penjualan · `stand_meter_id` → stand_meter · `harga_id` → harga · `liter` · `total_detail_jual` · `is_confirmed` default false · timestamps
→ HPP = Σ (`liter` × `harga.harga_beli` pada `harga_id` baris ini).

**`pj_operasional_setoran`** — penanggung jawab shift: `penjualan_id` · `jabatan_id` · `member_id`

**`penarikan`** — setor kas ke bank.
`penarikan_id` PK · `penjualan_id` → penjualan · `member_id` → member · `tanggal` · `nama_bank(16)` · `no_referensi(16)` · `metode_transfer(64)` · `total_uang` decimal(18,6) · `is_accepted` · timestamps → **memicu jurnal Bank/Kas**
**`file_penarikan`** — bukti setor (`keterangan` tambahan)

### 3.9 Kas Kecil

**`petty_cash`** — `petty_cash_id` PK · `spbu_id` → spbu · `no_kode(4)` · `no_bukti(8)` · `keterangan` · `jumlah` decimal(16,6) · `jenis` enum(**debit** = pengisian, **kredit** = pengeluaran) · `tanggal` · timestamps · softDeletes → **memicu jurnal petty cash**
**`file_petty_cash`** — lampiran

⚠️ Pemetaan `no_kode` → akun beban spesifik **belum diimplementasikan**; semua
pengeluaran masuk `6-1400` Beban Operasional Lainnya.

### 3.10 Artikel (situs publik)

**`post`** — `post_id` PK · `member_id` → member · `title` · `content` · `active` · timestamps · softDeletes
**`file_post`** · **`tag`** · **`kategori`** (`nama_kategori`, `detail`) · **`tag_post`** (pivot) · **`kategori_post`** (pivot)

### 3.11 Keuangan (mini General Ledger)

Ditambahkan migrasi `2026_07_10_*`. Semua PK/FK bertipe `char(36)`.

**`coa`** — Chart of Accounts, hierarkis.
`coa_id` PK · `parent_coa_id` nullable → coa (nullOnDelete) · `kode_akun(16)` **unique** · `nama_akun(128)` · `tipe_akun` enum(`aset`,`kewajiban`,`modal`,`pendapatan`,`hpp`,`beban`,`pendapatan_lain`,`beban_lain`) · `saldo_normal` enum(debit,kredit) · `kategori_arus_kas` enum(operasi,investasi,pendanaan) nullable · `is_kas` · `is_header` · `is_aktif` · `keterangan` · timestamps

**`periode_akuntansi`** — tutup buku per SPBU per bulan.
`periode_akuntansi_id` PK · `spbu_id` → spbu · `tahun` smallint · `bulan` smallint · `is_closed` · `closed_at` · `closed_by` → member (nullOnDelete) · timestamps · **unique(spbu_id, tahun, bulan)**

**`jurnal`** — kepala jurnal.
`jurnal_id` PK · `spbu_id` → spbu · `nomor_jurnal(32)` **unique** (format `JRN-YYYYMM-00001`) · `tanggal` · `keterangan` · `tipe_sumber(32)` · `sumber_id(36)` nullable · `is_posted` default true · `posted_by` → member (nullOnDelete) · timestamps
**`unique(tipe_sumber, sumber_id)`** ← kunci anti double-posting · **index(spbu_id, tanggal)**

Nilai `tipe_sumber` yang dipakai kode: `penjualan`, `penjualan_hpp`, `penarikan`,
`pembelian`, `pembayaran`, `petty_cash`, `gaji`, `losses`, `penyusutan`,
`stok_taking`, `saldo_awal`, `closing`, `manual`.

**`jurnal_detail`** — baris jurnal.
`jurnal_detail_id` PK · `jurnal_id` → jurnal (cascade) · `coa_id` → coa (**restrict**) · `urutan` smallint · `debit` decimal(18,6) default 0 · `kredit` decimal(18,6) default 0 · `keterangan` · timestamps · index(coa_id)

Invarian yang ditegakkan di aplikasi ([JurnalPosting](app/Support/JurnalPosting.php)),
**bukan** oleh constraint database:
minimal 2 baris · Σdebit = Σkredit (toleransi 1e-4) · satu baris hanya debit
**atau** kredit · tidak ada nilai negatif · akun harus ada, aktif, bukan header ·
periode belum ditutup.

### 3.12 Bawaan Laravel

`password_reset_tokens`, `password_resets` (duplikat lama), `failed_jobs`,
`personal_access_tokens`, `migrations`.

**`personal_access_tokens`** kini dipakai API mobile (lihat
[MOBILE_API.md](MOBILE_API.md)) dan disesuaikan dua kali:
- migrasi `2026_08_13_000001` mengubah `tokenable_id` dari `bigint` →
  `varchar(36)` agar muat UUID `userlogin.user_login_id`;
- migrasi `2026_08_13_000002` memulihkan `DEFAULT nextval(...)` pada kolom `id`.

⚠️ Sequence `id` hilang saat restore dump pada **semua** tabel ber-PK bigint.
`migrations` diperbaiki oleh `2026_07_10_000000_repair_migrations_table`;
`personal_access_tokens` dan `failed_jobs` oleh `2026_08_13_000002`. Bila nanti
ada tabel bigint baru dari paket pihak ketiga, gejalanya sama:
*"null value in column id violates not-null constraint"*.

---

## 4. Bagan Akun Standar (CoaSeeder)

Kode akun ini **dirujuk konstanta** [`App\Support\Akun`](app/Support/Akun.php)
dan **tidak boleh diubah dari menu master COA**. Seeder idempotent
(`firstOrCreate` per `kode_akun`).

| Kode | Nama | Tipe | Saldo normal | Arus kas | Kas? |
|---|---|---|---|---|---|
| 1-0000 | ASET | aset | debit | — | header |
| 1-1000 | Kas Operator | aset | debit | operasi | ✔ |
| 1-1100 | Kas Kecil (Petty Cash) | aset | debit | operasi | ✔ |
| 1-1200 | Bank | aset | debit | operasi | ✔ |
| 1-1300 | Piutang Usaha | aset | debit | operasi | |
| 1-1400 | PPN Masukan | aset | debit | operasi | |
| 1-1500 | Persediaan BBM | aset | debit | operasi | |
| 1-1600 | Persediaan Lainnya | aset | debit | operasi | |
| 1-2000 | Aset Tetap | aset | debit | investasi | |
| 1-2100 | Akumulasi Penyusutan | aset | **kredit** | investasi | |
| 2-0000 | KEWAJIBAN | kewajiban | kredit | — | header |
| 2-1000 | Utang Usaha | kewajiban | kredit | operasi | |
| 2-1100 | Utang PPh 21 | kewajiban | kredit | operasi | |
| 2-1200 | Utang Pajak Lainnya | kewajiban | kredit | operasi | |
| 3-0000 | MODAL | modal | kredit | — | header |
| 3-1000 | Modal Disetor | modal | kredit | pendanaan | |
| 3-1100 | Prive | modal | **debit** | pendanaan | |
| 3-2000 | Laba Ditahan | modal | kredit | pendanaan | |
| 3-2100 | Ikhtisar Laba Rugi | modal | kredit | — | |
| 4-0000 | PENDAPATAN | pendapatan | kredit | — | header |
| 4-1000 | Penjualan BBM | pendapatan | kredit | operasi | |
| 4-2000 | Penjualan Non-BBM | pendapatan | kredit | operasi | |
| 5-0000 | HARGA POKOK PENJUALAN | hpp | debit | — | header |
| 5-1000 | Harga Pokok Penjualan BBM | hpp | debit | operasi | |
| 5-2000 | Harga Pokok Penjualan Non-BBM | hpp | debit | operasi | |
| 5-3000 | Beban Susut BBM | hpp | debit | operasi | |
| 6-0000 | BEBAN OPERASIONAL | beban | debit | — | header |
| 6-1000 | Beban Gaji | beban | debit | operasi | |
| 6-1100 | Beban Listrik & Air | beban | debit | operasi | |
| 6-1200 | Beban Pemeliharaan | beban | debit | operasi | |
| 6-1300 | Beban Penyusutan | beban | debit | — (non-kas) | |
| 6-1400 | Beban Operasional Lainnya | beban | debit | operasi | |
| 6-2000 | Beban PPBKB | beban | debit | operasi | |
| 6-3000 | Beban PPh Final | beban | debit | operasi | |
| 7-0000 | PENDAPATAN LAIN-LAIN | pendapatan_lain | kredit | — | header |
| 7-1000 | Pendapatan Lain-lain | pendapatan_lain | kredit | operasi | |
| 8-0000 | BEBAN LAIN-LAIN | beban_lain | debit | — | header |
| 8-1000 | Beban Lain-lain | beban_lain | debit | operasi | |

Akun **nominal** (masuk laba rugi): `pendapatan`, `hpp`, `beban`,
`pendapatan_lain`, `beban_lain` — konstanta `Coa::TIPE_NOMINAL`.
Sisanya akun **riil** (neraca).

---

## 5. Peta Transaksi → Jurnal

| Peristiwa (titik konfirmasi) | `tipe_sumber` | Debit | Kredit |
|---|---|---|---|
| `penjualan.is_accepted_admin` **dan** `is_accepted_top` = true | `penjualan` | Kas Operator 1-1000 (+ selisih ke 8-1000 / 7-1000) | Penjualan BBM 4-1000 |
| idem, bagian HPP perpetual | `penjualan_hpp` | HPP BBM 5-1000 | Persediaan BBM 1-1500 |
| `penarikan.is_accepted` = true | `penarikan` | Bank 1-1200 | Kas Operator 1-1000 |
| `pajak_pembelian` dibuat/diubah | `pembelian` | `hpp`: Persediaan (net+PPN+PPBKB) · `terpisah`: Persediaan (net) + PPN Masukan + Beban PPBKB · **selalu** + Beban PPh Final | Utang Usaha 2-1000 |
| `pembayaran` dual approval | `pembayaran` | Utang Usaha 2-1000 | Bank 1-1200 |
| `petty_cash.jenis = kredit` | `petty_cash` | Beban Operasional Lainnya 6-1400 | Kas Kecil 1-1100 |
| `petty_cash.jenis = debit` | `petty_cash` | Kas Kecil 1-1100 | Bank 1-1200 |
| `gaji_member` dibuat/diubah | `gaji` | Beban Gaji 6-1000 (bersih + potongan) | Bank 1-1200 (bersih) + Utang PPh 21 2-1100 (potongan) |
| `losses.simbol = kurang` | `losses` | Beban Susut BBM 5-3000 | Persediaan BBM 1-1500 |
| `losses.simbol = lebih` | `losses` | Persediaan BBM 1-1500 | Beban Susut BBM 5-3000 |
| `keuangan:penyusutan` (bulanan) | `penyusutan` | Beban Penyusutan 6-1300 | Akumulasi Penyusutan 1-2100 |
| Input Saldo Awal per SPBU | `saldo_awal` | akun riil sesuai input | selisih diseimbangkan ke Modal Disetor 3-1000 |
| `keuangan:tutup-tahun` | `closing` | akun nominal ditutup | Laba Ditahan 3-2000 |

`sumber_id` selalu diisi PK record sumber; untuk `saldo_awal` diisi `spbu_id`.
Pembatalan (unconfirm/hapus) memanggil `JurnalPosting::batalkan(tipe, id)`,
ditolak jika periode sudah ditutup.

---

## 6. Seeder

[DatabaseSeeder](database/seeders/DatabaseSeeder.php) memanggil berurutan
(semua idempotent — hanya mengisi jika tabel kosong):

`HariSeeder` · `JenisLossSeeder` · `SatuanUkurSeeder` · `StatusPresensiSeeder` ·
`StatusPurchaseSeeder` · `TypeDokumenStokSeeder` · `StatusMemberSeeder` ·
`MemberSeeder` · `StatusSetoranSeeder` · `StatusKehadiranSeeder` ·
`ValidateBarcode`

⚠️ Dua catatan:
- Baris terakhir memanggil `ValidateBarcode::class` — itu **model**
  (`App\Models\ValidateBarcode`), bukan `ValidateBarcodeSeeder`. Seeder
  `ValidateBarcodeSeeder` ada tapi tidak terpanggil dari sini.
- **`CoaSeeder` sengaja TIDAK ada di `DatabaseSeeder`.** Jalankan terpisah:
  `php artisan db:seed --class=CoaSeeder`. Selama belum dijalankan,
  `PostingKeuangan::aktif()` bernilai false dan **seluruh posting jurnal
  dilewati diam-diam**.

---

## 7. Penyimpanan Berkas

Metadata berkas ada di database (`nama_file` = nama tersimpan,
`nama_dokumen` = nama asli upload); isinya di `storage/app/public/<folder>`,
diekspos lewat symlink `public/storage`.

| Folder | Tabel |
|---|---|
| `profile_img/` | `member.avatar` |
| `aset/` | `file_aset` |
| `fasilitas/` | `file_fasilitas` |
| `post/` | `file_post` |
| `pembayaran/` | `file_pembayaran` |
| `lo/`, `do/` | `file_lo` |
| `file_lampiran_lo/` | `file_lampiran_lo` |
| `file_penerimaan/` | `file_penerimaan` |
| `file_dokumen_stok/` | `file_dokumen_stok` |
| `file_stand_meter/` | `file_stand_meter` |
| `file_penarikan/` | `file_penarikan` |
| `file_petty_cash/` | `file_petty_cash` |
| `image_selfie/` | `image_selfie` — **diisi hanya oleh API mobile** |

Konvensi penamaan: `<nama_asli_tanpa_ekstensi>_<timestamp>.<ext>`.
Penghapusan record **tidak selalu** menghapus berkas fisik — cek per controller.

---

## 8. Catatan Lingkungan & Jebakan

1. **`char(36)`, bukan `uuid` native.** DB dev hasil restore dump. Tabel baru
   harus mengikuti agar FK/join valid.
2. **Boolean di PostgreSQL vs kode.** Banyak controller memakai
   `filter_var($x, FILTER_VALIDATE_BOOLEAN)` dan `? 1 : 0` karena nilai boolean
   bisa datang sebagai `'t'`/`'true'`/`1` dari lapisan yang berbeda. Jangan
   bandingkan langsung dengan `== true`.
3. **`Charts::monthly()` memakai `to_char(...)`** — PostgreSQL-only. Pindah ke
   MySQL akan mematahkan dashboard.
4. **Tabel `status_kehadiran` mungkin belum ada** di sebagian environment (§3.4).
5. **Tidak ada FK untuk `rencana_pembelian.pembelian_id`** — kolom bebas.
6. **`detail_gaji.nama_gaji` bertipe `uuid`** tetapi dipakai sebagai label teks.
7. **Typo yang sudah terlanjur ada di skema**: `bonus.deksripsi`,
   `location.lattitude`. Jangan "diperbaiki" tanpa migrasi + penyesuaian kode.
8. **Duplikasi migrasi**: `password_resets` dan `password_reset_tokens`
   dua-duanya dibuat.
9. **Nyaris tanpa indeks** di tabel transaksi besar (`dokumen_stok`,
   `stand_meter`, `detail_penjualan`). Kalau ada keluhan lambat, ini tempat
   pertama yang dilihat — bersama pola N+1 di closure Yajra
   (lihat [ARCHITECTURE.md §9](ARCHITECTURE.md)).
10. **Cascade delete agresif.** Menghapus satu `spbu` merontokkan hampir
    seluruh data turunannya. Sebagian besar tabel transaksi **tidak** punya
    soft delete.
