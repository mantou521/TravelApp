<?php

namespace Admin\Controller;
use Admin\Logic\booksLogic;
use Think\AjaxPage;
use Think\Page;

class BooksController extends BaseController {

    /**
     *  UU册列表
     */
    public function booksList(){             
        $books = M("Books")->select();
        $this->assign('booksList',$books);
        $this->display();   
                                          
    }
    
    /**
     *  UU册列表
     */
    public function ajaxBooksList(){       
        $keyword = I('post.key_word');
        $where = "keywords like '%$keyword%' or books_name like '%$keyword%'" ;
        $model = M('Books');
        $count = $model->where($where)->count();
        $Page  = new AjaxPage($count,10);
        $show = $Page->show();
        $booksList = $model->where($where)->order("books_id desc")->limit($Page->firstRow.','.$Page->listRows)->select();
        
        $this->assign('booksList',$booksList);
        $this->assign('page',$show);// 赋值分页输出
        $this->display();         
    }    
    
    
    /**
     * 添加修改UU册
     */
    public function addEditbooks(){                      
        //ajax提交验证
        if(($_GET['is_ajax'] == 1) && IS_POST)
        {            
            $data = I('post.');
            $cardnum = I("cardnum");
            $card = I("card");
            $pass = I("pass");
            // $cardnum = array_filter($cardnum);
            // if (count($cardnum) != count(array_unique($cardnum))) {    
            //    $return_arr = array(
            //         'status' => 0,
            //         'msg'   => '有重复值',                        
            //     );
            //     exit(json_encode($return_arr));
            // } 
            // if ($data['store_count'] != count($cardnum)) {
            //     $return_arr = array(
            //         'status' => 0,
            //         'msg'   => '数量不对,请检查是否有重复',                        
            //     );
            //     exit(json_encode($return_arr));
            // }
            foreach ($cardnum as $cn => $cns) {
                foreach ($card as $c => $cs) {
                    foreach ($pass as $p => $ps) {
                        $start['cardnum'] = $cardnum[$cn];
                        $start['card'] = $card[$cn];
                        $start['pass'] = $pass[$cn];
                        $starts[] = $start;
                        break;
                    }
                break;
                }
            }
            $data['message'] = serialize($starts);
            $data['add_time'] = time();

            // print_r($data);
            // die();
            if(I('post.books_id') == ""){
                $addbooks = M('Books')->add($data);
                $lastid = M("Books")->order("books_id desc")->getField("books_id");
                $books_sn = "UU".str_pad($lastid,7,"0",STR_PAD_LEFT);  
                $b['books_sn'] = $books_sn; 
                M('Books')->where('books_id='.$lastid)->save($b);
                $datas = M("Books")->order('books_id desc')->limit(1)->find();
                $books_id = $datas['books_id'];
                $delbookimgs = M('Books_images')->where('books_id='.$books_id)->delete();
                $imgs = I('post.books_images');
                $imgs = array_filter($imgs);
                foreach ($imgs as $k => $val) {
                    $img['image_url'] = $val;
                    $img['books_id'] = $books_id;
                    $addbookimgs = M('Books_images')->where('books_id='.$books_id)->add($img);
                }
                $return_arr = array(
                    'status' => 1,
                    'msg'   => '操作成功',                        
                    'data'  => array('url'=>U('Admin/Books/booksList')),
                );
                $this->ajaxReturn(json_encode($return_arr));
            }
            // print_r("expression");
            // die();
            $savebooks = M('Books')->where('books_id='.I("post.books_id"))->save($data);
            $delbookimgs = M('Books_images')->where('books_id='.I("post.books_id"))->delete();
            $imgs = I('post.books_images');
            $imgs = array_filter($imgs);
            foreach ($imgs as $k => $val) {
                $img['image_url'] = $val;
                $img['books_id'] = I('post.books_id');
                $addbookimgs = M('Books_images')->where('books_id='.I("post.books_id"))->add($img);
            }
            $return_arr = array(
                'status' => 1,
                'msg'   => '操作成功',                        
                'data'  => array('url'=>U('Admin/Books/booksList')),
            );
            $this->ajaxReturn(json_encode($return_arr));
        }
        
        $booksInfo = M('Books')->where('books_id='.I('GET.id',0))->find();
        $message = unserialize($booksInfo['message']);

        $this->assign('booksInfo',$booksInfo);  // 商品详情   
        $this->assign('message',$message);         
        $booksImages = M("Books_images")->where('books_id ='.I('GET.id',0))->select();
        $this->assign('booksImages',$booksImages);  // 商品相册
        $this->initEditor(); // 编辑器
        $this->display('_books');                                     
    } 

