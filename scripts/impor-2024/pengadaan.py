"""Extractor pengadaan 2024: order (rencana+pembayaran+faktur), LO, dan penerimaan.

Sumber:
- A  Informasi Detail Produk BBM-final.xlsx (Jan-Sep): baris order (TRX/SO/BG)
     dan baris DO/LO (nomor LO, tgl kirim/terima, nopol, supir).
- B  Laporan SPBU <Bulan>, sheet "Setoran , Penerimaan BBM" (Mar-Des): per produk
     per hari: nilai bayar (Harga BBM), PPh, KL dipesan, KL diterima, No LO.
- C  Laporan SPBU <Bulan>, sheet "Bongkaran BBM Aktual": per tangki per shift:
     penerima, supir, nopol, stok sebelum, rencana (teoritis), dipping sesudah.
- D  stok_shift.csv (hasil stok.py) hanya untuk melengkapi penerimaan yang tidak
     tercatat di Bongkaran.

Aturan utama (disetujui pemilik data):
- Order: Jan-Feb dari A (nilai dihitung dari harga satuan faktur bulan itu),
  Mar-Des dari B (nilai dari kolom Harga BBM + PPh), kode TRX/SO/BG diambil dari
  A bila cocok (tanggal+produk).
- LO: Jan-Feb dari A, Mar-Des dari B; nopol/supir/tgl kirim dilengkapi dari A/C.
- Penerimaan: baris Bongkaran; dipasangkan ke LO per produk menurut urutan waktu.
  Satu LO boleh terbagi ke beberapa tangki, satu baris bongkaran boleh berisi
  beberapa LO.
- LO dipasangkan ke order per produk menurut urutan tanggal sepanjang tahun (FIFO);
  LO awal Januari tanpa order -> order "carry-over 2023".
"""
import calendar
import collections
import csv
import datetime as dt
import os
import re

import openpyxl

from umum import BULAN, DATA, OUT, angka, fmt, path_laporan, tanggal, tulis_csv

PRODUK = {'PL': 'Pertalite', 'BS': 'Bio Solar', 'PX': 'Pertamax', 'DX': 'Pertamina Dex'}
KL_PER_LO = {'PL': 8, 'BS': 8, 'PX': 8, 'DX': 2}
PPBKB = {'PL': 0.05, 'BS': 0.05, 'PX': 0.075, 'DX': 0.075}   # dari faktur 2024
PPH = 0.0025
PPN = 0.11

BLOK_KELUARGA = {'PERTAMAX': 'PX', 'PERTALITE': 'PL', 'PERTALITE 2': 'PL', 'PERTALITE II': 'PL',
                 'BIO SOLAR': 'BS', 'PERTAMINA DEX': 'DX'}
BLOK_DOMBAK = {'PERTAMAX': 'DM1-PX', 'PERTALITE': 'DM2-PL (Lama)', 'PERTALITE 2': 'DM3-PL (Baru)',
               'PERTALITE II': 'DM3-PL (Baru)', 'BIO SOLAR': 'DM4-BS', 'PERTAMINA DEX': 'DM6-PDX'}
DOMBAK_KELUARGA = {'DM1-PX': 'PX', 'DM2-PL (Lama)': 'PL', 'DM3-PL (Baru)': 'PL', 'DM4-BS': 'BS', 'DM6-PDX': 'DX'}
# Januari 2024: tangki Pertamax (DM1) diisi Pertalite (LO Pertalite, dijual 10.000/L).
KELUARGA_KHUSUS = {('DM1-PX', '2024-01'): 'PL'}

SHIFT_NO = {1: 'Shift 1', 2: 'Shift 2', 3: 'Shift 3'}

# Pemindahan antar tangki yang di Excel tercatat sebagai "penjualan" diketik manual
# di tangki asal (tanpa gerak stand meter) dan "penerimaan" tanpa bongkaran di tangki
# tujuan. liter = keluar dari tangki asal, liter_terima = masuk ke tangki tujuan;
# selisihnya = susut pemindahan (losses "Good Transfer").
# - Januari: sisa Pertalite di tangki Pertamax (DM1) dipindah ke DM2 sebelum DM1 diisi
#   Pertamax lagi (Stock BBM Di Dombak F86/P86, F90/P90).
# - Nov-Des: Pertalite antar DM2 <-> DM3; Margin November/Desember menghitungnya sebagai
#   penjualan tambahan (Penjualan!G15 +718, G22 +902,1, Q26 +2.975,5, Q4 +708,8, Q8 +515,3).
TRANSFER = [
    {'tanggal': '2024-01-28', 'jam_kerja': 'Shift 2', 'urutan': 2, 'asal': 'DM1-PX', 'tujuan': 'DM2-PL (Lama)',
     'produk': 'Pertalite', 'liter': 6952.0, 'liter_terima': 6952.0, 'jam': '18.45'},
    {'tanggal': '2024-01-29', 'jam_kerja': 'Shift 3', 'urutan': 3, 'asal': 'DM1-PX', 'tujuan': 'DM2-PL (Lama)',
     'produk': 'Pertalite', 'liter': 8373.0, 'liter_terima': 8373.0, 'jam': '02.34'},
    # Stock BBM Di Dombak November Q37 / AL37
    {'tanggal': '2024-11-12', 'jam_kerja': 'Shift 1', 'urutan': 1, 'asal': 'DM2-PL (Lama)', 'tujuan': 'DM3-PL (Baru)',
     'produk': 'Pertalite', 'liter': 718.0, 'liter_terima': 615.3, 'jam': ''},
    # Q58 / AL58
    {'tanggal': '2024-11-19', 'jam_kerja': 'Shift 1', 'urutan': 1, 'asal': 'DM2-PL (Lama)', 'tujuan': 'DM3-PL (Baru)',
     'produk': 'Pertalite', 'liter': 902.1, 'liter_terima': 809.4, 'jam': ''},
    # AM70 / P70
    {'tanggal': '2024-11-23', 'jam_kerja': 'Shift 1', 'urutan': 1, 'asal': 'DM3-PL (Baru)', 'tujuan': 'DM2-PL (Lama)',
     'produk': 'Pertalite', 'liter': 2975.5, 'liter_terima': 2915.0, 'jam': ''},
    # Q79 / AL79
    {'tanggal': '2024-11-26', 'jam_kerja': 'Shift 1', 'urutan': 1, 'asal': 'DM2-PL (Lama)', 'tujuan': 'DM3-PL (Baru)',
     'produk': 'Pertalite', 'liter': 1559.1, 'liter_terima': 1449.5, 'jam': ''},
    # Desember AM5 (1.407,54) - stand meter (698,70) / P5
    {'tanggal': '2024-12-01', 'jam_kerja': 'Shift 2', 'urutan': 2, 'asal': 'DM3-PL (Baru)', 'tujuan': 'DM2-PL (Lama)',
     'produk': 'Pertalite', 'liter': 708.84, 'liter_terima': 615.0, 'jam': ''},
    # hanya di Margin Desember (Penjualan!Q8 +515,3; Stock DM2 penerimaan 1.532 vs 1.100); dipping
    # Shift 3 DM3 turun 473,5 L tanpa penjualan, DM2 naik 376,2 L tanpa bongkaran
    {'tanggal': '2024-12-05', 'jam_kerja': 'Shift 3', 'urutan': 3, 'asal': 'DM3-PL (Baru)', 'tujuan': 'DM2-PL (Lama)',
     'produk': 'Pertalite', 'liter': 515.3, 'liter_terima': 432.0, 'jam': ''},
]
# Salah ketik KL di kolom H Informasi Detail Produk (order Jan-Feb dihitung dari KL):
# F (rit) x G (KL per rit) dan nilai transaksi di kolom V sama-sama menunjuk angka koreksi.
# Januari R133: SO 4027835615 '18 kl', F x G = 16, V132 = BS 8 + PL 16 KL.
# Januari R168: SO 4027914678 '8 KL', F x G = 16, V168 = nilai BS 32 KL (bersama baris lain 16 KL).
KL_ORDER_KOREKSI = {('Januari', 133): 16.0, ('Januari', 168): 16.0}
# (tanggal, shift Bongkaran) -> shift: hari yang di sheet stok dicatat utuh di satu shift
SHIFT_PENERIMAAN_KOREKSI = {('2024-04-11', 2): 1}
PLAT = re.compile(r'^[A-Z]{1,2}\s?\d{1,4}\s?[A-Z]{0,3}$')

