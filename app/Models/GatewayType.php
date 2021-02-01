<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GatewayType extends Model
{
    use HasFactory;

    protected $table = 'gateway_types';

    public function gateway()
    {
        return $this->belongsTo(Gateway::class);
    }


}
