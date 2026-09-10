<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Persistent storage for revoked JWT tokens (replaces volatile Cache-based blacklist).
     */
    public function up(): void
    {
        Schema::create('revoked_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('jti', 64)->unique();
            $table->timestamp('expires_at')->index();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('revoked_tokens');
    }
};
