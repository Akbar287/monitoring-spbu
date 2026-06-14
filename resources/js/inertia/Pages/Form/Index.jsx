import { useForm, usePage } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import FileDropzone from '../../Components/FileDropzone';

// Halaman form CRUD generik (create & edit).
// Props dari controller:
//   title, breadcrumbs: [{label,url?}], backUrl,
//   method: 'post' | 'put', action: url tujuan submit,
//   fields: [{name, label, type: text|number|date|time|textarea|select,
//             value, options?: [{value,label}], placeholder?, hint?, rows?}]
// Error validasi Laravel otomatis tampil per-field lewat shared prop `errors`.
export default function Index({ title, breadcrumbs = [], backUrl, method = 'post', action, fields = [] }) {
    const { urls } = usePage().props;
    const { data, setData, post, put, processing, errors, transform } = useForm(
        Object.fromEntries(fields.map((f) => [f.name, f.value ?? '']))
    );

    const hasFile = fields.some((f) => f.type === 'file');

    const submit = (e) => {
        e.preventDefault();
        if (processing) return; // cegah double-submit klik ganda cepat
        // multipart tidak bisa dikirim sebagai PUT asli — pakai POST + _method
        if (method === 'put' && hasFile) {
            transform((d) => ({ ...d, _method: 'put' }));
            post(action, { preserveScroll: true });
            return;
        }
        (method === 'put' ? put : post)(action, { preserveScroll: true });
    };

    const inputClass = (name) => `form-control ${errors[name] ? 'is-invalid' : ''}`;

    const renderField = (f) => {
        const value = data[f.name] ?? '';
        const onChange = (e) => setData(f.name, e.target.value);

        if (f.type === 'textarea') {
            return (
                <textarea
                    id={f.name}
                    className={inputClass(f.name)}
                    rows={f.rows || 3}
                    value={value}
                    placeholder={f.placeholder || ''}
                    onChange={onChange}
                />
            );
        }

        if (f.type === 'hidden') {
            return null; // nilai tetap terkirim via useForm data
        }

        if (f.type === 'file') {
            // drag-n-drop (react-dropzone); useForm mengirim File sbg multipart
            return (
                <FileDropzone
                    value={data[f.name] instanceof File ? data[f.name] : null}
                    onChange={(file) => setData(f.name, file)}
                    accept={f.accept}
                    error={!!errors[f.name]}
                />
            );
        }

        if (f.type === 'multiselect') {
            const selected = Array.isArray(value) ? value.map(String) : [];
            return (
                <>
                    <select
                        id={f.name}
                        multiple
                        size={f.size || 6}
                        className={`custom-select ${errors[f.name] ? 'is-invalid' : ''}`}
                        value={selected}
                        onChange={(e) => setData(f.name, Array.from(e.target.selectedOptions).map((o) => o.value))}
                    >
                        {(f.options || []).map((o) => (
                            <option key={String(o.value)} value={String(o.value)}>
                                {o.label}
                            </option>
                        ))}
                    </select>
                    <small className="form-text text-muted">Tahan Ctrl/Cmd untuk memilih lebih dari satu.</small>
                </>
            );
        }

        if (f.type === 'select') {
            return (
                <select
                    id={f.name}
                    className={`custom-select ${errors[f.name] ? 'is-invalid' : ''}`}
                    value={String(value)}
                    onChange={onChange}
                >
                    <option value="">— Silakan Pilih —</option>
                    {(f.options || []).map((o) => (
                        <option key={String(o.value)} value={String(o.value)}>
                            {o.label}
                        </option>
                    ))}
                </select>
            );
        }

        return (
            <input
                id={f.name}
                type={f.type || 'text'}
                className={inputClass(f.name)}
                value={value}
                placeholder={f.placeholder || ''}
                readOnly={!!f.readonly}
                onChange={onChange}
            />
        );
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
                    <div className="breadcrumb-item">{title}</div>
                </div>
            </div>

            <div className="section-body">
                <div className="row justify-content-center">
                    <div className="col-12 col-lg-9">
                        <div className="card">
                            <div className="card-header">
                                <h4>{title}</h4>
                            </div>
                            <div className="card-body">
                                <form onSubmit={submit}>
                                    <div className="row">
                                        {fields.filter((f) => f.type !== 'hidden').map((f) => (
                                            <div key={f.name} className={f.col || 'col-12'}>
                                                <div className="form-group">
                                                    <label htmlFor={f.name}>{f.label}</label>
                                                    {renderField(f)}
                                                    {errors[f.name] && (
                                                        <div className="invalid-feedback d-block">{errors[f.name]}</div>
                                                    )}
                                                    {f.hint && <small className="form-text text-muted">{f.hint}</small>}
                                                </div>
                                            </div>
                                        ))}
                                    </div>

                                    <div className="d-flex align-items-center mt-2" style={{ gap: 10 }}>
                                        <button type="submit" className="btn btn-primary" disabled={processing}>
                                            {processing ? (
                                                <>
                                                    <i className="fas fa-circle-notch fa-spin mr-1"></i> Menyimpan...
                                                </>
                                            ) : (
                                                <>
                                                    <i className="fas fa-save mr-1"></i> Simpan
                                                </>
                                            )}
                                        </button>
                                        {backUrl && (
                                            <a href={backUrl} className="btn btn-light">
                                                Batal
                                            </a>
                                        )}
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
