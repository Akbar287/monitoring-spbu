import { router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '../../Layouts/AppLayout';
import { PageHeader, QualityTable, date, fmt } from './common';

function Approval({ when, name, operator }) {
    return <div className="mb-2"><span className={`badge badge-${when ? 'success' : 'warning'} mr-2`}>{when ? 'Disetujui' : 'Menunggu Validasi'}</span>
        <span>Dicatat oleh: <b>{operator || '—'}</b></span>
        {when && <div className="mt-1">Disetujui oleh <b>{name}</b> · {new Date(when).toLocaleString('id-ID', { timeZone: 'Asia/Jakarta' })}</div>}
    </div>;
}

export default function Show({ title, record, bolehMengubahPagi, bolehMencatat, bolehMenyetujui, peringatan, baseUrl }) {
    const { errors = {} } = usePage().props;
    const [processing, setProcessing] = useState(false);
    const url = `${baseUrl}/${record.kualitas_minyak_id}`;
    const approve = (tahap, penerimaan_id = null) => {
        const label = tahap === 'pagi' ? 'Pagi' : tahap === 'penerimaan' ? 'Penerimaan' : 'Akhir';
        if (window.confirm(`Setujui density ${label} ini? Hanya pemeriksaan ini yang akan dikunci.`)) {
            router.post(`${url}/setujui`, { tahap, penerimaan_id }, { preserveScroll: true, onStart: () => setProcessing(true), onFinish: () => setProcessing(false) });
        }
    };
    const editLink = (tahap, row = null) => `${url}/edit?${new URLSearchParams({ tahap, ...(row ? { penerimaan_id: row.id } : {}) })}`;
    return <AppLayout title={title}><PageHeader title={title} baseUrl={baseUrl}>
        <button className="btn btn-light" onClick={() => window.print()}>Cetak Formulir</button>
        {bolehMencatat && <a className="btn btn-primary" href={editLink('penerimaan')}>+ Tambah Penerimaan</a>}
    </PageHeader>
        <p>Setiap density memiliki persetujuan sendiri. Pagi yang sudah disetujui tetap mengizinkan penambahan Penerimaan dan Akhir pada catatan ini.</p>
        {Object.values(errors).map((error,i) => <div className="alert alert-danger" role="alert" key={i}>{error}</div>)}
        {peringatan.length > 0 && <div className="alert alert-danger"><strong>Temuan pemeriksaan · Kriteria penolakan penerimaan</strong><ul className="mb-0">{peringatan.map((p,i) => <li key={i}>{p}</li>)}</ul></div>}
        <div className="card"><div className="card-body">
            <div className="km-meta"><span>No. SPBU: <b>{record.nomor_spbu}</b></span><span>Produk: <b>{record.nama_produk}</b></span><span>No. Tangki / Dombak: <b>{record.nama_dombak}</b></span><span>Tanggal: <b>{date(record.tanggal)}</b></span></div>
            <QualityTable records={[record]} detail />
        </div></div>
        <div className="card"><div className="card-body"><h2 className="km-section-title">Density Pagi</h2>
            <Approval when={record.disetujui_pada} name={record.nama_penyetuju} operator={record.nama_operator} />
            <p className="mb-2" style={{ whiteSpace: 'pre-wrap' }}>Catatan Pagi: {record.catatan || '—'}</p>
            <div className="km-actions d-print-none" style={{ justifyContent: 'flex-start' }}>
                {bolehMengubahPagi && <a className="btn btn-primary" href={editLink('pagi')}>Ubah Density Pagi</a>}
                {bolehMenyetujui && !record.disetujui_pada && <button className="btn btn-success" disabled={processing} onClick={() => approve('pagi')}>Setujui Density Pagi</button>}
            </div>
        </div></div>
        {record.penerimaan.length === 0 && <div className="alert alert-info">Belum ada penerimaan pada catatan ini. Penerimaan dapat ditambahkan meskipun density Pagi sudah disetujui.</div>}
        {record.penerimaan.map((p,i) => <div className="card" key={p.id}><div className="card-body">
            <h2 className="km-section-title">Penerimaan {i + 1} · {p.nomor_mobil_tangki} · PNBP {p.nomor_pnbp}</h2>
            <Approval when={p.penerimaan_disetujui_pada} name={p.penerimaan_nama_penyetuju} operator={p.penerimaan_nama_operator} />
            <p>Truk {p.truk_berwenang ? 'berwenang' : 'tidak berwenang'}
                {p.volume_pnbp != null && <> · Volume PNBP: {fmt(p.volume_pnbp,3)} liter · Diterima: {fmt(p.volume_diterima,3)} liter · Selisih: {fmt((Number(p.volume_diterima) - Number(p.volume_pnbp)) / Number(p.volume_pnbp) * 100,3)}%</>}
            </p>
            <p style={{ whiteSpace: 'pre-wrap' }}>Catatan Penerimaan: {p.catatan_penerimaan || '—'}</p>
            <div className="km-actions d-print-none" style={{ justifyContent: 'flex-start' }}>
                {p.boleh_ubah_penerimaan && <a className="btn btn-primary" href={editLink('penerimaan',p)}>Ubah Density Penerimaan</a>}
                {bolehMenyetujui && !p.penerimaan_disetujui_pada && <button className="btn btn-success" disabled={processing} onClick={() => approve('penerimaan',p.id)}>Setujui Density Penerimaan</button>}
            </div>
            <hr /><h3 className="km-section-title">Density Akhir · Dua jam setelah penerimaan {i + 1}</h3>
            {p.waktu_akhir ? <><Approval when={p.akhir_disetujui_pada} name={p.akhir_nama_penyetuju} operator={p.akhir_nama_operator} />
                <p style={{ whiteSpace: 'pre-wrap' }}>Catatan Akhir: {p.catatan_akhir || '—'}</p></> : <p>Belum diisi. Pemeriksaan Akhir dapat diisi setelah dua jam, meskipun Penerimaan sudah disetujui.</p>}
            <div className="km-actions d-print-none" style={{ justifyContent: 'flex-start' }}>
                {p.boleh_ubah_akhir && <a className="btn btn-primary" href={editLink('akhir',p)}>{p.waktu_akhir ? 'Ubah Density Akhir' : 'Isi Density Akhir'}</a>}
                {bolehMenyetujui && p.waktu_akhir && !p.akhir_disetujui_pada && <button className="btn btn-success" disabled={processing} onClick={() => approve('akhir',p.id)}>Setujui Density Akhir</button>}
            </div>
        </div></div>)}
        <p className="text-muted">Pagi dan penerimaan: ambil sampel, ukur density dan suhu. Pemeriksaan pasca penerimaan paling cepat setelah dua jam. Satu kaleng sampel per kompartemen.</p>
        <p className="text-muted">Tolak penerimaan jika truk tidak berwenang, selisih density &gt; 0,005 g/cm³, atau selisih volume &gt; 0,15%.</p>
    </AppLayout>;
}
