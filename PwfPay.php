<?php

namespace App\Payments;

use Pwf\PaySDK\Base\ApiClient;
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
        $options->merchantNo = $this->config['pwfpay_merchant_no'];
        $options->lang = "CN";
        
        $options->merchantPrivateCertPath = $this->config['pwfpay_private_key'];
        $options->pwfPublicCertPath = $this->config['pwfpay_public_key'];
        
        $options->notifyUrl = "";
        return $options;
    }
    
    public function pay($order)
    {
        ApiClient::setOptions($this->_getOptions());

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
                "user_id" => $order['user_id']
            ];
            
            $ret = ApiClient::wallet()->payAddress($params);
            
            return [
                'type' => 1, // Redirect to url
                'data' => $ret->pay_url,
            ];
        }catch (\Exception $e){
            abort(500, $e->getMessage());
        }
    }

    public function notify($params)
    {

        ApiClient::setOptions($this->_getOptions());
        try{

            $ret = ApiClient::notify()->pay($params);
            
            if(!isset($ret->status) || $ret->status !== 1){
                return false;
            }else{
                return [
                    'trade_no' => $params['out_trade_no'],
                    'callback_no' => $params['order_num'],
                    'custom_result' => json_encode(['response'=>'ok'])
                ];
            }
            
        }catch (\Exception $e){
            abort(500, $e->getMessage());
        }
    }
}
