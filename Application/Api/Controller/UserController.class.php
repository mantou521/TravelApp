<?php
/**
 *
 * * 
 * ============================================================================
 * $ 2015-08-10 $
 */ 
namespace Api\Controller;
use Think\Controller;
class UserController extends BaseController {
    public $userLogic;
    
    /**
     * 析构流函数
     */
    public function  __construct() {   
        parent::__construct();    
    
    } 
    
    public function _initialize(){
        parent::_initialize();
        $this->userLogic = new \Home\Logic\UsersLogic();
        // $restLogic = new \Home\Logic\RestLogic();
    }
    
   
    /**
     *  登录
     */
    public function login(){
        $username = I('username','');
        $password = I('password','');
        $openid = I('openid','');
        // $unique_id = I("unique_id"); // 唯一id  类似于 pc 端的session id
        $data = $this->userLogic->app_login($username,$password,$openid);
        $user = M("Users")->where("mobile='$username'")->find();
        // print_r($user);die();
        $session_id = $user['session_id'];
        $cartLogic = new \Home\Logic\CartLogic();        
        $cartLogic->login_cart_handle($session_id,$data['result']['user_id']); // 用户登录后 需要对购物车 一些操作               
        exit(json_encode($data));
    }
    /*
     * 第三方登录
     */
    public function thirdLogin(){
        $map['openid'] = I('openid','');
        $map['oauth'] = I('from','');
        $map['nickname'] = I('nickname','');
        $map['head_pic'] = I('head_pic','');        
        $data = $this->userLogic->thirdLogin($map);
        exit(json_encode($data));
    }

    /**
     * 用户注册
     */
    public function reg(){
        $username = I('post.username','');
        $password = I('post.password','');
        $password2 = I('post.password2','');
        // $unique_id = I('unique_id');
        //是否开启注册验证码机制
        // print_r($_SESSION);die();
        if(check_mobile($username) && TpCache('sms.regis_sms_enable')){
            $code = I('post.code');
            if(empty($code))
                exit(json_encode(array('status'=>0,'msg'=>'请输入验证码','result'=>'')));
        }        
        $data = $this->userLogic->reg($username,$password,$code,$password2);
        exit(json_encode($data));
    }

    /*
     * 获取用户信息
     */
    public function userInfo(){
        //$user_id = I('user_id');
        $data = $this->userLogic->get_info($this->user_id);
        exit(json_encode($data));
    }

    /*
     *更新用户头像
     */
    public function updateUserInfo(){ 
        if(IS_POST){
            //$user_id = I('user_id');
            if(!$this->user_id)
                exit(json_encode(array('status'=>-1,'msg'=>'缺少参数','result'=>'')));
            $user_id = I("user_id");
            $base64 = $_POST['head_pic'];
            $base64_image = str_replace(' ', '+', $base64);
            //post的数据里面，加号会被替换为空格，需要重新替换回来，如果不是post的数据，则注释掉这一行
            $image_name = time().rand(10000,99999).'.png';
            $image_file = 'Public/upload/head_pic/'.$image_name;
            //服务器文件存储路径
            if (file_put_contents($image_file, base64_decode($base64_image))){
                $data['head_pic'] = SITE_URL.'/'.$image_file;
                $res = M("Users")->where("user_id=".$user_id)->save($data);
                $user = M("Users")->where("user_id=".$this->user_id)->find();
                exit(json_encode(array('status'=>1,'msg'=>'更新成功','result'=>$user)));

            }else{
                exit(json_encode(array('status'=>0,'msg'=>'更新失败','result'=>'')));
            }
        }
    }

    /*
     * 修改用户密码
     */
    public function password(){
        if(IS_POST){
            //$user_id = I('user_id');
            if(!$this->user_id)
                exit(json_encode(array('status'=>-1,'msg'=>'缺少参数','result'=>'')));
            $data = $this->userLogic->password($this->user_id,I('post.old_password'),I('post.new_password'),I('post.confirm_password')); // 获取用户信息
            exit(json_encode($data));
        }
    }

    // 设置登录密码
    public function setpassword(){
        $user_id = I("user_id");
        $token = I("token");
        $password = encrypt(I("password"));
        $user = M("Users")->where("user_id=".$user_id." AND token="."'".$token."'")->find();
        if(empty($user)){
            exit(json_encode(array('status'=>0,'msg'=>'信息有误请重新填写','result'=>'')));
        }else{
            $data['password'] = $password;
            $pas = M("Users")->where("user_id=".$user_id)->save($data);
            $users = M("Users")->where("user_id=".$user_id." AND token="."'".$token."'")->find();
            exit(json_encode(array('status'=>1,'msg'=>'设置成功','result'=>$users)));
        }
    }

    /**
     * 获取收货地址
     */
    public function getAddressList(){
       //$user_id = I('user_id');
        if(!$this->user_id)
            exit(json_encode(array('status'=>-1,'msg'=>'缺少参数','result'=>'')));
            $address = M('user_address')->where(array('user_id'=>$this->user_id))->select();
        if(!$address)
            exit(json_encode(array('status'=>1,'msg'=>'没有数据','result'=>'')));
        exit(json_encode(array('status'=>1,'msg'=>'获取成功','result'=>$address)));
    }

    /*
     * 添加地址
     */
    public function addAddress(){
        //$user_id = I('user_id',0);
        if(!$this->user_id) exit(json_encode(array('status'=>-1,'msg'=>'缺少参数','result'=>'')));
        $address_id = I('address_id',0);
        $data = $this->userLogic->add_address($this->user_id,$address_id,I('post.')); // 获取用户信息
        exit(json_encode($data));
    }
    /*
     * 地址删除
     */
    public function del_address(){
        $id = I('id');
        if(!$this->user_id) exit(json_encode(array('status'=>-1,'msg'=>'缺少参数','result'=>'')));
        $address = M('user_address')->where("address_id = $id")->find();
        $row = M('user_address')->where(array('user_id'=>$this->user_id,'address_id'=>$id))->delete();                
        // 如果删除的是默认收货地址 则要把第一个地址设置为默认收货地址
        if($address['is_default'] == 1)
        {
            $address = M('user_address')->where("user_id = {$this->user_id}")->find();            
            M('user_address')->where("address_id = {$address['address_id']}")->save(array('is_default'=>1));
        }        
        if(!$row)
           exit(json_encode(array('status'=>1,'msg'=>'删除成功','result'=>''))); 
        else
           exit(json_encode(array('status'=>1,'msg'=>'删除失败','result'=>''))); 
    } 
    /*
     * 设置默认收货地址
     */
    public function setDefaultAddress(){
//        $user_id = I('user_id',0);
        if(!$this->user_id) exit(json_encode(array('status'=>-1,'msg'=>'缺少参数','result'=>'')));
        $address_id = I('address_id',0);
        $data = $this->userLogic->set_default($this->user_id,$address_id); // 获取用户信息
        if(!$data)
            exit(json_encode(array('status'=>-1,'msg'=>'操作失败','result'=>'')));
        exit(json_encode(array('status'=>1,'msg'=>'操作成功','result'=>'')));
    }


    // 管理收货地址
    public function address(){
        $type = I("post.type");
        $this->user_id = I('post.user_id');
        // 列表地址
        if($type == 1){
            if(!$this->user_id)
            exit(json_encode(array('status'=>-1,'msg'=>'缺少参数','result'=>'')));
            $address = M('user_address')->where(array('user_id'=>$this->user_id))->select();
            if(!$address)
                exit(json_encode(array('status'=>1,'msg'=>'没有数据','result'=>$address)));
            exit(json_encode(array('status'=>1,'msg'=>'获取成功','result'=>$address)));
        }elseif($type == 2){//添加/修改地址
            if(!$this->user_id) exit(json_encode(array('status'=>-1,'msg'=>'缺少参数','result'=>'')));
            $address_id = I('address_id',0);
            $data = $this->userLogic->add_address($this->user_id,$address_id,I('post.')); // 获取用户信息
            exit(json_encode($data));
        }elseif($type == 3){//删除地址
            $id = I('address_id');
            if(!$this->user_id) exit(json_encode(array('status'=>-1,'msg'=>'缺少参数','result'=>'')));
            $address = M('user_address')->where("address_id = $id")->find();
            $row = M('user_address')->where(array('user_id'=>$this->user_id,'address_id'=>$id))->delete();      
            // print_r($address);die();          
            // 如果删除的是默认收货地址 则要把第一个地址设置为默认收货地址
            if($address['is_default'] == 1)
            {
                $address = M('user_address')->where("user_id = {$this->user_id}")->order("address_id desc")->limit(1)->select();
                foreach ($address as $a => $as) {
                    M('user_address')->where("address_id = {$as['address_id']}")->save(array('is_default'=>1));
                }
                // M('user_address')->where("address_id = {$address['address_id']}")->save(array('is_default'=>1));
            }        
            if($row)
               exit(json_encode(array('status'=>1,'msg'=>'删除成功','result'=>''))); 
            else
               exit(json_encode(array('status'=>-1,'msg'=>'删除失败','result'=>''))); 
        }elseif($type == 4){//默认地址
            // print_r($this->user_id);die();
            if(!$this->user_id) exit(json_encode(array('status'=>-1,'msg'=>'缺少参数','result'=>'')));
            $address_id = I('address_id',0);
            $data = $this->userLogic->set_default($this->user_id,$address_id); // 获取用户信息
            if(!$data)
                exit(json_encode(array('status'=>-1,'msg'=>'操作失败','result'=>'')));
            exit(json_encode(array('status'=>1,'msg'=>'操作成功','result'=>'')));
        }
    }

