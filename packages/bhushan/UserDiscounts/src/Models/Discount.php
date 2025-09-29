<?php

namespace Vendor\UserDiscounts\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Discount extends Model
{
    protected $table = 'discounts';
    protected $fillable = [
        'name',
        'type',
        'value',
        'active',
        'starts_at',
        'ends_at',
        'priority',
        'usage_cap_per_user'
    ];

    protected $casts = [
        'active' => 'boolean',
        'value' => 'float',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];
    public function userDiscounts(): HasMany
    {
        return $this->hasMany(UserDiscount::class);
    }
    public function isActive(): bool
    {
        if (! $this->active) return false;
        $now = now();
        if ($this->starts_at && $this->starts_at->greaterThan($now)) return
            false;

        if ($this->ends_at && $this->ends_at->lessThan($now)) return false;
        return true;
    }
}
