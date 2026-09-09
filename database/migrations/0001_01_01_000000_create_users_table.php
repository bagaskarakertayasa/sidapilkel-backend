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
        Schema::create('desa', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama_desa', 100)->unique();
            $table->string('kecamatan', 100)->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama_depan', 100);
            $table->string('nama_belakang', 100)->nullable();
            $table->string('username', 100)->unique();
            $table->string('email', 150)->nullable()->unique();
            $table->string('password', 255);
            $table->string('role', 50)->default('ADMIN_DESA');
            $table->string('status', 50)->default('aktif');
            $table->foreignUuid('desa_id')->nullable()->constrained('desa')->cascadeOnUpdate()->nullOnDelete();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignUuid('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
        Schema::dropIfExists('desa');
    }
};
