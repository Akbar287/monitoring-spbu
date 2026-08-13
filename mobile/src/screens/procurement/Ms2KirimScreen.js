import { useState } from 'react';
import { KeyboardAvoidingView, Platform, Pressable, ScrollView, StyleSheet, Text, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { Halaman, Button, Card, Field, Notice, Row } from '../../components/ui';
import DateField from '../../components/DateField';
import { Procurement } from '../../api/endpoints';
import { desimal6 } from '../../utils/format';
import { colors, radius, spacing, typography } from '../../theme';

/** Buat dokumen Pengiriman dari MS2 dan tandai baris yang ikut diangkut. */
export default function Ms2KirimScreen({ route, navigation }) {
    const { id, detail = [] } = route.params;

    const [tanggal, setTanggal] = useState(hariIni());
    const [noDo, setNoDo] = useState('');
    const [noPol, setNoPol] = useState('');
    const [supir, setSupir] = useState('');
    const [catatan, setCatatan] = useState('');
    const [terpilih, setTerpilih] = useState(() => detail.filter((d) => !d.is_delivery).map((d) => d.id));
    const [proses, setProses] = useState(false);
    const [error, setError] = useState(null);

    const alih = (idBaris) =>
        setTerpilih((s) => (s.includes(idBaris) ? s.filter((x) => x !== idBaris) : [...s, idBaris]));

    const kirim = async () => {
        if (proses) return;
        setError(null);
        setProses(true);
        try {
            const hasil = await Procurement.ms2.kirim(id, {
                tanggal,
                no_do: noDo.trim() || undefined,
                no_pol: noPol.trim(),
                nama_supir: supir.trim() || undefined,
                catatan: catatan.trim() || undefined,
                detail_ids: terpilih,
            });
            navigation.replace('Delivery', { id: hasil.pengiriman_id });
        } catch (e) {
            setError(e);
        } finally {
            setProses(false);
        }
    };

    return (
        <Halaman judul={'Kirim ke Delivery'} warna="oranye" keyboard>
                    {!!error && <Notice tone="danger">{error.fieldError?.('no_pol') || error.message}</Notice>}

                    <Card>
                        <DateField label="Tanggal Pengiriman *" value={tanggal} onChange={setTanggal} disabled={proses} />
                        <Field label="No. DO" value={noDo} onChangeText={setNoDo} autoCapitalize="characters" editable={!proses} />
                        <Field label="No. Polisi *" value={noPol} onChangeText={setNoPol} autoCapitalize="characters"
                            placeholder="mis. BE 1234 XY" editable={!proses} />
                        <Field label="Nama Supir" value={supir} onChangeText={setSupir} editable={!proses} />
                        <Field label="Catatan" value={catatan} onChangeText={setCatatan} multiline editable={!proses} />
                    </Card>

                    <Card>
                        <Text style={typography.heading}>Produk yang diangkut</Text>
                        {detail.map((b) => {
                            const aktif = terpilih.includes(b.id);
                            return (
                                <Pressable key={b.id} onPress={() => alih(b.id)} style={[styles.kotak, aktif && styles.kotakAktif]}>
                                    <View style={styles.barisAtas}>
                                        <Text style={styles.produk}>{b.produk}</Text>
                                        <Ionicons name={aktif ? 'checkbox' : 'square-outline'} size={22}
                                            color={aktif ? colors.primary : colors.textMuted} />
                                    </View>
                                    <Row label="Kuantitas" value={`${desimal6(b.jumlah)} ${b.satuan_jumlah}`} />
                                    <Row label="Shift" value={b.jam_kerja} />
                                </Pressable>
                            );
                        })}
                        <Text style={typography.caption}>
                            Produk yang tidak dicentang tetap di MS2 dan bisa dikirim pada rit berikutnya.
                        </Text>
                    </Card>

                    <Button
                        label={`Kirim ke Delivery (${terpilih.length} produk)`}
                        variant="success"
                        onPress={kirim}
                        loading={proses}
                        disabled={terpilih.length === 0 || !noPol.trim()}
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
    flex: { flex: 1 },
    isi: { padding: spacing.lg, gap: spacing.md, paddingBottom: spacing.xxl },
    barisAtas: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', gap: spacing.sm },
    kotak: { borderWidth: 1, borderColor: colors.border, borderRadius: radius.md, padding: spacing.md, gap: 2, backgroundColor: colors.background },
    kotakAktif: { borderColor: colors.primary, backgroundColor: colors.primarySoft },
    produk: { fontSize: 15, fontWeight: '700', color: colors.text },
});
