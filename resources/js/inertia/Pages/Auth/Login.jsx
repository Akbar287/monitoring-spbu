import { Head, useForm } from '@inertiajs/react';
import { useState } from 'react';

// Halaman login — port React dari auth/login.blade.php (split-screen).
// POST via useForm ke route('login'); error validasi (key: username) tampil
// otomatis; sukses -> redirect /home (sudah React, SPA penuh).
const CSS = `
:root{--pri:#6777ef;--pri-dark:#4f5fdc;--ink:#1f2440;--muted:#8089a8}
body{margin:0}
.auth-wrap{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;font-family:'Plus Jakarta Sans','Segoe UI',sans-serif;color:var(--ink);background:linear-gradient(135deg,#eef0fb 0%,#f6f7fb 100%)}
.auth-card{width:100%;max-width:920px;background:#fff;border-radius:24px;overflow:hidden;display:grid;grid-template-columns:1.05fr 1fr;box-shadow:0 30px 70px rgba(31,36,64,.18)}
.auth-brand{position:relative;overflow:hidden;padding:48px 40px;color:#fff;background:linear-gradient(150deg,#5b6bf0 0%,#6a4ff0 55%,#8b4ff0 100%);display:flex;flex-direction:column;justify-content:center}
.auth-brand:before,.auth-brand:after{content:"";position:absolute;border-radius:50%;background:rgba(255,255,255,.12)}
.auth-brand:before{width:240px;height:240px;top:-90px;right:-70px}
.auth-brand:after{width:160px;height:160px;bottom:-70px;left:-50px}
.auth-logo{width:60px;height:60px;border-radius:16px;background:rgba(255,255,255,.18);display:grid;place-items:center;font-size:26px;margin-bottom:22px;position:relative}
.auth-brand h2{font-weight:800;font-size:26px;margin:0 0 10px;position:relative}
.auth-brand p{font-size:14px;line-height:1.6;opacity:.9;margin:0 0 26px;position:relative}
.auth-features{list-style:none;padding:0;margin:0;position:relative}
.auth-features li{display:flex;align-items:center;gap:12px;font-size:13.5px;font-weight:500;padding:7px 0}
.auth-features li i{width:30px;height:30px;border-radius:9px;background:rgba(255,255,255,.16);display:grid;place-items:center;font-size:13px}
.auth-form{padding:48px 44px;display:flex;flex-direction:column;justify-content:center}
.auth-form h1{font-weight:800;font-size:24px;margin:0 0 4px}
.auth-form .sub{color:var(--muted);font-size:14px;margin:0 0 26px}
.field{margin-bottom:18px}
.field label{display:block;font-size:13px;font-weight:600;margin-bottom:7px;color:var(--ink)}
.input-icon{position:relative;display:flex;align-items:center}
.input-icon>i.lead-ico{position:absolute;left:16px;color:var(--muted);font-size:15px;pointer-events:none}
.input-icon input{width:100%;border:1.6px solid #e6e8f2;background:#fafbff;border-radius:12px;padding:13px 16px 13px 44px;font-size:14.5px;font-family:inherit;color:var(--ink);transition:border-color .18s,box-shadow .18s,background .18s;outline:none}
.input-icon input:focus{border-color:var(--pri);background:#fff;box-shadow:0 0 0 4px rgba(103,119,239,.14)}
.input-icon.has-error input{border-color:#fc544b}
.toggle-pass{position:absolute;right:8px;border:0;background:transparent;color:var(--muted);width:36px;height:36px;border-radius:9px;cursor:pointer}
.toggle-pass:hover{color:var(--pri);background:rgba(103,119,239,.1)}
.field-error{display:block;color:#fc544b;font-size:12.5px;font-weight:600;margin-top:6px}
.btn-submit{width:100%;border:0;border-radius:12px;padding:14px;font-size:15px;font-weight:700;color:#fff;cursor:pointer;background:linear-gradient(135deg,var(--pri),var(--pri-dark));box-shadow:0 10px 22px rgba(103,119,239,.4);transition:transform .18s,box-shadow .18s;display:inline-flex;align-items:center;justify-content:center;gap:9px;margin-top:4px}
.btn-submit:hover{transform:translateY(-2px);box-shadow:0 14px 28px rgba(103,119,239,.5)}
.btn-submit:disabled{opacity:.7;transform:none}
.back-link{text-align:center;margin-top:22px;font-size:13.5px}
.back-link a{color:var(--muted);font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:7px}
.back-link a:hover{color:var(--pri)}
.auth-foot{text-align:center;color:#aab0cb;font-size:12px;margin-top:18px}
@media (max-width:768px){.auth-card{grid-template-columns:1fr;max-width:440px}.auth-brand{display:none}.auth-form{padding:38px 28px}}
`;

