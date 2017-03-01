<?php
/**
 *
 * * 
 * ============================================================================
 * $ 2015-08-10 $
 */ 
namespace Api\Controller;
use Think\Controller;
use Api\Logic\GoodsLogic;
use Think\Page;
class AmuseController extends BaseController {
        
    /**
     * 析构流函数
     */
    public function  __construct() {   
        parent::__construct();                
    }

    //上传视频
    public function upload(){
        $video = I("video");
        $video = str_replace(" ","",$video);
        $video_name = time().rand(10000,99999).'.mp4';
        // $video_name = '123456.mp4';
        $pa = "videos";
        $time = date("Y-m-d");
        $t = explode("-",$time);
        $path='./Public/upload/'.$pa.'/'.$t[0].'/'.$t[1]."-".$t[2];
        $this->path($path);
        $new_path = 'Public/upload/'.$pa.'/'.$t[0].'/'.$t[1]."-".$t[2];
        $video_file = $new_path."/".$video_name;
        // echo $video_file;
        // die();
        $str =  pack('H*', $video);
        $fp = fopen($video_file, 'w');
        fwrite($fp, $str);
        fclose($fp);

        $user = M("users")->where("user_id=".I("user_id"))->find();
        $data['head_pic'] = $user['head_pic'];
        $data['nickname'] = $user['nickname'];
        $data['user_id'] = I("user_id");
        $data['title'] = I("title");
        $data['add_time'] = time();
        $data['url'] = $video_file;
        // print_r($_SERVER);
        $result = M("video")->add($data);
        $json_arr = array('status'=>1,'msg'=>'获取成功','result'=>array());
        $this->replace($json_arr);
    }

    // U娱乐首页
    public function happylist(){
        $happy = M("video")->select();
        $json_arr = array('status'=>1,'msg'=>'获取成功','result'=>$happy);
        $this->replace($json_arr);
    }

