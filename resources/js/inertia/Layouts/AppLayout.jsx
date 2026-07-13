import { useEffect, useMemo, useRef, useState } from 'react';
import { Head, router, usePage } from '@inertiajs/react';
import { isInertiaUrl, localUrl } from '../lib/inertiaRoutes';

// Breakpoint mobile (di bawah ini sidebar jadi overlay, bukan rail)
const MOBILE_BP = 1024;
const THEME_KEY = 'spbu-theme';
const RAIL_KEY = 'spbu-sidebar-rail';

const getInitialTheme = () => {
    if (typeof window === 'undefined') return 'light';
    try {
        const saved = window.localStorage.getItem(THEME_KEY);
        if (saved === 'dark' || saved === 'light') return saved;
    } catch {
        return 'light';
    }
    return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
};

const getInitialRail = () => {
    if (typeof window === 'undefined') return false;
    try {
        return window.localStorage.getItem(RAIL_KEY) === '1';
    } catch {
        return false;
    }
};

const applyColorTheme = (theme) => {
    if (typeof document === 'undefined') return;
    document.documentElement.dataset.theme = theme;
    document.documentElement.style.colorScheme = theme;
};

const pathOf = (u) => {
    try {
        return new URL(u, window.location.origin).pathname.replace(/\/+$/, '') || '/';
    } catch {
        return u;
    }
};

const prefixMatch = (current, path) => current === path || current.startsWith(path + '/');

// Link tetap dirender sebagai anchor biasa. Click delegation di AppLayout akan
// mengubah target Inertia menjadi router.visit(), sementara route Blade/file
// tetap berjalan sebagai navigasi browser penuh agar script jQuery lama aman.
function NavAnchor({ item, className, children, ...props }) {
    return (
        <a className={className} href={item.url} {...props}>
            {children}
        </a>
    );
}

// child ber-flag exact (mis. "Ringkasan" yang URL-nya /stok) hanya aktif pada
// kecocokan persis — tanpa ini /stok/totalisator ikut menyalakan parent lewat prefix.
const childIsActive = (current, c) => (c.exact ? current === pathOf(c.url) : prefixMatch(current, pathOf(c.url)));

// Saat rail, flyout dipasang position:fixed (agar tidak ter-clip overflow
// .sb-nav) lalu posisinya di-set dari rect item lewat CSS var --fly-top.
const pinFlyout = (e) => {
    const top = e.currentTarget.getBoundingClientRect().top;
    e.currentTarget.style.setProperty('--fly-top', `${Math.max(top, 8)}px`);
};

function SidebarItem({ item, current }) {
    const hasChildren = (item.children || []).length > 0;
    const childActive = hasChildren && item.children.some((c) => childIsActive(current, c));
    const active = hasChildren ? childActive || current === pathOf(item.url) : prefixMatch(current, pathOf(item.url));
    const [open, setOpen] = useState(active);

    if (!hasChildren) {
        return (
            <li className={`sb-item ${active ? 'active' : ''}`} onMouseEnter={pinFlyout}>
                <NavAnchor item={item} className="sb-link">
                    <i className={`sb-ico fas fa-${item.icon}`}></i>
                    <span className="sb-label">{item.label}</span>
                </NavAnchor>
                {/* tooltip saat mode rail */}
                <span className="sb-flyout sb-flyout-tip">{item.label}</span>
            </li>
        );
    }

    return (
        <li className={`sb-item has-sub ${active ? 'active' : ''} ${open ? 'open' : ''}`} onMouseEnter={pinFlyout}>
            <button
                type="button"
                className="sb-link"
                onClick={() => setOpen((v) => !v)}
                aria-expanded={open}
            >
                <i className={`sb-ico fas fa-${item.icon}`}></i>
                <span className="sb-label">{item.label}</span>
                <i className="sb-caret fas fa-chevron-right"></i>
            </button>
            {/* satu markup, dua wujud: akordion di mode penuh, flyout saat rail */}
            <div className="sb-flyout sb-flyout-menu">
                <span className="sb-flyout-head">{item.label}</span>
                <ul className="sb-sub">
                    {item.children.map((c) => {
                        const cActive = childIsActive(current, c);
                        return (
                            <li key={c.url + c.label} className={`sb-subitem ${cActive ? 'active' : ''}`}>
                                <NavAnchor item={c} className="sb-sublink">
                                    <span className="sb-dot"></span>
                                    {c.label}
                                </NavAnchor>
                            </li>
                        );
                    })}
                </ul>
            </div>
        </li>
    );
}

