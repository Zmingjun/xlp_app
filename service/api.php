<?php 
    header("content-type:text/html;charset=utf-8");
	require_once 'common/mysql_class.php';
	require_once 'common/action_class.php';
	require_once "common/jssdk.php";
	require_once 'php_sdk/mns-autoloader.php';
	require_once "php_sdk/autoload.php";
	require_once "php_sdk/OSSconfig.php";
	require_once "php_sdk/weixin_autoload.php";
    require_once 'php_sdk/api_sdk/vendor/autoload.php';
    require_once 'php_sdk/api_sdk/lib/Core/Config.php';
	session_start();
	header('Access-Control-Allow-Origin:*'); 
	$type=isset($_REQUEST['type'])?$_REQUEST['type']:1;
    
	
	switch ($type){
		case 10000:   
	    //根据code获取用户openid 
	    weixin();
		break;
		case 10001:
		//获取全部活动列表
		 $catid=isset($_GET['catid'])?$_GET['catid']:'';
		 get($catid);
		break;
		case 10002:
		//根据actid获取活动详细内容
		 $actid=isset($_GET['actid'])?$_GET['actid']:'';
		 $openid=isset($_GET['openid'])?$_GET['openid']:'';
		 get_actinfo($actid,$openid);
		break;
		case 10003:
		//根据活动id 用户id判断用户是否已参加活动
		 $actid=isset($_GET['actid'])?$_GET['actid']:'';
		 $openid=isset($_GET['openid'])?$_GET['openid']:'';
		 act_user($actid,$openid);
		break;
		case 10004:
		//活动报名
		$openid=isset($_POST['openid'])?$_POST['openid']:'';
		$actid=isset($_POST['actid'])?$_POST['actid']:'';
		$nickname=isset($_POST['nickname'])?$_POST['nickname']:'';
		$phone=isset($_POST['phone'])?$_POST['phone']:'';
		put_pay_user($openid,$actid,$nickname,$phone);
		break;
		//学分报名
		case 10094:
		$price = isset($_POST['price'])?$_POST['price']:'';
		$winmoney = isset($_POST['winmoney'])?$_POST['winmoney']:'';
		$openid = isset($_POST['openid'])?$_POST['openid']:'';
		$actid = isset($_POST['actid'])?$_POST['actid']:'';
		winmoney_payorder($price,$winmoney,$openid,$actid);
		break;
		//删除已结束活动
		case 100094:
		$actid = isset($_GET['actid'])?$_GET['actid']:'';
		$id = isset($_GET['jid'])?$_GET['jid']:'';
		delete_endact($id,$actid);
		break;		
		case 10005:
		//根据openid列出当前用户参加的活动
		$openid=isset($_GET['openid'])?$_GET['openid']:'';
		$acttype=isset($_GET['acttype'])?$_GET['acttype']:'1';
		get_act_user($openid,$acttype);
		break;
		case 10006:
		//根据openid列出当前用户可签到的活动
		$openid=isset($_GET['openid'])?$_GET['openid']:'';
		get_act_start($openid);
		break;
		case 10007:
		//根据openid，actid,lbs用户签到
		$openid=isset($_GET['openid'])?$_GET['openid']:'';
		$actid=isset($_GET['actid'])?$_GET['actid']:'';
		$lbs_x=isset($_GET['lbs_x'])?$_GET['lbs_x']:'';
		$lbs_y=isset($_GET['lbs_y'])?$_GET['lbs_y']:'';
		put_act_sign($openid,$actid,$lbs_x,$lbs_y);
		break;
		case 10008:
		//根据openid列出当前用户参加的活动进行中的状态
		$openid=isset($_GET['openid'])?$_GET['openid']:'';
		$lbs_x=isset($_GET['lbs_x'])?$_GET['lbs_x']:'';
		$lbs_y=isset($_GET['lbs_y'])?$_GET['lbs_y']:'';
		get_act_msg($openid,$lbs_x,$lbs_y);
		break;
		case 10009:
		//队伍打分
		$openid=isset($_GET['openid'])?$_GET['openid']:'';
		$actid=isset($_GET['actid'])?$_GET['actid']:'';
		$marknum=isset($_GET['marknum'])?$_GET['marknum']:'';
		$tem=isset($_GET['tem'])?$_GET['tem']:'';
		$mark=isset($_GET['mark'])?$_GET['mark']:'0';
		put_tro_mark($openid,$actid,$marknum,$tem,$mark);
		break;
		//项目比赛排名
		case 10505:
		$actid=isset($_GET['actid'])?$_GET['actid']:'';
		$tem=isset($_GET['tem'])?$_GET['tem']:'';
		$rank = isset($_GET['rank'])?$_GET['rank']:'';
		$pro_name =isset($_GET['pro_name'])?$_GET['pro_name']:'';
		put_tream_mark($actid,$rank,$pro_name);
		break;
		case 10010:
		//队伍积分排行
	    $actid=isset($_GET['actid'])?$_GET['actid']:'';
	    get_ranking($actid);
		break;
		case 10011:
		//创建活动
	    put_act();
		break;
		case 10012:
		//jssdk 返回   
	    get_jssdk();
		break;
		case 10013:
		//返回分类   
	    get_actcat();
		break;
		case 10014:
		//返回全部活动列表
		$id = isset($_GET['id'])?$_GET['id']:'';
		$page = isset($_GET['page'])?$_GET['page']:'';   
	    get_actlist($page,$id);
		break;
		case 10015:
		//修改活动_返回活动数据 
		$actid=isset($_GET['actid'])?$_GET['actid']:'';  
	    update_act($actid);
		break;
		case 10016:
		//删除活动
		$actid=isset($_GET['actid'])?$_GET['actid']:'';
	    del_act($actid);
		break;
		case 10017:
		//回收站
	    get_recycle();
		break;
		case 10018:
		//修改活动_修改活动数据
		$actid=isset($_GET['actid'])?$_GET['actid']:''; 
	    up_act($actid);
		break;
		case 10019:   
	    //上传图片
	    $myFile=isset($_FILES['imgurl'])?$_FILES['imgurl']:'';	              
	    put_file();
		break;
		//上传图片
		case 10095:
        up_file($accessKeyId,$accessKeySecret,$endpoint,$bucket);
		break;
		//获取阿里云OSS签名
		case 10098:
		upload_img($accessKeyId,$accessKeySecret,$endpoint,$bucket);
		break;
		//删除阿里云OSS图片
		case 10099:
		$img_name = isset($_GET['img_name'])?$_GET['img_name']:''; 
        delete_img($accessKeyId,$accessKeySecret,$endpoint,$bucket,$img_name);
		break;
		case 10020:
		//个人打分
		$openid=isset($_GET['openid'])?$_GET['openid']:'';
		$actid=isset($_GET['actid'])?$_GET['actid']:'';
		$userid=isset($_GET['userid'])?$_GET['userid']:'';
		$mark=isset($_GET['mark'])?$_GET['mark']:'0';
		put_user_mark($openid,$actid,$userid,$mark);
		break;
		case 10021:
		//获取个人信息
		$openid=isset($_GET['openid'])?$_GET['openid']:'';
		//$token=isset($_GET['token'])?$_GET['token']:'';
		get_user($openid);
		break;
		case 10022:
		//获取活动签到前十名
		$actid=isset($_GET['actid'])?$_GET['actid']:'';
		get_qiandao($actid);
		break;
		case 10023:
		//当前用户所在队伍积分。
		$actid=isset($_GET['actid'])?$_GET['actid']:'';
		$openid=isset($_GET['openid'])?$_GET['openid']:'';
		get_actsum($openid,$actid);
		break;
		//列出职位和竞选者
		case 10055:
		$actid = isset($_GET['actid'])?$_GET['actid']:'';
		$id = isset($_GET['id'])?$_GET['id']:'';
        get_electordetail($actid,$id);
		break; 
		//获取用户竞选的详细信息
		case 10024:
		//判断当前活动是否可以进行交换队伍。
		$actid=isset($_GET['actid'])?$_GET['actid']:'';
		$openid=isset($_GET['openid'])?$_GET['openid']:'';
		$useridd=isset($_GET['userid'])?$_GET['userid']:'';
		date_troops($openid,$useridd,$actid);
		break;
		case 10025:
		//队伍更换
		$actid=isset($_GET['actid'])?$_GET['actid']:'';
		$openid=isset($_GET['openid'])?$_GET['openid']:'';
		$userid=isset($_GET['userid'])?$_GET['userid']:'';
		$states=isset($_GET['states'])?$_GET['states']:'';
		up_troops($openid,$userid,$actid,$states);
		break;
		//判断是否是队长
		case 10067:
		$actid = isset($_GET['actid'])?$_GET['actid']:'';
		$tem = isset($_GET['tem'])?$_GET['tem']:'';
		$user_id = isset($_GET['user_id'])?$_GET['user_id']:'';
        check_captain($actid,$tem,$userid);
        break;
        //队长给成员打分
        case 10068:
        $userid = isset($_POST['userid'])?$_POST['userid']:'';
        $actid = isset($_POST['actid'])?$_POST['actid']:'';
        $rank = isset($_POST['rank'])?$_POST['rank']:'';
        $tem = isset($_POST['tem'])?$_POST['tem']:'';
        $marknum = isset($_POST['marknum'])?$_POST['marknum']:'';
        put_member_mark($userid,$rank,$actid,$tem,$marknum);
        break;
        //列出该活动的所有职位
        case 10069:
        $actid = isset($_GET['actid'])?$_GET['actid']:'';
        get_jobname($actid);
        break;
        //用户成为队长
        case 10065:
        $actid = isset($_GET['actid'])?$_GET['actid']:'';
        $userid = isset($_GET['userid'])?$_GET['userid']:'';
        $tem = isset($_GET['tem'])?$_GET['tem']:'';
        up_captain($actid,$userid,$tem);
        break;
        //查看队伍成员
        case 10071:
        $actid = isset($_GET['actid'])?$_GET['actid']:'';
        $tem = isset($_GET['tem'])?$_GET['tem']:'';
        check_trem_member($actid,$tem);
        break;
        //列出团队打分成员
        case 10072:
        $actid = isset($_GET['actid'])?$_GET['actid']:'';
        $tem = isset($_GET['tem'])?$_GET['tem']:'';
        put_trem_member($actid,$tem);
        break;
        //查询队伍分数
        case 10073:                                                                                             
        $actid = isset($_GET['actid'])?$_GET['actid']:'';
        $tem = isset($_GET['tem'])?$_GET['tem']:'';
        get_temtotal($actid,$tem);
        break;
        //查询队长详细信息
        case 10074:
        $tem = isset($_GET['tem'])?$_GET['tem']:'';
        $actid = isset($_GET['actid'])?$_GET['actid']:'';
        get_captain_info($tem,$actid);
        //获取加油用户列表
        case 10076:
        $actid = isset($_GET['actid'])?$_GET['actid']:'';
        $openid = isset($_GET['openid'])?$_GET['openid']:'';
        check_cheer($actid,$openid);
        break;
		case 10026:
		//读取首页广告
		$ad_type=isset($_GET['ad_type'])?$_GET['ad_type']:'';
		get_adhome($ad_type);
		break;
		//使用redis
		case 100000:
		get_redis();
		break;
		case 10027:
		//验证是否是活动管理员
		$actid=isset($_GET['actid'])?$_GET['actid']:'';
		$openid=isset($_GET['openid'])?$_GET['openid']:'';
		admin_act($actid,$openid);
		break;
		case 'actadmin':
		//活动管理员控制
		$actid=isset($_GET['actid'])?$_GET['actid']:'';
		$openid=isset($_GET['openid'])?$_GET['openid']:'';
		$adminsta=isset($_GET['adminsta'])?$_GET['adminsta']:'';
		$id = isset($_GET['id'])?$_GET['id']:'';
		$grade=isset($_GET['grade'])?$_GET['grade']:'';
		admin_act_sta($actid,$openid,$adminsta,$id,$grade);
		break;
		case 10028:   
	    //验证openid是否已经关注公众号
	    $openid=isset($_GET['openid'])?$_GET['openid']:'';
	    veopenid($openid);
	    break;
	    case 10029:   
	    //徒步记录
	    $openid=isset($_GET['openid'])?$_GET['openid']:'';
	    $actid=isset($_GET['actid'])?$_GET['actid']:'';
	    $sta=isset($_GET['sta'])?$_GET['sta']:'';
	    put_foot($openid,$actid,$sta);
	    break;
	    //个人打分排名
	    case 10088:
	    $actid = isset($_GET['actid'])?$_GET['actid']:'';
        get_user_ranking($actid);
	    break;
	    case 10030:   
	    //读取商品分类
	    get_goodstype();
	    break;
	    case 10031:   
	    //读取商品列表
	    $gtypeid=isset($_GET['gtypeid'])?$_GET['gtypeid']:'';
	    $gtype=isset($_GET['gtype'])?$_GET['gtype']:'';
	    $num=isset($_GET['num'])?$_GET['num']:'';
	    get_goods($gtypeid,$gtype,$num);
	    break;
	    case 10032:   
	    //读取商品详情
	    $goodsid=isset($_GET['goodsid'])?$_GET['goodsid']:'';
	    get_gcontent($goodsid);
	    break;
	    //推荐商品
	    case 10050:
	    get_recommend_goods();
	    break;
	    //添加收货地址
	    case 10060:
	     $address = isset($_GET['address'])?$_GET['address']:'';
	     $openid  = isset($_GET['openid'])?$_GET['openid']:'';
	     $consignee = isset($_GET['consignee'])?$_GET['consignee']:'';
	     $province = isset($_GET['province'])?$_GET['province']:'';
	     $city = isset($_GET['city'])?$_GET['city']:'';
	     $district = isset($_GET['district'])?$_GET['district']:'';
	     $address = isset($_GET['address'])?$_GET['address']:'';
	     $zipcode = isset($_GET['zipcode'])?$_GET['zipcode']:'';
	     $tel = isset($_GET['tel'])?$_GET['tel']:'';
	     $default = isset($_GET['default'])?$_GET['default']:'';
	     add_address($openid,$consignee,$province,$city,$district,$address,$zipcode,$tel,$default);
	    break;
	    //编辑收货地址
	    case 10061:
	     $address_id = isset($_GET['address_id'])?$_GET['address_id']:'';
	     $address = isset($_GET['address'])?$_GET['address']:'';
	     $openid  = isset($_GET['openid'])?$_GET['openid']:'';
	     $consignee = isset($_GET['consignee'])?$_GET['consignee']:'';
	     $province = isset($_GET['province'])?$_GET['province']:'';
	     $city = isset($_GET['city'])?$_GET['city']:'';
	     $district = isset($_GET['district'])?$_GET['district']:'';
	     $address = isset($_GET['address'])?$_GET['address']:'';
	     $zipcode = isset($_GET['zipcode'])?$_GET['zipcode']:'';
	     $tel = isset($_GET['tel'])?$_GET['tel']:'';
	     $default = isset($_GET['default'])?$_GET['default']:'';
	     edit_address($address_id,$openid,$consignee,$province,$city,$district,$address,$zipcode,$tel,$default);
	     break;
	    //删除收货地址
	    case 10062:
	     $id = isset($_GET['id'])?$_GET['id']:'';
         del_address($id);
         break;
         //获取用户学分
        case 10063:
         $openid = isset($_GET['openid'])?$_GET['openid']:'';
         get_winmoney($openid);
         break;
         //更新默认地址
        case 10070:
         $address_id = isset($_GET['address_id'])?$_GET['address_id']:'';
         $openid = isset($_GET['openid'])?$_GET['openid']:'';
         update_address($address_id,$openid);
         break;	  
	    //插入订单
        case 100015:
		 $openid  = isset($_POST['openid'])?$_POST['openid']:'';
		 $order_status = isset($_POST['order_status'])?$_POST['order_status']:'';            
		 $shipping_status = isset($_POST['shipping_status'])?$_POST['shipping_status']:'';
		 $pay_status = isset($_POST['pay_status'])?$_POST['pay_status']:'';
		 $consignee = isset($_POST['consignee'])?$_POST['consignee']:'';
		 $tel = isset($_POST['tel'])?$_POST['tel']:'';
		 $province = isset($_POST['province'])?$_POST['province']:'';
		 $city = isset($_POST['city'])?$_POST['city']:'';
		 $district = isset($_POST['district'])?$_POST['district']:'';
		 $address = isset($_POST['address'])?$_POST['address']:'';
		 $goods_amount = isset($_POST['goods_amount'])?$_POST['goods_amount']:'';
		 $add_time = isset($_POST['add_time'])?$_POST['add_time']:'';
		 $zipcode = isset($_POST['zipcode'])?$_POST['zipcode']:'';
		 $goods_id = isset($_POST['goods_id'])?$_POST['goods_id']:'';
		 $goods_name = isset($_POST['goods_name'])?$_POST['goods_name']:'';
		 $goods_number = isset($_POST['goods_number'])?$_POST['goods_number']:'';
		 $send_num = isset($_POST['send_num'])?$_POST['send_num']:'';
         $is_real = isset($_POST['is_real'])?$_POST['is_real']:'';
		 $goods_price = isset($_POST['goods_price'])?$_POST['goods_price']:'';
		 add_orders($openid,$order_status,$shipping_status,$pay_status,$consignee,$tel,$province,$city,$district,$address,$goods_amount,$add_time,$zipcode,$goods_id,$goods_name,$goods_number,$send_num,$is_real,$goods_price);
		 break;
		//查看订单
		case 100017:
         $openid = isset($_GET['openid'])?$_GET['openid']:'';
         get_user_orders($openid);
         break;
		//支付完成时更新订单
		case 100016:
         $openid  = isset($_GET['openid'])?$_GET['openid']:'';
         $goods_name = isset($_GET['goods_name'])?$_GET['goods_name']:'';
         $total = isset($_GET['total'])?$_GET['total']:'';
         $winmoney = isset($_GET['winmoney'])?$_GET['winmoney']:'';
         $pay_mode = isset($_GET['pay_mode'])?$_GET['pay_mode']:'';
         update_order($openid,$goods_name,$total,$winmoney,$pay_mode);
         break;
        //获取未完成订单
        case 100018:
         $openid = isset($_GET['openid'])?$_GET['openid']:'';
         get_unfinished_orders($openid);
         break;
        //获取完成的订单
        case 100019:
         $openid = isset($_GET['openid'])?$_GET['openid']:'';
         get_finished_orders($openid); 
         break;
        //获取已过期的订单
        case 100020:
         $openid = isset($_GET['openid'])?$_GET['openid']:'';
         get_overdue_orders($openid);
         break;
	    //读取收货地址列表
	    case 10051:
         $openid = isset($_GET['openid'])?$_GET['openid']:'';
         $is_default = isset($_GET['is_default'])?$_GET['is_default']:'0';
	     get_address($openid,$is_default);
	    break;
	    //读取单条地址
	    case 10052:
	      $id = isset($_GET['id'])?$_GET['id']:'';
	      get_oneaddres($id);
	      break;
	    //列出所有积分记录
	    case 10053:
          $openid = isset($_GET['openid'])?$_GET['openid']:'';
          $actid = isset($_GET['actid'])?$_GET['actid']:'';
          get_winmoney_log($openid,$actid);
          break;
        //兑换物品时扫描二维码
        case 10054:
          $order_sn = isset($_GET['order_sn'])?$_GET['order_sn']:'';
          exchange_update($order_sn);
          break;	       
	    case 10033:   
	    //读出用户用户名和所属队伍
	     $userid=isset($_GET['openid'])?$_GET['openid']:'';
	     $actid=isset($_GET['actid'])?$_GET['actid']:'';
	     get_qianuser($actid,$userid);
	     break;
	    case 10034:   
	    //读出活动管理状态
	    $openid=isset($_GET['openid'])?$_GET['openid']:'';
	    $actid=isset($_GET['actid'])?$_GET['actid']:'';
	    get_actsta($actid,$openid);
	    break;
	    case 10035:   
	    //app发送验证码
	    new Config();
        Config::load();
	    $phonekey=isset($_GET['udid'])?$_GET['udid']:'';
	    $phone=isset($_GET['phone'])?$_GET['phone']:'';
	    zuce_user($phonekey,$phone);
	    break;
	    case 10036:
	    //注册账号
	    $password=isset($_GET['password'])?$_GET['password']:'';
	    $phone=isset($_GET['phone'])?$_GET['phone']:'';
	    $smscode=isset($_GET['smscode'])?$_GET['smscode']:'';
	    put_userinfo($phone,$password,$smscode);
	    break;
	    case 10037:
	    //手机登录
	    $udid=isset($_GET['udid'])?$_GET['udid']:'';
	    $password=isset($_GET['password'])?$_GET['password']:'';
	    $phone=isset($_GET['phone'])?$_GET['phone']:'';
	    login_user($phone,$password,$udid);
	    break;
	    case 10038:
	    //微信登录
	    $udid=isset($_GET['udid'])?$_GET['udid']:'';
	    $openid=isset($_GET['openid'])?$_GET['openid']:'';
	    login_wuser($openid,$udid);
	    break;
	    //使用微信支付
		case 10097:
		$openid = isset($_GET['openid'])?$_GET['openid']:'';
		$actid = isset($_GET['actid'])?$_GET['actid']:'';
		$total = isset($_GET['total'])?$_GET['total']:'';
		weixin_pay($openid,$actid,$total);
		break;
		//微信支付成功后回调接口
		case 10096:
		$openid = isset($_GET['openid'])?$_GET['openid']:'';
		$actid = isset($_GET['actid'])?$_GET['actid']:'';
		weixin_success_pay($openid,$actid);
		//删除个人的学客圈
		case 100093:
		$id = isset($_GET['id'])?$_GET['id']:'';
        delete_ccl($id);
		break;
	    case 10039:
	    //下订单
	    $openid=isset($_GET['openid'])?$_GET['openid']:'';
	    
	    break;
	    case 10040:
		//输出服务器信息
		$http=getallheaders();
		/*foreach ($http as $k => $v) {
			echo "$k : $v<br>";
		}*/
		print_r($http);
		break;
		case 10041:
	    //更改头像
	    $url=isset($_GET['url'])?$_GET['url']:'';
	    $token=isset($_GET['token'])?$_GET['token']:'';
	    up_imgurl($token,$url);
	    break;
	    case 10042:
	    //退出登录
	    $token=isset($_GET['token'])?$_GET['token']:'';
	    login_out($token);
	    break;
	    case 10043:
	    //输出学圈列表
	    $openid = isset($_GET['openid'])?$_GET['openid']:'';
	    cll_list($openid);
	    break;
	    case 10044:
	    //输出学圈详情
	    $id=isset($_GET['id'])?$_GET['id']:'';
	    $openid=isset($_GET['openid'])?$_GET['openid']:'';
	    cll_content($id,$openid);
	    break;
	    //输出用户发表过的学客圈
	    case 10090:
	    $openid = isset($_GET['openid'])?$_GET['openid']:'';
        cll_get_usercll($openid);
        break;
	    case 10045:
	    //学客圈发表
	    $openid=isset($_POST['openid'])?$_POST['openid']:'';
	    $content=isset($_POST['content'])?$_POST['content']:'';
	    $thumb=isset($_POST['thumb'])?$_POST['thumb']:'';
	    cll_put_list($openid,$content,$thumb);
	    break;
	    case 10046:
	    //学客圈评论
	    $openid=isset($_GET['openid'])?$_GET['openid']:'';
	    $content=isset($_GET['content'])?$_GET['content']:'';
	    $id=isset($_GET['id'])?$_GET['id']:'';
	    cll_put_rew($openid,$content,$id);
	    break;
	    case 10047:
	    //学客圈点赞
	    $openid=isset($_GET['openid'])?$_GET['openid']:'';
	    $id=isset($_GET['id'])?$_GET['id']:'';
	    cll_put_like($openid,$id);
	    break;
	    //学客圈评论列表
	   	case 10048:
        $id = isset($_GET['id'])?$_GET['id']:'';
        cll_get_rew($id);
        break;
        //转发学客圈
        case 10049:
        $id = isset($_GET['id'])?$_GET['id']:'';
        $openid = isset($_GET['openid'])?$_GET['openid']:'';
        cll_smit($id,$openid);
        break;
        //查看落选人数
        case 10075:
        $actid = isset($_GET['actid'])?$_GET['actid']:'';
        check_unsuccessful($actid);
        break;
        //竞选结束
        case 10077:
        $actid = isset($_GET['actid'])?$_GET['actid']:'';
        ready_allocation($actid);
        break;
        //查看为用户点赞的人
        case 10078:
        $id = isset($_GET['id'])?$_GET['id']:'';
        check_like($id);
        break;
        //学客圈回复
        case 10079:
        $id = isset($_GET['id'])?$_GET['id']:'';
        $uid = isset($_GET['uid'])?$_GET['uid']:'';
        $replier = isset($_GET['openid'])?$_GET['openid']:'';
        $content = isset($_GET['content'])?$_GET['content']:'';
        ccl_replier($id,$uid,$replier,$content);
        break;
        case 10091:
        $actid = isset($_GET['actid'])?$_GET['actid']:'';
        act_over($actid);
        break;
	    case 10099:   
	    //测试
	    /*$sid=session_id();
	   if ($_COOKIE['user']>time()) {
	   	//echo $_COOKIE['user'];
	   }else{
	   	setcookie("user",time()+6,time()+5);
	   }
       
	    //echo $_SERVER['REMOTE_ADDR'];
	   // var_dump($_COOKIE);
       var_dump($_SESSION);*/
        //echo get_token();
	    break;

	    case 10100:   
	    $openid=isset($_GET['openid'])?$_GET['openid']:'';
	    $actid=isset($_GET['actid'])?$_GET['actid']:'';
	    get_act_job($actid,$openid);
	    break;
	    //获得活动角色表
	    case 10101:   
	    $openid=isset($_GET['openid'])?$_GET['openid']:'';
	    $actid=isset($_GET['actid'])?$_GET['actid']:'';
	    $jobid=isset($_GET['jobid'])?$_GET['jobid']:'';
	    up_act_job($actid,$openid,$jobid);
	    break;
	    //申请活动角色
	    case 10102:   
	    $openid=isset($_GET['openid'])?$_GET['openid']:'';
	    $actid=isset($_GET['actid'])?$_GET['actid']:'';
	    check_act_job($actid,$openid);
	    break;
	    //查询用户是否已经竞选了活动角色
	    case 10103:   
	    $openid=isset($_GET['openid'])?$_GET['openid']:'';
	    $jobid=isset($_GET['jobid'])?$_GET['jobid']:'';
	    up_job_add($openid,$jobid);
	    break;
	    //管理员分配队长
	    case 10107:
	    $actid = isset($_GET['actid'])?$_GET['actid']:'';
	    $userid = isset($_GET['userid'])?$_GET['userid']:'';
	    $troops = isset($_GET['troops'])?$_GET['troops']:'';
	    get_captain($actid,$userid,$troops);
	    break;
	    //其他用户给竞选者加油
	    case 10104:   
	    $openid=isset($_GET['openid'])?$_GET['openid']:'';
	    $jobid=isset($_GET['jobid'])?$_GET['jobid']:'';
	    $actid = isset($_GET['actid'])?$_GET['actid']:'';
	    get_job_user($openid,$jobid,$actid);
	    break;
	    //列出当前角色的竞选者
		case 10105:   
    	$openid=isset($_GET['openid'])?$_GET['openid']:'';
     	$jobid=isset($_GET['jobid'])?$_GET['jobid']:'';
     	check_job_my($openid,$jobid);
     	break;
     	//根据openid和jobid判断当前用户是否竞选者自己

		case 10106:   
    	$openid=isset($_GET['openid'])?$_GET['openid']:'';
     	$jobid=isset($_GET['jobid'])?$_GET['jobid']:'';
     	get_job_id($jobid);
     	break;
     	//根据当前角色id列出当前用户的头像，角色名称，和票数


//后台api控制开始
		case 'master':
		$action=isset($_REQUEST['action'])?$_REQUEST['action']:1;
			switch ($action)
			{
			case 10000://用户登录判断
			  $username=isset($_GET['username'])?$_GET['username']:'';
	     	  $password=isset($_GET['password'])?$_GET['password']:'';
	     	  login_admin($username,$password);
			  break;  

			case 10001://列出任务令分类列表
			  $token=isset($_GET['token'])?$_GET['token']:'';
			  $page = isset($_GET['page'])?$_GET['page']:'';
			  $id = isset($_GET['id'])?$_GET['id']:'';
	     	  Master_Get_ActCat($token,$page,$id);
			  break;  

			case 10002://增加任务令分类
			  $token=isset($_GET['token'])?$_GET['token']:'';
			  $tname=isset($_GET['tname'])?$_GET['tname']:'';
			  $fonticon=isset($_GET['fonticon'])?$_GET['fonticon']:'';
			  $color=isset($_GET['color'])?$_GET['color']:'';
			  $catran=isset($_GET['catran'])?$_GET['catran']:'';
	     	  Master_add_ActCat($token,$tname,$fonticon,$color,$catran);
			  break;  

			case 10003://编辑任务令分类
			  $token=isset($_GET['token'])?$_GET['token']:'';
			  $id=isset($_GET['id'])?$_GET['id']:'';
			  $tname=isset($_GET['tname'])?$_GET['tname']:'';
			  $fonticon=isset($_GET['fonticon'])?$_GET['fonticon']:'';
			  $color=isset($_GET['color'])?$_GET['color']:'';
			  $catran=isset($_GET['catran'])?$_GET['catran']:'';
	     	  Master_edit_ActCat($token,$id,$tname,$fonticon,$color,$catran);
			  break;  

			case 10004://编辑任务令分类
			  $token=isset($_GET['token'])?$_GET['token']:'';
			  $id=urldecode(isset($_GET['id'])?$_GET['id']:'');
	     	  Master_del_ActCat($token,$id);
			  break; 

			case 10005://发布任务令分类
			  $token = isset($_POST['token'])?$_POST['token']:'';
			  //var_dump($token);exit;
			  $catid = isset($_POST['catid'])?$_POST['catid']:'';
	     	  $title = isset($_POST['title'])?$_POST['title']:'';
	     	  $actdesc = isset($_POST['actdesc'])?$_POST['actdesc']:'';
	     	  $actimgurl = isset($_POST['actimgurl'])?$_POST['actimgurl']:'';
	     	  $actcode = isset($_POST['actcode'])?$_POST['actcode']:'';
	     	  $crocode = isset($_POST['crocode'])?$_POST['crocode']:'';
	     	  $masterid = isset($_POST['masterid'])?$_POST['masterid']:'';
	     	  $mastername = isset($_POST['mastername'])?$_POST['mastername']:'';
	     	  $masterphone = isset($_POST['masterphone'])?$_POST['masterphone']:'';
              $actstarttime = isset($_POST['actstarttime'])?$_POST['actstarttime']:'';	     	   
	     	  $actendtime = isset($_POST['actendtime'])?$_POST['actendtime']:'';	     	   
	     	  $joinstarttime = isset($_POST['joinstarttime'])?$_POST['joinstarttime']:'';	     	     
	     	  $joinendtime = isset($_POST['joinendtime'])?$_POST['joinendtime']:'';	     	     
	     	  $userlimit = isset($_POST['userlimit'])?$_POST['userlimit']:'';	     	     
	     	  $deposit = isset($_POST['deposit'])?$_POST['deposit']:'';
              $actsta = isset($_POST['actsta'])?$_POST['actsta']:'';
              $adminsta = isset($_POST['adminsta'])?$_POST['adminsta']:'';
              $time = isset($_POST['time'])?$_POST['time']:'';
              $lockstatus = isset($_POST['lock'])?$_POST['lock']:'';
              $count = isset($_POST['count'])?$_POST['count']:'';
              $content = isset($_POST['content'])?$_POST['content']:'';
              $longitude = isset($_POST['longitude'])?$_POST['longitude']:'';
              $latitude = isset($_POST['latitude'])?$_POST['latitude']:'';
              $address = isset($_POST['address'])?$_POST['address']:'';
              $arr = empty($_POST['arr'][0]['jobname'])?0:$_POST['arr'];
              $mapurl = isset($_POST['mapurl'])?$_POST['mapurl']:'';
              $setvalue = isset($_POST['setvalue'])?$_POST['setvalue']:'';
              $actran = isset($_POST['actran'])?$_POST['actran']:'';
              Master_add_ActInfo($token,$catid,$title,$actdesc, $actimgurl,$actcode,$crocode,$masterid,$mastername,$masterphone,$actstarttime,$actendtime,$joinstarttime,$joinendtime,$userlimit,$deposit,$actsta,$adminsta,$time,$lockstatus,$count,$content,$longitude,$latitude,$address,$arr,$mapurl,$setvalue,$actran);
			  break; 

			case 10006:////提交任务令编辑内容
			  $id = isset($_POST['id'])?$_POST['id']:'';
			  $token = isset($_POST['token'])?$_POST['token']:'';
			  $catid = isset($_POST['catid'])?$_POST['catid']:'';
	     	  $title = isset($_POST['title'])?$_POST['title']:'';
	     	  $actdesc = isset($_POST['actdesc'])?$_POST['actdesc']:'';
	     	  $actimgurl = isset($_POST['actimgurl'])?$_POST['actimgurl']:'';
	     	  $actcode = isset($_POST['actcode'])?$_POST['actcode']:'';
	     	  $crocode = isset($_POST['crocode'])?$_POST['crocode']:'';
	     	  $masterid = isset($_POST['masterid'])?$_POST['masterid']:'';
	     	  $mastername = isset($_POST['mastername'])?$_POST['mastername']:'';
	     	  $masterphone = isset($_POST['masterphone'])?$_POST['masterphone']:'';
              $actstarttime = isset($_POST['actstarttime'])?$_POST['actstarttime']:'';	     	   
	     	  $actendtime = isset($_POST['actendtime'])?$_POST['actendtime']:'';	     	   
	     	  $joinstarttime = isset($_POST['joinstarttime'])?$_POST['joinstarttime']:'';	     	     
	     	  $joinendtime = isset($_POST['joinendtime'])?$_POST['joinendtime']:'';	     	     
	     	  $userlimit = isset($_POST['userlimit'])?$_POST['userlimit']:'';	     	     
	     	  $deposit = isset($_POST['deposit'])?$_POST['deposit']:'';
              $actsta = isset($_POST['actsta'])?$_POST['actsta']:'';
              $adminsta = isset($_POST['adminsta'])?$_POST['adminsta']:'';
              $time = isset($_POST['time'])?$_POST['time']:'';
              $lockstatus = isset($_POST['lock'])?$_POST['lock']:'';
              $count = isset($_POST['count'])?$_POST['count']:'';
              $content = isset($_POST['content'])?$_POST['content']:'';
              $longitude = isset($_POST['longitude'])?$_POST['longitude']:'';
              $latitude = isset($_POST['latitude'])?$_POST['latitude']:'';
              $address = isset($_POST['address'])?$_POST['address']:'';
              $arr = empty($_POST['arr'][0]['jobname'])?0:$_POST['arr'];
              $mapurl = isset($_POST['mapurl'])?$_POST['mapurl']:'';
              $setvalue = isset($_POST['setvalue'])?$_POST['setvalue']:'';
              $actran = isset($_POST['actran'])?$_POST['actran']:'';
              //var_dump($_POST);exit;
              Master_edit_ActInfo($id,$token,$catid,$title,$actdesc, $actimgurl,$actcode,$crocode,$masterid,$mastername,$masterphone,$actstarttime,$actendtime,$joinstarttime,$joinendtime,$userlimit,$deposit,$actsta,$adminsta,$time,$lockstatus,$count,$content,$longitude,$latitude,$address,$arr,$mapurl,$setvalue,$actran);
			  break;     
            
            case 10007: //删除任务
	          $id = isset($_GET['id'])?$_GET['id']:'';
		      $token = isset($_GET['token'])?$_GET['token']:'';
	          Master_del_ActInfo($id,$token);
	          break;
            
            case 10008://查询该任务令下的所有内容
              $id = isset($_GET['id'])?$_GET['id']:'';
		      $token = isset($_GET['token'])?$_GET['token']:'';               
              Master_get_ActInfo($id,$token);
	          break;  

            //发布商品
             case 10009: 
              $gid = isset($_POST['gid'])?$_POST['gid']:'';
		      $token = isset($_POST['token'])?$_POST['token']:'';
	          $goods_name = isset($_POST['goods_name'])?$_POST['goods_name']:'';
	          $abs = isset($_POST['abs'])?$_POST['abs']:'';
  	          $imgurl = isset($_POST['imgurl'])?$_POST['imgurl']:'';
  	          $total = isset($_POST['total'])?$_POST['total']:'';
  	          $time = isset($_POST['time'])?$_POST['time']:'';
  	          $goods_number = isset($_POST['goods_number'])?$_POST['goods_number']:'';
  	          $count = isset($_POST['count'])?$_POST['count']:'';
  	          $is_real = isset($_POST['is_real'])?$_POST['is_real']:'';
  	          $content = isset($_POST['content'])?$_POST['content']:'';
  	          $url = isset($_POST['url'])?$_POST['url']:'';      
	          Master_add_Goods($gid,$token,$goods_name,$abs,$imgurl,$total,$time,$goods_number,$is_real,$count,$content,$url);
	          break;
             
            //删除商品
	         case 100010: 
	          $id = isset($_GET['id'])?$_GET['id']:'';
		      $token = isset($_GET['token'])?$_GET['token']:'';            
	          Master_del_Goods($id,$token);
	          break;

	        //编辑商品
             case 100011: 
	          $id = isset($_POST['id'])?$_POST['id']:'';
	          $gid = isset($_POST['gid'])?$_POST['gid']:'';
		      $token = isset($_POST['token'])?$_POST['token']:'';
	          $goodsid = isset($_POST['goodsid'])?$_POST['goodsid']:'';
	          $goods_name = isset($_POST['goods_name'])?$_POST['goods_name']:'';
	          $abs = isset($_POST['abs'])?$_POST['abs']:'';
  	          $imgurl = isset($_POST['imgurl'])?$_POST['imgurl']:'';
  	          $total = isset($_POST['total'])?$_POST['total']:'';
  	          $time = isset($_POST['time'])?$_POST['time']:'';
  	          $goods_number = isset($_POST['goods_number'])?$_POST['goods_number']:'';
  	          $is_real = isset($_POST['is_real'])?$_POST['is_real']:'';
  	          $url = isset($_POST['url'])?$_POST['url']:'';              
	          $content = isset($_POST['content'])?$_POST['content']:'';
	          $count = isset($_POST['count'])?$_POST['count']:''; 
	          Master_edit_Goods($id,$gid,$token,$goods_name,$abs,$imgurl,$total,$time,$goods_number,$is_real,$url,$content,$count);
	          break; 
			 
			 //查询该商品下的所有信息
	         case 100012:
	          $id = isset($_GET['id'])?$_GET['id']:''; 
              $token = isset($_GET['token'])?$_GET['token']:'';
              Master_get_Goodsdetail($id,$token);
             
              break;

             //获取全部商品
             case 100013: 
              $token = isset($_GET['token'])?$_GET['token']:'';
              $page = isset($_GET['page'])?$_GET['page']:'';
              $gid = isset($_GET['gid'])?$_GET['gid']:'';
              Master_get_Goods($token,$page,$gid);
              break;
             case 10030:
             get_type();
             break;
             //商品部分结束


             //会员订单管理开始
             //获取订单列表
             case 100014:
              $token = isset($_GET['token'])?$_GET['token']:'';
              $page = isset($_GET['page'])?$_GET['page']:'';
              $order_sn = isset($_GET['order_sn'])?$_GET['order_sn']:''; 
              Master_get_orders($token,$page,$order_sn);
              break;
             //插入订单
             case 100015:
              $order_sn = isset($_POST['order_sn'])?$_POST['order_sn']:'';
              $user_id  = isset($_POST['user_id'])?$_POST['user_id']:'';
              $order_status = isset($_POST['order_status'])?$_POST['order_status']:'';             
			  $shipping_status = isset($_POST['shipping_status'])?$_POST['shipping_status']:'';
			  $pay_status = isset($_POST['pay_status'])?$_POST['pay_status']:'';
			  $consignee = isset($_POST['consignee'])?$_POST['consignee']:'';
			  $tel = isset($_POST['tel'])?$_POST['tel']:'';
			  $province = isset($_POST['province'])?$_POST['province']:'';
			  $city = isset($_POST['city'])?$_POST['city']:'';
			  $district = isset($_POST['district'])?$_POST['district']:'';
			  $address = isset($_POST['address'])?$_POST['address']:'';
			  $goods_amount = isset($_POST['goods_amount'])?$_POST['goods_amount']:'';
			  $add_time = isset($_POST['add_time'])?$_POST['add_time']:'';
			  $zipcode = isset($_POST['zipcode'])?$_POST['zipcode']:'';
			  Master_add_orders($token,$order_sn,$user_id,$order_status,$shipping_status,$pay_status,$consignee,$tel,$province,$city,$district,$address,$goods_amount,$add_time,$zipcode);
			  break;
			 //删除订单
			 case 100016:
			  $id = isset($_GET['id'])?$_GET['id']:'';
		      $token = isset($_GET['token'])?$_GET['token']:'';            
	          Master_del_Orders($id,$token);
	          break;
	         
	         //获取广告列表
	         case 100017:
		      $token = isset($_GET['token'])?$_GET['token']:'';
		      $page = isset($_GET['page'])?$_GET['page']:'';
              $ad_type = isset($_GET['ad_type'])?$_GET['ad_type']:'';            
	          Master_get_Adv($token,$page,$ad_type);
	          break;
	         //发布广告
	         case 100018:
	          $token = isset($_POST['token'])?$_POST['token']:'';            
	          $title = isset($_POST['title'])?$_POST['title']:'';
	          $imgurl = isset($_POST['imgurl'])?$_POST['imgurl']:'';
	          $link = isset($_POST['link'])?$_POST['link']:'';
	          $lockstatus = isset($_POST['lockstatus'])?$_POST['lockstatus']:'';
	          $time = isset($_POST['time'])?$_POST['time']:'';
	          $sork = isset($_POST['sork'])?$_POST['sork']:'';
	          $ad_type = isset($_POST['ad_type'])?$_POST['ad_type']:'';
	          Master_add_Adv($token,$title,$imgurl,$link,$lockstatus,$time,$sork,$ad_type);
	          break;
	         //编辑广告
	         case 100019:
	          $id = isset($_POST['id'])?$_POST['id']:'';
	          $token = isset($_POST['token'])?$_POST['token']:'';            
	          $title = isset($_POST['title'])?$_POST['title']:'';
	          $imgurl = isset($_POST['imgurl'])?$_POST['imgurl']:'';
	          $link = isset($_POST['link'])?$_POST['link']:'';
	          $lockstatus = isset($_POST['lockstatus'])?$_POST['lockstatus']:'';
	          $time = isset($_POST['time'])?$_POST['time']:'';
	          $sork = isset($_POST['sork'])?$_POST['sork']:'';
	          $ad_type = isset($_POST['ad_type'])?$_POST['ad_type']:'';
	          //var_dump($_POST);exit;
	          Master_edit_Adv($token,$id,$title,$imgurl,$link,$lockstatus,$time,$sork,$ad_type);
	          break;
	          //获取单个广告
	         case 100020:
              $id = isset($_GET['id'])?$_GET['id']:'';
              $token = isset($_GET['token'])?$_GET['token']:'';
              Master_get_oneAdv($token,$id);
	          break;
	         //删除广告
	         case 100021:
	          $id = isset($_GET['id'])?$_GET['id']:'';
              $token = isset($_GET['token'])?$_GET['token']:'';
              Master_del_Adv($token,$id);
	          break;   
             //获取学客圈列表
	         case 100022:
	          $token = isset($_GET['token'])?$_GET['token']:'';
	          $page = isset($_GET['page'])?$_GET['page']:'';
              $uid = isset($_GET['uid'])?$_GET['uid']:'';
              Master_get_ccl($token,$page,$uid);
              break;
             //删除学客圈
             case 100024:
              $token = isset($_GET['token'])?$_GET['token']:'';
	          $id = isset($_GET['id'])?$_GET['id']:'';
              Master_del_ccl($token,$id);
              break;
             //获取会员列表
             case 100023:
              $token = isset($_GET['token'])?$_GET['token']:'';
              $page = isset($_GET['page'])?$_GET['page']:'';
              $name = isset($_GET['name'])?$_GET['name']:''; 
              Master_get_userinfo($token,$page,$name);
              break;
             //编辑学分
             case 100024:
              $token = isset($_GET['token'])?$_GET['token']:'';
              $id = isset($_GET['id'])?$_GET['id']:'';
              $winmoney = isset($_GET['winmoney'])?$_GET['winmoney']:'';
              Master_winmoney_edit($token,$id,$winmoney);
              break;
            //学分详情
             case 100025:
              $token = isset($_GET['token'])?$_GET['token']:'';
              $user_id = isset($_GET['user_id'])?$_GET['user_id']:'';
              $page = isset($_GET['page'])?$_GET['page']:'';
              $name = isset($_GET['name'])?$_GET['name']:''; 
              Master_winmoney_detail($token,$user_id,$page,$name);
              break;
            //学令牌用户
             case 100026:
              $token = isset($_GET['token'])?$_GET['token']:'';
              $tel = isset($_GET['tel'])?$_GET['tel']:'';
              Master_app_user($token,$tel);
              break;
            //改变实名验证状态
             case 100027:
              $token = isset($_GET['token'])?$_GET['token']:'';
              $user_id = isset($_GET['uid'])?$_GET['uid']:'';
              $status = isset($_GET['status'])?$_GET['status']:'';
              Master_info_status($token,$user_id,$status);
              break;
            //用户提现记录
             case 100028:
              $token = isset($_GET['token'])?$_GET['token']:'';
              $uid = isset($_GET['uid'])?$_GET['uid']:''; 
              Master_get_record($token,$uid);
              break;
            //同意/不同意用户提现申请
             case 100029:
              $token = isset($_GET['token'])?$_GET['token']:'';
              $id = isset($_GET['id'])?$_GET['id']:'';
              $status = isset($_GET['status'])?$_GET['status']:'';
              $user_id = isset($_GET['uid'])?$_GET['uid']:'';
              Master_consent_application($token,$id,$status,$user_id);
              break;
            //支教老师活动列表
             case 100030:
              $token = isset($_GET['token'])?$_GET['token']:'';
              $status = isset($_GET['status'])?$_GET['status']:'';
              $user_id = isset($_GET['uid'])?$_GET['uid']:'';
              Master_app_act($token,$status,$user_id);
              break;
            //活动审核
             case 100031:
              $token = isset($_GET['token'])?$_GET['token']:'';
              $id = isset($_GET['id'])?$_GET['id']:'';
              $status = isset($_GET['status'])?$_GET['status']:'';
              $user_id = isset($_GET['uid'])?$_GET['uid']:'';
			  Master_act_check($token,$id,$status,$user_id);
			  break;
			//用户申请退款记录
			 case 100032:
			  $token = isset($_GET['token'])?$_GET['token']:'';
              $uid = isset($_GET['uid'])?$_GET['uid']:''; 
              Master_product_refund($token,$uid);
              break;
            //同意退款申请
             case 100033:
              $token = isset($_GET['token'])?$_GET['token']:'';
              $id = isset($_GET['id'])?$_GET['id']:'';
              $status = isset($_GET['status'])?$_GET['status']:'';
              Master_consent_refund($token,$id,$status);
              break;
            //用户银行卡列表
             case 100034:
              $token = isset($_GET['token'])?$_GET['token']:'';
              $uid = isset($_GET['uid'])?$_GET['uid']:''; 
              Master_user_card($token,$uid);
              break;
            //手环列表
             case 100035:
              $token = isset($_GET['token'])?$_GET['token']:'';
              $uid = isset($_GET['uid'])?$_GET['uid']:''; 
              Master_user_usb($token,$uid);
              break;
            //学令牌产品列表
            case 100036:
              $token = isset($_GET['token'])?$_GET['token']:'';
              $uid = isset($_GET['uid'])?$_GET['uid']:'';
              Master_user_xlp($token,$uid);
              break;
            //修改活动图片模板
            case 100037:
              $token = isset($_GET['token'])?$_GET['token']:'';
              $id = isset($_GET['id'])?$_GET['id']:'';
              $imgurl = isset($_GET['imgurl'])?$_GET['imgurl']:'';
              Master_update_actimg($token,$id,$imgurl);
              break;
            //活动图片模板列表
            case 100038:
              $token = isset($_GET['token'])?$_GET['token']:'';
              Master_act_img($token);
              break;
            //学令牌学客圈列表
            case 100039:
              $token = isset($_GET['token'])?$_GET['token']:'';
              Master_xlp_ccl($token);
              break;
            //用户收益详细
            case 100040:
              $token = isset($_GET['token'])?$_GET['token']:'';
              $user_id = isset($_GET['id'])?$_GET['id']:'';
              Master_earnings_detail($token,$user_id);
              break;
            //屏蔽用户学客圈
            case 100041:
              $token = isset($_GET['token'])?$_GET['token']:'';
              $user_id = isset($_GET['uid'])?$_GET['uid']:'';
              $id = isset($_GET['id'])?$_GET['id']:'';
              $status = isset($_GET['status'])?$_GET['status']:'';
              Master_shield_ccl($token,$id,$user_id,$status);
              break;
            //编辑用户资料
            case 100042:
              $token = isset($_GET['token'])?$_GET['token']:'';
              $id = isset($_GET['id'])?$_GET['id']:'';
              $winmoney = isset($_GET['winmoney'])?$_GET['winmoney']:'';
              $nickname = isset($_GET['nickname'])?$_GET['nickname']:'';
              $headimgurl = isset($_GET['headimgurl'])?$_GET['headimgurl']:'';
              $bareheaded_photo = isset($_GET['bareheaded_photo'])?$_GET['bareheaded_photo']:'';
              $credit = isset($_GET['credit'])?$_GET['credit']:'';
              $bust_shot = isset($_GET['bust_shot'])?$_GET['bust_shot']:'';
              $id_card = isset($_GET['id_card'])?$_GET['id_card']:'';
              $role = isset($_GET['role'])?$_GET['role']:'';
              update_user_info($token,$id,$winmoney,$nickname,$headimgurl,$bareheaded_photo,$credit,$bust_shot,$id_card,$role);
              break;
            //用户资料
            case 100043:
              $token = isset($_GET['token'])?$_GET['token']:'';
              $id = isset($_GET['id'])?$_GET['id']:'';
              check_user_info($token,$id);
              break;
            //修改用户银行卡号
            case 100044:
              $token = isset($_GET['token'])?$_GET['token']:'';
              $id = isset($_GET['id'])?$_GET['id']:'';
              $card_number = isset($_GET['card_number'])?$_GET['card_number']:'';
              update_user_card($token,$id,$card_number);
              break;
            //修改活动列表
            case 100045:
              $token = isset($_GET['token'])?$_GET['token']:'';
              $id = isset($_GET['id'])?$_GET['id']:'';
              $title = isset($_GET['title'])?$_GET['title']:'';
              $actimgurl = isset($_GET['actimgurl'])?$_GET['actimgurl']:'';
              $tel = isset($_GET['tel'])?$_GET['tel']:'';
              $actstarttime = isset($_GET['actstarttime'])?$_GET['actstarttime']:'';
              $userlimit = isset($_GET['userlimit'])?$_GET['userlimit']:'';
              $actsta = isset($_GET['actsta'])?$_GET['actsta']:'';
              $address = isset($_GET['address'])?$_GET['address']:'';
              $subject = isset($_GET['subject'])?$_GET['subject']:'';
              $start_img = isset($_GET['start_img'])?$_GET['start_img']:'';
              $underway_img = isset($_GET['underway_img'])?$_GET['underway_img']:'';
              $end_img = isset($_GET['end_img'])?$_GET['end_img']:'';
              $latitude = isset($_GET['latitude'])?$_GET['latitude']:'';
              $longitude = isset($_GET['longitude'])?$_GET['longitude']:'';
              update_user_act($token,$id,$title,$actimgurl,$tel,$actstarttime,$userlimit,$actsta,$address,$subject,$start_img,$underway_img,$end_img,$latitude,$longitude);
              break;
            //显示用户活动
            case 100046:
              $token = isset($_GET['token'])?$_GET['token']:'';
              $id = isset($_GET['id'])?$_GET['id']:'';
              show_user_act($token,$id);
              break;
            //发货
            case 100047:
              $token = isset($_GET['token'])?$_GET['token']:'';
              $shipping_status = isset($_GET['shipping_status'])?$_GET['shipping_status']:'';
              update_shipping_status($token,$shipping_status);
              break;
            //代理商管理
            case 100048:
              $token = isset($_GET['token'])?$_GET['token']:'';
              $tel = isset($_GET['tel'])?$_GET['tel']:'';
              agent_manage($token,$tel);
              break;
            //代理商下级代理
            case 100049:
              $token = isset($_GET['token'])?$_GET['token']:'';
              $form = isset($_GET['form'])?$_GET['form']:'';
              $user_id = isset($_GET['user_id'])?$_GET['user_id']:'';
              $tel = isset($_GET['tel'])?$_GET['tel']:'';
              agent_subordinate($token,$form,$user_id,$tel);
              break;
            //代理商投资
            case 100050:
              $token = isset($_GET['token'])?$_GET['token']:'';
              $money = isset($_GET['money'])?$_GET['money']:'';
              $user_id = isset($_GET['user_id'])?$_GET['user_id']:'';
              agent_invest($token,$money,$user_id);
              break;
            default:
			echo json_encode(array('error'=>'未知action类型'),JSON_UNESCAPED_UNICODE);
			}
     	break;
        case 'recovery':   
		$action=isset($_REQUEST['action'])?$_REQUEST['action']:1;
            switch ($action)
			{    

                //显示商品回收站列表
                case 10009:
                  $token = isset($_GET['token'])?$_GET['token']:'';
                  $page = isset($_GET['page'])?$_GET['page']:'';
                  $gid = isset($_GET['gid'])?$_GET['gid']:'';   
                  Master_getgoods_recovery($token,$page,$gid);
                  break;
                //显示任务令回收站列表
                case 100011:
                  $token = isset($_GET['token'])?$_GET['token']:'';
                  $page = isset($_GET['page'])?$_GET['page']:'';
                  $title = isset($_GET['title'])?$_GET['title']:'';
                  Master_getact_recovery($token,$page,$title);
                  break; 
                //显示学客圈回收站列表
                case 100010:
                  $token = isset($_GET['token'])?$_GET['token']:'';
                  $page = isset($_GET['page'])?$_GET['page']:'';
                  $uid = isset($_GET['uid'])?$_GET['uid']:'';
                  Master_getccl_recovery($token,$page,$uid);
                  break;
				//进入回收站
                case 10000:
                  $id =  isset($_GET['id'])?$_GET['id']:'';
                  $token = isset($_GET['token'])?$_GET['token']:'';
                  $tabname = isset($_GET['tabname'])?$_GET['tabname']:'';
                  Master_recovery($id,$token,$tabname);
                  break;
                //恢复回收站商品
                case 10003:
                  $id = isset($_GET['id'])?$_GET['id']:'';
                  $token = isset($_GET['token'])?$_GET['token']:'';
                  $tabname = isset($_GET['tabname'])?$_GET['tabname']:'';
                  Master_recover($id,$token,$tabname);
                  break;                   

		    default:
			echo json_encode(array('error'=>'未知action类型'),JSON_UNESCAPED_UNICODE);
			}
     	break;

//后台api控制结束


		default:
		echo json_encode(array('error'=>'未知type类型'),JSON_UNESCAPED_UNICODE);
		break;
	}

