import { useState } from 'react';
import { Pressable, StyleSheet, Text, View } from 'react-native';
import Svg, { Circle, G, Line, Path, Rect, Text as SvgText } from 'react-native-svg';
import { aksen, colors, radius, spacing, typography } from '../theme';

const PALET = ['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#06b6d4', '#a855f7', '#ec4899', '#84cc16'];

/** Format nilai sesuai jenis kolom yang dikirim server. */
export function formatNilai(nilai, format) {
    const n = Number(nilai || 0);
    if (format === 'uang') {
        if (Math.abs(n) >= 1e9) return 'Rp ' + (n / 1e9).toFixed(2) + ' M';
        if (Math.abs(n) >= 1e6) return 'Rp ' + (n / 1e6).toFixed(1) + ' jt';
        if (Math.abs(n) >= 1e3) return 'Rp ' + (n / 1e3).toFixed(0) + ' rb';
        return 'Rp ' + n.toLocaleString('id-ID', { maximumFractionDigits: 0 });
    }
    if (format === 'liter') {
        if (Math.abs(n) >= 1e6) return (n / 1e6).toFixed(2) + ' jt L';
        if (Math.abs(n) >= 1e3) return (n / 1e3).toFixed(1) + ' rb L';
        return n.toLocaleString('id-ID', { maximumFractionDigits: 2 }) + ' L';
    }
    return n.toLocaleString('id-ID');
}

/** Nilai penuh (tanpa singkatan) untuk tooltip. */
function nilaiPenuh(nilai, format) {
    const n = Number(nilai || 0);
    if (format === 'uang') return 'Rp ' + n.toLocaleString('id-ID', { maximumFractionDigits: 2 });
    if (format === 'liter') return n.toLocaleString('id-ID', { maximumFractionDigits: 6 }) + ' L';
    return n.toLocaleString('id-ID');
}

function Bingkai({ judul, kosong, children, keterangan }) {
    return (
        <View style={styles.bingkai}>
            <Text style={styles.judul}>{judul}</Text>
            {kosong
                ? <Text style={styles.kosong}>Belum ada data untuk ditampilkan.</Text>
                : children}
            {!!keterangan && <Text style={styles.keterangan}>{keterangan}</Text>}
        </View>
    );
}

/** Grafik batang horizontal — cocok untuk label panjang di layar sempit. */
export function GrafikBatang({ judul, data = [], format, warna = 'indigo' }) {
    const [pilih, setPilih] = useState(null);
    const nilai = data.map((d) => Math.abs(Number(d.nilai || 0)));
    const maks = Math.max(...nilai, 1);
    const semuaNol = nilai.every((n) => n === 0);
    const a = aksen[warna] || aksen.indigo;

    return (
        <Bingkai judul={judul} kosong={data.length === 0 || semuaNol}>
            <View style={styles.batangIsi}>
                {data.map((d, i) => {
                    const n = Number(d.nilai || 0);
                    const lebar = Math.max((Math.abs(n) / maks) * 100, n === 0 ? 0 : 2);
                    const negatif = n < 0;

                    return (
                        <Pressable key={`${d.label}-${i}`} onPress={() => setPilih(pilih === i ? null : i)}>
                            <View style={styles.batangBaris}>
                                <Text style={styles.batangLabel} numberOfLines={1}>{d.label}</Text>
                                <View style={styles.batangJalur}>
                                    <View style={[
                                        styles.batang,
                                        { width: `${lebar}%`, backgroundColor: negatif ? aksen.merah.solid : a.solid },
                                    ]} />
                                </View>
                                <Text style={styles.batangNilai} numberOfLines={1}>{formatNilai(n, format)}</Text>
                            </View>
                            {pilih === i && (
                                <Text style={styles.tooltip}>{d.label}: {nilaiPenuh(n, format)}</Text>
                            )}
                        </Pressable>
                    );
                })}
            </View>
        </Bingkai>
    );
}

