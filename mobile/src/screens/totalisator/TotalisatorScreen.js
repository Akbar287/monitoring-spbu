import { useCallback, useState } from 'react';
import { useFocusEffect } from '@react-navigation/native';
import { Pressable, StyleSheet, Text, View } from 'react-native';
import { Badge, Card, Halaman, Statistik } from '../../components/ui';
import DaftarData from '../../components/DaftarData';
import DateField from '../../components/DateField';
import { Segmen, Fab } from '../persediaan/bagian';
import { Totalisator } from '../../api/endpoints';
import { liter, tanggalPendek } from '../../utils/format';
import { colors, spacing, typography } from '../../theme';

const PILIHAN = [
    { key: 'terbuka', label: 'Belum dikonfirmasi' },
    { key: 'riwayat', label: 'Riwayat' },
];

export default function TotalisatorScreen({ navigation }) {
    const [segmen, setSegmen] = useState('terbuka');
    const [dari, setDari] = useState('');
    const [sampai, setSampai] = useState('');
    const [ringkas, setRingkas] = useState(null);

    useFocusEffect(useCallback(() => {
        Totalisator.ringkasan().then(setRingkas).catch(() => setRingkas(null));
    }, []));

    const ambil = useCallback((page, cari, perPage) => {
        const p = { page, cari, per_page: perPage, dari: dari || undefined, sampai: sampai || undefined };
        return segmen === 'terbuka' ? Totalisator.daftar(p) : Totalisator.riwayat(p);
    }, [segmen, dari, sampai]);

    return (
        <Halaman judul="Totalisator" subjudul="Angka meter per shift" warna="pink" kembali={false} scroll={false}>
            <DaftarData
                key={`${segmen}-${dari}-${sampai}`}
                ambil={ambil}
                cariPlaceholder="Cari tanggal, nozzle, dombak, produk…"
                jumlahFilterAktif={(dari ? 1 : 0) + (sampai ? 1 : 0)}
                filter={
                    <>
                        <DateField label="Dari tanggal" value={dari} onChange={setDari} />
                        <DateField label="Sampai tanggal" value={sampai} onChange={setSampai} />
                    </>
                }
                kepala={
                    <>
                        {!!ringkas && (
                            <View style={styles.statistik}>
                                <Statistik label="Shift terbuka" nilai={ringkas.shift_terbuka} warna="oranye" ikon="time-outline" />
                                <Statistik label="Siap konfirmasi" nilai={ringkas.siap_konfirmasi} warna="hijau" ikon="checkmark-done-outline" />
                                <Statistik label="Belum diisi" nilai={ringkas.belum_diisi_akhir} warna="merah" ikon="create-outline" />
                            </View>
                        )}
                        <Segmen nilai={segmen} onGanti={setSegmen} pilihan={PILIHAN} />
                    </>
                }
                kosong={{
                    judul: segmen === 'terbuka' ? 'Tidak ada shift terbuka' : 'Belum ada riwayat',
                    pesan: segmen === 'terbuka' ? 'Tekan + untuk membuka shift baru.' : null,
                }}
                render={(item) => (
                    <Pressable onPress={() => navigation.navigate('TotalisatorDetail', { id: item.id })}>
                        <Card tone={item.is_confirmation ? 'abu' : item.belum_diisi_akhir ? 'merah' : 'hijau'}>
                            <View style={styles.baris}>
                                <View style={styles.isi}>
                                    <Text style={typography.heading} numberOfLines={1}>{item.judul}</Text>
                                    <Text style={typography.muted} numberOfLines={1}>
                                        {tanggalPendek(item.tanggal)} · {item.jam_kerja} · {item.produk}
                                    </Text>
                                </View>
                                <Badge
                                    label={item.is_confirmation ? 'Selesai' : item.belum_diisi_akhir ? 'Perlu diisi' : 'Siap'}
                                    tone={item.is_confirmation ? 'abu' : item.belum_diisi_akhir ? 'merah' : 'hijau'}
                                />
                            </View>
                            {!item.belum_diisi_akhir && <Text style={styles.liter}>{liter(item.liter)}</Text>}
                        </Card>
                    </Pressable>
                )}
            />
            <Fab onPress={() => navigation.navigate('TotalisatorForm')} />
        </Halaman>
    );
}

const styles = StyleSheet.create({
    statistik: { flexDirection: 'row', gap: spacing.md, marginBottom: spacing.sm },
    baris: { flexDirection: 'row', alignItems: 'center', gap: spacing.sm },
    isi: { flex: 1, gap: 2 },
    liter: { fontSize: 16, fontWeight: '800', color: colors.text },
});
