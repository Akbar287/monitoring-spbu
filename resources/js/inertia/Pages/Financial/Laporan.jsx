import { router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '../../Layouts/AppLayout';

// Halaman laporan keuangan generik (Buku Besar, Neraca Saldo, Laba Rugi, dst).
// Props dari controller:
//   title, breadcrumbs: [{label,url?}], action: url GET laporan,
//   filters: [{name, label, type: 'select'|'date'|'month', options?: [{value,label}],
//              value, allowAll?: bool (select dengan opsi "Semua")}]
//   summary?: [{label, value}]          // ringkasan di atas tabel
//   tables?: [{title?, columns: [..], alignRight?: [idx..],
//              rows: [[..] | {cells:[..], bold?:true}]}]
//   notice?: {text, color}
// Filter dikirim sebagai kunjungan GET Inertia -> server merender ulang halaman
// yang sama beserta data laporannya.
export default function Laporan({ title, breadcrumbs = [], action, filters = [], summary = [], tables = [], notice }) {
    const { urls } = usePage().props;
    const [values, setValues] = useState(Object.fromEntries(filters.map((f) => [f.name, f.value ?? ''])));

    const tampilkan = (e) => {
        e.preventDefault();
        const params = Object.fromEntries(Object.entries(values).filter(([, v]) => v !== '' && v !== null));
        router.get(action, params, { preserveState: true, preserveScroll: true });
    };

    const renderFilter = (f) => {
        const onChange = (e) => setValues({ ...values, [f.name]: e.target.value });
        if (f.type === 'select') {
            return (
                <select className="custom-select" value={String(values[f.name] ?? '')} onChange={onChange}>
                    {f.allowAll && <option value="">— Semua —</option>}
                    {(f.options || []).map((o) => (
                        <option key={String(o.value)} value={String(o.value)}>
                            {o.label}
                        </option>
                    ))}
                </select>
            );
        }
        return <input type={f.type || 'date'} className="form-control" value={values[f.name] ?? ''} onChange={onChange} />;
    };

    const cellsOf = (row) => (Array.isArray(row) ? row : row.cells);
    const isBold = (row) => !Array.isArray(row) && row.bold;

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
                <div className="row">
                    <div className="col-12 col-lg-4">
                        <div className="card">
                            <div className="card-header">
                                <h4>
                                    <i className="fas fa-filter mr-2"></i> Filter
                                </h4>
                            </div>
                            <div className="card-body">
                                <form onSubmit={tampilkan}>
                                    {filters.map((f) => (
                                        <div className="form-group" key={f.name}>
                                            <label>{f.label}</label>
                                            {renderFilter(f)}
                                        </div>
                                    ))}
                                    <button type="submit" className="btn btn-primary btn-block">
                                        <i className="fas fa-search mr-1"></i> Tampilkan
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div className="col-12 col-lg-8">
                        {notice && (
                            <div className={`alert alert-${notice.color || 'info'}`}>{notice.text}</div>
                        )}
                        {summary.length > 0 && (
                            <div className="card">
                                <div className="card-body py-3">
                                    <div className="row">
                                        {summary.map((s) => (
                                            <div className="col-6 col-md-4 mb-2" key={s.label}>
                                                <div className="text-muted small">{s.label}</div>
                                                <div className="font-weight-bold">{s.value}</div>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            </div>
                        )}
                        {tables.map((t, ti) => (
                            <div className="card" key={t.title || ti}>
                                {t.title && (
                                    <div className="card-header">
                                        <h4>{t.title}</h4>
                                    </div>
                                )}
                                <div className="card-body p-0">
                                    <div className="table-responsive">
                                        <table className="table table-striped table-sm mb-0">
                                            <thead>
                                                <tr>
                                                    {t.columns.map((c, ci) => (
                                                        <th key={c} className={(t.alignRight || []).includes(ci) ? 'text-right' : ''}>
                                                            {c}
                                                        </th>
                                                    ))}
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {t.rows.length === 0 && (
                                                    <tr>
                                                        <td colSpan={t.columns.length} className="text-center text-muted py-4">
                                                            Tidak ada data pada periode ini.
                                                        </td>
                                                    </tr>
                                                )}
                                                {t.rows.map((row, ri) => (
                                                    <tr key={ri} className={isBold(row) ? 'font-weight-bold bg-light' : ''}>
                                                        {cellsOf(row).map((cell, ci) => (
                                                            <td key={ci} className={(t.alignRight || []).includes(ci) ? 'text-right' : ''}>
                                                                {cell}
                                                            </td>
                                                        ))}
                                                    </tr>
                                                ))}
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
