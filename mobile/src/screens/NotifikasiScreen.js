import { useCallback, useState } from 'react';
import { FlatList, Pressable, RefreshControl, StyleSheet, Text, View } from 'react-native';
import { Halaman, Badge, Card, EmptyState, ErrorState, Loading } from '../components/ui';
import { Notifikasi } from '../api/endpoints';
import { useMuat } from '../hooks/useMuat';
import { tanggalPendek, jam } from '../utils/format';
import { colors, spacing, typography } from '../theme';

export default function NotifikasiScreen() {
    const [menandai, setMenandai] = useState(false);

    const { data, setData, memuat, menyegarkan, error, segarkan, muatUlang } = useMuat(
        useCallback(() => Notifikasi.daftar({ per_page: 50 }), [])
    );

    const items = data?.items || [];
    const belumDibaca = items.filter((n) => !n.is_read).length;

    const tandaiSatu = async (item) => {
        if (item.is_read) return;
        // optimistis: tandai lokal dulu supaya terasa instan
        setData((lama) => ({
            ...lama,
            items: lama.items.map((n) =>
                n.notifikasi_id === item.notifikasi_id ? { ...n, is_read: true } : n
            ),
        }));
        try {
            await Notifikasi.baca(item.notifikasi_id);
        } catch {
            segarkan(); // gagal -> ambil ulang keadaan sebenarnya
        }
    };

    const tandaiSemua = async () => {
        setMenandai(true);
        try {
            await Notifikasi.bacaSemua();
            await segarkan();
        } finally {
            setMenandai(false);
        }
    };

    if (memuat && !data) return <Halaman judul={'Notifikasi'} warna="merah" scroll={false} padded={false}><Loading /></Halaman>;
    if (error && !data) {
        return <Halaman judul={'Notifikasi'} warna="merah" scroll={false} padded={false}><ErrorState pesan={error.message} onCoba={muatUlang} /></Halaman>;
    }

    return (
        <Halaman judul={'Notifikasi'} warna="merah" scroll={false} padded={false}>
            {belumDibaca > 0 && (
                <View style={styles.bar}>
                    <Text style={typography.muted}>{belumDibaca} belum dibaca</Text>
                    <Pressable onPress={tandaiSemua} disabled={menandai}>
                        <Text style={styles.tautan}>
                            {menandai ? 'Menandai…' : 'Tandai semua dibaca'}
                        </Text>
                    </Pressable>
                </View>
            )}

            <FlatList
                data={items}
                keyExtractor={(item) => item.notifikasi_id}
                contentContainerStyle={styles.list}
                refreshControl={
                    <RefreshControl
                        refreshing={menyegarkan}
                        onRefresh={segarkan}
                        colors={[colors.primary]}
                        tintColor={colors.primary}
                    />
                }
                ListEmptyComponent={
                    <EmptyState judul="Belum ada notifikasi" pesan="Pemberitahuan dari sistem akan muncul di sini." />
                }
                renderItem={({ item }) => (
                    <Pressable onPress={() => tandaiSatu(item)}>
                        <Card tone={item.is_read ? undefined : "merah"}>
                            <View style={styles.baris}>
                                <Text style={[typography.heading, styles.judul]} numberOfLines={2}>
                                    {item.judul}
                                </Text>
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
    screen: { flex: 1, backgroundColor: colors.background },
    bar: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        paddingHorizontal: spacing.lg,
        paddingTop: spacing.md,
    },
    tautan: { color: colors.primary, fontWeight: '600', fontSize: 13 },
    list: { padding: spacing.lg, gap: spacing.md, flexGrow: 1 },
    kartuBaru: { borderColor: colors.primary, backgroundColor: colors.primarySoft },
    baris: { flexDirection: 'row', alignItems: 'flex-start', justifyContent: 'space-between', gap: spacing.sm },
    judul: { flex: 1 },
});
