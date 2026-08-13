import { useCallback, useState } from 'react';
import { Alert, Pressable, ScrollView, StyleSheet, Text, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { Halaman, Button, Card, ErrorState, Loading, Notice } from '../../components/ui';
import SelectField from '../../components/SelectField';
import { Kelola } from '../../api/endpoints';
import { useMuat } from '../../hooks/useMuat';
import { useOpsi } from '../../master/OpsiContext';
import { colors, radius, spacing, typography } from '../../theme';

/**
 * Kelola relasi many-to-many pegawai: jabatan (menentukan level/hak akses)
 * dan penempatan SPBU (dipakai dashboard & posting jurnal gaji).
 */
export default function MemberRelasiScreen({ route, navigation }) {
    const { id, nama } = route.params;
    const { ambil } = useOpsi();

    const [jabatanBaru, setJabatanBaru] = useState('');
    const [spbuBaru, setSpbuBaru] = useState('');
    const [proses, setProses] = useState(false);
    const [pesan, setPesan] = useState(null);

    const { data, setData, memuat, error, muatUlang } = useMuat(
        useCallback(() => Kelola.detail('master/member', id), [id])
    );

    const jalankan = async (fn, sukses) => {
        setProses(true);
        setPesan(null);
        try {
            setData(await fn());
            setPesan({ tone: 'success', teks: sukses });
        } catch (e) {
            setPesan({ tone: 'danger', teks: e.message });
        } finally {
            setProses(false);
        }
    };

    if (memuat && !data) return <Halaman judul="Jabatan & SPBU" subjudul={nama} warna="ungu"><Loading /></Halaman>;
    if (error && !data) {
        return <Halaman judul="Jabatan & SPBU" subjudul={nama} warna="ungu"><ErrorState pesan={error.message} onCoba={muatUlang} /></Halaman>;
    }

    const konfirmasiLepas = (label, aksi) =>
        Alert.alert('Lepas relasi?', `${label} akan dilepas dari pegawai ini.`, [
            { text: 'Batal', style: 'cancel' },
            { text: 'Lepas', style: 'destructive', onPress: aksi },
        ]);

    return (
        <Halaman judul="Jabatan & SPBU" subjudul={nama} warna="ungu">
                {!!pesan && <Notice tone={pesan.tone}>{pesan.teks}</Notice>}

                <Card>
                    <Text style={typography.heading}>Jabatan</Text>
                    <Text style={typography.caption}>
                        Jabatan menentukan level akses pegawai di web maupun aplikasi.
                    </Text>

                    {(data.daftar_jabatan || []).length === 0 ? (
                        <Text style={typography.muted}>Belum ada jabatan.</Text>
                    ) : (
                        data.daftar_jabatan.map((j) => (
                            <View key={j.jabatan_id} style={styles.chipBaris}>
                                <View style={styles.chipTeks}>
                                    <Text style={typography.body}>{j.nama_jabatan}</Text>
                                    {!!j.level && <Text style={typography.caption}>Level: {j.level}</Text>}
                                </View>
                                <Pressable
                                    hitSlop={10}
                                    disabled={proses}
                                    onPress={() => konfirmasiLepas(j.nama_jabatan, () =>
                                        jalankan(() => Kelola.member.hapusJabatan(id, j.jabatan_id), 'Jabatan dilepas.')
                                    )}
                                >
                                    <Ionicons name="close-circle" size={20} color={colors.danger} />
                                </Pressable>
                            </View>
                        ))
                    )}

                    <SelectField
                        label="Tambah jabatan"
                        value={jabatanBaru}
                        options={ambil('jabatan')}
                        onChange={setJabatanBaru}
                        disabled={proses}
                    />
                    <Button
                        label="Tambahkan Jabatan"
                        variant="outline"
                        disabled={!jabatanBaru || proses}
                        onPress={() => jalankan(
                            () => Kelola.member.tambahJabatan(id, jabatanBaru),
                            'Jabatan ditambahkan.'
                        ).then(() => setJabatanBaru(''))}
                    />
                </Card>

                <Card>
                    <Text style={typography.heading}>Penempatan SPBU</Text>
                    <Text style={typography.caption}>
                        Menentukan cakupan data pada beranda dan referensi stok/harga.
                    </Text>

                    {(data.daftar_spbu || []).length === 0 ? (
                        <Text style={typography.muted}>Belum ada penempatan.</Text>
                    ) : (
                        data.daftar_spbu.map((s) => (
                            <View key={s.spbu_id} style={styles.chipBaris}>
                                <Text style={[typography.body, styles.chipTeks]}>{s.nama_spbu}</Text>
                                <Pressable
                                    hitSlop={10}
                                    disabled={proses}
                                    onPress={() => konfirmasiLepas(s.nama_spbu, () =>
                                        jalankan(() => Kelola.member.hapusSpbu(id, s.spbu_id), 'Penempatan dilepas.')
                                    )}
                                >
                                    <Ionicons name="close-circle" size={20} color={colors.danger} />
                                </Pressable>
                            </View>
                        ))
                    )}

                    <SelectField
                        label="Tambah penempatan"
                        value={spbuBaru}
                        options={ambil('spbu')}
                        onChange={setSpbuBaru}
                        disabled={proses}
                    />
                    <Button
                        label="Tambahkan SPBU"
                        variant="outline"
                        disabled={!spbuBaru || proses}
                        onPress={() => jalankan(
                            () => Kelola.member.tambahSpbu(id, spbuBaru),
                            'Penempatan ditambahkan.'
                        ).then(() => setSpbuBaru(''))}
                    />
                </Card>
            </Halaman>
    );
}

const styles = StyleSheet.create({
    screen: { flex: 1, backgroundColor: colors.background },
    isi: { padding: spacing.lg, gap: spacing.md, paddingBottom: spacing.xxl },
    chipBaris: {
        flexDirection: 'row',
        alignItems: 'center',
        gap: spacing.md,
        padding: spacing.md,
        borderRadius: radius.md,
        borderWidth: 1,
        borderColor: colors.border,
        backgroundColor: colors.background,
    },
    chipTeks: { flex: 1 },
});
