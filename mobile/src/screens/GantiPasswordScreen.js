import { useState } from 'react';
import { ScrollView, StyleSheet } from 'react-native';
import { Halaman, Button, Card, Field, Notice } from '../components/ui';
import { Profil } from '../api/endpoints';
import { setToken } from '../api/client';
import { colors, spacing } from '../theme';

export default function GantiPasswordScreen({ navigation }) {
    const [form, setForm] = useState({ lama: '', baru: '', konfirmasi: '' });
    const [proses, setProses] = useState(false);
    const [error, setError] = useState(null);

    const ubah = (key) => (nilai) => setForm((f) => ({ ...f, [key]: nilai }));

    const kirim = async () => {
        if (proses) return;
        setError(null);

        if (form.baru !== form.konfirmasi) {
            setError({ message: 'Konfirmasi password tidak sama.' });
            return;
        }

        setProses(true);
        try {
            const hasil = await Profil.gantiPassword({
                password_lama: form.lama,
                password: form.baru,
                password_confirmation: form.konfirmasi,
            });
            // server mencabut semua token lama dan menerbitkan yang baru untuk
            // perangkat ini — simpan agar sesi tidak putus
            if (hasil?.token) await setToken(hasil.token);
            navigation.goBack();
        } catch (e) {
            setError(e);
        } finally {
            setProses(false);
        }
    };

    return (
        <Halaman judul={'Ganti Password'} warna="biru" keyboard>
                {!!error && (
                    <Notice tone="danger">
                        {error.fieldError?.('password') || error.fieldError?.('password_lama') || error.message}
                    </Notice>
                )}

                <Card>
                    <Field
                        label="Password lama"
                        value={form.lama}
                        onChangeText={ubah('lama')}
                        secureTextEntry
                        autoCapitalize="none"
                        editable={!proses}
                    />
                    <Field
                        label="Password baru"
                        value={form.baru}
                        onChangeText={ubah('baru')}
                        secureTextEntry
                        autoCapitalize="none"
                        hint="Minimal 8 karakter."
                        editable={!proses}
                    />
                    <Field
                        label="Ulangi password baru"
                        value={form.konfirmasi}
                        onChangeText={ubah('konfirmasi')}
                        secureTextEntry
                        autoCapitalize="none"
                        editable={!proses}
                    />
                </Card>

                <Notice tone="warning">
                    Setelah password diubah, sesi di perangkat lain akan dikeluarkan.
                </Notice>

                <Button label="Simpan password" onPress={kirim} loading={proses} />
            </Halaman>
    );
}

const styles = StyleSheet.create({
    screen: { flex: 1, backgroundColor: colors.background },
    isi: { padding: spacing.lg, gap: spacing.md },
});
