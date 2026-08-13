import { useCallback } from 'react';
import { StyleSheet, Text, View } from 'react-native';
import { Card, ErrorState, Halaman, Loading, Notice, Row, SectionTitle } from '../../components/ui';
import { Financial } from '../../api/endpoints';
import { useMuat } from '../../hooks/useMuat';
import { akuntansi, tanggalPanjang } from '../../utils/format';
import { colors, spacing, typography } from '../../theme';

/** Rincian satu jurnal — tiap baris menyebut kode akunnya. */
export default function JurnalDetailScreen({ route }) {
    const { id } = route.params;
    const { data, memuat, error, muatUlang } = useMuat(
        useCallback(() => Financial.jurnalDetail(id), [id])
    );

    const bungkus = (isi) => <Halaman judul="Detail Jurnal" warna="biru">{isi}</Halaman>;
    if (memuat && !data) return bungkus(<Loading />);
    if (error && !data) return bungkus(<ErrorState pesan={error.message} onCoba={muatUlang} />);

    return bungkus(
        <>
            {!data.seimbang && (
                <Notice tone="danger">
                    PERINGATAN: jurnal ini tidak seimbang — total debit ≠ total kredit. Segera periksa.
                </Notice>
            )}

            <Card gradien="biru">
                <Text style={styles.nomor}>{data.nomor_jurnal}</Text>
                <Text style={styles.ket}>{data.keterangan}</Text>
                <Row terang label="Tanggal" value={tanggalPanjang(data.tanggal)} />
                <Row terang label="Tipe sumber" value={data.tipe_sumber} />
                <Row terang label="SPBU" value={data.spbu} />
                {!!data.diposting_oleh && <Row terang label="Diposting oleh" value={data.diposting_oleh} />}
            </Card>

            <SectionTitle>Baris jurnal</SectionTitle>
            {data.detail.map((d, i) => (
                <Card key={i} tone={Number(d.debit) > 0 ? 'biru' : 'ungu'}>
                    <Text style={styles.kode}>{d.kode_akun}</Text>
                    <Text style={typography.heading}>{d.nama_akun}</Text>
                    {!!d.keterangan && <Text style={typography.caption}>{d.keterangan}</Text>}
                    <Row label="Debit" value={akuntansi(d.debit)} />
                    <Row label="Kredit" value={akuntansi(d.kredit)} />
                </Card>
            ))}

            <Card tone={data.seimbang ? 'hijau' : 'merah'}>
                <Row label="Total debit" value={akuntansi(data.total_debit)} />
                <Row label="Total kredit" value={akuntansi(data.total_kredit)} />
                <Row label="Seimbang" value={data.seimbang ? 'Ya' : 'TIDAK'} />
            </Card>
        </>
    );
}

const styles = StyleSheet.create({
    nomor: { fontSize: 20, fontWeight: '800', color: '#fff' },
    ket: { fontSize: 13, color: 'rgba(255,255,255,0.9)', marginBottom: spacing.xs },
    kode: { fontSize: 11, fontWeight: '800', color: colors.primary, letterSpacing: 0.5 },
});
