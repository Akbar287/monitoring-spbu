import { useState } from 'react';
import { ScrollView, StyleSheet } from 'react-native';
import { Halaman, Button, Card, Field, Notice } from '../../components/ui';
import SelectField from '../../components/SelectField';
import { Procurement } from '../../api/endpoints';
import { useOpsi } from '../../master/OpsiContext';
import { colors, spacing } from '../../theme';

export default function RencanaBaruScreen({ navigation }) {
    const { ambil } = useOpsi();
    const [spbuId, setSpbuId] = useState('');
    const [kode, setKode] = useState('');
    const [keterangan, setKeterangan] = useState('');
    const [proses, setProses] = useState(false);
    const [error, setError] = useState(null);

    const kirim = async () => {
        if (proses) return;
        setError(null);
        setProses(true);
        try {
            const hasil = await Procurement.perencanaan.buat({
                spbu_id: spbuId,
                kode_pembelian: kode.trim() || undefined,
                keterangan: keterangan.trim() || undefined,
            });
            // langsung buka dokumennya supaya baris produk bisa segera diisi
            navigation.replace('Dokumen', { id: hasil.id, tahap: 'perencanaan' });
        } catch (e) {
            setError(e);
        } finally {
            setProses(false);
        }
    };

    return (
        <Halaman judul={'Rencana Pembelian Baru'} warna="oranye" keyboard>
                {!!error && <Notice tone="danger">{error.fieldError?.('kode_pembelian') || error.message}</Notice>}

                <Card>
                    <SelectField
                        label="SPBU *"
                        value={spbuId}
                        options={ambil('spbu')}
                        onChange={setSpbuId}
                        error={error?.fieldError?.('spbu_id')}
                        disabled={proses}
                    />
                    <Field
                        label="Kode Pembelian"
                        value={kode}
                        onChangeText={setKode}
                        autoCapitalize="characters"
                        placeholder="Kosongkan untuk dibuat otomatis"
                        hint="Format otomatis: A-YYYYMMDD-n"
                        editable={!proses}
                    />
                    <Field
                        label="Keterangan"
                        value={keterangan}
                        onChangeText={setKeterangan}
                        multiline
                        editable={!proses}
                    />
                </Card>

                <Button label="Buat Rencana" onPress={kirim} loading={proses} disabled={!spbuId} />
            </Halaman>
    );
}

const styles = StyleSheet.create({
    screen: { flex: 1, backgroundColor: colors.background },
    isi: { padding: spacing.lg, gap: spacing.md },
});
