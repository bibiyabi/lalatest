<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gateway extends Model
{
    use HasFactory;

    protected $table = 'gateways';

    protected $fillable = [
        'name',
        'real_name',
    ];

    public function gatewayTypes()
    {
        return $this->hasMany(GatewayType::class, 'gateways_id', 'id');
    }

    public function settings()
    {
        return $this->hasMany(Setting::class,'gateway_id', 'id');
    }
}
