import { useCallback, useEffect, useState } from 'react';
import { Alert, Image, Pressable, StyleSheet, Text, View } from 'react-native';
import * as ImagePicker from 'expo-image-picker';
import Ionicons from '@expo/vector-icons/Ionicons';
import { Badge, Button, Card, Field, Halaman, Loading, Notice, Row, SectionTitle } from '../../components/ui';
import SelectField from '../../components/SelectField';
import DateField from '../../components/DateField';
import { Setoran } from '../../api/endpoints';
import { useOpsi } from '../../master/OpsiContext';
import { rupiah } from '../../utils/format';
import { colors, radius, spacing, typography } from '../../theme';

const METODE = [
    { value: 'Setor Tunai', label: 'Setor Tunai' },
    { value: 'Transfer', label: 'Transfer' },
    { value: 'Cash Deposit Machine', label: 'Cash Deposit Machine' },
    { value: 'Pick Up Service', label: 'Pick Up Service' },
];

/** Tambah / ubah satu catatan setoran ke bank, beserta bukti setornya. */
export default function PenarikanFormScreen({ route, navigation }) {
    const { penjualanId, bankId } = route.params;
    const ubah = !!bankId;
    const { ambil } = useOpsi();

    const [form, setForm] = useState({
        tanggal: new Date().toISOString().slice(0, 10),
        member_id: '', nama_bank: '', no_referensi: '', metode_transfer: '', total_uang: '',
    });
    const [petugas, setPetugas] = useState([]);
    const [data, setData] = useState(null);
    const [memuat, setMemuat] = useState(ubah);
    const [proses, setProses] = useState(false);
    const [pesan, setPesan] = useState(null);
    const [error, setError] = useState(null);

    // daftar petugas diambil dari seluruh jabatan yang ada
    useEffect(() => {
        const jabatan = ambil('jabatan');
        Promise.all(jabatan.map((j) => Setoran.petugas(j.value).catch(() => ({ items: [] }))))
            .then((hasil) => {
                const gabung = new Map();
                hasil.forEach((h) => (h.items || []).forEach((i) => gabung.set(i.value, i)));
                setPetugas([...gabung.values()].sort((a, b) => a.label.localeCompare(b.label)));
            });
    }, [ambil]);

    const muat = useCallback(async () => {
        if (!ubah) return;
        try {
            const d = await Setoran.bank.detail(penjualanId, bankId);
            setData(d);
            setForm({
                tanggal: d.tanggal, member_id: d.member_id ?? '', nama_bank: d.nama_bank,
                no_referensi: d.no_referensi, metode_transfer: d.metode_transfer,
                total_uang: String(d.total_uang),
            });
        } catch (e) {
            setError(e);
        } finally {
            setMemuat(false);
        }
    }, [ubah, penjualanId, bankId]);

    useEffect(() => { muat(); }, [muat]);

    const set = (k, v) => setForm((f) => ({ ...f, [k]: v }));

    const simpan = async () => {
        if (proses) return;
        setError(null); setProses(true);
        try {
            if (ubah) await Setoran.bank.ubah(penjualanId, bankId, form);
            else await Setoran.bank.simpan(penjualanId, form);
            navigation.goBack();
        } catch (e) {
            setError(e);
        } finally {
            setProses(false);
        }
    };

    const jalankan = async (fn, sukses) => {
        setProses(true); setPesan(null);
        try { await fn(); setPesan({ tone: 'success', teks: sukses }); muat(); }
        catch (e) { setPesan({ tone: 'danger', teks: e.message }); }
        finally { setProses(false); }
    };

    const unggah = async (dariKamera) => {
        const izin = dariKamera
            ? await ImagePicker.requestCameraPermissionsAsync()
            : await ImagePicker.requestMediaLibraryPermissionsAsync();
        if (!izin.granted) { setPesan({ tone: 'danger', teks: 'Izin ditolak.' }); return; }

        const opsi = { mediaTypes: ['images'], quality: 0.6 };
        const hasil = dariKamera
            ? await ImagePicker.launchCameraAsync(opsi)
            : await ImagePicker.launchImageLibraryAsync(opsi);
        if (hasil.canceled) return;

        jalankan(
            () => Setoran.bank.unggah(penjualanId, bankId, hasil.assets[0], 'Bukti setor'),
            'Bukti setor diunggah.'
        );
    };

    if (memuat) return <Halaman judul="Setoran ke Bank" warna="hijau"><Loading /></Halaman>;

    const terkunci = data?.is_accepted;
    const siap = Object.values(form).every((v) => String(v).trim() !== '');

    return (
        <Halaman judul={ubah ? 'Ubah Setoran Bank' : 'Setor ke Bank'} warna="hijau" keyboard>
            {!!error && (
                <Notice tone="danger">
                    {error.fieldError?.('total_uang') || error.fieldError?.('no_referensi') || error.message}
                </Notice>
            )}
            {!!pesan && <Notice tone={pesan.tone}>{pesan.teks}</Notice>}
            {terkunci && <Notice tone="warning">Setoran sudah dikonfirmasi — cabut konfirmasi dulu untuk mengubah.</Notice>}

            <Card>
                <DateField label="Tanggal setor *" value={form.tanggal} onChange={(v) => set('tanggal', v)} disabled={proses || terkunci} />
                <SelectField label="Petugas penyetor *" value={form.member_id} onChange={(v) => set('member_id', v)}
                    options={petugas} disabled={proses || terkunci} />
                <Field label="Nama bank *" value={form.nama_bank} onChangeText={(v) => set('nama_bank', v)}
                    maxLength={16} editable={!proses && !terkunci} placeholder="mis. BRI" />
                <Field label="No. referensi *" value={form.no_referensi} onChangeText={(v) => set('no_referensi', v)}
                    maxLength={16} editable={!proses && !terkunci} />
                <SelectField label="Metode transfer *" value={form.metode_transfer}
                    onChange={(v) => set('metode_transfer', v)} options={METODE} disabled={proses || terkunci} />
                <Field label="Total uang *" value={String(form.total_uang)} onChangeText={(v) => set('total_uang', v)}
                    keyboardType="numeric" editable={!proses && !terkunci}
                    hint="Boleh sampai 6 angka di belakang koma." />
                {form.total_uang !== '' && <Row label="Pratinjau" value={rupiah(form.total_uang)} />}
            </Card>

            {!terkunci && (
                <Button label={ubah ? 'Simpan Perubahan' : 'Simpan Setoran'} variant="success"
                    disabled={!siap} loading={proses} onPress={simpan} />
            )}

            {ubah && !!data && (
                <>
                    <SectionTitle>Bukti setor ({data.lampiran.length})</SectionTitle>
                    <View style={styles.tombolBaris}>
                        <Button label="Kamera" icon="📷" style={styles.tombol} onPress={() => unggah(true)} disabled={proses} />
                        <Button label="Galeri" icon="🖼️" variant="outline" style={styles.tombol} onPress={() => unggah(false)} disabled={proses} />
                    </View>

                    {data.lampiran.map((l) => (
                        <Card key={l.id}>
                            <View style={styles.baris}>
                                {l.is_gambar
                                    ? <Image source={{ uri: l.url }} style={styles.thumb} />
                                    : <View style={[styles.thumb, styles.berkas]}><Ionicons name="document-text-outline" size={24} color={colors.textMuted} /></View>}
                                <View style={styles.isi}>
                                    <Text style={typography.body} numberOfLines={2}>{l.nama_dokumen}</Text>
                                    <Text style={typography.caption}>{l.tanggal}</Text>
                                </View>
                                <Pressable hitSlop={10} disabled={proses}
                                    onPress={() => Alert.alert('Hapus lampiran?', l.nama_dokumen, [
                                        { text: 'Batal', style: 'cancel' },
                                        { text: 'Hapus', style: 'destructive', onPress: () => jalankan(
                                            () => Setoran.bank.hapusLampiran(penjualanId, bankId, l.id), 'Lampiran dihapus.') },
                                    ])}>
                                    <Ionicons name="trash-outline" size={20} color={colors.danger} />
                                </Pressable>
                            </View>
                        </Card>
                    ))}

                    <SectionTitle>Konfirmasi</SectionTitle>
                    <Card tone={data.is_accepted ? 'hijau' : 'oranye'}>
                        <View style={styles.baris}>
                            <Text style={typography.body}>Status</Text>
                            <Badge label={data.is_accepted ? 'Dikonfirmasi' : 'Menunggu'}
                                tone={data.is_accepted ? 'hijau' : 'oranye'} />
                        </View>
                        <Text style={typography.caption}>
                            Konfirmasi memposting jurnal Bank (debit) / Kas Operator (kredit).
                        </Text>
                        <Button
                            label={data.is_accepted ? 'Cabut Konfirmasi' : 'Konfirmasi Setoran'}
                            variant={data.is_accepted ? 'ghost' : 'success'}
                            disabled={proses}
                            onPress={() => jalankan(
                                () => Setoran.bank.konfirmasi(penjualanId, bankId, !data.is_accepted),
                                data.is_accepted ? 'Konfirmasi dicabut.' : 'Setoran dikonfirmasi.'
                            )}
                        />
                    </Card>

                    {!data.is_accepted && (
                        <Button label="Hapus Setoran Bank" variant="danger" disabled={proses}
                            onPress={() => Alert.alert('Hapus setoran bank?', 'Lampirannya ikut terhapus.', [
                                { text: 'Batal', style: 'cancel' },
                                { text: 'Hapus', style: 'destructive', onPress: async () => {
                                    setProses(true);
                                    try { await Setoran.bank.hapus(penjualanId, bankId); navigation.goBack(); }
                                    catch (e) { setPesan({ tone: 'danger', teks: e.message }); setProses(false); }
                                } }])} />
                    )}
                </>
            )}
        </Halaman>
    );
}

const styles = StyleSheet.create({
    tombolBaris: { flexDirection: 'row', gap: spacing.md },
    tombol: { flex: 1 },
    baris: { flexDirection: 'row', alignItems: 'center', gap: spacing.md, justifyContent: 'space-between' },
    isi: { flex: 1, gap: 2 },
    thumb: { width: 56, height: 56, borderRadius: radius.md, backgroundColor: colors.border },
    berkas: { alignItems: 'center', justifyContent: 'center' },
});
