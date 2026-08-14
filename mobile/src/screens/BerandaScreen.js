import { useCallback, useEffect, useState } from 'react';
import { useFocusEffect } from '@react-navigation/native';
import { Pressable, StyleSheet, Text, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import {
    Badge, Button, Card, EmptyState, ErrorState, Halaman, Loading, Notice, SectionTitle, Statistik,
} from '../components/ui';
import { Grafik, formatNilai } from '../components/Grafik';
import { Dashboard } from '../api/endpoints';
import { useMuat } from '../hooks/useMuat';
import { useAuth } from '../auth/AuthContext';
import { jam, jamSingkat, tanggalPanjang } from '../utils/format';
import { aksen, colors, radius, spacing, typography } from '../theme';

/**
 * Beranda.
 *
 * Bagian atas selalu personal (shift berjalan, jadwal, notifikasi).
 * Untuk jabatan berakses penuh, di bawahnya muncul panel analitik yang
 * dikelompokkan per domain menu — angka, grafik, dan kalimat interpretasi
 * yang sudah dihitung server (GET /analitik).
 */
export default function BerandaScreen({ navigation }) {
    const { user } = useAuth();
    const stakeholder = user?.akses_penuh === true;
    // Top Manajemen tidak punya jadwal kerja — blok shift & absensi disembunyikan
    const punyaJadwal = user?.tanpa_jadwal !== true;

    const { data, memuat, menyegarkan, error, segarkan, muatUlang } = useMuat(
        useCallback(() => Dashboard.ringkasan(), [])
    );

    const [analitik, setAnalitik] = useState(null);
    const [gagalAnalitik, setGagalAnalitik] = useState(null);

    const muatAnalitik = useCallback(() => {
        if (!stakeholder) return;
        Dashboard.analitik().then((d) => { setAnalitik(d); setGagalAnalitik(null); })
            .catch((e) => setGagalAnalitik(e));
    }, [stakeholder]);

    useEffect(() => { muatAnalitik(); }, [muatAnalitik]);
    useFocusEffect(useCallback(() => {
        muatUlang({ segarkan: true });
        muatAnalitik();
    }, [muatUlang, muatAnalitik]));

    const judul = `Halo, ${user?.nama?.split(' ')[0] || 'Pegawai'}`;
    const subjudul = [user?.jabatan, user?.level].filter(Boolean).join(' · ');

    const bungkus = (isi) => (
        <Halaman judul={judul} subjudul={subjudul} warna="indigo" kembali={false}
            onRefresh={() => { segarkan(); muatAnalitik(); }} refreshing={menyegarkan}>
            {isi}
        </Halaman>
    );

    if (memuat && !data) return bungkus(<Loading />);
    if (error && !data) return bungkus(<ErrorState pesan={error.message} onCoba={muatUlang} />);

    const shift = data?.shift_berjalan;
    const jadwal = data?.jadwal_hari_ini;

    return bungkus(
        <>
            <Text style={typography.caption}>{tanggalPanjang(data?.tanggal)}</Text>

            {punyaJadwal && !!shift && (
                <Card gradien="hijau">
                    <View style={styles.barisAtas}>
                        <Text style={styles.labelTerang}>Shift sedang berjalan</Text>
                        <View style={styles.titikHidup}>
                            <Ionicons name="ellipse" size={8} color="#fff" />
                            <Text style={styles.labelTerang}>Bertugas</Text>
                        </View>
                    </View>
                    <Text style={styles.shiftNama}>{shift.jam_kerja?.nama || 'Shift'}</Text>
                    <Text style={styles.shiftInfo}>Mulai {jam(shift.start_time)} · {tanggalPanjang(shift.tanggal)}</Text>
                    <Button label="Absen Pulang" variant="ghost"
                        onPress={() => navigation.navigate('JadwalTab', {
                            screen: 'JadwalDetail', params: { id: shift.penjadwalan_id }, initial: false,
                        })} />
                </Card>
            )}

            <View style={styles.statistik}>
                {punyaJadwal && (
                    <>
                        <Statistik label="Jadwal hari ini" nilai={jadwal?.total ?? 0} warna="indigo" ikon="calendar-outline" />
                        <Statistik label="Belum absen" nilai={jadwal?.belum_absen ?? 0}
                            warna={jadwal?.belum_absen ? 'oranye' : 'abu'} ikon="alarm-outline" />
                    </>
                )}
                <Statistik label="Notifikasi" nilai={data?.notifikasi_belum_dibaca ?? 0}
                    warna={data?.notifikasi_belum_dibaca ? 'merah' : 'abu'} ikon="notifications-outline"
                    onPress={() => navigation.navigate('Notifikasi')} />
            </View>

            {punyaJadwal && (
                <>
                    <SectionTitle
                        aksi={<Text style={styles.tautan} onPress={() => navigation.navigate('JadwalTab')}>Lihat semua</Text>}
                    >
                        Jadwal hari ini
                    </SectionTitle>

                    {(jadwal?.items || []).length === 0 ? (
                        <Card><EmptyState judul="Tidak ada jadwal" pesan="Anda tidak dijadwalkan bertugas hari ini." /></Card>
                    ) : jadwal.items.map((item) => (
                        <Pressable key={item.penjadwalan_id}
                            onPress={() => navigation.navigate('JadwalTab', {
                                screen: 'JadwalDetail', params: { id: item.penjadwalan_id }, initial: false,
                            })}>
                            <Card tone={item.mode === 'done' ? 'abu' : item.mode === 'out' ? 'hijau' : 'oranye'}>
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
                    ))}
                </>
            )}

            {stakeholder && (
                <PanelAnalitik data={analitik} gagal={gagalAnalitik} onCoba={muatAnalitik} navigation={navigation} />
            )}
        </>
    );
}

/** Panel monitoring stakeholder: satu blok per domain menu. */
function PanelAnalitik({ data, gagal, onCoba, navigation }) {
    if (gagal) {
        return (
            <>
                <SectionTitle>Monitoring</SectionTitle>
                <Card><ErrorState pesan={gagal.message} onCoba={onCoba} /></Card>
            </>
        );
    }
    if (!data) {
        return (
            <>
                <SectionTitle>Monitoring</SectionTitle>
                <Card><Loading label="Menyiapkan analitik…" /></Card>
            </>
        );
    }

    return (
        <>
            <SectionTitle>Monitoring · {data.spbu}</SectionTitle>
            <Text style={typography.caption}>
                Periode berjalan {data.periode.awal_bulan} s/d {data.periode.akhir_bulan}
            </Text>

            {data.domain.map((d) => (
                <DomainBlok key={d.kunci} domain={d} navigation={navigation} />
            ))}

            <Text style={[typography.caption, styles.kaki]}>
                Angka keuangan diturunkan dari buku besar — sumber yang sama dengan menu Financial.
            </Text>
        </>
    );
}

const TUJUAN = {
    penjualan: ['PersediaanTab', 'Setoran'],
    persediaan: ['PersediaanTab', 'Monitoring'],
    totalisator: ['PersediaanTab', 'Totalisator'],
    procurement: ['ProcurementTab', null],
    keuangan: ['MasterTab', 'Laporan'],
};

function DomainBlok({ domain, navigation }) {
    const [buka, setBuka] = useState(true);
    const a = aksen[domain.warna] || aksen.indigo;

    const buka_ = () => {
        const [tab, layar] = TUJUAN[domain.kunci] || [];
        if (!tab) return;
        if (domain.kunci === 'keuangan') {
            navigation.navigate(tab, { screen: 'Laporan', params: { jenis: 'laba-rugi' }, initial: false });
        } else if (layar) {
            navigation.navigate(tab, { screen: layar, initial: false });
        } else {
            navigation.navigate(tab);
        }
    };

    return (
        <Card style={styles.domain}>
            <Pressable onPress={() => setBuka((v) => !v)} style={styles.domainKepala}>
                <View style={[styles.ikon, { backgroundColor: a.lembut }]}>
                    <Ionicons name={domain.ikon} size={20} color={a.solid} />
                </View>
                <Text style={[typography.heading, styles.domainJudul]}>{domain.judul}</Text>
                <Ionicons name={buka ? 'chevron-up' : 'chevron-down'} size={18} color={colors.textMuted} />
            </Pressable>

            <View style={styles.kpiGrid}>
                {domain.kpi.map((k) => (
                    <View key={k.label} style={[styles.kpi, { borderColor: a.solid + '33' }]}>
                        <Text style={[styles.kpiNilai, { color: a.solid }]} numberOfLines={1}>
                            {formatNilai(k.nilai, k.format)}
                        </Text>
                        <Text style={styles.kpiLabel} numberOfLines={2}>{k.label}</Text>
                    </View>
                ))}
            </View>

            {buka && (
                <>
                    {domain.chart.map((c, i) => (
                        <View key={i} style={styles.chartKotak}>
                            <Grafik jenis={c.jenis} judul={c.judul} data={c.data} format={c.format} warna={domain.warna} />
                        </View>
                    ))}

                    {domain.insight.map((ins, i) => (
                        <Notice key={i} tone={ins.nada}>{ins.teks}</Notice>
                    ))}

                    {!!domain.catatan && <Text style={typography.caption}>{domain.catatan}</Text>}

                    {!!TUJUAN[domain.kunci] && (
                        <Button label={`Buka ${domain.judul}`} variant="ghost" onPress={buka_} />
                    )}
                </>
            )}
        </Card>
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
    labelTerang: { fontSize: 13, color: 'rgba(255,255,255,0.9)', fontWeight: '600' },
    shiftNama: { fontSize: 20, fontWeight: '800', color: '#fff' },
    shiftInfo: { fontSize: 13, color: 'rgba(255,255,255,0.85)', marginBottom: spacing.sm },
    statistik: { flexDirection: 'row', gap: spacing.md },
    tautan: { color: colors.primary, fontWeight: '700', fontSize: 13 },

    domain: { gap: spacing.md },
    domainKepala: { flexDirection: 'row', alignItems: 'center', gap: spacing.md },
    domainJudul: { flex: 1 },
    ikon: { width: 36, height: 36, borderRadius: radius.md, alignItems: 'center', justifyContent: 'center' },

    kpiGrid: { flexDirection: 'row', flexWrap: 'wrap', gap: spacing.sm },
    kpi: {
        flexGrow: 1, flexBasis: '47%', borderWidth: 1, borderRadius: radius.md,
        paddingVertical: spacing.sm, paddingHorizontal: spacing.md, gap: 2,
    },
    kpiNilai: { fontSize: 15, fontWeight: '800' },
    kpiLabel: { fontSize: 11, color: colors.textMuted, fontWeight: '600' },

    chartKotak: {
        borderTopWidth: 1, borderTopColor: colors.border, paddingTop: spacing.md,
    },
    kaki: { textAlign: 'center', marginTop: spacing.sm },
});
