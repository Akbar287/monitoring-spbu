import { useCallback } from 'react';
import { useFocusEffect } from '@react-navigation/native';
import { Pressable, StyleSheet, Text, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { Card, ErrorState, Halaman, Loading, Row, SectionTitle } from '../../components/ui';
import { Persediaan } from '../../api/endpoints';
import { useMuat } from '../../hooks/useMuat';
import { liter } from '../../utils/format';
import { aksen, colors, radius, spacing, typography } from '../../theme';

const MENU = [
    { key: 'Monitoring', label: 'Monitoring Stok', ikon: 'analytics-outline', warna: 'indigo', ket: 'Saldo per produk & dombak' },
    { key: 'Penerimaan', label: 'Penerimaan BBM', ikon: 'download-outline', warna: 'hijau', ket: 'Bongkar muatan LO' },
    { key: 'StokTaking', label: 'Stok Taking', ikon: 'speedometer-outline', warna: 'oranye', ket: 'Pengukuran fisik dombak' },
    { key: 'StokTransfer', label: 'Stok Transfer', ikon: 'swap-horizontal-outline', warna: 'ungu', ket: 'Pindah antar dombak' },
    { key: 'StokSummary', label: 'Stok Summary', ikon: 'document-text-outline', warna: 'biru', ket: 'Rekap gerakan harian' },
    { key: 'Totalisator', label: 'Totalisator', ikon: 'speedometer', warna: 'pink', ket: 'Angka meter nozzle per shift' },
    { key: 'Setoran', label: 'Setoran', ikon: 'cash-outline', warna: 'hijau', ket: 'Open → Closing → Setor bank' },
];

export default function PersediaanHomeScreen({ navigation }) {
    const { data, memuat, menyegarkan, error, segarkan, muatUlang } = useMuat(
        useCallback(() => Persediaan.ringkasan(), [])
    );

    useFocusEffect(useCallback(() => { muatUlang({ segarkan: true }); }, [muatUlang]));

    const bungkus = (isi) => (
        <Halaman judul="Manajemen Persediaan" subjudul={data?.spbu} warna="teal" kembali={false}
            onRefresh={segarkan} refreshing={menyegarkan}>
            {isi}
        </Halaman>
    );

    if (memuat && !data) return bungkus(<Loading />);
    if (error && !data) return bungkus(<ErrorState pesan={error.message} onCoba={muatUlang} />);

    const antre = data?.menunggu_konfirmasi ?? {};

    return bungkus(
        <>
            <Card gradien="teal">
                <Text style={styles.labelTerang}>Total stok tercatat</Text>
                <Text style={styles.angkaBesar}>{liter(data?.total_stok)}</Text>
                <View style={styles.pemisahTerang} />
                <Row terang label="Menunggu konfirmasi" value={`${antre.total ?? 0} dokumen`} />
                <Row terang label="Losses hari ini" value={`− ${liter(data?.losses_hari_ini?.kurang)}`} />
                <Row terang label="Profit hari ini" value={`+ ${liter(data?.losses_hari_ini?.lebih)}`} />
            </Card>

            <View style={styles.grid}>
                {MENU.map((m) => {
                    const a = aksen[m.warna] || aksen.indigo;
                    const badge = m.key === 'StokTaking' ? antre.stok_taking
                        : m.key === 'Penerimaan' ? antre.penerimaan
                        : m.key === 'StokTransfer' ? antre.transfer : 0;

                    return (
                        <Pressable
                            key={m.key}
                            style={({ pressed }) => [styles.tile, pressed && { opacity: 0.75 }]}
                            onPress={() => navigation.navigate(m.key)}
                        >
                            <View style={[styles.ikonKotak, { backgroundColor: a.lembut }]}>
                                <Ionicons name={m.ikon} size={22} color={a.solid} />
                            </View>
                            <Text style={styles.tileJudul}>{m.label}</Text>
                            <Text style={typography.caption} numberOfLines={2}>{m.ket}</Text>
                            {badge > 0 && (
                                <View style={[styles.badge, { backgroundColor: aksen.merah.solid }]}>
                                    <Text style={styles.badgeTeks}>{badge}</Text>
                                </View>
                            )}
                        </Pressable>
                    );
                })}
            </View>

            <SectionTitle>Saldo per produk</SectionTitle>
            {(data?.stok || []).map((s) => (
                <Card key={s.produk_id} tone={Number(s.stok) > 0 ? 'teal' : 'abu'}>
                    <View style={styles.baris}>
                        <Text style={typography.heading}>{s.produk}</Text>
                        <Text style={styles.stok}>{liter(s.stok)}</Text>
                    </View>
                    {s.dombak.length === 0
                        ? <Text style={typography.caption}>Belum ada dombak terhubung.</Text>
                        : s.dombak.map((d) => <Row key={d.dombak} label={d.dombak} value={liter(d.stok)} />)}
                </Card>
            ))}
        </>
    );
}

const styles = StyleSheet.create({
    labelTerang: { fontSize: 13, color: 'rgba(255,255,255,0.9)', fontWeight: '600' },
    angkaBesar: { fontSize: 26, fontWeight: '800', color: '#fff' },
    pemisahTerang: { height: 1, backgroundColor: 'rgba(255,255,255,0.25)', marginVertical: spacing.sm },
    grid: { flexDirection: 'row', flexWrap: 'wrap', gap: spacing.md },
    tile: {
        width: '47.5%', backgroundColor: colors.surface, borderRadius: radius.lg,
        borderWidth: 1, borderColor: colors.border, padding: spacing.lg, gap: 2,
    },
    ikonKotak: {
        width: 40, height: 40, borderRadius: radius.md,
        alignItems: 'center', justifyContent: 'center', marginBottom: spacing.xs,
    },
    tileJudul: { fontSize: 15, fontWeight: '700', color: colors.text },
    badge: {
        position: 'absolute', top: spacing.md, right: spacing.md,
        minWidth: 22, height: 22, borderRadius: 11, alignItems: 'center', justifyContent: 'center',
        paddingHorizontal: 6,
    },
    badgeTeks: { color: '#fff', fontSize: 11, fontWeight: '800' },
    baris: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
    stok: { fontSize: 15, fontWeight: '800', color: colors.text },
});
