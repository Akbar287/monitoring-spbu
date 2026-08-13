import { useCallback } from 'react';
import { Halaman } from '../../components/ui';
import DaftarData from '../../components/DaftarData';
import { Persediaan } from '../../api/endpoints';

export default function MonitoringScreen({ navigation }) {
    const ambil = useCallback(
        (page, cari, perPage) => Persediaan.monitoring.daftar({ page, cari, per_page: perPage }),
        []
    );

    return (
        <Halaman judul="Monitoring Stok" warna="indigo" scroll={false}>
            <DaftarData
                ambil={ambil}
                tone="indigo"
                cariPlaceholder="Cari nama produk…"
                kosong={{ judul: 'Belum ada catatan stok', pesan: 'Produk belum punya saldo persediaan.' }}
                onTekan={(item) => navigation.navigate('MonitoringDetail', { id: item.id, judul: item.judul })}
            />
        </Halaman>
    );
}
