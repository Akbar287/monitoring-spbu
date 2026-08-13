import { useCallback, useEffect, useState } from 'react';
import { StyleSheet, Text } from 'react-native';
import { Button, Card, Halaman, Loading, Notice, Row } from '../../components/ui';
import SelectField from '../../components/SelectField';
import DateField from '../../components/DateField';
import { Persediaan } from '../../api/endpoints';
import { useOpsi } from '../../master/OpsiContext';
import { liter } from '../../utils/format';
import { typography } from '../../theme';

/**
 * Catat pengukuran fisik satu dombak.
 * Tinggi dipilih dari tabel konversi milik dombak — volume-nya sudah pasti
 * ada di database, jadi tidak mungkin salah ketik nilai yang tak dikenal.
 */
export default function StokTakingFormScreen({ navigation }) {
    const { ambil: ambilOpsi } = useOpsi();

    const [dombakList, setDombakList] = useState(null);
    const [dombakId, setDombakId] = useState('');
    const [jamKerjaId, setJamKerjaId] = useState('');
    const [tanggal, setTanggal] = useState(new Date().toISOString().slice(0, 10));
    const [tinggi, setTinggi] = useState('');
    const [konversi, setKonversi] = useState([]);
    const [cari, setCari] = useState('');
    const [proses, setProses] = useState(false);
    const [error, setError] = useState(null);

    useEffect(() => { Persediaan.dombak().then((d) => setDombakList(d.items)).catch(() => setDombakList([])); }, []);

    // muat pilihan tinggi tiap kali dombak berganti
    useEffect(() => {
        if (!dombakId) { setKonversi([]); return; }
        setTinggi('');
        Persediaan.konversi(dombakId, cari).then((d) => setKonversi(d.items)).catch(() => setKonversi([]));
    }, [dombakId, cari]);

    const dombak = (dombakList || []).find((d) => d.value === dombakId);
    const terpilih = konversi.find((k) => k.tinggi === tinggi);

    const kirim = async () => {
        if (proses) return;
        setError(null);
        setProses(true);
        try {
            const hasil = await Persediaan.taking.simpan({
                dombak_id: dombakId,
                jam_kerja_id: jamKerjaId,
                tanggal,
                tinggi,
            });
            navigation.replace('StokTakingDetail', { id: hasil.id });
        } catch (e) {
            setError(e);
        } finally {
            setProses(false);
        }
    };

    if (!dombakList) return <Halaman judul="Stok Taking" warna="oranye"><Loading /></Halaman>;

    const siap = dombakId && jamKerjaId && tanggal && tinggi;
    // selisih dihitung sebagai pratinjau; angka resmi tetap dari server
    const selisih = terpilih && dombak
        ? (Number(terpilih.volume) - Number(dombak.stok)).toFixed(6)
        : null;

    return (
        <Halaman judul="Catat Stok Taking" warna="oranye" keyboard>
            {!!error && (
                <Notice tone="danger">{error.fieldError?.('tinggi') || error.message}</Notice>
            )}

            <Card>
                <SelectField
                    label="Dombak *"
                    value={dombakId}
                    onChange={setDombakId}
                    options={(dombakList || []).filter((d) => d.siap)}
                    disabled={proses}
                    hint="Hanya dombak yang sudah punya produk & tabel konversi."
                />
                <SelectField
                    label="Jam Kerja *"
                    value={jamKerjaId}
                    onChange={setJamKerjaId}
                    options={ambilOpsi('jam_kerja')}
                    disabled={proses}
                />
                <DateField label="Tanggal *" value={tanggal} onChange={setTanggal} disabled={proses} />
            </Card>

            {!!dombak && (
                <Card tone="indigo">
                    <Text style={typography.heading}>{dombak.label}</Text>
                    <Row label="Produk" value={dombak.produk} />
                    <Row label="Saldo tercatat" value={liter(dombak.stok)} />
                </Card>
            )}

            {!!dombakId && (
                <Card>
                    <SelectField
                        label="Tinggi terukur *"
                        value={tinggi}
                        onChange={setTinggi}
                        options={konversi}
                        disabled={proses}
                        hint={`Menampilkan ${konversi.length} baris konversi. Ketik di kotak cari untuk menyaring.`}
                    />
                    {!!terpilih && (
                        <>
                            <Row label="Volume dari tabel" value={liter(terpilih.volume)} />
                            <Row
                                label="Selisih terhadap saldo"
                                value={`${Number(selisih) >= 0 ? '+' : '−'} ${liter(Math.abs(Number(selisih)).toFixed(6))}`}
                            />
                            <Notice tone={Number(selisih) === 0 ? 'success' : 'warning'}>
                                {Number(selisih) === 0
                                    ? 'Tidak ada selisih.'
                                    : `Selisih akan dibukukan sebagai ${Number(selisih) < 0 ? 'losses' : 'profit'}.`}
                            </Notice>
                        </>
                    )}
                </Card>
            )}

            <Button label="Simpan Pengukuran" warna="oranye" disabled={!siap} loading={proses} onPress={kirim} />
            <Text style={[typography.caption, styles.catatan]}>
                Saldo dombak baru berubah setelah dokumen ini dikonfirmasi.
            </Text>
        </Halaman>
    );
}

const styles = StyleSheet.create({ catatan: { textAlign: 'center' } });
