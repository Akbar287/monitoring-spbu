import { router, useForm } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import AppLayout from '../../Layouts/AppLayout';
import { PageHeader, fmt } from './common';

const receiptKeys = ['waktu_penerimaan', 'nomor_mobil_tangki', 'nomor_pnbp', 'truk_berwenang', 'density_15_pnbp', 'density_penerimaan', 'suhu_penerimaan', 'volume_pnbp', 'volume_diterima', 'catatan_penerimaan'];
const inputTime = v => v ? v.replace(' ', 'T').slice(0, 16) : '';

function Field({ name, label, value, change, error, type = 'text', required = false, hint, ...props }) {
    return <div className="form-group col-md-4"><label htmlFor={name}>{label}{required ? ' *' : ''}</label>
        <input id={name} name={name} className={`form-control ${error ? 'is-invalid' : ''}`} type={type} value={value ?? ''} onChange={e => change(e.target.value)} required={required} {...props} />
        {error && <div className="invalid-feedback">{error}</div>}{hint && <small className="form-text text-muted">{hint}</small>}
    </div>;
}

function Conversion({ density, suhu, baseUrl, pnbp }) {
    const [result, setResult] = useState(null);
    const [error, setError] = useState('');
    const [loading, setLoading] = useState(false);
    useEffect(() => {
        const controller = new AbortController();
        setResult(null); setError(''); setLoading(false);
        if (density === '' || suhu === '' || density == null || suhu == null) return () => controller.abort();
        setLoading(true);
        const timer = setTimeout(async () => {
            try {
                const params = new URLSearchParams({ density: String(density).replace(',', '.'), suhu: String(suhu).replace(',', '.') });
                const response = await fetch(`${baseUrl}/astm/lookup?${params}`, { signal: controller.signal, credentials: 'same-origin', headers: { Accept: 'application/json' } });
                const payload = await response.json();
                if (!response.ok) throw new Error(Object.values(payload.errors || {}).flat()[0] || 'Konversi ASTM belum dapat dimuat.');
                setResult(payload);
            } catch (e) {
                if (e.name !== 'AbortError') setError(e.message);
            } finally { if (!controller.signal.aborted) setLoading(false); }
        }, 300);
        return () => { clearTimeout(timer); controller.abort(); };
    }, [density, suhu, baseUrl]);
    const diff = result && pnbp !== undefined && pnbp !== '' ? Number((Number(result.density_15) - Number(String(pnbp).replace(',', '.'))).toFixed(4)) : null;
    return <div className="km-result mb-3" aria-live="polite">
        <span className="d-block text-muted">Density standar 15 °C (ASTM)</span>
        {loading ? 'Mencari nilai ASTM…' : error ? <span className="text-danger">{error}</span> : result ? <><strong>{fmt(result.density_15)}</strong> g/cm³ <small className="text-muted">· PDF halaman {result.halaman}</small></> : 'Isi density observed dan suhu untuk melihat hasil.'}
        {diff !== null && <div className={Math.abs(diff) > .00500001 ? 'text-danger' : ''}>Selisih density c = b − a: <b>{fmt(diff)}</b> g/cm³{Math.abs(diff) > .00500001 ? ' · Melebihi batas 0,005; penerimaan harus ditolak sesuai formulir.' : ''}</div>}
    </div>;
}

