import { useCallback, useEffect, useState } from 'react';
import { StyleSheet, Text, View } from 'react-native';
import { Card, Halaman, Row } from '../../components/ui';
import DaftarData from '../../components/DaftarData';
import DateField from '../../components/DateField';
import SelectField from '../../components/SelectField';
import { Persediaan } from '../../api/endpoints';
import { liter, tanggalPendek } from '../../utils/format';
import { spacing, typography } from '../../theme';

/** Riwayat pergerakan stok satu produk — filter sama seperti halaman web. */
export default function MonitoringRiwayatScreen({ route }) {
    const { id, judul } = route.params;
    const [opsi, setOpsi] = useState({ tipe: [], dombak: [] });
    const [tipe, setTipe] = useState('');
    const [dombak, setDombak] = useState('');
    const [dari, setDari] = useState('');
    const [sampai, setSampai] = useState('');

    useEffect(() => {
        Persediaan.monitoring.opsiRiwayat(id).then(setOpsi).catch(() => {});
    }, [id]);

    const ambil = useCallback(
        (page, cari, perPage) => Persediaan.monitoring.riwayat(id, {
            page, cari, per_page: perPage,
            type_dokumen_stok_id: tipe || undefined,
            dombak_id: dombak || undefined,
            dari: dari || undefined,
            sampai: sampai || undefined,
        }),
        [id, tipe, dombak, dari, sampai]
    );

    const aktif = [tipe, dombak, dari, sampai].filter(Boolean).length;

    return (
        <Halaman judul="Riwayat Stok" subjudul={judul} warna="indigo" scroll={false}>
            <DaftarData
                key={`${tipe}-${dombak}-${dari}-${sampai}`}
                ambil={ambil}
                cariPlaceholder="Cari jenis pergerakan atau dombak…"
                jumlahFilterAktif={aktif}
                filter={
                    <>
                        <SelectField label="Jenis pergerakan" value={tipe} onChange={setTipe}
                            options={[{ value: '', label: 'Semua' }, ...opsi.tipe]} />
                        <SelectField label="Dombak" value={dombak} onChange={setDombak}
                            options={[{ value: '', label: 'Semua' }, ...opsi.dombak]} />
                        <DateField label="Dari tanggal" value={dari} onChange={setDari} />
                        <DateField label="Sampai tanggal" value={sampai} onChange={setSampai} />
                    </>
                }
                kosong={{ judul: 'Belum ada pergerakan', pesan: 'Tidak ada dokumen pada filter ini.' }}
                render={(d) => (
                    <Card tone={d.is_confirmation ? 'abu' : 'oranye'}>
                        <View style={styles.baris}>
                            <Text style={typography.heading} numberOfLines={1}>{d.jenis}</Text>
                            <Text style={typography.caption}>{tanggalPendek(d.tanggal)}</Text>
                        </View>
                        <Text style={typography.muted}>{d.dombak} · {d.jam_kerja}</Text>
                        <Row label="Stok awal" value={liter(d.stok_awal)} />
                        <Row label="Stok akhir" value={liter(d.stok_akhir)} />
                        <Row label="Selisih" value={liter(d.selisih)} />
                        {!!d.losses && (
                            <Row label={`Losses (${d.losses.jenis})`}
                                value={`${d.losses.simbol === 'kurang' ? '−' : '+'} ${liter(d.losses.jumlah)}`} />
                        )}
                    </Card>
                )}
            />
        </Halaman>
    );
}

const styles = StyleSheet.create({
    baris: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', gap: spacing.sm },
});
