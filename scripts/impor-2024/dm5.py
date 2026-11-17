"""Pecah stok Biosolar menjadi DM4 (terhubung nozzle) dan DM5 (tangki simpan).

Sejak Agustus 2024 dipping Biosolar di sheet "Stock BBM Di Dombak" ditulis '=DM4+DM5'
(stok.py menyimpannya di kolom dipping_dm4 / dipping_dm5). DM5 tidak terhubung nozzle:
BBM masuk dari bongkaran lalu dipindah ke DM4 sebelum dijual (keputusan pemilik data).

Per shift yang punya dipping DM5:
- bongkaran bertanda (BS2) di sheet Bongkaran masuk DM5; tanpa tanda, bongkaran terbesar
  dimasukkan ke DM5 selama kenaikan stok DM5 belum tertutup;
- transfer DM5 -> DM4 = stok buku DM5 + masuk DM5 - dipping DM5 (negatif = DM4 -> DM5) bila
  >= AMBANG_TRANSFER; selisih lebih kecil = variasi ukur, jadi losses stok taking DM5;
- pembacaan DM5 pertama tanpa saldo awal (2024: 31 Jan Shift 3, sebelumnya isi DM5 tercampur
  di angka gabungan) = pemindahan pembuka DM4 -> DM5 sebesar dipping DM5 saat itu.

Masukan : stok_shift.csv, penerimaan.csv, transfer.csv (hasil stok.py & pengadaan.py)
Keluaran: stok_dombak.csv (per tangki, termasuk DM5), penerimaan.csv (dombak DM5 untuk
          bongkaran ke DM5), transfer.csv (+ transfer DM4/DM5), dm5_catatan.csv
Jalankan sesudah pengadaan.py dan sebelum impor tahap stok.
"""
import collections
import csv
import os

from umum import OUT, OUT_SEBELUM, fmt, tulis_csv

DM4, DM5 = 'DM4-BS', 'DM5-BS'
# perubahan DM5 lebih kecil dari ini = variasi pengukuran dipping (stok taking DM5), bukan pemindahan
AMBANG_TRANSFER = 200.0


def baca(nama, folder=OUT):
    p = os.path.join(folder, nama)
    return list(csv.DictReader(open(p, encoding='utf-8'))) if os.path.exists(p) else []


def f(v):
    return float(v) if v not in (None, '') else None


