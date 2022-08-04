<?php

namespace App\Payments;

use Pwf\PaySDK\Base\PwfClient;
use Pwf\PaySDK\Base\Config;

class PwfPay {
    public function __construct($config) {
        $this->config = $config;
    }
    
    public function form()
    {
        return [
            'pwfpay_app_url' => [
                'label' => '订单支付API',
                'description' => 'Token，填写您在 Pwf平台 中得到的订单支付API',
                'type' => 'input',
            ],
            'pwfpay_app_token' => [
                'label' => 'Token',
                'description' => 'Token，填写您在 Pwf平台 中得到的Token值',
                'type' => 'input',
            ],
            'pwfpay_merchant_no' => [
                'label' => 'Merchant No',
                'description' => '商户号，填写您在 Pwf平台 中得到的商户号',
                'type' => 'input',
            ],
            'pwfpay_currency' => [
                'label' => '货币代码',
                'description' => '填写您的货币代码（大写），与 Pwf平台 中的值相同',
                'type' => 'input',
            ],
            'pwfpay_private_key' => [
                'label' => '商户私钥',
                'description' => '填写您生成的商户私钥',
                'type' => 'input',
            ],
            'pwfpay_public_key' => [
                'label' => 'Pwf平台公钥',
                'description' => '填写Pwf的平台公钥',
                'type' => 'input',
            ]
        ];
    }
    
    private function _getOptions()
    {
        $options = new Config();
        
        $options->apiUrl = $this->config['pwfpay_app_url'];
        $options->appToken = $this->config['pwfpay_app_token'];

        $options->lang = "CN";
        
        $options->merchantPrivateCertPath = $this->config['pwfpay_private_key'];
        $options->pwfPublicCertPath = $this->config['pwfpay_public_key'];
 
        return $options;
    }
    
    public function pay($order)
    {
        $pwfClient = new PwfClient($this->_getOptions());

        try{
            $params = [
                "trade_name" => config('v2board.app_name', 'V2Board'),
                "fiat_currency" => $this->config['pwfpay_currency'],
                "fiat_account" => sprintf('%.2f', $order['total_amount'] / 100),
                "out_trade_no" => $order['trade_no'],
                "subject" => $order['trade_no'],
                "timestamp" => time(),
                "return_url" => $order['return_url'],
                "notify_url" => $order['notify_url'],
                "collection_model" => 1,
                "user_id" => $order['user_id'],
                "merchant_no" => $this->config['pwfpay_merchant_no']
            ];
            
            $ret = $pwfClient->execute("/api/v2/wallet/payAddress",$params);
            if($ret->isSuccess()){

                if($ret->verify()){

                    $data = $ret->dataMap();
                    return [
                        'type' => 1, // Redirect to url
                        'data' => $data['pay_url'],
                    ];
                }else{
                    throw new \Exception("驗簽失敗，請檢查Pwf平台公鑰或商戶私鑰是否配置正確。");
                }
                
            }else{
                throw new \Exception($result->ret() .":".$result->msg());
            }

        }catch (\Exception $e){
            abort(500, $e->getMessage());
        }
    }

    public function notify($params)
    {

        $pwfClient = new PwfClient($this->_getOptions());
        try{

            $ret = $pwfClient->getApiResponse($params);
            if($ret->isSuccess()){

                if($ret->verify()){

                    $data = $ret->dataMap();
                    if(!isset($data['status']) || $data['status'] !== 1){
                        return false;
                    }else{
                        return [
                            'trade_no' => $data['out_trade_no'],
                            'callback_no' => $data['order_num'],
                            'custom_result' => json_encode(['response'=>'ok'])
                        ];
                    }

                }else{
                    throw new \Exception("驗簽失敗，請檢查Pwf平台公鑰或商戶私鑰是否配置正確。");
                }
                
            }

            return false;
            
        }catch (\Exception $e){
            abort(500, $e->getMessage());
        }
    }
}