    /*
     * 获取优惠券列表
     */
    public function getCouponList(){
        //$user_id = I('user_id',0);
        if(!$this->user_id)
            exit(json_encode(array('status'=>-1,'msg'=>'参数有误','result'=>'')));
        $data = $this->userLogic->get_coupon($this->user_id,$_REQUEST['type']);
        unset($data['show']);
        exit(json_encode($data));
    }
    /*
     * 获取商品收藏列表
     */
    public function getGoodsCollect(){
//        $user_id = I('user_id',0);
        if(!$this->user_id) exit(json_encode(array('status'=>-1,'msg'=>'缺少参数','result'=>'')));
        $data = $this->userLogic->get_goods_collect($this->user_id);

        unset($data['show']);
        exit(json_encode($data));
    }

    /*
     * 用户订单列表
     */
    public function getOrderList(){
       $shop = I('shop');
        $type = I('type','');
        if(!$this->user_id) exit(json_encode(array('status'=>-1,'msg'=>'缺少参数','result'=>'')));
        //条件搜索
        //I('field') && $map[I('field')] = I('value');
        //I('type') && $map['type'] = I('type');
        //$map['user_id'] = $user_id;
        $map = " user_id = {$this->user_id} ";        
        $map = $type ? $map.C($type) : $map;   
        if($shop != ""){
            $map = $map.'AND shop = '.$shop;
        }
        
        if(I('type') )
        $count = M('order')->where($map)->count();
        $Page       = new \Think\Page($count,10);

        $show = $Page->show();
        $order_str = "order_id DESC";
        // $order_list = M('order')->order($order_str)->where($map)->limit($Page->firstRow.','.$Page->listRows)->select();
        $order_list = M('order')->order($order_str)->where($map)->select();
// $sql = M("order")->getLastSql();
// echo $sql;
// die();
        // print_r($order_list);die();
        //获取订单商品
        foreach($order_list as $k=>$v){     
            $order_list[$k] = set_btn_order_status($v);  // 添加属性  包括按钮显示属性 和 订单状态显示属性
            //订单总额
            //$order_list[$k]['total_fee'] = $v['goods_amount'] + $v['shipping_fee'] - $v['integral_money'] -$v['bonus'] - $v['discount'];
            $data = $this->userLogic->get_order_goods($v['order_id']);
            $order_list[$k]['goods_list'] = $data['result']; 
            $original_img = $order_list[$k]['goods_list']; 
            foreach ($original_img as $i => $val) {
                $img = $val['original_img'];
                $original_img = SITE_URL.$img;
                $order_list[$k]['goods_list'][$i]['original_img'] = $original_img;
            }           
        }
        exit(json_encode(array('status'=>1,'msg'=>'获取成功','result'=>$order_list)));
    }
    /*
     * 获取订单详情
     */
    public function getOrderDetail(){
        //$user_id = I('user_id',0);
        if(!$this->user_id) exit(json_encode(array('status'=>-1,'msg'=>'缺少参数','result'=>'')));
        $id = I('id');
        if(I('id')){
            $map['order_id'] = $id;
        }else{
            $map['order_sn'] = I('sn');
        }
        $map['user_id'] = $this->user_id;
        $order_info = M('order')->where($map)->find();
        $order_info = set_btn_order_status($order_info);  // 添加属性  包括按钮显示属性 和 订单状态显示属性
        
        if(!$this->user_id > 0)
            exit(json_encode(array('status'=>-1,'msg'=>'参数有误','result'=>'')));
        if(!$order_info){
            exit(json_encode(array('status'=>-1,'msg'=>'订单不存在','result'=>'')));
        }
        
        $invoice_no = M('DeliveryDoc')->where("order_id = $id")->getField('invoice_no',true);
        $order_info['invoice_no'] = implode(' , ', $invoice_no);
        // 获取 最新的 一次发货时间
        $order_info['shipping_time'] = M('DeliveryDoc')->where("order_id = $id")->order('id desc')->getField('create_time');        
        
        //获取订单商品
        $data = $this->userLogic->get_order_goods($order_info['order_id']);
        $order_info['goods_list'] = $data['result'];
        //$order_info['total_fee'] = $order_info['goods_price'] + $order_info['shipping_price'] - $order_info['integral_money'] -$order_info['coupon_price'] - $order_info['discount'];

        // exit(json_encode(array('status'=>1,'msg'=>'获取成功','result'=>$order_info)));

        if(!$data){
            $json_arr = array('status'=>-1,'msg'=>'没有该商品','result'=>'');
        }else{
            $json_arr = array('status'=>1,'msg'=>'获取成功','result'=>$order_info);
        }
        
        $replace = array('src=&quot;\/'.SITE_URL.'\/Public','"'.SITE_URL.'\/Public');
        $search = array('src=&quot;\/Public','"\/Public');
        $json_str = json_encode($json_arr,TRUE); 
        $json_str = str_replace($search,$replace,$json_str);         
        exit($json_str);
    }

    /**
     * 取消/删除订单
     */
    public function cancelOrder(){
        $id = I('order_id');
//        $user_id = I('user_id',0);
        if(!$this->user_id > 0 || !$id > 0)
            exit(json_encode(array('status'=>-1,'msg'=>'参数有误','result'=>'')));
        if(I("type") == 1){//删除
            $ord = M("Order")->where('order_id='.$id)->delete();
            $ord_act = M("Order_action")->where('order_id='.$id)->delete();
            if($ord && $ord_act){
                exit(json_encode(array('status'=>1,'msg'=>'操作成功','result'=>'')));
            }
        }elseif(I("type") == 0){//取消
            $data = $this->userLogic->cancel_order($this->user_id,$id);
        }
        exit(json_encode($data));
    }

   
    /**
     * 发送手机注册验证码
     * http://www.tp-shop.cn/index.php?m=Api&c=User&a=send_sms_reg_code&mobile=13800138006&unique_id=123456
     */
    public function send_sms_reg_code(){
        $mobile = I('mobile');     
        $unique_id = I('unique_id');
        if(!check_mobile($mobile))
            exit(json_encode(array('status'=>-1,'msg'=>'手机号码格式有误')));
        $code =  rand(1000,9999);
        $send = $this->userLogic->sms_log($mobile,$code,$unique_id);
        if($send['status'] != 1)
            exit(json_encode(array('status'=>-1,'msg'=>$send['msg'])));
        exit(json_encode(array('status'=>1,'msg'=>'验证码已发送，请注意查收')));
    }    

