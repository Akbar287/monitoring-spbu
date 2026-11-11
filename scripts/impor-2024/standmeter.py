"""Extractor stand meter (totalisator) 2024.

Sumber : Laporan SPBU <Bulan> 2024.xlsx, sheet "Totalisator Shift 1/2/B2|Shift 3 Nozle".
Keluaran: storage/app/impor-2024/stand_meter.csv + stand_meter_catatan.csv

Aturan:
- B2 = Shift 3.
- Tanggal per blok harian; blok tanpa tanggal / salah ketik di antara dua tanggal
  berurutan diberi tanggal yang semestinya, blok template sesudah akhir bulan dilewati.
- Kode lama digabung ke nozzle penerus (lihat umum.NOZZLE).
- Slot DLT: meter Dexlite mati dilewati; mulai Juli slot DLT berisi bacaan PL
  (Pertalite II, meter baru) -> N-2A PL, termasuk shift tanpa penjualan.
- Bila satu nozzle DB punya >1 baris di tanggal+shift yang sama (masa transisi
  kode lama/baru), dipilih baris yang paling bersambung dengan bacaan sebelumnya
  (seri: yang punya bacaan akhir).
- Angka teks ber-pemisah ribuan dinormalkan; angka yang meleset faktor 1000
  dari bacaan sebelumnya (mis. 134.082 untuk 134082) dikoreksi.
- Celah antar shift < 1 L (pergantian presisi bacaan) dibiarkan seperti Excel;
  celah lebih besar ditutup ke akhir shift sebelumnya agar liter tidak hilang.
"""
import collections
import datetime as dt

import openpyxl

from umum import (BULAN, SHIFT, URUTAN_SHIFT, NOZZLE, DOMBAK_NOZZLE, angka, tanggal,
                  path_laporan, tulis_csv, fmt)

DLT_DIAM = 4732763.0


def blok_sheet(ws):
    """Bagi sheet totalisator menjadi blok harian: tanggal di kolom B baris pertama blok,
    blok ditutup baris TOTAL (atau dibuka tanggal baru)."""
    blok, cur = [], None
    for r in range(4, ws.max_row + 1):
        t = tanggal(ws.cell(r, 2).value)
        kode = ws.cell(r, 3).value
        kode = kode.strip() if isinstance(kode, str) else ''
        if kode.upper() == 'TOTAL':
            cur = None
            continue
        if not kode:
            continue
        if cur is None or (t and cur['baris']):
            cur = {'r': r, 'tanggal': t, 'baris': []}
            blok.append(cur)
        cur['baris'].append((r, kode))
    return blok


def baca_bulan(idx, bulan, catatan):
    """Tanggal blok disimpulkan dari urutan blok (blok = hari berikutnya): blok tanpa tanggal
    atau bertanggal di luar bulan yang diapit dua tanggal berurutan dianggap salah ketik
    (mis. Oktober Shift 3 R15 '2024-09-02' di antara 10-01 dan 10-03); blok tanpa tanggal
    sesudah hari terakhir bulan adalah template (mis. 'tanggal 31' November) dan dilewati."""
    wb = openpyxl.load_workbook(path_laporan(bulan), data_only=True)
    rows = []
    bulan_ini = (2024, idx + 1)
    for ws in wb.worksheets:
        if not ws.title.startswith('Totalisator'):
            continue
        key = ws.title.replace('Totalisator ', '').replace(' Nozle', '').strip()
        shift = SHIFT[key]
        blok = blok_sheet(ws)
        sebelum = None
        for i, b in enumerate(blok):
            t = b['tanggal']
            harap = sebelum + dt.timedelta(days=1) if sebelum else dt.date(2024, idx + 1, 1)
            if t and (t.year, t.month) == bulan_ini:
                pakai = t
            else:
                berikut = blok[i + 1]['tanggal'] if i + 1 < len(blok) else None
                if (harap.year, harap.month) == bulan_ini and (t is None or berikut == harap + dt.timedelta(days=1)):
                    pakai = harap
                    catatan.append({'jenis': 'tanggal_dikoreksi' if t else 'tanggal_kosong', 'tanggal': harap,
                                    'shift': shift, 'nozzle': '', 'kode': '', 'sumber': f"{bulan}/{ws.title}!R{b['r']}",
                                    'keterangan': f"Blok bertanggal {t or '(kosong)'} di antara {sebelum} dan {berikut} -> {harap}"})
                else:
                    continue  # template di luar bulan (mis. 30-31 Februari, blok kosong sesudah 30 November)
            sebelum = pakai
            for r, kode in b['baris']:
                rows.append({
                    'tanggal': pakai, 'shift': shift, 'kode': kode,
                    'awal_raw': ws.cell(r, 5).value, 'akhir_raw': ws.cell(r, 10).value,
                    'harga_raw': ws.cell(r, 12).value,
                    'sumber': f'{bulan}/{ws.title}!R{r}',
                })
    return rows


