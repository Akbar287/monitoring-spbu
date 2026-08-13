import { Procurement } from '../api/endpoints';

/**
 * Konfigurasi lima tahap Procurement. Layar daftar bersifat generik dan
 * membaca definisi dari sini; layar detail tetap khusus per tahap karena
 * aksinya berbeda-beda.
 */
export const TAHAP = {
    perencanaan: {
        label: 'Perencanaan',
        ikon: 'clipboard-outline',
        warna: 'indigo',
        deskripsi: 'Purchase Requisition',
        daftar: (params) => Procurement.perencanaan.daftar(params),
        layarDetail: 'Dokumen',
        bisaTambah: true,
        filter: [
            { key: 'aktif', label: 'Aktif' },
            { key: 'ditolak', label: 'Ditolak' },
            { key: 'semua', label: 'Semua' },
        ],
    },
    pembelian: {
        label: 'Pembelian',
        ikon: 'cart-outline',
        warna: 'ungu',
        deskripsi: 'Purchase Order & pajak',
        daftar: (params) => Procurement.pembelian.daftar(params),
        layarDetail: 'Dokumen',
    },
    pembayaran: {
        label: 'Pembayaran',
        ikon: 'card-outline',
        warna: 'hijau',
        deskripsi: 'Invoice & konfirmasi',
        daftar: (params) => Procurement.pembayaran.daftar(params),
        layarDetail: 'Pembayaran',
    },
    ms2: {
        label: 'MS2',
        ikon: 'swap-horizontal-outline',
        warna: 'oranye',
        deskripsi: 'Alokasi stok Pertamina',
        daftar: (params) => Procurement.ms2.daftar(params),
        layarDetail: 'Ms2',
        bisaTambah: true,
    },
    delivery: {
        label: 'Delivery',
        ikon: 'bus-outline',
        warna: 'biru',
        deskripsi: 'Pengiriman & dokumen LO',
        daftar: (params) => Procurement.delivery.daftar(params),
        layarDetail: 'Delivery',
        filter: [
            { key: 'berjalan', label: 'Berjalan' },
            { key: 'selesai', label: 'Selesai' },
            { key: 'semua', label: 'Semua' },
        ],
    },
};

export const DAFTAR_TAHAP = Object.entries(TAHAP).map(([key, konf]) => ({ key, ...konf }));
