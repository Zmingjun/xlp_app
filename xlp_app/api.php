<?php 
header("content-type:text/html;charset=utf-8");
  require_once 'common/mysql_class.php';
  require_once 'common/action_class.php';
  require_once "common/jssdk.php";
  require_once "php_sdk/autoload.php";
  require_once "php_sdk/OSSconfig.php";
  require_once "php_sdk/weixin_autoload.php";
  require_once 'php_sdk/api_sdk/vendor/autoload.php';
  require_once 'php_sdk/api_sdk/lib/Core/Config.php';
  session_start();
  header('Access-Control-Allow-Origin:*');
  // 响应类型  
    header('Access-Control-Allow-Methods: POST,GET');  
    // 响应头设置  
    header('Access-Control-Allow-Headers:x-requested-with,content-type');  
  $type=isset($_REQUEST['type'])?$_REQUEST['type']:1;
  echo 123;exit;
   
  
  switch ($type){
    //用户注册
    case 10001:
          $tel = isset($_POST['tel'])?$_POST['tel']:'';
          $pwd = isset($_POST['pwd'])?$_POST['pwd']:'';
          $role = isset($_POST['role'])?$_POST['role']:'3';
          $code = isset($_POST['code'])?$_POST['code']:'';
          $inviter = isset($_POST['inviter'])?$_POST['inviter']:''; 
          user_register($tel,$pwd,$role,$code,$inviter);
          break;
    //用户登陆
    case 10003:
          $tel = isset($_POST['tel'])?$_POST['tel']:'';
          $pwd = isset($_POST['pwd'])?$_POST['pwd']:'';
          $role = isset($_POST['role'])?$_POST['role']:'';
          $registrationid_id = isset($_POST['registrationid_id'])?$_POST['registrationid_id']:'';
          user_login($tel,$pwd,$role,$registrationid_id);
          break;
         //获取用户详细信息
        case 10005:
          $uid = isset($_GET['uid'])?$_GET['uid']:'';
          $role = isset($_GET['role'])?$_GET['role']:'';
          user_getinfo($uid,$role);
          break;
        //添加学生家长信息
        case 10006:
          $user_id = isset($_GET['uid'])?$_GET['uid']:'';
          $job = isset($_GET['job'])?$_GET['job']:'';
          $address = isset($_GET['address'])?$_GET['address']:'';
          $stu_sex = isset($_GET['stu_sex'])?$_GET['stu_sex']:'';
          $stu_age = isset($_GET['stu_age'])?$_GET['stu_age']:'';
          $stu_school = isset($_GET['stu_school'])?$_GET['stu_school']:'';
          $stu_class = isset($_GET['stu_class'])?$_GET['stu_class']:'';
          $par_sex = isset($_GET['par_sex'])?$_GET['par_sex']:'';
          stu_userinfo_add($user_id,$job,$address,$stu_sex,$stu_age,$stu_school,$stu_class,$par_sex);
          break;
        //添加班主任信息
        case 10007:
          $user_id = isset($_GET['uid'])?$_GET['uid']:'';
          $class = isset($_GET['class'])?$_GET['class']:'';
          $honour = isset($_GET['honour'])?$_GET['honour']:'';
          $seniority = isset($_GET['seniority'])?$_GET['seniority']:'';
          tea_userinfo_add($user_id,$class,$honour,$seniority);
          break;
        //添加联盟商信息
        case 10008:
          $user_id = isset($_GET['uid'])?$_GET['uid']:'';
          $address = isset($_GET['address'])?$_GET['address']:'';
          $business = isset($_GET['business'])?$_GET['business']:'';
          $area = isset($_GET['area'])?$_GET['area']:'';
          $team = isset($_GET['team'])?$_GET['team']:'';
          $resource = isset($_GET['resource'])?$_GET['resource']:'';
          $img = isset($_GET['img'])?$_GET['img']:'';
          ap_userinfo_add($user_id,$address,$business,$area,$team,$resource,$img);
          break;
        //修改用户密码
        case 10009:
          $tel = isset($_POST['tel'])?$_POST['tel']:'';
          $pwd = isset($_POST['pwd'])?$_POST['pwd']:'';
          $code = isset($_POST['code'])?$_POST['code']:'';
          $role = isset($_POST['role'])?$_POST['role']:'';
          user_editpwd($tel,$pwd,$code,$role);
          break;
        //修改头像
        case 10036:
          $user_id = isset($_GET['uid'])?$_GET['uid']:'';
          $headimgurl = isset($_GET['headimgurl'])?$_GET['headimgurl']:'';
          $token = isset($_GET['token'])?$_GET['token']:'';
          edit_img($user_id,$headimgurl,$token);
          break;
        //退出登陆
        case 10039:
          $user_id = isset($_GET['uid'])?$_GET['uid']:'';
          login_quit($user_id);
          break;
        //绑定银行卡
        case 10101:
          $token = isset($_GET['token'])?$_GET['token']:'';
          $user_id = isset($_GET['uid'])?$_GET['uid']:'';
          $card_number = isset($_GET['card_number'])?$_GET['card_number']:'';
          $time = isset($_GET['time'])?$_GET['time']:'';
          $code = isset($_GET['code'])?$_GET['code']:'';
          bind_bankcard($token,$user_id,$card_number,$time,$code); 
          break;
        //用户上传认证资料
        case 10124:
          $bareheaded_photo = isset($_GET['bareheaded_photo'])?$_GET['bareheaded_photo']:'';
          $bust_shot = isset($_GET['bust_shot'])?$_GET['bust_shot']:'';
          $id_card = isset($_GET['id_card'])?$_GET['id_card']:'';
          $address = isset($_GET['address'])?$_GET['address']:'';
          $name = isset($_GET['name'])?$_GET['name']:'';
          $user_id = isset($_GET['uid'])?$_GET['uid']:'';
          upload_user_data($bareheaded_photo,$bust_shot,$id_card,$address,$name,$user_id);
          break;
        //用户发起提现
        case 10127:
          $token = isset($_GET['token'])?$_GET['token']:'';
          $user_id = isset($_GET['user_id'])?$_GET['user_id']:'';
          $money = isset($_GET['money'])?$_GET['money']:'';
          $w_id = isset($_GET['w_id'])?$_GET['w_id']:'';
          $code = isset($_GET['code'])?$_GET['code']:'';
          user_withdraw($token,$user_id,$money,$w_id,$code);
          break;
        //银行卡列表
        case 10128:
          $token = isset($_GET['token'])?$_GET['token']:'';
          $user_id = isset($_GET['uid'])?$_GET['uid']:'';
          card_list($token,$user_id);
          break;
        case 10147:
          $user_id = isset($_GET['uid'])?$_GET['uid']:'';
          $key = isset($_GET['key'])?$_GET['key']:'';
          binding_xlp($user_id,$key);
          break;
        //修改手环信息  
        case 10148:
          $token = isset($_GET['token'])?$_GET['token']:'';
          $stu_name = isset($_GET['stu_name'])?$_GET['stu_name']:'';
          $stu_img = isset($_GET['stu_img'])?$_GET['stu_img']:'';
          $id = isset($_GET['id'])?$_GET['id']:'';
          edit_usb_detail($token,$stu_name,$stu_img,$id);
          break;
        //显示手环信息
        case 10149:
          $token = isset($_GET['token'])?$_GET['token']:'';
          $key = isset($_GET['key'])?$_GET['key']:'';
          show_usb($token,$key);
          break;
        //查看信用星级
        case 10157:
          $token = isset($_GET['token'])?$_GET['token']:'';
          $user_id = isset($_GET['uid'])?$_GET['uid']:'';
          show_credit($token,$user_id);
          break;
        //查看用户账单
        case 10158:
          $token = isset($_GET['token'])?$_GET['token']:'';
          $user_id = isset($_GET['uid'])?$_GET['uid']:'';
          show_bill($token,$user_id);
          break;
        //修改用户名  
        case 10160:
          $token = isset($_GET['token'])?$_GET['token']:'';
          $user_id = isset($_GET['uid'])?$_GET['uid']:'';
          $nickname = isset($_GET['nickname'])?$_GET['nickname']:'';
          edit_username($token,$user_id,$nickname);
          break;
        //展示活动学科
        case 10162:
          $token = isset($_GET['token'])?$_GET['token']:'';
          show_subject($token);
          break;
        //展示发布活动的图片模板
        case 10168:
          $token = isset($_GET['token'])?$_GET['token']:'';
          show_img($token);
          break;
        //展示激活学员列表
        case 10170:
          $token = isset($_GET['token'])?$_GET['token']:'';
          $user_id = isset($_GET['uid'])?$_GET['uid']:'';
          show_activate_member($token,$user_id);
          break;
        //展示未激活学员列表
        case 10169:
          $token = isset($_GET['token'])?$_GET['token']:'';
          $user_id = isset($_GET['uid'])?$_GET['uid']:'';
          show_unactivate_member($token,$user_id);
          break;
        //银行卡解绑
        case 10172:
          $token = isset($_GET['token'])?$_GET['token']:'';
          $id = isset($_GET['id'])?$_GET['id']:'';
          card_unbundling($token,$id);
          break;
        //显示认证资料
        case 10173:
          $token = isset($_GET['token'])?$_GET['token']:'';
          $user_id = isset($_GET['uid'])?$_GET['uid']:'';
          show_attestation_data($token,$user_id);
          break;
        //支教老师收益
        case 10174:
          $token = isset($_GET['token'])?$_GET['token']:'';
          $user_id = isset($_GET['uid'])?$_GET['uid']:'';
          $form = isset($_GET['form'])?$_GET['form']:'';
          show_income($token,$user_id,$form);
          break;
        //显示支教老师一周收益
        case 10175:
          $token = isset($_GET['token'])?$_GET['token']:'';
          $user_id = isset($_GET['uid'])?$_GET['uid']:'';
          $form = isset($_GET['form'])?$_GET['form']:'';
          week_show_income($token,$user_id,$form);
          break;
        //显示支教老师详细收益
        case 10176:
          $token = isset($_GET['token'])?$_GET['token']:'';
          $user_id = isset($_GET['uid'])?$_GET['uid']:'';
          $form = isset($_GET['form'])?$_GET['form']:'';
          show_income_detail($token,$user_id,$form);
          break;
        //检测用户是否有两种身份
        case 10179:
          $tel = isset($_GET['tel'])?$_GET['tel']:''; 
          check_user_role($tel);
          break;
        //活动中心显示手环信息
        case 10180:
          $token = isset($_GET['token'])?$_GET['token']:'';
          $user_id = isset($_GET['uid'])?$_GET['uid']:'';
          center_show_usb($token,$user_id);
          break;
        //个人中心
        case 10171:
          $token = isset($_GET['token'])?$_GET['token']:'';
          $user_id = isset($_GET['uid'])?$_GET['uid']:'';
          center_user($token,$user_id);
          break;
        //查询学分
        case 10021:
          $user_id = isset($_GET['user_id'])?$_GET['user_id']:'';
          get_winmoney($user_id);
          break;
        //支教老师发表活动
        case 10015:
          $user_id = isset($_POST['uid'])?$_POST['uid']:''; 
          $token = isset($_POST['token'])?$_POST['token']:''; 
          $title = isset($_POST['title'])?$_POST['title']:'';
          $userlimit = isset($_POST['userlimit'])?$_POST['userlimit']:'';
          $subject = isset($_POST['subject'])?$_POST['subject']:'';
          $time = isset($_POST['time'])?$_POST['time']:'';
          $tel = isset($_POST['tel'])?$_POST['tel']:'';
          $address = isset($_POST['address'])?$_POST['address']:'';
          $detail = isset($_POST['detail'])?$_POST['detail']:'';
          $actstarttime = isset($_POST['actstarttime'])?$_POST['actstarttime']:'';
          $actimgurl = isset($_POST['actimgurl'])?$_POST['actimgurl']:'';
          $latitude = isset($_POST['latitude'])?$_POST['latitude']:'';
          $longitude = isset($_POST['longitude'])?$_POST['longitude']:'';
          act_add($user_id,$token,$title,$userlimit,$subject,$time,$tel,$address,$detail,$actstarttime,$actimgurl,$latitude,$longitude);
          break;
        //活动详情
        case 10018:
          $token = isset($_GET['token'])?$_GET['token']:'';
          $id = isset($_GET['id'])?$_GET['id']:'';
          act_detail($token,$id);
          break;
        //检测用户是否报名该活动
        case 10121:
          $token = isset($_GET['token'])?$_GET['token']:'';
          $user_id = isset($_GET['uid'])?$_GET['uid']:'';
          $act_id = isset($_GET['act_id'])?$_GET['act_id']:'';
          check_isjoin($token,$user_id,$act_id);
          break;
        //支教老师打分
        case 10142:
          $token = isset($_GET['token'])?$_GET['token']:'';
          $key = isset($_GET['key'])?$_GET['key']:'';
          $score = isset($_GET['score'])?$_GET['score']:'';
          $act_id = isset($_GET['actid'])?$_GET['actid']:'';
          $time = isset($_GET['time'])?$_GET['time']:'';
          $user_id = isset($_GET['uid'])?$_GET['uid']:'';
          tea_marking($token,$key,$score,$act_id,$time,$user_id);
          break;
        //学令牌激活产品列表
        case 10144:
          $token = isset($_GET['token'])?$_GET['token']:'';
          $user_id = isset($_GET['uid'])?$_GET['uid']:'';
          xlp_list($token,$user_id);
          break;
        //申请退款
        case 10145:
          $token = isset($_GET['token'])?$_GET['token']:'';
          $user_id = isset($_GET['uid'])?$_GET['uid']:'';
          $content = isset($_GET['content'])?$_GET['content']:'';
          $key = isset($_GET['key'])?$_GET['key']:'';
          $name = isset($_GET['name'])?$_GET['name']:'';
          $tel = isset($_GET['tel'])?$_GET['tel']:'';
          application_return($token,$user_id,$content,$key,$name,$tel);
          break;
        //开始/结束活动
        case 10150:
          $token = isset($_GET['token'])?$_GET['token']:'';
          $id = isset($_GET['id'])?$_GET['id']:'';
          $actsta = isset($_GET['actsta'])?$_GET['actsta']:'';
          $user_id = isset($_GET['uid'])?$_GET['uid']:'';
          start_act($token,$id,$actsta,$user_id);
          break;
        //活动列表
        case 10151:
          $token = isset($_GET['token'])?$_GET['token']:'';
          $actsta = isset($_GET['status'])?$_GET['status']:'';
          $user_id = isset($_GET['uid'])?$_GET['uid']:'';
          act_list($token,$actsta,$user_id);
          break;
        //完善手环信息
        case 10152:
          $token = isset($_GET['token'])?$_GET['token']:'';
          $stu_img = isset($_GET['stu_img'])?$_GET['stu_img']:'';
          $stu_name = isset($_GET['stu_name'])?$_GET['stu_name']:'';
          $log = isset($_GET['log'])?$_GET['log']:'';
          $key = isset($_GET['key'])?$_GET['key']:'';
          perfect_usb($token,$stu_img,$stu_name,$log,$key);
          break;
        //检测手环是否激活
        case 10153:
          $token = isset($_GET['token'])?$_GET['token']:'';
          $key = isset($_GET['key'])?$_GET['key']:'';
          check_usb_status($token,$key);
          break;
        //删除活动
        case 10154:
          $token = isset($_GET['token'])?$_GET['token']:'';
          $id = isset($_GET['id'])?$_GET['id']:'';
          del_act($token,$id);
          break;
        //家长扫码领取学分
        case 10155:
          $token = isset($_GET['token'])?$_GET['token']:'';
          $key = isset($_GET['key'])?$_GET['key']:'';
          $user_id = isset($_GET['uid'])?$_GET['uid']:'';
          receive_winmoney($token,$key,$user_id);
          break;
        //活动结束时排名
        case 10156:
          $token = isset($_GET['token'])?$_GET['token']:'';
          $actid = isset($_GET['actid'])?$_GET['actid']:'';
          member_ranking($token,$actid);
          break;
        //上传活动图片
        case 10159:
          $token = isset($_GET['token'])?$_GET['token']:'';
          $actid = isset($_GET['actid'])?$_GET['actid']:'';
          $img = isset($_GET['img'])?$_GET['img']:'';
          $img_type = isset($_GET['img_type'])?$_GET['img_type']:'';
          upload_actimg($token,$actid,$img,$img_type);
          break;
        //查询用户是否报名
        case 10177:
          $token = isset($_GET['token'])?$_GET['token']:'';
          $user_id = isset($_GET['uid'])?$_GET['uid']:'';
          $actid  = isset($_GET['actid'])?$_GET['actid']:'';
          check_user_join($token,$user_id,$actid);
          break;
        //家长扫码时显示活动信息
        case 10178:
          $token = isset($_GET['token'])?$_GET['token']:'';
          $key = isset($_GET['key'])?$_GET['key']:'';
          show_act_info($token,$key);
          break;
        //学生参加活动
        case 10016:
          $act_id = isset($_GET['actid'])?$_GET['actid']:'';
          $token = isset($_GET['token'])?$_GET['token']:'';
          $user_id = isset($_GET['uid'])?$_GET['uid']:'';
          act_join($token,$act_id,$user_id);
          break;
         //用户收到的消息
        case 10134:
          $token = isset($_GET['token'])?$_GET['token']:'';
          $user_id = isset($_GET['uid'])?$_GET['uid']:'';
          user_news($token,$user_id);
          break; 
        //获取用户发表的活动
        case 10017:
          $user_id = isset($_GET['uid'])?$_GET['uid']:'';
          act_get($user_id);
          break;
        //获取活动详情
        case 10018:
          $user_id = isset($_GET['id'])?$_GET['id']:'';
          act_get_info($id);
          break;
        //获取分享连接
        case 10019:
          $user_id = isset($_GET['user_id'])?$_GET['user_id']:'';
          act_share();
          break;
        //查询物流信息
        case 10020:
          $tracking_number = isset($_GET['tracking_number'])?$_GET['tracking_number']:'';
          $logistics_company = isset($_GET['logistics_company'])?$_GET['logistics_company']:'';
          search_logistics($tracking_number,$logistics_company,$type=0);
          break;
        //阿里云签名
         case 10033:
           upload_img($accessKeyId,$accessKeySecret,$endpoint,$bucket);
           break;
        //获取用户基本信息
         case 10035:
           $token = isset($_GET['token'])?$_GET['token']:'';
           $user_id = isset($_GET['uid'])?$_GET['uid']:'';
           $un_xlp = isset($_GET['un_xlp'])?$_GET['un_xlp']:'';
           user_detail($token,$user_id,$un_xlp);
           break;
         case 10115:
         //输出学圈列表
           $user_id = isset($_GET['uid'])?$_GET['uid']:'';
           $token = isset($_GET['token'])?$_GET['token']:'';
           cll_list($user_id);
           break;
         //学客圈点赞
         case 10119:
           $user_id = isset($_GET['uid'])?$_GET['uid']:'';  
           $id=isset($_GET['id'])?$_GET['id']:'';
           $token = isset($_GET['token'])?$_GET['token']:'';
           cll_put_like($user_id,$id,$token);
           break;
        //输出学圈详情
         case 10116:
           $id=isset($_GET['id'])?$_GET['id']:'';
           $user_id = isset($_GET['uid'])?$_GET['uid']:'';
           cll_content($id,$user_id);
           break;
        //学客圈发表
         case 10117:
           $user_id = isset($_GET['uid'])?$_GET['uid']:'';
           $token = isset($_GET['token'])?$_GET['token']:'';
           $content=isset($_GET['content'])?$_GET['content']:'';
           $thumb=isset($_GET['thumb'])?$_GET['thumb']:'';
           cll_put_list($token,$content,$thumb,$user_id);
           break;
        //学客圈评论
         case 10136:
           $user_id = isset($_GET['uid'])?$_GET['uid']:'';
           $token = isset($_GET['token'])?$_GET['token']:'';
           $content=isset($_GET['content'])?$_GET['content']:'';
           $id=isset($_GET['id'])?$_GET['id']:'';
           cll_put_rew($user_id,$content,$id,$token);
           break;
         //学客圈回复
         case 10079:
           $user_id = isset($_GET['uid'])?$_GET['uid']:'';
           $token = isset($_GET['token'])?$_GET['token']:'';
           $content=isset($_GET['content'])?$_GET['content']:'';
           $id=isset($_GET['id'])?$_GET['id']:'';
           $replier = isset($_GET['replier'])?$_GET['replier']:'';
           ccl_push_rew($user_id,$token,$content,$id,$replier);
           break;
         //删除评论
         case 10137:
           $rew_id = isset($_GET['rew_id'])?$_GET['rew_id']:'';
           $token = isset($_GET['token'])?$_GET['token']:'';
           $id=isset($_GET['id'])?$_GET['id']:'';
           del_ccl_rew($rew_id,$token,$id); 
           break;
         //删除学客圈
         case 10139:
           $token = isset($_GET['token'])?$_GET['token']:'';
           $id=isset($_GET['id'])?$_GET['id']:'';
           del_ccl($token,$id); 
           break;
         //用户的学客圈
         case 10138:
           $token = isset($_GET['token'])?$_GET['token']:'';
           $user_id=isset($_GET['uid'])?$_GET['uid']:'';
           user_ccl($token,$user_id);
           break; 
         //学客圈评论列表
         case 10048:
           $token = isset($_GET['token'])?$_GET['token']:'';
           $id = isset($_GET['id'])?$_GET['id']:'';
           cll_get_rew($token,$id);
           break;
         //读取商品分类
         case 10030:   
           get_goodstype();
           break;
         //读取商品列表
         case 10031:   
           $gtypeid=isset($_GET['gtypeid'])?$_GET['gtypeid']:'';
           $gtype=isset($_GET['gtype'])?$_GET['gtype']:'';
           $num=isset($_GET['num'])?$_GET['num']:'';
           get_goods($gtypeid,$gtype,$num);
           break;
         //读取商品详情
         case 10032:   
           $goodsid=isset($_GET['goodsid'])?$_GET['goodsid']:'';
           get_gcontent($goodsid);
           break;
         //读取收货地址列表
         case 10113:
           $token = isset($_GET['token'])?$_GET['token']:'';
           $user_id = isset($_GET['uid'])?$_GET['uid']:'';
           $is_default = isset($_GET['is_default'])?$_GET['is_default']:'0';
           get_address($user_id,$is_default,$token);
           break;
         //添加收货地址
         case 10102:
           $token = isset($_GET['token'])?$_GET['token']:'';
           $address = isset($_GET['address'])?$_GET['address']:'';
           $user_id  = isset($_GET['uid'])?$_GET['uid']:'';
           $consignee = isset($_GET['consignee'])?$_GET['consignee']:'';
           $province = isset($_GET['province'])?$_GET['province']:'';
           $city = isset($_GET['city'])?$_GET['city']:'';
           $district = isset($_GET['district'])?$_GET['district']:'';
           $address = isset($_GET['address'])?$_GET['address']:'';
           $zipcode = isset($_GET['zipcode'])?$_GET['zipcode']:'';
           $tel = isset($_GET['tel'])?$_GET['tel']:'';
           $default = isset($_GET['default'])?$_GET['default']:'';
           add_address($user_id,$consignee,$province,$city,$district,$address,$zipcode,$tel,$default,$token);
           break;
         //编辑收货地址
         case 10103:
           $token = isset($_GET['token'])?$_GET['token']:''; 
           $address_id = isset($_GET['address_id'])?$_GET['address_id']:'';
           $address = isset($_GET['address'])?$_GET['address']:'';
           $user_id  = isset($_GET['user_id'])?$_GET['user_id']:'';
           $consignee = isset($_GET['consignee'])?$_GET['consignee']:'';
           $province = isset($_GET['province'])?$_GET['province']:'';
           $city = isset($_GET['city'])?$_GET['city']:'';
           $district = isset($_GET['district'])?$_GET['district']:'';
           $address = isset($_GET['address'])?$_GET['address']:'';
           $zipcode = isset($_GET['zipcode'])?$_GET['zipcode']:'';
           $tel = isset($_GET['tel'])?$_GET['tel']:'';
           $default = isset($_GET['default'])?$_GET['default']:'';
           edit_address($address_id,$user_id,$consignee,$province,$city,$district,$address,$zipcode,$tel,$default,$token);
           break;
         //删除收货地址
         case 10104:
           $token = isset($_GET['token'])?$_GET['token']:'';
           $id = isset($_GET['id'])?$_GET['id']:'';
           del_address($id,$token);
           break;
         //获取用户学分
         case 10130:
           $token = isset($_GET['token'])?$_GET['token']:'';
           $user_id = isset($_GET['uid'])?$_GET['uid']:'';
           get_winmoney($user_id,$token);
           break;
         //更新默认地址
         case 10106:
           $token = isset($_GET['token'])?$_GET['token']:'';
           $address_id = isset($_GET['address_id'])?$_GET['address_id']:'';
           $user_id = isset($_GET['uid'])?$_GET['uid']:'';
           update_address($address_id,$user_id,$token);
           break;   
         //插入订单
         case 10107:
           $token = isset($_POST['token'])?$_POST['token']:'';
           $user_id  = isset($_POST['uid'])?$_POST['uid']:'';
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
           $pay_type = isset($_POST['pay_type'])?$_POST['pay_type']:'';
           add_orders($user_id,$order_status,$shipping_status,$pay_status,$consignee,$tel,$province,$city,$district,$address,$goods_amount,$add_time,$zipcode,$goods_id,$goods_name,$goods_number,$send_num,$is_real,$goods_price,$token,$pay_type);
           break;
        //查看订单
        case 10108:
          $token = isset($_GET['token'])?$_GET['token']:'';
          $user_id = isset($_GET['uid'])?$_GET['uid']:'';
          get_user_orders($user_id,$token);
          break;
        //支付完成时更新订单
        case 10109:
          $token = isset($_GET['token'])?$_GET['token']:'';
          $user_id  = isset($_GET['uid'])?$_GET['uid']:'';
          $goods_name = isset($_GET['goods_name'])?$_GET['goods_name']:'';
          $total = isset($_GET['total'])?$_GET['total']:'';
          $winmoney = isset($_GET['winmoney'])?$_GET['winmoney']:'';
          $pay_mode = isset($_GET['pay_mode'])?$_GET['pay_mode']:'';
          update_order($user_id,$goods_name,$total,$winmoney,$pay_mode,$token);
          break;
        //获取未收货订单
        case 10110:
          $token = isset($_GET['token'])?$_GET['token']:'';
          $user_id = isset($_GET['uid'])?$_GET['uid']:'';
          get_unfinished_orders($user_id,$token);
          break;
        //获取完成的订单
        case 100019:
          $openid = isset($_GET['openid'])?$_GET['openid']:'';
          get_finished_orders($openid); 
          break;
        //获取退换货的订单
        case 10111:
          $token = isset($_GET['token'])?$_GET['token']:'';
          $user_id = isset($_GET['uid'])?$_GET['uid']:'';
          get_return_orders($user_id,$token);
          break;
         //读取单条地址
        case 10114:
          $token = isset($_GET['token'])?$_GET['token']:'';
          $id = isset($_GET['id'])?$_GET['id']:'';
          get_oneaddres($id,$token);
          break;
        //订单详情
        case  10129:
          $token = isset($_GET['token'])?$_GET['token']:'';
          $order_id = isset($_GET['order_id'])?$_GET['order_id']:'';
          order_detail($token,$order_id);
          break;
        //确认收货
        case 10131:
          $token = isset($_GET['token'])?$_GET['token']:'';
          $order_id = isset($_GET['order_id'])?$_GET['order_id']:'';
          order_over($token,$order_id); 
          break;
        //申请退货
        case 10132:
          $token = isset($_GET['token'])?$_GET['token']:'';
          $order_id = isset($_GET['order_id'])?$_GET['order_id']:'';
          apply_for_return($token,$order_id); 
          break;
        //查看附近的活动
        case 10161:
          $token = isset($_GET['token'])?$_GET['token']:'';
          $latitude= isset($_GET['latitude'])?$_GET['latitude']:'';
          $longitude = isset($_GET['longitude'])?$_GET['longitude']:'';
          near_act($token,$latitude,$longitude); 
          break;
        //学客圈和用户搜索
        case 10140:
          $token = isset($_GET['token'])?$_GET['token']:'';
          $name = isset($_GET['name'])?$_GET['name']:'';
          $user_id = isset($_GET['uid'])?$_GET['uid']:'';
          $role = isset($_GET['role'])?$_GET['role']:'';
          search_ccl_user($token,$name,$user_id,$role);
          break;
        //获取用户报名的活动
        case 10028:
          $user_id = isset($_GET['uid'])?$_GET['uid']:'';
          $token = isset($_GET['token'])?$_GET['token']:'';
          $actsta = isset($_GET['status'])?$_GET['status']:'';
          get_user_act($user_id,$token,$actsta);
          break;
        //app发起微信支付
        case 10029:
          $token = isset($_POST['token'])?$_POST['token']:'';
          $user_id = isset($_POST['uid'])?$_POST['uid']:'';
          $order_sn = isset($_POST['order_sn'])?$_POST['order_sn']:'';
          $goods_name = isset($_POST['goods_name'])?$_POST['goods_name']:'';
          $order_total = isset($_POST['order_total'])?$_POST['order_total']:'';
          weixin_pay($token,$user_id,$order_sn,$goods_name,$order_total);
          break;
        //清空用户消息
        case 10146:
          $token = isset($_GET['token'])?$_GET['token']:'';
          $id = isset($_GET['id'])?$_GET['id']:'';
          $user_id = isset($_GET['uid'])?$_GET['uid']:'';
          empty_news($token,$id,$user_id);
          break;
        //系统消息
        case 10167:
          $token = isset($_GET['token'])?$_GET['token']:'';
          $user_id = isset($_GET['uid'])?$_GET['uid']:'';
          system_news($token,$user_id);
          break;
        //微信回调信息
        case 10190:
          $token = isset($_POST['token'])?$_POST['token']:'';
          $order_sn = isset($_POST['order_sn'])?$_POST['order_sn']:'';
          update_order_status($token,$order_sn);
          break;
        //短信验证码
        case 10042:
          new Config();
          Config::load();
          $phone = isset($_GET['phone'])?$_GET['phone']:'';
          zuce_user($phone);
          break;
        case 10045:
          print_code();
          break;
        //查看打分记录
        case 10046:
          $token = isset($_GET['token'])?$_GET['token']:'';
          $actid = isset($_GET['actid'])?$_GET['actid']:'';
          show_grade_record($actid,$token);
          break;
        //读取商城首页广告
        case 10026:
          $ad_type=isset($_GET['ad_type'])?$_GET['ad_type']:'';
          get_adhome($ad_type);
          break;
        //学生家长用户删除自己已经报名的活动
        case 10027:
          $token = isset($_GET['token'])?$_GET['token']:''; 
          $id = isset($_GET['apply_id'])?$_GET['apply_id']:'';
          update_act_display($id,$token);
          break;
        //清空系统消息
        case 10050:
          $token = isset($_GET['token'])?$_GET['token']:'';
          $id = isset($_GET['id'])?$_GET['id']:'';
          empty_system_news($token,$id);
          break;
        //取消订单
        case 10051:
          $token = isset($_GET['token'])?$_GET['token']:'';
          $order_sn = isset($_GET['order_sn'])?$_GET['order_sn']:'';
          cancel_order($token,$order_sn);
          break;
        //删除订单
        case 10052:
          $token = isset($_GET['token'])?$_GET['token']:'';
          $order_sn = isset($_GET['order_sn'])?$_GET['order_sn']:'';
          delete_order($token,$order_sn);
          break;
        //代理商邀请的用户
        case 10053:
          $token = isset($_GET['token'])?$_GET['token']:'';
          $user_id = isset($_GET['uid'])?$_GET['uid']:'';
          check_subordinate($token,$user_id);
          break;
        //代理商下级具体收益
        case 10054:
          $token = isset($_GET['token'])?$_GET['token']:'';
          $user_id = isset($_GET['uid'])?$_GET['uid']:'';
          detail_earnings($token,$user_id);
          break;
        //代理商一周收益
        case 10055:
          $token = isset($_GET['token'])?$_GET['token']:'';
          $user_id = isset($_GET['uid'])?$_GET['uid']:'';
          week_earnings($token,$user_id);
          break;
        //代理商详细收益(每天)
        case 10056:
          $token = isset($_GET['token'])?$_GET['token']:'';
          $user_id = isset($_GET['uid'])?$_GET['uid']:'';
          dayily_earnings($token,$user_id);
          break;
        //代理商收入(下级投资)
        case 10057:
          $token = isset($_GET['token'])?$_GET['token']:'';
          $user_id = isset($_GET['uid'])?$_GET['uid']:'';
          lower_income($token,$user_id);
          break;
        //用户投资
        case 10058:
          $token = isset($_GET['token'])?$_GET['token']:'';
          $user_id = isset($_GET['uid'])?$_GET['uid']:'';
          $money = isset($_GET['money'])?$_GET['money']:'';
          user_investment($token,$user_id,$money);
          break;
        //代理商发起提现
        case 10059:
          $token = isset($_GET['token'])?$_GET['token']:'';
          $user_id = isset($_GET['user_id'])?$_GET['user_id']:'';
          $money = isset($_GET['money'])?$_GET['money']:'';
          $w_id = isset($_GET['w_id'])?$_GET['w_id']:'';
          $code = isset($_GET['code'])?$_GET['code']:'';
          agent_withdraw($token,$user_id,$money,$w_id,$code);
          break;
        //代理商的下级
        case 10060:
          $token = isset($_GET['token'])?$_GET['token']:'';
          $user_id = isset($_GET['user_id'])?$_GET['user_id']:'';
          agent_subordinate($token,$user_id);
          break;
        //代理商的详细
        case 10061:
          $token = isset($_GET['token'])?$_GET['token']:'';
          $user_id = isset($_GET['user_id'])?$_GET['user_id']:'';
          agent_detail($token,$user_id);
          break;
        //按月份搜索代理商收益
        case 10062:
           $token = isset($_GET['token'])?$_GET['token']:'';
           $month = isset($_GET['month'])?$_GET['month']:'';
           $user_id = isset($_GET['user_id'])?$_GET['user_id']:'';
           earnings_search($token,$month,$user_id);
           break;




 




     

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
        $id=isset($_GET['id'])?$_GET['id']:'';
        $page=isset($_GET['page'])?$_GET['page']:'';
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
              $mapurl = isset($_POST['address'])?$_POST['address']:'';
              Master_add_ActInfo($token,$catid,$title,$actdesc, $actimgurl,$actcode,$crocode,$masterid,$mastername,$masterphone,$actstarttime,$actendtime,$joinstarttime,$joinendtime,$userlimit,$deposit,$actsta,$adminsta,$time,$lockstatus,$count,$content,$longitude,$latitude,$address,$arr,$mapurl);
        break;
        
      case 10006:////提交任务令编辑内容
        $id = isset($_POST['id'])?$_POST['id']:'';
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
              
              Master_edit_ActInfo($id,$token,$catid,$title,$actdesc, $actimgurl,$actcode,$crocode,$masterid,$mastername,$masterphone,$actstarttime,$actendtime,$joinstarttime,$joinendtime,$userlimit,$deposit,$actsta,$adminsta,$time,$lockstatus,$count,$content,$longitude,$latitude,$address,$arr,$mapurl);
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

