import { useState } from 'react';
import { KeyboardAvoidingView, Platform, ScrollView, StyleSheet } from 'react-native';
import { Halaman, Button, Card, Field, Notice } from '../../components/ui';
import DateField from '../../components/DateField';
import { Procurement } from '../../api/endpoints';
import { rupiah6 } from '../../utils/format';
import { colors, spacing } from '../../theme';

export default function PembayaranFormScreen({ route, navigation }) {
    const { id, bayar, sisa } = route.params;
    const ubah = !!bayar;

    const [tanggal, setTanggal] = useState(bayar?.tanggal ?? hariIni());
    const [noCek, setNoCek] = useState(bayar?.no_cek_bg ?? '');
    const [noRek, setNoRek] = useState(bayar?.no_rekening ?? '');
    const [namaRek, setNamaRek] = useState(bayar?.nama_rekening ?? '');
    const [total, setTotal] = useState(bayar ? bersih(bayar.total_setoran) : '');
    const [proses, setProses] = useState(false);
    const [error, setError] = useState(null);

    const kirim = async () => {
        if (proses) return;
        setError(null);
        setProses(true);
        try {
            const payload = {
                tanggal,
                no_cek_bg: noCek.trim(),
                no_rekening: noRek.trim(),
                nama_rekening: namaRek.trim(),
                // string, bukan angka — jaga 6 desimal
                total_setoran: String(total).trim(),
            };
            ubah
                ? await Procurement.pembayaran.ubah(id, bayar.id, payload)
                : await Procurement.pembayaran.buat(id, payload);
            navigation.goBack();
        } catch (e) {
            setError(e);
        } finally {
            setProses(false);
        }
    };

    return (
        <Halaman judul={ubah ? 'Ubah Pembayaran' : 'Catat Pembayaran'} warna="oranye" keyboard>
                    {!!error && <Notice tone="danger">{error.message}</Notice>}
                    {!ubah && !!sisa && (
                        <Notice tone="primary">Sisa tagihan saat ini: {rupiah6(sisa)}</Notice>
                    )}

                    <Card>
                        <DateField label="Tanggal *" value={tanggal} onChange={setTanggal} disabled={proses}
                            error={error?.fieldError?.('tanggal')} />
                        <Field label="No. Cek / BG *" value={noCek} onChangeText={setNoCek} autoCapitalize="characters"
                            editable={!proses} error={error?.fieldError?.('no_cek_bg')} />
                        <Field label="No. Rekening *" value={noRek} onChangeText={setNoRek} keyboardType="number-pad"
                            editable={!proses} error={error?.fieldError?.('no_rekening')} />
                        <Field label="Nama Rekening *" value={namaRek} onChangeText={setNamaRek}
                            editable={!proses} error={error?.fieldError?.('nama_rekening')} />
                        <Field
                            label="Total Setoran *"
                            value={total}
                            onChangeText={setTotal}
                            keyboardType="decimal-pad"
                            placeholder="mis. 100000000.123456"
                            hint="Boleh sampai 6 angka di belakang koma. Pakai titik sebagai pemisah desimal."
                            editable={!proses}
                            error={error?.fieldError?.('total_setoran')}
                        />
                    </Card>

                    <Button label={ubah ? 'Simpan Perubahan' : 'Simpan Pembayaran'} onPress={kirim} loading={proses}
                        disabled={!noCek.trim() || !String(total).trim()} />
                </Halaman>
    );
}

function hariIni() {
    const d = new Date();
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
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
