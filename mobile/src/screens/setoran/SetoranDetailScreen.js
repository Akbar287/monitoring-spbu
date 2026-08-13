import { useCallback, useState } from 'react';
import { useFocusEffect } from '@react-navigation/native';
import { Alert, Pressable, StyleSheet, Text, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { Badge, Button, Card, ErrorState, Halaman, Loading, Notice, Row, SectionTitle } from '../../components/ui';
import { Setoran } from '../../api/endpoints';
import { useMuat } from '../../hooks/useMuat';
import { rupiah, tanggalPanjang } from '../../utils/format';
import { colors, spacing, typography } from '../../theme';

const URUT = ['open', 'penitipan', 'closing', 'bank', 'selesai'];
const NAMA = { open: 'Open', penitipan: 'Penitipan', closing: 'Closing', bank: 'Bank', selesai: 'Selesai' };

export default function SetoranDetailScreen({ route, navigation }) {
    const { id } = route.params;
    const [proses, setProses] = useState(false);
    const [pesan, setPesan] = useState(null);

    const { data, memuat, error, muatUlang } = useMuat(
        useCallback(() => Setoran.detail(id), [id])
    );
    useFocusEffect(useCallback(() => { muatUlang({ segarkan: true }); }, [muatUlang]));

    const bungkus = (isi) => <Halaman judul="Detail Setoran" warna="hijau">{isi}</Halaman>;
    if (memuat && !data) return bungkus(<Loading />);
    if (error && !data) return bungkus(<ErrorState pesan={error.message} onCoba={muatUlang} />);

    const jalankan = async (fn, sukses) => {
        setProses(true); setPesan(null);
        try { await fn(); setPesan({ tone: 'success', teks: sukses }); muatUlang({ segarkan: true }); }
        catch (e) { setPesan({ tone: 'danger', teks: e.message }); }
        finally { setProses(false); }
    };

    const konfirmasiAksi = (judul, isi, fn, sukses) => Alert.alert(judul, isi,
        [{ text: 'Batal', style: 'cancel' }, { text: 'Lanjutkan', onPress: () => jalankan(fn, sukses) }]);

    const posisi = URUT.indexOf(data.tahap);
    const keduanyaSetuju = data.is_accepted_admin && data.is_accepted_top;

    return bungkus(
        <>
            {!!pesan && <Notice tone={pesan.tone}>{pesan.teks}</Notice>}

            {/* alur tahap */}
            <Card>
                <View style={styles.alur}>
                    {URUT.map((t, i) => (
                        <View key={t} style={styles.langkah}>
                            <View style={[styles.titik, i <= posisi && styles.titikAktif]}>
                                <Text style={[styles.titikTeks, i <= posisi && styles.titikTeksAktif]}>{i + 1}</Text>
                            </View>
                            <Text style={[styles.langkahTeks, i === posisi && styles.langkahAktif]}>{NAMA[t]}</Text>
                        </View>
                    ))}
                </View>
            </Card>

            <Card gradien="hijau">
                <Text style={styles.labelTerang}>{tanggalPanjang(data.tanggal)} · {data.jam_kerja}</Text>
                <Text style={styles.angka}>{rupiah(data.total_penjualan)}</Text>
                <View style={styles.pemisah} />
                <Row terang label="Uang kotak" value={rupiah(data.total_uang_kotak)} />
                <Row terang label="Selisih" value={rupiah(data.selisih)} />
                <Row terang label="SPBU" value={data.spbu} />
            </Card>

            {keduanyaSetuju && (
                <Notice tone="success">
                    Setoran sudah disetujui admin & top manajemen — jurnal penjualan dan HPP sudah terbentuk.
                </Notice>
            )}

            <SectionTitle>Stand meter ({data.detail_terkonfirmasi}/{data.detail.length} dikonfirmasi)</SectionTitle>
            {data.detail.length === 0 ? (
                <Card><Text style={typography.muted}>Belum ada baris stand meter.</Text></Card>
            ) : data.detail.map((d) => (
                <Card key={d.detail_penjualan_id} tone={d.is_confirmed ? 'hijau' : 'oranye'}>
                    <View style={styles.baris}>
                        <Text style={typography.heading}>{d.nozzle} · {d.produk}</Text>
                        <Badge label={d.is_confirmed ? 'OK' : 'Belum'} tone={d.is_confirmed ? 'hijau' : 'oranye'} />
                    </View>
                    <Row label="Stand meter" value={`${d.stand_meter_awal} → ${d.stand_meter_akhir}`} />
                    <Row label="Liter" value={`${d.liter} L`} />
                    <Row label="Harga jual" value={rupiah(d.harga_jual)} />
                    <Row label="Total" value={rupiah(d.total)} />
                    {!keduanyaSetuju && (
                        <Button
                            label={d.is_confirmed ? 'Batalkan konfirmasi' : 'Konfirmasi baris'}
                            variant="ghost"
                            disabled={proses}
                            onPress={() => jalankan(
                                () => Setoran.konfirmasiDetail(id, d.detail_penjualan_id, !d.is_confirmed),
                                'Status baris diperbarui.'
                            )}
                        />
                    )}
                </Card>
            ))}

            {data.pj.length > 0 && (
                <>
                    <SectionTitle>Penanggung jawab shift</SectionTitle>
                    <Card tone="biru">
                        {data.pj.map((p) => <Row key={p.jabatan} label={p.jabatan} value={p.petugas.join(', ')} />)}
                    </Card>
                </>
            )}

            <SectionTitle
                aksi={
                    <Pressable onPress={() => navigation.navigate('PenarikanForm', { penjualanId: id })} hitSlop={10}>
                        <Text style={styles.tautan}>+ Setor ke bank</Text>
                    </Pressable>
                }
            >
                Setoran ke bank ({rupiah(data.total_disetor)})
            </SectionTitle>

            {data.penarikan.length === 0 ? (
                <Card><Text style={typography.muted}>Belum ada setoran ke bank.</Text></Card>
            ) : data.penarikan.map((t) => (
                <Pressable key={t.penarikan_id}
                    onPress={() => navigation.navigate('PenarikanForm', { penjualanId: id, bankId: t.penarikan_id })}>
                    <Card tone={t.is_accepted ? 'hijau' : 'oranye'}>
                        <View style={styles.baris}>
                            <Text style={typography.heading}>{t.nama_bank} · {rupiah(t.total_uang)}</Text>
                            <Badge label={t.is_accepted ? 'Dikonfirmasi' : 'Menunggu'} tone={t.is_accepted ? 'hijau' : 'oranye'} />
                        </View>
                        <Row label="Referensi" value={t.no_referensi} />
                        <Row label="Metode" value={t.metode_transfer} />
                        <Row label="Petugas" value={t.petugas} />
                        <Row label="Lampiran" value={`${t.jumlah_lampiran} berkas`} />
                    </Card>
                </Pressable>
            ))}

            <SectionTitle>Tindakan</SectionTitle>
            <View style={styles.aksi}>
                {posisi > 0 && (
                    <Button label={`← Kembali ke ${NAMA[URUT[posisi - 1]]}`} variant="ghost" disabled={proses}
                        onPress={() => jalankan(() => Setoran.mundur(id), 'Tahap dimundurkan.')} />
                )}
                {posisi < URUT.length - 1 && (
                    <Button label={`Lanjut ke ${NAMA[URUT[posisi + 1]]} →`} disabled={proses}
                        onPress={() => jalankan(() => Setoran.maju(id), 'Tahap dilanjutkan.')} />
                )}

                <Button
                    label={data.is_accepted_admin ? 'Cabut persetujuan Admin' : 'Setujui sebagai Admin'}
                    variant={data.is_accepted_admin ? 'ghost' : 'success'}
                    disabled={proses}
                    onPress={() => konfirmasiAksi(
                        'Persetujuan Admin',
                        data.is_accepted_admin
                            ? 'Mencabut persetujuan akan membatalkan jurnal penjualan bila keduanya sudah setuju.'
                            : 'Bila top manajemen juga menyetujui, jurnal penjualan & HPP akan terbentuk.',
                        () => Setoran.persetujuan(id, 'admin', !data.is_accepted_admin),
                        'Persetujuan admin diperbarui.'
                    )}
                />
                <Button
                    label={data.is_accepted_top ? 'Cabut persetujuan Top' : 'Setujui sebagai Top Manajemen'}
                    variant={data.is_accepted_top ? 'ghost' : 'success'}
                    disabled={proses}
                    onPress={() => konfirmasiAksi(
                        'Persetujuan Top Manajemen',
                        data.is_accepted_top
                            ? 'Mencabut persetujuan akan membatalkan jurnal penjualan bila keduanya sudah setuju.'
                            : 'Bila admin juga menyetujui, jurnal penjualan & HPP akan terbentuk.',
                        () => Setoran.persetujuan(id, 'top', !data.is_accepted_top),
                        'Persetujuan top manajemen diperbarui.'
                    )}
                />

                {data.tahap !== 'selesai' && (
                    <Button label="Tandai Selesai" variant="outline" disabled={proses}
                        onPress={() => jalankan(() => Setoran.selesai(id), 'Setoran ditandai selesai.')} />
                )}

                {!keduanyaSetuju && (
                    <Button label="Hapus Setoran" variant="danger" disabled={proses}
                        onPress={() => Alert.alert('Hapus setoran?', 'Seluruh baris stand meter ikut terhapus.',
                            [{ text: 'Batal', style: 'cancel' },
                             { text: 'Hapus', style: 'destructive', onPress: async () => {
                                 setProses(true);
                                 try { await Setoran.hapus(id); navigation.goBack(); }
                                 catch (e) { setPesan({ tone: 'danger', teks: e.message }); setProses(false); }
                             } }])} />
                )}
            </View>
        </>
    );
}

const styles = StyleSheet.create({
    alur: { flexDirection: 'row', justifyContent: 'space-between' },
    langkah: { alignItems: 'center', flex: 1, gap: 4 },
    titik: {
        width: 26, height: 26, borderRadius: 13, alignItems: 'center', justifyContent: 'center',
        backgroundColor: colors.border,
    },
    titikAktif: { backgroundColor: colors.primary },
    titikTeks: { fontSize: 12, fontWeight: '800', color: colors.textMuted },
    titikTeksAktif: { color: '#fff' },
    langkahTeks: { fontSize: 10, color: colors.textMuted, fontWeight: '600' },
    langkahAktif: { color: colors.primary, fontWeight: '800' },
    labelTerang: { fontSize: 13, color: 'rgba(255,255,255,0.9)', fontWeight: '600' },
    angka: { fontSize: 24, fontWeight: '800', color: '#fff' },
    pemisah: { height: 1, backgroundColor: 'rgba(255,255,255,0.25)', marginVertical: spacing.sm },
    baris: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', gap: spacing.sm },
    tautan: { color: colors.primary, fontWeight: '700', fontSize: 13 },
    aksi: { gap: spacing.sm },
});
