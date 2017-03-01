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
use Think\AjaxPage;
use Think\Page;
class TourismController extends BaseController {
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

   
    // U旅游新闻动态列表/详情
    public function lv_newslist()
    {
        $num = I("post.num");
        $Page  = new AjaxPage($count,$num);
        $show = $Page->show();
        if(!empty(I("post.news_id"))){
            $data = M('news')->where('news_id='.I("post.news_id"))->find();
            $json_arr = array('status'=>1,'msg'=>'获取成功','list'=>$data,);
        }else{
            $data = M('news')->order("add_time desc")->limit($Page->firstRow.','.$Page->listRows)->select();
            $total = M('news')->count('news_id');
            $pages = $total/$num;
            if($pages == false){
                $pages = "";
            }
            $json_arr = array('status'=>1,'msg'=>'获取成功','pages'=>$pages,'list'=>$data,);
        }
        $this->replace($json_arr); 
    }

    // U旅游线路
    public function lv_lines()
    {
        $num = I("post.num");
        $Page  = new AjaxPage($count,$num);
        $show = $Page->show();
        if(!empty(I("post.lines_id"))){
            $lines = M('lines')->where('lines_id='.I("post.lines_id"))->find();
            $city = M("line_address")->where("laid=".$lines['city'])->find();
            $lines['city_name'] = $city['hot'];
            $json_arr = array('status'=>1,'msg'=>'获取成功','list'=>$lines,);
        }else{

            $citys = M("line_address")->select();
            if(empty(I("city_id"))){
                $where = "city = 1 OR city = 2 OR city = 3 OR city = 4 OR city = 5 OR city = 6 OR city = 7 OR city = 8 OR city = 9";
                $where1 = "city = 10 OR city = 11 OR city = 12 OR city = 13 OR city = 14 OR city = 15 OR city = 16 OR city = 17 OR city = 18";
                $cn_lines = M('lines')->where($where)->order("add_time desc")->limit($Page->firstRow.','.$Page->listRows)->select();
                foreach ($cn_lines as $cn => $cnl) {
                    $city = M("line_address")->where("laid=".$cnl['city'])->find();
                    $cn_lines[$cn]['city_name'] = $city['hot'];
                }
                $gj_lines = M('lines')->where($where1)->order("add_time desc")->limit($Page->firstRow.','.$Page->listRows)->select();
                foreach ($gj_lines as $gj => $gjl) {
                    $city = M("line_address")->where("laid=".$gjl['city'])->find();
                    $gj_lines[$gj]['city_name'] = $city['hot'];
                }
                $total = M('lines')->count('lines_id');
                $pages = $total/$num;
                if($pages == false){
                    $pages = "";
                }
                $lines['cn_lines'] = $cn_lines;
                $lines['gj_lines'] = $gj_lines;
            }else{
                $lines = M('lines')->where("city=".I("city_id"))->order("add_time desc")->limit($Page->firstRow.','.$Page->listRows)->select();
                foreach ($lines as $cn => $cnl) {
                    $city = M("line_address")->where("laid=".$cnl['city'])->find();
                    $lines[$cn]['city_name'] = $city['hot'];
                }
            }
            $json_arr = array('status'=>1,'msg'=>'获取成功','pages'=>$pages,'result'=>$lines,'city'=>$citys);
        }
        $this->replace($json_arr); 
    }
    // 旅游路线搜索提示
    public function thisearch()
    {
        $q = I("q");
        $list = M('line_address')->where("hot like '%{$q}%'  ")->find();
        $json_arr = array('status'=>1,'msg'=>'获取成功','result'=>$list );
        $json_str = json_encode($json_arr,TRUE); 
        exit($json_str);   
    }
    // 私人定制
    public function customs(){
        $data = I("post.");
        $customs = M("customs")->add($data);
        if($customs){
            $json_arr = array('status'=>1,'msg'=>'操作成功','result'=>array() );
            $json_str = json_encode($json_arr,TRUE); 
            exit($json_str);   
        }else{
            $json_arr = array('status'=>0,'msg'=>'操作失败','result'=>array() );
            $json_str = json_encode($json_arr,TRUE); 
            exit($json_str);   
        }
    }

    // U旅游-关于我们
    public function lv_about(){
        $about = M("about")->where("classify='about_travel'")->find();
        $json_arr = array('status'=>1,'msg'=>'获取成功','pages'=>$pages,'list'=>$about,);
        $this->replace($json_arr); 
    }

