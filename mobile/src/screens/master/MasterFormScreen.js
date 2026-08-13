import { useCallback, useEffect, useMemo, useState } from 'react';
import { KeyboardAvoidingView, Platform, ScrollView, StyleSheet, Switch, Text, View } from 'react-native';
import { Halaman, Button, Card, Field, Loading, Notice } from '../../components/ui';
import SelectField from '../../components/SelectField';
import DateField from '../../components/DateField';
import TimeField from '../../components/TimeField';
import MultiSelectField from '../../components/MultiSelectField';
import { Kelola } from '../../api/endpoints';
import { REGISTRY, fieldUntuk } from '../../master/registry';
import { useOpsi } from '../../master/OpsiContext';
import { colors, spacing, typography } from '../../theme';

/**
 * Form tambah/ubah generik untuk seluruh resource master.
 * Bentuk field dibaca dari src/master/registry.js sehingga satu layar ini
 * melayani kesembilan resource.
 */
export default function MasterFormScreen({ route, navigation }) {
    const { resource, mode = 'buat', id } = route.params;
    const konf = REGISTRY[resource];
    const fields = useMemo(() => fieldUntuk(resource, mode), [resource, mode]);
    const { ambil } = useOpsi();

    const [nilai, setNilai] = useState(() => nilaiAwal(fields));
    const [memuat, setMemuat] = useState(mode === 'ubah');
    const [proses, setProses] = useState(false);
    const [error, setError] = useState(null);

    // Mode ubah: ambil detail lalu isi form dari kunci yang namanya sama
    useEffect(() => {
        if (mode !== 'ubah' || !id) return;
        let batal = false;

        (async () => {
            try {
                const data = await Kelola.detail(konf.endpoint, id);
                if (batal) return;
                const isi = { ...nilaiAwal(fields) };
                fields.forEach((f) => {
                    if (f.type === 'password') return; // tidak pernah diisi ulang
                    const v = data[f.name];
                    if (v === undefined || v === null) return;

                    if (f.type === 'switch') isi[f.name] = Boolean(v);
                    else if (f.type === 'multiselect') isi[f.name] = (Array.isArray(v) ? v : []).map(String);
                    else isi[f.name] = String(v);
                });
                setNilai(isi);
            } catch (e) {
                if (!batal) setError(e);
            } finally {
                if (!batal) setMemuat(false);
            }
        })();

        return () => { batal = true; };
    }, [mode, id, konf.endpoint, fields]);

    const ubah = useCallback((name, v) => setNilai((s) => ({ ...s, [name]: v })), []);

    const kirim = async () => {
        if (proses) return;
        setError(null);
        setProses(true);
        try {
            const payload = bangunPayload(fields, nilai);
            if (mode === 'ubah') {
                await Kelola.ubah(konf.endpoint, id, payload);
            } else {
                await Kelola.simpan(konf.endpoint, payload);
            }
            navigation.goBack();
        } catch (e) {
            setError(e);
        } finally {
            setProses(false);
        }
    };

    if (memuat) return <Halaman judul={`${mode === 'ubah' ? 'Ubah' : 'Tambah'} ${konf?.label || 'Data'}`} warna="ungu" keyboard><Loading /></Halaman>;

    return (
        <Halaman judul={`${mode === 'ubah' ? 'Ubah' : 'Tambah'} ${konf?.label || 'Data'}`} warna="ungu" keyboard>
                    {!!error && error.status !== 422 && <Notice tone="danger">{error.message}</Notice>}
                    {!!error && error.status === 422 && (
                        <Notice tone="danger">Periksa kembali isian yang ditandai merah.</Notice>
                    )}
                    {!!konf?.catatanForm && <Notice tone="warning">{konf.catatanForm}</Notice>}

                    <Card>
                        {fields.map((f) => {
                            const pesanError = error?.fieldError?.(f.name);

                            if (f.type === 'switch') {
                                return (
                                    <View key={f.name} style={styles.switchBaris}>
                                        <View style={styles.switchTeks}>
                                            <Text style={styles.switchLabel}>{f.label}</Text>
                                            {!!f.hint && <Text style={typography.caption}>{f.hint}</Text>}
                                        </View>
                                        <Switch
                                            value={Boolean(nilai[f.name])}
                                            onValueChange={(v) => ubah(f.name, v)}
                                            trackColor={{ true: colors.primary }}
                                            disabled={proses}
                                        />
                                    </View>
                                );
                            }

                            if (f.type === 'select') {
                                return (
                                    <SelectField
                                        key={f.name}
                                        label={f.label + (f.required ? ' *' : '')}
                                        value={nilai[f.name]}
                                        options={ambil(f.opsi)}
                                        onChange={(v) => ubah(f.name, v)}
                                        error={pesanError}
                                        hint={f.hint}
                                        disabled={proses}
                                    />
                                );
                            }

                            if (f.type === 'multiselect') {
                                return (
                                    <MultiSelectField
                                        key={f.name}
                                        label={f.label}
                                        value={nilai[f.name]}
                                        options={ambil(f.opsi)}
                                        onChange={(v) => ubah(f.name, v)}
                                        error={pesanError}
                                        hint={f.hint}
                                        disabled={proses}
                                    />
                                );
                            }

                            if (f.type === 'time') {
                                return (
                                    <TimeField
                                        key={f.name}
                                        label={f.label + (f.required ? ' *' : '')}
                                        value={nilai[f.name]}
                                        onChange={(v) => ubah(f.name, v)}
                                        error={pesanError}
                                        hint={f.hint}
                                        disabled={proses}
                                    />
                                );
                            }

                            if (f.type === 'date') {
                                return (
                                    <DateField
                                        key={f.name}
                                        label={f.label + (f.required ? ' *' : '')}
                                        value={nilai[f.name]}
                                        onChange={(v) => ubah(f.name, v)}
                                        error={pesanError}
                                        hint={f.hint}
                                        disabled={proses}
                                    />
                                );
                            }

                            return (
                                <Field
                                    key={f.name}
                                    label={f.label + (f.required ? ' *' : '')}
                                    value={nilai[f.name]}
                                    onChangeText={(v) => ubah(f.name, v)}
                                    error={pesanError}
                                    hint={f.hint}
                                    editable={!proses}
                                    multiline={f.type === 'textarea'}
                                    numberOfLines={f.type === 'textarea' ? 3 : 1}
                                    secureTextEntry={f.type === 'password'}
                                    maxLength={f.maxLength}
                                    autoCapitalize={f.autoCapitalize || (f.type === 'password' ? 'none' : 'sentences')}
                                    autoCorrect={false}
                                    keyboardType={
                                        f.keyboard
                                        || (f.type === 'number' || f.type === 'uang' ? 'numeric' : 'default')
                                    }
                                />
                            );
                        })}
                    </Card>

                    <Button
                        label={mode === 'ubah' ? 'Simpan Perubahan' : 'Simpan'}
                        onPress={kirim}
                        loading={proses}
                    />
                </Halaman>
    );
}

