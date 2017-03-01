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
class CartController extends BaseController {

    public $cartLogic; // 购物车逻辑操作类
    /**
     * 析构流函数
     */
    public function  __construct() {   
        parent::__construct();                
        $this->cartLogic = new \Home\Logic\CartLogic();                     
        
        $token = I("token"); // 唯一id  类似于 pc 端的session id
        //$user_id = I("user_id",0); // 用户id
        // 给用户计算会员价 登录前后不一样
        if($this->user_id){
            $user = M('users')->where("user_id = {$this->user_id}")->find();
            M('Cart')->execute("update `__PREFIX__cart` set member_goods_price = goods_price * {$user[discount]} where (user_id ={$user[user_id]} or session_id = '{$token}') and prom_type = 0");        
        }
            
        
    }

    /**
     * 将商品加入购物车
     */
    function addCart()
    {
        $shop = I('shop');
        $goods_id = I("goods_id"); // 商品id
        $goods_num = I("goods_num");// 商品数量
        $goods_spec = I("goods_spec"); // 商品规格
        // $goods_spec = explode("(", $goods_spec);
        // $goods_spec = explode(")", $goods_spec[1]);
        // $goods_spec = explode(",", $goods_spec[0]);
        // var_dump($goods_spec);
        // die();
        // // $goods_spec = explode("[&quot;", $goods_spec);
        // // $goods_spec = explode("&quot;]", $goods_spec[1]);
        // // $goods_spec = explode("&quot;", $goods_spec[0]);

        // // $rep = ",";
        // // foreach( $goods_spec as $k=>$v) {
        // //     if($rep == $v) unset($goods_spec[$k]);
        // // }
        // // foreach ($id as $k => $val) {
        // //     $data['selected'] = "1";
        // //     $carts = M("Cart")->where("id = $val")->save($data);
        // // }

        // $goods_spec = json_decode($goods_spec,true); //app 端 json 形式传输过来
        $token = I("token"); // 唯一id  类似于 pc 端的session id
        //$user_id = I("user_id",0); // 用户id        
        $result = $this->cartLogic->addCart($goods_id, $goods_num, $goods_spec,$token,$this->user_id); // 将商品加入购物车
        exit(json_encode($result)); 
    }
    
