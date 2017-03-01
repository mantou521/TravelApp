<?php

namespace Admin\Controller;
use Think\AjaxPage;
use Think\Page;

class GuestController extends BaseController {

    //联系我们
	function ck_contact(){
        if(IS_POST){
            $data = I('post.');
            $data['add_time'] = strtotime(I("add_time"));
            $data['publish_time'] = strtotime(I("add_time"));
            $travel = M("about")->where('classify="ck_contact"')->find();
            if (!empty($travel)) {
                $r = D('about')->where('classify="ck_contact"')->save($data);
            }else{
                $data['classify'] = "ck_contact";
                $r = D('about')->add($data);
            }
            $referurl = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : U('Admin/ck_contact');
            if($r){
                $this->success("操作成功",$referurl);
            }else{
                $this->error("操作失败",$referurl);
            }
        }else{
            $about = M("about")->where('classify="ck_contact"')->find();
            $this->assign('info',$about);
            $this->initEditor();
            $this->display();
        }
    }

    /**
     *  动态列表
     */
    public function cknewsList(){             
        $news = M("Ck_news")->select();
        $this->assign('cknewsList',$news);
        $this->display();   
                                          
    }
    
    /**
     *  搜索新闻列表
     */
    public function ajaxCknewsList(){       
        $keyword = I('post.key_word');
        $where = "keywords like '%$keyword%' or title like '%$keyword%'" ;
        $model = M('Ck_news');
        $count = $model->where($where)->count();
        $Page  = new AjaxPage($count,10);
        $show = $Page->show();
        $cknewsList = $model->where($where)->limit($Page->firstRow.','.$Page->listRows)->select();
        
        $this->assign('cknewsList',$cknewsList);
        $this->assign('page',$show);// 赋值分页输出
        $this->display();         
    }    
    
    public function cknews(){
        $act = I('GET.act','add');
        // print_r($act);die();
        if($act == 'edit'){
            $cats = M('Ck_news')->where('nid='.I('get.nid'))->find();
            $info = array();
            if(I('GET.nid')){
               $nid = I('GET.nid');
               $info = D('ck_news')->where('nid='.$nid)->find();
            }
        }
        if($act == 'del'){
            $cats = M('Ck_news')->where('nid='.I('get.nid'))->delete();
            $referurl = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : U('Admin/Guest/cknewsList');
            if($cats){
                $this->success("操作成功",$referurl);
                exit();
            }else{
                $this->error("操作失败",$referurl);
            }
        }
        $this->assign('act',$act);
        $this->assign('info',$info);
        $this->initEditor();
        $this->display();
    }
    

    /**
     * 初始化编辑器链接
     * @param $post_id post_id
     */
    private function initEditor()
    {
        $this->assign("URL_upload", U('Admin/Ueditor/imageUp',array('savepath'=>'news')));
        $this->assign("URL_fileUp", U('Admin/Ueditor/fileUp',array('savepath'=>'news')));
        $this->assign("URL_scrawlUp", U('Admin/Ueditor/scrawlUp',array('savepath'=>'news')));
        $this->assign("URL_getRemoteImage", U('Admin/Ueditor/getRemoteImage',array('savepath'=>'news')));
        $this->assign("URL_imageManager", U('Admin/Ueditor/imageManager',array('savepath'=>'news')));
        $this->assign("URL_imageUp", U('Admin/Ueditor/imageUp',array('savepath'=>'news')));
        $this->assign("URL_getMovie", U('Admin/Ueditor/getMovie',array('savepath'=>'news')));
        $this->assign("URL_Home", "");
    }
    

    // 添加创业新闻
    public function cknewsHandle(){
        $data = I('post.');
        if (I("author") == "") {
            $admin_id = $_SESSION['admin_id'];
            $author = M("admin")->where("admin_id=".$admin_id)->find();
            $data['author'] = $author['user_name'];
        }
        if($data['act'] == 'add'){
            $data['click'] = mt_rand(1000,1300);
            if (empty($data['add_time'])) {
                $data['add_time'] = time(); 
            }else{
                $data['add_time'] = strtotime($data['add_time']); 
            }
            
            $r = D('ck_news')->add($data);
        }
        
        if($data['act'] == 'edit'){
            if (empty($data['add_time'])) {
                $data['add_time'] = time(); 
            }else{
                $data['add_time'] = strtotime($data['add_time']); 
            }
            $r = D('ck_news')->where('nid='.$data['nid'])->save($data);
        }
        
        if($data['act'] == 'del'){
            $r = D('ck_news')->where('nid='.$data['nid'])->delete();
            if($r) exit(json_encode(1));        
        }
        $referurl = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : U('Admin/Guest/cknewsList');
        if($r){
            $this->success("操作成功",$referurl);
        }else{
            $this->error("操作失败",$referurl);
        }
    }
      
}