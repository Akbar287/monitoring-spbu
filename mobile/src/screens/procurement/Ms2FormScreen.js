import { useCallback, useState } from 'react';
import { Pressable, ScrollView, StyleSheet, Text, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { Halaman, Button, Card, EmptyState, ErrorState, Loading, Notice, Row } from '../../components/ui';
import DateField from '../../components/DateField';
import SelectField from '../../components/SelectField';
import { Procurement } from '../../api/endpoints';
import { useMuat } from '../../hooks/useMuat';
import { useOpsi } from '../../master/OpsiContext';
import { desimal6, rupiah6 } from '../../utils/format';
import { colors, radius, spacing, typography } from '../../theme';

/** Buat dokumen MS2 dari baris pembelian yang sudah dibayar. */
export default function Ms2FormScreen({ navigation }) {
    const { ambil } = useOpsi();
    const [tanggal, setTanggal] = useState(hariIni());
    const [pilihan, setPilihan] = useState({}); // detail_id -> jam_kerja_id
    const [proses, setProses] = useState(false);
    const [error, setError] = useState(null);

    const { data, memuat, error: gagalMuat, muatUlang } = useMuat(
        useCallback(() => Procurement.ms2.kandidat(), [])
    );

    if (memuat && !data) return <Halaman judul={'Buat MS2'} warna="oranye" keyboard><Loading /></Halaman>;
    if (gagalMuat && !data) {
        return <Halaman judul={'Buat MS2'} warna="oranye" keyboard><ErrorState pesan={gagalMuat.message} onCoba={muatUlang} /></Halaman>;
    }

    const kandidat = data?.items || [];
    const terpilih = Object.keys(pilihan);

    const alih = (item) => {
        setPilihan((s) => {
            const baru = { ...s };
            if (baru[item.id]) delete baru[item.id];
            else baru[item.id] = ambil('jam_kerja')[0]?.value ?? '';
            return baru;
        });
    };

    const kirim = async () => {
        if (proses) return;
        setError(null);
        setProses(true);
        try {
            const hasil = await Procurement.ms2.buat({
                tanggal,
                items: terpilih.map((detailId) => ({
                    detail_rencana_pembelian_id: detailId,
                    jam_kerja_id: pilihan[detailId],
                })),
            });
            navigation.replace('Ms2', { id: hasil.id });
        } catch (e) {
            setError(e);
        } finally {
            setProses(false);
        }
    };

    const semuaAdaShift = terpilih.every((k) => !!pilihan[k]);

    return (
        <Halaman judul={'Buat MS2'} warna="oranye" keyboard>
                {!!error && <Notice tone="danger">{error.message}</Notice>}

                <Card>
                    <DateField label="Tanggal MS2 *" value={tanggal} onChange={setTanggal} disabled={proses} />
                </Card>

                <Card>
                    <Text style={typography.heading}>Pilih produk</Text>
                    <Text style={typography.caption}>
                        Hanya baris dari dokumen yang sudah berada di tahap MS2 dan belum dialokasikan.
                    </Text>

                    {kandidat.length === 0 ? (
                        <EmptyState
                            judul="Belum ada kandidat"
                            pesan="Selesaikan tahap Pembayaran terlebih dahulu agar produknya muncul di sini."
                        />
                    ) : (
                        kandidat.map((item) => {
                            const aktif = !!pilihan[item.id];
                            return (
                                <View key={item.id} style={[styles.kotak, aktif && styles.kotakAktif]}>
                                    <Pressable onPress={() => alih(item)} style={styles.barisAtas}>
                                        <View style={styles.isiBaris}>
                                            <Text style={styles.produk}>{item.produk}</Text>
                                            <Text style={typography.caption}>{item.kode_pembelian}</Text>
                                        </View>
                                        <Ionicons
                                            name={aktif ? 'checkbox' : 'square-outline'}
                                            size={22}
                                            color={aktif ? colors.primary : colors.textMuted}
                                        />
                                    </Pressable>
                                    <Row label="Kuantitas" value={`${desimal6(item.jumlah)} ${item.satuan_jumlah}`} />
                                    <Row label="Rit" value={desimal6(item.rit)} />
                                    <Row label="Nilai" value={rupiah6(item.total_harga)} />

                                    {aktif && (
                                        <SelectField
                                            label="Shift pengambilan *"
                                            value={pilihan[item.id]}
                                            options={ambil('jam_kerja')}
                                            onChange={(v) => setPilihan((s) => ({ ...s, [item.id]: v }))}
                                            disabled={proses}
                                        />
                                    )}
                                </View>
                            );
                        })
                    )}
                </Card>

                <Button
                    label={`Buat MS2 (${terpilih.length} produk)`}
                    onPress={kirim}
                    loading={proses}
                    disabled={terpilih.length === 0 || !semuaAdaShift}
                />
            </Halaman>
    );
}

function hariIni() {
    const d = new Date();
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
}

const styles = StyleSheet.create({
    screen: { flex: 1, backgroundColor: colors.background },
    isi: { padding: spacing.lg, gap: spacing.md, paddingBottom: spacing.xxl },
    barisAtas: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', gap: spacing.sm },
    isiBaris: { flex: 1 },
    kotak: { borderWidth: 1, borderColor: colors.border, borderRadius: radius.md, padding: spacing.md, gap: 2, backgroundColor: colors.background },
    kotakAktif: { borderColor: colors.primary, backgroundColor: colors.primarySoft },
    produk: { fontSize: 15, fontWeight: '700', color: colors.text },
});
