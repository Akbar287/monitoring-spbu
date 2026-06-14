import { useForm, usePage } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '../../Layouts/AppLayout';

// Proses Penerimaan BBM dari File LO (port stok/penerimaan/show.blade +
// blok JS `penerimaan.show`): per dombak isi tinggi sebelum/setelah ->
// /master/dombak/konversi/api -> volume; Volume Aktual = Σ(penerimaan -
// sebelumnya), Selisih = aktual - dibeli. Payload store identik dgn form lama
// (array sejajar per dombak + tanggal/jam kerja/jam penerimaan).
export default function Process({ title, breadcrumbs = [], backUrl, action, info = [], dombakRows = [], jamKerjaOptions = [], volumeDibeli, api }) {
    const { urls } = usePage().props;

    const now = new Date();
    const pad = (n) => (n < 10 ? '0' + n : n);
    const { data, setData, post, processing, errors, transform } = useForm({
        tanggal: `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}`,
        jam_kerja_id: '',
        jam_penerimaan: `${pad(now.getHours())}:${pad(now.getMinutes())}`,
    });

    const [rows, setRows] = useState(
        dombakRows.map((d) => ({ ...d, tinggiSebelum: '', tinggiSetelah: '', alokasi: '', volSebelum: '0', volPenerimaan: '0' }))
    );

    const pf = (v) => parseFloat(String(v ?? '').replace(/,/g, '')) || 0;
    const totalAktual = rows.reduce((t, r) => t + (pf(r.volPenerimaan) - pf(r.volSebelum)), 0);
    const selisih = totalAktual - pf(volumeDibeli);

    const konversi = (dombakId, tinggi) =>
        fetch(api.konversi + '?' + new URLSearchParams({ dombak_id: dombakId, tinggi }), {
            credentials: 'same-origin',
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        }).then((r) => r.json());

    const onTinggiBlur = (i, field) => {
        const r = rows[i];
        const tinggi = field === 'tinggiSebelum' ? r.tinggiSebelum : r.tinggiSetelah;
        if (tinggi === '') return;
        konversi(r.id, tinggi)
            .then((res) => {
                if (res.status == 200 && res.data != null) {
                    setRows((rs) =>
                        rs.map((row, j) =>
                            j === i ? { ...row, [field === 'tinggiSebelum' ? 'volSebelum' : 'volPenerimaan']: res.data.volume } : row
                        )
                    );
                }
            })
            .catch((err) => console.error(err));
    };

    const setRow = (i, k, v) => setRows(rows.map((r, j) => (j === i ? { ...r, [k]: v } : r)));

    const submit = (e) => {
        e.preventDefault();
        transform((d) => ({
            ...d,
            dombak_id: rows.map((r) => r.id),
            tinggi_dombak_sebelumnya: rows.map((r) => r.tinggiSebelum),
            tinggi_dombak_setelahnya: rows.map((r) => r.tinggiSetelah),
            alokasi_dombak: rows.map((r) => r.alokasi),
            volume_sebelumnya: rows.map((r) => r.volSebelum),
        }));
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
    const ro = (label, value) => row(label, <input type="text" readOnly className="form-control" value={value ?? ''} />);

    const fmt = (n) => n.toLocaleString('en');

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
                                    <h6 className="text-primary border-bottom pb-2">Informasi LO & Rencana</h6>
                                    {info.map((f) => (
                                        <div key={f.label} className="row py-1" style={{ fontSize: 14 }}>
                                            <div className="col-md-4 text-md-right text-muted">{f.label}</div>
                                            <div className="col-md-7" style={{ fontWeight: 600 }}>
                                                {f.value ?? '-'}
                                            </div>
                                        </div>
                                    ))}

                                    {rows.map((r, i) => (
                                        <div key={r.id}>
                                            <h6 className="text-primary border-bottom pb-2 mt-4">Dombak: {r.nama}</h6>
                                            {row(
                                                'Tinggi Dombak (cm) Sebelumnya',
                                                <input
                                                    type="number"
                                                    step=".1"
                                                    min={r.min}
                                                    max={r.max}
                                                    className="form-control"
                                                    value={r.tinggiSebelum}
                                                    onChange={(e) => setRow(i, 'tinggiSebelum', e.target.value)}
                                                    onBlur={() => onTinggiBlur(i, 'tinggiSebelum')}
                                                />
                                            )}
                                            {row(
                                                'Tinggi Dombak (cm) Setelahnya',
                                                <input
                                                    type="number"
                                                    step=".1"
                                                    min={r.min}
                                                    max={r.max}
                                                    className="form-control"
                                                    value={r.tinggiSetelah}
                                                    onChange={(e) => setRow(i, 'tinggiSetelah', e.target.value)}
                                                    onBlur={() => onTinggiBlur(i, 'tinggiSetelah')}
                                                />
                                            )}
                                            {row(
                                                'Alokasi Dombak (liter)',
                                                <input
                                                    type="number"
                                                    step=".1"
                                                    className="form-control"
                                                    value={r.alokasi}
                                                    onChange={(e) => setRow(i, 'alokasi', e.target.value)}
                                                />
                                            )}
                                            {ro('Volume (liter) Sebelumnya', r.volSebelum)}
                                            {ro('Volume (liter) (Penerimaan)', r.volPenerimaan)}
                                        </div>
                                    ))}

                                    <h6 className="text-primary border-bottom pb-2 mt-4">Penerimaan</h6>
                                    {row(
                                        'Tanggal',
                                        <input type="date" className={`form-control ${errors.tanggal ? 'is-invalid' : ''}`} value={data.tanggal} onChange={(e) => setData('tanggal', e.target.value)} />,
                                        errors.tanggal
                                    )}
                                    {row(
                                        'Jam Kerja',
                                        <select className={`custom-select ${errors.jam_kerja_id ? 'is-invalid' : ''}`} value={data.jam_kerja_id} onChange={(e) => setData('jam_kerja_id', e.target.value)}>
                                            <option value="">— Silakan Pilih —</option>
                                            {jamKerjaOptions.map((o) => (
                                                <option key={String(o.value)} value={String(o.value)}>
                                                    {o.label}
                                                </option>
                                            ))}
                                        </select>,
                                        errors.jam_kerja_id
                                    )}
                                    {row(
                                        'Jam Penerimaan',
                                        <input type="time" className={`form-control ${errors.jam_penerimaan ? 'is-invalid' : ''}`} value={data.jam_penerimaan} onChange={(e) => setData('jam_penerimaan', e.target.value)} />,
                                        errors.jam_penerimaan
                                    )}
                                    {ro('Volume (liter) Dibeli', volumeDibeli)}
                                    {ro('Volume (liter) Aktual', fmt(totalAktual))}
                                    {ro('Volume (liter) (Loss/Profit)', fmt(selisih))}

                                    <div className="form-group row">
                                        <div className="col-md-7 offset-md-4 d-flex" style={{ gap: 10 }}>
                                            <button type="submit" className="btn btn-primary" disabled={processing}>
                                                {processing ? (
                                                    <>
                                                        <i className="fas fa-circle-notch fa-spin mr-1"></i> Menyimpan...
                                                    </>
                                                ) : (
                                                    <>
                                                        <i className="fas fa-save mr-1"></i> Simpan Penerimaan
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