export default function AppLayout({ title, children }) {
    const page = usePage();
    const { auth, navigation, urls, flash, spa } = page.props;
    const current = pathOf(page.url);
    const spaRoutes = spa?.routes || [];
    // Tema: nilai awal dari localStorage / preferensi sistem; diganti lewat
    // item di menu nav-user (tak ada di header karena navbar dihapus).
    const [theme, setTheme] = useState(getInitialTheme);
    const [rail, setRail] = useState(getInitialRail);
    const [mobileOpen, setMobileOpen] = useState(false);
    const [userMenu, setUserMenu] = useState(false);
    const userRef = useRef(null);

    useEffect(() => {
        applyColorTheme(theme);
        try {
            window.localStorage.setItem(THEME_KEY, theme);
        } catch {
            // Theme still applies for the current session when storage is unavailable.
        }
    }, [theme]);

    useEffect(() => {
        try {
            window.localStorage.setItem(RAIL_KEY, rail ? '1' : '0');
        } catch {
            // Preferensi rail hanya untuk sesi ini bila storage tak tersedia.
        }
    }, [rail]);

    // Delegasi link SPA: anchor yang menunjuk route Inertia diubah menjadi
    // router.visit(). Link Blade, download, target blank, hash, dan modifier
    // click tetap dibiarkan ke browser supaya plugin/script jQuery legacy tidak
    // kehilangan siklus page-load normalnya.
    useEffect(() => {
        const onClick = (e) => {
            if (
                e.defaultPrevented ||
                e.button !== 0 ||
                e.metaKey ||
                e.ctrlKey ||
                e.shiftKey ||
                e.altKey
            ) {
                return;
            }

            const target = e.target;
            if (!(target instanceof Element)) return;

            const anchor = target.closest('a[href]');
            if (!anchor || anchor.closest('[data-no-inertia]')) return;
            if (anchor.hasAttribute('download')) return;
            if (anchor.target && anchor.target.toLowerCase() !== '_self') return;

            const href = anchor.getAttribute('href');
            const url = localUrl(href);
            if (!url || !isInertiaUrl(href, spaRoutes)) return;

            const sameDocumentHash =
                url.pathname === window.location.pathname &&
                url.search === window.location.search &&
                url.hash;
            if (sameDocumentHash) return;

            e.preventDefault();
            router.visit(url.pathname + url.search + url.hash, {
                preserveScroll: url.pathname === window.location.pathname,
            });
        };

        document.addEventListener('click', onClick, true);
        return () => document.removeEventListener('click', onClick, true);
    }, [spaRoutes]);

    useEffect(() => {
        const onSubmit = (e) => {
            if (e.defaultPrevented) return;
            const form = e.target;
            if (!(form instanceof HTMLFormElement)) return;
            if (form.closest('[data-no-inertia]')) return;

            const action = form.getAttribute('action') || window.location.href;
            const url = localUrl(action);
            if (!url || !isInertiaUrl(action, spaRoutes)) return;

            const formData = new FormData(form);
            const method = String(formData.get('_method') || form.method || 'get').toLowerCase();
            if (!['get', 'post', 'put', 'patch', 'delete'].includes(method)) return;

            e.preventDefault();

            const target = url.pathname + url.search + url.hash;
            const options = { preserveScroll: true };

            if (method === 'get') {
                router.get(target, Object.fromEntries(formData.entries()), options);
                return;
            }

            formData.delete('_method');
            router[method](target, formData, options);
        };

        document.addEventListener('submit', onSubmit, true);
        return () => document.removeEventListener('submit', onSubmit, true);
    }, [spaRoutes]);

    // Tutup overlay mobile saat layar membesar, dan saat berpindah halaman.
    useEffect(() => {
        const onResize = () => {
            if (window.innerWidth > MOBILE_BP) setMobileOpen(false);
        };
        window.addEventListener('resize', onResize);
        const offNavigate = router.on('success', () => setMobileOpen(false));
        return () => {
            window.removeEventListener('resize', onResize);
            offNavigate();
        };
    }, []);

    // Kunci scroll body saat overlay mobile terbuka.
    useEffect(() => {
        document.body.style.overflow = mobileOpen ? 'hidden' : '';
        return () => {
            document.body.style.overflow = '';
        };
    }, [mobileOpen]);

    // Tutup menu user saat klik di luar.
    useEffect(() => {
        const onClick = (e) => {
            if (userRef.current && !userRef.current.contains(e.target)) setUserMenu(false);
        };
        document.addEventListener('mousedown', onClick);
        return () => document.removeEventListener('mousedown', onClick);
    }, []);

    const toggleRail = () => setRail((v) => !v);
    const openMobile = () => setMobileOpen((v) => !v);

    const logout = (e) => {
        e.preventDefault();
        router.post(urls.logout);
    };
    const toggleTheme = () => setTheme((t) => (t === 'dark' ? 'light' : 'dark'));

    const menuHeader = useMemo(() => auth?.level ?? 'Menu Utama', [auth]);

    return (
        <div id="app">
            <Head title={title} />
            <style dangerouslySetInnerHTML={{ __html: CSS }} />

            <div className={`app-shell ${rail ? 'rail' : ''}`}>
                {mobileOpen && <div className="sb-backdrop" onClick={() => setMobileOpen(false)}></div>}

                {/* pemicu mengambang khusus mobile (pengganti navbar) */}
                <button type="button" className="mobile-trigger" onClick={openMobile} aria-label="Buka menu">
                    <i className="fas fa-bars"></i>
                </button>

                <aside className={`app-sidebar ${mobileOpen ? 'mobile-open' : ''}`}>
                    <div className="sb-brand">
                        <NavAnchor item={{ url: urls.home, inertia: false }} className="sb-brand-link">
                            <span className="sb-brand-logo">
                                <img src="/images/logo.jpg" alt="logo" width="26" />
                            </span>
                            <span className="sb-brand-text">
                                <strong>SPBU</strong>
                                <small>Sistem Monitoring</small>
                            </span>
                        </NavAnchor>
                    </div>

                    <nav className="sb-nav">
                        <div className="sb-group-label">{menuHeader}</div>
                        <ul className="sb-menu">
                            {navigation.map((item) => (
                                <SidebarItem key={item.label} item={item} current={current} />
                            ))}
                        </ul>
                    </nav>

                    <div className="sb-user" ref={userRef}>
                        <button type="button" className={`sb-user-btn ${userMenu ? 'open' : ''}`} onClick={() => setUserMenu((v) => !v)}>
                            <img className="sb-avatar" src={auth?.avatar} alt="profil" />
                            <span className="sb-user-info">
                                <strong>{auth?.nama || 'Pengguna'}</strong>
                                <small>{auth?.level || 'Akun'}</small>
                            </span>
                            <i className="sb-user-caret fas fa-ellipsis-vertical"></i>
                        </button>
                        {userMenu && (
                            <div className="sb-user-menu">
                                <a href={urls.profil} className="sb-user-item">
                                    <i className="fas fa-user"></i> Profil Saya
                                </a>
                                <a href={urls.notifikasi} className="sb-user-item">
                                    <i className="fas fa-bell"></i> Notifikasi
                                </a>
                                <button type="button" className="sb-user-item" onClick={toggleTheme}>
                                    <i className={`fas fa-${theme === 'dark' ? 'sun' : 'moon'}`}></i>
                                    {theme === 'dark' ? 'Mode Terang' : 'Mode Gelap'}
                                </button>
                                <div className="sb-user-sep"></div>
                                <a href={urls.logout} onClick={logout} className="sb-user-item danger">
                                    <i className="fas fa-arrow-right-from-bracket"></i> Keluar
                                </a>
                            </div>
                        )}
                    </div>

                    {/* strip tepi (ala shadcn SidebarRail) — ciutkan/lebarkan di desktop */}
                    <button
                        type="button"
                        className="sb-rail-toggle"
                        onClick={toggleRail}
                        title={rail ? 'Lebarkan menu' : 'Ciutkan menu'}
                        aria-label={rail ? 'Lebarkan menu' : 'Ciutkan menu'}
                    ></button>
                </aside>

                <div className="app-main">
                    <div className="app-content">
                        <section className="section">
                            {flash?.msg && <div dangerouslySetInnerHTML={{ __html: flash.msg }} />}
                            {children}
                        </section>
                        <footer className="app-foot">
                            SPBU <span className="app-foot-dot"></span> {new Date().getFullYear()} Sistem Monitoring
                        </footer>
                    </div>
                </div>
            </div>
        </div>
    );
}

