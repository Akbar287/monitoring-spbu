// Definisi seluruh resource yang bisa dikelola dari aplikasi:
// sembilan resource Master Data + tujuh resource Konfigurasi.
//
// Satu tempat ini yang menentukan isi daftar, form, dan halaman detail —
// layar Kelola Data bersifat generik dan hanya membaca berkas ini. Menambah
// resource baru cukup dengan menambah satu entri (dan endpoint di backend).
//
// Tipe field yang didukung MasterFormScreen:
//   text · textarea · number · uang · date · time · select · multiselect ·
//   switch · password
// `opsi` menunjuk kunci pada respons GET /opsi.

export const MASTER = {
    aset: {
        endpoint: 'master/aset',
        modul: 'master',
        label: 'Aset',
        ikon: 'cube-outline',
        warna: 'indigo',
        deskripsi: 'Inventaris & penyusutan',
        lampiran: true,
        fields: [
            { name: 'spbu_id', label: 'SPBU', type: 'select', opsi: 'spbu', required: true },
            { name: 'nama_aset', label: 'Nama Aset', type: 'text', required: true },
            { name: 'jumlah', label: 'Jumlah', type: 'number', required: true, default: '1' },
            { name: 'harga', label: 'Harga Perolehan', type: 'uang', required: true },
            { name: 'penyusutan_per_hari', label: 'Penyusutan per Hari', type: 'uang', required: true, hint: 'Dipakai jurnal penyusutan bulanan.' },
            { name: 'tanggal_digunakan', label: 'Tanggal Digunakan', type: 'date', required: true },
            { name: 'is_digunakan', label: 'Sedang digunakan', type: 'switch', default: true },
            { name: 'keterangan', label: 'Keterangan', type: 'textarea' },
        ],
        detail: [
            { label: 'SPBU', key: 'spbu' },
            { label: 'Jumlah', key: 'jumlah' },
            { label: 'Harga perolehan', key: 'harga', format: 'uang' },
            { label: 'Penyusutan/hari', key: 'penyusutan_per_hari', format: 'uang' },
            { label: 'Tanggal digunakan', key: 'tanggal_digunakan', format: 'tanggal' },
            { label: 'Status', key: 'is_digunakan', format: 'ya_tidak', ya: 'Digunakan', tidak: 'Tidak digunakan' },
            { label: 'Keterangan', key: 'keterangan' },
            { label: 'Lampiran', key: 'jumlah_lampiran', format: 'berkas' },
        ],
    },

    fasilitas: {
        endpoint: 'master/fasilitas',
        modul: 'master',
        label: 'Fasilitas',
        ikon: 'business-outline',
        warna: 'biru',
        deskripsi: 'Sarana pendukung SPBU',
        lampiran: true,
        fields: [
            { name: 'spbu_id', label: 'SPBU', type: 'select', opsi: 'spbu', required: true },
            { name: 'nama_fasilitas', label: 'Nama Fasilitas', type: 'text', required: true },
            { name: 'jumlah', label: 'Jumlah', type: 'number', required: true, default: '1' },
            { name: 'tanggal_digunakan', label: 'Tanggal Digunakan', type: 'date', required: true },
            { name: 'keterangan', label: 'Keterangan', type: 'textarea' },
        ],
        detail: [
            { label: 'SPBU', key: 'spbu' },
            { label: 'Jumlah', key: 'jumlah' },
            { label: 'Tanggal digunakan', key: 'tanggal_digunakan', format: 'tanggal' },
            { label: 'Keterangan', key: 'keterangan' },
            { label: 'Lampiran', key: 'jumlah_lampiran', format: 'berkas' },
        ],
    },

    produk: {
        endpoint: 'master/produk',
        modul: 'master',
        label: 'Produk',
        ikon: 'water-outline',
        warna: 'teal',
        deskripsi: 'Jenis BBM yang dijual',
        fields: [
            { name: 'spbu_id', label: 'SPBU', type: 'select', opsi: 'spbu', required: true },
            { name: 'nama_produk', label: 'Nama Produk', type: 'text', required: true, maxLength: 64 },
            { name: 'singkatan', label: 'Singkatan', type: 'text', maxLength: 4, hint: 'Maksimal 4 karakter, mis. PL.' },
            { name: 'nama_jenis', label: 'Jenis', type: 'text', required: true },
            { name: 'oktan', label: 'Oktan (RON)', type: 'number', required: true },
            { name: 'is_aktif', label: 'Aktif', type: 'switch', default: true },
        ],
        detail: [
            { label: 'SPBU', key: 'spbu' },
            { label: 'Singkatan', key: 'singkatan' },
            { label: 'Jenis', key: 'nama_jenis' },
            { label: 'Oktan', key: 'oktan' },
            { label: 'Harga jual', key: 'harga_jual', format: 'uang' },
            { label: 'Harga beli', key: 'harga_beli', format: 'uang' },
            { label: 'Stok', key: 'stok', format: 'liter' },
            { label: 'Status', key: 'is_aktif', format: 'ya_tidak', ya: 'Aktif', tidak: 'Nonaktif' },
        ],
    },

    harga: {
        endpoint: 'master/harga',
        modul: 'master',
        label: 'Harga',
        ikon: 'pricetag-outline',
        warna: 'hijau',
        deskripsi: 'Harga jual & beli berlaku',
        fields: [
            { name: 'produk_id', label: 'Produk', type: 'select', opsi: 'produk', required: true },
            { name: 'jam_kerja_id', label: 'Jam Kerja', type: 'select', opsi: 'jam_kerja', required: true },
            { name: 'tanggal', label: 'Tanggal Berlaku', type: 'date', required: true },
            { name: 'harga_jual', label: 'Harga Jual', type: 'uang', required: true },
            { name: 'harga_beli', label: 'Harga Beli', type: 'uang', required: true, hint: 'Dipakai sebagai dasar HPP saat BBM terjual.' },
        ],
        catatanForm: 'Harga baru otomatis menjadi harga berlaku; harga lama disimpan sebagai riwayat karena masih dirujuk transaksi penjualan.',
        detail: [
            { label: 'Produk', key: 'produk' },
            { label: 'Jam kerja', key: 'jam_kerja' },
            { label: 'Tanggal', key: 'tanggal', format: 'tanggal' },
            { label: 'Harga jual', key: 'harga_jual', format: 'uang' },
            { label: 'Harga beli', key: 'harga_beli', format: 'uang' },
            { label: 'Margin', key: 'margin', format: 'uang' },
            { label: 'Berlaku', key: 'is_default', format: 'ya_tidak', ya: 'Ya', tidak: 'Riwayat' },
        ],
    },

    dombak: {
        endpoint: 'master/dombak',
        modul: 'master',
        label: 'Dombak',
        ikon: 'server-outline',
        warna: 'oranye',
        deskripsi: 'Tangki penyimpanan BBM',
        fields: [
            { name: 'spbu_id', label: 'SPBU', type: 'select', opsi: 'spbu', required: true },
            { name: 'nama_dombak', label: 'Nama Dombak', type: 'text', required: true, maxLength: 16 },
            { name: 'keterangan', label: 'Keterangan', type: 'textarea' },
            { name: 'is_aktif', label: 'Aktif', type: 'switch', default: true },
        ],
        detail: [
            { label: 'SPBU', key: 'spbu' },
            { label: 'Stok saat ini', key: 'stok', format: 'liter' },
            { label: 'Baris konversi', key: 'jumlah_konversi' },
            { label: 'Payung', key: 'payung', format: 'daftar' },
            { label: 'Keterangan', key: 'keterangan' },
            { label: 'Status', key: 'is_aktif', format: 'ya_tidak', ya: 'Aktif', tidak: 'Nonaktif' },
        ],
    },

    payung: {
        endpoint: 'master/payung',
        modul: 'master',
        label: 'Payung',
        ikon: 'umbrella-outline',
        warna: 'pink',
        deskripsi: 'Area pengisian',
        fields: [
            { name: 'spbu_id', label: 'SPBU', type: 'select', opsi: 'spbu', required: true },
            { name: 'nama_payung', label: 'Nama Payung', type: 'text', required: true, maxLength: 16 },
            { name: 'keterangan', label: 'Keterangan', type: 'textarea' },
            { name: 'is_aktif', label: 'Aktif', type: 'switch', default: true },
        ],
        detail: [
            { label: 'SPBU', key: 'spbu' },
            { label: 'Dispenser', key: 'dispenser', format: 'daftar' },
            { label: 'Dombak', key: 'dombak', format: 'daftar' },
            { label: 'Keterangan', key: 'keterangan' },
            { label: 'Status', key: 'is_aktif', format: 'ya_tidak', ya: 'Aktif', tidak: 'Nonaktif' },
        ],
    },

    dispenser: {
        endpoint: 'master/dispenser',
        modul: 'master',
        label: 'Dispenser',
        ikon: 'hardware-chip-outline',
        warna: 'ungu',
        deskripsi: 'Mesin pengisian',
        fields: [
            { name: 'payung_id', label: 'Payung', type: 'select', opsi: 'payung', required: true },
            { name: 'nama_dispenser', label: 'Nama Dispenser', type: 'text', required: true, maxLength: 16 },
            { name: 'keterangan', label: 'Keterangan', type: 'textarea' },
            { name: 'is_aktif', label: 'Aktif', type: 'switch', default: true },
        ],
        detail: [
            { label: 'Payung', key: 'payung' },
            { label: 'Nozzle', key: 'nozzle', format: 'daftar' },
            { label: 'Keterangan', key: 'keterangan' },
            { label: 'Status', key: 'is_aktif', format: 'ya_tidak', ya: 'Aktif', tidak: 'Nonaktif' },
        ],
    },

    nozzle: {
        endpoint: 'master/nozzle',
        modul: 'master',
        label: 'Nozzle',
        ikon: 'git-branch-outline',
        warna: 'merah',
        deskripsi: 'Selang per produk',
        fields: [
            { name: 'dispenser_id', label: 'Dispenser', type: 'select', opsi: 'dispenser', required: true },
            { name: 'produk_id', label: 'Produk', type: 'select', opsi: 'produk', required: true },
            { name: 'nama_nozzle', label: 'Nama Nozzle', type: 'text', required: true, maxLength: 16 },
            { name: 'keterangan', label: 'Keterangan', type: 'textarea' },
            { name: 'is_aktif', label: 'Aktif', type: 'switch', default: true },
        ],
        detail: [
            { label: 'Produk', key: 'produk' },
            { label: 'Dispenser', key: 'dispenser' },
            { label: 'Payung', key: 'payung' },
            { label: 'Keterangan', key: 'keterangan' },
            { label: 'Status', key: 'is_aktif', format: 'ya_tidak', ya: 'Aktif', tidak: 'Nonaktif' },
        ],
    },

    member: {
        endpoint: 'master/member',
        modul: 'master',
        label: 'Pegawai',
        ikon: 'people-outline',
        warna: 'biru',
        deskripsi: 'Data pegawai & akun login',
        foto: true,      // punya avatar
        relasi: true,    // punya pengelolaan jabatan & SPBU
        fields: [
            { name: 'nama', label: 'Nama Lengkap', type: 'text', required: true, maxLength: 128 },
            { name: 'nik', label: 'NIK', type: 'text', required: true, maxLength: 16, keyboard: 'number-pad' },
            { name: 'jenis_kelamin', label: 'Jenis Kelamin', type: 'select', opsi: 'jenis_kelamin', required: true },
            { name: 'tempat_lahir', label: 'Tempat Lahir', type: 'text', required: true },
            { name: 'tanggal_lahir', label: 'Tanggal Lahir', type: 'date', required: true },
            { name: 'verified', label: 'KTP terverifikasi', type: 'switch', default: false },
            { name: 'status_member_id', label: 'Status Pegawai', type: 'select', opsi: 'status_member', required: true },
            { name: 'email', label: 'Email', type: 'text', required: true, keyboard: 'email-address' },
            { name: 'no_hp', label: 'No. HP', type: 'text', keyboard: 'phone-pad' },
            { name: 'no_wa', label: 'No. WhatsApp', type: 'text', keyboard: 'phone-pad' },
            { name: 'bergabung_sejak', label: 'Bergabung Sejak', type: 'date', required: true },
            { name: 'username', label: 'Username', type: 'text', required: true, maxLength: 32, autoCapitalize: 'characters' },
            { name: 'password', label: 'Password', type: 'password', hint: 'Minimal 8 karakter. Kosongkan saat mengubah bila tidak ingin mengganti.' },
            { name: 'is_aktif', label: 'Akun aktif', type: 'switch', default: true },
            { name: 'jabatan_id', label: 'Jabatan Awal', type: 'select', opsi: 'jabatan', hanyaSaatBuat: true },
            { name: 'spbu_id', label: 'Penempatan SPBU', type: 'select', opsi: 'spbu', hanyaSaatBuat: true },
        ],
        detail: [
            { label: 'NIK', key: 'nik' },
            { label: 'Jenis kelamin', key: 'jenis_kelamin' },
            { label: 'Tempat, tgl lahir', key: 'tempat_lahir' },
            { label: 'Tanggal lahir', key: 'tanggal_lahir', format: 'tanggal' },
            { label: 'KTP terverifikasi', key: 'verified', format: 'ya_tidak' },
            { label: 'Status pegawai', key: 'status_member' },
            { label: 'Email', key: 'email' },
            { label: 'No. HP', key: 'no_hp' },
            { label: 'No. WhatsApp', key: 'no_wa' },
            { label: 'Bergabung', key: 'bergabung_sejak', format: 'tanggal' },
            { label: 'Username', key: 'username' },
            { label: 'Akun aktif', key: 'is_aktif', format: 'ya_tidak' },
        ],
    },
};

