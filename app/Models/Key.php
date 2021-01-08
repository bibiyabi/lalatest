<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Key extends Model
{
//    use HasFactory;

    protected $table = 'keys';
//    protected $timestamps = true;

    protected $fillable = [
        'user_id',
        'gateway_type_id',
        'keys',
    ];

}
