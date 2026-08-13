import { useCallback, useState } from 'react';
import { Pressable, StyleSheet, Text, View } from 'react-native';
import { Card, Halaman } from '../components/ui';
import DaftarData from '../components/DaftarData';
import DateField from '../components/DateField';
import { BadgeMode } from './BerandaScreen';
import { Jadwal } from '../api/endpoints';
import { jamSingkat, tanggalPendek } from '../utils/format';
import { colors, radius, spacing, typography } from '../theme';

const STATUS = [
    { key: 'semua', label: 'Semua' },
    { key: 'belum', label: 'Belum absen' },
    { key: 'berjalan', label: 'Bertugas' },
    { key: 'selesai', label: 'Selesai' },
];

const awalBulan = () => { const d = new Date(); return new Date(d.getFullYear(), d.getMonth(), 1).toISOString().slice(0, 10); };
const akhirBulan = () => { const d = new Date(); return new Date(d.getFullYear(), d.getMonth() + 1, 0).toISOString().slice(0, 10); };

export default function JadwalScreen({ navigation }) {
    const [status, setStatus] = useState('semua');
    const [dari, setDari] = useState(awalBulan());
    const [sampai, setSampai] = useState(akhirBulan());

    const ambil = useCallback(
        (page, cari, perPage) => Jadwal.daftar({ page, cari, per_page: perPage, status, dari, sampai }),
        [status, dari, sampai]
    );

    const filterAktif = (dari !== awalBulan() ? 1 : 0) + (sampai !== akhirBulan() ? 1 : 0);

    return (
        <Halaman judul="Jadwal Saya" warna="oranye" kembali={false} scroll={false}>
            <DaftarData
                key={`${status}-${dari}-${sampai}`}
                ambil={ambil}
                cariPlaceholder="Cari tanggal, shift, jabatan, status…"
                jumlahFilterAktif={filterAktif}
                filter={
                    <>
                        <DateField label="Dari tanggal" value={dari} onChange={setDari} />
                        <DateField label="Sampai tanggal" value={sampai} onChange={setSampai} />
                    </>
                }
                kepala={
                    <View style={styles.chipBaris}>
                        {STATUS.map((s) => (
                            <Pressable key={s.key} onPress={() => setStatus(s.key)}
                                style={[styles.chip, status === s.key && styles.chipAktif]}>
                                <Text style={[styles.chipTeks, status === s.key && styles.chipTeksAktif]}>{s.label}</Text>
                            </Pressable>
                        ))}
                    </View>
                }
                kosong={{ judul: 'Tidak ada jadwal', pesan: 'Belum ada jadwal pada rentang & filter ini.' }}
                render={(item) => (
                    <Pressable onPress={() => navigation.navigate('JadwalDetail', { id: item.penjadwalan_id })}>
                        <Card tone={item.mode === 'done' ? 'abu' : item.mode === 'out' ? 'hijau' : 'oranye'}>
                            <View style={styles.baris}>
                                <Text style={typography.heading}>{tanggalPendek(item.tanggal)}</Text>
                                <BadgeMode mode={item.mode} />
                            </View>
                            <Text style={typography.body}>
                                {item.jam_kerja?.nama || 'Shift'} · {jamSingkat(item.jam_kerja?.jam_datang)}–{jamSingkat(item.jam_kerja?.jam_pulang)}
                            </Text>
                            <Text style={typography.muted}>
                                {item.jabatan}{item.nozzles?.length ? ` · ${item.nozzles.join(', ')}` : ''}
                            </Text>
                        </Card>
                    </Pressable>
                )}
            />
        </Halaman>
    );
}

const styles = StyleSheet.create({
    chipBaris: { flexDirection: 'row', flexWrap: 'wrap', gap: spacing.sm, marginBottom: spacing.sm },
    chip: {
        paddingHorizontal: spacing.md, paddingVertical: 6, borderRadius: radius.pill,
        borderWidth: 1, borderColor: colors.border, backgroundColor: colors.surface,
    },
    chipAktif: { backgroundColor: colors.primary, borderColor: colors.primary },
    chipTeks: { fontSize: 12, color: colors.textMuted, fontWeight: '700' },
    chipTeksAktif: { color: '#fff' },
    baris: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' },
});
