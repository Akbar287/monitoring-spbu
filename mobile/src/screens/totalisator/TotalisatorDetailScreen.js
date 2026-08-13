import { useCallback, useEffect, useState } from 'react';
import { useFocusEffect } from '@react-navigation/native';
import { Alert, StyleSheet, Text, View } from 'react-native';
import { Badge, Button, Card, ErrorState, Field, Halaman, Loading, Notice, Row, SectionTitle } from '../../components/ui';
import { Totalisator } from '../../api/endpoints';
import { useMuat } from '../../hooks/useMuat';
import { liter, tanggalPanjang } from '../../utils/format';
import { spacing, typography } from '../../theme';

/** Detail satu shift totalisator: isi angka akhir, konfirmasi, hapus. */
export default function TotalisatorDetailScreen({ route, navigation }) {
    const { id } = route.params;
    const [akhir, setAkhir] = useState('');
    const [proses, setProses] = useState(false);
    const [pesan, setPesan] = useState(null);

    const { data, memuat, error, muatUlang } = useMuat(
        useCallback(() => Totalisator.detail(id), [id])
    );
    useFocusEffect(useCallback(() => { muatUlang({ segarkan: true }); }, [muatUlang]));

    // prefill kotak angka akhir dari data terbaru
    useEffect(() => {
        if (data && !data.belum_diisi_akhir) setAkhir(String(data.stand_meter_akhir));
    }, [data]);

    const bungkus = (isi) => <Halaman judul="Detail Totalisator" warna="pink">{isi}</Halaman>;
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

    const simpanAkhir = () => jalankan(() => Totalisator.akhir(id, akhir), 'Angka akhir disimpan.');

    const konfirmasi = () => Alert.alert(
        'Konfirmasi totalisator',
        `${liter(hitungLiter())} akan dipotong dari saldo dombak ${data.dombak}. Tindakan ini tidak bisa dibatalkan.`,
        [{ text: 'Batal', style: 'cancel' },
         { text: 'Konfirmasi', onPress: () => jalankan(() => Totalisator.konfirmasi(id), 'Stok dipotong dan dokumen penjualan dibuat.') }]
    );

    const hapus = () => Alert.alert('Hapus shift?', 'Catatan totalisator ini akan dihapus.',
        [{ text: 'Batal', style: 'cancel' },
         { text: 'Hapus', style: 'destructive', onPress: async () => {
             setProses(true);
             try { await Totalisator.hapus(id); navigation.goBack(); }
             catch (e) { setPesan({ tone: 'danger', teks: e.message }); setProses(false); }
         } }]);

    function hitungLiter() {
        const a = Number(akhir || data.stand_meter_akhir);
        const b = Number(data.stand_meter_awal);
        return Number.isFinite(a - b) && a >= b ? (a - b).toFixed(6) : '0.000000';
    }

    const bolehUbah = !data.is_confirmation;
    const literPratinjau = hitungLiter();

    return bungkus(
        <>
            {!!pesan && <Notice tone={pesan.tone}>{pesan.teks}</Notice>}

            <Card gradien="pink">
                <View style={styles.baris}>
                    <Text style={styles.labelTerang}>{data.nozzle} · {data.dombak}</Text>
                    <Badge
                        label={data.is_confirmation ? 'Dikonfirmasi' : data.belum_diisi_akhir ? 'Perlu diisi' : 'Siap'}
                        tone={data.is_confirmation ? 'hijau' : data.belum_diisi_akhir ? 'merah' : 'oranye'}
                    />
                </View>
                <Text style={styles.angka}>{liter(data.belum_diisi_akhir ? literPratinjau : data.liter)}</Text>
                <Row terang label="Produk" value={data.produk} />
                <Row terang label="Tanggal" value={tanggalPanjang(data.tanggal)} />
                <Row terang label="Shift" value={data.jam_kerja} />
            </Card>

            <Card>
                <Row label="Angka meter awal" value={data.stand_meter_awal} />
                <Row label="Angka meter akhir" value={data.belum_diisi_akhir ? 'belum diisi' : data.stand_meter_akhir} />
                <Row label="Saldo dombak kini" value={data.stok_dombak_sekarang ? liter(data.stok_dombak_sekarang) : '-'} />
                {data.dipakai_setoran && <Notice tone="warning">Data ini sudah dipakai pada setoran penjualan.</Notice>}
            </Card>

            {bolehUbah && (
                <>
                    <SectionTitle>Tutup shift</SectionTitle>
                    <Card tone="pink">
                        <Field
                            label="Angka meter akhir"
                            value={akhir}
                            onChangeText={setAkhir}
                            keyboardType="numeric"
                            editable={!proses}
                            placeholder={data.stand_meter_awal}
                            hint="Harus ≥ angka awal. Boleh sampai 6 angka di belakang koma."
                        />
                        {akhir !== '' && (
                            <Row label="Liter terjual (pratinjau)" value={liter(literPratinjau)} />
                        )}
                        <Button label="Simpan Angka Akhir" warna="pink" loading={proses}
                            disabled={akhir === ''} onPress={simpanAkhir} />
                    </Card>
                </>
            )}

            {!!data.dokumen_stok && (
                <Card tone="teal">
                    <Text style={typography.heading}>Dokumen stok</Text>
                    <Row label="Jenis" value={data.dokumen_stok.jenis} />
                    <Row label="Stok awal" value={liter(data.dokumen_stok.stok_awal)} />
                    <Row label="Stok akhir" value={liter(data.dokumen_stok.stok_akhir)} />
                </Card>
            )}

            {bolehUbah && (
                <View style={styles.aksi}>
                    <Button
                        label="Konfirmasi & Potong Stok"
                        variant="success"
                        disabled={data.belum_diisi_akhir || proses}
                        onPress={konfirmasi}
                    />
                    <Button label="Hapus" variant="danger" onPress={hapus} disabled={proses} />
                </View>
            )}
        </>
    );
}

const styles = StyleSheet.create({
    labelTerang: { fontSize: 14, fontWeight: '700', color: '#fff' },
    angka: { fontSize: 26, fontWeight: '800', color: '#fff' },
    baris: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', gap: spacing.sm },
    aksi: { gap: spacing.sm },
});
