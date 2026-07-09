import { useEffect, useRef, useState } from 'react';
import AppLayout from '../../Layouts/AppLayout';
import MenuTile from '../../Components/MenuTile';
import { buildChart, isEmptyData } from '../../lib/charts';

// Dashboard /home.
// Manajemen: kartu statistik + 4 chart (data dari endpoint lama /api/chart)
// + menu + tabel pembayaran + daftar pengiriman. Operator/Keamanan: kartu
// sambutan + menu. Paritas data & endpoint dengan home.blade.php lama.
const numberFormatter = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 });
const compactFormatter = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 1, notation: 'compact', compactDisplay: 'short' });
const dateFormatter = new Intl.DateTimeFormat('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
const timeFormatter = new Intl.DateTimeFormat('id-ID', { hour: '2-digit', minute: '2-digit' });

const asNumber = (v) => Number(v) || 0;
const formatMetric = (item, compact = true) => {
    const value = asNumber(item?.value);
    const formatter = compact ? compactFormatter : numberFormatter;
    if (item?.type === 'money') return `Rp ${formatter.format(value)}`;
    if (item?.type === 'liter') return `${formatter.format(value)} L`;
    return formatter.format(value);
};
const formatDate = (value) => {
    if (!value) return 'Hari ini';
    return dateFormatter.format(new Date(`${value}T00:00:00`));
};
const formatTime = (value) => {
    if (!value) return null;
    return timeFormatter.format(new Date(value));
};

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

function InsightCard({ item }) {
    const context = item?.name && item.name !== '-' ? (item.label === 'Shift Terbesar' ? item.name : `Tertinggi: ${item.name}`) : null;

    return (
        <div className="col-xl-3 col-md-6 col-12">
            <div className={`home-insight home-insight-${item?.tone || 'primary'}`}>
                <span className="home-insight-icon">
                    <i className={`fas fa-${item?.icon || 'chart-bar'}`}></i>
                </span>
                <span className="home-insight-body">
                    <span>{item?.label}</span>
                    <strong>{formatMetric(item)}</strong>
                    {context && <small>{context}</small>}
                </span>
            </div>
        </div>
    );
}

function ChartMetrics({ metrics = [] }) {
    if (!metrics.length) return null;

    return (
        <div className="chart-metrics">
            {metrics.map((metric) => (
                <div className="chart-metric" key={`${metric.label}-${metric.name || 'total'}`}>
                    <span>{metric.label}</span>
                    <strong>{formatMetric(metric)}</strong>
                    {metric.name && metric.name !== '-' && <small>{metric.name}</small>}
                </div>
            ))}
        </div>
    );
}

function prepareChartPayload(type, payload) {
    if (!payload) return null;
    if (type === 'doughnut' || (type === 'bar' && !payload.multi)) {
        return {
            labels: payload.labels,
            data: payload.datasets?.[0]?.data ?? [],
            backgroundColor: payload.datasets?.[0]?.backgroundColor,
        };
    }

    return { labels: payload.labels, datasets: payload.datasets ?? [] };
}

function HomeChart({ id, type, payload, opts, status, emptyMessage }) {
    const ref = useRef(null);
    const chartPayload = prepareChartPayload(type, payload);
    const chartType = payload?.multi ? 'stackedBar' : type;
    const empty = status === 'ready' && isEmptyData(chartType, chartPayload);

    useEffect(() => {
        if (!ref.current || !chartPayload || empty || status !== 'ready') return undefined;
        const chart = buildChart(ref.current, chartType, chartPayload, opts || {});
        return () => chart && chart.destroy();
    }, [chartPayload, chartType, empty, opts, status]);

    if (status === 'loading' || status === 'idle') {
        return <div className="chart-empty">Memuat chart...</div>;
    }

    if (status === 'error') {
        return <div className="chart-empty">Chart belum bisa dimuat</div>;
    }

    if (empty) {
        return <div className="chart-empty">{emptyMessage || 'Belum ada data'}</div>;
    }

    return <canvas id={id} ref={ref}></canvas>;
}

export default function Index({ isManagement, nama, level, stats, menus = [], pembayaran = [], delivery = [], links }) {
    const [charts, setCharts] = useState(null);
    const [chartStatus, setChartStatus] = useState(isManagement ? 'loading' : 'idle');

    useEffect(() => {
        if (!isManagement) return;
        const controller = new AbortController();
        setChartStatus('loading');
        fetch(links.chartApi, {
            credentials: 'same-origin',
            signal: controller.signal,
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then((r) => {
                if (!r.ok) throw new Error('Chart request failed');
                return r.json();
            })
            .then((res) => {
                setCharts(res.data);
                setChartStatus('ready');
            })
            .catch((err) => {
                if (err.name === 'AbortError') return;
                console.error(err);
                setChartStatus('error');
            });

        return () => controller.abort();
    }, [isManagement, links.chartApi]);

    const chartCard = (title, icon, color, badge, meta, child) => (
        <div className="card dashboard-chart-card">
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
                <ChartMetrics metrics={meta?.metrics} />
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
                            <div className="section-title mb-0">Ringkasan Operasional Hari Ini</div>
                            <p className="text-muted">
                                {formatDate(charts?.summary?.tanggal)}
                                {formatTime(charts?.summary?.generated_at) ? `, diperbarui ${formatTime(charts.summary.generated_at)}` : ''}
                            </p>
                        </div>
                        {charts?.summary?.cards?.map((item) => (
                            <InsightCard key={item.label} item={item} />
                        ))}
                    </div>

                    <div className="row">
                        <div className="col-12">
                            <div className="section-title mb-0">Dashboard Stok Hari Ini</div>
                            <p className="text-muted">Ringkasan persediaan BBM dan pergerakan stok per shift.</p>
                        </div>
                        <div className="col-lg-5 col-12">
                            {chartCard('Komposisi Stok BBM', 'gas-pump', 'primary', 'Liter', charts?.chart_stok?.meta,
                                <HomeChart id="chart-stok" type="doughnut" status={chartStatus} payload={charts?.chart_stok} opts={{ unit: 'L' }} emptyMessage="Belum ada stok tercatat" />)}
                        </div>
                        <div className="col-lg-7 col-12">
                            {chartCard('Volume Terjual per Produk & Shift', 'layer-group', 'warning', 'Liter', charts?.chart_stok_split?.meta,
                                <HomeChart id="chart-stok-split" type="bar" status={chartStatus} payload={charts ? { ...charts.chart_stok_split, multi: true } : null} opts={{ unit: 'L' }} emptyMessage="Belum ada penjualan liter hari ini" />)}
                        </div>
                    </div>

                    <div className="row">
                        <div className="col-12">
                            <div className="section-title mb-0">Dashboard Penjualan Hari Ini</div>
                            <p className="text-muted">Realisasi penjualan berdasarkan nozzle dan shift kerja.</p>
                        </div>
                        <div className="col-lg-7 col-12">
                            {chartCard('Omzet per Nozzle', 'coins', 'success', 'Rupiah', charts?.chart_penjualan?.meta,
                                <HomeChart id="chart-penjualan" type="bar" status={chartStatus} payload={charts?.chart_penjualan} opts={{ money: true }} emptyMessage="Belum ada omzet nozzle hari ini" />)}
                        </div>
                        <div className="col-lg-5 col-12">
                            {chartCard('Omzet per Shift', 'receipt', 'danger', 'Rupiah', charts?.chart_penjualan_split?.meta,
                                <HomeChart id="chart-penjualan-split" type="bar" status={chartStatus} payload={charts ? { ...charts.chart_penjualan_split, multi: true } : null} opts={{ money: true }} emptyMessage="Belum ada omzet shift hari ini" />)}
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