    /**
     *  收货确认
     */
    public function orderConfirm(){
        $id = I('order_id',0);
        //$user_id = I('user_id',0);
        if(!$this->user_id || !$id)
            exit(json_encode(array('status'=>-1,'msg'=>'参数有误','result'=>'')));
        $data = confirm_order($id);            
        exit(json_encode($data));
    }
    
    
    /*
     *添加评论
     */
    public function add_comment(){                
      
            // 晒图片        
            if($_FILES[img_file][tmp_name][0])
            {
                    $upload = new \Think\Upload();// 实例化上传类
                    $upload->maxSize   =    $map['author'] = (1024*1024*3);// 设置附件上传大小 管理员10M  否则 3M
                    $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
                    $upload->rootPath  =     './Public/upload/comment/'; // 设置附件上传根目录
                    $upload->replace  =     true; // 存在同名文件是否是覆盖，默认为false
                    //$upload->saveName  =   'file_'.$id; // 存在同名文件是否是覆盖，默认为false
                    // 上传文件 
                    $info   =   $upload->upload();                 
                    if(!$info) {// 上传错误提示错误信息                                                                                                
                        exit(json_encode(array('status'=>-1,'msg'=>$upload->getError()))); //$this->error($upload->getError());
                    }else{
                        foreach($info as $key => $val)
                        {
                            $comment_img[] = '/Public/upload/comment/'.$val['savepath'].$val['savename'];                            
                        }   
                        $comment_img = serialize($comment_img); // 上传的图片文件
                    }                     
            }         
         
         
            
            $unique_id = I("unique_id"); // 唯一id  类似于 pc 端的session id
            //$user_id = I('user_id'); // 用户id
            $user_info = M('users')->where("user_id = {$this->user_id}")->find();            

            $add['goods_id'] = I('goods_id');
            $add['email'] = $user_info['email'];
            $add['header'] = $user_info['head_pic'];
            $add['username'] = $user_info['nickname'];
            $add['order_id'] = I('order_id');
            $add['service_rank'] = I('service_rank');
            $add['deliver_rank'] = I('deliver_rank');
            $add['goods_rank'] = I('goods_rank');
            $add['is_show'] = '1';
            $add['content'] = I('content');
            $add['img'] = $comment_img;
            $add['add_time'] = time();
            $add['ip_address'] = $_SERVER['REMOTE_ADDR'];
            $add['user_id'] = $this->user_id;                    
            
            //添加评论
            $row = $this->userLogic->add_comment($add);
            exit(json_encode($row));
    }  
    
    /*
     * 账户资金
     */
    public function account(){
        
        $unique_id = I("unique_id"); // 唯一id  类似于 pc 端的session id
       // $user_id = I('user_id'); // 用户id
        //获取账户资金记录
        
        $data = $this->userLogic->get_account_log($this->user_id,I('get.type'));
        $account_log = $data['result'];
        exit(json_encode(array('status'=>1,'msg'=>'获取成功','result'=>$account_log)));
    }    
    
    /**
     * 退换货列表
     */
    public function return_goods_list()
    {        
        
        $unique_id = I("unique_id"); // 唯一id  类似于 pc 端的session id
       // $user_id = I('user_id'); // 用户id       
        $count = M('return_goods')->where("user_id = {$this->user_id}")->count();        
        $page = new \Think\Page($count,4);
        $list = M('return_goods')->where("user_id = {$this->user_id}")->order("id desc")->limit("{$page->firstRow},{$page->listRows}")->select();
        $goods_id_arr = get_arr_column($list, 'goods_id');
        if(!empty($goods_id_arr))
            $goodsList = M('goods')->where("goods_id in (".  implode(',',$goods_id_arr).")")->getField('goods_id,goods_name');        
        foreach ($list as $key => $val)
        {
            $val['goods_name'] = $goodsList[$val[goods_id]];
            $list[$key] = $val;
        }
        //$this->assign('page', $page->show());// 赋值分页输出                    	    	
        exit(json_encode(array('status'=>1,'msg'=>'获取成功','result'=>$list)));
    }    
    
    
    /**
     *  售后 详情
     */
    public function return_goods_info()
    {
        $id = I('id',0);
        $return_goods = M('return_goods')->where("id = $id")->find();
        if($return_goods['imgs'])
            $return_goods['imgs'] = explode(',', $return_goods['imgs']);        
        $goods = M('goods')->where("goods_id = {$return_goods['goods_id']} ")->find();                
        $return_goods['goods_name'] = $goods['goods_name'];
        exit(json_encode(array('status'=>1,'msg'=>'获取成功','result'=>$return_goods)));
    }    
    
    
    /**
     * 申请退货状态
     */
    public function return_goods_status()
    {
        $order_id = I('order_id',0);        
        $goods_id = I('goods_id',0);
        $spec_key = I('spec_key','');
        
        $return_goods = M('return_goods')->where("order_id = $order_id and goods_id = $goods_id and spec_key = '$spec_key' and status in(0,1)")->find();            
        if(!empty($return_goods))        
            exit(json_encode(array('status'=>1,'msg'=>'已经在申请退货中..','result'=>$return_goods['id']))); 
         else
             exit(json_encode(array('status'=>1,'msg'=>'可以去申请退货','result'=>-1)));
    }
    /**
     * 申请退货
     */
    public function return_goods()
    {
        $unique_id = I("unique_id"); // 唯一id  类似于 pc 端的session id
        //$user_id = I('user_id'); // 用户id              
        $order_id = I('order_id',0);
        $order_sn = I('order_sn',0);
        $goods_id = I('goods_id',0);
        $type = I('type',0); // 0 退货  1为换货
        $reason = I('reason',''); // 问题描述
        $spec_key = I('spec_key');
		                
        if(empty($order_id) || empty($order_sn) || empty($goods_id)|| empty($this->user_id)|| empty($type)|| empty($reason))
            exit(json_encode(array('status'=>-1,'msg'=>'参数不齐!')));
        
        $return_goods = M('return_goods')->where("order_id = $order_id and goods_id = $goods_id and spec_key = '$spec_key' and status in(0,1)")->find();            
        if(!empty($return_goods))
        {
            exit(json_encode(array('status'=>-2,'msg'=>'已经提交过退货申请!')));
        }       
        if(IS_POST)
        {
            
    		// 晒图片
    		if($_FILES[img_file][tmp_name][0])
    		{
    			$upload = new \Think\Upload();// 实例化上传类
    			$upload->maxSize   =    $map['author'] = (1024*1024*3);// 设置附件上传大小 管理员10M  否则 3M
    			$upload->exts      =    array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
    			$upload->rootPath  =    './Public/upload/return_goods/'; // 设置附件上传根目录
    			$upload->replace   =    true; // 存在同名文件是否是覆盖，默认为false
    			//$upload->saveName  =  'file_'.$id; // 存在同名文件是否是覆盖，默认为false
    			// 上传文件
    			$upinfo  =  $upload->upload();
    			if(!$upinfo) {// 上传错误提示错误信息
    				$this->error($upload->getError());
    			}else{
    				foreach($upinfo as $key => $val)
    				{
    					$return_imgs[] = '/Public/upload/return_goods/'.$val['savepath'].$val['savename'];
    				}
    				$data['imgs'] = implode(',', $return_imgs);// 上传的图片文件
    			}
    		}            
            $data['order_id'] = $order_id; 
            $data['order_sn'] = $order_sn; 
            $data['goods_id'] = $goods_id; 
            $data['addtime'] = time(); 
            $data['user_id'] = $this->user_id;            
            $data['type'] = $type; // 服务类型  退货 或者 换货
            $data['reason'] = $reason; // 问题描述            
            $data['spec_key'] = $spec_key; // 商品规格						
            M('return_goods')->add($data);      
            exit(json_encode(array('status'=>1,'msg'=>'申请成功,客服第一时间会帮你处理!')));                        
        }     
    }    
    
    /*
     * 设置默认收货地址
     */
    public function set_default(){
        $uid = I('post.uid');
        $id = I('post.id');
        M('user_address')->where(array('user_id'=>$uid))->save(array('is_default'=>0));
        $row = M('user_address')->where(array('user_id'=>$uid,'address_id'=>$id))->save(array('is_default'=>1));
        if(!$row)
            $this->error('操作失败');
        exit(json_encode(array('status'=>1,'msg'=>'获取成功','result'=>"succeed"))); 
    }
    

    // U币交易记录user_money标记为U币数量
    public function ubi_show()
    {
        $user_id = I("post.user_id");
        $token = I("post.token");
        $user = M("Users")->where("token="."'".$token."'". " and "."user_id=".$user_id)->find();
        // $sql = M("Users")->getLastSql();echo $sql;die();
        if($user == '')
            exit(json_encode(array('status'=>-1,'msg'=>'Token不能为空','result'=>"Token不能为空")));
        $more = I("post.more");
        if($more == 1){
            $ubi = M('account_log')->where(array('user_id'=>$user_id,'token'=>$token))->order("change_time desc")->select();
        }else{
            $ubi = M('account_log')->where(array('user_id'=>$user_id,'token'=>$token))->order("change_time desc")->limit(16)->select();
        }
        // $count = M("account_log")->count("user_id");
        $total_money = $user['user_money'];
        // $ubi['count'] = $count;
        // $ubi = M('account_log')->where(array('user_id'=>$user_id,'token'=>$token))->order("change_time desc")->select();
        exit(json_encode(array('status'=>1,'msg'=>'获取成功','total_money'=>$total_money,'result'=>$ubi))); 
    }

