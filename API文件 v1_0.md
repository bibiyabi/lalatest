# API文件 V9

## API必帶參數

| 欄位 | 型態   | 必要參數 | 說明      |
| ---- | ------ | -------- | --------- |
| sign | string | V        | signature |

## API Header必帶參數

| 欄位 | 型態   | 欄位值 | 說明      |
| ---- | ------ | ------ | :-------- |
| name | string | java   | API呼叫者 |

## 修改紀錄

### V2

* 提現下單、充值下單 ，type 改吃字串，gateway_coe 改名 transaction_type

### V3

* 提現新增 bank_province、bank_address、bank_city 三個參數。
* 提現 transaction_type、ifsc 補上對應欄位序號。

### V4

* 新增前台的出/入款應顯示欄位及下拉選單

### V5

* 更新API name withdraw/order => withdraw/create

### V6

2021/1/21
* 新增API 錯誤碼
* 新增代付重置訂單 API api/withdraw/reset

### V7

* 新增充值重置訂單 API api/deposit/reset

### V8

* 修改刪除資料設置id改為java id (user_pk)

### V9

* /api/withdraw/create 新增 22,23 代碼
* /api/deposit/create 新增 36 代碼
* /api/deposit/create 8、18 合併為 deposit_address

### V10

* /api/deposit/create 調整，欄位 8 獨立開一個欄位。
* /api/withdraw/create 23 代碼合併到16, 4
* /api/withdraw/create 14, 2, 15 代碼 , 補齊所有號碼變數

---

### API 錯誤碼

| code | 說明              |
| ---- | :---------------- |
| 000  | success           |
| 101  | 傳送失敗          |
| 101  | 傳送失敗          |
| 102  | 登入異常 驗簽失敗 |
| 107  | 查無資料          |
| 108  | 發生例外          |
| 111  | 請輸入完整訊息    |
| 120  | 資料庫錯誤        |
| 157  | 設定檔無此參數    |
| 158  | 第三方資料有誤    |
| 159  | 簽章有誤          |
| 302  | 已有該訂單        |
| 303  | 無此通道資訊      |

## 資料設置

### 新增/修改資料設置

```plaintext
POST /api/key
```

| 欄位       | 型態    | 必要參數 | 說明                                 |
| ---------- | ------- | -------- | :----------------------------------- |
| id         | integer | V        | 出/入款id /java unique  id           |
| gateway_id | integer | V        | 金流商/交易所 id                     |
| data       | string  | V        | 將以下欄位全部urlencode再json_encode |

| info_title          | string  |          | 信息名稱                        |
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

| 欄位 | 型態    | 必要參數 | 說明        |
| ---- | ------- | -------- | ----------- |
| id   | integer | V        | 設置資料 id |

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
| is_deposit | integer | V        | 入款=1 / 出款=0                                           |
| type       | string  | V        | 渠道名稱:bank_card, e_wallet, cryptocurrency, credit_card |

Response example:
備註: data裡的資料會被urlencode

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
| is_deposit   | integer | V        | 入款=1 / 出款=0                                           |
| type         | string  | V        | 渠道名稱:bank_card, e_wallet, cryptocurrency, credit_card |
| gateway_name | string  | V        | 金流商/交易所名稱                                         |

