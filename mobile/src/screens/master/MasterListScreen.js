import { useCallback, useLayoutEffect, useState } from 'react';
import { Pressable, StyleSheet } from 'react-native';
import { Halaman } from '../../components/ui';
import DaftarData from '../../components/DaftarData';
import { Fab } from '../persediaan/bagian';
import SelectField from '../../components/SelectField';
import { Kelola } from '../../api/endpoints';
import { REGISTRY } from '../../master/registry';
import { useOpsi } from '../../master/OpsiContext';

export default function MasterListScreen({ route, navigation }) {
    const { resource } = route.params;
    const konf = REGISTRY[resource];

    useLayoutEffect(() => {
        navigation.setOptions({ title: konf?.label || 'Master' });
    }, [navigation, konf]);

    const { ambil: opsi } = useOpsi();
    const [spbuId, setSpbuId] = useState('');

    // sebagian resource punya kolom spbu_id; filternya hanya ditampilkan di situ
    const punyaSpbu = (konf?.fields || []).some((f) => f.name === 'spbu_id');

    const ambil = useCallback(
        (page, cari, perPage) => Kelola.daftar(konf.endpoint, {
            page, cari, per_page: perPage, spbu_id: spbuId || undefined,
        }),
        [konf.endpoint, spbuId]
    );

    return (
        <Halaman judul={konf?.label || 'Data'} subjudul={konf?.deskripsi} warna="indigo" scroll={false}>
            <DaftarData
                key={spbuId}
                ambil={ambil}
                jumlahFilterAktif={spbuId ? 1 : 0}
                filter={punyaSpbu ? (
                    <SelectField label="SPBU" value={spbuId} onChange={setSpbuId}
                        options={[{ value: '', label: 'Semua SPBU' }, ...opsi('spbu')]} />
                ) : null}
                cariPlaceholder={`Cari ${konf?.label?.toLowerCase() || 'data'}…`}
                kosong={{ judul: `Belum ada ${konf?.label?.toLowerCase()}`, pesan: 'Tekan + untuk menambah data.' }}
                onTekan={(item) => navigation.navigate('MasterDetail', { resource, id: item.id })}
            />
            <Fab onPress={() => navigation.navigate('MasterForm', { resource, mode: 'buat' })} />
        </Halaman>
    );
}

const styles = StyleSheet.create({});