    // U旅游-限时优惠
    public function benefit(){
        $num = I("post.num");
        $Page  = new AjaxPage($count,$num);
        $show = $Page->show();
        if(!empty(I("post.benefits_id"))){
            $data = M('benefits')->where('benefits_id='.I("post.benefits_id"))->find();
            $json_arr = array('status'=>1,'msg'=>'获取成功','list'=>$data);
        }else{
            $data = M('benefits')->order("add_time desc")->limit($Page->firstRow.','.$Page->listRows)->select();
            $total = M('benefits')->count('benefits_id');
            $pages = $total/$num;
            if($pages == false){
                $pages = "";
            }
            $json_arr = array('status'=>1,'msg'=>'获取成功','pages'=>$pages,'list'=>$data,);
        }
        $this->replace($json_arr); 
    }

    // U旅游-关于我们
    public function lv_contact(){
        $about = M("about")->where("classify='contact_us'")->find();
        $json_arr = array('status'=>1,'msg'=>'获取成功','pages'=>$pages,'list'=>$about,);
        $this->replace($json_arr); 
    }
    // 添加结伴拼游
    public function add_tgter(){
        $user_id = I("user_id");
        $token = I('token');
        if($token == '')
            exit(json_encode(array('status'=>0,'msg'=>'Token不能为空','result'=>"Token不能为空")));
        $user = M("Users")->where('user_id='.$user_id)->getField("sex,nickname,head_pic");
        foreach ($user as $u => $us) {
            $sex = $us['sex'];
            $nickname = $us['nickname'];
            $head_pic = $us['head_pic'];
        }
        if(I("type") == 1){
            $data = I("post.");
            $data['add_time'] = time();
            $data['togethers_name'] = $nickname;
            $data['togethers_content'] = I("content");
            $data['address'] = I("address");
            $data['expense'] = I("expense");
            $data['want'] = I("want");
            $data['apart'] = I("apart");
            $data['start'] = I("start");

            $base64 = $_POST['images'];
            $base64 = explode("(", $base64);
            $base64 = explode(")", $base64[1]);
            $base64 = explode(",", $base64[0]);
            $res = M("togethers")->add($data);
            foreach ($base64 as $b => $val) {
                $base64_image = str_replace(' ', '+', $val);
                //post的数据里面，加号会被替换为空格，需要重新替换回来，如果不是post的数据，则注释掉这一行
                $image_name = time().rand(10000,99999).'.png';
                $pa = "togethers";
                $time = date("Y-m-d");
                $t = explode("-",$time);
                $path='./Public/upload/'.$pa.'/'.$t[0].'/'.$t[1]."-".$t[2];
                $this->path($path);
                $new_path = 'Public/upload/'.$pa.'/'.$t[0].'/'.$t[1]."-".$t[2];
                $image_file = $new_path."/".$image_name;
                // echo $image_file;
                //服务器文件存储路径
                $result = file_put_contents($image_file, base64_decode($base64_image));

                $images[] = SITE_URL.'/'.$image_file;
            }
            $lastid = M("togethers")->where('user_id='.I("user_id"))->order("togethers_id desc")->find();
            foreach ($images as $i => $is) {
                $imgs['image_url'] = $is;
                $imgs['togethers_id'] = $lastid['togethers_id'];
                $im = M("togethers_images")->add($imgs);
            }
            if ($res) {
                $json_arr = array('status'=>1,'msg'=>'发布成功','result'=>array());
                $this->replace($json_arr); 
            }
        }

    }

