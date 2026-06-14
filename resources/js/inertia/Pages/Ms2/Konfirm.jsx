import { useForm, usePage } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '../../Layouts/AppLayout';

// Konfirmasi MS2 -> Pengiriman (port konfirm_pengiriman.blade + blok JS
// btn-minus/plus-produk): semua produk default ikut; baris bisa dikeluarkan
// (merah) / dimasukkan lagi. Submit PUT update_pengiriman_to_delivery dengan
// detail_rencana_pembelian_id[] = baris yang masih aktif.
export default function Konfirm({ title, breadcrumbs = [], backUrl, action, member, tanggal, items = [] }) {
    const { urls } = usePage().props;
    const [excluded, setExcluded] = useState({});
    const { data, setData, put, processing, errors, transform } = useForm({
        tanggal: tanggal ?? '',
        no_do: '',
        no_pol: '',
        catatan: '',
    });

    const submit = (e) => {
        e.preventDefault();
        transform((d) => ({
            ...d,
            detail_rencana_pembelian_id: items.filter((it) => !excluded[it.id]).map((it) => it.id),
        }));
        put(action, { preserveScroll: true });
    };

    const row = (label, control, error) => (
        <div className="form-group row align-items-center">
            <label className="col-md-4 col-form-label text-md-right">{label}</label>
            <div className="col-md-7">
                {control}
                {error && <div className="invalid-feedback d-block">{error}</div>}
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
                    <div className="breadcrumb-item">{title}</div>
                </div>
            </div>

            <div className="section-body">
                <div className="row justify-content-center">
                    <div className="col-12 col-lg-9">
                        <div className="card">
                            <div className="card-header">
                                <h4>{title}</h4>
                            </div>
                            <div className="card-body">
                                <form onSubmit={submit}>
                                    {row('Yang Menerima', <input type="text" readOnly className="form-control" value={member ?? ''} />)}
                                    {row(
                                        'Tanggal',
                                        <input type="date" className={`form-control ${errors.tanggal ? 'is-invalid' : ''}`} value={data.tanggal} onChange={(e) => setData('tanggal', e.target.value)} />,
                                        errors.tanggal
                                    )}
                                    {row(
                                        'Nomor DO',
                                        <input type="text" className={`form-control ${errors.no_do ? 'is-invalid' : ''}`} value={data.no_do} onChange={(e) => setData('no_do', e.target.value)} />,
                                        errors.no_do
                                    )}
                                    {row(
                                        'Nomor Polisi',
                                        <input type="text" className={`form-control ${errors.no_pol ? 'is-invalid' : ''}`} value={data.no_pol} onChange={(e) => setData('no_pol', e.target.value)} />,
                                        errors.no_pol
                                    )}
                                    {row(
                                        'Catatan',
                                        <textarea rows={4} className={`form-control ${errors.catatan ? 'is-invalid' : ''}`} value={data.catatan} onChange={(e) => setData('catatan', e.target.value)} />,
                                        errors.catatan
                                    )}

                                    <h6 className="text-primary border-bottom pb-2 mt-4">Produk yang Dikirim</h6>
                                    <div className="table-responsive">
                                        <table className="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Nama Produk</th>
                                                    <th>Kuantitas</th>
                                                    <th>Kode Pembelian</th>
                                                    <th style={{ width: 70 }}>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {items.map((it) => (
                                                    <tr key={it.id} className={excluded[it.id] ? 'bg-danger text-white' : ''}>
                                                        <td>{it.nama_produk}</td>
                                                        <td>{it.kuantitas}</td>
                                                        <td>{it.kode_pembelian}</td>
                                                        <td>
                                                            <button
                                                                type="button"
                                                                className={`btn btn-sm ${excluded[it.id] ? 'btn-success' : 'btn-danger'}`}
                                                                title={excluded[it.id] ? 'Masukkan kembali' : 'Keluarkan dari pengiriman'}
                                                                onClick={() => setExcluded({ ...excluded, [it.id]: !excluded[it.id] })}
                                                            >
                                                                <i className={`fas ${excluded[it.id] ? 'fa-plus' : 'fa-minus'}`}></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                ))}
                                            </tbody>
                                        </table>
                                    </div>

                                    <div className="d-flex mt-3" style={{ gap: 10 }}>
                                        <button type="submit" className="btn btn-primary" disabled={processing}>
                                            {processing ? (
                                                <>
                                                    <i className="fas fa-circle-notch fa-spin mr-1"></i> Memproses...
                                                </>
                                            ) : (
                                                <>
                                                    <i className="fas fa-truck mr-1"></i> Lanjutkan ke Pengiriman
                                                </>
                                            )}
                                        </button>
                                        {backUrl && (
                                            <a href={backUrl} className="btn btn-light">
                                                Batal
                                            </a>
                                        )}
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
