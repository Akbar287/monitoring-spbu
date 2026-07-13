import { useMemo, useState } from 'react';

// Kalender bulanan ringan (pengganti jQuery fullCalendar utk kebutuhan app:
// tampil event per tanggal + klik hari / klik event). Tanpa dependensi baru.
// props:
//   events: [{title, start: 'YYYY-MM-DD...', color?}]
//   onDayClick?: (dateStr) => void
//   onEventClick?: (event) => void
const BULAN = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
const HARI = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];

const pad = (n) => (n < 10 ? '0' + n : String(n));
const keyOf = (y, m, d) => `${y}-${pad(m + 1)}-${pad(d)}`;

export default function MonthCalendar({ events = [], onDayClick, onEventClick }) {
    const today = new Date();
    const [cursor, setCursor] = useState({ y: today.getFullYear(), m: today.getMonth() });

    const eventMap = useMemo(() => {
        const map = {};
        events.forEach((ev) => {
            const k = String(ev.start || '').slice(0, 10);
            (map[k] = map[k] || []).push(ev);
        });
        return map;
    }, [events]);

    const cells = useMemo(() => {
        const first = new Date(cursor.y, cursor.m, 1);
        const startOffset = first.getDay();
        const daysInMonth = new Date(cursor.y, cursor.m + 1, 0).getDate();
        const list = [];
        for (let i = 0; i < startOffset; i++) list.push(null);
        for (let d = 1; d <= daysInMonth; d++) list.push(d);
        while (list.length % 7 !== 0) list.push(null);
        return list;
    }, [cursor]);

    const isToday = (d) => d && cursor.y === today.getFullYear() && cursor.m === today.getMonth() && d === today.getDate();
    const move = (delta) => {
        setCursor(({ y, m }) => {
            const dt = new Date(y, m + delta, 1);
            return { y: dt.getFullYear(), m: dt.getMonth() };
        });
    };

    return (
        <div className="month-calendar">
            <style dangerouslySetInnerHTML={{ __html: CSS }} />
            <div className="d-flex align-items-center justify-content-between mb-3">
                <div className="btn-group">
                    <button type="button" className="btn btn-sm month-calendar-btn" onClick={() => move(-1)}>
                        <i className="fas fa-chevron-left"></i>
                    </button>
                    <button
                        type="button"
                        className="btn btn-sm month-calendar-btn"
                        onClick={() => setCursor({ y: today.getFullYear(), m: today.getMonth() })}
                    >
                        Hari Ini
                    </button>
                    <button type="button" className="btn btn-sm month-calendar-btn" onClick={() => move(1)}>
                        <i className="fas fa-chevron-right"></i>
                    </button>
                </div>
                <h5 className="mb-0 month-calendar-title">
                    {BULAN[cursor.m]} {cursor.y}
                </h5>
                <div style={{ width: 120 }}></div>
            </div>

            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(7, 1fr)', gap: 4 }}>
                {HARI.map((h) => (
                    <div key={h} className="text-center text-muted" style={{ fontSize: 12, fontWeight: 700, padding: '6px 0' }}>
                        {h}
                    </div>
                ))}
                {cells.map((d, i) => {
                    const dateStr = d ? keyOf(cursor.y, cursor.m, d) : null;
                    const evs = d ? eventMap[dateStr] || [] : [];
                    return (
                        <div
                            key={i}
                            onClick={() => d && onDayClick && onDayClick(dateStr)}
                            className={`month-calendar-cell ${d ? '' : 'empty'} ${isToday(d) ? 'today' : ''}`}
                            style={{
                                minHeight: 86,
                                cursor: d && onDayClick ? 'pointer' : 'default',
                            }}
                        >
                            {d && (
                                <>
                                    <div className={`month-calendar-day ${isToday(d) ? 'today' : ''}`}>{d}</div>
                                    {evs.slice(0, 3).map((ev, j) => (
                                        <div
                                            key={j}
                                            title={ev.title}
                                            onClick={(e) => {
                                                if (onEventClick) {
                                                    e.stopPropagation();
                                                    onEventClick(ev);
                                                }
                                            }}
                                            style={{
                                                background: ev.color || '#6777ef',
                                                color: '#fff',
                                                borderRadius: 6,
                                                fontSize: 11,
                                                padding: '2px 6px',
                                                marginTop: 3,
                                                overflow: 'hidden',
                                                textOverflow: 'ellipsis',
                                                whiteSpace: 'nowrap',
                                                cursor: onEventClick ? 'pointer' : 'inherit',
                                            }}
                                        >
                                            {ev.title}
                                        </div>
                                    ))}
                                    {evs.length > 3 && (
                                        <div className="text-muted" style={{ fontSize: 10, marginTop: 2 }}>
                                            +{evs.length - 3} lainnya
                                        </div>
                                    )}
                                </>
                            )}
                        </div>
                    );
                })}
            </div>
        </div>
    );
}

const CSS = `
.month-calendar{color:var(--foreground)}
.month-calendar-btn{
    color:var(--foreground);
    background:var(--surface-soft);
    border:1px solid var(--border);
}
.month-calendar-btn:hover,
.month-calendar-btn:focus{
    color:var(--foreground);
    background:color-mix(in srgb, var(--surface-soft) 82%, var(--foreground));
    border-color:var(--border);
}
.month-calendar-title{
    color:var(--foreground);
    font-weight:700;
}
.month-calendar-cell{
    border-radius:10px;
    padding:6px;
    background:var(--surface);
    border:1px solid var(--border);
    transition:background .15s ease,border-color .15s ease,box-shadow .15s ease;
}
.month-calendar-cell:not(.empty):hover{
    background:var(--surface-soft);
}
.month-calendar-cell.empty{
    background:transparent;
    border-color:transparent;
}
.month-calendar-cell.today{
    box-shadow:0 0 0 2px #6777ef;
}
.month-calendar-day{
    color:var(--muted);
    font-size:12px;
    font-weight:700;
}
.month-calendar-day.today{
    color:#6777ef;
}
html[data-theme="dark"] .month-calendar-cell.today{
    box-shadow:0 0 0 2px #a5b4fc;
}
html[data-theme="dark"] .month-calendar-day.today{
    color:#c7d2fe;
}
`;
