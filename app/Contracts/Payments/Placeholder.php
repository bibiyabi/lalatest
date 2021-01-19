<?php


namespace App\Contracts\Payments;

use App\Constants\Payments\Type;

class Placeholder
{
    protected $type;
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
        $typeArray = Type::typeName;
        $result = [];
        if ($type == $typeArray[4]){  # 加密貨幣
            $result = [
                'transactionType'       => $this->transactionType,
                'coin'                  => $this->coin,
                'blockchainContract'    => $this->blockchainContract,
                'apiKey'                => $this->apiKey,
                'cryptoAddress'         => $this->cryptoAddress,
            ];
        }elseif($type == $typeArray[1]){  # 信用卡
            $result = [
                'transactionType'       => $this->transactionType,
            ];
        }elseif($type == $typeArray[3]){  # 電子錢包
            $result = [
                'transactionType'       => $this->transactionType,
            ];
        }

        $result += [
            'publicKey'                 => $this->publicKey,
            'privateKey'                => $this->privateKey,
            'md5Key'                    =>$this->md5Key,
            'notifyUrl'                 =>$this->notifyUrl,
            'returnUrl'                 =>$this->returnUrl,
            'note1'                     =>$this->note1,
            'note2'                     =>$this->note2,
        ];

        return $result;
    }

}
