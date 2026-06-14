import { usePage } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import MonthCalendar from '../../Components/MonthCalendar';

// Stok Summary: kalender bulanan; klik tanggal -> halaman ringkasan stok
// tanggal tsb (paritas dayClick fullCalendar lama; API event lama kosong).
export default function Index({ title, breadcrumbs = [], showBase }) {
    const { urls } = usePage().props;

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
                        <h4>
                            <i className="fas fa-calendar-day text-primary mr-2"></i> Pilih tanggal untuk melihat ringkasan stok
                        </h4>
                    </div>
                    <div className="card-body">
                        <MonthCalendar onDayClick={(d) => (window.location.href = showBase + '/' + d)} />
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
