@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Import Excel</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.konfigurasi') }}">Konfigurasi</a></div>
        <div class="breadcrumb-item">Import Excel</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Import Excel</h2>
    <p class="section-lead">
        Import Excel anda.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Import Excel') }}</h4>
                    <div class="card-header-action">
                        <button class="btn btn-primary">Unduh Template</button>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <form action="{{ route('import.store') }}" method="POST">@csrf
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row mb-3 justify-content-center">
                                    <label for="jenis_file" class="col-md-4 col-form-label text-md-end">{{ __('Jenis File Upload')
                                        }}</label>

                                    <div class="col-md-6">
                                        <select class="custom-select @error('jenis_file') is-invalid @enderror" name="jenis_file" id="jenis_file">
                                            @foreach(['Penerimaan BBM', 'Stok Taking', 'Totalisator', 'Penarikan'] as $s)
                                            <option {{ old('jenis_file') == $s ? 'selected' : '' }} value="{{ $s }}">{{ $s }}</option>
                                            @endforeach
                                        </select>

                                        @error('jenis_file')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="file_excel" class="col-md-4 col-form-label text-md-end">{{
                                        __('File')
                                        }}</label>

                                    <div class="col-md-6">
                                        <div class="custom-file">
                                            <input type="file" name="file_excel" class="custom-file-input" id="file_excel">
                                            <label class="custom-file-label" for="file_excel">Pilih File Excel</label>
                                        </div>

                                        @error('file_excel')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-0">
                                    <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                        <a type="button" href="{{ route('menu.konfigurasi') }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
                                        <button type="submit" title="Import Data" class="btn btn-success"><i class="fas fa-paperline"></i> Import</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
