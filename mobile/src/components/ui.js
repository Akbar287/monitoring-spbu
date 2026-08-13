import {
    ActivityIndicator,
    KeyboardAvoidingView,
    Platform,
    Pressable,
    RefreshControl,
    ScrollView,
    StatusBar,
    StyleSheet,
    Text,
    TextInput,
    View,
} from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { LinearGradient } from 'expo-linear-gradient';
import { useNavigation } from '@react-navigation/native';
import Ionicons from '@expo/vector-icons/Ionicons';
import { aksen, bayangan, colors, radius, spacing, typography } from '../theme';

const warnaAksen = (nama) => aksen[nama] || aksen.indigo;

/**
 * Kerangka halaman: pita gradien berwarna di atas, lalu konten yang "naik"
 * menutupi sebagian gradien.
 *
 * Aplikasi ini sengaja TIDAK memakai AppBar bawaan React Navigation
 * (`headerShown: false` di seluruh stack). Judul dan tombol kembali menjadi
 * bagian dari halaman ini supaya bisa diberi warna per modul.
 */
export function Halaman({
    judul,
    subjudul,
    warna = 'indigo',
    kembali,
    aksi,
    children,
    scroll = true,
    onRefresh,
    refreshing = false,
    padded = true,
    tinggiPita = 'sedang',
    keyboard = false,
}) {
    const navigation = useNavigation();
    const a = warnaAksen(warna);

    // tombol kembali muncul otomatis bila layar ini bisa kembali
    const bisaKembali = kembali ?? navigation.canGoBack?.() ?? false;
    const isi = padded ? <View style={styles.pad}>{children}</View> : children;

    const tinggi = { pendek: 96, sedang: 132, tinggi: 168 }[tinggiPita] ?? 132;

    return (
        <View style={styles.akar}>
            <StatusBar barStyle="light-content" backgroundColor="transparent" translucent />

            <LinearGradient
                colors={a.gradien}
                start={{ x: 0, y: 0 }}
                end={{ x: 1, y: 1 }}
                style={[styles.pita, { height: tinggi }]}
            />

            <SafeAreaView style={styles.aman} edges={['top']}>
                {(!!judul || bisaKembali || !!aksi) && (
                    <View style={styles.kepala}>
                        {bisaKembali && (
                            <Pressable
                                onPress={() => navigation.goBack()}
                                hitSlop={12}
                                style={({ pressed }) => [styles.tombolKembali, pressed && styles.ditekan]}
                            >
                                <Ionicons name="chevron-back" size={22} color="#fff" />
                            </Pressable>
                        )}

                        <View style={styles.kepalaTeks}>
                            {!!judul && <Text style={styles.judul} numberOfLines={1}>{judul}</Text>}
                            {!!subjudul && <Text style={styles.subjudul} numberOfLines={1}>{subjudul}</Text>}
                        </View>

                        {!!aksi && <View style={styles.kepalaAksi}>{aksi}</View>}
                    </View>
                )}

                {scroll ? (
                    <Pembungkus keyboard={keyboard}>
                    <ScrollView
                        style={styles.lembar}
                        contentContainerStyle={styles.lembarIsi}
                        keyboardShouldPersistTaps="handled"
                        showsVerticalScrollIndicator={false}
                        refreshControl={
                            onRefresh
                                ? <RefreshControl refreshing={refreshing} onRefresh={onRefresh} colors={[a.solid]} tintColor={a.solid} />
                                : undefined
                        }
                    >
                        {isi}
                    </ScrollView>
                    </Pembungkus>
                ) : (
                    <View style={[styles.lembar, styles.lembarKaku]}>{isi}</View>
                )}
            </SafeAreaView>
        </View>
    );
}

/** Bungkus konten agar input tidak tertutup papan ketik pada layar form. */
function Pembungkus({ keyboard, children }) {
    if (!keyboard) return children;

    return (
        <KeyboardAvoidingView
            style={{ flex: 1 }}
            behavior={Platform.OS === 'ios' ? 'padding' : undefined}
            keyboardVerticalOffset={Platform.OS === 'ios' ? 8 : 0}
        >
            {children}
        </KeyboardAvoidingView>
    );
}

