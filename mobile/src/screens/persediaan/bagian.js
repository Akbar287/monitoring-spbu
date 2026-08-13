import { useCallback, useEffect, useState } from 'react';
import { useFocusEffect } from '@react-navigation/native';
import { FlatList, Pressable, RefreshControl, StyleSheet, Text, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { Badge, Card, EmptyState, ErrorState, Loading } from '../../components/ui';
import { colors, radius, spacing, typography } from '../../theme';

/** Pemilih segmen (mis. Belum dikonfirmasi / Riwayat). */
export function Segmen({ nilai, onGanti, pilihan }) {
    return (
        <View style={styles.segmen}>
            {pilihan.map((p) => (
                <Pressable
                    key={p.key}
                    onPress={() => onGanti(p.key)}
                    style={[styles.segmenItem, nilai === p.key && styles.segmenAktif]}
                >
                    <Text style={[styles.segmenTeks, nilai === p.key && styles.segmenTeksAktif]}>{p.label}</Text>
                </Pressable>
            ))}
        </View>
    );
}

/**
 * Daftar berpaginasi yang dipakai seluruh submenu Persediaan.
 * `ambil(page)` harus mengembalikan { items, meta }.
 */
export function Daftar({ ambil, onTekan, kosong, tone }) {
    const [items, setItems] = useState([]);
    const [meta, setMeta] = useState(null);
    const [memuat, setMemuat] = useState(true);
    const [menyegarkan, setMenyegarkan] = useState(false);
    const [error, setError] = useState(null);

    const muat = useCallback(async ({ page = 1, segar = false } = {}) => {
        if (segar) setMenyegarkan(true); else if (page === 1) setMemuat(true);
        setError(null);
        try {
            const hasil = await ambil(page);
            setItems((lama) => (page === 1 ? hasil.items : [...lama, ...hasil.items]));
            setMeta(hasil.meta);
        } catch (e) {
            setError(e);
        } finally {
            setMemuat(false);
            setMenyegarkan(false);
        }
    }, [ambil]);

    useEffect(() => { muat({ page: 1 }); }, [muat]);
    useFocusEffect(useCallback(() => { muat({ page: 1, segar: true }); }, [muat]));

    if (memuat && items.length === 0) return <Loading />;
    if (error && items.length === 0) return <ErrorState pesan={error.message} onCoba={() => muat({ page: 1 })} />;

    return (
        <FlatList
            data={items}
            keyExtractor={(it) => String(it.id)}
            contentContainerStyle={styles.list}
            refreshControl={
                <RefreshControl refreshing={menyegarkan} onRefresh={() => muat({ page: 1, segar: true })}
                    colors={[colors.primary]} tintColor={colors.primary} />
            }
            onEndReachedThreshold={0.4}
            onEndReached={() => meta?.has_more && muat({ page: meta.current_page + 1 })}
            ListEmptyComponent={<EmptyState judul={kosong?.judul || 'Belum ada data'} pesan={kosong?.pesan} />}
            renderItem={({ item }) => (
                <Pressable onPress={() => onTekan?.(item)}>
                    <Card tone={tone}>
                        <View style={styles.baris}>
                            <View style={styles.isi}>
                                <Text style={typography.heading} numberOfLines={1}>{item.judul}</Text>
                                {!!item.subjudul && <Text style={typography.muted} numberOfLines={1}>{item.subjudul}</Text>}
                            </View>
                            {typeof item.is_confirmation === 'boolean' && (
                                <Badge
                                    label={item.is_confirmation ? 'Selesai' : 'Menunggu'}
                                    tone={item.is_confirmation ? 'hijau' : 'oranye'}
                                />
                            )}
                            <Ionicons name="chevron-forward" size={18} color={colors.textMuted} />
                        </View>
                    </Card>
                </Pressable>
            )}
        />
    );
}

/** Tombol mengambang tambah data. */
export function Fab({ onPress, ikon = 'add' }) {
    return (
        <Pressable style={({ pressed }) => [styles.fab, pressed && { opacity: 0.85 }]} onPress={onPress}>
            <Ionicons name={ikon} size={26} color="#fff" />
        </Pressable>
    );
}

const styles = StyleSheet.create({
    segmen: {
        flexDirection: 'row', gap: spacing.xs, padding: 4,
        backgroundColor: colors.border + '55', borderRadius: radius.pill, marginBottom: spacing.sm,
    },
    segmenItem: { flex: 1, paddingVertical: 8, borderRadius: radius.pill, alignItems: 'center' },
    segmenAktif: { backgroundColor: colors.surface },
    segmenTeks: { fontSize: 13, fontWeight: '700', color: colors.textMuted },
    segmenTeksAktif: { color: colors.primary },

    list: { gap: spacing.md, paddingBottom: 96, flexGrow: 1 },
    baris: { flexDirection: 'row', alignItems: 'center', gap: spacing.sm },
    isi: { flex: 1, gap: 2 },

    fab: {
        position: 'absolute', right: spacing.lg, bottom: spacing.xl,
        width: 56, height: 56, borderRadius: 28, backgroundColor: colors.primary,
        alignItems: 'center', justifyContent: 'center', elevation: 4,
        shadowColor: '#000', shadowOpacity: 0.2, shadowRadius: 8, shadowOffset: { width: 0, height: 4 },
    },
});
