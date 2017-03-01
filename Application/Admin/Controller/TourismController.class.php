<?php
/**
 */
namespace Admin\Controller;
use Think\AjaxPage;
use Think\Page;

class TourismController extends BaseController {

    /**
     *  动态列表
     */
    public function newsList(){             
        $news = M("News")->select();
        $this->assign('newsList',$news);
        $this->display();   
                                          
    }


     // U旅行简介
    public function introductions(){
        if(IS_POST){
            $data = I('post.');
            $data['type'] = 3;
            $result = M("introductions")->where("type=3")->find();
            if(empty($result)){
                $result = M("introductions")->add($data);
            }else{
                $result = M("introductions")->where("type=3")->save($data);
            }
            $referurl = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : U('Admin/Tourism');
            if($result){
                $this->success("操作成功",$referurl);
            }else{
                $this->error("操作失败",$referurl);
            }
        }
        $introductions = M("introductions")->where("type=3")->find();
        $this->assign("info",$introductions);
        $this->initEditor(); // 编辑器
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
    

    // 添加动态
    public function newsHandle(){
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
            
            $r = D('news')->add($data);
        }
        
        if($data['act'] == 'edit'){
            if (empty($data['add_time'])) {
                $data['add_time'] = time(); 
            }else{
                $data['add_time'] = strtotime($data['add_time']); 
            }
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
     *  旅游线路列表
     */
    public function linesList(){             
        $lines = M("Lines")->select();
        $this->assign('linesList',$lines);
        $this->display();   
                                          
    }

    /**
     *  搜索旅游线路
     */
    public function ajaxLinesList(){       
        $keyword = I('post.key_word');
        $where = "keywords like '%$keyword%' or title like '%$keyword%'" ;
        $model = M('Lines');
        $count = $model->where($where)->count();
        $Page  = new AjaxPage($count,10);
        $show = $Page->show();
        $linesList = $model->where($where)->limit($Page->firstRow.','.$Page->listRows)->select();
        
        $this->assign('linesList',$linesList);
        $this->assign('page',$show);// 赋值分页输出
        $this->display();         
    }    
    
    // 旅游线路
     public function lines(){
        $act = I('GET.act','add');
        // print_r($act);die();
        if($act == 'edit'){
            $cats = M('Lines')->where('lines_id='.I('get.lines_id'))->find();
            $info = array();
            if(I('GET.lines_id')){
               $lines_id = I('GET.lines_id');
               $info = D('Lines')->where('lines_id='.$lines_id)->find();
               $start_tp = unserialize($info['start_tp']);
               // $region = M("line_address")->where("laid=".$info['city'])->find();
               // $info['cityname'] = $region['hot'];
            }
        }
        if($act == 'del'){
            $cats = M('Lines')->where('lines_id='.I('get.lines_id'))->delete();
            $referurl = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : U('Admin/lines/newsList');
            if($cats){
                $this->success("操作成功",$referurl);
                exit();
            }else{
                $this->error("操作失败",$referurl);
            }
        }
        
        $region = M("line_address")->select();
        // print_r($region);die();
        $region = $this->tree($region);
        $this->assign('region',$region);
        $this->assign('act',$act);
        $this->assign('info',$info);
        $this->assign('start_tp',$start_tp);
        $this->initEditor();
        $this->display();
    }

    // 热门目的地推荐
     public function addressList(){
        $address = M("line_address")->select();
        // echo "<pre>";
        // print_r($address);
        $info = $this->tree($address);
        // print_r($info);
        $this->assign('info',$info);
        $this->display();
    }

    /**
     *  添加修改商品分类
     *  手动拷贝分类正则 ([\u4e00-\u9fa5/\w]+)  ('393','$1'), 
     *  select * from tp_goods_category where id = 393
        select * from tp_goods_category where parent_id = 393
        update tp_goods_category  set parent_id_path = concat_ws('_','0_76_393',id),`level` = 3 where parent_id = 393
        insert into `tp_goods_category` (`parent_id`,`name`) values 
        ('393','时尚饰品'),
     */
    public function addEditCategory(){
        
        if(($_GET['is_ajax'] == 1) && IS_POST)
        {   
            if(empty(I("id"))){
                $r = M("line_address")->add(I('post.'));
            }else{
                $r = M("line_address")->where("id=".I("id"))->save(I("post."));
            }
            $return_arr = array(
                'status' => 1,
                'msg'   => '操作成功',                        
                'data'  => array('url'=>U('Admin/Tourism/addressList')),
            );
            $this->ajaxReturn(json_encode($return_arr));
        }

        if (!empty(I("id"))) {
            $address = M("line_address")->where("id=".I("id"))->find();
            
        }
        $cat_list = M("line_address")->field('id,name,parent_id')->select();
        $cat_list = $this->tree($cat_list);

        $this->assign("address",$address);
        $this->assign("cat_list",$cat_list);
        $this->display("address");
    }
    
    // 删除线路
    public function delAddsCategory(){
        $cats = M("line_address")->where("id=".I("id"))->delete();
        $referurl = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : U('Admin/lines/newsList');
        if($cats){
            $this->success("操作成功",$referurl);
            exit();
        }else{
            $this->error("操作失败",$referurl);
        }
    }

    // 添加
    public function linesHandle(){
        $data = I('post.');

        if($data['act'] == 'add'){
            // $data['click'] = mt_rand(1000,1300);
            $start_time = I("start_time");
            $start_price = I("start_price");
            $child_price = I("child_price");
            foreach ($start_time as $st => $stime) {
                foreach ($start_price as $sp => $sprice) {
                    foreach ($child_price as $cp => $cprice) {
                        $start['start_time'] = strtotime($start_time[$st]);
                        $start['start_price'] = $start_price[$st];
                        $start['child_price'] = $child_price[$st];
                        $starts[] = $start;
                        break;
                    }
                break;
                }
            }
            $data['start_tp'] = serialize($starts);
            if (empty($data['add_time'])) {
                $data['add_time'] = time(); 
            }else{
                $data['add_time'] = strtotime($data['add_time']); 
            }
            if (empty($data['end_time'])) {
                $data['end_time'] = time(); 
            }else{
                $data['end_time'] = strtotime($data['end_time']); 
            }
            $r = D('lines')->add($data);
            $line_sn = M("lines")->order("lines_id desc")->limit(1)->find();
            if(empty($line_sn['line_sn'])){
                $lines_sn['line_sn'] = "UU".str_pad($line_sn['lines_id'],7,"0",STR_PAD_LEFT);
            }
            M("lines")->where("lines_id=".$line_sn['lines_id'])->save($lines_sn);
        }
        
        if($data['act'] == 'edit'){
            $start_time = I("start_time");
            $start_price = I("start_price");
            $child_price = I("child_price");
            foreach ($start_time as $st => $stime) {
                foreach ($start_price as $sp => $sprice) {
                    foreach ($child_price as $cp => $cprice) {
                        $start['start_time'] = strtotime($start_time[$st]);
                        $start['start_price'] = $start_price[$st];
                        $start['child_price'] = $child_price[$st];
                        $starts[] = $start;
                        break;
                    }
                break;
                }
            }
            // print_r($starts);die();
            $data['start_tp'] = serialize($starts);
            if (empty($data['add_time'])) {
                $data['add_time'] = time(); 
            }else{
                $data['add_time'] = strtotime($data['add_time']); 
            }
            if (empty($data['end_time'])) {
                $data['end_time'] = time(); 
            }else{
                $data['end_time'] = strtotime($data['end_time']); 
            }
            $r = D('lines')->where('lines_id='.$data['lines_id'])->save($data);
        }
        
        if($data['act'] == 'del'){
            $r = D('lines')->where('lines_id='.$data['lines_id'])->delete();
            if($r) exit(json_encode(1));        
        }
        $referurl = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : U('Admin/lines/newsList');
        if($r){
            $this->success("操作成功",$referurl);
        }else{
            $this->error("操作失败",$referurl);
        }
    }

    // UU旅游--关于
    function about_travel(){
        if(IS_POST){
            $data = I('post.');
            $data['add_time'] = strtotime(I("add_time"));
            $data['publish_time'] = strtotime(I("add_time"));

            $travel = M("about")->where('classify="about_travel"')->find();
            if (!empty($travel)) {
                $r = D('about')->where('classify="about_travel"')->save($data);
            }else{
                $data['classify'] = "about_travel";
                $r = D('about')->add($data);
            }
            $referurl = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : U('Admin/about_travel');
            if($r){
                $this->success("操作成功",$referurl);
            }else{
                $this->error("操作失败",$referurl);
            }
        }else{
            $about = M("about")->where('classify="about_travel"')->find();
            $this->assign('info',$about);
            $this->initEditor();
            $this->display();
        }
    }
    
    // 限时优惠
    function benefitsList(){
        $benefits = M("Benefits")->select();
        $this->assign('benefitsList',$benefits);
        $this->display();
    }

    /**
     *  搜索旅游线路
     */
    public function ajaxBenefitsList(){       
        $keyword = I('post.key_word');
        $where = "keywords like '%$keyword%' or title like '%$keyword%'" ;
        $model = M('Benefits');
        $count = $model->where($where)->count();
        $Page  = new AjaxPage($count,10);
        $show = $Page->show();
        $benefitsList = $model->where($where)->limit($Page->firstRow.','.$Page->listRows)->select();
        
        $this->assign('benefitsList',$benefitsList);
        $this->assign('page',$show);// 赋值分页输出
        $this->display();         
    }    

    // 限时优惠操作
     public function benefits(){
        $act = I('GET.act','add');
        // print_r($act);die();
        if($act == 'edit'){
            $cats = M('Benefits')->where('benefits_id='.I('get.benefits_id'))->find();
            $info = array();
            if(I('GET.benefits_id')){
               $benefits_id = I('GET.benefits_id');
               $info = D('Benefits')->where('benefits_id='.$benefits_id)->find();
               $start_tp = unserialize($info['start_tp']);
            }
        }
        if($act == 'del'){
            $cats = M('Benefits')->where('benefits_id='.I('get.benefits_id'))->delete();
            $referurl = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : U('Admin/benefits/benefitsList');
            if($cats){
                $this->success("操作成功",$referurl);
                exit();
            }else{
                $this->error("操作失败",$referurl);
            }
        }
        
        $region = M("region")->where('parent_id=0')->select();
        // print_r($region);die();
        $this->assign('region',$region);
        $this->assign('act',$act);
        $this->assign('info',$info);
        $this->assign('start_tp',$start_tp);
        $this->initEditor();
        $this->display();
    }

    // 添加限时优惠
    public function benefitsHandle(){
        $data = I('post.');
        if($data['act'] == 'add'){
            $start_time = I("start_time");
            $start_price = I("start_price");
            $child_price = I("child_price");
            foreach ($start_time as $st => $stime) {
                foreach ($start_price as $sp => $sprice) {
                    foreach ($child_price as $cp => $cprice) {
                        $start['start_time'] = strtotime($start_time[$st]);
                        $start['start_price'] = $start_price[$st];
                        $start['child_price'] = $child_price[$st];
                        $starts[] = $start;
                        break;
                    }
                break;
                }
            }
            $data['start_tp'] = serialize($starts);
            if (empty($data['add_time'])) {
                $data['add_time'] = time(); 
            }else{
                $data['add_time'] = strtotime($data['add_time']); 
            }
            if (empty($data['end_time'])) {
                $data['end_time'] = time(); 
            }else{
                $data['end_time'] = strtotime($data['end_time']); 
            }
            // $data['add_time'] = date("Y-m-d");
            $r = D('benefits')->add($data);
        }
        
        if($data['act'] == 'edit'){
            $start_time = I("start_time");
            $start_price = I("start_price");
            $child_price = I("child_price");
            foreach ($start_time as $st => $stime) {
                foreach ($start_price as $sp => $sprice) {
                    foreach ($child_price as $cp => $cprice) {
                        $start['start_time'] = strtotime($start_time[$st]);
                        $start['start_price'] = $start_price[$st];
                        $start['child_price'] = $child_price[$st];
                        $starts[] = $start;
                        break;
                    }
                break;
                }
            }
            $data['start_tp'] = serialize($starts);
            if (empty($data['add_time'])) {
                $data['add_time'] = time(); 
            }else{
                $data['add_time'] = strtotime($data['add_time']); 
            }
            if (empty($data['end_time'])) {
                $data['end_time'] = time(); 
            }else{
                $data['end_time'] = strtotime($data['end_time']); 
            }
            $r = D('benefits')->where('benefits_id='.$data['benefits_id'])->save($data);
        }
        
        if($data['act'] == 'del'){
            $r = D('benefits')->where('benefits_id='.$data['benefits_id'])->delete();
            if($r) exit(json_encode(1));        
        }
        $referurl = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : U('Admin/benefits/benefitsList');
        if($r){
            $this->success("操作成功",$referurl);
        }else{
            $this->error("操作失败",$referurl);
        }
    }

    // 私人定制
    function customsList(){
        $customs = M("customs")->select();
        $this->assign('customsList',$customs);
        $this->display();
    }
    

    /**
     *  搜索私人
     */
    public function ajaxCustomsList(){       
        $keyword = I('post.key_word');
        $where = "name like '%$keyword%'" ;
        $model = M('Customs');
        $count = $model->where($where)->count();
        $Page  = new AjaxPage($count,10);
        $show = $Page->show();
        $customsList = $model->where($where)->limit($Page->firstRow.','.$Page->listRows)->select();
        
        $this->assign('customsList',$customsList);
        $this->assign('page',$show);// 赋值分页输出
        $this->display();         
    }    

    // 私人定制操作
     public function customs(){
        $act = I('GET.act','add');
        // print_r($act);die();
        if($act == 'edit'){
            $cats = M('Customs')->where('customs_id='.I('get.customs_id'))->find();
            $info = array();
            if(I('GET.customs_id')){
               $customs_id = I('GET.customs_id');
               $info = D('Customs')->where('customs_id='.$customs_id)->find();
            }
        }
        if($act == 'del'){
            $cats = M('Customs')->where('customs_id='.I('get.customs_id'))->delete();
            $referurl = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : U('Admin/customs/customsList');
            if($cats){
                $this->success("操作成功",$referurl);
                exit();
            }else{
                $this->error("操作失败",$referurl);
            }
        }
        
        $region = M("region")->where('parent_id=0')->select();
        // print_r($region);die();
        $this->assign('region',$region);
        $this->assign('act',$act);
        $this->assign('info',$info);
        $this->initEditor();
        $this->display();
    }
    

    // 修改定制信息
    public function customsHandle(){
        $data = I('post.');

        if($data['act'] == 'add'){
            $data['click'] = mt_rand(1000,1300);
            // $data['add_time'] = time(); 
            if (empty($data['add_time'])) {
                $data['add_time'] = time(); 
            }else{
                $data['add_time'] = strtotime($data['add_time']); 
            }
            if (empty($data['end_time'])) {
                $data['end_time'] = time(); 
            }else{
                $data['end_time'] = strtotime($data['end_time']); 
            }
            $r = D('customs')->add($data);
        }
        
        if($data['act'] == 'edit'){
            // print_r($data);die();
            if (empty($data['add_time'])) {
                $data['add_time'] = time(); 
            }else{
                $data['add_time'] = strtotime($data['add_time']); 
            }
            if (empty($data['end_time'])) {
                $data['end_time'] = time(); 
            }else{
                $data['end_time'] = strtotime($data['end_time']); 
            }
            $r = D('customs')->where('customs_id='.$data['customs_id'])->save($data);
        }
        
        if($data['act'] == 'del'){
            $r = D('customs')->where('customs_id='.$data['customs_id'])->delete();
            if($r) exit(json_encode(1));        
        }
        $referurl = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : U('Admin/customs/customsList');
        if($r){
            $this->success("操作成功",$referurl);
        }else{
            $this->error("操作失败",$referurl);
        }
    }

    // UU旅游--联系我们
    function contact_us(){
        if(IS_POST){
            $data = I('post.');
            $data['add_time'] = strtotime(I("add_time"));
            $data['publish_time'] = strtotime(I("add_time"));
            $travel = M("about")->where('classify="contact_us"')->find();
            if (!empty($travel)) {
                $r = D('about')->where('classify="contact_us"')->save($data);
            }else{
                $data['classify'] = "contact_us";
                $r = D('about')->add($data);
            }
            $referurl = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : U('Admin/contact_us');
            if($r){
                $this->success("操作成功",$referurl);
            }else{
                $this->error("操作失败",$referurl);
            }
        }else{
            $about = M("about")->where('classify="contact_us"')->find();
            $this->assign('info',$about);
            $this->initEditor();
            $this->display();
        }
    }

    // 结伴拼邮
    function togethersList(){
        $togethers = M("togethers")->select();
        $this->assign('togethersList',$togethers);
        $this->display();
    }
   
    /**
     *  搜索结伴出游
     */
    public function ajaxTogethersList(){       
        $keyword = I('post.key_word');
        $where = "togethers_name like '%$keyword%' or address like '%$keyword%'" ;
        $model = M('Togethers');
        $count = $model->where($where)->count();
        $Page  = new AjaxPage($count,15);
        $show = $Page->show();
        $togethersList = $model->where($where)->limit($Page->firstRow.','.$Page->listRows)->select();
        // print_r($togethersList);die();
        $this->assign('togethersList',$togethersList);
        $this->assign('page',$show);// 赋值分页输出
        $this->display();         
    }    

    // 添加拼游规则
    public function expense(){
        print_r("添加拼游规则");
    }
    
    /**
     * 添加修改拼游
     */
    public function addEdittogethers(){                      
            //ajax提交验证
        if(($_GET['is_ajax'] == 1) && IS_POST)
        {            
            $data = I('post.');
            if (empty($data['add_time'])) {
                $data['add_time'] = time(); 
            }else{
                $data['add_time'] = strtotime($data['add_time']); 
            }
            if(I('post.togethers_id') == ""){
                $addtogethers = M('Togethers')->add($data);
                $return_arr = array(
                    'status' => 1,
                    'msg'   => '操作成功',                        
                    'data'  => array('url'=>U('Admin/Tourism/togethersList')),
                );
                $this->ajaxReturn(json_encode($return_arr));
            }
            // print_r("expression");
            // die();
            $savetogethers = M('Togethers')->where('togethers_id='.I("post.togethers_id"))->save($data);
            $deltogetherimgs = M('Togethers_images')->where('togethers_id='.I("post.togethers_id"))->delete();
            $imgs = I('post.togethers_images');
            $imgs = array_filter($imgs);
            foreach ($imgs as $k => $val) {
                $img['image_url'] = $val;
                $img['togethers_id'] = I('post.togethers_id');
                $addtogetherimgs = M('Togethers_images')->where('togethers_id='.I("post.togethers_id"))->add($img);
            }
                // print_r($imgs);die();
            $return_arr = array(
                'status' => 1,
                'msg'   => '操作成功',                        
                'data'  => array('url'=>U('Admin/Tourism/togethersList')),
            );
            $this->ajaxReturn(json_encode($return_arr));
        }
        
        $togethersInfo = M('Togethers')->where('togethers_id='.I('GET.id',0))->find();
        // print_r($togethersInfo);
        $this->assign('togethersInfo',$togethersInfo);  // 商品详情            
        $togethersImages = M("togethers_images")->where('togethers_id ='.I('GET.id',0))->select();
        // print_r($togethersImages);die();
        $this->assign('togethersImages',$togethersImages);  // 商品相册
        $this->initEditor(); // 编辑器
        $this->display('togethers');                                 
    } 

    // 删除拼游信息
    public function deltogethers(){
        $cats = M('Togethers')->where('togethers_id='.I('id'))->delete();
        $referurl = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : U('Admin/travels/travelsList');
        if($cats){
            $this->success("操作成功",$referurl);
            exit();
        }else{
            $this->error("操作失败",$referurl);
        }
    }

    /**
     * 删除商品相册图
     */
    public function del_togethers_images()
    {
        $path = I('filename','');
        M('togethers_images')->where("image_url = '$path'")->delete();
    }
    

    // 平安保险
    function safetyList(){
        $safety = M("safety")->select();
        $this->assign('safetyList',$safety);
        $this->display();
    }

    /**
     *  搜索结伴出游
     */
    public function ajaxSafetyList(){       
        $keyword = I('post.key_word');
        $where = "title like '%$keyword%'" ;
        $model = M('Safety');
        $count = $model->where($where)->count();
        $Page  = new AjaxPage($count,10);
        $show = $Page->show();
        $safetyList = $model->where($where)->limit($Page->firstRow.','.$Page->listRows)->select();
        // print_r($togethersList);die();
        $this->assign('safetyList',$safetyList);
        $this->assign('page',$show);// 赋值分页输出
        $this->display();         
    }    

     /**
     * 添加修改拼游
     */
    public function addEditsafety(){                      
            //ajax提交验证
        if(($_GET['is_ajax'] == 1) && IS_POST)
        {            
            $data = I('post.');
            if (empty($data['add_time'])) {
                $data['add_time'] = time(); 
            }else{
                $data['add_time'] = strtotime($data['add_time']); 
            }
            if(I('post.id') == ""){
                $addsafety = M('safety')->add($data);
                $return_arr = array(
                    'status' => 1,
                    'msg'   => '操作成功',                        
                    'data'  => array('url'=>U('Admin/Tourism/safetyList')),
                );
                $this->ajaxReturn(json_encode($return_arr));
            }
            $savesafety = M('safety')->where('id='.I("post.id"))->save($data);
            
            $return_arr = array(
                'status' => 1,
                'msg'   => '操作成功',                        
                'data'  => array('url'=>U('Admin/Tourism/safetyList')),
            );
            $this->ajaxReturn(json_encode($return_arr));
        }
        
        $safetyInfo = M('safety')->where('id='.I('GET.id',0))->find();
        // print_r($safetyInfo);
        $this->assign('safetyInfo',$safetyInfo);  // 商品详情        
        $this->initEditor(); // 编辑器
        $this->display('safety');                                 
    } 
    

     public function safety(){
        $act = I('GET.act','add');
        // print_r($act);die();
        if($act == 'edit'){
            $cats = M('safety')->where('id='.I('get.id'))->find();
            $info = array();
            if(I('GET.id')){
               $id = I('GET.id');
               $info = D('safety')->where('id='.$id)->find();
               $start_tp = unserialize($info['start_tp']);
            }
        }
        if($act == 'del'){
            $cats = M('safety')->where('id='.I('get.id'))->delete();
            $referurl = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : U('Admin/safety/safetyList');
            if($cats){
                $this->success("操作成功",$referurl);
                exit();
            }else{
                $this->error("操作失败",$referurl);
            }
        }
        
        $region = M("region")->where('parent_id=0')->select();
        // print_r($region);die();
        $this->assign('region',$region);
        $this->assign('act',$act);
        $this->assign('info',$info);
        $this->assign('start_tp',$start_tp);
        $this->initEditor();
        $this->display();
    }


    // U旅游订单
    function orderList(){
        $order = M("lv_order")->order("order_id desc")->select();
        $this->assign('orderList',$order);
        $this->display();
    }

    /**
     *  搜索旅游线路
     */
    public function ajaxOrderList(){       
        $keyword = I('post.key_word');
        // $pay_status = I('post.pay_status');

        $condition = array();
        I('consignee') ? $condition['consignee'] = trim(I('consignee')) : false;
        if($begin && $end){
            $condition['add_time'] = array('between',"$begin,$end");
        }
        I('order_sn') ? $condition['order_sn'] = trim(I('order_sn')) : false;
        I('order_status') != '' ? $condition['order_status'] = I('order_status') : false;
        I('pay_status') != '' ? $condition['pay_status'] = I('pay_status') : false;
        I('go') != '' ? $condition['go'] = I('go') : false;
        // $where = "order_sn like '%$keyword%' or add_time like '%$keyword%' or pay_status like '%$keyword%'" ;   
        $model = M('lv_order');
        $count = $model->where($condition)->count();
        $Page  = new AjaxPage($count,10);
        $show = $Page->show();
        $orderList = $model->where($condition)->order("order_id desc")->limit($Page->firstRow.','.$Page->listRows)->select();

        foreach ($orderList as $ol => $ols) {
            $orderList[$ol]['message'] = unserialize($orderList[$ol]['message']);
            $start_time = $orderList[$ol]['start_time'];
            if(time() > $start_time){
                $dol['go'] = "1";
                M("lv_order")->where("order_id=".$orderList[$ol]['order_id'])->save($dol);
                $orderList[$ol]['go'] = "1";
            }
            if($ols['shop'] == "3"){
                $orderList[$ol]["title"] = M("lines")->where("lines_id=".$ols['id'])->getField("title");
            }elseif($ols['shop'] == "4"){
                $orderList[$ol]["title"] = M("benefits")->where("benefits_id=".$ols['id'])->getField("title");
            }elseif($ols['shop'] == "5"){
                $orderList[$ol]["title"] = M("Customs")->where("customs_id=".$ols['id'])->getField("address");
            }
        }
        $this->assign('orderList',$orderList);
        $this->assign('page',$show);// 赋值分页输出
        $this->display();         
    }    

    // 私人定制推荐线路
    public function recommend(){
        $address = I("address");
        $title = "title like '%$address%' OR city like '%$address%' " ;
        // 旅游线路
        $line = M("lines")->where($title)->select();
        // 限时优惠
        $benefit = M("benefits")->where($title)->select();
        // 结伴拼游
        $where = "address like '%$address%'";
        $together = M("togethers")->where($where)->select();

        $safety = M("safety")->select();
        $this->assign("line",$line);
        $this->assign("benefit",$benefit);
        $this->assign("together",$together);
        $this->assign("safety",$safety);
        $this->display();
    }

    // 私人定制添加订单
    public function addorder()
    {
        $safety = M("safety")->where("id=".I("safety_id"))->find();

        $line['start_time'] = I("start_time");
        // $person['cards'] = I("cards");
        // $person['tel'] = I("tel");
        // $usernames = array(
        //         "0"=>"张","1"=>"李",
        //     );
        // $cards = array(
        //         "0"=>"130625178965471236","1"=>"130625178965471226",
        //     );
        // $tels = array(
        //         "0"=>"13800138000","1"=>"13800138001",
        //     );
        $cards = I("card");
        $tels = I("tel");
        $usernames = I("username");
        foreach ($cards as $us => $name) {
            foreach ($tels as $t => $tel) {
                foreach ($usernames as $un => $name) {
                    $person['username'] = $usernames[$us];
                    $person['card'] = $cards[$us];
                    $person['tel'] = $tels[$us];
                    $persons[] = $person;
                    break;
                }
                break;
            }
        }

        // $user = M("users")->where("user_id=".I("user_id"))->find();
        // $users['nickname'] = $user['nickname'];
        // $data['user'] = $users;
        $data['order_sn'] = date('YmdHis').rand(1000,9999);
        $data['user_id'] = I("user_id");
        $data['adult'] = I("adult");
        $data['child'] = I("child");
        // shop---3:旅游线路 4：限时优惠 5：结伴拼游
        if (I("table") == "lines_id") {
            $data['shop'] = "3";
        }elseif (I("table") == "benefits_id") {
            $data['shop'] = "4"; 
        }elseif (I("table") == "togethers_id") {
            $data['shop'] = "5";
        }
        $data['id'] = I("ids");
        $data['message'] = serialize($persons);
        // $data['message'] = $persons;
        $data['difference'] = I("difference");
        $data['add_time'] = time();
        $data['start_time'] = strtotime(I('start_time'));
        $data['safety_id'] = I("safety_id");
        $data['price'] = I("start_price");
        $data['kefu'] = I("kefu");
        $data['go'] = "0";
        $data['status'] = "0";
        
        //总价=出行日期路价*成人数+儿童价*儿童数+房间差数*房间差价+（成人数+儿童数）*保险价格

        $data['total_amount'] = I("start_price") * I("adult") + (I("child_price") * I("child")) + I("difference") * $line['dif_price'] + (I("adult") + I("child")) * $safety['price'];
        $data['order_amount'] = $data['total_amount'];

        $ord['order_sn'] = $data['order_sn'];
        $ord['user_id'] = I("user_id");
        $ord['total_amount'] = $data['total_amount'];
        $ord['order_amount'] = $data['order_amount'];
        $ord['shop'] = $data['shop'];
        $ord['add_time'] = $data['add_time'];
        M("order")->add($ord);

        $id = M("order")->where("order_sn=".$data['order_sn']." AND user_id=".I("user_id"))->getField("order_id");
        $data['order_id'] = $id;
        $res = M("lv_order")->add($data);
        $referurl = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : U('Admin/Tourism/customsList');
        if($res){
            echo '<script type="text/javascript"> alert("操作成功"); window.close();</script>';
            // $this->success("操作成功",$referurl);
            exit();
        }else{
            $this->error("操作失败",$referurl);
        }
    }

    public function travelsList(){
        $travels = M("travels")->order("id desc")->select();
        $this->assign('travels',$travels);
        $this->display();
    }

    /**
     *  搜索结伴出游
     */
    public function ajaxTravelsList(){       
        $keyword = I('post.key_word');
        $where = "title like '%$keyword%'" ;
        $model = M('travels');
        $count = $model->where($where)->count();
        $Page  = new AjaxPage($count,10);
        $show = $Page->show();
        $travelsList = $model->where($where)->limit($Page->firstRow.','.$Page->listRows)->select();
        // print_r($travelsList);die();
        $this->assign('travelsList',$travelsList);
        $this->assign('page',$show);// 赋值分页输出
        $this->display();         
    }    


    // 限时优惠操作
     public function travels(){
        $act = I('GET.act','add');
        // print_r($act);die();
        if($act == 'edit'){
            $info = M('travels')->where('id='.I('get.id'))->find();
            $info['content'] = $info['title1'].$info['content1'].$info['image1'].$info['title2'].$info['content2'].$info['image2'].$info['title3'].$info['content3'].$info['image3'].$info['title4'].$info['content4'].$info['image4'];
        }
        if($act == 'del'){
            $cats = M('travels')->where('id='.I('get.id'))->delete();
            $referurl = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : U('Admin/travels/travelsList');
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

    // 添加限时优惠
    public function travelsHandle(){
        $data = I('post.');
        if($data['act'] == 'add'){
            $data["title"] = I("title");
            $data["nickname"] = I("nickname");
            $data["image"] = SITE_URL.I("image");
            $data["background"] = SITE_URL.I("image");
            $data["head_pic"] = SITE_URL."/Public/images/TP-shop_logo.png";
            $data["content1"] = I("content1");
            $data["add_time"] = time();
            $r = D('travels')->add($data);
        }
        
        if($data['act'] == 'edit'){
            $data["title"] = I("title");
            $data["nickname"] = I("nickname");
            $data["image"] = SITE_URL.I("image");
            $data["background"] = SITE_URL.I("image");
            $data["head_pic"] = SITE_URL."/Public/images/TP-shop_logo.png";
            $data["content1"] = I("content1");
            $r = D('travels')->where("id = ".I("id"))->save($data);
        }
        
        if($data['act'] == 'del'){
            $r = D('travels')->where('id='.$data['id'])->delete();
            if($r) exit(json_encode(1));        
        }
        $referurl = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : U('Admin/travels/travelsList');
        if($r){
            $this->success("操作成功",$referurl);
        }else{
            $this->error("操作失败",$referurl);
        }
    }





    //存放无限分类
    static public $treeList = array(); 
    public function tree(&$data,$parentid = 0,$level = 0,$sign=' ˉ┗  ') {
        foreach ($data as $key => $value){
            if($value['parent_id']==$parentid){
                $value['level']=$level+1;
                if($value['parent_id'] == 0){
                    $value['sign'] = '┊';
                }else{$value['sign']=str_repeat('┊',$value['level']-1).$sign;}
                self::$treeList []=$value;                
                self::tree($data,$value['id'],$level+1,$sign);
            }
        }
        $arr = self::$treeList;
        return $arr ;
    }


}