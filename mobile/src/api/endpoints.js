import { api, request } from './client';

/**
 * Satu tempat untuk seluruh endpoint backend (routes/mobile.php).
 * Layar tidak boleh memanggil `api`/`request` langsung agar perubahan kontrak
 * cukup disesuaikan di berkas ini.
 */
export const Auth = {
    login: (username, password, deviceName) =>
        api.post('auth/login', { username, password, device_name: deviceName }),
    me: () => api.get('auth/me'),
    logout: () => api.post('auth/logout'),
    logoutAll: () => api.post('auth/logout-all'),
};

export const Dashboard = {
    ringkasan: () => api.get('dashboard'),
};

export const Jadwal = {
    daftar: (params) => api.get('jadwal', params),
    detail: (id) => api.get(`jadwal/${id}`),
    berjalan: () => api.get('jadwal/berjalan'),

    absenMasuk: (id, { barcode, latitude, longitude, gmt, selfie, device, deskripsiLokasi }) => {
        const form = new FormData();
        form.append('barcode', barcode);
        form.append('latitude', String(latitude));
        form.append('longitude', String(longitude));
        if (gmt !== undefined && gmt !== null) form.append('gmt', String(gmt));
        if (deskripsiLokasi) form.append('deskripsi_lokasi', deskripsiLokasi);
        form.append('selfie', fileDari(selfie, 'selfie-masuk'));
        appendDevice(form, device);
        return api.upload(`jadwal/${id}/masuk`, form);
    },

    absenPulang: (id, { latitude, longitude, gmt, selfie } = {}) => {
        const form = new FormData();
        if (latitude !== undefined && latitude !== null) form.append('latitude', String(latitude));
        if (longitude !== undefined && longitude !== null) form.append('longitude', String(longitude));
        if (gmt !== undefined && gmt !== null) form.append('gmt', String(gmt));
        if (selfie) form.append('selfie', fileDari(selfie, 'selfie-pulang'));
        return api.upload(`jadwal/${id}/pulang`, form);
    },
};

export const Notifikasi = {
    daftar: (params) => api.get('notifikasi', params),
    belumDibaca: () => api.get('notifikasi/belum-dibaca'),
    baca: (id) => api.post(`notifikasi/${id}/baca`),
    bacaSemua: () => api.post('notifikasi/baca-semua'),
};

export const Profil = {
    ambil: () => api.get('profil'),
    simpan: (data) => api.put('profil', data),
    gantiPassword: (data) => api.post('profil/password', data),
    gantiAvatar: (foto) => {
        const form = new FormData();
        form.append('avatar', fileDari(foto, 'avatar'));
        return api.upload('profil/avatar', form);
    },
};

export const Referensi = {
    stok: () => api.get('referensi/stok'),
    harga: () => api.get('referensi/harga'),
};

/**
 * Master Data & Konfigurasi (hanya untuk level Penanggung Jawab).
 *
 * `endpoint` adalah path penuh dari registry, mis. `master/aset` atau
 * `konfigurasi/jam-kerja` — lihat src/master/registry.js. Bentuk route kedua
 * modul identik sehingga fungsi di bawah melayani keduanya.
 */
export const Kelola = {
    opsi: () => api.get('opsi'),

    daftar: (endpoint, params) => api.get(endpoint, params),
    detail: (endpoint, id) => api.get(`${endpoint}/${id}`),
    simpan: (endpoint, data) => api.post(endpoint, data),
    ubah: (endpoint, id, data) => api.put(`${endpoint}/${id}`, data),
    hapus: (endpoint, id) => api.del(`${endpoint}/${id}`),

    // Lampiran/foto — hanya untuk resource 'aset' dan 'fasilitas'
    lampiran: {
        daftar: (jenis, id) => api.get(`master/${jenis}/${id}/lampiran`),
        unggah: (jenis, id, berkas, namaDokumen) => {
            const form = new FormData();
            form.append('berkas', fileDari(berkas, namaDokumen || 'lampiran'));
            if (namaDokumen) form.append('nama_dokumen', namaDokumen);
            return api.upload(`master/${jenis}/${id}/lampiran`, form);
        },
        hapus: (jenis, id, lampiranId) => api.del(`master/${jenis}/${id}/lampiran/${lampiranId}`),
    },

    // Aksi khusus pegawai
    member: {
        avatar: (id, foto) => {
            const form = new FormData();
            form.append('avatar', fileDari(foto, 'avatar'));
            return api.upload(`master/member/${id}/avatar`, form);
        },
        resetPassword: (id) => api.put(`master/member/${id}/reset-password`),
        tambahJabatan: (id, jabatanId) => api.post(`master/member/${id}/jabatan`, { jabatan_id: jabatanId }),
        hapusJabatan: (id, jabatanId) => api.del(`master/member/${id}/jabatan/${jabatanId}`),
        tambahSpbu: (id, spbuId) => api.post(`master/member/${id}/spbu`, { spbu_id: spbuId }),
        hapusSpbu: (id, spbuId) => api.del(`master/member/${id}/spbu/${spbuId}`),
    },
};

/**
 * Procurement — perencanaan → pembelian → pembayaran → MS2 → delivery.
 *
 * Seluruh nilai uang/kuantitas dikirim dan diterima sebagai STRING dengan 6
 * angka di belakang koma. Jangan pernah membungkusnya dengan Number(): nilai
 * seperti "156838621.987654" melebihi presisi aman double JavaScript.
 */
