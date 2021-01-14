# API文件 V1.0

## API必帶參數

| 欄位 | 型態   | 必要參數 | 說明      |
| ---- | ------ | -------- | --------- |
| sign | string | V        | signature |

## API Header必帶參數

| 欄位 | 型態   | 欄位值 | 說明      |
| ---- | ------ | ------ | :-------- |
| name | string | java   | API呼叫者 |


### 新增資料設置

```plaintext
POST /api/key
```


| 欄位                | 型態    | 必要參數 | 說明                            |
| ------------------- | ------- | -------- | :------------------------------ |
| id                  | integer | V        | 出/入款id /java unique  id      |
| info_title          | string  |          | 信息名稱                        |
| gateway_id          | integer | V        | 金流商/交易所 id                |
| transaction_type    | string  |          | 交易方式-出入款:信用卡/電子錢包 |
| account             | string  |          | 金流帳戶號                      |
| merchant_number     | string  |          | 金流商戶號                      |
| md5_key             | string  |          | md5                             |
| public_key          | string  |          | 公鑰                            |
| private_key         | string  |          | 私鑰                            |
| return_url          | string  |          | 同步地址                        |
| notify_url          | string  |          | 異步地址                        |
| coin                | string  |          | 幣種-加密貨幣                   |
| blockchain_contract | string  |          | 區塊鍊網路-加密貨幣             |
| crypto_address      | string  |          | 充值地址-加密貨幣               |
| api_key             | string  |          | API Key-加密貨幣                |
| note1               | string  |          | 備注欄位1                       |
| note2               | string  |          | 備注欄位2                       |


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

### 刪除資料設置

```plaintext
DELETE /api/key
```


| 欄位 | 型態    | 必要參數 | 說明                   |
| ---- | ------- | -------- | :--------------------- |
| id   | integer | V        | 設置資料 id (from php) |

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


### 金流商/交易所下拉選單


```plaintext
GET /api/vendor/list
```

| 欄位       | 型態    | 必要參數 | 說明                                                      |
| ---------- | ------- | -------- | :-------------------------------------------------------- |
| is_deposit | integer | V        | 出款=1 / 入款=0                                           |
| type       | string  | V        | 渠道名稱:bank_card, e_wallet, cryptocurrency, credit_card |

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

