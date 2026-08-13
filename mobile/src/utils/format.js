const BULAN = [
    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
];

const HARI = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

/** '2024-12-04' -> 'Rabu, 4 Desember 2024' */
export function tanggalPanjang(iso) {
    const d = parseTanggal(iso);
    if (!d) return '-';
    return `${HARI[d.getDay()]}, ${d.getDate()} ${BULAN[d.getMonth()]} ${d.getFullYear()}`;
}

/** '2024-12-04' -> '4 Des 2024' */
export function tanggalPendek(iso) {
    const d = parseTanggal(iso);
    if (!d) return '-';
    return `${d.getDate()} ${BULAN[d.getMonth()].slice(0, 3)} ${d.getFullYear()}`;
}

/** '2024-12-04 14:37:02' -> '14:37' */
export function jam(iso) {
    const d = parseTanggal(iso);
    if (!d) return '-';
    return `${pad(d.getHours())}:${pad(d.getMinutes())}`;
}

/** '14:00:00' -> '14:00' */
export function jamSingkat(waktu) {
    if (!waktu) return '-';
    return String(waktu).slice(0, 5);
}

/**
 * Format desimal 6 angka gaya Indonesia: "156.838.621,000000".
 *
 * Nilai dari API Procurement datang sebagai STRING dan diformat sebagai
 * string juga — sengaja tidak lewat Number() supaya digit ke-6 tidak hilang
 * pada nilai besar (double JS hanya aman ~15 digit signifikan).
 */
export function desimal6(nilai) {
    const teks = String(nilai ?? '0').trim();
    if (teks === '' || teks === 'null') return '0,000000';

    const negatif = teks.startsWith('-');
    const [bulat = '0', pecahan = ''] = teks.replace('-', '').split('.');

    const ribuan = bulat.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    const enam = (pecahan + '000000').slice(0, 6);

    return `${negatif ? '-' : ''}${ribuan},${enam}`;
}

/** Rupiah dengan presisi penuh 6 desimal. */
export function rupiah6(nilai) {
    return 'Rp ' + desimal6(nilai);
}

/** Kuantitas dengan presisi penuh 6 desimal. */
export function liter6(nilai) {
    return desimal6(nilai) + ' L';
}

export function rupiah(nilai) {
    const angka = Number(nilai || 0);
    return 'Rp ' + angka.toLocaleString('id-ID', { maximumFractionDigits: 0 });
}

export function liter(nilai) {
    const angka = Number(nilai || 0);
    return angka.toLocaleString('id-ID', { maximumFractionDigits: 2 }) + ' L';
}

function pad(n) {
    return String(n).padStart(2, '0');
}

/**
 * Postgres mengirim timestamp tanpa zona ('2024-12-04 14:37:02'). Safari/iOS
 * menolak format itu di `new Date()`, jadi normalkan dulu ke ISO.
 */
function parseTanggal(nilai) {
    if (!nilai) return null;
    const teks = String(nilai).trim().replace(' ', 'T');
    const d = new Date(teks);
    return Number.isNaN(d.getTime()) ? null : d;
}
