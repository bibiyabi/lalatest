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

    public static function createFromJson(string $json)
    {
        $data = json_decode($json);

        return new Placeholder(
            $data['type'] ?? '',
            $data['public_key'] ?? '',
            $data['private_key'] ?? '',
            $data['md5_key'] ?? '',
            $data['notify_url'] ?? '',
            $data['return_url'] ?? '',
            $data['transaction_type'] ?? '',
            $data['coin'] ?? '',
            $data['blockchain_contract'] ?? '',
            $data['crypto_address'] ?? '',
            $data['api_key'] ?? '',
            $data['note1'] ?? '',
            $data['note2'] ?? '',
        );
    }

    public function toArray()
    {
        $type = $this->type;
        $result = [];
        if ($type == Type::CRYPTO_CURRENCY){  # 加密貨幣
            $result = [
                'transactionType'       => $this->transactionType,
                'coin'                  => $this->coin,
                'blockchainContract'    => $this->blockchainContract,
                'apiKey'                => $this->apiKey,
                'cryptoAddress'         => $this->cryptoAddress,
            ];
        }elseif($type == Type::CREDIT_CARD){  # 信用卡
            $result = [
                'transactionType'       => $this->transactionType,
            ];
        }elseif($type == Type::WALLET){  # 電子錢包
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


    /**
     * 取得類型
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * 取得公鑰
     */
    public function getPublicKey()
    {
        return $this->publicKey;
    }

    /**
     * 取得私鑰
     */
    public function getPrivateKey()
    {
        return $this->privateKey;
    }

    /**
     * 取得 md5 金鑰
     */
    public function getMd5Key()
    {
        return $this->md5Key;
    }

    /**
     * 取得同步通知地址
     */
    public function getNotifyUrl()
    {
        return $this->notifyUrl;
    }

    /**
     * 取得異步通知地址
     */
    public function getReturnUrl()
    {
        return $this->returnUrl;
    }

    /**
     * 取得第三方之通道
     */
    public function getTransactionType()
    {
        return $this->transactionType;
    }

    /**
     * 幣種-加密貨幣
     */
    public function getCoin()
    {
        return $this->coin;
    }

    /**
     * 取得區塊鍊網路
     */
    public function getBlockchainContract()
    {
        return $this->blockchainContract;
    }

    /**
     * 充值地址
     */
    public function getCryptoAddress()
    {
        return $this->cryptoAddress;
    }

    /**
     * Get the value of apiKey
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * 備注欄位1
     */
    public function getNote1()
    {
        return $this->note1;
    }

    /**
     * 備注欄位2
     */
    public function getNote2()
    {
        return $this->note2;
    }
}
