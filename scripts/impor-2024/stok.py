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

from umum import (BULAN, DATA, DOMBAK_BLOK, OUT, TAHUN, ada_laporan_bulanan, angka, fmt, kolom_stock, path_laporan,
                  path_margin, pecah_dm45, tulis_csv)

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
        ws = openpyxl.load_workbook(path_margin(nama_bulan), data_only=True)['Stock']
        blok = {v: k for k, v in DOMBAK_BLOK.items()}
        cols = [c for c in range(1, ws.max_column + 1)
                if isinstance(ws.cell(2, c).value, str) and DOMBAK_BLOK.get(ws.cell(2, c).value.strip()) == dombak]
        if not cols:
            continue
        c = cols[0]
        k = kolom_stock(ws, c)
        s = 'Shift 3' if bln in shift3 else 'Shift 2'
        n_hari = calendar.monthrange(TAHUN, i + 1)[1]
        for r in range(4, 4 + n_hari):
            hari = ws.cell(r, c).value
            if not isinstance(hari, (int, float)):
                continue
            t = dt.date(TAHUN, i + 1, int(hari)).isoformat()
            g = lambda off: angka(ws.cell(r, c + off).value)
            tambah.append({
                'tanggal': t, 'jam_kerja': s, 'urutan': int(s[-1]), 'dombak': dombak,
                'stok_awal': fmt(g(k['awal'])), 'penerimaan': fmt(g(k['terima']) or 0), 'penjualan_excel': fmt(g(k['jual']) or 0),
                'penjualan_stand_meter': fmt(sum(v for kk, v in sm.items() if kk[0] == t and kk[2] == dombak)),
                'dipping': fmt(g(k['dipping'])), 'sumber': f'Margin {nama_bulan}/Stock!R{r} ({ws.cell(2, c).value.strip()})',
            })
        catatan.append({'jenis': 'pakai_margin', 'tanggal': bln, 'jam_kerja': s, 'dombak': dombak,
                        'sumber': os.path.basename(path_margin(nama_bulan)), 'keterangan': 'sheet stok per shift tanpa dipping; pakai Margin harian'})
        print(f'{dombak} {bln}: sheet stok per shift tanpa dipping -> tab Stock Margin (harian, {s})')
    return [b for b in baris if (b['tanggal'][:7], b['dombak']) not in kosong] + tambah


def dipping_harian_margin():
    """(tanggal, dombak) -> (dipping, dm4, dm5) dari kolom 'Stok Ahir Dipping' tab Stock Margin (harian)."""
    blok_dombak = dict(DOMBAK_BLOK)
    hasil = {}
    for i, b in enumerate(BULAN):
        p = path_margin(b)
        ws = openpyxl.load_workbook(p, data_only=True)['Stock']
        wf = openpyxl.load_workbook(p, data_only=False)['Stock']
        n_hari = calendar.monthrange(TAHUN, i + 1)[1]
        for c in range(1, ws.max_column + 1):
            d = blok_dombak.get(str(ws.cell(2, c).value or '').strip())
            if d is None:
                continue
            kd = kolom_stock(ws, c)['dipping']
            for r in range(4, 4 + n_hari):
                h, v = ws.cell(r, c).value, angka(ws.cell(r, c + kd).value)
                if isinstance(h, (int, float)) and 1 <= int(h) <= n_hari and v:
                    p45 = pecah_dm45(wf.cell(r, c + kd).value) if d == 'DM4-BS' else None
                    hasil[(dt.date(TAHUN, i + 1, int(h)).isoformat(), d)] = (v, p45)
    return hasil


def dm5_modal_kerja():
    """(tahun-bulan) -> liter 'Bio Solar II' (DM5) akhir bulan dari sheet Modal Kerja file
    'Laporan Keuangan cash flow <tahun>.xlsx' (2026: Jan-Jun, Agu)."""
    import glob
    p = sorted(glob.glob(os.path.join(DATA, f'Laporan Keuangan*{TAHUN}*.xlsx')))
    if not p:
        return {}
    ws = openpyxl.load_workbook(p[0], data_only=True)['Modal Kerja']
    nama = [b.upper() for b in ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus',
                                'September', 'Oktober', 'November', 'Desember']]
    hasil = {}
    for r in range(1, 12):
        for c in range(1, ws.max_column + 1):
            v = str(ws.cell(r, c).value or '').strip().upper()
            if v in nama:
                for rr in range(r + 1, r + 12):
                    if str(ws.cell(rr, c - 1).value or '').strip().lower() == 'bio solar ii':
                        liter = next((ws.cell(rr, cc).value for cc in (c + 1, c + 2) if isinstance(ws.cell(rr, cc).value, (int, float))), None)
                        if liter is not None:
                            hasil[f'{TAHUN}-{nama.index(v) + 1:02d}'] = float(liter)
    return hasil