    // U币转让
    public function ubi_sell(){
        if ($_POST && I("post.token") != "") {
            $data = $_POST; 
            $res = M("ubi_jiaoyi")->add($data);
            exit(json_encode(array('status'=>1,'msg'=>'发布成功','result'=>$data))); 
        }
    }

    // U币转让列表
    public function ubi_lists(){
        $more = I("post.more");
        if($more == 1){
            $ubi = M('ubi_jiaoyi')->order("add_time desc")->select();
        }else{
            $ubi = M('ubi_jiaoyi')->order("add_time desc")->limit(16)->select();
        }

        if(!$ubi){
            $json_arr = array('status'=>-1,'msg'=>'没有该商品','result'=>'');
        }else{
            $json_arr = array('status'=>1,'msg'=>'获取成功','result'=>$ubi);
        }
        
        $replace = array('src=&quot;\/'.SITE_URL.'\/Public','"'.SITE_URL.'\/Public');
        $search = array('src=&quot;\/Public','"\/Public');
        $json_str = json_encode($json_arr,TRUE); 
        $json_str = str_replace($search,$replace,$json_str);         
        exit($json_str);
    }

        // U币转让列表
    public function ubi(){
        $id = I("post.id");
        $user_id = I("post.user_id");
        $type = I("post.type");
        $ubi = M('ubi_jiaoyi')->where("deal_id=".$id)->find();

        if(I("post.click")=='1'){
            $data['click'] = ++$ubi['click'];
            $click['type'] = $type;
            $click['user_id'] = $user_id;
            $click['deal_id'] = $id;
            $res = M("ubi_jiaoyi")->where("deal_id=".$id)->save($data);
            $click = M("Click")->add($click);
            $clicks = M("Click")->where("deal_id=".$id)->select();
            foreach ($clicks as $c => $v) {
                $ids[] = $v['user_id'];
            }
            if (in_array($user_id, $ids)) {
                $ubi['click_ed'] = 1;
            }else{
                $ubi['click_ed'] = 0;
            }
            $json_arr = array('status'=>1,'msg'=>'点赞成功','result'=>$ubi);
            $json_str = json_encode($json_arr,TRUE); 
            exit($json_str);
        }elseif (I("post.click")=='0') {
            $data['click'] = --$ubi['click'];
            $res = M("ubi_jiaoyi")->where("deal_id=".$id)->save($data);
            $clicks = M("Click")->where("deal_id=".$id. " and user_id=". $user_id. " and type=".$type)->delete();
            $clicks = M("Click")->where("deal_id=".$id)->select();
            foreach ($clicks as $c => $v) {
                $ids[] = $v['user_id'];
            }
            if (in_array($user_id, $ids)) {
                $ubi['click_ed'] = 1;
            }else{
                $ubi['click_ed'] = 0;
            }
            $json_arr = array('status'=>1,'msg'=>'取消成功','result'=>$ubi);
            $json_str = json_encode($json_arr,TRUE); 
            exit($json_str);
        }elseif (I("post.click")== ""){
            $clicks = M("Click")->where("deal_id=".$id)->select();
            foreach ($clicks as $c => $v) {
                $ids[] = $v['user_id'];
            }
            if (in_array($user_id, $ids)) {
                $ubis = M('ubi_jiaoyi')->where("deal_id=".$id)->find();
                $ubis['click_ed'] = 1;
                $json_arr = array('status'=>1,'msg'=>'您已赞过了！谢谢！','result'=>$ubis);
            }else{
                $ubis = M('ubi_jiaoyi')->where("deal_id=".$id)->find();
                $ubis['click_ed'] = 0;
                $json_arr = array('status'=>1,'msg'=>'您还没有赞过！','result'=>$ubis);
            }
        }
        
        $replace = array('src=&quot;\/'.SITE_URL.'\/Public','"'.SITE_URL.'\/Public');
        $search = array('src=&quot;\/Public','"\/Public');
        $json_str = json_encode($json_arr,TRUE); 
        $json_str = str_replace($search,$replace,$json_str);    
        echo $json_str;   
        // exit($json_str);
    }


    //点赞
    public function liked()
    {
        $id = I("post.id");
        $user_id = I("post.user_id");
        $type = I("post.type");
        $ubi = M('ubi_jiaoyi')->where("deal_id=".$id)->find();

        if(I("post.click")=='1'){
            $data['click'] = ++$ubi['click'];
            $click['type'] = $type;
            $click['user_id'] = $user_id;
            $click['deal_id'] = $id;
            $res = M("ubi_jiaoyi")->where("deal_id=".$id)->save($data);
            $click = M("Click")->add($click);
            $clicks = M("Click")->where("deal_id=".$id)->select();
            foreach ($clicks as $c => $v) {
                $ids[] = $v['user_id'];
            }
            if (in_array($user_id, $ids)) {
                $ubi['click_ed'] = 1;
            }else{
                $ubi['click_ed'] = 0;
            }
            $json_arr = array('status'=>1,'msg'=>'点赞成功','result'=>$ubi);
            $json_str = json_encode($json_arr,TRUE); 
            exit($json_str);
        }elseif (I("post.click")=='0') {
            $data['click'] = --$ubi['click'];
            $res = M("ubi_jiaoyi")->where("deal_id=".$id)->save($data);
            $clicks = M("Click")->where("deal_id=".$id. " and user_id=". $user_id. " and type=".$type)->delete();
            $clicks = M("Click")->where("deal_id=".$id)->select();
            foreach ($clicks as $c => $v) {
                $ids[] = $v['user_id'];
            }
            if (in_array($user_id, $ids)) {
                $ubi['click_ed'] = 1;
            }else{
                $ubi['click_ed'] = 0;
            }
            $json_arr = array('status'=>1,'msg'=>'取消成功','result'=>$ubi);
            $json_str = json_encode($json_arr,TRUE); 
            exit($json_str);
        }elseif (I("post.click")== ""){
            $clicks = M("Click")->where("deal_id=".$id)->select();
            foreach ($clicks as $c => $v) {
                $ids[] = $v['user_id'];
            }
            if (in_array($user_id, $ids)) {
                $json_arr = array('status'=>0,'msg'=>'您已赞过了！谢谢！','result'=>'您已赞过了！谢谢！');
                $json_str = json_encode($json_arr,TRUE); 
                exit($json_str);
            }
        }
    }

    public function sendmes(){
        $to = I("tel");
        $data = rand(111111,999999);
        $_SESSION['code'] = $data;
        $num = $data;
        // print_r($_SESSION);
        $datas = array("".$data."",'5');
        $tempId = '1';
        $num = $this->messsend($to,$datas,$tempId,$num);
    }

    
    function messsend($to,$datas,$tempId,$num){
        //主帐号,对应开官网发者主账号下的 ACCOUNT SID
        $accountSid= '8a216da857f4d3ec0157ffb26ae00801';

        //主帐号令牌,对应官网开发者主账号下的 AUTH TOKEN
        $accountToken= '4ee547a697a044e1bdc587322674f1dc';

        //应用Id，在官网应用列表中点击应用，对应应用详情中的APP ID
        //在开发调试的时候，可以使用官网自动为您分配的测试Demo的APP ID
        $appId='8a216da857f4d3ec0157ffb26c980808';

        //请求地址
        //沙盒环境（用于应用开发调试）：sandboxapp.cloopen.com
        //生产环境（用户应用上线使用）：app.cloopen.com
        $serverIP='app.cloopen.com';

        //请求端口，生产环境和沙盒环境一致
        $serverPort='8883';

        //REST版本号，在官网文档REST介绍中获得。
        $softVersion='2013-12-26';
        // 初始化REST SDK
        // global $accountSid,$accountToken,$appId,$serverIP,$serverPort,$softVersion;
        // print_r($serverIP);die();
        $rest = new \Home\Logic\RestLogic($serverIP,$serverPort,$softVersion);
        // $rest = require SITE_URL'/Home/Logic/Rest.class.php';
        // $rest = $restLogic;
        $rest->setAccount($accountSid,$accountToken);
        $rest->setAppId($appId);

        
        // 发送模板短信
        $result = $rest->sendTemplateSMS($to,$datas,$tempId);
        if($result == NULL ) {
         echo "result error!";
         break;
        }
        if($result->statusCode!=0) {
         echo "error code :" . $result->statusCode . "<br>";
         echo "error msg :" . $result->statusMsg . "<br>";
         //TODO 添加错误处理逻辑
        }else{         // 获取返回信息
         $smsmessage = $result->TemplateSMS;
         $dat = "dateCreated:".$smsmessage->dateCreated."smsMessageSid:".$smsmessage->smsMessageSid;
         $json_arr = array('status'=>1,'msg'=>'获取成功！','result'=>$num);
                $json_str = json_encode($json_arr,TRUE); 
                exit($json_str);
         //TODO 添加成功处理逻辑
        }
    }

