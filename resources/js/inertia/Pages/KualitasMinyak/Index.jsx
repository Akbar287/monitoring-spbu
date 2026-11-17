import { router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '../../Layouts/AppLayout';
import { PageHeader, Pagination, PemeriksaanTable } from './common';

export default function Index({ title, riwayat, filters, records, dombak, produk, bolehMencatat, baseUrl }) {
    const { errors = {} } = usePage().props;
    const [values, setValues] = useState({ mulai: '', selesai: '', dombak_id: '', produk_id: '', ...filters });
    const filter = (event) => {
        event.preventDefault();
        router.get(riwayat ? `${baseUrl}/riwayat` : baseUrl, Object.fromEntries(Object.entries(values).filter(([,v]) => v)));
    };
    return <AppLayout title={title}>
        <PageHeader title={title}>
            <a className="btn btn-info" href={riwayat ? baseUrl : `${baseUrl}/riwayat`}>{riwayat ? 'Menunggu Validasi' : 'Riwayat Disetujui'}</a>
            <a className="btn btn-light" href={`${baseUrl}/astm`}>Tabel ASTM</a>
            {bolehMencatat && <a className="btn btn-primary" href={`${baseUrl}/create`}>Isi / Buka Catatan Harian</a>}
        </PageHeader>
        <p>{riwayat ? 'Density yang sudah disetujui Admin atau Manajer. Buka catatan untuk menambah Penerimaan atau Akhir pada tanggal yang sama.' : 'Pemeriksaan density yang menunggu validasi dan pemeriksaan Akhir yang belum diisi. Persetujuan berlaku per pemeriksaan.'}</p>
        <div className="card d-print-none"><div className="card-body">
            <form data-no-inertia onSubmit={filter} className="row align-items-end">
                {[['mulai', 'Dari Tanggal'], ['selesai', 'Sampai Tanggal']].map(([key, label]) => <div className="form-group col-md-2" key={key}>
                    <label htmlFor={key}>{label}</label><input id={key} className="form-control" type="date" value={values[key]} onChange={e => setValues({ ...values, [key]: e.target.value })} />
                </div>)}
                {[['dombak_id', 'Dombak', dombak, 'nama_dombak'], ['produk_id', 'Produk', produk, 'nama_produk']].map(([key, label, options, name]) => <div className="form-group col-md-3" key={key}>
                    <label htmlFor={key}>{label}</label><select id={key} className="form-control" value={values[key]} onChange={e => setValues({ ...values, [key]: e.target.value })}>
                        <option value="">Semua {label}</option>{options.map(o => <option key={o[key]} value={o[key]}>{o[name]}</option>)}
                    </select>
                </div>)}
                <div className="form-group col-md-2"><button className="btn btn-primary mr-2">Filter</button><a className="btn btn-light" href={riwayat ? `${baseUrl}/riwayat` : baseUrl}>Reset</a></div>
            </form>
            {Object.values(errors).map((e,i) => <div className="text-danger" role="alert" key={i}>{e}</div>)}
        </div></div>
        <div className="card"><div className="card-body"><PemeriksaanTable records={records.data} riwayat={riwayat} baseUrl={baseUrl} /><Pagination data={records} /></div></div>
    </AppLayout>;
}
