import * as Location from 'expo-location';

/**
 * Minta izin lokasi foreground lalu ambil satu titik koordinat.
 * Melempar Error dengan pesan siap tampil bila izin ditolak.
 */
export async function ambilLokasi() {
    const { status } = await Location.requestForegroundPermissionsAsync();

    if (status !== 'granted') {
        throw new Error('Izin lokasi ditolak. Aktifkan izin lokasi di pengaturan untuk bisa absen.');
    }

    const posisi = await Location.getCurrentPositionAsync({
        accuracy: Location.Accuracy.High,
    });

    return {
        latitude: posisi.coords.latitude,
        longitude: posisi.coords.longitude,
        akurasi: posisi.coords.accuracy,
        // Android menandai lokasi palsu; berguna untuk audit kehadiran
        palsu: posisi.mocked === true,
    };
}
