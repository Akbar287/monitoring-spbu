import { router, useForm, usePage } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import FileDropzone from '../../Components/FileDropzone';

export default function Upload({
    title,
    breadcrumbs = [],
    deleteAction,
    uploadAction,
    templateUrl,
    backUrl,
    filters,
    members = [],
    jamKerja = [],
}) {
    const { urls, flash } = usePage().props;
    const deleteForm = useForm({
        waktu_mulai: filters?.waktu_mulai || '',
        waktu_selesai: filters?.waktu_selesai || '',
        jam_kerja: filters?.jam_kerja || 'all',
        member: filters?.member || 'all',
    });
    const uploadForm = useForm({ file: null });

    const submitDelete = (e) => {
        e.preventDefault();
        if (!window.confirm('Jadwal kerja pada filter terpilih akan dihapus. Lanjutkan?')) return;
        router.delete(deleteAction, { data: deleteForm.data, preserveScroll: true });
    };

    const submitUpload = (e) => {
        e.preventDefault();
        uploadForm.post(uploadAction, { preserveScroll: true, forceFormData: true });
    };

    return (
        <AppLayout title={title}>
            <div className="section-header">
                <h1>{title}</h1>
                <div className="section-header-breadcrumb">
                    <div className="breadcrumb-item">
                        <a href={urls.home}>Dashboard</a>
                    </div>
                    {breadcrumbs.map((b) => (
                        <div key={b.label} className="breadcrumb-item">
                            {b.url ? <a href={b.url}>{b.label}</a> : b.label}
                        </div>
                    ))}
                    <div className="breadcrumb-item">Upload</div>
                </div>
            </div>

            <div className="section-body">
                {flash?.msg && <div dangerouslySetInnerHTML={{ __html: flash.msg }} />}
                <div className="row justify-content-center">
                    <div className="col-md-12">
                        <div className="card">
                            <div className="card-header">
                                <h4>Hapus Data Jadwal</h4>
                            </div>
                            <div className="card-body">
                                <form onSubmit={submitDelete}>
                                    <div className="row">
                                        <div className="col-12 col-md-6 mb-2">
                                            <input
                                                type="date"
                                                className="form-control"
                                                value={deleteForm.data.waktu_mulai}
                                                onChange={(e) => deleteForm.setData('waktu_mulai', e.target.value)}
                                            />
                                        </div>
                                        <div className="col-12 col-md-6 mb-2">
                                            <input
                                                type="date"
                                                className="form-control"
                                                value={deleteForm.data.waktu_selesai}
                                                onChange={(e) => deleteForm.setData('waktu_selesai', e.target.value)}
                                            />
                                        </div>
                                        <div className="col-12 col-md-6 mb-2">
                                            <select
                                                className="custom-select"
                                                value={deleteForm.data.jam_kerja}
                                                disabled={jamKerja.length === 0}
                                                onChange={(e) => deleteForm.setData('jam_kerja', e.target.value)}
                                            >
                                                <option value="all">Semua Jam Kerja</option>
                                                {jamKerja.map((j) => (
                                                    <option key={j.value} value={j.value}>
                                                        {j.label}
                                                    </option>
                                                ))}
                                            </select>
                                        </div>
                                        <div className="col-12 col-md-6 mb-2">
                                            <select
                                                className="custom-select"
                                                value={deleteForm.data.member}
                                                disabled={members.length === 0}
                                                onChange={(e) => deleteForm.setData('member', e.target.value)}
                                            >
                                                <option value="all">Semua Pegawai</option>
                                                {members.map((m) => (
                                                    <option key={m.value} value={m.value}>
                                                        {m.label}
                                                    </option>
                                                ))}
                                            </select>
                                        </div>
                                    </div>
                                    <button type="submit" className="btn btn-danger" disabled={deleteForm.processing}>
                                        <i className="fas fa-eraser mr-1"></i> Hapus Data
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div className="col-md-12">
                        <div className="card">
                            <div className="card-header">
                                <h4>Upload Jadwal Kerja</h4>
                            </div>
                            <div className="card-body">
                                <form onSubmit={submitUpload}>
                                    <div className="row mb-3 justify-content-center">
                                        <label className="col-md-4 col-form-label text-md-right">File</label>
                                        <div className="col-md-6">
                                            <FileDropzone
                                                value={uploadForm.data.file}
                                                onChange={(file) => uploadForm.setData('file', file)}
                                                error={!!uploadForm.errors.file}
                                            />
                                            {uploadForm.errors.file && (
                                                <div className="invalid-feedback d-block">{uploadForm.errors.file}</div>
                                            )}
                                        </div>
                                    </div>
                                    <div className="d-flex flex-wrap align-items-center" style={{ gap: 8 }}>
                                        <a href={backUrl} className="btn btn-primary">
                                            <i className="fas fa-arrow-left mr-1"></i> Kembali
                                        </a>
                                        <a href={templateUrl} target="_blank" rel="noreferrer" className="btn btn-info">
                                            <i className="fas fa-file mr-1"></i> Unduh Template
                                        </a>
                                        <button type="submit" className="btn btn-success" disabled={uploadForm.processing}>
                                            <i className="fas fa-save mr-1"></i> Upload File
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