    // 结伴拼游列表
    public function tgter_list(){    
        if(I("type") == 1){
            $data = I("post.");
            $user = M("Users")->where("user_id=".I("user_id"))->find();
            $data['head_pic'] = $user['head_pic'];
            $data['nickname'] = $user['nickname'];
            $data['add_time'] = time();
            $data['to_user_id'] = I("u_id");
            $tog_data = M("togethers_data")->add($data);
            // print_r($data);
            // die();
        }elseif(I("type") == 2){
            $togethers_id = I("togethers_id");
            $user_id = I("user_id");
            $to_click = M("togethers_click")->where("togethers_id=".$togethers_id." AND user_id=".$user_id)->find();
            if(I("click") == 1){
                if($to_click){
                    exit(json_encode(array('status'=>0,'msg'=>'已点赞了','result'=>array())));
                }else{
                    $toges = M("togethers")->where("togethers_id=".I("togethers_id"))->find();
                    $data['click_count'] = ++$toges['click_count'];
                    $together = M("togethers")->where("togethers_id=".I("togethers_id"))->save($data);
                    $cls["togethers_id"] = $togethers_id;
                    $cls['user_id'] = $user_id;
                    $toge_click = M("togethers_click")->add($cls);
                }
            }elseif(I("click") == 0){
                $toges = M("togethers")->where("togethers_id=".I("togethers_id"))->find();
                $data['click_count'] = --$toges['click_count'];
                $together = M("togethers")->where("togethers_id=".I("togethers_id"))->save($data);
                $toge_click = M("togethers_click")->where("togethers_id=".I("togethers_id")." AND user_id=".$user_id)->delete();
            }
            
        }
        $lists = M("togethers")->select();
        foreach ($lists as $l => $lts) {
            $click = M("togethers_click")->where("togethers_id=".$lts['togethers_id']." AND user_id=".I("user_id"))->find();
            if(!empty($click)){
                $lists[$l]['click_ed'] = 1;
            }else{
                $lists[$l]['click_ed'] = 0;
            }
            $list_imgs = M("togethers_images")->where("togethers_id=".$lts['togethers_id'])->select();
            foreach ($list_imgs as $li => $lis) {
                $image_url[$l][] = $lis['image_url'];
                $lists[$l]['image_url'] = $image_url[$l];
            }
            $tog_data = M("togethers_data")->where("togethers_id=".$lts['togethers_id'])->order("ask_id asc,add_time asc")->select();
            $tog_data = $this->tree($tog_data);
            $user = M("Users")->where("user_id=".$lts['user_id'])->find();
            $lists[$l]['head_pic'] = $user['head_pic'];
            $lists[$l]['nickname'] = $user['nickname'];
            $lists[$l]['sex'] = $user['sex'];

            foreach ($tog_data as $td => $tds) {
                if($lts['togethers_id'] == $tds['togethers_id']){
                    // print_r($tds);
                    $lists[$l]['tog_data'][] = $tds;
                }
            }
        }
        $json_arr = array('status'=>1,'msg'=>'获取成功','result'=>$lists);
        $this->replace($json_arr); 
    }

    // 收藏列表
    public function collect_list(){
        $news = M("tourism_collect")->where("user_id=".I("user_id")." AND shop = 1")->select();
        $lines = M("tourism_collect")->where("user_id=".I("user_id")." AND shop = 2")->select();
        $travels = M("tourism_collect")->where("user_id=".I("user_id")." AND shop = 3")->select();
        $result['news'] = $news;
        $result['lines'] = $lines;
        $result['travels'] = $travels;
        $json_arr = array('status'=>1,'msg'=>'获取成功','result'=>$result);
        $this->replace($json_arr); 
    }

    // 收藏
    public function collect(){
        $token = I('token');
        if($token == '')
            exit(json_encode(array('status'=>0,'msg'=>'Token不能为空','result'=>"Token不能为空")));
        if(I("shop") == 1){
            $res = M("News")->where("news_id=".I("id"))->find();
        }elseif (I("shop") == 2) {
            $res = M("lines")->where("lines_id=".I("id"))->find();
        }elseif (I("shop") == 3) {
            // $res = M("lines")->where("lines_id=".I("id"))->find();
        }
        $data['shop'] = I("shop");
        $data['user_id'] = I("user_id");
        $data['catid'] = I("id");
        $data['title'] = $res['title'];
        $data['image'] = $res['thumb'];
        $data['keywords'] = $res['keywords'];
        $data['add_time'] = time();
        $collect = M("tourism_collect")->add($data);
        $json_arr = array('status'=>1,'msg'=>'收藏成功','result'=>$data);
        $this->replace($json_arr); 
    }

