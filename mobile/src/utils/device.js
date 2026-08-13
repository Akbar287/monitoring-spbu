import * as Device from 'expo-device';
import Constants from 'expo-constants';
import { Platform } from 'react-native';

/** Nama token per perangkat, dipakai server untuk membedakan sesi. */
export async function namaPerangkat() {
    const model = Device.modelName || Device.deviceName || Platform.OS;
    return `${model}`.slice(0, 64);
}

/**
 * Metadata perangkat untuk tabel `devices` saat absen masuk.
 * Kolomnya pendek di sisi server dan tetap dipotong di sana, tetapi kita
 * kirim seperlunya saja.
 */
export function infoPerangkat() {
    return {
        tipe: 'Mobile',
        model: Device.modelName || 'Unknown',
        os: `${Device.osName || Platform.OS} ${Device.osVersion || ''}`.trim(),
        versi_os: String(Device.osVersion || '0'),
        aplikasi: 'SPBU Mobile',
        versi_aplikasi: String(Constants.expoConfig?.version || '1.0.0'),
    };
}

/** Offset zona waktu perangkat dalam jam (WIB = 7). */
export function offsetGmt() {
    return Math.round(-new Date().getTimezoneOffset() / 60);
}
