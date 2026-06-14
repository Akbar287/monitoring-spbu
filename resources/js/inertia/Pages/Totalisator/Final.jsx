import { useForm, usePage } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';

// Form Ubah / Input Final Totalisator.
// Port 1:1 dari blok totalisator.edit|totalisator.final di custom.js lama:
// selisih = bagian-bulat(akhir) - bagian-bulat(awal), ditampilkan format id-ID.
// Props: title, breadcrumbs, backUrl, action (PUT), info: [{label,value}],
//        standMeterAwal (angka), initialAkhir (nullable)
export default function Final({ title, breadcrumbs = [], backUrl, action, info = [], standMeterAwal, initialAkhir }) {
    const { urls } = usePage().props;
    const { data, setData, put, processing, errors } = useForm({
        stand_meter_akhir: initialAkhir ?? '',
    });

    // paritas custom.js: clearDot (ambil bagian sebelum titik) + removeComma
    const intPart = (v) => parseInt(String(v ?? '0').split('.')[0].replaceAll(',', ''), 10) || 0;
    const selisih = data.stand_meter_akhir === '' ? '' : new Intl.NumberFormat('id-ID').format(intPart(data.stand_meter_akhir) - intPart(standMeterAwal));

    const submit = (e) => {
        e.preventDefault();
        put(action, { preserveScroll: true });
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
                            </div>
                            <div className="card-body">
                                <form onSubmit={submit}>
                                    {info.map((f) => (
                                        <div key={f.label} className="form-group row align-items-center">
                                            <label className="col-md-4 col-form-label text-md-right text-muted">{f.label}</label>
                                            <div className="col-md-7">
                                                <input type="text" readOnly className="form-control-plaintext font-weight-bold" value={f.value ?? '-'} />
                                            </div>
                                        </div>
                                    ))}

                                    <div className="form-group row align-items-center">
                                        <label htmlFor="stand_meter_akhir" className="col-md-4 col-form-label text-md-right">
                                            Stand Meter Akhir
                                        </label>
                                        <div className="col-md-7">
                                            <input
                                                id="stand_meter_akhir"
                                                type="text"
                                                inputMode="numeric"
                                                autoFocus
                                                className={`form-control ${errors.stand_meter_akhir ? 'is-invalid' : ''}`}
                                                value={data.stand_meter_akhir}
                                                onChange={(e) => setData('stand_meter_akhir', e.target.value)}
                                            />
                                            {errors.stand_meter_akhir && (
                                                <div className="invalid-feedback d-block">{errors.stand_meter_akhir}</div>
                                            )}
                                        </div>
                                    </div>

                                    <div className="form-group row align-items-center">
                                        <label className="col-md-4 col-form-label text-md-right">Selisih</label>
                                        <div className="col-md-7">
                                            <input type="text" readOnly className="form-control" value={selisih} />
                                        </div>
                                    </div>

                                    <div className="form-group row">
                                        <div className="col-md-7 offset-md-4 d-flex" style={{ gap: 10 }}>
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
