"""Extractor stok per shift per dombak 2024.

Sumber : Laporan SPBU <Bulan> 2024.xlsx, sheet "Stock BBM Di Dombak"
         (blok per tangki, 3 baris per hari: Shift 1/2/3; kolom Stok Awal,
         Penerimaan BBM Aktual, Penjualan, Teoritis, Dipping, Losses).
Keluaran: storage/app/impor-2024/stok_shift.csv (+ stok_catatan.csv)

Blok DEXLITE (Jan-Jun, stok diam 265 L, tidak ada dombak di DB) dilewati.
Blok "PERTALITE 2" (mulai Juli, bekas tangki Dexlite) -> DM3-PL (Baru).
Validasi silang: penjualan blok vs liter stand_meter.csv per dombak/shift.
"""
import calendar
import collections
import csv
import datetime as dt
import os

import openpyxl

from umum import BULAN, DATA, DOMBAK_BLOK, OUT, angka, fmt, path_laporan, tulis_csv

SHIFT_NO = {1: 'Shift 1', 2: 'Shift 2', 3: 'Shift 3'}


def jual_stand_meter():
    """liter per (tanggal, jam_kerja, dombak) dari stand_meter.csv."""
    hasil = collections.defaultdict(float)
    with open(os.path.join(OUT, 'stand_meter.csv'), encoding='utf-8') as f:
        for r in csv.DictReader(f):
            hasil[(r['tanggal'], r['jam_kerja'], r['dombak'])] += float(r['liter'])
    return hasil


def ganti_dari_margin(baris, catatan):
    """Ganti baris tangki-bulan tanpa dipping dengan data harian tab Stock Margin.

    Satu baris per hari di shift terakhir yang dipakai bulan itu; stok awal,
    penerimaan dan dipping dari Margin (penjualan tetap dari stand meter).
    """
    import glob
    ada_dip = collections.defaultdict(bool)
    for b in baris:
        if b['dipping']:
            ada_dip[(b['tanggal'][:7], b['dombak'])] = True
    kosong = {(b['tanggal'][:7], b['dombak']) for b in baris} - {k for k, v in ada_dip.items() if v}
    if not kosong:
        return baris
    sm = jual_stand_meter()
    shift3 = {k[0][:7] for k, v in sm.items() if k[1] == 'Shift 3' and v}
    tambah = []
    for bln, dombak in sorted(kosong):
        i = int(bln[5:]) - 1
        nama_bulan = BULAN[i]
        p = (glob.glob(os.path.join(DATA, f'Margin {nama_bulan} 2024*.xlsx'))
             or glob.glob(os.path.join(DATA, f'Margin {nama_bulan.upper()} 2024*.xlsx')))
        ws = openpyxl.load_workbook(p[0], data_only=True)['Stock']
        blok = {v: k for k, v in DOMBAK_BLOK.items()}
        cols = [c for c in range(1, ws.max_column + 1)
                if isinstance(ws.cell(2, c).value, str) and DOMBAK_BLOK.get(ws.cell(2, c).value.strip()) == dombak]
        if not cols:
            continue
        c = cols[0]
        s = 'Shift 3' if bln in shift3 else 'Shift 2'
        n_hari = calendar.monthrange(2024, i + 1)[1]
        for r in range(4, 4 + n_hari):
            hari = ws.cell(r, c).value
            if not isinstance(hari, (int, float)):
                continue
            t = dt.date(2024, i + 1, int(hari)).isoformat()
            g = lambda off: angka(ws.cell(r, c + off).value)
            tambah.append({
                'tanggal': t, 'jam_kerja': s, 'urutan': int(s[-1]), 'dombak': dombak,
                'stok_awal': fmt(g(1)), 'penerimaan': fmt(g(2) or 0), 'penjualan_excel': fmt(g(3) or 0),
                'penjualan_stand_meter': fmt(sum(v for k, v in sm.items() if k[0] == t and k[2] == dombak)),
                'dipping': fmt(g(5)), 'sumber': f'Margin {nama_bulan}/Stock!R{r} ({ws.cell(2, c).value.strip()})',
            })
        catatan.append({'jenis': 'pakai_margin', 'tanggal': bln, 'jam_kerja': s, 'dombak': dombak,
                        'sumber': p[0].split('/')[-1], 'keterangan': 'sheet stok per shift tanpa dipping; pakai Margin harian'})
        print(f'{dombak} {bln}: sheet stok per shift tanpa dipping -> tab Stock Margin (harian, {s})')
    return [b for b in baris if (b['tanggal'][:7], b['dombak']) not in kosong] + tambah


