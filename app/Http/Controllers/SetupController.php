<?php

namespace App\Http\Controllers;

use App\Models\PengaturanSistem;
use App\Models\Spbu;
use App\Support\Setup;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Halaman pengisian awal (/setup) — yang dibuka admin saat pertama kali
 * memakai aplikasi. Daftar langkah, prasyarat, dan syarat selesainya
 * didefinisikan di App\Support\Setup.
 *
 * Controller ini sengaja TIDAK membuat jalan pintas untuk mengisi master data
 * atau dokumen pengadaan. Ia hanya mengantar admin ke form asli tiap modul,
 * supaya validasi, jurnal, dan efek berantainya sama persis dengan operasi
 * normal. Satu-satunya data yang ditulis di sini adalah identitas SPBU dan
 * tanggal mulai operasi.
 */
class SetupController extends Controller
{
    public function index()
    {
        $langkah = Setup::langkah();

        return Inertia::render('Setup/Index', [
            'title' => 'Set Up Awal',
            'langkah' => $langkah,
            'kemajuan' => Setup::kemajuan($langkah),
            'berikutnya' => Setup::berikutnya($langkah),
            'sudahSelesai' => Setup::selesai(),
            'tanggalMulaiOperasi' => Setup::tanggalMulaiOperasi(),
            'urls' => [
                'identitas' => route('setup.identitas'),
                'selesaikan' => route('setup.selesaikan'),
                'bukaKembali' => route('setup.buka-kembali'),
                'home' => url('/home'),
            ],
        ]);
    }

    /**
     * Langkah pertama: identitas SPBU + tanggal mulai operasi.
     * Bila SPBU sudah ada, form ini mengubah SPBU pertama alih-alih
     * menambah baris baru — set up tidak boleh memperbanyak data.
     */
    public function identitas()
    {
        $spbu = Spbu::orderBy('created_at')->first();

        return Inertia::render('Form/Index', [
            'title' => 'Identitas SPBU & Tanggal Mulai Operasi',
            'breadcrumbs' => [['label' => 'Set Up Awal', 'url' => route('setup.index')]],
            'backUrl' => route('setup.index'),
            'method' => 'post',
            'action' => route('setup.identitas.simpan'),
            'fields' => [
                ['name' => 'nama_spbu', 'label' => 'Nama SPBU', 'type' => 'text', 'value' => $spbu?->nama_spbu],
                ['name' => 'nomor_spbu', 'label' => 'Nomor SPBU', 'type' => 'text', 'value' => $spbu?->nomor_spbu],
                ['name' => 'tanggal_pendirian', 'label' => 'Tanggal Pendirian', 'type' => 'date', 'value' => $spbu?->tanggal_pendirian],
                ['name' => 'luas_lahan', 'label' => 'Luas Lahan', 'type' => 'text', 'value' => $spbu?->luas_lahan, 'col' => 'col-md-6'],
                ['name' => 'satuan_luas', 'label' => 'Satuan Luas', 'type' => 'text', 'value' => $spbu?->satuan_luas, 'col' => 'col-md-6'],
                ['name' => 'alamat', 'label' => 'Alamat', 'type' => 'textarea', 'value' => $spbu?->alamat],
                [
                    'name' => 'tanggal_mulai_operasi',
                    'label' => 'Tanggal Mulai Operasi Aplikasi',
                    'type' => 'date',
                    'value' => Setup::tanggalMulaiOperasi(),
                    'hint' => 'Tanggal aplikasi mulai dipakai mencatat. Saldo awal akun dan stok awal dombak diisi untuk posisi pada tanggal ini.',
                ],
            ],
        ]);
    }

    public function simpanIdentitas(Request $request)
    {
        $data = $request->validate([
            'nama_spbu' => ['required', 'string'],
            'nomor_spbu' => ['required', 'string'],
            'tanggal_pendirian' => ['required', 'date'],
            'luas_lahan' => ['required'],
            'satuan_luas' => ['required', 'string'],
            'alamat' => ['required', 'string'],
            'tanggal_mulai_operasi' => ['required', 'date'],
        ], [], [
            'tanggal_mulai_operasi' => 'tanggal mulai operasi',
        ]);

        $spbu = Spbu::orderBy('created_at')->first();
        $atributSpbu = collect($data)->except('tanggal_mulai_operasi')->all();

        if ($spbu) {
            $spbu->update($atributSpbu);
        } else {
            Spbu::create($atributSpbu);
        }

        PengaturanSistem::simpan(Setup::KUNCI_MULAI_OPERASI, $data['tanggal_mulai_operasi']);

        return redirect()->route('setup.index')
            ->with('msg', self::pesan('Identitas SPBU dan tanggal mulai operasi tersimpan.'));
    }

    /**
     * Menutup set up. Ditolak selama masih ada langkah wajib yang belum beres
     * agar sistem tidak terlanjur dipakai dengan pondasi setengah jadi.
     */
    public function selesaikan()
    {
        $kemajuan = Setup::kemajuan();

        if (! $kemajuan['bisa_diselesaikan']) {
            $sisa = $kemajuan['wajib'] - $kemajuan['wajib_selesai'];

            return redirect()->route('setup.index')
                ->with('msg', self::pesan("Masih ada {$sisa} langkah wajib yang belum lengkap.", 'danger'));
        }

        Setup::tandaiSelesai();

        return redirect(url('/home'))
            ->with('msg', self::pesan('Set up awal selesai. Aplikasi siap dipakai.'));
    }

    /**
     * Membuka kembali halaman set up (mis. saat menambah SPBU kedua).
     * Tidak menghapus data apa pun — hanya menghidupkan lagi panduannya.
     */
    public function bukaKembali()
    {
        Setup::bukaKembali();

        return redirect()->route('setup.index')
            ->with('msg', self::pesan('Panduan set up dibuka kembali.'));
    }

    private static function pesan(string $teks, string $jenis = 'success'): string
    {
        $judul = $jenis === 'success' ? 'Sukses!' : 'Perhatian!';

        return '<div class="alert alert-' . $jenis . ' alert-dismissible fade show" role="alert">'
            . '<strong>' . $judul . '</strong> ' . e($teks)
            . '<button type="button" class="close" data-dismiss="alert" aria-label="Close">'
            . '<span aria-hidden="true">&times;</span></button></div>';
    }
}
