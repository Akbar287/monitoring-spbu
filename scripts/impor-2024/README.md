# Impor data historis SPBU 2434520 (2024, 2025)

Mengubah Excel di `docs/data/<tahun>/` menjadi data aplikasi (stand meter, pengadaan,
penerimaan, stok, jurnal). Dua langkah: **extractor Python** menulis CSV
ternormalisasi ke `storage/app/impor-<tahun>/`, lalu **command artisan** mengimpor CSV
itu per tahap. Tahun dipilih dengan `IMPOR_TAHUN=<tahun>` (extractor) dan `--tahun=<tahun>`
(artisan); default 2024. Tahun berikutnya melanjutkan buku tahun sebelumnya, jadi impor
berurutan: 2024 lalu 2025.

## 1. Extractor (Python 3 + openpyxl)

```bash
python3 -m venv /tmp/venv-impor && /tmp/venv-impor/bin/pip install openpyxl
cd scripts/impor-2024
/tmp/venv-impor/bin/python standmeter.py   # -> stand_meter.csv (+ stand_meter_catatan.csv)
/tmp/venv-impor/bin/python stok.py         # -> stok_shift.csv  (butuh stand_meter.csv)
/tmp/venv-impor/bin/python pengadaan.py    # -> order.csv, lo.csv, penerimaan.csv, transfer.csv
/tmp/venv-impor/bin/python dm5.py          # -> stok_dombak.csv; Biosolar dipecah DM4/DM5 (+ transfer)
/tmp/venv-impor/bin/python penjualan.py    # -> penjualan_detail.csv, harga.csv
/tmp/venv-impor/bin/python keuangan.py     # -> keuangan_bulanan.csv, bonus.csv
# tahun lain: IMPOR_TAHUN=2025 /tmp/venv-impor/bin/python standmeter.py  (dst., urutan sama)
```

Urutan wajib: extractor belakangan memakai CSV dari extractor sebelumnya. Setiap
extractor menulis file `*_catatan.csv` berisi semua koreksi data yang dilakukannya.

| Data | Sumber utama | Pelengkap |
|---|---|---|
| Stand meter | Laporan SPBU → sheet Totalisator Shift 1/2/B2 (B2 = Shift 3) | — |
| Stok per shift (dipping) | Laporan SPBU → Stock BBM Di Dombak | Margin → Stock (bila sheet per shift tanpa dipping) |
| Penerimaan | Laporan SPBU → Bongkaran BBM Aktual | Stock BBM Di Dombak, Margin → Stock; dipilih per hari-tangki yang paling cocok dengan dipping |
| Order + faktur | Jan–Feb: Informasi Detail Produk; Mar–Des: Laporan → Setoran, Penerimaan BBM | kode TRX/SO/BG dari Informasi Detail Produk |
| LO | Jan–Feb: Informasi Detail Produk; Mar–Des: Laporan → Setoran, Penerimaan BBM | nopol/supir/tanggal terima dari Informasi Detail Produk dan Bongkaran |
| Harga jual | kolom Harga Jual di sheet Totalisator | Margin → Penjualan (harga harian) bila totalisator janggal |
| Harga pokok (HPP) | Margin → Penjualan harian nozle (kolom Tebus, dikurangi porsi PPh) | harga jual − margin tetap |
| Biaya, alokasi, bagi laba | Margin → Laba | — |
| Bonus per karyawan | Margin → Bonus Reward | — |

## 2. Import (artisan)

