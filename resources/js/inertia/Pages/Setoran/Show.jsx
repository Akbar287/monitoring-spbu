import { usePage } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';

// Detail setoran generik — dipakai tahap Penitipan / Closing / Setoran Bank.
// Semua aksi PUT memakai <form> HTML murni (full reload) karena target
// redirect antar-tahap sebagian masih Blade — paritas penuh dgn blade lama.
// Struktur props ditentukan controller per tahap (info, pj, standMeters,
// aksi per-baris utk konfirmasi closing, dan aksi global sesuai role/status).
const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

function PutForm({ action, children, confirm, fields = {} }) {
    return (
        <form
            action={action}
            method="POST"
            onSubmit={(e) => {
                if (confirm && !window.confirm(confirm)) e.preventDefault();
            }}
        >
            <input type="hidden" name="_token" value={csrf()} />
            <input type="hidden" name="_method" value="put" />
            {Object.entries(fields).map(([k, v]) => (
                <input key={k} type="hidden" name={k} value={v} />
            ))}
            {children}
        </form>
    );
}

function ActionButton({ a }) {
    if (a.method === 'link') {
        return (
            <a href={a.url} className={`btn btn-${a.color}`}>
                <i className={`fas fa-${a.icon}`}></i> {a.label}
            </a>
        );
    }
    return (
        <PutForm action={a.url} confirm={a.confirm} fields={a.fields || {}}>
            <button type="submit" className={`btn btn-${a.color}`} title={a.label}>
                <i className={`fas fa-${a.icon}`}></i> {a.label}
            </button>
        </PutForm>
    );
}

export default function Show({ title, breadcrumbs = [], backUrl, notice, info = [], pj = [], standMeters = [], actions = [] }) {
    const { urls } = usePage().props;

    const infoRow = (f, i) => (
        <div className="form-group row align-items-center justify-content-center" key={i}>
            <label className="col-md-4 col-form-label text-md-right">{f.label}</label>
            <div className="col-md-6">
                {f.prefix ? (
                    <div className="input-group">
                        <div className="input-group-prepend">
                            <span className="input-group-text">{f.prefix}</span>
                        </div>
                        <input type={f.type || 'text'} readOnly className="form-control" value={f.value ?? ''} />
                    </div>
                ) : (
                    <input type={f.type || 'text'} readOnly className="form-control" value={f.value ?? ''} />
                )}
            </div>
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
                    <div className="breadcrumb-item">Detail</div>
                </div>
            </div>

            <div className="section-body">
                <div className="row justify-content-center">
                    <div className="col-md-12">
                        <div className="card">
                            <div className="card-header">
                                <h4>Detail {title}</h4>
                            </div>
                            <div className="card-body">{info.map(infoRow)}</div>
                        </div>
                    </div>

                    <div className="col-md-12">
                        <div className="card">
                            <div className="card-header">
                                <h4>PJ Operasional</h4>
                            </div>
                            <div className="card-body">
                                {pj.length > 0 ? (
                                    <div className="table-responsive">
                                        <table className="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Jabatan</th>
                                                    <th>Nama</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {pj.map((p, i) => (
                                                    <tr key={i}>
                                                        <td>{p.jabatan}</td>
                                                        <td>
                                                            <ul className="mb-0 pl-3">
                                                                {(p.namas || []).map((n, j) => (
                                                                    <li key={j}>{n}</li>
                                                                ))}
                                                            </ul>
                                                        </td>
                                                    </tr>
                                                ))}
                                            </tbody>
                                        </table>
                                    </div>
                                ) : (
                                    <p className="mb-0">PJ Operasional belum di tentukan</p>
                                )}
                            </div>
                        </div>
                    </div>

                    <div className="col-md-12">
                        {standMeters.length === 0 ? (
                            <div className="card card-primary">
                                <div className="card-header">
                                    <h4>Detail Penjualan</h4>
                                </div>
                                <div className="card-body">
                                    <p className="mb-0">Tidak ada Stand Meter yang ditautkan</p>
                                </div>
                            </div>
                        ) : (
                            standMeters.map((sm, i) => (
                                <div className="card" key={i}>
                                    <div className="card-header">
                                        <h4>Stand Meter #{i + 1}</h4>
                                        {sm.badge && (
                                            <div className="card-header-action">
                                                <span className={`badge badge-${sm.badge.color}`}>{sm.badge.label}</span>
                                            </div>
                                        )}
                                    </div>
                                    <div className="card-body">
                                        {infoRow({ label: 'Nozzle', value: sm.nozzle }, 'n')}
                                        {infoRow({ label: 'Stand Meter Awal', value: sm.awal }, 'a')}
                                        {infoRow({ label: 'Stand Meter Akhir', value: sm.akhir }, 'b')}
                                        {sm.selisih != null && infoRow({ label: 'Selisih Stand Meter', value: sm.selisih }, 'c')}
                                        {sm.harga != null && infoRow({ label: 'Harga Jual per Liter', value: sm.harga, prefix: 'Rp' }, 'd')}
                                        {sm.total != null && infoRow({ label: 'Total Uang Penjualan', value: sm.total, prefix: 'Rp' }, 'e')}
                                        {sm.action && (
                                            <div className="row">
                                                <div className="col-md-6 offset-md-4">
                                                    <ActionButton a={sm.action} />
                                                </div>
                                            </div>
                                        )}
                                    </div>
                                </div>
                            ))
                        )}
                    </div>

                    <div className="col-md-12">
                        <div className="card">
                            <div className="card-header">
                                <h4>Pilihan</h4>
                            </div>
                            <div className="card-body">
                                {notice && <div className={`alert alert-${notice.type}`}><i className="fas fa-info-circle"></i> {notice.text}</div>}
                                <div className="d-flex flex-wrap align-items-end" style={{ gap: 8 }}>
                                    <a href={backUrl} className="btn btn-primary">
                                        <i className="fas fa-arrow-left"></i> Kembali
                                    </a>
                                    {actions.map((a, i) => (
                                        <ActionButton a={a} key={i} />
                                    ))}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
