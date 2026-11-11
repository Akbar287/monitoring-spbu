"""Extractor biaya, alokasi, dan pembagian laba bulanan 2024 + bonus per karyawan.

Sumber: Margin <Bulan> 2024.xlsx, sheet "Laba" (rincian biaya s/d pembagian laba) dan
sheet "Bonus Reward" (bonus per karyawan).
Keluaran: storage/app/impor-2024/keuangan_bulanan.csv, bonus.csv, keuangan_catatan.csv

Perlakuan (keputusan pemilik data 2026-11-11): semua dibayar dari Bank (1-1200),
dicatat satu jurnal per pos per akhir bulan.
- Biaya Operasional -> 6-1400, Non operasional & Lain-lain -> 8-1000,
  Sarana dan Prasarana -> 6-1200; rincian Januari: gaji -> 6-1000, uang cor tangki
  -> 6-1200, pengeluaran harian pompa & iuran Pertamina/Hiswana -> 6-1400.
- Alokasi Biaya Sarana & Prasarana 10% -> 6-1200; Reward Karyawan -> 6-1000.
- Pengembalian dana investasi SDP, bagian Pemilik dan Partner -> Prive 3-1100.
"""
import calendar
import collections
import datetime as dt
import glob
import os

import openpyxl

from umum import BULAN, DATA, angka, fmt, tulis_csv

POS = [  # (kata kunci di kolom Produk/Rincian, kode akun, jenis)
    ('gaji karyawan', '6-1000', 'beban'),
    ('gaji kantor', '6-1000', 'beban'),
    ('uang cor tangki', '6-1200', 'beban'),
    ('pengeluaran harian pompa', '6-1400', 'beban'),
    ('pembayaran 5 orang pertamina', '6-1400', 'beban'),
    ('biaya operasional', '6-1400', 'beban'),
    ('biaya non operasional', '8-1000', 'beban'),
    ('biaya lain-lain', '8-1000', 'beban'),
    ('biaya sarana dan prasarana', '6-1200', 'beban'),
    ('alokasi biaya sarana', '6-1200', 'beban'),
    ('reward karyawan', '6-1000', 'beban'),
    ('pengembalian dana investasi', '3-1100', 'prive'),
    ('pihak pemilik', '3-1100', 'prive'),
    ('pihak partner', '3-1100', 'prive'),
]

catatan = []


def margin_file(bulan):
    return (glob.glob(os.path.join(DATA, f'Margin {bulan} 2024*.xlsx'))
            or glob.glob(os.path.join(DATA, f'Margin {bulan.upper()} 2024*.xlsx')))[0]


def main():
    baris, bonus = [], []
    for i, b in enumerate(BULAN):
        wb = openpyxl.load_workbook(margin_file(b), data_only=True)
        ws = wb['Laba']
        akhir = dt.date(2024, i + 1, calendar.monthrange(2024, i + 1)[1]).isoformat()
        total_excel = None
        jumlah_beban = 0.0
        for r in range(9, ws.max_row + 1):
            label = ' '.join(str(ws.cell(r, c).value).strip() for c in (2, 3)
                             if ws.cell(r, c).value not in (None, '') and not isinstance(ws.cell(r, c).value, (int, float)))
            nilai = next((ws.cell(r, c).value for c in (8, 4, 9) if isinstance(ws.cell(r, c).value, (int, float))), None)
            if not label or nilai is None:
                continue
            kecil = label.lower()
            if 'total pengeluaran' in kecil:
                total_excel = nilai
                continue
            pos = next((p for p in POS if p[0] in kecil), None)
            if pos is None:
                continue
            if abs(nilai) < 0.5:
                continue
            baris.append({'bulan': akhir[:7], 'tanggal': akhir, 'jenis': pos[2], 'kode_akun': pos[1],
                          'keterangan': label[:120], 'nilai': fmt(round(nilai, 2), 2), 'sumber': f'Margin {b}/Laba!R{r}'})
            if pos[2] == 'beban' and not pos[0].startswith(('alokasi', 'reward')):
                jumlah_beban += nilai
        if total_excel is not None and abs(total_excel - jumlah_beban) > 1:
            catatan.append({'jenis': 'total_biaya_beda', 'tanggal': akhir,
                            'keterangan': f'jumlah pos {jumlah_beban:,.0f} vs TOTAL PENGELUARAN {total_excel:,.0f}'})

        # bonus per karyawan
        sh = [s for s in wb.sheetnames if s.lower().startswith('bonus')]
        if sh:
            wbs = wb[sh[0]]
            for r in range(1, wbs.max_row + 1):
                no, nama, jab = wbs.cell(r, 1).value, wbs.cell(r, 2).value, wbs.cell(r, 4).value
                if not isinstance(no, (int, float)) or not nama:
                    continue
                e, f = angka(wbs.cell(r, 5).value), angka(wbs.cell(r, 6).value)
                nilai = f if f else (e if e and e > 100 else None)
                persen = e if e is not None and e < 1 else None
                if not nilai:
                    continue
                bonus.append({'bulan': akhir[:7], 'tanggal': akhir, 'nama': str(nama).strip(), 'jabatan': str(jab or '').strip(),
                              'jk': str(wbs.cell(r, 3).value or '').strip().upper()[:1],   # kolom L/P
                              'persen': fmt(persen * 100 if persen is not None else 0, 2), 'nilai': fmt(round(nilai, 2), 2),
                              'sumber': f'Margin {b}/{sh[0]}!R{r}'})

    tulis_csv('keuangan_bulanan.csv', ['bulan', 'tanggal', 'jenis', 'kode_akun', 'keterangan', 'nilai', 'sumber'], baris)
    tulis_csv('bonus.csv', ['bulan', 'tanggal', 'nama', 'jabatan', 'jk', 'persen', 'nilai', 'sumber'], bonus)
    tulis_csv('keuangan_catatan.csv', ['jenis', 'tanggal', 'keterangan'], catatan)

    per = collections.defaultdict(float)
    for x in baris:
        per[(x['bulan'], x['jenis'], x['kode_akun'])] += float(x['nilai'])
    print(f'pos keuangan: {len(baris)} | bonus per karyawan: {len(bonus)} | catatan: {catatan}')
    print('bulan    6-1000 gaji/reward  6-1200 pemeliharaan  6-1400 operasional  8-1000 lain   3-1100 prive')
    for bln in sorted({x['bulan'] for x in baris}):
        g = lambda a: per.get((bln, 'beban' if a != '3-1100' else 'prive', a), 0)
        print(f"{bln}  {g('6-1000'):>18,.0f} {g('6-1200'):>19,.0f} {g('6-1400'):>18,.0f} {g('8-1000'):>12,.0f} {g('3-1100'):>13,.0f}")
    tot = collections.defaultdict(float)
    for x in baris:
        tot[x['jenis']] += float(x['nilai'])
    print('TOTAL:', {k: f'{v:,.0f}' for k, v in tot.items()},
          '| bonus per karyawan:', f"{sum(float(x['nilai']) for x in bonus):,.0f}")


if __name__ == '__main__':
    main()
