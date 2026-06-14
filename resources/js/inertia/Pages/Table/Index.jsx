import { router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '../../Layouts/AppLayout';
import DataTable from '../../Components/DataTable';

// Halaman index generik (list + DataTable server-side Yajra).
// Props dari controller:
//   title, lead?, cardTitle?, breadcrumbs?: [{label, url?}],
//   createUrl?, createLabel?,
//   headerActions?: [{label, url, color?, icon?}],
//   dateFilter?: {mulai, selesai}   // nilai aktif filter waktu (Y-m-d)
//   selectFilters?: [{name, label, value, options: [{value,label}]}]
//   table: { url, columns: [{label,data,...}] }
export default function Index({
    title,
    lead,
    cardTitle,
    breadcrumbs = [],
    createUrl,
    createLabel = 'Tambah',
    headerActions = [],
    dateFilter,
    selectFilters = [],
    table,
}) {
    const { urls } = usePage().props;
    const currentPath = usePage().url.split('?')[0];

    const [mulai, setMulai] = useState(dateFilter?.mulai ?? '');
    const [selesai, setSelesai] = useState(dateFilter?.selesai ?? '');
    const [selects, setSelects] = useState(() =>
        Object.fromEntries(selectFilters.map((f) => [f.name, String(f.value ?? 'all')]))
    );

    const applyFilter = (e) => {
        e.preventDefault();
        const params = {};
        selectFilters.forEach((f) => {
            const v = selects[f.name];
            // filter "nullable": nilai Semua tidak dikirim (controller pakai is_null)
            if (f.nullable && v === 'all') return;
            params[f.name] = v;
        });
        if (dateFilter) {
            params.waktu_mulai = mulai;
            params.waktu_selesai = selesai;
        }
        router.get(currentPath, params, { preserveScroll: true });
    };

    const hasFilter = dateFilter || selectFilters.length > 0;

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
                {lead && (
                    <>
                        <h2 className="section-title">{title}</h2>
                        <p className="section-lead">{lead}</p>
                    </>
                )}

                {hasFilter && (
                    <div className="card mb-3">
                        <div className="card-body py-3">
                            <form onSubmit={applyFilter} className="form-inline" style={{ gap: 12 }}>
                                {dateFilter && (
                                    <>
                                        <div className="form-group" style={{ gap: 8 }}>
                                            <label htmlFor="waktu_mulai" className="text-muted" style={{ fontSize: 13 }}>
                                                <i className="fas fa-calendar mr-1"></i> Dari
                                            </label>
                                            <input
                                                id="waktu_mulai"
                                                type="date"
                                                className="form-control form-control-sm"
                                                value={mulai}
                                                onChange={(e) => setMulai(e.target.value)}
                                            />
                                        </div>
                                        <div className="form-group" style={{ gap: 8 }}>
                                            <label htmlFor="waktu_selesai" className="text-muted" style={{ fontSize: 13 }}>
                                                Sampai
                                            </label>
                                            <input
                                                id="waktu_selesai"
                                                type="date"
                                                className="form-control form-control-sm"
                                                value={selesai}
                                                onChange={(e) => setSelesai(e.target.value)}
                                            />
                                        </div>
                                    </>
                                )}
                                {selectFilters.map((f) => (
                                    <div key={f.name} className="form-group" style={{ gap: 8 }}>
                                        <label htmlFor={f.name} className="text-muted" style={{ fontSize: 13 }}>
                                            {f.label}
                                        </label>
                                        <select
                                            id={f.name}
                                            className="custom-select custom-select-sm"
                                            style={{ minWidth: 140 }}
                                            value={selects[f.name]}
                                            onChange={(e) => setSelects({ ...selects, [f.name]: e.target.value })}
                                        >
                                            {(f.options || []).map((o) => (
                                                <option key={o.value} value={String(o.value)}>
                                                    {o.label}
                                                </option>
                                            ))}
                                        </select>
                                    </div>
                                ))}
                                <button type="submit" className="btn btn-primary btn-sm">
                                    <i className="fas fa-filter mr-1"></i> Filter
                                </button>
                            </form>
                        </div>
                    </div>
                )}

                <div className="row justify-content-center">
                    <div className="col-md-12">
                        <div className="card">
                            <div className="card-header">
                                <h4>{cardTitle || `List ${title}`}</h4>
                                {(createUrl || headerActions.length > 0) && (
                                    <div className="card-header-action">
                                        {headerActions.map((a) =>
                                            a.method === 'delete' ? (
                                                <button
                                                    key={a.label}
                                                    type="button"
                                                    className={`btn btn-${a.color || 'danger'} mr-2`}
                                                    onClick={() => {
                                                        if (window.confirm(a.confirm || 'Yakin ingin menghapus? Tindakan ini tidak bisa dibatalkan.')) {
                                                            router.delete(a.url);
                                                        }
                                                    }}
                                                >
                                                    {a.icon && <i className={`fas fa-${a.icon} mr-1`}></i>}
                                                    {a.label}
                                                </button>
                                            ) : (
                                                <a key={a.label} href={a.url} className={`btn btn-${a.color || 'secondary'} mr-2`}>
                                                    {a.icon && <i className={`fas fa-${a.icon} mr-1`}></i>}
                                                    {a.label}
                                                </a>
                                            )
                                        )}
                                        {createUrl && (
                                            <a href={createUrl} className="btn btn-primary">
                                                <i className="fas fa-plus mr-1"></i> {createLabel}
                                            </a>
                                        )}
                                    </div>
                                )}
                            </div>
                            <div className="card-body">
                                <DataTable url={table.url} columns={table.columns} />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
