<?php

namespace Admin\Controller;
use Think\AjaxPage;
use Think\Page;

class KefuController extends BaseController {


    
    /**
     *  搜索动态列表
     */
    public function ajaxShopkefuList(){       
        $keyword = I('post.key_word');
        $where = "nickname like '%$keyword%'" ;
        $model = M('Kefu');
        $count = $model->where($where)->count();
        $Page  = new AjaxPage($count,10);
        $show = $Page->show();
        $kefuList = $model->where($where)->limit($Page->firstRow.','.$Page->listRows)->select();

        $this->assign('kefuList',$kefuList);
        $this->assign('page',$show);// 赋值分页输出
        $this->display();         
    }    
    
    public function shopkefu(){
        $act = I('GET.act','add');
        // print_r($act);die();
        if($act == 'edit'){
            $cats = M('kefu')->where('id=1')->find();
            $info = array();
            if(I('GET.id')){
               $id = I('GET.id');
               $info = D('kefu')->where('id='.$id)->find();
            }
        }
        if($act == 'del'){
            $cats = M('News')->where('id='.I('get.id'))->delete();
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
    public function kefuHandle(){
        $data = I('post.');
        if($data['act'] == 'add'){
            $r = D('kefu')->add($data);
        }
        
        if($data['act'] == 'edit'){
            $r = D('kefu')->where('id='.$data['id'])->save($data);
        }
        
        if($data['act'] == 'del'){
            $r = D('kefu')->where('id='.$data['id'])->delete();
            if($r) exit(json_encode(1));        
        }
        $referurl = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : U('Admin/kefu/shopkefuList');
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