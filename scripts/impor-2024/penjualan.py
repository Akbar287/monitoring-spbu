"""Extractor penjualan 2024: harga jual/pokok per stand meter, untuk penjualan + HPP.

Sumber:
- stand_meter.csv (standmeter.py): liter + harga di kolom "Harga Jual" totalisator.
- Margin <Bulan> 2024.xlsx, sheet "Penjualan": harga satuan harian per produk
  (acuan bila harga totalisator janggal, mis. salin dari baris Dexlite).
- order.csv (pengadaan.py): biaya per liter faktur (net + PPN + PPBKB; metode 'hpp').
- Margin, sheet "Laba": rupiah penjualan per produk per bulan (validasi).
Keluaran: storage/app/impor-2024/penjualan_detail.csv, harga.csv, penjualan_catatan.csv
"""
import calendar
import collections
import csv
import datetime as dt
import glob
import os

import openpyxl

from umum import BULAN, OUT, TAHUN, angka, fmt, path_margin, tulis_csv

PRODUK_NOZZLE = {'PL': 'Pertalite', 'PX': 'Pertamax', 'BS': 'Bio Solar', 'PDX': 'Pertamina Dex'}
BLOK_PRODUK = {'PERTAMAX': 'Pertamax', 'PERTALITE': 'Pertalite', 'PERTALITE 2': 'Pertalite', 'PERTALITE II': 'Pertalite',
               'BIO SOLAR': 'Bio Solar', 'PERTAMINA DEX': 'Pertamina Dex'}
LABA_PRODUK = {'PERTAMAX': 'Pertamax', 'PERTALITE': 'Pertalite', 'PERTALITE 2': 'Pertalite', 'PERTALITE II': 'Pertalite',
               'BIO SOLAR': 'Bio Solar', 'PERTAMINA DEX': 'Pertamina Dex'}

catatan = []

# Harga totalisator yang TIDAK dikoreksi ke Margin karena sumber independen sepakat:
# 3 Jan Shift 2 N-1B Pertamax 13.500 (totalisator R20 dan Lap Penjualan Harian
# Januari24!R12:S12); Margin Penjualan!C6 berisi 13.550 ketikan manual.
HARGA_TOTALISATOR_DIPERCAYA = {('2024-01-03', 'Shift 2', 'N-1B PX')}


def margin_file(bulan):
    return path_margin(bulan)


def harga_margin():
    """(tanggal, produk) -> harga satuan dari sheet Penjualan Margin."""
    hasil = {}
    for i, b in enumerate(BULAN):
        ws = openpyxl.load_workbook(margin_file(b), data_only=True)['Penjualan']
        n_hari = calendar.monthrange(TAHUN, i + 1)[1]
        for c in range(1, ws.max_column + 1):
            p = BLOK_PRODUK.get(str(ws.cell(2, c).value or '').strip())
            if p is None:
                continue
            for r in range(4, 4 + n_hari):
                h, harga = ws.cell(r, c).value, angka(ws.cell(r, c + 2).value)
                if isinstance(h, (int, float)) and 1 <= int(h) <= n_hari and harga:
                    k = (dt.date(TAHUN, i + 1, int(h)).isoformat(), p)
                    hasil.setdefault(k, harga)   # blok Pertalite pertama (tangki lama) diutamakan
    return hasil


def laba_margin():
    """(bulan, produk) -> (rupiah penjualan, margin) dari sheet Laba."""
    jual = collections.defaultdict(float)
    margin = collections.defaultdict(float)
    for i, b in enumerate(BULAN):
        ws = openpyxl.load_workbook(margin_file(b), data_only=True)['Laba']
        for r in range(2, 12):
            p = LABA_PRODUK.get(str(ws.cell(r, 2).value or '').strip())
            if p:
                jual[(f'{TAHUN}-{i + 1:02d}', p)] += angka(ws.cell(r, 7).value) or 0
                margin[(f'{TAHUN}-{i + 1:02d}', p)] += angka(ws.cell(r, 8).value) or 0
    return jual, margin


