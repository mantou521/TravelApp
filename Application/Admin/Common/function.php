<?php

/**
 * 管理员操作记录
 * @param $log_url 操作URL
 * @param $log_info 记录信息
 */
function adminLog($log_info){
    $add['log_time'] = time();
    $add['admin_id'] = session('admin_id');
    $add['log_info'] = $log_info;
    $add['log_ip'] = getIP();
    $add['log_url'] = __ACTION__;
    M('admin_log')->add($add);
}


function getAdminInfo($admin_id){
	return D('admin')->where("admin_id=$admin_id")->find();
}

function tpversion()
{     
    if(!empty($_SESSION['isset_push']))
        return false;    
    $_SESSION['isset_push'] = 1;    
    error_reporting(0);//关闭所有错误报告
    $app_path = dirname($_SERVER['SCRIPT_FILENAME']).'/';
    $version_txt_path = $app_path.'/Application/Admin/Conf/version.txt';
    $curent_version = file_get_contents($version_txt_path);
    
    $vaules = array(            
            'domain'=>$_SERVER['HTTP_HOST'], 
            'last_domain'=>$_SERVER['HTTP_HOST'], 
            'key_num'=>$curent_version, 
            'install_time'=>INSTALL_DATE, 
            'cpu'=>'0001',
            'mac'=>'0002',
            'serial_number'=>SERIALNUMBER,
            );     
     $url = "http://service.tp-shop.cn/index.php?m=Home&c=Index&a=user_push&".http_build_query($vaules);
     stream_context_set_default(array('http' => array('timeout' => 3)));
     file_get_contents($url);       
}
 
/**
 * 面包屑导航  用于后台管理
 * 根据当前的控制器名称 和 action 方法
 */
function navigate_admin()
{        
    $navigate = include APP_PATH.'Common/Conf/navigate.php';    
    $location = strtolower('Admin/'.CONTROLLER_NAME);
    $arr = array(
        '后台首页'=>'javascript:void();',
        $navigate[$location]['name']=>'javascript:void();',
        $navigate[$location]['action'][ACTION_NAME]=>'javascript:void();',
    );
    return $arr;
}

/**
 * 导出excel
 * @param $strTable	表格内容
 * @param $filename 文件名
 */
function downloadExcel($strTable,$filename)
{
	header("Content-type: application/vnd.ms-excel");
	header("Content-Type: application/force-download");
	header("Content-Disposition: attachment; filename=".$filename."_".date('Y-m-d').".xls");
	header('Expires:0');
	header('Pragma:public');
	echo '<html><meta http-equiv="Content-Type" content="text/html; charset=utf-8" />'.$strTable.'</html>';
}

/**
 * 格式化字节大小
 * @param  number $size      字节数
 * @param  string $delimiter 数字和单位分隔符
 * @return string            格式化后的带单位的大小
 */
function format_bytes($size, $delimiter = '') {
	$units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
	for ($i = 0; $size >= 1024 && $i < 5; $i++) $size /= 1024;
	return round($size, 2) . $delimiter . $units[$i];
}

/**
 * 根据id获取地区名字
 * @param $regionId id
 */
function getRegionName($regionId){
    $data = M('region')->where(array('id'=>$regionId))->field('name')->find();
    return $data['name'];
}

function getMenuList($act_list){
	//根据角色权限过滤菜单
	$menu_list = getAllMenu();
	if($act_list != 'all'){
		$right = M('system_menu')->where("id in ($act_list)")->cache(true)->getField('right',true);
		foreach ($right as $val){
			$role_right .= $val.',';
		}
		$role_right = explode(',', $role_right);		
		foreach($menu_list as $k=>$mrr){
			foreach ($mrr['sub_menu'] as $j=>$v){
				if(!in_array($v['control'].'Controller@'.$v['act'], $role_right)){
					unset($menu_list[$k]['sub_menu'][$j]);//过滤菜单
				}
			}
		}
	}
	return $menu_list;
}

