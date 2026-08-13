import { createContext, useCallback, useContext, useEffect, useMemo, useRef, useState } from 'react';
import { getToken, setToken, setUnauthorizedHandler } from '../api/client';
import { Auth } from '../api/endpoints';
import { namaPerangkat } from '../utils/device';

const AuthContext = createContext(null);

/**
 * Sumber kebenaran status login. Token disimpan di SecureStore (keychain /
 * keystore), bukan AsyncStorage, karena isinya kredensial.
 */
export function AuthProvider({ children }) {
    const [user, setUser] = useState(null);
    const [memuat, setMemuat] = useState(true); // pemulihan sesi saat app dibuka
    const mounted = useRef(true);

    useEffect(() => () => { mounted.current = false; }, []);

    const keluarPaksa = useCallback(() => {
        if (mounted.current) setUser(null);
    }, []);

    useEffect(() => {
        setUnauthorizedHandler(keluarPaksa);
        return () => setUnauthorizedHandler(null);
    }, [keluarPaksa]);

    // Pemulihan sesi: kalau ada token tersimpan, validasi ke server sekali.
    useEffect(() => {
        (async () => {
            try {
                const token = await getToken();
                if (!token) return;
                const profil = await Auth.me();
                if (mounted.current) setUser(profil);
            } catch {
                await setToken(''); // token kedaluwarsa / dicabut
            } finally {
                if (mounted.current) setMemuat(false);
            }
        })();
    }, []);

    const masuk = useCallback(async (username, password) => {
        const hasil = await Auth.login(username, password, await namaPerangkat());
        await setToken(hasil.token);
        setUser(hasil.user);
        return hasil.user;
    }, []);

    const keluar = useCallback(async () => {
        try {
            await Auth.logout();
        } catch {
            // token mungkin sudah tidak valid di server — tetap bersihkan lokal
        }
        await setToken('');
        setUser(null);
    }, []);

    const segarkan = useCallback(async () => {
        const profil = await Auth.me();
        setUser(profil);
        return profil;
    }, []);

    const value = useMemo(
        () => ({ user, memuat, masuk, keluar, segarkan, setUser }),
        [user, memuat, masuk, keluar, segarkan]
    );

    return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth() {
    const ctx = useContext(AuthContext);
    if (!ctx) throw new Error('useAuth harus dipakai di dalam <AuthProvider>');
    return ctx;
}
