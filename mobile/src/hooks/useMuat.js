import { useCallback, useEffect, useRef, useState } from 'react';

/**
 * Pola muat-data yang dipakai hampir semua layar:
 * memuat pertama kali, pull-to-refresh, penanganan error, dan pembatalan
 * setState setelah komponen dilepas.
 *
 * @param {() => Promise<any>} pengambil fungsi pengambil data (harus stabil, bungkus useCallback)
 * @param {any[]} deps dependensi yang memicu muat ulang
 */
export function useMuat(pengambil, deps = []) {
    const [data, setData] = useState(null);
    const [memuat, setMemuat] = useState(true);
    const [menyegarkan, setMenyegarkan] = useState(false);
    const [error, setError] = useState(null);
    const hidup = useRef(true);

    useEffect(() => {
        hidup.current = true;
        return () => { hidup.current = false; };
    }, []);

    const jalankan = useCallback(async ({ segarkan = false } = {}) => {
        if (segarkan) setMenyegarkan(true); else setMemuat(true);
        setError(null);
        try {
            const hasil = await pengambil();
            if (hidup.current) setData(hasil);
        } catch (e) {
            if (hidup.current) setError(e);
        } finally {
            if (hidup.current) {
                setMemuat(false);
                setMenyegarkan(false);
            }
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, deps);

    useEffect(() => { jalankan(); }, [jalankan]);

    const segarkan = useCallback(() => jalankan({ segarkan: true }), [jalankan]);

    return { data, setData, memuat, menyegarkan, error, muatUlang: jalankan, segarkan };
}
