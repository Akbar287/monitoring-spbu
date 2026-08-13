import { useEffect, useState } from 'react';
import { Pressable, StyleSheet, Text, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { Button, Card, Halaman, Loading, Notice, Row, SectionTitle } from '../../components/ui';
import SelectField from '../../components/SelectField';
import DateField from '../../components/DateField';
import { Setoran } from '../../api/endpoints';
import { useOpsi } from '../../master/OpsiContext';
import { rupiah } from '../../utils/format';
import { colors, spacing, typography } from '../../theme';

/** Buka setoran baru: pilih shift lalu centang stand meter yang ikut. */
export default function SetoranFormScreen({ navigation }) {
    const { ambil } = useOpsi();

    const [spbuId, setSpbuId] = useState('');
    const [jamKerjaId, setJamKerjaId] = useState('');
    const [tanggal, setTanggal] = useState(new Date().toISOString().slice(0, 10));
    const [tersedia, setTersedia] = useState(null);
    const [dipilih, setDipilih] = useState([]);
    const [proses, setProses] = useState(false);
    const [error, setError] = useState(null);

    useEffect(() => {
        const spbu = ambil('spbu');
        if (spbu.length === 1 && !spbuId) setSpbuId(spbu[0].value);
    }, [ambil, spbuId]);

    // muat stand meter tiap kali tanggal / shift berubah
    useEffect(() => {
        if (!tanggal || !jamKerjaId) { setTersedia(null); return; }
        setTersedia(undefined);
        setDipilih([]);
        Setoran.standMeterTersedia(tanggal, jamKerjaId)
            .then((d) => setTersedia(d.items))
            .catch(() => setTersedia([]));
    }, [tanggal, jamKerjaId]);

    const alih = (id) => setDipilih((l) => (l.includes(id) ? l.filter((x) => x !== id) : [...l, id]));

    const totalTerpilih = (tersedia || [])
        .filter((s) => dipilih.includes(s.value))
        .reduce((a, s) => a + Number(s.total || 0), 0);

    const kirim = async () => {
        if (proses) return;
        setError(null);
        setProses(true);
        try {
            const hasil = await Setoran.buat({
                spbu_id: spbuId,
                jam_kerja_id: jamKerjaId,
                tanggal,
                stand_meter_id: dipilih,
            });
            navigation.replace('SetoranDetail', { id: hasil.penjualan_id });
        } catch (e) {
            setError(e);
        } finally {
            setProses(false);
        }
    };

    const siap = spbuId && jamKerjaId && tanggal && dipilih.length > 0;

    return (
        <Halaman judul="Buka Setoran" warna="hijau" keyboard>
            {!!error && <Notice tone="danger">{error.message}</Notice>}

            <Card>
                <SelectField label="SPBU *" value={spbuId} onChange={setSpbuId} options={ambil('spbu')} disabled={proses} />
                <DateField label="Tanggal *" value={tanggal} onChange={setTanggal} disabled={proses} />
                <SelectField label="Jam Kerja *" value={jamKerjaId} onChange={setJamKerjaId}
                    options={ambil('jam_kerja')} disabled={proses} />
            </Card>

            {tersedia === undefined && <Card><Loading label="Mencari stand meter…" /></Card>}

            {Array.isArray(tersedia) && (
                <>
                    <SectionTitle>Stand meter shift ini ({tersedia.length})</SectionTitle>

                    {tersedia.length === 0 ? (
                        <Card>
                            <Notice tone="warning">
                                Tidak ada stand meter pada tanggal & shift ini yang belum dipakai setoran.
                                Catat totalisator lebih dulu.
                            </Notice>
                        </Card>
                    ) : (
                        tersedia.map((s) => {
                            const aktif = dipilih.includes(s.value);
                            return (
                                <Pressable key={s.value} onPress={() => !proses && alih(s.value)}>
                                    <Card tone={aktif ? 'hijau' : undefined}>
                                        <View style={styles.baris}>
                                            <Ionicons
                                                name={aktif ? 'checkbox' : 'square-outline'}
                                                size={22}
                                                color={aktif ? colors.primary : colors.textMuted}
                                            />
                                            <View style={styles.isi}>
                                                <Text style={typography.heading}>{s.nozzle} · {s.produk}</Text>
                                                <Text style={typography.caption}>
                                                    {s.stand_meter_awal} → {s.stand_meter_akhir}
                                                </Text>
                                            </View>
                                        </View>
                                        <Row label="Liter" value={`${s.liter} L`} />
                                        <Row label="Harga jual" value={rupiah(s.harga_jual)} />
                                        <Row label="Total" value={rupiah(s.total)} />
                                        {!s.is_confirmation && (
                                            <Notice tone="warning">Totalisator ini belum dikonfirmasi.</Notice>
                                        )}
                                        {!s.harga_jual && (
                                            <Notice tone="danger">Produk belum punya harga berlaku.</Notice>
                                        )}
                                    </Card>
                                </Pressable>
                            );
                        })
                    )}

                    {dipilih.length > 0 && (
                        <Card gradien="hijau">
                            <Text style={styles.labelTerang}>{dipilih.length} stand meter dipilih</Text>
                            <Text style={styles.angka}>{rupiah(totalTerpilih.toFixed(6))}</Text>
                        </Card>
                    )}
                </>
            )}

            <Button label="Buka Setoran" variant="success" disabled={!siap} loading={proses} onPress={kirim} />
            <Text style={[typography.caption, styles.catatan]}>
                Total penjualan dihitung server dari liter × harga berlaku, presisi 6 desimal.
            </Text>
        </Halaman>
    );
}

const styles = StyleSheet.create({
    baris: { flexDirection: 'row', alignItems: 'center', gap: spacing.md },
    isi: { flex: 1, gap: 2 },
    labelTerang: { fontSize: 13, color: 'rgba(255,255,255,0.9)', fontWeight: '600' },
    angka: { fontSize: 22, fontWeight: '800', color: '#fff' },
    catatan: { textAlign: 'center' },
});
