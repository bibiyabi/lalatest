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
| msgName       | string |     | 信息名稱                |
| bankName      | string |     | 銀行名稱                |
| secondName    | string |     | 名稱                |
| firstName     | string |     | 姓氏                |
| cardNumber    | string |     | 卡號                |
| ifsc          | string |     | isfc                |
| cashflowMerchant  | integer |  V   | 金流商/交易所 id  |
| cashflowUserId    | string |     | 金流帳戶號         |
| cashflowMerchantId| string |     | 金流商戶號         |
| md5           | string |     | md5                |
| publickey     | string |     | 公鑰                |
| privatekey    | string |     | 私鑰                |
| syncAddress   | string |     | 同步地址                |
| asyncAddress  | string |     | 異步地址                |
| blockChain    | string |     | 區塊鍊網路                |
| rechargeAdd   | string |     | 充值地址                |
| apiKey        | string |     | API Key                |
| blockPrivateKey  | string |     | 密鑰                |
| remark1       | string |     | 備注欄位1                |
| remark2       | string |     | 備注欄位2                |


Response example:

```json
{"success":true,"code":0,"locale":"en","message":"传送成功","data":null}
```

```json
{"success":false,"code":1,"locale":"en","message":"传送失败","data":null,"debug":[]}
```

```json
{"success":false,"code":11,"locale":"en","message":"请输入完整信息","data":null,"debug":[]}
```
