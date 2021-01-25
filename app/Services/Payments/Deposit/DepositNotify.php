<?php


namespace App\Services\Payments\Deposit;

use App\Models\Order;
use App\Constants\Payments\Status;
use App\Exceptions\NotifyException;
use Illuminate\Support\Facades\Http;
use App\Repositories\MerchantRepository;
use App\Services\Signature;
use Illuminate\Support\Facades\Log;

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
        $url = $this->repo->getNotifyUrl($merchant) . '/deposit/result';

        $data = [
            'orderId' => $order->order_id,
            'status' => $order->status === Status::CALLBACK_SUCCESS ? '000' : '001',
        ];
        if ($order->status === Status::CALLBACK_SUCCESS) {
            $data['amount'] = $order->real_amount;
        }
        $data['signature'] = Signature::makeSign($data, $key);

        Log::info('Deposit-Notify Url:' . $url . '. Data:', $data);
        $response = Http::asForm()->post($url, $data)->json();

        if (!isset($response['status']) || $response['status'] !== '200') {
            Log::info('Deposit-Notify failed', $response ?? []);
            throw new NotifyException();
        }

        Log::info('Deposit-Notify success');
        return true;
    }
}
