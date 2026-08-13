import { useMemo, useState } from 'react';
import { FlatList, Modal, Pressable, StyleSheet, Text, TextInput, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { colors, radius, spacing, typography } from '../theme';

/**
 * Pemilih nilai berbentuk modal berisi daftar yang bisa dicari.
 * Dipakai menggantikan <Picker> agar tidak menambah dependensi native dan
 * tetap nyaman untuk daftar panjang (mis. jabatan, produk).
 */
export default function SelectField({ label, value, options = [], onChange, placeholder = '— Pilih —', error, hint, disabled }) {
    const [buka, setBuka] = useState(false);
    const [cari, setCari] = useState('');

    const terpilih = options.find((o) => String(o.value) === String(value));

    const hasil = useMemo(() => {
        const q = cari.trim().toLowerCase();
        if (!q) return options;
        return options.filter((o) => String(o.label).toLowerCase().includes(q));
    }, [cari, options]);

    return (
        <View style={styles.field}>
            {!!label && <Text style={styles.label}>{label}</Text>}

            <Pressable
                onPress={() => !disabled && setBuka(true)}
                style={[styles.kotak, !!error && styles.kotakError, disabled && styles.kotakMati]}
            >
                <Text style={terpilih ? styles.nilai : styles.placeholder} numberOfLines={1}>
                    {terpilih?.label || placeholder}
                </Text>
                <Text style={styles.panah}>▾</Text>
            </Pressable>

            {!!error && <Text style={styles.error}>{error}</Text>}
            {!!hint && !error && <Text style={typography.caption}>{hint}</Text>}

            <Modal visible={buka} animationType="slide" onRequestClose={() => setBuka(false)}>
                <SafeAreaView style={styles.modal}>
                    <View style={styles.modalHeader}>
                        <Text style={typography.heading}>{label || 'Pilih'}</Text>
                        <Pressable onPress={() => setBuka(false)} hitSlop={12}>
                            <Text style={styles.tutup}>Tutup</Text>
                        </Pressable>
                    </View>

                    {options.length > 8 && (
                        <TextInput
                            value={cari}
                            onChangeText={setCari}
                            placeholder="Cari…"
                            placeholderTextColor={colors.textMuted}
                            style={styles.cari}
                        />
                    )}

                    <FlatList
                        data={hasil}
                        keyExtractor={(o) => String(o.value)}
                        contentContainerStyle={styles.list}
                        ListEmptyComponent={
                            <Text style={[typography.muted, styles.kosong]}>
                                {options.length === 0 ? 'Belum ada pilihan tersedia.' : 'Tidak ada yang cocok.'}
                            </Text>
                        }
                        renderItem={({ item }) => {
                            const aktif = String(item.value) === String(value);
                            return (
                                <Pressable
                                    onPress={() => { onChange(item.value); setBuka(false); setCari(''); }}
                                    style={[styles.baris, aktif && styles.barisAktif]}
                                >
                                    <Text style={[styles.barisTeks, aktif && styles.barisTeksAktif]}>{item.label}</Text>
                                    {aktif && <Text style={styles.centang}>✓</Text>}
                                </Pressable>
                            );
                        }}
                    />
                </SafeAreaView>
            </Modal>
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
        gap: spacing.sm,
    },
    kotakError: { borderColor: colors.danger },
    kotakMati: { opacity: 0.6 },
    nilai: { flex: 1, fontSize: 15, color: colors.text },
    placeholder: { flex: 1, fontSize: 15, color: colors.textMuted },
    panah: { color: colors.textMuted, fontSize: 14 },
    error: { fontSize: 12, color: colors.danger },

    modal: { flex: 1, backgroundColor: colors.background },
    modalHeader: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        padding: spacing.lg,
    },
    tutup: { color: colors.primary, fontWeight: '600' },
    cari: {
        marginHorizontal: spacing.lg,
        marginBottom: spacing.sm,
        minHeight: 44,
        borderWidth: 1,
        borderColor: colors.border,
        borderRadius: radius.md,
        paddingHorizontal: spacing.md,
        backgroundColor: colors.surface,
        color: colors.text,
    },
    list: { paddingHorizontal: spacing.lg, paddingBottom: spacing.xxl, gap: spacing.sm },
    baris: {
        padding: spacing.md,
        borderRadius: radius.md,
        borderWidth: 1,
        borderColor: colors.border,
        backgroundColor: colors.surface,
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
    },
    barisAktif: { borderColor: colors.primary, backgroundColor: colors.primarySoft },
    barisTeks: { fontSize: 15, color: colors.text, flex: 1 },
    barisTeksAktif: { fontWeight: '700', color: colors.primaryDark },
    centang: { color: colors.primary, fontWeight: '800' },
    kosong: { textAlign: 'center', paddingVertical: spacing.xl },
});
