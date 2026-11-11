"""Utilitas bersama extractor data historis 2024 (Excel -> CSV ternormalisasi).

Semua keputusan pemetaan (dikonfirmasi pemilik data, 2026-11-10) dipusatkan di
sini supaya extractor lain tidak menebak sendiri.
"""
import csv
import datetime as dt
import os
import re

ROOT = os.path.abspath(os.path.join(os.path.dirname(__file__), '..', '..'))
DATA = os.path.join(ROOT, 'docs', 'data', '2024')
OUT = os.path.join(ROOT, 'storage', 'app', 'impor-2024')

BULAN = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli',
         'Agustus', 'September', 'Oktober', 'November', 'Desember']

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
    'DM4-BS': 'Bio Solar', 'DM6-PDX': 'Pertamina Dex',
}


def path_laporan(bulan):
    return os.path.join(DATA, f'Laporan SPBU {bulan} 2024.xlsx')


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
