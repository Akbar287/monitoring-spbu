import { useCallback, useState } from 'react';
import { useFocusEffect } from '@react-navigation/native';
import { StyleSheet, Text, View } from 'react-native';
import { Button, Card, ErrorState, Halaman, Loading, Notice, Row, SectionTitle } from '../../components/ui';
import { Persediaan } from '../../api/endpoints';
import { useMuat } from '../../hooks/useMuat';
import { liter, tanggalPendek } from '../../utils/format';
import { colors, spacing, typography } from '../../theme';

export default function MonitoringDetailScreen({ route, navigation }) {
    const { id, judul } = route.params;
    const [riwayat, setRiwayat] = useState(null);

    const { data, memuat, error, muatUlang } = useMuat(
        useCallback(() => Persediaan.monitoring.detail(id), [id])
    );

    useFocusEffect(useCallback(() => {
        muatUlang({ segarkan: true });
        Persediaan.monitoring.riwayat(id, { per_page: 15 }).then(setRiwayat).catch(() => setRiwayat(null));
    }, [muatUlang, id]));

    const bungkus = (isi) => <Halaman judul={judul || 'Detail Stok'} warna="indigo">{isi}</Halaman>;
    if (memuat && !data) return bungkus(<Loading />);
    if (error && !data) return bungkus(<ErrorState pesan={error.message} onCoba={muatUlang} />);

    return bungkus(
        <>
            <Card gradien="indigo">
                <Text style={styles.labelTerang}>Saldo tercatat</Text>
                <Text style={styles.angka}>{liter(data.stok)}</Text>
                <Text style={styles.labelTerang}>{data.jumlah_dombak} dombak · {data.total_dokumen} dokumen</Text>
            </Card>

            <SectionTitle>Rincian per dombak</SectionTitle>
            <Card>
                {data.dombak.length === 0
                    ? <Notice tone="warning">Belum ada dombak terhubung ke produk ini.</Notice>
                    : data.dombak.map((d) => <Row key={d.dombak_id} label={d.dombak} value={liter(d.stok)} />)}
            </Card>

            <SectionTitle
                aksi={
                    <Text style={{ color: '#6366f1', fontWeight: '700', fontSize: 13 }}
                        onPress={() => navigation.navigate('MonitoringRiwayat', { id, judul })}>
                        Riwayat lengkap
                    </Text>
                }
            >
                Pergerakan terakhir
            </SectionTitle>
            {!riwayat ? <Card><Text style={typography.muted}>Memuat riwayat…</Text></Card>
                : riwayat.items.length === 0
                    ? <Card><Text style={typography.muted}>Belum ada pergerakan.</Text></Card>
                    : riwayat.items.map((d) => (
                        <Card key={d.id} tone={d.is_confirmation ? 'abu' : 'oranye'}>
                            <View style={styles.baris}>
                                <Text style={typography.heading} numberOfLines={1}>{d.jenis}</Text>
                                <Text style={typography.caption}>{tanggalPendek(d.tanggal)}</Text>
                            </View>
                            <Text style={typography.muted}>{d.dombak} · {d.jam_kerja}</Text>
                            <Row label="Stok awal" value={liter(d.stok_awal)} />
                            <Row label="Stok akhir" value={liter(d.stok_akhir)} />
                            <Row label="Selisih" value={liter(d.selisih)} />
                            {!!d.losses && (
                                <Row
                                    label={`Losses (${d.losses.jenis})`}
                                    value={`${d.losses.simbol === 'kurang' ? '−' : '+'} ${liter(d.losses.jumlah)}`}
                                />
                            )}
                        </Card>
                    ))}
        </>
    );
}

const styles = StyleSheet.create({
    labelTerang: { fontSize: 13, color: 'rgba(255,255,255,0.9)', fontWeight: '600' },
    angka: { fontSize: 26, fontWeight: '800', color: '#fff' },
    baris: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
});
