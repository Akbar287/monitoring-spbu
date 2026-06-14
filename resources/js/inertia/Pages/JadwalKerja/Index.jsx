import { usePage } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '../../Layouts/AppLayout';
import MonthCalendar from '../../Components/MonthCalendar';

// Penjadwalan Pegawai — port halaman fullCalendar + fireModal lama:
//  - klik pegawai  -> POST api/jadwalkerja/cek {member_id} -> events kalender
//  - "Jadwalkan"   -> panel form: jabatan + baris [tanggal|jam kerja|nozzle]
//                     (tambah/hapus baris) -> POST api jadwal -> events baru
//  - klik event    -> konfirmasi -> POST api/jadwalkerja/delete -> events baru
// Endpoint & payload identik dengan implementasi jQuery.
const todayStr = () => {
    const d = new Date();
    const p = (n) => (n < 10 ? '0' + n : n);
    return `${d.getFullYear()}-${p(d.getMonth() + 1)}-${p(d.getDate())}`;
};

export default function Index({ title, breadcrumbs = [], employees = [], jamKerjaOptions = [], nozzleOptions = [], api, links }) {
    const { urls } = usePage().props;
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

    const [selected, setSelected] = useState(null); // {id, nama, jabatan[]}
    const [events, setEvents] = useState([]);
    const [showForm, setShowForm] = useState(false);
    const [jabatan, setJabatan] = useState('');
    const [rows, setRows] = useState([]);
    const [alert, setAlert] = useState(null);
    const [busy, setBusy] = useState(false);

    const defaultRow = () => ({
        tanggal: todayStr(),
        jam_kerja: jamKerjaOptions[0] ? String(jamKerjaOptions[0].value) : '',
        nozzle: nozzleOptions[0] ? String(nozzleOptions[0].value) : '',
    });

    const apiPost = (url, body) =>
        fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrf,
            },
            body: JSON.stringify(body),
        }).then((r) => r.json());

    const pickEmployee = (emp) => {
        setSelected(emp);
        setShowForm(false);
        setAlert(null);
        setJabatan(emp.jabatan[0] ?? '0');
        apiPost(api.cek, { member_id: emp.id })
            .then((res) => setEvents(res.schedule || []))
            .catch(() => setEvents([]));
    };

    const submitJadwal = () => {
        if (rows.some((r) => !r.tanggal)) {
            setAlert({ color: 'danger', text: 'Tanggal Wajib Diisi!' });
            return;
        }
        if (!selected.jabatan.length) {
            setAlert({ color: 'danger', text: 'Pegawai Tidak Memiliki Jabatan!' });
            return;
        }
        setBusy(true);
        apiPost(api.store, {
            member_id: selected.id,
            jabatan,
            tanggal: rows.map((r) => r.tanggal),
            jam_kerja: rows.map((r) => r.jam_kerja),
            nozzle: rows.map((r) => r.nozzle),
        })
            .then((res) => {
                setEvents(res.schedule || []);
                setAlert({ color: 'success', text: 'Pegawai Telah Terjadwal' });
                setRows([defaultRow()]);
            })
            .catch(() => setAlert({ color: 'danger', text: 'Gagal menyimpan jadwal. Periksa koneksi anda.' }))
            .finally(() => setBusy(false));
    };

    const onEventClick = (ev) => {
        if (!window.confirm('Ingin Menghapus Jadwal Kerja Terpilih?')) return;
        apiPost(api.delete, {
            date: String(ev.start).slice(0, 10),
            title: ev.title,
            member_id: selected.id,
        })
            .then((res) => {
                if (res.status == true) setEvents(res.schedule || []);
            })
            .catch(() => {});
    };

    const setRow = (i, k, v) => setRows(rows.map((r, j) => (j === i ? { ...r, [k]: v } : r)));

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
                <div className="card">
                    <div className="card-header">
                        <h4>Pegawai</h4>
                        <div className="card-header-action">
                            <a href={links.qr} className="btn btn-primary btn-sm mr-2">
                                <i className="fas fa-qrcode mr-1"></i> Kode QR
                            </a>
                            <a href={links.upload} className="btn btn-primary btn-sm">
                                <i className="fas fa-upload mr-1"></i> Upload File
                            </a>
                        </div>
                    </div>
                    <div className="card-body">
                        <div className="row">
                            {employees.map((emp) => (
                                <div key={emp.id} className="col-lg-3 col-md-4 col-sm-6 col-12 mb-3">
                                    <button
                                        type="button"
                                        onClick={() => pickEmployee(emp)}
                                        className="w-100 text-left"
                                        style={{
                                            border: selected?.id === emp.id ? '2px solid #6777ef' : '1px solid rgba(31,36,64,.1)',
                                            background: selected?.id === emp.id ? 'rgba(103,119,239,.08)' : '#fff',
                                            borderRadius: 12,
                                            padding: '12px 14px',
                                            cursor: 'pointer',
                                        }}
                                    >
                                        <div style={{ fontWeight: 700, fontSize: 14 }}>{emp.nama}</div>
                                        <div className="text-muted" style={{ fontSize: 12 }}>
                                            {emp.jabatan.length ? emp.jabatan.join(', ') : 'Tidak ada Jabatan'}
                                        </div>
                                    </button>
                                </div>
                            ))}
                            {employees.length === 0 && <p className="text-muted ml-3">Tidak ada pegawai.</p>}
                        </div>
                    </div>
                </div>

                {selected && (
                    <div className="card">
                        <div className="card-header">
                            <h4>Jadwal kerja {selected.nama}</h4>
                            <div className="card-header-action">
                                <button
                                    type="button"
                                    className="btn btn-primary btn-sm"
                                    onClick={() => {
                                        setShowForm(!showForm);
                                        setAlert(null);
                                        if (!showForm) setRows([defaultRow()]);
                                    }}
                                >
                                    <i className={`fas ${showForm ? 'fa-times' : 'fa-plus'} mr-1`}></i>
                                    {showForm ? 'Tutup' : 'Jadwalkan'}
                                </button>
                            </div>
                        </div>
                        <div className="card-body">
                            {alert && <div className={`alert alert-${alert.color}`}>{alert.text}</div>}

                            {showForm && (
                                <div className="mb-4 p-3" style={{ background: '#f6f7fd', borderRadius: 12 }}>
                                    <div className="form-group row align-items-center">
                                        <label className="col-md-3 col-form-label text-md-right">Jabatan</label>
                                        <div className="col-md-8">
                                            <select
                                                className="custom-select"
                                                value={jabatan}
                                                disabled={!selected.jabatan.length}
                                                onChange={(e) => setJabatan(e.target.value)}
                                            >
                                                {selected.jabatan.length ? (
                                                    selected.jabatan.map((j) => (
                                                        <option key={j} value={j}>
                                                            {j}
                                                        </option>
                                                    ))
                                                ) : (
                                                    <option value="0">Tidak ada Jabatan</option>
                                                )}
                                            </select>
                                        </div>
                                    </div>

                                    {rows.map((r, i) => (
                                        <div key={i} className="form-group row align-items-center">
                                            <div className="col-md-3 offset-md-0 col-form-label text-md-right text-muted">
                                                Pengaturan #{i + 1}
                                            </div>
                                            <div className="col-md-3">
                                                <input
                                                    type="date"
                                                    className="form-control"
                                                    value={r.tanggal}
                                                    onChange={(e) => setRow(i, 'tanggal', e.target.value)}
                                                />
                                            </div>
                                            <div className="col-md-3">
                                                <select className="custom-select" value={r.jam_kerja} onChange={(e) => setRow(i, 'jam_kerja', e.target.value)}>
                                                    {jamKerjaOptions.map((o) => (
                                                        <option key={String(o.value)} value={String(o.value)}>
                                                            {o.label}
                                                        </option>
                                                    ))}
                                                </select>
                                            </div>
                                            <div className="col-md-3">
                                                <select className="custom-select" value={r.nozzle} onChange={(e) => setRow(i, 'nozzle', e.target.value)}>
                                                    {nozzleOptions.map((o) => (
                                                        <option key={String(o.value)} value={String(o.value)}>
                                                            {o.label}
                                                        </option>
                                                    ))}
                                                </select>
                                            </div>
                                        </div>
                                    ))}

                                    <div className="d-flex" style={{ gap: 8 }}>
                                        <button type="button" className="btn btn-success btn-sm" onClick={() => setRows([...rows, defaultRow()])}>
                                            <i className="fas fa-plus mr-1"></i> Tambah Pengaturan
                                        </button>
                                        {rows.length > 1 && (
                                            <button type="button" className="btn btn-danger btn-sm" onClick={() => setRows(rows.slice(0, -1))}>
                                                <i className="fas fa-minus mr-1"></i> Hapus Pengaturan
                                            </button>
                                        )}
                                        <button type="button" className="btn btn-primary btn-sm ml-auto" disabled={busy} onClick={submitJadwal}>
                                            {busy ? (
                                                <>
                                                    <i className="fas fa-circle-notch fa-spin mr-1"></i> Menyimpan...
                                                </>
                                            ) : (
                                                <>
                                                    <i className="fas fa-save mr-1"></i> Simpan Jadwal
                                                </>
                                            )}
                                        </button>
                                    </div>
                                </div>
                            )}

                            <MonthCalendar events={events} onEventClick={onEventClick} />
                            <small className="text-muted d-block mt-2">Klik event pada kalender untuk menghapus jadwal.</small>
                        </div>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
