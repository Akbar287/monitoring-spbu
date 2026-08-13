import { useEffect, useState } from 'react';
import { StyleSheet, Text } from 'react-native';
import { Button, Card, Halaman, Loading, Notice, Row, SectionTitle } from '../../components/ui';
import SelectField from '../../components/SelectField';
import DateField from '../../components/DateField';
import TimeField from '../../components/TimeField';
import { Persediaan } from '../../api/endpoints';
import { useOpsi } from '../../master/OpsiContext';
import { liter } from '../../utils/format';
import { typography } from '../../theme';

/** Pindahkan BBM antar dombak berproduk sama. */
export default function StokTransferFormScreen({ navigation }) {
    const { ambil: ambilOpsi } = useOpsi();

    const [dombakList, setDombakList] = useState(null);
    const [form, setForm] = useState({
        tanggal: new Date().toISOString().slice(0, 10),
        waktu: '08:00',
        jam_kerja_id: '',
        dombak_asal_id: '',
        tinggi_asal_sebelum: '',
        tinggi_asal_sesudah: '',
        dombak_tujuan_id: '',
        tinggi_tujuan_sebelum: '',
        tinggi_tujuan_sesudah: '',
    });
    const [kAsal, setKAsal] = useState([]);
    const [kTujuan, setKTujuan] = useState([]);
    const [proses, setProses] = useState(false);
    const [error, setError] = useState(null);

    useEffect(() => { Persediaan.dombak().then((d) => setDombakList(d.items.filter((x) => x.siap))).catch(() => setDombakList([])); }, []);

    const set = (k, v) => setForm((f) => ({ ...f, [k]: v }));

    const pilihDombak = async (sisi, id) => {
        const kunci = sisi === 'asal' ? 'dombak_asal_id' : 'dombak_tujuan_id';
        setForm((f) => ({
            ...f,
            [kunci]: id,
            [`tinggi_${sisi}_sebelum`]: '',
            [`tinggi_${sisi}_sesudah`]: '',
        }));
        try {
            const d = await Persediaan.konversi(id, '');
            (sisi === 'asal' ? setKAsal : setKTujuan)(d.items);
        } catch {
            (sisi === 'asal' ? setKAsal : setKTujuan)([]);
        }
    };

    if (!dombakList) return <Halaman judul="Stok Transfer" warna="ungu"><Loading /></Halaman>;

    const asal = dombakList.find((d) => d.value === form.dombak_asal_id);
    const tujuan = dombakList.find((d) => d.value === form.dombak_tujuan_id);
    const vol = (list, t) => list.find((k) => k.tinggi === t)?.volume;

    const dikirim = vol(kAsal, form.tinggi_asal_sebelum) && vol(kAsal, form.tinggi_asal_sesudah)
        ? (Number(vol(kAsal, form.tinggi_asal_sebelum)) - Number(vol(kAsal, form.tinggi_asal_sesudah))).toFixed(6) : null;
    const diterima = vol(kTujuan, form.tinggi_tujuan_sebelum) && vol(kTujuan, form.tinggi_tujuan_sesudah)
        ? (Number(vol(kTujuan, form.tinggi_tujuan_sesudah)) - Number(vol(kTujuan, form.tinggi_tujuan_sebelum))).toFixed(6) : null;
    const selisih = dikirim !== null && diterima !== null ? (Number(diterima) - Number(dikirim)).toFixed(6) : null;

    const bedaProduk = asal && tujuan && asal.stok_inventory_id !== tujuan.stok_inventory_id;
    const siap = Object.values(form).every((v) => String(v).trim() !== '') && !bedaProduk;

    const kirim = async () => {
        if (proses) return;
        setError(null);
        setProses(true);
        try {
            const hasil = await Persediaan.transfer.simpan(form);
            navigation.replace('StokTransferDetail', { id: hasil.id });
        } catch (e) {
            setError(e);
        } finally {
            setProses(false);
        }
    };

    return (
        <Halaman judul="Catat Stok Transfer" warna="ungu" keyboard>
            {!!error && <Notice tone="danger">{error.message}</Notice>}
            {bedaProduk && <Notice tone="danger">Kedua dombak harus berisi produk yang sama.</Notice>}

            <Card>
                <DateField label="Tanggal *" value={form.tanggal} onChange={(v) => set('tanggal', v)} disabled={proses} />
                <TimeField label="Jam transfer *" value={form.waktu} onChange={(v) => set('waktu', v)} disabled={proses} />
                <SelectField label="Jam Kerja *" value={form.jam_kerja_id} onChange={(v) => set('jam_kerja_id', v)}
                    options={ambilOpsi('jam_kerja')} disabled={proses} />
            </Card>

            <SectionTitle>Dombak asal</SectionTitle>
            <Card tone="merah">
                <SelectField label="Dombak asal *" value={form.dombak_asal_id}
                    onChange={(v) => pilihDombak('asal', v)} options={dombakList} disabled={proses} />
                {!!asal && <Row label="Saldo tercatat" value={liter(asal.stok)} />}
                <SelectField label="Tinggi sebelum *" value={form.tinggi_asal_sebelum}
                    onChange={(v) => set('tinggi_asal_sebelum', v)} options={kAsal} disabled={proses} />
                <SelectField label="Tinggi sesudah *" value={form.tinggi_asal_sesudah}
                    onChange={(v) => set('tinggi_asal_sesudah', v)} options={kAsal} disabled={proses} />
                {dikirim !== null && <Row label="Dikirim" value={liter(dikirim)} />}
            </Card>

            <SectionTitle>Dombak tujuan</SectionTitle>
            <Card tone="hijau">
                <SelectField label="Dombak tujuan *" value={form.dombak_tujuan_id}
                    onChange={(v) => pilihDombak('tujuan', v)}
                    options={dombakList.filter((d) => d.value !== form.dombak_asal_id)} disabled={proses} />
                {!!tujuan && <Row label="Saldo tercatat" value={liter(tujuan.stok)} />}
                <SelectField label="Tinggi sebelum *" value={form.tinggi_tujuan_sebelum}
                    onChange={(v) => set('tinggi_tujuan_sebelum', v)} options={kTujuan} disabled={proses} />
                <SelectField label="Tinggi sesudah *" value={form.tinggi_tujuan_sesudah}
                    onChange={(v) => set('tinggi_tujuan_sesudah', v)} options={kTujuan} disabled={proses} />
                {diterima !== null && <Row label="Diterima" value={liter(diterima)} />}
            </Card>

            {selisih !== null && (
                <Notice tone={Number(selisih) === 0 ? 'success' : 'warning'}>
                    {Number(selisih) === 0
                        ? 'Jumlah kirim dan terima sama — tidak ada losses.'
                        : `Selisih ${Number(selisih) < 0 ? 'kurang' : 'lebih'} ${liter(Math.abs(Number(selisih)).toFixed(6))} akan dicatat sebagai losses transfer.`}
                </Notice>
            )}

            <Button label="Simpan Transfer" warna="ungu" disabled={!siap} loading={proses} onPress={kirim} />
            <Text style={[typography.caption, styles.catatan]}>
                Saldo kedua dombak berubah setelah transfer dikonfirmasi.
            </Text>
        </Halaman>
    );
}

const styles = StyleSheet.create({ catatan: { textAlign: 'center' } });
