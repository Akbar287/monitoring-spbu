import { useEffect, useRef, useState } from 'react';
import AppLayout from '../../Layouts/AppLayout';
import MenuTile from '../../Components/MenuTile';
import { buildChart } from '../../lib/charts';

// Dashboard /home.
// Manajemen: kartu statistik + 4 chart (data dari endpoint lama /api/chart)
// + menu + tabel pembayaran + daftar pengiriman. Operator/Keamanan: kartu
// sambutan + menu. Paritas data & endpoint dengan home.blade.php lama.
function StatCard({ icon, color, label, value }) {
    return (
        <div className="col-lg-3 col-md-6 col-sm-6 col-12">
            <div className="card card-statistic-1">
                <div className={`card-icon bg-${color}`}>
                    <i className={`fas fa-${icon}`}></i>
                </div>
                <div className="card-wrap">
                    <div className="card-header">
                        <h4>{label}</h4>
                    </div>
                    <div className="card-body">{value}</div>
                </div>
            </div>
        </div>
    );
}

function HomeChart({ id, type, payload, opts }) {
    const ref = useRef(null);
    useEffect(() => {
        if (!ref.current || !payload) return undefined;
        const data =
            type === 'doughnut' || (type === 'bar' && !payload.multi)
                ? { labels: payload.labels, data: payload.datasets?.[0]?.data ?? [] }
                : { labels: payload.labels, datasets: payload.datasets ?? [] };
        const chart = buildChart(ref.current, payload.multi ? 'stackedBar' : type, data, opts || {});
        return () => chart && chart.destroy();
    }, [type, payload, opts]);

    if (!payload) {
        return <div className="chart-empty">Memuat chart...</div>;
    }
    return <canvas id={id} ref={ref}></canvas>;
}