def main():
    catatan = []
    raw = []
    for i, b in enumerate(BULAN):
        raw.extend(baca_bulan(i, b, catatan))

    kandidat = collections.defaultdict(list)  # (nozzle, tanggal, shift) -> rows
    for x in raw:
        awal, akhir = angka(x['awal_raw']), angka(x['akhir_raw'])
        x['awal'], x['akhir'] = awal, akhir
        kode = x['kode']
        if kode == 'DLT':
            # meter Dexlite mati (DLT_DIAM) dilewati; mulai Juli slot DLT dipakai mencatat
            # Pertalite II (meter baru dari ~0), dengan atau tanpa penjualan -> N-2A PL
            if awal is None or akhir is None or abs(awal - DLT_DIAM) < 1 or x['tanggal'] < dt.date(2024, 7, 1):
                continue
            kode = 'PL'
            catatan.append({**x, 'jenis': 'dlt_jadi_pl', 'keterangan': 'Baris berlabel DLT berisi bacaan PL -> N-2A PL'})
        nz = NOZZLE.get(kode)
        if nz is None:
            catatan.append({**x, 'jenis': 'kode_tak_dikenal', 'keterangan': f'Kode {kode} dilewati'})
            continue
        x['nozzle'] = nz
        kandidat[(nz, x['tanggal'], x['shift'])].append(x)

    # urutkan per nozzle lalu pilih kandidat + perbaiki angka berdasarkan kontinuitas
    per_nozzle = collections.defaultdict(list)
    for (nz, t, s), xs in kandidat.items():
        per_nozzle[nz].append((t, URUTAN_SHIFT[s], s, xs))

    hasil = []
    for nz, items in per_nozzle.items():
        items.sort(key=lambda i: (i[0], i[1]))
        prev = None
        prev_row = None
        for t, u, s, xs in items:
            def jarak(x):
                if prev is None or x['awal'] is None:
                    return 0 if x['awal'] is not None else 1e18
                return abs(x['awal'] - prev)
            if len(xs) > 1:
                # paling bersambung dulu; bila seri, utamakan baris yang punya bacaan akhir
                xs = sorted(xs, key=lambda x: (jarak(x), x['akhir'] is None,
                                               -abs((x['akhir'] if x['akhir'] is not None else x['awal'] or 0) - (x['awal'] or 0))))
                for lain in xs[1:]:
                    catatan.append({**lain, 'nozzle': nz, 'jenis': 'duplikat_transisi',
                                    'keterangan': f"Dilewati, dipilih {xs[0]['kode']} ({xs[0]['sumber']})"})
            x = dict(xs[0])
            for kol in ('awal', 'akhir'):
                v = x[kol]
                ref = prev if kol == 'awal' else (x['awal'] if x['awal'] is not None else prev)
                # hanya bila v*1000 hampir tepat bacaan acuan (salah pemisah ribuan),
                # bukan meter yang di-reset (mis. PP4 3 Jan: 5.665.318 -> 3,06)
                if v is not None and ref and v < ref / 100 and abs(v * 1000 - ref) <= max(ref * 0.01, 1):
                    catatan.append({**x, 'nozzle': nz, 'jenis': 'koreksi_x1000',
                                    'keterangan': f'{kol} {v} -> {v * 1000}'})
                    x[kol] = v * 1000
            if x['awal'] is None and prev is not None:
                catatan.append({**x, 'nozzle': nz, 'jenis': 'awal_kosong', 'keterangan': f'awal diisi akhir sebelumnya {prev}'})
                x['awal'] = prev
            if x['akhir'] is None:
                catatan.append({**x, 'nozzle': nz, 'jenis': 'akhir_kosong', 'keterangan': 'Baris dilewati (tidak ada bacaan akhir)'})
                continue
            if x['awal'] is None:
                catatan.append({**x, 'nozzle': nz, 'jenis': 'awal_kosong', 'keterangan': 'Baris dilewati (tidak ada bacaan awal)'})
                continue
            if prev is not None and abs(x['awal'] - prev) > 0.01:
                gap = x['awal'] - prev
                if 0 < gap < 1:
                    # pergantian presisi bacaan (bulat -> 3 desimal): dibiarkan seperti Excel
                    catatan.append({**x, 'nozzle': nz, 'jenis': 'putus_kecil',
                                    'keterangan': f"awal {x['awal']} vs akhir sebelumnya {prev} (+{gap:.3f} L), tidak dikoreksi"})
                elif gap > 0 and prev_row is not None:
                    # bacaan akhir shift sebelumnya basi: tutup celah agar liter tidak hilang
                    prev_row['stand_meter_akhir'] = fmt(x['awal'])
                    prev_row['liter'] = fmt(x['awal'] - float(prev_row['stand_meter_awal']))
                    catatan.append({**x, 'nozzle': nz, 'jenis': 'putus_ditutup',
                                    'keterangan': f"akhir {prev_row['tanggal']} {prev_row['jam_kerja']} {prev} -> {x['awal']} (+{gap:.2f} L)"})
                else:
                    catatan.append({**x, 'nozzle': nz, 'jenis': 'meter_reset',
                                    'keterangan': f"awal {x['awal']} < akhir sebelumnya {prev}: totalisator di-reset, tidak dikoreksi"})
            if x['akhir'] < x['awal'] - 1e-6:
                catatan.append({**x, 'nozzle': nz, 'jenis': 'minus',
                                'keterangan': f"akhir < awal ({x['akhir'] - x['awal']:.2f} L)"})
            prev_row = {
                'tanggal': t.isoformat(), 'jam_kerja': s, 'urutan': u, 'nozzle': nz,
                'dombak': DOMBAK_NOZZLE[nz], 'stand_meter_awal': fmt(x['awal']),
                'stand_meter_akhir': fmt(x['akhir']), 'liter': fmt(x['akhir'] - x['awal']),
                'kode_excel': x['kode'], 'sumber': x['sumber'], 'harga_excel': fmt(angka(x['harga_raw']), 2),
            }
            hasil.append(prev_row)
            prev = x['akhir']

    hasil.sort(key=lambda h: (h['tanggal'], h['urutan'], h['nozzle']))
    tulis_csv('stand_meter.csv', ['tanggal', 'jam_kerja', 'urutan', 'nozzle', 'dombak', 'stand_meter_awal',
                                  'stand_meter_akhir', 'liter', 'kode_excel', 'sumber', 'harga_excel'], hasil)
    for c in catatan:
        c['tanggal'] = c['tanggal'].isoformat()
    tulis_csv('stand_meter_catatan.csv', ['jenis', 'tanggal', 'shift', 'nozzle', 'kode', 'sumber', 'keterangan'], catatan)

    # ringkasan
    print(f'stand_meter: {len(hasil)} baris')
    print('catatan:', dict(collections.Counter(c['jenis'] for c in catatan)))
    per_bulan = collections.defaultdict(float)
    for h in hasil:
        per_bulan[(h['tanggal'][:7], h['nozzle'])] += float(h['liter'])
    bulan = sorted({k[0] for k in per_bulan})
    noz = sorted({k[1] for k in per_bulan})
    print('liter per bulan per nozzle:')
    print('         ' + ''.join(f'{n:>10}' for n in noz))
    for b in bulan:
        print(f'{b}  ' + ''.join(f'{per_bulan.get((b, n), 0):>10,.0f}' for n in noz))


if __name__ == '__main__':
    main()
