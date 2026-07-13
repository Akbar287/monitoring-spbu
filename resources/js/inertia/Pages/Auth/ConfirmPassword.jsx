import { useForm } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';

export default function ConfirmPassword({ title = 'Confirm Password', action, forgotUrl }) {
    const { data, setData, post, processing, errors } = useForm({ password: '' });

    const submit = (e) => {
        e.preventDefault();
        post(action, { preserveScroll: true });
    };

    return (
        <AppLayout title={title}>
            <div className="section-header">
                <h1>Confirm Password</h1>
            </div>
            <div className="section-body">
                <div className="row justify-content-center">
                    <div className="col-md-8">
                        <div className="card">
                            <div className="card-header">
                                <h4>Confirm Password</h4>
                            </div>
                            <div className="card-body">
                                <p>Please confirm your password before continuing.</p>
                                <form onSubmit={submit}>
                                    <div className="form-group row">
                                        <label htmlFor="password" className="col-md-4 col-form-label text-md-right">
                                            Password
                                        </label>
                                        <div className="col-md-6">
                                            <input
                                                id="password"
                                                type="password"
                                                className={`form-control ${errors.password ? 'is-invalid' : ''}`}
                                                value={data.password}
                                                autoComplete="current-password"
                                                onChange={(e) => setData('password', e.target.value)}
                                            />
                                            {errors.password && <div className="invalid-feedback d-block">{errors.password}</div>}
                                        </div>
                                    </div>
                                    <div className="row">
                                        <div className="col-md-8 offset-md-4">
                                            <button type="submit" className="btn btn-primary" disabled={processing}>
                                                Confirm Password
                                            </button>
                                            {forgotUrl && (
                                                <a className="btn btn-link" href={forgotUrl}>
                                                    Forgot Your Password?
                                                </a>
                                            )}
                                        </div>
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
