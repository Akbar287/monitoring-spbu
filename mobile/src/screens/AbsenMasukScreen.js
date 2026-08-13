import { useCallback, useEffect, useState } from 'react';
import { Image, Pressable, ScrollView, StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { CameraView, useCameraPermissions } from 'expo-camera';
import Ionicons from '@expo/vector-icons/Ionicons';
import * as ImagePicker from 'expo-image-picker';
import { Halaman, Button, Card, Loading, Notice, Row } from '../components/ui';
import { Jadwal } from '../api/endpoints';
import { ambilLokasi } from '../utils/lokasi';
import { infoPerangkat, offsetGmt } from '../utils/device';
import { colors, radius, spacing, typography } from '../theme';

/**
 * Alur absen masuk: pindai QR -> ambil selfie -> kunci titik GPS -> kirim.
 * Ketiganya wajib karena server memvalidasi barcode, selfie, dan koordinat.
 */
export default function AbsenMasukScreen({ route, navigation }) {
    const { id } = route.params;

    const [izinKamera, mintaIzinKamera] = useCameraPermissions();
    const [barcode, setBarcode] = useState(null);
    const [selfie, setSelfie] = useState(null);
    const [lokasi, setLokasi] = useState(null);
    const [memuatLokasi, setMemuatLokasi] = useState(false);
    const [proses, setProses] = useState(false);
    const [error, setError] = useState(null);

    const muatLokasi = useCallback(async () => {
        setMemuatLokasi(true);
        setError(null);
        try {
            setLokasi(await ambilLokasi());
        } catch (e) {
            setError(e.message);
        } finally {
            setMemuatLokasi(false);
        }
    }, []);

    // Ambil lokasi begitu QR terbaca supaya user tidak menunggu dua kali.
    useEffect(() => {
        if (barcode && !lokasi && !memuatLokasi) muatLokasi();
        // sengaja hanya bergantung pada barcode: percobaan ulang dilakukan
        // lewat tombol, bukan dengan menjalankan efek berulang
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [barcode]);

    const ambilSelfie = async () => {
        setError(null);
        const izin = await ImagePicker.requestCameraPermissionsAsync();
        if (!izin.granted) {
            setError('Izin kamera ditolak. Aktifkan di pengaturan perangkat.');
            return;
        }

        const hasil = await ImagePicker.launchCameraAsync({
            mediaTypes: ['images'],
            cameraType: ImagePicker.CameraType.front,
            quality: 0.5,          // tekan ukuran unggahan di jaringan SPBU
            allowsEditing: false,
        });

        if (!hasil.canceled) setSelfie(hasil.assets[0]);
    };

    const kirim = async () => {
        if (proses) return;
        setError(null);
        setProses(true);
        try {
            await Jadwal.absenMasuk(id, {
                barcode,
                latitude: lokasi.latitude,
                longitude: lokasi.longitude,
                gmt: offsetGmt(),
                selfie,
                device: infoPerangkat(),
                deskripsiLokasi: lokasi.palsu ? 'Terdeteksi lokasi palsu' : null,
            });
            navigation.goBack();
        } catch (e) {
            setError(e.message);
        } finally {
            setProses(false);
        }
    };

    // ---- tahap 1: pindai QR -------------------------------------------------
    if (!barcode) {
        if (!izinKamera) return <Halaman judul={'Absen Masuk'} warna="hijau"><Loading label="Menyiapkan kamera…" /></Halaman>;

        if (!izinKamera.granted) {
            return (
                <Halaman judul="Absen Masuk" warna="hijau">
                    <Card tone="hijau">
                        <View style={styles.tengah}>
                            <Text style={styles.ikon}>📷</Text>
                            <Text style={typography.heading}>Izin kamera diperlukan</Text>
                            <Text style={[typography.muted, styles.tengahTeks]}>
                                Kamera dipakai untuk memindai QR code absensi yang ditampilkan Penanggung Jawab.
                            </Text>
                            <Button label="Izinkan kamera" warna="hijau" onPress={mintaIzinKamera} />
                        </View>
                    </Card>
                </Halaman>
            );
        }

        return (
            <SafeAreaView style={styles.screenGelap} edges={['bottom']}>
                <CameraView
                    style={styles.kamera}
                    facing="back"
                    barcodeScannerSettings={{ barcodeTypes: ['qr'] }}
                    onBarcodeScanned={({ data }) => {
                        if (!barcode) setBarcode(data); // hanya pindaian pertama yang dipakai
                    }}
                >
                    <View style={styles.overlay}>
                        <View style={styles.bingkai} />
                        <Text style={styles.overlayTeks}>
                            Arahkan kamera ke QR code absensi
                        </Text>
                    </View>
                </CameraView>

                <Pressable
                    onPress={() => navigation.goBack()}
                    hitSlop={12}
                    style={styles.kembaliMengambang}
                >
                    <Ionicons name="chevron-back" size={22} color="#fff" />
                </Pressable>
            </SafeAreaView>
        );
    }

    // ---- tahap 2: selfie + lokasi + kirim -----------------------------------
    const siap = !!barcode && !!selfie && !!lokasi;

    return (
        <Halaman judul={'Absen Masuk'} warna="hijau">
                {!!error && <Notice tone="danger">{error}</Notice>}

                <Card>
                    <Text style={typography.heading}>1. QR code</Text>
                    <Notice tone="success">QR code terbaca.</Notice>
                    <Button
                        label="Pindai ulang"
                        variant="ghost"
                        onPress={() => { setBarcode(null); setError(null); }}
                    />
                </Card>

                <Card>
                    <Text style={typography.heading}>2. Foto selfie</Text>
                    {selfie ? (
                        <View style={styles.previewBaris}>
                            <Image source={{ uri: selfie.uri }} style={styles.preview} />
                            <View style={styles.previewAksi}>
                                <Notice tone="success">Selfie siap dikirim.</Notice>
                                <Button label="Ambil ulang" variant="ghost" onPress={ambilSelfie} />
                            </View>
                        </View>
                    ) : (
                        <>
                            <Text style={typography.muted}>
                                Selfie wajib sebagai bukti kehadiran.
                            </Text>
                            <Button label="Ambil selfie" icon="🤳" onPress={ambilSelfie} />
                        </>
                    )}
                </Card>

                <Card>
                    <Text style={typography.heading}>3. Lokasi</Text>
                    {memuatLokasi && <Text style={typography.muted}>Mengambil titik GPS…</Text>}
                    {!!lokasi && (
                        <>
                            <Row label="Koordinat" value={`${lokasi.latitude.toFixed(5)}, ${lokasi.longitude.toFixed(5)}`} />
                            <Row label="Akurasi" value={lokasi.akurasi ? `± ${Math.round(lokasi.akurasi)} m` : '-'} />
                            {lokasi.palsu && <Notice tone="warning">Perangkat melaporkan lokasi palsu (mock location).</Notice>}
                        </>
                    )}
                    {!lokasi && !memuatLokasi && (
                        <Button label="Coba ambil lokasi lagi" variant="ghost" onPress={muatLokasi} />
                    )}
                </Card>

                <Button
                    label="Kirim Absen Masuk"
                    variant="success"
                    disabled={!siap}
                    loading={proses}
                    onPress={kirim}
                />
                <Text style={[typography.caption, styles.catatan]}>
                    QR code hanya berlaku beberapa saat. Bila gagal, minta Penanggung Jawab menampilkan ulang QR.
                </Text>
            </Halaman>
    );
}

const styles = StyleSheet.create({
    screen: { flex: 1, backgroundColor: colors.background },
    screenGelap: { flex: 1, backgroundColor: '#000' },
    kembaliMengambang: {
        position: 'absolute',
        top: spacing.xl,
        left: spacing.lg,
        width: 40,
        height: 40,
        borderRadius: 20,
        backgroundColor: 'rgba(0,0,0,0.45)',
        alignItems: 'center',
        justifyContent: 'center',
    },
    isi: { padding: spacing.lg, gap: spacing.md, paddingBottom: spacing.xxl },

    kamera: { flex: 1 },
    overlay: { flex: 1, alignItems: 'center', justifyContent: 'center', gap: spacing.lg },
    bingkai: {
        width: 240,
        height: 240,
        borderWidth: 3,
        borderColor: '#fff',
        borderRadius: radius.lg,
        backgroundColor: 'transparent',
    },
    overlayTeks: { color: '#fff', fontSize: 15, fontWeight: '600', textAlign: 'center', paddingHorizontal: spacing.xl },

    tengah: { flex: 1, alignItems: 'center', justifyContent: 'center', padding: spacing.xl, gap: spacing.md },
    tengahTeks: { textAlign: 'center' },
    ikon: { fontSize: 40 },

    previewBaris: { flexDirection: 'row', gap: spacing.md },
    preview: { width: 96, height: 128, borderRadius: radius.md, backgroundColor: colors.border },
    previewAksi: { flex: 1, gap: spacing.sm, justifyContent: 'center' },

    catatan: { textAlign: 'center' },
});
