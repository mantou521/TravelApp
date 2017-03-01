<?php

namespace Admin\Controller;
use Think\AjaxPage;
use Think\Page;

class TicketController extends BaseController {

	 // U机票简介
    public function introductions(){
        if(IS_POST){
            $data = I('post.');
            $data['type'] = 4;
            $result = M("introductions")->where("type=4")->find();
            if(empty($result)){
                $result = M("introductions")->add($data);
            }else{
                $result = M("introductions")->where("type=4")->save($data);
            }
            $referurl = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : U('Admin/Ticket');
            if($result){
                $this->success("操作成功",$referurl);
            }else{
                $this->error("操作失败",$referurl);
            }
        }
        $introductions = M("introductions")->where("type=4")->find();
        $this->assign("info",$introductions);
        $this->initEditor(); // 编辑器
        $this->display();
        
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