/** Grafik garis 12 bulan dengan area di bawahnya. */
export function GrafikGaris({ judul, data = [], format, warna = 'indigo' }) {
    const [pilih, setPilih] = useState(null);
    const a = aksen[warna] || aksen.indigo;

    const nilai = data.map((d) => Number(d.nilai || 0));
    const semuaNol = nilai.every((n) => n === 0);
    const W = 320;
    const H = 130;
    const padX = 6;
    const maks = Math.max(...nilai, 1);
    const min = Math.min(...nilai, 0);
    const rentang = maks - min || 1;

    const x = (i) => padX + (i * (W - padX * 2)) / Math.max(data.length - 1, 1);
    const y = (v) => H - 18 - ((v - min) / rentang) * (H - 34);

    const garis = data.map((d, i) => `${i === 0 ? 'M' : 'L'}${x(i).toFixed(1)},${y(Number(d.nilai || 0)).toFixed(1)}`).join(' ');
    const area = data.length
        ? `${garis} L${x(data.length - 1).toFixed(1)},${H - 18} L${x(0).toFixed(1)},${H - 18} Z`
        : '';

    return (
        <Bingkai judul={judul} kosong={data.length === 0 || semuaNol}
            keterangan={pilih !== null ? `${data[pilih].label}: ${nilaiPenuh(data[pilih].nilai, format)}` : 'Ketuk titik untuk melihat nilai penuh.'}>
            <Svg width="100%" height={H} viewBox={`0 0 ${W} ${H}`}>
                <Line x1={padX} y1={H - 18} x2={W - padX} y2={H - 18} stroke={colors.border} strokeWidth="1" />
                <Path d={area} fill={a.solid} fillOpacity="0.12" />
                <Path d={garis} stroke={a.solid} strokeWidth="2.5" fill="none" strokeLinejoin="round" />
                {data.map((d, i) => (
                    <G key={i}>
                        <Circle
                            cx={x(i)} cy={y(Number(d.nilai || 0))}
                            r={pilih === i ? 5.5 : 3}
                            fill={pilih === i ? a.solid : '#fff'}
                            stroke={a.solid} strokeWidth="2"
                            onPress={() => setPilih(pilih === i ? null : i)}
                        />
                        {i % 2 === 0 && (
                            <SvgText x={x(i)} y={H - 5} fontSize="8" fill={colors.textMuted} textAnchor="middle">
                                {d.label}
                            </SvgText>
                        )}
                    </G>
                ))}
            </Svg>
        </Bingkai>
    );
}

/** Grafik donat + legenda dengan persentase. */
export function GrafikDonat({ judul, data = [], format }) {
    const nilai = data.map((d) => Math.abs(Number(d.nilai || 0)));
    const total = nilai.reduce((a, b) => a + b, 0);

    const R = 52;
    const tebal = 20;
    const keliling = 2 * Math.PI * R;
    let mulai = 0;

    return (
        <Bingkai judul={judul} kosong={data.length === 0 || total === 0}>
            <View style={styles.donatBaris}>
                <Svg width={132} height={132} viewBox="0 0 132 132">
                    <G rotation="-90" origin="66, 66">
                        {data.map((d, i) => {
                            const porsi = Math.abs(Number(d.nilai || 0)) / (total || 1);
                            const panjang = porsi * keliling;
                            const el = (
                                <Circle
                                    key={i} cx="66" cy="66" r={R}
                                    stroke={PALET[i % PALET.length]}
                                    strokeWidth={tebal}
                                    fill="none"
                                    strokeDasharray={`${panjang} ${keliling - panjang}`}
                                    strokeDashoffset={-mulai}
                                />
                            );
                            mulai += panjang;
                            return el;
                        })}
                    </G>
                </Svg>

                <View style={styles.legenda}>
                    {data.map((d, i) => {
                        const porsi = total ? (Math.abs(Number(d.nilai || 0)) / total) * 100 : 0;
                        return (
                            <View key={i} style={styles.legendaBaris}>
                                <View style={[styles.titik, { backgroundColor: PALET[i % PALET.length] }]} />
                                <Text style={styles.legendaLabel} numberOfLines={1}>{d.label}</Text>
                                <Text style={styles.legendaNilai}>{porsi.toFixed(1)}%</Text>
                            </View>
                        );
                    })}
                </View>
            </View>
        </Bingkai>
    );
}

/** Pemilih otomatis berdasarkan `jenis` dari server. */
export function Grafik({ jenis, ...props }) {
    if (jenis === 'garis') return <GrafikGaris {...props} />;
    if (jenis === 'donat') return <GrafikDonat {...props} />;
    return <GrafikBatang {...props} />;
}

const styles = StyleSheet.create({
    bingkai: { gap: spacing.sm },
    judul: { fontSize: 13, fontWeight: '800', color: colors.text },
    kosong: { ...typography.caption, paddingVertical: spacing.lg, textAlign: 'center' },
    keterangan: { ...typography.caption, textAlign: 'center' },

    batangIsi: { gap: spacing.sm },
    batangBaris: { flexDirection: 'row', alignItems: 'center', gap: spacing.sm },
    batangLabel: { width: 86, fontSize: 11, color: colors.textMuted, fontWeight: '600' },
    batangJalur: { flex: 1, height: 14, backgroundColor: colors.border + '66', borderRadius: radius.pill, overflow: 'hidden' },
    batang: { height: 14, borderRadius: radius.pill },
    batangNilai: { width: 78, fontSize: 11, fontWeight: '800', color: colors.text, textAlign: 'right' },
    tooltip: { ...typography.caption, textAlign: 'right', marginTop: 2 },

    donatBaris: { flexDirection: 'row', alignItems: 'center', gap: spacing.md },
    legenda: { flex: 1, gap: 6 },
    legendaBaris: { flexDirection: 'row', alignItems: 'center', gap: spacing.sm },
    titik: { width: 10, height: 10, borderRadius: 5 },
    legendaLabel: { flex: 1, fontSize: 12, color: colors.text },
    legendaNilai: { fontSize: 12, fontWeight: '800', color: colors.textMuted },
});
