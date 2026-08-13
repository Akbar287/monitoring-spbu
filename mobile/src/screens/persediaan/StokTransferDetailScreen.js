import { useCallback, useState } from 'react';
import { useFocusEffect } from '@react-navigation/native';
import { Alert, StyleSheet, Text, View } from 'react-native';
import { Badge, Button, Card, ErrorState, Halaman, Loading, Notice, Row, SectionTitle } from '../../components/ui';
import { Persediaan } from '../../api/endpoints';
import { useMuat } from '../../hooks/useMuat';
import { liter, tanggalPanjang } from '../../utils/format';
import { spacing, typography } from '../../theme';

export default function StokTransferDetailScreen({ route, navigation }) {
    const { id } = route.params;
    const [proses, setProses] = useState(false);
    const [pesan, setPesan] = useState(null);

    const { data, memuat, error, muatUlang } = useMuat(
        useCallback(() => Persediaan.transfer.detail(id), [id])
    );
    useFocusEffect(useCallback(() => { muatUlang({ segarkan: true }); }, [muatUlang]));

    const bungkus = (isi) => <Halaman judul="Detail Transfer" warna="ungu">{isi}</Halaman>;
    if (memuat && !data) return bungkus(<Loading />);
    if (error && !data) return bungkus(<ErrorState pesan={error.message} onCoba={muatUlang} />);

    const konfirmasi = () => Alert.alert('Konfirmasi transfer',
        'Saldo kedua dombak akan diperbarui. Lanjutkan?',
        [{ text: 'Batal', style: 'cancel' }, { text: 'Konfirmasi', onPress: async () => {
            setProses(true); setPesan(null);
            try { await Persediaan.transfer.konfirmasi(id); setPesan({ tone: 'success', teks: 'Saldo diperbarui.' }); muatUlang({ segarkan: true }); }
            catch (e) { setPesan({ tone: 'danger', teks: e.message }); }
            finally { setProses(false); }
        } }]);

    const hapus = () => Alert.alert('Hapus transfer?', 'Seluruh dokumen stoknya ikut dihapus.',
        [{ text: 'Batal', style: 'cancel' }, { text: 'Hapus', style: 'destructive', onPress: async () => {
            setProses(true);
            try { await Persediaan.transfer.hapus(id); navigation.goBack(); }
            catch (e) { setPesan({ tone: 'danger', teks: e.message }); setProses(false); }
        } }]);

    return bungkus(
        <>
            {!!pesan && <Notice tone={pesan.tone}>{pesan.teks}</Notice>}

            <Card gradien="ungu">
                <View style={styles.baris}>
                    <Text style={styles.labelTerang}>{data.dombak_asal} → {data.dombak_tujuan}</Text>
                    <Badge label={data.is_confirmation ? 'Selesai' : 'Menunggu'}
                        tone={data.is_confirmation ? 'hijau' : 'oranye'} />
                </View>
                <Text style={styles.angka}>{liter(data.dikirim)}</Text>
                <Row terang label="Diterima" value={liter(data.diterima)} />
                <Row terang label="Produk" value={data.produk} />
                <Row terang label="Tanggal" value={tanggalPanjang(data.tanggal)} />
                <Row terang label="Jam kerja" value={data.jam_kerja} />
            </Card>

            <SectionTitle>Dokumen stok</SectionTitle>
            {(data.dokumen || []).map((d) => (
                <Card key={d.id} tone={d.is_confirmation ? 'abu' : 'oranye'}>
                    <View style={styles.baris}>
                        <Text style={typography.heading} numberOfLines={1}>{d.jenis}</Text>
                        <Text style={typography.caption}>{d.dombak}</Text>
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
                    <Button label="Konfirmasi Transfer" variant="success" loading={proses} onPress={konfirmasi} />
                    <Button label="Hapus" variant="danger" onPress={hapus} disabled={proses} />
                </View>
            )}
        </>
    );
}

const styles = StyleSheet.create({
    labelTerang: { fontSize: 13, color: 'rgba(255,255,255,0.9)', fontWeight: '700' },
    angka: { fontSize: 26, fontWeight: '800', color: '#fff' },
    baris: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', gap: spacing.sm },
    aksi: { gap: spacing.sm },
});
