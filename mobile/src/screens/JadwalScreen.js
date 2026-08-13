import { useCallback, useState } from 'react';
import { useFocusEffect } from '@react-navigation/native';
import { FlatList, Pressable, RefreshControl, StyleSheet, Text, View } from 'react-native';
import { Halaman, Card, EmptyState, ErrorState, Loading } from '../components/ui';
import { BadgeMode } from './BerandaScreen';
import { Jadwal } from '../api/endpoints';
import { useMuat } from '../hooks/useMuat';
import { jamSingkat, tanggalPendek } from '../utils/format';
import { colors, radius, spacing, typography } from '../theme';

const FILTER = [
    { key: 'semua', label: 'Semua' },
    { key: 'belum', label: 'Belum absen' },
    { key: 'berjalan', label: 'Bertugas' },
    { key: 'selesai', label: 'Selesai' },
];

export default function JadwalScreen({ navigation }) {
    const [status, setStatus] = useState('semua');

    const { data, memuat, menyegarkan, error, segarkan, muatUlang } = useMuat(
        useCallback(() => Jadwal.daftar({ status, per_page: 50 }), [status]),
        [status]
    );

    // kembali dari layar absen -> daftar harus mencerminkan status terbaru
    useFocusEffect(useCallback(() => { muatUlang({ segarkan: true }); }, [muatUlang]));

    const items = data?.items || [];

    return (
        <Halaman judul={'Jadwal Saya'} warna="teal" scroll={false} padded={false}>
            <View style={styles.filterBar}>
                {FILTER.map((f) => (
                    <Pressable
                        key={f.key}
                        onPress={() => setStatus(f.key)}
                        style={[styles.chip, status === f.key && styles.chipAktif]}
                    >
                        <Text style={[styles.chipTeks, status === f.key && styles.chipTeksAktif]}>
                            {f.label}
                        </Text>
                    </Pressable>
                ))}
            </View>

            {memuat && !data ? (
                <Loading />
            ) : error && !data ? (
                <ErrorState pesan={error.message} onCoba={muatUlang} />
            ) : (
                <FlatList
                    data={items}
                    keyExtractor={(item) => item.penjadwalan_id}
                    contentContainerStyle={styles.list}
                    refreshControl={
                        <RefreshControl
                            refreshing={menyegarkan}
                            onRefresh={segarkan}
                            colors={[colors.primary]}
                            tintColor={colors.primary}
                        />
                    }
                    ListEmptyComponent={
                        <EmptyState
                            judul="Tidak ada jadwal"
                            pesan="Belum ada jadwal pada rentang bulan ini untuk filter yang dipilih."
                        />
                    }
                    renderItem={({ item }) => (
                        <Pressable
                            onPress={() => navigation.navigate('JadwalDetail', { id: item.penjadwalan_id })}
                        >
                            <Card tone={item.mode === 'done' ? 'abu' : item.mode === 'out' ? 'hijau' : 'teal'}>
                                <View style={styles.baris}>
                                    <Text style={typography.heading}>{tanggalPendek(item.tanggal)}</Text>
                                    <BadgeMode mode={item.mode} />
                                </View>
                                <Text style={typography.body}>
                                    {item.jam_kerja?.nama || 'Shift'} · {jamSingkat(item.jam_kerja?.jam_datang)}–{jamSingkat(item.jam_kerja?.jam_pulang)}
                                </Text>
                                <Text style={typography.muted}>
                                    {item.jabatan}
                                    {item.nozzles?.length ? ` · ${item.nozzles.join(', ')}` : ''}
                                </Text>
                            </Card>
                        </Pressable>
                    )}
                />
            )}
        </Halaman>
    );
}

const styles = StyleSheet.create({
    screen: { flex: 1, backgroundColor: colors.background },
    filterBar: {
        flexDirection: 'row',
        gap: spacing.sm,
        paddingHorizontal: spacing.lg,
        paddingTop: spacing.md,
        paddingBottom: spacing.sm,
        flexWrap: 'wrap',
    },
    chip: {
        paddingHorizontal: spacing.md,
        paddingVertical: 6,
        borderRadius: radius.pill,
        borderWidth: 1,
        borderColor: colors.border,
        backgroundColor: colors.surface,
    },
    chipAktif: { backgroundColor: colors.primary, borderColor: colors.primary },
    chipTeks: { fontSize: 13, color: colors.textMuted, fontWeight: '600' },
    chipTeksAktif: { color: '#fff' },

    list: { padding: spacing.lg, paddingTop: spacing.sm, gap: spacing.md, flexGrow: 1 },
    baris: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' },
});
