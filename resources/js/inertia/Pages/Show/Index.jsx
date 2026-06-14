import { router, usePage } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import DataTable from '../../Components/DataTable';

// Halaman detail (show) generik.
// Props dari controller:
//   title, breadcrumbs: [{label,url?}], backUrl,
//   fields: [{label, value}],
//   actions?: [{label, url, color?, icon?, method?: 'put'|'post'|'delete', confirm?}]
//     - tanpa method: link biasa; dengan method: tombol router[method] + konfirmasi
//   deleteUrl?: url DELETE (tombol Hapus + konfirmasi)
//   deleteConfirm?: pesan konfirmasi kustom
//   notice?: {text, color}  // alert peringatan di atas tombol (mis. sebelum konfirmasi)
//   image?: url             // preview gambar (halaman file/lampiran)
//   tables?: [{title?, columns: [..], rows: [[..]]}]  // tabel statis embedded
//   table?: {title?, url, columns}  // DataTable server-side (Yajra) embedded
export default function Index({
    title,
    breadcrumbs = [],
    backUrl,
    fields = [],
    actions = [],
    deleteUrl,
    deleteConfirm,
    notice,
    image,
    tables = [],
    table,
}) {
    const { urls } = usePage().props;

    const onDelete = () => {
        if (window.confirm(deleteConfirm || 'Yakin ingin menghapus data ini? Tindakan ini tidak bisa dibatalkan.')) {
            router.delete(deleteUrl);
        }
    };

    const runAction = (a) => {
        if (!a.confirm || window.confirm(a.confirm)) {
            router[a.method](a.url);
        }
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
                    <div className="breadcrumb-item">{title}</div>
                </div>
            </div>

            <div className="section-body">
                <div className="row justify-content-center">
                    <div className="col-12 col-lg-9">
                        <div className="card">
                            <div className="card-header">
                                <h4>{title}</h4>
                                <div className="card-header-action">
                                    {actions.map((a) =>
                                        a.method ? (
                                            <button
                                                key={a.label}
                                                type="button"
                                                onClick={() => runAction(a)}
                                                className={`btn btn-${a.color || 'primary'} mr-2`}
                                            >
                                                {a.icon && <i className={`fas fa-${a.icon} mr-1`}></i>}
                                                {a.label}
                                            </button>
                                        ) : (
                                            <a
                                                key={a.label}
                                                href={a.url}
                                                onClick={(e) => {
                                                    if (a.confirm && !window.confirm(a.confirm)) e.preventDefault();
                                                }}
                                                className={`btn btn-${a.color || 'primary'} mr-2`}
                                            >
                                                {a.icon && <i className={`fas fa-${a.icon} mr-1`}></i>}
                                                {a.label}
                                            </a>
                                        )
                                    )}
                                    {deleteUrl && (
                                        <button type="button" onClick={onDelete} className="btn btn-danger">
                                            <i className="fas fa-trash mr-1"></i> Hapus
                                        </button>
                                    )}
                                </div>
                            </div>
                            <div className="card-body">
                                {notice && (
                                    <div className={`alert alert-${notice.color || 'warning'}`}>{notice.text}</div>
                                )}
                                {fields.map((f) => (
                                    <div key={f.label} className="row py-2" style={{ borderBottom: '1px solid rgba(31,36,64,.05)' }}>
                                        <div className="col-md-4 text-muted">{f.label}</div>
                                        <div className="col-md-8" style={{ fontWeight: 600, overflowWrap: 'break-word' }}>
                                            {f.value === null || f.value === '' || f.value === undefined ? '-' : String(f.value)}
                                        </div>
                                    </div>
                                ))}

                                {tables.map((t, ti) => (
                                    <div key={ti} className="mt-4">
                                        {t.title && <h6 className="text-primary border-bottom pb-2">{t.title}</h6>}
                                        <div className="table-responsive">
                                            <table className="table table-hover table-striped">
                                                <thead>
                                                    <tr>
                                                        {t.columns.map((c) => (
                                                            <th key={c} style={{ whiteSpace: 'nowrap' }}>{c}</th>
                                                        ))}
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    {t.rows.length === 0 ? (
                                                        <tr>
                                                            <td colSpan={t.columns.length} className="text-center text-muted py-3">
                                                                Tidak ada data
                                                            </td>
                                                        </tr>
                                                    ) : (
                                                        t.rows.map((r, ri) => (
                                                            <tr key={ri}>
                                                                {r.map((cell, ci) => (
                                                                    <td key={ci}>{cell}</td>
                                                                ))}
                                                            </tr>
                                                        ))
                                                    )}
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                ))}

                                {table && (
                                    <div className="mt-4">
                                        {table.title && <h6 className="text-primary border-bottom pb-2">{table.title}</h6>}
                                        <DataTable url={table.url} columns={table.columns} />
                                    </div>
                                )}

                                {image && (
                                    <div className="row py-3">
                                        <div className="col-md-4 text-muted">File Preview</div>
                                        <div className="col-md-8">
                                            <a href={image} target="_blank" rel="noreferrer">
                                                <img
                                                    src={image}
                                                    alt="preview"
                                                    className="img-thumbnail"
                                                    style={{ maxWidth: '100%', maxHeight: 420 }}
                                                />
                                            </a>
                                        </div>
                                    </div>
                                )}

                                {backUrl && (
                                    <div className="mt-4">
                                        <a href={backUrl} className="btn btn-light">
                                            <i className="fas fa-arrow-left mr-1"></i> Kembali
                                        </a>
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
