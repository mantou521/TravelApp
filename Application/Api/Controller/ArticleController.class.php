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
class ArticleController extends BaseController {
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

    /*
     * 获取UU头条
     */
    public function title_list(){
        $num = I("post.num");
        $Page  = new AjaxPage($count,$num);
        $show = $Page->show();
        if(!empty(I("post.article_id"))){
            $data = M('article')->where('article_id='.I("post.article_id"))->find();
        }else{
            $data = M('article')->where('cat_id=1')->order("add_time desc")->limit($Page->firstRow.','.$Page->listRows)->select();
            $total = M('article')->where('cat_id=1')->count('article_id');
            $pages = $total/$num;
        }
        // $data['content'] = "<html><head><title></title></head><body>".$data['content']."</body></html>";
        $json_arr = array('status'=>1,'msg'=>'成功','result'=>$data,'total'=>$total,'pages'=>$pages );
        $replace = array('src=&quot;'.SITE_URL.'\/Public','"'.SITE_URL.'\/Public');
        $search = array('src=&quot;\/Public','"\/Public');
        $json_str = json_encode($json_arr,TRUE); 
        $sea_str = array("&lt;","&gt;");
        $rep_str = array("<",">");
        $json_str = str_replace($search,$replace,$json_str);
        $json_str = str_replace($sea_str,$rep_str,$json_str); 
        $json_str = str_replace("&quot;","'",$json_str);        
        exit($json_str);
        
    }

    /*
     * 获取换购说明
     */
    public function explain_list(){
        $data = M('article')->where('cat_id=2')->order("add_time desc")->select();
        $json_arr = array('status'=>1,'msg'=>'获取成功','list'=>$data,);
        $this->replace($json_arr); 
        
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

    // U旅游旅游线路
    public function lv_lines()
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



    //路径替换
    public function replace($json_arr){
        $replace = array('src=&quot;'.SITE_URL.'\/Public','"'.SITE_URL.'\/Public');
        $search = array('src=&quot;\/Public','"\/Public');
        $json_str = json_encode($json_arr); 
        $sea_str = array("&lt;","&gt;","<img");
        $rep_str = array("<",">","<img");
        $json_str = str_replace($search,$replace,$json_str);
        $json_str = str_replace($sea_str,$rep_str,$json_str); 
        $json_str = str_replace("&quot;","'",$json_str);         
        exit($json_str);
    }

}