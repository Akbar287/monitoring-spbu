import { useCallback, useState } from 'react';
import { useFocusEffect } from '@react-navigation/native';
import { Alert, Image, Pressable, ScrollView, StyleSheet, Text, View } from 'react-native';
import { Halaman, Badge, Button, Card, ErrorState, Loading, Notice, Row } from '../../components/ui';
import { Procurement } from '../../api/endpoints';
import { useMuat } from '../../hooks/useMuat';
import { desimal6, tanggalPendek } from '../../utils/format';
import { colors, radius, spacing, typography } from '../../theme';

/** Tahap 5 — pengiriman & dokumen LO. */
export default function DeliveryScreen({ route, navigation }) {
    const { id } = route.params;
    const [proses, setProses] = useState(false);
    const [pesan, setPesan] = useState(null);

    const { data, memuat, error, muatUlang } = useMuat(
        useCallback(() => Procurement.delivery.detail(id), [id])
    );

    useFocusEffect(useCallback(() => { muatUlang({ segarkan: true }); }, [muatUlang]));

    if (memuat && !data) return <Halaman judul="Detail Pengiriman" subjudul={data?.no_pol} warna="oranye"><Loading /></Halaman>;
    if (error && !data) {
        return <Halaman judul="Detail Pengiriman" subjudul={data?.no_pol} warna="oranye"><ErrorState pesan={error.message} onCoba={muatUlang} /></Halaman>;
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

    const kal = data.kalkulasi || {};

    return (
        <Halaman judul="Detail Pengiriman" subjudul={data?.no_pol} warna="oranye">
                {!!pesan && <Notice tone={pesan.tone}>{pesan.teks}</Notice>}

                <Card>
                    <View style={styles.barisAtas}>
                        <Text style={typography.heading}>{data.no_do || 'Tanpa DO'}</Text>
                        <Badge label={data.is_done ? 'Selesai' : 'Berjalan'} tone={data.is_done ? 'success' : 'warning'} />
                    </View>
                    <Row label="No. Polisi" value={data.no_pol} />
                    <Row label="Tanggal" value={tanggalPendek(data.tanggal)} />
                    <Row label="Supir" value={(data.supir || []).join(', ') || '-'} />
                    <Row label="Pembawa" value={data.pembawa} />
                    <Row label="Konfirmasi Admin" value={data.is_confirmation_admin ? 'Sudah' : 'Belum'} />
                    <Row label="Konfirmasi Top" value={data.is_confirmation_top ? 'Sudah' : 'Belum'} />
                    {!!data.catatan && <Row label="Catatan" value={data.catatan} />}
                </Card>

                <Card>
                    <View style={styles.barisAtas}>
                        <Text style={typography.heading}>Dokumen LO</Text>
                        <Text style={typography.caption}>{kal.jumlah_lo ?? 0} berkas</Text>
                    </View>

                    {(data.lo || []).length === 0 ? (
                        <Text style={typography.muted}>Belum ada dokumen LO diunggah.</Text>
                    ) : (
                        data.lo.map((lo) => (
                            <View key={lo.id} style={styles.kotak}>
                                <View style={styles.barisAtas}>
                                    <Text style={styles.produk}>{lo.produk}</Text>
                                    {lo.sudah_diterima && <Badge label="Sudah diterima" tone="success" />}
                                </View>
                                <View style={styles.loBaris}>
                                    <Image source={{ uri: lo.url }} style={styles.thumb} />
                                    <View style={styles.loInfo}>
                                        <Row label="Kuantitas" value={`${desimal6(lo.jumlah)} ${lo.satuan_jumlah}`} />
                                        <Row label="No. Faktur" value={lo.no_faktur} />
                                        <Row label="No. LO" value={lo.no_lo} />
                                        <Row label="Diunggah" value={tanggalPendek(lo.tanggal_upload)} />
                                    </View>
                                </View>
                                {!lo.sudah_diterima && (
                                    <Pressable
                                        hitSlop={8}
                                        style={styles.hapus}
                                        onPress={() => Alert.alert('Hapus LO?', `${lo.no_lo} akan dihapus.`, [
                                            { text: 'Batal', style: 'cancel' },
                                            {
                                                text: 'Hapus',
                                                style: 'destructive',
                                                onPress: () => jalankan(() => Procurement.delivery.lo.hapus(id, lo.id), 'Dokumen LO dihapus.'),
                                            },
                                        ])}
                                    >
                                        <Text style={styles.hapusTeks}>Hapus LO</Text>
                                    </Pressable>
                                )}
                            </View>
                        ))
                    )}

                    <Row label="Total kuantitas LO" value={desimal6(kal.total_kuantitas)} valueStyle={styles.tebal} />

                    {!data.is_done && (
                        <Button label="Unggah Dokumen LO" variant="outline"
                            onPress={() => navigation.navigate('LoForm', { id })} />
                    )}
                </Card>

                {!data.is_done && (
                    <View style={styles.aksi}>
                        {!data.is_confirmation_admin && (
                            <Button label="Konfirmasi Admin" variant="outline" loading={proses}
                                onPress={() => jalankan(() => Procurement.delivery.konfirmasi(id, 'admin', true), 'Dikonfirmasi Admin.')} />
                        )}
                        {!data.is_confirmation_top && (
                            <Button label="Konfirmasi Top Manajemen" variant="outline" loading={proses}
                                onPress={() => jalankan(() => Procurement.delivery.konfirmasi(id, 'top', true), 'Dikonfirmasi Top Manajemen.')} />
                        )}
                        <Button label="Tandai Pengiriman Selesai" variant="success" loading={proses}
                            onPress={() => jalankan(() => Procurement.delivery.selesai(id), 'Pengiriman selesai.')} />
                    </View>
                )}

                {data.is_done && (
                    <Notice tone="success">
                        Pengiriman selesai. Dokumen LO siap dipakai pada modul Penerimaan BBM.
                    </Notice>
                )}
            </Halaman>
    );
}

const styles = StyleSheet.create({
    screen: { flex: 1, backgroundColor: colors.background },
    isi: { padding: spacing.lg, gap: spacing.md, paddingBottom: spacing.xxl },
    barisAtas: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', gap: spacing.sm },
    kotak: { borderWidth: 1, borderColor: colors.border, borderRadius: radius.md, padding: spacing.md, gap: spacing.xs, backgroundColor: colors.background },
    loBaris: { flexDirection: 'row', gap: spacing.md },
    loInfo: { flex: 1 },
    thumb: { width: 72, height: 72, borderRadius: radius.sm, backgroundColor: colors.border },
    produk: { fontSize: 15, fontWeight: '700', color: colors.text },
    tebal: { fontWeight: '800', color: colors.primaryDark },
    hapus: { alignSelf: 'flex-end' },
    hapusTeks: { color: colors.danger, fontSize: 12, fontWeight: '600' },
    aksi: { gap: spacing.sm },
});
