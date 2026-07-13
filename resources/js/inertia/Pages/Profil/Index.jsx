import { Link, usePage } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';

// Halaman lihat profil (port dari auth/profil.blade.php ke Inertia).
export default function Index({ title, avatar, role, fields, editUrl, passwordUrl, homeUrl }) {
    const { urls, flash } = usePage().props;
    return (
        <AppLayout title={title}>
            <div className="section-header">
                <h1>Profil</h1>
                <div className="section-header-breadcrumb">
                    <div className="breadcrumb-item">
                        <a href={urls.home}>Dashboard</a>
                    </div>
                    <div className="breadcrumb-item">Profil</div>
                </div>
            </div>

            <div className="section-body">
                <p className="text-muted mb-3">
                    Divisi <b>{role.divisi || '-'}</b> &middot; Level <b>{role.level || '-'}</b> &middot; Jabatan <b>{role.jabatan || '-'}</b>
                </p>
                <div className="row justify-content-center">
                    <div className="col-12">
                        <div className="card">
                            <div className="card-header">
                                <h4>Profil</h4>
                            </div>
                            <div className="card-body">
                                {flash?.msg && <div dangerouslySetInnerHTML={{ __html: flash.msg }} />}
                                <div className="row">
                                    <div className="col-12 col-md-4 text-center mb-4 mb-md-0">
                                        <img
                                            src={avatar}
                                            alt="avatar"
                                            style={{ width: '100%', maxWidth: 220, borderRadius: 14, border: '1px solid var(--border)' }}
                                        />
                                    </div>
                                    <div className="col-12 col-md-8">
                                        <div className="row">
                                            {fields.map((f) => (
                                                <div className="col-12 col-sm-6 form-group" key={f.label}>
                                                    <label className="text-muted small">{f.label}</label>
                                                    <input type="text" readOnly className="form-control" value={f.value ?? ''} />
                                                </div>
                                            ))}
                                        </div>
                                    </div>
                                </div>

                                <div className="d-flex flex-wrap mt-3" style={{ gap: 8 }}>
                                    <Link href={homeUrl} className="btn btn-primary">
                                        <i className="fas fa-arrow-left mr-1"></i> Kembali
                                    </Link>
                                    <Link href={passwordUrl} className="btn btn-info">
                                        <i className="fas fa-lock mr-1"></i> Ubah Password
                                    </Link>
                                    <Link href={editUrl} className="btn btn-success">
                                        <i className="fas fa-pen mr-1"></i> Ubah
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
