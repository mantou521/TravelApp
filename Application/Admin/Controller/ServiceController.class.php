<?php

namespace Admin\Controller;
use Think\AjaxPage;
use Think\Page;

class ServiceController extends BaseController {

    /**
     *  动态列表
     */
    public function hgservice(){             
        $news = M("News")->select();
        $this->assign('newsList',$news);
        $this->display();   
                                          
    }
    
    /**
     *  搜索动态列表
     */
    public function ajaxNewsList(){       
        $keyword = I('post.key_word');
        $where = "keywords like '%$keyword%' or title like '%$keyword%'" ;
        $model = M('News');
        $count = $model->where($where)->count();
        $Page  = new AjaxPage($count,10);
        $show = $Page->show();
        $newsList = $model->where($where)->limit($Page->firstRow.','.$Page->listRows)->select();
        
        $this->assign('newsList',$newsList);
        $this->assign('page',$show);// 赋值分页输出
        $this->display();         
    }    
    
    public function news(){
        $act = I('GET.act','add');
        // print_r($act);die();
        if($act == 'edit'){
            $cats = M('News')->where('news_id='.I('get.news_id'))->find();
            $info = array();
            if(I('GET.news_id')){
               $news_id = I('GET.news_id');
               $info = D('news')->where('news_id='.$news_id)->find();
            }
        }
        if($act == 'del'){
            $cats = M('News')->where('news_id='.I('get.news_id'))->delete();
            $referurl = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : U('Admin/lines/newsList');
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

    // 添加动态
    public function newsHandle(){
        $data = I('post.');

        if($data['act'] == 'add'){
            $data['click'] = mt_rand(1000,1300);
            // $data['add_time'] = time(); 
            $r = D('news')->add($data);
        }
        
        if($data['act'] == 'edit'){
            $r = D('news')->where('news_id='.$data['news_id'])->save($data);
        }
        
        if($data['act'] == 'del'){
            $r = D('news')->where('news_id='.$data['news_id'])->delete();
            if($r) exit(json_encode(1));        
        }
        $referurl = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : U('Admin/news/newsList');
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
        $this->assign("URL_upload", U('Admin/Ueditor/imageUp',array('savepath'=>'news')));
        $this->assign("URL_fileUp", U('Admin/Ueditor/fileUp',array('savepath'=>'news')));
        $this->assign("URL_scrawlUp", U('Admin/Ueditor/scrawlUp',array('savepath'=>'news')));
        $this->assign("URL_getRemoteImage", U('Admin/Ueditor/getRemoteImage',array('savepath'=>'news')));
        $this->assign("URL_imageManager", U('Admin/Ueditor/imageManager',array('savepath'=>'news')));
        $this->assign("URL_imageUp", U('Admin/Ueditor/imageUp',array('savepath'=>'news')));
        $this->assign("URL_getMovie", U('Admin/Ueditor/getMovie',array('savepath'=>'news')));
        $this->assign("URL_Home", "");
    }
    
}