<?php

declare(strict_types=1);

namespace Vinksyunit\NotTodayHoney\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrapAttempt extends Model
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('not-today-honey.storage.tables.trap_attempts', 'nt_honey_trap_attempts');
        $this->connection = config('not-today-honey.storage.connection');
    }

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'attacker_detection_id',
        'trap_name',
        'path',
        'method',
        'headers',
        'created_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'headers' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Get the attacker detection that owns this trap attempt.
     */
    public function attackerDetection(): BelongsTo
    {
        return $this->belongsTo(AttackerDetection::class);
    }
}
