<?php


namespace App\Services\Payments\Deposit;

use App\Models\Order;
use App\Constants\Payments\Status;
use Illuminate\Support\Facades\Http;
use App\Repositories\MerchantRepository;
use App\Services\Signature;
use Log;

class DepositNotify
{
    private $repo;

    public function __construct(MerchantRepository $repo) {
        $this->repo = $repo;
    }

    public function notify(Order $order): bool
    {
        $merchant = $order->merchant;
        $key = $this->repo->getKey($merchant);
        $url = $this->repo->getNotifyUrl($merchant);

        $data = [
            'orderId' => $order->order_id,
            'status' => $order->status === Status::CALLBACK_SUCCESS ? '000' : '001',
        ];
        $data['signature'] = Signature::makeSign($data, $key);

        $response = Http::post($url, $data)->json();

        if (!isset($response['status']) || $response['status'] !== '200') {
            Log::error('Deposit-Notify failed', $response ?? []);
            return false;
        }

        Log::info('Deposit-Notify success');
        return true;
    }
}