def baris_margin(catatan):
    """2026 (tanpa Laporan SPBU bulanan): satu baris per tangki per hari dari tab Stock Margin, di
    Shift 3. Dipping Biosolar '=DM4+DM5' dipecah; bulan tanpa rumus (Jul-Okt 2026) memakai stok
    DM5 akhir bulan dari Modal Kerja bila ada (keputusan pemilik data: DM5 tetap dipakai)."""
    sm = jual_stand_meter()
    jual_hari = collections.defaultdict(float)
    for (t, _, d), v in sm.items():
        jual_hari[(t, d)] += v
    mk = dm5_modal_kerja()
    baris = []
    for i, nama_bulan in enumerate(BULAN):
        p = path_margin(nama_bulan)
        ws = openpyxl.load_workbook(p, data_only=True)['Stock']
        wf = openpyxl.load_workbook(p, data_only=False)['Stock']
        n_hari = calendar.monthrange(TAHUN, i + 1)[1]
        for c in range(1, ws.max_column + 1):
            nama = str(ws.cell(2, c).value or '').strip()
            dombak = DOMBAK_BLOK.get(nama)
            if dombak is None:
                continue
            ada_rumus = False
            k = kolom_stock(ws, c)
            for r in range(4, 4 + n_hari):
                hari = ws.cell(r, c).value
                if not isinstance(hari, (int, float)) or not 1 <= int(hari) <= n_hari:
                    continue
                t = dt.date(TAHUN, i + 1, int(hari)).isoformat()
                g = lambda off: angka(ws.cell(r, c + off).value)
                b = {'tanggal': t, 'jam_kerja': 'Shift 3', 'urutan': 3, 'dombak': dombak,
                     'stok_awal': fmt(g(k['awal'])), 'penerimaan': fmt(g(k['terima']) or 0), 'penjualan_excel': fmt(g(k['jual']) or 0),
                     'penjualan_stand_meter': fmt(jual_hari.get((t, dombak), 0.0)),
                     'dipping': fmt(g(k['dipping'])), 'tera': fmt(g(k['tera']) or 0) if 'tera' in k else '',
                     'sumber': f'Margin {nama_bulan}/Stock!R{r} ({nama})'}
                if dombak == 'DM4-BS':
                    for kol, off in (('dipping', k['dipping']), ('stok_awal', k['awal'])):
                        p45 = pecah_dm45(wf.cell(r, c + off).value)
                        if p45 and g(off) is not None:
                            b[kol + '_dm4'], b[kol + '_dm5'] = fmt(p45[0]), fmt(p45[1])
                            ada_rumus = ada_rumus or kol == 'dipping'
                baris.append(b)
            if dombak == 'DM4-BS' and not ada_rumus:
                bln = f'{TAHUN}-{i + 1:02d}'
                akhir = [b for b in baris if b['dombak'] == 'DM4-BS' and b['tanggal'][:7] == bln and b['dipping']]
                if bln in mk and akhir:
                    akhir[-1]['dipping_dm5'] = fmt(mk[bln])
                    catatan.append({**akhir[-1], 'jenis': 'dm5_modal_kerja',
                                    'keterangan': f'tanpa rumus DM4+DM5; DM5 akhir bulan {mk[bln]} L dari Modal Kerja'})
                else:
                    catatan.append({'jenis': 'dm5_tanpa_data', 'tanggal': bln, 'jam_kerja': '', 'dombak': 'DM5-BS', 'sumber': '',
                                    'keterangan': 'tanpa rumus DM4+DM5 dan Modal Kerja: DM5 tetap di angka buku'})
    return baris


