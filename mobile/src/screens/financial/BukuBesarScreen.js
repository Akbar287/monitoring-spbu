import { useCallback, useEffect, useState } from 'react';
import { StyleSheet, Text, View } from 'react-native';
import { Card, EmptyState, ErrorState, Halaman, Loading, Row, SectionTitle } from '../../components/ui';
import SelectField from '../../components/SelectField';
import DateField from '../../components/DateField';
import { Financial } from '../../api/endpoints';
import { akuntansi, tanggalPendek } from '../../utils/format';
import { colors, spacing, typography } from '../../theme';

const awalBulan = () => { const d = new Date(); return new Date(d.getFullYear(), d.getMonth(), 1).toISOString().slice(0, 10); };
const akhirBulan = () => { const d = new Date(); return new Date(d.getFullYear(), d.getMonth() + 1, 0).toISOString().slice(0, 10); };

/** Buku besar satu akun: saldo awal, mutasi per jurnal, saldo berjalan. */
export default function BukuBesarScreen() {
    const [akun, setAkun] = useState([]);
    const [coaId, setCoaId] = useState('');
    const [dari, setDari] = useState(awalBulan());
    const [sampai, setSampai] = useState(akhirBulan());
    const [data, setData] = useState(null);
    const [memuat, setMemuat] = useState(false);
    const [error, setError] = useState(null);

    useEffect(() => {
        Financial.ringkasan().then((d) => setAkun(d.akun ?? [])).catch(() => setAkun([]));
    }, []);

    useEffect(() => {
        if (!coaId) { setData(null); return; }
        setMemuat(true); setError(null);
        Financial.bukuBesar({ coa_id: coaId, dari, sampai })
            .then(setData).catch(setError).finally(() => setMemuat(false));
    }, [coaId, dari, sampai]);

    return (
        <Halaman judul="Buku Besar" subjudul={data?.periode} warna="biru">
            <Card tone="abu">
                <SelectField label="Kode akun *" value={coaId} onChange={setCoaId} options={akun}
                    hint="Ketik kode atau nama akun di kotak cari." />
                <DateField label="Dari tanggal" value={dari} onChange={setDari} />
                <DateField label="Sampai tanggal" value={sampai} onChange={setSampai} />
            </Card>

            {!coaId && <Card><EmptyState judul="Pilih akun" pesan="Buku besar ditampilkan per kode akun." /></Card>}
            {memuat && <Loading />}
            {!!error && <ErrorState pesan={error.message} />}

            {!!data && !memuat && (
                <>
                    <Card gradien="biru">
                        <Text style={styles.kodeTerang}>{data.akun.kode_akun}</Text>
                        <Text style={styles.namaTerang}>{data.akun.nama_akun}</Text>
                        <Row terang label="Tipe akun" value={data.akun.tipe_akun} />
                        <Row terang label="Saldo normal" value={data.akun.saldo_normal} />
                        <Row terang label="Saldo awal" value={akuntansi(data.saldo_awal)} />
                        <Row terang label="Saldo akhir" value={akuntansi(data.saldo_akhir)} />
                    </Card>

                    <SectionTitle>Mutasi ({data.baris.length})</SectionTitle>
                    {data.baris.length === 0 ? (
                        <Card><Text style={typography.muted}>Tidak ada mutasi pada periode ini.</Text></Card>
                    ) : data.baris.map((b, i) => (
                        <Card key={`${b.nomor_jurnal}-${i}`}>
                            <View style={styles.baris}>
                                <Text style={styles.nomor}>{b.nomor_jurnal}</Text>
                                <Text style={typography.caption}>{tanggalPendek(b.tanggal)}</Text>
                            </View>
                            <Text style={typography.muted} numberOfLines={2}>{b.keterangan}</Text>
                            <Row label="Debit" value={akuntansi(b.debit)} />
                            <Row label="Kredit" value={akuntansi(b.kredit)} />
                            <Row label="Saldo" value={akuntansi(b.saldo)} />
                        </Card>
                    ))}

                    <Card tone="teal">
                        <Row label="Total debit" value={akuntansi(data.total_debit)} />
                        <Row label="Total kredit" value={akuntansi(data.total_kredit)} />
                    </Card>
                </>
            )}
        </Halaman>
    );
}

const styles = StyleSheet.create({
    kodeTerang: { fontSize: 12, fontWeight: '800', color: 'rgba(255,255,255,0.85)', letterSpacing: 1 },
    namaTerang: { fontSize: 20, fontWeight: '800', color: '#fff' },
    baris: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
    nomor: { fontSize: 13, fontWeight: '800', color: colors.primary },
});
