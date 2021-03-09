# Coding Style

## 命名

### 關鍵字命名

介面 e.g. Psr\Foo\BarInterface.

抽象 e.g. Psr\Foo\AbstractBar.

Traits e.g. Psr\Foo\BarTrait.

### 變數命名

前端輸入、前端輸出、資料庫，一律以 snake_case 命名
程式中一律以 camelCase 命名

## 大小寫

### 應採用小寫的類型

php 保留字
`true` `false` `and` `or` `if`

### 應採用大寫的類型

常數
`Status::SUCCESS` `LARAVEL_START`

### 應採用首字大寫

類別、介面名稱
`Class` `Interface`
PHP 檔名
`Index.php`

### 目錄結構

1. /Collections/ laravel collection 相關
1. /Constants/ 常數 用class宣告
1. /Constracts/ 介面, Interface 主要, abstract次要 , class不要在這裡
1. /Helpers/ 寫法參考 https://vocus.cc/@vic612/5fa9fa6cfd89780001283ee1
1. /Services/ 商業邏輯部分 ex: PaymentService
1. /Repository/ ex: OrderRepo 輔助 model，處理資料庫邏輯，然後注入到 service。
1. /Service/ ex: OrderService 輔助 controller，處理商業邏輯，然後注入到 controller