    /**
     * 删除购物车的商品
     */
    public function delCart()
    {       
        $user_id = I('user_id');
        if($user_id == '')
            exit(json_encode(array('status'=>0,'msg'=>'user_id不能为空','result'=>"user_id不能为空")));
        $token = I('token');
        if($token == '')
            exit(json_encode(array('status'=>0,'msg'=>'Token不能为空','result'=>"Token不能为空")));
        $user_id = I('user_id');
        $ids =I('ids');
        $id = explode(",", $ids);

        foreach ($id as $i => $v) {
            $result = M("Cart")->where("id=".$v)->delete(); // 删除id为5的用户数据
        }
        $result = M("Cart")->where("user_id=$user_id and session_id='$token'")->select();
        $return_arr = array('status'=>1,'msg'=>'删除成功','result'=>$result); // 返回结果状态
        exit(json_encode($return_arr));
        // $id = I("id"); // 商品 ids        
        // $result = M("Cart")->where("id=".$id)->delete(); // 删除id为5的用户数据
        
        // // 查找购物车数量
        // $token = I("token"); // 唯一id  类似于 pc 端的session id
        // $cart_count =  cart_goods_num(0,$token);
        // $return_arr = array('status'=>1,'msg'=>'删除成功','result'=>$cart_count); // 返回结果状态
        // exit(json_encode($return_arr));
    }
    
    
    /*
     * 请求获取购物车列表
     */
    public function cartList()
    {                    
        $cart_form_data = $_POST["cart_form_data"]; // goods_num 购物车商品数量
        $cart_form_data = json_decode($cart_form_data,true); //app 端 json 形式传输过来
        // print_r($cart_form_data);die();
        
        $token = I("token"); // 唯一id  类似于 pc 端的session id
//        $user_id = I("user_id",0); // 用户id                
        $where = " session_id = '$token' "; // 默认按照 $token 查询
        $this->user_id && $where = " user_id = ".$this->user_id; // 如果这个用户已经等了则按照用户id查询
        $cartList = M('Cart')->where($where)->getField("id,goods_num,selected"); 
        if(I("post.goodsNum") && I("post.cartid"))
        {
            // 修改购物车数量 和勾选状态
            // foreach($cart_form_data as $key => $val)
            // {   
                // print_r($cart_form_data);die();
                $data['goods_num'] = I("goodsNum");
                // $data['selected'] = $val['selected'];
                $cartID = I("cartid");
                if(($cartList[$cartID]['goods_num'] != $data['goods_num'])) 
                    M('Cart')->where("id = $cartID")->save($data);
            // }
            //$this->assign('select_all', $_POST['select_all']); // 全选框
        }                  
        $shop = I("shop");  
        $shop = explode("(", $shop);
        $shop = explode(")", $shop[1]);
        $shop = explode(",", $shop[0]);  
        $shop = array_unique($shop); 
        $shop = array_values($shop);              
        $result = $this->cartLogic->cartList($this->user,$shop, $token,1,0); // 选中的商品        
       // if(empty($result['total_price']))
       //     $result['total_price'] = Array( 'total_fee' =>0, 'cut_fee' =>0, 'num' => 0, 'atotal_fee' =>0, 'acut_fee' =>0, 'anum' => 0);        
      //  $result['result']['total_price'] = $result['total_price'];        
        
        exit(json_encode($result));
    }
    /**
     * 购物车第二步确定页面
     */
    public function cart2()
    {
        $token = I("token"); // 唯一id  类似于 pc 端的session id
        //$user_id = I("user_id"); // 用户id
        $usersInfo = get_user_info($this->user_id);  // 用户
        $userinfo['user_id'] = $usersInfo['user_id'];
        $userinfo['user_money'] = $usersInfo['user_money'];
        if($this->user_id == 0 ) exit(json_encode (array('status'=>-1,'msg'=>'用户user_id不能为空','result'=>'')));   
        if (empty(I("goods_id"))) {
        // print_r($token);die(); 
                 // if($this->cartLogic->cart_count($this->user_id,1) == 0 ) exit(json_encode (array('status'=>-2,'msg'=>'你的购物车没有选中商品','result'=>'')));
            $id  = I("ids");
            $id = explode("(", $id);
            $id = explode(")", $id[1]);
            $id = explode(",", $id[0]);
            // print_r($id);die();
            foreach ($id as $k => $val) {
                $order_good = M('cart')->where("user_id = {$this->user_id} and id = $val")->find();
                $order_goods[] = $order_good;
            }
        }   
        
        
        // 购物车商品  
        $shop = I("shop");
        $shop = explode("(", $shop);
        $shop = explode(")", $shop[1]);
        $shop = explode(",", $shop[0]);  
        $shop = array_unique($shop); 
        $shop = array_values($shop);  
        // print_r($shop);die();     
        // foreach ($shop as $key => $value) {
            // print_r($value);
           $cart_result = $this->cartLogic->cartList($this->user,$shop, $token,1,1); // 获取购物车商品          
        // }       
        // print_r($cart_result);
        // die();
        // 没有选中的不传递过去
            
        // print_r($cart_result);
        // $cartList[] = $cartList;    
        // die();
        if (I("goods_id")) {
            $cart_result['cartList'] = $cart_result['cartList'];  
            foreach ($cart_result['cartList'] as $cc => $ccv) {
                $cart_result['cartList'][$cc]['spec_key'] = I("spec_key");
                $cart_result['cartList'][$cc]['goods_num'] = I("goods_num");
                $spec_key = I("spec_key");
                $spec_goods_price = M("spec_goods_price")->where( "`key` = '$spec_key'")->find();
                if(!empty($spec_goods_price)){
                    $cart_result['cartList'][$cc]['shop_price'] = $spec_goods_price['price'];
                }else{
                    $cart_result['cartList'][$cc]['shop_price'] = $ccv['shop_price'];
                }
                $cart_result['cartList'][$cc]['spec_key_name'] = $spec_goods_price['key_name'];
                if ($cart_result['cartList'][$cc]['spec_key_name'] == null) {
                    $cart_result['cartList'][$cc]['spec_key_name'] = "";
                }
                if(!empty($spec_goods_price)){
                    $pay_money += $spec_goods_price['price'] * ($ccv['rule'] / 100) * I("goods_num");
                    $cut_fee += I("goods_num") * $spec_goods_price['price'] * ($ccv['rule'] / 100); 
                }else{
                    $pay_money += $ccv['shop_price'] * ($ccv['rule'] / 100) * I("goods_num");
                    $cut_fee += I("goods_num") * $ccv['shop_price'] * ($ccv['rule'] / 100);
                }
                // print_r($cart_result['cartList'][$cc]['shop_price']);
            }
        }else{
            $cartList = array();
            foreach($cart_result['cartList'] as $key => $val)
            {
                if($val['selected'] == 1) 
                   $cartList[] = $val;   
                if($val['shop'] == 1){
                    $goods = M("goods")->where("goods_id=".$val['goods_id'])->find();
                    $val['rule'] = $goods['rule'];
                    $pay_money += $val['goods_price'] * ($val['rule'] / 100) * $val['goods_num'];
                    // print_r($pay_money);
                    $cartLists['hg'][] = $val;
                }elseif ($val['shop'] == 2) {
                    $cartLists['uc'][] = $val;
                }
            }    
            $cart_result['cartList'] = $cartLists;  
        }
        // print_r($cart_result);
           // die();
        // 物流公司
        $shippingList = M('Plugin')->where("`type` = 'shipping' and status = 1")->select();// 物流公司                
        // 优惠券
        $Model = new \Think\Model(); // 找出这个用户的优惠券 没过期的  并且 订单金额达到 condition 优惠券指定标准的     
        $sql = "select c1.name,c1.money,c1.condition, c2.* from __PREFIX__coupon as c1 inner join __PREFIX__coupon_list as c2  on c2.cid = c1.id and c1.type in(0,1,2,3) and order_id = 0 where c2.uid = {$this->user_id} and ".time()." < c1.use_end_time and c1.condition <= {$cart_result['total_price']['total_fee']}";		
        $couponList = $Model->query($sql);                       
        // 收货地址
        $addresslist = M('UserAddress')->where("user_id = {$this->user_id}")->select();
        $c = M('UserAddress')->where("user_id = {$this->user_id} and is_default = 1")->count(); // 看看有没默认收货地址        
        // print_r($c);die(); 
        if((count($addresslist) < 0) && ($c == 0)){ // 如果没有设置默认收货地址, 则第一条设置为默认收货地址
            $addresslist[0]['is_default'] = 1; 
        }else{
            foreach ($addresslist as $al => $als) {
                if($als['is_default'] == 1){
                    $addresslist[0] = $addresslist[$al];
                }
            }
        }
        // 可用U币舍去小数点都变的
        $pay_money = explode(".", $pay_money);
        $cut_fee = explode(".", $cart_result['total_price']['cut_fee']);
        $pay_money = $pay_money[0];
        if ($pay_money == null) {
            $pay_money = "";
        }
        $cart_result['total_price']['pay_money'] = $pay_money;
        $cart_result['total_price']['cut_fee'] = $cut_fee[0];
        // print_r($pay_money);
           // die();
        $json_arr = array(
            'status'=>1,
            'msg'=>'获取成功',
            'result'=>array(
                           'addressList' =>$addresslist[0], // 收货地址
                           'shippingList'=>$shippingList, //物流列表 @h
                           'cartList'    =>$cart_result['cartList'], // 购物车列表
                           'totalPrice'  =>$cart_result['total_price'], // 总计                           
//                           'couponList'  =>$couponList, //优惠券列表@h
                           'userInfo'    =>$userinfo, // 用户详情  
                           // 'pay_money'   =>$pay_money,
                        ));                
        $replace = array('src=&quot;\/'.SITE_URL.'\/Public','"'.SITE_URL.'\/Public');
        $search = array('src=&quot;\/Public','"\/Public');
        $json_str = json_encode($json_arr,TRUE); 
        $json_str = str_replace($search,$replace,$json_str);         
        exit($json_str);
    }
       
