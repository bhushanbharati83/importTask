<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Upload extends Model
{
    protected $keyType = 'string';  // uuid primary key
    public $incrementing = false;

    protected $fillable = [
        'id',
        'filename',
        'size',
        'status',
        'checksum',
        'received_bytes'
    ];
}