export default function Login({ action, homeUrl }) {
    const { data, setData, post, processing, errors } = useForm({ username: '', password: '' });
    const [showPass, setShowPass] = useState(false);

    const submit = (e) => {
        e.preventDefault();
        post(action);
    };

    return (
        <div className="auth-wrap">
            <Head title="Login" />
            <style dangerouslySetInnerHTML={{ __html: CSS }} />

            <div className="auth-card">
                <div className="auth-brand">
                    <div className="auth-logo">
                        <i className="fas fa-gas-pump"></i>
                    </div>
                    <h2>Monitoring SPBU</h2>
                    <p>Sistem terpadu untuk memantau stok, penjualan, dan operasional SPBU secara real-time.</p>
                    <ul className="auth-features">
                        <li>
                            <i className="fas fa-chart-line"></i> Dashboard & laporan informatif
                        </li>
                        <li>
                            <i className="fas fa-gas-pump"></i> Monitoring stok & penjualan BBM
                        </li>
                        <li>
                            <i className="fas fa-shield-alt"></i> Akses aman sesuai jabatan
                        </li>
                    </ul>
                </div>

                <div className="auth-form">
                    <h1>Selamat Datang 👋</h1>
                    <p className="sub">Silakan masuk menggunakan akun Anda.</p>

                    <form onSubmit={submit}>
                        <div className="field">
                            <label htmlFor="username">Username</label>
                            <div className={`input-icon ${errors.username ? 'has-error' : ''}`}>
                                <i className="fas fa-user lead-ico"></i>
                                <input
                                    id="username"
                                    type="text"
                                    value={data.username}
                                    placeholder="Masukkan username"
                                    autoFocus
                                    onChange={(e) => setData('username', e.target.value)}
                                />
                            </div>
                            {errors.username && <span className="field-error">{errors.username}</span>}
                        </div>

                        <div className="field">
                            <label htmlFor="password">Password</label>
                            <div className={`input-icon ${errors.password ? 'has-error' : ''}`}>
                                <i className="fas fa-lock lead-ico"></i>
                                <input
                                    id="password"
                                    type={showPass ? 'text' : 'password'}
                                    value={data.password}
                                    placeholder="Masukkan password"
                                    autoComplete="current-password"
                                    onChange={(e) => setData('password', e.target.value)}
                                />
                                <button type="button" className="toggle-pass" tabIndex={-1} onClick={() => setShowPass(!showPass)}>
                                    <i className={`fas ${showPass ? 'fa-eye-slash' : 'fa-eye'}`}></i>
                                </button>
                            </div>
                            {errors.password && <span className="field-error">{errors.password}</span>}
                        </div>

                        <button type="submit" className="btn-submit" disabled={processing}>
                            {processing ? (
                                <>
                                    <i className="fas fa-circle-notch fa-spin"></i> Memproses...
                                </>
                            ) : (
                                <>
                                    <i className="fas fa-sign-in-alt"></i> Login
                                </>
                            )}
                        </button>
                    </form>

                    <div className="back-link">
                        <a href={homeUrl}>
                            <i className="fas fa-arrow-left"></i> Kembali ke beranda
                        </a>
                    </div>
                    <div className="auth-foot">&copy; Monitoring SPBU {new Date().getFullYear()}</div>
                </div>
            </div>
        </div>
    );
}