    /**
     * 获取订单商品价格 或者提交 订单
     */
    public function cart3(){
                        
        $token = I("token"); // 唯一id  类似于 pc 端的session id
        //$user_id = I("user_id"); // 用户id        
        $usersInfo = get_user_info($this->user_id);  // 用户 
        // $shop = I("shop"); // 1:游换购 2：UU册                
        $address_id = I("address_id"); //  收货地址id
        $shipping_code =  "shunfeng"; //  物流编号        
        $invoice_title = I('invoice_title'); // 发票
        $couponTypeSelect =  I("couponTypeSelect"); //  优惠券类型  1 下拉框选择优惠券 2 输入框输入优惠券代码
        $coupon_id =  I("coupon_id",0); //  优惠券id
        $couponCode =  I("couponCode"); //  优惠券代码
        $pay_points =  I("pay_points",0); //  使用积分
        $user_money =  I("user_money",0); //  使用余额        
        $user_money = $user_money ? $user_money : 0;                                              
        
        $goods_id = I("goods_id");
        // if($goods_id == ""){
        //     if($this->cartLogic->cart_count($this->user_id,1) == 0 ) exit(json_encode(array('status'=>-1,'msg'=>'你的购物车没有选中商品','result'=>null))); // 返回结果状态
        // }
        
        if(!$address_id) exit(json_encode(array('status'=>-1,'msg'=>'请完善收货人信息','result'=>null))); // 返回结果状态
        //if(!$shipping_code) exit(json_encode(array('status'=>-1,'msg'=>'请选择物流信息','result'=>null))); // 返回结果状态
        
 	$address = M('UserAddress')->where("address_id = $address_id")->find();
    if ($goods_id == "") {
        $id  = I("ids");
        $id = explode("(", $id);
        $id = explode(")", $id[1]);
        $id = explode(",", $id[0]);
        // print_r($id);die();
        foreach ($id as $k => $val) {
            $order_good = M('cart')->where("user_id = {$this->user_id} and id = $val")->find();
            $order_goods[] = $order_good;
        }

        $result = calculate_price($this->user_id,$order_goods,$shipping_code,0,$address[province],$address[city],$address[district],$pay_points,$user_money,$coupon_id,$couponCode,$shop);
    }else{
        $shop  = I("shop");
        $shop = explode("(", $shop);
        $shop = explode(")", $shop[1]);
        $shop = explode(",", $shop[0]);
        $shop = $shop[0];
        if($shop == 1){
            $order_goods = M('goods')->where("goods_id=$goods_id")->select();
        }elseif($shop == 2){
            $order_goods = M('books')->where("books_id=$goods_id")->select();
        }
        
        $result = calculate_price($this->user_id,$order_goods,0,$address[province],$address[city],$address[district],$pay_points,$user_money,$coupon_id,$shop);
    }

	// $result = calculate_price($this->user_id,$order_goods,$shipping_code,0,$address[province],$address[city],$address[district],$pay_points,$user_money,$coupon_id,$couponCode,$shop);
          // print_r($result);die();      
	if($result['status'] < 0)	
		exit(json_encode($result));      	
	// 订单满额优惠活动		                
        // $order_prom = get_order_promotion($result['result']['order_amount']);
        // $result['result']['order_amount'] = $order_prom['order_amount'] ;
        $result['result']['order_prom_id'] = $order_prom['order_prom_id'] ;
        // $result['result']['order_prom_amount'] = $order_prom['order_prom_amount'] ;
			
        $car_price = array(
            'postFee'      => $result['result']['shipping_price'], // 物流费
            'shipping'      => $result['result']['shipping'], // 物流费
            'couponFee'    => $result['result']['coupon_price'], // 优惠券            
            'balance'      => $result['result']['user_money'], // 使用用户余额
            'pointsFee'    => $result['result']['integral_money'], // 积分支付            
            'payables'     => $result['result']['order_amount'], // 应付金额
            'goodsFee'     => $result['result']['goods_price'],// 商品价格            
            'order_prom_id' => $result['result']['order_prom_id'], // 订单优惠活动id
            'order_prom_amount' => $result['result']['order_prom_amount'], // 订单优惠活动优惠了多少钱
        );
    $goods_list = $result['result']['order_goods'];
    // print_r($car_price);
    // die();
        // 提交订单        
        if($_REQUEST['act'] == "submit_order")
        {            
            if(empty($coupon_id) && !empty($couponCode))
               $coupon_id = M('CouponList')->where("`code`='$couponCode'")->getField('id'); 
            $shop = I("shop");
            $shop = explode("(", $shop);
            $shop = explode(")", $shop[1]);
            $shop = explode(",", $shop[0]);  
            $shop = array_unique($shop); 
            $shop = array_values($shop);   
            // print_r($shop);die();        
            $result = $this->cartLogic->addOrder($this->user_id,$address_id,$shipping_code,$invoice_title,$coupon_id,$car_price,$shop,$goods_list); // 添加订单                        
            exit(json_encode($result));            
        }
            $return_arr = array('status'=>1,'msg'=>'计算成功','result'=>$car_price); // 返回结果状态
            exit(json_encode($return_arr));   
    }


    //立即购买
    public function right_buy(){

    }
 
}
