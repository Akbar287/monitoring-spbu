import { Pressable, StyleSheet, Text, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { Halaman, Notice } from '../../components/ui';
import { GRUP } from '../../master/registry';
import { useOpsi } from '../../master/OpsiContext';
import { useAuth } from '../../auth/AuthContext';
import { aksen, bayangan, colors, radius, spacing, typography } from '../../theme';

/**
 * Beranda modul kelola data: dua kelompok — Master Data dan Konfigurasi —
 * mengikuti pembagian menu di aplikasi web. Tiap resource punya warnanya
 * sendiri (didefinisikan di registry) supaya mudah dikenali sekilas.
 */
export default function MasterHomeScreen({ navigation }) {
    const { error } = useOpsi();
    const { user } = useAuth();

    return (
        <Halaman
            judul="Kelola Data"
            subjudul={`${user?.nama ?? ''} · data acuan & konfigurasi SPBU`}
            warna="ungu"
            kembali={false}
        >
            {!!error && (
                <Notice tone="danger">
                    Daftar pilihan gagal dimuat: {error.message}. Form mungkin tampil tanpa opsi.
                </Notice>
            )}

            {GRUP.map((grup) => (
                <View key={grup.judul} style={styles.grup}>
                    <View>
                        <Text style={typography.heading}>{grup.judul}</Text>
                        <Text style={typography.caption}>{grup.deskripsi}</Text>
                    </View>

                    <View style={styles.grid}>
                        {grup.items.map((r) => {
                            const a = aksen[r.warna] || aksen.indigo;

                            return (
                                <Pressable
                                    key={r.key}
                                    style={({ pressed }) => [
                                        styles.tile,
                                        { backgroundColor: a.lembut, borderColor: a.solid + '2e' },
                                        pressed && styles.ditekan,
                                    ]}
                                    onPress={() => navigation.navigate('MasterList', { resource: r.key })}
                                >
                                    <View style={[styles.ikonKotak, { backgroundColor: a.solid }]}>
                                        <Ionicons name={r.ikon} size={20} color="#fff" />
                                    </View>
                                    <Text style={[styles.tileJudul, { color: a.teks }]}>{r.label}</Text>
                                    <Text style={styles.tileKeterangan} numberOfLines={2}>{r.deskripsi}</Text>
                                </Pressable>
                            );
                        })}
                    </View>
                </View>
            ))}
        </Halaman>
    );
}

const styles = StyleSheet.create({
    grup: { gap: spacing.md },
    grid: { flexDirection: 'row', flexWrap: 'wrap', gap: spacing.md },
    tile: {
        width: '47.5%',
        borderRadius: radius.lg,
        borderWidth: 1,
        padding: spacing.lg,
        gap: spacing.xs,
        ...bayangan.kartu,
    },
    ditekan: { opacity: 0.8, transform: [{ scale: 0.985 }] },
    ikonKotak: {
        width: 42,
        height: 42,
        borderRadius: radius.md,
        alignItems: 'center',
        justifyContent: 'center',
        marginBottom: spacing.xs,
    },
    tileJudul: { fontSize: 15, fontWeight: '800' },
    tileKeterangan: { ...typography.caption },
});
