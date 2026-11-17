"""Utilitas bersama extractor data historis (Excel -> CSV ternormalisasi).

Tahun dipilih lewat variabel lingkungan IMPOR_TAHUN (default 2024):
    IMPOR_TAHUN=2025 python standmeter.py
Semua keputusan pemetaan (dikonfirmasi pemilik data, 2026-11-10) dipusatkan di
sini supaya extractor lain tidak menebak sendiri.
"""
import csv
import datetime as dt
import glob
import os
import re

TAHUN = int(os.environ.get('IMPOR_TAHUN', '2024'))
ROOT = os.path.abspath(os.path.join(os.path.dirname(__file__), '..', '..'))
DATA = os.path.join(ROOT, 'docs', 'data', str(TAHUN))
OUT = os.path.join(ROOT, 'storage', 'app', f'impor-{TAHUN}')
# folder CSV tahun sebelumnya (sambungan saldo/sisa order)
OUT_SEBELUM = os.path.join(ROOT, 'storage', 'app', f'impor-{TAHUN - 1}')

BULAN = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli',
         'Agustus', 'September', 'Oktober', 'November', 'Desember']
# periode impor per tahun (keputusan pemilik data: 2026 = Januari s/d Oktober; November masih berjalan)
BULAN_TERAKHIR = {2026: 10}
BULAN = BULAN[:BULAN_TERAKHIR.get(TAHUN, 12)]

# "B2" di Excel = shift 3 (22.00-06.00).
SHIFT = {'Shift 1': 'Shift 1', 'Shift 2': 'Shift 2', 'B2': 'Shift 3', 'Shift 3': 'Shift 3'}
URUTAN_SHIFT = {'Shift 1': 1, 'Shift 2': 2, 'Shift 3': 3}

# Kode nozzle Excel -> nama nozzle di DB. Kode lama (Jan-Sep) digabung ke nozzle
# penerusnya karena angka totalisatornya bersambung saat ganti mesin 11-12 Sep 2024.
NOZZLE = {
    'PP1': 'N-1A PL', 'PLA': 'N-1A PL',
    'PP2': 'N-1C PL', 'PLB': 'N-1C PL',
    'PP4': 'N-1B PX', 'PX1': 'N-1B PX',
    'PX2': 'N-1D PX',
    'PL': 'N-2A PL', 'PL1': 'N-2A PL',
    'PL2': 'N-2B PL',
    'PS1': 'N-3A BS', 'BS1': 'N-3A BS',
    'BS2': 'N-3C BS',
    'DEX': 'N-3B PDX', 'DEX1': 'N-3B PDX',
    'DEX2': 'N-3D PDX',
    # 'DLT' (Dexlite) tidak pernah terjual -> dilewati; lihat standmeter.py
}

# Dombak untuk data 2024 (berbeda dari dombak_payung saat ini untuk nozzle
# Pertalite; stand_meter.dombak_id disimpan per baris).
DOMBAK_NOZZLE = {
    'N-1A PL': 'DM2-PL (Lama)', 'N-1C PL': 'DM2-PL (Lama)',
    'N-2A PL': 'DM3-PL (Baru)', 'N-2B PL': 'DM3-PL (Baru)',
    'N-1B PX': 'DM1-PX', 'N-1D PX': 'DM1-PX',
    'N-3A BS': 'DM4-BS', 'N-3C BS': 'DM4-BS',
    'N-3B PDX': 'DM6-PDX', 'N-3D PDX': 'DM6-PDX',
}

# Blok produk di sheet stok / bongkaran / setoran -> dombak.
DOMBAK_BLOK = {
    'PERTAMAX': 'DM1-PX',
    'PERTALITE': 'DM2-PL (Lama)',
    'PERTALITE 2': 'DM3-PL (Baru)', 'PERTALITE II': 'DM3-PL (Baru)',
    'BIO SOLAR': 'DM4-BS',
    'PERTAMINA DEX': 'DM6-PDX',
    # 'DEXLITE' tidak punya dombak di DB -> dilewati
}

PRODUK_DOMBAK = {
    'DM1-PX': 'Pertamax', 'DM2-PL (Lama)': 'Pertalite', 'DM3-PL (Baru)': 'Pertalite',
    'DM4-BS': 'Bio Solar', 'DM5-BS': 'Bio Solar', 'DM6-PDX': 'Pertamina Dex',
}

# Ejaan nama bulan di nama file yang menyimpang (mis. 'Laporan SPBU Febuari 2025.xlsx')
EJAAN_BULAN = {'Februari': ['Februari', 'Febuari', 'Pebruari']}