/** Alias lama — layar tab memakai ini sebelum header masuk ke halaman. */
export function Screen(props) {
    return <Halaman {...props} />;
}

/**
 * Kartu. `tone` memberi warna lembut + pita aksen di sisi kiri;
 * `gradien` membuat kartu penuh warna dengan teks putih.
 */
export function Card({ children, style, tone, gradien }) {
    if (gradien) {
        const a = warnaAksen(gradien);

        return (
            <LinearGradient
                colors={a.gradien}
                start={{ x: 0, y: 0 }}
                end={{ x: 1, y: 1 }}
                style={[styles.card, styles.cardGradien, style]}
            >
                {children}
            </LinearGradient>
        );
    }

    if (tone) {
        const a = warnaAksen(tone);

        return (
            <View style={[styles.card, { backgroundColor: a.lembut, borderColor: a.solid + '33' }, style]}>
                <View style={[styles.pitaAksen, { backgroundColor: a.solid }]} />
                {children}
            </View>
        );
    }

    return <View style={[styles.card, style]}>{children}</View>;
}

export function SectionTitle({ children, aksi }) {
    return (
        <View style={styles.sectionTitle}>
            <Text style={typography.heading}>{children}</Text>
            {aksi}
        </View>
    );
}

export function Button({ label, onPress, variant = 'primary', disabled, loading, icon, style, warna = 'indigo' }) {
    const nonaktif = disabled || loading;
    const a = warnaAksen(warna);

    if (variant === 'primary' && !nonaktif) {
        return (
            <Pressable onPress={onPress} style={({ pressed }) => [style, pressed && styles.ditekan]}>
                <LinearGradient
                    colors={a.gradien}
                    start={{ x: 0, y: 0 }}
                    end={{ x: 1, y: 0 }}
                    style={[styles.button, styles.buttonGradien]}
                >
                    <Text style={[styles.buttonLabel, { color: '#fff' }]}>
                        {icon ? `${icon}  ` : ''}{label}
                    </Text>
                </LinearGradient>
            </Pressable>
        );
    }

    const v = VARIAN(a)[variant] || VARIAN(a).primary;

    return (
        <Pressable
            onPress={onPress}
            disabled={nonaktif}
            style={({ pressed }) => [
                styles.button,
                { backgroundColor: v.bg, borderColor: v.border, borderWidth: 1 },
                pressed && !nonaktif && styles.ditekan,
                nonaktif && styles.buttonDisabled,
                style,
            ]}
        >
            {loading
                ? <ActivityIndicator color={v.fg} size="small" />
                : <Text style={[styles.buttonLabel, { color: v.fg }]}>{icon ? `${icon}  ` : ''}{label}</Text>}
        </Pressable>
    );
}

const VARIAN = (a) => ({
    primary: { bg: a.solid, fg: '#fff', border: a.solid },
    success: { bg: aksen.hijau.solid, fg: '#fff', border: aksen.hijau.solid },
    danger: { bg: aksen.merah.solid, fg: '#fff', border: aksen.merah.solid },
    outline: { bg: 'transparent', fg: a.teks, border: a.solid },
    ghost: { bg: colors.surface, fg: colors.text, border: colors.border },
});

export function Field({ label, error, hint, ...props }) {
    return (
        <View style={styles.field}>
            {!!label && <Text style={styles.fieldLabel}>{label}</Text>}
            <TextInput
                placeholderTextColor={colors.textMuted}
                style={[
                    styles.input,
                    props.multiline && styles.inputMultiline,
                    !!error && styles.inputError,
                ]}
                textAlignVertical={props.multiline ? 'top' : 'center'}
                {...props}
            />
            {!!error && <Text style={styles.fieldError}>{error}</Text>}
            {!!hint && !error && <Text style={typography.caption}>{hint}</Text>}
        </View>
    );
}

/** Baris "label : nilai" untuk tampilan detail. */
export function Row({ label, value, valueStyle, terang }) {
    return (
        <View style={styles.row}>
            <Text style={[typography.muted, styles.rowLabel, terang && styles.teksTerangRedup]}>{label}</Text>
            <Text style={[typography.body, styles.rowValue, terang && styles.teksTerang, valueStyle]}>
                {value ?? '-'}
            </Text>
        </View>
    );
}

