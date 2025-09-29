<?php

namespace Vendor\UserDiscounts\Models;

use Illuminate\Database\Eloquent\Model;

class DiscountAudit extends Model
{
    protected $table = 'discount_audits';
    protected $fillable = ['user_id', 'discount_id', 'action', 'meta'];
    protected $casts = ['meta' => 'array'];
}
