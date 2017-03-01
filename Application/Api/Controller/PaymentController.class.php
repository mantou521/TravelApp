<?php

namespace Api\Controller;

use Think\Controller;
use Api\Logic\GoodsLogic;
use Think\Page;

require_once("plugins/payment/alipay/app_notify/lib/alipay_core.function.php");

class PaymentController extends BaseController
{
    public $config_value = array();// 支付宝支付配置参数

    /**
     * 析构流函数
     */
    public function __construct()
    {
        parent::__construct();
        $paymentPlugin = M('Plugin')->where("code='alipay' and  type = 'payment' ")->find(); // 找到支付插件的配置
        $this->config_value = unserialize($paymentPlugin['config_value']); // 配置反序列化
    }


    /**
     * 订单 rsa签名
     */


    public function orderSign()
    {
        require_once("plugins/payment/alipay/app_notify/lib/alipay_rsa.function.php");
        require_once("plugins/payment/alipay/app_notify/alipay.config.php");
        $order = M('order')->field('order_amount')->where('order_sn=' . I('order_sn'))->find();
        if ($order['pay_status'] == 1) {
            $json_arr = array('status' => 0, 'msg' => '订单已支付!', 'orderInfor' => $order);
            echo json_encode($json_arr);
        }

        $biz_content = array(
            "timeout_express" => "30m",
            "product_code" => "QUICK_MSECURITY_PAY",
            "total_amount" => "0.01",
            "subject" => "uu财富",
            "body" => "描述",
            "out_trade_no" => I('order_sn'),
        );
        $biz_content = json_encode($biz_content);
        $common = array(
            "app_id" => "2016072900120170",
            "method" => "alipay.trade.app.pay",
            "charset" => "utf-8",
            "sign_type" => "RSA",
            "timestamp" => date('Y-m-d H:i:s', time()),
            "version" => "1.0",
            "notify_url" => "http://cf.52cold.com/index.php?m=Api&c=Payment&a=alipayNotify",
            "biz_content" => $biz_content
        );
        $mygoods = argSort($common);

        //拼接
        $mystr = createLinkstring($mygoods);
        //签名
        $sign = rsaSign($mystr, $alipay_config['private_key_path']);
        //对签名进行urlencode转码
        $sign = urlencode($sign);
        //生成最终签名信息
        $orderInfor = $mystr . "&sign=" . $sign;
        $json_arr = array('status' => 1, 'msg' => '成功!', 'common' => $common, 'sign' => $sign, 'orderInfor' => $orderInfor);
        echo json_encode($json_arr);
        /*******特殊的 验签支付宝反馈给App的签名信息*******/
        //支付宝反馈给App端信息拆解如下
//        $str = 'body=%E7%B2%BE%E5%93%81%E5%84%BF%E7%AB%A5%E4%B9%A6%E5%8C%85%E5%96%9C%E6%B4%8B%E6%B4%8B%E7%9A%84&notify_url=http%3A%2F%2F211.149.220.47%2Fphp%2Fnotify_url.php&out_trade_no=40609294027478&partner=2088011744308664&seller=2088011744308664&subject=%E4%B9%A6%E5%8C%85&success=true&total_fee=0.01';
//
//        //被拆解后的支付宝签名
//        $sign = 'Itorzqous2F7kYWWOpmoB%2FJUYgySRzh%2FOOKMhVhv%2BM48CnFk%2BQCp2cKcSsNGcDTs2AsAk%2BRYTuyMYZkGH56t8jcV2GGFkrJr%2FPxcGRlEK08QadAhImYzy9piVjoW0102lhSJYapiXGBTl5eiZ88RiyRA62D2nJEtH%2FBVXpuq63A%3D';

        //得到签名
//        $sign = urldecode($sign);
//        //得到待签名字符串
//        $str = urldecode($mystr);
//        //验签数据,验签成功将返回true 否则 flase
//        var_dump(rsaVerify($str,$alipay_config['ali_public_key_path'], $sign));
    }

    /**
     * app端发起支付宝,支付宝返回服务器端,  返回到这里
     * http://cf.52cold.com/index.php/Api/Payment/alipayNotify
     */
    public function alipayNotify()
    {
        require_once("plugins/payment/alipay/app_notify/lib/alipay_notify.class.php");
        $alipay_config['partner'] = $this->config_value['alipay_partner'];//合作身份者id，以2088开头的16位纯数字
        //计算得出通知验证结果
        $alipayNotify = new \AlipayNotify($alipay_config);
        $verify_result = $alipayNotify->verifyNotify();
        //验证成功
        if ($verify_result) {
            $order_sn = $out_trade_no = trim($_POST['out_trade_no']); //商户订单号
            $trade_no = $_POST['trade_no'];//支付宝交易号
            $trade_status = $_POST['trade_status'];//交易状态
            if ($_POST['trade_status'] == 'TRADE_FINISHED') {
                update_pay_status($order_sn); // 修改订单支付状态                
            } else if ($_POST['trade_status'] == 'TRADE_SUCCESS') {
                update_pay_status($order_sn); // 修改订单支付状态                
            }
            M('order')->where("order_sn = '$order_sn'")->save(array('pay_code' => 'alipay', 'pay_name' => 'app支付宝'));
            M("lv_order")->where("order_sn = '$order_sn'")->save(array('status' => "1" , "pay_status" => "1"));

            echo "success"; //  告诉支付宝支付成功 请不要修改或删除               
        } else {
            echo "fail"; //验证失败         
        }
    }

