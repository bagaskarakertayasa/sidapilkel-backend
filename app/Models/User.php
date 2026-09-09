<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'nama_depan',
        'nama_belakang',
        'username',
        'email',
        'password',
        'role',
        'status',
        'desa_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    public function getRoleAttribute(?string $value): string
    {
        return $value ?: (empty($this->attributes['desa_id']) ? 'ADMIN_PUSAT' : 'ADMIN_DESA');
    }

    public function desa(): BelongsTo
    {
        return $this->belongsTo(Desa::class, 'desa_id');
    }

    public function isAdminPusat(): bool
    {
        return $this->role === 'ADMIN_PUSAT';
    }

    public function isAdminDesa(): bool
    {
        return $this->role === 'ADMIN_DESA';
    }

    public function isAktif(): bool
    {
        return $this->status === 'aktif';
    }
}