# Porsi PPh 22 dalam harga tebus (PPh / total faktur), dari faktur 2024. Harga tebus Excel
# sudah termasuk PPh, sedangkan persediaan (metode 'hpp') = net + PPN + PPBKB karena
# PPh dibebankan terpisah (Beban PPh Final) saat faktur diposting.
PORSI_PPH = {'Pertalite': 172414 / 77612414, 'Pertamax': 227848 / 104667848,
             'Bio Solar': 117241 / 52837241, 'Pertamina Dex': 65190 / 29975190}
# margin SPBU per liter (harga jual - harga pokok) dari faktur yang bersih; cadangan
# bila Excel tidak punya harga tebus untuk hari itu
MARGIN_TETAP = {'Pertalite': 320, 'Bio Solar': 210, 'Pertamax': 445, 'Pertamina Dex': 495}
NOZZLE_PRODUK_EXCEL = {'Pertalite': 'Pertalite', 'Pertalite II': 'Pertalite', 'Pertamax': 'Pertamax',
                       'Biosolar': 'Bio Solar', 'Pertamina DEX': 'Pertamina Dex'}


def tebus_excel():
    """(tanggal, produk) -> harga tebus (termasuk PPh) dari sheet 'Penjualan harian nozle' Margin."""
    hasil = {}
    for i, b in enumerate(BULAN):
        ws = openpyxl.load_workbook(margin_file(b), data_only=True)['Penjualan harian nozle']
        t = None
        hitung = collections.defaultdict(collections.Counter)
        for r in range(4, ws.max_row + 1):
            v = ws.cell(r, 2).value
            if isinstance(v, dt.datetime):
                t = v.date()
            p = NOZZLE_PRODUK_EXCEL.get(str(ws.cell(r, 4).value or '').strip())
            tebus = ws.cell(r, 9).value
            if t and t.year == TAHUN and t.month == i + 1 and p and isinstance(tebus, (int, float)) and tebus > 0:
                hitung[(t.isoformat(), p)][round(tebus, 6)] += 1
        for k, c in hitung.items():
            hasil[k] = c.most_common(1)[0][0]
    return hasil


def biaya_pada(tebus, produk, tanggal, harga_jual):
    """Harga pokok per liter: tebus Excel tanpa PPh; cadangan: tebus terakhir bulan itu, lalu jual - margin."""
    t = tebus.get((tanggal, produk))
    if t is None:
        sebelum = [v for (d, p), v in tebus.items() if p == produk and d[:7] == tanggal[:7] and d <= tanggal]
        t = max(((d, v) for (d, p), v in tebus.items() if p == produk and d[:7] == tanggal[:7] and d <= tanggal),
                default=(None, None))[1] if sebelum else None
    if t is None:
        return round(harga_jual - MARGIN_TETAP[produk], 6), 'margin_tetap'
    pokok = t * (1 - PORSI_PPH[produk])
    if not (harga_jual - 2 * MARGIN_TETAP[produk] - 1000 <= pokok <= harga_jual + 1000):
        # tebus produk lain (mis. Januari: tebus Pertalite di baris Pertamax) -> margin tetap
        return round(harga_jual - MARGIN_TETAP[produk], 6), 'margin_tetap'
    return round(pokok, 6), 'tebus_excel'


def tera_harian():
    """(tanggal, dombak) -> liter tera (kolom 'Tera' tab Stock Margin, mulai Juli 2026)."""
    hasil = {}
    p = os.path.join(OUT, 'stok_shift.csv')
    for r in csv.DictReader(open(p, encoding='utf-8')):
        if r.get('tera') and float(r['tera']) > 0:
            hasil[(r['tanggal'], r['dombak'])] = float(r['tera'])
    return hasil


def biaya_faktur():
    """2026: biaya per liter termasuk PPh dari biaya_harian.csv (pengadaan.py, acuan faktur asli)."""
    p = os.path.join(OUT, 'biaya_harian.csv')
    if TAHUN < 2026 or not os.path.exists(p):
        return None
    return {(r['tanggal'], r['produk']): float(r['biaya_per_liter']) for r in csv.DictReader(open(p, encoding='utf-8'))}


