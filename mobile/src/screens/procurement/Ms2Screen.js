import { useCallback, useState } from 'react';
import { useFocusEffect } from '@react-navigation/native';
import { Alert, ScrollView, StyleSheet, Text, View } from 'react-native';
import { Halaman, Badge, Button, Card, ErrorState, Loading, Notice, Row } from '../../components/ui';
import { Procurement } from '../../api/endpoints';
import { useMuat } from '../../hooks/useMuat';
import { desimal6, rupiah6, tanggalPendek } from '../../utils/format';
import { colors, radius, spacing, typography } from '../../theme';

/** Tahap 4 — detail dokumen MS2 (alokasi stok Pertamina). */
export default function Ms2Screen({ route, navigation }) {
    const { id } = route.params;
    const [proses, setProses] = useState(false);
    const [pesan, setPesan] = useState(null);

    const { data, memuat, error, muatUlang } = useMuat(
        useCallback(() => Procurement.ms2.detail(id), [id])
    );

    useFocusEffect(useCallback(() => { muatUlang({ segarkan: true }); }, [muatUlang]));

    if (memuat && !data) return <Halaman judul="Detail MS2" subjudul={data?.kode_sms} warna="oranye"><Loading /></Halaman>;
    if (error && !data) {
        return <Halaman judul="Detail MS2" subjudul={data?.kode_sms} warna="oranye"><ErrorState pesan={error.message} onCoba={muatUlang} /></Halaman>;
    }

    const kal = data.kalkulasi || {};

    return (
        <Halaman judul="Detail MS2" subjudul={data?.kode_sms} warna="oranye">
                {!!pesan && <Notice tone={pesan.tone}>{pesan.teks}</Notice>}

                <Card>
                    <View style={styles.barisAtas}>
                        <Text style={typography.heading}>{data.kode_sms}</Text>
                        <Badge label={data.is_done ? 'Sudah dikirim' : 'Menunggu'} tone={data.is_done ? 'success' : 'warning'} />
                    </View>
                    <Row label="Tanggal" value={tanggalPendek(data.tanggal)} />
                    <Row label="Dibuat oleh" value={data.pembuat} />
                    <Row label="Jumlah produk" value={String(data.jumlah_produk ?? 0)} />
                </Card>

                <Card>
                    <Text style={typography.heading}>Produk dialokasikan</Text>
                    {(data.detail || []).map((b) => (
                        <View key={b.id} style={styles.kotak}>
                            <View style={styles.barisAtas}>
                                <Text style={styles.produk}>{b.produk}</Text>
                                {b.is_delivery && <Badge label="Dikirim" tone="success" />}
                            </View>
                            <Row label="Dokumen" value={b.kode_pembelian} />
                            <Row label="Kuantitas" value={`${desimal6(b.jumlah)} ${b.satuan_jumlah}`} />
                            <Row label="Rit" value={desimal6(b.rit)} />
                            <Row label="Shift" value={b.jam_kerja} />
                            <Row label="Nilai" value={rupiah6(b.total_harga)} />
                        </View>
                    ))}
                </Card>

                <Card>
                    <Text style={typography.heading}>Kalkulasi</Text>
                    <Row label="Total kuantitas" value={desimal6(kal.total_kuantitas)} />
                    <Row label="Total rit" value={desimal6(kal.total_rit)} />
                    <Row label="Total nilai" value={rupiah6(kal.total_nilai)} valueStyle={styles.tebal} />
                </Card>

                {(data.pengiriman || []).length > 0 && (
                    <Card>
                        <Text style={typography.heading}>Pengiriman terkait</Text>
                        {data.pengiriman.map((p) => (
                            <Button
                                key={p.pengiriman_id}
                                label={`${p.no_do || 'Tanpa DO'} · ${p.no_pol}`}
                                variant="ghost"
                                onPress={() => navigation.navigate('Delivery', { id: p.pengiriman_id })}
                            />
                        ))}
                    </Card>
                )}

                <View style={styles.aksi}>
                    {!data.is_done && (
                        <Button
                            label="Kirim ke Delivery"
                            variant="success"
                            onPress={() => navigation.navigate('Ms2Kirim', { id, detail: data.detail || [] })}
                        />
                    )}
                    {!data.is_done && (
                        <Button
                            label="Hapus MS2"
                            variant="danger"
                            loading={proses}
                            onPress={() => Alert.alert('Hapus MS2?', 'Produk akan dikembalikan ke daftar kandidat.', [
                                { text: 'Batal', style: 'cancel' },
                                {
                                    text: 'Hapus',
                                    style: 'destructive',
                                    onPress: async () => {
                                        setProses(true);
                                        try {
                                            await Procurement.ms2.hapus(id);
                                            navigation.goBack();
                                        } catch (e) {
                                            setPesan({ tone: 'danger', teks: e.message });
                                        } finally { setProses(false); }
                                    },
                                },
                            ])}
                        />
                    )}
                </View>
            </Halaman>
    );
}

const styles = StyleSheet.create({
    screen: { flex: 1, backgroundColor: colors.background },
    isi: { padding: spacing.lg, gap: spacing.md, paddingBottom: spacing.xxl },
    barisAtas: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', gap: spacing.sm },
    kotak: { borderWidth: 1, borderColor: colors.border, borderRadius: radius.md, padding: spacing.md, gap: 2, backgroundColor: colors.background },
    produk: { fontSize: 15, fontWeight: '700', color: colors.text },
    tebal: { fontWeight: '800', color: colors.primaryDark },
    aksi: { gap: spacing.sm },
});