export default function Form({ title, record, pemeriksaan, tahap = 'pagi', action, method = 'post', dombak, baseUrl, today, identitas = {}, identitasDiperiksa = false }) {
    const isPagi = tahap === 'pagi';
    const [ready, setReady] = useState(!!record || identitasDiperiksa);
    const initial = isPagi ? {
        dombak_id: record?.dombak_id ?? identitas.dombak_id ?? '', tanggal: record?.tanggal ?? identitas.tanggal ?? today,
        jam_awal: record?.jam_awal?.slice(0, 5) ?? '06:00', density_awal: record?.density_awal ?? '',
        suhu_awal: record?.suhu_awal ?? '', tinggi_air: record?.tinggi_air ?? '', catatan: record?.catatan ?? '',
    } : tahap === 'penerimaan' ? Object.fromEntries(receiptKeys.map(k => [k,
        k === 'waktu_penerimaan' ? inputTime(pemeriksaan?.[k]) : pemeriksaan?.[k] ?? (k === 'truk_berwenang' ? true : ''),
    ])) : {
        waktu_akhir: inputTime(pemeriksaan?.waktu_akhir), density_akhir: pemeriksaan?.density_akhir ?? '',
        suhu_akhir: pemeriksaan?.suhu_akhir ?? '', catatan_akhir: pemeriksaan?.catatan_akhir ?? '',
    };
    const { data, setData, post, put, errors, processing } = useForm(initial);
    const selected = dombak.find(d => d.id === data.dombak_id);
    const changeIdentity = (key, value) => { setReady(false); setData(key, value); };
    const input = (key, label, props = {}) => <Field key={key} name={key} label={label} value={data[key]} change={v => setData(key, v)} error={errors[key]} {...props} />;
    const numeric = { inputMode: 'decimal', placeholder: 'Contoh: 0,726' };
    const noteKey = isPagi ? 'catatan' : `catatan_${tahap}`;
    const submit = e => {
        e.preventDefault();
        if (!ready) {
            router.get(`${baseUrl}/create`, { dombak_id: data.dombak_id, tanggal: data.tanggal });
            return;
        }
        method === 'put' ? put(action) : post(action);
    };
    return <AppLayout title={title}><PageHeader title={title} baseUrl={baseUrl} />
        <p>Density Pagi, Penerimaan, dan Akhir dicatat serta disetujui secara terpisah. Persetujuan Pagi tetap mengizinkan pemeriksaan berikutnya pada tanggal yang sama.</p>
        {Object.keys(errors).length > 0 && <div className="alert alert-danger" role="alert"><b>Pemeriksaan belum tersimpan.</b><ul className="mb-0">{Object.entries(errors).map(([k,v]) => <li key={k}>{v}</li>)}</ul></div>}
        <form data-no-inertia onSubmit={submit}>
            <div className="card"><div className="card-body"><h2 className="km-section-title">Identitas catatan harian</h2><div className="row mt-3">
                <div className="form-group col-md-4"><label htmlFor="dombak_id">Dombak / No. Tangki *</label>
                    <select id="dombak_id" required className={`form-control ${errors.dombak_id ? 'is-invalid' : ''}`} disabled={!!record} value={record?.dombak_id ?? data.dombak_id} onChange={e => changeIdentity('dombak_id', e.target.value)}>
                        <option value="">Pilih dombak</option>{dombak.map(d => <option key={d.id} value={d.id}>{d.nama} · SPBU {d.nomor_spbu}</option>)}
                        {record && !dombak.some(d => d.id === record.dombak_id) && <option value={record.dombak_id}>{record.nama_dombak}</option>}
                    </select>{errors.dombak_id && <div className="invalid-feedback">{errors.dombak_id}</div>}
                </div>
                <Field name="nomor_spbu" label="No. SPBU" value={record?.nomor_spbu ?? selected?.nomor_spbu ?? ''} readOnly change={() => {}} hint="Diambil dari master dombak." />
                <Field name="nama_produk" label="Produk" value={record?.nama_produk ?? selected?.nama_produk ?? ''} readOnly change={() => {}} hint="Diambil dari relasi persediaan dombak." />
                <Field name="tanggal" label="Tanggal Catatan" value={record?.tanggal ?? data.tanggal} type="date" readOnly={!!record} required change={v => changeIdentity('tanggal', v)} error={errors.tanggal} />
            </div>{selected && !selected.nama_produk && !record && <div className="alert alert-warning">Produk dombak belum terhubung secara tunggal. Lengkapi master persediaan sebelum mencatat.</div>}
                {!ready && <><p>Pilih tanggal dan dombak. Jika pemeriksaan Pagi sudah ada, Anda akan diarahkan ke catatan tersebut untuk menambah Penerimaan atau Akhir.</p>
                    <button className="btn btn-primary" disabled={!data.dombak_id || !data.tanggal}>Lanjutkan / Buka Catatan Harian</button></>}
            </div></div>

            {ready && <>
                <div className="card"><div className="card-body"><h2 className="km-section-title">{isPagi ? 'Pagi · Sebelum penerimaan' : tahap === 'penerimaan' ? 'Penerimaan · Dari mobil tangki' : 'Akhir · Dua jam setelah penerimaan'}</h2>
                    <p className="text-muted">Density dalam g/cm³, suhu dalam °C. Density observed mengikuti tabel per 0,001; suhu per 0,5 °C.</p>
                    {isPagi ? <><div className="row mt-3">
                        {input('jam_awal', 'Jam', { type: 'time', required: true })}
                        {input('density_awal', 'Density observed (hidrometer)', { ...numeric, required: true })}
                        {input('suhu_awal', 'Suhu observed (°C)', { inputMode: 'decimal', required: true, placeholder: 'Contoh: 31,5' })}
                        {input('tinggi_air', 'Tinggi air tangki pendam (pasta air) · cm', { inputMode: 'decimal', required: true, placeholder: '0' })}
                    </div><Conversion density={data.density_awal} suhu={data.suhu_awal} baseUrl={baseUrl} /></>
                    : tahap === 'penerimaan' ? <>
                        <div className="row mt-3">
                            {input('waktu_penerimaan', 'Tanggal dan Jam Penerimaan', { type: 'datetime-local', required: true })}
                            {input('nomor_mobil_tangki', 'No. Mobil Tangki', { required: true, maxLength: 32 })}
                            {input('nomor_pnbp', 'No. PNBP', { required: true, maxLength: 64 })}
                            {input('density_15_pnbp', 'a · Density std 15 °C pada PNBP', { ...numeric, required: true, placeholder: 'Contoh: 0,7393' })}
                            {input('density_penerimaan', 'Density observed penerimaan (hidrometer)', { ...numeric, required: true })}
                            {input('suhu_penerimaan', 'Suhu observed penerimaan (°C)', { inputMode: 'decimal', required: true })}
                        </div>
                        <Conversion density={data.density_penerimaan} suhu={data.suhu_penerimaan} pnbp={data.density_15_pnbp} baseUrl={baseUrl} />
                        <div className="form-group"><div className="custom-control custom-checkbox"><input className="custom-control-input" id="truk_berwenang" type="checkbox" checked={data.truk_berwenang} onChange={e => setData('truk_berwenang', e.target.checked)} />
                            <label className="custom-control-label" htmlFor="truk_berwenang">Truk berwenang untuk mengirim BBM</label></div></div>
                        <div className="row">
                            {input('volume_pnbp', 'Volume PNBP (liter) · opsional', { inputMode: 'decimal', hint: 'Isi bersama volume diterima untuk memeriksa batas selisih 0,15%.' })}
                            {input('volume_diterima', 'Volume diterima (liter) · opsional', { inputMode: 'decimal' })}
                        </div>
                    </> : <>
                        <p>Penerimaan terkait: <b>{pemeriksaan?.nomor_mobil_tangki}</b> · PNBP <b>{pemeriksaan?.nomor_pnbp}</b> · {pemeriksaan?.waktu_penerimaan?.replace('T', ' ').slice(0,16)}. Persetujuan Penerimaan tidak mengunci pengisian Akhir.</p>
                        <div className="row">
                            {input('waktu_akhir', 'Tanggal dan Jam Pemeriksaan Akhir', { type: 'datetime-local', required: true })}
                            {input('density_akhir', 'Density observed pasca penerimaan', { ...numeric, required: true })}
                            {input('suhu_akhir', 'Suhu observed pasca penerimaan (°C)', { inputMode: 'decimal', required: true })}
                        </div><Conversion density={data.density_akhir} suhu={data.suhu_akhir} baseUrl={baseUrl} />
                    </>}
                    <div className="form-group"><label htmlFor={noteKey}>Catatan / tindak lanjut · {isPagi ? 'Pagi' : tahap}</label>
                        <textarea id={noteKey} className="form-control" style={{ minHeight: 100 }} value={data[noteKey]} onChange={e => setData(noteKey, e.target.value)} maxLength={4000} /></div>
                    <p className="text-muted">Petunjuk formulir: 1 kaleng sampel per kompartemen. Tolak penerimaan jika truk tidak berwenang, selisih density &gt; 0,005 g/cm³, atau selisih volume &gt; 0,15%. Catat hasil pemeriksaan dan tindak lanjutnya.</p>
                    <button className="btn btn-primary mr-2" disabled={processing || (!record && (!selected || !selected.nama_produk))}>{processing ? 'Menyimpan…' : `Simpan Density ${isPagi ? 'Pagi' : tahap === 'penerimaan' ? 'Penerimaan' : 'Akhir'}`}</button>
                    <a className="btn btn-light" href={record ? `${baseUrl}/${record.kualitas_minyak_id}` : baseUrl}>Kembali</a>
                </div></div>
            </>}
        </form>
    </AppLayout>;
}