// ---------------------------------------------------------------------------
// Konfigurasi — struktur organisasi, shift, hari kerja, dan tarif pajak.
// Perubahan di sini berdampak lintas modul: level menentukan hak akses,
// jam kerja menjadi acuan absensi dan seluruh transaksi harian.
// ---------------------------------------------------------------------------

export const KONFIGURASI = {
    spbu: {
        endpoint: 'konfigurasi/spbu',
        modul: 'konfigurasi',
        label: 'SPBU',
        ikon: 'business-outline',
        warna: 'indigo',
        deskripsi: 'Identitas stasiun',
        fields: [
            { name: 'nama_spbu', label: 'Nama SPBU', type: 'text', required: true },
            { name: 'nomor_spbu', label: 'Nomor SPBU', type: 'text', required: true, maxLength: 32 },
            { name: 'tanggal_pendirian', label: 'Tanggal Pendirian', type: 'date', required: true },
            { name: 'luas_lahan', label: 'Luas Lahan', type: 'uang', required: true },
            { name: 'satuan_luas', label: 'Satuan Luas', type: 'select', opsi: 'satuan_luas', required: true },
            { name: 'alamat', label: 'Alamat', type: 'textarea', required: true },
        ],
        detail: [
            { label: 'Nomor SPBU', key: 'nomor_spbu' },
            { label: 'Tanggal pendirian', key: 'tanggal_pendirian', format: 'tanggal' },
            { label: 'Luas lahan', key: 'luas_lahan' },
            { label: 'Satuan', key: 'satuan_luas' },
            { label: 'Alamat', key: 'alamat' },
            { label: 'Divisi', key: 'jumlah_divisi' },
            { label: 'Produk', key: 'jumlah_produk' },
            { label: 'Dombak', key: 'jumlah_dombak' },
            { label: 'Jam kerja', key: 'jumlah_jam_kerja' },
        ],
    },

    divisi: {
        endpoint: 'konfigurasi/divisi',
        modul: 'konfigurasi',
        label: 'Divisi',
        ikon: 'git-network-outline',
        warna: 'ungu',
        deskripsi: 'Pembagian unit kerja',
        fields: [
            { name: 'spbu_id', label: 'SPBU', type: 'select', opsi: 'spbu', required: true },
            { name: 'nama_divisi', label: 'Nama Divisi', type: 'text', required: true, maxLength: 64 },
            { name: 'keterangan', label: 'Keterangan', type: 'textarea' },
            { name: 'is_aktif', label: 'Aktif', type: 'switch', default: true },
        ],
        detail: [
            { label: 'SPBU', key: 'spbu' },
            { label: 'Level di bawahnya', key: 'level', format: 'daftar' },
            { label: 'Keterangan', key: 'keterangan' },
            { label: 'Status', key: 'is_aktif', format: 'ya_tidak', ya: 'Aktif', tidak: 'Nonaktif' },
        ],
    },

    level: {
        endpoint: 'konfigurasi/level',
        modul: 'konfigurasi',
        label: 'Level',
        ikon: 'layers-outline',
        warna: 'pink',
        deskripsi: 'Tingkatan & hak akses',
        catatanForm: 'Nama level menentukan hak akses: "Penanggung Jawab", "Komisaris", "Direktur Utama", "Direktur", "Operator", dan "Keamanan" dikenali khusus oleh sistem. Nama lain hanya mendapat menu dasar.',
        fields: [
            { name: 'divisi_id', label: 'Divisi', type: 'select', opsi: 'divisi', required: true },
            { name: 'nama_level', label: 'Nama Level', type: 'text', required: true, maxLength: 64 },
            { name: 'keterangan', label: 'Keterangan', type: 'textarea' },
            { name: 'is_aktif', label: 'Aktif', type: 'switch', default: true },
        ],
        detail: [
            { label: 'Divisi', key: 'divisi' },
            { label: 'Jabatan di bawahnya', key: 'jabatan', format: 'daftar' },
            { label: 'Keterangan', key: 'keterangan' },
            { label: 'Status', key: 'is_aktif', format: 'ya_tidak', ya: 'Aktif', tidak: 'Nonaktif' },
        ],
    },

    jabatan: {
        endpoint: 'konfigurasi/jabatan',
        modul: 'konfigurasi',
        label: 'Jabatan',
        ikon: 'ribbon-outline',
        warna: 'teal',
        deskripsi: 'Posisi pegawai',
        fields: [
            { name: 'level_id', label: 'Level', type: 'select', opsi: 'level', required: true },
            { name: 'nama_jabatan', label: 'Nama Jabatan', type: 'text', required: true, maxLength: 64 },
            { name: 'keterangan', label: 'Keterangan', type: 'textarea' },
            { name: 'is_aktif', label: 'Aktif', type: 'switch', default: true },
        ],
        detail: [
            { label: 'Level', key: 'level' },
            { label: 'Divisi', key: 'divisi' },
            { label: 'Jumlah pegawai', key: 'jumlah_pegawai' },
            { label: 'Keterangan', key: 'keterangan' },
            { label: 'Status', key: 'is_aktif', format: 'ya_tidak', ya: 'Aktif', tidak: 'Nonaktif' },
        ],
    },

    hari: {
        endpoint: 'konfigurasi/hari',
        modul: 'konfigurasi',
        label: 'Hari',
        ikon: 'today-outline',
        warna: 'hijau',
        deskripsi: 'Hari kerja & libur',
        fields: [
            { name: 'nama_hari', label: 'Nama Hari', type: 'text', required: true, maxLength: 32 },
            { name: 'hari_kerja', label: 'Termasuk hari kerja', type: 'switch', default: true },
            { name: 'keterangan', label: 'Keterangan', type: 'text', maxLength: 255 },
        ],
        detail: [
            { label: 'Hari kerja', key: 'hari_kerja', format: 'ya_tidak' },
            { label: 'Keterangan', key: 'keterangan' },
            { label: 'Dipakai jam kerja', key: 'jam_kerja', format: 'daftar' },
        ],
    },

    'jam-kerja': {
        endpoint: 'konfigurasi/jam-kerja',
        modul: 'konfigurasi',
        label: 'Jam Kerja',
        ikon: 'time-outline',
        warna: 'oranye',
        deskripsi: 'Shift & batas absensi',
        catatanForm: 'Jam kerja menjadi acuan absensi, harga per shift, dan seluruh dokumen stok. Mengubahnya memengaruhi transaksi yang akan datang.',
        fields: [
            { name: 'spbu_id', label: 'SPBU', type: 'select', opsi: 'spbu', required: true },
            { name: 'nama_jam_kerja', label: 'Nama Shift', type: 'text', required: true, maxLength: 32 },
            { name: 'urutan', label: 'Urutan', type: 'number', required: true, default: '1', hint: 'Menentukan posisi shift dalam sehari.' },
            { name: 'batas_awal_in', label: 'Batas Awal Absen Masuk', type: 'time', required: true },
            { name: 'jam_datang_in', label: 'Jam Datang', type: 'time', required: true },
            { name: 'batas_telat', label: 'Batas Telat', type: 'time', required: true },
            { name: 'batas_akhir_in', label: 'Batas Akhir Absen Masuk', type: 'time', required: true },
            { name: 'masuk_istirahat', label: 'Mulai Istirahat', type: 'time', required: true },
            { name: 'jam_istirahat', label: 'Selesai Istirahat', type: 'time', required: true },
            { name: 'jam_pulang_out', label: 'Jam Pulang', type: 'time', required: true },
            { name: 'akhir_out', label: 'Batas Akhir Absen Pulang', type: 'time', required: true },
            { name: 'hari_ids', label: 'Berlaku pada Hari', type: 'multiselect', opsi: 'hari', hint: 'Opsional — relasi ini belum dipakai logika operasional mana pun.' },
        ],
        detail: [
            { label: 'SPBU', key: 'spbu' },
            { label: 'Urutan', key: 'urutan' },
            { label: 'Batas awal masuk', key: 'batas_awal_in' },
            { label: 'Jam datang', key: 'jam_datang_in' },
            { label: 'Batas telat', key: 'batas_telat' },
            { label: 'Batas akhir masuk', key: 'batas_akhir_in' },
            { label: 'Mulai istirahat', key: 'masuk_istirahat' },
            { label: 'Selesai istirahat', key: 'jam_istirahat' },
            { label: 'Jam pulang', key: 'jam_pulang_out' },
            { label: 'Batas akhir pulang', key: 'akhir_out' },
            { label: 'Hari', key: 'hari', format: 'daftar' },
        ],
    },

    pajak: {
        endpoint: 'konfigurasi/pajak',
        modul: 'konfigurasi',
        label: 'Tarif Pajak',
        ikon: 'calculator-outline',
        warna: 'merah',
        deskripsi: 'PPN, PPBKB, PPh pembelian',
        catatanForm: 'Hanya boleh ada satu tarif aktif. Mengaktifkan tarif ini otomatis menonaktifkan tarif lain, dan tarif aktif itulah yang dipakai seluruh perhitungan pembelian BBM.',
        fields: [
            { name: 'ppn', label: 'PPN (%)', type: 'uang', required: true },
            { name: 'ppbkb', label: 'PPBKB (%)', type: 'uang', required: true },
            { name: 'pph', label: 'PPh 22 (%)', type: 'uang', required: true },
            { name: 'is_aktif', label: 'Jadikan tarif berlaku', type: 'switch', default: true },
        ],
        detail: [
            { label: 'PPN', key: 'ppn' },
            { label: 'PPBKB', key: 'ppbkb' },
            { label: 'PPh 22', key: 'pph' },
            { label: 'Status', key: 'is_aktif', format: 'ya_tidak', ya: 'Tarif berlaku', tidak: 'Tidak aktif' },
            { label: 'Dipakai faktur', key: 'dipakai_pembelian' },
        ],
    },
};

/** Seluruh resource yang dikenal layar generik, digabung dari kedua modul. */
export const REGISTRY = { ...MASTER, ...KONFIGURASI };

/** Pengelompokan untuk layar beranda Kelola Data. */
export const GRUP = [
    {
        judul: 'Master Data',
        deskripsi: 'Data acuan operasional SPBU',
        items: Object.entries(MASTER).map(([key, konf]) => ({ key, ...konf })),
    },
    {
        judul: 'Konfigurasi',
        deskripsi: 'Struktur organisasi, shift, dan tarif',
        items: Object.entries(KONFIGURASI).map(([key, konf]) => ({ key, ...konf })),
    },
];

/** Field yang relevan untuk mode tertentu (beberapa hanya muncul saat membuat). */
export function fieldUntuk(key, mode) {
    return (REGISTRY[key]?.fields || []).filter((f) => !(f.hanyaSaatBuat && mode === 'ubah'));
}
