<?php

namespace App\Http\Controllers;

use App\Exports\TemplateTransaksiExport;
use App\Imports\Transaksi\ImporterRegistry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class ImportController extends Controller
{
    public function index()
    {
        $jenis = ImporterRegistry::jenisTersedia();

        return Inertia::render('Form/Index', [
            'title' => 'Import Excel',
            'breadcrumbs' => [['label' => 'Konfigurasi', 'url' => route('menu.konfigurasi')]],
            'backUrl' => route('menu.konfigurasi'),
            'method' => 'post',
            'action' => route('import.store'),
            // Tombol unduh template per jenis (ditampilkan di atas form).
            'downloads' => collect($jenis)->map(fn ($j) => [
                'label' => 'Template ' . $j,
                'url' => route('import.template', ['jenis' => $j]),
            ])->all(),
            'fields' => [
                [
                    'name' => 'jenis_file',
                    'label' => 'Jenis File',
                    'type' => 'select',
                    'value' => null,
                    'options' => collect($jenis)->map(fn ($s) => ['value' => $s, 'label' => $s])->all(),
                    'hint' => 'Unduh template sesuai jenis di atas, isi sheet "Data" (lihat sheet "Referensi" untuk nama yang valid), lalu unggah di sini.',
                ],
                [
                    'name' => 'file_excel',
                    'label' => 'File Excel',
                    'type' => 'file',
                    'value' => null,
                    'accept' => '.xlsx,.xls,.csv',
                    'hint' => 'Format yang didukung: .xlsx, .xls, .csv',
                ],
            ],
        ]);
    }

    public function template(Request $request)
    {
        $jenis = (string) $request->query('jenis', '');
        if (! ImporterRegistry::support($jenis)) {
            abort(404);
        }

        $nama = 'template-' . Str::slug($jenis) . '.xlsx';

        return Excel::download(new TemplateTransaksiExport($jenis), $nama);
    }

    public function store(Request $request)
    {
        $request->validate([
            'jenis_file' => ['required'],
            'file_excel' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:' . \App\Support\Upload::maxKb()],
        ]);

        $jenis = $request->jenis_file;
        if (! ImporterRegistry::support($jenis)) {
            return back()->with('msg', $this->alert('danger', 'Gagal!', "Jenis import \"$jenis\" belum didukung."));
        }

        $sheets = Excel::toArray(null, $request->file('file_excel'));
        $rows = $sheets[0] ?? [];
        if (count($rows) <= 1) {
            return back()->with('msg', $this->alert('warning', 'Kosong!', 'File tidak memiliki baris data.'));
        }

        $result = ImporterRegistry::make($jenis)->import($rows, Auth::user()->member_id);

        if (! empty($result['errors'])) {
            $list = collect($result['errors'])->take(20)->implode('</li><li>');
            $extra = count($result['errors']) > 20 ? '<li>… dan ' . (count($result['errors']) - 20) . ' error lainnya</li>' : '';

            return back()->with('msg', $this->alert(
                'danger',
                'Import dibatalkan!',
                'Tidak ada data yang disimpan karena ada kesalahan:<ul><li>' . $list . '</li>' . $extra . '</ul>'
            ));
        }

        return redirect()->route('import.index')->with('msg', $this->alert(
            'success',
            'Sukses!',
            $result['inserted'] . ' baris ' . $jenis . ' berhasil diimpor.'
        ));
    }

    private function alert(string $type, string $strong, string $body): string
    {
        return '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert"><strong>' . $strong . '</strong> ' . $body . '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
    }
}
