import { useForm, usePage } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';

// Ubah harga detail pembelian — port blok `pembelian.detail.edit` custom.js:
// ketik Harga -> Total Harga = jumlah x harga (live). Server hanya membaca
// `harga`; total dihitung ulang di server saat update.
const num = (v) => parseFloat(String(v ?? '').replace(/,/g, '').split('.')[0]) || 0;
const fmt = (v) => new Intl.NumberFormat('id-ID', { style: 'decimal', maximumFractionDigits: 6 }).format(v);

export default function DetailEdit({ title, breadcrumbs = [], backUrl, action, info }) {
    const { urls } = usePage().props;
    const { data, setData, put, processing, errors } = useForm({ harga: info.harga ?? '' });

    const total = fmt(num(info.jumlah) * num(data.harga));

    const submit = (e) => {
        e.preventDefault();
        put(action, { preserveScroll: true });
    };

    const row = (label, control) => (
        <div className="form-group row align-items-center">
            <label className="col-md-4 col-form-label text-md-right">{label}</label>
            <div className="col-md-6">{control}</div>
        </div>
    );
    const ro = (v) => <input type="text" readOnly className="form-control" value={v ?? ''} />;

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
                                    {row('Produk', ro(info.produk))}
                                    {row('Rit', ro(info.rit))}
                                    {row('Quantity', ro(info.jumlah))}
                                    {row('Satuan', ro(info.satuan))}
                                    {row(
                                        'Harga',
                                        <>
                                            <input
                                                id="harga"
                                                type="text"
                                                inputMode="decimal"
                                                className={`form-control ${errors.harga ? 'is-invalid' : ''}`}
                                                value={data.harga}
                                                onChange={(e) => setData('harga', e.target.value)}
                                            />
                                            {errors.harga && <div className="invalid-feedback d-block">{errors.harga}</div>}
                                        </>
                                    )}
                                    {row('Total Harga', ro(total))}

                                    <div className="form-group row">
                                        <div className="col-md-6 offset-md-4 d-flex" style={{ gap: 10 }}>
                                            <button type="submit" className="btn btn-primary" disabled={processing}>
                                                {processing ? (
                                                    <>
                                                        <i className="fas fa-circle-notch fa-spin mr-1"></i> Menyimpan...
                                                    </>
                                                ) : (
                                                    <>
                                                        <i className="fas fa-save mr-1"></i> Simpan
                                                    </>
                                                )}
                                            </button>
                                            {backUrl && (
                                                <a href={backUrl} className="btn btn-light">
                                                    Batal
                                                </a>
                                            )}
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
