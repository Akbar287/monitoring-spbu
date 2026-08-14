import { ActivityIndicator, View, StyleSheet } from 'react-native';
import { NavigationContainer, DefaultTheme } from '@react-navigation/native';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import { createBottomTabNavigator } from '@react-navigation/bottom-tabs';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import Ionicons from '@expo/vector-icons/Ionicons';

import { useAuth } from '../auth/AuthContext';
import LoginScreen from '../screens/LoginScreen';
import BerandaScreen from '../screens/BerandaScreen';
import JadwalScreen from '../screens/JadwalScreen';
import JadwalDetailScreen from '../screens/JadwalDetailScreen';
import AbsenMasukScreen from '../screens/AbsenMasukScreen';
import NotifikasiScreen from '../screens/NotifikasiScreen';
import ProfilScreen from '../screens/ProfilScreen';
import GantiPasswordScreen from '../screens/GantiPasswordScreen';
import MasterHomeScreen from '../screens/master/MasterHomeScreen';
import MasterListScreen from '../screens/master/MasterListScreen';
import MasterDetailScreen from '../screens/master/MasterDetailScreen';
import MasterFormScreen from '../screens/master/MasterFormScreen';
import LampiranScreen from '../screens/master/LampiranScreen';
import MemberRelasiScreen from '../screens/master/MemberRelasiScreen';
import ProcurementHomeScreen from '../screens/procurement/ProcurementHomeScreen';
import ProcurementListScreen from '../screens/procurement/ProcurementListScreen';
import DokumenScreen from '../screens/procurement/DokumenScreen';
import BarisFormScreen from '../screens/procurement/BarisFormScreen';
import PajakFormScreen from '../screens/procurement/PajakFormScreen';
import RencanaBaruScreen from '../screens/procurement/RencanaBaruScreen';
import PembayaranScreen from '../screens/procurement/PembayaranScreen';
import PembayaranFormScreen from '../screens/procurement/PembayaranFormScreen';
import Ms2Screen from '../screens/procurement/Ms2Screen';
import Ms2FormScreen from '../screens/procurement/Ms2FormScreen';
import Ms2KirimScreen from '../screens/procurement/Ms2KirimScreen';
import DeliveryScreen from '../screens/procurement/DeliveryScreen';
import LoFormScreen from '../screens/procurement/LoFormScreen';
import PersediaanHomeScreen from '../screens/persediaan/PersediaanHomeScreen';
import MonitoringScreen from '../screens/persediaan/MonitoringScreen';
import MonitoringDetailScreen from '../screens/persediaan/MonitoringDetailScreen';
import MonitoringRiwayatScreen from '../screens/persediaan/MonitoringRiwayatScreen';
import StokTakingScreen from '../screens/persediaan/StokTakingScreen';
import StokTakingFormScreen from '../screens/persediaan/StokTakingFormScreen';
import DokumenDetailScreen from '../screens/persediaan/DokumenDetailScreen';
import PenerimaanScreen from '../screens/persediaan/PenerimaanScreen';
import PenerimaanFormScreen from '../screens/persediaan/PenerimaanFormScreen';
import PenerimaanDetailScreen from '../screens/persediaan/PenerimaanDetailScreen';
import StokTransferScreen from '../screens/persediaan/StokTransferScreen';
import StokTransferFormScreen from '../screens/persediaan/StokTransferFormScreen';
import StokTransferDetailScreen from '../screens/persediaan/StokTransferDetailScreen';
import StokSummaryScreen from '../screens/persediaan/StokSummaryScreen';
import TotalisatorScreen from '../screens/totalisator/TotalisatorScreen';
import TotalisatorFormScreen from '../screens/totalisator/TotalisatorFormScreen';
import TotalisatorDetailScreen from '../screens/totalisator/TotalisatorDetailScreen';
import SetoranScreen from '../screens/setoran/SetoranScreen';
import SetoranFormScreen from '../screens/setoran/SetoranFormScreen';
import SetoranDetailScreen from '../screens/setoran/SetoranDetailScreen';
import PenarikanFormScreen from '../screens/setoran/PenarikanFormScreen';
import LaporanScreen from '../screens/financial/LaporanScreen';
import BukuBesarScreen from '../screens/financial/BukuBesarScreen';
import JurnalUmumScreen from '../screens/financial/JurnalUmumScreen';
import JurnalDetailScreen from '../screens/financial/JurnalDetailScreen';
import CoaScreen from '../screens/financial/CoaScreen';
import { OpsiProvider } from '../master/OpsiContext';
import { colors } from '../theme';

const Tab = createBottomTabNavigator();
const Stack = createNativeStackNavigator();