export default function Index({ isManagement, nama, level, stats, menus = [], pembayaran = [], delivery = [], links }) {
    const [charts, setCharts] = useState(null);

    useEffect(() => {
        if (!isManagement) return;
        fetch(links.chartApi, {
            credentials: 'same-origin',
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then((r) => r.json())
            .then((res) => setCharts(res.data))
            .catch((err) => console.error(err));
    }, [isManagement, links.chartApi]);

    const chartCard = (title, icon, color, badge, child) => (
        <div className="card">
            <div className="card-header">
                <h4>
                    <i className={`fas fa-${icon} text-${color} mr-2`}></i> {title}
                </h4>
                {badge && (
                    <div className="card-header-action">
                        <span className={`badge badge-${color}`}>{badge}</span>
                    </div>
                )}
            </div>
            <div className="card-body">
                <div className="chart-shell">{child}</div>
            </div>
        </div>
    );

    return (
        <AppLayout title="Dashboard">
            {isManagement ? (
                <>
                    <div className="row">
                        <StatCard icon="user" color="primary" label="Total Member" value={stats.member} />
                        <StatCard icon="tint" color="danger" label="Produk" value={stats.produk} />
                        <StatCard icon="truck" color="warning" label="Pengiriman" value={stats.pengiriman} />
                        <StatCard icon="thermometer-half" color="success" label="Dombak" value={stats.dombak} />
                    </div>

                    <div className="row">
                        <div className="col-12">
                            <div className="section-title mb-0">Dashboard Stok Hari Ini</div>
                            <p className="text-muted">Ringkasan persediaan BBM dan pergerakan stok per shift.</p>
                        </div>
                        <div className="col-lg-5 col-12">
                            {chartCard('Komposisi Stok BBM', 'gas-pump', 'primary', 'Liter',
                                <HomeChart id="chart-stok" type="doughnut" payload={charts?.chart_stok} opts={{ unit: 'L' }} />)}
                        </div>
                        <div className="col-lg-7 col-12">
                            {chartCard('Penjualan Stok per Shift', 'layer-group', 'warning', 'Liter',
                                <HomeChart id="chart-stok-split" type="bar" payload={charts ? { ...charts.chart_stok_split, multi: true } : null} opts={{ unit: 'L' }} />)}
                        </div>
                    </div>

                    <div className="row">
                        <div className="col-12">
                            <div className="section-title mb-0">Dashboard Penjualan Hari Ini</div>
                            <p className="text-muted">Realisasi penjualan berdasarkan nozzle dan shift kerja.</p>
                        </div>
                        <div className="col-lg-7 col-12">
                            {chartCard('Penjualan per Nozzle', 'coins', 'success', 'Rupiah',
                                <HomeChart id="chart-penjualan" type="bar" payload={charts?.chart_penjualan} opts={{ money: true }} />)}
                        </div>
                        <div className="col-lg-5 col-12">
                            {chartCard('Penjualan per Shift', 'receipt', 'danger', 'Rupiah',
                                <HomeChart id="chart-penjualan-split" type="bar" payload={charts ? { ...charts.chart_penjualan_split, multi: true } : null} opts={{ money: true }} />)}
                        </div>
                    </div>
                </>
            ) : (
                <div className="row">
                    <div className="col-md-6 col-sm-6 col-12">
                        <div className="card card-statistic-1">
                            <div className="card-icon bg-primary">
                                <i className="far fa-user"></i>
                            </div>
                            <div className="card-wrap">
                                <div className="card-header">
                                    <h4>Selamat Datang</h4>
                                </div>
                                <div className="card-body">{nama}</div>
                            </div>
                        </div>
                    </div>
                    <div className="col-md-6 col-sm-6 col-12">
                        <div className="card card-statistic-1">
                            <div className="card-icon bg-danger">
                                <i className="fas fa-address-card"></i>
                            </div>
                            <div className="card-wrap">
                                <div className="card-header">
                                    <h4>Jabatan</h4>
                                </div>
                                <div className="card-body">{level}</div>
                            </div>
                        </div>
                    </div>
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

            {isManagement && (
                <div className="row">
                    <div className="col-md-8">
                        <div className="card">
                            <div className="card-header">
                                <h4>Pembayaran</h4>
                                <div className="card-header-action">
                                    <a href={links.pembayaranIndex} className="btn btn-danger">
                                        Lihat lebih <i className="fas fa-chevron-right"></i>
                                    </a>
                                </div>
                            </div>
                            <div className="card-body p-0">
                                <div className="table-responsive table-invoice">
                                    <table className="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Tanggal</th>
                                                <th>Nama Produk</th>
                                                <th>Nominal</th>
                                                <th>Status</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {pembayaran.length > 0 ? (
                                                pembayaran.map((pe, i) => (
                                                    <tr key={i}>
                                                        <td>{pe.tanggal}</td>
                                                        <td className="font-weight-600">{pe.produk}</td>
                                                        <td>Rp. {pe.total}</td>
                                                        <td>
                                                            <div className="badge badge-warning">Unpaid</div>
                                                        </td>
                                                        <td>
                                                            <a href={pe.url} className="btn btn-primary btn-sm">
                                                                Detail
                                                            </a>
                                                        </td>
                                                    </tr>
                                                ))
                                            ) : (
                                                <tr>
                                                    <td colSpan={5}>
                                                        <p className="text-center mb-0 py-3">Tidak ada yang perlu dibayarkan</p>
                                                    </td>
                                                </tr>
                                            )}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div className="col-md-4">
                        <div className="card card-hero">
                            <div className="card-header">
                                <div className="card-icon">
                                    <i className="fas fa-truck"></i>
                                </div>
                                <h4>{delivery.length}</h4>
                                <div className="card-description">Dalam Pengiriman</div>
                            </div>
                            <div className="card-body p-0">
                                <div className="tickets-list">
                                    {delivery.length > 0 ? (
                                        delivery.map((d, i) => (
                                            <a key={i} href={d.url} className="ticket-item">
                                                <div className="ticket-title">
                                                    <h4>{d.produk}</h4>
                                                </div>
                                                <div className="ticket-info">
                                                    <div>{d.jumlah}</div>
                                                    <div className="bullet"></div>
                                                    <div>{d.tanggal}</div>
                                                </div>
                                            </a>
                                        ))
                                    ) : (
                                        <p className="text-center my-5">Tidak ada Pengiriman</p>
                                    )}
                                    <a href={links.pengirimanIndex} className="ticket-item ticket-more">
                                        View All <i className="fas fa-chevron-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </AppLayout>
    );
}
