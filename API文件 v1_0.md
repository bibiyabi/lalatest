# API文件 V1.0

## API必帶參數

| 欄位          | 型態    | 必要參數  | 說明                     |
| ------------ | ------ | -------- | ------------------------|
| sign         | string | V        | signature               |

## API Header必帶參數

| 欄位          | 型態    | 欄位值   | 說明                     |
| ------------ | ------ | ------- | :-----------------------|
| name         | string |  java   | API呼叫者                |


### 新增資料設置

```plaintext
POST /api/key
```


| 欄位          | 型態     | 必要參數   | 說明                     |
| ------------ | ------  | ------- | :-----------------------  |
| id            | integer |  V   | 出/入款id /java unique  id   |
| info_title    | string |     | 信息名稱                |
| gateway_id    | integer |  V   | 金流商/交易所 id  |
| transaction_type      | string |     | 交易方式-出入款:信用卡/電子錢包 |
| account       | string |     | 金流帳戶號         |
| merchant_number| string |     | 金流商戶號         |
| md5_key       | string |     | md5                |
| public_key    | string |     | 公鑰                |
| private_key   | string |     | 私鑰                |
| return_url    | string |     | 同步地址                |
| notify_url    | string |     | 異步地址                |
| coin          | string |     | 幣種-加密貨幣                |
| blockchain_contract    | string |     | 區塊鍊網路-加密貨幣                |
| crypto_address | string |     | 充值地址-加密貨幣                |
| api_key        | string |     | API Key-加密貨幣                |
| note1         | string |     | 備注欄位1                |
| note2         | string |     | 備注欄位2                |


Response example:

```json
{
    "success": true,
    "code": 100,
    "locale": "en",
    "message": "传送成功",
    "data": null
}
```

```json
{
    "success": false,
    "code": 101,
    "locale": "en",
    "message": "传送失败",
    "data": null,
    "debug": []
}
```

```json
{
    "success": false,
    "code": 111,
    "locale": "en",
    "message": "请输入完整信息",
    "data": null,
    "debug": []
}
```


### 金流商/交易所下拉選單


```plaintext
GET /api/vendor/list
```

| 欄位                 | 型態     | 必要參數   | 說明                     |
| ------------        | ------  | -------   | :-----------------------|
| is_deposit          | integer |  V        | 出款=1 / 入款=0            |
| type                | string  |  V        | 渠道名稱:bank_card, e_wallet, cryptocurrency, credit_card  |

Response example:

```json
{
    "success": true,
        "code": 100,
        "locale": "en",
        "message": "传送成功",
        "data": {
            "values": [
                {
                    "id": "2",
                    "name": "Inrusdt"
                },
                {
                    "id": "1",
                    "name": "ApplePay"
                }
            ]
        }
}
```
無金流商/交易所
```json
{
    "success": true,
        "code": 107,
        "locale": "en",
        "message": "查无资料",
        "data": {
            "values": []
        }
}
```

### 提示字

```plaintext
GET /api/placeholder
```

| 欄位                 | 型態     | 必要參數   | 說明                     |
| ------------        | ------  | -------   | :-----------------------|
| is_deposit          | integer |  V        | 出款=1 /入款=0             |
| type                 | string |  V        | 渠道名稱:bank_card, e_wallet, cryptocurrency, credit_card                   |
| gateway_name        | string |  V         | 金流商/交易所名稱            |


```json
{
    "success": true,
        "code": 100,
        "locale": "en",
        "message": "传送成功",
        "data": {
            "publicKey": "hello world",
            "privateKey": "666",
            "md5Key": "666",
            "notifyUrl": "http://google.com",
            "returnUrl": "http://google.com",
            "transactionType": [    // 交易方式
                "UPI",
                "PAYATM"
            ],
            "coin": [   // 幣種
                "USDT",
                "BITCOIN"
            ],
            "blockchainContract": [  // 區塊練網路
                       "TR20",
                       "CC60"
            ],
            "apiKey": "key",
            "note1": "lala",
            "note2": "yoyo"
        }
}
```