export const Procurement = {
    ringkasan: () => api.get('procurement/ringkasan'),

    perencanaan: {
        daftar: (params) => api.get('procurement/perencanaan', params),
        detail: (id) => api.get(`procurement/perencanaan/${id}`),
        buat: (data) => api.post('procurement/perencanaan', data),
        ubah: (id, data) => api.put(`procurement/perencanaan/${id}`, data),
        hapus: (id) => api.del(`procurement/perencanaan/${id}`),
        konfirmasi: (id, setuju, keterangan) =>
            api.post(`procurement/perencanaan/${id}/konfirmasi`, { setuju, keterangan }),
        tambahProduk: (id, data) => api.post(`procurement/perencanaan/${id}/produk`, data),
        ubahProduk: (id, detailId, data) => api.put(`procurement/perencanaan/${id}/produk/${detailId}`, data),
        hapusProduk: (id, detailId) => api.del(`procurement/perencanaan/${id}/produk/${detailId}`),
    },

    pembelian: {
        daftar: (params) => api.get('procurement/pembelian', params),
        detail: (id) => api.get(`procurement/pembelian/${id}`),
        ubahHarga: (id, detailId, data) => api.put(`procurement/pembelian/${id}/produk/${detailId}`, data),
        ubahPajak: (id, data) => api.put(`procurement/pembelian/${id}/pajak`, data),
        hitungUlangPajak: (id) => api.post(`procurement/pembelian/${id}/pajak/hitung-ulang`),
        lanjut: (id) => api.put(`procurement/pembelian/${id}/lanjut`),
        kembalikan: (id) => api.put(`procurement/pembelian/${id}/kembalikan`),
    },

    pembayaran: {
        daftar: (params) => api.get('procurement/pembayaran', params),
        detail: (id) => api.get(`procurement/pembayaran/${id}`),
        buat: (id, data) => api.post(`procurement/pembayaran/${id}`, data),
        ubah: (id, bayarId, data) => api.put(`procurement/pembayaran/${id}/${bayarId}`, data),
        hapus: (id, bayarId) => api.del(`procurement/pembayaran/${id}/${bayarId}`),
        konfirmasi: (id, bayarId, peran, setuju) =>
            api.put(`procurement/pembayaran/${id}/${bayarId}/konfirmasi`, { peran, setuju }),
        lanjut: (id) => api.put(`procurement/pembayaran/${id}/lanjut`),
        tolak: (id) => api.put(`procurement/pembayaran/${id}/tolak`),
        lampiran: {
            unggah: (id, bayarId, berkas, namaDokumen) => {
                const form = new FormData();
                form.append('berkas', fileDari(berkas, namaDokumen || 'bukti'));
                if (namaDokumen) form.append('nama_dokumen', namaDokumen);
                return api.upload(`procurement/pembayaran/${id}/${bayarId}/lampiran`, form);
            },
            hapus: (id, bayarId, fileId) => api.del(`procurement/pembayaran/${id}/${bayarId}/lampiran/${fileId}`),
        },
    },

    ms2: {
        daftar: (params) => api.get('procurement/ms2', params),
        kandidat: () => api.get('procurement/ms2/kandidat'),
        detail: (id) => api.get(`procurement/ms2/${id}`),
        buat: (data) => api.post('procurement/ms2', data),
        hapus: (id) => api.del(`procurement/ms2/${id}`),
        kirim: (id, data) => api.post(`procurement/ms2/${id}/kirim`, data),
    },

    delivery: {
        daftar: (params) => api.get('procurement/delivery', params),
        detail: (id) => api.get(`procurement/delivery/${id}`),
        ubah: (id, data) => api.put(`procurement/delivery/${id}`, data),
        konfirmasi: (id, peran, setuju) => api.put(`procurement/delivery/${id}/konfirmasi`, { peran, setuju }),
        selesai: (id) => api.put(`procurement/delivery/${id}/selesai`),
        lo: {
            unggah: (id, { detail_rencana_pembelian_id, no_faktur, no_lo, berkas }) => {
                const form = new FormData();
                form.append('detail_rencana_pembelian_id', detail_rencana_pembelian_id);
                form.append('no_faktur', no_faktur);
                form.append('no_lo', no_lo);
                form.append('berkas', fileDari(berkas, 'lo'));
                return api.upload(`procurement/delivery/${id}/lo`, form);
            },
            hapus: (id, loId) => api.del(`procurement/delivery/${id}/lo/${loId}`),
        },
    },
};

export const Ping = () => request('ping');

/** Ubah hasil expo-image-picker menjadi bagian FormData yang dimengerti RN. */
function fileDari(asset, namaDasar) {
    const uri = typeof asset === 'string' ? asset : asset.uri;
    const ext = (uri.split('.').pop() || 'jpg').split('?')[0];
    return {
        uri,
        name: `${namaDasar}.${ext}`,
        type: asset?.mimeType || `image/${ext === 'jpg' ? 'jpeg' : ext}`,
    };
}

function appendDevice(form, device) {
    if (!device) return;
    Object.entries(device).forEach(([key, value]) => {
        if (value !== undefined && value !== null && value !== '') {
            form.append(`device[${key}]`, String(value));
        }
    });
}
