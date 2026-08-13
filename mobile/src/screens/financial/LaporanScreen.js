import { useCallback, useEffect, useState } from 'react';
import { StyleSheet, Text, View } from 'react-native';
import { Card, ErrorState, Halaman, Loading, Notice, Row, SectionTitle } from '../../components/ui';
import SelectField from '../../components/SelectField';
import DateField from '../../components/DateField';
import { Financial } from '../../api/endpoints';
import { useMuat } from '../../hooks/useMuat';
import { akuntansi } from '../../utils/format';
import { colors, spacing, typography } from '../../theme';

const JUDUL = { 'neraca-saldo': 'Neraca Saldo', 'laba-rugi': 'Laba Rugi', neraca: 'Neraca' };
const awalBulan = () => { const d = new Date(); return new Date(d.getFullYear(), d.getMonth(), 1).toISOString().slice(0, 10); };
const akhirBulan = () => { const d = new Date(); return new Date(d.getFullYear(), d.getMonth() + 1, 0).toISOString().slice(0, 10); };

/**
 * Laporan keuangan baca-saja. Setiap baris menampilkan KODE AKUN di depan
 * nama akunnya, sesuai aturan bahwa tiap angka harus bisa ditelusuri ke COA.
 * Angka ditampilkan gaya akuntansi (negatif dalam kurung) seperti di web.
 */
export default function LaporanScreen({ route }) {
    const { jenis } = route.params;
    const [dari, setDari] = useState(awalBulan());
    const [sampai, setSampai] = useState(akhirBulan());
    const [per, setPer] = useState(new Date().toISOString().slice(0, 10));
    const [spbuId, setSpbuId] = useState('');
    const [opsiSpbu, setOpsiSpbu] = useState([]);

    const ambil = useCallback(() => {
        const params = spbuId ? { spbu_id: spbuId } : {};
        if (jenis === 'neraca') return Financial.neraca({ ...params, per });
        const fn = jenis === 'laba-rugi' ? Financial.labaRugi : Financial.neracaSaldo;
        return fn({ ...params, dari, sampai });
    }, [jenis, dari, sampai, per, spbuId]);

    const { data, memuat, menyegarkan, error, segarkan, muatUlang } = useMuat(ambil, [jenis, dari, sampai, per, spbuId]);

    // daftar SPBU untuk penyaring — sekali saja saat layar dibuka
    useEffect(() => {
        Financial.ringkasan().then((d) => setOpsiSpbu(d.spbu ?? [])).catch(() => setOpsiSpbu([]));
    }, []);

    const bungkus = (isi) => (
        <Halaman judul={JUDUL[jenis] || 'Laporan'} subjudul={data?.periode} warna="biru"
            onRefresh={segarkan} refreshing={menyegarkan}>
            <Card tone="abu">
                <SelectField label="SPBU" value={spbuId} onChange={setSpbuId}
                    options={[{ value: '', label: 'Semua (konsolidasi)' }, ...opsiSpbu]} />
                {jenis === 'neraca'
                    ? <DateField label="Per tanggal" value={per} onChange={setPer} />
                    : (<>
                        <DateField label="Dari tanggal" value={dari} onChange={setDari} />
                        <DateField label="Sampai tanggal" value={sampai} onChange={setSampai} />
                    </>)}
            </Card>
            {isi}
        </Halaman>
    );

    if (memuat && !data) return bungkus(<Loading />);
    if (error && !data) return bungkus(<ErrorState pesan={error.message} onCoba={muatUlang} />);

    return bungkus(
        <>
            {!!data.catatan && <Notice tone={data.catatan.nada}>{data.catatan.teks}</Notice>}
            <Card><Row label="SPBU" value={data.spbu} /><Row label="Periode" value={data.periode} /></Card>

            {jenis === 'neraca-saldo' && <NeracaSaldo data={data} />}
            {jenis === 'laba-rugi' && <LabaRugi data={data} />}
            {jenis === 'neraca' && <Neraca data={data} />}
        </>
    );
}

function BarisAkun({ b }) {
    return (
        <View style={styles.baris}>
            <View style={styles.kiri}>
                <Text style={styles.kode}>{b.kode_akun}</Text>
                <Text style={typography.body} numberOfLines={2}>{b.nama_akun}</Text>
            </View>
            <Text style={styles.nilai}>{akuntansi(b.nilai)}</Text>
        </View>
    );
}

