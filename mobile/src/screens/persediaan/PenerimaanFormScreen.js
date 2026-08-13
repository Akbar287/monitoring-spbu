import { useCallback, useEffect, useState } from 'react';
import { Pressable, StyleSheet, Text, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { Button, Card, Field, Halaman, Loading, Notice, Row, SectionTitle } from '../../components/ui';
import SelectField from '../../components/SelectField';
import DateField from '../../components/DateField';
import TimeField from '../../components/TimeField';
import { Persediaan } from '../../api/endpoints';
import { useOpsi } from '../../master/OpsiContext';
import { liter } from '../../utils/format';
import { colors, spacing, typography } from '../../theme';

const barisKosong = () => ({ dombak_id: '', tinggi_sebelum: '', tinggi_sesudah: '', jumlah: '', konversi: [] });

/** Bongkar satu LO ke satu atau beberapa dombak. */
export default function PenerimaanFormScreen({ route, navigation }) {
    const { loId, judul } = route.params;
    const { ambil: ambilOpsi } = useOpsi();

    const [lo, setLo] = useState(null);
    const [dombakList, setDombakList] = useState([]);
    const [tanggal, setTanggal] = useState(new Date().toISOString().slice(0, 10));
    const [waktu, setWaktu] = useState('08:00');
    const [jamKerjaId, setJamKerjaId] = useState('');
    const [alokasi, setAlokasi] = useState([barisKosong()]);
    const [proses, setProses] = useState(false);
    const [error, setError] = useState(null);

    useEffect(() => {
        Persediaan.penerimaan.detailLo(loId).then(setLo).catch((e) => setError(e));
        Persediaan.dombak().then((d) => setDombakList(d.items.filter((x) => x.siap))).catch(() => setDombakList([]));
    }, [loId]);

    const ubah = (i, kunci, nilai) => setAlokasi((lama) =>
        lama.map((b, idx) => (idx === i ? { ...b, [kunci]: nilai } : b)));

    // muat tabel konversi begitu dombak dipilih
    const pilihDombak = async (i, dombakId) => {
        ubah(i, 'dombak_id', dombakId);
        ubah(i, 'tinggi_sebelum', '');
        ubah(i, 'tinggi_sesudah', '');
        try {
            const d = await Persediaan.konversi(dombakId, '');
            setAlokasi((lama) => lama.map((b, idx) => (idx === i ? { ...b, konversi: d.items } : b)));
        } catch {
            ubah(i, 'konversi', []);
        }
    };

    const kirim = async () => {
        if (proses) return;
        setError(null);
        setProses(true);
        try {
            await Persediaan.penerimaan.simpan(loId, {
                tanggal,
                waktu,
                jam_kerja_id: jamKerjaId,
                alokasi: alokasi.map((b) => ({
                    dombak_id: b.dombak_id,
                    tinggi_sebelum: b.tinggi_sebelum,
                    tinggi_sesudah: b.tinggi_sesudah,
                    jumlah: b.jumlah,
                })),
            });
            navigation.goBack();
        } catch (e) {
            setError(e);
        } finally {
            setProses(false);
        }
    };

    if (!lo && !error) return <Halaman judul="Penerimaan" warna="hijau"><Loading /></Halaman>;

    const lengkap = jamKerjaId && alokasi.every((b) => b.dombak_id && b.tinggi_sebelum && b.tinggi_sesudah && b.jumlah !== '');

    return (
        <Halaman judul={judul || 'Penerimaan BBM'} warna="hijau" keyboard>
            {!!error && <Notice tone="danger">{error.message}</Notice>}

            {!!lo && (
                <Card gradien="hijau">
                    <Text style={styles.labelTerang}>{lo.judul}</Text>
                    <Text style={styles.angka}>{liter(lo.jumlah)}</Text>
                    <Row terang label="Produk" value={lo.produk} />
                    <Row terang label="No. Faktur" value={lo.no_faktur} />
                    <Row terang label="No. Polisi" value={lo.pengiriman?.no_pol} />
                </Card>
            )}

            <Card>
                <DateField label="Tanggal bongkar *" value={tanggal} onChange={setTanggal} disabled={proses} />
                <TimeField label="Jam bongkar *" value={waktu} onChange={setWaktu} disabled={proses} />
                <SelectField label="Jam Kerja *" value={jamKerjaId} onChange={setJamKerjaId}
                    options={ambilOpsi('jam_kerja')} disabled={proses} />
            </Card>

            <SectionTitle
                aksi={
                    <Pressable onPress={() => setAlokasi((l) => [...l, barisKosong()])} hitSlop={10}>
                        <Text style={styles.tautan}>+ Tambah dombak</Text>
                    </Pressable>
                }
            >
                Alokasi ke dombak
            </SectionTitle>

            {alokasi.map((b, i) => {
                const dombak = dombakList.find((d) => d.value === b.dombak_id);
                const vSebelum = b.konversi.find((k) => k.tinggi === b.tinggi_sebelum);
                const vSesudah = b.konversi.find((k) => k.tinggi === b.tinggi_sesudah);
                const terukur = vSebelum && vSesudah
                    ? (Number(vSesudah.volume) - Number(vSebelum.volume)).toFixed(6) : null;
                const selisih = terukur !== null && b.jumlah !== ''
                    ? (Number(terukur) - Number(b.jumlah)).toFixed(6) : null;

                return (
                    <Card key={i} tone="hijau">
                        <View style={styles.baris}>
                            <Text style={typography.heading}>Dombak {i + 1}</Text>
                            {alokasi.length > 1 && (
                                <Pressable onPress={() => setAlokasi((l) => l.filter((_, x) => x !== i))} hitSlop={10}>
                                    <Ionicons name="close-circle" size={20} color={colors.danger} />
                                </Pressable>
                            )}
                        </View>

                        <SelectField label="Dombak *" value={b.dombak_id}
                            onChange={(v) => pilihDombak(i, v)} options={dombakList} disabled={proses} />
                        {!!dombak && <Row label="Saldo tercatat" value={liter(dombak.stok)} />}

                        <SelectField label="Tinggi sebelum bongkar *" value={b.tinggi_sebelum}
                            onChange={(v) => ubah(i, 'tinggi_sebelum', v)} options={b.konversi} disabled={proses} />
                        <SelectField label="Tinggi setelah bongkar *" value={b.tinggi_sesudah}
                            onChange={(v) => ubah(i, 'tinggi_sesudah', v)} options={b.konversi} disabled={proses} />

                        <Field label="Jumlah menurut surat jalan (liter) *" value={b.jumlah}
                            onChangeText={(v) => ubah(i, 'jumlah', v)} keyboardType="numeric" editable={!proses}
                            hint="Boleh sampai 6 angka di belakang koma." />

                        {terukur !== null && (
                            <>
                                <Row label="Kenaikan terukur" value={liter(terukur)} />
                                {selisih !== null && (
                                    <Notice tone={Number(selisih) === 0 ? 'success' : 'warning'}>
                                        {Number(selisih) === 0
                                            ? 'Terukur sama dengan surat jalan.'
                                            : `Selisih ${Number(selisih) < 0 ? 'kurang' : 'lebih'} ${liter(Math.abs(Number(selisih)).toFixed(6))} akan dicatat sebagai losses.`}
                                    </Notice>
                                )}
                            </>
                        )}
                    </Card>
                );
            })}

            <Button label="Simpan Penerimaan" variant="success" disabled={!lengkap} loading={proses} onPress={kirim} />
            <Text style={[typography.caption, styles.catatan]}>
                Saldo dombak berubah setelah penerimaan dikonfirmasi di tab Verifikasi.
            </Text>
        </Halaman>
    );
}

const styles = StyleSheet.create({
    labelTerang: { fontSize: 13, color: 'rgba(255,255,255,0.9)', fontWeight: '600' },
    angka: { fontSize: 24, fontWeight: '800', color: '#fff' },
    baris: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
    tautan: { color: colors.primary, fontWeight: '700', fontSize: 13 },
    catatan: { textAlign: 'center' },
});
