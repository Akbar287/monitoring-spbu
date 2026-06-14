import { useForm, usePage } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '../../Layouts/AppLayout';

// Form Tambah Totalisator — port 1:1 dari blok `totalisator.create` custom.js:
//  - pilih dombak -> GET /stok/totalisator/create/api?dombak_id -> daftar nozzle,
//    reset tanggal/jam/stand meter (semua readonly, submit nonaktif)
//  - pilih nozzle -> GET ?nozzle_id -> tanggal + jam kerja + stand meter awal:
//      * receive=true  : ada Penerimaan BBM -> tanggal bisa diedit + jam kerja
//        jadi SELECT (user wajib pilih shift) + alert info
//      * receive=false : tanggal & jam readonly; jam tampil "Shift N" dan ikut
//        tersubmit sbg teks (paritas lama — server menghitung ulang sendiri)
//      * stand_meter_akhir == 0 -> stand meter awal bisa diisi (mulai 0),
//        selain itu readonly berisi stand meter akhir terakhir
export default function Create({ title, breadcrumbs = [], backUrl, action, dombakOptions = [], apiUrl }) {
    const { urls } = usePage().props;
    const { data, setData, post, processing, errors } = useForm({
        dombak_id: '',
        nozzle_id: '',
        tanggal: '',
        jam_kerja_id: '',
        stand_meter_awal: '',
    });

    const [nozzleOptions, setNozzleOptions] = useState([]);
    const [nozzleDisabled, setNozzleDisabled] = useState(true);
    const [receive, setReceive] = useState(false);
    const [jamOptions, setJamOptions] = useState([]);
    const [tanggalEditable, setTanggalEditable] = useState(false);
    const [awalEditable, setAwalEditable] = useState(false);
    const [canSubmit, setCanSubmit] = useState(false);

    const jsonGet = (params) =>
        fetch(apiUrl + '?' + new URLSearchParams(params), {
            credentials: 'same-origin',
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        }).then((r) => r.json());

    const onDombakChange = (e) => {
        const v = e.target.value;
        // paritas beforeSend: reset seluruh field turunan
        setData((d) => ({ ...d, dombak_id: v, nozzle_id: '', tanggal: '', jam_kerja_id: '', stand_meter_awal: '' }));
        setNozzleOptions([]);
        setNozzleDisabled(true);
        setReceive(false);
        setTanggalEditable(false);
        setAwalEditable(false);
        setCanSubmit(false);
        if (v === '') return;

        jsonGet({ dombak_id: v })
            .then((res) => {
                if (res.status == 'success' && res.data != null) {
                    setNozzleOptions(res.data.map((n) => ({ value: n.nozzle_id, label: n.nama_nozzle + ' - ' + n.nama_produk })));
                    setNozzleDisabled(false);
                }
            })
            .catch((err) => console.error(err));
    };

    const onNozzleChange = (e) => {
        const v = e.target.value;
        setData('nozzle_id', v);
        if (v === '' || v === '0') return;

        jsonGet({ nozzle_id: v })
            .then((res) => {
                if (res.status == 'success' && res.data != null) {
                    const d = res.data;
                    const isReceive = !!d.receive;
                    setReceive(isReceive);
                    setTanggalEditable(isReceive);
                    setJamOptions(isReceive ? d.jam_kerja_id.map((x) => ({ value: x.jam_kerja_id, label: x.nama_jam_kerja })) : []);

                    const awalEditableNow = d.stand_meter_akhir == 0;
                    setAwalEditable(awalEditableNow);

                    setData((prev) => ({
                        ...prev,
                        nozzle_id: v,
                        tanggal: d.tanggal ?? '',
                        // paritas: non-receive submit teks "Shift N"; receive wajib pilih
                        jam_kerja_id: isReceive ? '' : 'Shift ' + (d.jam_kerja ?? 1),
                        stand_meter_awal: awalEditableNow ? '0' : String(d.stand_meter_akhir ?? 0),
                    }));
                    setCanSubmit(true);
                }
            })
            .catch((err) => console.error(err));
    };

    const submit = (e) => {
        e.preventDefault();
        post(action, { preserveScroll: true });
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
                                {receive && (
                                    <div className="alert alert-info">
                                        Sistem mendeteksi ada Penerimaan BBM. Silakan isi tanggal dan jam kerja sesuai dengan keadaan.
                                    </div>
                                )}

                                <form onSubmit={submit}>
                                    {row(
                                        'Dombak',
                                        <select
                                            className={`custom-select ${errors.dombak_id ? 'is-invalid' : ''}`}
                                            value={data.dombak_id}
                                            onChange={onDombakChange}
                                        >
                                            <option value="">— Silakan Pilih Dombak —</option>
                                            {dombakOptions.map((o) => (
                                                <option key={String(o.value)} value={String(o.value)}>
                                                    {o.label}
                                                </option>
                                            ))}
                                        </select>,
                                        errors.dombak_id
                                    )}

                                    {row(
                                        'Nozzle',
                                        <select
                                            className={`custom-select ${errors.nozzle_id ? 'is-invalid' : ''}`}
                                            value={data.nozzle_id}
                                            onChange={onNozzleChange}
                                            disabled={nozzleDisabled}
                                        >
                                            <option value="">{nozzleDisabled ? '— Pilih Dombak dahulu —' : 'Silakan Pilih Nozzle'}</option>
                                            {nozzleOptions.map((o) => (
                                                <option key={String(o.value)} value={String(o.value)}>
                                                    {o.label}
                                                </option>
                                            ))}
                                        </select>,
                                        errors.nozzle_id
                                    )}

                                    {row(
                                        'Tanggal',
                                        <input
                                            type="date"
                                            className={`form-control ${errors.tanggal ? 'is-invalid' : ''}`}
                                            value={data.tanggal}
                                            readOnly={!tanggalEditable}
                                            onChange={(e) => setData('tanggal', e.target.value)}
                                        />,
                                        errors.tanggal
                                    )}

                                    {row(
                                        'Jam Kerja',
                                        receive ? (
                                            <select
                                                className={`custom-select ${errors.jam_kerja_id ? 'is-invalid' : ''}`}
                                                value={data.jam_kerja_id}
                                                onChange={(e) => setData('jam_kerja_id', e.target.value)}
                                            >
                                                <option value="">— Pilih Jam Kerja —</option>
                                                {jamOptions.map((o) => (
                                                    <option key={String(o.value)} value={String(o.value)}>
                                                        {o.label}
                                                    </option>
                                                ))}
                                            </select>
                                        ) : (
                                            <input type="text" readOnly className="form-control" value={data.jam_kerja_id} />
                                        ),
                                        errors.jam_kerja_id
                                    )}

                                    {row(
                                        'Stand Meter Awal',
                                        <input
                                            type="text"
                                            inputMode="numeric"
                                            className={`form-control ${errors.stand_meter_awal ? 'is-invalid' : ''}`}
                                            value={data.stand_meter_awal}
                                            readOnly={!awalEditable}
                                            onChange={(e) => setData('stand_meter_awal', e.target.value)}
                                        />,
                                        errors.stand_meter_awal
                                    )}

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
