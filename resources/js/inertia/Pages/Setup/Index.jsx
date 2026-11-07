import { router } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';

// Halaman pengisian awal sistem.
// Props dari SetupController: langkah[], kemajuan, berikutnya, sudahSelesai,
// tanggalMulaiOperasi, urls.
//
// Urutan langkah bukan sekadar tampilan — langkah yang prasyaratnya belum
// beres dikirim dengan `terkunci: true` oleh App\Support\Setup dan di sini
// tautannya dimatikan, supaya admin tidak mengisi data yang menggantung.

function Ikhtisar({ kemajuan, sudahSelesai, tanggalMulaiOperasi }) {
    const { persen, wajib_selesai: wajibSelesai, wajib, selesai, total } = kemajuan;

    return (
        <div className="card">
            <div className="card-body">
                <div className="d-flex flex-wrap align-items-center justify-content-between mb-3">
                    <div>
                        <h4 className="mb-1">
                            {sudahSelesai ? 'Set up sudah dituntaskan' : 'Kemajuan pengisian awal'}
                        </h4>
                        <p className="text-muted mb-0">
                            {wajibSelesai} dari {wajib} langkah wajib selesai
                            {selesai > wajibSelesai && ` · ${selesai} dari ${total} langkah termasuk yang opsional`}
                            {tanggalMulaiOperasi && ` · mulai operasi ${tanggalMulaiOperasi}`}
                        </p>
                    </div>
                    <div className="display-4 font-weight-bold text-primary">{persen}%</div>
                </div>

                <div className="progress" style={{ height: 10 }}>
                    <div
                        className={`progress-bar ${persen === 100 ? 'bg-success' : 'bg-primary'}`}
                        role="progressbar"
                        style={{ width: `${persen}%` }}
                        aria-valuenow={persen}
                        aria-valuemin={0}
                        aria-valuemax={100}
                    />
                </div>
            </div>
        </div>
    );
}

function Rincian({ rincian }) {
    if (!rincian.length) return null;

    return (
        <div className="d-flex flex-wrap mt-3" style={{ gap: '0.5rem' }}>
            {rincian.map((r) => (
                <a
                    key={r.label}
                    href={r.url}
                    className={`badge badge-${r.jumlah > 0 ? 'success' : 'secondary'} p-2`}
                    style={{ fontSize: '0.8rem', fontWeight: 500 }}
                >
                    {r.jumlah > 0 && <i className="fas fa-check mr-1" />}
                    {r.label}
                    <span className="ml-1">({r.jumlah})</span>
                </a>
            ))}
        </div>
    );
}

