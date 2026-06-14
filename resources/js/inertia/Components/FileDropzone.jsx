import { usePage } from '@inertiajs/react';
import { useCallback, useEffect, useState } from 'react';
import { useDropzone } from 'react-dropzone';

// Area unggah file drag-n-drop (react-dropzone) — dipakai semua field
// type `file` di Form/Index. Satu file; gambar mendapat preview thumbnail.
// Filter ekstensi diterapkan pada file picker via atribut accept native
// (validasi final tetap di server).
const fmtSize = (b) => {
    if (b >= 1048576) return (b / 1048576).toFixed(2) + ' MB';
    if (b >= 1024) return (b / 1024).toFixed(1) + ' KB';
    return b + ' B';
};

export default function FileDropzone({ value, onChange, accept, error, maxSizeKb }) {
    const { uploadMaxKb } = usePage().props;
    const limitKb = maxSizeKb ?? uploadMaxKb ?? 2048;
    const limitLabel = limitKb >= 1024 ? (limitKb / 1024).toFixed(limitKb % 1024 === 0 ? 0 : 1) + ' MB' : limitKb + ' KB';

    const [preview, setPreview] = useState(null);
    const [sizeError, setSizeError] = useState(null);

    useEffect(() => {
        if (value && value.type && value.type.startsWith('image/')) {
            const url = URL.createObjectURL(value);
            setPreview(url);
            return () => URL.revokeObjectURL(url);
        }
        setPreview(null);
        return undefined;
    }, [value]);

    const onDrop = useCallback(
        (accepted, rejected) => {
            if (rejected.length > 0) {
                const r = rejected[0];
                const tooLarge = r.errors?.some((e) => e.code === 'file-too-large');
                setSizeError(
                    tooLarge
                        ? `Ukuran file "${r.file.name}" (${fmtSize(r.file.size)}) melebihi batas maksimal ${limitLabel}.`
                        : 'File tidak dapat diterima.'
                );
                onChange(null);
                return;
            }
            if (accepted.length > 0) {
                setSizeError(null);
                onChange(accepted[0]);
            }
        },
        [onChange, limitLabel]
    );

    const { getRootProps, getInputProps, isDragActive, open } = useDropzone({
        onDrop,
        multiple: false,
        noClick: false,
        maxSize: limitKb * 1024,
    });

    const border = error || sizeError ? '#fc544b' : isDragActive ? '#6777ef' : '#cdd2e6';

    if (value) {
        return (
            <div
                className="d-flex align-items-center"
                style={{
                    gap: 14,
                    border: `1.5px solid ${border}`,
                    borderRadius: 12,
                    padding: '12px 16px',
                    background: '#fafbff',
                }}
            >
                {preview ? (
                    <img src={preview} alt="preview" style={{ width: 56, height: 56, objectFit: 'cover', borderRadius: 8 }} />
                ) : (
                    <div
                        style={{
                            width: 56,
                            height: 56,
                            borderRadius: 8,
                            display: 'grid',
                            placeItems: 'center',
                            background: 'rgba(103,119,239,.12)',
                            color: '#6777ef',
                            fontSize: 22,
                        }}
                    >
                        <i className="fas fa-file-alt"></i>
                    </div>
                )}
                <div style={{ minWidth: 0, flex: 1 }}>
                    <div style={{ fontWeight: 700, fontSize: 14, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
                        {value.name}
                    </div>
                    <div className="text-muted" style={{ fontSize: 12 }}>
                        {fmtSize(value.size)}
                    </div>
                </div>
                <button
                    type="button"
                    className="btn btn-sm btn-outline-danger"
                    title="Hapus file"
                    onClick={() => onChange(null)}
                >
                    <i className="fas fa-times"></i>
                </button>
            </div>
        );
    }

    return (
        <div
            {...getRootProps()}
            style={{
                border: `2px dashed ${border}`,
                borderRadius: 12,
                padding: '28px 16px',
                textAlign: 'center',
                cursor: 'pointer',
                background: isDragActive ? 'rgba(103,119,239,.07)' : '#fafbff',
                transition: 'background .15s ease, border-color .15s ease',
            }}
        >
            <input {...getInputProps({ accept: accept || undefined })} />
            <i
                className={`fas ${isDragActive ? 'fa-hand-point-down' : 'fa-cloud-upload-alt'}`}
                style={{ fontSize: 30, color: isDragActive ? '#6777ef' : '#aab0cb' }}
            ></i>
            <div style={{ fontWeight: 600, fontSize: 14, marginTop: 8 }}>
                {isDragActive ? 'Lepaskan file di sini…' : 'Tarik & letakkan file di sini'}
            </div>
            <div className="text-muted" style={{ fontSize: 12.5, marginTop: 2 }}>
                atau{' '}
                <span
                    style={{ color: '#6777ef', fontWeight: 700 }}
                    onClick={(e) => {
                        e.stopPropagation();
                        open();
                    }}
                >
                    pilih file
                </span>{' '}
                dari perangkat anda{accept ? ` (${accept})` : ''} · Maks {limitLabel}
            </div>
            {sizeError && (
                <div style={{ color: '#fc544b', fontSize: 12.5, fontWeight: 600, marginTop: 8 }}>
                    <i className="fas fa-exclamation-circle mr-1"></i>
                    {sizeError}
                </div>
            )}
        </div>
    );
}