    //忘记密码
    public function refpassd(){
        $tel = I("tel");
        $user = M("Users")->where("mobile=".$tel)->find();
        // if(empty(I("code")))
        //     return array('status'=>0,'msg'=>'请输入验证码','result'=>'');
        if(empty($user)){
            $json_arr = array('status'=>0,'msg'=>'你还没有注册！别闹！','result'=>$num);
            $json_str = json_encode($json_arr,TRUE); 
            exit($json_str);
        }else{
            $data['password'] = encrypt(I("password"));
            $users = M("Users")->where("mobile=".$tel)->save($data);
            $user = M("Users")->where("mobile=".$tel)->find();
            $json_arr = array('status'=>1,'msg'=>'修改成功！','result'=>$user);
            $json_str = json_encode($json_arr,TRUE); 
            exit($json_str);
        }
    }


       /**
     * 账户管理->密码安全->发送验证码
     * @param Int mobile  手机号
     * @param String user_id  身份号
     */
    public function sendMessageCode(){
        $mobile=I('post.mobile');
        $user_id=I('post.user_id');
        //检查输入的手机号与绑定手机号是否一致
        $user = $this->userLogic->get_info($user_id);
        if ($user['result']['mobile']!=$mobile) {
            $json_arr = array('status'=>0,'msg'=>'请输入正确的手机号','result'=>'请输入正确的手机号');
            $json_str = json_encode($json_arr,TRUE); 
            exit($json_str);
        }
        //发送手机验证码
        $this->userLogic->send_validate_code($mobile,'message');
    }

    /**
     * 账户管理->密码安全->检验验证码并重置密码
     * @param String user_id  身份号
     * @param String code 验证码
     * @param String password 新密码
     */
    public function resetPassword(){
        $user_id=I('post.user_id');
        $code=I('post.code');
        $mobile=I('post.mobile');
        $password=I('post.password');
        //验证验证码是否正确
        $res=$this->userLogic->check_validate_code_no_die($code,$mobile);
        var_dump($res);
        if ($res['status']!=1) {
            $json_arr = array('status'=>0,'msg'=>'验证码错误','result'=>'请输入正确的验证码');
            $json_str = json_encode($json_arr,TRUE); 
            exit($json_str);
        }

        //修改账号的密码
        $data['password']=md5($password);
        $result=$this->userLogic->update_info($user_id,$data);
        if ($result) {
            $json_arr = array('status'=>1,'msg'=>'重置密码成功','result'=>'重置密码成功');
            $json_str = json_encode($json_arr,TRUE); 
            exit($json_str);
        }
    }
    

    /**
     * 账户管理->密码安全->修改支付密码
     * @param String user_id  身份号
     * @param String oldPassword 原密码
     * @param String password 新密码
     */
    public function updatePayPassword(){
        $user_id=I('post.user_id');
        $oldPassword=I('post.oldPassword');
        $password=I('post.password');
        //判断参数
        if (!$user_id) {
            $json_arr = array('status'=>-1,'msg'=>'参数错误','result'=>'参数错误');
            $json_str = json_encode($json_arr,TRUE); 
            exit($json_str);
        }else{
            //判断原密码是否正确
            // $user = $this->userLogic->get_info($user_id);
            $user=M('users')->where('user_id='.$user_id)->find();
            // echo $user['result']['pay_pass'];
            // var_dump($user);
            if ($user['pay_pass']!=md5($oldPassword)) {
                $json_arr = array('status'=>1,'msg'=>'密码输入错误','result'=>'密码输入错误');
                $json_str = json_encode($json_arr,TRUE); 
                exit($json_str);
            }
            //修改密码
            $data['pay_pass']=md5($password);
            $result=$this->userLogic->update_info($user_id,$data);
            if ($result) {
                $json_arr = array('status'=>1,'msg'=>'修改密码成功','result'=>'修改密码成功');
                $json_str = json_encode($json_arr,TRUE); 
                exit($json_str);
            }
        }
    }

    function head_pic() {

        $data = file_get_contents('php://input');
        $file = explode("&head_pic=", $data);
        $df = $file['1'];
        // var_dump($file);
        $path = 'Public/upload/head_pic';
        $this->creatfile($path);
        $name=time();
        $file = $path.'/' . $name . '.png';
        // mkdir($file,0777);
        // var_dump($data);
        // die();
        file_put_contents($file, $df);
        
    }

    // 修改手机号
    public function edit_moblie()
    {
        $user_id = I('user_id');
        if($user_id == '')
            exit(json_encode(array('status'=>0,'msg'=>'user_id不能为空','result'=>"user_id不能为空")));
        $token = I('token');
        if($token == '')
            exit(json_encode(array('status'=>0,'msg'=>'Token不能为空','result'=>"Token不能为空")));
        $tel = I('tel');
        if($tel == '')
            exit(json_encode(array('status'=>0,'msg'=>'联系电话不能为空','result'=>"联系电话不能为空")));
        $us = M("Users")->where("mobile='$tel'")->find();
        if(!empty($us)){
            exit(json_encode(array('status'=>-1,'msg'=>'手机号已存在','result'=>"手机号已存在")));
        }
        $data['mobile'] = $tel;
        $res = M("Users")->where("user_id=$user_id and token= '$token'")->save($data);
        $user = M("Users")->where("user_id=$user_id and token= '$token'")->find();
        if ($res) {
            exit(json_encode(array('status'=>1,'msg'=>'更改成功','result'=>$user)));
        }
        
    }

    // 修改用户名
    public function edit_name()
    {
        $user_id = I('user_id');
        if($user_id == '')
            exit(json_encode(array('status'=>0,'msg'=>'user_id不能为空','result'=>"user_id不能为空")));
        $token = I('token');
        if($token == '')
            exit(json_encode(array('status'=>0,'msg'=>'Token不能为空','result'=>"Token不能为空")));
        $nickname = I('nickname');
        if($nickname == '')
            exit(json_encode(array('status'=>0,'msg'=>'昵称不能为空','result'=>"昵称不能为空")));
        $us = M("Users")->where("nickname='$nickname'")->find();
        if(!empty($us)){
            exit(json_encode(array('status'=>-1,'msg'=>'昵称已存在','result'=>"昵称已存在")));
        }
        $data['nickname'] = $nickname;
        $res = M("Users")->where("user_id=$user_id and token= '$token'")->save($data);
        $user = M("Users")->where("user_id=$user_id and token= '$token'")->find();
        if ($res) {
            exit(json_encode(array('status'=>1,'msg'=>'更改成功','result'=>$user)));
        }
        
    }

