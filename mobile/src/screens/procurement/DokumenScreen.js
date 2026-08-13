import { useCallback, useState } from 'react';
import { useFocusEffect } from '@react-navigation/native';
import { Alert, Pressable, ScrollView, StyleSheet, Text, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { Halaman, Badge, Button, Card, ErrorState, Loading, Notice, Row } from '../../components/ui';
import { Procurement } from '../../api/endpoints';
import { useMuat } from '../../hooks/useMuat';
import { desimal6, rupiah6, tanggalPendek } from '../../utils/format';
import { colors, radius, spacing, typography } from '../../theme';

/**
 * Detail dokumen pengadaan untuk tahap Perencanaan dan Pembelian.
 * Keduanya memakai bentuk data yang sama (rencana + baris produk + pajak);
 * yang berbeda hanya aksi yang tersedia.
 */
export default function DokumenScreen({ route, navigation }) {
    const { id, tahap = 'perencanaan' } = route.params;
    const perencanaan = tahap === 'perencanaan';

    const [proses, setProses] = useState(false);
    const [pesan, setPesan] = useState(null);

    const { data, memuat, error, muatUlang } = useMuat(
        useCallback(
            () => (perencanaan ? Procurement.perencanaan.detail(id) : Procurement.pembelian.detail(id)),
            [id, perencanaan]
        )
    );

    useFocusEffect(useCallback(() => { muatUlang({ segarkan: true }); }, [muatUlang]));

    if (memuat && !data) return <Halaman judul={perencanaan ? 'Detail Perencanaan' : 'Detail Pembelian'} subjudul={data?.kode_pembelian} warna="oranye"><Loading /></Halaman>;
    if (error && !data) {
        return <Halaman judul={perencanaan ? 'Detail Perencanaan' : 'Detail Pembelian'} subjudul={data?.kode_pembelian} warna="oranye"><ErrorState pesan={error.message} onCoba={muatUlang} /></Halaman>;
    }

    const jalankan = async (fn, sukses) => {
        setProses(true);
        setPesan(null);
        try {
            const hasil = await fn();
            setPesan({ tone: 'success', teks: sukses || hasil?.message || 'Berhasil.' });
            await muatUlang({ segarkan: true });
        } catch (e) {
            setPesan({ tone: 'danger', teks: e.message });
        } finally {
            setProses(false);
        }
    };

    const konfirmasiRencana = (setuju) => {
        Alert.alert(
            setuju ? 'Setujui rencana?' : 'Tolak rencana?',
            setuju
                ? 'Dokumen akan naik ke tahap Pembelian dan pajak dihitung dari grand total saat ini.'
                : 'Dokumen ditandai ditolak dan tidak dilanjutkan.',
            [
                { text: 'Batal', style: 'cancel' },
                {
                    text: setuju ? 'Setujui' : 'Tolak',
                    style: setuju ? 'default' : 'destructive',
                    onPress: () => jalankan(() => Procurement.perencanaan.konfirmasi(id, setuju), null),
                },
            ]
        );
    };

    const hapusBaris = (baris) => {
        Alert.alert('Hapus baris?', `${baris.produk} akan dihapus dari dokumen ini.`, [
            { text: 'Batal', style: 'cancel' },
            {
                text: 'Hapus',
                style: 'destructive',
                onPress: () => jalankan(() => Procurement.perencanaan.hapusProduk(id, baris.id), 'Baris dihapus.'),
            },
        ]);
    };

    const pajak = data.pajak || {};
    const kal = data.kalkulasi || {};
    const bolehUbah = perencanaan && !(data.is_confirmation && !data.is_denied);

    return (
        <Halaman judul={perencanaan ? 'Detail Perencanaan' : 'Detail Pembelian'} subjudul={data?.kode_pembelian} warna="oranye">
                {!!pesan && <Notice tone={pesan.tone}>{pesan.teks}</Notice>}
                {data.is_denied && !!data.keterangan_denied && (
                    <Notice tone="danger">Ditolak: {data.keterangan_denied}</Notice>
                )}

                <Card>
                    <View style={styles.barisAtas}>
                        <Text style={typography.heading}>{data.kode_pembelian}</Text>
                        <Badge
                            label={data.is_denied ? 'Ditolak' : (data.status || '-')}
                            tone={data.is_denied ? 'danger' : 'primary'}
                        />
                    </View>
                    <Row label="SPBU" value={data.spbu} />
                    <Row label="Pemohon" value={data.pemohon} />
                    <Row label="Dibuat" value={tanggalPendek(data.created_at)} />
                    {!!data.tanggal_pembelian && <Row label="Tanggal pembelian" value={tanggalPendek(data.tanggal_pembelian)} />}
                    {!!data.keterangan && <Row label="Keterangan" value={data.keterangan} />}
                </Card>

                <Card>
                    <View style={styles.barisAtas}>
                        <Text style={typography.heading}>Baris produk</Text>
                        <Text style={typography.caption}>{kal.jumlah_baris ?? 0} baris</Text>
                    </View>

                    {(data.detail || []).length === 0 ? (
                        <Text style={typography.muted}>Belum ada produk pada dokumen ini.</Text>
                    ) : (
                        data.detail.map((b) => (
                            <Pressable
                                key={b.id}
                                onPress={() => navigation.navigate('BarisForm', {
                                    id, tahap, detail: b, mode: perencanaan ? 'baris' : 'harga',
                                })}
                                style={styles.barisProduk}
                            >
                                <View style={styles.barisAtas}>
                                    <Text style={styles.produkNama}>{b.produk}</Text>
                                    <Ionicons name="create-outline" size={16} color={colors.primary} />
                                </View>
                                <Row label="Kuantitas" value={`${desimal6(b.jumlah)} ${b.satuan_jumlah}`} />
                                <Row label="Rit" value={desimal6(b.rit)} />
                                <Row label="Harga satuan" value={rupiah6(b.harga)} />
                                <Row label="Total" value={rupiah6(b.total_harga)} valueStyle={styles.tebal} />
                                {bolehUbah && (
                                    <Pressable onPress={() => hapusBaris(b)} hitSlop={8} style={styles.hapusBaris}>
                                        <Text style={styles.hapusTeks}>Hapus baris</Text>
                                    </Pressable>
                                )}
                            </Pressable>
                        ))
                    )}

                    {bolehUbah && (
                        <Button
                            label="Tambah Produk"
                            variant="outline"
                            onPress={() => navigation.navigate('BarisForm', { id, tahap, mode: 'baris' })}
                        />
                    )}
                </Card>

                <Card>
                    <Text style={typography.heading}>Kalkulasi</Text>
                    <Row label="Total kuantitas" value={desimal6(kal.total_kuantitas)} />
                    <Row label="Total rit" value={desimal6(kal.total_rit)} />
                    <Row label="Grand total" value={rupiah6(data.grand_total)} valueStyle={styles.tebal} />
                </Card>

                <Card>
                    <View style={styles.barisAtas}>
                        <Text style={typography.heading}>Pajak pembelian</Text>
                        {!pajak.tersimpan && <Badge label="Proyeksi" tone="warning" />}
                    </View>
                    {!pajak.tarif && (
                        <Notice tone="warning">
                            Belum ada tarif pajak aktif — angka di bawah bernilai nol.
                        </Notice>
                    )}
                    <Row label="Net price" value={rupiah6(pajak.net_price)} />
                    <Row label={`PPN${pajak.tarif ? ` (${desimal6(pajak.tarif.ppn)}%)` : ''}`} value={rupiah6(pajak.ppn)} />
                    <Row label={`PPBKB${pajak.tarif ? ` (${desimal6(pajak.tarif.ppbkb)}%)` : ''}`} value={rupiah6(pajak.ppbkb)} />
                    <Row label={`PPh 22${pajak.tarif ? ` (${desimal6(pajak.tarif.pph)}%)` : ''}`} value={rupiah6(pajak.pph)} />
                    <Row label="Total amount" value={rupiah6(pajak.total_amount)} valueStyle={styles.tebal} />
                    <Row label="Metode posting" value={pajak.metode_posting_pajak === 'terpisah' ? 'Terpisah' : 'Masuk HPP'} />

                    {!perencanaan && (
                        <>
                            <Button
                                label="Ubah Pajak Manual"
                                variant="outline"
                                onPress={() => navigation.navigate('PajakForm', { id, pajak })}
                            />
                            <Button
                                label="Hitung Ulang dari Tarif"
                                variant="ghost"
                                loading={proses}
                                onPress={() => jalankan(() => Procurement.pembelian.hitungUlangPajak(id))}
                            />
                        </>
                    )}
                </Card>

                <View style={styles.aksi}>
                    {perencanaan && bolehUbah && (
                        <>
                            <Button label="Setujui & Lanjut ke Pembelian" variant="success" loading={proses}
                                onPress={() => konfirmasiRencana(true)} />
                            <Button label="Tolak Rencana" variant="danger" loading={proses}
                                onPress={() => konfirmasiRencana(false)} />
                        </>
                    )}

                    {!perencanaan && (
                        <>
                            <Button label="Lanjut ke Pembayaran" variant="success" loading={proses}
                                onPress={() => jalankan(() => Procurement.pembelian.lanjut(id))} />
                            <Button label="Kembalikan ke Perencanaan" variant="ghost" loading={proses}
                                onPress={() => jalankan(() => Procurement.pembelian.kembalikan(id))} />
                        </>
                    )}

                    {perencanaan && (
                        <Button
                            label="Hapus Dokumen"
                            variant="danger"
                            loading={proses}
                            onPress={() => Alert.alert('Hapus dokumen?', 'Seluruh baris produk ikut terhapus.', [
                                { text: 'Batal', style: 'cancel' },
                                {
                                    text: 'Hapus',
                                    style: 'destructive',
                                    onPress: async () => {
                                        setProses(true);
                                        try {
                                            await Procurement.perencanaan.hapus(id);
                                            navigation.goBack();
                                        } catch (e) {
                                            setPesan({ tone: 'danger', teks: e.message });
                                        } finally { setProses(false); }
                                    },
                                },
                            ])}
                        />
                    )}
                </View>
            </Halaman>
    );
}

const styles = StyleSheet.create({
    screen: { flex: 1, backgroundColor: colors.background },
    isi: { padding: spacing.lg, gap: spacing.md, paddingBottom: spacing.xxl },
    barisAtas: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', gap: spacing.sm },
    barisProduk: {
        borderWidth: 1, borderColor: colors.border, borderRadius: radius.md,
        padding: spacing.md, gap: 2, backgroundColor: colors.background,
    },
    produkNama: { fontSize: 15, fontWeight: '700', color: colors.text },
    tebal: { fontWeight: '800', color: colors.primaryDark },
    hapusBaris: { alignSelf: 'flex-end', paddingTop: spacing.xs },
    hapusTeks: { color: colors.danger, fontSize: 12, fontWeight: '600' },
    aksi: { gap: spacing.sm },
});
