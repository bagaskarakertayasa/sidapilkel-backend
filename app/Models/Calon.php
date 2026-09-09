<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Calon extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'calon';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'desa_id',
        'no_urut',
        'nama_calon',
        'foto',
        'asal_banjar',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    public function desa(): BelongsTo
    {
        return $this->belongsTo(Desa::class, 'desa_id');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(TPSCalonVote::class, 'calon_id');
    }

    public function tpsCalonVotes(): HasMany
    {
        return $this->hasMany(TPSCalonVote::class, 'calon_id');
    }
}
