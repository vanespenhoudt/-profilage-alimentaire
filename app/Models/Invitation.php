<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invitation extends Model
{
    protected $fillable = ['email', 'invited_by', 'token', 'role', 'used_at', 'expires_at'];

    protected function casts(): array
    {
        return [
            'used_at'    => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function isUsed(): bool
    {
        return $this->used_at !== null;
    }

    public function isExpired(): bool
    {
        return !$this->isUsed()
            && $this->expires_at !== null
            && $this->expires_at->isPast();
    }

    public function isPending(): bool
    {
        return !$this->isUsed() && !$this->isExpired();
    }
}
