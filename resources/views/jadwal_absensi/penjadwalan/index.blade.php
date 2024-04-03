@extends('layouts.app')

@section('content')

<div class="row justify-content-center">
    <div class="col-12 col-sm-12 col-md-4">
        <div class="card" style="min-height: 850px!important;overflow-x:hidden;overflow-y:scroll;">
            <div class="card-header">
                <h4>Pegawai</h4>
                <div class="card-header-action">
                    <a href="{{ url('jadwalkerja/qr') }}" class="btn btn-primary btn-sm">Kode QR</a>
                </div>
            </div>
            <div class="card-body">
                <ul class="list-unstyled list-unstyled-border  list-employee-schedule">
                    @foreach($employee as $user)
                    <li class="media">
                        <img alt="image" class="mr-3 rounded-circle" width="50" src="{{ asset('storage/profile_img/' . $user->avatar) }}">
                        <div class="media-body">
                            <div class="mt-0 mb-1 font-weight-bold"><a href="javascript:void(0)" data-user="{{ $user->member_id }}" data-name="{{ $user->ktp()->first()->nama }}" class="jadwal-kerja-href">{{ $user->ktp()->first()->nama }}</a></div>
                            @php $j = []; foreach($user->jabatan()->select('nama_jabatan')->get() as $ja) {$j[] = $ja->nama_jabatan;} @endphp
                            <div class="text-secondary text-small font-600-bold"><i class="fas fa-circle"></i> <span>{{ implode(', ', $j) }}</span></div>
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-12 col-md-8">
        <div class="card card-calendar" style="display:none;">
            <div class="card-header">
                <h4 id="title-calendar">Jadwal Kerja Pegawai</h4>
                <div class="card-header-action">
                    <button class="btn btn-primary btn-sm" data-user="" data-name="" id="add-schedule">Jadwalkan</button>
                </div>
            </div>
            <div class="card-body">
                <div class="fc-overflow">
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </div>
    <form class="modal-part" id="modal-create-schedule">
        <div class="form-group">
            <label>Nama Pegawai</label>
            <div class="input-group">
                <div class="input-group-prepend">
                    <div class="input-group-text"><i class="fas fa-user"></i></div>
                </div>
                <input type="hidden" readonly class="form-control" autocomplete="off" id="temp_jam_kerja" readonly value="@foreach($jamKerja as $jk) {{$jk->jam_kerja_id . '|' . $jk->nama_jam_kerja . '||'}} @endforeach " name="text" />
                <input type="hidden" readonly class="form-control" autocomplete="off" id="temp_nozzle" readonly value="@foreach($nozzle as $n){{ $n->nozzle_id . '|'.$n->nama_nozzle . '||' }}@endforeach" name="text" />

                <input type="hidden" readonly class="form-control input-employee-sch-id" autocomplete="off" readonly value="" name="text" />
                <input type="text" readonly class="form-control input-employee-sch-name" autocomplete="off" readonly value="" name="text" />
            </div>
        </div>

        <div class="form-group">
            <label>Jabatan</label>
            <div class="input-group">
                <div class="input-group-prepend">
                    <div class="input-group-text"><i class="fas fa-sitemap"></i></div>
                </div>
                <select name="jabatan" disabled class="custom-select input-jabatan-sch" id="jabatan">
                    @if($user->jabatan()->select('jabatan_id', 'nama_jabatan')->count() > 0)
                    @foreach($user->jabatan()->select('nama_jabatan')->get() as $jk)
                    <option value="{{ $jk->jabatan_id }}">{{ $jk->nama_jabatan }}</option>
                    @endforeach
                    @else
                    <option value="0">Tidak ada Jabatan di Pegawai {{ $user->ktp()->select('nama')->first()->nama }}</option>
                    @endif
                </select>
            </div>
        </div>

        <div class="setting-calendar">
            <div class="card card-primary wrapper" data-wrapper="1">
                <div class="card-header">
                    <h4>Kalendar #1</h4>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Tanggal Kerja</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text"><i class="fas fa-calendar"></i></div>
                            </div>
                            <input type="date" value="{{date('Y-m-d')}}" name="tanggal[]" class="form-control input-date-sch">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Jam Kerja</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text"><i class="fas fa-clock"></i></div>
                            </div>
                            <select name="jenis_kerja[]" @if(is_null($jamKerja)) disabled @endif class="custom-select input-jam_kerja-sch">
                                @if(!is_null($jamKerja))
                                @foreach($jamKerja as $jk)
                                <option value="{{ $jk->jam_kerja_id }}">{{ $jk->nama_jam_kerja }}</option>
                                @endforeach
                                @else
                                <option value="0">Tidak ada Jam Kerja</option>
                                @endif
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Nozzle</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text"><i class="fas fa-thermometer-full"></i></div>
                            </div>
                            <select name="nozzle_id[]" id="nozzle_id" class="custom-select input-nozzle-sch">
                                <option value="all">Semua Nozzle</option>
                                @foreach($nozzle as $n)
                                <option value="{{ $n->nozzle_id }}">{{ $n->nama_nozzle }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

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
    </form>
</div>
@endsection
