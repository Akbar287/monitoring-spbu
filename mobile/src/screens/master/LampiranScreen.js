import { useCallback, useState } from 'react';
import { Alert, Image, Pressable, ScrollView, StyleSheet, Text, View } from 'react-native';
import * as ImagePicker from 'expo-image-picker';
import Ionicons from '@expo/vector-icons/Ionicons';
import { Halaman, Button, Card, EmptyState, ErrorState, Loading, Notice } from '../../components/ui';
import { Kelola } from '../../api/endpoints';
import { useMuat } from '../../hooks/useMuat';
import { tanggalPendek } from '../../utils/format';
import { colors, radius, spacing, typography } from '../../theme';

/**
 * Galeri foto & berkas lampiran untuk master Aset dan Fasilitas.
 * Sumber foto bisa dari kamera langsung atau galeri perangkat.
 */
export default function LampiranScreen({ route, navigation }) {
    const { jenis, id, judul } = route.params;

    const [proses, setProses] = useState(false);
    const [pesan, setPesan] = useState(null);

    const { data, memuat, error, muatUlang, segarkan, menyegarkan } = useMuat(
        useCallback(() => Kelola.lampiran.daftar(jenis, id), [jenis, id])
    );

    const unggah = async (dariKamera) => {
        setPesan(null);

        const izin = dariKamera
            ? await ImagePicker.requestCameraPermissionsAsync()
            : await ImagePicker.requestMediaLibraryPermissionsAsync();

        if (!izin.granted) {
            setPesan({ tone: 'danger', teks: dariKamera ? 'Izin kamera ditolak.' : 'Izin galeri ditolak.' });
            return;
        }

        const opsi = { mediaTypes: ['images'], quality: 0.6, allowsEditing: false };
        const hasil = dariKamera
            ? await ImagePicker.launchCameraAsync({ ...opsi, cameraType: ImagePicker.CameraType.back })
            : await ImagePicker.launchImageLibraryAsync(opsi);

        if (hasil.canceled) return;

        setProses(true);
        try {
            const aset = hasil.assets[0];
            await Kelola.lampiran.unggah(jenis, id, aset, aset.fileName || `Foto ${new Date().toLocaleDateString('id-ID')}`);
            await muatUlang({ segarkan: true });
            setPesan({ tone: 'success', teks: 'Foto berhasil diunggah.' });
        } catch (e) {
            setPesan({ tone: 'danger', teks: e.message });
        } finally {
            setProses(false);
        }
    };

    const hapus = (item) => {
        Alert.alert('Hapus lampiran?', `"${item.nama_dokumen}" akan dihapus permanen.`, [
            { text: 'Batal', style: 'cancel' },
            {
                text: 'Hapus',
                style: 'destructive',
                onPress: async () => {
                    setProses(true);
                    try {
                        await Kelola.lampiran.hapus(jenis, id, item.id);
                        await muatUlang({ segarkan: true });
                        setPesan({ tone: 'success', teks: 'Lampiran dihapus.' });
                    } catch (e) {
                        setPesan({ tone: 'danger', teks: e.message });
                    } finally {
                        setProses(false);
                    }
                },
            },
        ]);
    };

    if (memuat && !data) return <Halaman judul="Foto & Lampiran" subjudul={judul} warna="ungu"><Loading /></Halaman>;
    if (error && !data) {
        return <Halaman judul="Foto & Lampiran" subjudul={judul} warna="ungu"><ErrorState pesan={error.message} onCoba={muatUlang} /></Halaman>;
    }

    const items = data?.items || [];

    return (
        <Halaman judul="Foto & Lampiran" subjudul={judul} warna="ungu">
                {!!pesan && <Notice tone={pesan.tone}>{pesan.teks}</Notice>}

                <View style={styles.tombolBaris}>
                    <Button label="Kamera" icon="📷" onPress={() => unggah(true)} loading={proses} style={styles.tombol} />
                    <Button label="Galeri" icon="🖼️" variant="outline" onPress={() => unggah(false)} style={styles.tombol} />
                </View>

                {items.length === 0 ? (
                    <Card>
                        <EmptyState judul="Belum ada lampiran" pesan="Ambil foto atau pilih dari galeri untuk menambah." />
                    </Card>
                ) : (
                    items.map((item) => (
                        <Card key={item.id}>
                            <View style={styles.baris}>
                                {item.is_gambar ? (
                                    <Image source={{ uri: item.url }} style={styles.thumb} />
                                ) : (
                                    <View style={[styles.thumb, styles.thumbBerkas]}>
                                        <Ionicons name="document-text-outline" size={26} color={colors.textMuted} />
                                    </View>
                                )}

                                <View style={styles.info}>
                                    <Text style={typography.body} numberOfLines={2}>{item.nama_dokumen}</Text>
                                    <Text style={typography.caption}>{tanggalPendek(item.tanggal_upload)}</Text>
                                </View>

                                <Pressable onPress={() => hapus(item)} hitSlop={10} disabled={proses}>
                                    <Ionicons name="trash-outline" size={20} color={colors.danger} />
                                </Pressable>
                            </View>
                        </Card>
                    ))
                )}
            </Halaman>
    );
}

const styles = StyleSheet.create({
    screen: { flex: 1, backgroundColor: colors.background },
    isi: { padding: spacing.lg, gap: spacing.md, paddingBottom: spacing.xxl },
    tombolBaris: { flexDirection: 'row', gap: spacing.md },
    tombol: { flex: 1 },
    baris: { flexDirection: 'row', alignItems: 'center', gap: spacing.md },
    thumb: { width: 64, height: 64, borderRadius: radius.md, backgroundColor: colors.border },
    thumbBerkas: { alignItems: 'center', justifyContent: 'center' },
    info: { flex: 1, gap: 2 },
});
