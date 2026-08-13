import { useCallback, useState } from 'react';
import { Pressable, StyleSheet, Text, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { Card, Halaman } from '../../components/ui';
import DaftarData from '../../components/DaftarData';
import DateField from '../../components/DateField';
import { Fab } from '../persediaan/bagian';
import { TAHAP } from '../../procurement/tahap';
import { colors, radius, spacing, typography } from '../../theme';

/** Daftar dokumen untuk satu tahap procurement (konfigurasi di tahap.js). */
export default function ProcurementListScreen({ route, navigation }) {
    const { tahap } = route.params;
    const konf = TAHAP[tahap];

    const [status, setStatus] = useState(konf.filter?.[0]?.key ?? null);
    const [dari, setDari] = useState('');
    const [sampai, setSampai] = useState('');

    const ambil = useCallback((page, cari, perPage) => {
        const params = { page, cari, per_page: perPage };
        if (status) params.status = status;
        if (dari) params.dari = dari;
        if (sampai) params.sampai = sampai;
        return konf.daftar(params);
    }, [konf, status, dari, sampai]);

    const layarTambah = konf.layarTambah;

    return (
        <Halaman judul={konf.label} subjudul={konf.deskripsi} warna={konf.warna} scroll={false}>
            <DaftarData
                key={`${tahap}-${status}-${dari}-${sampai}`}
                ambil={ambil}
                cariPlaceholder={`Cari ${konf.label.toLowerCase()}…`}
                jumlahFilterAktif={(dari ? 1 : 0) + (sampai ? 1 : 0)}
                filter={
                    <>
                        <DateField label="Dari tanggal" value={dari} onChange={setDari} />
                        <DateField label="Sampai tanggal" value={sampai} onChange={setSampai} />
                    </>
                }
                kepala={
                    (konf.filter?.length ?? 0) > 0 && (
                        <View style={styles.chipBaris}>
                            {konf.filter.map((f) => (
                                <Pressable key={f.key} onPress={() => setStatus(f.key)}
                                    style={[styles.chip, status === f.key && styles.chipAktif]}>
                                    <Text style={[styles.chipTeks, status === f.key && styles.chipTeksAktif]}>
                                        {f.label}
                                    </Text>
                                </Pressable>
                            ))}
                        </View>
                    )
                }
                kosong={{ judul: `Belum ada ${konf.label.toLowerCase()}`, pesan: konf.kosong }}
                render={(item) => (
                    <Pressable onPress={() => navigation.navigate(konf.layarDetail, { id: item.id, tahap })}>
                        <Card tone={konf.warna}>
                            <View style={styles.baris}>
                                <View style={styles.isiBaris}>
                                    <Text style={typography.heading} numberOfLines={1}>{item.judul}</Text>
                                    <Text style={typography.muted} numberOfLines={2}>{item.subjudul}</Text>
                                </View>
                                <Ionicons name="chevron-forward" size={18} color={colors.textMuted} />
                            </View>
                        </Card>
                    </Pressable>
                )}
            />

            {!!layarTambah && <Fab onPress={() => navigation.navigate(layarTambah)} />}
        </Halaman>
    );
}

const styles = StyleSheet.create({
    chipBaris: { flexDirection: 'row', flexWrap: 'wrap', gap: spacing.sm, marginBottom: spacing.sm },
    chip: {
        paddingHorizontal: spacing.md, paddingVertical: 6, borderRadius: radius.pill,
        borderWidth: 1, borderColor: colors.border, backgroundColor: colors.surface,
    },
    chipAktif: { backgroundColor: colors.primary, borderColor: colors.primary },
    chipTeks: { fontSize: 12, color: colors.textMuted, fontWeight: '700' },
    chipTeksAktif: { color: '#fff' },
    baris: { flexDirection: 'row', alignItems: 'center', gap: spacing.sm },
    isiBaris: { flex: 1, gap: 2 },
});