const tema = {
    ...DefaultTheme,
    colors: {
        ...DefaultTheme.colors,
        primary: colors.primary,
        background: colors.background,
        card: colors.surface,
        text: colors.text,
        border: colors.border,
    },
};

function BerandaStack() {
    return (
        <Stack.Navigator screenOptions={{ headerShown: false }}>
            <Stack.Screen name="BerandaUtama" component={BerandaScreen} />
            <Stack.Screen name="Notifikasi" component={NotifikasiScreen} />
        </Stack.Navigator>
    );
}

function ProcurementStack() {
    return (
        <Stack.Navigator screenOptions={{ headerShown: false }}>
            <Stack.Screen name="ProcurementHome" component={ProcurementHomeScreen} />
            <Stack.Screen name="ProcurementList" component={ProcurementListScreen} />
            <Stack.Screen name="RencanaBaru" component={RencanaBaruScreen} />
            <Stack.Screen name="Dokumen" component={DokumenScreen} />
            <Stack.Screen name="BarisForm" component={BarisFormScreen} />
            <Stack.Screen name="PajakForm" component={PajakFormScreen} />
            <Stack.Screen name="Pembayaran" component={PembayaranScreen} />
            <Stack.Screen name="PembayaranForm" component={PembayaranFormScreen} />
            <Stack.Screen name="Ms2" component={Ms2Screen} />
            <Stack.Screen name="Ms2Form" component={Ms2FormScreen} />
            <Stack.Screen name="Ms2Kirim" component={Ms2KirimScreen} />
            <Stack.Screen name="Delivery" component={DeliveryScreen} />
            <Stack.Screen name="LoForm" component={LoFormScreen} />
        </Stack.Navigator>
    );
}

function JadwalStack() {
    return (
        <Stack.Navigator screenOptions={{ headerShown: false }}>
            <Stack.Screen name="JadwalDaftar" component={JadwalScreen} />
            <Stack.Screen name="JadwalDetail" component={JadwalDetailScreen} />
            <Stack.Screen name="AbsenMasuk" component={AbsenMasukScreen} />
        </Stack.Navigator>
    );
}

function PersediaanStack() {
    return (
        // header bawaan dimatikan seperti stack lain — judul & tombol kembali
        // disediakan komponen Halaman
        <Stack.Navigator screenOptions={{ headerShown: false }}>
            <Stack.Screen name="PersediaanHome" component={PersediaanHomeScreen} />
            <Stack.Screen name="Monitoring" component={MonitoringScreen} />
            <Stack.Screen name="MonitoringDetail" component={MonitoringDetailScreen} />
            <Stack.Screen name="MonitoringRiwayat" component={MonitoringRiwayatScreen} />
            <Stack.Screen name="StokTaking" component={StokTakingScreen} />
            <Stack.Screen name="StokTakingForm" component={StokTakingFormScreen} />
            <Stack.Screen name="StokTakingDetail" component={DokumenDetailScreen} />
            <Stack.Screen name="Penerimaan" component={PenerimaanScreen} />
            <Stack.Screen name="PenerimaanForm" component={PenerimaanFormScreen} />
            <Stack.Screen name="PenerimaanDetail" component={PenerimaanDetailScreen} />
            <Stack.Screen name="StokTransfer" component={StokTransferScreen} />
            <Stack.Screen name="StokTransferForm" component={StokTransferFormScreen} />
            <Stack.Screen name="StokTransferDetail" component={StokTransferDetailScreen} />
            <Stack.Screen name="StokSummary" component={StokSummaryScreen} />
            <Stack.Screen name="Totalisator" component={TotalisatorScreen} />
            <Stack.Screen name="TotalisatorForm" component={TotalisatorFormScreen} />
            <Stack.Screen name="TotalisatorDetail" component={TotalisatorDetailScreen} />
            <Stack.Screen name="Setoran" component={SetoranScreen} />
            <Stack.Screen name="SetoranForm" component={SetoranFormScreen} />
            <Stack.Screen name="SetoranDetail" component={SetoranDetailScreen} />
            <Stack.Screen name="PenarikanForm" component={PenarikanFormScreen} />
        </Stack.Navigator>
    );
}

function MasterStack() {
    return (
        <Stack.Navigator screenOptions={{ headerShown: false }}>
            <Stack.Screen name="MasterHome" component={MasterHomeScreen} />
            <Stack.Screen name="MasterList" component={MasterListScreen} />
            <Stack.Screen name="MasterDetail" component={MasterDetailScreen} />
            <Stack.Screen name="MasterForm" component={MasterFormScreen} />
            <Stack.Screen name="Lampiran" component={LampiranScreen} />
            <Stack.Screen name="MemberRelasi" component={MemberRelasiScreen} />
            <Stack.Screen name="Laporan" component={LaporanScreen} />
            <Stack.Screen name="BukuBesar" component={BukuBesarScreen} />
            <Stack.Screen name="JurnalUmum" component={JurnalUmumScreen} />
            <Stack.Screen name="JurnalDetail" component={JurnalDetailScreen} />
            <Stack.Screen name="Coa" component={CoaScreen} />
        </Stack.Navigator>
    );
}

