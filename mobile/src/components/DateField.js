import { useState } from 'react';
import { Platform, Pressable, StyleSheet, Text, View } from 'react-native';
import DateTimePicker from '@react-native-community/datetimepicker';
import { tanggalPanjang } from '../utils/format';
import { colors, radius, spacing, typography } from '../theme';

/**
 * Pemilih tanggal yang selalu menghasilkan string `Y-m-d` — format yang
 * divalidasi backend (`date_format:Y-m-d`).
 */
export default function DateField({ label, value, onChange, error, hint, disabled }) {
    const [buka, setBuka] = useState(false);

    const nilaiTanggal = value ? new Date(`${value}T00:00:00`) : new Date();

    const pilih = (event, dipilih) => {
        // Android menutup dialog sendiri; iOS memakai tampilan spinner inline
        if (Platform.OS === 'android') setBuka(false);
        if (event?.type === 'dismissed' || !dipilih) return;

        const y = dipilih.getFullYear();
        const m = String(dipilih.getMonth() + 1).padStart(2, '0');
        const d = String(dipilih.getDate()).padStart(2, '0');
        onChange(`${y}-${m}-${d}`);
    };

    return (
        <View style={styles.field}>
            {!!label && <Text style={styles.label}>{label}</Text>}

            <Pressable
                onPress={() => !disabled && setBuka(true)}
                style={[styles.kotak, !!error && styles.kotakError, disabled && styles.kotakMati]}
            >
                <Text style={value ? styles.nilai : styles.placeholder}>
                    {value ? tanggalPanjang(value) : '— Pilih tanggal —'}
                </Text>
                <Text style={styles.ikon}>📅</Text>
            </Pressable>

            {!!error && <Text style={styles.error}>{error}</Text>}
            {!!hint && !error && <Text style={typography.caption}>{hint}</Text>}

            {buka && (
                <>
                    <DateTimePicker
                        value={nilaiTanggal}
                        mode="date"
                        display={Platform.OS === 'ios' ? 'spinner' : 'default'}
                        onChange={pilih}
                    />
                    {Platform.OS === 'ios' && (
                        <Pressable onPress={() => setBuka(false)} style={styles.selesai}>
                            <Text style={styles.selesaiTeks}>Selesai</Text>
                        </Pressable>
                    )}
                </>
            )}
        </View>
    );
}

const styles = StyleSheet.create({
    field: { gap: spacing.xs },
    label: { fontSize: 13, fontWeight: '600', color: colors.text },
    kotak: {
        minHeight: 46,
        borderWidth: 1,
        borderColor: colors.border,
        borderRadius: radius.md,
        paddingHorizontal: spacing.md,
        backgroundColor: colors.surface,
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'space-between',
    },
    kotakError: { borderColor: colors.danger },
    kotakMati: { opacity: 0.6 },
    nilai: { fontSize: 15, color: colors.text },
    placeholder: { fontSize: 15, color: colors.textMuted },
    ikon: { fontSize: 15 },
    error: { fontSize: 12, color: colors.danger },
    selesai: { alignSelf: 'flex-end', padding: spacing.sm },
    selesaiTeks: { color: colors.primary, fontWeight: '700' },
});
