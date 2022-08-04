為V2board增加 pwfpay支付通道的支持


## 第一步，安裝pwfpay擴展
在v2board根目錄下執行
```Shell
composer require pwf/paysdk
```

## 第二部，複製類文件

下載PwfPay.php文件並複製到v2borad項目中`app/Payments/`目錄下


## 第三部，刷新v2board後台管理頁面並使用