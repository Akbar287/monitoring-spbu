import { router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '../../Layouts/AppLayout';
import { PageHeader, Pagination, fmt } from './common';

export default function Astm({ title, filters, rows, manifest, baseUrl }) {
    const [density, setDensity] = useState(filters.density ?? '');
    const [suhu, setSuhu] = useState(filters.suhu ?? '');
    const { errors = {} } = usePage().props;
    return <AppLayout title={title}><PageHeader title={title} baseUrl={baseUrl} />
        <div className="alert alert-info">Density reduction to 15 °C · Sumber: {manifest.sumber}. Nilai sesuai sel PDF, tanpa interpolasi. Density dalam g/cm³; suhu dalam °C.</div>
        <div className="card"><div className="card-body">
            <form data-no-inertia className="form-inline mb-3" style={{ gap: 12 }} onSubmit={e => { e.preventDefault(); router.get(`${baseUrl}/astm`, { ...(density !== '' ? { density } : {}), ...(suhu !== '' ? { suhu } : {}) }); }}>
                <label htmlFor="density">Density observed</label><input id="density" className="form-control" type="number" min="0.690" max="0.899" step="0.001" value={density} onChange={e => setDensity(e.target.value)} />
                <label htmlFor="suhu">Suhu</label><input id="suhu" className="form-control" type="number" min="0" max="50" step="0.5" value={suhu} onChange={e => setSuhu(e.target.value)} />
                <button className="btn btn-primary">Cari</button><a className="btn btn-light" href={`${baseUrl}/astm`}>Reset</a>
            </form>
            {Object.values(errors).map((e,i) => <p role="alert" className="text-danger" key={i}>{e}</p>)}
            <div className="table-responsive"><table className="table table-bordered"><thead><tr><th>Density observed (g/cm³)</th><th>Suhu (°C)</th><th>Density std 15 °C (g/cm³)</th><th>Halaman sumber</th></tr></thead>
                <tbody>{rows.data.map(r => <tr key={r.id}><td>{fmt(r.density_observed,3)}</td><td>{fmt(r.suhu,1)}</td><td>{fmt(r.density_15)}</td><td>{r.halaman}</td></tr>)}
                    {!rows.data.length && <tr><td colSpan="4">Pasangan density/suhu tidak tersedia pada sumber.</td></tr>}</tbody></table></div>
            <Pagination data={rows} />
        </div></div>
        <div className="card"><div className="card-body"><h2 className="km-section-title">Kelengkapan sumber</h2><ul>{manifest.catatan.map(n => <li key={n}>{n}</li>)}
            <li>Sel 0,691 / 25 °C tidak digunakan karena halaman 1 dan 2 memberikan nilai berbeda.</li></ul>
            <p className="mb-0">{manifest.jumlah_sel.toLocaleString('id-ID')} pasangan density/suhu diimpor. Tabel ini menjadi acuan otomatis formulir.</p>
        </div></div>
    </AppLayout>;
}
