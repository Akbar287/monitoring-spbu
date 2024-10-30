@extends('layouts.app')

@section('content')

<div class="row justify-content-center">
    <div class="col-12 col-sm-12 col-md-12">
        <div class="card card-calendar">
            <div class="card-header">
                <h4 id="title-calendar">Kalendar Rangkuman Stok</h4>
                {{-- <div class="card-header-action">
                    <button class="btn btn-primary btn-sm mr-2" data-user="" data-name="" id="add-schedule">Jadwalkan</button>
                </div> --}}
            </div>
            <div class="card-body">
                <div class="fc-overflow">
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- <div class="col-12 col-sm-12 col-md-12">
        <div class="card card-secondary">
            <div class="card-header">
                <h4>Pilihan</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <button title="Hapus Pengaturan" type="button" id="remove_setting" class="btn btn-danger mr-2">Hapus Pengaturan</button>
                        <button title="Tambah Pengaturan" type="button" id="add_setting" class="btn btn-success">Tambah Pengaturan</button>
                    </div>
                </div>
            </div>
        </div>
    </div> --}}
</div>
@endsection