| 欄位         | 型態    | 必要參數 | 說明                                                      |
| ------------ | ------- | -------- | :-------------------------------------------------------- |
| is_deposit   | integer | V        | 出款=1 /入款=0                                            |
| type         | string  | V        | 渠道名稱:bank_card, e_wallet, cryptocurrency, credit_card |
| gateway_name | string  | V        | 金流商/交易所名稱                                         |


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
            "transactionType": [
                "UPI",
                "PAYATM"
            ],
            "coin": [
                "USDT",
                "BITCOIN"
            ],
            "blockchainContract": [
                       "TR20",
                       "CC60"
            ],
            "apiKey": "key",
            "note1": "lala",
            "note2": "yoyo"
        }
}
```

回傳欄位說明:回傳值皆不固定輸出

| 欄位                 | 型態   |
| -------------------- | ------ |
| `publicKey`          | string |
| `privateKey`         | string |
| `md5Key`             | string |
| `notifyUrl`          | string |
| `returnUrl`          | string |
| `transactionType`    | array  |
| `coin`               | array  |
| `blockchainContract` | array  |
| `apiKey`             | string |
| `note1`              | string |
| `note2`              | string |

沒有提示字/ 找不到該第三方檔案

```json
{
     "success": false,
        "code": 158,
        "locale": "en",
        "message": "第三方資料有誤",
        "data": null,
        "debug": []
}
```

## 充值（入款）



### 充值下單

```plaintext
POST /api/deposit/create
```

| 欄位         | 型態    | 必要參數 | 說明                               |
| ------------ | ------- | -------- | :--------------------------------- |
| order_id     | string  | V        | 訂單編號                           |
| pk           | integer | V        | 設定檔流水號（同步商戶資料的那份） |
| type         | integer | V        | 1 銀行卡 2 電子錢包 3 數字貨幣     |
| amount       | integer |          | 訂單金額                           |
| bank_name    | string  |          | 打款銀行名稱                       |
| account_name | string  |          | 打款帳戶名                         |
| txn_time     | time    |          | 打款成功時間 ex: 23-59-59          |
| screenshot   | image   |          | 支付成功截圖                       |
| tx_id        | string  |          | 區塊鍊交易ID                       |
| card_number  | string  |          | 卡號                               |
| mobile       | string  |          | 手機號                             |
| account_id   | string  |          | 電子錢包帳號                       |
| email        | string  |          | 電子信箱                           |
| country      | string  |          | 國家                               |
| state        | string  |          | 區                                 |
| city         | string  |          | 城市                               |
| address      | string  |          | 地址                               |
| zip          | string  |          | 郵遞區號                           |
| last_name    | string  |          | 姓氏                               |
| first_name   | string  |          | 名字                               |
| telegram     | string  |          | telegram                           |
| expired_date | string  |          | 到期日期 mm/yyyy                   |
| gateway_code | string  |          | 金流商（銀行） 通道代碼            |
| ifsc         | string  |          | ifsc                               |

Response example:

```json
{
    "success": true,
    "code": 0,
    "locale": "en",
    "message": "OK",
    "data": {
        "type": "form",
        "content": "<form action=\"https://www.inrusdt.com\" method=\"post\"><input name=\"merchantId\" value=\"\" hidden=\"true\"><input name=\"userId\" value=\"\" hidden=\"true\"><input name=\"payMethod\" value=\"\" hidden=\"true\"><input name=\"money\" value=\"\" hidden=\"true\"><input name=\"bizNum\" value=\"\" hidden=\"true\"><input name=\"notifyAddress\" value=\"\" hidden=\"true\"><input name=\"type\" value=\"\" hidden=\"true\"><input name=\"sign\" value=\"e49c058e12bbf978ed721c5bd37fe7c1\" hidden=\"true\"></form>"
    }
}
```

```json
{
    "success": true,
    "code": 0,
    "locale": "en",
    "message": "OK",
    "data": {
        "type": "url",
        "content": "http://google.com"
    }
}
```

| 欄位           | 說明     |
| -------------- | -------- |
| `data.type`    | 跳轉方式 |
| `data.content` | 跳轉內容 |


## 提現（出款）
### 提現下單

```plaintext
POST /api/withdraw/order
```

| 欄位             | 型態    | 必要參數 | 對應出入所需欄位Excel號碼 | 說明                                       |
| ---------------- | ------- | -------- | ------------------ | :----------------------------------------- |
| order_id         | string  | V        | java               | 訂單編號                                   |
| pk               | integer | V        | java               | 設定檔流水號（同步商戶資料的那份）         |
| type             | integer | V        | java               | 1 銀行卡 2 電子錢包 3 數字貨幣             |
| amount           | integer |          | 1                  | 訂單金額 (數字貨幣傳貨幣數量)              |
| fund_passwd      | string  |          | 3                  | 資金密碼                                   |
| email            | string  |          | 5                  | 電子信箱                                   |
| user_country     | string  |          | 6                  | 使用者國家                                 |
| user_state       | string  |          | 7                  | 使用者區                                   |
| user_city        | string  |          | 8                  | 使用者城市                                 |
| user_address     | string  |          | 9                  | 使用者地址                                 |
| bank_province    | string  |          |                    | 銀行省                                     |
| bank_city        | string  |          |                    | 銀行城市                                   |
| bank_address     | string  |          |                    | 銀行地址                                   |
| last_name        | string  |          | 10                 | 姓氏                                       |
| first_name       | string  |          | 11                 | 名字                                       |
| mobile           | string  |          | 12                 | 手機號                                     |
| telegram         | string  |          | 13                 | telegram                                   |
| withdraw_address | string  |          | 16                 | 銀行卡號  、電子錢包帳號 、   數字貨幣地址 |
| gateway_code     | string  |          |                    | 金流商（銀行） 通道代碼                    |
| ifsc             | string  |          |                    | ifsc                                       |


Response example:

```json
{
    "success": true,
    "code": 0,
    "locale": "en",
    "message": "OK",
    "data": {}
}
```
