import { useState } from 'react';
import { Platform, Pressable, StyleSheet, Text, View } from 'react-native';
import DateTimePicker from '@react-native-community/datetimepicker';
import { colors, radius, spacing, typography } from '../theme';

/**
 * Pemilih jam yang selalu menghasilkan string `HH:MM` — bentuk yang
 * divalidasi backend (`date_format:H:i,H:i:s`) untuk kolom jam kerja.
 */
export default function TimeField({ label, value, onChange, error, hint, disabled }) {
    const [buka, setBuka] = useState(false);

    const nilaiWaktu = (() => {
        const d = new Date();
        const [j, m] = String(value || '').split(':');
        d.setHours(Number(j) || 0, Number(m) || 0, 0, 0);
        return d;
    })();

    const pilih = (event, dipilih) => {
        if (Platform.OS === 'android') setBuka(false);
        if (event?.type === 'dismissed' || !dipilih) return;

        const j = String(dipilih.getHours()).padStart(2, '0');
        const m = String(dipilih.getMinutes()).padStart(2, '0');
        onChange(`${j}:${m}`);
    };

    return (
        <View style={styles.field}>
            {!!label && <Text style={styles.label}>{label}</Text>}

            <Pressable
                onPress={() => !disabled && setBuka(true)}
                style={[styles.kotak, !!error && styles.kotakError, disabled && styles.kotakMati]}
            >
                <Text style={value ? styles.nilai : styles.placeholder}>{value || '— Pilih jam —'}</Text>
                <Text style={styles.ikon}>🕐</Text>
            </Pressable>

            {!!error && <Text style={styles.error}>{error}</Text>}
            {!!hint && !error && <Text style={typography.caption}>{hint}</Text>}

            {buka && (
                <>
                    <DateTimePicker
                        value={nilaiWaktu}
                        mode="time"
                        is24Hour
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