function Langkah({ langkah, nomor, adalahBerikutnya }) {
    const { selesai, terkunci, wajib, berantai } = langkah;

    const warnaTepi = selesai ? '#47c363' : terkunci ? '#e3eaef' : adalahBerikutnya ? '#6777ef' : '#ffa426';

    return (
        <div className="card mb-3" style={{ borderLeft: `4px solid ${warnaTepi}` }}>
            <div className="card-body">
                <div className="d-flex">
                    <div
                        className="mr-3 d-flex align-items-center justify-content-center flex-shrink-0"
                        style={{
                            width: 46,
                            height: 46,
                            borderRadius: 12,
                            background: selesai ? '#47c363' : terkunci ? '#e3eaef' : '#6777ef',
                            color: terkunci ? '#98a6ad' : '#fff',
                        }}
                    >
                        <i className={`fas ${selesai ? 'fa-check' : terkunci ? 'fa-lock' : langkah.ikon}`} />
                    </div>

                    <div className="flex-grow-1">
                        <div className="d-flex flex-wrap align-items-center" style={{ gap: '0.4rem' }}>
                            <h5 className="mb-0 mr-1">
                                {nomor}. {langkah.judul}
                            </h5>
                            {!wajib && <span className="badge badge-light">opsional</span>}
                            {berantai && <span className="badge badge-info">isi berurutan</span>}
                            {adalahBerikutnya && !selesai && <span className="badge badge-primary">lanjutkan di sini</span>}
                        </div>

                        <p className="text-muted mb-0 mt-1">{langkah.deskripsi}</p>

                        {terkunci && (
                            <p className="text-muted mb-0 mt-2">
                                <i className="fas fa-lock mr-1" />
                                Selesaikan dahulu: {langkah.penghalang.join(', ').replace(/_/g, ' ')}
                            </p>
                        )}

                        <Rincian rincian={langkah.rincian} />

                        <div className="mt-3 d-flex flex-wrap align-items-center" style={{ gap: '0.5rem' }}>
                            {terkunci ? (
                                <button type="button" className="btn btn-light" disabled>
                                    Terkunci
                                </button>
                            ) : (
                                <a href={langkah.url} className={`btn btn-${selesai ? 'outline-primary' : 'primary'}`}>
                                    {selesai ? 'Periksa kembali' : 'Isi sekarang'}
                                    <i className="fas fa-arrow-right ml-1" />
                                </a>
                            )}

                            {langkah.kelola && langkah.kelola !== langkah.url && !terkunci && (
                                <a href={langkah.kelola} className="btn btn-light">
                                    Kelola daftar
                                </a>
                            )}

                            <span className="text-muted">
                                {langkah.jumlah} {langkah.satuan} tercatat
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}

export default function Index({
    title,
    langkah = [],
    kemajuan,
    berikutnya,
    sudahSelesai,
    tanggalMulaiOperasi,
    urls,
}) {
    const selesaikan = () => {
        if (!kemajuan.bisa_diselesaikan) return;
        router.post(urls.selesaikan);
    };

    return (
        <AppLayout title={title}>
            <div className="section-header">
                <h1>{title}</h1>
            </div>

            <div className="row">
                <div className="col-12">
                    <p className="text-muted">
                        Halaman ini mengurutkan seluruh data pondasi yang harus ada sebelum aplikasi dipakai
                        mencatat transaksi. Tiap langkah membuka langkah berikutnya, jadi kerjakan dari atas.
                        Pengisiannya memakai form asli tiap modul, sehingga jurnal dan pergerakan stok terbentuk
                        sama persis seperti operasi normal.
                    </p>
                </div>

                <div className="col-12 mb-2">
                    <Ikhtisar
                        kemajuan={kemajuan}
                        sudahSelesai={sudahSelesai}
                        tanggalMulaiOperasi={tanggalMulaiOperasi}
                    />
                </div>

                <div className="col-12">
                    {langkah.map((l, i) => (
                        <Langkah
                            key={l.kunci}
                            langkah={l}
                            nomor={i + 1}
                            adalahBerikutnya={berikutnya?.kunci === l.kunci}
                        />
                    ))}
                </div>

                <div className="col-12">
                    <div className="card">
                        <div className="card-body d-flex flex-wrap align-items-center justify-content-between">
                            <div>
                                <h5 className="mb-1">
                                    {sudahSelesai ? 'Set up sudah ditutup' : 'Tutup set up'}
                                </h5>
                                <p className="text-muted mb-0">
                                    {sudahSelesai
                                        ? 'Panduan ini tidak lagi menghalangi menu lain. Buka kembali bila ada SPBU baru yang perlu disiapkan.'
                                        : kemajuan.bisa_diselesaikan
                                          ? 'Semua langkah wajib sudah lengkap. Aplikasi siap dipakai mencatat transaksi.'
                                          : `Masih ada ${kemajuan.wajib - kemajuan.wajib_selesai} langkah wajib yang belum lengkap.`}
                                </p>
                            </div>

                            {sudahSelesai ? (
                                <div className="d-flex" style={{ gap: '0.5rem' }}>
                                    <button
                                        type="button"
                                        className="btn btn-light"
                                        onClick={() => router.post(urls.bukaKembali)}
                                    >
                                        Buka kembali panduan
                                    </button>
                                    <a href={urls.home} className="btn btn-primary">
                                        Ke dashboard
                                    </a>
                                </div>
                            ) : (
                                <button
                                    type="button"
                                    className="btn btn-success"
                                    disabled={!kemajuan.bisa_diselesaikan}
                                    onClick={selesaikan}
                                >
                                    <i className="fas fa-flag-checkered mr-1" />
                                    Selesaikan set up
                                </button>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