def main():
    sm = jual_stand_meter()
    baris, catatan = [], []
    for i, bulan in enumerate(BULAN):
        ws = openpyxl.load_workbook(path_laporan(bulan), data_only=True)['Stock BBM Di Dombak']
        n_hari = calendar.monthrange(2024, i + 1)[1]
        blok = [(c, ws.cell(2, c).value.strip()) for c in range(1, ws.max_column + 1)
                if isinstance(ws.cell(2, c).value, str) and ws.cell(2, c).value.strip()]
        for c, nama in blok:
            dombak = DOMBAK_BLOK.get(nama)
            if dombak is None:
                continue
            hari = None
            # tabel selalu 93 baris (31 hari x 3 shift) mulai baris 4; di bawahnya
            # ada baris ringkasan/coretan yang kolom shift-nya kebetulan berisi angka
            for r in range(4, 4 + 31 * 3):
                h = ws.cell(r, c).value
                if isinstance(h, (int, float)):
                    hari = int(h)
                shift = ws.cell(r, c + 1).value
                if not isinstance(shift, (int, float)) or hari is None or hari > n_hari:
                    continue
                t = dt.date(2024, i + 1, hari).isoformat()
                s = SHIFT_NO[int(shift)]
                v = {k: angka(ws.cell(r, c + off).value) for k, off in
                     (('awal', 3), ('terima', 4), ('jual', 5), ('teoritis', 6), ('dipping', 7))}
                jual_sm = sm.get((t, s, dombak), 0.0)
                b = {
                    'tanggal': t, 'jam_kerja': s, 'urutan': int(shift), 'dombak': dombak,
                    'stok_awal': fmt(v['awal']), 'penerimaan': fmt(v['terima'] or 0),
                    'penjualan_excel': fmt(v['jual'] or 0), 'penjualan_stand_meter': fmt(jual_sm),
                    'dipping': fmt(v['dipping']), 'sumber': f'{bulan}/Stock BBM Di Dombak!R{r} ({nama})',
                }
                baris.append(b)
                if v['dipping'] is None:
                    catatan.append({**b, 'jenis': 'dipping_kosong', 'keterangan': ''})
                if abs((v['jual'] or 0) - jual_sm) > 0.5:
                    catatan.append({**b, 'jenis': 'jual_beda',
                                    'keterangan': f"excel {v['jual'] or 0:.2f} vs stand meter {jual_sm:.2f}"})

    # tangki-bulan yang di sheet stok per shift tidak punya dipping sama sekali
    # (mis. Pertamina Dex Januari) -> pakai tab Stock file Margin (harian)
    baris = ganti_dari_margin(baris, catatan)

    # shift terakhir suatu hari tanpa dipping (31 Mei semua shift, 30 Sep Shift 3): dipping akhir
    # hari = stok awal Shift 1 besok (sama dengan "Stok Ahir Dipping" harian di Margin), supaya
    # selisihnya tercatat di hari/bulan itu, bukan di hari berikutnya
    per_hari = collections.defaultdict(list)
    for b in baris:
        per_hari[(b['tanggal'], b['dombak'])].append(b)
    for (t, d), xs in per_hari.items():
        akhir = max(xs, key=lambda b: b['urutan'])
        if akhir['dipping']:
            continue
        besok = (dt.date.fromisoformat(t) + dt.timedelta(days=1)).isoformat()
        nxt = sorted(per_hari.get((besok, d), []), key=lambda b: b['urutan'])
        if nxt and nxt[0]['stok_awal']:
            akhir['dipping'] = nxt[0]['stok_awal']
            catatan.append({**akhir, 'jenis': 'dipping_dari_awal_besok',
                            'keterangan': f"dipping akhir hari = stok awal {besok} {nxt[0]['jam_kerja']} {nxt[0]['stok_awal']}"})

    # kontinuitas: stok awal shift = dipping shift sebelumnya (per dombak)
    per_dombak = collections.defaultdict(list)
    for b in baris:
        per_dombak[b['dombak']].append(b)
    for d, xs in per_dombak.items():
        xs.sort(key=lambda b: (b['tanggal'], b['urutan']))
        for prev, cur in zip(xs, xs[1:]):
            if prev['dipping'] and cur['stok_awal'] and abs(float(prev['dipping']) - float(cur['stok_awal'])) > 0.5:
                catatan.append({**cur, 'jenis': 'awal_beda_dipping',
                                'keterangan': f"awal {cur['stok_awal']} vs dipping sebelumnya {prev['dipping']}"})

    baris.sort(key=lambda b: (b['tanggal'], b['urutan'], b['dombak']))
    kolom = ['tanggal', 'jam_kerja', 'urutan', 'dombak', 'stok_awal', 'penerimaan', 'penjualan_excel',
             'penjualan_stand_meter', 'dipping', 'sumber']
    tulis_csv('stok_shift.csv', kolom, baris)
    tulis_csv('stok_catatan.csv', ['jenis', 'tanggal', 'jam_kerja', 'dombak', 'sumber', 'keterangan'], catatan)

    print(f'stok_shift: {len(baris)} baris')
    print('catatan:', dict(collections.Counter(c['jenis'] for c in catatan)))
    per = collections.defaultdict(lambda: [0.0, 0.0, 0.0])
    for b in baris:
        k = (b['tanggal'][:7], b['dombak'])
        per[k][0] += float(b['penerimaan'])
        per[k][1] += float(b['penjualan_excel'])
        per[k][2] += float(b['penjualan_stand_meter'])
    print('bulan    dombak          terima      jual_excel  jual_standmeter')
    for k in sorted(per):
        t, j, s = per[k]
        print(f'{k[0]}  {k[1]:14} {t:>11,.0f} {j:>13,.0f} {s:>13,.0f}' + ('   <-- beda' if abs(j - s) > 1 else ''))


if __name__ == '__main__':
    main()
