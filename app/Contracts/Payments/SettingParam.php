<?php


namespace App\Contracts\Payments;

class SettingParam
{
    private $infoTitle;
    private $account;
    private $merchant;
    private $md5Key;
    private $publicKey;
    private $privateKey;
    private $returnUrl;
    private $notifyUrl;
    private $coin;
    private $blockchainContract;
    private $cryptoAddress;
    private $apiKey;
    private $note1;
    private $note2;

    public function __construct(
        string $infoTitle = null,
        string $account = null,
        string $merchant = null,
        string $type = null,
        string $publicKey = null,
        string $privateKey = null,
        string $md5Key = null,
        string $notifyUrl = null,
        string $returnUrl = null,
        string $transactionType = null,
        string $coin = null,
        string $blockchainContract = null,
        string $cryptoAddress = null,
        string $apiKey = null,
        string $note1 = null,
        string $note2 = null
    )
    {
        $this->infoTitle = $infoTitle;
        $this->account = $account;
        $this->merchant = $merchant;
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
        $data = json_decode($json, true);

        return new SettingParam(
            $data['infoTitle'] ?? '',
            $data['account'] ?? '',
            $data['merchant'] ?? '',
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

    /**
     * Get the value of 信息標題
     */
    public function getInfoTitle()
    {
        return $this->infoTitle;
    }

    /**
     * Get the value of 金流帳戶號
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * Get the value of 金流商戶號
     */
    public function getMerchant()
    {
        return $this->merchant;
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
