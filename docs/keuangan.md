# Modul Keuangan — Desain & Aturan Posting

Modul keuangan dibangun di atas **mini general ledger**: semua transaksi uang
dicatat sebagai jurnal berimbang (debit = kredit), dan seluruh laporan
(buku besar, laba rugi, neraca, arus kas, perubahan modal) **diturunkan dari
jurnal**, bukan dihitung ulang dari tabel operasional per laporan.

## Komponen

| Komponen | Lokasi | Peran |
|---|---|---|
| `coa` | tabel + menu Financial → COA | Master bagan akun (Chart of Accounts), hierarkis via `parent_coa_id` |
| `jurnal`, `jurnal_detail` | tabel + menu Financial → Jurnal Umum | Buku jurnal; satu jurnal berisi ≥2 baris debit/kredit yang seimbang |
| `periode_akuntansi` | tabel | Tutup buku per SPBU per bulan (`is_closed` mengunci posting & pembatalan) |
| `App\Support\Akun` | kode | Konstanta kode akun baku yang dirujuk posting otomatis — **kode akun ini tidak boleh diubah dari menu COA** |
| `App\Support\JurnalPosting` | kode | Satu-satunya pintu pencatatan jurnal: validasi seimbang, akun valid & aktif, bukan header, periode terbuka, anti double-posting per (tipe_sumber, sumber_id) |
| `CoaSeeder` | seeder | Bagan akun standar SPBU, idempotent (aman dijalankan ulang) |

## Keputusan akuntansi (sudah disepakati)

1. **Pajak pembelian fleksibel per pembelian** — kolom
   `pajak_pembelian.metode_posting_pajak`:
   - `hpp` (default): net + PPN + PPBKB dikapitalisasi ke **Persediaan BBM**
     (ikut terbawa ke HPP saat BBM terjual).
   - `terpisah`: net ke Persediaan, PPN ke **PPN Masukan** (aset),
     PPBKB ke **Beban PPBKB**.
   - **PPh 22 selalu diposting ke Beban PPh Final**, apa pun metodenya —
     pajak penghasilan bukan komponen biaya perolehan persediaan menurut
     standar akuntansi.
2. **Persediaan perpetual** — HPP dicatat pada saat penjualan diposting
   (liter × `harga_beli` yang berlaku), selisih stock taking / losses diposting
   sebagai penyesuaian persediaan.
3. **Saldo awal** — diinput sebagai jurnal `tipe_sumber = saldo_awal` per SPBU
   pada tanggal cut-over (kas, bank, persediaan, aset tetap, akumulasi
   penyusutan, utang, modal). Neraca hanya valid setelah saldo awal terisi.

## Aturan posting otomatis (peta transaksi → jurnal)

| Peristiwa (titik konfirmasi) | Debit | Kredit |
|---|---|---|
| Penjualan di-accept (`is_accepted_admin`/`top`) — jurnal tipe `penjualan` | Kas Operator `1-1000` (+ selisih setor ke `7-1000`/`8-1000`) | Penjualan BBM `4-1000` |
| — HPP-nya (perpetual), jurnal terpisah tipe `penjualan_hpp` | HPP BBM `5-1000` | Persediaan BBM `1-1500` |
| Penarikan / setor ke bank (`is_accepted`) | Bank `1-1200` | Kas Operator `1-1000` |
| Pembayaran pembelian dikonfirmasi | lihat `JurnalPosting::barisPembelian()` (fleksibel pajak) | Bank `1-1200` |
| Petty cash `kredit` (pengeluaran) | Beban sesuai mapping `no_kode` → COA (default Beban Operasional Lainnya `6-1400`) | Kas Kecil `1-1100` |
| Petty cash `debit` (pengisian) | Kas Kecil `1-1100` | Bank `1-1200` |
| Gaji dibayar | Beban Gaji `6-1000` | Kas/Bank + Utang PPh 21 `2-1100` (potongan) |
| Losses `kurang` | Beban Susut BBM `5-3000` | Persediaan BBM `1-1500` |
| Losses `lebih` | Persediaan BBM `1-1500` | Beban Susut BBM `5-3000` |
| Penyusutan (job bulanan dari `aset.penyusutan_per_hari`) | Beban Penyusutan `6-1300` | Akumulasi Penyusutan `1-2100` |

Pembatalan/unconfirm transaksi sumber → `JurnalPosting::batalkan(tipe, id)`
menghapus jurnalnya (ditolak jika periode sudah ditutup).

## Derivasi laporan (semua dari `jurnal_detail`)

- **Buku Besar**: per akun = saldo awal periode + mutasi debit/kredit.
- **Neraca Saldo**: total debit/kredit per akun (alat validasi internal).
- **Laba Rugi**: akun tipe `pendapatan`, `hpp`, `beban`, `pendapatan_lain`,
  `beban_lain` dalam rentang tanggal.
- **Neraca**: saldo akun riil (`aset`, `kewajiban`, `modal`) s.d. tanggal +
  laba berjalan.
