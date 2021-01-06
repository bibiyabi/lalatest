<?php

namespace App\Contracts\Payments;

use App\Repositories\KeysRepository;
use Illuminate\Http\Request;

interface PaymentInterface
{

    public function checkInputData(Request $request);

    public function toOrderQueue();

}
