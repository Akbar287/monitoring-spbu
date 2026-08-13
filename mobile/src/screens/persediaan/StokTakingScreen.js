import { useCallback, useState } from 'react';
import { Halaman } from '../../components/ui';
import { Persediaan } from '../../api/endpoints';
import { Fab, Segmen } from './bagian';
import DaftarData from '../../components/DaftarData';

const PILIHAN = [
    { key: 'belum', label: 'Menunggu' },
    { key: 'riwayat', label: 'Riwayat' },
];

export default function StokTakingScreen({ navigation }) {
    const [segmen, setSegmen] = useState('belum');

    const ambil = useCallback(
        (page, cari, perPage) => (segmen === 'belum'
            ? Persediaan.taking.daftar({ page, cari, per_page: perPage })
            : Persediaan.taking.riwayat({ page, cari, per_page: perPage })),
        [segmen]
    );

    return (
        <Halaman judul="Stok Taking" subjudul="Pengukuran fisik dombak" warna="oranye" scroll={false}>
            <Segmen nilai={segmen} onGanti={setSegmen} pilihan={PILIHAN} />
            <DaftarData
                key={segmen}
                ambil={ambil}
                cariPlaceholder="Cari tanggal, dombak, produk…"
                tone={segmen === 'belum' ? 'oranye' : 'abu'}
                kosong={{
                    judul: segmen === 'belum' ? 'Tidak ada yang menunggu' : 'Belum ada riwayat',
                    pesan: segmen === 'belum' ? 'Tekan + untuk mencatat pengukuran baru.' : null,
                }}
                onTekan={(item) => navigation.navigate('StokTakingDetail', { id: item.id })}
            />
            <Fab onPress={() => navigation.navigate('StokTakingForm')} />
        </Halaman>
    );
}
