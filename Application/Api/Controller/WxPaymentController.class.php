<?php
/**
 *
 * * 
 * ============================================================================
 * $ 2015-08-10 $
 */
namespace Api\Controller;

use Think\Controller;
use Api\Logic\GoodsLogic;
use Think\Page;
class WxPaymentController extends BaseController
{
    public $config = array(
        'appid' => "wx8d8b9951c3e6f31d",    		/*微信开放平台上的应用id*/
        'mch_id' => "1396900502",   		/*微信申请成功之后邮件中的商户id*/
        'api_key' => "HLrPrMULJ8gn4n9m4ZedBSNOPqhFk2EO",    /*在微信商户平台上自己设定的api密钥 32位*/
    );
    //服务器异步通知页面路径(必填)
    public $notify_url = '';

    //商户订单号(必填，商户网站订单系统中唯一订单号)
    public $out_trade_no = '';

    //商品描述(必填，不填则为商品名称)
    public $body = '';

    //付款金额(必填)
    public $total_fee = 0;

    //自定义超时(选填，支持dhmc)
    public $time_expire = '';

    private $WxPayHelper;

    function __construct()
    {
        $this->WxPayHelper = new \Api\Lib\WxPayHelper();
    }
    /*
     * 预订单
     */
    public function testAction()
    {
        $order = M('order')->field('order_amount,pay_status')->where('order_sn=' . I('order_sn'))->find();
        if ($order['pay_status'] == 1) {
            $this->WxPayHelper->echoResult(0, '订单已支付');
        }
        //$total_fee = $order['order_amount']; //订单总金额
        $total_fee = 0.01; //订单总金额
        $this->total_fee = intval($total_fee * 100);//订单的金额 1元
        $this->out_trade_no = I('order_sn');//订单号
        $this->body = '描述信息';//支付描述信息
        $this->time_expire = date('YmdHis', time() + 86400);//订单支付的过期时间(eg:一天过期)
        $this->notify_url = 'http://cf.52cold.com/plugins/payment/wxapp/notify.php';//异步通知URL(更改支付状态)

        //数据以JSON的形式返回给APP
        $app_response = $this->doPay();
        if (isset($app_response['return_code']) && $app_response['return_code'] == 'FAIL') {
            $errorCode = 100;
            $errorMsg = $app_response['return_msg'];
            $this->WxPayHelper->echoResult($errorCode, $errorMsg);
        } else {
            $errorCode = 1;
            $errorMsg = 'success';
            $responseData = array(
                'notify_url' => $this->notify_url,
                'app_response' => $app_response,
            );
            $this->WxPayHelper->echoResult($errorCode, $errorMsg, $responseData);
        }
    }

    public function chkParam()
    {
        //用户网站订单号
        if (empty($this->out_trade_no)) {
            die('out_trade_no error');
        }
        //商品描述
        if (empty($this->body)) {
            die('body error');
        }
        if (empty($this->time_expire)){
            die('time_expire error');
        }
        //检测支付金额
        if (empty($this->total_fee) || !is_numeric($this->total_fee)) {
            die('total_fee error');
        }
        //异步通知URL
        if (empty($this->notify_url)) {
            die('notify_url error');
        }
        if (!preg_match("#^http:\/\/#i", $this->notify_url)) {
            $this->notify_url = "http://" . $_SERVER['HTTP_HOST'] . $this->notify_url;
        }
        return true;
    }

    /**
     * 生成支付(返回给APP)
     * @return boolean|mixed
     */
    public function doPay() {
        //检测构造参数
        $this->chkParam();
        return $this->createAppPara();
    }

    /**
     * APP统一下单
     */
    private  function createAppPara()
    {
        $url = "https://api.mch.weixin.qq.com/pay/unifiedorder";

        $data["appid"] 		      = $this->config['appid'];//微信开放平台审核通过的应用APPID
        $data["body"] 		      = $this->body;//商品或支付单简要描述
        $data["mch_id"] 	      = $this->config['mch_id'];//商户号
        $data["nonce_str"] 	      = $this->WxPayHelper->getRandChar(32);//随机字符串
        $data["notify_url"]       = $this->notify_url;//通知地址
        $data["out_trade_no"]     = $this->out_trade_no;//商户订单号
        $data["spbill_create_ip"] = $this->WxPayHelper->get_client_ip();//终端IP
        $data["total_fee"]        = $this->total_fee;//总金额
        $data["time_expire"]	  = $this->time_expire;//交易结束时间
        $data["trade_type"]   	  = "APP";//交易类型
        $data["sign"] 			  = $this->WxPayHelper->getSign($data, $this->config['api_key']);//签名

        $xml 		= $this->WxPayHelper->arrayToXml($data);
        $response 	= $this->WxPayHelper->postXmlCurl($xml, $url);

        //将微信返回的结果xml转成数组
        $responseArr = $this->WxPayHelper->xmlToArray($response);
        if(isset($responseArr["return_code"]) && $responseArr["return_code"]=='SUCCESS'){
            return 	$this->getOrder($responseArr['prepay_id']);
        }
        return $responseArr;
    }

