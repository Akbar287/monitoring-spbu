-- =====================================================================
-- Setara migrasi 2026_08_15_000001_create_pengaturan_sistem_table
-- untuk diinjeksikan langsung ke PostgreSQL produksi.
--
-- Aman dijalankan ulang (idempoten).
--
-- Blok terakhir menandai set up awal sebagai SELESAI bila database sudah
-- berisi data operasional — supaya instalasi yang sedang berjalan tidak
-- tiba-tiba mengunci admin di halaman /setup.
-- =====================================================================

BEGIN;

CREATE TABLE IF NOT EXISTS public.pengaturan_sistem (
    pengaturan_sistem_id CHARACTER(36)       NOT NULL,
    kunci                CHARACTER VARYING(64) NOT NULL,
    nilai                TEXT                NULL,
    created_at           TIMESTAMP(0) WITHOUT TIME ZONE NULL,
    updated_at           TIMESTAMP(0) WITHOUT TIME ZONE NULL,
    CONSTRAINT pengaturan_sistem_pkey PRIMARY KEY (pengaturan_sistem_id)
);

CREATE UNIQUE INDEX IF NOT EXISTS pengaturan_sistem_kunci_unique
    ON public.pengaturan_sistem USING btree (kunci);

-- Tandai set up selesai HANYA bila sistem memang sudah berisi data.
INSERT INTO public.pengaturan_sistem (pengaturan_sistem_id, kunci, nilai, created_at, updated_at)
SELECT gen_random_uuid()::text, 'setup_selesai_at', to_char(now(), 'YYYY-MM-DD HH24:MI:SS'), now(), now()
WHERE  EXISTS (SELECT 1 FROM public.spbu)
  AND  EXISTS (SELECT 1 FROM public.produk)
  AND  EXISTS (SELECT 1 FROM public.member)
ON CONFLICT (kunci) DO NOTHING;

INSERT INTO public.pengaturan_sistem (pengaturan_sistem_id, kunci, nilai, created_at, updated_at)
SELECT gen_random_uuid()::text, 'setup_dilewati', 'sistem sudah berisi data saat migrasi dijalankan', now(), now()
WHERE  EXISTS (SELECT 1 FROM public.pengaturan_sistem WHERE kunci = 'setup_selesai_at')
ON CONFLICT (kunci) DO NOTHING;

-- Catat migrasinya agar `php artisan migrate` tidak menjalankannya lagi.
INSERT INTO public.migrations (migration, batch)
SELECT '2026_08_15_000001_create_pengaturan_sistem_table', COALESCE((SELECT MAX(batch) FROM public.migrations), 0) + 1
WHERE NOT EXISTS (
    SELECT 1 FROM public.migrations
    WHERE migration = '2026_08_15_000001_create_pengaturan_sistem_table'
);

COMMIT;

-- Pemeriksaan setelah dijalankan:
--   SELECT kunci, nilai FROM pengaturan_sistem;
--   -> harus ada baris 'setup_selesai_at' pada instalasi yang sudah berjalan.