```bash
php artisan impor:historis-2024 bersihkan-stok-2026   # hapus dokumen_stok 2026 yang rusak
php artisan impor:historis-2024 pajak-lib             # tarif master 11 / 5 / 0,25
php artisan impor:historis-2024 stand-meter
php artisan impor:historis-2024 pengadaan             # rencana s/d LO + jurnal faktur & pembayaran
php artisan impor:historis-2024 saldo-awal [--ganti]  # 31-12-2023: Bank Rp 1 M + persediaan awal
php artisan impor:historis-2024 stok                  # penerimaan, Good Issue, transfer, stok taking + jurnal losses
php artisan impor:historis-2024 penjualan             # harga riwayat, penjualan per shift, setoran bank + jurnal
php artisan impor:historis-2024 keuangan              # jurnal manual biaya/alokasi/prive bulanan
php artisan impor:historis-2024 bonus                 # catatan bonus per karyawan (+ member baru bila namanya belum ada)

# 2025 (sesudah 2024): tanpa bersihkan-stok-2026 dan saldo-awal
php artisan impor:historis-2024 stand-meter --tahun=2025   # lalu pengadaan, stok, penjualan, keuangan, bonus
```

- Tambahkan `--dry-run` untuk mencoba tanpa menyimpan (semua di-rollback).
- Setiap tahap berjalan dalam satu transaksi dan menolak jalan bila datanya sudah ada,
  kecuali `bonus` yang idempoten (baris yang sudah tercatat dilewati).
- Untuk menguji di salinan DB: `DB_DATABASE=nama_db_uji php artisan ...`.
- Tahap `stok` memakan waktu ±2 menit (±14.700 dokumen dan ±6.000 jurnal losses). `stok --ganti`
  membangun ulang data stok 2024 (dokumen, losses + jurnalnya, penerimaan, transfer hasil impor)
  dalam satu transaksi. Dokumen penerimaan/transfer dalam satu shift diurutkan per jam kejadian,
  sehingga riwayat stok per tangki (urut `created_at`) selalu bersambung.
- Backup DB sebelum tahap pertama (`pg_dump -Fc`). Tahap `bersihkan-stok-2026` menghapus
  data dan tidak bisa dibatalkan selain dengan backup. Untuk membatalkan seluruh impor:
  `pg_restore --clean --if-exists --no-owner -d spbu <file.dump>` (pakai `pg_restore` versi
  server PostgreSQL, mis. lewat `docker exec -i <container> pg_restore ... < file.dump`).

## 3. Keputusan pemetaan (dikonfirmasi pemilik data)

