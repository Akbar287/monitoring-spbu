<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ImportController extends Controller
{
    public function index()
    {
        return view('konfigurasi/import/index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'jenis_file' => ['required'],
            'file_excel' => ['required']
        ]);

        return redirect('/konfigurasi/import/')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Excel sudah di import.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }
}
