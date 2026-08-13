import { useCallback, useState } from 'react';
import { StyleSheet, Text, View } from 'react-native';
import { Badge, Card, ErrorState, Halaman, Loading, Row, SectionTitle } from '../../components/ui';
import DateField from '../../components/DateField';
import { Persediaan } from '../../api/endpoints';
import { useMuat } from '../../hooks/useMuat';
import { liter } from '../../utils/format';
import { spacing, typography } from '../../theme';

/** Rekap seluruh pergerakan stok pada satu tanggal. */
export default function StokSummaryScreen() {
    const [tanggal, setTanggal] = useState(new Date().toISOString().slice(0, 10));

    const { data, memuat, menyegarkan, error, segarkan, muatUlang } = useMuat(
        useCallback(() => Persediaan.summary(tanggal), [tanggal]),
        [tanggal]
    );

    const pemilih = (
        <Card>
            <DateField label="Tanggal laporan" value={tanggal} onChange={setTanggal} />
        </Card>
    );

    return (
        <Halaman judul="Stok Summary" subjudul={data?.label_tanggal} warna="biru"
            onRefresh={segarkan} refreshing={menyegarkan}>
            {pemilih}

            {memuat && !data ? <Loading />
                : error && !data ? <ErrorState pesan={error.message} onCoba={muatUlang} />
                : (
                    <>
                        <Card tone="biru">
                            <Row label="Total pergerakan" value={`${data.total_gerakan} dokumen`} />
                        </Card>

                        {data.produk.map((p) => (
                            <View key={p.produk_id} style={styles.grup}>
                                <SectionTitle>{p.produk}</SectionTitle>
                                <Card tone="teal">
                                    <Row label="Saldo sekarang" value={liter(p.stok_sekarang)} />
                                    <Row label="Gerakan hari ini" value={`${p.jumlah_gerakan}`} />
                                </Card>

                                {p.items.length === 0 ? (
                                    <Card><Text style={typography.muted}>Tidak ada pergerakan.</Text></Card>
                                ) : p.items.map((it) => (
                                    <Card key={it.id} tone={it.is_confirmation ? 'abu' : 'oranye'}>
                                        <View style={styles.baris}>
                                            <Text style={typography.heading} numberOfLines={1}>{it.jenis}</Text>
                                            <Badge label={it.is_confirmation ? 'OK' : 'Menunggu'}
                                                tone={it.is_confirmation ? 'hijau' : 'oranye'} />
                                        </View>
                                        <Text style={typography.caption}>
                                            {it.dombak} · {it.jam_kerja} · {String(it.waktu ?? '').slice(0, 5)}
                                        </Text>
                                        {it.rows.map((r, i) => <Row key={i} label={r.label} value={r.value} />)}
                                    </Card>
                                ))}
                            </View>
                        ))}
                    </>
                )}
        </Halaman>
    );
}

const styles = StyleSheet.create({
    grup: { gap: spacing.md },
    baris: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', gap: spacing.sm },
});
