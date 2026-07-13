import { useEffect, useRef, useState } from 'react';
import { Link, useForm, usePage } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';

// Halaman lihat profil (port dari auth/profil.blade.php ke Inertia).
export default function Index({ title, avatar, role, fields, editUrl, passwordUrl, uploadAvatarUrl, homeUrl }) {
    const { urls, flash } = usePage().props;
    const fileInputRef = useRef(null);
    const uploadForm = useForm({ avatar: null });
    const [previewUrl, setPreviewUrl] = useState(null);
    const [previewName, setPreviewName] = useState('');
    const [dialogOpen, setDialogOpen] = useState(false);

    useEffect(() => {
        if (!previewUrl) return undefined;
        return () => URL.revokeObjectURL(previewUrl);
    }, [previewUrl]);

    useEffect(() => {
        if (!dialogOpen) return undefined;
        const onKeyDown = (e) => {
            if (e.key === 'Escape' && !uploadForm.processing) {
                cancelPreview();
            }
        };
        window.addEventListener('keydown', onKeyDown);
        return () => window.removeEventListener('keydown', onKeyDown);
    }, [dialogOpen, uploadForm.processing]);

    const openFilePicker = () => {
        fileInputRef.current?.click();
    };

    const selectAvatar = (e) => {
        const file = e.target.files?.[0];
        if (!file) return;

        uploadForm.clearErrors('avatar');
        uploadForm.setData('avatar', file);
        setPreviewName(file.name);
        setPreviewUrl(URL.createObjectURL(file));
        setDialogOpen(true);
        e.target.value = '';
    };

    const cancelPreview = () => {
        if (uploadForm.processing) return;

        setDialogOpen(false);
        setPreviewUrl(null);
        setPreviewName('');
        uploadForm.setData('avatar', null);
    };

    const submitAvatar = (e) => {
        e.preventDefault();
        uploadForm.post(uploadAvatarUrl, {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: () => {
                setDialogOpen(false);
                setPreviewUrl(null);
                setPreviewName('');
                uploadForm.reset('avatar');
            },
        });
    };

    return (
        <AppLayout title={title}>
            <style dangerouslySetInnerHTML={{ __html: PROFILE_CSS }} />
            <div className="section-header">
                <h1>Profil</h1>
                <div className="section-header-breadcrumb">
                    <div className="breadcrumb-item">
                        <a href={urls.home}>Dashboard</a>
                    </div>
                    <div className="breadcrumb-item">Profil</div>
                </div>
            </div>

            <div className="section-body">
                <p className="text-muted mb-3">
                    Divisi <b>{role.divisi || '-'}</b> &middot; Level <b>{role.level || '-'}</b> &middot; Jabatan <b>{role.jabatan || '-'}</b>
                </p>
                <div className="row justify-content-center">
                    <div className="col-12">
                        <div className="card">
                            <div className="card-header">
                                <h4>Profil</h4>
                            </div>
                            <div className="card-body">
                                {flash?.msg && <div dangerouslySetInnerHTML={{ __html: flash.msg }} />}
                                <div className="row">
                                    <div className="col-12 col-md-4 text-center mb-4 mb-md-0">
                                        <div className="profile-avatar-panel">
                                            <img
                                                className="profile-avatar-image"
                                                src={avatar}
                                                alt="avatar"
                                            />
                                            <input
                                                ref={fileInputRef}
                                                type="file"
                                                className="d-none"
                                                accept="image/jpeg,image/png,image/webp,image/bmp"
                                                onChange={selectAvatar}
                                            />
                                            <button type="button" className="btn btn-primary mt-3" onClick={openFilePicker}>
                                                <i className="fas fa-camera mr-1"></i> Upload Foto
                                            </button>
                                            {uploadForm.errors.avatar && (
                                                <div className="invalid-feedback d-block mt-2">{uploadForm.errors.avatar}</div>
                                            )}
                                        </div>
                                    </div>
                                    <div className="col-12 col-md-8">
                                        <div className="row">
                                            {fields.map((f) => (
                                                <div className="col-12 col-sm-6 form-group" key={f.label}>
                                                    <label className="text-muted small">{f.label}</label>
                                                    <input type="text" readOnly className="form-control" value={f.value ?? ''} />
                                                </div>
                                            ))}
                                        </div>
                                    </div>
                                </div>

                                <div className="d-flex flex-wrap mt-3" style={{ gap: 8 }}>
                                    <Link href={homeUrl} className="btn btn-primary">
                                        <i className="fas fa-arrow-left mr-1"></i> Kembali
                                    </Link>
                                    <Link href={passwordUrl} className="btn btn-info">
                                        <i className="fas fa-lock mr-1"></i> Ubah Password
                                    </Link>
                                    <Link href={editUrl} className="btn btn-success">
                                        <i className="fas fa-pen mr-1"></i> Ubah
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {dialogOpen && (
                <div
                    className="profile-upload-backdrop"
                    onMouseDown={(e) => {
                        if (e.target === e.currentTarget) cancelPreview();
                    }}
                >
                    <form
                        data-no-inertia
                        className="profile-upload-dialog"
                        role="dialog"
                        aria-modal="true"
                        aria-labelledby="profile-upload-title"
                        onSubmit={submitAvatar}
                    >
                        <div className="profile-upload-head">
                            <div>
                                <h5 id="profile-upload-title">Preview Foto Profil</h5>
                                <p>{previewName || 'Foto yang dipilih'}</p>
                            </div>
                            <button type="button" className="profile-upload-close" onClick={cancelPreview} aria-label="Batalkan">
                                <i className="fas fa-times"></i>
                            </button>
                        </div>
                        <div className="profile-upload-preview">
                            {previewUrl && <img src={previewUrl} alt="Preview foto profil" />}
                        </div>
                        {uploadForm.errors.avatar && (
                            <div className="alert alert-danger mb-0">{uploadForm.errors.avatar}</div>
                        )}
                        <div className="profile-upload-actions">
                            <button type="button" className="btn btn-light" onClick={cancelPreview} disabled={uploadForm.processing}>
                                Batalkan
                            </button>
                            <button type="submit" className="btn btn-success" disabled={uploadForm.processing}>
                                {uploadForm.processing ? (
                                    <>
                                        <i className="fas fa-circle-notch fa-spin mr-1"></i> Mengupload...
                                    </>
                                ) : (
                                    <>
                                        <i className="fas fa-upload mr-1"></i> Upload
                                    </>
                                )}
                            </button>
                        </div>
                    </form>
                </div>
            )}
        </AppLayout>
    );
}

