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
        <div>
            <div className="d-flex align-items-center justify-content-between mb-3">
                <div className="btn-group">
                    <button type="button" className="btn btn-sm btn-light" onClick={() => move(-1)}>
                        <i className="fas fa-chevron-left"></i>
                    </button>
                    <button
                        type="button"
                        className="btn btn-sm btn-light"
                        onClick={() => setCursor({ y: today.getFullYear(), m: today.getMonth() })}
                    >
                        Hari Ini
                    </button>
                    <button type="button" className="btn btn-sm btn-light" onClick={() => move(1)}>
                        <i className="fas fa-chevron-right"></i>
                    </button>
                </div>
                <h5 className="mb-0" style={{ fontWeight: 700 }}>
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
                            style={{
                                minHeight: 86,
                                borderRadius: 10,
                                padding: 6,
                                background: d ? '#fff' : 'transparent',
                                border: d ? '1px solid rgba(31,36,64,.08)' : 'none',
                                cursor: d && onDayClick ? 'pointer' : 'default',
                                outline: isToday(d) ? '2px solid #6777ef' : 'none',
                            }}
                        >
                            {d && (
                                <>
                                    <div style={{ fontSize: 12, fontWeight: 700, color: isToday(d) ? '#6777ef' : '#6c757d' }}>{d}</div>
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