function nilaiAwal(fields) {
    return Object.fromEntries(
        fields.map((f) => {
            if (f.type === 'switch') return [f.name, Boolean(f.default)];
            if (f.type === 'multiselect') return [f.name, f.default ?? []];
            return [f.name, f.default ?? ''];
        })
    );
}

/** Ubah nilai form (semuanya string/boolean) menjadi tipe yang diharapkan API. */
function bangunPayload(fields, nilai) {
    const payload = {};

    fields.forEach((f) => {
        const v = nilai[f.name];

        if (f.type === 'switch') {
            payload[f.name] = Boolean(v);
            return;
        }

        if (f.type === 'multiselect') {
            payload[f.name] = Array.isArray(v) ? v : [];
            return;
        }

        // password kosong saat mengubah = tidak diganti
        if (f.type === 'password' && !String(v || '').trim()) return;

        if (v === '' || v === undefined || v === null) {
            if (!f.required) payload[f.name] = null;
            else payload[f.name] = '';   // biarkan server yang menolak
            return;
        }

        if (f.type === 'number') {
            payload[f.name] = Number(String(v).replace(/[^\d-]/g, ''));
        } else if (f.type === 'uang') {
            payload[f.name] = Number(String(v).replace(/[^\d.-]/g, ''));
        } else {
            payload[f.name] = v;
        }
    });

    return payload;
}

const styles = StyleSheet.create({
    screen: { flex: 1, backgroundColor: colors.background },
    flex: { flex: 1 },
    isi: { padding: spacing.lg, gap: spacing.md, paddingBottom: spacing.xxl },
    switchBaris: {
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'space-between',
        gap: spacing.md,
        paddingVertical: spacing.xs,
    },
    switchTeks: { flex: 1, gap: 2 },
    switchLabel: { fontSize: 14, fontWeight: '600', color: colors.text },
});
