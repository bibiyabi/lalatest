<?php


namespace App\Contracts\Payments;

use App\Constants\Payments\Type;

class Placeholder
{
    protected $type;
    protected $account;
    protected $merchantNumber;
    protected $publicKey;
    protected $privateKey;
    protected $md5Key;
    protected $notifyUrl;
    protected $returnUrl;
    protected $transactionType;
    protected $coin;
    protected $blockchainContract;
    protected $cryptoAddress;
    protected $apiKey;
    protected $note1;
    protected $note2;

    public function __construct(
        string $type = null,
        string $account = null,
        string $merchantNumber = null,
        string $publicKey = null,
        string $privateKey = null,
        string $md5Key = null,
        string $notifyUrl = null,
        string $returnUrl = null,
        array $transactionType = null,
        array $coin = null,
        array $blockchainContract = null,
        string $cryptoAddress = null,
        string $apiKey = null,
        string $note1 = null,
        string $note2 = null
    )
    {
        $this->type = $type;
        $this->account = $account;
        $this->merchantNumber = $merchantNumber;
        $this->publicKey = $publicKey;
        $this->privateKey = $privateKey;
        $this->md5Key = $md5Key;
        $this->notifyUrl = $notifyUrl;
        $this->returnUrl = $returnUrl;
        $this->transactionType = $transactionType;
        $this->coin = $coin;
        $this->blockchainContract = $blockchainContract;
        $this->cryptoAddress = $cryptoAddress;
        $this->apiKey = $apiKey;
        $this->note1 = $note1;
        $this->note2 = $note2;
    }


    public function toArray()
    {
        $type = $this->type;
        $result = [];
        if ($type == Type::CRYPTO_CURRENCY){
            $result = [
                'transactionType'       => $this->transactionType,
                'coin'                  => $this->coin,
                'blockchainContract'    => $this->blockchainContract,
                'apiKey'                => $this->apiKey,
                'cryptoAddress'         => $this->cryptoAddress,
            ];
        }elseif($type == Type::CREDIT_CARD){
            $result = [
                'account'               => $this->account,
                'merchantNumber'        => $this->merchantNumber,
                'transactionType'       => $this->transactionType,
            ];
        }elseif($type == Type::WALLET){
            $result = [
                'account'               => $this->account,
                'merchantNumber'        => $this->merchantNumber,
                'transactionType'       => $this->transactionType,
            ];
        }elseif ($type == Type::BANK_CARD){
            $result = [
                'account'               => $this->account,
                'merchantNumber'        => $this->merchantNumber,
            ];
        }

        $result += [
            'publicKey'                 => $this->publicKey,
            'privateKey'                => $this->privateKey,
            'md5Key'                    => $this->md5Key,
            'notifyUrl'                 => $this->notifyUrl,
            'returnUrl'                 => $this->returnUrl,
            'note1'                     => $this->note1,
            'note2'                     => $this->note2,
        ];

        return $result;
    }

}
