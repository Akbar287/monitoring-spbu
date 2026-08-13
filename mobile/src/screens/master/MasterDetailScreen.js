import { useCallback, useState } from 'react';
import { useFocusEffect } from '@react-navigation/native';
import { Alert, Image, ScrollView, StyleSheet, Text, View } from 'react-native';
import * as ImagePicker from 'expo-image-picker';
import { Halaman, Button, Card, ErrorState, Loading, Notice, Row } from '../../components/ui';
import { Kelola } from '../../api/endpoints';
import { REGISTRY } from '../../master/registry';
import { useMuat } from '../../hooks/useMuat';
import { liter, rupiah, tanggalPanjang } from '../../utils/format';
import { colors, radius, spacing, typography } from '../../theme';

export default function MasterDetailScreen({ route, navigation }) {
    const { resource, id } = route.params;
    const konf = REGISTRY[resource];

    const [proses, setProses] = useState(false);
    const [pesan, setPesan] = useState(null);

    const { data, memuat, error, muatUlang } = useMuat(
        useCallback(() => Kelola.detail(konf.endpoint, id), [konf.endpoint, id])
    );

    // kembali dari form ubah -> detail harus ikut terbarui
    useFocusEffect(useCallback(() => { muatUlang({ segarkan: true }); }, [muatUlang]));

    if (memuat && !data) return <Halaman judul={`Detail ${konf?.label || ''}`.trim()} subjudul={data?.judul} warna="ungu"><Loading /></Halaman>;
    if (error && !data) {
        return <Halaman judul={`Detail ${konf?.label || ''}`.trim()} subjudul={data?.judul} warna="ungu"><ErrorState pesan={error.message} onCoba={muatUlang} /></Halaman>;
    }

    const hapus = () => {
        Alert.alert(
            `Hapus ${konf.label}?`,
            `"${data.judul}" akan dihapus. Tindakan ini tidak bisa dibatalkan.`,
            [
                { text: 'Batal', style: 'cancel' },
                {
                    text: 'Hapus',
                    style: 'destructive',
                    onPress: async () => {
                        setProses(true);
                        setPesan(null);
                        try {
                            await Kelola.hapus(konf.endpoint, id);
                            navigation.goBack();
                        } catch (e) {
                            // server menolak bila data masih dipakai transaksi lain
                            setPesan({ tone: 'danger', teks: e.message });
                        } finally {
                            setProses(false);
                        }
                    },
                },
            ]
        );
    };

    const gantiFoto = async () => {
        setPesan(null);
        const izin = await ImagePicker.requestCameraPermissionsAsync();
        if (!izin.granted) {
            setPesan({ tone: 'danger', teks: 'Izin kamera ditolak.' });
            return;
        }

        const hasil = await ImagePicker.launchCameraAsync({
            mediaTypes: ['images'],
            cameraType: ImagePicker.CameraType.front,
            quality: 0.6,
            allowsEditing: true,
        });
        if (hasil.canceled) return;

        setProses(true);
        try {
            await Kelola.member.avatar(id, hasil.assets[0]);
            await muatUlang({ segarkan: true });
            setPesan({ tone: 'success', teks: 'Foto pegawai diperbarui.' });
        } catch (e) {
            setPesan({ tone: 'danger', teks: e.message });
        } finally {
            setProses(false);
        }
    };

    const resetPassword = () => {
        Alert.alert(
            'Reset password',
            `Password ${data.judul} akan direset ke 12345678 dan semua sesi perangkatnya dikeluarkan.`,
            [
                { text: 'Batal', style: 'cancel' },
                {
                    text: 'Reset',
                    style: 'destructive',
                    onPress: async () => {
                        setProses(true);
                        try {
                            const h = await Kelola.member.resetPassword(id);
                            setPesan({ tone: 'warning', teks: `Password direset ke ${h.password_baru}. Minta pegawai segera menggantinya.` });
                        } catch (e) {
                            setPesan({ tone: 'danger', teks: e.message });
                        } finally {
                            setProses(false);
                        }
                    },
                },
            ]
        );
    };

    return (
        <Halaman judul={`Detail ${konf?.label || ''}`.trim()} subjudul={data?.judul} warna="ungu">
                {!!pesan && <Notice tone={pesan.tone}>{pesan.teks}</Notice>}

                <Card style={konf.foto ? styles.kartuFoto : undefined}>
                    {konf.foto && (
                        data.avatar_url
                            ? <Image source={{ uri: data.avatar_url }} style={styles.avatar} />
                            : (
                                <View style={[styles.avatar, styles.avatarKosong]}>
                                    <Text style={styles.avatarHuruf}>{(data.judul || '?').charAt(0).toUpperCase()}</Text>
                                </View>
                            )
                    )}
                    <Text style={typography.title}>{data.judul}</Text>
                    {!!data.subjudul && <Text style={typography.muted}>{data.subjudul}</Text>}
                </Card>

                <Card>
                    {konf.detail.map((baris) => (
                        <Row key={baris.label} label={baris.label} value={formatNilai(data[baris.key], baris)} />
                    ))}
                </Card>

                {resource === 'member' && (
                    <Card>
                        <Text style={typography.heading}>Jabatan & Penempatan</Text>
                        <Row
                            label="Jabatan"
                            value={(data.daftar_jabatan || []).map((j) => j.nama_jabatan).join(', ') || '-'}
                        />
                        <Row
                            label="SPBU"
                            value={(data.daftar_spbu || []).map((s) => s.nama_spbu).join(', ') || '-'}
                        />
                        <Button
                            label="Kelola Jabatan & SPBU"
                            variant="outline"
                            onPress={() => navigation.navigate('MemberRelasi', { id, nama: data.judul })}
                        />
                    </Card>
                )}

                <View style={styles.aksi}>
                    <Button
                        label="Ubah Data"
                        onPress={() => navigation.navigate('MasterForm', { resource, mode: 'ubah', id })}
                    />

                    {konf.lampiran && (
                        <Button
                            label={`Foto & Lampiran${data.jumlah_lampiran ? ` (${data.jumlah_lampiran})` : ''}`}
                            variant="outline"
                            onPress={() => navigation.navigate('Lampiran', { jenis: resource, id, judul: data.judul })}
                        />
                    )}

                    {konf.foto && (
                        <Button label="Ganti Foto Pegawai" variant="outline" onPress={gantiFoto} loading={proses} />
                    )}

                    {resource === 'member' && (
                        <Button label="Reset Password" variant="ghost" onPress={resetPassword} />
                    )}

                    <Button label={`Hapus ${konf.label}`} variant="danger" onPress={hapus} loading={proses} />
                </View>
            </Halaman>
    );
}

/** Terjemahkan nilai mentah API sesuai `format` di registry. */
function formatNilai(nilai, baris) {
    if (nilai === null || nilai === undefined || nilai === '') return '-';

    switch (baris.format) {
        case 'uang': return rupiah(nilai);
        case 'liter': return liter(nilai);
        case 'tanggal': return tanggalPanjang(nilai);
        case 'ya_tidak': return nilai ? (baris.ya || 'Ya') : (baris.tidak || 'Tidak');
        case 'daftar': return Array.isArray(nilai) && nilai.length ? nilai.join(', ') : '-';
        case 'berkas': return `${nilai} berkas`;
        default: return String(nilai);
    }
}

const styles = StyleSheet.create({
    screen: { flex: 1, backgroundColor: colors.background },
    isi: { padding: spacing.lg, gap: spacing.md, paddingBottom: spacing.xxl },
    kartuFoto: { alignItems: 'center' },
    avatar: { width: 84, height: 84, borderRadius: radius.pill, backgroundColor: colors.border },
    avatarKosong: { alignItems: 'center', justifyContent: 'center', backgroundColor: colors.primarySoft },
    avatarHuruf: { fontSize: 32, fontWeight: '800', color: colors.primary },
    aksi: { gap: spacing.sm },
});
