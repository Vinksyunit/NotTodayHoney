<?php

declare(strict_types=1);

namespace Vinksyunit\NotTodayHoney\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CredentialAttempt extends Model
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('not-today-honey.storage.tables.credential_attempts', 'nt_honey_credential_attempts');
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
        'password_hash',
        'username_used',
        'password_matched',
        'username_matched',
        'created_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'password_matched' => 'boolean',
        'username_matched' => 'boolean',
        'created_at' => 'datetime',
    ];

    /**
     * Get the attacker detection that owns this credential attempt.
     */
    public function attackerDetection(): BelongsTo
    {
        return $this->belongsTo(AttackerDetection::class);
    }
}