catatan = []


def cat(jenis, ket, **kw):
    catatan.append({'jenis': jenis, 'keterangan': ket, **kw})


def keluarga(material):
    m = str(material).upper()
    if 'PERTAMAX' in m:
        return 'PX'
    if 'PERTALITE' in m:
        return 'PL'
    if 'SOLAR' in m:
        return 'BS'
    if 'DEX' in m:
        return 'DX'
    return None


def nomor_lo(v):
    """Ambil nomor LO (10 digit) dari sel: angka, '8103675418 & 8103592373', '(..) (..)'."""
    if v is None:
        return []
    if isinstance(v, float):
        if v != int(v):
            cat('lo_desimal', f'Sel LO bernilai desimal {v!r}, diambil bagian bulat')
        s = str(int(v))
    else:
        s = str(v)
    return re.findall(r'(?<!\d)\d{9,10}(?!\d)', s)


def kl_dari(v):
    m = re.search(r'(\d+(?:[.,]\d+)?)', str(v or ''))
    return float(m.group(1).replace(',', '.')) if m else None


def plat(v):
    s = re.sub(r'\s+', ' ', str(v or '').strip().upper())
    return s if PLAT.match(s) else None


def rapikan_plat(s):
    """'BE8927AUC' / 'BE 8927AUC' -> 'BE 8927 AUC'."""
    if not s:
        return None
    m = re.match(r'^([A-Z]{1,2})\s?(\d{1,4})\s?([A-Z]{0,3})$', s.replace(' ', '') if ' ' not in s else s)
    if not m:
        m = re.match(r'^([A-Z]{1,2})(\d{1,4})([A-Z]{0,3})$', s.replace(' ', ''))
    return ' '.join(x for x in m.groups() if x) if m else s


def ref_order(v):
    s = str(v or '').upper()
    m = re.search(r'TRX\s*ID\s*:?\s*(\d+)', s)
    if m:
        return 'TRX-' + m.group(1)
    m = re.search(r'S[O0]\s*(?:NUMBER)?\s*:?\s*(\d+)', s)
    if m:
        return 'SO-' + m.group(1)
    m = re.search(r'BG\s*:?\s*([A-Z0-9]+)', s)
    if m:
        return 'BG-' + m.group(1)
    return None


def pecah_pajak(kel, total, pph=None):
    """Bagi total faktur menjadi net/PPN/PPBKB/PPh (DPP diturunkan dari PPh = 0,25% DPP)."""
    if pph is None or pph <= 0:
        # DPP = total / (1 + PPN + PPBKB + PPh) untuk komponen pajak, net = total - pajak
        dpp = None
    else:
        dpp = pph / PPH
    if dpp is None:
        return None
    ppn = round(dpp * PPN)
    ppbkb = round(dpp * PPBKB[kel])
    pph = round(pph)
    net = round(total) - ppn - ppbkb - pph
    return {'net': net, 'ppn': ppn, 'ppbkb': ppbkb, 'pph': pph, 'total': round(total)}


# --------------------------------------------------------------------------- A
def baca_a():
    wb = openpyxl.load_workbook(os.path.join(DATA, 'Informasi Detail Produk BBM-final.xlsx'), data_only=True)
    orders, los, tarif = [], [], collections.defaultdict(collections.Counter)
    for i, bulan in enumerate(BULAN[:9]):
        ws = wb[bulan]
        tgl = None
        rows = list(range(8, ws.max_row + 1))
        prev_do = None
        for r in rows:
            g = lambda c: ws.cell(r, c).value
            if tanggal(g(3)):
                tgl = tanggal(g(3))
            d = g(4)
            if not isinstance(d, str):
                continue
            kel = keluarga(d)
            if kel is None:
                continue
            e = g(5)
            ket = str(g(15) or '').strip().upper()
            isdo = any(g(c) is not None for c in (10, 11, 12, 13))
            ref = ref_order(e)
            if not isdo or ref:
                kl = kl_dari(g(8)) or ((g(6) or 0) * (g(7) or 0))
                if (bulan, r) in KL_ORDER_KOREKSI:
                    cat('kl_dikoreksi', f'{ref} {kel}: kolom H {kl:g} KL -> {KL_ORDER_KOREKSI[(bulan, r)]:g} KL '
                                        f'(F x G dan nilai transaksi V)', sumber=f'A/{bulan}!R{r}')
                    kl = KL_ORDER_KOREKSI[(bulan, r)]
                t = tanggal(g(9)) or tgl
                if 'BATAL' in ket:
                    cat('order_batal', f'{ref} {kel} {kl} KL dilewati', sumber=f'A/{bulan}!R{r}')
                    continue
                if t is None or not kl:
                    cat('order_tak_lengkap', f'{e!r} {kel} {kl}', sumber=f'A/{bulan}!R{r}')
                    continue
                orders.append({'tanggal': t, 'ref': ref or f'A-{bulan}-R{r}', 'kel': kel, 'kl': kl,
                               'sumber': f'A/{bulan}!R{r}'})
                continue
            # baris DO/LO
            nomor = nomor_lo(e)
            kl = kl_dari(g(8)) or kl_dari(g(12))
            m_ini, m_lanjut = g(13), ws.cell(r + 1, 13).value
            nopol = plat(m_ini)
            supir = None
            if nopol is None and m_ini:
                supir = str(m_ini).strip()          # baris kedua sepasang: M berisi supir
                nopol = prev_do['nopol'] if prev_do else None
            elif m_lanjut and not plat(m_lanjut) and not isinstance(ws.cell(r + 1, 4).value, str):
                supir = str(m_lanjut).strip()
            elif m_lanjut and not plat(m_lanjut):
                supir = str(m_lanjut).strip()
            kirim = tanggal(g(10)) or (prev_do['kirim'] if prev_do and not tanggal(g(3)) else None) or tgl
            terima = tanggal(g(11)) or (prev_do['terima'] if prev_do and not tanggal(g(3)) else None) or kirim
            if 'BATAL' in ket:
                cat('lo_batal', f'{nomor} dilewati', sumber=f'A/{bulan}!R{r}')
                continue
            if not nomor:
                cat('lo_tanpa_nomor', f'{e!r} {kel} {kl} KL', sumber=f'A/{bulan}!R{r}')
                continue
            per = (kl / len(nomor)) if kl else KL_PER_LO[kel]
            item = {'kel': kel, 'nopol': rapikan_plat(nopol), 'supir': supir, 'kirim': kirim, 'terima': terima,
                    'sumber': f'A/{bulan}!R{r}', 'ket': ket}
            for n in nomor:
                los.append({**item, 'no_lo': n, 'kl': per})
            prev_do = item
            # tarif satuan faktur (untuk order Jan-Feb tanpa nilai)
            net, ppn, ppbkb, pph, tot = (angka(g(c)) for c in (18, 19, 20, 21, 22))
            if tot and kl and not isinstance(ws.cell(r + 1, 4).value, str):
                tarif[(i + 1, kel)][(round(net / kl, 4), round(ppn / kl, 4), round(ppbkb / kl, 4),
                                     round(pph / kl, 4), round(tot / kl, 4))] += 1
    return orders, los, tarif