    // U娱乐详情
    public function happyshow(){
        // 读取信息，是否给评论用户点赞
        if(I("type") == "0"){
            $data['user_id'] = I("user_id");
            $data['video_id'] = I("id");
            $data['ask_users'] = I("ask_users");
            $video_data = M("video_data")->where("video_id=".I("id"))->order("add_time desc")->select();
            $clicks = M("video_click")->where("user_id=".I("user_id"))->select();
            foreach ($clicks as $cl => $cls) {
                foreach ($video_data as $fs => $fos) {
                    if($fos['id'] == $cls['id'] && $cls['ask_id'] == $fos['ask_id']){
                        $video_data[$fs]['click_ed'] = "1";
                    }
                }
            }
        }elseif(I("type") == "1"){
            // 评论
            $user = M("Users")->where("user_id=".I("user_id"))->find();
            $data['head_pic'] = $user['head_pic'];
            $data['user_id'] = $user['user_id'];
            $data['user_name'] = $user['nickname'];
            $data['to_user_id'] = I('u_id');
            $data['video_id'] = I("id");
            $data['content'] = I("content");
            $data['to_ask_id'] = I("to_ask_id");
            $data['add_time'] = time();
            $video_data = M("video_data")->where("video_id=".I("id")." AND user_id=".I("user_id"))->order("add_time desc")->select();
            foreach ($video_data as $key => $f) {
                $cont[] = $f['content'];
            }
            if(in_array(I("content"), $cont)){
                exit(json_encode(array('status'=>0,'msg'=>'您已评论过了,聊点其他的吧','result'=>$video_data)));
            }else{
                $fo_data = M("video_data")->add($data);
            }
            if($fo_data){
                $video_data = M("video_data")->where("video_id=".I("id"))->order("add_time desc")->select();
                $clicks = M("video_click")->where("user_id=".I("user_id"))->select();
                foreach ($clicks as $cl => $cls) {
                    foreach ($video_data as $fs => $fos) {
                        if($fos['video_id'] == $cls['video_id'] && $fos['to_ask_id'] == 0){
                            $video_data[$fs]['click_ed'] = "1";
                        }
                    }
                }
            }else{
                exit(json_encode(array('status'=>0,'msg'=>'评论失败','result'=>$video)));
            }
            
        }
        elseif (I("type") == "2") {
            // 给评论用户点赞
            if (I("post.click")=='1') {
                $click['user_id'] = I("user_id");
                $click['video_id'] = I("id");
                $click['ask_users'] = I("ask_users");
                $click['ask_id'] = I("ask_id");
                $clicks = M("video_click")->where("user_id=".I("user_id"))->select();
                foreach ($clicks as $c => $vc) {
                    $ids[] = $vc['ask_id'];
                    $akids[] = $vc['ask_users'];
                    $fids[] = $vc['video_id'];
                }
                if(in_array(I("id"), $fids) && in_array(I("ask_users"), $akids) && in_array(I("ask_id"), $ids)){
                    $json_arr = array('status'=>0,'msg'=>'已点赞','result'=>'已点赞');
                    $json_str = json_encode($json_arr,TRUE); 
                    exit($json_str);
                }else{
                    $click = M("video_click")->add($click);
                    $video = M("video")->where("id=".I("id"))->find();
                    $video_data = M("video_data")->where("video_id=".I("id"))->find();
                    $data['ask_user'] = I("ask_user");
                    if(I("ask_id") == "0"){
                        $data['click'] = ++$video['click'];
                        $res = M("video")->where("id=".I("id"))->save($data);
                        $video['click_ed'] = "1";
                    }else{
                        $data['click'] = ++$video_data['click'];
                        $res = M("video_data")->where("video_id=".I("id")." AND ask_id=".I("ask_id"))->save($data);
                    }
                    
                    $video_data = M("video_data")->where("video_id=".I("id"))->order("add_time desc")->select();
                    $clicks = M("video_click")->where("user_id=".I("user_id"))->select();
                    foreach ($clicks as $cl => $cls) {
                        foreach ($video_data as $fs => $fos) {
                            if($fos['video_id'] == $cls['video_id'] && $fos['ask_id'] == $cls['ask_id']){
                                $video_data[$fs]['click_ed'] = "1";
                            }
                        }
                    }
                }
            }elseif (I("post.click")=='0') {
                $video = M("video")->where('id='.I("id"))->find();
                $data['click'] = --$video['click'];
                M("video")->where('id='.I("id"))->save($data);

                $video_data = M("video_data")->where('video_id='.I("id"))->find();
                $data['click'] = --$video_data['click'];
                M("video_data")->where('video_id='.I("id")." AND ask_id=".I("ask_id"))->save($data);
                M("video_click")->where("user_id=".I("user_id")." AND video_id=".I("id")." AND ask_id=".I("ask_id"))->delete();
                $clicks = M("video_click")->where("user_id=".I("user_id"))->select();
                $video_se = M("video")->select();
                $video_data = M("video_data")->where("video_id=".I("id"))->order("add_time desc")->select();
                $clicks = M("video_click")->where("user_id=".I("user_id"))->select();
                foreach ($clicks as $cl => $cls) {
                    foreach ($video_data as $fs => $fos) {
                        if($fos['video_id'] == $cls['video_id'] && $cls['ask_id'] == $fos['ask_id']){
                            $video_data[$fs]['click_ed'] = "1";
                        }
                    }
                }
            }
        }

        elseif (I("type") == "3"){
            $user = M("users")->where("user_id=".I("uid"))->find();
            $data['user_id'] = I("user_id");
            $data['to_user_id'] = I("uid");
            $data['head_pic'] = $user['head_pic'];
            $data['nickname'] = $user['nickname'];
            $data['concern_ed'] = "1";
            if(I("concern") == "1"){
                $concern = M("video_concern")->where("user_id=".I("user_id")." AND to_user_id=".I("uid"))->find();
                if(!$concern){
                    M("video_concern")->add($data);
                }
                
            }elseif(I("concern") == "0"){
                M("video_concern")->where("user_id=".I("user_id")." AND to_user_id=".I("uid"))->delete();
            }
            
            $video_data = M("video_data")->where("video_id=".I("id"))->order("add_time desc")->select();
            $clicks = M("video_click")->where("user_id=".I("user_id"))->select();
            foreach ($clicks as $cl => $cls) {
                foreach ($video_data as $fs => $fos) {
                    if($fos['video_id'] == $cls['video_id'] && $fos['ask_id'] == $cls['ask_id']){
                        $video_data[$fs]['click_ed'] = "1";
                    }
                }
            }
            $concern = M("video_concern")->select();
            foreach ($concern as $c => $cc) {
                $user_ids[] = $cc['user_id'];
                $uid[] = $cc['to_user_id'];
            }
            // print_r($concern);
        }

        $video = M("video")->where("id=".I("id"))->find();
        if(in_array(I('user_id'), $user_ids) && in_array(I('uid'), $uid)){
            $video['concern_ed'] = "1";
        }else{
            $video['concern_ed'] = "0";
        }
        $clicks = M("video_click")->where("user_id=".I("user_id"))->select();
        foreach ($clicks as $cl => $cls) {
            if($video['id'] == $cls['video_id'] && $cls['ask_id'] == "0"){
                $video['click_ed'] = "1";
            }
        }
        $fo_data = $this->tree($video_data);
        // print_r($cats);
        // die();

        //循环评论信息，判断层级
        foreach ($fo_data as $key => $value) {
            foreach ($fo_data as $fd => $fds) {
                // 评论信息里的被回复的问题id=评论问题id
                if($fo_data[$fd]['to_ask_id'] == $fo_data[$key]['ask_id']){
                    // 第一级评论整合成数组
                    $fo_data[$key]['data'][] = $fo_data[$fd];
                    // $fo_da_da[] = $fo_data[$fd];
                    // 删除该判断层级的元素
                    unset($fo_data[$fd]);
                    // 循环第二层评论
                    foreach ($fo_data[$key]['data'] as $fdd => $fdds) {
                        foreach ($fo_data[$key]['data'] as $fddk => $fddks) {
                            // 第一层评论里的用户id=下一级的被回复的用户id
                            if ($fo_data[$key]['data'][$fdd]['user_id'] == $fo_data[$key]['data'][$fddk]['to_user_id']) {
                                // 整合层级
                                $fo_data[$key]['data'][$fdd]['datas'][] = $fo_data[$key]['data'][$fddk];
                                unset($fo_data[$key]['data'][$fddk]);
                            }
                        break;
                        }
                    }
                }
            }
        }

        foreach ($fo_data as $f => $fs) {
            $for_data[] = $fs;
        }
        
        $result['video'] = $video;
        $result['video_data'] = $for_data;
        
        $json_str = json_encode($json_arr,TRUE); 
        exit($json_str);
    }

    // 关注列表
    public function conlist(){
        $concern = M("video_concern")->where("user_id=".I("user_id"))->select();
        foreach ($concern as $c => $con) {
            $user[] = M("users")->where("user_id=".$con['user_id'])->find();
        }
        $json_arr = array('status'=>1,'msg'=>'获取成功','result'=>$user);
        $this->replace($json_arr);
        print_r($user);
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
