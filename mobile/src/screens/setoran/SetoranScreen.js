import { useCallback, useEffect, useState } from 'react';
import { useFocusEffect } from '@react-navigation/native';
import { FlatList, Pressable, RefreshControl, ScrollView, StyleSheet, Text, TextInput, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { Badge, Button, Card, EmptyState, ErrorState, Halaman, Loading, Row } from '../../components/ui';
import SelectField from '../../components/SelectField';
import DateField from '../../components/DateField';
import Paginasi from '../../components/Paginasi';
import { Fab } from '../persediaan/bagian';
import { Setoran } from '../../api/endpoints';
import { useOpsi } from '../../master/OpsiContext';
import { rupiah, tanggalPendek } from '../../utils/format';
import { colors, radius, spacing, typography } from '../../theme';

const TAHAP = [
    { key: 'semua', label: 'Semua' },
    { key: 'open', label: 'Open' },
    { key: 'penitipan', label: 'Penitipan' },
    { key: 'closing', label: 'Closing' },
    { key: 'bank', label: 'Bank' },
    { key: 'selesai', label: 'Selesai' },
];

const awalBulan = () => { const d = new Date(); return new Date(d.getFullYear(), d.getMonth(), 1).toISOString().slice(0, 10); };
const akhirBulan = () => { const d = new Date(); return new Date(d.getFullYear(), d.getMonth() + 1, 0).toISOString().slice(0, 10); };

/**
 * Daftar setoran: search bar, saringan tahap, filter lanjutan, dan paginasi
 * bernomor — menyamai halaman web.
 */
export default function SetoranScreen({ navigation }) {
    const { ambil } = useOpsi();

    const [cariInput, setCariInput] = useState('');
    const [cari, setCari] = useState('');
    const [tahap, setTahap] = useState('semua');
    const [halaman, setHalaman] = useState(1);
    const [bukaFilter, setBukaFilter] = useState(false);
    const [filter, setFilter] = useState({
        dari: awalBulan(), sampai: akhirBulan(),
        jam_kerja_id: '', spbu_id: '', nama_bank: '', no_referensi: '', metode_transfer: '',
    });

    const [data, setData] = useState(null);
    const [ringkas, setRingkas] = useState(null);
    const [memuat, setMemuat] = useState(true);
    const [menyegarkan, setMenyegarkan] = useState(false);
    const [error, setError] = useState(null);

    // debounce pencarian
    useEffect(() => {
        const t = setTimeout(() => { setCari(cariInput.trim()); setHalaman(1); }, 400);
        return () => clearTimeout(t);
    }, [cariInput]);

    const muat = useCallback(async (page, segar = false) => {
        segar ? setMenyegarkan(true) : setMemuat(true);
        setError(null);
        try {
            const params = { page, per_page: 20, tahap, cari: cari || undefined };
            Object.entries(filter).forEach(([k, v]) => { if (v) params[k] = v; });
            setData(await Setoran.daftar(params));
        } catch (e) {
            setError(e);
        } finally {
            setMemuat(false);
            setMenyegarkan(false);
        }
    }, [tahap, cari, filter]);

    useEffect(() => { muat(halaman); }, [muat, halaman]);
    useEffect(() => { setHalaman(1); }, [tahap, filter]);
    useFocusEffect(useCallback(() => {
        muat(halaman, true);
        Setoran.ringkasan().then(setRingkas).catch(() => setRingkas(null));
    }, [muat, halaman]));

    const jumlahFilterAktif = Object.entries(filter)
        .filter(([k, v]) => v && !(k === 'dari' && v === awalBulan()) && !(k === 'sampai' && v === akhirBulan())).length;

    return (
        <Halaman judul="Setoran" subjudul={ringkas?.spbu} warna="hijau" kembali={false} scroll={false}>
            {!!ringkas && (
                <Card gradien="hijau">
                    <Text style={styles.labelTerang}>Penjualan bulan ini</Text>
                    <Text style={styles.angka}>{rupiah(ringkas.bulan_ini.total_penjualan)}</Text>
                    <View style={styles.pemisah} />
                    <Row terang label="Uang kotak" value={rupiah(ringkas.bulan_ini.total_uang_kotak)} />
                    <Row terang label="Selisih" value={rupiah(ringkas.bulan_ini.selisih)} />
                    <Row terang label="Menunggu persetujuan" value={`${ringkas.menunggu_persetujuan} shift`} />
                </Card>
            )}

            <View style={styles.cariBar}>
                <Ionicons name="search" size={18} color={colors.textMuted} />
                <TextInput
                    value={cariInput}
                    onChangeText={setCariInput}
                    placeholder="Cari tanggal, shift, nozzle, bank, no. referensi…"
                    placeholderTextColor={colors.textMuted}
                    style={styles.cariInput}
                    autoCorrect={false}
                />
                {cariInput.length > 0 && (
                    <Pressable onPress={() => setCariInput('')} hitSlop={10}>
                        <Ionicons name="close-circle" size={18} color={colors.textMuted} />
                    </Pressable>
                )}
                <Pressable onPress={() => setBukaFilter((v) => !v)} hitSlop={10}>
                    <Ionicons name="options-outline" size={20} color={jumlahFilterAktif ? colors.primary : colors.textMuted} />
                </Pressable>
            </View>

            <ScrollView horizontal showsHorizontalScrollIndicator={false} style={styles.chipBaris}>
                {TAHAP.map((t) => (
                    <Pressable key={t.key} onPress={() => setTahap(t.key)}
                        style={[styles.chip, tahap === t.key && styles.chipAktif]}>
                        <Text style={[styles.chipTeks, tahap === t.key && styles.chipTeksAktif]}>
                            {t.label}
                            {ringkas?.tahap?.[t.key] ? ` (${ringkas.tahap[t.key].jumlah})` : ''}
                        </Text>
                    </Pressable>
                ))}
            </ScrollView>

            {bukaFilter && (
                <Card tone="abu">
                    <Text style={typography.heading}>Filter</Text>
                    <DateField label="Dari tanggal" value={filter.dari} onChange={(v) => setFilter((f) => ({ ...f, dari: v }))} />
                    <DateField label="Sampai tanggal" value={filter.sampai} onChange={(v) => setFilter((f) => ({ ...f, sampai: v }))} />
                    <SelectField label="Jam kerja" value={filter.jam_kerja_id}
                        onChange={(v) => setFilter((f) => ({ ...f, jam_kerja_id: v }))} options={ambil('jam_kerja')} />
                    <SelectField label="SPBU" value={filter.spbu_id}
                        onChange={(v) => setFilter((f) => ({ ...f, spbu_id: v }))} options={ambil('spbu')} />
                    <Button label="Bersihkan filter" variant="ghost"
                        onPress={() => setFilter({ dari: awalBulan(), sampai: akhirBulan(), jam_kerja_id: '', spbu_id: '', nama_bank: '', no_referensi: '', metode_transfer: '' })} />
                </Card>
            )}

            {memuat && !data ? <Loading />
                : error && !data ? <ErrorState pesan={error.message} onCoba={() => muat(halaman)} />
                : (
                    <FlatList
                        data={data?.items ?? []}
                        keyExtractor={(i) => String(i.id)}
                        contentContainerStyle={styles.list}
                        refreshControl={
                            <RefreshControl refreshing={menyegarkan} onRefresh={() => muat(halaman, true)}
                                colors={[colors.primary]} tintColor={colors.primary} />
                        }
                        ListEmptyComponent={
                            <EmptyState judul="Tidak ada setoran"
                                pesan={cari ? `Tidak ada hasil untuk "${cari}".` : 'Tekan + untuk membuka setoran baru.'} />
                        }
                        ListFooterComponent={<Paginasi meta={data?.meta} onPindah={setHalaman} sibuk={memuat} />}
                        renderItem={({ item }) => (
                            <Pressable onPress={() => navigation.navigate('SetoranDetail', { id: item.id })}>
                                <Card tone={item.tahap === 'selesai' ? 'abu' : item.tahap === 'bank' ? 'hijau' : 'oranye'}>
                                    <View style={styles.baris}>
                                        <View style={styles.isi}>
                                            <Text style={typography.heading}>{tanggalPendek(item.tanggal)} · {item.jam_kerja}</Text>
                                            <Text style={typography.muted} numberOfLines={1}>{item.status}</Text>
                                        </View>
                                        <Ionicons name="chevron-forward" size={18} color={colors.textMuted} />
                                    </View>
                                    <Row label="Penjualan" value={rupiah(item.total_penjualan)} />
                                    <Row label="Uang kotak" value={rupiah(item.total_uang_kotak)} />
                                    <View style={styles.badgeBaris}>
                                        <Badge label={item.is_accepted_admin ? 'Admin ✓' : 'Admin −'} tone={item.is_accepted_admin ? 'hijau' : 'abu'} />
                                        <Badge label={item.is_accepted_top ? 'Top ✓' : 'Top −'} tone={item.is_accepted_top ? 'hijau' : 'abu'} />
                                    </View>
                                </Card>
                            </Pressable>
                        )}
                    />
                )}

            <Fab onPress={() => navigation.navigate('SetoranForm')} />
        </Halaman>
    );
}

const styles = StyleSheet.create({
    labelTerang: { fontSize: 13, color: 'rgba(255,255,255,0.9)', fontWeight: '600' },
    angka: { fontSize: 24, fontWeight: '800', color: '#fff' },
    pemisah: { height: 1, backgroundColor: 'rgba(255,255,255,0.25)', marginVertical: spacing.sm },
    cariBar: {
        flexDirection: 'row', alignItems: 'center', gap: spacing.sm, paddingHorizontal: spacing.md,
        minHeight: 44, borderRadius: radius.md, borderWidth: 1, borderColor: colors.border,
        backgroundColor: colors.surface,
    },
    cariInput: { flex: 1, fontSize: 14, color: colors.text, paddingVertical: 0 },
    chipBaris: { flexGrow: 0, marginVertical: spacing.xs },
    chip: {
        paddingHorizontal: spacing.md, paddingVertical: 7, borderRadius: radius.pill,
        borderWidth: 1, borderColor: colors.border, backgroundColor: colors.surface, marginRight: spacing.sm,
    },
    chipAktif: { backgroundColor: colors.primary, borderColor: colors.primary },
    chipTeks: { fontSize: 12, fontWeight: '700', color: colors.textMuted },
    chipTeksAktif: { color: '#fff' },
    list: { gap: spacing.md, paddingBottom: 96, flexGrow: 1 },
    baris: { flexDirection: 'row', alignItems: 'center', gap: spacing.sm },
    isi: { flex: 1, gap: 2 },
    badgeBaris: { flexDirection: 'row', gap: spacing.sm, marginTop: spacing.xs },
});