- **Nozzle:** kode lama digabung ke nozzle penerusnya, karena meternya bersambung saat ganti mesin 11–12 Sep 2024. Pemetaannya PP1/PLA→N-1A, PP2/PLB→N-1C, PP4/PX1→N-1B, PX2→N-1D, PL/PL1→N-2A, PL2→N-2B, PS1/BS1→N-3A, BS2→N-3C, DEX/DEX1→N-3B, DEX2→N-3D. DLT (Dexlite) dilewati.
- **Dombak 2024:** N-1A/N-1C → DM2-PL (Lama), N-2A/N-2B → DM3-PL (Baru). Ini berbeda dari `dombak_payung` saat ini, yang tidak diubah. Blok "Pertalite 2" → DM3, Biosolar → DM4-BS (DM5 tidak dipakai di 2024).
- **Januari:** tangki DM1 (Pertamax) berisi Pertalite. Sisanya dipindah ke DM2 tanggal 28–29 Jan dan dicatat sebagai `dombak_transfer` produk Pertalite.
- **Pemindahan Pertalite DM2 ↔ DM3** (12, 19, 23, 26 Nov; 1 dan 5 Des) di Excel tercatat sebagai "penjualan" tanpa gerak stand meter di tangki asal dan "penerimaan" tanpa bongkaran di tangki tujuan; Margin menghitungnya sebagai penjualan tambahan (Penjualan!G15 +718, G22 +902,1, Q26 +2.975,5, Q4 +708,8, Q8 +515,3). Di DB menjadi `dombak_transfer`: tangki asal berkurang liter keluar, tangki tujuan bertambah liter diterima, selisihnya losses "Good Transfer". Daftar di `pengadaan.py` (`TRANSFER`).
- **Stand meter:** tanggal tiap blok disimpulkan dari urutan blok (Oktober Shift 3 R15 bertanggal 2024-09-02 = 2 Okt); blok template tanpa tanggal sesudah akhir bulan dilewati; mulai Juli slot DLT berisi meter Pertalite II → N-2A; celah < 1 L antar shift (pergantian presisi bacaan) dibiarkan seperti Excel.
- **Penerimaan:** nominal (dasar losses bongkar) = nilai yang disepakati dua dari tiga sumber (sheet stok, Margin, rencana Bongkaran, hanya angka bulat ratusan); 11 Apr penerimaan Shift 2 ikut Shift 1 (sheet stok mencatat seluruh hari di Shift 1); hari yang shift terakhirnya tanpa dipping (31 Mei, 30 Sep, 7 Feb DM6) memakai stok awal Shift 1 besok sebagai dipping akhir hari.
- **Order carry-over Desember 2023** (dibayar 2023, LO diterima Januari) menjadi rencana `A-20231231-1` dengan harga 0; besarnya median kebutuhan dari baris sisa order sheet Setoran (Pertalite 88 KL, Biosolar 64 KL, Dex 2 KL). Nilainya masuk saldo awal persediaan.
- **Order:** nomor TRX yang diulang Informasi Detail Produk pada order tanggal lain tidak dipakai lagi (pemilik = tanggal yang tertanam di nomor TRX); KL Januari R133 dan R168 dikoreksi ke F × G (didukung nilai transaksi V); PPh yang meleset > 5% dari porsi PPh faktur diselaraskan (Mei N36); LO yang muncul ganda di Detail Produk memakai baris yang tanggalnya paling dekat dengan sheet Setoran; kelebihan LO pada satu order mengurangi kapasitas order berikutnya, sehingga baris order belum terkirim = sisa order sebenarnya.
- **Harga 3 Jan Shift 2 N-1B** 13.500 sesuai totalisator dan Lap Penjualan Harian (Margin 13.550 diketik manual).
- **Status rencana:** MS2, karena aplikasi tidak pernah memajukan status melewati MS2. Satu baris detail = satu LO (liter).
- **Faktur:** nilai faktur Excel apa adanya, metode `hpp`. Pembayaran ke rekening 2434520 a.n. PT. MGS. Pembuat data: superadmin. Berkas bukti tidak diunggah (`file_lo.nama_file = default.png`).
- **Rantai stok:** saldo buku dihitung berurutan (penerimaan → transfer → penjualan → stok taking), lalu disamakan ke dipping setiap shift. Saldo stok berjalan (`stok_inventory*`) tidak diubah. Losses 2024 dinilai dengan biaya per liter periodenya (bukan harga beli default saat ini), dengan akun yang sama dengan `PostingKeuangan::postLosses`.
- **Nilai faktur:** bila nilai bayar di sheet Setoran tidak sesuai KL-nya (mis. 24 KL dibayar senilai 16 KL), nilai dihitung ulang dari KL × harga tebus Excel. KL dibenarkan oleh LO dan dipping.
- **LO pengganti:** bongkaran yang tidak punya nomor LO di Excel diberi nomor `TL-NA0xxxx` (4 digit acak; `NA` = nomor perlu direvisi di aplikasi bila nomor aslinya diketahui).
- **Order pelengkap** (`PELENGKAP-YYYYMM`): pesanan yang sudah dibayar tapi tidak tercatat di sheet Setoran, diturunkan dari sisa order akhir bulan di sheet itu sendiri.
- **Penjualan:** satu penjualan per shift (status Selesai), satu detail per stand meter (liter > 0), harga 2024 sebagai riwayat (`is_default` 0). Setoran penuh ke Bank Mandiri di hari yang sama.
- **Harga beli:** `harga.harga_beli` berarti harga dasar (net) per liter, sama seperti master yang dipakai pengadaan. HPP dan susut = net × faktor kapitalisasi pajak (`PostingKeuangan::faktorKapitalisasi`, 1 + PPN% + PPBKB% untuk metode `hpp`). Harga riwayat 2024 disimpan setara-net (biaya per liter Excel ÷ faktor), sehingga HPP terposting sama dengan biaya Excel.
- **Biaya bulanan:** jurnal manual dari Bank. Operasional → 6-1400, Non operasional & Lain-lain → 8-1000, Sarana & Prasarana dan Alokasi 10% → 6-1200, gaji & Reward → 6-1000, bagian pemilik/partner & pengembalian investasi SDP → Prive 3-1100. Bonus per karyawan dicatat di tabel `bonus` (tanpa jurnal).
- **Member baru dari sheet bonus:** nama yang tidak ada di master (2024: "BUDI SETIAWAN", September) dibuatkan member berstatus Tidak Aktif, jabatan sesuai sheet, jenis kelamin dari kolom L/P, username `MGSJ-nnn` berikutnya dengan login nonaktif dan password acak. NIK, tempat/tanggal lahir, dan email diisi penanda untuk dilengkapi di aplikasi.

