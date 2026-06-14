import { useEffect, useMemo, useState } from 'react';
import { Head, Link, router, usePage } from '@inertiajs/react';

// Breakpoint mobile stisla (media query max-width: 1024px di app.css)
const MOBILE_BP = 1024;

const pathOf = (u) => {
    try {
        return new URL(u, window.location.origin).pathname.replace(/\/+$/, '') || '/';
    } catch {
        return u;
    }
};

const prefixMatch = (current, path) => current === path || current.startsWith(path + '/');

// Halaman yang sudah Inertia diberi flag `inertia: true` dari Navigation.php
// dan dinavigasi via <Link> (SPA + NProgress). Halaman yang masih Blade harus
// memakai <a> biasa — <Link> ke Blade akan ditolak Inertia.
function NavAnchor({ item, className, children }) {
    return item.inertia ? (
        <Link className={className} href={item.url}>
            {children}
        </Link>
    ) : (
        <a className={className} href={item.url}>
            {children}
        </a>
    );
}

// child ber-flag exact (mis. "Ringkasan" yang URL-nya /stok) hanya aktif pada
// kecocokan persis — tanpa ini /stok/totalisator ikut menyalakan parent
// Manajemen Persediaan lewat prefix /stok.
const childIsActive = (current, c) => (c.exact ? current === pathOf(c.url) : prefixMatch(current, pathOf(c.url)));

function SidebarItem({ item, current }) {
    const hasChildren = (item.children || []).length > 0;
    const childActive = hasChildren && item.children.some((c) => childIsActive(current, c));
    // parent dengan submenu: highlight hanya jika exact (halaman menu) atau ada child aktif,
    // agar prefix pendek (mis. /stok) tidak ikut menyala di /stok/totalisator.
    const active = hasChildren ? childActive || current === pathOf(item.url) : prefixMatch(current, pathOf(item.url));
    const [open, setOpen] = useState(active);

    if (!hasChildren) {
        return (
            <li className={active ? 'active' : ''}>
                <NavAnchor item={item} className="nav-link">
                    <i className={`fas fa-${item.icon}`}></i>
                    <span>{item.label}</span>
                </NavAnchor>
            </li>
        );
    }

    return (
        <li className={`dropdown ${active ? 'active' : ''}`}>
            <a
                href="#"
                className="nav-link has-dropdown"
                onClick={(e) => {
                    e.preventDefault();
                    setOpen(!open);
                }}
            >
                <i className={`fas fa-${item.icon}`}></i> <span>{item.label}</span>
            </a>
            <ul className="dropdown-menu" style={{ display: open ? 'block' : 'none' }}>
                {item.children.map((c) => {
                    const cActive = childIsActive(current, c);
                    return (
                        <li key={c.url + c.label} className={cActive ? 'active' : ''}>
                            <NavAnchor item={c} className="nav-link">
                                {c.label}
                            </NavAnchor>
                        </li>
                    );
                })}
            </ul>
        </li>
    );
}

export default function AppLayout({ title, children }) {
    const { auth, navigation, urls, flash } = usePage().props;
    const current = pathOf(usePage().url);

    // Di Blade, stisla.js yang mengatur kelas body utk responsive sidebar;
    // di halaman React tugas itu di sini: layar kecil -> sidebar tersembunyi
    // (sidebar-gone), hamburger menampilkan sbg overlay (sidebar-show),
    // klik di luar sidebar / navigasi menutupnya kembali.
    useEffect(() => {
        const sync = () => {
            if (window.innerWidth <= MOBILE_BP) {
                document.body.classList.add('sidebar-gone');
                document.body.classList.remove('sidebar-mini');
            } else {
                document.body.classList.remove('sidebar-gone', 'sidebar-show');
            }
        };
        sync();

        // menutup = pasang lagi sidebar-gone (stisla TIDAK punya rule
        // body.sidebar-show .main-sidebar — tampil berarti gone dilepas)
        const hideMobile = () => {
            document.body.classList.remove('sidebar-show');
            if (window.innerWidth <= MOBILE_BP) document.body.classList.add('sidebar-gone');
        };
        const closeOnOutside = (e) => {
            if (!document.body.classList.contains('sidebar-show')) return;
            if (e.target.closest('.main-sidebar') || e.target.closest('[data-sidebar-toggle]')) return;
            hideMobile();
        };
        const closeOnNavigate = () => hideMobile();

        window.addEventListener('resize', sync);
        document.addEventListener('click', closeOnOutside);
        const offNavigate = router.on('success', closeOnNavigate);

        return () => {
            window.removeEventListener('resize', sync);
            document.removeEventListener('click', closeOnOutside);
            offNavigate();
        };
    }, []);

    const toggleSidebar = (e) => {
        e.preventDefault();
        const body = document.body.classList;
        if (window.innerWidth <= MOBILE_BP) {
            if (body.contains('sidebar-show')) {
                body.remove('sidebar-show');
                body.add('sidebar-gone');
            } else {
                body.remove('sidebar-gone'); // tampilkan: lepas gone (left:0)
                body.add('sidebar-show');    // backdrop + kunci scroll
            }
        } else {
            body.toggle('sidebar-mini');
        }
    };

    const logout = (e) => {
        e.preventDefault();
        router.post(urls.logout);
    };

    const menuHeader = useMemo(() => auth?.level ?? 'Menu Utama', [auth]);

    return (
        <div id="app">
            <Head title={title} />
            <div className="main-wrapper">
                <div className="navbar-bg"></div>
                <nav className="navbar navbar-expand-lg main-navbar">
                    <div className="div-inline mr-auto">
                        <ul className="navbar-nav mr-3">
                            <li>
                                <a href="#" onClick={toggleSidebar} data-sidebar-toggle className="nav-link nav-link-lg">
                                    <i className="fas fa-bars"></i>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <ul className="navbar-nav navbar-right">
                        <li className="dropdown d-flex">
                            <a href={urls.profil} className="nav-link nav-link-lg nav-link-user">
                                <img alt="profile" src={auth?.avatar} className="rounded-circle mr-1" />
                            </a>
                            <a href={urls.notifikasi} className="nav-link nav-link-lg nav-link-user">
                                <i className="fas fa-bell"></i>
                            </a>
                            <a href={urls.logout} onClick={logout} className="nav-link nav-link-lg nav-link-user" id="logout-btn">
                                <i className="fas fa-sign-out-alt"></i>
                            </a>
                        </li>
                    </ul>
                </nav>

                <div className="main-sidebar">
                    <aside id="sidebar-wrapper">
                        <div className="sidebar-brand align-items-center">
                            <a href={urls.home}>
                                <img src="/images/logo.jpg" alt="logo" className="img-responsive" width="30" /> SPBU
                            </a>
                        </div>
                        <div className="sidebar-brand sidebar-brand-sm">
                            <a href={urls.home}>
                                <img src="/images/logo.jpg" alt="logo" className="img-responsive" width="30" />
                            </a>
                        </div>
                        <ul className="sidebar-menu">
                            <li className="menu-header">{menuHeader}</li>
                            {navigation.map((item) => (
                                <SidebarItem key={item.label} item={item} current={current} />
                            ))}
                        </ul>
                    </aside>
                </div>

                <div className="main-content">
                    <section className="section">
                        {flash?.msg && <div dangerouslySetInnerHTML={{ __html: flash.msg }} />}
                        {children}
                    </section>
                </div>

                <footer className="main-footer">
                    <div className="footer-left">
                        SPBU <div className="bullet"></div> {new Date().getFullYear()} Sistem Monitoring
                    </div>
                </footer>
            </div>
        </div>
    );
}