def main():
    stok = baca('stok_shift.csv')
    pen = baca('penerimaan.csv')
    transfer = [t for t in baca('transfer.csv') if not (t['asal'] in (DM4, DM5) and t['tujuan'] in (DM4, DM5))]
    catatan = []

    bs = sorted([r for r in stok if r['dombak'] == DM4], key=lambda r: (r['tanggal'], int(r['urutan'])))
    terima = collections.defaultdict(list)
    for p in pen:
        if p['dombak'] in (DM4, DM5):
            p['dombak'] = DM4
            terima[(p['tanggal'], int(p['urutan']))].append(p)

    # stok DM5 awal tahun: rumus stok awal baris pertama, atau dipping DM5 terakhir tahun lalu
    b_buku = 0.0
    if bs and f(bs[0].get('stok_awal_dm5')) is not None:
        b_buku = f(bs[0]['stok_awal_dm5'])
    else:
        lalu = [r for r in baca('stok_dombak.csv', OUT_SEBELUM) if r['dombak'] == DM5 and r['dipping']]
        if lalu:
            b_buku = f(sorted(lalu, key=lambda r: (r['tanggal'], int(r['urutan'])))[-1]['dipping'])
    b_awal = b_buku
    dipakai = b_buku > 0
    mulai = dipakai

    baris_dm5 = []
    n_tr = 0
    for r in bs:
        k = (r['tanggal'], int(r['urutan']))
        rs = terima.get(k, [])
        b = f(r.get('dipping_dm5'))
        if b is not None:
            dipakai = True
        if not dipakai:
            continue
        ke5 = [p for p in rs if p.get('ke_dm5') == '1']
        stok_awal5 = b_buku
        if b is not None and not mulai:
            # pembukaan DM5: isi DM5 selama ini tercampur di angka gabungan (buku DM4)
            masuk5 = sum(f(p['jumlah_terukur']) for p in ke5)
            buka = b - b_buku - masuk5
            if abs(buka) > 0.05:
                transfer.append({'tanggal': r['tanggal'], 'jam_kerja': r['jam_kerja'], 'urutan': r['urutan'], 'jam': '',
                                 'asal': DM4, 'tujuan': DM5, 'produk': 'Bio Solar', 'liter': fmt(buka, 3),
                                 'liter_terima': fmt(buka, 3)})
                n_tr += 1
            catatan.append({'jenis': 'pembukaan_dm5', 'tanggal': r['tanggal'], 'jam_kerja': r['jam_kerja'],
                            'keterangan': f'dipping DM5 pertama {b:.1f} L: {buka:.1f} L dipindah dari buku DM4'})
            mulai = True
            b_buku = b
        else:
            if b is not None and not ke5:
                # tanpa tanda (BS2): bongkaran terbesar ke DM5 selama kenaikan DM5 belum tertutup
                naik = b - b_buku
                for p in sorted(rs, key=lambda p: -f(p['jumlah_terukur'])):
                    if sum(f(x['jumlah_terukur']) for x in ke5) >= naik - 300:
                        break
                    ke5.append(p)
            masuk5 = sum(f(p['jumlah_terukur']) for p in ke5)
            if b is None:
                b_buku += masuk5
            else:
                t = b_buku + masuk5 - b
                if abs(t) >= AMBANG_TRANSFER:
                    asal, tujuan = (DM5, DM4) if t > 0 else (DM4, DM5)
                    transfer.append({'tanggal': r['tanggal'], 'jam_kerja': r['jam_kerja'], 'urutan': r['urutan'], 'jam': '',
                                     'asal': asal, 'tujuan': tujuan, 'produk': 'Bio Solar', 'liter': fmt(abs(t), 3),
                                     'liter_terima': fmt(abs(t), 3)})
                    n_tr += 1
                    if t < 0:
                        catatan.append({'jenis': 'transfer_ke_dm5', 'tanggal': r['tanggal'], 'jam_kerja': r['jam_kerja'],
                                        'keterangan': f'DM5 naik {b - stok_awal5:.1f} L, bongkaran ke DM5 {masuk5:.1f} L -> DM4 ke DM5 {-t:.1f} L'})
                b_buku = b   # selisih < ambang: losses stok taking DM5
        for p in ke5:
            p['dombak'] = DM5
        # baris DM4: dipping = suku DM4; tanpa rumus, gabungan dikurangi buku DM5
        d = f(r['dipping'])
        if f(r.get('dipping_dm4')) is not None:
            r['dipping'] = r['dipping_dm4']
        elif d is not None:
            r['dipping'] = fmt(d - b_buku)
            catatan.append({'jenis': 'dipping_tanpa_rumus', 'tanggal': r['tanggal'], 'jam_kerja': r['jam_kerja'],
                            'keterangan': f'dipping gabungan {d:.1f}, DM5 buku {b_buku:.1f} -> DM4 {d - b_buku:.1f}'})
        if f(r.get('stok_awal_dm4')) is not None:
            r['stok_awal'] = r['stok_awal_dm4']
        elif f(r['stok_awal']) is not None and stok_awal5:
            # stok awal gabungan tanpa rumus (Margin 1 Jan 2026: 27.408,2 = DM4 14.827 + DM5 12.581,2)
            r['stok_awal'] = fmt(f(r['stok_awal']) - stok_awal5)
        baris_dm5.append({'tanggal': r['tanggal'], 'jam_kerja': r['jam_kerja'], 'urutan': r['urutan'], 'dombak': DM5,
                          'stok_awal': fmt(stok_awal5) if not baris_dm5 else '', 'penerimaan': fmt(masuk5),
                          'penjualan_excel': '0', 'penjualan_stand_meter': '0',
                          'dipping': fmt(b) if b is not None else '', 'sumber': r['sumber'] + ' [suku DM5]'})

    hasil = stok + baris_dm5
    hasil.sort(key=lambda r: (r['tanggal'], int(r['urutan']), r['dombak']))
    kolom = ['tanggal', 'jam_kerja', 'urutan', 'dombak', 'stok_awal', 'penerimaan', 'penjualan_excel',
             'penjualan_stand_meter', 'dipping', 'sumber', 'tera']
    tulis_csv('stok_dombak.csv', kolom, hasil)
    tulis_csv('penerimaan.csv', list(pen[0].keys()) if pen else [], pen)
    transfer.sort(key=lambda t: (t['tanggal'], int(t['urutan'])))
    tulis_csv('transfer.csv', ['tanggal', 'jam_kerja', 'urutan', 'jam', 'asal', 'tujuan', 'produk', 'liter', 'liter_terima'], transfer)
    tulis_csv('dm5_catatan.csv', ['jenis', 'tanggal', 'jam_kerja', 'keterangan'], catatan)

    ke5 = [p for p in pen if p['dombak'] == DM5]
    print(f'DM5: stok awal {b_awal:.1f} L, {len(baris_dm5)} shift, bongkaran ke DM5 {len(ke5)} baris '
          f'({sum(f(p["jumlah_terukur"]) for p in ke5):,.0f} L), transfer DM4/DM5 {n_tr}')
    print('catatan:', dict(collections.Counter(c['jenis'] for c in catatan)))


if __name__ == '__main__':
    main()
