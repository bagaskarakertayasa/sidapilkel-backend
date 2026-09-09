<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class TPS extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tps';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'desa_id',
        'no_tps',
        'banjar_tps',
        'jml_pml_tetap',
        'mgn_hak_suara',
        'tdk_mgn_hak_suara',
        'suara_tdk_sah',
        'suara_sah',
    ];

    protected function casts(): array
    {
        return [
            'no_tps'            => 'integer',
            'jml_pml_tetap'     => 'integer',
            'mgn_hak_suara'     => 'integer',
            'tdk_mgn_hak_suara' => 'integer',
            'suara_tdk_sah'     => 'integer',
            'suara_sah'         => 'integer',
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

    public function desa(): BelongsTo
    {
        return $this->belongsTo(Desa::class, 'desa_id');
    }

    public function calonVotes(): HasMany
    {
        return $this->hasMany(TPSCalonVote::class, 'tps_id');
    }
}
