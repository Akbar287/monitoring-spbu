import { useState } from 'react';
import { KeyboardAvoidingView, Platform, ScrollView, StyleSheet, Text } from 'react-native';
import { Halaman, Button, Card, Field, Notice, Row } from '../../components/ui';
import SelectField from '../../components/SelectField';
import { Procurement } from '../../api/endpoints';
import { useOpsi } from '../../master/OpsiContext';
import { desimal6, rupiah6 } from '../../utils/format';
import { colors, spacing, typography } from '../../theme';

const SATUAN = [
    { value: 'Liter', label: 'Liter' },
    { value: 'KL', label: 'Kilo Liter' },
];

/**
 * Dua mode:
 *  - `baris` (Perencanaan): pilih produk, kuantitas, satuan. Harga diambil
 *    server dari harga beli default produk.
 *  - `harga` (Pembelian): kunci harga tebus & rit hasil negosiasi.
 *
 * Angka dikirim sebagai STRING agar 6 desimal tidak dibulatkan JavaScript.
 */
export default function BarisFormScreen({ route, navigation }) {
    const { id, mode = 'baris', detail } = route.params;
    const { ambil } = useOpsi();
    const ubah = !!detail;

    const [produkId, setProdukId] = useState(detail?.produk_id ?? '');
    const [jumlah, setJumlah] = useState(detail ? bersih(detail.jumlah) : '');
    const [satuan, setSatuan] = useState(detail?.satuan_jumlah ?? 'Liter');
    const [harga, setHarga] = useState(detail ? bersih(detail.harga) : '');
    const [rit, setRit] = useState(detail ? bersih(detail.rit) : '');
    const [proses, setProses] = useState(false);
    const [error, setError] = useState(null);

    const kirim = async () => {
        if (proses) return;
        setError(null);
        setProses(true);
        try {
            if (mode === 'harga') {
                await Procurement.pembelian.ubahHarga(id, detail.id, {
                    harga: String(harga).trim(),
                    rit: String(rit).trim() || undefined,
                    jumlah: String(jumlah).trim() || undefined,
                });
            } else {
                const payload = { produk_id: produkId, jumlah: String(jumlah).trim(), satuan_jumlah: satuan };
                ubah
                    ? await Procurement.perencanaan.ubahProduk(id, detail.id, payload)
                    : await Procurement.perencanaan.tambahProduk(id, payload);
            }
            navigation.goBack();
        } catch (e) {
            setError(e);
        } finally {
            setProses(false);
        }
    };

    return (
        <Halaman judul={mode === 'harga' ? 'Ubah Harga Produk' : (ubah ? 'Ubah Baris Produk' : 'Tambah Produk')} warna="oranye" keyboard>
                    {!!error && <Notice tone="danger">{error.fieldError?.('harga') || error.fieldError?.('jumlah') || error.message}</Notice>}

                    {mode === 'harga' ? (
                        <>
                            <Card>
                                <Text style={typography.heading}>{detail.produk}</Text>
                                <Row label="Kuantitas saat ini" value={`${desimal6(detail.jumlah)} ${detail.satuan_jumlah}`} />
                                <Row label="Harga saat ini" value={rupiah6(detail.harga)} />
                                <Row label="Total saat ini" value={rupiah6(detail.total_harga)} />
                            </Card>

                            <Card>
                                <Field
                                    label="Harga Tebus per Satuan *"
                                    value={harga}
                                    onChangeText={setHarga}
                                    keyboardType="decimal-pad"
                                    placeholder="mis. 8300.123457"
                                    hint="Boleh sampai 6 angka di belakang koma. Pakai titik sebagai pemisah desimal."
                                    editable={!proses}
                                />
                                <Field
                                    label="Kuantitas"
                                    value={jumlah}
                                    onChangeText={setJumlah}
                                    keyboardType="decimal-pad"
                                    hint="Kosongkan bila tidak diubah."
                                    editable={!proses}
                                />
                                <Field
                                    label="Rit"
                                    value={rit}
                                    onChangeText={setRit}
                                    keyboardType="decimal-pad"
                                    hint="Kosongkan agar dihitung otomatis (kuantitas ÷ 8.000, dibulatkan ke atas)."
                                    editable={!proses}
                                />
                            </Card>

                            <Card>
                                <Text style={typography.heading}>Pratinjau</Text>
                                <Row label="Total baris" value={rupiah6(kaliLokal(jumlah || detail.jumlah, harga))} />
                                <Text style={typography.caption}>
                                    Angka final dihitung ulang server dengan presisi penuh 6 desimal.
                                </Text>
                            </Card>
                        </>
                    ) : (
                        <Card>
                            <SelectField
                                label="Produk *"
                                value={produkId}
                                options={ambil('produk')}
                                onChange={setProdukId}
                                error={error?.fieldError?.('produk_id')}
                                disabled={proses}
                                hint="Harga diambil otomatis dari harga beli default produk."
                            />
                            <Field
                                label="Kuantitas *"
                                value={jumlah}
                                onChangeText={setJumlah}
                                keyboardType="decimal-pad"
                                placeholder="mis. 8000"
                                hint="Boleh sampai 6 angka di belakang koma."
                                editable={!proses}
                            />
                            <SelectField
                                label="Satuan *"
                                value={satuan}
                                options={SATUAN}
                                onChange={setSatuan}
                                disabled={proses}
                            />
                        </Card>
                    )}

                    <Button
                        label={ubah || mode === 'harga' ? 'Simpan Perubahan' : 'Tambahkan'}
                        onPress={kirim}
                        loading={proses}
                        disabled={mode === 'harga' ? !String(harga).trim() : (!produkId || !String(jumlah).trim())}
                    />
                </Halaman>
    );
}

/** Hilangkan nol berlebih di belakang agar enak diedit ("8000.000000" → "8000"). */
function bersih(nilai) {
    const t = String(nilai ?? '');
    return t.includes('.') ? t.replace(/0+$/, '').replace(/\.$/, '') : t;
}

/**
 * Pratinjau perkalian di klien. Sengaja memakai desimal string sederhana
 * (bukan float) supaya angka yang ditampilkan tidak menyesatkan; nilai
 * otoritatif tetap dari server.
 */
function kaliLokal(a, b) {
    const na = Number(String(a).replace(',', '.'));
    const nb = Number(String(b).replace(',', '.'));
    if (!isFinite(na) || !isFinite(nb)) return '0';

    return (na * nb).toFixed(6);
}

const styles = StyleSheet.create({
    screen: { flex: 1, backgroundColor: colors.background },
    flex: { flex: 1 },
    isi: { padding: spacing.lg, gap: spacing.md, paddingBottom: spacing.xxl },
});
