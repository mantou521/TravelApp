<?php
/**
 *
 * * 
 * ============================================================================
 * $ 2015-08-10 $
 */ 
namespace Api\Controller;
use Home\Logic\UsersLogic;
use Think\Controller;
class UsersController extends BaseController {
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
    }

    // 获取用户信息
   public function show_mes(){
    $user_id = I('post.user_id');
    $token = I("post.token");
    $data = M("Users")->where("user_id=".$user_id)->find();
    // $data = M("Users")->where("user_id=".$user_id." and "."token="."'".$token."'")->find();
    exit(json_encode(array('status'=>1,'msg'=>'获取成功','result'=>$data)));
   }
    
   
   // 获取用户收货地址信息
   public function address_list(){
    $user_id = I('post.user_id');
    $token = I('post.token');
    $user = M("Users")->where(array('user_id'=>$user_id,'token'=>$token))->find();
    if(!empty($user)){
        $address = M('user_address')->where(array('user_id'=>$user_id,'token'=>$token))->select();
    }else{
        exit(json_encode(array('status'=>-1,'msg'=>'缺少参数','result'=>'')));
    }
    if(!$address)
        exit(json_encode(array('status'=>1,'msg'=>'没有数据','result'=>'')));
    exit(json_encode(array('status'=>1,'msg'=>'获取成功','result'=>$address)));

   }

   /*
     * 添加地址
     */
    public function add_address(){
        header("Content-type:text/html;charset=utf-8");
        if(I("post.type ") == 1){
            $logic = new UsersLogic();
            $user_id = I("post.user_id");
            $token = I('post.token');
            $user = M("Users")->where(array('user_id'=>$user_id,'token'=>$token))->find();
            if(!empty($user)){
                // print_r("expression");die();
                $data = $logic->add_address($id,0,I('post.'));
                // $data = M("user_address")->where('user_id='.$id)->add(I('post.'));
            }else{
                $data = $logic->add_address($this->user_id,0,I('post.'));
            }
            // $data = $logic->add_address($this->user_id,0,I('post.'));
            if($data['status'] != 1)
                exit(json_encode(array('status'=>1,'msg'=>'获取成功','result'=>$data)));
                exit('<script>alert("'.$data['msg'].'");history.go(-1);</script>');
            $call_back = $_REQUEST['call_back'];
            exit(json_encode(array('status'=>1,'msg'=>'添加成功','result'=>$data)));
        }
        
    }

    /*
     * 地址编辑
     */
    public function edit_address(){
        // print_r("expression");
        $uid = I('post.uid');
        $id = I('post.id');
        $address = M('user_address')->where(array('address_id'=>$id,'user_id'=> $uid))->find();
        $datas = I("post.");
        if(IS_POST){
            $logic = new UsersLogic();
            $data = M('user_address')->where('address_id='.$id)->save($datas);
            // $sql = M('user_address')->getLastSql();
            // echo $sql;

            if($data['status'] != 1)
                exit('<script>alert("'.$data['msg'].'");history.go(-1);</script>');

            $call_back = $_REQUEST['call_back'];
            echo "<script>parent.{$call_back}('success');</script>";
            exit(); // 成功 回调closeWindow方法 并返回新增的id
        }
        //获取省份
        $p = M('region')->where(array('parent_id'=>0,'level'=> 1))->select();
        $c = M('region')->where(array('parent_id'=>$address['province'],'level'=> 2))->select();
        $d = M('region')->where(array('parent_id'=>$address['city'],'level'=> 3))->select();
        if($address['twon']){
            $e = M('region')->where(array('parent_id'=>$address['district'],'level'=>4))->select();
            $this->assign('twon',$e);
        }

        
        exit(json_encode(array('status'=>1,'msg'=>'修改成功','result'=>$address)));
    }
    

    /*
     * 设置默认收货地址
     */
    public function set_default(){
        $user_id = I('post.user_id');
        $id = I('post.id');
        $token = I('post.token');
        $user = M("Users")->where(array('user_id'=>$user_id,'token'=>$token))->find();
        if(!empty($user)){
            $rows = M('user_address')->where(array('user_id'=>$user_id))->save(array('is_default'=>0));
            $row = M('user_address')->where(array('user_id'=>$user_id,'address_id'=>$id))->save(array('is_default'=>1));
        }
        if($row)
        echo (json_encode(array('status'=>1,'msg'=>'修改成功','result'=>"succeed"))); 
    }
    
    /**
     * 收藏商品
     */
    function collectGoods(){
        //$user_id = I('user_id');
        $goods_id = I('goods_id');
        $user_id = I('user_id');
        $type = I('type');
        $count = M('Goods')->where("goods_id = $goods_id")->count();
        if($count == 0)  exit(json_encode(array('status'=>1,'msg'=>'收藏商品不存在','result'=>array())));
        //删除收藏商品
        if($type==1){
            M('GoodsCollect')->where("user_id = ".I('user_id')." AND goods_id = $goods_id")->delete();
            exit(json_encode(array('status'=>1,'msg'=>'已取消','result'=>array() )));
        }
        $count = M('GoodsCollect')->where("user_id = ".I('user_id')." AND goods_id = $goods_id")->count();
        if($count > 0)        exit(json_encode(array('status'=>1,'msg'=>'已收藏','result'=>array() )));
        M('GoodsCollect')->add(array(
            'goods_id'=>$goods_id,
            'user_id'=>$user_id,
            'add_time'=>time(),
        ));
        exit(json_encode(array('status'=>1,'msg'=>'收藏成功','result'=>array() )));
    }

    /*
     * 获取商品收藏列表
     */
    public function getGoodsCollect(){
       $user_id = I('user_id',0);
        //if(!$this->user_id) exit(json_encode(array('status'=>-1,'msg'=>'缺少参数','result'=>'')));
        $data = $this->userLogic->get_goods_collect($user_id);
        foreach($data['result'] as &$r){

        }
        unset($data['show']);
        exit(json_encode($data));
    }


    // U币交易记录
    public function ubi_show()
    {
        $id = I("post.id");
        $ubi = M('account_log')->where(array('user_id'=>$id))->order("change_time desc")->select();
        exit(json_encode(array('status'=>1,'msg'=>'获取成功','result'=>$ubi))); 
    }

    // U币转让
    public function ubi_sell(){
        if ($_POST) {
            $data = $_POST; 
            $res = M("ubi_jiaoyi")->add($data);
            exit(json_encode(array('status'=>1,'msg'=>'发布成功'))); 
        }
    }

    // U币转让列表
    public function ubi_lists(){
        // $id = I("post.id");
        $ubi = M('ubi_jiaoyi')->order("last_time desc")->select();
        exit(json_encode(array('status'=>1,'msg'=>'获取成功','result'=>$ubi))); 
    }


    //微信授权获取用户
    public function wxempower(){
        $code = I("post.code");
        $appid = "wx8d8b9951c3e6f31d";  
        $secret = "cae1b5d734ae9e013c7764eae67b3124";  
        // $code = I("post.code");  
        $get_token_url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$appid.'&secret='.$secret.'&code='.$code.'&grant_type=authorization_code';  

        $json_obj =$this-> getJson($get_token_url);
        //根据openid和access_token查询用户信息  
        $access_token = $json_obj['access_token'];  
        $openid = $json_obj['openid'];  
        $refresh_token = $json_obj['refresh_token'];
        //重新获取access_token
        $acc_tok = 'https://api.weixin.qq.com/sns/oauth2/refresh_token?appid='.$appid.'&grant_type=refresh_token&refresh_token='.$refresh_token.'';
        $acc_toks =$this-> getJson($acc_tok);
        $access_token = $acc_toks['access_token'];  

        $get_user_info_url = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$access_token.'&openid='.$openid.'&lang=zh_CN';  

        $user_obj = $this-> getJson($get_user_info_url);
         
        echo json_encode($user_obj);  
        

    }

    function getJson($url){
        $ch = curl_init();  
        curl_setopt($ch,CURLOPT_URL,$url);  
        curl_setopt($ch,CURLOPT_HEADER,0);  
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );  
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);  
        $res = curl_exec($ch); 
        // print_r($res);die(); 
        curl_close($ch); 
        return json_decode($res, true); 
    }


    //私人定制
    public function customs()
    {
        $data = I("post.");
        $customs = M('customs')->add($data);
        $referurl = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : U('Home/index/index');
        if($customs){
            $this->success("操作成功",$referurl);
        }else{
            $this->error("操作失败",$referurl);
        }
    }


    //修改用户信息
    public function edit_user()
    {
        
    }
}