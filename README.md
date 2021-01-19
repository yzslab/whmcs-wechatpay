# 微信支付WHMCS模块
基于微信支付APIv3开发

由于微信支付的退款API仅有v2，不想引入两个版本的代码，因此未实现退款接口

## 环境需求

PHP >= 7.0

## 使用方法
1. 给你的WHMCS启用SSL，微信支付要求回调URL是HTTPS协议

2. 登录微信支付商户平台：账户中心-API安全，分别设置API秘钥以及APIv3秘钥。注意本页面的API秘钥处点击“查看证书”可以获取证书序列号，设置模块时需要使用

3. 到本项目的[Release页面](https://github.com/yzslab/whmcs-wechatpay/releases)下载模块文件，把解包后的文件放入WHMCS根目录下的modules/gateways/目录中

4. 登入WHMCS管理员区域，启用付款模块“微信支付”，填写模块信息，其中商户私钥，把证书工具生产的私钥文件中的内容完全复制粘贴进去即可，即包括开头的-----BEGIN PRIVATE KEY-----以及结尾的-----END PRIVATE KEY-----

## DEBUG

* 二维码生成失败

    查阅System Activity Log

* 回调掉单

    查阅Gateway Transaction Log
    
* 其它

    如果WHMCS中未能记录错误信息，请查看HTTP服务器以及PHP的错误日志

## License

Apache License Version 2.0