備註: data裡的資料會被urlencode

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
            "cryptoAddress": "add",
            "note1": "lala",
            "note2": "yoyo"
        }
}
```

回傳欄位說明:回傳值依據出入款及渠道不同而不固定輸出

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
| `cryptoAddress`      | string |
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

### 前台的出/入款應顯示欄位及下拉選單

```plaintext
GET /api/requirement
```

| 欄位         | 型態    | 必要參數 | 說明                                                      |
| ------------ | ------- | -------- | :-------------------------------------------------------- |
| is_deposit   | integer | V        | 入款=1 / 出款=0                                           |
| type         | string  | V        | 渠道名稱:bank_card, e_wallet, cryptocurrency, credit_card |
| gateway_name | string  | V        | 金流商/交易所名稱                                         |

備註: data裡的資料會被urlencode

```json
{
     "success": true,
        "code": 100,
        "locale": "en",
        "message": "传送成功",
        "data": {
            "value": "%7B%22column%22%3A%5B2%2C3%2C4%2C6%5D%7D"
        }
}
```

回傳欄位說明:column為顯示欄位代號(參考出入款所需欄位整理), select為下拉選單如銀行卡,不一定回傳

| 欄位     | 型態  |
| -------- | ----- |
| `column` | array |
| `select` | array |

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

| 欄位             | 型態    | 必要參數 | 對應出入所需欄位NO | 說明                                                      |
| ---------------- | ------- | -------- | ------------------ | --------------------------------------------------------- |
| order_id         | string  | V        | java               | 訂單編號                                                  |
| pk               | integer | V        | java               | 設定檔流水號（同步商戶資料的那份）                        |
| type             | string  | V        | java               | 渠道名稱:bank_card, e_wallet, cryptocurrency, credit_card |
| amount           | integer |          | 6 11 16            | 訂單金額                                                  |
| bank_name        | string  |          | 7 17               | 打款銀行名稱                                              |
| account_name     | string  |          | 8                  | 打款帳戶名                                                |
| txn_time         | time    |          | 9                  | 打款成功時間 ex: 23-59-59                                 |
| screenshot       | image   |          | 10                 | 支付成功截圖                                              |
| tx_id            | string  |          | 15                 | 區塊鍊交易ID                                              |
| deposit_address  | string  |          | 18 36              | 卡號、銀行帳號                                            |
| mobile           | string  |          | 19                 | 手機號                                                    |
| account_id       | string  |          | 20                 | 電子錢包帳號                                              |
| email            | string  |          | 21                 | 電子信箱                                                  |
| country          | string  |          | 22                 | 國家                                                      |
| state            | string  |          | 23                 | 區                                                        |
| city             | string  |          | 24                 | 城市                                                      |
| address          | string  |          | 25                 | 地址                                                      |
| zip              | string  |          | 26                 | 郵遞區號                                                  |
| last_name        | string  |          | 27                 | 姓氏                                                      |
| first_name       | string  |          | 28                 | 名字                                                      |
| telegram         | string  |          | 29                 | telegram                                                  |
| expired_date     | string  |          | 30                 | 到期日期 mm/yyyy                                          |
| transaction_type | string  |          | 31                 | 金流商（銀行） 通道代碼                                   |
| ifsc             | string  |          | 32                 | ifsc                                                      |
| bank_province    | string  |          | 33                 | 銀行所在省                                                |
| bank_address     | string  |          | 34                 | 銀行地址                                                  |
| bank_city        | string  |          | 35                 | 銀行所在城市                                              |

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

### 充值重置訂單

```plaintext
POST /api/deposit/reset
```

| 欄位     | 型態   | 必要參數 | 說明   |
| -------- | ------ | -------- | ------ |
| order_id | string | V        | 訂單編 |
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

## 提現（出款）

### 提現下單

```plaintext
POST /api/withdraw/create
```

| 欄位             | 型態    | 必要參數 | 對應出入所需欄位Excel號碼 | 說明                                                      |
| ---------------- | ------- | -------- | ------------------------- | :-------------------------------------------------------- |
| order_id         | string  | V        | java                      | 訂單編號                                                  |
| pk               | integer | V        | java                      | 設定檔流水號（同步商戶資料的那份）                        |
| type             | string  | V        | java                      | 渠道名稱:bank_card, e_wallet, cryptocurrency, credit_card |
| amount           | integer |          | 1、14                    | 訂單金額 (數字貨幣傳貨幣數量)                             |
| bank_card_option | integer |          | 2                         | 銀行卡                           |
| fund_passwd      | string  |          | 3                         | 資金密碼                                                  |
| email            | string  |          | 5                         | 電子信箱                                                  |
| user_country     | string  |          | 6                         | 使用者國家                                                |
| user_state       | string  |          | 7                         | 使用者區                                                  |
| user_city        | string  |          | 8                         | 使用者城市                                                |
| user_address     | string  |          | 9                         | 使用者地址                                                |
| bank_province    | string  |          | 19                        | 銀行省                                                    |
| bank_city        | string  |          | 21                        | 銀行城市                                                  |
| bank_address     | string  |          | 20                        | 銀行地址                                                  |
| last_name        | string  |          | 10                        | 姓氏                                                      |
| first_name       | string  |          | 11                        | 名字                                                      |
| mobile           | string  |          | 12                        | 手機號                                                    |
| telegram         | string  |          | 13                        | telegram                                                  |
| network          | string  |          | 15                        | 區塊鏈網路(目前僅顯示)                                                  |
| withdraw_address | string  |          | 16 、4 、23               | 收款地址 、電子錢包帳號 、 银行账号                       |
| transaction_type | string  |          | 17                        | 金流商（銀行） 通道代碼                                   |
| ifsc             | string  |          | 18                        | ifsc                                                      |
| zip              | string  |          | 22                        | 郵遞區號                                                  |

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

### 代付重置訂單

```plaintext
POST /api/withdraw/reset
```

| 欄位     | 型態   | 必要參數 | 對應出入所需欄位Excel號碼 | 說明   |
| -------- | ------ | -------- | ------------------------- | :----- |
| order_id | string | V        | java                      | 訂單編 |
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
