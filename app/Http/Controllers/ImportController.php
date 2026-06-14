<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class ImportController extends Controller
{
    public function index()
    {
        return Inertia::render('Form/Index', [
            'title' => 'Import Excel',
            'breadcrumbs' => [['label' => 'Konfigurasi', 'url' => route('menu.konfigurasi')]],
            'backUrl' => route('menu.konfigurasi'),
            'method' => 'post',
            'action' => route('import.store'),
            'fields' => [
                [
                    'name' => 'jenis_file',
                    'label' => 'Jenis File',
                    'type' => 'select',
                    'value' => null,
                    'options' => collect(['Penerimaan BBM', 'Stok Taking', 'Totalisator', 'Penarikan'])
                        ->map(fn ($s) => ['value' => $s, 'label' => $s])->all(),
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

    public function store(Request $request)
    {
        $request->validate([
            'jenis_file' => ['required'],
            'file_excel' => ['required', 'file', 'max:' . \App\Support\Upload::maxKb()]
        ]);

        return redirect('/konfigurasi/import/')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Excel sudah di import.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }
}
