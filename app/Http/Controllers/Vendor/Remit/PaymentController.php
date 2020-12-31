<?php

namespace App\Http\Controllers\Vendor\Remit;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Jobs\Remit\Order as JobOrder;


class Payment extends Controller
{
    public function setOrderToProcessing(Request $request) {
        #set db
        $this->dispatch(new JobOrder($request));

        echo 'endOrder';
    }

    public function reset() {

    }



}