// CSS scoped ke shell baru (kelas sb-*/hdr-*/app-*), memakai token desain
// theme.css (--surface/--border/--foreground/--muted/--pri) agar selaras dengan
// kartu & tabel, dan otomatis mengikuti mode gelap. Sengaja pakai nama kelas
// sendiri supaya tidak bentrok aturan stisla lama (.main-navbar/.main-sidebar).
const CSS = `
:root{
  --shell-w:264px;--shell-rail:76px;--shell-header:60px;
  --glass-bg:rgba(255,255,255,.46);
  --glass-bg-strong:rgba(255,255,255,.66);
  --glass-border:rgba(255,255,255,.72);
  --glass-divider:rgba(255,255,255,.44);
  --glass-highlight:rgba(255,255,255,.48);
  --glass-shadow:0 22px 58px rgba(31,36,64,.14);
  --glass-inset:inset 0 1px 0 rgba(255,255,255,.70), inset 0 -1px 0 rgba(255,255,255,.24);
  --app-ink-bg:
    radial-gradient(920px 560px at 8% 4%, rgba(103,119,239,.22) 0%, rgba(103,119,239,.10) 34%, transparent 68%),
    radial-gradient(780px 520px at 96% 10%, rgba(139,79,240,.18) 0%, rgba(139,79,240,.08) 36%, transparent 70%),
    radial-gradient(760px 500px at 18% 92%, rgba(79,195,240,.16) 0%, rgba(79,195,240,.07) 35%, transparent 72%),
    linear-gradient(135deg,#eef0fb 0%,#f8f9ff 52%,#eef4ff 100%);
}
html[data-theme=dark]{
  --glass-bg:rgba(15,23,42,.56);
  --glass-bg-strong:rgba(30,41,59,.62);
  --glass-border:rgba(255,255,255,.14);
  --glass-divider:rgba(255,255,255,.09);
  --glass-highlight:rgba(255,255,255,.10);
  --glass-shadow:0 24px 66px rgba(0,0,0,.38);
  --glass-inset:inset 0 1px 0 rgba(255,255,255,.16), inset 0 -1px 0 rgba(255,255,255,.06);
  --app-ink-bg:
    radial-gradient(1120px 680px at 10% 2%, rgba(70,82,201,.46) 0%, rgba(70,82,201,.20) 34%, transparent 68%),
    radial-gradient(980px 620px at 96% 12%, rgba(116,64,201,.38) 0%, rgba(116,64,201,.16) 36%, transparent 70%),
    radial-gradient(860px 560px at 22% 90%, rgba(38,150,190,.24) 0%, rgba(38,150,190,.10) 38%, transparent 72%),
    linear-gradient(135deg,#0d1024 0%,#12162e 100%);
}
/* Background "ink" setelah login. Semua halaman authenticated lewat AppLayout,
   jadi perubahan background cukup dikendalikan dari file ini. */
.app-shell{
  display:flex;
  min-height:100vh;
  color:var(--foreground);
  background:var(--app-ink-bg);
  background-attachment:fixed;
}
.app-shell *{
  box-sizing:border-box;
}
.app-glass,
.app-sidebar,
.app-content .section .section-header,
.app-content .card,
.sb-user-menu,
.app-shell.rail .sb-flyout,
.mobile-trigger{
  background:
    linear-gradient(135deg,var(--glass-highlight) 0%, transparent 42%),
    linear-gradient(180deg,var(--glass-bg-strong) 0%, var(--glass-bg) 100%) !important;
  border:1px solid var(--glass-border) !important;
  box-shadow:var(--glass-shadow), var(--glass-inset) !important;
  backdrop-filter:blur(28px) saturate(1.35);
  -webkit-backdrop-filter:blur(28px) saturate(1.35);
}
.app-content .section .section-header,
.app-content .card,
.sb-user-menu,
.app-shell.rail .sb-flyout,
.mobile-trigger{
  border-radius:18px;
}
.app-content .section .section-header{
  margin-bottom:24px;
}
.app-content .section .section-header h1,
.app-content .section .section-header .breadcrumb-item,
.app-content .section .section-header .breadcrumb-item a{
  position:relative;
  z-index:1;
}
.app-content .card .card-header,
.app-content .card .card-body,
.app-content .card .card-footer{
  position:relative;
  z-index:1;
}
.app-content .card .card-header,
.app-content .card .card-footer{
  background:rgba(255,255,255,.14) !important;
  border-color:var(--glass-divider) !important;
}
html[data-theme=dark] .app-content .card .card-header,
html[data-theme=dark] .app-content .card .card-footer{
  background:rgba(15,23,42,.18) !important;
}
.sb-backdrop{position:fixed;inset:0;z-index:940;background:rgba(2,6,23,.5);backdrop-filter:blur(1px);animation:fade .2s ease}
@keyframes fade{from{opacity:0}to{opacity:1}}

/* ---------- SIDEBAR ---------- */
.app-sidebar{position:sticky;top:0;align-self:flex-start;height:100vh;flex:0 0 var(--shell-w);width:var(--shell-w);
  display:flex;flex-direction:column;border-radius:0 24px 24px 0;
  transition:width .22s ease,flex-basis .22s ease;z-index:950}
.app-shell.rail .app-sidebar{flex-basis:var(--shell-rail);width:var(--shell-rail)}

.sb-brand{display:flex;align-items:center;gap:10px;height:var(--shell-header);padding:0 12px 0 16px;border-bottom:1px solid var(--glass-divider);flex-shrink:0}
.sb-brand-link{display:flex;align-items:center;gap:11px;text-decoration:none;color:inherit;min-width:0;flex:1}
.sb-brand-logo{width:36px;height:36px;border-radius:9px;background:var(--pri);display:grid;place-items:center;flex-shrink:0;overflow:hidden}
.sb-brand-logo img{border-radius:6px;display:block}
.sb-brand-text{display:flex;flex-direction:column;line-height:1.2;min-width:0}
.sb-brand-text strong{font-size:14px;font-weight:700;color:var(--foreground)}
.sb-brand-text small{font-size:11px;color:var(--muted)}
.app-shell.rail .sb-brand{padding:0;justify-content:center}
.app-shell.rail .sb-brand-text{display:none}

.sb-nav{flex:1;overflow-y:auto;overflow-x:hidden;padding:12px 12px 8px;scrollbar-width:thin}
.sb-nav::-webkit-scrollbar{width:6px}
.sb-nav::-webkit-scrollbar-thumb{background:var(--glass-divider);border-radius:3px}
.sb-group-label{font-size:11px;font-weight:600;letter-spacing:.04em;text-transform:uppercase;color:var(--muted);padding:6px 10px 8px}
.app-shell.rail .sb-group-label{height:1px;padding:0;margin:4px 14px 10px;background:var(--glass-divider);font-size:0;overflow:hidden}
.sb-menu{list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:2px}

.sb-item{position:relative;--sb-icon:#6777ef}
.sb-menu>.sb-item:nth-child(12n+1){--sb-icon:#6777ef}
.sb-menu>.sb-item:nth-child(12n+2){--sb-icon:#fc544b}
.sb-menu>.sb-item:nth-child(12n+3){--sb-icon:#ffa426}
.sb-menu>.sb-item:nth-child(12n+4){--sb-icon:#47c363}
.sb-menu>.sb-item:nth-child(12n+5){--sb-icon:#3abaf4}
.sb-menu>.sb-item:nth-child(12n+6){--sb-icon:#9b59b6}
.sb-menu>.sb-item:nth-child(12n+7){--sb-icon:#14b8a6}
.sb-menu>.sb-item:nth-child(12n+8){--sb-icon:#f97316}
.sb-menu>.sb-item:nth-child(12n+9){--sb-icon:#ec4899}
.sb-menu>.sb-item:nth-child(12n+10){--sb-icon:#22c55e}
.sb-menu>.sb-item:nth-child(12n+11){--sb-icon:#06b6d4}
.sb-menu>.sb-item:nth-child(12n+12){--sb-icon:#a855f7}
.sb-link{display:flex;align-items:center;gap:11px;width:100%;border:0;background:transparent;cursor:pointer;
  text-decoration:none;color:var(--muted);font-size:13.5px;font-weight:500;font-family:inherit;text-align:left;
  padding:9px 10px;border-radius:8px;transition:background .15s,color .15s}
.sb-link:hover{background:rgba(255,255,255,.26);color:var(--foreground)}
html[data-theme=dark] .sb-link:hover{background:rgba(255,255,255,.07)}
.sb-ico{width:18px;font-size:14px;text-align:center;flex-shrink:0;color:var(--sb-icon);filter:drop-shadow(0 4px 10px color-mix(in srgb, var(--sb-icon) 38%, transparent))}
.sb-label{flex:1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.sb-caret{font-size:11px;color:var(--muted);transition:transform .2s ease}
.sb-item.open>.sb-link .sb-caret{transform:rotate(90deg)}
.sb-item.active>.sb-link{background:rgba(255,255,255,.34);color:var(--foreground);font-weight:600;box-shadow:inset 0 1px 0 rgba(255,255,255,.32)}
html[data-theme=dark] .sb-item.active>.sb-link{background:rgba(255,255,255,.10)}
.sb-item.active>.sb-link .sb-ico{color:var(--sb-icon)}

/* submenu (akordion mode penuh) */
.sb-flyout-menu{overflow:hidden}
.sb-flyout-head{display:none}
.sb-sub{list-style:none;margin:3px 0 3px;padding:0 0 0 30px;display:none;flex-direction:column;gap:1px;position:relative}
.sb-sub:before{content:"";position:absolute;left:19px;top:2px;bottom:6px;width:1.5px;background:var(--glass-divider)}
.sb-item.open>.sb-flyout-menu .sb-sub{display:flex}
.sb-sublink{display:flex;align-items:center;gap:9px;text-decoration:none;color:var(--muted);font-size:13px;font-weight:500;
  padding:7px 10px;border-radius:7px;transition:background .15s,color .15s}
.sb-sublink:hover{background:rgba(255,255,255,.22);color:var(--foreground)}
html[data-theme=dark] .sb-sublink:hover{background:rgba(255,255,255,.07)}
.sb-dot{width:5px;height:5px;border-radius:50%;background:currentColor;opacity:.5;flex-shrink:0}
.sb-subitem.active>.sb-sublink{color:var(--foreground);font-weight:600}
.sb-subitem.active>.sb-sublink .sb-dot{opacity:1;background:var(--pri)}
.sb-flyout-tip{display:none}

/* ---------- MODE RAIL (ikon) ---------- */
.app-shell.rail .sb-label,.app-shell.rail .sb-caret{display:none}
.app-shell.rail .sb-link{justify-content:center;padding:11px 0}
.app-shell.rail .sb-ico{width:auto;font-size:16px}
/* submenu tampil sebagai flyout (fixed) saat hover; di rail .sb-flyout-menu
   ADALAH flyout-nya sendiri, jadi .sb-sub selalu tampil di dalamnya */
.app-shell.rail .sb-flyout{position:fixed;left:calc(var(--shell-rail) + 6px);top:var(--fly-top,80px);min-width:190px;
  padding:6px;opacity:0;visibility:hidden;transform:translateX(-6px);
  transition:opacity .16s ease,transform .16s ease,visibility .16s;z-index:960}
.app-shell.rail .sb-item:hover>.sb-flyout{opacity:1;visibility:visible;transform:none}
.app-shell.rail .sb-flyout:before{content:"";position:absolute;left:-10px;top:0;width:12px;height:100%}
.app-shell.rail .sb-flyout-head{display:block;font-size:12px;font-weight:700;color:var(--foreground);padding:6px 10px 8px;border-bottom:1px solid var(--glass-divider);margin-bottom:4px}
.app-shell.rail .sb-flyout-menu .sb-sub{display:flex;padding:0}
.app-shell.rail .sb-flyout-menu .sb-sub:before{display:none}
.app-shell.rail .sb-flyout-tip{display:block;font-size:12.5px;font-weight:600;color:var(--foreground);white-space:nowrap}
.app-shell.rail .sb-flyout-tip.sb-flyout{padding:9px 12px;top:2px}

/* user footer */
.sb-user{position:relative;border-top:1px solid var(--glass-divider);padding:10px;flex-shrink:0}
.sb-user-btn{display:flex;align-items:center;gap:10px;width:100%;border:0;background:transparent;cursor:pointer;
  padding:8px;border-radius:9px;transition:background .15s;font-family:inherit}
.sb-user-btn:hover,.sb-user-btn.open{background:rgba(255,255,255,.24)}
html[data-theme=dark] .sb-user-btn:hover,
html[data-theme=dark] .sb-user-btn.open{background:rgba(255,255,255,.07)}
.sb-avatar{width:34px;height:34px;border-radius:8px;object-fit:cover;flex-shrink:0;background:var(--surface-soft)}
.sb-user-info{display:flex;flex-direction:column;line-height:1.25;min-width:0;flex:1;text-align:left}
.sb-user-info strong{font-size:13px;font-weight:600;color:var(--foreground);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.sb-user-info small{font-size:11px;color:var(--muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.sb-user-caret{color:var(--muted);font-size:13px;flex-shrink:0}
.app-shell.rail .sb-user{padding:10px 8px}
.app-shell.rail .sb-user-info,.app-shell.rail .sb-user-caret{display:none}
.app-shell.rail .sb-user-btn{justify-content:center}
.sb-user-menu{position:absolute;left:10px;right:10px;bottom:calc(100% - 2px);padding:6px;z-index:961;animation:pop .16s ease}
@keyframes pop{from{opacity:0;transform:translateY(6px)}to{opacity:1;transform:none}}
.app-shell.rail .sb-user-menu{left:10px;right:auto;min-width:190px}
.sb-user-item{display:flex;align-items:center;gap:10px;width:100%;border:0;background:transparent;cursor:pointer;
  text-decoration:none;color:var(--foreground);font-size:13px;font-weight:500;font-family:inherit;text-align:left;
  padding:9px 10px;border-radius:7px;transition:background .15s}
.sb-user-item i{width:16px;text-align:center;color:var(--muted)}
.sb-user-item:hover{background:rgba(255,255,255,.24)}
html[data-theme=dark] .sb-user-item:hover{background:rgba(255,255,255,.07)}
.sb-user-item.danger{color:#ef4444}
.sb-user-item.danger i{color:#ef4444}
.sb-user-sep{height:1px;background:var(--glass-divider);margin:5px 0}

/* strip tepi untuk ciutkan/lebarkan (ala shadcn SidebarRail) */
.sb-rail-toggle{position:absolute;top:0;right:-8px;width:16px;height:100%;border:0;background:transparent;cursor:pointer;z-index:955;padding:0}
.sb-rail-toggle:after{content:"";position:absolute;top:0;left:7px;width:2px;height:100%;background:transparent;border-radius:2px;transition:background .15s}
.sb-rail-toggle:hover:after{background:var(--glass-border)}

/* pemicu mengambang khusus mobile (pengganti navbar) */
.mobile-trigger{display:none}

/* ---------- MAIN ---------- */
.app-main{flex:1;min-width:0;display:flex;flex-direction:column}
.app-content{flex:1;display:flex;flex-direction:column;padding:28px}
.app-content>.section{flex:1}
.app-foot{margin-top:24px;padding-top:16px;border-top:1px solid var(--border);color:var(--muted);font-size:12.5px;display:flex;align-items:center;gap:8px}
.app-foot-dot{width:4px;height:4px;border-radius:50%;background:var(--muted)}

/* ---------- MOBILE ---------- */
@media (max-width:1024px){
  .app-sidebar{position:fixed;top:0;left:0;height:100vh;flex-basis:var(--shell-w);width:var(--shell-w);
    transform:translateX(-100%);box-shadow:0 20px 60px rgba(2,6,23,.35)}
  .app-sidebar.mobile-open{transform:none}
  .app-shell.rail .app-sidebar{flex-basis:var(--shell-w);width:var(--shell-w)}
  /* di mobile selalu mode penuh walau flag rail aktif */
  .app-shell.rail .sb-label,.app-shell.rail .sb-caret,.app-shell.rail .sb-brand-text,
  .app-shell.rail .sb-user-info,.app-shell.rail .sb-user-caret{display:flex}
  .app-shell.rail .sb-brand{padding:0 12px 0 16px;justify-content:flex-start}
  .app-shell.rail .sb-link{justify-content:flex-start;padding:9px 10px}
  .app-shell.rail .sb-ico{width:18px;font-size:14px}
  .app-shell.rail .sb-item.open>.sb-flyout-menu .sb-sub{display:flex}
  .app-shell.rail .sb-flyout{position:static;opacity:1;visibility:visible;transform:none;box-shadow:none;border:0;padding:0;min-width:0;background:transparent}
  .app-shell.rail .sb-flyout-head{display:none}
  .app-shell.rail .sb-flyout-tip{display:none}
  .app-shell.rail .sb-group-label{text-align:left;font-size:11px;padding:6px 10px 8px}
  .app-shell.rail .sb-user-btn{justify-content:flex-start}
  .sb-rail-toggle{display:none}
  .mobile-trigger{display:grid;position:fixed;top:14px;left:14px;z-index:930;width:42px;height:42px;
    color:var(--foreground);place-items:center;font-size:17px;cursor:pointer}
  .app-content{padding:66px 16px 20px}
}
@media (prefers-reduced-motion:reduce){
  .app-sidebar,.sb-caret,.sb-flyout,.sb-link,.sb-rail-toggle:after{transition:none}
  .sb-backdrop,.sb-user-menu{animation:none}
}
`;