    // 环球游记
    public function travels(){
        $token = I('token');
        if($token == '')
            exit(json_encode(array('status'=>0,'msg'=>'Token不能为空','result'=>"Token不能为空")));
        $user = M("users")->where("user_id=".I("user_id"))->find();
        $data = I("post.");
        $data['head_pic'] = $user['head_pic'];
        $data['nickname'] = $user['nickname'];
        $data['user_id'] = I("user_id");

        $base64 = $_POST['image'];
        $base64 = explode("(", $base64);
        $base64 = explode(")", $base64[1]);
        $base64 = explode(",", $base64[0]);
        // $res = M("togethers")->add($data);
        foreach ($base64 as $b => $val) {
            $base64_image = str_replace(' ', '+', $val);
            //post的数据里面，加号会被替换为空格，需要重新替换回来，如果不是post的数据，则注释掉这一行
            $image_name = time().rand(10000,99999).'.png';
            $pa = "travels";
            $time = date("Y-m-d");
            $t = explode("-",$time);
            $path='./Public/upload/'.$pa.'/'.$t[0].'/'.$t[1]."-".$t[2];
            $this->path($path);
            $new_path = 'Public/upload/'.$pa.'/'.$t[0].'/'.$t[1]."-".$t[2];
            $image_file = $new_path."/".$image_name;
            // echo $image_file;
            //服务器文件存储路径
            $result = file_put_contents($image_file, base64_decode($base64_image));

            $images[] = SITE_URL.'/'.$image_file;
        }
        $data['image'] = serialize($images);
        $res = M("travels")->add($data);
        if ($res){
            $json_arr = array('status'=>1,'msg'=>'获取成功','result'=>array());
        }else{
            exit(json_encode(array('status'=>0,'msg'=>'发布失败','result'=>array())));
        }
        $this->replace($json_arr); 
    }

    // 环球游记列表
    public function travels_list(){
        $travels = M("travels")->select();
        foreach ($travels as $key => $value) {
            $travels[$key]['image'] = unserialize($value['image']);
        }
        $json_arr = array('status'=>1,'msg'=>'收藏成功','result'=>$travels);
        $this->replace($json_arr); 
    }

    // 环球游记详情
    public function travels_show(){
        $travels = M("travels")->where("id=".I("id"))->find();
        if(I("click") == "1"){
            $data['click'] = ++$travels['click'];
            $tr = M("travels")->where("id=".I("id"))->save($data);
            $click['user_id'] = I("user_id");
            $click['travels_id'] =I("id");
            $travels_click = M("travels_click")->add($click);
        }elseif(I("click") == "0") {
            $data['click'] = --$travels['click'];
            $tr = M("travels")->where("id=".I("id"))->save($data);
            $travels_click = M("travels_click")->where("travels_id=".I("id")." AND user_id=".I("user_id"))->delete();
        }
        if(I("type") == 1){
            $data = I("post.");
            $user = M("Users")->where("user_id=".I("user_id"))->find();
            $data['head_pic'] = $user['head_pic'];
            $data['nickname'] = $user['nickname'];
            $data['add_time'] = time();
            $data['to_user_id'] = I("u_id");
            $data['travels_id'] = I("id");
            $tog_data = M("travels_data")->add($data);
        }
        $travels = M("travels")->where("id=".I("id"))->find();
        $travels_click = M("travels_click")->where("travels_id=".I("id")." AND user_id=".I("user_id"))->find();
        $travels_data = M("travels_data")->where("travels_id=".I('id'))->order("ask_id asc,add_time asc")->select();
            $travels_data = $this->tree($travels_data);
        if ($travels_click) {
            $travels['clicked'] = 1;
        }else{
            $travels['clicked'] = 0;
        }
        $travels['image'] = unserialize($travels['image']);

        $result['travels'] = $travels;
        $result['travels_data'] = $travels_data;
        $json_arr = array('status'=>1,'msg'=>'成功','result'=>$result);
        $this->replace($json_arr); 
    }

    // 环球游记搜索
    public function sear_travels(){
        $q = I("q");
        $list = M('travels')->where("title like '%{$q}%'  ")->find();
        $json_arr = array('status'=>1,'msg'=>'获取成功','result'=>$list );
        $json_str = json_encode($json_arr,TRUE); 
        exit($json_str); 
    }


