import { useCallback, useState } from 'react';
import { useFocusEffect } from '@react-navigation/native';
import { Alert, Image, ScrollView, StyleSheet, Text, View } from 'react-native';
import { Halaman, Badge, Button, Card, ErrorState, Loading, Notice, Row } from '../components/ui';
import { Jadwal } from '../api/endpoints';
import { useMuat } from '../hooks/useMuat';
import { ambilLokasi } from '../utils/lokasi';
import { offsetGmt } from '../utils/device';
import { jam, jamSingkat, tanggalPanjang } from '../utils/format';
import { colors, radius, spacing, typography } from '../theme';

export default function JadwalDetailScreen({ route, navigation }) {
    const { id } = route.params;
    const [proses, setProses] = useState(false);
    const [pesan, setPesan] = useState(null);

    const { data, memuat, error, muatUlang } = useMuat(
        useCallback(() => Jadwal.detail(id), [id])
    );

    // setelah absen masuk di layar lain, detail harus ikut diperbarui
    useFocusEffect(useCallback(() => { muatUlang({ segarkan: true }); }, [muatUlang]));

    if (memuat && !data) return <Halaman judul={'Detail Jadwal'} warna="teal"><Loading /></Halaman>;
    if (error && !data) {
        return <Halaman judul={'Detail Jadwal'} warna="teal"><ErrorState pesan={error.message} onCoba={muatUlang} /></Halaman>;
    }

    const absenPulang = () => {
        Alert.alert(
            'Absen Pulang',
            'Akhiri shift dan catat waktu pulang sekarang?',
            [
                { text: 'Batal', style: 'cancel' },
                { text: 'Ya, absen pulang', style: 'default', onPress: kirimPulang },
            ]
        );
    };

    const kirimPulang = async () => {
        setProses(true);
        setPesan(null);
        try {
            // lokasi bersifat opsional saat pulang — kegagalan GPS tidak boleh
            // menggagalkan absen
            const lokasi = await ambilLokasi().catch(() => null);
            await Jadwal.absenPulang(id, {
                latitude: lokasi?.latitude,
                longitude: lokasi?.longitude,
                gmt: offsetGmt(),
            });
            setPesan({ tone: 'success', teks: 'Absen pulang berhasil dicatat.' });
            muatUlang({ segarkan: true });
        } catch (e) {
            setPesan({ tone: 'danger', teks: e.message });
        } finally {
            setProses(false);
        }
    };

    return (
        <Halaman judul={'Detail Jadwal'} warna="teal">
                {!!pesan && <Notice tone={pesan.tone}>{pesan.teks}</Notice>}

                <Card>
                    <View style={styles.baris}>
                        <Text style={typography.heading}>{data.jam_kerja?.nama || 'Shift'}</Text>
                        <StatusBadge data={data} />
                    </View>
                    <Text style={typography.muted}>{tanggalPanjang(data.tanggal)}</Text>

                    <View style={styles.pemisah} />

                    <Row label="Jabatan" value={data.jabatan} />
                    <Row label="Status kehadiran" value={data.status_kehadiran} />
                    <Row
                        label="Jam kerja"
                        value={`${jamSingkat(data.jam_kerja?.jam_datang)} – ${jamSingkat(data.jam_kerja?.jam_pulang)}`}
                    />
                    <Row label="Batas telat" value={jamSingkat(data.jam_kerja?.batas_telat)} />
                    <Row
                        label="Nozzle"
                        value={data.nozzles?.length ? data.nozzles.join(', ') : 'Semua nozzle'}
                    />
                    {!!data.deskripsi && <Row label="Catatan" value={data.deskripsi} />}
                </Card>

                <Card>
                    <Text style={typography.heading}>Kehadiran</Text>
                    <Row label="Absen masuk" value={data.start_time ? jam(data.start_time) : 'Belum'} />
                    <Row label="Absen pulang" value={data.finish_time ? jam(data.finish_time) : 'Belum'} />
                    <Row label="Dikonfirmasi PJ" value={data.is_confirm ? 'Sudah' : 'Belum'} />
                </Card>

                {(data.bukti?.selfie || []).length > 0 && (
                    <Card>
                        <Text style={typography.heading}>Bukti selfie</Text>
                        <ScrollView horizontal showsHorizontalScrollIndicator={false}>
                            <View style={styles.galeri}>
                                {data.bukti.selfie.map((url) => (
                                    <Image key={url} source={{ uri: url }} style={styles.selfie} />
                                ))}
                            </View>
                        </ScrollView>
                    </Card>
                )}

                {(data.bukti?.lokasi || []).length > 0 && (
                    <Card>
                        <Text style={typography.heading}>Lokasi tercatat</Text>
                        {data.bukti.lokasi.map((l, i) => (
                            <Row
                                key={`${l.latitude}-${l.longitude}-${i}`}
                                label={l.deskripsi || `Titik ${i + 1}`}
                                value={`${l.latitude.toFixed(5)}, ${l.longitude.toFixed(5)}`}
                            />
                        ))}
                    </Card>
                )}

                {data.mode === 'in' && (
                    <Button
                        label="Absen Masuk (Scan QR)"
                        onPress={() => navigation.navigate('AbsenMasuk', { id, judul: data.jam_kerja?.nama })}
                    />
                )}

                {data.mode === 'out' && (
                    <Button
                        label="Absen Pulang"
                        variant="success"
                        loading={proses}
                        onPress={absenPulang}
                    />
                )}

                {data.mode === 'done' && (
                    <Notice tone="success">Kehadiran untuk shift ini sudah lengkap.</Notice>
                )}
            </Halaman>
    );
}

function StatusBadge({ data }) {
    if (data.mode === 'done') return <Badge label="Selesai" tone="muted" />;
    if (data.mode === 'out') return <Badge label="Bertugas" tone="success" />;
    return <Badge label="Belum absen" tone="warning" />;
}

const styles = StyleSheet.create({
    screen: { flex: 1, backgroundColor: colors.background },
    isi: { padding: spacing.lg, gap: spacing.md, paddingBottom: spacing.xxl },
    baris: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' },
    pemisah: { height: 1, backgroundColor: colors.border, marginVertical: spacing.sm },
    galeri: { flexDirection: 'row', gap: spacing.sm },
    selfie: { width: 110, height: 150, borderRadius: radius.md, backgroundColor: colors.border },
});
