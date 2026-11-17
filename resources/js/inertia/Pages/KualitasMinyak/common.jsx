import { router } from '@inertiajs/react';
import './kualitas.css';

export const fmt = (value, places = 4) => value === null || value === undefined || value === '' ? '—' : Number(value).toFixed(places);
export const date = (value) => value ? value.slice(0, 10).split('-').reverse().join('/') : '—';
export const time = (value) => value ? value.replace('T', ' ').slice(11, 16) : '—';
export const datetime = (value) => value ? `${date(value)} ${time(value)}` : '—';

export function PageHeader({ title, baseUrl, children }) {
    return <div className="section-header km-header"><h1>{title}</h1><div className="km-actions d-print-none">
        <a className="btn btn-light" href="/tera">Tera</a>
        {baseUrl && <a className="btn btn-light" href={baseUrl}>Kualitas Minyak</a>}{children}
    </div></div>;
}

export function Pagination({ data }) {
    return <div className="d-flex justify-content-between align-items-center flex-wrap mt-3 d-print-none" style={{ gap: 8 }}>
        <span>{data.total} data · Halaman {data.current_page} dari {data.last_page}</span>
        <div className="btn-group">
            <button className="btn btn-light" disabled={!data.prev_page_url} onClick={() => router.get(data.prev_page_url)}>Sebelumnya</button>
            <button className="btn btn-light" disabled={!data.next_page_url} onClick={() => router.get(data.next_page_url)}>Berikutnya</button>
        </div>
    </div>;
}

export function QualityTable({ records, baseUrl, detail = false }) {
    return <div className="table-responsive km-table-wrap"><table className="table table-bordered km-table">
        <thead>
            <tr>{!detail && <th rowSpan="3">Tanggal / Identitas</th>}
                <th colSpan="5">Awal (pagi hari dari nozzle / sebelum penerimaan)</th>
                <th colSpan="8">Penerimaan (dari mobil tangki)</th><th colSpan="4">Akhir (pasca penerimaan)</th>
                {!detail && <th rowSpan="3" className="d-print-none">Aksi</th>}
            </tr>
            <tr><th rowSpan="2">Jam</th><th rowSpan="2">Density observed<br />g/cm³</th><th rowSpan="2">Suhu observed<br />°C</th>
                <th rowSpan="2">Density std 15 °C<br />g/cm³</th><th rowSpan="2">Tinggi air tangki pendam<br />cm</th>
                <th rowSpan="2">Tanggal / Jam</th><th rowSpan="2">No. Mobil Tangki</th><th rowSpan="2">No. PNBP</th>
                <th>a</th><th colSpan="3">b</th><th>c = b − a</th><th rowSpan="2">Tanggal / Jam</th>
                <th rowSpan="2">Density observed<br />g/cm³</th><th rowSpan="2">Suhu observed<br />°C</th><th rowSpan="2">Density std 15 °C<br />g/cm³</th></tr>
            <tr><th>Density std 15 °C PNBP<br />g/cm³</th><th>Density observed<br />g/cm³</th><th>Suhu observed<br />°C</th>
                <th>Density std 15 °C<br />g/cm³</th><th>Selisih density<br />g/cm³</th></tr>
        </thead>
        <tbody>{records.map((r) => (r.penerimaan.length ? r.penerimaan : [null]).map((p, index) => <tr key={`${r.kualitas_minyak_id}-${index}`}>
            {!detail && <td><strong>{date(r.tanggal)}</strong><br />SPBU {r.nomor_spbu}<br />{r.nama_dombak}<br />{r.nama_produk}</td>}
            <td>{r.jam_awal?.slice(0, 5)}</td><td>{fmt(r.density_awal, 3)}</td><td>{fmt(r.suhu_awal, 1)}</td>
            <td>{fmt(r.density_15_awal)}</td><td>{fmt(r.tinggi_air, 2)}</td>
            <td>{datetime(p?.waktu_penerimaan)}</td><td>{p?.nomor_mobil_tangki || '—'}</td><td>{p?.nomor_pnbp || '—'}</td>
            <td>{fmt(p?.density_15_pnbp)}</td><td>{fmt(p?.density_penerimaan, 3)}</td><td>{fmt(p?.suhu_penerimaan, 1)}</td>
            <td>{fmt(p?.density_15_penerimaan)}</td><td className={Math.abs(Number(p?.selisih_density)) > .00500001 ? 'text-danger font-weight-bold' : ''}>{fmt(p?.selisih_density)}</td>
            <td>{datetime(p?.waktu_akhir)}</td><td>{fmt(p?.density_akhir, 3)}</td><td>{fmt(p?.suhu_akhir, 1)}</td><td>{fmt(p?.density_15_akhir)}</td>
            {!detail && <td className="d-print-none"><a className="btn btn-sm btn-info" href={`${baseUrl}/${r.kualitas_minyak_id}`}>Detail</a></td>}
        </tr>))}{records.length === 0 && <tr><td colSpan={19} className="text-center py-5">Belum ada catatan sesuai filter.</td></tr>}</tbody>
    </table></div>;
}

