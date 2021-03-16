<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GatewayType extends Model
{
    use HasFactory;

    protected $table = 'gateway_types';

    protected $fillable = [
        'gateways_id',
        'types_id',
        'is_support_deposit',
        'is_support_withdraw',
    ];

    public function gateway()
    {
        return $this->belongsTo(Gateway::class,'gateways_id','id');
    }


}
