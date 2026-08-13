import { Pressable, StyleSheet, Text, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { colors, radius, spacing, typography } from '../theme';

/**
 * Kontrol halaman bernomor (bukan gulir tak berujung) — dipakai daftar
 * Totalisator yang datanya ribuan baris.
 */
export default function Paginasi({ meta, onPindah, sibuk }) {
    if (!meta || meta.last_page <= 1) {
        return meta ? <Text style={styles.info}>{meta.total} data</Text> : null;
    }

    const { current_page: kini, last_page: akhir, total } = meta;
    const bisaMundur = kini > 1 && !sibuk;
    const bisaMaju = kini < akhir && !sibuk;

    return (
        <View style={styles.bar}>
            <Tombol ikon="chevron-back" aktif={bisaMundur} onPress={() => onPindah(kini - 1)} />
            <View style={styles.tengah}>
                <Text style={styles.halaman}>Halaman {kini} / {akhir}</Text>
                <Text style={typography.caption}>{total} data</Text>
            </View>
            <Tombol ikon="chevron-forward" aktif={bisaMaju} onPress={() => onPindah(kini + 1)} />
        </View>
    );
}

function Tombol({ ikon, aktif, onPress }) {
    return (
        <Pressable
            onPress={aktif ? onPress : undefined}
            disabled={!aktif}
            style={({ pressed }) => [styles.tombol, !aktif && styles.mati, pressed && aktif && { opacity: 0.7 }]}
        >
            <Ionicons name={ikon} size={20} color={aktif ? colors.primary : colors.textMuted} />
        </Pressable>
    );
}

const styles = StyleSheet.create({
    bar: {
        flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between',
        backgroundColor: colors.surface, borderRadius: radius.lg, borderWidth: 1,
        borderColor: colors.border, padding: spacing.sm, marginTop: spacing.sm,
    },
    tengah: { alignItems: 'center' },
    halaman: { fontSize: 13, fontWeight: '700', color: colors.text },
    tombol: {
        width: 40, height: 40, borderRadius: radius.md, alignItems: 'center',
        justifyContent: 'center', backgroundColor: colors.background,
    },
    mati: { opacity: 0.4 },
    info: { ...typography.caption, textAlign: 'center', marginTop: spacing.sm },
});
