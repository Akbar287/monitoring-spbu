import { usePage } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '../../Layouts/AppLayout';

// Generator laporan Margin: pilih SPBU/produk/bulan/jenis laporan lalu unduh.
// Unduhan memakai navigasi GET biasa (bukan kunjungan Inertia) karena server
// merespons file Excel — header X-Inertia justru akan merusaknya.
export default function Margin({ title, breadcrumbs = [], action, options, selected }) {
    const { urls } = usePage().props;
    const [spbu, setSpbu] = useState(selected.spbu ?? '');
    const [produk, setProduk] = useState(String(selected.produk ?? 'all'));
    const [month, setMonth] = useState(selected.month ?? '');
    const [typeReport, setTypeReport] = useState(String(selected.typeReport ?? '1'));

    const unduh = (e) => {
        e.preventDefault();
        const params = new URLSearchParams({ produk_id: produk, month, type_report_id: typeReport });
        if (spbu) params.set('spbu_id', spbu);
        window.location.href = action + '?' + params.toString();
    };

    const field = (label, control) => (
        <div className="form-group row align-items-center">
            <label className="col-md-4 col-form-label text-md-right">{label}</label>
            <div className="col-md-7">{control}</div>
        </div>
    );

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
                    <div className="breadcrumb-item">{title}</div>
                </div>
            </div>

            <div className="section-body">
                <div className="row justify-content-center">
                    <div className="col-12 col-lg-8">
                        <div className="card">
                            <div className="card-header">
                                <h4>
                                    <i className="fas fa-file-excel text-success mr-2"></i> Unduh Laporan Margin
                                </h4>
                            </div>
                            <div className="card-body">
                                <form onSubmit={unduh}>
                                    {field(
                                        'SPBU',
                                        <select className="custom-select" value={spbu} onChange={(e) => setSpbu(e.target.value)}>
                                            <option value="">— Semua SPBU —</option>
                                            {options.spbu.map((o) => (
                                                <option key={String(o.value)} value={String(o.value)}>
                                                    {o.label}
                                                </option>
                                            ))}
                                        </select>
                                    )}
                                    {field(
                                        'Produk',
                                        <select className="custom-select" value={produk} onChange={(e) => setProduk(e.target.value)}>
                                            <option value="all">Semua Produk</option>
                                            {options.produk.map((o) => (
                                                <option key={String(o.value)} value={String(o.value)}>
                                                    {o.label}
                                                </option>
                                            ))}
                                        </select>
                                    )}
                                    {field(
                                        'Bulan',
                                        <input type="month" className="form-control" value={month} onChange={(e) => setMonth(e.target.value)} />
                                    )}
                                    {field(
                                        'Jenis Laporan',
                                        <select className="custom-select" value={typeReport} onChange={(e) => setTypeReport(e.target.value)}>
                                            {options.typeReport.map((o) => (
                                                <option key={String(o.value)} value={String(o.value)}>
                                                    {o.label}
                                                </option>
                                            ))}
                                        </select>
                                    )}

                                    <div className="form-group row">
                                        <div className="col-md-7 offset-md-4">
                                            <button type="submit" className="btn btn-success">
                                                <i className="fas fa-download mr-1"></i> Unduh Laporan
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
