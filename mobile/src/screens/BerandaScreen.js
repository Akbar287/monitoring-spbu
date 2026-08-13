import { useCallback } from 'react';
import { useFocusEffect } from '@react-navigation/native';
import { Pressable, StyleSheet, Text, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import {
    Badge, Button, Card, EmptyState, ErrorState, Halaman, Loading, Row, SectionTitle, Statistik,
} from '../components/ui';
import { Dashboard } from '../api/endpoints';
import { useMuat } from '../hooks/useMuat';
import { useAuth } from '../auth/AuthContext';
import { jam, jamSingkat, liter, rupiah, tanggalPanjang } from '../utils/format';
import { aksen, bayangan, colors, radius, spacing, typography } from '../theme';

export default function BerandaScreen({ navigation }) {
    const { user } = useAuth();
    const { data, memuat, menyegarkan, error, segarkan, muatUlang } = useMuat(
        useCallback(() => Dashboard.ringkasan(), [])
    );

    // segarkan tiap layar difokuskan agar angka absensi selalu terbaru
    useFocusEffect(useCallback(() => { muatUlang({ segarkan: true }); }, [muatUlang]));

    const judul = `Halo, ${user?.nama?.split(' ')[0] || 'Pegawai'}`;
    const subjudul = [user?.jabatan, user?.level].filter(Boolean).join(' · ');

    if (memuat && !data) {
        return <Halaman judul={judul} subjudul={subjudul} warna="indigo" kembali={false}><Loading /></Halaman>;
    }
    if (error && !data) {
        return (
            <Halaman judul={judul} subjudul={subjudul} warna="indigo" kembali={false}>
                <ErrorState pesan={error.message} onCoba={muatUlang} />
            </Halaman>
        );
    }

    const shift = data?.shift_berjalan;
    const jadwal = data?.jadwal_hari_ini;
    const ringkasan = data?.ringkasan;

    return (
        <Halaman
            judul={judul}
            subjudul={subjudul}
            warna="indigo"
            kembali={false}
            onRefresh={segarkan}
            refreshing={menyegarkan}
        >
            <Text style={typography.caption}>{tanggalPanjang(data?.tanggal)}</Text>

            {!!shift && (
                <Card gradien="hijau">
                    <View style={styles.barisAtas}>
                        <Text style={styles.labelTerang}>Shift sedang berjalan</Text>
                        <View style={styles.titikHidup}>
                            <Ionicons name="ellipse" size={8} color="#fff" />
                            <Text style={styles.labelTerang}>Bertugas</Text>
                        </View>
                    </View>
                    <Text style={styles.shiftNama}>{shift.jam_kerja?.nama || 'Shift'}</Text>
                    <Text style={styles.shiftInfo}>
                        Mulai {jam(shift.start_time)} · {tanggalPanjang(shift.tanggal)}
                    </Text>
                    <Button
                        label="Absen Pulang"
                        variant="ghost"
                        onPress={() => navigation.navigate('JadwalTab', {
                            screen: 'JadwalDetail',
                            params: { id: shift.penjadwalan_id },
                            initial: false,
                        })}
                    />
                </Card>
            )}

            <View style={styles.statistik}>
                <Statistik label="Jadwal hari ini" nilai={jadwal?.total ?? 0} warna="indigo" ikon="calendar-outline" />
                <Statistik
                    label="Belum absen"
                    nilai={jadwal?.belum_absen ?? 0}
                    warna={jadwal?.belum_absen ? 'oranye' : 'abu'}
                    ikon="alarm-outline"
                />
                <Statistik
                    label="Notifikasi"
                    nilai={data?.notifikasi_belum_dibaca ?? 0}
                    warna={data?.notifikasi_belum_dibaca ? 'merah' : 'abu'}
                    ikon="notifications-outline"
                    onPress={() => navigation.navigate('Notifikasi')}
                />
            </View>

            <SectionTitle
                aksi={
                    <Text style={styles.tautan} onPress={() => navigation.navigate('JadwalTab')}>
                        Lihat semua
                    </Text>
                }
            >
                Jadwal hari ini
            </SectionTitle>

            {(jadwal?.items || []).length === 0 ? (
                <Card>
                    <EmptyState judul="Tidak ada jadwal" pesan="Anda tidak dijadwalkan bertugas hari ini." />
                </Card>
            ) : (
                jadwal.items.map((item) => {
                    const warna = item.mode === 'done' ? 'abu' : item.mode === 'out' ? 'hijau' : 'oranye';

                    return (
                        <Pressable
                            key={item.penjadwalan_id}
                            onPress={() => navigation.navigate('JadwalTab', {
                                screen: 'JadwalDetail',
                                params: { id: item.penjadwalan_id },
                                initial: false,
                            })}
                        >
                            <Card tone={warna}>
                                <View style={styles.barisAtas}>
                                    <Text style={typography.heading}>{item.jam_kerja?.nama || 'Shift'}</Text>
                                    <BadgeMode mode={item.mode} />
                                </View>
                                <Text style={typography.muted}>
                                    {jamSingkat(item.jam_kerja?.jam_datang)} – {jamSingkat(item.jam_kerja?.jam_pulang)}
                                    {item.nozzles?.length ? ` · ${item.nozzles.join(', ')}` : ''}
                                </Text>
                            </Card>
                        </Pressable>
                    );
                })
            )}

            {!!ringkasan && Object.keys(ringkasan).length > 0 && (
                <>
                    <SectionTitle>Ringkasan operasional</SectionTitle>

                    <Card gradien="biru">
                        <Text style={styles.labelTerang}>{ringkasan.spbu}</Text>
                        <Text style={styles.angkaBesar}>{rupiah(ringkasan.penjualan_hari_ini)}</Text>
                        <Text style={styles.labelTerang}>Penjualan hari ini</Text>
                        <View style={styles.pemisahTerang} />
                        <Row terang label="Penjualan bulan ini" value={rupiah(ringkasan.penjualan_bulan_ini)} />
                        <Row terang label="Setoran menunggu" value={`${ringkasan.setoran_menunggu_persetujuan} shift`} />
                    </Card>

                    {(ringkasan.stok || []).length > 0 && (
                        <Card tone="teal">
                            <Text style={typography.heading}>Stok berjalan</Text>
                            {ringkasan.stok.map((s) => (
                                <Row key={s.produk} label={s.produk} value={liter(s.stok)} />
                            ))}
                        </Card>
                    )}
                </>
            )}
        </Halaman>
    );
}

export function BadgeMode({ mode }) {
    if (mode === 'done') return <Badge label="Selesai" tone="abu" />;
    if (mode === 'out') return <Badge label="Bertugas" tone="hijau" />;
    return <Badge label="Belum absen" tone="oranye" />;
}

const styles = StyleSheet.create({
    barisAtas: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', gap: spacing.sm },
    titikHidup: { flexDirection: 'row', alignItems: 'center', gap: spacing.xs },
    labelTerang: { fontSize: 12, fontWeight: '700', color: 'rgba(255,255,255,0.9)' },
    shiftNama: { fontSize: 22, fontWeight: '800', color: '#fff' },
    shiftInfo: { fontSize: 12, color: 'rgba(255,255,255,0.85)', marginBottom: spacing.sm },
    angkaBesar: { fontSize: 26, fontWeight: '800', color: '#fff' },
    pemisahTerang: { height: 1, backgroundColor: 'rgba(255,255,255,0.25)', marginVertical: spacing.sm },
    statistik: { flexDirection: 'row', gap: spacing.md },
    tautan: { color: colors.primary, fontWeight: '700', fontSize: 13 },
});
