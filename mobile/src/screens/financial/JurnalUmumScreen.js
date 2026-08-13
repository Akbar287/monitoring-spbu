import { useCallback, useState } from 'react';
import { Pressable, StyleSheet, Text, View } from 'react-native';
import { Card, Halaman } from '../../components/ui';
import DaftarData from '../../components/DaftarData';
import DateField from '../../components/DateField';
import SelectField from '../../components/SelectField';
import { Financial } from '../../api/endpoints';
import { akuntansi, tanggalPendek } from '../../utils/format';
import { colors, spacing, typography } from '../../theme';

const awalBulan = () => { const d = new Date(); return new Date(d.getFullYear(), d.getMonth(), 1).toISOString().slice(0, 10); };
const akhirBulan = () => { const d = new Date(); return new Date(d.getFullYear(), d.getMonth() + 1, 0).toISOString().slice(0, 10); };

const SUMBER = [
    { value: '', label: 'Semua sumber' },
    { value: 'penjualan', label: 'Penjualan' },
    { value: 'penjualan_hpp', label: 'HPP penjualan' },
    { value: 'penarikan', label: 'Setoran ke bank' },
    { value: 'pembelian', label: 'Faktur pembelian' },
    { value: 'pembayaran', label: 'Pembayaran' },
    { value: 'petty_cash', label: 'Petty cash' },
    { value: 'gaji', label: 'Gaji' },
    { value: 'losses', label: 'Susut stok' },
    { value: 'penyusutan', label: 'Penyusutan' },
    { value: 'saldo_awal', label: 'Saldo awal' },
    { value: 'closing', label: 'Tutup tahun' },
];

export default function JurnalUmumScreen({ navigation }) {
    const [dari, setDari] = useState(awalBulan());
    const [sampai, setSampai] = useState(akhirBulan());
    const [sumber, setSumber] = useState('');

    const ambil = useCallback(
        (page, cari, perPage) => Financial.jurnal({
            page, cari, per_page: perPage, dari, sampai,
            tipe_sumber: sumber || undefined,
        }),
        [dari, sampai, sumber]
    );

    const aktif = (dari !== awalBulan() ? 1 : 0) + (sampai !== akhirBulan() ? 1 : 0) + (sumber ? 1 : 0);

    return (
        <Halaman judul="Jurnal Umum" subjudul={`${dari} s/d ${sampai}`} warna="biru" scroll={false}>
            <DaftarData
                key={`${dari}-${sampai}-${sumber}`}
                ambil={ambil}
                cariPlaceholder="Cari nomor jurnal, keterangan, sumber…"
                jumlahFilterAktif={aktif}
                filter={
                    <>
                        <DateField label="Dari tanggal" value={dari} onChange={setDari} />
                        <DateField label="Sampai tanggal" value={sampai} onChange={setSampai} />
                        <SelectField label="Tipe sumber" value={sumber} onChange={setSumber} options={SUMBER} />
                    </>
                }
                kosong={{ judul: 'Belum ada jurnal', pesan: 'Tidak ada jurnal pada rentang & filter ini.' }}
                render={(item) => (
                    <Pressable onPress={() => navigation.navigate('JurnalDetail', { id: item.id })}>
                        <Card>
                            <View style={styles.baris}>
                                <Text style={styles.nomor}>{item.nomor_jurnal}</Text>
                                <Text style={typography.caption}>{tanggalPendek(item.tanggal)}</Text>
                            </View>
                            <Text style={typography.muted} numberOfLines={2}>{item.keterangan}</Text>
                            <View style={styles.baris}>
                                <Text style={typography.caption}>{item.tipe_sumber}</Text>
                                <Text style={styles.total}>{akuntansi(item.total)}</Text>
                            </View>
                        </Card>
                    </Pressable>
                )}
            />
        </Halaman>
    );
}

const styles = StyleSheet.create({
    baris: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
    nomor: { fontSize: 14, fontWeight: '800', color: colors.primary },
    total: { fontSize: 14, fontWeight: '800', color: colors.text, fontVariant: ['tabular-nums'] },
});