## 4. Keputusan tambahan (Biosolar DM5, 2025)

- **Biosolar DM4/DM5:** dipping Biosolar di sheet stok ditulis `=DM4+DM5` (sejak 31 Jan 2024).
  `dm5.py` memecahnya: bongkaran bertanda (BS2) masuk DM5 (tanpa tanda: bongkaran terbesar selama
  kenaikan DM5 belum tertutup); perubahan DM5 ≥ 200 L yang tidak dijelaskan bongkaran menjadi
  `dombak_transfer` DM5 → DM4 (atau sebaliknya); perubahan lebih kecil = selisih ukur (stok taking
  DM5). Pembacaan DM5 pertama 2024 = pemindahan pembuka DM4 → DM5.
- **2025 sumber pengadaan:** order dari tab "Setoran , penerimaan" di file Margin tiap bulan.
  Sheet Setoran di Laporan SPBU 2025 tidak dipakai: September tidak lengkap dan Desember salinan
  Oktober. LO = gabungan Margin dan Informasi Detail Produk per nomor; nomor LO yang tidak ada di
  sumber mana pun memakai pengganti `TL-NA0xxxx`. Informasi Detail Produk Mei 2025 dst. bergeser
  satu kolom (dideteksi dari judul "Material"). Dipping akhir hari yang kosong (Desember 2025)
  diambil dari dipping harian tab Stock di Margin.
- **Sisa order akhir tahun:** LO awal tahun milik order tahun lalu (baris 6 sheet Setoran Januari)
  mengisi baris order tahun lalu yang belum terkirim, mulai dari baris yang cukup menampung satu LO
  (backlog akhir Desember). Sisa kecil FIFO di tengah tahun tidak dibesarkan; tidak ada rencana
  carry-over baru.
- **Satu LO = satu truk:** setiap LO (termasuk pengganti `TL-NA0xxxx`) berukuran 8 KL (Pertamina Dex
  per 1 KL). Bongkaran satu produk dalam satu shift (semua tangki) dijumlah, dibulatkan ke jumlah
  truk, lalu diberi LO utuh sebanyak itu. Shift dengan bongkaran < setengah truk digabung ke shift
  bongkaran berikutnya (atau sebelumnya) dalam 1 hari; sisa ≥ 600 L dibawa ke shift berikutnya
  (truk yang dibongkar lintas shift). Total rencana per LO = ukuran LO; selisih terhadap liter
  terukur menjadi susut bongkar. Bongkaran kecil tanpa pasangan (mis. PX 40 L) bukan penerimaan:
  kenaikan dipping-nya menjadi selisih stok taking.
