<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class TPSCalonVote extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tps_calon_votes';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'tps_id',
        'calon_id',
        'jumlah_suara',
    ];

    protected function casts(): array
    {
        return [
            'jumlah_suara' => 'integer',
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

    public function tps(): BelongsTo
    {
        return $this->belongsTo(TPS::class, 'tps_id');
    }

    public function calon(): BelongsTo
    {
        return $this->belongsTo(Calon::class, 'calon_id');
    }
}