export function Badge({ label, tone = 'indigo' }) {
    const a = warnaAksen(PETA_TONE[tone] || tone);

    return (
        <View style={[styles.badge, { backgroundColor: a.lembut }]}>
            <Text style={[styles.badgeText, { color: a.teks }]}>{label}</Text>
        </View>
    );
}

/** Nama tone lama tetap dikenali supaya layar lama tidak perlu diubah. */
const PETA_TONE = {
    primary: 'indigo',
    success: 'hijau',
    warning: 'oranye',
    danger: 'merah',
    muted: 'abu',
    info: 'biru',
};

export function Loading({ label = 'Memuat…' }) {
    return (
        <View style={styles.center}>
            <ActivityIndicator color={colors.primary} size="large" />
            <Text style={[typography.muted, { marginTop: spacing.md }]}>{label}</Text>
        </View>
    );
}

export function EmptyState({ judul = 'Belum ada data', pesan, aksi }) {
    return (
        <View style={styles.center}>
            <View style={[styles.ikonBulat, { backgroundColor: aksen.indigo.lembut }]}>
                <Ionicons name="file-tray-outline" size={28} color={aksen.indigo.solid} />
            </View>
            <Text style={typography.heading}>{judul}</Text>
            {!!pesan && <Text style={[typography.muted, styles.emptyPesan]}>{pesan}</Text>}
            {aksi}
        </View>
    );
}

export function ErrorState({ pesan, onCoba }) {
    return (
        <View style={styles.center}>
            <View style={[styles.ikonBulat, { backgroundColor: aksen.merah.lembut }]}>
                <Ionicons name="alert-circle-outline" size={28} color={aksen.merah.solid} />
            </View>
            <Text style={typography.heading}>Gagal memuat</Text>
            <Text style={[typography.muted, styles.emptyPesan]}>{pesan}</Text>
            {!!onCoba && <Button label="Coba lagi" variant="outline" onPress={onCoba} />}
        </View>
    );
}

/** Alert inline untuk pesan sukses/gagal di dalam form. */
export function Notice({ tone = 'danger', children }) {
    const a = warnaAksen(PETA_TONE[tone] || tone);

    return (
        <View style={[styles.notice, { backgroundColor: a.lembut, borderLeftColor: a.solid }]}>
            <Text style={{ color: a.teks, fontSize: 13, lineHeight: 19 }}>{children}</Text>
        </View>
    );
}

/** Kotak angka berwarna untuk ringkasan. */
export function Statistik({ label, nilai, warna = 'indigo', ikon, onPress, style }) {
    const a = warnaAksen(warna);
    const dasar = [styles.statistik, { backgroundColor: a.lembut, borderColor: a.solid + '2a' }, style];

    // View tidak menerima style berbentuk fungsi — hanya Pressable yang boleh
    const Wadah = onPress ? Pressable : View;
    const propStyle = onPress
        ? ({ pressed }) => [...dasar, pressed && styles.ditekan]
        : dasar;

    return (
        <Wadah onPress={onPress} style={propStyle}>
            {!!ikon && (
                <View style={[styles.statistikIkon, { backgroundColor: a.solid }]}>
                    <Ionicons name={ikon} size={14} color="#fff" />
                </View>
            )}
            <Text style={[styles.statistikNilai, { color: a.teks }]}>{nilai}</Text>
            <Text style={styles.statistikLabel} numberOfLines={2}>{label}</Text>
        </Wadah>
    );
}

