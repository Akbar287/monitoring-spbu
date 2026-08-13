import { useEffect, useState } from 'react';
import { StyleSheet, Text } from 'react-native';
import { Button, Card, Field, Halaman, Loading, Notice, Row } from '../../components/ui';
import SelectField from '../../components/SelectField';
import DateField from '../../components/DateField';
import { Persediaan, Totalisator } from '../../api/endpoints';
import { useOpsi } from '../../master/OpsiContext';
import { liter } from '../../utils/format';
import { typography } from '../../theme';

/**
 * Buka shift totalisator.
 *
 * Alurnya dibuat berurutan supaya cepat di lapangan: pilih dombak → nozzle →
 * sisanya (tanggal, shift, angka awal) terisi otomatis dari shift sebelumnya.
 * Angka awal dikunci bila sudah terbawa, agar tidak salah ketik.
 */
export default function TotalisatorFormScreen({ navigation }) {
    const { ambil: ambilOpsi } = useOpsi();

    const [dombakList, setDombakList] = useState(null);
    const [nozzleList, setNozzleList] = useState([]);
    const [dombakId, setDombakId] = useState('');
    const [nozzleId, setNozzleId] = useState('');
    const [saran, setSaran] = useState(null);
    const [form, setForm] = useState({ tanggal: '', jam_kerja_id: '', stand_meter_awal: '' });
    const [proses, setProses] = useState(false);
    const [error, setError] = useState(null);

    useEffect(() => {
        Persediaan.dombak().then((d) => setDombakList(d.items)).catch(() => setDombakList([]));
    }, []);

    const pilihDombak = async (id) => {
        setDombakId(id);
        setNozzleId('');
        setSaran(null);
        setForm({ tanggal: '', jam_kerja_id: '', stand_meter_awal: '' });
        try {
            const d = await Totalisator.nozzle(id);
            setNozzleList(d.items);
        } catch {
            setNozzleList([]);
        }
    };

    const pilihNozzle = async (id) => {
        setNozzleId(id);
        setError(null);
        try {
            const s = await Totalisator.saran(id);
            setSaran(s);
            setForm({
                tanggal: s.tanggal,
                jam_kerja_id: s.jam_kerja_id ?? '',
                stand_meter_awal: s.stand_meter_awal,
            });
        } catch (e) {
            setError(e);
        }
    };

    const set = (k, v) => setForm((f) => ({ ...f, [k]: v }));

    const kirim = async () => {
        if (proses) return;
        setError(null);
        setProses(true);
        try {
            const hasil = await Totalisator.simpan({
                nozzle_id: nozzleId,
                dombak_id: dombakId,
                jam_kerja_id: form.jam_kerja_id,
                tanggal: form.tanggal,
                stand_meter_awal: form.stand_meter_awal,
            });
            navigation.replace('TotalisatorDetail', { id: hasil.id });
        } catch (e) {
            setError(e);
        } finally {
            setProses(false);
        }
    };

    if (!dombakList) return <Halaman judul="Totalisator" warna="pink"><Loading /></Halaman>;

    const siap = dombakId && nozzleId && form.tanggal && form.jam_kerja_id && form.stand_meter_awal !== '';

    return (
        <Halaman judul="Buka Shift Totalisator" warna="pink" keyboard>
            {!!error && (
                <Notice tone="danger">
                    {error.fieldError?.('stand_meter_awal') || error.fieldError?.('tanggal') || error.message}
                </Notice>
            )}

            <Card>
                <SelectField label="Dombak *" value={dombakId} onChange={pilihDombak}
                    options={dombakList} disabled={proses} />
                <SelectField label="Nozzle *" value={nozzleId} onChange={pilihNozzle}
                    options={nozzleList} disabled={proses || !dombakId}
                    hint={dombakId ? `${nozzleList.length} nozzle melayani dombak ini.` : 'Pilih dombak lebih dulu.'} />
            </Card>

            {!!saran && (
                <>
                    <Notice tone={saran.awal_terkunci ? 'success' : 'warning'}>{saran.catatan}</Notice>

                    {!!saran.sebelumnya && (
                        <Card tone="abu">
                            <Text style={typography.heading}>Shift sebelumnya</Text>
                            <Row label="Tanggal" value={saran.sebelumnya.tanggal} />
                            <Row label="Shift" value={saran.sebelumnya.jam_kerja} />
                            <Row label="Angka akhir" value={saran.sebelumnya.stand_meter_akhir} />
                        </Card>
                    )}

                    <Card tone="pink">
                        <DateField label="Tanggal *" value={form.tanggal}
                            onChange={(v) => set('tanggal', v)} disabled={proses} />
                        <SelectField label="Shift *" value={form.jam_kerja_id}
                            onChange={(v) => set('jam_kerja_id', v)} options={ambilOpsi('jam_kerja')} disabled={proses} />
                        <Field
                            label="Angka meter awal *"
                            value={String(form.stand_meter_awal)}
                            onChangeText={(v) => set('stand_meter_awal', v)}
                            keyboardType="numeric"
                            editable={!proses && !saran.awal_terkunci}
                            hint={saran.awal_terkunci
                                ? 'Terkunci — nilainya harus sama dengan angka akhir shift sebelumnya.'
                                : 'Isi manual, boleh sampai 6 angka di belakang koma.'}
                        />
                    </Card>
                </>
            )}

            <Button label="Buka Shift" warna="pink" disabled={!siap} loading={proses} onPress={kirim} />
            <Text style={[typography.caption, styles.catatan]}>
                Angka akhir diisi belakangan lewat halaman detail, saat shift ditutup.
            </Text>
        </Halaman>
    );
}

const styles = StyleSheet.create({ catatan: { textAlign: 'center' } });