function getAllMenu(){
	return	array(
		'system' => array('name'=>'系统设置','icon'=>'fa-cog','sub_menu'=>array(
				array('name'=>'网站设置','act'=>'index','control'=>'System'),
				array('name'=>'友情链接','act'=>'linkList','control'=>'Article'),
				array('name'=>'自定义导航','act'=>'navigationList','control'=>'System'),
				array('name'=>'区域管理','act'=>'region','control'=>'Tools'),
				array('name'=>'权限资源列表','act'=>'right_list','control'=>'System'),
		)),
		'access' => array('name' => '权限管理', 'icon'=>'fa-gears', 'sub_menu' => array(
				array('name' => '管理员列表', 'act'=>'index', 'control'=>'Admin'),
				array('name' => '角色管理', 'act'=>'role', 'control'=>'Admin'),
				array('name' => '供应商管理', 'act'=>'supplier', 'control'=>'Admin'),
				array('name' => '管理员日志', 'act'=>'log', 'control'=>'Admin'),
		)),
		'member' => array('name'=>'会员管理','icon'=>'fa-user','sub_menu'=>array(
				array('name'=>'会员列表','act'=>'index','control'=>'User'),
				array('name'=>'会员等级','act'=>'levelList','control'=>'User'),
				//array('name'=>'充值和提现','act'=>'account','control'=>'User'),
				//array('name'=>'会员整合','act'=>'integrate','control'=>'User'),
		)),
		'banner' => array('name' => 'Banner', 'icon'=>'fa-book', 'sub_menu' => array(
				array('name' => 'banner', 'act'=>'bannersList', 'control'=>'Banners'),
				// array('name' => '旅游线路', 'act'=>'linesList', 'control'=>'Banners'),
				// array('name' => '私人定制', 'act'=>'customsList', 'control'=>'Banners'),
				// array('name' => '关于我们', 'act'=>'about_travel', 'control'=>'Banners'),
		)),
		'content' => array('name' => 'UU头条', 'icon'=>'fa-comments', 'sub_menu' => array(
				array('name' => '头条列表', 'act'=>'articleList', 'control'=>'Article'),
		)),
		'goods' => array('name' => '游换购', 'icon'=>'fa-book', 'sub_menu' => array(
				array('name' => '换购简介', 'act'=>'introductions', 'control'=>'Goods'),
				array('name' => '换购说明', 'act'=>'article', 'control'=>'Goods'),
				array('name' => '商品分类', 'act'=>'categoryList', 'control'=>'Goods'),
				array('name' => '商品列表', 'act'=>'goodsList', 'control'=>'Goods'),
				array('name' => '换购专区', 'act'=>'hgList', 'control'=>'Goods'),
				array('name' => '商品类型', 'act'=>'goodsTypeList', 'control'=>'Goods'),
				array('name' => '商品规格', 'act' =>'specList', 'control' => 'Goods'),
				array('name' => '商品属性', 'act'=>'goodsAttributeList', 'control'=>'Goods'),
				array('name' => '品牌列表', 'act'=>'brandList', 'control'=>'Goods'),
				array('name' => '商品评论','act'=>'index','control'=>'Comment'),
				array('name' => '商品咨询','act'=>'ask_list','control'=>'Comment'),
		)),
		'order' => array('name' => '游换购订单', 'icon'=>'fa-money', 'sub_menu' => array(
				array('name' => '订单列表', 'act'=>'index', 'control'=>'Order'),
				array('name' => '发货单', 'act'=>'delivery_list', 'control'=>'Order'),
				array('name' => '快递单', 'act'=>'express_list', 'control'=>'Order'),
				array('name' => '退货单', 'act'=>'return_list', 'control'=>'Order'),
				// array('name' => '添加订单', 'act'=>'add_order', 'control'=>'Order'),
				array('name' => '订单日志', 'act'=>'order_log', 'control'=>'Order'),
		)),
		'books' => array('name' => 'UU册', 'icon'=>'fa-book', 'sub_menu' => array(
			    array('name' => '简介', 'act'=>'introductions', 'control'=>'Books'),
				array('name' => 'UU册列表', 'act'=>'booksList', 'control'=>'Books'),
				array('name' => '关于我们', 'act'=>'article', 'control'=>'Books'),
				array('name' => '定制UU册', 'act'=>'booksTypeList', 'control'=>'Books'),
				array('name' => '留言板', 'act' =>'feedbackList', 'control' => 'Books'),
		)),
		'tourism' => array('name' => 'U旅行', 'icon'=>'fa-book', 'sub_menu' => array(
				array('name' => '简介', 'act'=>'introductions', 'control'=>'Tourism'),
				array('name' => '新闻中心', 'act'=>'newsList', 'control'=>'Tourism'),
				array('name' => '旅游线路', 'act'=>'linesList', 'control'=>'Tourism'),
				array('name' => '私人定制', 'act'=>'customsList', 'control'=>'Tourism'),
				array('name' => '线路目的地', 'act'=>'addressList', 'control'=>'Tourism'),
				array('name' => '关于我们', 'act'=>'about_travel', 'control'=>'Tourism'),
				array('name' => '限时优惠', 'act'=>'benefitsList', 'control'=>'Tourism'),
				array('name' => '联系我们', 'act'=>'contact_us', 'control'=>'Tourism'),
				array('name' => '结伴拼游', 'act'=>'togethersList', 'control'=>'Tourism'),
				array('name' => '环球游记', 'act'=>'travelsList', 'control'=>'Tourism'),
				array('name' => 'U旅游订单', 'act'=>'orderList', 'control'=>'Tourism'),
				array('name' => '保险', 'act'=>'safetyList', 'control'=>'Tourism'),
		)),

		'ticket' => array('name' => 'U机票', 'icon'=>'fa-comments', 'sub_menu' => array(
				array('name' => '简介', 'act'=>'introductions', 'control'=>'Ticket'),
		)),

		'planning' => array('name' => 'U企划', 'icon'=>'fa-comments', 'sub_menu' => array(
				array('name' => '简介', 'act'=>'introductions', 'control'=>'Planning'),
				array('name' => '关于我们', 'act'=>'article', 'control'=>'Planning'),
				array('name' => '策划实战', 'act'=>'planList', 'control'=>'Planning'),
				array('name' => '专家团队', 'act'=>'expertList', 'control'=>'Planning'),
				array('name' => '联系我们', 'act'=>'lianxi', 'control'=>'Planning'),
		)),

		'recreation' => array('name' => 'U娱乐', 'icon'=>'fa-comments', 'sub_menu' => array(
				array('name' => '简介', 'act'=>'introductions', 'control'=>'Recreation'),
		)),

		'Kefu' => array('name' => '客服专线', 'icon'=>'fa-comments', 'sub_menu' => array(
				array('name' => '客服专线', 'act'=>'shopkefuList', 'control'=>'Kefu'),
				// array('name' => 'UU册客服', 'act'=>'bookkefu', 'control'=>'Kefu'),
				// array('name' => 'U旅行客服', 'act'=>'linekefu', 'control'=>'Kefu'),
		)),
		
		// 'guest' => array('name' => 'U创客','icon' => 'fa-comments','sub_menu' => array(
		// 		array('name' => '创业新闻','act'=>'cknewsList','control'=>'Guest'),
		// 		array('name' => '待审核','act'=>'audit','control'=>'Guest'),
		// 		array('name' => '案例','act'=>'case','control'=>'Guest'),
		// 		// array('name' => '项目广场','act'=>'case','control'=>'Guest'),
		// 		// array('name' => '即将开始','act'=>'case','control'=>'Guest'),
		// 		array('name' => '联系我们','act'=>'ck_contact','control'=>'Guest'),

		// )),
		
	);
}


function respose($res){
	exit(json_encode($res));
}