    // 删除UU册
    function delBooks(){
        $booksInfo = M('Books')->where('books_id='.I('GET.id',0))->delete();
        if($booksInfo){
            $this->success("操作成功");
        }else{
            $this->error("操作失败");
        }
    }

    // // 添加UU册种类
    // public function addBookstype(){
    //     if(IS_POST){
    //         $ids = I("id");
    //         $books_name = I("books_name");
    //         foreach ($ids as $i => $id) {
    //             foreach ($books_name as $bn => $bns) {
    //                 $data['id'] = $ids[$i];
    //                 $data['books_name'] = $books_name[$i];
    //             }
    //         $sa = M("books_type")->where("id=" .$data['id'])->save($data);
    //         }
    //         $return_arr = array(
    //             'status' => 1,
    //             'msg'   => '操作成功',                        
    //             'data'  => array('url'=>U('Admin/Books/booksList')),
    //         );
    //         $this->ajaxReturn(json_encode($return_arr));
    //     }
    //     $bookstype = M("books_type")->select();
    //     $this->assign("bookstype",$bookstype);
    //     $this->display();
    // }

    /**
     * 初始化编辑器链接     
     * 本编辑器参考 地址 http://fex.baidu.com/ueditor/
     */
    private function initEditor()
    {
        $this->assign("URL_upload", U('Admin/Ueditor/imageUp',array('savepath'=>'books'))); // 图片上传目录
        $this->assign("URL_imageUp", U('Admin/Ueditor/imageUp',array('savepath'=>'article'))); //  不知道啥图片
        $this->assign("URL_fileUp", U('Admin/Ueditor/fileUp',array('savepath'=>'article'))); // 文件上传s
        $this->assign("URL_scrawlUp", U('Admin/Ueditor/scrawlUp',array('savepath'=>'article')));  //  图片流
        $this->assign("URL_getRemoteImage", U('Admin/Ueditor/getRemoteImage',array('savepath'=>'article'))); // 远程图片管理
        $this->assign("URL_imageManager", U('Admin/Ueditor/imageManager',array('savepath'=>'article'))); // 图片管理        
        $this->assign("URL_getMovie", U('Admin/Ueditor/getMovie',array('savepath'=>'article'))); // 视频上传
        $this->assign("URL_Home", "");
    }    

    /**
     * 删除商品相册图
     */
    public function del_books_images()
    {
        $path = I('filename','');
        M('books_images')->where("image_url = '$path'")->delete();
    }

    // UU册简介
    public function introductions(){
        if(IS_POST){
            $data = I('post.');
            $data['type'] = 2;
            $result = M("introductions")->where("type=2")->find();
            if(empty($result)){
                $res = M("introductions")->add($data);
            }else{
                $result = M("introductions")->where("type=2")->save($data);
            }
            $referurl = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : U('Admin/Books');
            if($result){
                $this->success("操作成功",$referurl);
            }else{
                $this->error("操作失败",$referurl);
            }
        }
        $introductions = M("introductions")->where("type=2")->find();
        $this->assign("info",$introductions);
        $this->initEditor(); // 编辑器
        $this->display();
        
    }

    public function article(){
        $info = M('article')->where('cat_id=3')->find();
        $data['title'] = I("title");
        $data['content'] = I("content");
        $data['keywords'] = I("keywords");
        $data['link'] = I("link");
        $data['description'] = I("description"); 
        $data['thumb'] = I("thumb"); 
        $data['add_time'] = strtotime(I('publish_time')); 
        $data['publish_time'] = strtotime(I('publish_time')); 
        if(empty($info)){
            $act = "add";
        }else{
            $act = "edit";
        }
        if(I("act") == "add"){
            $res = M("article")->add($data);
        }elseif(I("act") == "edit"){
            $res = M("article")->where("article_id=".I("article_id")." AND cat_id=3 ")->save($data);
            $referurl = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : U('Admin/Goods');
            if($res){
                $this->success("操作成功",$referurl);
            }else{
                $this->error("操作失败",$referurl);
            }
            exit();
        }
        $this->assign('act',$act);
        $this->assign('info',$info);
        $this->initEditor();
        $this->display();
    }
    
    

}