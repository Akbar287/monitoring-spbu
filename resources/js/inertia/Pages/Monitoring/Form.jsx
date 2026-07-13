import { useForm, usePage } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';

export default function Form({
    title,
    breadcrumbs = [],
    action,
    method = 'post',
    backUrl,
    products = [],
    product,
    dombaks = [],
    warning,
}) {
    const { urls, flash } = usePage().props;
    const { data, setData, post, put, processing, errors } = useForm({
        produk_id: product?.id || products[0]?.value || '',
        dombak_id: dombaks.map((d) => d.id),
        stok: dombaks.map((d) => String(d.stok ?? '0.00')),
    });

    const submit = (e) => {
        e.preventDefault();
        (method === 'put' ? put : post)(action, { preserveScroll: true });
    };

    const setStok = (idx, value) => {
        const next = [...data.stok];
        next[idx] = value;
        setData('stok', next);
    };

    return (
        <AppLayout title={title}>
            <div className="section-header">
                <h1>Monitoring Stok</h1>
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
                    <div className="col-md-12">
                        {flash?.msg && <div dangerouslySetInnerHTML={{ __html: flash.msg }} />}
                        <form onSubmit={submit}>
                            <div className="card">
                                <div className="card-header">
                                    <h4>{product ? 'Produk' : 'Pilih Produk'}</h4>
                                </div>
                                <div className="card-body">
                                    <div className="row mb-3 justify-content-center">
                                        <label htmlFor="produk_id" className="col-md-4 col-form-label text-md-right">
                                            Produk
                                        </label>
                                        <div className="col-md-6">
                                            {product ? (
                                                <>
                                                    <input type="hidden" name="produk_id" value={data.produk_id} />
                                                    <input id="produk_id" type="text" className="form-control" value={product.label} readOnly />
                                                </>
                                            ) : (
                                                <select
                                                    id="produk_id"
                                                    className={`custom-select ${errors.produk_id ? 'is-invalid' : ''}`}
                                                    value={data.produk_id}
                                                    onChange={(e) => setData('produk_id', e.target.value)}
                                                >
                                                    {products.map((p) => (
                                                        <option key={p.value} value={p.value}>
                                                            {p.label}
                                                        </option>
                                                    ))}
                                                </select>
                                            )}
                                            {errors.produk_id && <div className="invalid-feedback d-block">{errors.produk_id}</div>}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div className="card">
                                <div className="card-header">
                                    <h4>Atur Dombak</h4>
                                </div>
                                <div className="card-body">
                                    {warning && <div className="alert alert-danger">{warning}</div>}
                                    <div className="table-responsive">
                                        <table className="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Id Dombak</th>
                                                    <th>Nama Dombak</th>
                                                    <th>Initial Stok</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {dombaks.map((d, idx) => (
                                                    <tr key={d.id}>
                                                        <td>
                                                            <input type="text" className="form-control" value={d.id} readOnly />
                                                        </td>
                                                        <td>
                                                            <input type="text" className="form-control" value={d.name} readOnly />
                                                        </td>
                                                        <td>
                                                            <input
                                                                type="text"
                                                                className={`form-control ${errors[`stok.${idx}`] ? 'is-invalid' : ''}`}
                                                                value={data.stok[idx] ?? ''}
                                                                onChange={(e) => setStok(idx, e.target.value)}
                                                            />
                                                            {errors[`stok.${idx}`] && (
                                                                <div className="invalid-feedback d-block">{errors[`stok.${idx}`]}</div>
                                                            )}
                                                        </td>
                                                    </tr>
                                                ))}
                                            </tbody>
                                        </table>
                                    </div>
                                    <span className="text-muted">
                                        Jangan gunakan koma. Untuk kedetailan gunakan angka desimal, contoh: 24000.00 liter.
                                    </span>
                                </div>
                            </div>

                            <div className="card">
                                <div className="card-body">
                                    <div className="d-flex align-items-center" style={{ gap: 10 }}>
                                        <a href={backUrl} className="btn btn-primary">
                                            <i className="fas fa-arrow-left mr-1"></i> Kembali
                                        </a>
                                        <button type="submit" className="btn btn-success" disabled={processing}>
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
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
