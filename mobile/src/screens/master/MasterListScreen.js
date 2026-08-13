import { useCallback, useEffect, useState } from 'react';
import { useFocusEffect } from '@react-navigation/native';
import { ActivityIndicator, FlatList, Pressable, RefreshControl, StyleSheet, Text, TextInput, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { Halaman, Card, EmptyState, ErrorState, Loading } from '../../components/ui';
import { Kelola } from '../../api/endpoints';
import { REGISTRY } from '../../master/registry';
import { colors, radius, spacing, typography } from '../../theme';

export default function MasterListScreen({ route, navigation }) {
    const { resource } = route.params;
    const konf = REGISTRY[resource];

    const [cariInput, setCariInput] = useState('');
    const [cari, setCari] = useState('');
    const [items, setItems] = useState([]);
    const [meta, setMeta] = useState(null);
    const [memuat, setMemuat] = useState(true);
    const [menyegarkan, setMenyegarkan] = useState(false);
    const [memuatLagi, setMemuatLagi] = useState(false);
    const [error, setError] = useState(null);

    // debounce pencarian
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
            const hasil = await Kelola.daftar(konf.endpoint, { cari, page, per_page: 25 });
            setItems((lama) => (page === 1 ? hasil.items : [...lama, ...hasil.items]));
            setMeta(hasil.meta);
        } catch (e) {
            setError(e);
        } finally {
            setMemuat(false);
            setMenyegarkan(false);
            setMemuatLagi(false);
        }
    }, [konf.endpoint, cari]);

    useEffect(() => { muat({ page: 1, mode: 'awal' }); }, [muat]);

    // kembali dari form/detail -> daftar harus mencerminkan perubahan
    useFocusEffect(useCallback(() => { muat({ page: 1, mode: 'segar' }); }, [muat]));

    const muatLagi = () => {
        if (memuatLagi || !meta?.has_more) return;
        muat({ page: meta.current_page + 1, mode: 'lagi' });
    };

    return (
        <Halaman judul={konf?.label || 'Master'} warna="ungu" scroll={false} padded={false}>
            <View style={styles.cariBar}>
                <Ionicons name="search" size={18} color={colors.textMuted} />
                <TextInput
                    value={cariInput}
                    onChangeText={setCariInput}
                    placeholder={`Cari ${konf?.label?.toLowerCase() || 'data'}…`}
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

            {memuat && items.length === 0 ? (
                <Loading />
            ) : error && items.length === 0 ? (
                <ErrorState pesan={error.message} onCoba={() => muat({ page: 1, mode: 'awal' })} />
            ) : (
                <FlatList
                    data={items}
                    keyExtractor={(item) => String(item.id)}
                    contentContainerStyle={styles.list}
                    refreshControl={
                        <RefreshControl
                            refreshing={menyegarkan}
                            onRefresh={() => muat({ page: 1, mode: 'segar' })}
                            colors={[colors.primary]}
                            tintColor={colors.primary}
                        />
                    }
                    onEndReachedThreshold={0.4}
                    onEndReached={muatLagi}
                    ListFooterComponent={memuatLagi ? <ActivityIndicator style={styles.footer} color={colors.primary} /> : null}
                    ListEmptyComponent={
                        <EmptyState
                            judul={cari ? 'Tidak ditemukan' : `Belum ada ${konf?.label?.toLowerCase()}`}
                            pesan={cari ? `Tidak ada hasil untuk "${cari}".` : 'Tekan tombol + untuk menambah data.'}
                        />
                    }
                    renderItem={({ item }) => (
                        <Pressable onPress={() => navigation.navigate('MasterDetail', { resource, id: item.id })}>
                            <Card tone={konf?.warna}>
                                <View style={styles.baris}>
                                    <View style={styles.isiBaris}>
                                        <Text style={typography.heading} numberOfLines={1}>{item.judul}</Text>
                                        {!!item.subjudul && (
                                            <Text style={typography.muted} numberOfLines={1}>{item.subjudul}</Text>
                                        )}
                                    </View>
                                    <Ionicons name="chevron-forward" size={18} color={colors.textMuted} />
                                </View>
                            </Card>
                        </Pressable>
                    )}
                />
            )}

            <Pressable
                style={({ pressed }) => [styles.fab, pressed && { opacity: 0.85 }]}
                onPress={() => navigation.navigate('MasterForm', { resource, mode: 'buat' })}
            >
                <Ionicons name="add" size={26} color="#fff" />
            </Pressable>
        </Halaman>
    );
}

const styles = StyleSheet.create({
    screen: { flex: 1, backgroundColor: colors.background },
    cariBar: {
        flexDirection: 'row',
        alignItems: 'center',
        gap: spacing.sm,
        margin: spacing.lg,
        marginBottom: spacing.sm,
        paddingHorizontal: spacing.md,
        minHeight: 44,
        borderRadius: radius.md,
        borderWidth: 1,
        borderColor: colors.border,
        backgroundColor: colors.surface,
    },
    cariInput: { flex: 1, fontSize: 15, color: colors.text, paddingVertical: 0 },
    list: { padding: spacing.lg, paddingTop: spacing.sm, gap: spacing.md, flexGrow: 1, paddingBottom: 96 },
    baris: { flexDirection: 'row', alignItems: 'center', gap: spacing.sm },
    isiBaris: { flex: 1, gap: 2 },
    footer: { paddingVertical: spacing.lg },
    fab: {
        position: 'absolute',
        right: spacing.lg,
        bottom: spacing.xl,
        width: 56,
        height: 56,
        borderRadius: 28,
        backgroundColor: colors.primary,
        alignItems: 'center',
        justifyContent: 'center',
        elevation: 4,
        shadowColor: '#000',
        shadowOpacity: 0.2,
        shadowRadius: 8,
        shadowOffset: { width: 0, height: 4 },
    },
});
