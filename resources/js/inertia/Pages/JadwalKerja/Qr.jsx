import { router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '../../Layouts/AppLayout';

export default function Qr({ title, breadcrumbs = [], backUrl, filters, members = [], statuses = [], items = [] }) {
    const { urls, flash } = usePage().props;
    const [tanggal, setTanggal] = useState(filters?.tanggal || '');
    const [member, setMember] = useState(filters?.member || 'all');
    const [status, setStatus] = useState(filters?.status_kehadiran_id || 'all');

    const submit = (e) => {
        e.preventDefault();
        router.get(
            window.location.pathname,
            { tanggal, member, status_kehadiran_id: status },
            { preserveScroll: true }
        );
    };

    return (
        <AppLayout title={title}>
            <div className="section-header">
                <h1>{title}</h1>
                <div className="section-header-breadcrumb">
                    <div className="breadcrumb-item">
                        <a href={urls.home}>Dashboard</a>
                    </div>
                    {breadcrumbs.map((b) => (
                        <div key={b.label} className="breadcrumb-item">
                            {b.url ? <a href={b.url}>{b.label}</a> : b.label}
                        </div>
                    ))}
                    <div className="breadcrumb-item">QR Code</div>
                </div>
            </div>

            <div className="section-body">
                {flash?.msg && <div dangerouslySetInnerHTML={{ __html: flash.msg }} />}
                <div className="card">
                    <div className="card-header">
                        <h4>Filter QR Code</h4>
                    </div>
                    <div className="card-body">
                        <form onSubmit={submit}>
                            <div className="row">
                                <div className="col-12 col-md-3 mb-2">
                                    <input type="date" className="form-control" value={tanggal} onChange={(e) => setTanggal(e.target.value)} />
                                </div>
                                <div className="col-12 col-md-3 mb-2">
                                    <select className="custom-select" value={status} onChange={(e) => setStatus(e.target.value)}>
                                        <option value="all">Semua Status Kehadiran</option>
                                        {statuses.map((s) => (
                                            <option key={s.value} value={s.value}>
                                                {s.label}
                                            </option>
                                        ))}
                                    </select>
                                </div>
                                <div className="col-12 col-md-3 mb-2">
                                    <select className="custom-select" value={member} onChange={(e) => setMember(e.target.value)}>
                                        <option value="all">Semua Pegawai</option>
                                        {members.map((m) => (
                                            <option key={m.value} value={m.value}>
                                                {m.label}
                                            </option>
                                        ))}
                                    </select>
                                </div>
                                <div className="col-12 col-md-3 mb-2">
                                    <button type="submit" className="btn btn-success btn-block">
                                        <i className="fas fa-filter mr-1"></i> Filter
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div className="row justify-content-center">
                    {items.length === 0 ? (
                        <div className="col-12">
                            <div className="card">
                                <div className="card-body">
                                    <p className="mb-0">Tidak ada QR Code pada tanggal terpilih.</p>
                                </div>
                            </div>
                        </div>
                    ) : (
                        items.map((item) => (
                            <div key={item.barcode} className="col-12 col-sm-6 col-md-4 col-lg-4 col-xl-3">
                                <div className="card" style={{ overflowX: 'auto' }}>
                                    <div className="card-header">
                                        <h4>{item.nama}</h4>
                                    </div>
                                    <div className="card-body" dangerouslySetInnerHTML={{ __html: item.qr }} />
                                    <div className="card-footer">
                                        <table className="table-hover">
                                            <tbody>
                                                <tr>
                                                    <th>Tanggal / Jam Kerja</th>
                                                    <td>{item.tanggalJam}</td>
                                                </tr>
                                                <tr>
                                                    <th>Jabatan</th>
                                                    <td>{item.jabatan}</td>
                                                </tr>
                                                <tr>
                                                    <th>Validasi Akhir</th>
                                                    <td>{item.finish}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        ))
                    )}
                </div>

                <div className="card">
                    <div className="card-header">
                        <h4>Pilihan</h4>
                    </div>
                    <div className="card-body">
                        <a href={backUrl} className="btn btn-primary">
                            <i className="fas fa-arrow-left mr-1"></i> Kembali
                        </a>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