    // 购买订单
    public function lv_order(){
        $shop = I("shop");
        $id = I("id");
        // 旅游路线
        if ($shop == 3) {
            $lines = M("lines")->where("lines_id=".I("id"))->find();
            $line['id'] = $lines['lines_id'];
            $line['title'] = $lines['title'];
            $line['description'] = $lines['description'];
            $line['add_time'] = $lines['add_time'];
            $line['end_time'] = $lines['end_time'];
            $line['price'] = $lines['price'];
            $line['child_price'] = $lines['child_price'];
            $line['dif_price'] = $lines['dif_price'];
            $line['tel'] = $lines['tel'];
        }elseif ($shop == 4) {
            $lines = M("benefits")->where("benefits_id=".I("id"))->find();
            $line['id'] = $lines['benefits_id'];
            $line['title'] = $lines['title'];
            $line['description'] = $lines['description'];
            $line['add_time'] = $lines['add_time'];
            $line['end_time'] = $lines['end_time'];
            $line['price'] = $lines['price'];
            $line['child_price'] = $lines['child_price'];
            $line['dif_price'] = $lines['dif_price'];
            $line['tel'] = $lines['tel'];
            
        }
        // 提交订单
        if(I("type") == 1){
            $safety = M("safety")->where("id=".I("safety_id"))->find();
            $line['start_time'] = I("start_time");
            // $person['cards'] = I("cards");
            // $person['tel'] = I("tel");
            $usernames = array(
                    "0"=>"张","1"=>"李",
                );
            $cards = array(
                    "0"=>"130625178965471236","1"=>"130625178965471226",
                );
            $tels = array(
                    "0"=>"13800138000","1"=>"13800138001",
                );
            foreach ($cards as $us => $name) {
                foreach ($tels as $t => $tel) {
                    foreach ($usernames as $un => $name) {
                        $person['username'] = $usernames[$us];
                        $person['card'] = $cards[$us];
                        $person['tel'] = $tels[$us];
                        $persons[] = $person;
                        break;
                    }
                    break;
                }
            }
            // $user = M("users")->where("user_id=".I("user_id"))->find();
            // $users['nickname'] = $user['nickname'];
            // $data['user'] = $users;
            $data['order_sn'] = date('YmdHis').rand(1000,9999);
            $data['user_id'] = I("user_id");
            $data['adult'] = I("adult");
            $data['child'] = I("child");
            $data['shop'] = I("shop");
            $data['message'] = serialize($persons);
            // $data['message'] = $persons;
            $data['difference'] = I("difference");
            $data['add_time'] = time();
            $data['start_time'] = I("start_time");
            $data['safety_id'] = I("safety_id");
            $data['price'] = I("start_price");
            $data['kefu'] = $line['tel'];
            //总价=出行日期路价*成人数+儿童价*儿童数+房间差数*房间差价+（成人数+儿童数）*保险价格

            $data['total_amount'] = I("start_price") * I("adult") + (I("child_price") * I("child")) + I("difference") * $line['dif_price'] + (I("adult") + I("child")) * $safety['price'];
            $data['order_amount'] = $data['total_amount'];
            $res = M("lv_order")->add($data);
        }else{
            // 保险信息
            $safety = M("safety")->select();
            $line['start_tp'] = unserialize($lines['start_tp']);
        }
        
        $result['line'] = $line;
        $result['safety'] = $safety;
        $data['message'] = unserialize($data['message']);
        $result['data'] = $data;
        // $result['persons'] = $persons;
        $json_arr = array('status'=>1,'msg'=>'成功','result'=>$result);
        $this->replace($json_arr); 
    }


    // 路径
    public function path($path){
        $new_path=str_replace('\\', '/', $path);
        $name=time().$file['name'][$k];
        $tmp_name=$file['tmp_name'][$k];
        //当没有文件夹的时候就创建一个文件夹
        $path = $this->creatfile($new_path);
        return $path;
    }

    //递归创建文件夹
    public function creatfile($path){
      if (!file_exists($path))
      {
       $this->creatfile(dirname($path));
       mkdir($path, 0777);
      }
    }

    //路径替换
    public function replace($json_arr){
        $replace = array('src=&quot;'.SITE_URL.'\/Public','"'.SITE_URL.'\/Public');
        $search = array('src=&quot;\/Public','"\/Public');
        $json_str = json_encode($json_arr); 
        $sea_str = array("&lt;","&gt;","<img");
        $rep_str = array("<",">","<img ");
        $json_str = str_replace($search,$replace,$json_str);
        $json_str = str_replace($sea_str,$rep_str,$json_str); 
        $json_str = str_replace("&quot;","'",$json_str);         
        exit($json_str);
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

}