# --------------------------------------------------------------------------- B
def baca_b():
    orders, los, sisa_awal = [], [], {}
    for i, bulan in enumerate(BULAN):
        if i < 2:
            continue   # Jan-Feb: tata letak lama, nilainya kosong
        ws = openpyxl.load_workbook(path_laporan(bulan), data_only=True)['Setoran , Penerimaan BBM']
        n_hari = calendar.monthrange(2024, i + 1)[1]
        blok = [(c, ws.cell(3, c).value.strip()) for c in range(1, ws.max_column + 1)
                if isinstance(ws.cell(3, c).value, str) and ws.cell(3, c).value.strip()]
        for n, (c, nama) in enumerate(blok):
            kel = BLOK_KELUARGA.get(nama)
            if kel is None or nama in ('PERTALITE 2', 'PERTALITE II'):
                continue   # blok Pertalite II hanya berisi '-' (LO-nya tercatat di blok Pertalite)
            end = blok[n + 1][0] if n + 1 < len(blok) else c + 9
            kol = {str(ws.cell(5, cc).value or '').strip(): cc for cc in range(c, end)}
            dolo = [cc for cc in range(c, end) if str(ws.cell(5, cc).value or '').strip() == 'DO/LO']
            c_bg, c_harga, c_pph, c_lo = kol.get('No Cek BG'), kol.get('Harga BBM'), kol.get('PPh'), kol.get('No LO')
            c_set, c_trm = dolo[0], dolo[1]
            sisa_awal[(i + 1, kel)] = angka(ws.cell(6, c_set).value) or 0
            for r in range(7, ws.max_row + 1):
                hari = ws.cell(r, c).value
                if not isinstance(hari, (int, float)) or not (1 <= int(hari) <= n_hari):
                    continue
                t = dt.date(2024, i + 1, int(hari))
                kl_set = angka(ws.cell(r, c_set).value)
                harga = angka(ws.cell(r, c_harga).value)
                pph = angka(ws.cell(r, c_pph).value)
                bg = str(ws.cell(r, c_bg).value or '').strip()
                if kl_set and kl_set > 0:
                    orders.append({'tanggal': t, 'ref': 'BG-' + bg if bg and bg.lower() not in ('online', '-') else None,
                                   'kel': kel, 'kl': kl_set, 'total': harga, 'pph': pph,
                                   'sumber': f'B/{bulan}!R{r}'})
                nomor = nomor_lo(ws.cell(r, c_lo).value)
                kl_trm = angka(ws.cell(r, c_trm).value) or 0
                if nomor:
                    per = KL_PER_LO[kel]
                    if kl_trm and abs(kl_trm - per * len(nomor)) > 0.01:
                        cat('lo_kl_beda', f'{len(nomor)} LO x {per} KL != terima {kl_trm} KL', sumber=f'B/{bulan}!R{r}')
                    for no in nomor:
                        los.append({'kel': kel, 'no_lo': no, 'kl': per, 'terima': t, 'kirim': None,
                                    'nopol': None, 'supir': None, 'sumber': f'B/{bulan}!R{r}', 'ket': ''})
                elif kl_trm:
                    cat('terima_tanpa_lo', f'{kel} {kl_trm} KL tanpa nomor LO', sumber=f'B/{bulan}!R{r}')
    return orders, los, sisa_awal


# --------------------------------------------------------------------------- C
def baca_c():
    hasil = []
    for i, bulan in enumerate(BULAN):
        ws = openpyxl.load_workbook(path_laporan(bulan), data_only=True)['Bongkaran BBM Aktual']
        n_hari = calendar.monthrange(2024, i + 1)[1]
        blok = [(c, ws.cell(3, c).value.strip()) for c in range(1, ws.max_column + 1)
                if isinstance(ws.cell(3, c).value, str) and ws.cell(3, c).value.strip()]
        for c, nama in blok:
            dombak = BLOK_DOMBAK.get(nama)
            if dombak is None:
                continue
            hari = None
            for r in range(5, 5 + 31 * 3):
                h = ws.cell(r, c).value
                if isinstance(h, (int, float)):
                    hari = int(h)
                sh = ws.cell(r, c + 1).value
                if hari is None or hari > n_hari or not isinstance(sh, (int, float)):
                    continue
                g = lambda off: ws.cell(r, c + off).value
                awal, teori, dip = angka(g(6)), angka(g(7)), angka(g(8))
                if dip is None or awal is None:
                    continue
                t = dt.date(2024, i + 1, hari)
                rencana = (teori - awal) if teori is not None else None
                terukur = dip - awal
                if terukur <= 0:
                    cat('bongkar_tidak_naik', f'{dombak} sebelum {awal} sesudah {dip}', sumber=f'C/{bulan}!R{r}', tanggal=t.isoformat())
                    continue
                kel = KELUARGA_KHUSUS.get((dombak, t.strftime('%Y-%m')), DOMBAK_KELUARGA[dombak])
                hasil.append({
                    'tanggal': t, 'urutan': int(sh), 'jam_kerja': SHIFT_NO[int(sh)], 'jam': str(g(2) or '').strip(),
                    'dombak': dombak, 'kel': kel, 'penerima': str(g(3) or '').strip(),
                    'supir': str(g(4) or '').strip(), 'nopol': rapikan_plat(plat(g(5)) or (str(g(5)).strip().upper() if g(5) else None)),
                    'sebelum': awal, 'sesudah': dip, 'rencana': rencana if rencana and rencana > 0 else terukur,
                    'terukur': terukur, 'sumber': f'C/{bulan}!R{r} ({nama})',
                })
    return hasil


MARGIN_BLOK = {'PERTAMAX': 'DM1-PX', 'PERTALITE': 'DM2-PL (Lama)', 'PERTALITE 2': 'DM3-PL (Baru)',
               'PERTALITE II': 'DM3-PL (Baru)', 'BIO SOLAR': 'DM4-BS', 'PERTAMINA DEX': 'DM6-PDX'}


def terima_margin():
    """Penerimaan harian (nominal) per (tanggal, dombak) dari tab Stock file Margin."""
    import glob
    hasil = {}
    for i, b in enumerate(BULAN):
        p = (glob.glob(os.path.join(DATA, f'Margin {b} 2024*.xlsx'))
             or glob.glob(os.path.join(DATA, f'Margin {b.upper()} 2024*.xlsx')))
        ws = openpyxl.load_workbook(p[0], data_only=True)['Stock']
        n_hari = calendar.monthrange(2024, i + 1)[1]
        for c in range(1, ws.max_column + 1):
            d = MARGIN_BLOK.get(str(ws.cell(2, c).value or '').strip())
            if d is None:
                continue
            for r in range(4, 4 + n_hari):
                h = ws.cell(r, c).value
                if isinstance(h, (int, float)) and 1 <= int(h) <= n_hari:
                    hasil[(dt.date(2024, i + 1, int(h)).isoformat(), d)] = angka(ws.cell(r, c + 2).value) or 0
    return hasil