def main():
    hm = harga_margin()
    tebus = biaya_faktur() or tebus_excel()
    asal_biaya = collections.Counter()
    rows = list(csv.DictReader(open(os.path.join(OUT, 'stand_meter.csv'), encoding='utf-8')))
    detail = []
    for r in rows:
        liter = float(r['liter'])
        if liter < 0.0005:
            # nol, atau minus sangat kecil dari pembulatan stand meter (total -0,39 L setahun): bukan penjualan
            continue
        produk = PRODUK_NOZZLE[r['nozzle'].split()[-1]]
        hx = float(r['harga_excel']) if r['harga_excel'] else None
        if r['nozzle'] == 'N-1B PX' and r['tanggal'].startswith('2024-01') and hx == 10000:
            produk = 'Pertalite'   # Januari: tangki Pertamax berisi Pertalite, dijual 10.000/L
            harga = hx
        else:
            acuan = hm.get((r['tanggal'], produk))
            harga = hx
            if (r['tanggal'], r['jam_kerja'], r['nozzle']) in HARGA_TOTALISATOR_DIPERCAYA and hx:
                catatan.append({'jenis': 'harga_totalisator_dipertahankan', 'tanggal': r['tanggal'], 'sumber': r['sumber'],
                                'keterangan': f"{r['nozzle']} {produk}: totalisator {hx} (Lap Harian sama), Margin {acuan}"})
            elif TAHUN >= 2026 and hx:
                # 2026: LAPORAN HARIAN mencatat harga per shift (perubahan harga di tengah hari, mis. Dex
                # 17 Apr Shift 3 24.450); sheet Penjualan Margin kadang tertinggal (Feb/Mar PX, 1-10 Jul PX)
                if acuan and hx != acuan:
                    catatan.append({'jenis': 'harga_margin_berbeda', 'tanggal': r['tanggal'], 'sumber': r['sumber'],
                                    'keterangan': f"{r['nozzle']} {produk}: LAPORAN HARIAN {hx} dipakai, Margin {acuan}"})
            elif acuan and hx != acuan:
                catatan.append({'jenis': 'harga_dikoreksi', 'tanggal': r['tanggal'], 'sumber': r['sumber'],
                                'keterangan': f"{r['nozzle']} {produk}: totalisator {hx} -> Margin {acuan}"})
                harga = acuan
            elif not hx:
                harga = acuan
        pokok, asal = biaya_pada(tebus, produk, r['tanggal'], harga)
        asal_biaya[asal] += 1
        detail.append({
            'tanggal': r['tanggal'], 'jam_kerja': r['jam_kerja'], 'urutan': r['urutan'], 'nozzle': r['nozzle'],
            'dombak': r['dombak'], 'produk_harga': produk, 'liter': fmt(liter),
            'harga_jual': fmt(harga, 2), 'harga_beli': fmt(pokok),
            'total': fmt(round(liter * harga, 2), 2), 'sumber': r['sumber'],
        })

    # tera (keputusan pemilik: Biaya Tera ikut dalam perhitungan penjualan): liter tera keluar lewat
    # nozzle lalu kembali ke tangki, jadi bukan penjualan. Liter tera hari itu dikurangkan dari baris
    # stand meter terbesar tangki itu; importer mencatatnya sebagai Good Issue Tera + Good Return Tera.
    tera = tera_harian()
    per_hari = collections.defaultdict(list)
    for d in detail:
        d['tera'] = '0'
        per_hari[(d['tanggal'], d['dombak'])].append(d)
    sisa_tera = 0.0
    for k, liter_tera in sorted(tera.items()):
        sisa = liter_tera
        for d in sorted(per_hari.get(k, []), key=lambda d: -float(d['liter'])):
            if sisa <= 0.0005:
                break
            ambil = min(sisa, float(d['liter']))
            d['tera'] = fmt(ambil)
            d['liter'] = fmt(float(d['liter']) - ambil)
            d['total'] = fmt(round(float(d['liter']) * float(d['harga_jual']), 2), 2)
            sisa -= ambil
        if sisa > 0.0005:
            sisa_tera += sisa
            catatan.append({'jenis': 'tera_tanpa_penjualan', 'tanggal': k[0], 'sumber': k[1],
                            'keterangan': f'{sisa:.2f} L tera tidak tertampung penjualan hari itu'})
    if tera:
        print(f'tera: {sum(tera.values()):,.2f} L dari {len(tera)} hari-tangki dikeluarkan dari penjualan'
              f' (tak tertampung {sisa_tera:.2f} L)')

    # baris harga: satu per produk per perubahan (harga jual atau biaya); dipakai semua shift
    harga = {}
    for d in sorted(detail, key=lambda d: d['tanggal']):
        k = (d['produk_harga'], d['harga_jual'], d['harga_beli'])
        harga.setdefault(k, d['tanggal'])
    harga_rows = [{'produk': p, 'tanggal': t, 'harga_jual': hj, 'harga_beli': hb} for (p, hj, hb), t in sorted(harga.items(), key=lambda x: (x[0][0], x[1]))]

    tulis_csv('penjualan_detail.csv', ['tanggal', 'jam_kerja', 'urutan', 'nozzle', 'dombak', 'produk_harga', 'liter',
                                       'harga_jual', 'harga_beli', 'total', 'sumber', 'tera'], detail)
    tulis_csv('harga.csv', ['produk', 'tanggal', 'harga_jual', 'harga_beli'], harga_rows)
    tulis_csv('penjualan_catatan.csv', ['jenis', 'tanggal', 'sumber', 'keterangan'], catatan)

    # ---- ringkasan & validasi vs sheet Laba
    print(f'detail penjualan: {len(detail)} | shift penjualan: {len({(d["tanggal"], d["jam_kerja"]) for d in detail})} | baris harga: {len(harga_rows)}')
    print('catatan:', dict(collections.Counter(c['jenis'] for c in catatan)), '| sumber harga pokok:', dict(asal_biaya))
    for h in harga_rows:
        print(f"  harga {h['produk']:14} mulai {h['tanggal']}: jual {float(h['harga_jual']):>9,.0f}  pokok {float(h['harga_beli']):>10,.2f}")
    laba, laba_mg = laba_margin()
    kita = collections.defaultdict(float)
    mg = collections.defaultdict(float)   # margin versi Excel: (jual - tebus termasuk PPh) x liter
    hpp = 0.0
    for d in detail:
        k = (d['tanggal'][:7], d['produk_harga'])
        kita[k] += float(d['total'])
        tebus_l = float(d['harga_beli']) / (1 - PORSI_PPH[d['produk_harga']])
        mg[k] += float(d['liter']) * (float(d['harga_jual']) - tebus_l)
        hpp += float(d['liter']) * float(d['harga_beli'])
    print('\nbulan    produk          rupiah kita     Margin(Laba)        beda |  margin kita  margin Laba      beda')
    tk = tm = mk = ml = 0
    for k in sorted(set(kita) | set(laba)):
        a, b = kita.get(k, 0), laba.get(k, 0)
        c, e = mg.get(k, 0), laba_mg.get(k, 0)
        tk, tm, mk, ml = tk + a, tm + b, mk + c, ml + e
        flag = '  <==' if abs(a - b) > 1_000_000 or abs(c - e) > 500_000 else ''
        print(f"{k[0]}  {k[1]:14} {a:>15,.0f} {b:>15,.0f} {a - b:>12,.0f} | {c:>12,.0f} {e:>12,.0f} {c - e:>9,.0f}{flag}")
    print(f"TOTAL                   {tk:>15,.0f} {tm:>15,.0f} {tk - tm:>12,.0f} | {mk:>12,.0f} {ml:>12,.0f} {mk - ml:>9,.0f}")
    print(f"HPP setahun (liter x harga pokok tanpa PPh): {hpp:,.0f}")


if __name__ == '__main__':
    main()