function ProfilStack() {
    return (
        <Stack.Navigator screenOptions={{ headerShown: false }}>
            <Stack.Screen name="ProfilUtama" component={ProfilScreen} />
            <Stack.Screen name="GantiPassword" component={GantiPasswordScreen} />
        </Stack.Navigator>
    );
}

const IKON = {
    BerandaTab: ['home', 'home-outline'],
    JadwalTab: ['calendar', 'calendar-outline'],
    ProcurementTab: ['cart', 'cart-outline'],
    PersediaanTab: ['cube', 'cube-outline'],
    MasterTab: ['grid', 'grid-outline'],
    ProfilTab: ['person', 'person-outline'],
};

function TabUtama({ aksesPenuh, punyaJadwal }) {
    // Aplikasi digambar edge-to-edge (navigationBarColor transparan), jadi tab
    // bar harus menaikkan sendiri kontennya setinggi inset bawah — kalau tidak,
    // labelnya tertimpa tombol Home/Back/Menu sistem Android.
    const insets = useSafeAreaInsets();

    return (
        <Tab.Navigator
            screenOptions={({ route }) => ({
                headerShown: false,
                tabBarActiveTintColor: colors.primary,
                tabBarInactiveTintColor: colors.textMuted,
                tabBarStyle: {
                    backgroundColor: colors.surface,
                    borderTopColor: colors.border,
                    borderTopWidth: 1,
                    // latar tetap membentang ke bawah, isinya yang dinaikkan
                    height: 62 + insets.bottom,
                    paddingTop: 6,
                    paddingBottom: 8 + insets.bottom,
                },
                // enam tab pada layar ponsel — ukuran label dikecilkan agar tidak terpotong
                tabBarLabelStyle: { fontSize: 10, fontWeight: '700' },
                tabBarItemStyle: { paddingVertical: 2 },
                tabBarIcon: ({ focused, color, size }) => {
                    const [aktif, nonaktif] = IKON[route.name] || IKON.BerandaTab;
                    return <Ionicons name={focused ? aktif : nonaktif} size={size} color={color} />;
                },
            })}
        >
            <Tab.Screen name="BerandaTab" component={BerandaStack} options={{ title: 'Beranda' }} />
            {punyaJadwal && (
                <Tab.Screen name="JadwalTab" component={JadwalStack} options={{ title: 'Jadwal' }} />
            )}
            {aksesPenuh && (
                <Tab.Screen name="ProcurementTab" component={ProcurementStack} options={{ title: 'Procurement' }} />
            )}
            {aksesPenuh && (
                <Tab.Screen name="PersediaanTab" component={PersediaanStack} options={{ title: 'Persediaan' }} />
            )}
            {aksesPenuh && (
                <Tab.Screen name="MasterTab" component={MasterStack} options={{ title: 'Kelola' }} />
            )}
            <Tab.Screen name="ProfilTab" component={ProfilStack} options={{ title: 'Profil' }} />
        </Tab.Navigator>
    );
}

export default function RootNavigator() {
    const { user, memuat } = useAuth();

    // pemulihan sesi dari SecureStore — hindari kedipan layar login
    if (memuat) {
        return (
            <View style={styles.splash}>
                <ActivityIndicator size="large" color={colors.primary} />
            </View>
        );
    }

    // Procurement, Master Data, dan Konfigurasi hanya untuk jabatan tertentu
    // (Direktur Utama, Direktur, Komisaris, Admin, Manajer). Flag ini dihitung
    // server di App\Support\Akses dan dikirim lewat profil, jadi daftar
    // jabatannya tidak disalin di sini. Sisanya hanya Beranda, Jadwal, Profil.
    const aksesPenuh = user?.akses_penuh === true;
    // Divisi stakeholder (Top Manajemen) tidak menjalani jadwal kerja & absensi,
    // jadi tab Jadwal disembunyikan — aturannya di App\Support\Akses.
    const punyaJadwal = user?.tanpa_jadwal !== true;

    return (
        <NavigationContainer theme={tema}>
            {user ? (
                <OpsiProvider aktif={aksesPenuh}>
                    <TabUtama aksesPenuh={aksesPenuh} punyaJadwal={punyaJadwal} />
                </OpsiProvider>
            ) : (
                <LoginScreen />
            )}
        </NavigationContainer>
    );
}

const styles = StyleSheet.create({
    splash: { flex: 1, alignItems: 'center', justifyContent: 'center', backgroundColor: colors.background },
});
