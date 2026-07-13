import { useEffect, useState } from 'react';
import { Head, router, usePage } from '@inertiajs/react';
import { isInertiaUrl, localUrl } from '../lib/inertiaRoutes';

export default function PublicLayout({ title, heroTitle, heroSubtitle, children }) {
    const { spa } = usePage().props;
    const spaRoutes = spa?.routes || [];
    const [theme, setTheme] = useState(() =>
        typeof document !== 'undefined' ? document.documentElement.dataset.theme || 'light' : 'light'
    );

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

    const toggleTheme = () => {
        if (window.spbuTheme) {
            setTheme(window.spbuTheme.toggle());
        }
    };

    return (
        <>
            <Head title={title} />
            <style dangerouslySetInnerHTML={{ __html: CSS }} />
            <div className="portal">
                <header className="portal-hero">
                    <nav className="portal-nav">
                        <a href="/" className="portal-brand">
                            <img src="/images/logo.jpg" alt="SPBU" />
                            <span>SPBU</span>
                        </a>
                        <div className="portal-nav-actions">
                            <button
                                type="button"
                                className="portal-theme-toggle"
                                onClick={toggleTheme}
                                title={theme === 'dark' ? 'Gunakan mode terang' : 'Gunakan mode gelap'}
                                aria-label={theme === 'dark' ? 'Gunakan mode terang' : 'Gunakan mode gelap'}
                            >
                                <i className={`fas fa-${theme === 'dark' ? 'sun' : 'moon'}`}></i>
                            </button>
                            <a href="/login" className="portal-login">
                                <i className="fas fa-sign-in-alt"></i> Login
                            </a>
                        </div>
                    </nav>
                    <div className="portal-hero-inner">
                        <div className="portal-copy">
                            <span className="portal-kicker">
                                <i className="fas fa-broadcast-tower"></i> Portal Informasi Operasional
                            </span>
                            <h1>{heroTitle || 'Portal Informasi SPBU'}</h1>
                            <p>{heroSubtitle || 'Pengumuman dan informasi resmi sistem monitoring SPBU.'}</p>
                            <div className="portal-actions">
                                <a href="#artikel" className="portal-cta">
                                    <i className="fas fa-newspaper"></i> Lihat Artikel
                                </a>
                                <a href="/login" className="portal-ghost">
                                    <i className="fas fa-gauge-high"></i> Dashboard
                                </a>
                            </div>
                        </div>
                        <div className="portal-visual" aria-hidden="true">
                            <div className="portal-logo-orbit">
                                <img src="/images/logo.jpg" alt="" />
                            </div>
                            <div className="portal-mini-card card-a">
                                <i className="fas fa-gas-pump"></i>
                                <span>Stok BBM</span>
                                <strong>Monitoring</strong>
                            </div>
                            <div className="portal-mini-card card-b">
                                <i className="fas fa-chart-line"></i>
                                <span>Laporan</span>
                                <strong>Real-time</strong>
                            </div>
                        </div>
                    </div>
                </header>
                <main className="portal-main">{children}</main>
            </div>
        </>
    );
}

