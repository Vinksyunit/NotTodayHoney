<?php

namespace Vinksyunit\NotTodayHoney\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CredentialAttempt extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'nt_honey_credential_attempts';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'attacker_detection_id',
        'credential_id',
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
