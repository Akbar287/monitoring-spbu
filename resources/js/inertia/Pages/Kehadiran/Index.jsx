import { router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '../../Layouts/AppLayout';
import DataTable from '../../Components/DataTable';

// Kehadiran operator: kartu "jadwal sedang berjalan" (jika ada presensi yang
// belum ditutup) + tabel jadwal + filter tanggal. Halaman detail/QR scanner
// (kehadiran.show) tetap Blade — link dari sini full page load.
export default function Index({ title, breadcrumbs = [], dateFilter, ongoing, table }) {
    const { urls } = usePage().props;
    const currentPath = usePage().url.split('?')[0];
    const [mulai, setMulai] = useState(dateFilter?.mulai ?? '');
    const [selesai, setSelesai] = useState(dateFilter?.selesai ?? '');

    const applyFilter = (e) => {
        e.preventDefault();
        router.get(currentPath, { waktu_mulai: mulai, waktu_selesai: selesai }, { preserveScroll: true });
    };

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
                {ongoing && (
                    <div className="card mb-3" style={{ borderLeft: '4px solid #47c363' }}>
                        <div className="card-body py-3 d-flex flex-wrap align-items-center justify-content-between" style={{ gap: 12 }}>
                            <div>
                                <h6 className="mb-1 text-success">
                                    <i className="fas fa-business-time mr-1"></i> Jadwal kerja Anda sedang berjalan
                                </h6>
                                <div className="text-muted" style={{ fontSize: 13.5 }}>
                                    {ongoing.nama} · {ongoing.jamKerja} · mulai {ongoing.startTime} ({ongoing.tanggal})
                                </div>
                            </div>
                            <a href={ongoing.url} className="btn btn-success">
                                <i className="fas fa-arrow-right mr-1"></i> Lanjutkan Presensi
                            </a>
                        </div>
                    </div>
                )}

                <div className="card mb-3">
                    <div className="card-body py-3">
                        <form onSubmit={applyFilter} className="form-inline" style={{ gap: 12 }}>
                            <div className="form-group" style={{ gap: 8 }}>
                                <label className="text-muted" style={{ fontSize: 13 }}>
                                    <i className="fas fa-calendar mr-1"></i> Dari
                                </label>
                                <input type="date" className="form-control form-control-sm" value={mulai} onChange={(e) => setMulai(e.target.value)} />
                            </div>
                            <div className="form-group" style={{ gap: 8 }}>
                                <label className="text-muted" style={{ fontSize: 13 }}>
                                    Sampai
                                </label>
                                <input type="date" className="form-control form-control-sm" value={selesai} onChange={(e) => setSelesai(e.target.value)} />
                            </div>
                            <button type="submit" className="btn btn-primary btn-sm">
                                <i className="fas fa-filter mr-1"></i> Filter
                            </button>
                        </form>
                    </div>
                </div>

                <div className="card">
                    <div className="card-header">
                        <h4>Jadwal Kehadiran</h4>
                    </div>
                    <div className="card-body">
                        <DataTable url={table.url} columns={table.columns} />
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
