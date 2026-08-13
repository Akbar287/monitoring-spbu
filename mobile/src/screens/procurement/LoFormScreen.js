import { useCallback, useState } from 'react';
import { Image, KeyboardAvoidingView, Platform, Pressable, ScrollView, StyleSheet, Text, View } from 'react-native';
import * as ImagePicker from 'expo-image-picker';
import Ionicons from '@expo/vector-icons/Ionicons';
import { Halaman, Button, Card, EmptyState, ErrorState, Field, Loading, Notice, Row } from '../../components/ui';
import { Procurement } from '../../api/endpoints';
import { useMuat } from '../../hooks/useMuat';
import { desimal6 } from '../../utils/format';
import { colors, radius, spacing, typography } from '../../theme';

/**
 * Unggah dokumen LO untuk satu baris produk. Kuantitas & satuan tidak
 * diketik ulang — server menyalinnya dari baris rencana pembelian supaya
 * angka LO persis sama dengan dokumen asalnya.
 */
export default function LoFormScreen({ route, navigation }) {
    const { id } = route.params;

    const [detailId, setDetailId] = useState('');
    const [noFaktur, setNoFaktur] = useState('');
    const [noLo, setNoLo] = useState('');
    const [foto, setFoto] = useState(null);
    const [proses, setProses] = useState(false);
    const [error, setError] = useState(null);

    const { data, memuat, error: gagalMuat, muatUlang } = useMuat(
        useCallback(() => Procurement.delivery.detail(id), [id])
    );

    if (memuat && !data) return <Halaman judul={'Unggah Dokumen LO'} warna="oranye" keyboard><Loading /></Halaman>;
    if (gagalMuat && !data) {
        return <Halaman judul={'Unggah Dokumen LO'} warna="oranye" keyboard><ErrorState pesan={gagalMuat.message} onCoba={muatUlang} /></Halaman>;
    }

    const kandidat = data?.kandidat_lo || [];

    const ambilFoto = async (dariKamera) => {
        setError(null);
        const izin = dariKamera
            ? await ImagePicker.requestCameraPermissionsAsync()
            : await ImagePicker.requestMediaLibraryPermissionsAsync();
        if (!izin.granted) {
            setError({ message: dariKamera ? 'Izin kamera ditolak.' : 'Izin galeri ditolak.' });
            return;
        }

        const opsi = { mediaTypes: ['images'], quality: 0.6 };
        const hasil = dariKamera
            ? await ImagePicker.launchCameraAsync(opsi)
            : await ImagePicker.launchImageLibraryAsync(opsi);

        if (!hasil.canceled) setFoto(hasil.assets[0]);
    };

    const kirim = async () => {
        if (proses) return;
        setError(null);
        setProses(true);
        try {
            await Procurement.delivery.lo.unggah(id, {
                detail_rencana_pembelian_id: detailId,
                no_faktur: noFaktur.trim(),
                no_lo: noLo.trim(),
                berkas: foto,
            });
            navigation.goBack();
        } catch (e) {
            setError(e);
        } finally {
            setProses(false);
        }
    };

    return (
        <Halaman judul={'Unggah Dokumen LO'} warna="oranye" keyboard>
                    {!!error && (
                        <Notice tone="danger">
                            {error.fieldError?.('berkas') || error.fieldError?.('no_lo') || error.message}
                        </Notice>
                    )}

                    <Card>
                        <Text style={typography.heading}>1. Pilih produk</Text>
                        {kandidat.length === 0 ? (
                            <EmptyState
                                judul="Semua produk sudah punya LO"
                                pesan="Tidak ada baris yang menunggu dokumen LO pada pengiriman ini."
                            />
                        ) : (
                            kandidat.map((k) => {
                                const aktif = detailId === k.id;
                                return (
                                    <Pressable key={k.id} onPress={() => setDetailId(k.id)}
                                        style={[styles.kotak, aktif && styles.kotakAktif]}>
                                        <View style={styles.barisAtas}>
                                            <Text style={styles.produk}>{k.produk}</Text>
                                            <Ionicons name={aktif ? 'radio-button-on' : 'radio-button-off'} size={20}
                                                color={aktif ? colors.primary : colors.textMuted} />
                                        </View>
                                        <Row label="Dokumen" value={k.kode_pembelian} />
                                        <Row label="Kuantitas" value={`${desimal6(k.jumlah)} ${k.satuan_jumlah}`} />
                                    </Pressable>
                                );
                            })
                        )}
                    </Card>

                    <Card>
                        <Text style={typography.heading}>2. Nomor dokumen</Text>
                        <Field label="No. Faktur *" value={noFaktur} onChangeText={setNoFaktur} maxLength={16}
                            autoCapitalize="characters" editable={!proses} error={error?.fieldError?.('no_faktur')} />
                        <Field label="No. LO *" value={noLo} onChangeText={setNoLo} maxLength={16}
                            autoCapitalize="characters" editable={!proses} error={error?.fieldError?.('no_lo')} />
                    </Card>

                    <Card>
                        <Text style={typography.heading}>3. Foto dokumen</Text>
                        {foto ? (
                            <View style={styles.previewBaris}>
                                <Image source={{ uri: foto.uri }} style={styles.preview} />
                                <View style={styles.previewAksi}>
                                    <Notice tone="success">Foto siap diunggah.</Notice>
                                    <Button label="Ambil ulang" variant="ghost" onPress={() => ambilFoto(true)} />
                                </View>
                            </View>
                        ) : (
                            <View style={styles.tombolBaris}>
                                <Button label="Kamera" icon="📷" style={styles.tombol} onPress={() => ambilFoto(true)} />
                                <Button label="Galeri" icon="🖼️" variant="outline" style={styles.tombol}
                                    onPress={() => ambilFoto(false)} />
                            </View>
                        )}
                    </Card>

                    <Button
                        label="Unggah LO"
                        onPress={kirim}
                        loading={proses}
                        disabled={!detailId || !noFaktur.trim() || !noLo.trim() || !foto}
                    />
                </Halaman>
    );
}

const styles = StyleSheet.create({
    screen: { flex: 1, backgroundColor: colors.background },
    flex: { flex: 1 },
    isi: { padding: spacing.lg, gap: spacing.md, paddingBottom: spacing.xxl },
    barisAtas: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', gap: spacing.sm },
    kotak: { borderWidth: 1, borderColor: colors.border, borderRadius: radius.md, padding: spacing.md, gap: 2, backgroundColor: colors.background },
    kotakAktif: { borderColor: colors.primary, backgroundColor: colors.primarySoft },
    produk: { fontSize: 15, fontWeight: '700', color: colors.text },
    tombolBaris: { flexDirection: 'row', gap: spacing.md },
    tombol: { flex: 1 },
    previewBaris: { flexDirection: 'row', gap: spacing.md },
    preview: { width: 96, height: 128, borderRadius: radius.md, backgroundColor: colors.border },
    previewAksi: { flex: 1, gap: spacing.sm, justifyContent: 'center' },
});
