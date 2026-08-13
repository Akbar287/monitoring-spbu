import { useCallback, useState } from 'react';
import { useFocusEffect } from '@react-navigation/native';
import { Alert, StyleSheet, Text, View } from 'react-native';
import { Badge, Button, Card, ErrorState, Halaman, Loading, Notice, Row, SectionTitle } from '../../components/ui';
import { Persediaan } from '../../api/endpoints';
import { useMuat } from '../../hooks/useMuat';
import { liter, tanggalPanjang } from '../../utils/format';
import { spacing, typography } from '../../theme';

export default function PenerimaanDetailScreen({ route, navigation }) {
    const { id } = route.params;
    const [proses, setProses] = useState(false);
    const [pesan, setPesan] = useState(null);

    const { data, memuat, error, muatUlang } = useMuat(
        useCallback(() => Persediaan.penerimaan.detail(id), [id])
    );
    useFocusEffect(useCallback(() => { muatUlang({ segarkan: true }); }, [muatUlang]));

    const bungkus = (isi) => <Halaman judul="Detail Penerimaan" warna="hijau">{isi}</Halaman>;
    if (memuat && !data) return bungkus(<Loading />);
    if (error && !data) return bungkus(<ErrorState pesan={error.message} onCoba={muatUlang} />);

    const konfirmasi = () => Alert.alert(
        'Konfirmasi penerimaan',
        'Saldo dombak akan diperbarui sesuai dokumen. Lanjutkan?',
        [{ text: 'Batal', style: 'cancel' }, { text: 'Konfirmasi', onPress: async () => {
            setProses(true); setPesan(null);
            try { await Persediaan.penerimaan.konfirmasi(id); setPesan({ tone: 'success', teks: 'Saldo diperbarui.' }); muatUlang({ segarkan: true }); }
            catch (e) { setPesan({ tone: 'danger', teks: e.message }); }
            finally { setProses(false); }
        } }]
    );

    const hapus = () => Alert.alert('Hapus penerimaan?', 'Seluruh dokumen stoknya ikut dihapus.',
        [{ text: 'Batal', style: 'cancel' }, { text: 'Hapus', style: 'destructive', onPress: async () => {
            setProses(true);
            try { await Persediaan.penerimaan.hapus(id); navigation.goBack(); }
            catch (e) { setPesan({ tone: 'danger', teks: e.message }); setProses(false); }
        } }]);

    return bungkus(
        <>
            {!!pesan && <Notice tone={pesan.tone}>{pesan.teks}</Notice>}

            <Card tone={data.is_confirmation ? 'hijau' : 'oranye'}>
                <View style={styles.baris}>
                    <Text style={typography.heading} numberOfLines={1}>{data.judul}</Text>
                    <Badge label={data.is_confirmation ? 'Dikonfirmasi' : 'Menunggu'}
                        tone={data.is_confirmation ? 'hijau' : 'oranye'} />
                </View>
                <Text style={typography.muted}>{tanggalPanjang(data.tanggal)}</Text>
                <Row label="Diterima" value={liter(data.diterima)} />
                <Row label="Dombak" value={data.dombak} />
            </Card>

            {!!data.lo && (
                <Card tone="biru">
                    <Text style={typography.heading}>Dokumen LO</Text>
                    <Row label="No. LO" value={data.lo.no_lo} />
                    <Row label="Produk" value={data.lo.produk} />
                    <Row label="Jumlah surat jalan" value={liter(data.lo.jumlah)} />
                </Card>
            )}

            <SectionTitle>Dokumen stok</SectionTitle>
            {(data.dokumen || []).map((d) => (
                <Card key={d.id} tone={d.is_confirmation ? 'abu' : 'oranye'}>
                    <View style={styles.baris}>
                        <Text style={typography.heading} numberOfLines={1}>{d.jenis}</Text>
                        <Badge label={d.is_confirmation ? 'OK' : 'Menunggu'}
                            tone={d.is_confirmation ? 'hijau' : 'oranye'} />
                    </View>
                    <Row label="Stok awal" value={liter(d.stok_awal)} />
                    <Row label="Stok akhir" value={liter(d.stok_akhir)} />
                    <Row label="Selisih" value={liter(d.selisih)} />
                    {!!d.losses && (
                        <Row label={`Losses (${d.losses.jenis})`}
                            value={`${d.losses.simbol === 'kurang' ? '−' : '+'} ${liter(d.losses.jumlah)}`} />
                    )}
                </Card>
            ))}

            {!data.is_confirmation && (
                <View style={styles.aksi}>
                    <Button label="Konfirmasi Penerimaan" variant="success" loading={proses} onPress={konfirmasi} />
                    <Button label="Hapus" variant="danger" onPress={hapus} disabled={proses} />
                </View>
            )}
        </>
    );
}

const styles = StyleSheet.create({
    baris: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', gap: spacing.sm },
    aksi: { gap: spacing.sm },
});