function NeracaSaldo({ data }) {
    return (
        <>
            <SectionTitle>Saldo akhir per akun</SectionTitle>
            {data.baris.length === 0 ? <Card><Text style={typography.muted}>Tidak ada aktivitas.</Text></Card> : (
                <Card>
                    {data.baris.map((b) => (
                        <View key={b.kode_akun} style={styles.blok}>
                            <View style={styles.baris}>
                                <View style={styles.kiri}>
                                    <Text style={styles.kode}>{b.kode_akun}</Text>
                                    <Text style={typography.body} numberOfLines={2}>{b.nama_akun}</Text>
                                </View>
                            </View>
                            <Row label="Mutasi debit" value={akuntansi(b.mutasi_debit)} />
                            <Row label="Mutasi kredit" value={akuntansi(b.mutasi_kredit)} />
                            <Row label="Akhir debit" value={akuntansi(b.akhir_debit)} />
                            <Row label="Akhir kredit" value={akuntansi(b.akhir_kredit)} />
                        </View>
                    ))}
                </Card>
            )}
            <Card tone={data.seimbang ? 'hijau' : 'merah'}>
                <Text style={typography.heading}>Total</Text>
                <Row label="Mutasi D / K" value={`${akuntansi(data.total.mut_d)} / ${akuntansi(data.total.mut_k)}`} />
                <Row label="Akhir D / K" value={`${akuntansi(data.total.akhir_d)} / ${akuntansi(data.total.akhir_k)}`} />
            </Card>
        </>
    );
}

function LabaRugi({ data }) {
    return (
        <>
            {data.bagian.map((b) => (
                <View key={b.judul} style={styles.grup}>
                    <SectionTitle>{b.judul}</SectionTitle>
                    <Card>
                        {b.baris.length === 0
                            ? <Text style={typography.muted}>Tidak ada.</Text>
                            : b.baris.map((x) => <BarisAkun key={x.kode_akun} b={x} />)}
                        <View style={styles.pemisah} />
                        <Row label={`Total ${b.judul}`} value={akuntansi(b.subtotal)} />
                    </Card>
                </View>
            ))}
            <Card gradien="biru">
                {data.ikhtisar.map((i) => (
                    <Row terang key={i.label} label={i.label} value={akuntansi(i.nilai)} />
                ))}
            </Card>
        </>
    );
}

function Neraca({ data }) {
    const blok = (judul, isi) => (
        <View style={styles.grup}>
            <SectionTitle>{judul}</SectionTitle>
            <Card>
                {isi.baris.length === 0
                    ? <Text style={typography.muted}>Tidak ada.</Text>
                    : isi.baris.map((x, i) => <BarisAkun key={`${x.kode_akun}-${i}`} b={x} />)}
                <View style={styles.pemisah} />
                <Row label={`Total ${judul}`} value={akuntansi(isi.total)} />
            </Card>
        </View>
    );

    return (
        <>
            {blok('Aset', data.aset)}
            {blok('Kewajiban', data.kewajiban)}
            {blok('Modal', data.modal)}
            <Card gradien={data.seimbang ? 'hijau' : 'merah'}>
                <Row terang label="Total Aset" value={akuntansi(data.aset.total)} />
                <Row terang label="Kewajiban + Modal" value={akuntansi(data.total_pasiva)} />
                <Row terang label="Selisih" value={akuntansi(data.selisih)} />
            </Card>
        </>
    );
}

const styles = StyleSheet.create({
    grup: { gap: spacing.md },
    blok: { gap: 2, paddingVertical: spacing.xs, borderBottomWidth: 1, borderBottomColor: colors.border },
    baris: { flexDirection: 'row', alignItems: 'flex-start', justifyContent: 'space-between', gap: spacing.md },
    kiri: { flex: 1 },
    kode: { fontSize: 11, fontWeight: '800', color: colors.primary, letterSpacing: 0.5 },
    nilai: { fontSize: 14, fontWeight: '700', color: colors.text, fontVariant: ['tabular-nums'] },
    pemisah: { height: 1, backgroundColor: colors.border, marginVertical: spacing.xs },
});
