import PublicLayout from '../../Layouts/PublicLayout';

const palette = ['#6777ef', '#fc544b', '#ffa426', '#47c363', '#3abaf4', '#9b59b6'];

function Sidebar() {
    return (
        <aside>
            <div className="widget">
                <h5><i className="fas fa-lock"></i> Login ke Aplikasi</h5>
                <p>Akses dashboard monitoring SPBU menggunakan Username dan Password Anda.</p>
                <a href="/login" className="portal-login">
                    <i className="fas fa-sign-in-alt"></i> Masuk Sekarang
                </a>
            </div>
            <div className="widget">
                <h5><i className="fas fa-compass"></i> Akses Cepat</h5>
                <ul className="tip-list">
                    <li><i className="fas fa-newspaper"></i> Informasi operasional terbaru.</li>
                    <li><i className="fas fa-shield-alt"></i> Pengumuman resmi manajemen.</li>
                    <li><i className="fas fa-gas-pump"></i> Update aktivitas SPBU.</li>
                </ul>
            </div>
            <div className="widget">
                <h5><i className="fas fa-circle-info"></i> Tentang Portal</h5>
                <ul className="tip-list">
                    <li><i className="fas fa-bullhorn"></i> Pengumuman resmi SPBU.</li>
                    <li><i className="fas fa-tags"></i> Telusuri artikel berdasarkan kategori dan tag.</li>
                    <li><i className="fas fa-clock"></i> Selalu diperbarui dengan informasi terbaru.</li>
                </ul>
            </div>
        </aside>
    );
}

function FeaturedArticle({ article }) {
    if (!article) return null;

    return (
        <article className="featured-article">
            <div className="featured-copy">
                <span className="featured-label">
                    <i className="fas fa-star"></i> Sorotan Utama
                </span>
                <h2>{article.title}</h2>
                <div className="art-meta">
                    <span><i className="far fa-user"></i> {article.author}</span>
                    <span><i className="far fa-calendar"></i> {article.date}</span>
                </div>
                <p>{article.excerpt}</p>
                <a href={article.url} className="portal-cta">
                    Baca Sorotan <i className="fas fa-arrow-right"></i>
                </a>
            </div>
            <div className="featured-visual" aria-hidden="true">
                <i className="fas fa-newspaper"></i>
            </div>
        </article>
    );
}

function ArticleCard({ article, index }) {
    const accent = palette[index % palette.length];

    return (
        <article className="art-card">
            <div className="art-ico" style={{ background: `linear-gradient(135deg, ${accent}, ${accent}cc)` }}>
                <i className="fas fa-newspaper"></i>
            </div>
            <h3 className="art-title">{article.title}</h3>
            <div className="art-meta">
                <span><i className="far fa-user"></i> {article.author}</span>
                <span><i className="far fa-calendar"></i> {article.date}</span>
            </div>
            <p className="art-excerpt">{article.excerpt}</p>
            <a href={article.url} className="art-read">
                Baca selengkapnya <i className="fas fa-arrow-right"></i>
            </a>
        </article>
    );
}

function Pagination({ links = [] }) {
    if (links.length <= 3) return null;
    return (
        <div className="pagination">
            {links.map((link, index) =>
                link.url ? (
                    <a key={index} href={link.url} className={link.active ? 'active' : ''} dangerouslySetInnerHTML={{ __html: link.label }} />
                ) : (
                    <span key={index} className={link.active ? 'active' : ''} dangerouslySetInnerHTML={{ __html: link.label }} />
                )
            )}
        </div>
    );
}

export default function Index({ title, heroTitle, heroSubtitle, heading, articles, pagination }) {
    const featured = articles[0];
    const rest = articles.slice(1);

    return (
        <PublicLayout title={title} heroTitle={heroTitle} heroSubtitle={heroSubtitle}>
            <div id="artikel" className="portal-grid">
                <section>
                    {heading && (
                        <div className="portal-heading">
                            <div className="ph-ico"><i className={`fas ${heading.icon}`}></i></div>
                            <div>
                                <div className="ph-label">{heading.label}</div>
                                <div className="ph-value">{heading.value}</div>
                                {heading.detail && <p className="ph-detail">{heading.detail}</p>}
                            </div>
                        </div>
                    )}

                    {articles.length > 0 ? (
                        <>
                            <FeaturedArticle article={featured} />
                            <div className="section-panel portal-section-title">
                                <div>
                                    <span>Berita & Artikel</span>
                                    <h2>Informasi Terbaru</h2>
                                </div>
                                <i className="fas fa-bolt"></i>
                            </div>
                            <div className="article-grid">
                                {(rest.length ? rest : articles).map((article, index) => (
                                    <ArticleCard key={article.id} article={article} index={index} />
                                ))}
                            </div>
                            <Pagination links={pagination?.links || []} />
                        </>
                    ) : (
                        <div className="empty-state">
                            <i className="far fa-folder-open"></i>
                            <p>Belum ada artikel yang tersedia saat ini.</p>
                        </div>
                    )}
                </section>
                <Sidebar />
            </div>
        </PublicLayout>
    );
}
