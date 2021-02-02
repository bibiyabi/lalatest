<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gateway extends Model
{
    use HasFactory;

    protected $table = 'gateways';

    public function gatewayTypes()
    {
        return $this->hasMany(GatewayType::class, 'gateways_id', 'id');
    }
}
