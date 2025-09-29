<?php

namespace Vendor\UserDiscounts\Models;

use Illuminate\Database\Eloquent\Model;

class UserDiscount extends Model
{
    protected $table = 'user_discounts';
    protected $fillable = [
        'user_id',
        'discount_id',
        'usage_count'
    ];
    public $timestamps = true;
    public function discount()
    {
        return $this->belongsTo(Discount::class);
    }
}
