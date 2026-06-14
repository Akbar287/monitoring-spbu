import { usePage } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';

// Detail Open Penjualan — port show.blade.php.
// Aksi Konfirmasi & Hapus memakai <form> HTML murni (full reload) karena
// target redirect-nya (penitipan show / index lama) sebagian masih Blade —
// pola coexistence yang aman, identik dengan perilaku blade lama.
const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

export default function OpenShow({ title, breadcrumbs = [], backUrl, info, pj = [], standMeters = [], isPJ, actions }) {
    const { urls } = usePage().props;

    const infoRow = (label, value, type = 'text') => (
        <div className="form-group row align-items-center justify-content-center">
            <label className="col-md-4 col-form-label text-md-right">{label}</label>
            <div className="col-md-6">
                <input type={type} readOnly className="form-control" value={value ?? ''} />
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
                    <div className="breadcrumb-item">Detail</div>
                </div>
            </div>

            <div className="section-body">
                <div className="row justify-content-center">
                    <div className="col-md-12">
                        <div className="card">
                            <div className="card-header">
                                <h4>Detail Open Penjualan</h4>
                            </div>
                            <div className="card-body">
                                {infoRow('SPBU', info.spbu)}
                                {infoRow('Tanggal', info.tanggal, 'date')}
                                {infoRow('Jam Kerja', info.jam_kerja)}
                            </div>
                        </div>
                    </div>

                    <div className="col-md-12">
                        <div className="card">
                            <div className="card-header">
                                <h4>PJ Operasional</h4>
                            </div>
                            <div className="card-body">
                                {pj.length > 0 ? (
                                    <div className="table-responsive">
                                        <table className="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Jabatan</th>
                                                    <th>Nama</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {pj.map((p, i) => (
                                                    <tr key={i}>
                                                        <td>{p.jabatan}</td>
                                                        <td>{p.nama}</td>
                                                    </tr>
                                                ))}
                                            </tbody>
                                        </table>
                                    </div>
                                ) : (
                                    <p className="mb-0">PJ Operasional belum di tentukan</p>
                                )}
                            </div>
                        </div>
                    </div>

                    <div className="col-md-12">
                        {standMeters.length === 0 ? (
                            <div className="card card-primary">
                                <div className="card-header">
                                    <h4>Detail Penjualan</h4>
                                </div>
                                <div className="card-body">
                                    <p className="mb-0">Tidak ada Stand Meter yang ditautkan</p>
                                </div>
                            </div>
                        ) : (
                            standMeters.map((sm, i) => (
                                <div className="card" key={i}>
                                    <div className="card-header">
                                        <h4>Stand Meter #{i + 1}</h4>
                                    </div>
                                    <div className="card-body">
                                        {infoRow('Nozzle', sm.nozzle)}
                                        {infoRow('Stand Meter Awal', sm.awal)}
                                        {infoRow('Stand Meter Akhir', sm.akhir)}
                                    </div>
                                </div>
                            ))
                        )}
                    </div>

                    <div className="col-md-12">
                        <div className="card">
                            <div className="card-header">
                                <h4>Pilihan</h4>
                            </div>
                            <div className="card-body">
                                <div className="row mb-0">
                                    <div className="col-md-8 offset-md-2">
                                        <div className="alert alert-warning">
                                            Pastikan Stand Meter dan PJ Operasional sudah terisi dengan benar. Karena di
                                            Fase berikutnya kedua data tersebut tidak dapat diubah/dihapus
                                        </div>
                                    </div>
                                    <div className="col-md-8 offset-md-2 d-flex flex-wrap align-items-end" style={{ gap: 8 }}>
                                        <a href={backUrl} className="btn btn-primary">
                                            <i className="fas fa-arrow-left"></i> Kembali
                                        </a>
                                        {isPJ && (
                                            <>
                                                <a href={actions.pj} className="btn btn-info">
                                                    <i className="fas fa-users"></i> PJ Operasional
                                                </a>
                                                <a href={actions.detail} className="btn btn-warning">
                                                    <i className="fas fa-thermometer-full"></i> Stand Meter
                                                </a>
                                                <form action={actions.konfirmasi} method="POST">
                                                    <input type="hidden" name="_token" value={csrf()} />
                                                    <input type="hidden" name="_method" value="put" />
                                                    <button type="submit" className="btn btn-success">
                                                        <i className="fas fa-arrow-right"></i> Konfirmasi
                                                    </button>
                                                </form>
                                            </>
                                        )}
                                        <form
                                            action={actions.delete}
                                            method="POST"
                                            onSubmit={(e) => {
                                                if (!window.confirm('Open Penjualan akan dihapus!\n Lanjutkan?')) e.preventDefault();
                                            }}
                                        >
                                            <input type="hidden" name="_token" value={csrf()} />
                                            <input type="hidden" name="_method" value="delete" />
                                            <button type="submit" className="btn btn-danger" title="Hapus Data">
                                                <i className="fas fa-trash"></i> Hapus
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
