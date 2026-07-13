import PublicLayout from '../../Layouts/PublicLayout';

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
        </aside>
    );
}

export default function Show({ title, post, previousUrl }) {
    return (
        <PublicLayout title={title} heroTitle={post.title} heroSubtitle="Detail pengumuman dan informasi resmi SPBU.">
            <div className="portal-grid">
                <article className="article-detail">
                    <a href={previousUrl || '/'} className="back-btn">
                        <i className="fas fa-arrow-left"></i> Kembali
                    </a>
                    <h1>{post.title}</h1>

                    <div className="detail-meta">
                        <span>
                            <i className="far fa-user"></i> Oleh <a href={post.authorUrl}>{post.author}</a>
                        </span>
                        <span><i className="far fa-calendar"></i> {post.date}</span>
                    </div>

                    {post.categories.length > 0 && (
                        <div className="taxonomy">
                            <span className="lbl"><i className="fas fa-folder"></i> Kategori:</span>
                            {post.categories.map((category) => (
                                <a key={category.url} href={category.url} className="chip">
                                    {category.label}
                                </a>
                            ))}
                        </div>
                    )}

                    {post.tags.length > 0 && (
                        <div className="taxonomy">
                            <span className="lbl"><i className="fas fa-hashtag"></i> Tag:</span>
                            {post.tags.map((tag) => (
                                <a key={tag.url} href={tag.url} className="chip">
                                    #{tag.label}
                                </a>
                            ))}
                        </div>
                    )}

                    <div className="content">{post.content}</div>
                </article>
                <Sidebar />
            </div>
        </PublicLayout>
    );
}
