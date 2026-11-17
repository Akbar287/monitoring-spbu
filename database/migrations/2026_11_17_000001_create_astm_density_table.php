<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('astm_density', function (Blueprint $table) {
            $table->id();
            $table->decimal('density_observed', 7, 4);
            $table->decimal('suhu', 5, 2);
            $table->decimal('density_15', 7, 4);
            $table->unsignedSmallInteger('halaman');
            $table->string('sumber');
            $table->char('sumber_sha256', 64);
            $table->unique(['density_observed', 'suhu']);
        });

        $manifest = json_decode(file_get_contents(database_path('data/astm_d53.json')), true, 512, JSON_THROW_ON_ERROR);
        $file = fopen(database_path('data/astm_d53.csv'), 'r');
        try {
            fgetcsv($file);
            $batch = [];
            while (($row = fgetcsv($file)) !== false) {
                $batch[] = array_combine(['density_observed', 'suhu', 'density_15', 'halaman'], $row) + [
                    'sumber' => $manifest['sumber'], 'sumber_sha256' => $manifest['sha256'],
                ];
                if (count($batch) === 100) {
                    DB::table('astm_density')->insert($batch);
                    $batch = [];
                }
            }
            if ($batch) {
                DB::table('astm_density')->insert($batch);
            }
        } finally {
            fclose($file);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('astm_density');
    }
};