def pilih_penerimaan(bongkar):
    """Pilih sumber penerimaan per (tanggal, dombak) memakai dipping sebagai acuan.

    Bongkaran (C) dan sheet stok (D) saling melengkapi; untuk tiap hari dihitung
    selisih = dipping akhir hari - (stok awal hari + penerimaan - penjualan stand
    meter). Sumber dengan |selisih| terkecil dipakai (seri -> Bongkaran, karena
    rinciannya lengkap: sebelum/sesudah, supir, nopol).
    """
    stok = collections.defaultdict(list)
    with open(os.path.join(OUT, 'stok_shift.csv'), encoding='utf-8') as f:
        for r in csv.DictReader(f):
            stok[(r['tanggal'], r['dombak'])].append(r)
    jual = collections.defaultdict(float)
    with open(os.path.join(OUT, 'stand_meter.csv'), encoding='utf-8') as f:
        for r in csv.DictReader(f):
            jual[(r['tanggal'], r['dombak'])] += float(r['liter'])

    # pemindahan antar tangki bukan penerimaan LO: baris Bongkaran yang ternyata pemindahan
    # (Januari) dibuang; liter masuk dikurangkan dari penerimaan sheet stok / Margin tangki
    # tujuan dan ikut diperhitungkan pada acuan dipping
    tin, tout, tin_shift = collections.defaultdict(float), collections.defaultdict(float), collections.defaultdict(float)
    for tr in TRANSFER:
        tin[(tr['tanggal'], tr['tujuan'])] += tr['liter_terima']
        tout[(tr['tanggal'], tr['asal'])] += tr['liter']
        tin_shift[(tr['tanggal'], tr['urutan'], tr['tujuan'])] += tr['liter_terima']
        cocok = [x for x in bongkar if x['tanggal'].isoformat() == tr['tanggal'] and x['dombak'] == tr['tujuan']
                 and x['jam_kerja'] == tr['jam_kerja']]
        tot = sum(x['terukur'] for x in cocok)
        if cocok and abs(tot - tr['liter_terima']) <= 0.01 * tr['liter_terima']:
            for x in cocok:
                bongkar.remove(x)
            cat('transfer_bukan_penerimaan', f"{tr['asal']} -> {tr['tujuan']} {tr['liter_terima']:.0f} L ({len(cocok)} baris bongkaran)",
                tanggal=tr['tanggal'])

    c_hari = collections.defaultdict(list)
    for x in bongkar:
        c_hari[(x['tanggal'].isoformat(), x['dombak'])].append(x)
    m_hari = terima_margin()

    def baris_baru(k, q, urutan, sumber, cs):
        t = dt.date.fromisoformat(k[0])
        kel = KELUARGA_KHUSUS.get((k[1], k[0][:7]), DOMBAK_KELUARGA[k[1]])
        return {'tanggal': t, 'urutan': urutan, 'jam_kerja': SHIFT_NO[urutan], 'jam': '', 'dombak': k[1], 'kel': kel,
                'penerima': '', 'supir': cs[0]['supir'] if cs else '', 'nopol': cs[0]['nopol'] if cs else None,
                'sebelum': None, 'sesudah': None, 'rencana': q, 'terukur': q, 'sumber': sumber}

    kunci = (set(c_hari) | {k for k, rs in stok.items() if any(float(r['penerimaan'] or 0) > 0 for r in rs)}
             | {k for k, v in m_hari.items() if v > 0 and k in stok})
    hasil, statistik = [], collections.Counter()
    for k in sorted(kunci):
        rs = sorted(stok.get(k, []), key=lambda r: r['urutan'])
        cs = c_hari.get(k, [])
        # penerimaan sheet stok per shift tanpa liter pemindahan yang masuk di shift itu
        d_rows = [(r, float(r['penerimaan'] or 0) - tin_shift.get((k[0], int(r['urutan']), k[1]), 0.0)) for r in rs]
        d_rows = [(r, q) for r, q in d_rows if q > 1]
        c_tot = sum(x['terukur'] for x in cs)
        d_tot = sum(q for _, q in d_rows)
        m_tot = m_hari.get(k, 0.0)
        masuk_tr, keluar_tr = tin.get(k, 0.0), tout.get(k, 0.0)
        if masuk_tr and m_tot >= masuk_tr - 1:
            m_tot -= masuk_tr          # Margin mencatat pemindahan sebagai penerimaan
        awal = next((float(r['stok_awal']) for r in rs if r['stok_awal']), None)
        akhir = next((float(r['dipping']) for r in reversed(rs) if r['dipping']), None)
        shift_akhir = max((int(r['urutan']) for r in rs if r['dipping']), default=int(rs[-1]['urutan']) if rs else 2)
        if rs and not rs[-1]['dipping']:
            # shift terakhir hari ini tanpa dipping (mis. 31 Mei, 30 Sep): bongkaran malam baru
            # terlihat di stok awal hari berikutnya -> pakai itu sebagai acuan akhir hari
            besok = (dt.date.fromisoformat(k[0]) + dt.timedelta(days=1)).isoformat()
            awal_besok = next((float(r['stok_awal']) for r in sorted(stok.get((besok, k[1]), []), key=lambda r: r['urutan'])
                               if r['stok_awal']), None)
            if awal_besok is not None:
                akhir = awal_besok
                shift_akhir = int(rs[-1]['urutan'])

        # kandidat: (nama, total, prioritas) -- prioritas kecil dipilih bila selisihnya setara
        kandidat = [('C', c_tot, 0)] if cs else []
        if cs and m_tot - c_tot >= 1000:
            kandidat.append(('C+M', m_tot, 1))     # bongkaran + bongkaran yang hanya tercatat di Margin
        if d_rows:
            kandidat.append(('D', d_tot, 2))
        if m_tot > 0:
            kandidat.append(('M', m_tot, 3))
        if not kandidat:
            continue
        # bongkaran yang dicatat di hari yang dippingnya tidak naik = salah tanggal/dobel
        kandidat.append(('KOSONG', 0.0, 4))
        if awal is None or akhir is None:
            pilih = kandidat[0][0]
            statistik['tanpa_dipping_' + pilih] += 1
        else:
            dasar = akhir - awal + jual.get(k, 0.0) - masuk_tr + keluar_tr
            skor = {n: abs(dasar - tot) for n, tot, _ in kandidat}
            terbaik = min(skor.values())
            # bongkaran (C) tetap dipakai kecuali sumber lain lebih cocok > 1.000 L
            pilih = min(kandidat, key=lambda c: (skor[c[0]] > terbaik + 1000, c[2]))[0]
            if terbaik > 1000:
                cat('penerimaan_meragukan', f'{k[1]} bongkaran {c_tot:.0f} / stok {d_tot:.0f} / margin {m_tot:.0f}, '
                                            f'selisih terbaik {terbaik:.0f} L', tanggal=k[0])
        statistik[pilih] += 1

        if pilih in ('C', 'C+M'):
            dipakai = list(cs)
            if pilih == 'C+M':
                dipakai.append(baris_baru(k, m_tot - c_tot, shift_akhir, f'Margin/Stock {k[0]} [sisa yang tidak ada di Bongkaran]', cs))
                cat('tambah_dari_margin', f'{k[1]} +{m_tot - c_tot:.0f} L (bongkaran {c_tot:.0f}, margin {m_tot:.0f})', tanggal=k[0])
        elif pilih == 'D':
            dipakai = [baris_baru(k, q, int(r['urutan']), r['sumber'] + ' [sheet stok]', cs) for r, q in d_rows]
            if cs:
                cat('pakai_sheet_stok', f'{k[1]} bongkaran {c_tot:.0f} L diganti sheet stok {d_tot:.0f} L', tanggal=k[0])
        elif pilih == 'KOSONG':
            dipakai = []
            cat('bongkaran_dibuang', f'{k[1]} bongkaran {c_tot:.0f} / stok {d_tot:.0f} / margin {m_tot:.0f} L, '
                                     f'dipping tidak naik', tanggal=k[0])
        else:
            dipakai = [baris_baru(k, m_tot, shift_akhir, f'Margin/Stock {k[0]}', cs)]
            cat('pakai_margin', f'{k[1]} {m_tot:.0f} L hanya dari Margin (bongkaran {c_tot:.0f}, stok {d_tot:.0f})', tanggal=k[0])

        # jumlah "seharusnya" (dasar losses bongkar): dua dari tiga sumber nominal yang sepakat
        # (sheet stok, Margin, rencana Bongkaran) -- mis. 15 Sep DM3 sheet = Margin = 2.500 vs
        # Bongkaran 2.700; 26 Nov DM3 sheet = Bongkaran = 6.000 vs Margin 8.000 (pembagian tangki
        # Margin berbeda). Tanpa kesepakatan: nominal Margin bila dalam 10% dari terukur.
        # (sumber hanya dianggap nominal bila bulat ratusan liter -- porsi LO selalu bulat; sheet
        # stok kadang berisi angka terukur, mis. 26 Jan DM1 15.879,8 = Bongkaran terukur)
        tot_ukur = sum(x['terukur'] for x in dipakai)
        c_rencana = sum(x['rencana'] for x in dipakai)
        bulat = lambda v: v > 0 and abs(v - round(v, -2)) < 0.01
        nominal = None
        if d_rows and bulat(m_tot) and abs(d_tot - m_tot) <= 1:
            nominal = m_tot
        elif d_rows and bulat(d_tot) and abs(d_tot - c_rencana) <= 1:
            nominal = None
        elif m_tot > 0 and tot_ukur > 0 and abs(m_tot - tot_ukur) <= 0.1 * m_tot:
            nominal = m_tot
        if nominal and tot_ukur > 0 and abs(nominal - tot_ukur) <= 0.2 * nominal:
            for x in dipakai:
                x['rencana'] = nominal * x['terukur'] / tot_ukur
        hasil.extend(dipakai)
    print('sumber penerimaan per hari-tangki:', dict(statistik))
    return atribusi_shift(hasil, stok, jual)


