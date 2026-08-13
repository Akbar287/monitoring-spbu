import { createContext, useCallback, useContext, useEffect, useMemo, useState } from 'react';
import { Kelola } from '../api/endpoints';

const OpsiContext = createContext(null);

/**
 * Daftar pilihan dropdown master (SPBU, produk, jabatan, …) dimuat sekali
 * lalu dipakai bersama seluruh form. Hanya diambil bila pengguna berhak
 * membuka modul Master, karena endpoint-nya dibatasi Penanggung Jawab.
 */
export function OpsiProvider({ aktif, children }) {
    const [opsi, setOpsi] = useState({});
    const [memuat, setMemuat] = useState(false);
    const [error, setError] = useState(null);

    const muat = useCallback(async () => {
        if (!aktif) return;
        setMemuat(true);
        setError(null);
        try {
            setOpsi(await Kelola.opsi());
        } catch (e) {
            setError(e);
        } finally {
            setMemuat(false);
        }
    }, [aktif]);

    useEffect(() => { muat(); }, [muat]);

    const value = useMemo(
        () => ({ opsi, memuat, error, muat, ambil: (kunci) => opsi?.[kunci] || [] }),
        [opsi, memuat, error, muat]
    );

    return <OpsiContext.Provider value={value}>{children}</OpsiContext.Provider>;
}

export function useOpsi() {
    return useContext(OpsiContext) ?? { opsi: {}, memuat: false, error: null, muat: () => {}, ambil: () => [] };
}
