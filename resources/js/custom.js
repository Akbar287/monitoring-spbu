"use strict";

const { default: Swal } = require("sweetalert2");
import Cleave from "cleave.js"
import { Chart } from "chart.js/auto";
import Typed from "typed.js";
import QrScanner from 'qr-scanner';

$(function () {
    let routeName = ($('meta[name=route]')[0].content);
    let cdnDatatableIdn = 'https://cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json';

    console.log(routeName)
    if($('.thousand-style').toArray().length > 0) {
        $('.thousand-style').toArray().forEach(e => {
            new Cleave(e, {
                numeral: true,
                numeralThousandsGroupStyle: 'thousand',
                numeralDecimalScale: 6
            });
        });
    }
    if (document.location.pathname == '/konfigurasi/spbu') {
        $('.table-spbu').DataTable({
            language: {
                url: cdnDatatableIdn,
            },
            processing: true,
            serverSide: true,
            ajax: document.location.href,
            columns: [
                {data: 'nama_spbu', name: 'nama_spbu'},
                {data: 'nomor_spbu', name: 'nomor_spbu'},
                {data: 'tanggal_pendirian', name: 'tanggal_pendirian'},
                {data: 'alamat', name: 'alamat'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    }
    if (document.location.pathname == '/konfigurasi/divisi') {
        $('.table-divisi').DataTable({
            language: {
                url: cdnDatatableIdn,
            },
            processing: true,
            serverSide: true,
            ajax: document.location.href,
            columns: [
                {data: 'nama_divisi', name: 'nama_divisi'},
                {data: 'keterangan', name: 'keterangan'},
                {data: 'is_aktif', name: 'is_aktif'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    }
    if (document.location.pathname == '/konfigurasi/hari') {
        $('.table-hari').DataTable({
            language: {
                url: cdnDatatableIdn,
            },
            processing: true,
            serverSide: true,
            ajax: document.location.href,
            columns: [
                {data: 'nama_hari', name: 'nama_hari'},
                {data: 'hari_kerja', name: 'hari_kerja'},
                {data: 'keterangan', name: 'keterangan'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    }
    if (document.location.pathname == '/konfigurasi/jam_kerja') {
        $('.table-jam-kerja').DataTable({
            language: {
                url: cdnDatatableIdn,
            },
            processing: true,
            serverSide: true,
            ajax: document.location.href,
            columns: [
                {data: 'nama_jam_kerja', name: 'nama_jam_kerja'},
                {data: 'jam_datang_in', name: 'jam_datang_in'},
                {data: 'jam_pulang_out', name: 'jam_pulang_out'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    }
    if (document.location.pathname == '/master/aset') {
        $('.table-aset').DataTable({
            language: {
                url: cdnDatatableIdn,
            },
            processing: true,
            serverSide: true,
            ajax: document.location.href,
            columns: [
                {data: 'nama_aset', name: 'nama_aset'},
                {data: 'jumlah', name: 'jumlah'},
                {data: 'keterangan', name: 'keterangan'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    }
    if (document.location.pathname == '/master/fasilitas') {
        $('.table-fasilitas').DataTable({
            language: {
                url: cdnDatatableIdn,
            },
            processing: true,
            serverSide: true,
            ajax: document.location.href,
            columns: [
                {data: 'nama_fasilitas', name: 'nama_fasilitas'},
                {data: 'jumlah', name: 'jumlah'},
                {data: 'tanggal_digunakan', name: 'tanggal_digunakan'},
                {data: 'keterangan', name: 'keterangan'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });

    }
    if (document.location.pathname == '/master/produk') {
        $('.table-produk').DataTable({
            language: {
                url: cdnDatatableIdn,
            },
            processing: true,
            serverSide: true,
            ajax: document.location.href,
            columns: [
                {data: 'nama_produk', name: 'nama_produk'},
                {data: 'nama_jenis', name: 'nama_jenis'},
                {data: 'oktan', name: 'oktan'},
                {data: 'is_aktif', name: 'keterangan'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    }
    if (document.location.pathname == '/master/dombak') {
        $('.table-dombak').DataTable({
            language: {
                url: cdnDatatableIdn,
            },
            processing: true,
            serverSide: true,
            ajax: document.location.href,
            columns: [
                {data: 'nama_dombak', name: 'nama_dombak'},
                {data: 'is_aktif', name: 'is_aktif'},
                {data: 'keterangan', name: 'keterangan'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    }
    if (document.location.pathname == '/master/payung') {
        $('.table-payung').DataTable({
            language: {
                url: cdnDatatableIdn,
            },
            processing: true,
            serverSide: true,
            ajax: document.location.href,
            columns: [
                {data: 'nama_payung', name: 'nama_payung'},
                {data: 'is_aktif', name: 'is_aktif'},
                {data: 'keterangan', name: 'keterangan'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    }
    if (document.location.pathname == '/master/dispenser') {
        $('.table-dispenser').DataTable({
            language: {
                url: cdnDatatableIdn,
            },
            processing: true,
            serverSide: true,
            ajax: document.location.href,
            columns: [
                {data: 'nama_dispenser', name: 'nama_dispenser'},
                {data: 'nama_payung', name: 'nama_payung'},
                {data: 'is_aktif', name: 'is_aktif'},
                {data: 'keterangan', name: 'keterangan'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    }
    if (document.location.pathname == '/master/nozzle') {
        $('.table-nozzle').DataTable({
            language: {
                url: cdnDatatableIdn,
            },
            processing: true,
            serverSide: true,
            ajax: document.location.href,
            columns: [
                {data: 'nama_payung', name: 'nama_payung'},
                {data: 'nama_dispenser', name: 'nama_dispenser'},
                {data: 'nama_produk', name: 'nama_payung'},
                {data: 'nama_nozzle', name: 'nama_nozzle'},
                {data: 'is_aktif', name: 'is_aktif'},
                {data: 'keterangan', name: 'keterangan'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    }
    if (routeName == 'harga.index' || routeName == 'harga.riwayat.index') {
        $('.table-harga').DataTable({
            language: {
                url: cdnDatatableIdn,
            },
            processing: true,
            serverSide: true,
            ajax: document.location.href,
            columns: [
                {data: 'produk', name: 'produk'},
                {data: 'tanggal', name: 'tanggal'},
                {data: 'jam_kerja', name: 'jam_kerja'},
                {data: 'harga_jual', name: 'harga_jual'},
                {data: 'harga_beli', name: 'harga_beli'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    }
    if (routeName == 'payung.dombak') {
        $('.table-payung-dombak').DataTable({
            language: {
                url: cdnDatatableIdn,
            },
            processing: true,
            serverSide: true,
            ajax: document.location.href,
            columns: [
                {data: 'nama_payung', name: 'nama_payung'},
                {data: 'nama_dombak', name: 'nama_dombak'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    }
    if (routeName == 'payung.dombak.create') {
        $('.table-payung-dombak-create').DataTable({
            language: {
                url: cdnDatatableIdn,
            },
            processing: true,
            serverSide: true,
            ajax: document.location.href,
            columns: [
                {data: 'dombak_id', name: 'dombak_id'},
                {data: 'nama_dombak', name: 'nama_dombak'},
                {data: 'is_aktif', name: 'is_aktif'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    }
    if (routeName == 'konversi.index') {
        $('.table-konversi').DataTable({
            language: {
                url: cdnDatatableIdn,
            },
            processing: true,
            serverSide: true,
            ajax: document.location.href,
            columns: [
                {data: 'tinggi', name: 'tinggi'},
                {data: 'volume', name: 'volume'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    }
    if (routeName == 'member.index') {
        $('.table-member').DataTable({
            language: {
                url: cdnDatatableIdn,
            },
            processing: true,
            serverSide: true,
            ajax: document.location.href,
            columns: [
                {data: 'avatar', name: 'avatar'},
                {data: 'nama', name: 'nama'},
                {data: 'username', name: 'username'},
                {data: 'verified', name: 'verified'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    }
    if (routeName == 'level.index') {
        $('.table-level').DataTable({
            language: {
                url: cdnDatatableIdn,
            },
            processing: true,
            serverSide: true,
            ajax: document.location.href,
            columns: [
                {data: 'nama_level', name: 'nama_level'},
                {data: 'nama_divisi', name: 'nama_divisi'},
                {data: 'keterangan', name: 'keterangan'},
                {data: 'is_aktif', name: 'is_aktif'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    }
    if (routeName == 'jabatan.index') {
        $('.table-jabatan').DataTable({
            language: {
                url: cdnDatatableIdn,
            },
            processing: true,
            serverSide: true,
            ajax: document.location.href,
            columns: [
                {data: 'nama_jabatan', name: 'nama_jabatan'},
                {data: 'nama_level', name: 'nama_level'},
                {data: 'keterangan', name: 'keterangan'},
                {data: 'is_aktif', name: 'is_aktif'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    }
    if (routeName == 'member.jabatan.index') {
        $('.table-jabatan-member').DataTable({
            language: {
                url: cdnDatatableIdn,
            },
            processing: true,
            serverSide: true,
            ajax: document.location.href,
            columns: [
                {data: 'jabatan_id', name: 'jabatan_id'},
                {data: 'nama_divisi', name: 'nama_divisi'},
                {data: 'nama_level', name: 'nama_level'},
                {data: 'nama_jabatan', name: 'nama_jabatan'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    }
    if (routeName == 'member.jabatan.create') {
        $('.table-jabatan-member-create').DataTable({
            language: {
                url: cdnDatatableIdn,
            },
            processing: true,
            serverSide: true,
            ajax: document.location.href,
            columns: [
                {data: 'nama_divisi', name: 'nama_divisi'},
                {data: 'nama_level', name: 'nama_level'},
                {data: 'nama_jabatan', name: 'nama_jabatan'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    }
    if (routeName == 'monitoring.index') {
        $('.table-monitoring').DataTable({
            language: {
                url: cdnDatatableIdn,
            },
            processing: true,
            serverSide: true,
            ajax: document.location.href,
            columns: [
                {data: 'nama_produk', name: 'nama_produk'},
                {data: 'stok', name: 'stok'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    }
    if(routeName == 'monitoring.show_riwayat') {
        $('.table-riwayat-monitoring').DataTable({
            language: {
                url: cdnDatatableIdn,
            },
            processing: true,
            serverSide: true,
            ajax: document.location.href,
            columns: [
                {data: 'tanggal', name: 'tanggal'},
                {data: 'type_movement', name: 'type_movement'},
                {data: 'nama_dombak', name: 'nama_dombak'},
                {data: 'stok_awal', name: 'stok_awal'},
                {data: 'stok_akhir', name: 'stok_akhir'},
                {data: 'total_loss', name: 'total_loss'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    }
    if (routeName == 'monitoring.dombak.index') {
        $('.table-monitoring-dombak').DataTable({
            language: {
                url: cdnDatatableIdn,
            },
            processing: true,
            serverSide: true,
            ajax: document.location.href,
            columns: [
                {data: 'dombak_id', name: 'dombak_id'},
                {data: 'nama_dombak', name: 'nama_dombak'},
                {data: 'stok', name: 'stok'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    }
    if (routeName == 'monitoring.dombak.create') {
        $('.table-monitoring-dombak').DataTable({
            language: {
                url: cdnDatatableIdn,
            },
            processing: true,
            serverSide: true,
            ajax: document.location.href,
            columns: [
                {data: 'dombak_id', name: 'dombak_id'},
                {data: 'nama_dombak', name: 'nama_dombak'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    }
    function addDigitMonth(month) {
        return (month.length == 1) ? '0' + month : month;
    }
    if(routeName == 'home') {
        $('select#doing').on('change', function(e) {
            if (e.target.value == '0') {
                $('.value_ai').children().remove();
                $('.value_ai').addClass('d-none')
            } else if(e.target.value == 'download_margin') {
                $('.value_ai').removeClass('d-none');
                $('.value_ai').children().remove();
                $('.value_ai').append('<div class="ai text-justify"></div>');

                new Typed('.ai', {strings: ['Tentu saja, anda dapat klik link <a href="'+location.origin+'/financial/margin">disini</a> dan pilih jenis laporan yang diinginkan, bulan, dan produk. semoga informasi ini membantu. 😄'], typeSpeed: 10})
            } else {
                $.ajax({
                    url: document.location.origin + '/api/home',
                    method: 'get',
                    data: {
                        '_token': $('meta[name="csrf-token"]').attr('content'),
                        type: e.target.value
                    },
                    dataType: 'json',
                    beforeSend: function(waiting) {
                        $('.value_ai').removeClass('d-none');
                        $('.value_ai').children().remove();
                        $('.value_ai').append('<div class="ai text-justify"></div>');

                        new Typed('.ai', {strings: ['Mohon Tunggu ⏱️, Saya sedang mendapatkan data dari server 🌐'], typeSpeed: 10})
                    },
                    success: function(data) {
                        if (e.target.value == 'how_many_stok_in_dombak') {
                            $('.value_ai').removeClass('d-none');
                            $('.value_ai').children().remove();
                            $('.value_ai').append('<div class="ai text-justify"></div>');

                            let temp = '';

                            data.data.forEach(x => temp += 'Produk <b>' + x.nama_produk + '</b> dengan banyaknya stok sebesar <b>' + new Intl.NumberFormat("id-ID", { style: "decimal", maximumFractionDigits: 6 }).format(x.stok) + '</b> liter. ')

                            new Typed('.ai', {strings: ['Hari ini, stok di Dombak terdiri dari beberapa produk utama yang dimiliki. '+ temp +' untuk informasi lebih lanjut silahkan klik link <a href="'+location.origin+'/stok/monitoring">disini</a>. semoga informasi ini membantu. 😄'], typeSpeed: 10})
                        }
                        if(e.target.value == 'how_many_stok_in_pertamina') {
                            $('.value_ai').removeClass('d-none');
                            $('.value_ai').children().remove();
                            $('.value_ai').append('<div class="ai text-justify"></div>');

                            let temp = '';

                            data.data.forEach(x => temp += 'Produk <b>' + x.nama_produk + '</b> dengan banyaknya stok sebesar <b>' + new Intl.NumberFormat("id-ID", { style: "decimal", maximumFractionDigits: 6 }).format(x.stok / 8000) + '</b> Kl. ')

                            new Typed('.ai', {strings: ['Hari ini, tercatat terdapat beberapa produk stok yang di Pertamina . '+ temp +' untuk informasi lebih lanjut silahkan klik link <a href="'+ location.origin + '/procurement/ms2' +'">disini</a>. semoga informasi ini membantu. 😄'], typeSpeed: 10})
                        }
                        if(e.target.value == 'how_many_sales_today') {
                            $('.value_ai').removeClass('d-none');
                            $('.value_ai').children().remove();
                            $('.value_ai').append('<div class="ai text-justify"></div>');

                            const date = new Date();
                            let now = date.getFullYear() + '-' + addDigitMonth(date.getMonth()) + '-' + date.getDate()

                            let temp = '';let detail = '';
                            data.data.summary.forEach(x => temp += ' Untuk Jam kerja <b>' + x.nama_jam_kerja + "</b> menjual sebesar <b>Rp. " + x.total_penjualan + '</b>.')

                            data.data.details.forEach(x => detail += 'Untuk Produk <b>' + x.produk + '</b> di jam kerja <b>' + x.nama_jam_kerja + '</b> memiliki penjualan sebesar <b>Rp. ' + x.penjualan + '</b>.')

                            new Typed('.ai', {strings: ['secara ringkas Hari ini, total penjualan 💰 minyak cukup signifikan. di ' + temp + '<br><br>Secara Detail, Penjualan per jam kerja dan per produk yang dijual yaitu ' + detail + '.Untuk mengetahui informasi lebih lanjut, Silakan klik link <a href="'+ location.origin + '/stok/totalisator/riwayat?_token=' + $('meta[name="csrf-token"]').attr('content') + '&waktu_mulai='+now+'&waktu_selesai='+ now +'&produk_id=all">disini</a>. semoga informasi ini membantu anda 😄.'], typeSpeed: 10})
                        }
                        if(e.target.value == 'how_many_profit_or_loss_today') {
                            $('.value_ai').removeClass('d-none');
                            $('.value_ai').children().remove();
                            $('.value_ai').append('<div class="ai text-justify"></div>');

                            if(data.data.summary > 0) {
                                new Typed('.ai', {strings: ['Akumulasi profit atau kerugian 💸 untuk hari ini dihitung berdasarkan Stok Taking dan Penerimaan BBM 📈. Dari Profit or Loss produk-produk minyak tersebut, sistem mencatatkan keuntungan sebesar <b>'+ data.data.summary +'</b> liter.'], typeSpeed: 10})
                            } else if (data.data.summary < 0) {
                                new Typed('.ai', {strings: ['Akumulasi profit atau kerugian 💸 untuk hari ini dihitung berdasarkan Stok Taking dan Penerimaan BBM 📈. Dari Profit or Loss produk-produk minyak tersebut, sistem mencatatkan kerugian sebesar <b>'+ data.data.summary +'</b> liter.'], typeSpeed: 10})
                            } else {
                                new Typed('.ai', {strings: ['Akumulasi profit atau kerugian 💸 untuk hari ini dihitung berdasarkan Stok Taking dan Penerimaan BBM 📈. Saat ini belum ada kerugian ataupun keuntungan dari akumulasi losses.'], typeSpeed: 10})
                            }
                        }
                    },
                    error: function(err) {
                        $('.value_ai').removeClass('d-none');
                        $('.value_ai').children().remove();
                        $('.value_ai').append('<div class="ai text-justify"></div>');

                        new Typed('.ai', {strings: ['Terjadi Kesalahan ❗️, Coba Periksa kembali koneksi internet 🌐 anda.'], typeSpeed: 10})
                    }
                })
            }
        })
        $.ajax({
            url: document.location.origin + '/api/chart',
            method: 'get',
            dataType: 'json',
            success: function(data) {
                new Chart(document.getElementById('chart-stok'), {
                    type: 'bar',
                    label: 'Stok BBM',
                    data: data.data.chart_stok,
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
                new Chart(document.getElementById('chart-stok-split'), {
                    type: 'bar',
                    label: 'Pengeluaran Stok Tanggal 12 Juli 2023',
                    data: data.data.chart_stok_split,
                    options: {
                        plugins: {
                            title: {
                                display: true,
                                text: 'Penjualan Per Shift (dalam Liter)'
                            },
                        },
                        interaction: {
                            intersect: false,
                        },
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });

                new Chart(document.getElementById('chart-penjualan'), {
                    type: 'bar',
                    label: 'Penjualan',
                    data: data.data.chart_penjualan,
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
                new Chart(document.getElementById('chart-penjualan-split'), {
                    type: 'bar',
                    data: data.data.chart_penjualan_split,
                    options: {
                        plugins: {
                            title: {
                                display: true,
                                text: 'Penjualan Per Shift (dalam Rupiah)'
                            },
                        },
                        interaction: {
                            intersect: false,
                        },
                        responsive: true,
                        scales: {
                            x: {
                                stacked: true
                            },
                            y: {
                                stacked: true,
                                beginAtZero: true
                            }
                        }
                    }
                });
            },
            error: function(err) {console.error(err)}
        })
    }
    if (routeName == 'absensi.index') {
        $('.table-absensi').DataTable({
            language: {
                url: cdnDatatableIdn,
            },
            processing: true,
            serverSide: true,
            ajax: document.location.href,
            columns: [
                {data: 'status_kehadiran', name: 'status_kehadiran'},
                {data: 'tanggal', name: 'tanggal'},
                {data: 'jam_kerja', name: 'jam_kerja'},
                {data: 'jabatan', name: 'jabatan'},
                {data: 'is_attended', name: 'is_attended'},
                {data: 'is_confirm', name: 'is_confirm'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    }
    if(routeName == 'profil.edit_password') {
        $('button.see_password').on('click', function() {
            if($(this).parent().siblings().attr('type') == 'password') {
                $(this).parent().siblings().attr('type', 'text');
            } else {
                $(this).parent().siblings().attr('type', 'password');
            }
        });
    }
    if(routeName == 'notifikasi') {
        $('.table-notifikasi').DataTable({
            language: {
                url: cdnDatatableIdn,
            },
            processing: true,
            serverSide: true,
            ajax: document.location.href,
            columns: [
                {data: 'created_at', name: 'created_at'},
                {data: 'judul', name: 'judul'},
                {data: 'status', name: 'status'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    }
    if (routeName == 'perencanaan.index' || routeName == 'perencanaan.ditolak' ) {
        $('.table-perencanaan').DataTable({
            language: {
                url: cdnDatatableIdn,
            },
            processing: true,
            serverSide: true,
            ajax: document.location.href,
            columns: [
                {data: 'kode_pembelian', name: 'kode_pembelian'},
                {data: 'created_at', name: 'created_at'},
                {data: 'member_id', name: 'member_id'},
                {data: 'produk_id', name: 'produk_id'},
                {data: 'is_confirmation', name: 'is_confirmation'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    }
    if (routeName == 'perencanaan.detail.index') {
        $('.table-detail-rencana').DataTable({
            language: {
                url: cdnDatatableIdn,
            },
            processing: true,
            serverSide: true,
            ajax: document.location.href,
            columns: [
                {data: 'detail_rencana_pembelian_id', name: 'detail_rencana_pembelian_id'},
                {data: 'nama_produk', name: 'nama_produk'},
                {data: 'jumlah', name: 'jumlah'},
                {data: 'satuan_jumlah', name: 'satuan_jumlah'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    }
    if (routeName == 'pembelian.index') {
        $('.table-pembelian').DataTable({
            language: {
                url: cdnDatatableIdn,
            },
            processing: true,
            serverSide: true,
            ajax: document.location.href,
            columns: [
                {data: 'kode_pembelian', name: 'kode_pembelian'},
                {data: 'created_at', name: 'created_at'},
                {data: 'member_id', name: 'member_id'},
                {data: 'produk_id', name: 'produk_id'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    }
    if (routeName == 'pembelian.detail.index') {
        $('.table-detail-pembelian').DataTable({
            language: {
                url: cdnDatatableIdn,
            },
            processing: true,
            serverSide: true,
            ajax: document.location.href,
            columns: [
                {data: 'nama_produk', name: 'nama_produk'},
                {data: 'rit', name: 'rit'},
                {data: 'jumlah', name: 'jumlah'},
                {data: 'satuan_jumlah', name: 'satuan_jumlah'},
                {data: 'harga', name: 'harga'},
                {data: 'total_harga', name: 'total_harga'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    }
    if(routeName == 'pembelian.detail.edit') {

        $('input#harga').on('keyup', function() {
            let harga = $('input#harga').val().replaceAll(',', '')
            let jml = ((($('input#jumlah').val()).split('.')[0]).replace(',', ''))

            $('input#total_harga').val(new Intl.NumberFormat("id-ID", { style: "decimal", maximumFractionDigits: 6 }).format(jml * harga))
        })
    }
    if (routeName == 'pembayaran.index') {
        $('.table-pembayaran').DataTable({
            language: {
                url: cdnDatatableIdn,
            },
            processing: true,
            serverSide: true,
            ajax: document.location.href,
            columns: [
                {data: 'kode_pembelian', name: 'kode_pembelian'},
                {data: 'created_at', name: 'created_at'},
                {data: 'grand_total', name: 'grand_total'},
                {data: 'produk_id', name: 'produk_id'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    }
    if (routeName == 'pembayaran.show') {
        $('.table-pembayaran-show').DataTable({
            language: {
                url: cdnDatatableIdn,
            },
            processing: true,
            serverSide: true,
            ajax: document.location.href,
            columns: [
                {data: 'tanggal', name: 'tanggal'},
                {data: 'no_rekening', name: 'no_rekening'},
                {data: 'nama_rekening', name: 'nama_rekening'},
                {data: 'total_setoran', name: 'total_setoran'},
                {data: 'is_confirmation', name: 'is_confirmation'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    }
    if (routeName == 'pembayaran.detail') {
        $('.table-pembayaran-file').DataTable({
            language: {
                url: cdnDatatableIdn,
            },
            processing: true,
            serverSide: true,
            ajax: document.location.href,
            columns: [
                {data: 'tanggal_upload', name: 'tanggal_upload'},
                {data: 'nama_file', name: 'nama_file'},
                {data: 'nama_dokumen', name: 'nama_dokumen'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    }
    if(routeName == 'pembayaran.file.create') {
        $(".custom-file-input").on("change", function (e) {
            $(this).siblings().text(e.target.value.split("fakepath")[1]);
            var input = $(e.currentTarget);
            var file = input[0].files[0];
            var reader = new FileReader();
            reader.onload = function (e) {
                $(".img.img-thumbnail.img-temporary").attr("src", e.target.result);
                $(".img.img-thumbnail.img-temporary").removeClass("d-none");
            };
            reader.readAsDataURL(file);
        });
    }
    if (routeName == 'pengiriman.index') {
        $('.table-pengiriman').DataTable({
            language: {
                url: cdnDatatableIdn,
            },
            processing: true,
            serverSide: true,
            ajax: document.location.href,
            columns: [
                {data: 'tanggal', name: 'tanggal'},
                {data: 'no_pol', name: 'no_pol'},
                {data: 'no_do', name: 'no_do'},
                {data: 'is_done', name: 'is_done'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    }
    if(routeName == 'pengiriman.show') {
        $('.table-pengiriman-lo').DataTable({
            language: {
                url: cdnDatatableIdn,
            },
            processing: true,
            serverSide: true,
            ajax: document.location.href,
            columns: [
                {data: 'no_faktur', name: 'no_faktur'},
                {data: 'no_lo', name: 'no_lo'},
                {data: 'nama_produk', name: 'nama_produk'},
                {data: 'jumlah', name: 'jumlah'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    }
    if (routeName == 'file_lo.index') {
        $('.table-file-lo').DataTable({
            language: {
                url: cdnDatatableIdn,
            },
            processing: true,
            serverSide: true,
            ajax: document.location.href,
            columns: [
                {data: 'no_lo', name: 'no_lo'},
                {data: 'produk_id', name: 'produk_id'},
                {data: 'jumlah', name: 'jumlah'},
                {data: 'no_faktur', name: 'no_faktur'},
                {data: 'tanggal_upload', name: 'tanggal_upload'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    }
    if (routeName == 'pengiriman.file_lo_show.lampiran.index') {
        $('.table-file-lampiran-lo').DataTable({
            language: {
                url: cdnDatatableIdn,
            },
            processing: true,
            serverSide: true,
            ajax: document.location.href,
            columns: [
                {data: 'nama_file', name: 'nama_file'},
                {data: 'nama_dokumen', name: 'nama_dokumen'},
                {data: 'tanggal_upload', name: 'tanggal_upload'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    }
    if (routeName == 'pengiriman.file_lo.show') {
        $('.table-file-lampiran-lo').DataTable({
            language: {
                url: cdnDatatableIdn,
            },
            columns: [
                {data: 'nama_file', name: 'nama_file'},
                {data: 'nama_dokumen', name: 'nama_dokumen'},
                {data: 'tanggal_upload', name: 'tanggal_upload'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    }
    if (routeName == 'penerimaan.file.index') {
        $('.table-penerimaan').DataTable({
            language: {
                url: cdnDatatableIdn,
            },
            processing: true,
            serverSide: true,
            ajax: document.location.href,
            columns: [
                {data: 'file_penerimaan_id', name: 'file_penerimaan_id'},
                {data: 'nama_dokumen', name: 'nama_dokumen'},
                {data: 'tanggal_upload', name: 'tanggal_upload'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    }
    if (routeName == 'penerimaan.file.riwayat.index') {
        $('.table-penerimaan').DataTable({
            language: {
                url: cdnDatatableIdn,
            },
            processing: true,
            serverSide: true,
            ajax: document.location.href,
            columns: [
                {data: 'file_penerimaan_id', name: 'file_penerimaan_id'},
                {data: 'nama_dokumen', name: 'nama_dokumen'},
                {data: 'tanggal_upload', name: 'tanggal_upload'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    }
    if (routeName == 'stok-taking.index' || routeName == 'stok-taking.riwayat') {
        $('.table-stok-taking').DataTable({
            language: {
                url: cdnDatatableIdn,
            },
            processing: true,
            serverSide: true,
            ajax: document.location.href,
            columns: [
                {data: 'tanggal', name: 'tanggal'},
                {data: 'type_dokumen', name: 'type_dokumen'},
                {data: 'nama_produk', name: 'nama_produk'},
                {data: 'jam_kerja', name: 'jam_kerja'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    }
    if (routeName == 'stok-summary.show') {
        $('.see-more-window').on('click', function(e) {
            e.preventDefault();
            const temp = JSON.parse($(this).parent().parent().parent().siblings('.temp-depth')[0].textContent);

            let wrap = `<p>ID Dokumen Stok: ${temp.dokumen_stok_id}</p><div class="form-group" style="overflow-wrap: 'break-word';"><label>Tanggal</label><div class="input-group"><div class="input-group-prepend"><div class="input-group-text"><i class="fas fa-calendar"></i></div></div><input readonly type="text" class="form-control" value="${temp.tanggal}" name="calendar"></div></div><div class="form-group"><label>Jenis </label><div class="input-group"><div class="input-group-prepend"><div class="input-group-text"><i class="fas fa-calendar"></i></div></div><input readonly type="text" class="form-control" value="${temp.type_movement}" name="jenis"></div></div>`;

            $(this).fireModal({
                title: 'My Modal',
                body: wrap,
            });
        })
        $('.edit-more-window').on('click', function() {
            console.log($(this).data('id'));
        })
    }
    if (routeName == 'stok-taking.file.index') {
        $('.table-stok-taking-file').DataTable({
            language: {
                url: cdnDatatableIdn,
            },
            processing: true,
            serverSide: true,
            ajax: document.location.href,
            columns: [
                {data: 'file_dokumen_stok_id', name: 'file_dokumen_stok_id'},
                {data: 'nama_dokumen', name: 'nama_dokumen'},
                {data: 'tanggal_upload', name: 'tanggal_upload'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    }
    if (routeName == 'stok-transfer.index' || routeName == 'stok-transfer.riwayat') {
        $('.table-stok-transfer').DataTable({
            language: {
                url: cdnDatatableIdn,
            },
            processing: true,
            serverSide: true,
            ajax: document.location.href,
            columns: [
                {data: 'tanggal', name: 'tanggal'},
                {data: 'jam_kerja', name: 'jam_kerja'},
                {data: 'dombak_sebelum', name: 'dombak_sebelum'},
                {data: 'dombak_setelah', name: 'dombak_setelah'},
                {data: 'nama_produk', name: 'nama_produk'},
                {data: 'stok_berpindah', name: 'stok_berpindah'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    }
    if (routeName == 'stok-transfer.file.index') {
        $('.table-stok-transfer-file').DataTable({
            language: {
                url: cdnDatatableIdn,
            },
            processing: true,
            serverSide: true,
            ajax: document.location.href,
            columns: [
                {data: 'nama_dokumen', name: 'nama_dokumen'},
                {data: 'tanggal_upload', name: 'tanggal_upload'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    }
    if (routeName == 'stok-transfer.create') {
        $('select#dombak_id').on('change', function(e) {
            checkKonversiDombakPertama('sebelumnya');
            checkKonversiDombakPertama('setelahnya');
            checkVolumeDombakPertama();
        });
        $('input#tinggi_dombak').on('change', function(e) {
            checkKonversiDombakPertama('sebelumnya');
        });
        $('input#tinggi_dombak_setelahnya').on('change', function(e) {
            checkKonversiDombakPertama('setelahnya');
        });

        function checkVolumeDombakPertama() {
            if($('select#dombak_id').val() != '') {
                $.ajax({
                    url: document.location.origin + '/master/dombak/api',
                    method: 'get',
                    data: {
                        dombak_id: $('select#dombak_id').val(),
                        type: 'transfer'
                    },
                    dataType: 'json',
                    success: function(data) {
                        if(data.status == 200){
                            if(data.data != null){
                                $('input#volume_tercatat').val(data.data.stok)
                                $('#tinggi_dombak').removeAttr('readonly');
                                $('#tinggi_dombak_setelahnya').removeAttr('readonly');

                                if(data.dombak.length > 0) {
                                    $('select#dombak_kedua_id').children().remove();
                                    let wrap = '<option value="">Silahkan Pilih Dombak Kedua</option>'
                                    data.dombak.forEach(e => {
                                        wrap += '<option value="'+ e.dombak_id +'">'+ e.nama_dombak +' ('+ e.nama_produk + ': ' +e.stok +'Liter)</option>'
                                    });
                                    $('select#dombak_kedua_id').append(wrap);
                                    $('select#dombak_kedua_id').removeAttr('disabled');
                                } else {
                                    $('select#dombak_kedua_id').children().remove();
                                    $('select#dombak_kedua_id').attr('disabled', '');
                                    $('select#dombak_kedua_id').append('<option value="">Silahkan Pilih Dombak Kedua</option>');
                                }
                            }
                            else {
                                $('input#volume_tercatat').val("Tidak ada Data")
                                $('#tinggi_dombak').attr('readonly', '');
                                $('#tinggi_dombak_setelahnya').attr('readonly', '');
                            }
                        }
                    },
                    error: function(err) {console.error(err)}
                });
            }
        }

        function checkKonversiDombakPertama (type) {
            if(type == 'sebelumnya') {
                if($('select#dombak_id').val() != '' && $('input#tinggi_dombak').val() != '') {
                    $.ajax({
                        url: document.location.origin + '/master/dombak/konversi/api',
                        method: 'get',
                        data: {
                            dombak_id: $('select#dombak_id').val(),
                            tinggi: $('input#tinggi_dombak').val(),
                        },
                        dataType: 'json',
                        success: function(data) {
                            if(data.status == 200){
                                if(data.data != null){
                                    let calc = parseFloat($('input#volume_setelah').val().replace(',', '')) - parseFloat(data.data.volume.replace(',', ''))
                                    $('input#volume_sebelumnya').val(data.data.volume)
                                    $('input#volume').val(Intl.NumberFormat('id-ID', {style: 'decimal', maximumFractionDigits: 6 }).format(calc).replaceAll('.', ',').replace(/,(?=\d$)/, '.'))
                                }
                                else {
                                    $('input#volume_sebelumnya').val("Tidak ada Data")
                                    $('input#volume').val("0")
                                }
                            }
                        },
                        error: function(err) {console.error(err)}
                    });
                } else {
                    $('input#volume_sebelumnya').val("0")
                }
            } else if(type == 'setelahnya') {
                if($('select#dombak_id').val() != '' && $('input#tinggi_dombak_setelahnya').val() != '') {
                    $.ajax({
                        url: document.location.origin + '/master/dombak/konversi/api',
                        method: 'get',
                        data: {
                            dombak_id: $('select#dombak_id').val(),
                            tinggi: $('input#tinggi_dombak_setelahnya').val(),
                        },
                        dataType: 'json',
                        success: function(data) {
                            if(data.status == 200){
                                if(data.data != null){
                                    let calc = parseFloat(data.data.volume.replace(',', '')) - parseFloat($('input#volume_sebelumnya').val().replace(',', ''))
                                    $('input#volume_setelah').val(data.data.volume)
                                    $('input#volume').val(Intl.NumberFormat('id-ID', {style: 'decimal', maximumFractionDigits: 6 }).format(calc).replaceAll('.', ',').replace(/,(?=\d$)/, '.'))
                                }
                                else {
                                    $('input#volume_setelah').val("Tidak ada Data")
                                    $('input#volume').val("0")
                                }
                            }
                        },
                        error: function(err) {console.error(err)}
                    });
                } else {
                    $('input#volume_setelah').val("0")
                }
            } else {
                return;
            }
        }

        $('select#dombak_kedua_id').on('change', function(e) {
            checkKonversiDombakKedua('sebelumnya');
            checkKonversiDombakKedua('setelahnya');
            checkVolumeDombakKedua();
        });
        $('input#tinggi_dombak_kedua').on('change', function(e) {
            checkKonversiDombakKedua('sebelumnya');
        });
        $('input#tinggi_dombak_kedua_setelah').on('change', function(e) {
            checkKonversiDombakKedua('setelahnya');
        });

        function checkVolumeDombakKedua() {
            if($('select#dombak_kedua_id').val() != '') {
                $.ajax({
                    url: document.location.origin + '/master/dombak/api',
                    method: 'get',
                    data: {
                        dombak_id: $('select#dombak_kedua_id').val(),
                    },
                    dataType: 'json',
                    success: function(data) {
                        if(data.status == 200){
                            if(data.data != null){
                                $('input#volume_sebelumnya_kedua_terakhir').val(data.data.stok)
                                $('#tinggi_dombak_kedua').removeAttr('readonly');
                                $('#tinggi_dombak_kedua_setelah').removeAttr('readonly');
                            }
                            else {
                                $('input#volume_sebelumnya_kedua_terakhir').val("Tidak ada Data")
                                $('#tinggi_dombak_kedua').attr('readonly', '');
                                $('#tinggi_dombak_kedua_setelah').attr('readonly', '');
                            }
                        }
                    },
                    error: function(err) {console.error(err)}
                });
            }
        }

        function checkKonversiDombakKedua (type) {
            if(type == 'sebelumnya') {
                if($('select#dombak_kedua_id').val() != '' && $('input#tinggi_dombak_kedua').val() != '') {
                    $.ajax({
                        url: document.location.origin + '/master/dombak/konversi/api',
                        method: 'get',
                        data: {
                            dombak_id: $('select#dombak_kedua_id').val(),
                            tinggi: $('input#tinggi_dombak_kedua').val(),
                        },
                        dataType: 'json',
                        success: function(data) {
                            if(data.status == 200){
                                if(data.data != null){
                                    let calc = parseFloat($('input#volume_sebelumnya_kedua').val().replace(',', '')) - parseFloat(data.data.volume.replace(',', ''))
                                    let calc_ = (parseFloat(data.data.volume.replace(',', '')) - parseFloat($('input#volume_sebelumnya_kedua').val().replace(',', ''))) - (parseFloat($('input#volume').val().replace(',', '')) * -1);
                                    $('input#volume_sebelumnya_kedua').val(data.data.volume)
                                    $('input#volume_kedua').val(Intl.NumberFormat('id-ID', {style: 'decimal', maximumFractionDigits: 6 }).format(calc).replaceAll('.', ','));
                                    $('input#volume_selisih_kedua').val(Intl.NumberFormat('id-ID', {style: 'decimal', maximumFractionDigits: 6 }).format(calc_).replaceAll('.', ','));
                                }
                                else {
                                    $('input#volume_sebelumnya_kedua').val("Tidak ada Data")
                                    $('input#volume_kedua').val("0");
                                }
                            }
                        },
                        error: function(err) {console.error(err)}
                    });
                } else {
                    $('input#volume_sebelumnya_kedua').val("0")

                }
            } else if(type == 'setelahnya') {
                if($('select#dombak_kedua_id').val() != '' && $('input#tinggi_dombak_kedua_setelah').val() != '') {
                    $.ajax({
                        url: document.location.origin + '/master/dombak/konversi/api',
                        method: 'get',
                        data: {
                            dombak_id: $('select#dombak_kedua_id').val(),
                            tinggi: $('input#tinggi_dombak_kedua_setelah').val(),
                        },
                        dataType: 'json',
                        success: function(data) {
                            if(data.status == 200){
                                if(data.data != null){
                                    let calc = parseFloat(data.data.volume.replace(',', '')) - parseFloat($('input#volume_sebelumnya_kedua').val().replace(',', ''));
                                    let calc_ = (parseFloat(data.data.volume.replace(',', '')) - parseFloat($('input#volume_sebelumnya_kedua').val().replace(',', ''))) - (parseFloat($('input#volume').val().replace(',', '')) * -1);

                                    console.log(calc, calc_, parseFloat(data.data.volume.replace(',', '')), parseFloat($('input#volume_sebelumnya_kedua').val().replace(',', '')), parseFloat($('input#volume').val().replace(',', '')));


                                    $('input#volume_sebelumnya_kedua_setelah').val(data.data.volume)
                                    $('input#volume_kedua').val(Intl.NumberFormat('id-ID', {style: 'decimal', maximumFractionDigits: 6 }).format(calc).replaceAll('.', ','));
                                    $('input#volume_selisih_kedua').val(Intl.NumberFormat('id-ID', {style: 'decimal', maximumFractionDigits: 6 }).format(calc_).replaceAll('.', ','));
                                    $('button[type=submit]').removeAttr('disabled');
                                }
                                else {
                                    $('input#volume_sebelumnya_kedua_setelah').val("Tidak ada Data")
                                    $('input#volume_kedua').val("0");
                                    $('button[type=submit]').attr('disabled', '');
                                }
                            }
                        },
                        error: function(err) {console.error(err)}
                    });
                } else {
                    $('input#volume_sebelumnya_kedua_setelah').val("0")

                }
            } else {return;}
        }
    }
    if (routeName == 'totalisator.index' || routeName == 'totalisator.riwayat') {
        $('.table-totalisator').DataTable({
            language: {
                url: cdnDatatableIdn,
            },
            processing: true,
            serverSide: true,
            ajax: document.location.href,
            columns: [
                {data: 'nozzle', name: 'nozzle'},
                {data: 'nama_produk', name: 'nama_produk'},
                {data: 'tanggal', name: 'tanggal'},
                {data: 'jam_kerja', name: 'jam_kerja'},
                {data: 'stand_meter_awal', name: 'stand_meter_awal'},
                {data: 'stand_meter_akhir', name: 'stand_meter_akhir'},
                {data: 'selisih', name: 'selisih'},
                {data: 'status', name: 'status'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    }
    if (routeName == 'totalisator.create') {
        $('select#dombak_id').on('change', function(e) {
            $.ajax({
                url: document.location.origin + '/stok/totalisator/create/api',
                method: 'get',
                data: {
                    dombak_id: $('select#dombak_id').val(),
                },
                dataType: 'json',
                beforeSend: function() {
                    $('select#nozzle_id').children().remove();
                    $('select#nozzle_id').attr('disabled', '');
                    $('input#jam_kerja_id').attr('readonly', '');
                    $('input#tanggal').attr('readonly', '');
                },
                success: function(data) {
                    if(data.status == 'success'){
                        if(data.data != null){
                            $('button[type=submit]').attr('disabled', '');
                            $('select#nozzle_id').removeAttr('disabled');
                            $('select#nozzle_id').append('<option value="0">Silakan Pilih Nozzle</option>')
                            data.data.forEach(e => {
                                $('select#nozzle_id').append('<option value="'+ e.nozzle_id +'">'+ e.nama_nozzle + " - " + e.nama_produk +'</option>');
                            })
                            $('input#tanggal').attr('type', 'text');
                            $('input#tanggal').val('');
                            $('input#jam_kerja_id').val('');
                            $('input#stand_meter_awal').val('');
                            $('input#stand_meter_awal').attr('readonly', '')
                        }
                    }
                },
                error: function(err) {console.error(err)}
            });
        });


        $('select#nozzle_id').on('change', function(e) {
            $.ajax({
                url: document.location.origin + '/stok/totalisator/create/api',
                method: 'get',
                data: {
                    nozzle_id: $('select#nozzle_id').val()
                },
                dataType: 'json',
                beforeSend: function() {
                    $('button[type=submit]').attr('disabled');
                },
                success: function(data) {
                    if(data.status == 'success'){
                        if(data.data != null){
                            if ($('select#nozzle_id').children().first()[0].textContent == 'Silakan Pilih Nozzle') {
                                $('select#nozzle_id').children().first().remove();
                            }
                            $('button[type=submit]').removeAttr('disabled');
                            $('input#tanggal').attr('type', 'date');
                            $('input#tanggal').val(data.data.tanggal);
                            if(data.data.receive) {
                                $('input#tanggal').removeAttr('readonly');
                                $('.information').children().remove();
                                $('.information').append('<div class="alert alert-info">Sistem mendeteksi ada Penerimaan BBM. Silakan isi tanggal dan jam kerja sesuai dengan keadaan.</div>');
                                $('.jam_kerja_dynamic').children().remove();
                                let loop = '';
                                data.data.jam_kerja_id.forEach(x => {
                                    loop += '<option value="'+ x.jam_kerja_id +'" >'+ x.nama_jam_kerja +'</option>'
                                })
                                $('.jam_kerja_dynamic').append('<select class="custom-select" name="jam_kerja_id" id="jam_kerja_id">'+ loop +'</select>');
                            } else {
                                $('.information').children().remove();
                                $('.jam_kerja_dynamic').children().remove();
                                $('.jam_kerja_dynamic').append('<input id="jam_kerja_id" type="text" class="form-control" name="jam_kerja_id" readonly value="'+ data.data.jam_kerja +'" />');
                                $('input#tanggal').attr('readonly', '');
                            }
                            $('input#jam_kerja_id').val('Shift ' + (data.data.jam_kerja ?? 1));
                            if(data.data.stand_meter_akhir == 0) {
                                $('input#stand_meter_awal').removeAttr('readonly');
                                $('input#stand_meter_awal').val(0);
                            } else {
                                $('input#stand_meter_awal').attr('readonly', '');
                                $('input#stand_meter_awal').val(data.data.stand_meter_akhir ?? 0);
                            }
                        }
                        else {

                        }
                    }
                },
                error: function(err) {console.error(err)}
            });
        })


    }
    if(routeName == 'totalisator.edit' || routeName == 'totalisator.final') {
        $('input#stand_meter_akhir').on('change', function(e) {
            let awal = removeComma(clearDot($('input#stand_meter_awal').val())), akhir = removeComma(clearDot($(this).val()));
            $('input#selisih').val(Intl.NumberFormat(["ban", "id"]).format(akhir - awal));
        });

        function clearDot(number) {
            const reDot = /[.]/;
            if(number.search(reDot)) {
                return number.split('.')['0'];
            } else {
                return number;
            }
        }

        function removeComma(number) {
            return number.replaceAll(',', '');
        }
    }
    if (routeName == 'totalisator.file.index' || routeName == 'totalisator.file.riwayat.index') {
        $('.table-stok-taking-file').DataTable({
            language: {
                url: cdnDatatableIdn,
            },
            processing: true,
            serverSide: true,
            ajax: document.location.href,
            columns: [
                {data: 'file_stand_meter_id', name: 'file_stand_meter_id'},
                {data: 'nama_dokumen', name: 'nama_dokumen'},
                {data: 'tanggal_upload', name: 'tanggal_upload'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    }
    if(routeName == 'stok-taking.create') {
        $('input#tinggi_dombak').on('change', function(e) {
            checkKonversiDombak();
        });

        $('select#dombak_id').on('change', function(e) {
            checkKonversiDombak();
            checkVolumeDombak();
        });

        function checkVolumeDombak() {
            if($('select#dombak_id').val() != '') {
                $.ajax({
                    url: document.location.origin + '/master/dombak/api',
                    method: 'get',
                    data: {
                        dombak_id: $('select#dombak_id').val(),
                    },
                    dataType: 'json',
                    success: function(data) {
                        if(data.status == 200){
                            if(data.data != null){
                                $('input#volume_sebelumnya').val(data.data.stok)
                                $('input#volume_selisih').val(data.data.stok - $('input#volume').val());
                                $('button[type=submit]').removeAttr('disabled');
                            }
                            else {
                                $('input#volume_sebelumnya').val("Tidak ada Data")
                                $('input#volume_selisih').val("0");
                                $('button[type=submit]').attr('disabled', '');
                            }
                        }
                    },
                    error: function(err) {console.error(err)}
                });
            }
        }

        function checkKonversiDombak () {
            if($('select#dombak_id').val() != '' && $('input#tinggi_dombak').val() != '') {
                $.ajax({
                    url: document.location.origin + '/master/dombak/konversi/api',
                    method: 'get',
                    data: {
                        dombak_id: $('select#dombak_id').val(),
                        tinggi: $('input#tinggi_dombak').val(),
                    },
                    dataType: 'json',
                    success: function(data) {
                        if(data.status == 200){
                            let calc = data.data.volume.replace(',', '') - $('input#volume_sebelumnya').val().replace(',', '');
                            if(data.data != null){
                                $('input#volume').val(data.data.volume)
                                $('input#volume_selisih').val(Intl.NumberFormat('id-ID', {style: 'decimal', maximumFractionDigits: 6 }).format(calc).replaceAll('.', ','));
                                $('button[type=submit]').removeAttr('disabled');
                            }
                            else {
                                $('input#volume').val("Tidak ada Data")
                                $('input#volume_selisih').val("0");
                                $('button[type=submit]').attr('disabled', '');
                            }
                        }
                    },
                    error: function(err) {console.error(err)}
                });
            } else {
                $('input#volume').val("0")
            }
        }

    }
    if (routeName == 'financial.petty-cash') {
        $('.table-petty-cash').DataTable({
            language: {
                url: cdnDatatableIdn,
            },
            processing: true,
            serverSide: true,
            ajax: document.location.href,
            columns: [
                {data: 'tanggal', name: 'tanggal'},
                {data: 'no_kode', name: 'no_kode'},
                {data: 'jenis', name: 'jenis'},
                {data: 'jumlah', name: 'jumlah'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    }
    if (routeName == 'financial.petty-cash.file') {
        $('.table-petty-cash').DataTable({
            language: {
                url: cdnDatatableIdn,
            },
            processing: true,
            serverSide: true,
            ajax: document.location.href,
            columns: [
                {data: 'tanggal', name: 'tanggal'},
                {data: 'nama_dokumen', name: 'nama_dokumen'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    }
    if (routeName == 'penerimaan.index') {
        $('.table-penerimaan').DataTable({
            language: {
                url: cdnDatatableIdn,
            },
            processing: true,
            serverSide: true,
            ajax: document.location.href,
            columns: [
                {data: 'tanggal', name: 'tanggal'},
                {data: 'no_faktur', name: 'no_faktur'},
                {data: 'no_lo', name: 'no_lo'},
                {data: 'nama_produk', name: 'nama_produk'},
                {data: 'jumlah', name: 'jumlah'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    }
    if (routeName == 'penerimaan.verifikasi') {
        $('.table-verifikasi').DataTable({
            language: {
                url: cdnDatatableIdn,
            },
            processing: true,
            serverSide: true,
            ajax: document.location.href,
            columns: [
                {data: 'tanggal', name: 'tanggal'},
                {data: 'dombak', name: 'dombak'},
                {data: 'no_faktur', name: 'no_faktur'},
                {data: 'no_lo', name: 'no_lo'},
                {data: 'nama_produk', name: 'nama_produk'},
                {data: 'jumlah', name: 'jumlah'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    }
    if (routeName == 'penerimaan.riwayat') {
        $('.table-penerimaan-riwayat').DataTable({
            language: {
                url: cdnDatatableIdn,
            },
            processing: true,
            serverSide: true,
            ajax: document.location.href,
            columns: [
                {data: 'tanggal', name: 'tanggal'},
                {data: 'dombak', name: 'dombak'},
                {data: 'no_faktur', name: 'no_faktur'},
                {data: 'no_lo', name: 'no_lo'},
                {data: 'nama_produk', name: 'nama_produk'},
                {data: 'jumlah', name: 'jumlah'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    }
    if (routeName == 'penerimaan.show') {
        $('.tinggi_dombak_sebelumnya').on('change', function () {
            checkKonversiDombak($(this).val(), $(this).data('dombak')).then(res => {
                $(this).parent().parent().siblings('.volume_sebelumnya-row').children('div').children('input').val(res.data.volume);
                volume_terisi();
            }).catch(err => console.error(err));
        });
        $('.tinggi_dombak_setelahnya').on('change', function () {
            checkKonversiDombak($(this).val(), $(this).data('dombak')).then(res => {
                $(this).parent().parent().siblings('.volume_penerimaan-row').children('div').children('input').val(res.data.volume);
                volume_terisi();
            }).catch(err => console.error(err));
        });

        function volume_terisi() {
            if($('input#volume_sebelumnya').val() != '' && $('input#volume_penerimaan').val() != ' ') {
                let i=0, total = 0;
                for(i=0; i < $('.volume_sebelumnya').length; i++) {
                    total += $('.volume_penerimaan')[i].value.replace(',', '') - $('.volume_sebelumnya')[i].value.replace(',', '');
                }

                let v_dbl = $('input#volume_dibeli').val().replace(',', '');
                let v_lpt = total - v_dbl;

                $('#volume_aktual').val(total.toLocaleString('en'));
                $('#volume_selisih').val(v_lpt.toLocaleString('en'));
            }
        }

        async function checkKonversiDombak (tinggi, dombak) {
            if($('select#dombak_id').val() != '' && $('input#tinggi_dombak').val() != '') {
                return await $.ajax({
                    url: document.location.origin + '/master/dombak/konversi/api',
                    method: 'get',
                    data: {
                        dombak_id: dombak,
                        tinggi: tinggi,
                    },
                    dataType: 'json',
                    success: function(data) {
                        if(data.status == 200){
                            return data;
                        }
                    },
                    error: function(err) {console.error(err)}
                });
            } else {
                console.error('Pilih dombak dan tinggi')
            }
        }

        // $('select#dombak_id').on('change', function(e) {
        //     if($(this).children('option').first()[0] == '<option value="">Pilih Dombak</option>') {
        //         $(this).children('option').first().remove();
        //     }
        //     checkVolumeDombak();
        // });

        // function checkVolumeDombak() {
        //     if($('select#dombak_id').val() != '') {
        //         $.ajax({
        //             url: document.location.origin + '/master/dombak/api',
        //             method: 'get',
        //             data: {
        //                 dombak_id: $('select#dombak_id').val(),
        //             },
        //             dataType: 'json',
        //             success: function(data) {
        //                 if(data.status == 200){
        //                     if(data.data != null){
        //                         $('input#tinggi_dombak_sebelumnya').attr('min', data.tinggi.min).attr('max', data.tinggi.max).removeAttr('readonly');
        //                         $('input#tinggi_dombak_setelahnya').attr('min', data.tinggi.min).attr('max', data.tinggi.max).removeAttr('readonly');
        //                         $('span#show-last-volume').removeClass('d-none');
        //                         $('span#show-last-volume > span').text(data.data.stok)
        //                     } else {
        //                         console.error('Terdapat Error');
        //                     }
        //                 }
        //             },
        //             error: function(err) {console.error(err)}
        //         });
        //     }
        // }
    }
    if (routeName == 'setoran.open') {
        $('.table-open-setoran').DataTable({
            language: {
                url: cdnDatatableIdn,
            },
            processing: true,
            serverSide: true,
            ajax: document.location.href,
            columns: [
                {data: 'tanggal', name: 'tanggal'},
                {data: 'jam_kerja', name: 'jam_kerja'},
                {data: 'total_stand_meter', name: 'total_stand_meter'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    }
    if (routeName == 'setoran.open.create') {
        $(document).on('change', 'select.nozzle_id', function(e) {
            if($(this).children().first().attr('value') == '0') {
                $(this).children().first().remove();
            }

            $.ajax({
                url: document.location.origin + '/api/setoran/open-penjualan/create',
                method: 'get',
                data: {
                    nozzle_id: $(this).val(),
                    tanggal: $('input[name=tanggal]').val(),
                    spbu_id: $('select[name=spbu_id]').val(),
                    jam_kerja_id: $('select[name=jam_kerja_id]').val(),
                    '_token': $('meta[name="csrf-token"]').attr('content'),
                },
                dataType: 'json',
                success: function(data) {
                    $(this).removeAttr('disabled')
                    let que = $(e)[0].currentTarget.dataset.que;
                        if(data.status == 'success'){
                            $('.alert-success.alert-sm-1.que' + que).addClass('d-none');
                            $('.alert-danger.alert-sm-2.que-' + que).addClass('d-none');
                            $('.alert-danger.alert-sm-1.que-' + que).addClass('d-none');
                            $('.stand_meter_awal.que-' + que).val(data.data.stand_meter_awal ?? '0')
                            $('.stand_meter_akhir.que-' + que).val(data.data.stand_meter_akhir ?? '0')
                        } else {
                            $('.stand_meter_awal.que-' + que).val('0')
                            $('.stand_meter_akhir.que-' + que).val('0')
                            if(data.message == 'Stand Meter not found') {
                                $('.alert-success.alert-sm-1.que' + que).addClass('d-none');
                                $('.alert-danger.alert-sm-2.que-' + que).addClass('d-none');
                                $('.alert-danger.alert-sm-1.que-' + que).removeClass('d-none');
                            }
                            if(data.message == 'Nozzle not found') {
                                $('.alert-success.alert-sm-1.que' + que).addClass('d-none');
                                $('.alert-danger.alert-sm-1.que-' + que).addClass('d-none');
                                $('.alert-danger.alert-sm-2.que-' + que).removeClass('d-none');
                            }
                        }
                },
                error: function(err) {$(this).removeAttr('disabled');console.error(err)}
            });
        });

        $('.sm-plus').on('click', function() {
            let que = $('.que-main').children().length + 1;

            let nozzle = $('input[name=nozzle]').val().split('||').map(x =>  x.split('|').map(y => y.trim()));
            let nozzleWrapper = '<select class="custom-select nozzle_id" name="nozzle_id[]" id="nozzle_id" data-que="'+ que +'"><option value="0">Silakan Pilih Nozzle</option>';
            nozzle.pop();
            nozzle.forEach(x => {
                nozzleWrapper += '<option value="'+ x[0] +'">'+ x[1] +'</option>';
            })
            nozzleWrapper += '</select>';
            $('.que-main').append('<div class="card que-'+ que +'"><div class="card-header"><h4>Stand Meter #'+ que +'</h4><div class="card-header-action"><button class="btn btn-danger btn-erase-sm" data-que="'+ que +'" type="button" title="Hapus"><i class="fas fa-minus"></i></button></div></div><div class="card-body"><div class="row"><div class="col-12"><div class="alert alert-success alert-sm-1 que-'+ que +' d-none"> Stand Meter Sudah Dicatat </div><div class="alert alert-danger alert-sm-1 que-'+ que +' d-none"> Stand Meter Belum Dicatat. Silakan anda buat dan konfirmasi di menu Totalisator</div><div class="alert alert-danger alert-sm-2 que-'+ que +' d-none"> Nozzle Tidak ditemukan.</div></div></div><div class="row mb-3 justify-content-center"><label for="nozzle_id" class="col-md-4 col-form-label text-md-end">Nozzle</label><div class="col-md-6">'+ nozzleWrapper +'</div></div><div class="row mb-3 justify-content-center sm-1"><label for="stand_meter_awal" class="col-md-4 col-form-label text-md-end">Stand Meter Awal</label><div class="col-md-6"><div class="input-group"><input id="stand_meter_awal" type="text" readonly class="form-control stand_meter_awal thousand-style que-'+ que +'" name="stand_meter_awal" value="0" /></div></div></div><div class="row mb-3 justify-content-center sm-2"><label for="stand_meter_akhir" class="col-md-4 col-form-label text-md-end">Stand Meter Akhir</label><div class="col-md-6"><div class="input-group"><input id="stand_meter_akhir" type="text" readonly class="form-control stand_meter_akhir thousand-style que-'+ que +'" name="stand_meter_akhir" value="0" /></div></div></div></div></div>');
        });
        $(document).on('click', '.btn-erase-sm', function() {
            console.log($(this).parent().parent().parent().remove())
        })
    }
    if (routeName == 'setoran.open.detail') {
        $('.table-open-detail-setoran').DataTable({
            language: {
                url: cdnDatatableIdn,
            },
            processing: true,
            serverSide: true,
            ajax: document.location.href,
            columns: [
                {data: 'nama_nozzle', name: 'nama_nozzle'},
                {data: 'stand_meter_awal', name: 'stand_meter_awal'},
                {data: 'stand_meter_akhir', name: 'stand_meter_akhir'},
                {data: 'harga', name: 'harga'},
                {data: 'liter', name: 'liter'},
                {data: 'total_detail_jual', name: 'total_detail_jual'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    }
    if(routeName == 'setoran.open.detail.create') {
        $('select[id=nozzle_id]').on('change', function(e) {
            if($(this).children()[0].value == '0') {
                $(this).children().first().remove();
            }
            let r = $(this).children().filter((index, el) => el.value == e.target.value);

            $('input[name=stand_meter_awal]').val(r[0].dataset.smaw)
            $('input[name=stand_meter_akhir]').val(r[0].dataset.smak)
            $('input[name=harga_jual]').val(r[0].dataset.hj)
            $('input[name=total_detail_jual]').val(r[0].dataset.thj)
        });
    }
    if(routeName == 'pembayaran.pajak_edit') {
        let gross_cleansing = $('input[data-type=gross_price_check]').val().replaceAll(',', '');

        $('input[name=ppn]').on('change', function(e) {
            let calc = gross_cleansing - $('input[name=ppn]').val().replaceAll(',', '') -  $('input[name=pph]').val().replaceAll(',', '') -  $('input[name=ppbkb]').val().replaceAll(',', '');

            $('input[name=net_price]').val(Intl.NumberFormat('id-ID', {style: 'decimal', maximumFractionDigits: 6 }).format(calc).replaceAll('.', ','))
        })
        $('input[name=pph]').on('change', function(e) {
            let calc = gross_cleansing - $('input[name=ppn]').val().replaceAll(',', '') -  $('input[name=pph]').val().replaceAll(',', '') -  $('input[name=ppbkb]').val().replaceAll(',', '');

            $('input[name=net_price]').val(Intl.NumberFormat('id-ID', {style: 'decimal', maximumFractionDigits: 6 }).format(calc).replaceAll('.', ','))
        })
        $('input[name=ppbkb]').on('change', function(e) {
            let calc = gross_cleansing - $('input[name=ppn]').val().replaceAll(',', '') -  $('input[name=pph]').val().replaceAll(',', '') -  $('input[name=ppbkb]').val().replaceAll(',', '');

            $('input[name=net_price]').val(Intl.NumberFormat('id-ID', {style: 'decimal', maximumFractionDigits: 6 }).format(calc).replaceAll('.', ','))
        })
    }
    if(routeName == 'setoran.penitipan') {
        $('.table-penitipan-setoran').DataTable({
            language: {
                url: cdnDatatableIdn,
            },
            processing: true,
            serverSide: true,
            ajax: document.location.href,
            columns: [
                {data: 'tanggal', name: 'tanggal'},
                {data: 'jam_kerja', name: 'jam_kerja'},
                {data: 'stand_meter', name: 'stand_meter'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    }
    if(routeName == 'setoran.setoran_bank.index_setoran_bank_penjualan') {
        $('.table-setoran-bank').DataTable({
            language: {
                url: cdnDatatableIdn,
            },
            processing: true,
            serverSide: true,
            ajax: document.location.href,
            columns: [
                {data: 'status', name: 'status'},
                {data: 'tanggal', name: 'tanggal'},
                {data: 'jam_kerja', name: 'jam_kerja'},
                {data: 'stand_meter', name: 'stand_meter'},
                {data: 'total_uang_penjualan', name: 'total_uang_penjualan'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    }
    if(routeName == 'setoran.setoran_bank.bank.index') {
        $('.table-bank-setoran-list').DataTable({
            language: {
                url: cdnDatatableIdn,
            },
            processing: true,
            serverSide: true,
            ajax: document.location.href,
            columns: [
                {data: 'tanggal', name: 'tanggal'},
                {data: 'nama_bank', name: 'nama_bank'},
                {data: 'no_ref', name: 'no_ref'},
                {data: 'metode_transfer', name: 'metode_transfer'},
                {data: 'total_uang', name: 'total_uang'},
                {data: 'status', name: 'status'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    }
    if(routeName == 'setoran.closing') {
        $('.table-closing-setoran').DataTable({
            language: {
                url: cdnDatatableIdn,
            },
            processing: true,
            serverSide: true,
            ajax: document.location.href,
            columns: [
                {data: 'tanggal', name: 'tanggal'},
                {data: 'jam_kerja', name: 'jam_kerja'},
                {data: 'stand_meter', name: 'stand_meter'},
                {data: 'total_uang_penjualan', name: 'total_uang_penjualan'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    }
    if(routeName == 'setoran.setoran_bank.file.index') {
        $('.table-file-penarikan').DataTable({
            language: {
                url: cdnDatatableIdn,
            },
            processing: true,
            serverSide: true,
            ajax: document.location.href,
            columns: [
                {data: 'tanggal', name: 'tanggal'},
                {data: 'gambar', name: 'gambar'},
                {data: 'nama_file', name: 'nama_file'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    }
    if(routeName == 'ms2.produk.index') {
        $('.table-ms2').DataTable({
            language: {
                url: cdnDatatableIdn,
            },
            processing: true,
            serverSide: true,
            ajax: document.location.href,
            columns: [
                {data: 'nama_produk', name: 'nama_produk'},
                {data: 'stok_kl', name: 'stok_kl'},
                {data: 'total_pembelian', name: 'total_pembelian'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    }
    if(routeName == 'ms2.produk.show') {
        $('.table-detail-produk').DataTable({
            language: {
                url: cdnDatatableIdn,
            },
            processing: true,
            serverSide: true,
            ajax: document.location.href,
            columns: [
                {data: 'tanggal_pembelian', name: 'tanggal_pembelian'},
                {data: 'kode_pembelian', name: 'kode_pembelian'},
                {data: 'jumlah', name: 'jumlah'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    }
    if(routeName == 'ms2.pengiriman') {
        $('.table-pengiriman').DataTable({
            language: {
                url: cdnDatatableIdn,
            },
            processing: true,
            serverSide: true,
            ajax: document.location.href,
            columns: [
                {data: 'tanggal', name: 'tanggal'},
                {data: 'produk', name: 'produk'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    }
    if(routeName == 'ms2.pengiriman.create') {
        $('.checkbox-drp').on('click', function() {
            if ($(this)[0].checked) {
                $(this).parent().siblings('.select-' + $(this).attr('id')).removeClass('d-none')
                $(this).parent().siblings('.select-' + $(this).attr('id')).attr('name', 'detail_rencana_pembelian_id_shift[]')
            } else {
                $(this).parent().siblings('.select-' + $(this).attr('id')).addClass('d-none')
                $(this).parent().siblings('.select-' + $(this).attr('id')).removeAttr('name')
            }
        });
    }
    if(routeName == 'ms2.pengiriman.konfirm_pengiriman') {
        $('.btn-minus-produk').on('click', function() {
            $(this).parent().parent().addClass('bg-danger');
            $(this).addClass('d-none');
            $(this).siblings().removeClass('d-none');
            $(this).parent().siblings('input').remove();
        });
        $('.btn-plus-produk').on('click', function() {
            $(this).parent().parent().removeClass('bg-danger');
            $(this).addClass('d-none');
            $(this).siblings().removeClass('d-none');
            $(this).parent().parent().append('<input type="hidden" name="detail_rencana_pembelian_id[]" value="'+ $(this).data('id') +'">')
        });
    }
    if(routeName == 'pajak_pembelian_lib.index') {
        $('.table-pajak-lib').DataTable({
            language: {
                url: cdnDatatableIdn,
            },
            processing: true,
            serverSide: true,
            ajax: document.location.href,
            columns: [
                {data: 'tanggal', name: 'tanggal'},
                {data: 'ppn', name: 'ppn'},
                {data: 'pph', name: 'pph'},
                {data: 'ppbkb', name: 'ppbkb'},
                {data: 'is_aktif', name: 'is_aktif'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    }
    if(routeName == 'pembelian.detail.edit_pajak') {
        $('#ppn').on('keyup', function(e) {
            $('#total_amount').val(new Intl.NumberFormat("id-ID", { style: "decimal", maximumFractionDigits: 6 }).format(parseInt($('#net_price').val().replaceAll(',', '')) + parseInt(e.target.value.replaceAll(',', '')) + parseInt($('#pph').val().replaceAll(',', '')) + parseInt($('#ppbkb').val().replaceAll(',', ''))));
        });
        $('#pph').on('keyup', function(e) {
            $('#total_amount').val(new Intl.NumberFormat("id-ID", { style: "decimal", maximumFractionDigits: 6 }).format(parseInt($('#net_price').val().replaceAll(',', '')) + parseInt($('#ppn').val().replaceAll(',', '')) + parseInt(e.target.value.replaceAll(',', '')) + parseInt($('#ppbkb').val().replaceAll(',', ''))));
        });
        $('#ppbkb').on('keyup', function(e) {
            $('#total_amount').val(new Intl.NumberFormat("id-ID", { style: "decimal", maximumFractionDigits: 6 }).format(parseInt($('#net_price').val().replaceAll(',', '')) + parseInt($('#ppn').val().replaceAll(',', '')) + parseInt($('#pph').val().replaceAll(',', '')) + parseInt(e.target.value.replaceAll(',', ''))));
        });

    }
    if(routeName == 'kategori.index') {
        $('.table-kategori').DataTable({
            language: {
                url: cdnDatatableIdn,
            },
            processing: true,
            serverSide: true,
            ajax: document.location.href,
            columns: [
                {data: 'nama_kategori', name: 'nama_kategori'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    }
    if(routeName == 'tag.index') {
        $('.table-tag').DataTable({
            language: {
                url: cdnDatatableIdn,
            },
            processing: true,
            serverSide: true,
            ajax: document.location.href,
            columns: [
                {data: 'nama_tag', name: 'nama_tag'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    }
    if(routeName == 'post.index') {
        $('.table-post').DataTable({
            language: {
                url: cdnDatatableIdn,
            },
            processing: true,
            serverSide: true,
            ajax: document.location.href,
            columns: [
                {data: 'tanggal', name: 'tanggal'},
                {data: 'title', name: 'title'},
                {data: 'status', name: 'status'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    }
    if(routeName == 'post.create') {
        $('select#tag').select2();
        $('select#kategori').select2();
    }
    if(routeName == 'rekap-pajak.index') {
        $('.table-rekap-pajak').DataTable({
            language: {
                url: cdnDatatableIdn,
            },
            processing: true,
            serverSide: true,
            ajax: document.location.href,
            columns: [
                {data: 'tanggal', name: 'tanggal'},
                {data: 'kode_pembelian', name: 'kode_pembelian'},
                {data: 'ppn', name: 'ppn'},
                {data: 'pph', name: 'pph'},
                {data: 'ppbkb', name: 'ppbkb'},
                {data: 'total_pajak', name: 'total_pajak'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    }
    if(routeName == 'file.post.index') {
        $('.table-file-post').DataTable({
            language: {
                url: cdnDatatableIdn,
            },
            processing: true,
            serverSide: true,
            ajax: document.location.href,
            columns: [
                {data: 'nama_dokumen', name: 'nama_dokumen'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    }
    if(routeName == 'ms2.pengiriman.show') {
        $('.table-produk-detail-ms2').DataTable({
            language: {
                url: cdnDatatableIdn,
            },
            processing: true,
            serverSide: true,
            ajax: document.location.href,
            columns: [
                {data: 'no_lo', name: 'no_lo'},
                {data: 'tanggal', name: 'tanggal'},
                {data: 'kode', name: 'kode'},
                {data: 'nama_produk', name: 'nama_produk'},
                {data: 'stok', name: 'stok'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    }
    if(routeName == 'stok-summary.index') {
        $("#calendar").fullCalendar({
            defaultView: 'month',
            height: 750,
            businessHours: true,
            selectable: true,
            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'month'
            },
            buttonIcons: true,
            selectMirror: true,
            locale: 'id',
            dayMaxEvents: true,
            editable: false,
            droppable: false,
            viewRender: function(view, element) {
                var currentMonth = view.start.month() + 1; // Bulan dimulai dari 0, tambahkan 1
                var currentYear = view.start.year();

                // Panggil API berdasarkan bulan dan tahun aktif
                // $.ajax({
                //     url: window.location.href + '/api',
                //     method: "GET",
                //     data: {
                //         month: currentMonth,
                //         year: currentYear
                //     },
                //     success: function(response) {
                //         console.log(response);
                //         // Hapus event lama dari kalender
                //         $('#calendar').fullCalendar('removeEvents');

                //         // Tambahkan event baru dari hasil API ke kalender
                //         $('#calendar').fullCalendar('renderEvents', response.events);

                //         // Jika data berbentuk lain, sesuaikan pengolahan datanya di sini
                //     },
                //     error: function() {
                //         alert("Gagal mengambil data dari API.");
                //     }
                // });

                console.log('yeayy');
            },
            dayClick: function(date, jsEvent, view) {
                // Mendapatkan tanggal, bulan, dan tahun dari objek date
                var day = date.date();      // Tanggal
                var month = date.month() + 1; // Bulan (di FullCalendar, bulan dimulai dari 0)
                var year = date.year();     // Tahun

                // Menampilkan alert
                if(window.confirm("Apakah anda ingin menambah/Mengubah Data Tanggal " + day + " " + convertMonthToString(month) + " " + year)) {
                    let url = window.location.href.endsWith('/') ? window.location.href.slice(0, -1) : window.location.href;
                    window.location.href = url + '/' + year + '-' + (month < 10 ? '0' + month : month) + '-' + (day < 10 ? '0' + day : day);
                }
            },
            select: function(arg) {
                return false;
            },eventClick: function(arg) {
                console.log(arg);
            },
            events: [
                {
                    title: 'BS, PL, PX, PDX',
                    start: '2024-10-20T06:00:00',
                    end: '2024-10-20T22:00:00',
                }
            ],
            drop: function() {
            },
        });
        $('#calendar').fullCalendar('render');
    }
    function convertMonthToString(month) {
        if(month == 1) return 'Januari';
        if(month == 2) return 'Februari';
        if(month == 3) return 'Maret';
        if(month == 4) return 'April';
        if(month == 5) return 'Mei';
        if(month == 6) return 'Juni';
        if(month == 7) return 'Juli';
        if(month == 8) return 'Agustus';
        if(month == 9) return 'September';
        if(month == 10) return 'Oktober';
        if(month == 11) return 'November';
        if(month == 12) return 'Desember';
        return '';
    }
    if(routeName == 'jadwal-kerja.index') {
        $(function() {
            let numberWrapper = 1;
            let temp_jam_kerja = '';
            let temp_nozzle = '';
            let date = new Date();
            let now = [date.getFullYear(), (date.getMonth() + 1), date.getDate()];

            $('input#temp_jam_kerja').val().split('||').map((jk, i) => {
                if(jk.trim() == '') return;
                temp_jam_kerja += '<option '+ (i == 0 ? 'selected' : '') + ' value="'+ jk.trim().split('|')[0] +'">'+ jk.trim().split('|')[1] +'</option>';
            });

            $('input#temp_nozzle').val().split('||').map((n, i) => {
                if(n.trim() == '') return;
                temp_nozzle += '<option '+ (i == 0 ? 'selected' : '') +' value="'+ n.trim().split('|')[0] +'">'+ n.trim().split('|')[1] +'</option>'
            });

            $("#add-schedule").fireModal({
                title: 'Jadwalkan Kerja',
                body: $("#modal-create-schedule"),
                footerClass: 'bg-whitesmoke',
                autoFocus: false,
                onFormSubmit: function(modal, e, form) {
                    modal.find('.modal-body').children('.alert').remove()
                    const member_id = $('.input-employee-sch-id').val();
                    const jabatan = $('.input-jabatan-sch').val();
                    const tanggal_raw = $('.input-date-sch');
                    const jam_kerja_raw = $('.input-jam_kerja-sch');
                    const nozzle_raw = $('.input-nozzle-sch');
                    let tanggal = [];
                    let jam_kerja = [];
                    let nozzle = [];

                    // Tanggal
                    for(let i = 0; i < tanggal_raw.length; i++) {
                        tanggal.push(tanggal_raw[i].value);
                    }
                    // End Tanggal
                    // Jam Kerja
                    for(let i = 0; i < jam_kerja_raw.length; i++) {
                        jam_kerja.push(jam_kerja_raw[i].value);
                    }
                    // End Jam Kerja
                    // Nozzle
                    for(let i = 0; i < nozzle_raw.length; i++) {
                        nozzle.push(nozzle_raw[i].value);
                    }
                    // End Nozzle

                    if($('.input-date-sch').val() == ""){
                        form.stopProgress();
                        modal.find('.modal-body').children('.alert').remove()
                        modal.find('.modal-body').prepend('<div class="alert alert-danger">Tanggal Wajib Diisi!</div>')
                    } else if (jabatan === '0') {
                        form.stopProgress();
                        modal.find('.modal-body').children('.alert').remove()
                        modal.find('.modal-body').prepend('<div class="alert alert-danger">Pegawai Tidak Memiliki Jabatan!</div>')
                    } else {
                        $.ajax({
                            url : document.location.origin + '/api'  + document.location.pathname,
                            type: 'post',
                            data: {
                                '_token': $('meta[name="csrf-token"]').attr('content'),
                                member_id,
                                jabatan,
                                tanggal,
                                jam_kerja,
                                nozzle
                            },
                            success: async function(data) {
                                form.stopProgress();
                                modal.find('.modal-body').children('.alert').remove()
                                modal.find('.modal-body').prepend('<div class="alert alert-success">Pegawai Telah Terjadwal</div>')
                                $('.input-date-sch').val(now[0]+'-'+(now[1] < 10 ? '0'+now[1] : now[1]) + '-' + (now[2] < 10 ? '0'+now[2] : now[2]))
                                $('#calendar').fullCalendar('destroy');
                                await $("#calendar").fullCalendar({
                                    defaultView: 'month',
                                    height: 750,
                                    businessHours: true,
                                    selectable: true,
                                    header: {
                                        left: 'today',
                                        center: 'title',
                                        right: 'month'
                                    },
                                    selectMirror: true,
                                    select: function(arg) {
                                        return false;
                                    },eventClick: function(arg) {
                                        if(window.confirm('Ingin Menghapus Jadwal Kerja Terpilih?')) {
                                            deleteSchedule(arg)
                                        }
                                    },
                                    dayMaxEvents: true,
                                    editable: false,
                                    droppable: false,
                                    drop: function() {
                                    },
                                    events: data.schedule
                                });
                                $('#calendar').fullCalendar('render');

                            }, error: function() {
                                form.stopProgress();
                                modal.find('.modal-body').children('.alert').remove()
                                modal.find('.modal-body').prepend('<div class="alert alert-danger">Mohon Cek Internet Anda.</div>')
                            }
                        })
                    }
                    e.preventDefault();
                },
                shown: function(modal, form) {},
                buttons: [
                    {
                        text: 'Jadwalkan',
                        submit: true,
                        class: 'btn btn-primary btn-shadow',
                        handler: function(modal) {
                        }
                    }
                ]
            });

            $('#add_setting').on('click', function() {
                numberWrapper += 1;
                $('.setting-calendar').append('<div class="card card-primary wrapper" data-wrapper="'+ numberWrapper +'"><div class="card-header"><h4>Kalendar #'+numberWrapper+'</h4></div><div class="card-body"><div class="form-group"><label>Tanggal Kerja</label><div class="input-group"><div class="input-group-prepend"><div class="input-group-text"><i class="fas fa-calendar"></i></div></div><input type="date" value="'+ now[0] + '-' + (now[1] < 10 ? '0'+now[1] : now[1]) + '-' + (now[2] < 10 ? '0'+now[2] : now[2]) +'" class="form-control input-date-sch"></div></div><div class="form-group"><label>Jam Kerja</label><div class="input-group"><div class="input-group-prepend"><div class="input-group-text"><i class="fas fa-clock"></i></div></div><select name="jenis_kerja" class="custom-select input-jam_kerja-sch">'+ temp_jam_kerja +'</select></div></div><div class="form-group"><label>Nozzle</label><div class="input-group"><div class="input-group-prepend"><div class="input-group-text"><i class="fas fa-thermometer-full"></i></div></div><select name="nozzle_id" id="nozzle_id" class="custom-select input-nozzle-sch"><option value="all">Semua Nozzle</option>'+ temp_nozzle +'</select></div></div></div></div>');
            });

            $('#remove_setting').on('click', function() {
                if($('.setting-calendar').children().length > 1) {
                    $('.setting-calendar').children().last().remove();
                    numberWrapper -=1;
                } else {
                    window.alert('Anda tidak dapat menghapus pengaturan kalendar #1')
                }
            });

            $('.jadwal-kerja-href').on('click', async function() {
                let user = $(this).data('user');
                let name = $(this).data('name');

                $('.input-employee-sch-id').val(user)
                $('.input-employee-sch-name').val(name)

                // Jabatan
                let jabatan = ($(this).parent().siblings().children('span')[0].innerText).split(', ')
                $('select#jabatan').attr('disabled', '');
                $('select#jabatan').children().remove();
                let appendJabatan = '';
                if(jabatan.length > 0 && jabatan[0] !== '') {
                    $('select#jabatan').removeAttr('disabled');
                    jabatan.forEach(j => appendJabatan += '<option value="'+ j +'">'+ j +'</option>')
                    $('select#jabatan').append(appendJabatan)
                } else {
                    $('select#jabatan').attr('disabled', '');
                    $('select#jabatan').append('<option value="0">Tidak ada Jabatan</option>')
                }
                // End Jabatan

                $('.jadwal-kerja-href').removeClass('active-schedule');
                $(this).addClass('active-schedule');

                $('#title-calendar').text('Jadwal kerja ' + name);
                let data = [];
                $('.card-calendar').css('display', '')

                await $.ajax({
                    url: document.location.origin + '/api/jadwalkerja/cek',
                    type: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        member_id: user
                    },
                    beforeSend: function() {},
                    success: function(res) {
                        if(res.status == true) {
                            data = res.schedule
                        }
                    },
                    error: function() {}
                })
                $('#calendar').fullCalendar('destroy');
                await $("#calendar").fullCalendar({
                    defaultView: 'month',
                    height: 750,
                    businessHours: true,
                    selectable: true,
                    header: {
                        left: 'today',
                        center: 'title',
                        right: 'month'
                    },
                    selectMirror: true,
                    select: function(arg) {
                        return false;
                    },eventClick: function(arg) {
                        if(confirm('Ingin Menghapus Jadwal Kerja Terpilih?')) {
                            deleteSchedule(arg)
                        }
                    },
                    dayMaxEvents: true,
                    editable: false,
                    droppable: false,
                    drop: function() {
                    },
                    events: data
                });
                $('#calendar').fullCalendar('render');
            })
            function deleteSchedule(arg) {
                $.ajax({
                    url: document.location.origin + '/api/jadwalkerja/delete',
                    type: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        date: arg.start._i,
                        title: arg.title,
                        member_id: $('.input-employee-sch-id').val()
                    },
                    beforeSend: function() {},
                    success: async function(res) {
                        if(res.status == true) {
                            $('#calendar').fullCalendar('destroy');
                                await $("#calendar").fullCalendar({
                                    defaultView: 'month',
                                    height: 750,
                                    businessHours: true,
                                    selectable: true,
                                    header: {
                                        left: 'today',
                                        center: 'title',
                                        right: 'month'
                                    },
                                    selectMirror: true,
                                    select: function(arg) {
                                        return false;
                                    },eventClick: function(arg) {
                                        if(confirm('Ingin Menghapus Jadwal Kerja Terpilih?')) {
                                            deleteSchedule(arg)
                                        }
                                    },
                                    dayMaxEvents: true,
                                    editable: false,
                                    droppable: false,
                                    drop: function() {
                                    },
                                    events: res.schedule
                                });
                                $('#calendar').fullCalendar('render');
                        }
                    },
                    error: function() {}
                })
            }
        })
    }
    if(routeName == 'setoran.open.pj') {
        $('.table-open-pj-setoran').DataTable({
            language: {
                url: cdnDatatableIdn,
            },
            processing: true,
            serverSide: true,
            ajax: document.location.href,
            columns: [
                {data: 'nama_jabatan', name: 'nama_jabatan'},
                {data: 'nama', name: 'nama'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    }
    if(routeName == 'setoran.open.pj.create') {
        $('select#jabatan_id').on('change', function() {
            if($(this).children().first()['0'].value == '0') {
                $(this).children().first().remove();
            }
            $.ajax({
                url: document.location.origin + '/api/setoran/open-penjualan/pj/create',
                method: 'get',
                data: {
                    jabatan_id: $('select#jabatan_id').val(),
                },
                dataType: 'json',
                beforeSend: function() {
                    $('.custom-checkbox').addClass('d-none');
                    $('.custom-control-input').attr('checked', false);
                    $('.msg-pj').removeClass('d-none');
                    $('.msg-pj').text('Loading... ');
                },
                success: function(data) {
                    if(data.message == 'Member has been catched'){
                        if(data.data.length > 0) {
                            $('.msg-pj').addClass('d-none');
                            $('.custom-control.custom-checkbox').map((index, el) => {
                                if($.inArray(el.dataset.id, data.data) != -1) {
                                    el.classList.remove('d-none')
                                }
                            })
                        } else {
                            $('.msg-pj').text("Pegawai Tidak ada di Jabatan yang dipilih");
                        }
                    }
                    else {
                        $('.msg-pj').text("Jabatan Salah");
                    }
                },
                error: function(err) {console.error(err)}
            });
        })
    }
    if(routeName == 'menu.kehadiran') {
        $('.table-jadwal-kerja').DataTable({
            language: {
                url: cdnDatatableIdn,
            },
            processing: true,
            serverSide: true,
            ajax: document.location.href,
            columns: [
                {data: 'status', name: 'status'},
                {data: 'tanggal', name: 'tanggal'},
                {data: 'jam_kerja', name: 'jam_kerja'},
                {data: 'nozzle', name: 'nozzle'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    }
    if(routeName == 'kehadiran.show') {
        if($('#jenis_jam').val() == 'in') {
            $('.btn-from-one-to-two').on('click', function() {
                $('.wizard-content.one').addClass('d-none');
                $('.wizard-step.two').addClass('wizard-step-active')
                $('.wizard-content.two').removeClass('d-none')
            });
            $('.btn-two-to-one').on('click', function() {
                $('.wizard-content.one').removeClass('d-none');
                $('.wizard-step.two').removeClass('wizard-step-active')
                $('.wizard-content.two').addClass('d-none')
            });
            $('.btn-two-to-three').on('click', function() {
                $('.wizard-content.two').addClass('d-none');
                $('.wizard-step.three').addClass('wizard-step-active')
                $('.wizard-content.three').removeClass('d-none')
            });
            $('.btn-three-to-two').on('click', function() {
                $('.wizard-content.two').removeClass('d-none');
                $('.wizard-step.three').removeClass('wizard-step-active');
                $('.wizard-content.three').addClass('d-none');
            })

            const scanner = new QrScanner(document.getElementById('qr-video'), result => {
                $('#cam-qr-result').append(result)
                if(result != '') {
                    stopCamera();
                    checkAndSubmitAttendance(result)
                }
            });

            const checkAndSubmitAttendance = (val) => {
                $.ajax({
                    url: document.location.href + '/api',
                    method: 'put',
                    data: {
                        '_token': $('meta[name="csrf-token"]').attr('content'),
                        abs: val,
                        code: document.location.href.split('/')[document.location.href.split('/').length - 1]
                    },
                    dataType: 'json',
                    beforeSend: function() {
                        $('#start_cam').attr('disabled', '')
                        $('.btn-three-to-two').attr('disabled', '')
                        $('.waiting-alert').removeClass('d-none');
                    },
                    success: function(res) {
                        $('#start_cam').removeAttr('disabled', '')
                        $('.btn-three-to-two').removeAttr('disabled', '')
                        $('.waiting-alert').addClass('d-none');
                        if(res.message == 'qrcode not valid') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Not Valid',
                                text: 'QR Code Tidak Valid!',
                                timer: 2000
                            });
                        }
                        if(res.message == 'qrcode wrong') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Not Valid',
                                text: 'QR Code Salah!',
                                timer: 2000
                            });
                        }
                        if(res.status) {
                            $('.wizard-steps').addClass('d-none');
                            $('.wizard-content.three').addClass('d-none');
                            $('.wizard-content.complete').removeClass('d-none');
                            Swal.fire({
                                icon: 'success',
                                title: 'Absen Berhasil',
                                text: 'Absen Masuk berhasil dicatat',
                                timer: 2000
                            });
                        }
                    },
                    error: function(err) {console.error(err)}
                });
            }

            function stopCamera() {
                scanner.stop();
                $('#start_cam').removeAttr('disabled')
                $('#stop_cam').attr('disabled', '')
            }

            $('#camList').on('change', event => {
                scanner.setCamera(event.target.value).then(() => {});
            });

            QrScanner.listCameras(true).then(cameras => cameras.forEach(camera => {
                $('#cam-list').append('<option value="'+ camera.id +'">' + camera.label + '</option>');
            }));

            $('#start_cam').on('click',function() {
                scanner.start().then(() => {
                });
                $('#start_cam').attr('disabled', '')
                $('#stop_cam').removeAttr('disabled')
            });

            $('#stop_cam').on('click',function () {
                stopCamera();
            });
        } else {
            $('.close-btn-from-one-to-two').on('click', function() {
                $.ajax({
                    url: document.location.href + '/api/out',
                    method: 'put',
                    data: {
                        '_token': $('meta[name="csrf-token"]').attr('content'),
                    },
                    dataType: 'json',
                    beforeSend: function() {
                        $('#start_cam').attr('disabled', '')
                        $('.btn-three-to-two').attr('disabled', '')
                        $('.waiting-alert').removeClass('d-none');
                    },
                    success: function(res) {
                        if(res.status) {
                            $('.wizard-content.close-one').addClass('d-none')
                            $('.wizard-step.close-two').addClass('wizard-step-active')
                            $('.wizard-content.close-complete').removeClass('d-none')
                        }
                    },
                    error: function(err) {console.error(err)}
                });
            })
        }
    }

    $('.table-file-fasilitas').DataTable({
        language: {
            url: cdnDatatableIdn,
        },
        processing: true,
        serverSide: true,
        ajax: document.location.href,
        columns: [
            {data: 'nama_file', name: 'nama_file'},
            {data: 'nama_dokumen', name: 'nama_dokumen'},
            {data: 'tanggal_upload', name: 'tanggal_upload'},
            {data: 'action', name: 'action', orderable: false, searchable: false},
        ]
    });
    $('.table-file-aset').DataTable({
        language: {
            url: cdnDatatableIdn,
        },
        processing: true,
        serverSide: true,
        ajax: document.location.href,
        columns: [
            {data: 'nama_file', name: 'nama_file'},
            {data: 'nama_dokumen', name: 'nama_dokumen'},
            {data: 'tanggal_upload', name: 'tanggal_upload'},
            {data: 'action', name: 'action', orderable: false, searchable: false},
        ]
    });

    if(routeName == 'aset.create' || routeName == 'aset.edit') {
        var cleaveOne = new Cleave('.currency', {
            numeral: true,
            numeralThousandsGroupStyle: 'thousand'
        });
    }

    $(".custom-file-input").on("change", function (e) {
        $(this).siblings().text(e.target.value.split("fakepath")[1]);
        var input = $(e.currentTarget);
        var file = input[0].files[0];
        var reader = new FileReader();
        reader.onload = function (e) {
            $(".img.img-thumbnail.img-temporary").attr("src", e.target.result);
            $(".img.img-thumbnail.img-temporary").removeClass("d-none");
        };
        reader.readAsDataURL(file);
    });

    $("#logout-btn").on("click", function () {
        Swal.fire({
            title: "Keluar ?",
            text: "Keluar dari sistem Monitoring SPBU!",
            icon: "question",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Keluar!",
            cancelButtonText: "Batalkan",
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById("logout-form").submit();
            }
        });
    });
});
