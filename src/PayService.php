<?php


namespace Payment\PaymentSdk;

/**
 * 支付服务
 */
class PayService 
{

    /**
     * 创建支付订单
     * @param string $payment_method_id 支付方式id
     * @param string $ace_store_id 站点id
     * @param string $payment_channel_id 渠道id
     * @param string $return_url 返回url
     * @param string $cancel_url 取消url
     * @param string $methods_name 支付方式名称
     * @param string $paypal_type paypal支付方式类型1正常2快捷
     * @param string $product_list 产品列表
     * @param string $currency 币种
     * @author wind <254044378@qq.com>
     */
    public static function createOrder($params)
    {

        $json = PayClient::executeCall(
            "/api/paymentOrder",
            "POST",
            $params
        );
        return  PayResponse::fromModel($json);
    }

    /**
     * 查询第三方的账单
     *
     * @param string $ace_store_id 站点id
     * @param string $methods_name 支付方式名称
     * @param string $payment_id paypal支付的payment_id
     * @author wind <254044378@qq.com>
     */
    public static function queryOtherOrder($params)
    {
        $json = PayClient::executeCall(
            "/api/paymentOrder/query",
            "POST",
            $params
        );
        return  PayResponse::fromModel($json);
    }
    /**
     * 支付订单
     *
     * @param string $ace_store_id 站点id
     * @param string $methods_name 支付方式名称
     * @param string $payment_id paypal支付的payment_id
     * @param string $product_list paypal预支付的产品列表
     * @param string $payer_id paypal支付的payer_id
     * @author wind <254044378@qq.com>
     */
    public static function payOrder($params)
    {
        $json = PayClient::executeCall(
            "/api/paymentOrder/pay",
            "POST",
            $params
        );
        return  PayResponse::fromModel($json);
    }
}   