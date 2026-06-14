import { useEffect, useRef } from 'react';
import { buildChart, isEmptyData } from '../lib/charts';

export default function ChartCard({ title, icon = 'chart-bar', color = 'primary', badge, col = 'col-lg-6 col-12', type, data, opts = {} }) {
    const canvasRef = useRef(null);
    const empty = isEmptyData(type, data);

    useEffect(() => {
        if (empty || !canvasRef.current) return undefined;
        const chart = buildChart(canvasRef.current, type, data, opts);
        return () => chart && chart.destroy();
    }, [empty, type, data, opts]);

    return (
        <div className={col}>
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
                    <div className="chart-shell">
                        {empty ? <div className="chart-empty">Belum ada data</div> : <canvas ref={canvasRef}></canvas>}
                    </div>
                </div>
            </div>
        </div>
    );
}
