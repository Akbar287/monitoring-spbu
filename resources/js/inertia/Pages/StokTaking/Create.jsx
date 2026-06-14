import { useForm, usePage } from '@inertiajs/react';
import { useCallback, useState } from 'react';
import AppLayout from '../../Layouts/AppLayout';

// Form Tambah Stok Taking — port 1:1 dari blok `stok-taking.create` custom.js:
//  - pilih dombak  -> GET /master/dombak/api          -> isi Volume Sebelumnya
//  - isi tinggi    -> GET /master/dombak/konversi/api -> isi Volume + Selisih
//  - submit nonaktif selama konversi/volume belum valid ("Tidak ada Data")
// Endpoint, parameter, dan format nilai sama persis dengan implementasi jQuery.
export default function Create({ title, breadcrumbs = [], backUrl, action, options, api }) {
    const { urls } = usePage().props;
    const { data, setData, post, processing, errors } = useForm({
        type_dokumen_id: '',
        dombak_id: '',
        tanggal: '',
        jam_kerja_id: '',
        tinggi_dombak: '',
    });

    const [volumeSebelumnya, setVolumeSebelumnya] = useState('');
    const [volume, setVolume] = useState('');
    const [volumeSelisih, setVolumeSelisih] = useState('');
    const [canSubmit, setCanSubmit] = useState(false);

    const num = (v) => parseFloat(String(v ?? '').replace(/,/g, '')) || 0;
    const fmtSelisih = (calc) =>
        new Intl.NumberFormat('id-ID', { style: 'decimal', maximumFractionDigits: 6 })
            .format(calc)
            .replaceAll('.', ',');

    const jsonGet = (url, params) =>
        fetch(url + '?' + new URLSearchParams(params), {
            credentials: 'same-origin',
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        }).then((r) => r.json());

    // paritas checkVolumeDombak()
    const checkVolumeDombak = useCallback(
        (dombakId, volumeNow) => {
            if (dombakId === '') return;
            jsonGet(api.dombak, { dombak_id: dombakId })
                .then((res) => {
                    if (res.status == 200) {
                        if (res.data != null) {
                            setVolumeSebelumnya(res.data.stok);
                            setVolumeSelisih(String(num(res.data.stok) - num(volumeNow)));
                            setCanSubmit(true);
                        } else {
                            setVolumeSebelumnya('Tidak ada Data');
                            setVolumeSelisih('0');
                            setCanSubmit(false);
                        }
                    }
                })
                .catch((err) => console.error(err));
        },
        [api.dombak]
    );

    // paritas checkKonversiDombak()
    const checkKonversiDombak = useCallback(
        (dombakId, tinggi, volSebelumnya) => {
            if (dombakId === '' || tinggi === '') return;
            jsonGet(api.konversi, { dombak_id: dombakId, tinggi })
                .then((res) => {
                    if (res.status == 200) {
                        if (res.data != null) {
                            const calc = num(res.data.volume) - num(volSebelumnya);
                            setVolume(res.data.volume);
                            setVolumeSelisih(fmtSelisih(calc));
                            setCanSubmit(true);
                        } else {
                            setVolume('Tidak ada Data');
                            setVolumeSelisih('0');
                            setCanSubmit(false);
                        }
                    }
                })
                .catch((err) => console.error(err));
        },
        [api.konversi]
    );

    const onDombakChange = (e) => {
        const v = e.target.value;
        setData('dombak_id', v);
        checkKonversiDombak(v, data.tinggi_dombak, volumeSebelumnya);
        checkVolumeDombak(v, volume);
    };

    const onTinggiChange = (e) => {
        const v = e.target.value;
        setData('tinggi_dombak', v);
    };

    const onTinggiBlur = () => {
        checkKonversiDombak(data.dombak_id, data.tinggi_dombak, volumeSebelumnya);
    };

    const submit = (e) => {
        e.preventDefault();
        post(action, { preserveScroll: true });
    };

    const selectField = (name, label, opts, onChange) => (
        <div className="form-group row align-items-center">
            <label htmlFor={name} className="col-md-4 col-form-label text-md-right">
                {label}
            </label>
            <div className="col-md-7">
                <select
                    id={name}
                    className={`custom-select ${errors[name] ? 'is-invalid' : ''}`}
                    value={data[name]}
                    onChange={onChange || ((e) => setData(name, e.target.value))}
                >
                    <option value="">— Silakan Pilih —</option>
                    {opts.map((o) => (
                        <option key={String(o.value)} value={String(o.value)}>
                            {o.label}
                        </option>
                    ))}
                </select>
                {errors[name] && <div className="invalid-feedback d-block">{errors[name]}</div>}
            </div>
        </div>
    );

    const readonlyField = (label, value) => (
        <div className="form-group row align-items-center">
            <label className="col-md-4 col-form-label text-md-right text-muted">{label}</label>
            <div className="col-md-7">
                <input type="text" readOnly className="form-control" value={value} />
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
                                    {selectField('type_dokumen_id', 'Tipe Dokumen', options.typeDokumen)}
                                    {selectField('dombak_id', 'Dombak', options.dombak, onDombakChange)}

                                    <div className="form-group row align-items-center">
                                        <label htmlFor="tanggal" className="col-md-4 col-form-label text-md-right">
                                            Tanggal
                                        </label>
                                        <div className="col-md-7">
                                            <input
                                                id="tanggal"
                                                type="date"
                                                className={`form-control ${errors.tanggal ? 'is-invalid' : ''}`}
                                                value={data.tanggal}
                                                onChange={(e) => setData('tanggal', e.target.value)}
                                            />
                                            {errors.tanggal && <div className="invalid-feedback d-block">{errors.tanggal}</div>}
                                        </div>
                                    </div>

                                    {selectField('jam_kerja_id', 'Jam Kerja', options.jamKerja)}

                                    <div className="form-group row align-items-center">
                                        <label htmlFor="tinggi_dombak" className="col-md-4 col-form-label text-md-right">
                                            Tinggi Dombak
                                        </label>
                                        <div className="col-md-7">
                                            <input
                                                id="tinggi_dombak"
                                                type="text"
                                                inputMode="decimal"
                                                className={`form-control ${errors.tinggi_dombak ? 'is-invalid' : ''}`}
                                                value={data.tinggi_dombak}
                                                onChange={onTinggiChange}
                                                onBlur={onTinggiBlur}
                                            />
                                            {errors.tinggi_dombak && (
                                                <div className="invalid-feedback d-block">{errors.tinggi_dombak}</div>
                                            )}
                                            <small className="form-text text-muted">
                                                Volume terisi otomatis dari tabel konversi dombak.
                                            </small>
                                        </div>
                                    </div>

                                    {readonlyField('Volume Sebelumnya', volumeSebelumnya)}
                                    {readonlyField('Volume', volume)}
                                    {readonlyField('Volume Selisih', volumeSelisih)}

                                    <div className="form-group row">
                                        <div className="col-md-7 offset-md-4 d-flex" style={{ gap: 10 }}>
                                            <button type="submit" className="btn btn-primary" disabled={!canSubmit || processing}>
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
