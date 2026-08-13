import { useCallback, useState } from 'react';
import { useFocusEffect } from '@react-navigation/native';
import { Alert, Image, Pressable, ScrollView, StyleSheet, Text, View } from 'react-native';
import * as ImagePicker from 'expo-image-picker';
import Ionicons from '@expo/vector-icons/Ionicons';
import { Halaman, Badge, Button, Card, ErrorState, Loading, Notice, Row } from '../../components/ui';
import { Procurement } from '../../api/endpoints';
import { useMuat } from '../../hooks/useMuat';
import { desimal6, rupiah6, tanggalPendek } from '../../utils/format';
import { colors, radius, spacing, typography } from '../../theme';

/** Tahap 3 — daftar pembayaran satu dokumen beserta rekap sisa tagihan. */
export default function PembayaranScreen({ route, navigation }) {
    const { id } = route.params;
    const [proses, setProses] = useState(false);
    const [pesan, setPesan] = useState(null);

    const { data, memuat, error, muatUlang } = useMuat(
        useCallback(() => Procurement.pembayaran.detail(id), [id])
    );

    useFocusEffect(useCallback(() => { muatUlang({ segarkan: true }); }, [muatUlang]));

    if (memuat && !data) return <Halaman judul="Pembayaran" subjudul={data?.kode_pembelian} warna="oranye"><Loading /></Halaman>;
    if (error && !data) {
        return <Halaman judul="Pembayaran" subjudul={data?.kode_pembelian} warna="oranye"><ErrorState pesan={error.message} onCoba={muatUlang} /></Halaman>;
    }

    const jalankan = async (fn, sukses) => {
        setProses(true);
        setPesan(null);
        try {
            await fn();
            setPesan({ tone: 'success', teks: sukses });
            await muatUlang({ segarkan: true });
        } catch (e) {
            setPesan({ tone: 'danger', teks: e.message });
        } finally {
            setProses(false);
        }
    };

    const unggahBukti = async (bayar) => {
        const izin = await ImagePicker.requestCameraPermissionsAsync();
        if (!izin.granted) {
            setPesan({ tone: 'danger', teks: 'Izin kamera ditolak.' });
            return;
        }
        const hasil = await ImagePicker.launchCameraAsync({ mediaTypes: ['images'], quality: 0.6 });
        if (hasil.canceled) return;

        jalankan(
            () => Procurement.pembayaran.lampiran.unggah(id, bayar.id, hasil.assets[0], 'Bukti ' + bayar.no_cek_bg),
            'Bukti pembayaran diunggah.'
        );
    };

    const rekap = data.rekap_pembayaran || {};

    return (
        <Halaman judul="Pembayaran" subjudul={data?.kode_pembelian} warna="oranye">
                {!!pesan && <Notice tone={pesan.tone}>{pesan.teks}</Notice>}

                <Card>
                    <View style={styles.barisAtas}>
                        <Text style={typography.heading}>{data.kode_pembelian}</Text>
                        <Badge label={data.status || '-'} tone="primary" />
                    </View>
                    <Row label="SPBU" value={data.spbu} />
                    <Row label="Tanggal pembelian" value={tanggalPendek(data.tanggal_pembelian)} />
                </Card>

                <Card>
                    <Text style={typography.heading}>Rekap tagihan</Text>
                    <Row label="Tagihan (total amount)" value={rupiah6(rekap.tagihan)} valueStyle={styles.tebal} />
                    <Row label="Sudah dibayar" value={rupiah6(rekap.dibayar)} />
                    <Row label="Dibayar & terkonfirmasi" value={rupiah6(rekap.dibayar_terkonfirmasi)} />
                    <Row label="Sisa" value={rupiah6(rekap.sisa)} valueStyle={styles.tebal} />
                    <Row label="Sisa (versi terkonfirmasi)" value={rupiah6(rekap.sisa_terkonfirmasi)} />
                    <Row label="Menunggu konfirmasi" value={`${rekap.menunggu_konfirmasi ?? 0} transaksi`} />
                </Card>

                <Card>
                    <View style={styles.barisAtas}>
                        <Text style={typography.heading}>Transaksi pembayaran</Text>
                        <Text style={typography.caption}>{rekap.jumlah_transaksi ?? 0}</Text>
                    </View>

                    {(data.pembayaran || []).length === 0 ? (
                        <Text style={typography.muted}>Belum ada pembayaran dicatat.</Text>
                    ) : (
                        data.pembayaran.map((p) => (
                            <View key={p.id} style={styles.kotakBayar}>
                                <View style={styles.barisAtas}>
                                    <Text style={styles.nominal}>{rupiah6(p.total_setoran)}</Text>
                                    <Badge
                                        label={p.terkonfirmasi ? 'Terkonfirmasi' : 'Menunggu'}
                                        tone={p.terkonfirmasi ? 'success' : 'warning'}
                                    />
                                </View>
                                <Row label="Tanggal" value={tanggalPendek(p.tanggal)} />
                                <Row label="No. Cek/BG" value={p.no_cek_bg} />
                                <Row label="Rekening" value={`${p.no_rekening} · ${p.nama_rekening}`} />
                                <Row label="Konfirmasi Admin" value={p.is_confirmation_admin ? 'Sudah' : 'Belum'} />
                                <Row label="Konfirmasi Top" value={p.is_confirmation_top ? 'Sudah' : 'Belum'} />

                                {(p.lampiran || []).length > 0 && (
                                    <ScrollView horizontal showsHorizontalScrollIndicator={false}>
                                        <View style={styles.galeri}>
                                            {p.lampiran.map((l) => (
                                                <Pressable
                                                    key={l.id}
                                                    onLongPress={() => Alert.alert('Hapus bukti?', l.nama_dokumen, [
                                                        { text: 'Batal', style: 'cancel' },
                                                        {
                                                            text: 'Hapus',
                                                            style: 'destructive',
                                                            onPress: () => jalankan(
                                                                () => Procurement.pembayaran.lampiran.hapus(id, p.id, l.id),
                                                                'Bukti dihapus.'
                                                            ),
                                                        },
                                                    ])}
                                                >
                                                    {l.is_gambar
                                                        ? <Image source={{ uri: l.url }} style={styles.thumb} />
                                                        : (
                                                            <View style={[styles.thumb, styles.thumbBerkas]}>
                                                                <Ionicons name="document-text-outline" size={22} color={colors.textMuted} />
                                                            </View>
                                                        )}
                                                </Pressable>
                                            ))}
                                        </View>
                                    </ScrollView>
                                )}

                                <View style={styles.tombolBaris}>
                                    {!p.is_confirmation_admin && (
                                        <Button label="Konfirmasi Admin" variant="outline" style={styles.tombol} loading={proses}
                                            onPress={() => jalankan(() => Procurement.pembayaran.konfirmasi(id, p.id, 'admin', true), 'Dikonfirmasi Admin.')} />
                                    )}
                                    {!p.is_confirmation_top && (
                                        <Button label="Konfirmasi Top" variant="outline" style={styles.tombol} loading={proses}
                                            onPress={() => jalankan(() => Procurement.pembayaran.konfirmasi(id, p.id, 'top', true), 'Dikonfirmasi Top Manajemen.')} />
                                    )}
                                </View>

                                <View style={styles.tombolBaris}>
                                    <Button label="Bukti" icon="📷" variant="ghost" style={styles.tombol}
                                        onPress={() => unggahBukti(p)} />
                                    {!p.terkonfirmasi && (
                                        <>
                                            <Button label="Ubah" variant="ghost" style={styles.tombol}
                                                onPress={() => navigation.navigate('PembayaranForm', { id, bayar: p })} />
                                            <Button label="Hapus" variant="ghost" style={styles.tombol}
                                                onPress={() => Alert.alert('Hapus pembayaran?', rupiah6(p.total_setoran), [
                                                    { text: 'Batal', style: 'cancel' },
                                                    {
                                                        text: 'Hapus',
                                                        style: 'destructive',
                                                        onPress: () => jalankan(() => Procurement.pembayaran.hapus(id, p.id), 'Pembayaran dihapus.'),
                                                    },
                                                ])} />
                                        </>
                                    )}
                                </View>
                            </View>
                        ))
                    )}

                    <Button label="Catat Pembayaran" variant="outline"
                        onPress={() => navigation.navigate('PembayaranForm', { id, sisa: rekap.sisa })} />
                </Card>

                <View style={styles.aksi}>
                    <Button label="Lanjut ke MS2" variant="success" loading={proses}
                        onPress={() => jalankan(() => Procurement.pembayaran.lanjut(id), 'Dilanjutkan ke tahap MS2.')} />
                    <Button label="Tolak & Kembalikan ke Perencanaan" variant="danger" loading={proses}
                        onPress={() => jalankan(() => Procurement.pembayaran.tolak(id), 'Dikembalikan ke Perencanaan.')} />
                </View>
            </Halaman>
    );
}

const styles = StyleSheet.create({
    screen: { flex: 1, backgroundColor: colors.background },
    isi: { padding: spacing.lg, gap: spacing.md, paddingBottom: spacing.xxl },
    barisAtas: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', gap: spacing.sm },
    tebal: { fontWeight: '800', color: colors.primaryDark },
    kotakBayar: {
        borderWidth: 1, borderColor: colors.border, borderRadius: radius.md,
        padding: spacing.md, gap: 2, backgroundColor: colors.background,
    },
    nominal: { fontSize: 16, fontWeight: '800', color: colors.text },
    tombolBaris: { flexDirection: 'row', gap: spacing.sm, marginTop: spacing.sm, flexWrap: 'wrap' },
    tombol: { flex: 1, minWidth: 120 },
    galeri: { flexDirection: 'row', gap: spacing.sm, marginTop: spacing.sm },
    thumb: { width: 60, height: 60, borderRadius: radius.sm, backgroundColor: colors.border },
    thumbBerkas: { alignItems: 'center', justifyContent: 'center' },
    aksi: { gap: spacing.sm },
});