const CSS = `
:root {
    --portal-ink: #121936;
    --portal-muted: #667085;
    --portal-copy: #475467;
    --portal-accent: #4f46e5;
    --portal-accent-strong: #5b21b6;
    --portal-glass: rgba(255,255,255,.56);
    --portal-glass-strong: rgba(255,255,255,.78);
    --portal-border: rgba(255,255,255,.72);
    --portal-control-bg: rgba(255,255,255,.46);
    --portal-control-border: rgba(255,255,255,.68);
    --portal-control-hover: rgba(255,255,255,.62);
    --portal-inset: inset 0 1px 0 rgba(255,255,255,.72);
    --portal-shadow: 0 24px 70px rgba(31,36,64,.16);
    --portal-bg:
        radial-gradient(900px 560px at 9% 0%, rgba(255,120,142,.34), transparent 68%),
        radial-gradient(820px 560px at 88% 8%, rgba(103,119,239,.30), transparent 70%),
        radial-gradient(680px 500px at 18% 90%, rgba(255,196,87,.28), transparent 72%),
        linear-gradient(135deg,#fff4fb 0%,#eef4ff 58%,#fff7e8 100%);
    --portal-visual-bg:
        linear-gradient(135deg, rgba(255,255,255,.58), rgba(255,255,255,.20)),
        radial-gradient(420px 260px at 20% 20%, rgba(255,120,142,.42), transparent 62%),
        radial-gradient(400px 280px at 90% 84%, rgba(255,196,87,.38), transparent 64%);
    --portal-soft-glow: rgba(103,119,239,.16);
    --portal-empty-bg: rgba(255,255,255,.58);
    --portal-pagination-bg: rgba(255,255,255,.56);
}
html[data-theme=dark] {
    --portal-ink: #eef2ff;
    --portal-muted: #a8b1cf;
    --portal-copy: #cbd5e1;
    --portal-accent: #a5b4fc;
    --portal-accent-strong: #c4b5fd;
    --portal-glass: rgba(15,23,42,.58);
    --portal-glass-strong: rgba(15,23,42,.76);
    --portal-border: rgba(148,163,184,.26);
    --portal-control-bg: rgba(15,23,42,.54);
    --portal-control-border: rgba(148,163,184,.28);
    --portal-control-hover: rgba(30,41,59,.72);
    --portal-inset: inset 0 1px 0 rgba(255,255,255,.12);
    --portal-shadow: 0 28px 82px rgba(0,0,0,.42);
    --portal-bg:
        radial-gradient(900px 560px at 9% 0%, rgba(255,96,128,.18), transparent 68%),
        radial-gradient(820px 560px at 88% 8%, rgba(103,119,239,.24), transparent 70%),
        radial-gradient(680px 500px at 18% 90%, rgba(34,197,94,.11), transparent 72%),
        linear-gradient(135deg,#090d1d 0%,#10162d 54%,#16122a 100%);
    --portal-visual-bg:
        linear-gradient(135deg, rgba(15,23,42,.70), rgba(15,23,42,.30)),
        radial-gradient(420px 260px at 20% 20%, rgba(255,120,142,.18), transparent 62%),
        radial-gradient(400px 280px at 90% 84%, rgba(103,119,239,.26), transparent 64%);
    --portal-soft-glow: rgba(165,180,252,.16);
    --portal-empty-bg: rgba(15,23,42,.52);
    --portal-pagination-bg: rgba(15,23,42,.58);
}
body {
    margin: 0;
    background: var(--portal-bg);
    background-attachment: fixed;
    font-family: "Plus Jakarta Sans", system-ui, sans-serif;
    color: var(--portal-ink);
}
.portal { min-height: 100vh; overflow-x: hidden; }
.portal-hero {
    position: relative;
    color: var(--portal-ink);
    padding: 18px 20px 92px;
    overflow: hidden;
}
.portal-hero:before,
.portal-hero:after {
    content: "";
    position: absolute;
    border-radius: 999px;
    pointer-events: none;
    filter: blur(16px);
    opacity: .9;
    animation: portal-drift 16s ease-in-out infinite alternate;
}
.portal-hero:before {
    width: 420px;
    height: 420px;
    left: -120px;
    top: 80px;
    background: radial-gradient(circle, rgba(255,77,109,.42), transparent 64%);
}
.portal-hero:after {
    width: 480px;
    height: 480px;
    right: -160px;
    bottom: -120px;
    background: radial-gradient(circle, rgba(103,119,239,.34), transparent 64%);
    animation-delay: -6s;
}
@keyframes portal-drift {
    from { transform: translate3d(0,0,0) scale(1); }
    to { transform: translate3d(46px,-24px,0) scale(1.08); }
}
.portal-nav {
    position: relative;
    z-index: 3;
    max-width: 1120px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px;
    border-radius: 18px;
    background: var(--portal-control-bg);
    border: 1px solid var(--portal-control-border);
    box-shadow: 0 14px 38px rgba(31,36,64,.10), var(--portal-inset);
    backdrop-filter: blur(22px) saturate(1.25);
    -webkit-backdrop-filter: blur(22px) saturate(1.25);
    animation: portal-rise .65s cubic-bezier(.22,1,.36,1) both;
}
.portal-nav-actions {
    display: inline-flex;
    align-items: center;
    gap: 10px;
}
.portal-brand {
    color: var(--portal-ink);
    display: inline-flex;
    align-items: center;
    gap: 10px;
    font-weight: 800;
    text-decoration: none;
}
.portal-brand img {
    width: 38px;
    height: 38px;
    object-fit: cover;
    border-radius: 11px;
    box-shadow: 0 8px 20px rgba(31,36,64,.16);
}
.portal-theme-toggle {
    width: 42px;
    height: 42px;
    border-radius: 999px;
    border: 1px solid var(--portal-control-border);
    background: var(--portal-control-bg);
    color: var(--portal-ink);
    display: grid;
    place-items: center;
    cursor: pointer;
    box-shadow: var(--portal-inset);
    transition: transform .18s ease, background .18s ease, color .18s ease;
}
.portal-theme-toggle:hover {
    transform: translateY(-2px);
    background: var(--portal-control-hover);
}
.portal-login,
.portal-cta,
.portal-ghost {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    border-radius: 999px;
    padding: 10px 15px;
    font-weight: 800;
    text-decoration: none;
    transition: transform .18s ease, box-shadow .18s ease, background .18s ease;
}
.portal-login,
.portal-cta {
    color: #fff;
    background: linear-gradient(135deg,#6777ef,#8b4ff0);
    box-shadow: 0 12px 28px rgba(103,119,239,.32);
}
.portal-login:hover,
.portal-cta:hover,
.portal-ghost:hover {
    transform: translateY(-2px);
}
.portal-hero-inner {
    position: relative;
    z-index: 2;
    max-width: 1120px;
    margin: 66px auto 0;
    display: grid;
    grid-template-columns: minmax(0, 1.1fr) 380px;
    gap: 36px;
    align-items: center;
}
.portal-copy { animation: portal-rise .75s cubic-bezier(.22,1,.36,1) .08s both; }
.portal-kicker {
    display: inline-flex;
    align-items: center;
    gap: 9px;
    color: var(--portal-accent-strong);
    background: var(--portal-control-bg);
    border: 1px solid var(--portal-control-border);
    border-radius: 999px;
    padding: 8px 12px;
    font-size: 13px;
    font-weight: 800;
    backdrop-filter: blur(18px);
}
.portal-hero h1 {
    max-width: 760px;
    margin: 18px 0 12px;
    font-size: clamp(36px, 6vw, 72px);
    line-height: .98;
    letter-spacing: 0;
}
.portal-hero p {
    max-width: 680px;
    margin: 0;
    color: var(--portal-copy);
    font-size: 18px;
    line-height: 1.7;
}
.portal-actions { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 26px; }
.portal-ghost {
    color: var(--portal-ink);
    background: var(--portal-control-bg);
    border: 1px solid var(--portal-control-border);
    box-shadow: var(--portal-inset);
}
.portal-visual {
    min-height: 340px;
    position: relative;
    border-radius: 34px;
    background: var(--portal-visual-bg);
    border: 1px solid var(--portal-border);
    box-shadow: var(--portal-shadow), var(--portal-inset);
    backdrop-filter: blur(30px) saturate(1.35);
    -webkit-backdrop-filter: blur(30px) saturate(1.35);
    animation: portal-rise .75s cubic-bezier(.22,1,.36,1) .18s both, portal-float 7s ease-in-out 1s infinite;
}
.portal-logo-orbit {
    position: absolute;
    inset: 50% auto auto 50%;
    width: 150px;
    height: 150px;
    transform: translate(-50%, -50%);
    border-radius: 34px;
    display: grid;
    place-items: center;
    background: var(--portal-control-bg);
    border: 1px solid var(--portal-control-border);
    box-shadow: 0 18px 44px rgba(31,36,64,.18);
}
.portal-logo-orbit img {
    width: 106px;
    height: 106px;
    border-radius: 26px;
    object-fit: cover;
}
.portal-mini-card {
    position: absolute;
    display: grid;
    gap: 2px;
    min-width: 142px;
    padding: 14px;
    border-radius: 18px;
    background: var(--portal-glass-strong);
    border: 1px solid var(--portal-border);
    box-shadow: 0 14px 38px rgba(31,36,64,.12);
    backdrop-filter: blur(20px);
}
.portal-mini-card i { color: var(--portal-accent); font-size: 18px; }
.portal-mini-card span { color: var(--portal-muted); font-size: 12px; font-weight: 700; }
.portal-mini-card strong { color: var(--portal-ink); font-size: 17px; }
.card-a { left: -18px; top: 42px; animation: portal-bob 5.8s ease-in-out infinite; }
.card-b { right: -14px; bottom: 44px; animation: portal-bob 6.4s ease-in-out -2s infinite; }
@keyframes portal-rise { from { opacity: 0; transform: translateY(18px); } to { opacity: 1; transform: none; } }
@keyframes portal-float { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
@keyframes portal-bob { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-8px); } }
.portal-main {
    position: relative;
    z-index: 4;
    max-width: 1120px;
    margin: -54px auto 56px;
    padding: 0 20px;
}
.portal-grid { display: grid; grid-template-columns: minmax(0, 1fr) 320px; gap: 22px; align-items: start; }
.article-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px; }
.art-card, .widget, .article-detail, .portal-heading, .featured-article, .section-panel {
    background: var(--portal-glass);
    border: 1px solid var(--portal-border);
    border-radius: 20px;
    padding: 20px;
    box-shadow: var(--portal-shadow), var(--portal-inset);
    backdrop-filter: blur(24px) saturate(1.25);
    -webkit-backdrop-filter: blur(24px) saturate(1.25);
    animation: portal-rise .58s cubic-bezier(.22,1,.36,1) both;
}
.art-card {
    position: relative;
    overflow: hidden;
    transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
}
.art-card:after {
    content: "";
    position: absolute;
    inset: auto -30px -70px auto;
    width: 160px;
    height: 160px;
    border-radius: 999px;
    background: radial-gradient(circle, var(--portal-soft-glow), transparent 68%);
}
.art-card:hover {
    transform: translateY(-5px);
    border-color: var(--portal-control-border);
    box-shadow: var(--portal-shadow), var(--portal-inset);
}
.art-ico { width: 46px; height: 46px; border-radius: 14px; display: grid; place-items: center; color: #fff; margin-bottom: 14px; box-shadow: 0 12px 30px rgba(31,36,64,.16); }
.art-title { position: relative; z-index: 1; font-size: 18px; line-height: 1.35; margin: 0 0 10px; }
.art-meta, .detail-meta { display: flex; flex-wrap: wrap; gap: 12px; color: var(--portal-muted); font-size: 13px; margin-bottom: 12px; }
.art-meta a, .detail-meta a { color: var(--portal-accent); }
.art-excerpt { position: relative; z-index: 1; color: var(--portal-copy); min-height: 58px; }
.art-read, .back-btn, .chip { color: var(--portal-accent); font-weight: 800; text-decoration: none; }
.widget { margin-bottom: 18px; }
.widget h5 { margin: 0 0 10px; font-size: 16px; }
.widget p, .tip-list { color: var(--portal-copy); }
.tip-list { padding-left: 0; list-style: none; display: grid; gap: 10px; }
.tip-list li { display: flex; gap: 10px; align-items: flex-start; }
.tip-list i { color: var(--portal-accent); margin-top: 3px; }
.portal-heading { display: flex; gap: 14px; margin-bottom: 18px; }
.ph-ico { width: 42px; height: 42px; border-radius: 14px; background: var(--portal-control-bg); color: var(--portal-accent); display: grid; place-items: center; }
.ph-label { color: var(--portal-muted); font-size: 13px; }
.ph-value { font-size: 22px; font-weight: 800; }
.featured-article {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 190px;
    gap: 22px;
    align-items: center;
    margin-bottom: 18px;
    overflow: hidden;
}
.featured-copy { position: relative; z-index: 1; }
.featured-label {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: var(--portal-accent-strong);
    background: var(--portal-control-bg);
    border: 1px solid var(--portal-control-border);
    border-radius: 999px;
    padding: 7px 11px;
    font-size: 12px;
    font-weight: 800;
}
.featured-article h2 {
    margin: 14px 0 10px;
    font-size: clamp(26px, 4vw, 42px);
    line-height: 1.08;
}
.featured-article p {
    color: var(--portal-copy);
    font-size: 16px;
    line-height: 1.7;
    margin: 0 0 18px;
}
.featured-visual {
    min-height: 190px;
    border-radius: 28px;
    display: grid;
    place-items: center;
    color: #fff;
    font-size: 58px;
    background:
        radial-gradient(150px 120px at 28% 22%, rgba(255,255,255,.42), transparent 70%),
        linear-gradient(135deg,#6777ef,#8b4ff0 54%,#ff7a90);
    box-shadow: 0 22px 54px rgba(103,119,239,.30);
    animation: portal-float 7s ease-in-out infinite;
}
.portal-section-title {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    margin-bottom: 18px;
    padding: 16px 18px;
}
.portal-section-title span {
    color: var(--portal-muted);
    font-size: 12px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .06em;
}
.portal-section-title h2 {
    margin: 2px 0 0;
    font-size: 24px;
}
.portal-section-title>i {
    width: 42px;
    height: 42px;
    border-radius: 14px;
    display: grid;
    place-items: center;
    color: #fff;
    background: linear-gradient(135deg,#ffa426,#fc544b);
}
.article-detail h1 { margin: 12px 0; font-size: clamp(30px, 4vw, 48px); line-height: 1.08; }
.taxonomy { display: flex; flex-wrap: wrap; gap: 8px; margin: 12px 0; align-items: center; }
.taxonomy .lbl { color: var(--portal-muted); font-weight: 800; }
.chip { background: var(--portal-control-bg); border: 1px solid var(--portal-control-border); border-radius: 999px; padding: 6px 10px; }
.content { margin-top: 22px; white-space: pre-wrap; line-height: 1.8; color: var(--portal-copy); }
.empty-state { background: var(--portal-empty-bg); border: 1px dashed var(--portal-control-border); border-radius: 20px; padding: 32px; color: var(--portal-muted); text-align: center; backdrop-filter: blur(18px); }
.pagination { display: flex; flex-wrap: wrap; gap: 6px; justify-content: center; margin-top: 18px; }
.pagination a, .pagination span { padding: 8px 11px; border-radius: 999px; background: var(--portal-pagination-bg); border: 1px solid var(--portal-control-border); text-decoration: none; color: var(--portal-ink); backdrop-filter: blur(14px); }
.pagination .active { background: #4f46e5; color: #fff; }
@media (max-width: 900px) {
    .portal-hero-inner { grid-template-columns: 1fr; margin-top: 42px; }
    .portal-visual { min-height: 260px; }
    .portal-grid { grid-template-columns: 1fr; }
    .article-grid { grid-template-columns: 1fr; }
    .featured-article { grid-template-columns: 1fr; }
    .featured-visual { min-height: 150px; }
    .portal-main { margin-top: -38px; }
}
@media (prefers-reduced-motion: reduce) {
    .portal-nav,.portal-copy,.portal-visual,.art-card,.widget,.article-detail,.portal-heading,.featured-article,.section-panel,
    .portal-hero:before,.portal-hero:after,.card-a,.card-b { animation: none !important; transition: none !important; }
}
`;
