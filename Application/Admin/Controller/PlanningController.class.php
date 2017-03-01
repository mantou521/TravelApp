<?php

namespace Admin\Controller;
use Think\AjaxPage;
use Think\Page;

class PlanningController extends BaseController {

    
    // UU企划--关于
    function article(){
        if(IS_POST){
            $data = I('post.');
            $data['add_time'] = strtotime(I("publish_time"));
            $data['publish_time'] = strtotime(I("publish_time"));
            $travel = M("about")->where('classify="about_paln"')->find();
            if (!empty($travel)) {
                $r = D('about')->where('classify="about_paln"')->save($data);
            }else{
                $data['classify'] = "about_paln";
                $r = D('about')->add($data);
            }
            $referurl = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : U('Admin/Planning');
            if($r){
                $this->success("操作成功",$referurl);
            }else{
                $this->error("操作失败",$referurl);
            }
        }else{
            $about = M("about")->where('classify="about_paln"')->find();
            $this->assign('info',$about);
            $this->initEditor();
            $this->display();
        }
    }

     // U企划简介
    public function introductions(){
        if(IS_POST){
            $data = I('post.');
            $data['type'] = 5;
            $result = M("introductions")->where("type=5")->find();
            if(empty($result)){
                $result = M("introductions")->add($data);
            }else{
                $result = M("introductions")->where("type=5")->save($data);
            }
            $referurl = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : U('Admin/Goods');
            if($result){
                $this->success("操作成功",$referurl);
            }else{
                $this->error("操作失败",$referurl);
            }
        }
        $introductions = M("introductions")->where("type=5")->find();
        $this->assign("info",$introductions);
        $this->initEditor(); // 编辑器
        $this->display();
        
    }


    // UU企划--联系
    function lianxi(){
        if(IS_POST){
            $data = I('post.');
            $data['add_time'] = strtotime(I("publish_time"));
            $data['publish_time'] = strtotime(I("publish_time"));
            $travel = M("about")->where('classify="lianxi_paln"')->find();
            if (!empty($travel)) {
                $r = M('about')->where('classify="lianxi_paln"')->save($data);
            }else{
                $data['classify'] = "lianxi_paln";
                $r = M('about')->add($data);
            }
            $referurl = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : U('Admin/Planning');
            if($r){
                $this->success("操作成功",$referurl);
            }else{
                $this->error("操作失败",$referurl);
            }
        }else{
            $about = M("about")->where('classify="lianxi_paln"')->find();
            $this->assign('info',$about);
            $this->initEditor();
            $this->display();
        }
    }


    // 策划实战
    public function planList(){
        $planning = M("planning")->select();
        $this->assign('planList',$planning);
        $this->display();
    }

