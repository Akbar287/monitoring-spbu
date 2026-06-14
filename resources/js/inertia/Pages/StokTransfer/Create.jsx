import { useForm, usePage } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '../../Layouts/AppLayout';

// Form Tambah Stok Transfer — port 1:1 blok `stok-transfer.create` custom.js.
// Rantai ganda:
//  Dombak-1: pilih -> /master/dombak/api {type:'transfer'} -> volume tercatat +
//            daftar Dombak-2 + buka input tinggi; tinggi sebelum/setelah ->
//            /master/dombak/konversi/api -> volume sebelum/setelah + terpindah.
//  Dombak-2: pilih -> /master/dombak/api -> volume tercatat terakhir + buka
//            tinggi; tinggi sebelum/setelah -> konversi -> volume kedua +
//            selisih (loss/profit). Submit baru aktif setelah tinggi-setelah
//            Dombak-2 valid (paritas lama).
export default function Create({ title, breadcrumbs = [], backUrl, action, jamKerjaOptions = [], dombakOptions = [], api }) {
    const { urls } = usePage().props;
    const { data, setData, post, processing, errors } = useForm({
        tanggal: '',
        jam_kerja_id: '',
        waktu: '',
        dombak_id: '',
        volume_tercatat: '',
        tinggi_dombak: '',
        tinggi_dombak_setelahnya: '',
        volume_sebelumnya: '',
        volume_setelah: '',
        volume: '',
        dombak_kedua_id: '',
        volume_sebelumnya_kedua_terakhir: '',
        tinggi_dombak_kedua: '',
        tinggi_dombak_kedua_setelah: '',
        volume_sebelumnya_kedua: '',
        volume_sebelumnya_kedua_setelah: '',
        volume_kedua: '',
        volume_selisih_kedua: '',
    });

    const [dombak2Options, setDombak2Options] = useState([]);
    const [dombak2Disabled, setDombak2Disabled] = useState(true);
    const [dombak2Empty, setDombak2Empty] = useState(false);
    const [tinggi1Disabled, setTinggi1Disabled] = useState(true);
    const [tinggi2Disabled, setTinggi2Disabled] = useState(true);
    const [canSubmit, setCanSubmit] = useState(false);

    const pf = (v) => parseFloat(String(v ?? '').replace(',', '')) || 0;
    // paritas: Intl id-ID lalu '.'->',' lalu koma-terakhir-sebelum-digit-akhir -> '.'
    const fmtDot = (calc) =>
        new Intl.NumberFormat('id-ID', { style: 'decimal', maximumFractionDigits: 6 })
            .format(calc)
            .replaceAll('.', ',')
            .replace(/,(?=\d$)/, '.');
    const fmtKoma = (calc) =>
        new Intl.NumberFormat('id-ID', { style: 'decimal', maximumFractionDigits: 6 })
            .format(calc)
            .replaceAll('.', ',');

    const jsonGet = (url, params) =>
        fetch(url + '?' + new URLSearchParams(params), {
            credentials: 'same-origin',
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        }).then((r) => r.json());

    // ---------- Dombak pertama ----------
    const checkVolume1 = (dombakId) => {
        if (dombakId === '') return;
        jsonGet(api.dombak, { dombak_id: dombakId, type: 'transfer' })
            .then((res) => {
                if (res.status == 200) {
                    if (res.data != null) {
                        setData((d) => ({ ...d, volume_tercatat: res.data.stok }));
                        setTinggi1Disabled(false);
                        if ((res.dombak || []).length > 0) {
                            setDombak2Options(
                                res.dombak.map((e) => ({
                                    value: e.dombak_id,
                                    label: e.nama_dombak + ' (' + e.nama_produk + ': ' + e.stok + 'Liter)',
                                }))
                            );
                            setDombak2Disabled(false);
                            setDombak2Empty(false);
                        } else {
                            setDombak2Options([]);
                            setDombak2Disabled(true);
                            setDombak2Empty(true);
                        }
                    } else {
                        setData((d) => ({ ...d, volume_tercatat: 'Tidak ada Data' }));
                        setTinggi1Disabled(true);
                    }
                }
            })
            .catch((err) => console.error(err));
    };

    const checkKonv1 = (jenis, snap) => {
        const { dombakId, tinggi, tinggiSetelah } = snap;
        if (jenis === 'sebelumnya') {
            if (dombakId === '' || tinggi === '') {
                setData((d) => ({ ...d, volume_sebelumnya: '0' }));
                return;
            }
            jsonGet(api.konversi, { dombak_id: dombakId, tinggi })
                .then((res) => {
                    if (res.status == 200) {
                        if (res.data != null) {
                            setData((d) => {
                                const calc = pf(d.volume_setelah) - pf(res.data.volume);
                                return { ...d, volume_sebelumnya: res.data.volume, volume: fmtDot(calc) };
                            });
                        } else {
                            setData((d) => ({ ...d, volume_sebelumnya: 'Tidak ada Data', volume: '0' }));
                        }
                    }
                })
                .catch((err) => console.error(err));
        } else {
            if (dombakId === '' || tinggiSetelah === '') {
                setData((d) => ({ ...d, volume_setelah: '0' }));
                return;
            }
            jsonGet(api.konversi, { dombak_id: dombakId, tinggi: tinggiSetelah })
                .then((res) => {
                    if (res.status == 200) {
                        if (res.data != null) {
                            setData((d) => {
                                const calc = pf(res.data.volume) - pf(d.volume_sebelumnya);
                                return { ...d, volume_setelah: res.data.volume, volume: fmtDot(calc) };
                            });
                        } else {
                            setData((d) => ({ ...d, volume_setelah: 'Tidak ada Data', volume: '0' }));
                        }
                    }
                })
                .catch((err) => console.error(err));
        }
    };

    // ---------- Dombak kedua ----------
    const checkVolume2 = (dombakId) => {
        if (dombakId === '') return;
        jsonGet(api.dombak, { dombak_id: dombakId })
            .then((res) => {
                if (res.status == 200) {
                    if (res.data != null) {
                        setData((d) => ({ ...d, volume_sebelumnya_kedua_terakhir: res.data.stok }));
                        setTinggi2Disabled(false);
                    } else {
                        setData((d) => ({ ...d, volume_sebelumnya_kedua_terakhir: 'Tidak ada Data' }));
                        setTinggi2Disabled(true);
                    }
                }
            })
            .catch((err) => console.error(err));
    };

    const checkKonv2 = (jenis, snap) => {
        const { dombak2Id, tinggi2, tinggi2Setelah } = snap;
        if (jenis === 'sebelumnya') {
            if (dombak2Id === '' || tinggi2 === '') {
                setData((d) => ({ ...d, volume_sebelumnya_kedua: '0' }));
                return;
            }
            jsonGet(api.konversi, { dombak_id: dombak2Id, tinggi: tinggi2 })
                .then((res) => {
                    if (res.status == 200) {
                        if (res.data != null) {
                            setData((d) => {
                                const calc = pf(d.volume_sebelumnya_kedua) - pf(res.data.volume);
                                const calc2 = pf(res.data.volume) - pf(d.volume_sebelumnya_kedua) - pf(d.volume) * -1;
                                return {
                                    ...d,
                                    volume_sebelumnya_kedua: res.data.volume,
                                    volume_kedua: fmtKoma(calc),
                                    volume_selisih_kedua: fmtKoma(calc2),
                                };
                            });
                        } else {
                            setData((d) => ({ ...d, volume_sebelumnya_kedua: 'Tidak ada Data', volume_kedua: '0' }));
                        }
                    }
                })
                .catch((err) => console.error(err));
        } else {
            if (dombak2Id === '' || tinggi2Setelah === '') {
                setData((d) => ({ ...d, volume_sebelumnya_kedua_setelah: '0' }));
                return;
            }
            jsonGet(api.konversi, { dombak_id: dombak2Id, tinggi: tinggi2Setelah })
                .then((res) => {
                    if (res.status == 200) {
                        if (res.data != null) {
                            setData((d) => {
                                const calc = pf(res.data.volume) - pf(d.volume_sebelumnya_kedua);
                                const calc2 = pf(res.data.volume) - pf(d.volume_sebelumnya_kedua) - pf(d.volume) * -1;
                                return {
                                    ...d,
                                    volume_sebelumnya_kedua_setelah: res.data.volume,
                                    volume_kedua: fmtKoma(calc),
                                    volume_selisih_kedua: fmtKoma(calc2),
                                };
                            });
                            setCanSubmit(true);
                        } else {
                            setData((d) => ({ ...d, volume_sebelumnya_kedua_setelah: 'Tidak ada Data', volume_kedua: '0' }));
                            setCanSubmit(false);
                        }
                    }
                })
                .catch((err) => console.error(err));
        }
    };

    // ---------- handlers ----------
    const onDombak1 = (e) => {
        const v = e.target.value;
        setData('dombak_id', v);
        const snap = { dombakId: v, tinggi: data.tinggi_dombak, tinggiSetelah: data.tinggi_dombak_setelahnya };
        checkKonv1('sebelumnya', snap);
        checkKonv1('setelahnya', snap);
        checkVolume1(v);
    };
    const onTinggi1Blur = () =>
        checkKonv1('sebelumnya', { dombakId: data.dombak_id, tinggi: data.tinggi_dombak, tinggiSetelah: data.tinggi_dombak_setelahnya });
    const onTinggi1SetelahBlur = () =>
        checkKonv1('setelahnya', { dombakId: data.dombak_id, tinggi: data.tinggi_dombak, tinggiSetelah: data.tinggi_dombak_setelahnya });

    const onDombak2 = (e) => {
        const v = e.target.value;
        setData('dombak_kedua_id', v);
        const snap = { dombak2Id: v, tinggi2: data.tinggi_dombak_kedua, tinggi2Setelah: data.tinggi_dombak_kedua_setelah };
        checkKonv2('sebelumnya', snap);
        checkKonv2('setelahnya', snap);
        checkVolume2(v);
    };
    const onTinggi2Blur = () =>
        checkKonv2('sebelumnya', { dombak2Id: data.dombak_kedua_id, tinggi2: data.tinggi_dombak_kedua, tinggi2Setelah: data.tinggi_dombak_kedua_setelah });
    const onTinggi2SetelahBlur = () =>
        checkKonv2('setelahnya', { dombak2Id: data.dombak_kedua_id, tinggi2: data.tinggi_dombak_kedua, tinggi2Setelah: data.tinggi_dombak_kedua_setelah });

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
    const input = (name, opts = {}) => (
        <input
            type={opts.type || 'text'}
            inputMode={opts.type ? undefined : 'decimal'}
            readOnly={opts.readOnly}
            disabled={opts.disabled}
            className={`form-control ${errors[name] ? 'is-invalid' : ''}`}
            value={data[name]}
            onChange={(e) => setData(name, e.target.value)}
            onBlur={opts.onBlur}
        />
    );

    const sectionTitle = (text) => (
        <div className="row">
            <div className="col-md-7 offset-md-4">
                <h6 className="text-primary border-bottom pb-2 mt-3">{text}</h6>
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
                                    {sectionTitle('Informasi Umum')}
                                    {row('Tanggal', input('tanggal', { type: 'date' }), errors.tanggal)}
                                    {row(
                                        'Jam Kerja',
                                        <select
                                            className={`custom-select ${errors.jam_kerja_id ? 'is-invalid' : ''}`}
                                            value={data.jam_kerja_id}
                                            onChange={(e) => setData('jam_kerja_id', e.target.value)}
                                        >
                                            <option value="">— Silakan Pilih —</option>
                                            {jamKerjaOptions.map((o) => (
                                                <option key={String(o.value)} value={String(o.value)}>
                                                    {o.label}
                                                </option>
                                            ))}
                                        </select>,
                                        errors.jam_kerja_id
                                    )}
                                    {row('Waktu', input('waktu', { type: 'time' }), errors.waktu)}

                                    {sectionTitle('Dombak Pertama (Asal)')}
                                    {row(
                                        'Dombak',
                                        <select
                                            className={`custom-select ${errors.dombak_id ? 'is-invalid' : ''}`}
                                            value={data.dombak_id}
                                            onChange={onDombak1}
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
                                    {row('Volume (liter) Tercatat Terakhir', input('volume_tercatat', { readOnly: true }))}
                                    {row('Tinggi Dombak (cm) Sebelum', input('tinggi_dombak', { disabled: tinggi1Disabled, onBlur: onTinggi1Blur }), errors.tinggi_dombak)}
                                    {row('Tinggi Dombak (cm) Setelah', input('tinggi_dombak_setelahnya', { disabled: tinggi1Disabled, onBlur: onTinggi1SetelahBlur }), errors.tinggi_dombak_setelahnya)}
                                    {row('Volume (liter) Sebelumnya', input('volume_sebelumnya', { readOnly: true }))}
                                    {row('Volume (liter) Setelah', input('volume_setelah', { readOnly: true }))}
                                    {row('Volume (liter) Dipindahkan', input('volume', { readOnly: true }))}

                                    {sectionTitle('Dombak Kedua (Tujuan)')}
                                    {dombak2Empty && (
                                        <div className="row">
                                            <div className="col-md-7 offset-md-4">
                                                <div className="alert alert-warning py-2 mb-3" style={{ fontSize: 13 }}>
                                                    <i className="fas fa-exclamation-triangle mr-1"></i>
                                                    Tidak ada dombak lain dengan produk yang sama sebagai tujuan transfer.
                                                    Tautkan dombak lain ke produk ini dulu di menu <strong>Master → Dombak</strong>.
                                                </div>
                                            </div>
                                        </div>
                                    )}
                                    {row(
                                        'Dombak Kedua',
                                        <select
                                            className={`custom-select ${errors.dombak_kedua_id ? 'is-invalid' : ''}`}
                                            value={data.dombak_kedua_id}
                                            onChange={onDombak2}
                                            disabled={dombak2Disabled}
                                        >
                                            <option value="">{dombak2Disabled ? '— Pilih Dombak Pertama dahulu —' : 'Silahkan Pilih Dombak Kedua'}</option>
                                            {dombak2Options.map((o) => (
                                                <option key={String(o.value)} value={String(o.value)}>
                                                    {o.label}
                                                </option>
                                            ))}
                                        </select>,
                                        errors.dombak_kedua_id
                                    )}
                                    {row('Volume (liter) Tercatat Terakhir', input('volume_sebelumnya_kedua_terakhir', { readOnly: true }))}
                                    {row('Tinggi Dombak (cm) Sebelum', input('tinggi_dombak_kedua', { disabled: tinggi2Disabled, onBlur: onTinggi2Blur }), errors.tinggi_dombak_kedua)}
                                    {row('Tinggi Dombak (cm) Setelah', input('tinggi_dombak_kedua_setelah', { disabled: tinggi2Disabled, onBlur: onTinggi2SetelahBlur }), errors.tinggi_dombak_kedua_setelah)}
                                    {row('Volume (liter) Sebelumnya', input('volume_sebelumnya_kedua', { readOnly: true }))}
                                    {row('Volume (liter) Setelah', input('volume_sebelumnya_kedua_setelah', { readOnly: true }))}
                                    {row('Volume (liter) Terpindah', input('volume_kedua', { readOnly: true }))}
                                    {row('Volume (liter) (Loss/Profit)', input('volume_selisih_kedua', { readOnly: true }))}

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
