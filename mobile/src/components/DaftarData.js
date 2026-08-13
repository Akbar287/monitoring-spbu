import { useCallback, useEffect, useState } from 'react';
import { useFocusEffect } from '@react-navigation/native';
import { FlatList, Pressable, RefreshControl, StyleSheet, Text, TextInput, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { Card, EmptyState, ErrorState, Loading } from './ui';
import Paginasi from './Paginasi';
import { colors, radius, spacing, typography } from '../theme';

/**
 * Komponen daftar tunggal untuk SELURUH layar list aplikasi.
 *
 * Menyatukan tiga hal yang harus ada di setiap daftar:
 *   1. search bar (debounce 400 ms, dikirim ke server sebagai `cari`)
 *   2. panel filter yang bisa dibuka-tutup
 *   3. paginasi bernomor (bukan gulir tak berujung)
 *
 * `ambil(page, cari)` wajib mengembalikan { items, meta } dari ApiResponse::paginated.
 */
export default function DaftarData({
    ambil,
    onTekan,
    render,
    kosong,
    tone,
    cariPlaceholder,
    filter,
    jumlahFilterAktif = 0,
    kepala,
    perPage = 20,
    segarkanSaatFokus = true,
}) {
    const [cariInput, setCariInput] = useState('');
    const [cari, setCari] = useState('');
    const [halaman, setHalaman] = useState(1);
    const [bukaFilter, setBukaFilter] = useState(false);

    const [data, setData] = useState(null);
    const [memuat, setMemuat] = useState(true);
    const [menyegarkan, setMenyegarkan] = useState(false);
    const [error, setError] = useState(null);

    // debounce pencarian; setiap kata kunci baru kembali ke halaman 1
    useEffect(() => {
        const t = setTimeout(() => { setCari(cariInput.trim()); setHalaman(1); }, 400);
        return () => clearTimeout(t);
    }, [cariInput]);

    const muat = useCallback(async (page, segar = false) => {
        segar ? setMenyegarkan(true) : setMemuat(true);
        setError(null);
        try {
            setData(await ambil(page, cari || undefined, perPage));
        } catch (e) {
            setError(e);
        } finally {
            setMemuat(false);
            setMenyegarkan(false);
        }
    }, [ambil, cari, perPage]);

    useEffect(() => { muat(halaman); }, [muat, halaman]);
    // filter berubah dari luar -> kembali ke halaman 1
    useEffect(() => { setHalaman(1); }, [ambil]);

    useFocusEffect(useCallback(() => {
        if (segarkanSaatFokus) muat(halaman, true);
    }, [segarkanSaatFokus, muat, halaman]));

    const isi = () => {
        if (memuat && !data) return <Loading />;
        if (error && !data) return <ErrorState pesan={error.message} onCoba={() => muat(halaman)} />;

        return (
            <FlatList
                data={data?.items ?? []}
                keyExtractor={(it, i) => String(it.id ?? i)}
                contentContainerStyle={styles.list}
                refreshControl={
                    <RefreshControl refreshing={menyegarkan} onRefresh={() => muat(halaman, true)}
                        colors={[colors.primary]} tintColor={colors.primary} />
                }
                ListEmptyComponent={
                    <EmptyState
                        judul={cari ? 'Tidak ditemukan' : (kosong?.judul || 'Belum ada data')}
                        pesan={cari ? `Tidak ada hasil untuk "${cari}".` : kosong?.pesan}
                    />
                }
                ListFooterComponent={<Paginasi meta={data?.meta} onPindah={setHalaman} sibuk={memuat} />}
                renderItem={({ item }) => (render
                    ? render(item)
                    : (
                        <Pressable onPress={() => onTekan?.(item)}>
                            <Card tone={tone}>
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
                    ))}
            />
        );
    };

    return (
        <>
            {kepala}

            {!!cariPlaceholder && (
                <View style={styles.cariBar}>
                    <Ionicons name="search" size={18} color={colors.textMuted} />
                    <TextInput
                        value={cariInput}
                        onChangeText={setCariInput}
                        placeholder={cariPlaceholder}
                        placeholderTextColor={colors.textMuted}
                        style={styles.cariInput}
                        autoCorrect={false}
                    />
                    {cariInput.length > 0 && (
                        <Pressable onPress={() => setCariInput('')} hitSlop={10}>
                            <Ionicons name="close-circle" size={18} color={colors.textMuted} />
                        </Pressable>
                    )}
                    {!!filter && (
                        <Pressable onPress={() => setBukaFilter((v) => !v)} hitSlop={10}>
                            <Ionicons
                                name="options-outline"
                                size={20}
                                color={jumlahFilterAktif ? colors.primary : colors.textMuted}
                            />
                        </Pressable>
                    )}
                </View>
            )}

            {!!filter && bukaFilter && <Card tone="abu">{filter}</Card>}

            {isi()}
        </>
    );
}

const styles = StyleSheet.create({
    cariBar: {
        flexDirection: 'row', alignItems: 'center', gap: spacing.sm, paddingHorizontal: spacing.md,
        minHeight: 44, borderRadius: radius.md, borderWidth: 1, borderColor: colors.border,
        backgroundColor: colors.surface, marginBottom: spacing.sm,
    },
    cariInput: { flex: 1, fontSize: 14, color: colors.text, paddingVertical: 0 },
    list: { gap: spacing.md, paddingBottom: 96, flexGrow: 1 },
    baris: { flexDirection: 'row', alignItems: 'center', gap: spacing.sm },
    isiBaris: { flex: 1, gap: 2 },
});
