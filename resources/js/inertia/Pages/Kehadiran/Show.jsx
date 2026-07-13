import { router, usePage } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';
import AppLayout from '../../Layouts/AppLayout';

const Field = ({ label, value, textarea = false }) => (
    <div className="form-group row align-items-center">
        <label className="col-md-4 text-md-right text-left">{label}</label>
        <div className="col-lg-4 col-md-6">
            {textarea ? (
                <textarea className="form-control" readOnly value={value || ''} />
            ) : (
                <input type="text" className="form-control" readOnly value={value || ''} />
            )}
        </div>
    </div>
);

const Check = ({ title, text, backUrl }) => (
    <div className="row justify-content-center align-items-center">
        <div className="col-12 col-md-8 col-lg-6">
            <div className="text-center">
                <i className="fas fa-check-circle mb-3" style={{ color: 'green', fontSize: 80 }}></i>
                <h5 className="mb-2">{title}</h5>
                <p className="mb-0">
                    {text} <a href={backUrl}>sebelumnya</a>, atau keluar aplikasi.
                </p>
            </div>
        </div>
    </div>
);

export default function Show({ title, breadcrumbs = [], schedule, utility, ongoing, backUrl, api }) {
    const { urls } = usePage().props;
    const [step, setStep] = useState(schedule.mode === 'out' ? 'out-info' : 'info');
    const [busy, setBusy] = useState(false);
    const [message, setMessage] = useState(null);
    const [cameras, setCameras] = useState([]);
    const [cameraId, setCameraId] = useState('');
    const videoRef = useRef(null);
    const scannerRef = useRef(null);

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

    useEffect(() => {
        if (step !== 'scan') return;
        let mounted = true;

        import('qr-scanner').then(({ default: QrScanner }) => {
            if (!mounted || !videoRef.current) return;
            QrScanner.listCameras(true).then((list) => {
                if (!mounted) return;
                setCameras(list);
                if (list[0]) setCameraId(list[0].id);
            });

            scannerRef.current = new QrScanner(videoRef.current, (result) => {
                const value = typeof result === 'string' ? result : result?.data;
                if (value) submitAttendance(value);
            });
        });

        return () => {
            mounted = false;
            scannerRef.current?.stop();
            scannerRef.current?.destroy();
            scannerRef.current = null;
        };
    }, [step]);

    useEffect(() => {
        if (cameraId && scannerRef.current) {
            scannerRef.current.setCamera(cameraId).catch(() => {});
        }
    }, [cameraId]);

    const requestJson = (url, data = {}) =>
        fetch(url, {
            method: 'PUT',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrf,
            },
            body: JSON.stringify(data),
        }).then((r) => r.json());

    const submitAttendance = (codeValue) => {
        if (busy) return;
        scannerRef.current?.stop();
        setBusy(true);
        setMessage(null);
        requestJson(api.in, { abs: codeValue, code: schedule.id })
            .then((res) => {
                if (res.status) {
                    setStep('complete');
                    return;
                }
                setMessage(res.message === 'qrcode wrong' ? 'QR Code salah.' : 'QR Code tidak valid.');
            })
            .catch(() => setMessage('Gagal mengirim absensi. Periksa koneksi anda.'))
            .finally(() => setBusy(false));
    };

    const closeAttendance = () => {
        if (busy) return;
        setBusy(true);
        requestJson(api.out)
            .then((res) => {
                if (res.status) setStep('out-complete');
            })
            .catch(() => setMessage('Gagal mengirim absen keluar. Periksa koneksi anda.'))
            .finally(() => setBusy(false));
    };

    const startCamera = () => {
        setMessage(null);
        scannerRef.current?.start().catch(() => setMessage('Kamera tidak dapat dibuka.'));
    };

    const stopCamera = () => scannerRef.current?.stop();

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
                    <div className="breadcrumb-item">Jadwal Kerja</div>
                </div>
            </div>

            <div className="section-body">
                <div className="card">
                    <div className="card-header">
                        <h4>Jadwal kerja Anda</h4>
                    </div>
                    <div className="card-body">
                        {message && <div className="alert alert-danger">{message}</div>}

                        {schedule.mode === 'out' ? (
                            <>
                                {step === 'out-info' && (
                                    <>
                                        <Field label="Nama" value={schedule.nama} />
                                        <Field label="Jabatan" value={schedule.jabatan} />
                                        <Field label="Tanggal" value={schedule.tanggal} />
                                        <Field label="Jam Kerja" value={schedule.jamKerja} />
                                        <Field label="Jam Masuk" value={schedule.startTime} />
                                        <Field label="Deskripsi" value={schedule.deskripsi} textarea />
                                        <div className="form-group row">
                                            <div className="col-12 text-center">
                                                <button type="button" className="btn btn-primary" disabled={busy} onClick={closeAttendance}>
                                                    Absen Keluar <i className="fas fa-check ml-1"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </>
                                )}
                                {step === 'out-complete' && (
                                    <Check title="Anda telah berhasil Absen Keluar" text="Silakan kembali ke menu" backUrl={backUrl} />
                                )}
                            </>
                        ) : (
                            <>
                                {step === 'info' && (
                                    <>
                                        <Field label="Nama" value={schedule.nama} />
                                        <Field label="Jabatan" value={schedule.jabatan} />
                                        <Field label="Tanggal" value={schedule.tanggal} />
                                        <Field label="Jam Kerja" value={schedule.jamKerja} />
                                        <Field label="Deskripsi" value={schedule.deskripsi} textarea />
                                        {ongoing && <div className="alert alert-danger text-center">Anda perlu absen keluar terlebih dahulu</div>}
                                        <div className="form-group row">
                                            <div className="col-md-4"></div>
                                            <div className="col-lg-4 col-md-6 text-right">
                                                <button type="button" className="btn btn-primary" disabled={ongoing} onClick={() => setStep('device')}>
                                                    Berikutnya <i className="fas fa-arrow-right ml-1"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </>
                                )}

                                {step === 'device' && (
                                    <>
                                        <Field label="Jenis Perangkat & Browser" value={`${utility.typeDevice} (${utility.browser})`} />
                                        <Field label="Sistem Operasi" value={utility.osAndVersionOs} />
                                        <Field label="Alamat IP" value={utility.ip} />
                                        <Field label="Kota" value={utility.city} />
                                        <div className="form-group row">
                                            <div className="col-md-4"></div>
                                            <div className="col-lg-4 col-md-6 text-right">
                                                <button type="button" className="btn btn-danger mr-2" onClick={() => setStep('info')}>
                                                    <i className="fas fa-arrow-left mr-1"></i> Sebelumnya
                                                </button>
                                                <button type="button" className="btn btn-primary" onClick={() => setStep('scan')}>
                                                    Berikutnya <i className="fas fa-arrow-right ml-1"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </>
                                )}

                                {step === 'scan' && (
                                    <>
                                        <div className="form-group row align-items-center">
                                            <div className="col-md-4"></div>
                                            <div className="col-lg-4 col-md-6">
                                                <video ref={videoRef} style={{ width: '100%' }}></video>
                                            </div>
                                        </div>
                                        <div className="form-group row align-items-center">
                                            <label className="col-md-4 text-md-right text-left">Kamera</label>
                                            <div className="col-lg-4 col-md-6">
                                                <select className="custom-select" value={cameraId} onChange={(e) => setCameraId(e.target.value)}>
                                                    {cameras.map((camera) => (
                                                        <option key={camera.id} value={camera.id}>
                                                            {camera.label || camera.id}
                                                        </option>
                                                    ))}
                                                </select>
                                            </div>
                                        </div>
                                        <div className="form-group row align-items-center">
                                            <label className="col-md-4 text-md-right text-left">Pilihan</label>
                                            <div className="col-lg-4 col-md-6">
                                                <button type="button" className="btn btn-primary mr-2" disabled={busy} onClick={startCamera}>
                                                    Mulai Kamera
                                                </button>
                                                <button type="button" className="btn btn-primary" disabled={busy} onClick={stopCamera}>
                                                    Matikan Kamera
                                                </button>
                                                {busy && <div className="alert alert-info mt-2">Loading ...</div>}
                                            </div>
                                        </div>
                                        <div className="form-group row">
                                            <div className="col-md-4"></div>
                                            <div className="col-lg-4 col-md-6 text-right">
                                                <button type="button" className="btn btn-danger" onClick={() => setStep('device')}>
                                                    <i className="fas fa-arrow-left mr-1"></i> Sebelumnya
                                                </button>
                                            </div>
                                        </div>
                                    </>
                                )}

                                {step === 'complete' && (
                                    <Check title="Anda telah berhasil Absen Masuk" text="Silakan kembali ke menu" backUrl={backUrl} />
                                )}
                            </>
                        )}
                    </div>
                </div>

                <button type="button" className="btn btn-light" onClick={() => router.visit(backUrl)}>
                    <i className="fas fa-arrow-left mr-1"></i> Kembali
                </button>
            </div>
        </AppLayout>
    );
}
