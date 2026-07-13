import { useState } from 'react';
import { Link, useForm, usePage } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';

// Ubah password (port dari auth/profil_password.blade.php). POST ke
// profil.store_password; kesalahan (password lama salah / konfirmasi beda)
// dikembalikan sebagai flash msg dari controller, error validasi via useForm.
export default function Password({ title, avatar, action, backUrl }) {
    const { urls, flash } = usePage().props;
    const { data, setData, post, processing, errors } = useForm({
        password_lama: '',
        password_baru: '',
        konfirmasi_password_baru: '',
    });
    const [show, setShow] = useState({});

    const submit = (e) => {
        e.preventDefault();
        post(action);
    };

    const field = (name, label) => (
        <div className="form-group" key={name}>
            <label>{label}</label>
            <div className="input-group">
                <input
                    type={show[name] ? 'text' : 'password'}
                    className={`form-control ${errors[name] ? 'is-invalid' : ''}`}
                    placeholder={label}
                    value={data[name]}
                    onChange={(e) => setData(name, e.target.value)}
                />
                <div className="input-group-append">
                    <button type="button" className="btn btn-primary" tabIndex={-1} onClick={() => setShow((s) => ({ ...s, [name]: !s[name] }))}>
                        <i className={`fas ${show[name] ? 'fa-eye-slash' : 'fa-eye'}`}></i>
                    </button>
                </div>
                {errors[name] && <div className="invalid-feedback d-block">{errors[name]}</div>}
            </div>
        </div>
    );

    return (
        <AppLayout title={title}>
            <div className="section-header">
                <h1>Profil</h1>
                <div className="section-header-breadcrumb">
                    <div className="breadcrumb-item">
                        <a href={urls.home}>Dashboard</a>
                    </div>
                    <div className="breadcrumb-item">
                        <a href={backUrl}>Profil</a>
                    </div>
                    <div className="breadcrumb-item">Ubah Password</div>
                </div>
            </div>

            <div className="section-body">
                <div className="row justify-content-center">
                    <div className="col-12">
                        <div className="card">
                            <div className="card-header">
                                <h4>Ubah Password</h4>
                            </div>
                            <div className="card-body">
                                {flash?.msg && <div dangerouslySetInnerHTML={{ __html: flash.msg }} />}
                                <form onSubmit={submit}>
                                    <div className="row">
                                        <div className="col-12 col-md-4 text-center mb-4 mb-md-0">
                                            <img
                                                src={avatar}
                                                alt="avatar"
                                                style={{ width: '100%', maxWidth: 220, borderRadius: 14, border: '1px solid var(--border)' }}
                                            />
                                        </div>
                                        <div className="col-12 col-md-8">
                                            {field('password_lama', 'Password Lama')}
                                            {field('password_baru', 'Password Baru')}
                                            {field('konfirmasi_password_baru', 'Konfirmasi Password Baru')}
                                        </div>
                                    </div>

                                    <div className="d-flex flex-wrap mt-3" style={{ gap: 8 }}>
                                        <Link href={backUrl} className="btn btn-primary">
                                            <i className="fas fa-arrow-left mr-1"></i> Kembali
                                        </Link>
                                        <button type="submit" className="btn btn-success" disabled={processing}>
                                            {processing ? (
                                                <>
                                                    <i className="fas fa-circle-notch fa-spin mr-1"></i> Menyimpan...
                                                </>
                                            ) : (
                                                <>
                                                    <i className="fas fa-pen mr-1"></i> Simpan
                                                </>
                                            )}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
