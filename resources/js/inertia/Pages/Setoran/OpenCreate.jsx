import { useForm, usePage } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '../../Layouts/AppLayout';

// Tambah Open Penjualan — port blok `setoran.open.create` custom.js.
// Baris "Stand Meter #n" dinamis (+/-). Pilih nozzle -> GET
// /api/setoran/open-penjualan/create {nozzle_id,tanggal,spbu_id,jam_kerja_id}
// -> isi stand meter awal/akhir, atau alert: belum dicatat / nozzle tak ada.
// Server hanya memakai nozzle_id[] (+header); stand meter dibaca ulang di DB.
export default function OpenCreate({ title, breadcrumbs = [], backUrl, action, spbuOptions = [], jamKerjaOptions = [], nozzleOptions = [], api }) {
    const { urls } = usePage().props;
    const { data, setData, post, processing, errors, transform } = useForm({
        spbu_id: '',
        tanggal: '',
        jam_kerja_id: '',
    });

    const [rows, setRows] = useState([{ nozzle_id: '', awal: '0', akhir: '0', alert: null }]);

    transform((d) => ({ ...d, nozzle_id: rows.map((r) => r.nozzle_id) }));

    const patchRow = (i, patch) => setRows((rs) => rs.map((r, idx) => (idx === i ? { ...r, ...patch } : r)));

    const onNozzleChange = (i, nozzleId) => {
        patchRow(i, { nozzle_id: nozzleId });
        if (nozzleId === '') return;
        const params = new URLSearchParams({
            nozzle_id: nozzleId,
            tanggal: data.tanggal,
            spbu_id: data.spbu_id,
            jam_kerja_id: data.jam_kerja_id,
        });
        fetch(api.standMeter + '?' + params, {
            credentials: 'same-origin',
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then((r) => r.json())
            .then((res) => {
                if (res.status === 'success') {
                    patchRow(i, {
                        awal: res.data?.stand_meter_awal ?? '0',
                        akhir: res.data?.stand_meter_akhir ?? '0',
                        alert: null,
                    });
                } else {
                    patchRow(i, {
                        awal: '0',
                        akhir: '0',
                        alert: res.message === 'Nozzle not found' ? 'nozzle_missing' : 'sm_missing',
                    });
                }
            })
            .catch((err) => console.error(err));
    };

    const addRow = () => setRows((rs) => [...rs, { nozzle_id: '', awal: '0', akhir: '0', alert: null }]);
    const removeRow = (i) => setRows((rs) => rs.filter((_, idx) => idx !== i));

    const submit = (e) => {
        e.preventDefault();
        post(action, { preserveScroll: true });
    };

    const headerRow = (name, label, control) => (
        <div className="form-group row align-items-center">
            <label htmlFor={name} className="col-md-4 col-form-label text-md-right">
                {label}
            </label>
            <div className="col-md-6">
                {control}
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
                <form onSubmit={submit}>
                    <div className="row justify-content-center">
                        <div className="col-md-12">
                            <div className="card">
                                <div className="card-header">
                                    <h4>Informasi Umum</h4>
                                </div>
                                <div className="card-body">
                                    {headerRow(
                                        'spbu_id',
                                        'SPBU',
                                        <select
                                            id="spbu_id"
                                            className={`custom-select ${errors.spbu_id ? 'is-invalid' : ''}`}
                                            value={data.spbu_id}
                                            onChange={(e) => setData('spbu_id', e.target.value)}
                                        >
                                            <option value="">— Silakan Pilih —</option>
                                            {spbuOptions.map((o) => (
                                                <option key={String(o.value)} value={String(o.value)}>
                                                    {o.label}
                                                </option>
                                            ))}
                                        </select>
                                    )}
                                    {headerRow(
                                        'tanggal',
                                        'Tanggal',
                                        <input
                                            id="tanggal"
                                            type="date"
                                            className={`form-control ${errors.tanggal ? 'is-invalid' : ''}`}
                                            value={data.tanggal}
                                            onChange={(e) => setData('tanggal', e.target.value)}
                                        />
                                    )}
                                    {headerRow(
                                        'jam_kerja_id',
                                        'Jam Kerja',
                                        <select
                                            id="jam_kerja_id"
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
                                        </select>
                                    )}
                                </div>
                            </div>
                        </div>

                        <div className="col-md-12">
                            {rows.map((row, i) => (
                                <div className="card" key={i}>
                                    <div className="card-header">
                                        <h4>Stand Meter #{i + 1}</h4>
                                        <div className="card-header-action">
                                            {rows.length > 1 && (
                                                <button
                                                    type="button"
                                                    className="btn btn-danger"
                                                    title="Hapus"
                                                    onClick={() => removeRow(i)}
                                                >
                                                    <i className="fas fa-minus"></i>
                                                </button>
                                            )}
                                        </div>
                                    </div>
                                    <div className="card-body">
                                        {row.alert === 'sm_missing' && (
                                            <div className="alert alert-danger">
                                                Stand Meter Belum Dicatat. Silakan anda buat dan konfirmasi di menu Totalisator
                                            </div>
                                        )}
                                        {row.alert === 'nozzle_missing' && (
                                            <div className="alert alert-danger">Nozzle Tidak ditemukan.</div>
                                        )}

                                        <div className="form-group row align-items-center justify-content-center">
                                            <label className="col-md-4 col-form-label text-md-right">Nozzle</label>
                                            <div className="col-md-6">
                                                <select
                                                    className="custom-select"
                                                    value={row.nozzle_id}
                                                    onChange={(e) => onNozzleChange(i, e.target.value)}
                                                >
                                                    <option value="">Silakan Pilih Nozzle</option>
                                                    {nozzleOptions.map((o) => (
                                                        <option key={String(o.value)} value={String(o.value)}>
                                                            {o.label}
                                                        </option>
                                                    ))}
                                                </select>
                                            </div>
                                        </div>
                                        <div className="form-group row align-items-center justify-content-center">
                                            <label className="col-md-4 col-form-label text-md-right">Stand Meter Awal</label>
                                            <div className="col-md-6">
                                                <input type="text" readOnly className="form-control" value={row.awal} />
                                            </div>
                                        </div>
                                        <div className="form-group row align-items-center justify-content-center">
                                            <label className="col-md-4 col-form-label text-md-right">Stand Meter Akhir</label>
                                            <div className="col-md-6">
                                                <input type="text" readOnly className="form-control" value={row.akhir} />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            ))}

                            <div className="text-center mb-4">
                                <button type="button" className="btn btn-outline-primary" onClick={addRow}>
                                    <i className="fas fa-plus mr-1"></i> Tambah Stand Meter
                                </button>
                            </div>

                            {errors.nozzle_id && <div className="alert alert-danger">{errors.nozzle_id}</div>}

                            <div className="d-flex justify-content-center" style={{ gap: 10 }}>
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
                    </div>
                </form>
            </div>
        </AppLayout>
    );
}
