<?php

namespace App\Contracts\Payments;

class OrderParam
{
    private $orderId;
    private $amount;
    private $bankName;
    private $accountName;
    private $txnTime;
    private $screenshot;
    private $txId;
    private $cardNumber;
    private $mobile;
    private $accountId;
    private $email;
    private $country;
    private $state;
    private $city;
    private $address;
    private $zip;
    private $lastName;
    private $firstName;
    private $telegram;
    private $expiredDate;
    private $transactionType;
    private $ifsc;
    private $bankProvince;
    private $bankAddress;
    private $bankCity;

    public function __construct(
        $orderId = null,
        $amount = null,
        $bankName = null,
        $accountName = null,
        $txnTime = null,
        $screenshot = null,
        $txId = null,
        $cardNumber = null,
        $mobile = null,
        $accountId = null,
        $email = null,
        $country = null,
        $state = null,
        $city = null,
        $address = null,
        $zip = null,
        $lastName = null,
        $firstName = null,
        $telegram = null,
        $expiredDate = null,
        $transactionType = null,
        $ifsc = null,
        $bankProvince = null,
        $bankAddress = null,
        $bankCity = null
    ) {
        $this->order_id = $orderId;
        $this->amount = $amount;
        $this->bank_name = $bankName;
        $this->account_name = $accountName;
        $this->txn_time = $txnTime;
        $this->screenshot = $screenshot;
        $this->tx_id = $txId;
        $this->card_number = $cardNumber;
        $this->mobile = $mobile;
        $this->account_id = $accountId;
        $this->email = $email;
        $this->country = $country;
        $this->state = $state;
        $this->city = $city;
        $this->address = $address;
        $this->zip = $zip;
        $this->last_name = $lastName;
        $this->first_name = $firstName;
        $this->telegram = $telegram;
        $this->expired_date = $expiredDate;
        $this->transaction_type = $transactionType;
        $this->ifsc = $ifsc;
        $this->bank_province = $bankProvince;
        $this->bank_address = $bankAddress;
        $this->bank_city = $bankCity;
    }

    public static function createFromJson(string $json)
    {
        $data = json_decode($json, true);

        return new OrderParam(
            $data['order_id']           ?? null,
            $data['amount']           ?? null,
            $data['bank_name']        ?? null,
            $data['account_name']     ?? null,
            $data['txn_time']         ?? null,
            $data['screenshot']       ?? null,
            $data['tx_id']            ?? null,
            $data['card_number']      ?? null,
            $data['mobile']           ?? null,
            $data['account_id']       ?? null,
            $data['email']            ?? null,
            $data['country']          ?? null,
            $data['state']            ?? null,
            $data['city']             ?? null,
            $data['address']          ?? null,
            $data['zip']              ?? null,
            $data['last_name']        ?? null,
            $data['first_name']       ?? null,
            $data['telegram']         ?? null,
            $data['expired_date']     ?? null,
            $data['transaction_type'] ?? null,
            $data['ifsc']             ?? null,
            $data['bank_province']    ?? null,
            $data['bank_address']     ?? null,
            $data['bank_city']        ?? null,
        );
    }

    /**
     * Get the value of orderId
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * Get the value of amount
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Get the value of bankName
     */
    public function getBankName()
    {
        return $this->bankName;
    }

    /**
     * Get the value of accountName
     */
    public function getAccountName()
    {
        return $this->accountName;
    }

    /**
     * Get the value of txnTime
     */
    public function getTxnTime()
    {
        return $this->txnTime;
    }

    /**
     * Get the value of screenshot
     */
    public function getScreenshot()
    {
        return $this->screenshot;
    }

    /**
     * Get the value of txId
     */
    public function getTxId()
    {
        return $this->txId;
    }

    /**
     * Get the value of cardNumber
     */
    public function getCardNumber()
    {
        return $this->cardNumber;
    }

    /**
     * Get the value of mobile
     */
    public function getMobile()
    {
        return $this->mobile;
    }

    /**
     * Get the value of accountId
     */
    public function getAccountId()
    {
        return $this->accountId;
    }

    /**
     * Get the value of email
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Get the value of country
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Get the value of state
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Get the value of city
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Get the value of address
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Get the value of zip
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * Get the value of lastName
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Get the value of firstName
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Get the value of telegram
     */
    public function getTelegram()
    {
        return $this->telegram;
    }

    /**
     * Get the value of expiredDate
     */
    public function getExpiredDate()
    {
        return $this->expiredDate;
    }

    /**
     * Get the value of transactionType
     */
    public function getTransactionType()
    {
        return $this->transactionType;
    }

    /**
     * Get the value of ifsc
     */
    public function getIfsc()
    {
        return $this->ifsc;
    }

    /**
     * Get the value of bankProvince
     */
    public function getBankProvince()
    {
        return $this->bankProvince;
    }

    /**
     * Get the value of bankAddress
     */
    public function getBankAddress()
    {
        return $this->bankAddress;
    }

    /**
     * Get the value of bankCity
     */
    public function getBankCity()
    {
        return $this->bankCity;
    }
}