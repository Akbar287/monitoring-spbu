import { useCallback, useEffect, useMemo, useRef, useState } from 'react';

// Tabel server-side yang berbicara protokol DataTables/Yajra — memakai
// endpoint AJAX yang sama dengan implementasi jQuery lama, tanpa perubahan
// backend. Header X-Requested-With wajib agar $request->ajax() bernilai true.
//
// columns: [{ label, data, name?, orderable?, searchable?, raw? }]
export default function DataTable({ url, columns }) {
    const [rows, setRows] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [page, setPage] = useState(0);
    const [perPage, setPerPage] = useState(10);
    const [searchInput, setSearchInput] = useState('');
    const [search, setSearch] = useState('');
    const [order, setOrder] = useState(() => {
        const idx = columns.findIndex((c) => c.orderable !== false);
        return idx >= 0 ? { col: idx, dir: 'asc' } : null;
    });
    const [total, setTotal] = useState(0);
    const [filtered, setFiltered] = useState(0);
    const drawRef = useRef(0);

    // debounce pencarian 400ms
    useEffect(() => {
        const t = setTimeout(() => {
            setSearch(searchInput);
            setPage(0);
        }, 400);
        return () => clearTimeout(t);
    }, [searchInput]);

    const params = useMemo(() => {
        const p = new URLSearchParams();
        p.set('draw', String(drawRef.current + 1));
        p.set('start', String(page * perPage));
        p.set('length', String(perPage));
        p.set('search[value]', search);
        p.set('search[regex]', 'false');
        if (order) {
            p.set('order[0][column]', String(order.col));
            p.set('order[0][dir]', order.dir);
        }
        columns.forEach((c, i) => {
            p.set(`columns[${i}][data]`, c.data);
            p.set(`columns[${i}][name]`, c.name || c.data);
            p.set(`columns[${i}][searchable]`, c.searchable === false ? 'false' : 'true');
            p.set(`columns[${i}][orderable]`, c.orderable === false ? 'false' : 'true');
            p.set(`columns[${i}][search][value]`, '');
            p.set(`columns[${i}][search][regex]`, 'false');
        });
        return p;
    }, [url, columns, page, perPage, search, order]);

    useEffect(() => {
        const controller = new AbortController();
        const draw = ++drawRef.current;
        setLoading(true);
        setError(null);

        const sep = url.includes('?') ? '&' : '?';
        fetch(url + sep + params.toString(), {
            signal: controller.signal,
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                Accept: 'application/json',
            },
        })
            .then((r) => {
                if (!r.ok) throw new Error('HTTP ' + r.status);
                return r.json();
            })
            .then((json) => {
                if (draw !== drawRef.current) return; // respons usang
                setRows(json.data || []);
                setTotal(json.recordsTotal ?? 0);
                setFiltered(json.recordsFiltered ?? 0);
                setLoading(false);
            })
            .catch((e) => {
                if (e.name === 'AbortError') return;
                setError('Gagal memuat data. Periksa koneksi anda.');
                setLoading(false);
            });

        return () => controller.abort();
    }, [url, params]);

    const toggleSort = useCallback(
        (idx) => {
            if (columns[idx].orderable === false) return;
            setOrder((o) =>
                o && o.col === idx ? { col: idx, dir: o.dir === 'asc' ? 'desc' : 'asc' } : { col: idx, dir: 'asc' }
            );
            setPage(0);
        },
        [columns]
    );

    const lastPage = Math.max(0, Math.ceil(filtered / perPage) - 1);
    const from = filtered === 0 ? 0 : page * perPage + 1;
    const to = Math.min(filtered, (page + 1) * perPage);

    // jendela nomor halaman (maks 5)
    const pageNumbers = useMemo(() => {
        const start = Math.max(0, Math.min(page - 2, lastPage - 4));
        return Array.from({ length: Math.min(5, lastPage + 1) }, (_, i) => start + i);
    }, [page, lastPage]);

    return (
        <div>
            <div className="d-flex flex-wrap justify-content-between align-items-center mb-3" style={{ gap: 12 }}>
                <div className="d-flex align-items-center" style={{ gap: 8 }}>
                    <select
                        className="custom-select custom-select-sm"
                        style={{ width: 80 }}
                        value={perPage}
                        onChange={(e) => {
                            setPerPage(Number(e.target.value));
                            setPage(0);
                        }}
                    >
                        {[10, 25, 50, 100].map((n) => (
                            <option key={n} value={n}>
                                {n}
                            </option>
                        ))}
                    </select>
                    <span className="text-muted" style={{ fontSize: 13 }}>
                        baris per halaman
                    </span>
                </div>
                <div className="input-group input-group-sm" style={{ maxWidth: 240 }}>
                    <div className="input-group-prepend">
                        <span className="input-group-text">
                            <i className="fas fa-search"></i>
                        </span>
                    </div>
                    <input
                        type="text"
                        className="form-control"
                        placeholder="Cari..."
                        value={searchInput}
                        onChange={(e) => setSearchInput(e.target.value)}
                    />
                </div>
            </div>

            <div className="table-responsive" style={{ position: 'relative' }}>
                {loading && (
                    <div
                        style={{
                            position: 'absolute',
                            inset: 0,
                            background: 'rgba(255,255,255,.6)',
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'center',
                            zIndex: 2,
                        }}
                    >
                        <i className="fas fa-circle-notch fa-spin fa-lg" style={{ color: '#6777ef' }}></i>
                    </div>
                )}
                <table className="table table-hover">
                    <thead>
                        <tr>
                            {columns.map((c, i) => {
                                const sortable = c.orderable !== false;
                                const sorted = order && order.col === i;
                                return (
                                    <th
                                        key={c.data + i}
                                        onClick={() => toggleSort(i)}
                                        style={sortable ? { cursor: 'pointer', whiteSpace: 'nowrap' } : { whiteSpace: 'nowrap' }}
                                    >
                                        {c.label}
                                        {sortable && (
                                            <i
                                                className={`fas ${sorted ? (order.dir === 'asc' ? 'fa-sort-up' : 'fa-sort-down') : 'fa-sort'} ml-2`}
                                                style={{ opacity: sorted ? 1 : 0.3 }}
                                            ></i>
                                        )}
                                    </th>
                                );
                            })}
                        </tr>
                    </thead>
                    <tbody>
                        {error && (
                            <tr>
                                <td colSpan={columns.length} className="text-center text-danger py-4">
                                    {error}
                                </td>
                            </tr>
                        )}
                        {!error && rows.length === 0 && !loading && (
                            <tr>
                                <td colSpan={columns.length} className="text-center text-muted py-4">
                                    Tidak ada data yang tersedia
                                </td>
                            </tr>
                        )}
                        {!error &&
                            rows.map((row, ri) => (
                                <tr key={ri}>
                                    {columns.map((c, ci) =>
                                        c.raw ? (
                                            <td key={ci} dangerouslySetInnerHTML={{ __html: row[c.data] ?? '' }} />
                                        ) : (
                                            <td key={ci}>{row[c.data] ?? '-'}</td>
                                        )
                                    )}
                                </tr>
                            ))}
                    </tbody>
                </table>
            </div>

            <div className="d-flex flex-wrap justify-content-between align-items-center mt-2" style={{ gap: 12 }}>
                <span className="text-muted" style={{ fontSize: 13 }}>
                    Menampilkan {from}–{to} dari {filtered} data
                    {filtered !== total ? ` (difilter dari ${total})` : ''}
                </span>
                <nav>
                    <ul className="pagination pagination-sm mb-0">
                        <li className={`page-item ${page === 0 ? 'disabled' : ''}`}>
                            <button className="page-link" onClick={() => setPage(page - 1)} disabled={page === 0}>
                                <i className="fas fa-chevron-left"></i>
                            </button>
                        </li>
                        {pageNumbers.map((n) => (
                            <li key={n} className={`page-item ${n === page ? 'active' : ''}`}>
                                <button className="page-link" onClick={() => setPage(n)}>
                                    {n + 1}
                                </button>
                            </li>
                        ))}
                        <li className={`page-item ${page >= lastPage ? 'disabled' : ''}`}>
                            <button className="page-link" onClick={() => setPage(page + 1)} disabled={page >= lastPage}>
                                <i className="fas fa-chevron-right"></i>
                            </button>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    );
}
