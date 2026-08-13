import * as SecureStore from 'expo-secure-store';
import Constants from 'expo-constants';
import { Platform } from 'react-native';

const TOKEN_KEY = 'spbu.auth.token';
const PORT_BACKEND = 8000;

// Diambil dari .env (EXPO_PUBLIC_* di-inline oleh Expo saat bundling — ubah
// .env lalu RESTART `npm start`, karena nilainya ikut ke dalam bundel).
const URL_ENV = (process.env.EXPO_PUBLIC_API_URL || '').trim().replace(/\/+$/, '');

const menunjukKeDiriSendiri = (url) =>
    /^https?:\/\/(127\.0\.0\.1|localhost|0\.0\.0\.0)(:|\/|$)/i.test(url);

/**
 * Host Metro (= IP laptop yang menjalankan `npm start`), mis. "192.168.1.102:8081".
 * Dipakai sebagai cadangan karena di perangkat fisik `127.0.0.1` berarti
 * ponsel itu sendiri, sehingga backend di laptop tidak akan pernah terjangkau.
 */
function hostMetro() {
    const host = Constants.expoConfig?.hostUri?.split(':')[0];
    return host ? `http://${host}:${PORT_BACKEND}` : null;
}

function tentukanBaseUrl() {
    if (URL_ENV && !menunjukKeDiriSendiri(URL_ENV)) {
        return { url: URL_ENV, sumber: 'env' };
    }

    // Di web, localhost memang benar (browser jalan di mesin yang sama).
    if (Platform.OS !== 'web') {
        const otomatis = hostMetro();
        if (otomatis) return { url: otomatis, sumber: 'otomatis' };
    }

    return { url: URL_ENV || `http://127.0.0.1:${PORT_BACKEND}`, sumber: URL_ENV ? 'env' : 'bawaan' };
}

const terpilih = tentukanBaseUrl();

export const BASE_URL = terpilih.url;
/** 'env' = dari .env · 'otomatis' = diturunkan dari host Metro · 'bawaan' = fallback */
export const SUMBER_BASE_URL = terpilih.sumber;
export const API_URL = `${BASE_URL}/api/v1`;

/** Error API yang membawa status HTTP dan detail validasi Laravel. */
export class ApiError extends Error {
    constructor(message, { status = 0, errors = {}, data = null } = {}) {
        super(message);
        this.name = 'ApiError';
        this.status = status;
        this.errors = errors;
        this.data = data;
    }

    /** Pesan validasi pertama untuk sebuah field, bila ada. */
    fieldError(field) {
        const list = this.errors?.[field];
        return Array.isArray(list) ? list[0] : list || null;
    }
}

let cachedToken = null;
let onUnauthorized = null;

/** Dipanggil AuthContext supaya 401 dari server otomatis memaksa logout. */
export function setUnauthorizedHandler(handler) {
    onUnauthorized = handler;
}

export async function getToken() {
    if (cachedToken !== null) return cachedToken;
    cachedToken = (await SecureStore.getItemAsync(TOKEN_KEY)) || '';
    return cachedToken;
}

export async function setToken(token) {
    cachedToken = token || '';
    if (token) {
        await SecureStore.setItemAsync(TOKEN_KEY, token);
    } else {
        await SecureStore.deleteItemAsync(TOKEN_KEY);
    }
}

function buildUrl(path, params) {
    const url = `${API_URL}/${String(path).replace(/^\/+/, '')}`;
    if (!params) return url;

    const query = Object.entries(params)
        .filter(([, v]) => v !== undefined && v !== null && v !== '')
        .map(([k, v]) => `${encodeURIComponent(k)}=${encodeURIComponent(v)}`)
        .join('&');

    return query ? `${url}?${query}` : url;
}

/**
 * Pemanggil HTTP tunggal untuk seluruh aplikasi.
 *
 * Server membalas amplop { status, message, data } (App\Support\ApiResponse),
 * kecuali error validasi Laravel yang berbentuk { message, errors } — keduanya
 * dinormalkan menjadi nilai balik `data` atau lemparan ApiError.
 */
export async function request(path, { method = 'GET', params, body, form, timeout = 20000 } = {}) {
    const token = await getToken();

    const headers = { Accept: 'application/json' };
    if (token) headers.Authorization = `Bearer ${token}`;

    let payload;
    if (form) {
        payload = form; // FormData: biarkan RN menyetel Content-Type + boundary
    } else if (body !== undefined) {
        headers['Content-Type'] = 'application/json';
        payload = JSON.stringify(body);
    }

    const controller = new AbortController();
    const timer = setTimeout(() => controller.abort(), timeout);

    let response;
    try {
        response = await fetch(buildUrl(path, params), {
            method,
            headers,
            body: payload,
            signal: controller.signal,
        });
    } catch (e) {
        clearTimeout(timer);
        const pesan = e.name === 'AbortError'
            ? 'Permintaan terlalu lama. Periksa koneksi Anda.'
            : 'Tidak dapat terhubung ke server. Periksa koneksi atau alamat server.';
        throw new ApiError(pesan, { status: 0 });
    }
    clearTimeout(timer);

    let json = null;
    try {
        json = await response.json();
    } catch {
        json = null;
    }

    if (response.status === 401) {
        await setToken('');
        onUnauthorized?.();
        throw new ApiError(json?.message || 'Sesi Anda berakhir. Silakan masuk kembali.', { status: 401 });
    }

    if (!response.ok || json?.status === false) {
        throw new ApiError(json?.message || `Terjadi kesalahan (HTTP ${response.status}).`, {
            status: response.status,
            errors: json?.errors || {},
            data: json?.data ?? null,
        });
    }

    return json?.data ?? null;
}

export const api = {
    get: (path, params) => request(path, { method: 'GET', params }),
    post: (path, body) => request(path, { method: 'POST', body }),
    put: (path, body) => request(path, { method: 'PUT', body }),
    del: (path) => request(path, { method: 'DELETE' }),
    upload: (path, form) => request(path, { method: 'POST', form, timeout: 60000 }),
};

/**
 * Diagnosa koneksi ke backend — dipakai tombol "Tes Koneksi" di layar login.
 * Mengembalikan pesan yang menunjuk penyebab konkret, bukan sekadar "gagal".
 */
export async function cekKoneksi() {
    const mulai = Date.now();

    try {
        const response = await fetch(`${API_URL}/ping`, {
            headers: { Accept: 'application/json' },
            signal: AbortSignal.timeout ? AbortSignal.timeout(8000) : undefined,
        });

        const ms = Date.now() - mulai;

        if (!response.ok) {
            return {
                ok: false,
                pesan: `Server menjawab HTTP ${response.status}. Pastikan alamat menunjuk ke aplikasi Laravel yang benar.`,
            };
        }

        const json = await response.json();

        return {
            ok: true,
            pesan: `Terhubung ke "${json?.data?.app ?? 'server'}" dalam ${ms} ms.`,
        };
    } catch (e) {
        const lokal = menunjukKeDiriSendiri(BASE_URL);

        return {
            ok: false,
            pesan: lokal
                ? `Tidak dapat terhubung ke ${BASE_URL}. Alamat ini menunjuk ke perangkat itu sendiri — isi EXPO_PUBLIC_API_URL di mobile/.env dengan IP LAN komputer, lalu restart "npm start".`
                : `Tidak dapat terhubung ke ${BASE_URL}. Periksa: (1) backend berjalan dengan --host=0.0.0.0, (2) ponsel dan komputer satu jaringan Wi-Fi, (3) firewall tidak memblokir port ${PORT_BACKEND}.`,
        };
    }
}
