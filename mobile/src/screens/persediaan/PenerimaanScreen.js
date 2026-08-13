import { useCallback, useState } from 'react';
import { Halaman } from '../../components/ui';
import { Persediaan } from '../../api/endpoints';
import { Segmen } from './bagian';
import DaftarData from '../../components/DaftarData';

const PILIHAN = [
    { key: 'lo', label: 'LO siap' },
    { key: 'verifikasi', label: 'Verifikasi' },
    { key: 'riwayat', label: 'Riwayat' },
];

export default function PenerimaanScreen({ navigation }) {
    const [segmen, setSegmen] = useState('lo');

    const ambil = useCallback((page, cari, perPage) => {
        const p = { page, cari, per_page: perPage };
        if (segmen === 'lo') return Persediaan.penerimaan.lo(p);
        if (segmen === 'verifikasi') return Persediaan.penerimaan.verifikasi(p);
        return Persediaan.penerimaan.riwayat(p);
    }, [segmen]);

    const tekan = (item) => {
        if (segmen === 'lo') navigation.navigate('PenerimaanForm', { loId: item.id, judul: item.judul });
        else navigation.navigate('PenerimaanDetail', { id: item.id });
    };

    return (
        <Halaman judul="Penerimaan BBM" subjudul="Bongkar muatan LO" warna="hijau" scroll={false}>
            <Segmen nilai={segmen} onGanti={setSegmen} pilihan={PILIHAN} />
            <DaftarData
                key={segmen}
                ambil={ambil}
                cariPlaceholder={segmen === 'lo' ? 'Cari no. LO, faktur, produk, no. polisi…' : 'Cari no. LO atau dombak…'}
                tone={segmen === 'riwayat' ? 'abu' : 'hijau'}
                kosong={{
                    judul: segmen === 'lo' ? 'Tidak ada LO menunggu' : segmen === 'verifikasi' ? 'Tidak ada yang perlu diverifikasi' : 'Belum ada riwayat',
                    pesan: segmen === 'lo' ? 'Semua dokumen LO sudah dibongkar.' : null,
                }}
                onTekan={tekan}
            />
        </Halaman>
    );
}