- **Arus Kas** (metode langsung): mutasi akun ber-flag `is_kas`,
  diklasifikasikan menurut `kategori_arus_kas` akun lawannya.
- **Perubahan Modal**: modal awal + setoran − prive + laba periode.
- **Margin**: tetap dihitung dari data operasional (liter × selisih harga) —
  berfungsi sebagai pembanding silang terhadap laba kotor versi buku besar.

## Status pengerjaan

- [x] Tabel coa/jurnal/jurnal_detail/periode_akuntansi + model
- [x] CoaSeeder + CRUD master COA + tampilan Jurnal Umum
- [x] `JurnalPosting` (validasi + `barisPembelian` pajak fleksibel)
- [x] Kolom `pajak_pembelian.metode_posting_pajak`
- [x] Hook posting via model event: Penjualan, Penarikan, PajakPembelian (faktur),
      Pembayaran, PettyCash (`App\Support\PostingKeuangan`) — semua jalur tulis
      (controller/import) tertangkap; GL baru aktif setelah CoaSeeder dijalankan
- [x] Pilihan metode posting pajak di form Ubah Pajak Pembayaran (PajakEdit)
- [x] Hook posting losses (nilai = liter x harga beli default; dilewati jika
      harga default 0 agar alur stok tidak terblokir) dan gaji (beban = bersih
      + potongan; potongan ke Utang PPh 21; SPBU dari penempatan member)
- [x] Penyusutan aset: `php artisan keuangan:penyusutan [--bulan=Y-m] [--dry-run]`
      — per SPBU per bulan, tanggal posting akhir bulan, kumulatif dibatasi
      nilai perolehan. Asumsi: `aset.harga` = nilai perolehan baris,
      `penyusutan_per_hari` untuk baris tsb. Jalankan tiap akhir bulan
      (bisa didaftarkan ke scheduler)
- [x] Command `php artisan keuangan:backfill` (opsi --dari --sampai --dry-run;
      idempotent — record yang sudah punya jurnal dilewati, gagal per-record
      dilaporkan tanpa menghentikan sisanya)
- [x] Input saldo awal per SPBU (menu Financial → Saldo Awal): jurnal pembuka
      tipe_sumber=saldo_awal, sumber_id=spbu_id (satu per SPBU, simpan ulang
      mengganti); selisih debit-kredit otomatis diseimbangkan ke Modal Disetor
- [x] Laporan Buku Besar (per akun: saldo awal + mutasi + saldo berjalan,
      filter SPBU/akun/rentang tanggal) dan Neraca Saldo (per akun: awal/mutasi/
      akhir D-K, cek keseimbangan otomatis dengan peringatan bila tidak balance)
      — keduanya memakai halaman generik `Financial/Laporan.jsx` yang reusable
      untuk laporan berikutnya
- [x] Laporan Laba Rugi (pendapatan − HPP = laba kotor − beban = laba
      operasional ± lain-lain = laba bersih; per SPBU atau konsolidasi)
- [x] Laporan Neraca (saldo akun riil s.d. tanggal + Laba (Rugi) Berjalan dari
      akun nominal yang belum ditutup; cek otomatis aset = kewajiban + modal)
- [x] Laporan Arus Kas metode langsung (klasifikasi via kategori_arus_kas akun
      lawan; transfer antar akun kas otomatis tereksklusi; validasi kas akhir
      vs saldo buku besar). Catatan: jurnal penjualan dipecah dua —
      tipe `penjualan` (kas) dan `penjualan_hpp` (HPP/persediaan, non-kas) —
      agar arus kas bersih dari baris non-kas
- [x] Laporan Perubahan Modal (modal awal + mutasi akun modal + laba periode =
      modal akhir; validasi vs bagian Modal di Neraca)
- [x] Tutup buku bulanan: menu Financial → Tutup Buku (tutup/buka periode per
      SPBU; periode tertutup menolak posting & pembatalan jurnal)
- [x] Closing tahunan: `php artisan keuangan:tutup-tahun {tahun} [--dry-run]`
      — memindahkan saldo akun nominal per 31 Des ke Laba Ditahan per SPBU,
      idempotent. Urutan akhir tahun: selesaikan transaksi → tutup-tahun →
      baru tutup periode Desember di menu Tutup Buku
- [x] MarginController: `dd()` debug di report Totalisator sudah dihapus

## Catatan lingkungan

Database dev adalah hasil restore dump: semua kolom id bertipe `character(36)`
(bukan `uuid` native), sehingga tabel keuangan baru juga memakai `char(36)`
agar FK dan join valid. Tabel `migrations` sempat kehilangan sequence `id`;
diperbaiki oleh migrasi `2026_07_10_000000_repair_migrations_table`.
Migrasi `create_status_kehadirans_table` tercatat belum pernah dijalankan di
DB ini (tabelnya tidak ada) — di luar scope modul keuangan, perlu ditinjau
terpisah.
