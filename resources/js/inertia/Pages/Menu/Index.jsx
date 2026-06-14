import AppLayout from '../../Layouts/AppLayout';
import ChartCard from '../../Components/ChartCard';
import MenuTile from '../../Components/MenuTile';
import { usePage } from '@inertiajs/react';

// Halaman menu generik: dipakai oleh /master, /procurement, /stok, /tera,
// /setoran, /artikel, /financial, /jadwal_absensi, /konfigurasi.
export default function Index({ title, description, menus = [], charts = [] }) {
    const { urls } = usePage().props;

    return (
        <AppLayout title={title}>
            <div className="section-header">
                <h1>{title}</h1>
                <div className="section-header-breadcrumb">
                    <div className="breadcrumb-item">
                        <a href={urls.home}>Dashboard</a>
                    </div>
                    <div className="breadcrumb-item">{title}</div>
                </div>
            </div>

            {charts.length > 0 && (
                <div className="row">
                    <div className="col-12">
                        <div className="section-title mb-0">Ringkasan {title}</div>
                        {description && <p className="text-muted">{description}</p>}
                    </div>
                    {charts.map((c) => (
                        <ChartCard key={c.id || c.title} {...c} />
                    ))}
                </div>
            )}

            <div className="row">
                <div className="col-12">
                    <div className="section-title">Menu</div>
                </div>
                {menus.map((m) => (
                    <MenuTile key={m.nama + m.url} menu={m} />
                ))}
            </div>
        </AppLayout>
    );
}
