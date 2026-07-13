import { useState } from 'react';
import AppLayout from '../../Layouts/AppLayout';

const movementIcon = (movement) => {
    if (movement === 'Commit Change From User') return 'fa-comment-alt';
    if (movement === 'Good Return Tera' || movement === 'Good Receive Penerimaan') return 'fa-arrow-down';
    if (movement === 'Good Issue Tera') return 'fa-arrow-up';
    if (movement === 'Loss') return 'fa-cut';
    if (movement === 'Stok Taking') return 'fa-thermometer-full';
    if (movement === 'Good Issue Sales') return 'fa-coins';
    return 'fa-exchange-alt';
};

export default function Show({ title, dateLabel, breadcrumbs = [], products = [], backUrl }) {
    const [open, setOpen] = useState(products[0]?.id || null);

    return (
        <AppLayout title={title}>
            <div className="section-header">
                <h1>{title}</h1>
                <div className="section-header-breadcrumb">
                    {breadcrumbs.map((b) => (
                        <div key={b.label} className="breadcrumb-item">
                            {b.url ? <a href={b.url}>{b.label}</a> : b.label}
                        </div>
                    ))}
                    <div className="breadcrumb-item">{dateLabel}</div>
                </div>
            </div>

            <div className="section-body">
                <div className="card">
                    <div className="card-header">
                        <h4>{dateLabel}</h4>
                    </div>
                </div>

                {products.map((product) => (
                    <div className="card" key={product.id}>
                        <div className="card-header">
                            <h4 className="mb-0">
                                <button type="button" className="btn btn-link p-0" onClick={() => setOpen(open === product.id ? null : product.id)}>
                                    {product.name}
                                </button>
                            </h4>
                        </div>
                        {open === product.id && (
                            <div className="card-body" style={{ background: '#f2f5f8b4' }}>
                                {product.items.length === 0 ? (
                                    <div className="alert alert-info mb-0">Tidak ada data</div>
                                ) : (
                                    <div className="activities">
                                        {product.items.map((item) => (
                                            <div className="activity" key={item.id}>
                                                <div className="activity-icon bg-primary text-white shadow-primary">
                                                    <i className={`fas ${movementIcon(item.type_movement)}`}></i>
                                                </div>
                                                <div className="activity-detail">
                                                    <div className="mb-2">
                                                        <span className="text-job text-primary">{item.waktu}</span>
                                                        <span className="bullet"></span>
                                                        <span className="text-job">{item.type_movement}</span>
                                                        <span className="bullet"></span>
                                                        <span className={`text-job ${item.is_confirmation ? 'text-success' : 'text-danger'}`}>
                                                            {item.is_confirmation ? 'Terkonfirmasi' : 'Belum terkonfirmasi'}
                                                        </span>
                                                    </div>
                                                    <div className="table-responsive">
                                                        <table className="table table-border">
                                                            <thead>
                                                                <tr>
                                                                    <th>Informasi</th>
                                                                    <th>Besaran</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                {item.rows.map((row) => (
                                                                    <tr key={row.label}>
                                                                        <th>{row.label}</th>
                                                                        <td>{row.value}</td>
                                                                    </tr>
                                                                ))}
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                )}
                            </div>
                        )}
                    </div>
                ))}

                {backUrl && (
                    <a href={backUrl} className="btn btn-primary">
                        <i className="fas fa-arrow-left mr-1"></i> Kembali
                    </a>
                )}
            </div>
        </AppLayout>
    );
}