    //交流平台商品列表
   public function forumlist(){
        $user_id = I('user_id');
        $type = 'FINISH';
        // if(!$this->user_id) exit(json_encode(array('status'=>-1,'msg'=>'缺少参数','result'=>'')));
        //条件搜索
        //I('field') && $map[I('field')] = I('value');
        //I('type') && $map['type'] = I('type');
        //$map['user_id'] = $user_id;
        $map = " user_id = $user_id";        
        $map = $type ? $map.C($type) : $map;   
        // print_r($map);
        
        if(I('type') )
        $count = M('order')->where($map)->count();
        $Page       = new \Think\Page($count,10);

        $show = $Page->show();
        $order_str = "order_id DESC";
        $order_list = M('order')->order($order_str)->where($map)->limit($Page->firstRow.','.$Page->listRows)->select();

        //获取订单商品
        foreach($order_list as $k=>$v){     
            $order_list[$k] = set_btn_order_status($v);  // 添加属性  包括按钮显示属性 和 订单状态显示属性
            //订单总额
            //$order_list[$k]['total_fee'] = $v['goods_amount'] + $v['shipping_fee'] - $v['integral_money'] -$v['bonus'] - $v['discount'];
            $data = $this->userLogic->get_order_goods($v['order_id']);
            $order_list[$k]['goods_list'] = $data['result']; 
            $original_img = $order_list[$k]['goods_list']; 
            foreach ($original_img as $i => $val) {
                $img = $val['original_img'];
                $original_img = SITE_URL.$img;
                $order_list[$k]['goods_list'][$i]['original_img'] = $original_img;
                $goods_list[] = $order_list[$k]['goods_list'];
                $goods_list[$k][$i]['order_status_code'] = $order_list[$k]['order_status_code'];
                $goods_list[$k][$i]['order_status_desc'] = $order_list[$k]['order_status_desc'];
            }      
        }
        foreach ($goods_list as $gl => $glist) {
            foreach ($glist as $g => $gls) {
                $good_list[] = $gls;
            }
        }
        exit(json_encode(array('status'=>1,'msg'=>'获取成功','result'=>$good_list)));
    }

    // 交流平台发布消息
    public function share_gds()
    {
        $share = I("share");
        if($share == 1){
            $data['goods_id'] = I("goods_id");
            $data['user_id'] = I("user_id");
            $data['content'] = I("content");
            $data['add_time'] = time();
            $user = M("users")->where("user_id=".I("user_id"))->find();
            $goods = M("goods")->where("goods_id=".I("goods_id"))->find();
            $data['user_name'] = $user["nickname"];
            $data['head_pic'] = $user["head_pic"];
            $data['goods_price'] = $goods["shop_price"];
            $data['goods_name'] = $goods["goods_name"];
            $data['original_img'] = $goods["original_img"];
            $data['cat_id'] = $goods["cat_id"];

            $base64 = $_POST['images'];
            $base64 = explode("(", $base64);
            $base64 = explode(")", $base64[1]);
            $base64 = explode(",", $base64[0]);
            // print_r($base64);die();
            foreach ($base64 as $b => $val) {
                $base64_image = str_replace(' ', '+', $val);
                //post的数据里面，加号会被替换为空格，需要重新替换回来，如果不是post的数据，则注释掉这一行
                $image_name = time().rand(10000,99999).'.png';
                $image_file = 'Public/upload/share/'.$image_name;
                // echo $base64_image;
                //服务器文件存储路径
                $result = file_put_contents($image_file, base64_decode($base64_image));

                $images[] = SITE_URL.'/'.$image_file;
            }
                $data['images'] = serialize($images);
                // print_r($data);
                $res = M("forum")->add($data);
            if ($res){
                exit(json_encode(array('status'=>1,'msg'=>'发布成功','result'=>1)));

            }else{
                exit(json_encode(array('status'=>0,'msg'=>'发布失败','result'=>'')));
            }

        }
        $goods_id = I("post.goods_id");
        $goods = M("goods")->where("goods_id = $goods_id")->getField("goods_id,goods_name,shop_price,original_img");
        $user_id = I("post.user_id");
        $users = M("users")->where("user_id = $user_id")->getField("user_id,nickname,head_pic");
        $res['goods'] = $goods;
        $res['users'] = $users;
        if(!$res){
            $json_arr = array('status'=>-1,'msg'=>'没有该商品','result'=>'');
        }else{
            $json_arr = array('status'=>1,'msg'=>'获取成功','result'=>$res);
        }
        
        $replace = array('src=&quot;\/'.SITE_URL.'\/Public','"'.SITE_URL.'\/Public');
        $search = array('src=&quot;\/Public','"\/Public');
        $json_str = json_encode($json_arr,TRUE); 
        $json_str = str_replace($search,$replace,$json_str);         
        exit($json_str);
    }

    // 交流信息列表
    public function forum_list(){
        $forum = M("forum")->select();
        $user_id = I("user_id");
        if(I("click") != ""){
            if (I("post.click")=='1') {
                $click['type'] = $type;
                $click['user_id'] = $user_id;
                $click['forum_id'] = I("forum_id");
                // $click['user_id'] = I("user_id");
                $clicks = M("forum_click")->where("user_id=".$user_id)->select();
                foreach ($clicks as $c => $vc) {
                    $ids[] = $vc['ask_users'];
                    $fids[] = $vc['forum_id'];
                }
                if(in_array(I("forum_id"), $fids) && in_array(I("ask_users"), $ids)){
                    $json_arr = array('status'=>0,'msg'=>'已点赞','result'=>'已点赞');
                }else{
                    $click = M("forum_click")->add($click);
                    foreach ($forum as $f => $fs) {
                        $data['click'] = ++$fs['click'];
                        $res = M("forum")->where("forum_id=".I("forum_id"))->save($data);
                    }   
                    $clicks = M("forum_click")->where("user_id=".$user_id)->select();
                    $forum_se = M("forum")->select();
                    foreach ($clicks as $cl => $cls) {
                        foreach ($forum_se as $fs => $fos) {
                            if($fos['forum_id'] == $cls['forum_id']){
                                $forum_se[$fs]['click_ed'] = "1";
                            }
                        }
                    }
                    foreach ($forum_se as $key => $value) {
                        $forum_se[$key]['images'] = unserialize($value['images']);
                    }
                    $json_arr = array('status'=>1,'msg'=>'获取成功','result'=>$forum_se);
                }
            }elseif (I("post.click")=='0') {
                $forum = M("forum")->where('forum_id='.I("forum_id"))->find();
                $data['click'] = --$forum['click'];
                M("forum")->where('forum_id='.I("forum_id"))->save($data);
                M("forum_click")->where("user_id=".$user_id." AND forum_id=".I("forum_id")." AND ask_users=0")->delete();
                $clicks = M("forum_click")->where("user_id=".$user_id)->select();
                $forum_se = M("forum")->select();
                foreach ($clicks as $cl => $cls) {
                    foreach ($forum_se as $fs => $fos) {
                        if($fos['forum_id'] == $cls['forum_id'] && $cls['ask_users'] == 0){
                            $forum_se[$fs]['click_ed'] = "1";
                        }
                    }
                }
                foreach ($forum_se as $key => $value) {
                    $forum_se[$key]['images'] = unserialize($value['images']);
                }
                $json_arr = array('status'=>1,'msg'=>'取消成功','result'=>$forum_se);
            }
        }else{
            if(!empty(I("user_id"))){
                $clicks = M("forum_click")->where("user_id=".$user_id)->select();
            }else{
                $clicks = array();
            }
            
            $forum_se = M("forum")->select();
            foreach ($clicks as $cl => $cls) {
                foreach ($forum_se as $fs => $fos) {
                    if($fos['forum_id'] == $cls['forum_id']){
                        $forum_se[$fs]['click_ed'] = "1";
                    }
                }
            }


            foreach ($forum_se as $key => $value) {
                $forum_se[$key]['images'] = unserialize($value['images']);
                if($forum_se[$key]['images'] == false){
                    $forum_se[$key]['images'] = array();
                }
                if(empty(I("user_id"))){
                    $forum_se[$key]['click_ed'] = "0";
                }
            }
            $json_arr = array('status'=>1,'msg'=>'获取成功','result'=>$forum_se);
        }
        
        $this->replace($json_arr); 
        exit($json_str);
    }

