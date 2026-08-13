import { useCallback } from 'react';
import { useFocusEffect } from '@react-navigation/native';
import { Pressable, StyleSheet, Text, View } from 'react-native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { Card, ErrorState, Halaman, Loading, Notice, Row, Statistik } from '../../components/ui';
import { Procurement } from '../../api/endpoints';
import { useMuat } from '../../hooks/useMuat';
import { DAFTAR_TAHAP } from '../../procurement/tahap';
import { desimal6 } from '../../utils/format';
import { aksen, bayangan, radius, spacing, typography } from '../../theme';

export default function ProcurementHomeScreen({ navigation }) {
    const { data, memuat, menyegarkan, error, segarkan, muatUlang } = useMuat(
        useCallback(() => Procurement.ringkasan(), [])
    );

    useFocusEffect(useCallback(() => { muatUlang({ segarkan: true }); }, [muatUlang]));

    if (memuat && !data) {
        return <Halaman judul="Procurement" warna="oranye" kembali={false}><Loading /></Halaman>;
    }
    if (error && !data) {
        return (
            <Halaman judul="Procurement" warna="oranye" kembali={false}>
                <ErrorState pesan={error.message} onCoba={muatUlang} />
            </Halaman>
        );
    }

    const jumlahTahap = Object.fromEntries((data?.per_tahap || []).map((t) => [t.tahap, t.jumlah]));
    // nama tahap di tabel status_purchase vs kunci layar
    const petaJumlah = {
        perencanaan: jumlahTahap['Rencana'] ?? 0,
        pembelian: jumlahTahap['Pembelian'] ?? 0,
        pembayaran: jumlahTahap['Pembayaran'] ?? 0,
        ms2: jumlahTahap['MS2'] ?? 0,
        delivery: data?.delivery_berjalan ?? 0,
    };

    return (
        <Halaman
            judul="Procurement"
            subjudul={data?.spbu}
            warna="oranye"
            kembali={false}
            onRefresh={segarkan}
            refreshing={menyegarkan}
        >
            {!data?.tarif_pajak && (
                <Notice tone="warning">
                    Belum ada tarif pajak aktif. Perhitungan PPN/PPBKB/PPh tidak bisa dijalankan —
                    atur dulu di Kelola Data → Tarif Pajak.
                </Notice>
            )}

            <Text style={typography.heading}>Alur pengadaan</Text>

            <View style={styles.alur}>
                {DAFTAR_TAHAP.map((t, i) => {
                    const a = aksen[t.warna] || aksen.oranye;

                    return (
                        <Pressable
                            key={t.key}
                            style={({ pressed }) => [
                                styles.tahap,
                                { backgroundColor: a.lembut, borderColor: a.solid + '2e' },
                                pressed && styles.ditekan,
                            ]}
                            onPress={() => navigation.navigate('ProcurementList', { tahap: t.key })}
                        >
                            <View style={[styles.nomor, { backgroundColor: a.solid }]}>
                                <Text style={styles.nomorTeks}>{i + 1}</Text>
                            </View>

                            <View style={styles.tahapIsi}>
                                <Text style={[styles.tahapJudul, { color: a.teks }]}>{t.label}</Text>
                                <Text style={typography.caption}>{t.deskripsi}</Text>
                            </View>

                            <View style={[styles.jumlahBulat, { backgroundColor: a.solid }]}>
                                <Text style={styles.jumlahTeks}>{petaJumlah[t.key]}</Text>
                            </View>

                            <Ionicons name="chevron-forward" size={16} color={a.solid} />
                        </Pressable>
                    );
                })}
            </View>

            <Text style={typography.heading}>Perlu perhatian</Text>
            <View style={styles.statistik}>
                <Statistik
                    label="Rencana ditolak"
                    nilai={data?.perencanaan_ditolak ?? 0}
                    warna={data?.perencanaan_ditolak ? 'merah' : 'abu'}
                    ikon="close-circle-outline"
                />
                <Statistik
                    label="Bayar menunggu"
                    nilai={data?.pembayaran_menunggu ?? 0}
                    warna={data?.pembayaran_menunggu ? 'oranye' : 'abu'}
                    ikon="hourglass-outline"
                />
                <Statistik
                    label="Delivery jalan"
                    nilai={data?.delivery_berjalan ?? 0}
                    warna={data?.delivery_berjalan ? 'biru' : 'abu'}
                    ikon="bus-outline"
                />
            </View>

            {!!data?.tarif_pajak && (
                <Card gradien="teal">
                    <Text style={styles.judulTerang}>Tarif pajak berlaku</Text>
                    <Row terang label="PPN" value={desimal6(data.tarif_pajak.ppn) + ' %'} />
                    <Row terang label="PPBKB" value={desimal6(data.tarif_pajak.ppbkb) + ' %'} />
                    <Row terang label="PPh 22" value={desimal6(data.tarif_pajak.pph) + ' %'} />
                    <Text style={styles.catatanTerang}>
                        Semua perhitungan memakai presisi 6 angka di belakang koma.
                    </Text>
                </Card>
            )}
        </Halaman>
    );
}

const styles = StyleSheet.create({
    alur: { gap: spacing.sm },
    tahap: {
        flexDirection: 'row',
        alignItems: 'center',
        gap: spacing.md,
        borderRadius: radius.lg,
        borderWidth: 1,
        padding: spacing.md,
        ...bayangan.kartu,
    },
    ditekan: { opacity: 0.82 },
    nomor: { width: 30, height: 30, borderRadius: radius.pill, alignItems: 'center', justifyContent: 'center' },
    nomorTeks: { color: '#fff', fontWeight: '800', fontSize: 13 },
    tahapIsi: { flex: 1 },
    tahapJudul: { fontSize: 15, fontWeight: '800' },
    jumlahBulat: { minWidth: 28, height: 24, borderRadius: radius.pill, paddingHorizontal: 8, alignItems: 'center', justifyContent: 'center' },
    jumlahTeks: { color: '#fff', fontWeight: '800', fontSize: 12 },
    statistik: { flexDirection: 'row', gap: spacing.md },
    judulTerang: { fontSize: 16, fontWeight: '800', color: '#fff' },
    catatanTerang: { fontSize: 11, color: 'rgba(255,255,255,0.85)', marginTop: spacing.xs },
});
