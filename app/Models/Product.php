<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    //

    protected $fillable = [
        'sku',
        'name',
        'price',
        'primary_image_id',

    ];
}