def atribusi_shift(hasil, stok, jual_hari):
    """Pindahkan penerimaan ke shift yang lonjakan dippingnya cocok.

    Bongkaran kadang dicatat di shift berbeda dari shift saat dipping sudah naik
    (mis. dibongkar akhir shift 1, dicatat shift 2). Bila dibiarkan, stok taking
    menghasilkan pasangan losses +8.000 / -8.000 L di hari yang sama. Untuk tiap
    hari-tangki dicoba semua penempatan shift; dipakai bila mengurangi total
    |selisih per shift| lebih dari 1.000 L.
    """
    import itertools
    # 11 Apr: sheet stok mencatat seluruh hari (penjualan, penerimaan, dipping) di Shift 1, jadi
    # bongkaran yang di Bongkaran tercatat Shift 2 ikut Shift 1 (kalau tidak, saldo buku Shift 1
    # negatif lalu stok taking +7.600 / -7.900 L)
    for x in hasil:
        u = SHIFT_PENERIMAAN_KOREKSI.get((x['tanggal'].isoformat(), x['urutan']))
        if u:
            cat('pindah_shift', f"{x['dombak']} {x['terukur']:.0f} L: {x['jam_kerja']} -> {SHIFT_NO[u]} "
                                f"(sheet stok mencatat seluruh hari di {SHIFT_NO[u]})", sumber=x['sumber'],
                tanggal=x['tanggal'].isoformat())
            x['urutan'], x['jam_kerja'] = u, SHIFT_NO[u]
    jual = collections.defaultdict(float)
    with open(os.path.join(OUT, 'stand_meter.csv'), encoding='utf-8') as f:
        for r in csv.DictReader(f):
            jual[(r['tanggal'], int(r['urutan']), r['dombak'])] += float(r['liter'])
    tr_adj = collections.defaultdict(float)
    for tr in TRANSFER:
        tr_adj[(tr['tanggal'], tr['urutan'], tr['tujuan'])] += tr['liter_terima']
        tr_adj[(tr['tanggal'], tr['urutan'], tr['asal'])] -= tr['liter']

    per_hari = collections.defaultdict(list)
    for x in hasil:
        per_hari[(x['tanggal'].isoformat(), x['dombak'])].append(x)
    pindah = 0
    for (t, d), xs in per_hari.items():
        rows = {int(r['urutan']): r for r in stok.get((t, d), [])}
        shifts = sorted(u for u, r in rows.items() if r['dipping'] and r['stok_awal'])
        if len(shifts) < 2 or any(x['urutan'] not in shifts for x in xs) or len(xs) > 5:
            continue

        def skor(asg):
            masuk = collections.defaultdict(float)
            for x, u in zip(xs, asg):
                masuk[u] += x['terukur']
            return sum(abs(float(rows[u]['dipping']) - (float(rows[u]['stok_awal']) + masuk[u]
                                                        + tr_adj.get((t, u, d), 0) - jual.get((t, u, d), 0)))
                       for u in shifts)

        asal = tuple(x['urutan'] for x in xs)
        terbaik = min(itertools.product(shifts, repeat=len(xs)), key=lambda a: (skor(a), a != asal))
        if terbaik != asal and skor(asal) - skor(terbaik) > 1000:
            for x, u in zip(xs, terbaik):
                if x['urutan'] != u:
                    cat('pindah_shift', f"{d} {x['terukur']:.0f} L: {x['jam_kerja']} -> {SHIFT_NO[u]} "
                                        f"(selisih {skor(asal):.0f} -> {skor(terbaik):.0f} L)", sumber=x['sumber'], tanggal=t)
                    x['urutan'], x['jam_kerja'] = u, SHIFT_NO[u]
                    pindah += 1
    print(f'penerimaan dipindah shift: {pindah}')
    return hasil


# --------------------------------------------------------------------------- pasangkan
def pasang_lo_ke_terima(los, terima):
    """Alokasi liter penerimaan ke LO per keluarga produk, urut waktu."""
    alok = []
    for kel in PRODUK:
        q = sorted([l for l in los if l['kel'] == kel], key=lambda l: (l['terima'], l['urut']))
        rs = sorted([x for x in terima if x['kel'] == kel], key=lambda x: (x['tanggal'], x['urutan'], x['dombak']))
        sisa = {id(l): l['kl'] * 1000 for l in q}
        qi = 0
        lepas = []   # LO yang dilepas dari antrian karena lama tidak dibongkar (bisa diselamatkan via nopol)
        nopol = lambda s: re.sub(r'\s+', '', str(s or '').upper())
        for x in rs:
            perlu = x['rencana']
            bagian = []
            while perlu > 0.5:
                # LO yang terlalu lama tidak diterima dilepas dari antrian
                while qi < len(q) and (x['tanggal'] - q[qi]['terima']).days > 3 and sisa[id(q[qi])] >= q[qi]['kl'] * 1000 - 0.5:
                    q[qi]['status'] = 'tanpa_penerimaan'
                    lepas.append(q[qi])
                    qi += 1
                if qi >= len(q) or (q[qi]['terima'] - x['tanggal']).days > 2:
                    # sebelum membuat LO pengganti: LO terlepas dengan truk (nopol) yang sama dalam 7 hari
                    selamat = next((l for l in lepas if sisa[id(l)] > 0.5 and abs((l['terima'] - x['tanggal']).days) <= 7
                                    and x['nopol'] and nopol(l.get('nopol')) == nopol(x['nopol'])), None)
                    if selamat is not None:
                        ambil = min(perlu, sisa[id(selamat)])
                        bagian.append((selamat, ambil))
                        sisa[id(selamat)] -= ambil
                        perlu -= ambil
                        selamat['status'] = 'diterima'
                        cat('lo_diselamatkan', f"LO {selamat['no_lo']} ({selamat['terima']}) dipasangkan ke bongkaran "
                                               f"{x['tanggal']} truk {x['nopol']}", sumber=x['sumber'], tanggal=x['tanggal'].isoformat())
                        continue
                    if bagian and perlu < 600:
                        # sisa pembulatan (mis. rencana 8.012 untuk LO 8.000): bukan LO baru,
                        # tempel ke LO terakhir supaya total rencana tetap sama dengan Excel
                        cat('sisa_pembulatan', f"{kel} {perlu:.0f} L di {x['dombak']} dibebankan ke LO terakhir",
                            sumber=x['sumber'], tanggal=x['tanggal'].isoformat())
                        lo_akhir, a_akhir = bagian[-1]
                        bagian[-1] = (lo_akhir, a_akhir + perlu)
                        perlu = 0
                        break
                    # tidak ada LO yang pantas -> LO pengganti
                    ph = {'kel': kel, 'no_lo': None, 'kl': perlu / 1000, 'terima': x['tanggal'], 'kirim': x['tanggal'],
                          'nopol': x['nopol'], 'supir': x['supir'], 'sumber': x['sumber'], 'ket': 'PENGGANTI',
                          'urut': 10 ** 9, 'status': 'pengganti'}
                    los.append(ph)
                    sisa[id(ph)] = perlu
                    bagian.append((ph, perlu))
                    cat('lo_pengganti', f"{kel} {perlu:.0f} L di {x['dombak']} tanpa LO", sumber=x['sumber'],
                        tanggal=x['tanggal'].isoformat())
                    perlu = 0
                    break
                lo = q[qi]
                ambil = min(perlu, sisa[id(lo)])
                bagian.append((lo, ambil))
                sisa[id(lo)] -= ambil
                perlu -= ambil
                lo['status'] = 'diterima'
                lo.setdefault('nopol_c', x['nopol'])
                lo.setdefault('supir_c', x['supir'])
                lo.setdefault('tgl_c', x['tanggal'])
                if sisa[id(lo)] <= 0.5:
                    qi += 1
            total = sum(a for _, a in bagian) or 1
            for lo, a in bagian:
                alok.append({'x': x, 'lo': lo, 'rencana': a, 'terukur': x['terukur'] * a / total})
        for l in q[qi:]:
            if sisa[id(l)] >= l['kl'] * 1000 - 0.5:
                l.setdefault('status', 'tanpa_penerimaan')
    return alok


