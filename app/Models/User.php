<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * SECURITY NOTE: 'role' and 'status' are in $fillable because admin-only
     * controllers need mass-assignment. All write endpoints enforce ADMIN_PUSAT
     * authorization via FormRequest->authorize(). If adding new endpoints that
     * accept user input and call create()/update(), ensure authorization is checked.
     */
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
