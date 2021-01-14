<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
//    use HasFactory;

    protected $table = 'settings';

    protected $fillable = [
        'user_id',
        'gateway_type_id',
        'settings',
    ];

    public function gateway()
    {
        return $this->belongsTo(Gateway::class);
    }
}
