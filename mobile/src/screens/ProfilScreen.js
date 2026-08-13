import { useState } from 'react';
import { Alert, Image, StyleSheet, Text, View } from 'react-native';
import * as ImagePicker from 'expo-image-picker';
import { Button, Card, Halaman, Notice, Row } from '../components/ui';
import { Profil } from '../api/endpoints';
import { useAuth } from '../auth/AuthContext';
import { BASE_URL } from '../api/client';
import { tanggalPendek } from '../utils/format';
import { colors, radius, spacing, typography } from '../theme';

export default function ProfilScreen({ navigation }) {
    const { user, keluar, segarkan } = useAuth();
    const [proses, setProses] = useState(false);
    const [pesan, setPesan] = useState(null);

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
            await Profil.gantiAvatar(hasil.assets[0]);
            await segarkan();
            setPesan({ tone: 'success', teks: 'Foto profil diperbarui.' });
        } catch (e) {
            setPesan({ tone: 'danger', teks: e.message });
        } finally {
            setProses(false);
        }
    };

    const konfirmasiKeluar = () => {
        Alert.alert('Keluar', 'Yakin ingin keluar dari aplikasi?', [
            { text: 'Batal', style: 'cancel' },
            { text: 'Keluar', style: 'destructive', onPress: keluar },
        ]);
    };

    return (
        <Halaman judul="Profil" subjudul={user?.jabatan} warna="biru" kembali={false}>
            {!!pesan && <Notice tone={pesan.tone}>{pesan.teks}</Notice>}

            <Card gradien="biru" style={styles.kartuProfil}>
                {user?.avatar_url ? (
                    <Image source={{ uri: user.avatar_url }} style={styles.avatar} />
                ) : (
                    <View style={[styles.avatar, styles.avatarKosong]}>
                        <Text style={styles.avatarHuruf}>{(user?.nama || '?').charAt(0).toUpperCase()}</Text>
                    </View>
                )}
                <Text style={styles.namaTerang}>{user?.nama}</Text>
                <Text style={styles.subTerang}>{user?.jabatan} · {user?.level}</Text>
                <Button label="Ganti foto" variant="ghost" onPress={gantiFoto} loading={proses} />
            </Card>

            <Card tone="biru">
                <Text style={typography.heading}>Data pegawai</Text>
                <Row label="Username" value={user?.username} />
                <Row label="NIK" value={user?.nik} />
                <Row label="Divisi" value={user?.divisi} />
                <Row label="Email" value={user?.email} />
                <Row label="No. HP" value={user?.no_hp} />
                <Row label="Bergabung" value={tanggalPendek(user?.bergabung_sejak)} />
                <Row
                    label="SPBU"
                    value={user?.spbu?.length ? user.spbu.map((s) => s.nama_spbu).join(', ') : 'Belum ditetapkan'}
                />
            </Card>

            <Card tone="oranye">
                <Text style={typography.heading}>Keamanan</Text>
                <Button
                    label="Ganti password"
                    variant="outline"
                    onPress={() => navigation.navigate('GantiPassword')}
                />
            </Card>

            <Button label="Keluar" variant="danger" onPress={konfirmasiKeluar} />

            <Text style={[typography.caption, styles.server]} numberOfLines={1}>
                Server: {BASE_URL}
            </Text>
        </Halaman>
    );
}

const styles = StyleSheet.create({
    kartuProfil: { alignItems: 'center', gap: spacing.sm },
    avatar: { width: 88, height: 88, borderRadius: radius.pill, backgroundColor: colors.border },
    avatarKosong: { alignItems: 'center', justifyContent: 'center', backgroundColor: 'rgba(255,255,255,0.25)' },
    avatarHuruf: { fontSize: 34, fontWeight: '800', color: '#fff' },
    namaTerang: { fontSize: 20, fontWeight: '800', color: '#fff' },
    subTerang: { fontSize: 12, color: 'rgba(255,255,255,0.85)' },
    server: { textAlign: 'center' },
});