def _cari(awalan, bulan):
    for b in EJAAN_BULAN.get(bulan, [bulan]):
        for nama in (b, b.upper()):
            p = sorted(glob.glob(os.path.join(DATA, f'{awalan} {nama} {TAHUN}*.xlsx')))
            if p:
                return p[0]
    raise FileNotFoundError(f'{awalan} {bulan} {TAHUN} tidak ada di {DATA}')


def path_laporan(bulan):
    return _cari('Laporan SPBU', bulan)


def path_margin(bulan):
    return _cari('Margin', bulan)


def path_harian():
    """'LAPORAN HARIAN SPBU 20 Per <tahun>.xlsx': meter per tanggal/shift/mesin (sumber stand meter 2026)."""
    p = sorted(glob.glob(os.path.join(DATA, f'LAPORAN HARIAN SPBU*{TAHUN}*.xlsx')))
    if not p:
        raise FileNotFoundError(f'LAPORAN HARIAN SPBU {TAHUN} tidak ada di {DATA}')
    return p[0]


def ada_laporan_bulanan():
    """2024-2025 punya 'Laporan SPBU <Bulan>' (totalisator, stok per shift, bongkaran); 2026 tidak."""
    return bool(glob.glob(os.path.join(DATA, f'Laporan SPBU * {TAHUN}*.xlsx')))


JUDUL_STOCK = {'stok awal': 'awal', 'penerimaan': 'terima', 'tera': 'tera', 'penjualan': 'jual',
               'stok ahir teoritis': 'teoritis', 'stok ahir dipping': 'dipping'}


def kolom_stock(ws, c):
    """Offset kolom per blok tangki di tab Stock Margin dari judul baris 3. Mulai Juli 2026 ada kolom
    'Tera' sesudah 'Penerimaan' sehingga Penjualan/Dipping bergeser satu kolom."""
    hasil = {}
    for off in range(1, 10):
        nama = JUDUL_STOCK.get(str(ws.cell(3, c + off).value or '').strip().lower())
        if nama and nama not in hasil:
            hasil[nama] = off
        if str(ws.cell(3, c + off).value or '').strip().lower() == 'tanggal':
            break
    hasil.setdefault('awal', 1)
    hasil.setdefault('terima', 2)
    hasil.setdefault('jual', 3)
    hasil.setdefault('dipping', 5)
    return hasil


def pecah_dm45(rumus):
    """Dipping Biosolar ditulis '=DM4+DM5' (mis. '=6895+4227.9'); kembalikan (dm4, dm5).
    Tiga suku ('=14167+16091.2+2000'): suku pertama DM4, sisanya DM5. Satu angka: DM4 saja.
    None bila sel bukan rumus penjumlahan angka."""
    if not isinstance(rumus, str):
        return None
    m = re.fullmatch(r'=\s*([\d.]+(?:\s*\+\s*[\d.]+)*)\s*', rumus)
    if not m:
        return None
    suku = [float(x) for x in re.split(r'\s*\+\s*', m.group(1))]
    return suku[0], sum(suku[1:])


def angka(v):
    """Angka Excel -> float. Menerima teks ber-pemisah ribuan ('8.342.370', '  134,082')."""
    if v is None:
        return None
    if isinstance(v, bool):
        return None
    if isinstance(v, (int, float)):
        return float(v)
    s = str(v).strip().replace(' ', '')
    if s in ('', '-'):
        return None
    if re.fullmatch(r'\d{1,3}(\.\d{3})+', s):
        return float(s.replace('.', ''))
    if re.fullmatch(r'\d{1,3}(,\d{3})+', s):
        return float(s.replace(',', ''))
    if re.fullmatch(r'-?\d+(,\d+)?', s):
        return float(s.replace(',', '.'))
    try:
        return float(s)
    except ValueError:
        return None


def tanggal(v):
    if isinstance(v, dt.datetime):
        return v.date()
    if isinstance(v, dt.date):
        return v
    return None


def tulis_csv(nama, kolom, baris):
    os.makedirs(OUT, exist_ok=True)
    p = os.path.join(OUT, nama)
    with open(p, 'w', newline='', encoding='utf-8') as f:
        w = csv.DictWriter(f, fieldnames=kolom, extrasaction='ignore')
        w.writeheader()
        for b in baris:
            w.writerow(b)
    return p


def fmt(x, d=6):
    return '' if x is None else f'{x:.{d}f}'
