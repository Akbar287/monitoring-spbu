import { Head, useForm } from '@inertiajs/react';
import { useState } from 'react';

// Halaman login — port React dari auth/login.blade.php (split-screen).
// POST via useForm ke route('login'); error validasi (key: username) tampil
// otomatis; sukses -> redirect /home (sudah React, SPA penuh).
//
// Tema: ikut html[data-theme] yang dipasang early-script di inertia.blade.php
// (localStorage 'spbu-theme' -> fallback prefers-color-scheme). Tombol bulan/
// matahari di pojok memakai window.spbuTheme sehingga pilihan terbawa ke
// seluruh aplikasi. Semua animasi dimatikan pada prefers-reduced-motion.
const CSS = `
:root{
  --au-pri:#6777ef;--au-pri-dark:#4f5fdc;--au-ink:#1f2440;--au-muted:#8089a8;
  --au-page-bg:linear-gradient(135deg,#eef0fb 0%,#f6f7fb 55%,#eef4ff 100%);
  --au-card-bg:#fff;--au-card-border:transparent;--au-card-shadow:0 30px 70px rgba(31,36,64,.18);
  --au-input-bg:#fafbff;--au-input-bg-focus:#fff;--au-input-border:#e6e8f2;
  --au-foot:#aab0cb;--au-orb-opacity:.5;
  --au-toggle-bg:rgba(255,255,255,.75);--au-toggle-ink:#5a6285;--au-toggle-border:rgba(31,36,64,.08);
}
html[data-theme=dark]{
  --au-ink:#e9ebfa;--au-muted:#8d95b5;
  --au-page-bg:radial-gradient(1200px 700px at 15% -10%,#1b2150 0%,transparent 55%),
            radial-gradient(1000px 600px at 110% 110%,#251b4d 0%,transparent 55%),
            linear-gradient(135deg,#0d1024 0%,#12162e 100%);
  --au-card-bg:#171c38;--au-card-border:rgba(255,255,255,.07);--au-card-shadow:0 34px 80px rgba(0,0,0,.55);
  --au-input-bg:#111530;--au-input-bg-focus:#0d112a;--au-input-border:rgba(255,255,255,.1);
  --au-foot:#5c6486;--au-orb-opacity:.35;
  --au-toggle-bg:rgba(23,28,56,.8);--au-toggle-ink:#c9cfec;--au-toggle-border:rgba(255,255,255,.1);
}
body{margin:0}
.auth-wrap{position:relative;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;
  font-family:'Plus Jakarta Sans','Segoe UI',sans-serif;color:var(--au-ink);background:var(--au-page-bg);
  overflow:hidden;transition:background .4s ease,color .4s ease}

/* ---- orb dekoratif yang melayang pelan di latar ---- */
.orb{position:absolute;border-radius:50%;filter:blur(70px);opacity:var(--au-orb-opacity);pointer-events:none;
  animation:drift 22s ease-in-out infinite alternate;transition:opacity .4s ease}
.orb-1{width:420px;height:420px;top:-120px;left:-100px;background:#6777ef}
.orb-2{width:360px;height:360px;bottom:-130px;right:-90px;background:#8b4ff0;animation-duration:27s;animation-delay:-8s}
.orb-3{width:230px;height:230px;top:55%;left:8%;background:#4fc3f0;animation-duration:31s;animation-delay:-15s;opacity:calc(var(--au-orb-opacity)*.6)}
@keyframes drift{
  0%{transform:translate(0,0) scale(1)}
  50%{transform:translate(46px,-34px) scale(1.1)}
  100%{transform:translate(-30px,26px) scale(.94)}
}

/* ---- tombol ganti tema ---- */
.theme-toggle{position:fixed;top:20px;right:20px;z-index:10;width:44px;height:44px;border-radius:50%;
  border:1px solid var(--au-toggle-border);background:var(--au-toggle-bg);color:var(--au-toggle-ink);cursor:pointer;
  display:grid;place-items:center;font-size:16px;backdrop-filter:blur(10px);
  box-shadow:0 6px 18px rgba(0,0,0,.12);transition:transform .25s ease,background .4s ease,color .4s ease}
.theme-toggle:hover{transform:scale(1.1) rotate(12deg)}
.theme-toggle:active{transform:scale(.94)}

/* ---- kartu ---- */
.auth-card{position:relative;width:100%;max-width:920px;background:var(--au-card-bg);border:1px solid var(--au-card-border);
  border-radius:24px;overflow:hidden;display:grid;grid-template-columns:1.05fr 1fr;box-shadow:var(--au-card-shadow);
  animation:card-in .65s cubic-bezier(.22,1,.36,1) both;transition:background .4s ease,border-color .4s ease,box-shadow .4s ease}
@keyframes card-in{from{opacity:0;transform:translateY(22px) scale(.985)}to{opacity:1;transform:none}}

/* ---- panel brand: gradient hidup + dekorasi mengambang ---- */
.auth-brand{position:relative;overflow:hidden;padding:48px 40px;color:#fff;
  background:linear-gradient(150deg,#5b6bf0 0%,#6a4ff0 45%,#8b4ff0 80%,#5b6bf0 120%);
  background-size:220% 220%;animation:pan 14s ease-in-out infinite;
  display:flex;flex-direction:column;justify-content:center}
html[data-theme=dark] .auth-brand{background:linear-gradient(150deg,#4652c9 0%,#5940c9 45%,#7440c9 80%,#4652c9 120%);background-size:220% 220%}
@keyframes pan{0%,100%{background-position:0% 30%}50%{background-position:100% 70%}}
.auth-brand:before,.auth-brand:after{content:"";position:absolute;border-radius:50%;background:rgba(255,255,255,.12);
  animation:bob 9s ease-in-out infinite alternate}
.auth-brand:before{width:240px;height:240px;top:-90px;right:-70px}
.auth-brand:after{width:160px;height:160px;bottom:-70px;left:-50px;animation-delay:-4.5s}
@keyframes bob{from{transform:translateY(-10px) scale(1)}to{transform:translateY(12px) scale(1.07)}}
.auth-logo{width:60px;height:60px;border-radius:16px;background:rgba(255,255,255,.18);display:grid;place-items:center;
  font-size:26px;margin-bottom:22px;position:relative;animation:float 6s ease-in-out infinite}
@keyframes float{0%,100%{transform:translateY(0)}50%{transform:translateY(-8px)}}
.auth-brand h2{font-weight:800;font-size:26px;margin:0 0 10px;position:relative}
.auth-brand p{font-size:14px;line-height:1.6;opacity:.9;margin:0 0 26px;position:relative}
.auth-features{list-style:none;padding:0;margin:0;position:relative}
.auth-features li{display:flex;align-items:center;gap:12px;font-size:13.5px;font-weight:500;padding:7px 0}
.auth-features li i{width:30px;height:30px;border-radius:9px;background:rgba(255,255,255,.16);display:grid;place-items:center;font-size:13px}

/* entrance stagger untuk isi kartu — fill backwards agar transform akhir
   tidak terkunci animation dan transition hover tetap hidup */
.auth-brand h2,.auth-brand p,.auth-features li,
.auth-form h1,.auth-form .sub,.auth-form .field,.auth-form .btn-submit,.back-link,.auth-foot{
  animation:rise .55s cubic-bezier(.22,1,.36,1) backwards}
.auth-logo{animation:rise .55s cubic-bezier(.22,1,.36,1) .08s backwards,float 6s ease-in-out .7s infinite}
.auth-brand h2{animation-delay:.14s}
.auth-brand p{animation-delay:.2s}
.auth-features li:nth-child(1){animation-delay:.26s}
.auth-features li:nth-child(2){animation-delay:.32s}
.auth-features li:nth-child(3){animation-delay:.38s}
.auth-form h1{animation-delay:.16s}
.auth-form .sub{animation-delay:.22s}
.auth-form .field:nth-of-type(1){animation-delay:.28s}
.auth-form .field:nth-of-type(2){animation-delay:.34s}
.auth-form .btn-submit{animation-delay:.4s}
.back-link{animation-delay:.46s}
.auth-foot{animation-delay:.5s}
@keyframes rise{from{opacity:0;transform:translateY(14px)}}

/* ---- form ---- */
.auth-form{padding:48px 44px;display:flex;flex-direction:column;justify-content:center}
.auth-form h1{font-weight:800;font-size:24px;margin:0 0 4px}
.auth-form .sub{color:var(--au-muted);font-size:14px;margin:0 0 26px}
.field{margin-bottom:18px}
.field label{display:block;font-size:13px;font-weight:600;margin-bottom:7px;color:var(--au-ink)}
.input-icon{position:relative;display:flex;align-items:center}
.input-icon>i.lead-ico{position:absolute;left:16px;color:var(--au-muted);font-size:15px;pointer-events:none;transition:color .18s}
.input-icon:focus-within>i.lead-ico{color:var(--au-pri)}
.input-icon input{width:100%;border:1.6px solid var(--au-input-border);background:var(--au-input-bg);border-radius:12px;
  padding:13px 16px 13px 44px;font-size:14.5px;font-family:inherit;color:var(--au-ink);outline:none;
  transition:border-color .18s,box-shadow .18s,background .4s ease,color .4s ease}
.input-icon input::placeholder{color:var(--au-muted);opacity:.75}
.input-icon input:focus{border-color:var(--au-pri);background:var(--au-input-bg-focus);box-shadow:0 0 0 4px rgba(103,119,239,.16)}
.input-icon.has-error{animation:shake .4s cubic-bezier(.36,.07,.19,.97)}
.input-icon.has-error input{border-color:#fc544b}
@keyframes shake{10%,90%{transform:translateX(-1px)}20%,80%{transform:translateX(2px)}30%,50%,70%{transform:translateX(-4px)}40%,60%{transform:translateX(4px)}}
.toggle-pass{position:absolute;right:8px;border:0;background:transparent;color:var(--au-muted);width:36px;height:36px;border-radius:9px;cursor:pointer;transition:color .18s,background .18s}
.toggle-pass:hover{color:var(--au-pri);background:rgba(103,119,239,.12)}
.field-error{display:block;color:#fc544b;font-size:12.5px;font-weight:600;margin-top:6px}

/* ---- tombol submit dengan kilau saat hover ---- */
.btn-submit{position:relative;overflow:hidden;width:100%;border:0;border-radius:12px;padding:14px;font-size:15px;font-weight:700;color:#fff;cursor:pointer;
  background:linear-gradient(135deg,var(--au-pri),var(--au-pri-dark));box-shadow:0 10px 22px rgba(103,119,239,.4);
  transition:transform .18s,box-shadow .18s;display:inline-flex;align-items:center;justify-content:center;gap:9px;margin-top:4px}
.btn-submit:after{content:"";position:absolute;top:0;left:-80%;width:50%;height:100%;
  background:linear-gradient(100deg,transparent,rgba(255,255,255,.35),transparent);transform:skewX(-20deg);transition:left .55s ease}
.btn-submit:hover{transform:translateY(-2px);box-shadow:0 14px 28px rgba(103,119,239,.5)}
.btn-submit:hover:after{left:130%}
.btn-submit:disabled{opacity:.7;transform:none}
html[data-theme=dark] .btn-submit{box-shadow:0 10px 24px rgba(79,95,220,.45)}

.back-link{text-align:center;margin-top:22px;font-size:13.5px}
.back-link a{color:var(--au-muted);font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:7px;transition:color .18s}
.back-link a:hover{color:var(--au-pri)}
.auth-foot{text-align:center;color:var(--au-foot);font-size:12px;margin-top:18px}

@media (max-width:768px){.auth-card{grid-template-columns:1fr;max-width:440px}.auth-brand{display:none}.auth-form{padding:38px 28px}}
@media (prefers-reduced-motion:reduce){
  .auth-card,.orb,.auth-brand,.auth-brand:before,.auth-brand:after,.auth-logo,
  .auth-brand h2,.auth-brand p,.auth-features li,.auth-form h1,.auth-form .sub,
  .auth-form .field,.auth-form .btn-submit,.back-link,.auth-foot,.input-icon.has-error{animation:none}
}
`;

