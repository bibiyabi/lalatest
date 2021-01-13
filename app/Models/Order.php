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
        'key_id',
        'amount',
        'real_amount',
        'gateway_id',
        'status',
        'order_param',
    ];

    public function key()
    {
        return $this->belongsTo(Key::class);
    }

    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }
}