const styles = StyleSheet.create({
    akar: { flex: 1, backgroundColor: colors.background },
    pita: { position: 'absolute', left: 0, right: 0, top: 0 },
    aman: { flex: 1 },

    kepala: {
        flexDirection: 'row',
        alignItems: 'center',
        gap: spacing.md,
        paddingHorizontal: spacing.lg,
        paddingTop: Platform.OS === 'android' ? spacing.md : spacing.xs,
        paddingBottom: spacing.md,
        minHeight: 52,
    },
    tombolKembali: {
        width: 36,
        height: 36,
        borderRadius: radius.pill,
        backgroundColor: 'rgba(255,255,255,0.22)',
        alignItems: 'center',
        justifyContent: 'center',
    },
    kepalaTeks: { flex: 1 },
    kepalaAksi: { flexDirection: 'row', gap: spacing.sm },
    judul: { fontSize: 19, fontWeight: '800', color: colors.atasGradien },
    subjudul: { fontSize: 12, color: colors.atasGradienRedup, marginTop: 1 },

    lembar: {
        flex: 1,
        backgroundColor: colors.background,
        borderTopLeftRadius: radius.xl,
        borderTopRightRadius: radius.xl,
    },
    lembarIsi: { paddingBottom: spacing.xxl },
    lembarKaku: { overflow: 'hidden' },
    pad: { padding: spacing.lg, gap: spacing.md },

    card: {
        backgroundColor: colors.surface,
        borderRadius: radius.lg,
        padding: spacing.lg,
        borderWidth: 1,
        borderColor: colors.border,
        gap: spacing.sm,
        overflow: 'hidden',
        ...bayangan.kartu,
    },
    cardGradien: { borderColor: 'transparent', ...bayangan.naik },
    pitaAksen: { position: 'absolute', left: 0, top: 0, bottom: 0, width: 4 },

    sectionTitle: {
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'space-between',
        marginTop: spacing.sm,
    },

    button: {
        minHeight: 48,
        borderRadius: radius.md,
        alignItems: 'center',
        justifyContent: 'center',
        paddingHorizontal: spacing.lg,
    },
    buttonGradien: { borderWidth: 0, ...bayangan.kartu },
    ditekan: { opacity: 0.82, transform: [{ scale: 0.995 }] },
    buttonDisabled: { opacity: 0.45 },
    buttonLabel: { fontSize: 15, fontWeight: '700' },

    field: { gap: spacing.xs },
    fieldLabel: { fontSize: 13, fontWeight: '700', color: colors.text },
    input: {
        minHeight: 48,
        borderWidth: 1,
        borderColor: colors.border,
        borderRadius: radius.md,
        paddingHorizontal: spacing.md,
        backgroundColor: colors.background,
        color: colors.text,
        fontSize: 15,
    },
    inputMultiline: { minHeight: 92, paddingTop: spacing.md, paddingBottom: spacing.md },
    inputError: { borderColor: colors.danger, backgroundColor: colors.dangerSoft },
    fieldError: { fontSize: 12, color: colors.danger },

    row: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'flex-start',
        gap: spacing.md,
        paddingVertical: 3,
    },
    rowLabel: { flexShrink: 0 },
    rowValue: { flex: 1, textAlign: 'right', fontWeight: '600' },
    teksTerang: { color: '#fff' },
    teksTerangRedup: { color: 'rgba(255,255,255,0.75)' },

    badge: {
        paddingHorizontal: spacing.md,
        paddingVertical: 5,
        borderRadius: radius.pill,
        alignSelf: 'flex-start',
    },
    badgeText: { fontSize: 11, fontWeight: '800', letterSpacing: 0.2 },

    center: {
        flex: 1,
        alignItems: 'center',
        justifyContent: 'center',
        padding: spacing.xl,
        gap: spacing.sm,
        minHeight: 240,
    },
    ikonBulat: {
        width: 60,
        height: 60,
        borderRadius: radius.pill,
        alignItems: 'center',
        justifyContent: 'center',
        marginBottom: spacing.xs,
    },
    emptyPesan: { textAlign: 'center' },

    notice: {
        borderRadius: radius.md,
        padding: spacing.md,
        borderLeftWidth: 4,
    },

    statistik: {
        flex: 1,
        borderRadius: radius.lg,
        borderWidth: 1,
        padding: spacing.md,
        alignItems: 'center',
        gap: 2,
    },
    statistikIkon: {
        width: 26,
        height: 26,
        borderRadius: radius.pill,
        alignItems: 'center',
        justifyContent: 'center',
        marginBottom: 2,
    },
    statistikNilai: { fontSize: 22, fontWeight: '800' },
    statistikLabel: { ...typography.caption, textAlign: 'center' },
});