export default function Login({ action, homeUrl }) {
    const { data, setData, post, processing, errors } = useForm({ username: '', password: '' });
    const [showPass, setShowPass] = useState(false);
    const [theme, setTheme] = useState(() =>
        typeof document !== 'undefined' ? document.documentElement.dataset.theme || 'light' : 'light'
    );

    const submit = (e) => {
        e.preventDefault();
        post(action);
    };

    const toggleTheme = () => {
        if (window.spbuTheme) {
            setTheme(window.spbuTheme.toggle());
        }
    };

    return (
        <div className="auth-wrap">
            <Head title="Login" />
            <style dangerouslySetInnerHTML={{ __html: CSS }} />

            <div className="orb orb-1"></div>
            <div className="orb orb-2"></div>
            <div className="orb orb-3"></div>

            <button
                type="button"
                className="theme-toggle"
                onClick={toggleTheme}
                title={theme === 'dark' ? 'Gunakan mode terang' : 'Gunakan mode gelap'}
                aria-label={theme === 'dark' ? 'Gunakan mode terang' : 'Gunakan mode gelap'}
            >
                <i className={`fas fa-${theme === 'dark' ? 'sun' : 'moon'}`}></i>
            </button>

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
                            <div className={`input-icon ${errors.username ? 'has-error' : ''}`} key={`u-${errors.username || ''}`}>
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
                            <div className={`input-icon ${errors.password ? 'has-error' : ''}`} key={`p-${errors.password || ''}`}>
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
