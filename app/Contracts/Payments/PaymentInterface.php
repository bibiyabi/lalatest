<?php

namespace App\Contracts\Payments;

use App\Repositories\KeysRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

interface PaymentInterface
{

    public function checkInputData($request);

    public function toOrderQueue();

}
