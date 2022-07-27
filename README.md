为V2board增加 pwfpay支付通道的支持


## 第一步，安装pwfpay扩展
在v2board根目录下执行
```Shell
composer require pwf/paysdk
```

## 第二部，复制类文件

下载PwfPay.php文件并复制到v2borad项目中`app/Payments/`目录下


## 第三部，刷新v2board后台管理页面并使用