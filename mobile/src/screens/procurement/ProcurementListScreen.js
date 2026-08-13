import { useCallback, useEffect, useState } from 'react';
import { useFocusEffect } from '@react-navigation/native';
import { ActivityIndicator, FlatList, Pressable, RefreshControl, StyleSheet, Text, TextInput, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { Halaman, Card, EmptyState, ErrorState, Loading } from '../../components/ui';
import { TAHAP } from '../../procurement/tahap';
import { colors, radius, spacing, typography } from '../../theme';

/** Daftar dokumen untuk satu tahap procurement (konfigurasi di tahap.js). */
export default function ProcurementListScreen({ route, navigation }) {
    const { tahap } = route.params;
    const konf = TAHAP[tahap];

    const [cariInput, setCariInput] = useState('');
    const [cari, setCari] = useState('');
    const [filter, setFilter] = useState(konf.filter?.[0]?.key ?? null);
    const [items, setItems] = useState([]);
    const [meta, setMeta] = useState(null);
    const [memuat, setMemuat] = useState(true);
    const [menyegarkan, setMenyegarkan] = useState(false);
    const [memuatLagi, setMemuatLagi] = useState(false);
    const [error, setError] = useState(null);

    useEffect(() => {
        const t = setTimeout(() => setCari(cariInput.trim()), 400);
        return () => clearTimeout(t);
    }, [cariInput]);

    const muat = useCallback(async ({ page = 1, mode = 'awal' } = {}) => {
        if (mode === 'awal') setMemuat(true);
        if (mode === 'segar') setMenyegarkan(true);
        if (mode === 'lagi') setMemuatLagi(true);
        setError(null);
        try {
            const params = { cari, page, per_page: 25 };
            if (filter) params.status = filter;
            const hasil = await konf.daftar(params);
            setItems((lama) => (page === 1 ? hasil.items : [...lama, ...hasil.items]));
            setMeta(hasil.meta);
        } catch (e) {
            setError(e);
        } finally {
            setMemuat(false); setMenyegarkan(false); setMemuatLagi(false);
        }
    }, [konf, cari, filter]);

    useEffect(() => { muat({ page: 1, mode: 'awal' }); }, [muat]);
    useFocusEffect(useCallback(() => { muat({ page: 1, mode: 'segar' }); }, [muat]));

    const layarTambah = { perencanaan: 'RencanaBaru', ms2: 'Ms2Form' }[tahap];

    return (
        <Halaman judul={konf.label} warna="oranye" scroll={false} padded={false}>
            <View style={styles.cariBar}>
                <Ionicons name="search" size={18} color={colors.textMuted} />
                <TextInput
                    value={cariInput}
                    onChangeText={setCariInput}
                    placeholder={`Cari ${konf.label.toLowerCase()}…`}
                    placeholderTextColor={colors.textMuted}
                    style={styles.cariInput}
                    autoCorrect={false}
                />
                {cariInput.length > 0 && (
                    <Pressable onPress={() => setCariInput('')} hitSlop={10}>
                        <Ionicons name="close-circle" size={18} color={colors.textMuted} />
                    </Pressable>
                )}
            </View>

            {!!konf.filter && (
                <View style={styles.filterBar}>
                    {konf.filter.map((f) => (
                        <Pressable
                            key={f.key}
                            onPress={() => setFilter(f.key)}
                            style={[styles.chip, filter === f.key && styles.chipAktif]}
                        >
                            <Text style={[styles.chipTeks, filter === f.key && styles.chipTeksAktif]}>{f.label}</Text>
                        </Pressable>
                    ))}
                </View>
            )}

            {memuat && items.length === 0 ? (
                <Loading />
            ) : error && items.length === 0 ? (
                <ErrorState pesan={error.message} onCoba={() => muat({ page: 1, mode: 'awal' })} />
            ) : (
                <FlatList
                    data={items}
                    keyExtractor={(i) => String(i.id)}
                    contentContainerStyle={styles.list}
                    refreshControl={
                        <RefreshControl refreshing={menyegarkan} onRefresh={() => muat({ page: 1, mode: 'segar' })}
                            colors={[colors.primary]} tintColor={colors.primary} />
                    }
                    onEndReachedThreshold={0.4}
                    onEndReached={() => {
                        if (!memuatLagi && meta?.has_more) muat({ page: meta.current_page + 1, mode: 'lagi' });
                    }}
                    ListFooterComponent={memuatLagi ? <ActivityIndicator style={styles.footer} color={colors.primary} /> : null}
                    ListEmptyComponent={
                        <EmptyState
                            judul={`Belum ada ${konf.label.toLowerCase()}`}
                            pesan={cari ? `Tidak ada hasil untuk "${cari}".` : 'Dokumen akan muncul di sini setelah dibuat pada tahap sebelumnya.'}
                        />
                    }
                    renderItem={({ item }) => (
                        <Pressable
                            onPress={() => navigation.navigate(konf.layarDetail, { id: item.id, tahap })}
                        >
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
            )}

            {!!layarTambah && (
                <Pressable
                    style={({ pressed }) => [styles.fab, pressed && { opacity: 0.85 }]}
                    onPress={() => navigation.navigate(layarTambah)}
                >
                    <Ionicons name="add" size={26} color="#fff" />
                </Pressable>
            )}
        </Halaman>
    );
}

const styles = StyleSheet.create({
    screen: { flex: 1, backgroundColor: colors.background },
    cariBar: {
        flexDirection: 'row', alignItems: 'center', gap: spacing.sm,
        margin: spacing.lg, marginBottom: spacing.sm, paddingHorizontal: spacing.md,
        minHeight: 44, borderRadius: radius.md, borderWidth: 1,
        borderColor: colors.border, backgroundColor: colors.surface,
    },
    cariInput: { flex: 1, fontSize: 15, color: colors.text, paddingVertical: 0 },
    filterBar: { flexDirection: 'row', gap: spacing.sm, paddingHorizontal: spacing.lg, paddingBottom: spacing.sm, flexWrap: 'wrap' },
    chip: {
        paddingHorizontal: spacing.md, paddingVertical: 6, borderRadius: radius.pill,
        borderWidth: 1, borderColor: colors.border, backgroundColor: colors.surface,
    },
    chipAktif: { backgroundColor: colors.primary, borderColor: colors.primary },
    chipTeks: { fontSize: 13, color: colors.textMuted, fontWeight: '600' },
    chipTeksAktif: { color: '#fff' },
    list: { padding: spacing.lg, paddingTop: spacing.sm, gap: spacing.md, flexGrow: 1, paddingBottom: 96 },
    baris: { flexDirection: 'row', alignItems: 'center', gap: spacing.sm },
    isiBaris: { flex: 1, gap: 2 },
    footer: { paddingVertical: spacing.lg },
    fab: {
        position: 'absolute', right: spacing.lg, bottom: spacing.xl,
        width: 56, height: 56, borderRadius: 28, backgroundColor: colors.primary,
        alignItems: 'center', justifyContent: 'center', elevation: 4,
        shadowColor: '#000', shadowOpacity: 0.2, shadowRadius: 8, shadowOffset: { width: 0, height: 4 },
    },
});
