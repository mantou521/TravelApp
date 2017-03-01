<?php

namespace Admin\Controller;
use Admin\Logic\GoodsLogic;
use Think\AjaxPage;
use Think\Page;

class BannersController extends BaseController {

    /**
     *  UU册列表
     */
    public function bannersList(){             
        $banners = M("Banners")->select();
        $this->assign('bannersList',$banners);
        $this->display();   
                                          
    }
    
    /**
     *  UU册列表
     */
    public function ajaxbannersList(){       
        $keyword = I('post.key_word');
        $where = "keywords like '%$keyword%' or banners_name like '%$keyword%'" ;
        $model = M('Banners');
        $count = $model->where($where)->count();
        $Page  = new AjaxPage($count,10);
        $show = $Page->show();
        $bannersList = $model->where($where)->limit($Page->firstRow.','.$Page->listRows)->select();
        
        $this->assign('bannersList',$bannersList);
        $this->assign('page',$show);// 赋值分页输出
        $this->display();         
    }    
    
    
    /**
     * 添加修改UU册
     */
    public function addEditbanners(){                      
        //ajax提交验证
        if(($_GET['is_ajax'] == 1) && IS_POST)
        {            
            $data = I('post.');
            if(I('post.banners_id') == ""){
                $addbanners = M('Banners')->add($data);
                $datas = M("Banners")->order('banners_id desc')->limit(1)->find();
                $banners_id = $datas['banners_id'];
                $delbannerimgs = M('Banners_images')->where('banners_id='.$banners_id)->delete();
                $imgs = I('post.banners_images');
                $imgs = array_filter($imgs);
                foreach ($imgs as $k => $val) {
                    $img['image_url'] = $val;
                    $img['banners_id'] = $banners_id;
                    $addbannerimgs = M('Banners_images')->where('banners_id='.$banners_id)->add($img);
                }
                $return_arr = array(
                    'status' => 1,
                    'msg'   => '操作成功',                        
                    'data'  => array('url'=>U('Admin/Banners/bannersList')),
                );
                $this->ajaxReturn(json_encode($return_arr));
            }
            // print_r("expression");
            // die();
            $savebanners = M('Banners')->where('banners_id='.I("post.banners_id"))->save($data);
            $delbannerimgs = M('Banners_images')->where('banners_id='.I("post.banners_id"))->delete();
            $imgs = I('post.banners_images');
            $imgs = array_filter($imgs);
            foreach ($imgs as $k => $val) {
                $img['image_url'] = $val;
                $img['banners_id'] = I('post.banners_id');
                $addbannerimgs = M('Banners_images')->where('banners_id='.I("post.banners_id"))->add($img);
            }
            $return_arr = array(
                'status' => 1,
                'msg'   => '操作成功',                        
                'data'  => array('url'=>U('Admin/Banners/bannersList')),
            );
            $this->ajaxReturn(json_encode($return_arr));
        }
        
        $bannersInfo = M('Banners')->where('banners_id='.I('GET.id',0))->find();
        
        $this->assign('bannersInfo',$bannersInfo);  // banner详情            
        $bannersImages = M("Banners_images")->where('banners_id ='.I('GET.id',0))->select();
        $locals = M("Local")->select();
        $this->assign('locals',$locals);
        $this->assign('bannersImages',$bannersImages);  // banner相册
        $this->initEditor(); // 编辑器
        $this->display('_banners');                                     
    } 

 // 删除UU册
    function delBanners(){
        $bannersInfo = M('Banners')->where('banners_id='.I('GET.id',0))->delete();
        if($bannersInfo){
            $this->success("操作成功");
        }else{
            $this->error("操作失败");
        }
    }

    /**
     * 初始化编辑器链接     
     * 本编辑器参考 地址 http://fex.baidu.com/ueditor/
     */
    private function initEditor()
    {
        $this->assign("URL_upload", U('Admin/Ueditor/imageUp',array('savepath'=>'banners'))); // 图片上传目录
        $this->assign("URL_imageUp", U('Admin/Ueditor/imageUp',array('savepath'=>'article'))); //  不知道啥图片
        $this->assign("URL_fileUp", U('Admin/Ueditor/fileUp',array('savepath'=>'article'))); // 文件上传s
        $this->assign("URL_scrawlUp", U('Admin/Ueditor/scrawlUp',array('savepath'=>'article')));  //  图片流
        $this->assign("URL_getRemoteImage", U('Admin/Ueditor/getRemoteImage',array('savepath'=>'article'))); // 远程图片管理
        $this->assign("URL_imageManager", U('Admin/Ueditor/imageManager',array('savepath'=>'article'))); // 图片管理        
        $this->assign("URL_getMovie", U('Admin/Ueditor/getMovie',array('savepath'=>'article'))); // 视频上传
        $this->assign("URL_Home", "");
    }    

    /**
     * 删除banner相册图
     */
    public function del_banners_images()
    {
        $path = I('filename','');
        M('banners_images')->where("image_url = '$path'")->delete();
    }
    
    /**
     * 添加banner位置
     */
    public function local()
    {
        $local = M("local")->select();
        $this->assign('info',$local);
        $this->display();
    }

    public function addEditlocal(){
        //ajax提交验证
        if(($_GET['is_ajax'] == 1) && IS_POST)
        {            
            $data['local'] = I('post.local');
            // print_r($data);die();
            $local = M("local")->add($data);
            $return_arr = array(
                'status' => 1,
                'msg'   => '操作成功',                        
                'data'  => array('url'=>U('Admin/Banners/bannersList')),
            );
            $this->ajaxReturn(json_encode($return_arr));
        }
        
    }
    
    
  
}