def main():
    sm = jual_stand_meter()
    baris, catatan = [], []
    if not ada_laporan_bulanan():
        baris = baris_margin(catatan)
    for i, bulan in enumerate(BULAN if ada_laporan_bulanan() else []):
        ws = openpyxl.load_workbook(path_laporan(bulan), data_only=True)['Stock BBM Di Dombak']
        # rumus sel: dipping Biosolar ditulis '=DM4+DM5' (DM5 = tangki simpan, dipakai sejak Agu 2024)
        wf = openpyxl.load_workbook(path_laporan(bulan), data_only=False)['Stock BBM Di Dombak']
        n_hari = calendar.monthrange(TAHUN, i + 1)[1]
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
                t = dt.date(TAHUN, i + 1, hari).isoformat()
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
                if dombak == 'DM4-BS':
                    for kol, off in (('dipping', 7), ('stok_awal', 3)):
                        p = pecah_dm45(wf.cell(r, c + off).value)
                        if p and v[kol if kol == 'dipping' else 'awal'] is not None:
                            b[kol + '_dm4'], b[kol + '_dm5'] = fmt(p[0]), fmt(p[1])
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
    harian = dipping_harian_margin()
    for (t, d), xs in per_hari.items():
        akhir = max(xs, key=lambda b: b['urutan'])
        if akhir['dipping']:
            continue
        if (t, d) in harian:
            # dipping akhir hari dari Margin (mis. Desember 2025: sheet per shift kosong sejak pertengahan bulan)
            v, p45 = harian[(t, d)]
            akhir['dipping'] = fmt(v)
            if p45:
                akhir['dipping_dm4'], akhir['dipping_dm5'] = fmt(p45[0]), fmt(p45[1])
            catatan.append({**akhir, 'jenis': 'dipping_dari_margin', 'keterangan': f'dipping akhir hari {v} dari Margin Stock'})
            continue
        besok = (dt.date.fromisoformat(t) + dt.timedelta(days=1)).isoformat()
        nxt = sorted(per_hari.get((besok, d), []), key=lambda b: b['urutan'])
        if nxt and nxt[0]['stok_awal']:
            akhir['dipping'] = nxt[0]['stok_awal']
            if nxt[0].get('stok_awal_dm5'):
                akhir['dipping_dm4'], akhir['dipping_dm5'] = nxt[0]['stok_awal_dm4'], nxt[0]['stok_awal_dm5']
            catatan.append({**akhir, 'jenis': 'dipping_dari_awal_besok',
                            'keterangan': f"dipping akhir hari = stok awal {besok} {nxt[0]['jam_kerja']} {nxt[0]['stok_awal']}"})

    # dipping akhir hari sheet per shift vs "Stok Ahir Dipping" harian Margin: salah ketik di salah
    # satu sumber (mis. 7 Des 2025 DM3 2.158,5 vs 3.158,5) -> pilih yang paling bersambung dengan
    # shift sebelum (dipping + terima - jual) dan shift sesudahnya; Margin hanya bila jelas lebih baik
    per_dombak = collections.defaultdict(list)
    for b in baris:
        per_dombak[b['dombak']].append(b)
    for d, xs in per_dombak.items():
        xs.sort(key=lambda b: (b['tanggal'], b['urutan']))
        for i, b in enumerate(xs):
            if not b['dipping'] or i + 1 >= len(xs) or xs[i + 1]['tanggal'] == b['tanggal'] or (b['tanggal'], d) not in harian:
                continue
            v, p45 = harian[(b['tanggal'], d)]
            lap = float(b['dipping'])
            if abs(v - lap) <= 1:
                continue
            prev, nxt = (xs[i - 1] if i else None), xs[i + 1]
            gerak = lambda x: float(x['penerimaan'] or 0) - float(x['penjualan_stand_meter'] or 0)

            def galat(cand):
                e = 0.0
                if prev is not None and prev['dipping']:
                    e += abs(cand - (float(prev['dipping']) + gerak(b)))
                if nxt['dipping']:
                    e += abs(float(nxt['dipping']) - (cand + gerak(nxt)))
                return e
            if galat(v) + 50 < galat(lap):
                catatan.append({**b, 'jenis': 'dipping_koreksi_margin',
                                'keterangan': f"dipping {lap} -> {v} (Margin; galat {galat(lap):.0f} -> {galat(v):.0f} L)"})
                b['dipping'] = fmt(v)
                if p45:
                    b['dipping_dm4'], b['dipping_dm5'] = fmt(p45[0]), fmt(p45[1])
                elif b.get('dipping_dm4'):   # Margin tanpa rumus DM4+DM5: selisih dibebankan ke suku DM4
                    b['dipping_dm4'] = fmt(float(b['dipping_dm4']) + v - lap)
                if nxt['stok_awal'] and abs(float(nxt['stok_awal']) - lap) <= 0.5:
                    nxt['stok_awal'] = fmt(v)   # stok awal besok = rumus salinan dipping yang salah ketik
                    if nxt.get('stok_awal_dm4') and b.get('dipping_dm4'):
                        nxt['stok_awal_dm4'], nxt['stok_awal_dm5'] = b['dipping_dm4'], b['dipping_dm5']
            else:
                catatan.append({**b, 'jenis': 'dipping_beda_margin',
                                'keterangan': f"dipping {lap} dipertahankan, Margin {v} (galat {galat(lap):.0f} vs {galat(v):.0f} L)"})

    # kontinuitas: stok awal shift = dipping shift sebelumnya (per dombak)
    for d, xs in per_dombak.items():
        xs.sort(key=lambda b: (b['tanggal'], b['urutan']))
        for prev, cur in zip(xs, xs[1:]):
            if prev['dipping'] and cur['stok_awal'] and abs(float(prev['dipping']) - float(cur['stok_awal'])) > 0.5:
                catatan.append({**cur, 'jenis': 'awal_beda_dipping',
                                'keterangan': f"awal {cur['stok_awal']} vs dipping sebelumnya {prev['dipping']}"})

    baris.sort(key=lambda b: (b['tanggal'], b['urutan'], b['dombak']))
    kolom = ['tanggal', 'jam_kerja', 'urutan', 'dombak', 'stok_awal', 'penerimaan', 'penjualan_excel',
             'penjualan_stand_meter', 'dipping', 'sumber', 'dipping_dm4', 'dipping_dm5', 'stok_awal_dm4', 'stok_awal_dm5', 'tera']
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