    // 交流信息详情及评论
    public function forum_lc(){
        // 读取信息，是否给评论用户点赞
        if(I("type") == "0"){
            $data['user_id'] = I("user_id");
            $data['forum_id'] = I("forum_id");
            $data['ask_users'] = I("ask_users");
            $forum_data = M("forum_data")->where("forum_id=".I("forum_id"))->order("add_time desc")->select();
            $clicks = M("forum_click")->where("user_id=".I("user_id"))->select();
            foreach ($clicks as $cl => $cls) {
                foreach ($forum_data as $fs => $fos) {
                    if($fos['forum_id'] == $cls['forum_id'] && $cls['ask_id'] == $fos['ask_id']){
                        $forum_data[$fs]['click_ed'] = "1";
                    }
                }
            }
        }elseif(I("type") == "1"){
            // 评论
            $user = M("Users")->where("user_id=".I("user_id"))->find();
            $data['head_pic'] = $user['head_pic'];
            $data['user_id'] = $user['user_id'];
            $data['user_name'] = $user['nickname'];
            $data['to_user_id'] = I('u_id');
            $data['forum_id'] = I("forum_id");
            $data['content'] = I("content");
            $data['to_ask_id'] = I("to_ask_id");
            $data['add_time'] = time();
            $forum_data = M("forum_data")->where("forum_id=".I("forum_id")." AND user_id=".I("user_id"))->order("add_time desc")->select();
            foreach ($forum_data as $key => $f) {
                $cont[] = $f['content'];
            }
            if(in_array(I("content"), $cont)){
                exit(json_encode(array('status'=>0,'msg'=>'您已评论过了,聊点其他的吧','result'=>$forum_data)));
            }else{
                $fo_data = M("forum_data")->add($data);
            }
            if($fo_data){
                $forum_data = M("forum_data")->where("forum_id=".I("forum_id"))->order("add_time desc")->select();
                $clicks = M("forum_click")->where("user_id=".I("user_id"))->select();
                foreach ($clicks as $cl => $cls) {
                    foreach ($forum_data as $fs => $fos) {
                        if($fos['forum_id'] == $cls['forum_id'] && $fos['to_ask_id'] == 0){
                            $forum_data[$fs]['click_ed'] = "1";
                        }
                    }
                }
            }else{
                exit(json_encode(array('status'=>0,'msg'=>'评论失败','result'=>$forum)));
            }
            
        }elseif (I("type") == 2) {
            // 给评论用户点赞
            if (I("post.click")=='1') {
                $click['user_id'] = I("user_id");
                $click['forum_id'] = I("forum_id");
                $click['ask_users'] = I("ask_users");
                $click['ask_id'] = I("ask_id");
                $clicks = M("forum_click")->where("user_id=".I("user_id"))->select();
                foreach ($clicks as $c => $vc) {
                    $ids[] = $vc['ask_id'];
                    $akids[] = $vc['ask_users'];
                    $fids[] = $vc['forum_id'];
                }
                if(in_array(I("forum_id"), $fids) && in_array(I("ask_users"), $akids) && in_array(I("ask_id"), $ids)){
                    $json_arr = array('status'=>0,'msg'=>'已点赞','result'=>'已点赞');
                    $json_str = json_encode($json_arr,TRUE); 
                    exit($json_str);
                }else{
                    $click = M("forum_click")->add($click);
                    $forum = M("forum")->where("forum_id=".I("forum_id"))->find();
                    $forum_data = M("forum_data")->where("forum_id=".I("forum_id")." AND ask_id=".I("ask_id"))->find();
                    $data['ask_user'] = I("ask_user");
                    if(I("ask_id") == 0){
                        $data['click'] = ++$forum['click'];
                        $res = M("forum")->where("forum_id=".I("forum_id"))->save($data);
                    }else{
                        $data['click'] = ++$forum_data['click'];
                        $res = M("forum_data")->where("forum_id=".I("forum_id")." AND ask_id=".I("ask_id"))->save($data);
                    }
                    
                    $forum_data = M("forum_data")->where("forum_id=".I("forum_id"))->order("add_time desc")->select();
                    $clicks = M("forum_click")->where("user_id=".I("user_id"))->select();
                    foreach ($clicks as $cl => $cls) {
                        foreach ($forum_data as $fs => $fos) {
                            if($fos['forum_id'] == $cls['forum_id'] && $fos['ask_id'] == $cls['ask_id']){
                                $forum_data[$fs]['click_ed'] = "1";
                            }
                        }
                    }
                }
            }elseif (I("post.click")=='0') {
                $forum = M("forum")->where('forum_id='.I("forum_id"))->find();
                $data['click'] = --$forum['click'];
                M("forum")->where('forum_id='.I("forum_id"))->save($data);

                $forum_data = M("forum_data")->where('forum_id='.I("forum_id"))->find();
                $data['click'] = --$forum_data['click'];
                M("forum_data")->where('forum_id='.I("forum_id")." AND ask_id=".I("ask_id"))->save($data);
                M("forum_click")->where("user_id=".I("user_id")." AND forum_id=".I("forum_id")." AND ask_id=".I("ask_id"))->delete();
                $clicks = M("forum_click")->where("user_id=".I("user_id"))->select();
                $forum_se = M("forum")->select();
                $forum_data = M("forum_data")->where("forum_id=".I("forum_id"))->order("add_time desc")->select();
                $clicks = M("forum_click")->where("user_id=".I("user_id"))->select();
                foreach ($clicks as $cl => $cls) {
                    foreach ($forum_data as $fs => $fos) {
                        if($fos['forum_id'] == $cls['forum_id'] && $cls['ask_id'] == $fos['ask_id']){
                            $forum_data[$fs]['click_ed'] = "1";
                        }
                    }
                }
            }
        }
        
        $forum = M("forum")->where("forum_id=".I("forum_id"))->find();
        $clicks = M("forum_click")->where("user_id=".I("user_id"))->select();
        foreach ($clicks as $cl => $cls) {
            if($forum['forum_id'] == $cls['forum_id'] && $cls['ask_id'] == 0){
                $forum['click_ed'] = "1";
            }
        }
        $fo_data = $this->tree($forum_data);
        $forum['images'] = unserialize($forum['images']);
        $cats = M("goods_category")->where('id='.$forum['cat_id'])->find();
        $forum['cat_name'] = $cats['name'];
        // print_r($cats);
        // die();
        // foreach ($fo_data as $key => $value) {
        //     foreach ($fo_data as $fd => $fds) {
        //         if($fo_data[$fd]['to_ask_id'] == $fo_data[$key]['ask_id']){
        //             $fo_data[$key]['data'][] = $fo_data[$fd];
        //             unset($fo_data[$fd]);
        //         }
        //     }
        // }

        //循环评论信息，判断层级
        foreach ($fo_data as $key => $value) {
            foreach ($fo_data as $fd => $fds) {
                // 评论信息里的被回复的问题id=评论问题id
                if($fo_data[$fd]['to_ask_id'] == $fo_data[$key]['ask_id']){
                    // 第一级评论整合成数组
                    $fo_data[$key]['data'][] = $fo_data[$fd];
                    // $fo_da_da[] = $fo_data[$fd];
                    // 删除该判断层级的元素
                    unset($fo_data[$fd]);
                    // 循环第二层评论
                    foreach ($fo_data[$key]['data'] as $fdd => $fdds) {
                        foreach ($fo_data[$key]['data'] as $fddk => $fddks) {
                            // 第一层评论里的用户id=下一级的被回复的用户id
                            if ($fo_data[$key]['data'][$fdd]['user_id'] == $fo_data[$key]['data'][$fddk]['to_user_id']) {
                                // 整合层级
                                $fo_data[$key]['data'][$fdd]['datas'][] = $fo_data[$key]['data'][$fddk];
                                unset($fo_data[$key]['data'][$fddk]);
                            }
                        break;
                        }
                    }
                }
            }
        }

        foreach ($fo_data as $f => $fs) {
            $for_data[] = $fs;
        }
        
        $result['forum'] = $forum;
        $result['forum_data'] = $for_data;
        // print_r($result);
        // die();
        $json_arr = array('status'=>1,'msg'=>'获取成功','result'=>$result);
        $json_str = json_encode($json_arr,TRUE); 
        exit($json_str);
    }

    public function addwxmes(){
        $data = I("post.");
        $data['oauth'] = "weixin";
        $data['reg_time'] = time();
        $data['token'] = md5(time().mt_rand(1,999999999));
        $openid = $data['openid'];
        $users = M("Users")->where("openid="."'".$openid."'")->find();

        if(empty($users)){
            $res = M("Users")->add($data);
        }else{
            $users = M("Users")->where("openid="."'".$openid."'")->find();
            $json_arr = array('status'=>1,'msg'=>'已授权，可直接登录','result'=>$users);
        }
        if($res){
            $users = M("Users")->where("openid="."'".$openid."'")->find();
            $json_arr = array('status'=>1,'msg'=>'登录成功','result'=>$users);
        }
        $json_str = json_encode($json_arr,TRUE); 
        exit($json_str);

    }

    //递归创建文件夹
    public function creatfile($path){
      if (!file_exists($path))
      {
       $this->creatfile(dirname($path));
       mkdir($path, 0777);
      }
    }

