<?php
namespace App\Payment\Withdraw;
use App\Services\AbstractDepositPayment;
use App\Validations\ApplyPayValidation;
use App\Exceptions\WithdrawException;
use App\Collections\ApplePayCollection;
use App\Jobs\Payment\Withdraw\Order;
use App\Contracts\Payments\PaymentInterface;
use Illuminate\Http\Request;
use App\Repositories\KeysRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Bus;

use Throwable;


class Payment implements PaymentInterface
{

    private $postData;

    public function checkInputData($postData)  {
        $this->postData = $postData;
        #Log::channel('withdraw')->info(__LINE__ , $this->postData);
        if (false) {
            throw new WithdrawException("asdsd");
        }
        return $this;
    }

    public function toOrderQueue()  {

        echo __LINE__ ."\r\n";
        #set db
        Bus::chain([
            new Order($this->postData),
        ])->catch(function (Throwable $e) {
            echo $e->getMessage() . __LINE__ . "\r\n";

        })->dispatch();

        echo __LINE__ ."\r\n";
        echo 'endOrder';
    }
}