const PROFILE_CSS = `
.profile-avatar-panel {
    display: grid;
    justify-items: center;
}
.profile-avatar-image {
    width: 100%;
    max-width: 220px;
    aspect-ratio: 1;
    object-fit: cover;
    border-radius: 18px;
    border: 1px solid var(--border);
    box-shadow: 0 16px 34px rgba(31,36,64,.14);
}
.profile-upload-backdrop {
    position: fixed;
    inset: 0;
    z-index: 2000;
    display: grid;
    place-items: center;
    padding: 18px;
    background: rgba(15,23,42,.46);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
}
.profile-upload-dialog {
    width: min(560px, 100%);
    display: grid;
    gap: 16px;
    padding: 20px;
    border-radius: 22px;
    border: 1px solid var(--glass-border, rgba(255,255,255,.72));
    background: var(--glass-bg-strong, rgba(255,255,255,.84));
    box-shadow: 0 28px 80px rgba(15,23,42,.28), inset 0 1px 0 rgba(255,255,255,.62);
    backdrop-filter: blur(28px) saturate(1.25);
    -webkit-backdrop-filter: blur(28px) saturate(1.25);
    animation: profile-dialog-in .18s ease-out both;
}
.profile-upload-head,
.profile-upload-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}
.profile-upload-head h5 {
    margin: 0;
    font-size: 18px;
}
.profile-upload-head p {
    max-width: 420px;
    margin: 4px 0 0;
    color: var(--muted);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.profile-upload-close {
    width: 38px;
    height: 38px;
    border: 1px solid var(--border);
    border-radius: 12px;
    color: var(--foreground);
    background: var(--surface-soft);
    cursor: pointer;
}
.profile-upload-preview {
    overflow: hidden;
    border-radius: 18px;
    background: var(--surface-soft);
    border: 1px solid var(--border);
}
.profile-upload-preview img {
    display: block;
    width: 100%;
    max-height: 420px;
    object-fit: contain;
}
.profile-upload-actions {
    justify-content: flex-end;
    flex-wrap: wrap;
}
@keyframes profile-dialog-in {
    from { opacity: 0; transform: translateY(12px) scale(.98); }
    to { opacity: 1; transform: none; }
}
`;
