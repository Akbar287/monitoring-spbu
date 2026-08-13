import { useCallback, useState } from 'react';
import { Pressable, StyleSheet, Text, View } from 'react-native';
import { Badge, Button, Card, Halaman } from '../components/ui';
import DaftarData from '../components/DaftarData';
import { Notifikasi } from '../api/endpoints';
import { jam, tanggalPendek } from '../utils/format';
import { colors, radius, spacing, typography } from '../theme';

export default function NotifikasiScreen() {
    const [hanyaBaru, setHanyaBaru] = useState(false);
    const [versi, setVersi] = useState(0); // paksa muat ulang setelah tandai dibaca

    const ambil = useCallback(
        (page, cari, perPage) => Notifikasi.daftar({
            page, cari, per_page: perPage,
            hanya_belum_dibaca: hanyaBaru ? 1 : undefined,
        }),
        [hanyaBaru, versi] // eslint-disable-line react-hooks/exhaustive-deps
    );

    const tandai = async (item) => {
        if (item.is_read) return;
        try { await Notifikasi.baca(item.notifikasi_id); setVersi((v) => v + 1); } catch { /* diamkan */ }
    };

    const tandaiSemua = async () => {
        try { await Notifikasi.bacaSemua(); setVersi((v) => v + 1); } catch { /* diamkan */ }
    };

    return (
        <Halaman judul="Notifikasi" warna="merah" scroll={false}>
            <DaftarData
                key={`${hanyaBaru}-${versi}`}
                ambil={ambil}
                cariPlaceholder="Cari judul atau isi notifikasi…"
                jumlahFilterAktif={hanyaBaru ? 1 : 0}
                filter={
                    <>
                        <Pressable onPress={() => setHanyaBaru((v) => !v)}
                            style={[styles.chip, hanyaBaru && styles.chipAktif]}>
                            <Text style={[styles.chipTeks, hanyaBaru && styles.chipTeksAktif]}>
                                Hanya yang belum dibaca
                            </Text>
                        </Pressable>
                        <Button label="Tandai semua dibaca" variant="ghost" onPress={tandaiSemua} />
                    </>
                }
                kosong={{ judul: 'Belum ada notifikasi', pesan: 'Pemberitahuan dari sistem akan muncul di sini.' }}
                render={(item) => (
                    <Pressable onPress={() => tandai(item)}>
                        <Card tone={item.is_read ? undefined : 'merah'}>
                            <View style={styles.baris}>
                                <Text style={[typography.heading, styles.judul]} numberOfLines={2}>{item.judul}</Text>
                                {!item.is_read && <Badge label="Baru" tone="merah" />}
                            </View>
                            <Text style={typography.body}>{item.content}</Text>
                            <Text style={typography.caption}>
                                {tanggalPendek(item.created_at)} · {jam(item.created_at)}
                            </Text>
                        </Card>
                    </Pressable>
                )}
            />
        </Halaman>
    );
}

const styles = StyleSheet.create({
    chip: {
        paddingHorizontal: spacing.md, paddingVertical: 8, borderRadius: radius.pill,
        borderWidth: 1, borderColor: colors.border, backgroundColor: colors.surface, alignSelf: 'flex-start',
    },
    chipAktif: { backgroundColor: colors.primary, borderColor: colors.primary },
    chipTeks: { fontSize: 12, color: colors.textMuted, fontWeight: '700' },
    chipTeksAktif: { color: '#fff' },
    baris: { flexDirection: 'row', alignItems: 'flex-start', justifyContent: 'space-between', gap: spacing.sm },
    judul: { flex: 1 },
});