    public function testAction()
    {
        $order = M('order')->field('order_amount')->where('order_sn=' . I('order_sn'))->find();
        $url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
        $post_data['appid'] = 'wx8d8b9951c3e6f31d';
        $post_data['mch_id'] = '1396900502';
        $post_data['nonce_str'] = 'K8264ILTKCH16CQ2502SI8ZNMTM67VS';
        $post_data['body'] = 'QQ会员充值';
        $post_data['out_trade_no'] = I('order_sn');
        $post_data['total_fee'] = 1;
        $post_data['spbill_create_ip'] = '123.12.12.123';
        $post_data['notify_url'] = 'http://cf.52cold.com/index.php?m=Api&c=Payment&a=wxNotify';
        $post_data['trade_type'] = 'APP';
        $mygoods = argSort($post_data);
        $mystr = createLinkstring($mygoods);
        $stringSignTemp = $mystr . "&key=HLrPrMULJ8gn4n9m4ZedBSNOPqhFk2EO";
        $post_data['sign'] = strtoupper(md5($stringSignTemp));
        $post_data = $this->arrayToXml($post_data);
        $User = new \Api\Logic\RequestPost();
        $res = $User->request_post($url, $post_data);
        $orderInfor = $this->xmlToArray($res);
        $json_arr = array('status' => 1, 'msg' => '成功!', 'orderInfor' => $orderInfor);
        echo json_encode($json_arr);
    }

    public function arrayToXml($arr)
    {
        $xml = "<root>";
        foreach ($arr as $key => $val) {
            if (is_array($val)) {
                $xml .= "<" . $key . ">" . arrayToXml($val) . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            }
        }
        $xml .= "</root>";
        return $xml;
    }

    public function xmlToArray($xml)
    {

        //禁止引用外部xml实体

        libxml_disable_entity_loader(true);

        $xmlstring = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);

        $val = json_decode(json_encode($xmlstring), true);

        return $val;

    }

    public function wxNotify()
    {
        $re = '<xml>
  <appid><![CDATA[wx2421b1c4370ec43b]]></appid>
  <attach><![CDATA[支付测试]]></attach>
  <bank_type><![CDATA[CFT]]></bank_type>
  <fee_type><![CDATA[CNY]]></fee_type>
  <is_subscribe><![CDATA[Y]]></is_subscribe>
  <mch_id><![CDATA[10000100]]></mch_id>
  <nonce_str><![CDATA[5d2b6c2a8db53831f7eda20af46e531c]]></nonce_str>
  <openid><![CDATA[oUpF8uMEb4qRXf22hE3X68TekukE]]></openid>
  <out_trade_no><![CDATA[1409811653]]></out_trade_no>
  <result_code><![CDATA[SUCCESS]]></result_code>
  <return_code><![CDATA[SUCCESS]]></return_code>
  <sign><![CDATA[B552ED6B279343CB493C5DD0D78AB241]]></sign>
  <sub_mch_id><![CDATA[10000100]]></sub_mch_id>
  <time_end><![CDATA[20140903131540]]></time_end>
  <total_fee>1</total_fee>
  <trade_type><![CDATA[JSAPI]]></trade_type>
  <transaction_id><![CDATA[1004400740201409030005092168]]></transaction_id>
</xml> ';
        $arr = $this->xmlToArray($re);
        echo "<pre>";
        print_r($arr);
        echo "</pre>";

    }

    /**
     * 异步通知信息验证
     * @return boolean|mixed
     */
    public function verifyNotify()
    {
        $xml = isset($GLOBALS['HTTP_RAW_POST_DATA']) ? $GLOBALS['HTTP_RAW_POST_DATA'] : '';
        if (!$xml) {
            return false;
        }
        $wx_back = $this->xmlToArray($xml);
        if (empty($wx_back)) {
            return false;
        }
        $checkSign = $this->WxPayHelper->getVerifySign($wx_back, $this->config['api_key']);
        if ($checkSign == $wx_back['sign']) {
            return $wx_back;
        }
        return false;
    }

    function getVerifySign($data, $key)
    {
        $String = $this->formatParameters($data, false);
        //签名步骤二：在string后加入KEY
        $String = $String . "&key=" . $key;
        //签名步骤三：MD5加密
        $String = md5($String);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($String);
        return $result;
    }


}