- **LO tanpa bongkaran (2025: 10 LO, BS 32 / PL 24 / PX 8 / Dex 4 KL):** LO yang tercatat di
  sumber order tetapi tidak pernah menaikkan dipping tangki (Margin dan sheet stok tidak mencatat
  penerimaan) mengikuti stok dombak: baris ordernya tetap "belum terkirim" (sudah dibayar), nomor
  LO dicatat di keterangan rencana "LO tanpa bongkaran di dombak (perlu dicek)".
- **Dipping akhir hari:** bila sheet stok per shift dan "Stok Ahir Dipping" Margin berbeda, dipakai
  nilai yang paling bersambung dengan shift sebelum dan sesudahnya (Margin hanya bila galatnya
  lebih kecil ≥ 50 L), dicatat `dipping_koreksi_margin` / `dipping_beda_margin` di stok_catatan.csv.
- **Utang DSP (2025):** "Pengembalian Hutang DSP" = pelunasan utang (akun baru 2-1300 Utang DSP);
  saldo awal per 31-12-2024 = total pelunasan 2025 (lawan Modal Disetor), sehingga akhir 2025 = 0.
  Catatan: TOTAL PENGELUARAN di sheet Laba 2025 memuat pelunasan ini, jadi laba GL lebih tinggi
  dari "Laba Bersih" Excel sebesar total pelunasan.

## 5. Tahun 2026 (Januari–Oktober)

Sumber 2026 berbeda: tidak ada Laporan SPBU bulanan maupun Informasi Detail Produk.

- **Stand meter:** `LAPORAN HARIAN SPBU 20 Per 2026.xlsx` (sheet per bulan; blok 10 mesin per shift,
  3 blok per hari; tanggal dari urutan blok karena kolom tanggal kadang salah ketik tahun). Sheet NOV
  sesudah 11 Nov dan DES masih template 2025 dan tidak dipakai. Stand meter Jan–Mei yang sudah diinput
  lewat aplikasi identik dengan Excel; dombaknya diseragamkan (N-1A/N-1C = DM2 Lama) dan dikonfirmasi.
- **Harga jual:** per shift dari LAPORAN HARIAN (harga sering berubah di tengah hari); sheet Penjualan
  Margin hanya cadangan karena kadang tertinggal (Feb/Mar Pertamax, 1–10 Jul Pertamax).
- **Biaya per liter (HPP):** Pertalite/Biosolar dari tebus Excel; Pertamax/Dex dari faktur asli tab
  Setoran (tebus Excel tidak ikut naik), hari tanpa faktur = rasio biaya/harga jual faktur terdekat x
  harga jual hari itu (`biaya_harian.csv`).
- **Stok:** harian dari tab Stock Margin (Shift 3); judul kolom dibaca per blok karena mulai Juli ada
  kolom Tera. Dipping Biosolar `=DM4+DM5` dipecah; stok awal gabungan tanpa rumus dikurangi buku DM5.
- **Tera:** liter tera bukan penjualan (keputusan pemilik: Biaya Tera ikut dalam perhitungan
  penjualan). Liter tera hari itu dikurangkan dari stand meter terbesar tangki itu; importer mencatat
  Good Issue Tera + Good Return Tera (header `tera`/`detail_tera`), tanpa jurnal.
- **Pengadaan:** order dari tab Setoran Margin; Agustus dari `Lap Tebusan` (Setoran kurang lengkap);
  Juli (tab Setoran tidak diisi) dari penerimaan tab Stock. Penerimaan harian dari tab Stock; shift
  bongkaran dimajukan bila stok awal tidak cukup untuk penjualan shift sebelumnya. LO tanpa nomor =
  pengganti `TL-NA0xxxx` (satu per truk).
- **Tahap tambahan:** `bersihkan-uji` menghapus jurnal uji yang sumbernya tidak ada (2026:
  JRN-202608-00001/00002). Urutan 2026: bersihkan-uji, stand-meter, pengadaan, stok, penjualan,
  keuangan, bonus.