    /**
     * 执行第二次签名，才能返回给客户端使用
     * @param int $prepayId:预支付交易会话标识
     * @return array
     */
    public function getOrder($prepayId)
    {
        $data["appid"] 		= $this->config['appid'];
        $data["noncestr"] 	= $this->WxPayHelper->getRandChar(32);
        $data["package"] 	= "Sign=WXPay";
        $data["partnerid"] 	= $this->config['mch_id'];
        $data["prepayid"] 	= $prepayId;
        $data["timestamp"] 	= time();
        $data["sign"] 		= $this->WxPayHelper->getSign($data, $this->config['api_key']);
//        $data["packagestr"] = "Sign=WXPay";
        return $data;
    }

    public function order_status(){
        update_pay_status(I('out_trade_no'));
//        add_wxpaylog($pay_arr);
    }
    /**
     * 回调notify到这里
     */
    public function notify(){

        $verify_result = $this->verifyNotify();
        if (isset($verify_result['result_code']) && $verify_result['result_code']=='SUCCESS') {
            $requestReturnData = file_get_contents("php://input");
            //商户订单号
            $out_trade_no = $verify_result['out_trade_no'];
            //交易号
            $trade_no     = $verify_result['transaction_id'];
            //交易状态
            $trade_status = $verify_result['result_code'];
            //支付金额
            $total_fee 	  = $verify_result['total_fee']/100;
            //支付过期时间
            $pay_date 	  = $verify_result['time_end'];
            //IP
            $pay_ip 	  = $verify_result['attach'];
            /*
                @todo
                1.更改订单状态为已支付。(需自己完善)
                2.添加付款信息到数据库,方便对账。(需自己完善)
            */
            $pay_arr = array(
                'pay_type' 			=> isset($_REQUEST['pay_type']) ? $_REQUEST['pay_type'] : '',
                'action' 			=> 'notify',
                'domain_type' 		=> isset($_REQUEST['domain_type']) ? $_REQUEST['domain_type'] : '',
                'out_trade_no' 		=> $out_trade_no,
                'trade_no' 			=> $trade_no,
                'trade_status' 		=> $trade_status,
                'trade_return_data' => $requestReturnData,
                'create_ip' 		=> $pay_ip,
            );
            update_pay_status($out_trade_no);
            add_wxpaylog($pay_arr);
            //处理后同步返回给微信
            exit('<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>');
        }
        exit('<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[ERROR]]></return_msg></xml>');
    }
    /**
     * 异步通知信息验证
     * @return boolean|mixed
     */
    public function verifyNotify()
    {
        $xml = isset($GLOBALS['HTTP_RAW_POST_DATA']) ? $GLOBALS['HTTP_RAW_POST_DATA'] : '';
        $this->setLog($xml);
        if(!$xml){
            return false;
        }
        $wx_back = $this->WxPayHelper->xmlToArray($xml);
        if(empty($wx_back)){
            return false;
        }
        $checkSign = $this->WxPayHelper->getVerifySign($wx_back, $this->config['api_key']);
        $this->setLog($checkSign);
        if($checkSign==$wx_back['sign']){
            return $wx_back;
        }	return false;
    }

    /**
     * 查询订单信息
     */
    public function orderQuery(){
        $order = M('order')->field('order_amount,pay_status')->where('order_sn=' . I('order_sn'))->find();
        if ($order['pay_status'] == 1) {
            $this->WxPayHelper->echoResult(0, '订单已支付');
        }
        $this->out_trade_no = I('order_sn');//订单号
        $responseArr = $this->wxOrderQuery();
        if(isset($responseArr["return_code"]) && $responseArr["result_code"]=='SUCCESS'){
            $this->WxPayHelper->echoResult(1, $responseArr["trade_state"], $responseArr);
        } else {
            $this->WxPayHelper->echoResult(100, $responseArr['return_msg'], $responseArr);
        }
    }

    public function wxOrderQuery(){
        $url = "https://api.mch.weixin.qq.com/pay/orderquery";

        $data["appid"] 		      = $this->config['appid'];//微信开放平台审核通过的应用APPID
        $data["mch_id"] 	      = $this->config['mch_id'];//商户号
        $data["nonce_str"] 	      = $this->WxPayHelper->getRandChar(32);//随机字符串
        $data["out_trade_no"]     = $this->out_trade_no;//商户订单号
        $data["sign"] 			  = $this->WxPayHelper->getSign($data, $this->config['api_key']);//签名

        $xml 		= $this->WxPayHelper->arrayToXml($data);
        $response 	= $this->WxPayHelper->postXmlCurl($xml, $url);


        //将微信返回的结果xml转成数组
        $responseArr = $this->WxPayHelper->xmlToArray($response);
        return $responseArr;


    }
    public function setLog( $logthis ){
        file_put_contents(__DIR__ . '/logfile.log', date("Y-m-d H:i:s"). " " . $logthis. "\r\n", FILE_APPEND | LOCK_EX);
    }

    function __destruct() {

    }


}
