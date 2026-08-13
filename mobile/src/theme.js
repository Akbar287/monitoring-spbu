// Palet mengikuti tema Stisla yang dipakai aplikasi web (primary #6777ef),
// diperluas dengan warna aksen per modul supaya tiap layar punya identitas
// visual sendiri tanpa kehilangan kesatuan.
export const colors = {
    primary: '#6777ef',
    primaryDark: '#4c5ad4',
    primarySoft: '#eef0ff',
    success: '#47c363',
    successSoft: '#e8f8ec',
    warning: '#ffa426',
    warningSoft: '#fff4e5',
    danger: '#fc544b',
    dangerSoft: '#ffecea',
    info: '#3abaf4',
    infoSoft: '#e6f6fe',

    text: '#1f2233',
    textMuted: '#7a7f9a',
    border: '#e6e9f4',
    background: '#f6f7fc',
    surface: '#ffffff',

    // di atas latar gradien berwarna
    atasGradien: '#ffffff',
    atasGradienRedup: 'rgba(255,255,255,0.82)',
};

/**
 * Aksen bernama — dipakai `tone` pada Card/Badge/Notice dan `warna` pada
 * Halaman. Tiap entri: gradien latar, warna solid, dan versi lembut.
 */
export const aksen = {
    indigo: { gradien: ['#6777ef', '#8f6ded'], solid: '#6777ef', lembut: '#eef0ff', teks: '#3f4bbf' },
    ungu: { gradien: ['#7d5fff', '#b06ab3'], solid: '#8b5cf6', lembut: '#f3edff', teks: '#6d3fd4' },
    biru: { gradien: ['#2f80ed', '#56ccf2'], solid: '#2f80ed', lembut: '#e8f2ff', teks: '#1b5fbe' },
    teal: { gradien: ['#0ba29d', '#38ef7d'], solid: '#12b3a8', lembut: '#e3fbf7', teks: '#0a7d76' },
    hijau: { gradien: ['#2eb872', '#7ed957'], solid: '#2eb872', lembut: '#e9f9ef', teks: '#1d7c4d' },
    oranye: { gradien: ['#f7971e', '#ffd200'], solid: '#f7971e', lembut: '#fff4e0', teks: '#b76a05' },
    merah: { gradien: ['#f45c43', '#eb3349'], solid: '#f0483e', lembut: '#ffecea', teks: '#c02c2c' },
    pink: { gradien: ['#e05297', '#f79ac0'], solid: '#e05297', lembut: '#ffeaf4', teks: '#b02a6e' },
    abu: { gradien: ['#8e9ab5', '#c2cbdd'], solid: '#8e9ab5', lembut: '#eef1f7', teks: '#5c6780' },
};

export const spacing = { xs: 4, sm: 8, md: 12, lg: 16, xl: 24, xxl: 32 };

export const radius = { sm: 8, md: 12, lg: 18, xl: 26, pill: 999 };

export const typography = {
    title: { fontSize: 22, fontWeight: '800', color: colors.text },
    heading: { fontSize: 16, fontWeight: '700', color: colors.text },
    body: { fontSize: 14, color: colors.text },
    muted: { fontSize: 13, color: colors.textMuted },
    caption: { fontSize: 12, color: colors.textMuted },
};

/** Bayangan lembut yang konsisten lintas platform. */
export const bayangan = {
    kartu: {
        shadowColor: '#1f2233',
        shadowOpacity: 0.06,
        shadowRadius: 12,
        shadowOffset: { width: 0, height: 4 },
        elevation: 2,
    },
    naik: {
        shadowColor: '#1f2233',
        shadowOpacity: 0.14,
        shadowRadius: 16,
        shadowOffset: { width: 0, height: 6 },
        elevation: 6,
    },
};
