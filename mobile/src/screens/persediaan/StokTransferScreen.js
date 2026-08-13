import { useCallback, useState } from 'react';
import { Halaman } from '../../components/ui';
import { Persediaan } from '../../api/endpoints';
import { Fab, Segmen } from './bagian';
import DaftarData from '../../components/DaftarData';

const PILIHAN = [
    { key: 'belum', label: 'Menunggu' },
    { key: 'riwayat', label: 'Riwayat' },
];

export default function StokTransferScreen({ navigation }) {
    const [segmen, setSegmen] = useState('belum');

    const ambil = useCallback(
        (page, cari, perPage) => (segmen === 'belum'
            ? Persediaan.transfer.daftar({ page, cari, per_page: perPage })
            : Persediaan.transfer.riwayat({ page, cari, per_page: perPage })),
        [segmen]
    );

    return (
        <Halaman judul="Stok Transfer" subjudul="Pindah antar dombak" warna="ungu" scroll={false}>
            <Segmen nilai={segmen} onGanti={setSegmen} pilihan={PILIHAN} />
            <DaftarData
                key={segmen}
                ambil={ambil}
                cariPlaceholder="Cari tanggal, produk, dombak…"
                tone={segmen === 'belum' ? 'ungu' : 'abu'}
                kosong={{
                    judul: segmen === 'belum' ? 'Tidak ada transfer menunggu' : 'Belum ada riwayat',
                    pesan: segmen === 'belum' ? 'Tekan + untuk mencatat transfer baru.' : null,
                }}
                onTekan={(item) => navigation.navigate('StokTransferDetail', { id: item.id })}
            />
            <Fab onPress={() => navigation.navigate('StokTransferForm')} />
        </Halaman>
    );
}
