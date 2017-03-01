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
class PlanController extends BaseController {
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

    public function index(){
        // banner
        $banner = M("banners")->where("local=8")->getField("banners_id");
        $b_id = $banner;
        $banimg = M("Banners_images")->where('banners_id='.$b_id)->order("banners_id desc")->select();
        foreach ($banimg as $bi => $bis) {
            $banimgs[] = $bis['image_url'];
        }
        $banners['banners_id'] = $banner;
        $banners['images'] = $banimgs;
        // U旅游-关于我们
        $abouts = M("about")->where("classify='about_paln'")->find();
        $about['about_id'] = $abouts['about_id'];
        $about['description'] = $abouts['description'];
        // 策划实战
        $plans = M("Planning")->order("id desc")->limit(3)->select();
        // 专家团队
        $experts = M("expert")->order('id asc')->limit(4)->select();

        $result['banner'] = $banners;
        $result['about'] = $about;
        $result['plans'] = $plans;
        $result['experts'] = $experts;
        $json_arr = array('status'=>1,'msg'=>'获取成功','result'=>$result);
        $this->replace($json_arr); 
    }

    // 策划实战
    public function planning(){
        if (!empty(I("id"))) {
            $plans = M("Planning")->where("id=".I("id"))->find();
        }else{
            $plans = M("Planning")->select();
        }
        
        $result['plan'] = $plans;
        $json_arr = array('status'=>1,'msg'=>'获取成功','result'=>$result);
        $this->replace($json_arr); 
    }

    // U旅游-关于我们
    public function qh_contact(){
        $about = M("about")->where("classify='about_paln'")->find();
        $json_arr = array('status'=>1,'msg'=>'获取成功','pages'=>$pages,'list'=>$about,);
        $this->replace($json_arr); 
    }

    // U旅游-联系我们
    public function qh_lianxi(){
        $about = M("about")->where("classify='lianxi_paln'")->find();
        $json_arr = array('status'=>1,'msg'=>'获取成功','pages'=>$pages,'list'=>$about,);
        $this->replace($json_arr); 
    }

    // 专家团队列表
    public function expert(){
        if(!empty(I("id"))){
            $expert = M("expert")->where("id=".I("id"))->find();
        }else{
            $expert = M("expert")->select();
        }

        $result['expert'] = $expert;
        $json_arr = array('status'=>1,'msg'=>'获取成功','result'=>$result);
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