    //存放无限分类
    static public $treeList = array(); 
    public function tree(&$data,$parentid = 0,$level = 0,$sign=' ˉ┗  ') {
        foreach ($data as $key => $value){
            if($value['to_ask_id']==$parentid){
                $value['level']=$level+1;
                if($value['to_ask_id'] == 0){
                    $value['sign'] = '┊';
                }else{$value['sign']=str_repeat('┊',$value['level']-1).$sign;}
                self::$treeList []=$value;                
                self::tree($data,$value['ask_id'],$level+1,$sign);
            }
        }
        $arr = self::$treeList;
        return $arr ;
    }

    //路径替换
    public function replace($json_arr){
        $replace = array('src=&quot;'.SITE_URL.'\/Public','"'.SITE_URL.'\/Public');
        $search = array('src=&quot;\/Public','"\/Public');
        $json_str = json_encode($json_arr); 
        $sea_str = array("&lt;","&gt;","<img");
        $rep_str = array("<",">","<img style='width:100%'");
        $json_str = str_replace($search,$replace,$json_str);
        $json_str = str_replace($sea_str,$rep_str,$json_str); 
        $json_str = str_replace("&quot;","'",$json_str);         
        exit($json_str);
    }


    // 交流信息详情及评论
    public function forum_lcs(){
        // 读取信息，是否给评论用户点赞
        if(I("type") == "0"){
            $data['user_id'] = I("user_id");
            $data['forum_id'] = I("forum_id");
            $data['ask_users'] = I("ask_users");
            $forum_data = M("forum_data")->where("forum_id=".I("forum_id"))->order("add_time desc")->select();
            $clicks = M("forum_click")->where("user_id=".I("user_id"))->select();
            foreach ($clicks as $cl => $cls) {
                foreach ($forum_data as $fs => $fos) {
                    if($fos['forum_id'] == $cls['forum_id'] && $cls['ask_id'] == $fos['ask_id']){
                        $forum_data[$fs]['click_ed'] = "1";
                    }
                }
            }
        }elseif(I("type") == "1"){
            // 评论
            $user = M("Users")->where("user_id=".I("user_id"))->find();
            $data['head_pic'] = $user['head_pic'];
            $data['user_id'] = $user['user_id'];
            $data['user_name'] = $user['nickname'];
            $data['to_user_id'] = I('u_id');
            $data['forum_id'] = I("forum_id");
            $data['content'] = I("content");
            $data['to_ask_id'] = I("to_ask_id");
            $data['add_time'] = time();
            $forum_data = M("forum_data")->where("forum_id=".I("forum_id")." AND user_id=".I("user_id"))->order("add_time desc")->select();
            foreach ($forum_data as $key => $f) {
                $cont[] = $f['content'];
            }
            if(in_array(I("content"), $cont)){
                exit(json_encode(array('status'=>0,'msg'=>'您已评论过了,聊点其他的吧','result'=>$forum_data)));
            }else{
                $fo_data = M("forum_data")->add($data);
            }
            if($fo_data){
                $forum_data = M("forum_data")->where("forum_id=".I("forum_id"))->order("add_time desc")->select();
                $clicks = M("forum_click")->where("user_id=".I("user_id"))->select();
                foreach ($clicks as $cl => $cls) {
                    foreach ($forum_data as $fs => $fos) {
                        if($fos['forum_id'] == $cls['forum_id'] && $fos['to_ask_id'] == 0){
                            $forum_data[$fs]['click_ed'] = "1";
                        }
                    }
                }
            }else{
                exit(json_encode(array('status'=>0,'msg'=>'评论失败','result'=>$forum)));
            }
            
        }elseif (I("type") == 2) {
            // 给评论用户点赞
            if (I("post.click")=='1') {
                $click['user_id'] = I("user_id");
                $click['forum_id'] = I("forum_id");
                $click['ask_users'] = I("ask_users");
                $click['ask_id'] = I("ask_id");
                $clicks = M("forum_click")->where("user_id=".I("user_id"))->select();
                foreach ($clicks as $c => $vc) {
                    $ids[] = $vc['ask_id'];
                    $akids[] = $vc['ask_users'];
                    $fids[] = $vc['forum_id'];
                }
                if(in_array(I("forum_id"), $fids) && in_array(I("ask_users"), $akids) && in_array(I("ask_id"), $ids)){
                    $json_arr = array('status'=>0,'msg'=>'已点赞','result'=>'已点赞');
                    $json_str = json_encode($json_arr,TRUE); 
                    exit($json_str);
                }else{
                    $click = M("forum_click")->add($click);
                    $forum = M("forum")->where("forum_id=".I("forum_id"))->find();
                    $forum_data = M("forum_data")->where("forum_id=".I("forum_id")." AND ask_id=".I("ask_id"))->find();
                    $data['ask_user'] = I("ask_user");
                    if(I("ask_id") == 0){
                        $data['click'] = ++$forum['click'];
                        $res = M("forum")->where("forum_id=".I("forum_id"))->save($data);
                    }else{
                        $data['click'] = ++$forum_data['click'];
                        $res = M("forum_data")->where("forum_id=".I("forum_id")." AND ask_id=".I("ask_id"))->save($data);
                    }
                    
                    $forum_data = M("forum_data")->where("forum_id=".I("forum_id"))->order("add_time desc")->select();
                    $clicks = M("forum_click")->where("user_id=".I("user_id"))->select();
                    foreach ($clicks as $cl => $cls) {
                        foreach ($forum_data as $fs => $fos) {
                            if($fos['forum_id'] == $cls['forum_id'] && $fos['ask_id'] == $cls['ask_id']){
                                $forum_data[$fs]['click_ed'] = "1";
                            }
                        }
                    }
                }
            }elseif (I("post.click")=='0') {
                $forum = M("forum")->where('forum_id='.I("forum_id"))->find();
                $data['click'] = --$forum['click'];
                M("forum")->where('forum_id='.I("forum_id"))->save($data);

                $forum_data = M("forum_data")->where('forum_id='.I("forum_id"))->find();
                $data['click'] = --$forum_data['click'];
                M("forum_data")->where('forum_id='.I("forum_id")." AND ask_id=".I("ask_id"))->save($data);
                M("forum_click")->where("user_id=".I("user_id")." AND forum_id=".I("forum_id")." AND ask_id=".I("ask_id"))->delete();
                $clicks = M("forum_click")->where("user_id=".I("user_id"))->select();
                $forum_se = M("forum")->select();
                $forum_data = M("forum_data")->where("forum_id=".I("forum_id"))->order("add_time desc")->select();
                $clicks = M("forum_click")->where("user_id=".I("user_id"))->select();
                foreach ($clicks as $cl => $cls) {
                    foreach ($forum_data as $fs => $fos) {
                        if($fos['forum_id'] == $cls['forum_id'] && $cls['ask_id'] == $fos['ask_id']){
                            $forum_data[$fs]['click_ed'] = "1";
                        }
                    }
                }
            }
        }
        
        $forum = M("forum")->where("forum_id=".I("forum_id"))->find();
        $clicks = M("forum_click")->where("user_id=".I("user_id"))->select();
        foreach ($clicks as $cl => $cls) {
            if($forum['forum_id'] == $cls['forum_id'] && $cls['ask_id'] == 0){
                $forum['click_ed'] = "1";
            }
        }
        $fo_data = $this->tree($forum_data);
        $forum['images'] = unserialize($forum['images']);
        $cats = M("goods_category")->where('id='.$forum['cat_id'])->find();
        $forum['cat_name'] = $cats['name'];
        // print_r($cats);
        // die();

       foreach ($fo_data as $key => $value) {
            foreach ($fo_data as $fd => $fds) {
                // 评论信息里的被回复的问题id=评论问题id
                if($fo_data[$fd]['to_ask_id'] == $fo_data[$key]['ask_id']){
                    // print_r($value);
                    $fo_data[$key]['val_data'] = $fds;
                    $fff[] = $fo_data[$key];
                    // 删除该判断层级的元素
                    unset($fo_data[$fd]);
                }
            }
            print_r($fff);
        }

        foreach ($fo_data as $f => $fs) {
            $for_data[] = $fs;
        }
        // print_r($for_data);
        $result['forum'] = $forum;
        $result['forum_data'] = $for_data;
        // print_r($result);
        // die();
        $json_arr = array('status'=>1,'msg'=>'获取成功','result'=>$result);
        $json_str = json_encode($json_arr,TRUE); 
        exit($json_str);
    }

}