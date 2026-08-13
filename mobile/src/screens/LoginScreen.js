import { useState } from 'react';
import {
    Image,
    KeyboardAvoidingView,
    Platform,
    ScrollView,
    StyleSheet,
    Text,
    View,
} from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { LinearGradient } from 'expo-linear-gradient';
import Constants from 'expo-constants';
import { Button, Field, Notice } from '../components/ui';
import { useAuth } from '../auth/AuthContext';
import { BASE_URL, SUMBER_BASE_URL, cekKoneksi } from '../api/client';
import { aksen, bayangan, colors, radius, spacing, typography } from '../theme';

export default function LoginScreen() {
    const { masuk } = useAuth();
    const [username, setUsername] = useState('');
    const [password, setPassword] = useState('');
    const [lihatPassword, setLihatPassword] = useState(false);
    const [proses, setProses] = useState(false);
    const [error, setError] = useState(null);
    const [menguji, setMenguji] = useState(false);
    const [hasilUji, setHasilUji] = useState(null);

    const ujiKoneksi = async () => {
        setMenguji(true);
        setHasilUji(null);
        setHasilUji(await cekKoneksi());
        setMenguji(false);
    };

    const kirim = async () => {
        if (proses) return;
        setError(null);

        if (!username.trim() || !password) {
            setError({ message: 'Username dan password wajib diisi.' });
            return;
        }

        setProses(true);
        try {
            await masuk(username.trim(), password);
            // navigasi berpindah sendiri lewat perubahan state auth di RootNavigator
        } catch (e) {
            setError(e);
        } finally {
            setProses(false);
        }
    };

    return (
        <LinearGradient
            colors={aksen.indigo.gradien}
            start={{ x: 0, y: 0 }}
            end={{ x: 1, y: 1 }}
            style={styles.screen}
        >
            <SafeAreaView style={styles.flex}>
            <KeyboardAvoidingView
                style={styles.flex}
                behavior={Platform.OS === 'ios' ? 'padding' : undefined}
            >
                <ScrollView contentContainerStyle={styles.isi} keyboardShouldPersistTaps="handled">
                    <View style={styles.header}>
                        <Image source={require('../../assets/icon.png')} style={styles.logo} />
                        <Text style={styles.judul}>{NAMA_APLIKASI}</Text>
                        <Text style={styles.slogan}>Masuk dengan akun pegawai Anda</Text>
                    </View>

                    <View style={styles.kartu}>
                        {!!error && (
                            <Notice tone="danger">
                                {error.fieldError?.('username') || error.message}
                            </Notice>
                        )}

                        <Field
                            label="Username"
                            value={username}
                            onChangeText={setUsername}
                            placeholder="mis. MGSJ-001"
                            autoCapitalize="characters"
                            autoCorrect={false}
                            returnKeyType="next"
                            editable={!proses}
                        />

                        <Field
                            label="Password"
                            value={password}
                            onChangeText={setPassword}
                            placeholder="Kata sandi"
                            secureTextEntry={!lihatPassword}
                            autoCapitalize="none"
                            autoCorrect={false}
                            returnKeyType="go"
                            onSubmitEditing={kirim}
                            editable={!proses}
                        />

                        <Text style={styles.toggle} onPress={() => setLihatPassword((v) => !v)}>
                            {lihatPassword ? 'Sembunyikan password' : 'Tampilkan password'}
                        </Text>

                        <Button label="Masuk" onPress={kirim} loading={proses} />
                    </View>

                    {!!hasilUji && (
                        <Notice tone={hasilUji.ok ? 'success' : 'danger'}>{hasilUji.pesan}</Notice>
                    )}

                    <View style={styles.kaki}>
                        <Text style={styles.server} numberOfLines={1}>
                            Server: {BASE_URL}
                            {SUMBER_BASE_URL === 'otomatis' ? ' (otomatis dari host Metro)' : ''}
                        </Text>
                        <Text style={styles.uji} onPress={menguji ? undefined : ujiKoneksi}>
                            {menguji ? 'Menguji koneksi…' : 'Tes koneksi ke server'}
                        </Text>
                    </View>
                </ScrollView>
                </KeyboardAvoidingView>
            </SafeAreaView>
        </LinearGradient>
    );
}

// nama diambil dari app.json supaya tidak ada dua sumber kebenaran
const NAMA_APLIKASI = Constants.expoConfig?.name ?? 'SPBU';

const styles = StyleSheet.create({
    screen: { flex: 1 },
    flex: { flex: 1 },
    isi: { flexGrow: 1, justifyContent: 'center', padding: spacing.xl, gap: spacing.xl },
    header: { alignItems: 'center', gap: spacing.xs },
    logo: { width: 84, height: 84, borderRadius: radius.xl, marginBottom: spacing.sm, ...bayangan.naik },
    judul: { fontSize: 26, fontWeight: '800', color: '#fff' },
    kartu: {
        backgroundColor: colors.surface,
        borderRadius: radius.xl,
        padding: spacing.xl,
        gap: spacing.md,
        ...bayangan.naik,
    },
    toggle: { fontSize: 13, color: colors.primary, fontWeight: '600' },
    kaki: { gap: spacing.xs },
    slogan: { fontSize: 13, color: 'rgba(255,255,255,0.85)' },
    server: { fontSize: 12, textAlign: 'center', color: 'rgba(255,255,255,0.75)' },
    uji: { fontSize: 12, textAlign: 'center', color: '#fff', fontWeight: '800' },
});
