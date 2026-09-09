<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('calon')) {
            Schema::create('calon', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('desa_id')->constrained('desa')->cascadeOnUpdate()->cascadeOnDelete();
                $table->integer('no_urut');
                $table->string('nama_calon', 150);
                $table->string('foto', 255)->nullable();
                $table->string('asal_banjar', 150)->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('tps')) {
            Schema::create('tps', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('desa_id')->constrained('desa')->cascadeOnUpdate()->cascadeOnDelete();
                $table->integer('no_tps');
                $table->string('banjar_tps', 100);
                $table->integer('jml_pml_tetap')->default(0);
                $table->integer('mgn_hak_suara')->default(0);
                $table->integer('tdk_mgn_hak_suara')->default(0);
                $table->integer('suara_tdk_sah')->default(0);
                $table->integer('suara_sah')->default(0);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('tps_calon_votes')) {
            Schema::create('tps_calon_votes', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('tps_id')->constrained('tps')->cascadeOnUpdate()->cascadeOnDelete();
                $table->foreignUuid('calon_id')->constrained('calon')->cascadeOnUpdate()->cascadeOnDelete();
                $table->integer('jumlah_suara')->default(0);
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tps_calon_votes');
        Schema::dropIfExists('tps');
        Schema::dropIfExists('calon');
    }
};
