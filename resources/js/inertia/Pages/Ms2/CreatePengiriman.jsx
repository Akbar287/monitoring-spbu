import { useForm, usePage } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '../../Layouts/AppLayout';

// Ajukan MS2 (port create_pengiriman.blade + blok JS checkbox-drp):
// per produk tampil daftar detail rencana (checkbox); saat dicentang muncul
// select shift. Submit: detail_rencana_pembelian_id[] (yang dicentang, urut
// render) + detail_rencana_pembelian_id_shift[] (shift per item, urutan sama)
// — paritas perilaku name-attribute blade lama.
export default function CreatePengiriman({ title, breadcrumbs = [], backUrl, action, tanggalDefault, shiftOptions = [], produkGroups = [] }) {
    const { urls } = usePage().props;
    const defaultShift = shiftOptions.length ? String(shiftOptions[0].value) : '';
    const [picked, setPicked] = useState({}); // id -> shift value (terpilih) | undefined
    const { data, setData, post, processing, errors, transform } = useForm({ tanggal: tanggalDefault ?? '' });

    const toggle = (id) => {
        setPicked((p) => {
            const next = { ...p };
            if (next[id] !== undefined) delete next[id];
            else next[id] = defaultShift;
            return next;
        });
    };

    const submit = (e) => {
        e.preventDefault();
        const ids = [];
        const shifts = [];
        produkGroups.forEach((g) =>
            g.items.forEach((it) => {
                if (picked[it.id] !== undefined) {
                    ids.push(it.id);
                    shifts.push(picked[it.id]);
                }
            })
        );
        transform((d) => ({
            ...d,
            detail_rencana_pembelian_id: ids,
            detail_rencana_pembelian_id_shift: shifts,
        }));
        post(action, { preserveScroll: true });
    };

    const totalPicked = Object.keys(picked).length;

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
                                    <div className="form-group row align-items-center">
                                        <label className="col-md-4 col-form-label text-md-right">Tanggal</label>
                                        <div className="col-md-7">
                                            <input
                                                type="date"
                                                className={`form-control ${errors.tanggal ? 'is-invalid' : ''}`}
                                                value={data.tanggal}
                                                onChange={(e) => setData('tanggal', e.target.value)}
                                            />
                                            {errors.tanggal && <div className="invalid-feedback d-block">{errors.tanggal}</div>}
                                        </div>
                                    </div>

                                    {produkGroups.map((g) => (
                                        <div key={g.nama} className="form-group row">
                                            <label className="col-md-4 col-form-label text-md-right">{g.nama}</label>
                                            <div className="col-md-7">
                                                {g.items.length === 0 && (
                                                    <p className="text-muted mb-0" style={{ paddingTop: 8 }}>
                                                        Tidak ada stok MS2 tersedia
                                                    </p>
                                                )}
                                                {g.items.map((it) => (
                                                    <div key={it.id} className="mb-2">
                                                        <div className="custom-control custom-checkbox">
                                                            <input
                                                                type="checkbox"
                                                                className="custom-control-input"
                                                                id={it.id}
                                                                checked={picked[it.id] !== undefined}
                                                                onChange={() => toggle(it.id)}
                                                            />
                                                            <label className="custom-control-label" htmlFor={it.id}>
                                                                {it.label}
                                                            </label>
                                                        </div>
                                                        {picked[it.id] !== undefined && (
                                                            <select
                                                                className="custom-select mt-1"
                                                                value={picked[it.id]}
                                                                onChange={(e) => setPicked({ ...picked, [it.id]: e.target.value })}
                                                            >
                                                                {shiftOptions.map((s) => (
                                                                    <option key={String(s.value)} value={String(s.value)}>
                                                                        {s.label}
                                                                    </option>
                                                                ))}
                                                            </select>
                                                        )}
                                                    </div>
                                                ))}
                                            </div>
                                        </div>
                                    ))}

                                    <div className="d-flex align-items-center mt-3" style={{ gap: 10 }}>
                                        <button type="submit" className="btn btn-primary" disabled={totalPicked === 0 || processing}>
                                            {processing ? (
                                                <>
                                                    <i className="fas fa-circle-notch fa-spin mr-1"></i> Menyimpan...
                                                </>
                                            ) : (
                                                <>
                                                    <i className="fas fa-save mr-1"></i> Ajukan MS2 ({totalPicked} item)
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
