import { Link, useForm, usePage } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';

// Ubah profil (port dari auth/profil_edit.blade.php). POST ke profil.store;
// error validasi Laravel tampil per-field otomatis lewat useForm errors.
export default function Edit({ title, avatar, action, backUrl, values }) {
    const { urls, flash } = usePage().props;
    const { data, setData, post, processing, errors } = useForm({ ...values });

    const submit = (e) => {
        e.preventDefault();
        post(action);
    };

    const input = (name, label, type = 'text', col = 'col-12 col-sm-6') => (
        <div className={`${col} form-group`} key={name}>
            <label>{label}</label>
            <input
                type={type}
                className={`form-control ${errors[name] ? 'is-invalid' : ''}`}
                value={data[name] ?? ''}
                onChange={(e) => setData(name, e.target.value)}
            />
            {errors[name] && <div className="invalid-feedback d-block">{errors[name]}</div>}
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
                    <div className="breadcrumb-item">Ubah</div>
                </div>
            </div>

            <div className="section-body">
                <div className="row justify-content-center">
                    <div className="col-12">
                        <div className="card">
                            <div className="card-header">
                                <h4>Ubah Profil</h4>
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
                                            <div className="row">
                                                {input('nik', 'NIK')}
                                                {input('nama', 'Nama')}
                                                {input('tempat_lahir', 'Tempat Lahir')}
                                                {input('tanggal_lahir', 'Tanggal Lahir', 'date')}
                                                <div className="col-12 col-sm-6 form-group">
                                                    <label>Jenis Kelamin</label>
                                                    <select
                                                        className={`custom-select ${errors.jenis_kelamin ? 'is-invalid' : ''}`}
                                                        value={data.jenis_kelamin ?? ''}
                                                        onChange={(e) => setData('jenis_kelamin', e.target.value)}
                                                    >
                                                        <option value="pria">Pria</option>
                                                        <option value="wanita">Wanita</option>
                                                    </select>
                                                    {errors.jenis_kelamin && <div className="invalid-feedback d-block">{errors.jenis_kelamin}</div>}
                                                </div>
                                                {input('email', 'Email')}
                                                {input('no_hp', 'No. HP')}
                                                {input('no_wa', 'No. WhatsApp')}
                                                {input('username', 'Username')}
                                            </div>
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