    /**
     *  搜索策划实战
     */
    public function ajaxPlanList(){       
        $keyword = I('post.key_word');
        $where = "title like '%$keyword%'" ;
        $model = M('planning');
        $count = $model->where($where)->count();
        $Page  = new AjaxPage($count,10);
        $show = $Page->show();
        $planList = $model->where($where)->limit($Page->firstRow.','.$Page->listRows)->select();

        $this->assign('planList',$planList);
        $this->assign('page',$show);// 赋值分页输出
        $this->display();         
    }    
    //读取策划实战信息
    public function plan(){
        $act = I('GET.act','add');
        // print_r($act);die();
        if($act == 'edit'){
            $cats = M('Planning')->where('id='.I('get.id'))->find();
            $info = array();
            if(I('GET.id')){
               $id = I('GET.id');
               $info = D('Planning')->where('id='.$id)->find();
            }
        }
        if($act == 'del'){
            $cats = M('Planning')->where('id='.I('get.id'))->delete();
            $referurl = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : U('Admin/Planning/planList');
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
        $this->display("planning");
    }
    
   
    // 添加修改策划实战
    public function planHandle(){
        $data = I('post.');
        if (I("author") == "") {
            $admin_id = $_SESSION['admin_id'];
            $author = M("admin")->where("admin_id=".$admin_id)->find();
            $data['author'] = $author['user_name'];
        }
        if($data['act'] == 'add'){
            if (empty($data['publish_time'])) {
                $data['add_time'] = time(); 
            }else{
                $data['add_time'] = strtotime($data['publish_time']); 
            }
            
            $r = D('planning')->add($data);
        }
        
        if($data['act'] == 'edit'){
            if (empty($data['add_time'])) {
                $data['add_time'] = time(); 
            }else{
                $data['add_time'] = strtotime($data['add_time']); 
            }
            $r = D('planning')->where('id='.$data['id'])->save($data);
        }
        
        if($data['act'] == 'del'){
            $r = D('planning')->where('id='.$data['id'])->delete();
            if($r) exit(json_encode(1));        
        }
        $referurl = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : U('Admin/Planning/planList');
        if($r){
            $this->success("操作成功",$referurl);
        }else{
            $this->error("操作失败",$referurl);
        }
    }

    // 专家团队
    public function expertList(){
        $expert = M("expert")->select();
        $this->assign('expertList',$expert);
        $this->display();
    }

     /**
     *  搜索专家团队
     */
    public function ajaxExpertList(){       
        $keyword = I('post.key_word');

        $where = "name like '%$keyword%'" ;
        $model = M('expert');
        $count = $model->where($where)->count();
        $Page  = new AjaxPage($count,10);
        $show = $Page->show();
        $expertList = $model->where($where)->limit($Page->firstRow.','.$Page->listRows)->select();

        $this->assign('expertList',$expertList);
        $this->assign('page',$show);// 赋值分页输出
        $this->display();         
    }    
    //读取专家团队信息
    public function expert(){
        $act = I('GET.act','add');
        // print_r($act);die();
        if($act == 'edit'){
            $cats = M('expert')->where('id='.I('get.id'))->find();
            $info = array();
            if(I('GET.id')){
               $id = I('GET.id');
               $info = D('expert')->where('id='.$id)->find();
            }
        }
        if($act == 'del'){
            $cats = M('expert')->where('id='.I('get.id'))->delete();
            $referurl = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : U('Admin/expert/expertList');
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
        $this->display("expert");
    }
    
   
    // 添加修改专家团队
    public function expertHandle(){
        $data = I('post.');
        if($data['act'] == 'add'){
            if (empty($data['add_time'])) {
                $data['add_time'] = time(); 
            }else{
                $data['add_time'] = strtotime($data['add_time']); 
            }
            
            $r = M('expert')->add($data);
        }
        
        if($data['act'] == 'edit'){
            if (empty($data['publish_time'])) {
                $data['add_time'] = time(); 
            }else{
                $data['add_time'] = strtotime($data['publish_time']); 
            }
            $r = M('expert')->where('id='.$data['id'])->save($data);
        }
        
        if($data['act'] == 'del'){
            $r = M('expert')->where('id='.$data['id'])->delete();
            if($r) exit(json_encode(1));        
        }
        $referurl = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : U('Admin/expert/expertList');
        if($r){
            $this->success("操作成功",$referurl);
        }else{
            $this->error("操作失败",$referurl);
        }
    }



    /**
     * 初始化编辑器链接
     * @param $post_id post_id
     */
    private function initEditor()
    {
        $this->assign("URL_upload", U('Admin/Ueditor/imageUp',array('savepath'=>'plan')));
        $this->assign("URL_fileUp", U('Admin/Ueditor/fileUp',array('savepath'=>'plan')));
        $this->assign("URL_scrawlUp", U('Admin/Ueditor/scrawlUp',array('savepath'=>'plan')));
        $this->assign("URL_getRemoteImage", U('Admin/Ueditor/getRemoteImage',array('savepath'=>'plan')));
        $this->assign("URL_imageManager", U('Admin/Ueditor/imageManager',array('savepath'=>'plan')));
        $this->assign("URL_imageUp", U('Admin/Ueditor/imageUp',array('savepath'=>'plan')));
        $this->assign("URL_getMovie", U('Admin/Ueditor/getMovie',array('savepath'=>'plan')));
        $this->assign("URL_Home", "");
    }
    


}