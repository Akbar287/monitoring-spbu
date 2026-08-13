import { useCallback, useState } from 'react';
import { StyleSheet, Text, View } from 'react-native';
import { Badge, Card, Halaman, Notice } from '../../components/ui';
import DaftarData from '../../components/DaftarData';
import SelectField from '../../components/SelectField';
import { Financial } from '../../api/endpoints';
import { colors, spacing, typography } from '../../theme';

const TIPE = [
    { value: '', label: 'Semua tipe' },
    { value: 'aset', label: 'Aset' },
    { value: 'kewajiban', label: 'Kewajiban' },
    { value: 'modal', label: 'Modal' },
    { value: 'pendapatan', label: 'Pendapatan' },
    { value: 'hpp', label: 'HPP' },
    { value: 'beban', label: 'Beban' },
    { value: 'pendapatan_lain', label: 'Pendapatan lain-lain' },
    { value: 'beban_lain', label: 'Beban lain-lain' },
];

/** Bagan akun (COA) — rujukan kode akun. Baca-saja. */
export default function CoaScreen() {
    const [tipe, setTipe] = useState('');

    const ambil = useCallback(
        (page, cari, perPage) => Financial.coa({ page, cari, per_page: perPage, tipe_akun: tipe || undefined }),
        [tipe]
    );

    return (
        <Halaman judul="Bagan Akun (COA)" subjudul="Rujukan kode akun" warna="ungu" scroll={false}>
            <DaftarData
                key={tipe}
                ambil={ambil}
                cariPlaceholder="Cari kode atau nama akun…"
                jumlahFilterAktif={tipe ? 1 : 0}
                filter={<SelectField label="Tipe akun" value={tipe} onChange={setTipe} options={TIPE} />}
                kepala={
                    <Notice tone="warning">
                        Baca-saja. Kode akun dirujuk konstanta App\Support\Akun dan tidak boleh diubah dari aplikasi.
                    </Notice>
                }
                kosong={{ judul: 'Tidak ditemukan' }}
                render={(item) => (
                    <Card tone={item.is_header ? 'abu' : undefined}>
                        <View style={styles.baris}>
                            <Text style={styles.kode}>{item.kode_akun}</Text>
                            <View style={styles.badge}>
                                {item.is_kas && <Badge label="Kas" tone="hijau" />}
                                {!item.is_aktif && <Badge label="Nonaktif" tone="merah" />}
                                <Badge label={item.saldo_normal} tone="biru" />
                            </View>
                        </View>
                        <Text style={[typography.heading, item.is_header && styles.header]}>{item.nama_akun}</Text>
                        <Text style={typography.caption}>
                            {item.tipe_akun}{item.kategori_arus_kas ? ` · arus kas: ${item.kategori_arus_kas}` : ''}
                        </Text>
                    </Card>
                )}
            />
        </Halaman>
    );
}

const styles = StyleSheet.create({
    baris: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
    kode: { fontSize: 13, fontWeight: '800', color: colors.primary, letterSpacing: 0.5 },
    header: { fontWeight: '900' },
    badge: { flexDirection: 'row', gap: spacing.xs },
});
