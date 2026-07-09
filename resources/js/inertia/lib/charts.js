import { Chart } from 'chart.js/auto';

// Port dari spbuChart (resources/js/custom.js) untuk dunia React:
// builder menerima elemen canvas, mengembalikan instance Chart (atau null
// jika data kosong — pemanggil yang merender placeholder).

const PALETTE = ['#6777ef', '#fc544b', '#ffa426', '#47c363', '#3abaf4', '#9b59b6', '#fdb45c', '#34395e', '#00c9a7', '#f55376'];
const idr = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 });
const toNumber = (v) => Number(v) || 0;

const fmtShort = (v) => {
    const value = toNumber(v);
    const n = Math.abs(value);
    if (n >= 1e9) return (value / 1e9).toFixed(1).replace('.0', '') + ' M';
    if (n >= 1e6) return (value / 1e6).toFixed(1).replace('.0', '') + ' jt';
    if (n >= 1e3) return (value / 1e3).toFixed(1).replace('.0', '') + ' rb';
    return idr.format(value);
};

const fmtVal = (opt, v) => (opt.money ? 'Rp ' + idr.format(v ?? 0) : fmtShort(v) + (opt.unit ? ' ' + opt.unit : ''));
const tick = (opt, v) => (opt.money ? 'Rp ' + fmtShort(v) : fmtShort(v));

const hexToRgba = (hex, a) => {
    const n = parseInt(hex.replace('#', ''), 16);
    return `rgba(${(n >> 16) & 255},${(n >> 8) & 255},${n & 255},${a})`;
};

function applyTheme() {
    Chart.defaults.font.family = "'Plus Jakarta Sans', 'Nunito', 'Segoe UI', sans-serif";
    Chart.defaults.font.size = 12;
    Chart.defaults.color = '#6c757d';
    Chart.defaults.plugins.legend.labels.usePointStyle = true;
    Chart.defaults.plugins.legend.labels.pointStyle = 'circle';
    Chart.defaults.plugins.legend.labels.boxWidth = 8;
    Chart.defaults.plugins.legend.labels.padding = 16;
}

const TOOLTIP = {
    backgroundColor: 'rgba(33, 37, 41, 0.92)',
    titleColor: '#ffffff',
    bodyColor: '#e9ecef',
    padding: 12,
    cornerRadius: 8,
    usePointStyle: true,
    boxPadding: 6,
};
const GRID_Y = { border: { display: false }, grid: { color: 'rgba(0,0,0,0.05)', drawBorder: false } };
const GRID_X = { grid: { display: false }, border: { display: false } };

export function isEmptyData(type, d) {
    if (!d || !d.labels || !d.labels.length) return true;
    if (d.datasets) {
        return !(d.datasets || []).some((ds) => (ds.data || []).some((v) => toNumber(v) > 0));
    }
    return !(d.data || []).some((v) => Number(v) > 0);
}

export function buildChart(canvas, type, d, opt = {}) {
    if (isEmptyData(type, d)) return null;
    applyTheme();

    if (type === 'doughnut') {
        return new Chart(canvas, {
            type: 'doughnut',
            data: {
                labels: d.labels,
                datasets: [{
                    data: d.data,
                    backgroundColor: d.backgroundColor || d.labels.map((_, i) => PALETTE[i % PALETTE.length]),
                    borderColor: '#ffffff',
                    borderWidth: 3,
                    hoverOffset: 10,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '62%',
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        ...TOOLTIP,
                        callbacks: {
                            label(c) {
                                const tot = c.dataset.data.reduce((a, b) => a + (Number(b) || 0), 0) || 1;
                                return '  ' + fmtVal(opt, c.parsed) + ' (' + ((c.parsed / tot) * 100).toFixed(1) + '%)';
                            },
                        },
                    },
                },
            },
        });
    }

    if (type === 'bar') {
        const horizontal = !!opt.horizontal;
        const colorEach = opt.colorEach !== false;
        return new Chart(canvas, {
            type: 'bar',
            data: {
                labels: d.labels,
                datasets: [{
                    label: opt.label || '',
                    data: d.data,
                    backgroundColor: colorEach
                        ? (d.backgroundColor || d.labels.map((_, i) => PALETTE[i % PALETTE.length]))
                        : PALETTE[opt.colorIndex || 0],
                    borderRadius: 6,
                    borderSkipped: false,
                    maxBarThickness: 46,
                }],
            },
            options: {
                indexAxis: horizontal ? 'y' : 'x',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        ...TOOLTIP,
                        callbacks: { label: (c) => '  ' + fmtVal(opt, horizontal ? c.parsed.x : c.parsed.y) },
                    },
                },
                scales: horizontal
                    ? { x: { beginAtZero: true, ticks: { callback: (v) => tick(opt, v) }, ...GRID_Y }, y: GRID_X }
                    : { x: GRID_X, y: { beginAtZero: true, ticks: { callback: (v) => tick(opt, v) }, ...GRID_Y } },
            },
        });
    }

    if (type === 'line') {
        const datasets = (d.datasets || [{ label: opt.label || '', data: d.data }]).map((ds, i) => ({
            tension: 0.4,
            fill: true,
            borderWidth: 3,
            pointRadius: 3,
            pointHoverRadius: 5,
            borderColor: PALETTE[i % PALETTE.length],
            backgroundColor: hexToRgba(PALETTE[i % PALETTE.length], 0.12),
            pointBackgroundColor: PALETTE[i % PALETTE.length],
            ...ds,
        }));
        return new Chart(canvas, {
            type: 'line',
            data: { labels: d.labels, datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { intersect: false, mode: 'index' },
                plugins: {
                    legend: { display: !!d.datasets, position: 'bottom' },
                    tooltip: {
                        ...TOOLTIP,
                        callbacks: { label: (c) => '  ' + (c.dataset.label ? c.dataset.label + ': ' : '') + fmtVal(opt, c.parsed.y) },
                    },
                },
                scales: { x: GRID_X, y: { beginAtZero: true, ticks: { callback: (v) => tick(opt, v) }, ...GRID_Y } },
            },
        });
    }

    if (type === 'stackedBar') {
        return new Chart(canvas, {
            type: 'bar',
            data: {
                labels: d.labels,
                datasets: (d.datasets || []).map((ds, i) => ({
                    backgroundColor: PALETTE[i % PALETTE.length],
                    borderRadius: 6,
                    borderSkipped: false,
                    maxBarThickness: 46,
                    ...ds,
                })),
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { intersect: false, mode: 'index' },
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        ...TOOLTIP,
                        callbacks: {
                            label: (c) => '  ' + c.dataset.label + ': ' + fmtVal(opt, c.parsed.y),
                            footer(items) {
                                if (!items || items.length < 2) return '';
                                const total = items.reduce((sum, item) => sum + toNumber(item.parsed.y), 0);
                                return 'Total: ' + fmtVal(opt, total);
                            },
                        },
                    },
                },
                scales: {
                    x: { stacked: true, ...GRID_X },
                    y: { stacked: true, beginAtZero: true, ticks: { callback: (v) => tick(opt, v) }, ...GRID_Y },
                },
            },
        });
    }

    return null;
}
