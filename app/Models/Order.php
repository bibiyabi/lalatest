<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'user_id',
        'user_order_id',
        'key_id',
        'amount',
        'real_amount',
        'gateway_id',
        'status_id',
        'order_param',
    ];

    public function key()
    {
        return $this->belongsTo(Key::class);
    }
}
