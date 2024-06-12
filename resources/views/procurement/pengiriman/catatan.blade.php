@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Detail Pengiriman</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.procurement') }}">Procurement</a></div>
        <div class="breadcrumb-item"><a href="{{ route('pengiriman.index') }}">Pengiriman</a></div>
        <div class="breadcrumb-item">Detail Pengiriman</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Detail Pengiriman</h2>
    <p class="section-lead">
        Detail Pengiriman anda.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Detail Pengiriman') }}</h4>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <form action="{{ route('pengiriman.update_catatan', $pengiriman->pengiriman_id) }}" method="post"> @csrf @method('put')
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row mb-3 justify-content-center">
                                    <label for="catatan" class="col-md-4 col-form-label text-md-end">{{ __('Catatan')
                                        }}</label>

                                    <div class="col-md-6">
                                        <textarea name="catatan" class="form-control" id="catatan" cols="30" rows="10">
                                            {{ $pengiriman->catatan }}
                                        </textarea>
                                    </div>
                                </div>
                                <div class="row mb-0">
                                    <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                        <a type="button" href="{{ route('pengiriman.show', $pengiriman->pengiriman_id) }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
                                        <button type="submit" class="btn btn-success"><i class="fas fa fa-pen"></i> Simpan</button>
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
