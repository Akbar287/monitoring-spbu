import { Pressable, StyleSheet, Text, View } from 'react-native';
import { colors, radius, spacing, typography } from '../theme';

/**
 * Pilihan ganda berbentuk chip. Cocok untuk daftar pendek seperti hari;
 * nilainya array of value.
 */
export default function MultiSelectField({ label, value = [], options = [], onChange, error, hint, disabled }) {
    const terpilih = (Array.isArray(value) ? value : []).map(String);

    const alih = (v) => {
        const key = String(v);
        onChange(terpilih.includes(key) ? terpilih.filter((x) => x !== key) : [...terpilih, key]);
    };

    return (
        <View style={styles.field}>
            {!!label && <Text style={styles.label}>{label}</Text>}

            {options.length === 0 ? (
                <Text style={typography.muted}>Belum ada pilihan tersedia.</Text>
            ) : (
                <View style={styles.chips}>
                    {options.map((o) => {
                        const aktif = terpilih.includes(String(o.value));
                        return (
                            <Pressable
                                key={String(o.value)}
                                onPress={() => !disabled && alih(o.value)}
                                style={[styles.chip, aktif && styles.chipAktif, disabled && styles.chipMati]}
                            >
                                <Text style={[styles.chipTeks, aktif && styles.chipTeksAktif]}>{o.label}</Text>
                            </Pressable>
                        );
                    })}
                </View>
            )}

            {!!error && <Text style={styles.error}>{error}</Text>}
            {!!hint && !error && <Text style={typography.caption}>{hint}</Text>}
        </View>
    );
}

const styles = StyleSheet.create({
    field: { gap: spacing.xs },
    label: { fontSize: 13, fontWeight: '600', color: colors.text },
    chips: { flexDirection: 'row', flexWrap: 'wrap', gap: spacing.sm },
    chip: {
        paddingHorizontal: spacing.md,
        paddingVertical: 8,
        borderRadius: radius.pill,
        borderWidth: 1,
        borderColor: colors.border,
        backgroundColor: colors.surface,
    },
    chipAktif: { backgroundColor: colors.primary, borderColor: colors.primary },
    chipMati: { opacity: 0.6 },
    chipTeks: { fontSize: 13, color: colors.textMuted, fontWeight: '600' },
    chipTeksAktif: { color: '#fff' },
    error: { fontSize: 12, color: colors.danger },
});
