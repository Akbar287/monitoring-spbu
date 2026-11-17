#!/usr/bin/env python3
"""Ekstrak nilai PDF yang diberikan, tanpa rumus perkiraan. Memerlukan pdftotext."""
import csv
import hashlib
import json
import re
import subprocess
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
SOURCE = ROOT / 'docs/data/astm_d53b_table_for_oil_density.pdf'
DEST = ROOT / 'database/data/astm_d53.csv'


def main():
    content = subprocess.check_output(['pdftotext', '-layout', str(SOURCE), '-'], text=True)
    cells, conflicts = {}, {}
    for page, text in enumerate(content.split('\f'), 1):
        if not text.strip():
            continue
        headers = [re.findall(r'0\.\d{3}(?!\d)', line) for line in text.splitlines()]
        densities = next((v for v in headers if len(v) == 10), None)
        if not densities:
            raise ValueError(f'Header density tidak ditemukan di halaman {page}')
        count = 0
        for line in text.splitlines():
            match = re.match(r'^\s*(\d{1,2}\.\d)\s+(.*)$', line)
            if not match:
                continue
            values = re.findall(r'0\.\s*\d{4}', match[2])
            if not values:  # sel kosong pada sumber tidak diisi
                continue
            if len(values) != 10:
                raise ValueError(f'Jumlah kolom salah di halaman {page}: {line}')
            count += 1
            for density, value in zip(densities, values):
                key = (density, match[1])
                cell = {'density_15': re.sub(r'\s', '', value), 'halaman': page}
                if key in cells and cells[key]['density_15'] != cell['density_15']:
                    conflicts[str(key)] = [cells[key], cell]
                cells[key] = cell
        if count not in (21, 51):
            raise ValueError(f'Jumlah baris tidak diharapkan pada halaman {page}: {count}')
    with DEST.open('w', newline='') as handle:
        writer = csv.writer(handle)
        writer.writerow(['density_observed', 'suhu', 'density_15', 'halaman'])
        for key, cell in sorted(cells.items(), key=lambda kv: (float(kv[0][0]), float(kv[0][1]))):
            if str(key) not in conflicts:
                writer.writerow([*key, cell['density_15'], cell['halaman']])
    manifest = {
        'sumber': SOURCE.name, 'sha256': hashlib.sha256(SOURCE.read_bytes()).hexdigest(),
        'jumlah_sel': len(cells) - len(conflicts), 'sel_konflik_tidak_diimpor': conflicts,
        'catatan': ['Density 0.760–0.799 tidak ada pada PDF.',
                    'Density 0.690–0.699 pada suhu di bawah 15 C kosong.',
                    'Density 0.870–0.899 hanya tersedia pada suhu 25–50 C.',
                    'Nilai disalin apa adanya; tidak ada interpolasi atau ekstrapolasi.'],
    }
    DEST.with_suffix('.json').write_text(json.dumps(manifest, indent=2, ensure_ascii=False) + '\n')
    print(json.dumps(manifest, indent=2, ensure_ascii=False))


if __name__ == '__main__':
    main()
