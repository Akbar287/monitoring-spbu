import { ActivityIndicator, View, StyleSheet } from 'react-native';
import { NavigationContainer, DefaultTheme } from '@react-navigation/native';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import { createBottomTabNavigator } from '@react-navigation/bottom-tabs';
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

function MasterStack() {
    return (
        <Stack.Navigator screenOptions={{ headerShown: false }}>
            <Stack.Screen name="MasterHome" component={MasterHomeScreen} />
            <Stack.Screen name="MasterList" component={MasterListScreen} />
            <Stack.Screen name="MasterDetail" component={MasterDetailScreen} />
            <Stack.Screen name="MasterForm" component={MasterFormScreen} />
            <Stack.Screen name="Lampiran" component={LampiranScreen} />
            <Stack.Screen name="MemberRelasi" component={MemberRelasiScreen} />
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
    MasterTab: ['grid', 'grid-outline'],
    ProfilTab: ['person', 'person-outline'],
};

function TabUtama({ bolehMaster }) {
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
                    height: 62,
                    paddingTop: 6,
                    paddingBottom: 8,
                },
                tabBarLabelStyle: { fontSize: 11, fontWeight: '700' },
                tabBarItemStyle: { paddingVertical: 2 },
                tabBarIcon: ({ focused, color, size }) => {
                    const [aktif, nonaktif] = IKON[route.name] || IKON.BerandaTab;
                    return <Ionicons name={focused ? aktif : nonaktif} size={size} color={color} />;
                },
            })}
        >
            <Tab.Screen name="BerandaTab" component={BerandaStack} options={{ title: 'Beranda' }} />
            <Tab.Screen name="JadwalTab" component={JadwalStack} options={{ title: 'Jadwal' }} />
            <Tab.Screen name="ProcurementTab" component={ProcurementStack} options={{ title: 'Procurement' }} />
            {bolehMaster && (
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

    // Modul Master Data & Konfigurasi hanya untuk Penanggung Jawab — sama
    // seperti sidebar web, dan ditegakkan juga oleh middleware `level` di backend.
    const bolehMaster = user?.level === 'Penanggung Jawab';

    return (
        <NavigationContainer theme={tema}>
            {user ? (
                <OpsiProvider aktif={bolehMaster}>
                    <TabUtama bolehMaster={bolehMaster} />
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
