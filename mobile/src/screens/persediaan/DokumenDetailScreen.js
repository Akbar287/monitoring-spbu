import { useCallback, useState } from 'react';
import { useFocusEffect } from '@react-navigation/native';
import { Alert, StyleSheet, Text, View } from 'react-native';
import { Badge, Button, Card, ErrorState, Halaman, Loading, Notice, Row } from '../../components/ui';
import { Persediaan } from '../../api/endpoints';
import { useMuat } from '../../hooks/useMuat';
import { liter, tanggalPanjang } from '../../utils/format';
import { spacing, typography } from '../../theme';

/** Detail satu dokumen Stok Taking, termasuk aksi konfirmasi & hapus. */
export default function DokumenDetailScreen({ route, navigation }) {
    const { id } = route.params;
    const [proses, setProses] = useState(false);
    const [pesan, setPesan] = useState(null);

    const { data, memuat, error, muatUlang } = useMuat(
        useCallback(() => Persediaan.taking.detail(id), [id])
    );

    useFocusEffect(useCallback(() => { muatUlang({ segarkan: true }); }, [muatUlang]));

    const bungkus = (isi) => <Halaman judul="Detail Stok Taking" warna="oranye">{isi}</Halaman>;
    if (memuat && !data) return bungkus(<Loading />);
    if (error && !data) return bungkus(<ErrorState pesan={error.message} onCoba={muatUlang} />);

    const jalankan = async (fn, sukses) => {
        setProses(true);
        setPesan(null);
        try {
            await fn();
            setPesan({ tone: 'success', teks: sukses });
            muatUlang({ segarkan: true });
        } catch (e) {
            setPesan({ tone: 'danger', teks: e.message });
        } finally {
            setProses(false);
        }
    };

    const konfirmasi = () => Alert.alert(
        'Konfirmasi stok taking',
        `Saldo dombak ${data.dombak} akan diubah menjadi ${liter(data.stok_akhir)}. Lanjutkan?`,
        [{ text: 'Batal', style: 'cancel' },
         { text: 'Konfirmasi', onPress: () => jalankan(() => Persediaan.taking.konfirmasi(id), 'Saldo diperbarui.') }]
    );

    const hapus = () => Alert.alert(
        'Hapus dokumen?', 'Pengukuran ini akan dihapus permanen.',
        [{ text: 'Batal', style: 'cancel' },
         { text: 'Hapus', style: 'destructive', onPress: async () => {
             setProses(true);
             try { await Persediaan.taking.hapus(id); navigation.goBack(); }
             catch (e) { setPesan({ tone: 'danger', teks: e.message }); setProses(false); }
         } }]
    );

    return bungkus(
        <>
            {!!pesan && <Notice tone={pesan.tone}>{pesan.teks}</Notice>}

            <Card tone={data.is_confirmation ? 'hijau' : 'oranye'}>
                <View style={styles.baris}>
                    <Text style={typography.heading}>{data.dombak} · {data.produk}</Text>
                    <Badge label={data.is_confirmation ? 'Dikonfirmasi' : 'Menunggu'}
                        tone={data.is_confirmation ? 'hijau' : 'oranye'} />
                </View>
                <Text style={typography.muted}>{tanggalPanjang(data.tanggal)} · {data.jam_kerja}</Text>
            </Card>

            <Card>
                <Row label="Stok awal" value={liter(data.stok_awal)} />
                <Row label="Stok akhir (terukur)" value={liter(data.stok_akhir)} />
                <Row label="Selisih" value={liter(data.selisih)} />
                {!!data.losses && (
                    <Row
                        label={`Losses (${data.losses.jenis})`}
                        value={`${data.losses.simbol === 'kurang' ? '−' : '+'} ${liter(data.losses.jumlah)}`}
                    />
                )}
                <Row label="Petugas" value={data.petugas} />
            </Card>

            {(data.tinggi || []).length > 0 && (
                <Card tone="biru">
                    <Text style={typography.heading}>Tinggi terukur</Text>
                    {data.tinggi.map((t, i) => (
                        <Row key={i} label={`${t.tinggi} cm`} value={liter(t.volume)} />
                    ))}
                </Card>
            )}

            {!data.is_confirmation && (
                <View style={styles.aksi}>
                    <Button label="Konfirmasi & Perbarui Saldo" variant="success" loading={proses} onPress={konfirmasi} />
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
