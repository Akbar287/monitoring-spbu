import { useMemo, useState } from 'react';
import { KeyboardAvoidingView, Platform, ScrollView, StyleSheet, Text } from 'react-native';
import { Halaman, Button, Card, Field, Notice, Row } from '../../components/ui';
import SelectField from '../../components/SelectField';
import { Procurement } from '../../api/endpoints';
import { desimal6, rupiah6 } from '../../utils/format';
import { colors, spacing, typography } from '../../theme';

const METODE = [
    { value: 'hpp', label: 'Masuk HPP — PPN & PPBKB dikapitalisasi ke Persediaan' },
    { value: 'terpisah', label: 'Terpisah — PPN ke PPN Masukan, PPBKB ke Beban' },
];

/**
 * Penyesuaian pajak manual saat faktur Pertamina berbeda dari hasil hitungan
 * tarif. Semua nilai dikirim sebagai string 6 desimal.
 */
export default function PajakFormScreen({ route, navigation }) {
    const { id, pajak } = route.params;

    const [net, setNet] = useState(bersih(pajak?.net_price));
    const [ppn, setPpn] = useState(bersih(pajak?.ppn));
    const [ppbkb, setPpbkb] = useState(bersih(pajak?.ppbkb));
    const [pph, setPph] = useState(bersih(pajak?.pph));
    const [metode, setMetode] = useState(pajak?.metode_posting_pajak ?? 'hpp');
    const [proses, setProses] = useState(false);
    const [error, setError] = useState(null);

    const total = useMemo(() => {
        const jumlah = [net, ppn, ppbkb, pph]
            .map((v) => Number(String(v).replace(',', '.')) || 0)
            .reduce((a, b) => a + b, 0);

        return jumlah.toFixed(6);
    }, [net, ppn, ppbkb, pph]);

    const kirim = async () => {
        if (proses) return;
        setError(null);
        setProses(true);
        try {
            await Procurement.pembelian.ubahPajak(id, {
                net_price: String(net).trim(),
                ppn: String(ppn).trim(),
                ppbkb: String(ppbkb).trim(),
                pph: String(pph).trim(),
                metode_posting_pajak: metode,
                // total sengaja tidak dikirim: server menjumlahkan sendiri
                // pada presisi penuh supaya tidak terpengaruh float klien
            });
            navigation.goBack();
        } catch (e) {
            setError(e);
        } finally {
            setProses(false);
        }
    };

    return (
        <Halaman judul={'Ubah Pajak Pembelian'} warna="oranye" keyboard>
                    {!!error && <Notice tone="danger">{error.message}</Notice>}

                    <Notice tone="warning">
                        Isi sesuai faktur. Nilai disimpan apa adanya sampai 6 angka di belakang koma,
                        tanpa dipaksa cocok dengan persentase tarif.
                    </Notice>

                    <Card>
                        <Field label="Net Price *" value={net} onChangeText={setNet} keyboardType="decimal-pad" editable={!proses} />
                        <Field label="PPN *" value={ppn} onChangeText={setPpn} keyboardType="decimal-pad" editable={!proses} />
                        <Field label="PPBKB *" value={ppbkb} onChangeText={setPpbkb} keyboardType="decimal-pad" editable={!proses} />
                        <Field label="PPh 22 *" value={pph} onChangeText={setPph} keyboardType="decimal-pad" editable={!proses} />
                    </Card>

                    <Card>
                        <Text style={typography.heading}>Total (pratinjau)</Text>
                        <Row label="Net + PPN + PPBKB + PPh" value={rupiah6(total)} />
                        {!!pajak?.tarif && (
                            <Text style={typography.caption}>
                                Tarif aktif: PPN {desimal6(pajak.tarif.ppn)}% · PPBKB {desimal6(pajak.tarif.ppbkb)}% · PPh {desimal6(pajak.tarif.pph)}%
                            </Text>
                        )}
                    </Card>

                    <Card>
                        <SelectField
                            label="Metode Posting Pajak *"
                            value={metode}
                            options={METODE}
                            onChange={setMetode}
                            disabled={proses}
                            hint="Menentukan bagaimana PPN & PPBKB masuk ke jurnal keuangan."
                        />
                    </Card>

                    <Button label="Simpan Pajak" onPress={kirim} loading={proses} />
                </Halaman>
    );
}

function bersih(nilai) {
    const t = String(nilai ?? '');
    return t.includes('.') ? t.replace(/0+$/, '').replace(/\.$/, '') : t;
}

const styles = StyleSheet.create({
    screen: { flex: 1, backgroundColor: colors.background },
    flex: { flex: 1 },
    isi: { padding: spacing.lg, gap: spacing.md, paddingBottom: spacing.xxl },
});
