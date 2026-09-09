<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Desa extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'desa';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'nama_desa',
        'kecamatan',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'desa_id');
    }

    public function calon(): HasMany
    {
        return $this->hasMany(Calon::class, 'desa_id');
    }

    public function tps(): HasMany
    {
        return $this->hasMany(TPS::class, 'desa_id');
    }
}
