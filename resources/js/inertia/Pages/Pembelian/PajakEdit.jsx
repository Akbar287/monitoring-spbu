import { useForm, usePage } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';

// Ubah pajak pembelian — port blok `pembelian.detail.edit_pajak` custom.js:
// ketik PPN/PPH/PPBKB -> Total Amount = net + ppn + pph + ppbkb (live,
// parseInt + format id-ID, paritas penuh). Server strip koma/titik sendiri.
const pInt = (v) => parseInt(String(v ?? '').replaceAll(',', ''), 10) || 0;
const fmt = (v) => new Intl.NumberFormat('id-ID', { style: 'decimal', maximumFractionDigits: 6 }).format(v);

export default function PajakEdit({ title, breadcrumbs = [], backUrl, action, values }) {
    const { urls } = usePage().props;
    const { data, setData, put, processing, errors } = useForm({
        net_price: values.net_price ?? '',
        ppn: values.ppn ?? '',
        pph: values.pph ?? '',
        ppbkb: values.ppbkb ?? '',
        total_amount: values.total_amount ?? '',
    });

    const recompute = (patch) => {
        const next = { ...data, ...patch };
        next.total_amount = fmt(pInt(next.net_price) + pInt(next.ppn) + pInt(next.pph) + pInt(next.ppbkb));
        setData(next);
    };

    const submit = (e) => {
        e.preventDefault();
        put(action, { preserveScroll: true });
    };

    const row = (name, label, readonly = false) => (
        <div className="form-group row align-items-center" key={name}>
            <label htmlFor={name} className="col-md-4 col-form-label text-md-right">
                {label}
            </label>
            <div className="col-md-6">
                <input
                    id={name}
                    type="text"
                    inputMode="decimal"
                    readOnly={readonly}
                    className={`form-control ${errors[name] ? 'is-invalid' : ''}`}
                    value={data[name]}
                    onChange={(e) => recompute({ [name]: e.target.value })}
                />
                {errors[name] && <div className="invalid-feedback d-block">{errors[name]}</div>}
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
                                    {row('net_price', 'Net Price', true)}
                                    {row('ppn', 'PPN')}
                                    {row('pph', 'PPH')}
                                    {row('ppbkb', 'PPBKB')}
                                    {row('total_amount', 'Total Amount', true)}

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