def kapasitas_carry_over(orders, los, sisa_awal):
    """Sisa order Desember 2023 per produk, diturunkan dari sheet Setoran.

    Baris pertama sheet Setoran tiap bulan = KL yang sudah dibayar tapi belum
    terkirim di akhir bulan sebelumnya. Untuk tiap akhir bulan m:
        sisa_sheet(m) = carry_over + order(<=m) - LO(<=m)
    sehingga carry_over = median(sisa_sheet(m) - order(<=m) + LO(<=m)).
    """
    hasil, per_bulan = {}, {}
    for kel in PRODUK:
        calon = {}
        for m in range(2, 12):
            if (m + 1, kel) not in sisa_awal:
                continue
            akhir = dt.date(2024, m, calendar.monthrange(2024, m)[1])
            o = sum(x['kl'] for x in orders if x['kel'] == kel and x['tanggal'] <= akhir)
            l = sum(x['kl'] for x in los if x['kel'] == kel and x['terima'] <= akhir)
            calon[m] = sisa_awal[(m + 1, kel)] - o + l
        urut = sorted(calon.values())
        med = urut[len(urut) // 2] if urut else 0
        # minimal: LO yang tiba sebelum order pertama tahun ini
        det = sorted(x['tanggal'] for x in orders if x['kel'] == kel)
        awal = sum(x['kl'] for x in los if x['kel'] == kel and (not det or x['terima'] < det[0]))
        per = KL_PER_LO[kel]
        hasil[kel] = max(round(max(med, 0) / per) * per, awal)
        per_bulan[kel] = calon
        print(f'carry-over 2023 {kel}: {hasil[kel]:.0f} KL (kebutuhan per akhir bulan {[(m, round(v)) for m, v in calon.items()]})')
    return hasil, per_bulan


def order_pelengkap(carry, per_bulan, tebus_pada):
    """Pesanan yang sudah dibayar tetapi tidak tercatat di sheet Setoran.

    Bila kebutuhan per akhir bulan (lihat kapasitas_carry_over) melebihi carry-over
    secara MENETAP sampai akhir tahun (selubung bawah dari bulan itu ke depan), selisihnya
    dicatat sebagai order pelengkap tanggal 1 bulan itu. Lonjakan sesaat diabaikan.
    """
    from penjualan import PORSI_PPH
    hasil = []
    for kel, calon in per_bulan.items():
        per = KL_PER_LO[kel]
        bulan = sorted(calon)
        sudah = 0
        for i, m in enumerate(bulan):
            selubung = min(calon[k] - carry.get(kel, 0) for k in bulan[i:])
            perlu = max(0, round(selubung / per) * per)
            if perlu > sudah:
                kl = perlu - sudah
                t = dt.date(2024, m, 1)
                acuan = tebus_pada(kel, t)
                total = acuan * kl * 1000
                p = pecah_pajak(kel, total, total * PORSI_PPH[PRODUK[kel]])
                hasil.append({'tanggal': t, 'ref': f"PELENGKAP-{t:%Y%m}", 'kel': kel, 'kl': float(kl), 'pajak': p,
                              'sumber': 'pelengkap (sisa order sheet Setoran)', 'pelengkap': True})
                cat('order_pelengkap', f"{kel} {kl} KL {t:%Y-%m}: kebutuhan {selubung + carry.get(kel, 0):.0f} KL > carry-over "
                                       f"{carry.get(kel, 0):.0f} + order tercatat; Rp {p['total']:,.0f}", tanggal=t.isoformat())
                sudah = perlu
    return hasil


def pasang_order(orders, los, carry):
    """FIFO per keluarga: LO mengisi kapasitas order terlama lebih dulu,
    diawali kapasitas sisa order Desember 2023 (carry-over)."""
    for kel in PRODUK:
        det = sorted([o for o in orders if o['kel'] == kel], key=lambda o: (o['tanggal'], o['urut']))
        if carry.get(kel):
            det.insert(0, {'kode': 'CARRY-OVER-2023', 'kel': kel, 'kl': carry[kel], 'tanggal': dt.date(2023, 12, 31),
                           'urut': -1, 'sumber': 'carry-over', 'carry': True})
        ls = sorted([l for l in los if l['kel'] == kel], key=lambda l: (l['terima'], l['urut']))
        kap = {id(o): o['kl'] for o in det}
        di = 0
        for l in ls:
            butuh = l['kl']
            while di < len(det) and kap[id(det[di])] <= 1e-6:
                di += 1
            if not det or l['terima'] < det[0]['tanggal']:
                l['order'] = 'CARRY-OVER-2023'
                continue
            if di >= len(det) or (det[di]['tanggal'] - l['terima']).days > 3:
                # order yang tersisa masih jauh di depan: LO kelebihan, tempel ke order
                # terakhir yang sudah dibayar sebelum LO ini
                lalu = [o for o in det if o['tanggal'] <= l['terima']]
                o = lalu[-1]
                l['order'] = o['kode']
                o.setdefault('lo', []).append(l)
                kap[id(o)] -= butuh
                cat('lo_melebihi_order', f"LO {l['no_lo']} {kel} {l['kl']} KL ditempel ke {o['kode']}",
                    sumber=l['sumber'], tanggal=l['terima'].isoformat())
                continue
            o = det[di]
            l['order'] = o['kode']
            o.setdefault('lo', []).append(l)
            kap[id(o)] -= butuh
            if kap[id(o)] < -1e-6:
                # satu LO tidak bisa dibelah: kelebihannya mengurangi kapasitas order berikutnya
                # supaya sisa order (baris belum terkirim) tetap sama dengan selisih kumulatif
                lebih = lebih_awal = -kap[id(o)]
                kap[id(o)] = 0.0
                for nxt in det[di + 1:]:
                    if lebih <= 1e-6:
                        break
                    ambil = min(lebih, kap[id(nxt)])
                    kap[id(nxt)] -= ambil
                    lebih -= ambil
                kap[id(o)] = -lebih
                if lebih_awal >= 0.5:
                    cat('order_kelebihan_lo', f"order {o['kode']} {kel} {o['kl']:g} KL menerima LO lebih; "
                                              f"kelebihan mengurangi order berikutnya", sumber=o['sumber'])
        for o in det:
            o['kl_terkirim'] = o['kl'] - max(kap[id(o)], 0)
            if o.get('carry'):
                o['jumlah_lo'] = len(o.get('lo', []))


def main():
    a_orders, a_los, tarif = baca_a()
    b_orders, b_los, sisa_awal = baca_b()
    terima = pilih_penerimaan(baca_c())

    # tarif satuan per (bulan, produk) dari baris B yang punya nilai -> untuk baris tanpa nilai
    tarif_b = collections.defaultdict(collections.Counter)
    for o in b_orders:
        if o['total'] and o['pph'] and o['kl']:
            tarif_b[(o['tanggal'].month, o['kel'])][(round(o['total'] / o['kl'], 2), round(o['pph'] / o['kl'], 2))] += 1
    for o in b_orders:
        if not o['total']:
            t = tarif_b.get((o['tanggal'].month, o['kel']))
            if not t:
                for dm in (-1, 1, -2, 2, -3, 3):
                    t = tarif_b.get((o['tanggal'].month + dm, o['kel']))
                    if t:
                        break
            if t:
                tot_kl, pph_kl = t.most_common(1)[0][0]
                o['total'], o['pph'] = tot_kl * o['kl'], pph_kl * o['kl']
                cat('nilai_dari_tarif', f"{o['kel']} {o['kl']} KL x {tot_kl:,.2f}/KL", sumber=o['sumber'])

    # ---- order: Jan-Feb dari A, Mar-Des dari B (kode dari A bila cocok)
    a_idx = collections.defaultdict(list)
    for o in a_orders:
        a_idx[(o['tanggal'], o['kel'])].append(o)
    orders = []
    for o in a_orders:
        if o['tanggal'].month <= 2:
            tar = tarif.get((o['tanggal'].month, o['kel'])) or tarif.get((3, o['kel']))
            net, ppn, ppbkb, pph, tot = tar.most_common(1)[0][0] if tar else (None,) * 5
            if tot is None:
                cat('order_tanpa_tarif', f"{o['ref']} {o['kel']}", sumber=o['sumber'])
                continue
            k = o['kl']
            orders.append({**o, 'pajak': {'net': round(net * k), 'ppn': round(ppn * k), 'ppbkb': round(ppbkb * k),
                                          'pph': round(pph * k), 'total': round(tot * k)}})
    # harga tebus harian Excel (termasuk PPh) dan harga jual - margin tetap = acuan nilai faktur per liter
    from penjualan import tebus_excel, harga_margin, PORSI_PPH, MARGIN_TETAP
    tebus = tebus_excel()
    jual_harian = harga_margin()

    def tebus_pada(kel, tanggal):
        produk = PRODUK[kel]
        t = tanggal.isoformat()
        kandidat = sorted((d, v) for (d, p), v in tebus.items() if p == produk and d <= t and d[:7] >= t[:7])
        if not kandidat:
            kandidat = sorted((d, v) for (d, p), v in tebus.items() if p == produk)
            kandidat = [x for x in kandidat if x[0] >= t] or kandidat
            return kandidat[0][1] if kandidat else None
        return kandidat[-1][1]

    # Informasi Detail Produk kadang mengulang nomor TRX order sebelumnya pada order di tanggal
    # lain. Pemilik nomor = order di tanggal yang tertanam di nomor TRX (TRX-YYYYMMDDhhmm...),
    # selain itu kemunculan pertama; order lain dengan nomor yang sama dicatat tanpa nomor
    ref_pertama = {}
    for o in sorted(a_orders, key=lambda o: o['tanggal']):
        if o['ref'] and not o['ref'].startswith('A-'):
            ref_pertama.setdefault(o['ref'], o['tanggal'])
    for ref in ref_pertama:
        m = re.match(r'TRX-(\d{4})(\d{2})(\d{2})', ref)
        if m:
            try:
                t = dt.date(int(m.group(1)), int(m.group(2)), int(m.group(3)))
            except ValueError:
                continue
            if t.year == 2024 and any(o['tanggal'] == t for o in b_orders + a_orders if o['ref'] in (None, ref)):
                ref_pertama[ref] = t
    for o in b_orders:
        cocok = a_idx.get((o['tanggal'], o['kel']))
        if cocok and not o['ref']:
            ref = cocok[0]['ref']
            if ref_pertama.get(ref, o['tanggal']) != o['tanggal']:
                cat('ref_ganda', f"{ref} sudah dipakai order {ref_pertama[ref]}; order {o['tanggal']} {o['kel']} {o['kl']:g} KL "
                                 f"dicatat tanpa nomor TRX", sumber=o['sumber'], tanggal=o['tanggal'].isoformat())
            else:
                o['ref'] = ref
        if not o['total']:
            cat('order_tanpa_nilai', f"{o['kel']} {o['kl']} KL", sumber=o['sumber'])
            continue
        p = pecah_pajak(o['kel'], o['total'], o['pph'])
        if p is None:
            cat('order_tanpa_pph', f"{o['kel']} {o['kl']} KL total {o['total']}", sumber=o['sumber'])
            continue
        # KL dibenarkan oleh LO & dipping; bila nilai bayar tidak sesuai KL (mis. 24 KL dibayar
        # senilai 16 KL), nilai faktur dihitung ulang dari KL x harga tebus Excel hari itu
        acuan = tebus_pada(o['kel'], o['tanggal'])
        if acuan:
            per_liter = (p['total'] - p['pph']) / (o['kl'] * 1000)
            pokok_acuan = acuan * (1 - PORSI_PPH[PRODUK[o['kel']]])
            jual = jual_harian.get((o['tanggal'].isoformat(), PRODUK[o['kel']]))
            pokok_margin = jual - MARGIN_TETAP[PRODUK[o['kel']]] if jual else None
            wajar = abs(per_liter - pokok_acuan) <= 0.015 * pokok_acuan or (
                pokok_margin is not None and abs(per_liter - pokok_margin) <= 0.015 * pokok_margin)
            if not wajar:
                total_baru = acuan * o['kl'] * 1000
                pph_baru = total_baru * PORSI_PPH[PRODUK[o['kel']]]
                p_baru = pecah_pajak(o['kel'], total_baru, pph_baru)
                cat('nilai_disesuaikan', f"{o['kel']} {o['kl']} KL: Rp {p['total']:,.0f} ({per_liter:,.1f}/L) -> "
                                         f"Rp {p_baru['total']:,.0f} ({pokok_acuan:,.1f}/L)", sumber=o['sumber'])
                p = p_baru
            else:
                # nilai wajar, tetapi PPh di sheet kadang milik baris lain (Mei N36: PPh 12 KL di baris
                # 8 KL); PPh dipakai memecah pajak -> diselaraskan dengan porsi PPh faktur bila meleset > 5%
                harap = p['total'] * PORSI_PPH[PRODUK[o['kel']]]
                if abs(p['pph'] - harap) > 0.05 * harap:
                    cat('pph_disesuaikan', f"{o['kel']} {o['kl']:g} KL: PPh {p['pph']:,.0f} -> {harap:,.0f}",
                        sumber=o['sumber'], tanggal=o['tanggal'].isoformat())
                    p = pecah_pajak(o['kel'], p['total'], harap)
        orders.append({**o, 'pajak': p})

    # kelompokkan detail menjadi rencana: per ref; tanpa ref -> per tanggal
    grup = collections.OrderedDict()
    for n, o in enumerate(sorted(orders, key=lambda o: (o['tanggal'], o['sumber']))):
        o['urut'] = n
        kunci = o['ref'] or f"ONLINE-{o['tanggal']:%Y%m%d}"
        o['kode'] = kunci
        grup.setdefault(kunci, []).append(o)

    # ---- LO: Jan-Feb dari A, Mar-Des dari B, dilengkapi data A (nomor LO yang muncul lebih
    # dari sekali di A: dipakai baris yang tanggalnya paling dekat dengan sheet Setoran)
    a_semua = collections.defaultdict(list)
    for l in a_los:
        a_semua[l['no_lo']].append(l)
    los, sudah = [], set()
    for l in a_los:
        if l['terima'] and l['terima'].month <= 2 and l['no_lo'] not in sudah:
            los.append(dict(l))
            sudah.add(l['no_lo'])
    for l in b_los:
        if l['no_lo'] in sudah:
            cat('lo_ganda', f"{l['no_lo']} sudah tercatat", sumber=l['sumber'])
            continue
        calon = a_semua.get(l['no_lo'], [])
        a = min(calon, key=lambda x: abs(((x['terima'] or x['kirim'] or l['terima']) - l['terima']).days)) if calon else None
        if len(calon) > 1:
            cat('lo_ganda_a', f"{l['no_lo']} muncul {len(calon)}x di Detail Produk; dipakai {a['sumber']}", sumber=l['sumber'])
        if a:
            l.update({'nopol': a['nopol'], 'supir': a['supir'], 'kirim': a['kirim']})
            if a.get('terima') and abs((a['terima'] - l['terima']).days) <= 10:
                # tanggal di sheet Setoran = tanggal LO terbit; Detail Produk punya tanggal terima sebenarnya
                l['terima'] = a['terima']
        los.append(l)
        sudah.add(l['no_lo'])
    for n, l in enumerate(los):
        l['urut'] = n

    alok = pasang_lo_ke_terima(los, terima)
    carry, per_bulan = kapasitas_carry_over(orders, los, sisa_awal)
    tambahan = order_pelengkap(carry, per_bulan, tebus_pada)
    for o in tambahan:
        o['urut'] = len(orders)
        o['kode'] = o['ref']
        orders.append(o)
        grup.setdefault(o['kode'], []).append(o)
    pasang_order(orders, los, carry)

    # ---- tulis
    rencana_rows = []
    for kode, ds in grup.items():
        for o in ds:
            p = o['pajak']
            rencana_rows.append({'kode': kode, 'tanggal': o['tanggal'].isoformat(), 'produk': PRODUK[o['kel']],
                                 'kl': fmt(o['kl'], 3), 'kl_terkirim': fmt(o.get('kl_terkirim', 0), 3),
                                 'net': p['net'], 'ppn': p['ppn'], 'ppbkb': p['ppbkb'], 'pph': p['pph'],
                                 'total': p['total'], 'no_cek_bg': kode[3:] if kode.startswith('BG-') else 'Online',
                                 'sumber': o['sumber']})
    tulis_csv('order.csv', ['kode', 'tanggal', 'produk', 'kl', 'kl_terkirim', 'net', 'ppn', 'ppbkb', 'pph', 'total',
                            'no_cek_bg', 'sumber'], rencana_rows)

    # LO pengganti (bongkaran tanpa nomor LO di Excel): 'TL-NA0' + 4 digit acak; NA = nomor
    # perlu direvisi (keputusan pemilik data). Acak deterministik supaya hasil extractor stabil.
    import random
    n_ph = 0
    dipakai = {l['no_lo'] for l in los if l['no_lo']}
    lo_rows = []
    for l in los:
        if l['no_lo'] is None:
            n_ph += 1
            acak = random.Random(f"{l['terima']}-{l['kel']}-{n_ph}")
            while True:
                nomor = f"TL-NA0{acak.randint(0, 9999):04d}"
                if nomor not in dipakai:
                    break
            dipakai.add(nomor)
            l['no_lo'] = nomor
        lo_rows.append({'no_lo': l['no_lo'], 'produk': PRODUK[l['kel']], 'kl': fmt(l['kl'], 3),
                        'tanggal_terima': l['terima'].isoformat(),
                        'tanggal_kirim': (l.get('kirim') or l.get('tgl_c') or l['terima']).isoformat(),
                        'no_pol': l.get('nopol') or l.get('nopol_c') or '', 'supir': l.get('supir') or l.get('supir_c') or '',
                        'kode_order': l.get('order', ''), 'status': l.get('status', 'tanpa_penerimaan'),
                        'sumber': l['sumber']})
    tulis_csv('lo.csv', ['no_lo', 'produk', 'kl', 'tanggal_terima', 'tanggal_kirim', 'no_pol', 'supir', 'kode_order',
                         'status', 'sumber'], lo_rows)

    pen_rows = []
    for a in alok:
        x = a['x']
        pen_rows.append({'tanggal': x['tanggal'].isoformat(), 'jam_kerja': x['jam_kerja'], 'urutan': x['urutan'],
                         'jam': x['jam'], 'dombak': x['dombak'], 'no_lo': a['lo']['no_lo'],
                         'jumlah_rencana': fmt(a['rencana'], 3), 'jumlah_terukur': fmt(a['terukur'], 3),
                         'sebelum_terukur': fmt(x['sebelum'], 3), 'sesudah_terukur': fmt(x['sesudah'], 3),
                         'penerima': x['penerima'], 'supir': x['supir'], 'no_pol': x['nopol'] or '', 'sumber': x['sumber']})
    pen_rows.sort(key=lambda r: (r['tanggal'], r['urutan'], r['dombak'], r['no_lo']))
    tulis_csv('penerimaan.csv', ['tanggal', 'jam_kerja', 'urutan', 'jam', 'dombak', 'no_lo', 'jumlah_rencana',
                                 'jumlah_terukur', 'sebelum_terukur', 'sesudah_terukur', 'penerima', 'supir', 'no_pol',
                                 'sumber'], pen_rows)
    for c in catatan:
        c.setdefault('tanggal', '')
        c.setdefault('sumber', '')
    tulis_csv('pengadaan_catatan.csv', ['jenis', 'tanggal', 'sumber', 'keterangan'], catatan)
    tulis_csv('transfer.csv', ['tanggal', 'jam_kerja', 'urutan', 'jam', 'asal', 'tujuan', 'produk', 'liter', 'liter_terima'], TRANSFER)

    # ---- ringkasan
    print(f"order detail: {len(orders)} dalam {len(grup)} rencana | LO: {len(los)} | penerimaan (alokasi): {len(alok)}")
    print('catatan:', dict(collections.Counter(c['jenis'] for c in catatan)))
    print('status LO:', dict(collections.Counter(l.get('status', 'tanpa_penerimaan') for l in los)))
    print('LO ke order carry-over:', collections.Counter(l['kel'] for l in los if l.get('order') == 'CARRY-OVER-2023'))
    print('\nbulan    kel   order_KL   LO_KL   terima_rencana_KL  terima_terukur_KL')
    per = collections.defaultdict(lambda: [0.0, 0.0, 0.0, 0.0])
    for o in orders:
        per[(o['tanggal'].strftime('%Y-%m'), o['kel'])][0] += o['kl']
    for l in los:
        per[(l['terima'].strftime('%Y-%m'), l['kel'])][1] += l['kl']
    for a in alok:
        per[(a['x']['tanggal'].strftime('%Y-%m'), a['x']['kel'])][2] += a['rencana'] / 1000
        per[(a['x']['tanggal'].strftime('%Y-%m'), a['x']['kel'])][3] += a['terukur'] / 1000
    for k in sorted(per):
        o, l, r, m = per[k]
        print(f"{k[0]}  {k[1]}  {o:>8.0f} {l:>7.0f} {r:>12.1f} {m:>12.1f}")
    # cek: sisa order belum terkirim di akhir bulan (FIFO) vs baris pertama sheet Setoran bulan berikutnya
    print('\nsisa order akhir bulan: hitungan FIFO vs sheet Setoran (baris awal bulan berikutnya)')
    for kel in PRODUK:
        baris = []
        for m in range(2, 12):
            akhir = dt.date(2024, m, calendar.monthrange(2024, m)[1])
            dipesan = carry.get(kel, 0) + sum(o['kl'] for o in orders if o['kel'] == kel and o['tanggal'] <= akhir)
            terkirim = sum(l['kl'] for l in los if l['kel'] == kel and l['terima'] <= akhir)
            baris.append(f"{m:02d}:{dipesan - terkirim:+.0f}/{sisa_awal.get((m + 1, kel), 0):.0f}")
        print(f'  {kel}: ' + '  '.join(baris))


if __name__ == '__main__':
    main()