export function PemeriksaanTable({ records, riwayat, baseUrl }) {
    const rows = [];
    for (const r of records) {
        const add = (tahap, waktu, density, suhu, hasil, when, name, operator, belum = false) => {
            if (Boolean(when) === riwayat) rows.push({ record: r, tahap, waktu, density, suhu, hasil, when, name, operator, belum });
        };
        add('Pagi', `${r.tanggal} ${r.jam_awal}`, r.density_awal, r.suhu_awal, r.density_15_awal, r.disetujui_pada, r.nama_penyetuju, r.nama_operator);
        r.penerimaan.forEach((p, i) => {
            add(`Penerimaan ${i + 1}`, p.waktu_penerimaan, p.density_penerimaan, p.suhu_penerimaan, p.density_15_penerimaan, p.penerimaan_disetujui_pada, p.penerimaan_nama_penyetuju, p.penerimaan_nama_operator);
            add(`Akhir ${i + 1}`, p.waktu_akhir, p.density_akhir, p.suhu_akhir, p.density_15_akhir, p.akhir_disetujui_pada, p.akhir_nama_penyetuju, p.akhir_nama_operator, !p.waktu_akhir);
        });
    }
    return <div className="table-responsive"><table className="table table-bordered">
        <thead><tr><th>Tanggal / SPBU</th><th>Dombak / Produk</th><th>Pemeriksaan</th><th>Waktu Ukur</th><th>Density observed (g/cm³)</th><th>Suhu (°C)</th><th>Density std 15 °C (g/cm³)</th><th>Status / Pencatat</th><th>Aksi</th></tr></thead>
        <tbody>{rows.map((p,i) => <tr key={i}>
            <td>{date(p.record.tanggal)}<br />SPBU {p.record.nomor_spbu}</td><td>{p.record.nama_dombak}<br />{p.record.nama_produk}</td>
            <td><b>{p.tahap}</b></td><td>{datetime(p.waktu)}</td><td>{fmt(p.density,3)}</td><td>{fmt(p.suhu,1)}</td><td>{fmt(p.hasil)}</td>
            <td><span className={`badge badge-${p.when ? 'success' : p.belum ? 'light' : 'warning'}`}>{p.when ? 'Disetujui' : p.belum ? 'Belum diisi' : 'Menunggu Validasi'}</span>
                <br />{p.operator || '—'}{p.when && <div>Disetujui: {p.name}<br />{new Date(p.when).toLocaleString('id-ID', { timeZone: 'Asia/Jakarta' })}</div>}</td>
            <td><a className="btn btn-info btn-sm" href={`${baseUrl}/${p.record.kualitas_minyak_id}`}>Buka Catatan</a></td>
        </tr>)}{!rows.length && <tr><td colSpan="9" className="text-center py-5">Belum ada pemeriksaan sesuai filter.</td></tr>}</tbody>
    </table></div>;
}
