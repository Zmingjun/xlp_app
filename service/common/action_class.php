<?php 
use AliyunMNS\Client;
use AliyunMNS\Topic;
use AliyunMNS\Constants;
use AliyunMNS\Model\MailAttributes;
use AliyunMNS\Model\SmsAttributes;
use AliyunMNS\Model\BatchSmsAttributes;
use AliyunMNS\Model\MessageAttributes;
use AliyunMNS\Exception\MnsException;
use AliyunMNS\Requests\PublishMessageRequest;
use AliyunMNS\Requests\CreateTopicRequest;
use OSS\OssClient;  
use OSS\Core\OssException;
use Aliyun\Core\Profile\DefaultProfile;
use Aliyun\Core\DefaultAcsClient;
use Aliyun\Api\Sms\Request\V20170525\SendSmsRequest;
use Aliyun\Api\Sms\Request\V20170525\QuerySendDetailsRequest;
use JPush\Jgpush;
//1245645645612346
//项目项目重建54564456546546546654
费圣诞节嘎斯抠脚大汉放空间撒谎的发快件和速度快解放哈市将豆腐
 //curl方法
    function curl_get($url){        
             $curl2 = curl_init();                     
             curl_setopt($curl2, CURLOPT_URL, $url);
             curl_setopt($curl2, CURLOPT_HEADER, false);
             curl_setopt($curl2, CURLOPT_RETURNTRANSFER, 1);      
             $return = curl_exec($curl2);
             curl_close($curl2);            
             return $return;
         }

  /**
    * 验证用户信息
    */
    function post($url,$data){      
        $postdata = http_build_query($data);
        $opts = array('http' =>array('method'=>'POST','header'=>'Content-type: application/x-www-form-urlencoded','content'=>$postdata));
        $context = stream_context_create($opts);         
        $result = file_get_contents($url,true,$context);     
        return $result;
    }
    //根据授权code获取用户id 
   function weixin(){
    $appid="wxc144a54f3f0f0eb4";
    $secret="9cc5767a27d3930d4a587d99ba550a8c";
    $code=!empty($_GET['code'])?trim($_GET['code']):'';
    if(!$code){
        header("location:https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$appid."&redirect_uri=http%3a%2f%2fwechat.123win.com.cn&response_type=code&scope=snsapi_userinfo#wechat_redirect");exit;
    }
    $pdata=array(
        'appid'=>$appid,
        'secret'=>$secret,
        'code'=>$code,
        'grant_type'=>'authorization_code'
        );
    $data=post("https://api.weixin.qq.com/sns/oauth2/access_token",$pdata);
    $data=json_decode($data);
    $token=$data->access_token;
    $openid = $data->openid;
    put_user($openid,$token);
    echo  $data->openid;  
    }

    function weixin_pay($openid,$actid,$total)
    {
      
      $actpay=Act::act_pay_user($actid,$openid);
      
      if(!$actpay){           
       
          $time=time();                   
          $tools = new JsApiPay();
          $openId = $openid;
          $total = $total*100;
          //$openId = $tools->GetOpenid();
          //②、统一下单
          $input = new WxPayUnifiedOrder();
          $input->SetBody("活动报名费用");
          $input->SetAttach("test");
          $input->SetOut_trade_no(WxPayConfig::MCHID.date("YmdHis"));
          $input->SetTotal_fee($total);
          $input->SetTime_start(date("YmdHis"));
          $input->SetTime_expire(date("YmdHis", time() + 600));
          $input->SetGoods_tag("test");
          $input->SetNotify_url("https://wechat.123win.com.cn/service/php_sdk/weixin_pay/example/notify.php");
          $input->SetTrade_type("JSAPI");
          $input->SetOpenid($openId);
          $order = WxPayApi::unifiedOrder($input);
           //echo '<font color="#f00"><b>统一下单支付单信息</b></font><br/>';
           //printf_info($order);
          $jsApiParameters = $tools->GetJsApiParameters($order);

          //获取共享收货地址js函数参数
          $editAddress = $tools->GetEditAddressParameters();
           //echo json_encode($jsApiParameters, JSON_UNESCAPED_UNICODE);
          echo $jsApiParameters;
         }else
         {
           $cc = 1;
           echo json_decode($cc,JSON_UNESCAPED_UNICODE);
         }
    }
    
    function weixin_success_pay($openid,$actid)
    {   
       $actpay=Act::act_pay_user($actid,$openid);
        $act_payorder = 1;
        $time = time();     
        $user_info = get_user($openid);
          
             $bb=Act::put_pay_userdb($openid,$actid,$user_info['nickname'],$user_info['phone'],$act_payorder,$time);
             if($bb){
               $act=Act::get_actinfo_db($actid);
               $time=date('Y-m-d H:i',$act['actstarttime']);
               $ttime=date('Y-m-d H:i',time());
               $data='{
               "touser":"'.$openid.'",
               "template_id":"PBzuqbudfeTgga6KZsIoa3EJBEIZlg5rGTiflnelOI4",
               "url":"http://wechat.123win.com.cn/web/MyActivity.html?id=",        
               "data":{
                       "first": {
                           "value":"活动报名成功,请尽快签到！",
                           "color":"#173177"
                       },
                       "keyword1":{
                           "value":"'.$act['title'].'",
                           "color":"#173177"
                       },
                       "keyword2": {
                           "value":"'.$time.'",
                           "color":"#173177"
                       },
                       "keyword3": {
                           "value":"'.$act['address'].'",
                           "color":"#173177"
                       },
                       "remark":{
                           "value":"活动联系人:'.$act['mastername'].'  电话:'.$act['masterphone'].'！",
                           "color":"#FF7F00"
                       }
               }
             }';
             apply_suc($data);
             $cc=array();
             $aa=yz_token();
             $cc['token']=$aa;
             $cc['data']=array('states'=>'2');
             echo json_encode($cc, JSON_UNESCAPED_UNICODE);
             //echo json_encode(array('states'=>'2'));
         }
      
  }

    //根据openid 把用户信息写人数据库
    function put_user($openid,$token)
    {
       
    $user=User_info::getOpenid($openid);
    if(!$user){
      //$token=get_open_token($code);echo $token.'<hr/>';
      $url = "https://api.weixin.qq.com/sns/userinfo?access_token={$token}&openid={$openid}&lang=zh_CN";
          $json = file_get_contents($url);
          $std = json_decode($json);
          $nickname=$std->nickname;
          $sex=$std->sex;
          $city=$std->city;
          $country=$std->country;
          $province=$std->province;
          $headimgurl=$std->headimgurl;
          $subscribe_time=$regtime=time();
          //$openid=md5($openid);
          $userinfo=User_info::addUser($openid,$nickname,$sex,$city,$country,$province,$headimgurl,$regtime);
          $weixin=User_init_info::addUser($openid,$nickname,$sex,$city,$country,$province,$headimgurl,$subscribe_time);
          
           $_SESSION["userid"]=$openid;
         }else{
          //$openid=md5($openid);
           $_SESSION["userid"]=$openid;

         }
       }
        //查询活动信息10001
         function get($catid){
        //   // $act=Act::get_act();
        //   foreach ($act as $kk=>$vv) {
        //    $sum=Act::act_infolimit($vv['id']);
        //    $act[$kk]['sum']=$sum['limi'];
        //   }

        // if ($act) {  
        //   foreach ($act as &$v){
        //     if ($time < $v['actstarttime']){
        //        //活动未开始
              
        //        $v['actstate']=2;
        //        Act::up_actsta(2,$v['id']);
        //     }elseif($v['actstarttime'] < $time && $time < $v['actendtime']){
        //       //活动进行中
        //        $v['actstate']=1;
        //        Act::up_actsta(1,$v['id']);
        //     }elseif($time > $v['actendtime']){
        //       //活动已结束
        //        $v['actstate']=0;
        //        Act::up_actsta(0,$v['id']);  
        //     }
        //      $qiandao=Act::act_time_user($v['id'],$openid);
        //        if($qiandao['id']){
        //          $type=1;
        //          $sign=Act::act_sign_user($type,$v['id'],$openid);
        //          $v['actsign']=1;
        //        }
        //        if(!$qiandao['id'] && $v['actstate']==0){
        //         $type=2;
        //         $sign=Act::act_sign_user($type,$v['id'],$openid);
        //         $v['actsign']=2;
        //        }
        //   } 
          
        // }        if ($act) {
          $act = Act::get_act();
          $time = time();  
          foreach ($act as &$v){
            if ($time < $v['actstarttime']){
               //活动未开始
             //  echo $time;echo $v['actstarttime'];
               $v['actstate']=2;
               Act::up_actsta(2,$v['id']);
            }elseif($v['actstarttime'] < $time && $time < $v['actendtime']){
              //活动进行中
               $v['actstate']=1;
               Act::up_actsta(1,$v['id']);
            }elseif($time > $v['actendtime']){
              //活动已结束
               $v['actstate']=0;
               Act::up_actsta(0,$v['id']);  
            }
          }

          if (empty($catid)) {
            $bb=Act::get_act();
          }else{
            $bb=Act::get_typeact($catid);
          }
           if ($bb) {
           $cc=array();
           $aa=yz_token();
           $cc['token']=$aa;
           $cc['data']=$bb;
           echo json_encode($cc, JSON_UNESCAPED_UNICODE);
           }else{
            $cc=array();
           $aa=yz_token();
           $cc['token']=$aa;
           $cc['data']=array('states'=>'0');
           echo json_encode($cc, JSON_UNESCAPED_UNICODE);
             //echo json_encode(array('states'=>'0'));
          }
           
          
      }
        //查一条活动详细信息10002
        function get_actinfo($actid,$openid){
             $actinfo=Act::get_actinfo_db($actid);
             $checkact=Act::act_check_pay($actid,$openid);
             $sum=Act::act_infolimit($actid);
             $actinfo['limit']=$sum['limi'];
             //判断用户是否支付
              if ($checkact) {//如果数据存在
                if ($checkact['paystate'] == 0) {//如果等于0则未支付
                  // $cc['data']=array('test'=>'1');//未支付
                  $actinfo['paystate']='1';
                }else{
                  // $cc['data']=array('test'=>'2');//已支付
                  $actinfo['paystate']='2';
                }
              }else{
                // $cc['data']=array('test'=>'0');//未报名
                $actinfo['paystate']='0';
              }
             if ($actinfo) {
             $label=explode(',',$actinfo['label']);
             foreach ($label as $k => $v) { 
                       $aa[$k]['title']=$v; 
                     }
                     $actinfo['label']=$aa;
             $cc=array();
             $aa=yz_token();
             $cc['token']=$aa;
             $cc['data']=$actinfo;  

             echo json_encode($cc, JSON_UNESCAPED_UNICODE); 
             $count=Act::put_actcount($actid);



             }else{
              $cc=array();       
         $aa=yz_token();
         $cc['token']=$aa;
         $cc['data']=array('states'=>'11');
         echo json_encode($cc, JSON_UNESCAPED_UNICODE);
            // echo json_encode(array('states'=>'11'));
             }
             
        }
        //列出队伍成员打分
        function put_trem_member($actid,$tem)
        {
              $result = Act::get_trem_member($actid,$tem);
              if($result)
              { 
                  echo json_encode($result, JSON_UNESCAPED_UNICODE);
              }
              else
              {
                  echo json_encode(array('states'=>'0')); 
              }
        }
        //给队伍成员打分
        function put_member_mark($userid,$rank,$actid,$tem,$marknum)
        {

             $result = Act::check_captain($userid,$actid,$tem);
             $act=Act::get_mark($actid);          
             
                if($result)
                {
                  $arr = Act::get_member_num($actid,$tem);
                  $num_count = $arr['count'];
                  $avg_mark = Act::get_temtotal($actid,$tem);
                  $redis = new redis();    
                  $redis->connect('127.0.0.1', 6379);
                  $redis->set('score',$marknum);                                        
                  $avg_mark = $avg_mark['total'] * 0.5 / $num_count;
                  $time=date("m月d日 H:i",time());
                  $captain = Act::get_captain_info($tem,$actid);
                  $total = Act::get_temtotal($actid,$tem);
                  $user = Act::get_userinfo($userid);
                  $count = count($rank);
                  $start = microtime(true); 
                  $ch_list = array();  
                  $multi_ch = curl_multi_init();
                  $arr = Act::get_temtotal($actid,$tem);
                  $mark = $arr['total'] * 0.1+$avg_mark;
                  $tem_userid = Act::get_userid($captain['userid']);
                  detail_score($actid,$tem_userid['id'],$mark);
                  //User_info::up_uwinmoney($tem_userid['id'],$mark);
                  $shouzhi = '+'.$mark;
                  App::banwinmoney('系统分配学分',$tem_userid['id'],$shouzhi,$actid);
                  $user_info = Act::get_user($captain['userid']);                 
                  $data='{
                         "touser":"'.$captain['userid'].'",
                         "template_id":"I0jaI-Hx4PnEp4XOzJZksTHrhZBOF4IzLp6K0ylsr9U",
                         "url":"https://wechat.123win.com.cn/web/creditRecord.html?id='.$actid.'",        
                         "data":{
                                 "first": {
                                     "value":"您好，系统给你分配了'.$mark.'分，所获取的分数已存进学分账户，请查看",
                                     "color":"#173177"
                                 },
                                 "keyword1":{
                                     "value":"'.$time.'",
                                     "color":"#173177"
                                 },
                                 "keyword2": {
                                     "value":"'.$mark.'分",
                                     "color":"#173177"
                                 },
                                 "keyword3": {
                                     "value":"系统分配",
                                     "color":"#173177"
                                 },
                                 "keyword4": {
                                     "value":"'.$user_info['winmoney'].'",
                                     "color":"#173177"
                                 },
                                 "remark":{
                                     "value":"谢谢您的合作！",
                                     "color":"#173177"
                                 }
                         }
                       }';
                    apply_suc($data);
                  
           
              for($i = 0; $i<$count;$i++)
             {         
                   //评分成功

                   if($total['total'] - $rank[$i]['mark'] > 0)
                  { 
                    
                    //Act::del_ranking($rank[$i]['mark'],$actid,$tem);
                    $to_openid = get_openid($rank[$i]['userid']);
                    $result = Act::get_user($rank[$i]['userid']);
                    $only_mark = $rank[$i]['mark'] + $avg_mark;
                    detail_score($actid,$rank[$i]['userid'],$only_mark);
                    //User_info::up_uwinmoney($rank[$i]['userid'],$only_mark);
                    $mark = '+'.$rank[$i]['mark'];
                    App::banwinmoney('队长打分',$rank[$i]['userid'],$mark,$actid);
                    $result = Act::get_user($to_openid);
                     $data='{
                         "touser":"'.$to_openid.'",
                         "template_id":"I0jaI-Hx4PnEp4XOzJZksTHrhZBOF4IzLp6K0ylsr9U",
                         "url":"https://wechat.123win.com.cn/web/creditRecord.html?id='.$actid.'",        
                         "data":{
                                 "first": {
                                     "value":"您的队长'.$user['nickname'].'给您打了'.$rank[$i]['mark'].'分，所获取的分数已存进学分账户，请查看",
                                     "color":"#173177"
                                 },
                                 "keyword1":{
                                     "value":"'.$time.'",
                                     "color":"#173177"
                                 },
                                 "keyword2": {
                                     "value":"'.$rank[$i]['mark'].'分",
                                     "color":"#173177"
                                 },
                                 "keyword3": {
                                     "value":"队长打分",
                                     "color":"#173177"
                                 },
                                 "keyword4": {
                                     "value":"'.$result['winmoney'].'",
                                     "color":"#173177"
                                 },
                                 "remark":{
                                     "value":"谢谢您的合作！",
                                     "color":"#173177"
                                 }
                         }
                       }';
                  $token=get_token();    
                  $ch_list[$i] = curl_init("https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=$token");  
                  curl_setopt($ch_list[$i],CURLOPT_POSTFIELDS,$data);  
                  curl_multi_add_handle($multi_ch, $ch_list[$i]);  
            }
            else
            {
               echo json_encode(array('states'=>'0'));exit;
               //超出打分范围
            }
            
         }
            curl_mulit($start,$multi_ch,$ch_list);
        }
        else
        {   
            //你不是队长
            echo json_encode(array('states'=>'2'));exit;
        }

    }
        //处理上限分数
       function detail_score($actid,$userid,$mark)
       {  
          $score = Act::get_act_winmoney($actid,$userid);
          $setvalue = Act::score_limit($actid);
          if($score['count']<$setvalue&&$mark+$score['count']<$setvalue)
          {
             User_info::up_uwinmoney($userid,$mark);
          }
          else                                                                     
          {
             $total = Goods::get_winmoney($userid); 
             $mark = $setvalue-$score['count'];
             $openid = get_openid($userid);
             if($mark>=0)
            {
               User_info::up_uwinmoney($userid,$mark);     
            }
          }
       }

        //列出该活动的所有职位
       function get_jobname($actid)
       {
           $result = Act::get_jobname($actid);
           if($result)
           {
             echo json_encode($result,JSON_UNESCAPED_UNICODE);
           }
           else
           {
              echo json_encode(array('states'=>'0'));
           }
       }
       
       //列出加油列表
       function check_cheer($actid,$openid)
       {
           $result['data'] = Act::check_cheer($actid,$openid);                                
           $data = Act::job_select_user($openid,$actid);
           $result['poll'] = $data['poll'];
           $info = Act::get_user($openid);
           $result['info'] = $info;
           if($result)
           {
             echo json_encode($result,JSON_UNESCAPED_UNICODE);
           }
           else
           {
              echo json_encode(array('states'=>'0'));
           }

       }
        function get_temtotal($actid,$tem)
        {
           $result = Act::get_temtotal($actid,$tem);
           if($result)
           {      
              
              $redis = new redis();    
              $redis->connect('127.0.0.1', 6379);
              //echo $redis->get('score'); 
              if(!$redis->get('score'))
              {
                $usable_score = $result['total']*0.4;
                $redis->set('score',$usable_score);
                $redis->expire('score',86400);
                $redis['score'] = $usable_score;
              }
              else
              {
                $result['score'] = $redis->get('score');
              } 
             echo json_encode($result);exit; 
           }
           else
           {
             echo json_encode(array('states'=>'0'));
           }
        }
        //查询用户是否是队长
        function check_captain($actid,$tem,$userid)
        {
            $result = User_info::check_captain($userid,$actid,$tem);
            if($result)
            {
               echo json_encode(array('states'=>'1'));
            }else
            {
               echo json_encode(array('states'=>'0'));
            }
        }
        //队伍项目比赛获得的排名获取积分
        function put_tream_mark($actid,$rank,$pro_name)
        {
          $trem_num = Act::act_infolimit($actid);
          $trem_num = $trem_num['limi'];
         
          foreach($rank as $k => $v)
          {
              switch($rank[$k]['ranking'])
              {
                  case '1':
                       $tem_name = altroops($rank[$k]['tem']);
                       $mark = $trem_num * 5;
                      // $rank=Act::put_ranking($mark,$actid,$rank[$k]['tem']);
                       
                          //评分成功
                       $start = microtime(true); 
                       $ch_list = array();  
                       $multi_ch = curl_multi_init();
                       $result = Act::get_trem_userid($rank[$k]['tem'],$actid,$mark);
                      //Act::put_ranking($mark,$actid,$rank[$k]['tem']);
                       $count = count($result);
                    for ($i = 0;$i < $count; ++$i){  
                      $time=date("m月d日 H:i",time());
                      $data='{
                           "touser":"'.$result[$i]['userid'].'",
                           "template_id":"rLuuCqF9RwYgzJ31nPFcjYbAHWoSgu2DaN1Edj3Wy4o",
                           "url":"",        
                           "data":{
                                   "first": {
                                       "value":"您好，有一条来自活动中心的提醒！",
                                       "color":"#173177"
                                   },
                                   "keyword1":{
                                       "value":"'.$pro_name.'活动排名",
                                       "color":"#173177"
                                   },
                                   "keyword2": {
                                       "value":"'.$pro_name.'活动排名开始",
                                       "color":"#173177"
                                   },
                                   "keyword3": {
                                       "value":"'.$time.'",
                                       "color":"#173177"
                                   },
                                   "keyword4": {
                                       "value":"您的队伍:'.$tem_name.'在'.$pro_name.'活动排名中获得第一名！",
                                       "color":"#173177"
                                   },
                                   "remark":{
                                       "value":"谢谢您的合作！",
                                       "color":"#173177"
                                   }
                           }
                         }';
                        $token=get_token();    
                        $ch_list[$i] = curl_init("https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=$token");  
                        curl_setopt($ch_list[$i],CURLOPT_POSTFIELDS,$data);  
                        curl_multi_add_handle($multi_ch, $ch_list[$i]);  
                      }
                       curl_mulit($start,$multi_ch,$ch_list);
                     
                  break;                 
                  case '2':
                       $tem_name = altroops($rank[$k]['tem']);
                       $mark = $trem_num * 2;
                       //$rank=Act::put_ranking($mark,$actid,$rank[$k]['tem']);
                       // if ($rank){
                          //评分成功
                          $start = microtime(true); 
                          $ch_list = array();  
                          $multi_ch = curl_multi_init();
                          $result = Act::get_trem_userid($rank[$k]['tem'],$actid,$mark);
                          $count = count($result);
                      for ($i = 0;$i < $count; ++$i){  
                        $time=date("m月d日 H:i",time());
                        $data='{
                             "touser":"'.$result[$i]['userid'].'",
                             "template_id":"rLuuCqF9RwYgzJ31nPFcjYbAHWoSgu2DaN1Edj3Wy4o",
                             "url":"",        
                             "data":{
                                     "first": {
                                         "value":"您好，有一条来自活动中心的提醒！",
                                         "color":"#173177"
                                     },
                                     "keyword1":{
                                         "value":"'.$pro_name.'活动排名",
                                         "color":"#173177"
                                     },
                                     "keyword2": {
                                         "value":"'.$pro_name.'活动排名开始",
                                         "color":"#173177"
                                     },
                                     "keyword3": {
                                         "value":"'.$time.'",
                                         "color":"#173177"
                                     },
                                     "keyword4": {
                                         "value":"您的队伍:'.$tem_name.'在'.$pro_name.'活动排名中获得第二名！",
                                         "color":"#173177"
                                     },
                                     "remark":{
                                         "value":"谢谢您的合作！",
                                         "color":"#173177"
                                     }
                             }
                           }';
                      $token=get_token();    
                      $ch_list[$i] = curl_init("https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=$token");  
                      curl_setopt($ch_list[$i],CURLOPT_POSTFIELDS,$data);  
                      curl_multi_add_handle($multi_ch, $ch_list[$i]);  
                    }
                      curl_mulit($start,$multi_ch,$ch_list);
                     // echo json_encode(array('states'=>'1'));
                       //echo json_encode(array('states'=>'1'));
                       // }
                   break;                  
                   case '3':
                       $tem_name = altroops($rank[$k]['tem']);
                       $mark = $trem_num * 1;
                       // $rank=Act::put_ranking($mark,$actid,$rank[$k]['tem']);
                       // if ($rank){
                          //评分成功
                      $start = microtime(true); 
                      $ch_list = array();  
                      $multi_ch = curl_multi_init();
                      $result = Act::get_trem_userid($rank[$k]['tem'],$actid,$mark);
                      $count = count($result);
                      for ($i = 0;$i < $count; ++$i){  
                        $time=date("m月d日 H:i",time());
                        $data='{
                             "touser":"'.$result[$i]['userid'].'",
                             "template_id":"rLuuCqF9RwYgzJ31nPFcjYbAHWoSgu2DaN1Edj3Wy4o",
                             "url":"",        
                             "data":{
                                     "first": {
                                         "value":"您好，有一条来自活动中心的提醒！",
                                         "color":"#173177"
                                     },
                                     "keyword1":{
                                         "value":"'.$pro_name.'活动排名",
                                         "color":"#173177"
                                     },
                                     "keyword2": {
                                         "value":"'.$pro_name.'活动排名开始",
                                         "color":"#173177"
                                     },
                                     "keyword3": {
                                         "value": "'.$time.'",
                                         "color":"#173177"
                                     },
                                     "keyword4": {
                                         "value":"您的队伍:'.$tem_name.'在'.$pro_name.'活动排名中获得第三名！",
                                         "color":"#173177"
                                     },
                                     "remark":{
                                         "value":"谢谢您的合作！",
                                         "color":"#173177"
                                     }
                             }
                           }';
                      $token=get_token();    
                      $ch_list[$i] = curl_init("https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=$token");  
                      curl_setopt($ch_list[$i],CURLOPT_POSTFIELDS,$data);  
                      curl_multi_add_handle($multi_ch, $ch_list[$i]);  
                    }
                    curl_mulit($start,$multi_ch,$ch_list);
                    //echo json_encode(array('states'=>'1'));
                       //echo json_encode(array('states'=>'1'));
                       // }
                    break;                 
                    case '4':
                       $tem_name = altroops($rank[$k]['tem']);
                       $mark = $trem_num * 0.5;
                       // $rank=Act::put_ranking($mark,$actid,$rank[$k]['tem'],$mark);
                       // if ($rank){
                          //评分成功
                      $start = microtime(true); 
                      $ch_list = array();  
                      $multi_ch = curl_multi_init();
                      $result = Act::get_trem_userid($rank[$k]['tem'],$actid,$mark);
                      $count = count($result);
                      for ($i = 0;$i < $count; ++$i){  
                        $time=date("m月d日 H:i",time());
                        $data='{
                             "touser":"'.$result[$i]['userid'].'",
                             "template_id":"rLuuCqF9RwYgzJ31nPFcjYbAHWoSgu2DaN1Edj3Wy4o",
                             "url":"",        
                             "data":{
                                     "first": {
                                         "value":"您好，有一条来自活动中心的提醒！",
                                         "color":"#173177"
                                     },
                                     "keyword1":{
                                         "value":"'.$pro_name.'活动排名",
                                         "color":"#173177"
                                     },
                                     "keyword2": {
                                         "value":"'.$pro_name.'活动排名开始",
                                         "color":"#173177"
                                     },
                                     "keyword3": {
                                         "value":"'.$time.'",  
                                         "color":"#173177"
                                     },
                                     "keyword4": {
                                         "value":"您的队伍:'.$tem_name.'在'.$pro_name.'活动排名中获得第四名！",
                                         "color":"#173177"
                                     },
                                     "remark":{
                                         "value":"谢谢您的合作！",
                                         "color":"#173177"
                                     }
                             }
                           }';
                      $token=get_token();    
                      $ch_list[$i] = curl_init("https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=$token");  
                      curl_setopt($ch_list[$i],CURLOPT_POSTFIELDS,$data);  
                      curl_multi_add_handle($multi_ch, $ch_list[$i]);  
                    }
                    curl_mulit($start,$multi_ch,$ch_list);   
                    break;
               }
            }
        }

        function check_trem_member($actid,$tem)
        {
            $result = Act::check_trem_member($actid,$tem);
            if($result)
            {
                echo json_encode($result, JSON_UNESCAPED_UNICODE);
             
            }
            else
            {
                echo json_encode(array('states'=>'0'));
            }   
          
        }
        //根据活动id 用户id判断用户是否已参加活动
         function act_user($actid,$openid){
           $bb=Act::act_pay_user($actid,$openid);
           if($bb){
             $cc=array();
             $aa=yz_token();
             $cc['token']=$aa;
             $cc['data']=array('states'=>'4');
             echo json_encode($cc, JSON_UNESCAPED_UNICODE);
           }else{
             $cc=array();
             $aa=yz_token();
             $cc['token']=$aa;
             $cc['data']=array('states'=>'5');
             echo json_encode($cc, JSON_UNESCAPED_UNICODE);
            }
        }

        function delete_endact($id,$actid)
        {  
            //查看活动状态，只能删除已结束的
            $acttype = Act::get_actsta($actid);
            
            if($acttype['actsta'])
            {   
                   
                 echo json_encode(array('states'=>'0')); //活动未结束,不能删除 
            }
            else
            {             
                Act::delete_act($id);
                echo json_encode(array('states'=>'1'));
            }

        } 

        function delete_ccl($id)
        {
           $bb = Master::del_admin_ccl($id);
           if($bb)
           {
             echo json_encode(array('states'=>'1'));
           }else
           {
             echo json_encode(array('states'=>'0'));
           }
        }
        //活动报名
         function put_pay_user($openid,$actid,$nickname,$phone){
          if($openid=="" || $phone=="" || $nickname==""){
           echo json_encode(array('states'=>'0'));
           }else{
              $actpay=Act::act_pay_user($actid,$openid);
              $actendtime=Act::act_info_user($actid);
              $get_actinfo=Act::get_actinfo_db($actid);
              $limit=Act::act_infolimit($actid);
          if (($actendtime['userlimit'] > $limit['limi']) || ($actendtime['userlimit'] == 0)){
            if (!$actpay){
              if($actendtime['joinendtime']>time()){
                $time=time();
                //判断活动是否收费
                if ($get_actinfo['deposit']=='0') {//如果等于零则是免费
                            $act_payorder='1';

                $bb=Act::put_pay_userdb($openid,$actid,$nickname,$phone,$act_payorder,$time);
                       if($bb){
                         $act=Act::get_actinfo_db($actid);
                         $time=date('Y-m-d H:i',$act['actstarttime']);
                         $ttime=date('Y-m-d H:i',time());
                         $data='{
                         "touser":"'.$openid.'",
                         "template_id":"PBzuqbudfeTgga6KZsIoa3EJBEIZlg5rGTiflnelOI4",
                         "url":"http://wechat.123win.com.cn/web/MyActivity.html?id='.$actid.'",        
                         "data":{
                                 "first": {
                                     "value":"活动报名成功,请尽快签到！",
                                     "color":"#173177"
                                 },
                                 "keyword1":{
                                     "value":"'.$act['title'].'",
                                     "color":"#173177"
                                 },
                                 "keyword2": {
                                     "value":"'.$time.'",
                                     "color":"#173177"
                                 },
                                 "keyword3": {
                                     "value":"'.$act['address'].'",
                                     "color":"#173177"
                                 },
                                 "remark":{
                                     "value":"活动联系人:'.$act['mastername'].'  电话:'.$act['masterphone'].'！",
                                     "color":"#FF7F00"
                                 }
                         }
                       }';
                       apply_suc($data);
                       $cc=array();
                       $aa=yz_token();
                       $cc['token']=$aa;
                       $cc['data']=array('states'=>'2');
                       echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                       //echo json_encode(array('states'=>'2'));
                   }else{
                    $cc=array();
                       $aa=yz_token();
                       $cc['token']=$aa;
                       $cc['data']=array('states'=>'5');
                       echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                    //echo json_encode(array('states'=>'5'));exit;
                    }
                  }
                }else{
                  $cc=array();
                       $aa=yz_token();
                       $cc['token']=$aa;
                       $cc['data']=array('states'=>'1');
                       echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                  //echo json_encode(array('states'=>'1'));exit;
                  }
            }else{
              $cc=array();
                       $aa=yz_token();
                       $cc['token']=$aa;
                       $cc['data']=array('states'=>'3');
                       echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                       //echo json_encode(array('states'=>'3'));exit;
              }
          }else{
            $cc=array();
                       $aa=yz_token();
                       $cc['token']=$aa;
                       if($actendtime['userlimit'])
                       {
                         $cc['data']=array('states'=>'4');
                         echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                       }
 
                         //echo json_encode(array('states'=>'4'));
            }
        }    
        }
        //根据openid列出当前用户参加的活动
        function get_act_user($openid,$acttype){
          if(!empty($openid)){
            $time=time();
           switch ($acttype) {
             case '1':
             //全部活动
               $act=Act::get_act_user($openid);
               if (!$act) {
                 echo json_encode(array('states'=>'0'));exit;
               }
               break;
             //未开始的活动  
               case '2':
               $act=Act::get_actw_user($openid);
               if (!$act) {
                 echo json_encode(array('states'=>'0'));exit;
               }
               break;
            //已结束活动
               case '3':
               $act=Act::get_actj_user($openid);
               if (!$act) {
                 echo json_encode(array('states'=>'0'));exit;
               }
               break;
           }
          foreach ($act as $kk=>$vv) {
           $sum=Act::act_infolimit($vv['id']);
           $act[$kk]['sum']=$sum['limi'];
          }

        if ($act) {  
          foreach ($act as &$v){
            if ($time < $v['actstarttime']){
               //活动未开始
             //  echo $time;echo $v['actstarttime'];
               $v['actstate']=2;
               Act::up_actsta(2,$v['id']);
            }elseif($v['actstarttime'] < $time && $time < $v['actendtime']){
              //活动进行中
               $v['actstate']=1;
               Act::up_actsta(1,$v['id']);
            }elseif($time > $v['actendtime']){
              //活动已结束
               $v['actstate']=0;
               Act::up_actsta(0,$v['id']);  
            }
             $qiandao=Act::act_time_user($v['id'],$openid);
               if($qiandao['id']){
                 $type=1;
                 $sign=Act::act_sign_user($type,$v['id'],$openid);
                 $v['actsign']=1;
               }
               if(!$qiandao['id'] && $v['actstate']==0){
                $type=2;
                $sign=Act::act_sign_user($type,$v['id'],$openid);
                $v['actsign']=2;
               }
          }
          /*$data='actstarttime actendtime joinstarttime joinendtime';
          $act=datime($data,$act,3);*/
          $cc=array();
                       $aa=yz_token();
                       $cc['token']=$aa;
                       $cc['data']=$act;
                       echo json_encode($cc, JSON_UNESCAPED_UNICODE);
          //echo $act=json_encode($act, JSON_UNESCAPED_UNICODE);
           }else{
            return false;exit;
           }
          }else{
            echo json_encode(array('states'=>'6'));exit;
          }
        }
        //根据openid列出当前用户可签到的活动
        function get_act_start($openid){ 
        $act=Act::get_act_start($openid);
        if($act){
          $cc=array();
                       $aa=yz_token();
                       $cc['token']=$aa;
                       $cc['data']=$act;
                       echo json_encode($cc, JSON_UNESCAPED_UNICODE);
        //echo  $act=json_encode($act, JSON_UNESCAPED_UNICODE);
        }else{
        //用户没有可以签到的活动
        $cc=array();
                       $aa=yz_token();
                       $cc['token']=$aa;
                       $cc['data']=array('states'=>'7');
                       echo json_encode($cc, JSON_UNESCAPED_UNICODE);
       // echo json_encode(array('states'=>'7'));exit;
      }
       
        }
        //用户签到
        function put_act_sign($openid,$actid,$lbs_x,$lbs_y){ 
          $lbs=Act::put_act_lbs($actid);
          $uid = get_userid($openid);
          $latitude=$lbs['latitude'];
          $longitude=$lbs['longitude'];
          $arr = User_info::getOpenid($openid);
         // var_dump($lbs_x);exit;
          $num=round(distance($lbs_x,$lbs_y,$latitude,$longitude));

          if($num <= 5000){
            $act=Act::put_act_sign($openid,$actid);
              if($act){
               $result = Act::check_user_success($openid,$actid);
               if(!$result)
              {
                $mintroop=mintroop($actid);   
              }else
              {
                $mintroop = $result['ready_tream'];  
              }       
               $time=time();
               $act=Act::sign_user($openid,$actid,$mintroop,$time);
               //Act::add_ranks($actid,$uid,$mintroop);
               $aa=Act::get_title($actid);
               $time=date("m月d日 H:i",time());
               if ($act) {
                 //签到成功并返回用户队伍
                $total = App::get_log_winmoney('用户签到');
                $total = $total['winmoney'];
                // $arr=Act::get_qiandao($actid);
                // foreach($arr as $k => $v)
                // {
                  
                // }
                User_info::up_uwinmoney($uid,$total);
                $total = '+'.$total;
                App::banwinmoney('用户签到',$uid,$total,$actid);
                  $data='{
                         "touser":"'.$openid.'",
                         "template_id":"24Gb7LxhpYELNLBtdl_LFGqOLGUo0RpxqZzQEW6i--U",
                         "url":"http://wechat.123win.com.cn/web/team.html?id='.$actid.'",        
                         "data":{
                                 "first": {
                                     "value":"'.$aa['title'].'活动签到成功！",
                                     "color":"#173177"
                                 },
                                 "keyword1":{
                                     "value":"'.$arr['nickname'].'",
                                     "color":"#173177"
                                 },
                                 "keyword2": {
                                     "value":"'.$aa['address'].'",
                                     "color":"#173177"
                                 },
                                 "keyword3":{
                                     "value":"'.$time.'",
                                     "color":"#173177"
                                 },                                 
                                 "remark":{
                                     "value":"亲爱的会员感谢您的到来！",
                                     "color":"#173177"
                                 }
                         }
                       }';
                       apply_suc($data);
                       $cc=array();
                       $gg=array('states'=>'8','troop'=>"$mintroop");
                       $aa=yz_token();
                       $cc['token']=$aa;
                       $cc['data']=$gg;
                       echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                 //echo json_encode(array('states'=>'8','troop'=>"$mintroop"));
               }else{
                //签到失败
                $cc=array();  
                       $aa=yz_token();
                       $cc['token']=$aa;
                       $cc['data']=array('states'=>'12');
                       echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                 //echo json_encode(array('states'=>'12'));exit;
               }
              }else{
                //用户已签到
                $cc=array();  
                       $aa=yz_token();
                       $cc['token']=$aa;
                       $cc['data']=array('states'=>'9');
                       echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                //echo json_encode(array('states'=>'9'));exit;
              }
          }else{
                //用户不在活动现场
                $cc=array();  
                       $aa=yz_token();
                       $cc['token']=$aa;
                       $cc['data']=array('states'=>'10');
                       echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                //echo json_encode(array('states'=>'10'));exit;
          }
        }
        //10008
        function get_act_msg($openid,$lbs_x,$lbs_y){
          $act=Act::get_act_pro($openid); 
          foreach ($act as  $v) {
           $lbs=Act::put_act_lbs($v['actid']);
           $num=round(distance($lbs_x,$lbs_y,$lbs['latitude'],$lbs['longitude']));
           if($num <= 5000){
              $actid=$v['actid'];
             }
          }
         
          $act=Act::get_pay_one($openid,$actid);

          $states=array();
          if ($act['adminsta']==1) {
            //签到状态
              $states['sign']=1;
          }/*elseif ($act['adminsta']==2) {
              $states['sign']=2;
          }*/
          if ($act['actsign']==2) {
                $states['sign']=2;
          }
          if ($act['actsign']==1) {
                $states['sign']=3;
          }
          if ($act['adminsta']==5) {
              $states['mark']=1;
          }else{
             $states['mark']=0;
          }
          if ($act['adminsta']==7) {
              $states['alma']=1;
          }else{
             $states['alma']=0;
          }
          if ($act['adminsta']==8) {
              $states['worker']=1;
          }else{
             $states['worker']=0;
          }
          if($act['adminsta']==6)
          {
            $states['end'] = 1;
          }else
          {
             $states['end'] = 0;
          }
          if($act['actsta'] == 0)
          {
             $states['actsta'] = 0;
          } 
          //读出打分次数
          $states['marknum']=Act::mark_num($actid)['marknum'];
          $states['actid']=$actid;
          $states['adminsta']=$act['adminsta'];
         $cc=array();       
         $aa=yz_token();
         $cc['token']=$aa;
         $cc['data']=$states;
         echo json_encode($cc, JSON_UNESCAPED_UNICODE);
          //echo json_encode($states, JSON_UNESCAPED_UNICODE);

        }
        //用户打分
        function put_tro_mark($openid,$actid,$marknum,$tem,$mark)
        {   

            $tro=Act::get_troop($actid,$openid);
            $troop=$tro['troops'];
            //echo $openid,$actid,$marknum,$tem,$mark;exit;
             //var_dump($tro); echo $tem; exit;
            // echo json_decode($tem);exit;
            if ($tem==$troop) {
              //用户不能打分给自己队伍
              echo json_encode(array('states'=>'5'));exit;
            }else{
            $act=Act::get_mark($actid);
           if($act['adminsta'] == 5){
            $sum=Act::get_scosum($actid,$openid,$marknum);
            $sum=$sum['sum'];
            $tosum=$sum+$mark;
            //$tosum=$marka+$markb+$markc+$markd;
            //var_dump($marknum);var_dump($act); var_dump($tosum);exit;
            if ($tosum <= $act['setvalue']) {
                if ($marknum==$act['marknum']){
                  //写入打分
                   $scotime=time();
                   $rank=Act::put_ranking($mark,$actid,$tem);
                   $mark_d=Act::put_tro_mark($openid,$actid,$tem,$marknum,$mark,$scotime);
                   if ($rank && $mark_d) {
                      //评分成功
                   $cc=array();       
                   $aa=yz_token();
                   $cc['token']=$aa;
                   $cc['data']=array('states'=>'1');
                   echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                   //echo json_encode(array('states'=>'1'));
                   }
                  
                }else{
                 //已打过分
                 $cc=array();       
                   $aa=yz_token();
                   $cc['token']=$aa;
                   $cc['data']=array('states'=>'2');
                   echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                 //echo json_encode(array('states'=>'2'));exit;
                }
            }else{
              //分值超过设置分数
             // var_dump($tosum); var_dump($act['setvalue']);exit; 
              $cc=array();       
                   $aa=yz_token();
                   $cc['token']=$aa;
                   $cc['data']=array('states'=>'3');
                   echo json_encode($cc, JSON_UNESCAPED_UNICODE);
             // echo json_encode(array('states'=>'3'));exit;
            }
           }else{
             //该活动不在打分状态
             $cc=array();       
                   $aa=yz_token();
                   $cc['token']=$aa;
                   $cc['data']=array('states'=>'4');
                   echo json_encode($cc, JSON_UNESCAPED_UNICODE);
             //echo json_encode(array('states'=>'4'));exit;
           }
           }
        }
        //获取openid
        function get_code($code)
        {
            $appid = "wxc144a54f3f0f0eb4";
            $appsecret = "9cc5767a27d3930d4a587d99ba550a8c";
            //获取openid
            $url="https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$appid."&secret=".$appsecret."&code=".$code."&grant_type=authorization_code";

            $result = https_request($url);

            $jsoninfo = json_decode($result, true);
            $openid = $jsoninfo["openid"];//从返回json结果中读出openid

            $callback=$_GET['callback'];  
             
            echo $openid; //把openid 送回前端 

        }
        function get_captain_info($tem,$actid)
        {
             $result = Act::get_captain_info($tem,$actid);
             if($result)
             {
                echo json_encode($result, JSON_UNESCAPED_UNICODE);exit;
             }
             else
             {
               echo json_encode(array('states'=>'0'));exit;
             }
        }
         function https_request($url,$data = null){
              $curl = curl_init();
              curl_setopt($curl, CURLOPT_URL, $url);
              curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
              curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
              if (!empty($data)){
                curl_setopt($curl, CURLOPT_POST, 1);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
              }
              curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
              $output = curl_exec($curl);
              curl_close($curl);
              return $output;
              }
         //10010 
        function get_ranking($actid)
        {
         $rank=Act::get_ranking($actid);
                   $cc=array();       
                   $aa=yz_token();
                   $cc['token']=$aa;
                   $cc['data']=$rank;
                   echo json_encode($cc, JSON_UNESCAPED_UNICODE);
        } 
        //获得全局token   
        function get_token()
        {
         // access_token 应该全局存储与更新，以下代码以写入到文件中
            $data =json_decode(file_get_contents("access_token.json"));
            if ($data->expire_time < time()) {    
              $url ="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wxc144a54f3f0f0eb4&secret=9cc5767a27d3930d4a587d99ba550a8c";
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_TIMEOUT, 500);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl, CURLOPT_URL, $url);
            $res = curl_exec($curl);
            curl_close($curl);
            //$res=file_get_contents($url);
            $res=json_decode($res);
              $access_token = $res->access_token;
              if ($access_token) {
                $data->expire_time = time() + 5000;
                $data->access_token = $access_token;
                $fp = fopen("access_token.json", "w");
                fwrite($fp, json_encode($data));
                fclose($fp);
              }
            } else {
              $access_token = $data->access_token;
            }
            return $access_token;
        }  
        //活动报名成功模板提醒
        function apply_suc($data)
        {
             $token=get_token();    
             $url="https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=$token";
             $act=get_url($url,$data);
             return $act;
        }

        //curl模拟请求
        function get_url($url,$data=null)
        {
          // 初始化一个 cURL 对象 
          $curl = curl_init(); 
          // 设置你需要抓取的URL 
          curl_setopt($curl, CURLOPT_URL,$url); 
          curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
          curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
          curl_setopt($curl, CURLOPT_HEADER, 1); 
          if (!empty($data)) {
          curl_setopt($curl,CURLOPT_POST, 1);  
          //传递的变量
          curl_setopt($curl,CURLOPT_POSTFIELDS,$data); 
          }
          // 设置cURL 参数，要求结果保存到字符串中还是输出到屏幕上。
          // 1如果成功只将结果返回，不自动输出任何内容。如果失败返回FALSE 
          curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); 
          // 运行cURL，请求网页 
          $data = curl_exec($curl); 
          // 关闭URL请求 
          curl_close($curl);
          return $data;
        }
        //处理curl批处理
        function curl_mulit($start,$multi_ch,$ch_list)
        {
           
            $active = null;  
            do {  
                $mrc = curl_multi_exec($multi_ch, $active); //处理在栈中的每一个句柄。无论该句柄需要读取或写入数据都可调用此方法。  
            } while ($mrc == CURLM_CALL_MULTI_PERFORM);   
            //Note:  
            //该函数仅返回关于整个批处理栈相关的错误。即使返回 CURLM_OK 时单个传输仍可能有问题。  
              
              
            while ($active && $mrc == CURLM_OK) {  
                if (curl_multi_select($multi_ch) != -1) { //阻塞直到cURL批处理连接中有活动连接。  
                    do {  
                    $mrc = curl_multi_exec($multi_ch, $active);  
                    } while ($mrc == CURLM_CALL_MULTI_PERFORM);  
                }  
            }  
            //获取http返回的结果  
            foreach ($ch_list as $k => $ch) {  
                $result=curl_multi_getcontent($ch);  
                curl_multi_remove_handle($multi_ch,$ch);  
                curl_close($ch);  
            }  
            curl_multi_close($multi_ch);  
            // $end = microtime(true);  
            // echo $end-$start; 
           
        }
        //10011
        function put_act(){
          //openid是openid或者token
          $openid=isset($_GET['openid'])?$_GET['openid']:'';
          switch (strlen($openid)) {
            case '28':
              $user=Act::get_user($openid);
              $masterid=$user['id'];
              $mastername=$user['nickname'];
              $masterphone=$user['tel'];
              break;
            case '32':
              $user=Act::get_user_token($openid);
              $masterid=$user['id'];
              $mastername=$user['nickname'];
              $masterphone=$user['tel'];
              break;
          }
          if ($_POST) {
            $title=isset($_POST['title'])?$_POST['title']:'';
            $catid=isset($_POST['catid'])?$_POST['catid']:'';
            $actdesc=isset($_POST['actdesc'])?$_POST['actdesc']:'';
            $actstarttime=isset($_POST['actstarttime'])?$_POST['actstarttime']:'';
            $actendtime=isset($_POST['actendtime'])?$_POST['actendtime']:'';
            $joinstarttime=isset($_POST['joinstarttime'])?$_POST['joinstarttime']:'';
            $joinendtime=isset($_POST['joinendtime'])?$_POST['joinendtime']:'';
            $userlimit=isset($_POST['userlimit'])?$_POST['userlimit']:'';
            $deposit=isset($_POST['deposit'])?$_POST['deposit']:'';
            $address=isset($_POST['address'])?$_POST['address']:'';
            $latitude=isset($_POST['latitude'])?$_POST['latitude']:'';
            $longitude=isset($_POST['longitude'])?$_POST['longitude']:'';
            $content=isset($_POST['content'])?$_POST['content']:'';
            $actimgurl=isset($_POST['actimgurl'])?$_POST['actimgurl']:'';
            $actinfo=Act::put_actinfo($title,$catid,$actdesc,$actimgurl,$masterid,$mastername,$masterphone,$actstarttime,$actendtime,$joinstarttime,$joinendtime,$userlimit,$deposit,time());
            $actid=Act::get_actid();
            $actid=$actid['actid'];
            $actlbs=Act::put_actlbs($actid,$address,$latitude,$longitude);
            $actcontent=Act::put_actcontent($actid,$content);
            if ($actinfo && $actlbs && $actcontent) {
              $total=0;
              Act::put_act_ranking($actid,1,$total);
              Act::put_act_ranking($actid,2,$total);
              Act::put_act_ranking($actid,3,$total);
              Act::put_act_ranking($actid,4,$total);
              echo json_encode(array('states'=>'1'));
               }else{
              echo json_encode(array('states'=>'0'));
               }
            
          }
        }
        //10013
        function get_actcat(){
          $cat=Act::get_actcat();
         echo json_encode($cat, JSON_UNESCAPED_UNICODE);
        }
         //10014
        function get_actlist($page,$id){
          $cat=Act::get_actlist($page,$id);
          echo json_encode($cat, JSON_UNESCAPED_UNICODE);
        }
         //10015
        function update_act($actid){
          $cat=Act::update_act($actid);
          echo json_encode($cat, JSON_UNESCAPED_UNICODE);
        }
         //10016
        function del_act($actid){
          $cat=Act::del_act($actid);
          if($cat){
            //放到回收站成功
            echo json_encode(array('states'=>'1'));
          }else{
            //放到回收站失败
            echo json_encode(array('states'=>'0'));
          }  
        }
        //10017
        function get_recycle()
        {
         $act=Act::get_recycle();
          echo json_encode($act, JSON_UNESCAPED_UNICODE);
        }
        function winmoney_payorder($price,$winmoney,$openid,$actid)
        {
              $user = Act::get_userinfo($openid);
              if($price < $user['winmoney'])
              {
                $winmoney = $user['winmoney'] - $price;
                Act::up_winmoney($openid,$winmoney);
                $winmoney = '-'.$price;
               App::banwinmoney('活动报名',$userid,$winmoney,$actid);                
                $actpay=Act::act_pay_user($actid,$openid);
                $act_payorder = 1;
                $time = time();     
                $user_info = get_user($openid);
          
             $bb=Act::put_pay_userdb($openid,$actid,$user_info['nickname'],$user_info['phone'],$act_payorder,$time);
             if($bb){
               $act=Act::get_actinfo_db($actid);
               $time=date('Y-m-d H:i',$act['actstarttime']);
               $ttime=date('Y-m-d H:i',time());
               $data='{
               "touser":"'.$openid.'",
               "template_id":"PBzuqbudfeTgga6KZsIoa3EJBEIZlg5rGTiflnelOI4",
               "url":"http://wechat.123win.com.cn/web/MyActivity.html",        
               "data":{
                       "first": {
                           "value":"活动报名成功,请尽快签到！",
                           "color":"#173177"
                       },
                       "keyword1":{
                           "value":"'.$act['title'].'",
                           "color":"#173177"
                       },
                       "keyword2": {
                           "value":"'.$time.'",
                           "color":"#173177"
                       },
                       "keyword3": {
                           "value":"'.$act['address'].'",
                           "color":"#173177"
                       },
                       "remark":{
                           "value":"活动联系人:'.$act['mastername'].'  电话:'.$act['masterphone'].'！",
                           "color":"#FF7F00"
                       }
               }
             }';
             apply_suc($data);
             $cc=array();
             $aa=yz_token();
             $cc['token']=$aa;
             $cc['data']=array('states'=>'2');
             echo json_encode($cc, JSON_UNESCAPED_UNICODE);
             //echo json_encode(array('states'=>'2'));
          }
        }
      }
        //10018
        function up_act($actid)
        {
          //openid是openid或者token
          $openid=isset($_GET['openid'])?$_GET['openid']:'';
          switch (strlen($openid)) {
            case '28':
              $user=Act::get_user($openid);
              $masterid=$user['id'];
              $mastername=$user['nickname'];
              $masterphone=$user['tel'];
              break;
            case '32':
              $user=Act::get_user_token($openid);
              $masterid=$user['id'];
              $mastername=$user['nickname'];
              $masterphone=$user['tel'];
              break;
          }
          if ($_POST) {
            $title=isset($_POST['title'])?$_POST['title']:'';
            $catid=isset($_POST['catid'])?$_POST['catid']:'';
            $actdesc=isset($_POST['actdesc'])?$_POST['actdesc']:'';
            $actstarttime=isset($_POST['actstarttime'])?$_POST['actstarttime']:'';
            $actendtime=isset($_POST['actendtime'])?$_POST['actendtime']:'';
            $joinstarttime=isset($_POST['joinstarttime'])?$_POST['joinstarttime']:'';
            $joinendtime=isset($_POST['joinendtime'])?$_POST['joinendtime']:'';
            $userlimit=isset($_POST['userlimit'])?$_POST['userlimit']:'';
            $deposit=isset($_POST['deposit'])?$_POST['deposit']:'';
            $address=isset($_POST['address'])?$_POST['address']:'';
            $latitude=isset($_POST['latitude'])?$_POST['latitude']:'';
            $longitude=isset($_POST['longitude'])?$_POST['longitude']:'';
            $content=isset($_POST['content'])?$_POST['content']:'';            
            $actimgurl=isset($_POST['actimgurl'])?$_POST['actimgurl']:'';
            $actinfo=Act::up_info($actid,$title,$catid,$actdesc,$actimgurl,$masterid,$mastername,$masterphone,$actstarttime,$actendtime,$joinstarttime,$joinendtime,$userlimit,$deposit,time()); 
            $actlbs=Act::up_lbs($actid,$address,$latitude,$longitude);
            $actcontent=Act::up_content($actid,$content);
            if ($actinfo && $actlbs && $actcontent) {
              echo json_encode(array('states'=>'1'));
               }else{
              echo json_encode(array('states'=>'0'));
               }
           }
        }
        //10019图片上传
        function put_file()
        {
          $uptypes=array(  
        'image/jpg',  
        'image/jpeg',  
        'image/png',  
        'image/pjpeg',  
        'image/gif',  
        'image/bmp',  
        'image/x-png'  
           );  
      
          $max_file_size=2000000;     //上传文件大小限制, 单位BYTE  
          $destination_folder="../upload/"; //上传文件路径  
          $watermark=0;      //是否附加水印(1为加水印,其他为不加水印);  
          $watertype=2;      //水印类型(1为文字,2为图片)  
          $waterposition=5;     //水印位置(1为左下角,2为右下角,3为左上角,4为右上角,5为居中);  
          $waterstring="学令赢";  //水印字符串  
          $waterimg="ssyy.jpg";    //水印图片  
          $imgpreview=0;      //是否生成预览图(1为生成,其他为不生成);  
          $imgpreviewsize=1/2;    //缩略图比例  
           if ($_SERVER['REQUEST_METHOD'] == 'POST')  
          {  
              if (@!is_uploaded_file($_FILES["imgfile"][tmp_name]))  
              //是否存在文件  
              {  
                   echo "图片不存在!";  
                   exit;  
              }  
            
              $file = $_FILES["imgfile"];  
              if($max_file_size < $file["size"])  
              //检查文件大小  
              {  
                  echo "文件太大!";  
                  exit;  
              }  
            
              if(!in_array($file["type"], $uptypes))  
              //检查文件类型  
              {  
                  echo "文件类型不符!".$file["type"];  
                  exit;  
              }  
            
              if(!file_exists($destination_folder))  
              {  
                  mkdir($destination_folder);  
              }  
            
              $filename=$file["tmp_name"];  
              $image_size = getimagesize($filename);  
              $pinfo=pathinfo($file["name"]);  
              $ftype=$pinfo['extension'];  
              $destination = $destination_folder.time().".".$ftype;  
              if (file_exists($destination) && $overwrite != true)  
              {  
                  echo "同名文件已经存在了";  
                  exit;  
              }  
            
              if(!move_uploaded_file ($filename, $destination))  
              {  
                  echo "移动文件出错";  
                  exit;  
              }  
            
              $pinfo=pathinfo($destination);  
              @$fname=$pinfo[basename];  
              /*echo " <font color=red>已经成功上传</font><br>文件名:  <font color=blue>".$destination_folder.$fname."</font><br>";  
              echo " 宽度:".$image_size[0];  
              echo " 长度:".$image_size[1];  
              echo "<br> 大小:".$file["size"]." bytes";*/
              // $cc=array();       
              //      $aa=yz_token();
              //      $cc['token']=$aa;
              //      $cc['data']=array('imgurl'=>"$destination_folder$fname");
              //      echo json_encode($cc, JSON_UNESCAPED_UNICODE);

                   // header("$destination_folder$fname"); 
                      
                  echo "https://wechat.123win.com.cn/upload/$fname";
                   // header("Location: $destination_folder$fname");
             // echo json_encode(array('imgurl'=>"$destination_folder$fname"),JSON_UNESCAPED_SLASHES);
            
              if($watermark==1)  
              {  
                  $iinfo=getimagesize($destination,$iinfo);  
                  $nimage=imagecreatetruecolor($image_size[0],$image_size[1]);  
                  $white=imagecolorallocate($nimage,255,255,255);  
                  $black=imagecolorallocate($nimage,0,0,0);  
                  $red=imagecolorallocate($nimage,255,0,0);  
                  imagefill($nimage,0,0,$white);  
                  switch ($iinfo[2])  
                  {  
                      case 1:  
                      $simage =imagecreatefromgif($destination);  
                      break;  
                      case 2:  
                      $simage =imagecreatefromjpeg($destination);  
                      break;  
                      case 3:  
                      $simage =imagecreatefrompng($destination);  
                      break;  
                      case 6:  
                      $simage =imagecreatefromwbmp($destination);  
                      break;  
                      default:  
                      die("不支持的文件类型");  
                      exit;  
                  }  
            
                  imagecopy($nimage,$simage,0,0,0,0,$image_size[0],$image_size[1]);  
                  imagefilledrectangle($nimage,1,$image_size[1]-15,80,$image_size[1],$white);  
            
                  switch($watertype)  
                  {  
                      case 1:   //加水印字符串  
                      imagestring($nimage,2,3,$image_size[1]-15,$waterstring,$black);  
                      break;  
                      case 2:   //加水印图片  
                      $simage1 =imagecreatefromgif("ssyy.jpg");  
                      imagecopy($nimage,$simage1,0,0,0,0,85,15);  
                      imagedestroy($simage1);  
                      break;  
                  }  
            
                  switch ($iinfo[2])  
                  {  
                      case 1:  
                      //imagegif($nimage, $destination);  
                      imagejpeg($nimage, $destination);  
                      break;  
                      case 2:  
                      imagejpeg($nimage, $destination);  
                      break;  
                      case 3:  
                      imagepng($nimage, $destination);  
                      break;  
                      case 6:  
                      imagewbmp($nimage, $destination);  
                      //imagejpeg($nimage, $destination);  
                      break;  
                  }  
            
                  //覆盖原上传文件  
                  imagedestroy($nimage);  
                  imagedestroy($simage);  
              }  
            
              if($imgpreview==1)  
              {  
              echo "<br>图片预览:<br>";  
              echo "<img src=\"".$destination."\" width=".($image_size[0]*$imgpreviewsize)." height=".($image_size[1]*$imgpreviewsize);  
              echo " alt=\"图片预览:\r文件名:".$destination."\r上传时间:\">";  
               
              }  
          }  
        }
      
     function gmt_iso8601($time) {
        $dtStr = date("c", $time);
        $mydatetime = new DateTime($dtStr);
        $expiration = $mydatetime->format(DateTime::ISO8601);
        $pos = strpos($expiration, '+');
        $expiration = substr($expiration, 0, $pos);
        return $expiration."Z";
    }  

      function upload_img($accessKeyId,$accessKeySecret,$endpoint,$bucket){   
              
        $id= $accessKeyId;
        $key= $accessKeySecret;;
        $host = 'https://123win.oss-cn-shenzhen.aliyuncs.com'; //bucket+endpoint
        $callbackUrl = "https://oss-demo.aliyuncs.com:23450";

        $callback_param = array('callbackUrl'=>$callbackUrl, 
                     'callbackBody'=>'filename=${object}&size=${size}&mimeType=${mimeType}&height=${imageInfo.height}&width=${imageInfo.width}', 
                     'callbackBodyType'=>"application/json");
        $callback_string = json_encode($callback_param);

        $base64_callback_body = base64_encode($callback_string);
        $now = time();
        $expire = 30; //设置该policy超时时间是10s. 即这个policy过了这个有效时间，将不能访问
        $end = $now + $expire;
        $expiration = gmt_iso8601($end);

        $dir = 'upload/';

        //最大文件大小.用户可以自己设置
        $condition = array(0=>'content-length-range', 1=>0, 2=>1048576000);
        $conditions[] = $condition; 

        //表示用户上传的数据,必须是以$dir开始, 不然上传会失败,这一步不是必须项,只是为了安全起见,防止用户通过policy上传到别人的目录
        $start = array(0=>'starts-with', 1=>'$key', 2=>$dir);
        $conditions[] = $start; 


        $arr = array('expiration'=>$expiration,'conditions'=>$conditions);
        //echo json_encode($arr);
        //return;
        $policy = json_encode($arr);
        $base64_policy = base64_encode($policy);
        $string_to_sign = $base64_policy;
        $signature = base64_encode(hash_hmac('sha1', $string_to_sign, $key, true));

        $response = array();
        $response['accessid'] = $id;
        $response['host'] = $host;
        $response['policy'] = $base64_policy;
        $response['signature'] = $signature;
        $response['expire'] = $end;
        $response['callback'] = $base64_callback_body;
        //这个参数是设置用户上传指定的前缀
        $response['dir'] = $dir.'${filename}';
        echo json_encode($response);
       }
      
      //获取文件类型后缀   
        function extend($file_name){   
            $extend = pathinfo($file_name);   
            $extend = strtolower($extend["extension"]);   
            return $extend;   
        }

        function putObject($ossClient, $bucket, $object, $content)  
        {  
            try{  
                $ossClient->putObject($bucket, $object, $content);  
            } catch(OssException $e) {  
                printf(__FUNCTION__ . ": FAILED\n");  
                printf($e->getMessage() . "\n");  
                return;  
            }  
           // print(__FUNCTION__ . ": OK" . "\n");  
        }  
      
      //上传图片保存在服务器
       function up_file($accessKeyId,$accessKeySecret,$endpoint,$bucket)
       {
          try{  
              $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);  
           }catch(OssException $e){  
               print $e->getMessage();  
          }     
          $path = "./upload/";   
          $extArr = array("jpg", "png", "gif","jpeg","pjpeg","bmp","x-png");   
          if(isset($_POST) and $_SERVER['REQUEST_METHOD'] == "POST"){   
              
              $name = $_FILES['imgfile']['name'];   
              $size = $_FILES['imgfile']['size'];   
            
              if(empty($name)){   
                  echo '请选择要上传的图片';   
                  exit;   
              }   
              $ext = extend($name);   
              if(!in_array($ext,$extArr)){   
                  echo '图片格式错误！';   
                  exit;   
              }   
              if($size>(100000*1024)){   
                  echo '图片大小不能超过100000KB';   
                  exit;   
              }

              $image_name = time().rand(100,999).".".$ext;   
                 
              $tmp = $_FILES['imgfile']['tmp_name'];   
              
           // echo $path.$image_name;exit;
            if(move_uploaded_file($tmp, $path.$image_name)){   
        
                  $object = "upload/".$image_name;  //上传文件路径名称，OSS路径  
                  $content = file_get_contents($path.$image_name); //本地文件路径  
                  putObject($ossClient,$bucket,$object,$content); //上传到OSS  
                  $img_url = 'http://123win.oss-cn-shenzhen.aliyuncs.com/'.$object;  
                  echo $img_url;
                  //unlink($path.$image_name);  
            
              }else{   
                  echo '上传出错了！';   
              }   
           }
       }
     //删除图片
        function delete_img($accessKeyId,$accessKeySecret,$endpoint,$bucket,$img_name)
        {    
            //替换路径
            $table_change = array('http://123win.oss-cn-shenzhen.aliyuncs.com/uploads'=>'uploads');
            $object = strtr($img_name,$table_change);
            try{
                $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint); 
                $ossClient->deleteObject($bucket, $object);
            } catch(OssException $e) {
                printf(__FUNCTION__ . ": FAILED\n");
                printf($e->getMessage() . "\n");
                return;
            }
            print(__FUNCTION__ . ": OK" . "\n");
        }
        
        //10020
        function put_user_mark($openid,$actid,$userid,$mark)
        {
         
          $act=Act::get_mark($actid);
          $to_userid = get_userid($userid); 
         //打分对象openid
          $to_openid = $userid;
          $uid = get_userid($openid);
          //用户不能给自己打分
          if($uid != $to_userid){                    
           if($act['adminsta'] == 7){
           $sin=Act::act_time_user($actid,$openid);
           if ($sin) {
              $num=Act::user_mark($actid,$openid);
              //最多可打分值
              $max=$act['grade'] - $num['num'];
              if ($max >= $mark) {
                //可以打分
                $bb=Act::put_user_mark($openid,$actid,$userid,$mark,time());
                $user=Act::get_user($openid);
                $time=date("m月d日 H:i",time());
                if ($bb) {
                   $total = $mark;
                   $to_userid = get_userid($to_openid);
                   User_info::up_uwinmoney($to_userid,$total*0.1);
                   $total = '+'.$total;
                   App::banwinmoney('个人打分',$to_userid,$total,$actid);
                   $result = Act::get_user($to_openid);
                   $surplus = $max - $mark; 
                   $data='{
                         "touser":"'.$openid.'",
                         "template_id":"rLuuCqF9RwYgzJ31nPFcjYbAHWoSgu2DaN1Edj3Wy4o",
                         "url":"",        
                         "data":{
                                 "first": {
                                     "value":"您有一条来自活动中心的消息!",
                                     "color":"#173177"
                                 },
                                 "keyword1":{
                                     "value":"【评分提示】",
                                     "color":"#173177"
                                 },
                                 "keyword2": {
                                     "value":"评分成功",
                                     "color":"#173177"
                                 },
                                 "keyword3": {
                                     "value":"'.$time.'",
                                     "color":"#173177"
                                 },
                                 "keyword4": {
                                     "value":"您已经成功为用户'.$result['nickname'].'打'.$mark.'分,剩余'.$surplus.'分，若不打分系统将收回！",
                                     "color":"#173177"
                                 },
                                 "remark":{
                                     "value":"谢谢您的合作！",
                                     "color":"#173177"
                                 }
                         }
                       }';
                       apply_suc($data);
                  //  $data1='{
                  //        "touser":"'.$to_openid.'",
                  //        "template_id":"I0jaI-Hx4PnEp4XOzJZksTHrhZBOF4IzLp6K0ylsr9U",
                  //        "url":"https://wechat.123win.com.cn/web/creditRecord.html?id='.$actid.'",        
                  //        "data":{
                  //                "first": {
                  //                    "value":"用户'.$user['nickname'].'给您打了'.$mark.'分，所获取的分数已存进学分账户，请查看",
                  //                    "color":"#173177"
                  //                },
                  //                "keyword1":{
                  //                    "value":"'.$time.'",
                  //                    "color":"#173177"
                  //                },
                  //                "keyword2": {
                  //                    "value":"'.$mark.'",
                  //                    "color":"#173177"
                  //                },
                  //                "keyword3": {
                  //                    "value":"个人打分",
                  //                    "color":"#173177"
                  //                },
                  //                "keyword4": {
                  //                    "value":"'.$result['winmoney'].'",
                  //                    "color":"#173177"
                  //                },
                  //                "remark":{
                  //                    "value":"谢谢您的合作！",
                  //                    "color":"#173177"
                  //                }
                  //        }
                  //      }';    
                  // apply_suc($data1);
                  $cc=array();       
                   $aa=yz_token();
                   $cc['token']=$aa;
                   $cc['data']=array('states'=>'1');
                   echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                  //echo json_encode(array('states'=>'1'));exit;
                }else{
                  $cc=array();       
                   $aa=yz_token();
                   $cc['token']=$aa;
                   $cc['data']=array('states'=>'0');
                   echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                  //echo json_encode(array('states'=>'0'));exit;
                }
              }else{
              //该分值大于用户所剩分值
              $cc=array();       
                   $aa=yz_token();
                   $cc['token']=$aa;
                   $cc['data']=array('states'=>'2');
                   echo json_encode($cc, JSON_UNESCAPED_UNICODE);
            // echo json_encode(array('states'=>'2'));exit;
              }
             }else{
              //没有该打分对象
              $cc=array();       
                   $aa=yz_token();
                   $cc['token']=$aa;
                   $cc['data']=array('states'=>'3');
                   echo json_encode($cc, JSON_UNESCAPED_UNICODE);
            // echo json_encode(array('states'=>'3'));exit;
             }
           }else{
            //该活动不在打分状态
            $cc=array();       
                   $aa=yz_token();
                   $cc['token']=$aa;
                   $cc['data']=array('states'=>'4');
                   echo json_encode($cc, JSON_UNESCAPED_UNICODE);
             //echo json_encode(array('states'=>'4'));exit;
           }
            
         }
         else
         {
             $cc=array();       
             $cc['data']=array('states'=>'5');
             echo json_encode($cc, JSON_UNESCAPED_UNICODE);
         }

      }
 
     //活动个人排名
        function get_user_ranking($actid)
        {
          
           $cc = Act::get_user_ranking($actid);
               foreach($cc as $k => $v)
               {    
                    $cc[$k]['jobname'] = Act::get_job($cc[$k]['userid'],$actid);                   
               }
            echo json_encode($cc,JSON_UNESCAPED_UNICODE);
        }
        function up_captain($actid,$userid,$tem)
        {
             $openid = get_openid($userid);
             $nickname = Act::get_qianuser($actid,$openid);
             $captain = Act::check_captain($actid,$tem,$openid);
             $trp=altroops($tem);
             if(!$captain)
            {
               $result = Act::up_captain($actid,$openid,$tem);
               if($result)
               {  
                  $cc=array();       
                  $cc['status']=array('states'=>'1');
                  $cc['data'] = $nickname;
                  echo json_encode($cc,JSON_UNESCAPED_UNICODE);
                   $time=date("m月d日 H:i",time());
                   $data='{
                         "touser":"'.$openid.'",
                         "template_id":"rLuuCqF9RwYgzJ31nPFcjYbAHWoSgu2DaN1Edj3Wy4o",
                         "url":"",        
                         "data":{
                                 "first": {
                                     "value":"您有一条来自活动中心的消息!",
                                     "color":"#173177"
                                 },
                                 "keyword1":{
                                     "value":"恭喜您成为'.$trp.'的队长",
                                     "color":"#173177"
                                 },
                                 "keyword2":{
                                     "value":"成为队长",
                                     "color":"#173177"
                                 },
                                 "keyword3": {
                                     "value":"'.$time.'",
                                     "color":"#173177"
                                 },
                                 "keyword4": {
                                     "value":"团队分10%为队长，50%平均，40%队长可奖励给优秀队员",
                                     "color":"#173177"
                                 },
                                 "remark":{
                                     "value":"感谢您的合作",
                                     "color":"#173177"
                                 }
                         }
                       }';
                  apply_suc($data);
               }else
               {  
                  //你不是这个队伍的
                  echo json_encode(array('status'=>'0'));
               }
           }
           else
          {   //已经成为队长
              echo json_encode(array('status'=>'2'));
          }

        }
        //10021
        function get_user($openid)
        {
          switch (strlen($openid)) {
            case '28':
              $user=Act::get_user($openid);
              break;
            case '32':
              $user=Act::get_user_token($openid);
              break;
          }
          //$user=Act::get_user($openid);
          $cc=array();       
                   $aa=yz_token($openid);
                   $cc['token']=$aa;
                   $cc['data']=$user;
                   echo json_encode($cc, JSON_UNESCAPED_UNICODE);
          //echo json_encode($user, JSON_UNESCAPED_UNICODE);
        }
        //添加收货地址
        function add_address($openid,$consignee,$province,$city,$district,$address,$zipcode,$tel,$default)
        {        
          $uid = User_info::getOpenid($openid);
          $uid = $uid['id'];
          $bb = Goods::add_address($uid,$consignee,$province,$city,$district,$address,$zipcode,$tel,$default);
          if($bb==1){
                echo json_encode(array('status'=>'1'));
              }else{
                echo json_encode(array('status'=>'0'));
              }         
        }
        //编辑收货地址
        function edit_address($address_id,$openid,$consignee,$province,$city,$district,$address,$zipcode,$tel,$default)
        {        
          $uid = User_info::getOpenid($openid);
          $uid = $uid['id'];
          $bb = Goods::edit_address($address_id,$uid,$consignee,$province,$city,$district,$address,$zipcode,$tel,$default);
          if($bb==1){
                echo json_encode(array('status'=>'1'));
              }else{
                echo json_encode(array('status'=>'0'));
              }         
        }
        //删除收货地址
        function del_address($id)
        {        

          $bb = Goods::del_address($id);
          if($bb==1){
                echo json_encode(array('status'=>'1'));
              }else{
                echo json_encode(array('status'=>'0'));
              }         
        }

        function up_status($id)
        {
            if($id)
            {
                echo json_encode(array('status'=>'1'));
            }else
            {
                echo json_encode(array('status'=>'0'));
            }

        }

        //获取推荐商品
         function get_recommend_goods()
        {

          $cc=Goods::get_regoods();
          echo json_encode($cc, JSON_UNESCAPED_UNICODE);
          
        }
        function get_winmoney_log($openid,$actid)
        {
          $user_id = get_userid($openid);
          $cc=APP::get_winmoney_log($user_id,$actid);
          echo json_encode($cc, JSON_UNESCAPED_UNICODE);
          
        }
        function cll_smit($id,$openid)
        {
          $uid = get_userid($openid);
          $cc=App::get_ccl_smit($id,$uid);
          if(!$cc)
          {   
              App::cll_smit($id,$uid);
              $userid =App:: get_cll_user($id);
              $total = App::get_log_winmoney('学客圈转发');
              $total = $total['winmoney'];
              User_info::up_uwinmoney($userid['uid'],$total);
              $total = '+'.$total;
              $actid ='';
              App::banwinmoney('学客圈转发',$userid['uid'],$total,$actid);
              echo json_encode(array('status'=>'1'));

          }
          else
          {
              echo json_encode(array('status'=>'0'));
          }         
        
        }

        function cll_get_usercll($openid)
        {
          $uid = get_userid($openid);
          $cc=APP::cll_get_usercll($uid);
          $count = count($cc);
          if($count)
          {
            $cc['0']['count'] = $count;  
          }
          
          echo json_encode($cc,JSON_UNESCAPED_UNICODE); 
        }

         //同一个操作24小时内不能重复获取学分
        function banwinmoney($log,$userid,$credit)
        {
           APP::banwinmoney($log,$userid,$credit);
        }
        //10022
        function get_qiandao($actid)
        {
          $user=Act::get_qiandao($actid);
          /*$data='time';
          $user=datime($data,$user,5);*/
          $cc=array();       
                   $aa=yz_token();
                   $cc['token']=$aa;
                   $cc['data']=$user;
                   echo json_encode($cc, JSON_UNESCAPED_UNICODE);
         // echo json_encode($user, JSON_UNESCAPED_UNICODE);
        }
        //10023
        function get_actsum($openid,$actid)
        {
          $user=Act::act_time_user($actid,$openid);
          $trop=$user['troops'];
          $sum=Act::get_actsum($actid,$trop);
          $cc=array();       
                   $aa=yz_token();
                   $cc['token']=$aa;
                   $cc['data']=$sum;
                   echo json_encode($cc, JSON_UNESCAPED_UNICODE);
          //echo json_encode($sum, JSON_UNESCAPED_UNICODE);
        }
        //10024
        function date_troops($openid,$useridd,$actid)
        {
          $sta=Act::get_adminsta($actid);
          //控制状态是否是活动准备中
          if ($sta['adminsta']==3) {
            $user=Act::act_time_user($actid,$openid);
            $trop=$user['troops'];
            $trp=altroops($trop);
         $trp=altroops($trop);
            $userid=get_openid($useridd);
            $username=Act::get_userinfo($userid);
            $username=$username['nickname'];
            $openname=Act::get_userinfo($openid);
            $openname=$openname['nickname'];
            $openidd=get_userid($openid);
            $time=date("m月d日 H:i",time());
              $data='{
                         "touser":"'.$userid.'",
                         "template_id":"rLuuCqF9RwYgzJ31nPFcjYbAHWoSgu2DaN1Edj3Wy4o",
                         "url":"http://wechat.123win.com.cn/web/ex_confirm.html?id='.$actid.'&userid='.$openid.'",        
                         "data":{
                                 "first": {
                                     "value":"'.$username.'您有一条来自活动中心的消息!来自'.$trp.'的'.$openname.'请求和您更换队伍！",
                                     "color":"#173177"
                                 },
                                 "keyword1":{
                                     "value":"【战队更换提示】",
                                     "color":"#173177"
                                 },
                                 "keyword2":{
                                     "value":"战队更换",
                                     "color":"#173177"
                                 },
                                 "keyword3":{
                                     "value":"'.$time.'",
                                     "color":"#173177"
                                 },
                                 "keyword4": {
                                     "value":"",
                                     "color":"#173177"
                                 },
                                 "remark":{
                                     "value":"如果同意,点击本条信息进入确认！若拒绝请忽视本条信息！",
                                     "color":"#173177"
                                 }
                         }
                       }';
                  apply_suc($data);
                  $cc=array();       
                   $aa=yz_token();
                   $cc['token']=$aa;
                   $cc['data']=array('states'=>'1');
                   echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                  //echo json_encode(array('states'=>'1'));
          }else{
            $cc=array();       
                   $aa=yz_token();
                   $cc['token']=$aa;
                   $cc['data']=array('states'=>'0');
                   echo json_encode($cc, JSON_UNESCAPED_UNICODE);
            //echo json_encode(array('states'=>'0'));exit;
          }
        }
        //10025
        function up_troops($openid,$userid,$actid,$states)
        {

            //$userid=get_openid($userid);
            $username=Act::get_userinfo($userid);
            $username=$username['nickname'];
            //$openid=get_openid($openid);
            $openname=Act::get_userinfo($openid);
            $openname=$openname['nickname'];
            //echo $openname;exit;
          switch ($states) {
            case '0':
            $time=date("m月d日 H:i",time());
                $data='{
                         "touser":"'.$userid.'",
                         "template_id":"rLuuCqF9RwYgzJ31nPFcjYbAHWoSgu2DaN1Edj3Wy4o",
                         "url":"",        
                         "data":{
                                 "first": {
                                     "value":"'.$username.'您有一条来自活动中心的消息!",
                                     "color":"#173177"
                                 },
                                 "keyword1":{
                                     "value":"【战队更换提示】",
                                     "color":"#173177"
                                 },
                                 "keyword2": {
                                     "value":"'.$username.'",
                                     "color":"#173177"
                                 },
                                 "keyword3": {
                                     "value":"'.$openname.'拒绝与您更换队伍！",
                                     "color":"#173177"
                                 },
                                 "keyword4": {
                                     "value":"'.$time.'",
                                     "color":"#173177"
                                 },
                                 "remark":{
                                     "value":"谢谢您的合作！",
                                     "color":"#173177"
                                 }
                         }
                       }';//echo $data;exit;
                  apply_suc($data);
                  $cc=array();       
                   $aa=yz_token();
                   $cc['token']=$aa;
                   $cc['data']=array('states'=>'1');
                   echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                  //echo json_encode(array('states'=>'1'));
                  exit;
              break;
            case '1':
            $sta=Act::get_adminsta($actid);
            if ($sta['adminsta']==3) {
            //$useridd=get_openid($userid);var_dump($userid);exit;
            $user=Act::act_time_user($actid,$userid);
            $usertrop=$user['troops'];
            $usertrpname=altroops($usertrop);
            $open=Act::act_time_user($actid,$openid);
            $opentrop=$open['troops'];
            $opentrpname=altroops($opentrop);
            $op=Act::up_troops($usertrop,$openid,$actid);
            $us=Act::up_troops($opentrop,$userid,$actid);
            if ($op) {
              $time=date("m月d日 H:i",time());
              $data='{
                         "touser":"'.$openid.'",
                         "template_id":"rLuuCqF9RwYgzJ31nPFcjYbAHWoSgu2DaN1Edj3Wy4o",
                         "url":"http://wechat.123win.com.cn/web/team.html?id='.$actid.'",        
                         "data":{
                                 "first": {
                                     "value":"'.$openname.'您有一条来自活动中心的消息!",
                                     "color":"#173177"
                                 },
                                 "keyword1":{
                                     "value":"【战队更换提示】",
                                     "color":"#173177"
                                 },
                                 "keyword2": {
                                     "value":"已成功更换到'.$usertrpname.'！",                         
                                     "color":"#173177"
                                 },
                                 "keyword3": {
                                     "value":"'.$time.'",
                                     "color":"#173177"
                                 },
                                 "keyword4": {
                                     "value":"更换队伍成功",
                                     "color":"#173177"
                                 },
                                 "remark":{
                                     "value":"谢谢您的合作！",
                                     "color":"#173177"
                                 }
                         }
                       }';
                  apply_suc($data);
             }
            if ($us) {
              $time=date("m月d日 H:i",time());
              $data='{
                         "touser":"'.$userid.'",
                         "template_id":"rLuuCqF9RwYgzJ31nPFcjYbAHWoSgu2DaN1Edj3Wy4o",
                         "url":"http://wechat.123win.com.cn/web/team.html?id='.$actid.'",        
                         "data":{
                                 "first": {
                                     "value":"'.$username.'您有一条来自活动中心的消息!",
                                     "color":"#173177"
                                 },
                                 "keyword1":{
                                     "value":"【战队更换提示】",
                                     "color":"#173177"
                                 },
                                 "keyword2": {
                                     "value":"已成功更换到'.$opentrpname.'！",
                                     "color":"#173177"
                                 },
                                 "keyword3": {
                                     "value":"'.$time.'", 
                                     "color":"#173177"
                                 },
                                 "keyword4": {
                                     "value":"更换队伍成功", 
                                     "color":"#173177"
                                 },
                                 "remark":{
                                     "value":"谢谢您的合作！",
                                     "color":"#173177"
                                 }
                         }
                       }';
                  apply_suc($data);
            }
            $cc=array();       
                   $aa=yz_token();
                   $cc['token']=$aa;
                   $cc['data']=array('states'=>'1');
                   echo json_encode($cc, JSON_UNESCAPED_UNICODE);
           //echo json_encode(array('states'=>'1'));
            exit;
                 }else{
                   $cc=array();       
                   $aa=yz_token();
                   $cc['token']=$aa;
                   $cc['data']=array('states'=>'0');
                   echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                  //echo json_encode(array('states'=>'0'));
                  exit;
            }
              break;
            }
        }
        //10026
        function get_adhome($ad_type)
        {
          $aa=yz_token();
          $ad=Act::get_adhome($ad_type);
          $cc=array();
          $cc['token']=$aa;
          $cc['data']=$ad;
          echo json_encode($cc,JSON_UNESCAPED_UNICODE);
        }
        function cll_get_rew($id)
        {
           $cc = App::cll_rew($id);
           echo json_encode($cc,JSON_UNESCAPED_UNICODE);
        }
         //10027
        function admin_act($actid,$openid)
        { 
          $masterid = get_userid($openid);
          $bb=Act::admin_act($actid,$masterid);
          
          if ($bb) {
            $act=Act::get_paytit($actid);
            //$title=$act['title'];
            $cc=array();       
                   $aa=yz_token();
                   $cc['token']=$aa;
                   $cc['data']=$act;
                   echo json_encode($cc, JSON_UNESCAPED_UNICODE);
            //echo json_encode($act, JSON_UNESCAPED_UNICODE);
          }else{
            $cc=array();       
                   $aa=yz_token();
                   $cc['token']=$aa;
                   $cc['data']=array('states'=>'0');
                   echo json_encode($cc, JSON_UNESCAPED_UNICODE);
            //echo json_encode(array('states'=>'0'));
          }
          
        }
         //actadmin
        function admin_act_sta($actid,$openid,$adminsta,$id,$grade)
        { 
          $uid = get_userid($openid);
          $bb=Act::admin_act($actid,$uid);
          $lbs=Act::get_paylbs($actid);
          $title=Act::get_paytit($actid);
          $time=date("m月d日 H:i",time());
          if ($bb) {
            switch ($adminsta) {
              case '1':
                  $sta=Act::up_adminsta($actid,$adminsta);
                  $start = microtime(true);  
                  $ch_list = array();  
                  $multi_ch = curl_multi_init();
                  $userid=Act::get_payuser($actid);            
                  $count = count($userid);               
                  for ($i = 0;$i < $count; ++$i) {  
                     $data='{
                         "touser":"'.$userid[$i]['userid'].'",
                         "template_id":"24Gb7LxhpYELNLBtdl_LFGqOLGUo0RpxqZzQEW6i--U",
                         "url":"https://wechat.123win.com.cn/web/sign.html?id='.$actid.'",        
                         "data":{
                                 "first": {
                                     "value":"【'.$title['title'].'】活动已经开始了！请点击签到。",
                                     "color":"#173177"
                                 },
                                 "keyword1":{
                                     "value":"'.$userid[$i]['nickname'].'",
                                     "color":"#173177"
                                 },
                                 "keyword2": {
                                     "value":"'.$lbs['address'].'",
                                     "color":"#173177"
                                 },
                                 "keyword3": {
                                     "value":"'.$time.'",
                                     "color":"#173177"
                                 },
                                 "remark":{
                                     "value":"谢谢您的合作！",
                                     "color":"#173177"
                                 }
                         }
                       }';
                $token=get_token();    
                $ch_list[$i] = curl_init("https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=$token");  
                curl_setopt($ch_list[$i],CURLOPT_POSTFIELDS,$data);  
                curl_multi_add_handle($multi_ch, $ch_list[$i]);  
                }                                      
                curl_mulit($start,$multi_ch,$ch_list);
                break;
                case '2':
                  $start = microtime(true);  
                  $ch_list = array();  
                  $multi_ch = curl_multi_init();
                  $userid=Act::get_payuserw($actid);
                  $count = count($userid);               
                  for ($i = 0;$i < $count; ++$i) {  
                    $time=date("m月d日 H:i",time());
                    $data='{
                           "touser":"'.$userid[$i]['userid'].'",
                         "template_id":"rLuuCqF9RwYgzJ31nPFcjYbAHWoSgu2DaN1Edj3Wy4o",
                         "url":"",        
                         "data":{
                                 "first": {
                                     "value":"【缺席提醒】：您参加的【'.$title['title'].'】活动签到阶段已经结束，您的本次缺席，系统已经记录！",
                                     "color":"#173177"
                                 },
                                 "keyword1":{
                                     "value":"签到失败",
                                     "color":"#173177"
                                 },
                                 "keyword2": {
                                     "value":"地点'.$lbs['address'].'",
                                     "color":"#173177"
                                 },
                                 "keyword3": {
                                     "value":"'.$time.'",
                                     "color":"#173177"
                                 },
                                 "remark":{
                                     "value":"请保持好良好活动参与率，多次缺席将冻结您的活动参与资格！",
                                     "color":"#173177"
                                 }
                         }
                       }';
                $token=get_token();    
                $ch_list[$i] = curl_init("https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=$token");  
                curl_setopt($ch_list[$i],CURLOPT_POSTFIELDS,$data);  
                curl_multi_add_handle($multi_ch, $ch_list[$i]);  
                }                                      
                curl_mulit($start,$multi_ch,$ch_list);
                $sta=Act::up_adminsta($actid,$adminsta);
                if ($sta !==false) {
                  echo json_encode(array('states'=>'1'));
                }
                break;
                case '3':
                  $start = microtime(true);  
                  $ch_list = array();  
                  $multi_ch = curl_multi_init();
                  $userid=Act::get_payusery($actid);
                  $count = count($userid);             
                  for ($i = 0;$i < $count; ++$i) {  
                  $time=date("m月d日 H:i",time());
                  $data='{
                         "touser":"'.$userid[$i]['userid'].'",
                         "template_id":"rLuuCqF9RwYgzJ31nPFcjYbAHWoSgu2DaN1Edj3Wy4o",
                         "url":"http://wechat.123win.com.cn/web/team.html?id='.$actid.'",        
                         "data":{
                                 "first": {
                                     "value":"您好，有一条来自【'.$title['title'].'】的提醒！",
                                     "color":"#173177"
                                 },
                                 "keyword1":{
                                     "value":"【允许更换队伍提醒】",
                                     "color":"#173177"
                                 },
                                 "keyword2": {
                                     "value":"",
                                     "color":"#173177"
                                 },
                                 "keyword3": {
                                     "value":"现在已经开放更换队伍权限，请找到愿与您交换队伍角色的小伙伴进行队伍交换！",
                                     "color":"#173177"
                                 },
                                 "keyword4": {
                                     "value":"'.$time.'",
                                     "color":"#173177"
                                 },
                                 "remark":{
                                     "value":"谢谢您的合作！",
                                     "color":"#173177"
                                 }
                         }
                       }';
                  $token=get_token();    
                  $ch_list[$i] = curl_init("https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=$token");  
                  curl_setopt($ch_list[$i],CURLOPT_POSTFIELDS,$data);  
                  curl_multi_add_handle($multi_ch, $ch_list[$i]);  
                }
                curl_mulit($start,$multi_ch,$ch_list);
                $sta=Act::up_adminsta($actid,$adminsta);
                if ($sta !==false){
                  echo json_encode(array('states'=>'1'));
                }
                break;
                case '4':
                //   $start = microtime(true);  
                //   $ch_list = array();  
                //   $multi_ch = curl_multi_init();
                //   $userid=Act::get_payusery($actid);
                //   $count = count($userid); 

                //   for ($i = 0;$i < $count; ++$i){  
                //     $time=date("m月d日 H:i",time());
                //     $data='{
                //          "touser":"'.$userid[$i]['userid'].'",
                //          "template_id":"rLuuCqF9RwYgzJ31nPFcjYbAHWoSgu2DaN1Edj3Wy4o",
                //          "url":"",        
                //          "data":{
                //                  "first": {
                //                      "value":"您好，有一条来自【'.$title['title'].'】的提醒！",
                //                      "color":"#173177"
                //                  },
                //                  "keyword1":{
                //                      "value":"【停止更换队伍提醒】",
                //                      "color":"#173177"
                //                  },
                //                  "keyword2": {
                //                      "value":"停止更换队伍提醒",
                //                      "color":"#173177"
                //                  },
                //                  "keyword3": {
                //                      "value":"'.$time.'",
                //                      "color":"#173177"
                //                  },
                //                  "keyword4": {
                //                      "value":"活动正式开始，现已暂停更换队伍角色。请与您当前的队伍成员积极参与活动。为团队争取更多学分哦！",
                //                      "color":"#173177"
                //                  },
                //                  "remark":{
                //                      "value":"谢谢您的合作！",
                //                      "color":"#173177"
                //                  }
                //          }
                //        }';
                //   $token=get_token();    
                //   $ch_list[$i] = curl_init("https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=$token");  
                //   curl_setopt($ch_list[$i],CURLOPT_POSTFIELDS,$data);  
                //   curl_multi_add_handle($multi_ch, $ch_list[$i]);  
                // }
                // curl_mulit($start,$multi_ch,$ch_list);
                $sta=Act::up_adminsta($actid,$adminsta);
                if ($sta !==false) {
                  echo json_encode(array('states'=>'1'));
                }
                break;
                case '5':
                //输出活动用户并按签到排序
                $user=Act::get_paysignuser($actid);
                echo json_encode($user, JSON_UNESCAPED_UNICODE);
                break;
                case '6':
                //
                $Ateamtime=Act::get_trosum($actid,1);
                $Ateamtime=$Ateamtime['sum'];
                $Bteamtime=Act::get_trosum($actid,2);
                $Bteamtime=$Bteamtime['sum'];
                $Cteamtime=Act::get_trosum($actid,3);
                $Cteamtime=$Cteamtime['sum'];
                $Dteamtime=Act::get_trosum($actid,4);
                $Dteamtime=$Dteamtime['sum'];
                $totaltime=$Ateamtime+$Bteamtime+$Cteamtime+$Dteamtime;
                
                $Ateam=1-($Ateamtime/$totaltime);
                $Ateam=round($Ateam,1);
                $Bteam=1-($Bteamtime/$totaltime);
                $Bteam=round($Bteam,1);
                $Cteam=1-($Cteamtime/$totaltime);
                $Cteam=round($Cteam,1);
                $Dteam=1-($Dteamtime/$totaltime);
                $Dteam=round($Dteam,1);
                $foot=Act::get_footscore($actid);
                $footscore=$foot['footscore'];
                $Ateamvalue=round($footscore*($Ateam/($Ateam+$Bteam+$Cteam+$Dteam)),0);
                $Bteamvalue=round($footscore*($Bteam/($Ateam+$Bteam+$Cteam+$Dteam)),0);
                $Cteamvalue=round($footscore*($Cteam/($Ateam+$Bteam+$Cteam+$Dteam)),0);
                $Dteamvalue=round($footscore*($Dteam/($Ateam+$Bteam+$Cteam+$Dteam)),0);
                if ($foot['footlock']==0) {
                $a=Act::put_ranking($Ateamvalue,$actid,1);
                $b=Act::put_ranking($Bteamvalue,$actid,2);
                $c=Act::put_ranking($Cteamvalue,$actid,3);
                $d=Act::put_ranking($Dteamvalue,$actid,4);
                $footlock=1;
                Act::up_actfootlock($actid,$footlock);
                if ($a && $b && $c && $d) {
                 echo json_encode(array('states'=>'1'));
                }
                }else{
                 echo json_encode(array('states'=>'0'));
                }
                break;
                case '7':
                  $setvalue=isset($_GET['setvalue'])?$_GET['setvalue']:'';
                  $adminsta=5;
                  $start = microtime(true);  
                  $ch_list = array();  
                  $multi_ch = curl_multi_init();
                  $userid=Act::check_act_capatin($actid);
                  $count = count($userid); 
                  for ($i = 0;$i < $count; ++$i){  
                    $time=date("m月d日 H:i",time());
                    $data='{
                           "touser":"'.$userid[$i]['userid'].'",
                         "template_id":"rLuuCqF9RwYgzJ31nPFcjYbAHWoSgu2DaN1Edj3Wy4o",
                         "url":"http://wechat.123win.com.cn/web/team.html?id='.$actid.'",        
                         "data":{
                                 "first": {
                                     "value":"您好，有一条来自【'.$title['title'].'】的提醒！",
                                     "color":"#173177"
                                 },
                                 "keyword1":{
                                     "value":"【队伍打分开始】",
                                     "color":"#173177"
                                 },
                                 "keyword2": {
                                     "value":"队伍打分",
                                     "color":"#173177"
                                 },
                                 "keyword3": {
                                     "value":"'.$time.'",
                                     "color":"#173177"
                                 },
                                 "keyword4": {
                                     "value":"队伍打分开始，请您为您喜欢的队伍打分！",
                                     "color":"#173177"
                                 },
                                 "remark":{
                                     "value":"谢谢您的合作！",
                                     "color":"#173177"
                                 }
                         }
                       }';
                  $token=get_token();    
                  $ch_list[$i] = curl_init("https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=$token");  
                  curl_setopt($ch_list[$i],CURLOPT_POSTFIELDS,$data);  
                  curl_multi_add_handle($multi_ch, $ch_list[$i]);  
                }
                  curl_mulit($start,$multi_ch,$ch_list);
                  $a=Act::up_adminsta($actid,$adminsta);
                  $b=Act::up_marknum($actid);
                  $c=Act::up_setvalue($actid,$setvalue);
                  if ($a && $b && $c) {
                   echo json_encode(array('states'=>'1'));
                  
                  }else{
                   echo json_encode(array('states'=>'0'));
                  }
                break;
                case '8':
                  $adminsta=6;
                  $a=Act::up_adminsta($actid,$adminsta);
                  $start = microtime(true);  
                  $ch_list = array();  
                  $multi_ch = curl_multi_init();
                  $userid=Act::get_payuser($actid);            
                  $count = count($userid);               
                  for ($i = 0;$i < $count; ++$i) { 
                     $str = get_job_winmoney($actid,$userid[$i]['userid']);
                     $data='{
                         "touser":"'.$userid[$i]['userid'].'",
                         "template_id":"rLuuCqF9RwYgzJ31nPFcjYbAHWoSgu2DaN1Edj3Wy4o",
                         "url":"http://wechat.123win.com.cn/web/Ranking.html?actid='.$actid.'",        
                         "data":{
                                 "first": {
                                     "value":"您好，有一条来自【'.$title['title'].'】的提醒！",
                                     "color":"#173177"
                                 },
                                 "keyword1":{
                                     "value":"【角色打分结束】",
                                     "color":"#173177"
                                 },
                                 "keyword2": {
                                     "value":"角色打分结束",
                                     "color":"#173177"
                                 },
                                 "keyword3": {
                                     "value":"角色打分结束，您总共获'.$str['count'].'学分，点击查看排行榜！",
                                     "color":"#173177"
                                 },
                                 "keyword4": {
                                     "value":"'.$time.'",
                                     "color":"#173177"
                                 },
                                 "remark":{
                                     "value":"谢谢您的合作！",
                                     "color":"#173177"
                                 }
                         }
                       }';
                $token=get_token();    
                $ch_list[$i] = curl_init("https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=$token");  
                curl_setopt($ch_list[$i],CURLOPT_POSTFIELDS,$data);  
                curl_multi_add_handle($multi_ch, $ch_list[$i]);  
                }                                      
                curl_mulit($start,$multi_ch,$ch_list);
                  if ($a) {
                   echo json_encode(array('states'=>'1'));
                  }else{
                   echo json_encode(array('states'=>'0'));
                  }
                break;
                case '13':
                  $adminsta=6;
                  $a=Act::up_adminsta($actid,$adminsta);
                  $start = microtime(true);  
                  $ch_list = array();  
                  $multi_ch = curl_multi_init();
                  $userid=Act::get_payuser($actid);            
                  $count = count($userid);               
                  for ($i = 0;$i < $count; ++$i) {  
                     $data='{
                         "touser":"'.$userid[$i]['userid'].'",
                         "template_id":"rLuuCqF9RwYgzJ31nPFcjYbAHWoSgu2DaN1Edj3Wy4o",
                         "url":"http://wechat.123win.com.cn/web/Ranking.html?actid='.$actid.'",        
                         "data":{
                                 "first": {
                                     "value":"您好，有一条来自【'.$title['title'].'】的提醒！",
                                     "color":"#173177"
                                 },
                                 "keyword1":{
                                     "value":"【团队打分结束】",
                                     "color":"#173177"
                                 },
                                 "keyword2": {
                                     "value":"团队打分结束",
                                     "color":"#173177"
                                 },
                                 "keyword3": {
                                     "value":"团队打分结束，点击查看排行榜！",
                                     "color":"#173177"
                                 },
                                 "keyword4": {
                                     "value":"'.$time.'",
                                     "color":"#173177"
                                 },
                                 "remark":{
                                     "value":"谢谢您的合作！",
                                     "color":"#173177"
                                 }
                         }
                       }';
                $token=get_token();    
                $ch_list[$i] = curl_init("https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=$token");  
                curl_setopt($ch_list[$i],CURLOPT_POSTFIELDS,$data);  
                curl_multi_add_handle($multi_ch, $ch_list[$i]);  
                }                                      
                curl_mulit($start,$multi_ch,$ch_list);
                  if ($a) {
                   echo json_encode(array('states'=>'1'));
                  }else{
                   echo json_encode(array('states'=>'0'));
                  }
                break;
                case '11':
                  $adminsta=7;
                  $start = microtime(true);  
                  $ch_list = array();  
                  $multi_ch = curl_multi_init();
                  $result = Act::get_electordetail($actid,$id);
                  $userid=Act::get_payusery($actid);
                  $count = count($userid); 
                  for ($i = 0;$i < $count; ++$i){
                    //$userid = get_userid($userid[$i]['userid']);
                    $arr = Act::get_successful_user($id,$actid,$userid[$i]['userid']);
                    if(!$arr)
                    {  
                    $time=date("m月d日 H:i",time());
                    $data='{
                           "touser":"'.$userid[$i]['userid'].'",
                         "template_id":"rLuuCqF9RwYgzJ31nPFcjYbAHWoSgu2DaN1Edj3Wy4o",
                         "url":"https://wechat.123win.com.cn/web/personal_rating.html?actid='.$actid.'&id='.$id.'",        
                        "data":{
                                 "first": {
                                     "value":"您好'.$userid[$i]['nickname'].'，有一条来自【'.$title['title'].'】的提醒！",
                                     "color":"#173177"
                                 },
                                 "keyword1":{
                                     "value":"【'.$result[0]['jobname'].'打分开始】",
                                     "color":"#173177"
                                 },
                                 "keyword2": {
                                     "value":"'.$result[0]['jobname'].'打分开始，请您为您喜欢的选手打分！",
                                     "color":"#173177"
                                 },
                                 "keyword3": {
                                     "value":"'.$time.'",
                                     "color":"#173177"
                                 },
                                 "keyword4": {
                                     "value":"",
                                     "color":"#173177"
                                 },
                                 "remark":{
                                     "value":"谢谢您的合作！",
                                     "color":"#173177"
                                 }
                         }
                       }';
                  $token=get_token();    
                  $ch_list[$i] = curl_init("https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=$token");  
                  curl_setopt($ch_list[$i],CURLOPT_POSTFIELDS,$data);  
                  curl_multi_add_handle($multi_ch, $ch_list[$i]);
                  }  
                }
                  curl_mulit($start,$multi_ch,$ch_list);
                  Act::up_grade_status($id);
                  Act::up_grade($grade,$actid);
                  $a=Act::up_adminsta($actid,$adminsta);
                  if ($a) {
                   echo json_encode(array('states'=>'1'));
                  }else{
                   echo json_encode(array('states'=>'0'));
                  }

                  break;
                case '9':
                  $footscore=isset($_GET['footscore'])?$_GET['footscore']:'';
                  $a=Act::up_footscore($actid,$footscore);
                  if ($a) {
                   echo json_encode(array('states'=>'1'));
                  }else{
                   echo json_encode(array('states'=>'0'));
                  }
                break;
                case '12':
                  $start = microtime(true);  
                  $ch_list = array();  
                  $multi_ch = curl_multi_init();
                  $userid=Act::check_act_capatin($actid);
                  $count = count($userid);
                  for ($i = 0;$i < $count; ++$i){
                    $time=date("m月d日 H:i",time());
                    $data='{
                           "touser":"'.$userid[$i]['userid'].'",
                         "template_id":"rLuuCqF9RwYgzJ31nPFcjYbAHWoSgu2DaN1Edj3Wy4o",
                         "url":"https://wechat.123win.com.cn/web/captain_mark.html?tem='.$userid[$i]['troops'].'&actid='.$actid.'",        
                         "data":{
                                 "first": {
                                     "value":"您好，您有一条来自【'.$title['title'].'】的提醒！",
                                     "color":"#173177"
                                 },
                                 "keyword1":{
                                     "value":"【打分开始】",
                                     "color":"#173177"
                                 },
                                 "keyword2": {
                                     "value":"队长打分开始，请您为您的队伍成员打分！",
                                     "color":"#173177"
                                 },
                                 "keyword3": {
                                     "value":"'.$time.'",
                                     "color":"#173177"
                                 },
                                 "keyword4": {
                                     "value":"",
                                     "color":"#173177"
                                 },
                                 "remark":{
                                     "value":"谢谢您的合作！",
                                     "color":"#173177"
                                 }
                         }
                       }';
                  $token=get_token();    
                  $ch_list[$i] = curl_init("https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=$token");  
                  curl_setopt($ch_list[$i],CURLOPT_POSTFIELDS,$data);  
                  curl_multi_add_handle($multi_ch, $ch_list[$i]);  
                }
                  Act::up_marknum($actid);
                  curl_mulit($start,$multi_ch,$ch_list);
                  break;
                case '10':
                  $tem1=Act::get_actsum($actid,1);
                  $tem2=Act::get_actsum($actid,2);
                  $tem3=Act::get_actsum($actid,3);
                  $tem4=Act::get_actsum($actid,4);
                  
                  $user1=Act::get_sumsign($actid,1);
                  $user2=Act::get_sumsign($actid,2);
                  $user3=Act::get_sumsign($actid,3);
                  $user4=Act::get_sumsign($actid,4);

                  $sum1=round($tem1['total']*0.8,0);
                  $sum2=round($tem2['total']*0.8,0);
                  $sum3=round($tem3['total']*0.8,0);
                  $sum4=round($tem4['total']*0.8,0);

                  @$mk1=$sum1/$user1['count'];
                  @$mk2=$sum2/$user2['count'];
                  @$mk3=$sum3/$user3['count'];
                  @$mk4=$sum4/$user4['count'];
                  
                  $user1=Act::get_sumuser($actid,1);
                  $user2=Act::get_sumuser($actid,2);
                  $user3=Act::get_sumuser($actid,3);
                  $user4=Act::get_sumuser($actid,4);
                  foreach ($user1 as $k => $v) {
                    $aa=User_info::up_winmoney($v['userid'],$mk1);
                  }
                  foreach ($user2 as $k => $v) {
                    $aa=User_info::up_winmoney($v['userid'],$mk2);
                  }
                  foreach ($user3 as $k => $v) {
                    $aa=User_info::up_winmoney($v['userid'],$mk3);
                  }
                  foreach ($user4 as $k => $v) {
                    $aa=User_info::up_winmoney($v['userid'],$mk4);
                  }
                  $adminsta=0;
                  $a=Act::up_adminsta($actid,$adminsta);
                  if ($a) {
                   echo json_encode(array('states'=>'1'));
                  }else{
                   echo json_encode(array('states'=>'0'));
                  }
                break; 
                case '20':
                   $result = get_ranking($actid);
                   echo json_encode($result);
                default:
                # code...
                break;
            }
           // echo json_encode(array('states'=>'1'));
          }else{
            echo json_encode(array('states'=>'0'));
          }
          
        }
        function veopenid($openid)
        {
         $token=get_token();    
         $url="https://api.weixin.qq.com/cgi-bin/user/info?access_token=$token&openid=$openid&lang=zh_CN";
         $act=curl_get($url);//echo $token.'<hr>';echo $act.'<hr>';
         //$act=get_url($url);
         $aa=json_decode($act);

         $bb=$aa->subscribe;
         if ($bb ==1) {
          echo json_encode(array('states'=>'1'));
         }else{
          echo json_encode(array('states'=>'0'));
         }

        }
        function put_foot($openid,$actid,$sta)
        {
          switch ($sta) {
            case '1':
              $user=User_info::getOpenid($openid);
              $userid=$user['id'];
              $pay=Act::get_payqd($actid,$openid);
              if ($pay) {
               $statime=Act::get_statime($actid,$userid);
               if (!$statime['statime']) {
                 $time=time();
                 $tro=$pay['troops'];
                 $foot=Act::put_foot($actid,$userid,$tro,$time);
                if ($foot) {
                  echo json_encode(array('states'=>'1'));
                }else{
                  echo json_encode(array('states'=>'3'));
                }
               }else{
                //用户已经参加该徒步活动
                echo json_encode(array('states'=>'2'));
               }
              }else{
                //用户未签到该活动
                echo json_encode(array('states'=>'0'));
              }
              break;
            case '2':
            $user=User_info::getOpenid($openid);
            $userid=$user['id'];
            $statime=Act::get_statime($actid,$userid);
              if ($statime['statime']) {
                if (!$statime['endtime']){
                  $time=time();
                  $result=$time-$statime['statime'];
                  $foot=Act::up_foot($actid,$userid,$result,$time);
                  if ($foot) {
                     $result=secsToStr($result);
                     echo json_encode(array('states'=>'5','result'=>"$result"),JSON_UNESCAPED_UNICODE);
                  }else{
                    echo json_encode(array('states'=>'6'));
                  }
                }else{
                   //用户已经结束该徒步活动
                echo json_encode(array('states'=>'4'));
                }
              }else{
                //用户没参加该徒步活动
                echo json_encode(array('states'=>'3'));
              }
              break;
              
          }
          
        }
        //10030
        function get_goodstype()
        {
          $type=Goods::get_goodstype();
          echo json_encode($type,JSON_UNESCAPED_UNICODE);
        }
        
        //获取地址
        function get_address($openid,$is_default)
        {
          $uid = User_info::getOpenid($openid);
          $uid = $uid['id'];
          $type=Goods::get_address($uid,$is_default);
           echo json_encode($type,JSON_UNESCAPED_UNICODE);   
        }
        //更新默认地址
        function update_address($address_id,$openid)
        { 
          $uid = User_info::getOpenid($openid);
          $uid = $uid['id'];
          $type=Goods::update_address($address_id,$uid);
          echo json_encode($type,JSON_UNESCAPED_UNICODE);    
        }
        //获取个人积分
        function get_winmoney($openid)
        {
          $uid = User_info::getOpenid($openid);
          $uid = $uid['id'];
          $type=Goods::get_winmoney($uid);
          echo json_encode($type,JSON_UNESCAPED_UNICODE);   
          
        }
        //生成唯一订单号
        function build_order_no()
        {
         $order_sn =  date('YmdHis') . str_pad(mt_rand(1, 999999), 2, '0', STR_PAD_LEFT);
         return $order_sn;
        }
       
       //插入订单
       function add_orders($openid,$order_status,$shipping_status,$pay_status,$consignee,$tel,$province,$city,$district,$address,$goods_amount,$add_time,$zipcode,$goods_id,$goods_name,$goods_number,$send_num,$is_real,$goods_price)
       {
          
              
              $uid = User_info::getOpenid($openid);
              $uid = $uid['id'];
              $order_sn = build_order_no();
              $bb=Goods::add_orders($order_sn,$uid,$order_status,$shipping_status,$pay_status,$consignee,$tel,$province,$city,$district,$address,$goods_amount,$add_time,$zipcode,$goods_id,$goods_name,$goods_number,$send_num,$is_real,$goods_price);
              if($bb==1){
                echo json_encode(array('status'=>'1'));
              }else{
                echo json_encode(array('status'=>'0'));
              }         

       }
        //获取用户订单
        function get_user_orders($openid)
        {
            $uid = get_userid($openid);
            $bb = Goods::get_orders($uid);
            echo json_encode($bb,JSON_UNESCAPED_UNICODE);
         }
        
        //更新订单状态
        function update_order($openid,$goods_name,$total,$winmoney,$pay_mode)
        {
          $uid = User_info::getOpenid($openid);
          $uid = $uid['id'];
          $arr = Goods::get_winmoney($uid); 
          $winmoney = $arr['winmoney'];
          $winmoney = $winmoney - $total; 
          $log = '购买'.$goods_name;
          $credit = '-'.$total;
          //学分充足的时候才可以进行下一步 
         
         
          if($arr['winmoney'] - $total > 0 )
          {
               $type=Goods::update_order($uid,$goods_name,$total,$winmoney,$pay_mode);
               User_info::up_winmoney($openid,$winmoney);
               $actid = '';
               App::banwinmoney($log,$uid,$credit,$actid);
               echo json_encode(array('status'=>'1'));
          }else
          {    
               //学分不足
               echo json_encode(array('status'=>'0'));
          } 
              
            
        }
        //活动结束时
        function act_over($actid)
        {
           $ch_list = array();  
           $multi_ch = curl_multi_init();
           $result = Act::get_payusery($actid);
           $start = microtime(true);
           $time=date("m月d日 H:i",time());; 
           foreach ($result as $k => $v) {
               $str = User_info::getOpenid($result[$k]['userid']);
               $uid = get_userid($result[$k]['userid']);
               $arr = Act::get_act_winmoney($actid,$uid);
               $data='{
                           "touser":"'.$result[$k]['userid'].'",
                           "template_id":"rLuuCqF9RwYgzJ31nPFcjYbAHWoSgu2DaN1Edj3Wy4o",
                           "url":"",        
                           "data":{
                                   "first": {
                                       "value":"您好，有一条来自活动中心的提醒！",
                                       "color":"#173177"
                                   },
                                   "keyword1":{
                                       "value":"本次活动已经结束",
                                       "color":"#173177"
                                   },
                                   "keyword2": {
                                       "value":"活动结束",
                                       "color":"#173177"
                                   },
                                   "keyword3": {
                                       "value":"'.$time.'",
                                       "color":"#173177"
                                   },
                                   "keyword4": {
                                       "value":"您在本次活动中获得:'.$arr['count'].'学分,当前学分:'.$str['winmoney'].',学分可用作在学分积分商城兑换精美物品",
                                       "color":"#173177"
                                   },
                                   "remark":{
                                       "value":"感谢您本次的参与！",
                                       "color":"#173177"
                                   }
                           }
                         }';
                  $token=get_token();    
                  $ch_list[$k] = curl_init("https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=$token");  
                  curl_setopt($ch_list[$k],CURLOPT_POSTFIELDS,$data);
                  curl_multi_add_handle($multi_ch, $ch_list[$k]); 
           }
            curl_mulit($start,$multi_ch,$ch_list);

        }
        //兑换完成时更新订单状态
        function exchange_update($order_sn)
        {
           $bb=Goods::exchange_update($order_sn);
           if($bb==1){
                echo json_encode(array('status'=>'1'));
              }else{
                
              }         
               
        }
        //获取未完成的订单
        function get_unfinished_orders($openid)
        {
           $uid = User_info::getOpenid($openid);
           $uid = $uid['id'];
           $type = Goods::get_unfinished_orders($uid);
           echo json_encode($type,JSON_UNESCAPED_UNICODE);  

        }
        function get_finished_orders($openid)
        {
           $uid = User_info::getOpenid($openid);
           $uid = $uid['id'];
           $type = Goods::get_finished_orders($uid);
           echo json_encode($type,JSON_UNESCAPED_UNICODE);  

        }
        function get_overdue_orders($openid)
        {
           $uid = User_info::getOpenid($openid);
           $uid = $uid['id'];
           $type = Goods::get_overdue_orders($uid);
           echo json_encode($type,JSON_UNESCAPED_UNICODE);  

        }
        function get_oneaddres($id)
        {
           $type=Goods::get_oneaddress($id);
           echo json_encode($type,JSON_UNESCAPED_UNICODE);   
   
        }
        
        //10031
        function get_goods($gtypeid,$gtype,$num)
        {
          // if (empty($gtypeid)) {
          //   $goods=Goods::get_goods();
          // }else{
          //   $goods=Goods::get_typegoods($gtypeid);
          // }
          switch ($gtype)
          {
          case 'new':
            $goods=Goods::get_typegoods_new($num);
            break;  
          case 'hot':
            $goods=Goods::get_typegoods_hot($num);
            break;
          default:
              if (empty($gtypeid)) {
                $goods=Goods::get_goods();
              }else{
                $goods=Goods::get_typegoods($gtypeid);
              }
          }


          echo json_encode($goods,JSON_UNESCAPED_UNICODE);
        }
        //10032
        function get_gcontent($goodsid)
        {
           $cc=array();       
           $aa=yz_token();
           $get_ginfo=Goods::get_ginfo($goodsid);
           $goods=Goods::get_gcontent($goodsid);
           $get_gimg=Goods::get_gimg($goodsid);
           //var_dump($get_gimg);exit;
           $cc['token']=$aa;
           // $cc['data']=array('info'=>$get_ginfo);
           // $cc['data']=array('content'=>$goods);
           if($get_gimg)
           {
            $get_gimg = explode(',',$get_gimg['0']['url']);
           }
           $cc['data']=array('img'=>$get_gimg,'info'=>$get_ginfo,'content'=>$goods);
           echo json_encode($cc, JSON_UNESCAPED_UNICODE);
        }
        //10033
        function get_qianuser($actid,$userid)
        {
          //$openid=get_userid($userid);
          $openid = $userid;
          $goods=Act::get_qianuser($actid,$openid);
          echo json_encode($goods,JSON_UNESCAPED_UNICODE);
        }
        //10034
        function get_actsta($actid,$openid)
        {

          $open=Act::get_actsta($actid);
          $marknum=$open['marknum'];
          $sum=Act::get_scosum($actid,$openid,$marknum);
          $open['num']=$open['setvalue']-$sum['sum'];
          $aa=$open;
          unset($aa['setvalue']);
          echo json_encode($aa,JSON_UNESCAPED_UNICODE);
        }

        //10035
        function zuce_user($phonekey,$phone)
        {
          $smscode=(string)rand(111111,999999);
          if(preg_match("/^1[34578]\d{9}$/",$phone))
          {
            $user=App::get_usertel($phone);
            if (!$user) {
            $time=App::get_code($phone);
            if (!$time) {
              //发送验证码
              $time=time();
              $aa=App::put_code($phonekey,$phone,$smscode,$time);
              $instance = new SmsDemo();
              $bb=$instance->sendSms($phone,$smscode);
             
              if($bb==1){
                echo json_encode(array('status'=>'1'));
              }else{
                echo json_encode(array('status'=>'0'));
              }         
              
              }else{
                if (time()-$time['time']>=60) {
                  $time=time();
                  $aa=App::up_codetime($phone,$smscode,$time);
                  $instance = new SmsDemo();
                  $bb=$instance->sendSms($phone,$smscode);                 
                  if ($bb==1) {
                    echo json_encode(array('status'=>'1'));
                  }else{
                    echo json_encode(array('status'=>'0'));
                  }
                  //发送验证码
                 }else{
                  //获取验证码必须间隔60秒
                  echo json_encode(array('status'=>'3'));
                 }
              }
            }else{
              //该手机已经注册
              echo json_encode(array('status'=>'4'));
            }
          }else{
            //手机号码格式不正确
            echo json_encode(array('status'=>'2'));
          }
          //echo $code;
        }
        //10036
        function put_userinfo($tel,$password,$smscode,$huahuamay)
        {
          if(preg_match("/^1[34578]\d{9}$/",$tel))
          {
            $code=App::get_code($tel);
            if ($smscode==$code['smscode']) {
              $user=App::get_usertel($tel);
              if (!$user) {
                $time=time();
                $password=md5($password);
                $aa=App::put_userinfo($tel,$password,$time,$huahuamay);
                if ($aa) {
                  //注册成功
                  echo json_encode(array('status'=>'1'));
                }else{
                  //注册失败
                  echo json_encode(array('status'=>'0'));
                }
              }else{
                //该手机已经注册
                echo json_encode(array('status'=>'4'));
              }
            
          }else{
            //验证码错误
            echo json_encode(array('status'=>'3'));
           }
          }else{
            //手机号码不正确
            echo json_encode(array('status'=>'2'));
          }

        }
        //10037
        function login_user($tel,$password)
        {
          
          if(preg_match("/^1[34578]\d{9}$/",$tel))
          { //手机登录
            $pwd=md5($pwd);
            $aa=App::login_user($tel,$pwd);
            if ($aa) {
              //登录成功
              $token='1';
              //$_SESSION['token']=$token
              $last_login_time = time();
              $last_login_ip = $_SERVER['REMOTE_ADDR'];
              User_info::add_token($token,$tel,$last_login_time,$last_login_ip);
              echo json_encode(array('status'=>'1'));
            }else{
              //登录失败
              echo json_encode(array('status'=>'0'));
            }

          }
        }
        //10038
        function login_wuser($openid,$udid)
        {
          
            $aa=App::login_wuser($openid);
            if ($aa) {
              //登录成功
              $token=md5($openid.$udid);
              //$_SESSION['token']=$token;
              $_SESSION['userid']=$aa['id'];
              User_info::add_token($token,$aa['id']);
              echo json_encode(array('status'=>'1'));
            }else{
              //登录失败
              echo json_encode(array('status'=>'0'));
            }
        }
        //10041
        function up_imgurl($token,$url)
        {
          $url=User_info::up_imgurl($token,$url);
          if ($url) {
                   $cc=array();       
                   $aa=yz_token();
                   $cc['token']=$aa;
                   $cc['data']=array('states'=>'1');
                   echo json_encode($cc, JSON_UNESCAPED_UNICODE);
          }else{
                    $cc=array();       
                   $aa=yz_token();
                   $cc['token']=$aa;
                   $cc['data']=array('states'=>'0');
                   echo json_encode($cc, JSON_UNESCAPED_UNICODE);
          }
        }
        //10042
        function login_out($token)
        {
          $user=User_info::login_out($token);
          if ($user) {
            $cc=array();       
                   $aa=yz_token();
                   $cc['token']=$aa;
                   $cc['data']=array('states'=>'1');
                   echo json_encode($cc, JSON_UNESCAPED_UNICODE);
          }else{
            $cc=array();       
                   $aa=yz_token();
                   $cc['token']=$aa;
                   $cc['data']=array('states'=>'0');
                   echo json_encode($cc, JSON_UNESCAPED_UNICODE);
          }
        }
        //10043
        function cll_list($openid)
        {
         $uid = get_userid($openid);
         $list=App::cll_list($uid);
         foreach ($list as $k => $v) {
           $count=App::cll_count($v['id']);
           $is_like = App::like_why($uid,$v['id']);
           $list[$k]['is_like'] = $is_like['status'];
           $list[$k]['like']=$count['wrzm'];
           $list[$k]['rew']=$count['mysh'];
           $list[$k]['smit']=$count['sgbh'];
           $url=explode(',',$v['thumb']);
           $img=array();
           foreach ($url as $kk => $vv) {
             $img[$kk]['url']=$vv;
           }
           $list[$k]['thumb']=$img;
           $con=count($url);
           if ($v['thumb']=='') {
             $list[$k]['type']=1;
           }else if($con==1){
            $list[$k]['type']=2;
          }else if($con>1){
            $list[$k]['type']=3;
          }
         }
         $get=array();
         $get['data']=$list;
         echo json_encode($get, JSON_UNESCAPED_UNICODE);
         //var_dump($list);//var_dump($count);
        }
        function get_captain($actid,$userid,$troops)
        {   
            $userid = get_openid($userid);
            $result = User_info::get_captain($actid,$userid,$troops);
            if($result)
            {
              echo json_encode(array('state'=>'1'));
            }
            else
            {
              echo json_encode(array('state'=>'0')); 
            }
        }
        //10078
        function check_like($id)
        {
            $result = Act::check_like($id);
            if($result)
            {
               $cc['data']=$result;
               $cc['status']=array('states'=>'0');
               echo json_encode($result, JSON_UNESCAPED_UNICODE);     
            }
            else
            {
               $cc['status']=array('states'=>'1');
               echo json_encode($cc, JSON_UNESCAPED_UNICODE);
 
            }
        }
        //10044
        function cll_content($id,$openid)
        {
         
         $uid = get_userid($openid); 
         $list=App::cll_content($id,$uid);
         $rew=App::cll_rew($id);
           $count=App::cll_count($list['id']);
           $list['like']=$count['wrzm'];
           $list['rew']=$count['mysh'];
           $list['smit']=$count['sgbh'];
           if($list['thumb'])
           {
            $url=explode(',',$list['thumb']);
           }
           $img=array();
           foreach ($url as $kk => $vv) {
             if($vv)
             {
              $img[$kk]['url']=$vv;
             }
           }
           $list['thumb']=$img;
           $con=count($url);
           if ($list['thumb']=='') {
             $list['type']=1;
           }else if($con==1){
            $list['type']=2;
          }else if($con>1){
            $list['type']=3;
          }
          $list['rewlist']=$rew;
         $get=array();
         $get['data']=$list;
         echo json_encode($get, JSON_UNESCAPED_UNICODE);
         //var_dump($list);//var_dump($count);
        }
        //10045
        function cll_put_list($openid,$content,$thumb)
        {
          $time=time();
          $userid=get_userid($openid);
          if ($userid) {
            $cll=App::cll_put_list($userid,$content,$thumb,$time);
          if ($cll) {
             $aa=yz_token();
                   $cc['token']=$aa;
                   $cc['data']=array('states'=>'1');
                   echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                   $total = App::get_log_winmoney('发表学客圈');
                   $total = $total['winmoney'];
                   User_info::up_uwinmoney($userid,$total);
                   $total = '+'.$total;
                   $actid ='';
                   App::banwinmoney('发表学客圈',$userid,$total,$actid);
          }else{
            $aa=yz_token();
                   $cc['token']=$aa;
                   $cc['data']=array('states'=>'0');
                   echo json_encode($cc, JSON_UNESCAPED_UNICODE);
          }
          }else{
            //无效openid
            $aa=yz_token();
                   $cc['token']=$aa;
                   $cc['data']=array('states'=>'2');
                   echo json_encode($cc, JSON_UNESCAPED_UNICODE);
          }
          
        }
        //10046
        function cll_put_rew($openid,$content,$id,$replier)
        {
          $time=time();
          $userid=get_userid($openid);
          if ($userid) {
            $cll=App::cll_put_rew($userid,$content,$id,$time);
            if ($cll) {
             $aa=yz_token();
                   $cc['token']=$aa;
                   $cc['data']=array('states'=>'1');
                   echo json_encode($cc, JSON_UNESCAPED_UNICODE);
          }else{
            $aa=yz_token();
                   $cc['token']=$aa;
                   $cc['data']=array('states'=>'0');
                   echo json_encode($cc, JSON_UNESCAPED_UNICODE);
          }
          }else{
            //无效openid
            $aa=yz_token();
                   $cc['token']=$aa;
                   $cc['data']=array('states'=>'2');
                   echo json_encode($cc, JSON_UNESCAPED_UNICODE);
          }
        }
        //获取各操作的学分
        function get_log_winmoney($log_name)
        {
           $cc = App::get_log_winmoney();
        }
        //10047
        function cll_put_like($openid,$id)
        {
          $time=time();
          $userid=get_userid($openid);
          if ($userid) {
            $like=App::like_why($userid,$id);
          if ($like) {
            switch ($like['status']) {
              case '0':
                $status=1;
                $cll=App::cll_up_like($userid,$id,$status);
                if ($cll) {
                 $aa=yz_token();
                       $cc['token']=$aa;
                       $cc['data']=array('status'=>"$status");
                       echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                }else{
                  /*$aa=yz_token();
                         $cc['token']=$aa;
                         $cc['data']=array('states'=>'0');
                         echo json_encode($cc, JSON_UNESCAPED_UNICODE);*/
                }
                break;
              case '1':
                $status=0;
                $cll=App::cll_up_like($userid,$id,$status);
                if ($cll) {
                 $aa=yz_token();
                       $cc['token']=$aa;
                       $cc['data']=array('status'=>"$status");
                       echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                }else{
                  /*$aa=yz_token();
                         $cc['token']=$aa;
                         $cc['data']=array('states'=>'0');
                         echo json_encode($cc, JSON_UNESCAPED_UNICODE);*/
                }
                break;
            }
            
          }else{
            $cll=App::cll_put_like($userid,$id,$time);
            if ($cll) {
              $sum=App::cll_get_like($id);
                  if ($sum['sum']<10) {
                    $user=App::get_cll_user($id);
                    $total = App::get_log_winmoney('学客圈点赞');
                    $total = $total['winmoney'];
                    User_info::up_uwinmoney($user['uid'],$total);
                    $total = '+'.$total;
                    $actid = '';
                    App::banwinmoney('学客圈点赞',$userid,$total,$actid);
                  }
             $aa=yz_token();
                   $cc['token']=$aa;
                   $cc['data']=array('status'=>'0');
                   echo json_encode($cc, JSON_UNESCAPED_UNICODE);
          }else{
            /*$aa=yz_token();
                   $cc['token']=$aa;
                   $cc['data']=array('states'=>'0');
                   echo json_encode($cc, JSON_UNESCAPED_UNICODE);*/
          }
          }
            
          }else{
            //无效openid
            $aa=yz_token();
                   $cc['token']=$aa;
                   $cc['data']=array('states'=>'2');
                   echo json_encode($cc, JSON_UNESCAPED_UNICODE);
          }
        }
        //秒数转化成天。时。分。秒
        function secsToStr($secs) 
        {
                    if($secs>=86400){$days=floor($secs/86400);
                    $secs=$secs%86400;
                    @$r=$days.' 天';
                    if($days<>1){$r;}
                    if($secs>0){$r.=', ';}}
                    if($secs>=3600){$hours=floor($secs/3600);
                    $secs=$secs%3600;
                    @$r.=$hours.' 小时';
                    if($hours<>1){$r;}
                    if($secs>0){$r.=', ';}}
                    if($secs>=60){$minutes=floor($secs/60);
                    $secs=$secs%60;
                    @$r.=$minutes.' 分钟';
                    if($minutes<>1){$r;}
                    if($secs>0){$r.=', ';}}
                    @$r.=$secs.' 秒';
                    if($secs<>1){$r;
                    }
                    return $r;
                   }
        /*格式化时间戳$data='字段 字段'需要格式化的字段用空格分开
         $type为类型，1为精确到秒 2为精确到天
         *arr为数组
         */
       function datime($data,$arr,$type=1)
       {
        $aa=explode(" ", $data);
         if (count($arr) == count($arr, 1)) {
         foreach ($aa as $key => $value) {
              switch ($type) {
                  case '1':
                  $arr[$aa[$key]]=date('Y-m-d H:i:s',$arr[$aa[$key]]);
                  break;
                  case '2':
                  $arr[$aa[$key]]=date('Y-m-d',$arr[$aa[$key]]);
                  break;
                  case '3':
                  $arr[$aa[$key]]=date('m月d日 H:i',$arr[$aa[$key]]);
                  break;
                  case '4':
                  $arr[$aa[$key]]=date('m月d日',$arr[$aa[$key]]);
                  break;
                  case '5':
                  $arr[$aa[$key]]=date('H:i',$arr[$aa[$key]]);
                  break;
                }
          
            }
       } else {
        foreach ($arr as $k => $v) {
            foreach ($aa as $key => $value) {
              switch ($type) {
                  case '1':
                  $arr[$k][$aa[$key]]=date('Y-m-d H:i:s',$arr[$k][$aa[$key]]);
                  break;
                  case '2':
                  $arr[$k][$aa[$key]]=date('Y-m-d',$arr[$k][$aa[$key]]);
                  break;
                  case '3':
                  $arr[$k][$aa[$key]]=date('m月d日 H:i',$arr[$k][$aa[$key]]);
                  break;
                  case '4':
                  $arr[$k][$aa[$key]]=date('m月d日',$arr[$k][$aa[$key]]);
                  break;
                  case '5':
                  $arr[$k][$aa[$key]]=date('H:i',$arr[$k][$aa[$key]]);
                  break;
                }
          
            }
            
          }
      }
        
        return $arr;
      }
      /**
     * @param $lat1 经度1
     * @param $lng1 纬度1
     * @param $lat2 经度2
     * @param $lng2 纬度2
     * @return int  返回值米
     */
       function distance($lat1, $lon1, $lat2,$lon2)
      {
          $radius = 6378.137;
          $rad = floatval(M_PI / 180.0);
          $lat1 = floatval($lat1) * $rad;
          $lon1 = floatval($lon1) * $rad;
          $lat2 = floatval($lat2) * $rad;
          $lon2 = floatval($lon2) * $rad;

          $theta = $lon2 - $lon1;

          $dist = acos(sin($lat1) * sin($lat2) +
                      cos($lat1) * cos($lat2) * cos($theta)
                  );

          if ($dist < 0 ) {
              $dist += M_PI;
          }

           $dist = $dist * $radius;
           return $dist*1000;
      }
      //返回活动最少人的队伍名称
      function mintroop($actid)
      {
          $arr=array(
            'tem1'=>'where actid='.$actid.' && troops=1',
            'tem2'=>'where actid='.$actid.' && troops=2',
            'tem3'=>'where actid='.$actid.' && troops=3',
            'tem4'=>'where actid='.$actid.' && troops=4'
            );
          foreach ($arr as  $k=>$v) {
            
            $sql = "select count(id)as min from act_signtime {$v}";
                $conn = DbConn::getInstance();
                $result= $conn->queryOne($sql);
                $min[]=$result['min'];
          }
          natsort($min);
          foreach ($min as $k => $v) {
            $tem[]=$k;
          }
          switch ($tem[0]) {
            case '0':
              $tem=1;
              break;
              case '1':
              $tem=2;
              break;
              case '2':
              $tem=3;
              break;
              case '3':
              $tem=4;
              break;
          }
          return $tem;
      }
      //战队名称转化
      function altroops($trpid='')
      {
        switch ($trpid) {
          case '1':
            return '陆战队';
            break;
          case '2':
            return '海战队';
            break;
          case '3':
            return '太空队';
            break;
          case '4':
            return '风魔队';
            break;  
        }
      }
    

      //10012
      function get_jssdk(){
        $jssdk = new JSSDK("wxc144a54f3f0f0eb4", "9cc5767a27d3930d4a587d99ba550a8c");
        $signPackage = $jssdk->GetSignPackage();
        echo $aa=json_encode($signPackage, JSON_UNESCAPED_UNICODE);
      }
      //openid转userid
      function get_openid($userid)
      {
        $openid=Act::get_openid($userid);
        return $openid['openid'];
      }
      //openid转userid
      function get_userid($openid)
      {
        $openid=Act::get_userid($openid);
        return $openid['id'];
      }
      //验证token
      function yz_token($token='')
      {
        if (empty($token)) {
          $token=isset($_GET['token'])?$_GET['token']:'';
        }
        if (!empty($token)) {
          $to=User_info::get_ltoken($token);
        if($to){
          if ($token===$to['token']) {
            $bb=array();
            $bb['user_id']=$to['id'];
            $bb['token_status']=1;           
               return $bb;
          }else{          
              return array('token_status'=>'0');
          }
          }else{
              return array('token_status'=>'0'); 
          }
        }else{
              return array('token_status'=>'0');
          }
      }

        //短信服务
  class SmsDemo
  {

      public function sendSms($phone,$smscode) {

        $accessKeyId = 'LTAIsGh8ozwyuFIB';
            $accessKeySecret = 'lvJpPhevbDnM380ft8wHZ4TL00Oaca';  
          // 短信API产品名
          $product = "Dysmsapi";

          // 短信API产品域名
          $domain = "dysmsapi.aliyuncs.com";

          // 暂时不支持多Region
          $region = "cn-hangzhou";

          // 服务结点
          $endPointName = "cn-hangzhou";

          // 初始化用户Profile实例
          $profile = DefaultProfile::getProfile($region, $accessKeyId, $accessKeySecret);

          // 增加服务结点
          DefaultProfile::addEndpoint($endPointName, $region, $product, $domain);

          // 初始化AcsClient用于发起请求
          $this->acsClient = new DefaultAcsClient($profile);

          // 初始化SendSmsRequest实例用于设置发送短信的参数
          $request = new SendSmsRequest();

          // 必填，设置雉短信接收号码
          $request->setPhoneNumbers("$phone");

          // 必填，设置签名名称
          $request->setSignName("学令赢");

          // 必填，设置模板CODE
          $request->setTemplateCode("SMS_91785037");

          // 可选，设置模板参数
          
          $request->setTemplateParam("{\"name\":\"$smscode\"}");

          // 发起访问请求
          $acsResponse = $this->acsClient->getAcsResponse($request);

          // 打印请求结果
          // var_dump($acsResponse);

          return $acsResponse;

      }
  }


        //查询活动的角色列表
         function get_act_job($actid,$openid){
           $bb=Act::act_check_pay($actid,$openid);
           $job=Act::act_get_job($actid);
           $title=Act::get_actinfo_db($actid);
           if ($bb['paystate']==1) {//如果改活动用户已经支付
             # code...
            $cc=array();
            $aa=yz_token();
            $cc['token']=$aa;
            $cc['title']=$title['title'];
            $cc['data']=$job;
            echo json_encode($cc, JSON_UNESCAPED_UNICODE);
           }else{
             $cc=array();
             $aa=yz_token();
             $cc['token']=$aa;
             $cc['data']=array('states'=>'0');
             echo json_encode($cc, JSON_UNESCAPED_UNICODE);            
           }
        }


        //用户申请角色
         function up_act_job($actid,$openid,$jobid){
           $bb=Act::act_check_pay($actid,$openid);
           $ee=Act::job_check_user($actid,$openid);
           $time=time();
           if ($bb['paystate']==1) {//如果用户已经支付活动费用
                if (!$ee) {//如果不存在竞选角色
                  Act::del_user_job($actid,$openid);                   
                  $ff= Act::job_up_user($actid,$openid,$jobid,$time);
                  
                    if ($ff) {
                       $cc=array();
                       $aa=yz_token();
                       $cc['token']=$aa;
                       $cc['states']=3;
                       $cc['id'] = $ff;
                      //参加竞选成功
                       echo json_encode($cc, JSON_UNESCAPED_UNICODE);  
                    }else{
                       $cc=array();
                       $aa=yz_token();
                       $cc['token']=$aa;
                       $cc['states']=2;//参加竞选失败
                       echo json_encode($cc, JSON_UNESCAPED_UNICODE);  
                    }
                }else{
                   $ff=Act::job_select_user($openid,$actid);                       
                   $cc=array();
                   $aa=yz_token();
                   $cc['token']=$aa;
                   $cc['states']=1;//用户已经成功竞选过角色
                   $cc['id'] = $ff['id'];
                   echo json_encode($cc, JSON_UNESCAPED_UNICODE);  
                }
           }else{//用户未支付费用
             $cc=array();
             $aa=yz_token();
             $cc['token']=$aa;
             $cc['states']=0;//用户还没支付费用
             echo json_encode($cc, JSON_UNESCAPED_UNICODE);  
           }

        }

        //查询用户是否参与了角色
         function check_act_job($actid,$openid){
           $bb=Act::job_check_user($actid,$openid);
           if ($bb) {
             $cc=array();
             $aa=yz_token();
             $cc['token']=$aa;
             $cc['data']=$bb;
             echo json_encode($cc, JSON_UNESCAPED_UNICODE);  
           }else{
             $cc=array();
             $aa=yz_token();
             $cc['token']=$aa;
             $cc['data']=0;
             echo json_encode($cc, JSON_UNESCAPED_UNICODE);  
           }

        }
        //查询用户是否参与了角色
         function up_job_add($openid,$jobid){
           $time=time();
           $bb=Act::up_job_check($openid,$jobid);//查询用户投票的对象是否自己
           $ee=Act::up_job_add_check($openid,$jobid);
           if (!$bb) {//查询用户投票的对象是否自己
             if (!$ee) {//如果用户没给该用户投票过
               # code...
                $ff=Act::add_user_job($openid,$jobid,$time);
                $gg=Act::add_user_job_one($jobid);//添加票数
                if ($ff && $gg) {
                 $cc=array();
                 $aa=yz_token();
                 $cc['token']=$aa;
                 $cc['states']=3;//如果用户已经给竞选者投票过
                 echo json_encode($cc, JSON_UNESCAPED_UNICODE);  
                }else{
                 $cc=array();
                 $aa=yz_token();
                 $cc['token']=$aa;
                 $cc['states']=2;//如果用户已经给竞选者投票过
                 echo json_encode($cc, JSON_UNESCAPED_UNICODE);  
                }

             }else{//如果用户已经给竞选者投票过
               $cc=array();
               $aa=yz_token();
               $cc['token']=$aa;
               $cc['states']=1;//如果用户已经给竞选者投票过
               echo json_encode($cc, JSON_UNESCAPED_UNICODE);  
             }

           }else{//如果用户投票的是给自己
             $cc=array();
             $aa=yz_token();
             $cc['token']=$aa;
             $cc['states']=0;//如果用户投票的是给自己
             echo json_encode($cc, JSON_UNESCAPED_UNICODE);  
           }
        }
           function get_electordetail($actid,$id)
          {
               $cc = Act::get_electordetail($actid,$id);
               $dd = Act::get_mark($actid);
               $get = $cc[0]['joblimit'];
            
               foreach($cc as $k => $v)
               {    
                         
                    $bb[$k]['data'] = Act::get_job_user($cc[$k]['id'],$get);
                    $bb[$k]['jobname'] = $cc[$k]['jobname'];
                    $bb['grade'] = $dd['grade'];

               }  
              echo json_encode($bb, JSON_UNESCAPED_UNICODE);   
          }
        
         //预分配队伍
        function ready_allocation($actid)
        {
              
            $result = Act::get_jobname($actid);
            foreach($result as $k => $v)
            {    
                $get = $result[$k]['joblimit'];     
                $bb[$k] = Act::get_job_user($result[$k]['id'],$get);
                array_push($bb[$k],$result[$k]['bonus']);               
            }
                  
            $count = count($bb);
      
            for($i = 0;$i<$count;$i++)
            {
                $str = count($bb[$i]);
                $bonus = $bb[$i][$str-1];
                $n = 1;
                array_pop($bb[$i]);
                $tem = array('0' =>1,'1' => 2,'2'=>3,'3'=>4);
                shuffle($bb[$i]);
                shuffle($tem);
               for($j = 0;$j<$str;$j++)
               { 
                     $userid = get_userid($bb[$i][$j]['openid']);
                     
                     User_info::up_uwinmoney($userid,$bonus);
                     //保证随机性                           
                     if($str < 5)
                     {                         
                       Act::up_ready_tream($tem[$j],$bb[$i][$j]['job_user_id']);
                     }
                     else
                     {
                       $n>4?$n=1:$n=$n;
                       Act::up_ready_tream($n,$bb[$i][$j]['job_user_id']);
                     }
                     $n += 1;
                                
               } 
                
            }
             
          if($result)
          {
            echo json_encode(array('status'=>'0'));
          }
          else
          {
             echo json_encode(array('status'=>'1'));
          }
       
       }      
  

        //列出该角色竞选者
         function get_job_user($openid,$jobid,$actid){
           $time=time();
           $get = 0;
           $bb=Act::get_job_user_fid($jobid);
           $ff=Act::get_job_user($bb['f_id'],$get,$actid);
             $cc=array();
             $aa=yz_token();
             $cc['token']=$aa;
             $cc['data']=$ff;
             echo json_encode($cc, JSON_UNESCAPED_UNICODE);  
        }
        //查看落选者
        function check_unsuccessful($actid)
        {
    
              $result = Act::get_jobname($actid);
              foreach($result as $k => $v)
              {
                   $data = Act::get_electordetail($actid,$result[$k]['id']);
                   $get = $data[0]['joblimit'];
                   $cc = Act::act_get_job($actid);
                   $bb[$k] = Act::get_job_user($result[$k]['id'],$get);
                   $count = count($bb[$k]); 
                   $str = $get - $count;      
                   Act::up_jobsurplus($str,$result[$k]['id']);
              }
              job_success($bb,$cc,$actid);
              get_unsuccess($actid);  
  
        }

        //根据openid和jobid判断当前用户是否竞选者自己
         function check_job_my($openid,$jobid){
           $bb=Act::check_job_my($openid,$jobid);
           if ($bb) {
             $cc=array();
             $aa=yz_token();
             $cc['token']=$aa;
             $cc['states']=0;
             echo json_encode($cc, JSON_UNESCAPED_UNICODE);  
           }else{
             $cc=array();
             $aa=yz_token();
             $cc['token']=$aa;
             $cc['states']=1;
             echo json_encode($cc, JSON_UNESCAPED_UNICODE);  
           }
        }
        
        //为成功竞选者标记状态
        function job_success($arr,$cc,$actid)
        {  
            
            
            $count = count($arr);
            // $start = microtime(true);  
            // $ch_list = array();  
            // $multi_ch = curl_multi_init();
            $time=date("m月d日 H:i",time());

           for($i = 0;$i<$count; $i++)
           {  
               $str = count($arr[$i]);
               $bonus = $cc[$i]['bonus'];
               $jobname = $cc[$i]['jobname'];
              
              if($str > 0)
              {    
                   $start = microtime(true);  
                   $ch_list = array();  
                   $multi_ch = curl_multi_init();
               for($j = 0; $j<$str;$j++)
               {   
                   
                   $id = $arr[$i][$j]['job_user_id'];
                   $userid = $arr[$i][$j]['openid'];
                   $uid = get_userid($userid);
                   Act::job_success($id);
                  // User_info::up_uwinmoney($id,$bonus);
                   $total =  '+'.$bonus;
                   $log = '竞选'.$jobname.'角色成功';
                   App::banwinmoney($log,$uid,$total,$actid);
                   $data='{
                         "touser":"'.$userid.'",
                         "template_id":"rLuuCqF9RwYgzJ31nPFcjYbAHWoSgu2DaN1Edj3Wy4o",
                         "url":"https://wechat.123win.com.cn/web/point_friend.html?actid='.$actid.'&openid='.$userid.'",        
                         "data":{
                                 "first": {
                                     "value":"您有一条来自活动中心的消息!",
                                     "color":"#173177"
                                 },
                                 "keyword1":{
                                     "value":"您竞选'.$jobname.'角色成功，获得'.$bonus.'积分",
                                     "color":"#173177"
                                 },
                                 "keyword2":{
                                     "value":"竞选成功",
                                     "color":"#173177"
                                 },
                                 "keyword3": {
                                     "value":"'.$time.'",
                                     "color":"#173177"
                                 },
                                 "remark":{
                                     "value":"感谢您的合作",
                                     "color":"#173177"
                                 }
                         }
                       }';
                $token=get_token();    
                $ch_list[$i] = curl_init("https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=$token");  
                curl_setopt($ch_list[$i],CURLOPT_POSTFIELDS,$data);  
                curl_multi_add_handle($multi_ch, $ch_list[$i]);    
               }
              curl_mulit($start,$multi_ch,$ch_list);
              

              }
             
           }
             
       }

       function get_unsuccess($actid)
       {      
              $time=date("m月d日 H:i",time());
              $result = Act::get_unsuccessful_user($actid);
              $start = microtime(true);  
              $ch_list = array();  
              $multi_ch = curl_multi_init();                 
              $count = count($result);               
              for ($i = 0;$i < $count; ++$i) {  
                     $data='{
                         "touser":"'.$result[$i]['user'].'",
                         "template_id":"rLuuCqF9RwYgzJ31nPFcjYbAHWoSgu2DaN1Edj3Wy4o",
                         "url":"http://wechat.123win.com.cn/web/campaignRole.html?actid='.$actid.'",        
                         "data":{
                                 "first": {
                                     "value":"您有一条来自活动中心的消息!",
                                     "color":"#173177"
                                 },
                                 "keyword1":{
                                     "value":"您竞选'.$result[$i]['jobname'].'角色，落选了,您可以继续竞选",
                                     "color":"#173177"
                                 },
                                 "keyword2": {
                                     "value":"竞选结果将在活动的前一晚9:00公布",
                                     "color":"#173177"
                                 },
                                 "keyword3": {
                                     "value":"'.$time.'",
                                     "color":"#173177"
                                 },
                                 "remark":{
                                     "value":"感谢您的合作",
                                     "color":"#173177"
                                 }
                         }
                       }';
                $token=get_token();    
                $ch_list[$i] = curl_init("https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=$token");  
                curl_setopt($ch_list[$i],CURLOPT_POSTFIELDS,$data);  
                curl_multi_add_handle($multi_ch, $ch_list[$i]);  
                }                                      
                curl_mulit($start,$multi_ch,$ch_list);
       }



        //根据当前角色id列出当前用户的头像，角色名称，和票数
        function get_job_id($jobid){
           $bb=Act::get_job_ida($jobid);//获得角色票数
           $dd=Act::get_job_idb($bb['f_id']);//获得角色昵称
           $ee=Act::get_job_idc($bb['user']);//获得角色头像

             $cc=array();
             $aa=yz_token();
             $cc['token']=$aa;
             $cc['poll']=$bb['poll'];
             $cc['jobname']=$dd['jobname'];
             $cc['headimgurl']=$ee['headimgurl'];


             echo json_encode($cc, JSON_UNESCAPED_UNICODE);  

        }
       
       //学客圈回复
        function ccl_replier($id,$uid,$replier,$content)
        {
            $replier = get_userid($replier);
            if($replier == $uid)
            {  
               $time = time();
               $result = App::cll_put_rew($replier,$content,$id,$time);
            }
            else
            {
              $time = time();
              $result = Act::ccl_replier($id,$uid,$replier,$content,$time);
            }
            
            if($result)
            {
              echo json_encode(array('state'=>'1'));
            }
            else
            {
               echo json_encode(array('state'=>'0'));
            }
        }



//后台api控制开始

        //管理员登录验证开始
        function login_admin($username,$password){
           if($username=="" || $password==""){
            echo json_encode(array('state'=>'2'));
            //echo time();
           }else{
             $aa=Master::check_adminuandp($username,$password);//判断用户名和密码是否存在
             $cc=array();
             if ($aa) {
              $id=$aa['id'];
              $time=time();
              $ip=$_SERVER['REMOTE_ADDR'];
              $token=md5($username+time()+$password+$ip);
              $bb=Master::updata_admintoken($id,$time,$ip,$token);
              if ($bb) {
                echo json_encode(array('state'=>'0','token'=>$token));
              }else{
                echo json_encode(array('state'=>'3'));
              }

              //echo json_encode(array('state'=>'0'));
             }else{
              echo json_encode(array('state'=>'1'));
             }
           }
        }
        // API说明：
        // state返回值0：帐号密码正确
        // state返回值1：帐号密码错误
        // state返回值2：帐号密码提交存在错误
        // state返回值3：登录失败，请重试
        //token说明ip+时间戳
        //管理员登录验证结束

        //列出任务令分类开始
        function Master_Get_ActCat($token,$page,$id){
          $aa=Master::check_admintoken($token);
          $cc=array();
          if ($aa) {
            $bb=Master::get_admin_actcat($page,$id);
            $cc['token']=array('state'=>'0');
            $cc['data']=$bb;
            echo json_encode($cc, JSON_UNESCAPED_UNICODE);
          }else{
            $cc['token']=array('state'=>'1');
            echo json_encode($cc, JSON_UNESCAPED_UNICODE);
          }
        }
        //列出任务令分类开始

        //任务令分类增加开始
        function Master_add_ActCat($token,$tname,$fonticon,$color,$catran){
          $aa=Master::check_admintoken($token);
          $cc=array();
          if ($aa) {
            if ($tname=='') {
              $cc['token']=array('state'=>'0');
              $cc['data']=array('state'=>'2');
              echo json_encode($cc, JSON_UNESCAPED_UNICODE);
            }else{
              $bb=Master::add_admin_actcat($tname,$fonticon,$color,$catran);
              if ($bb) {
                $cc['token']=array('state'=>'0');
                $cc['data']=array('state'=>'0');
                echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                }else{
                $cc['token']=array('state'=>'0');
                $cc['data']=array('state'=>'1');
                echo json_encode($cc, JSON_UNESCAPED_UNICODE);
              }
            } 
          }else{
            $cc['token']=array('state'=>'1');
            echo json_encode($cc, JSON_UNESCAPED_UNICODE);
          }
        }
        //任务令分类增加结束


        //任务令分类编辑开始
        function Master_edit_ActCat($token,$id,$tname,$fonticon,$color,$catran){
          $aa=Master::check_admintoken($token);
          $cc=array();
          if ($aa) {
            if ($tname=='') {
              $cc['token']=array('state'=>'0');
              $cc['data']=array('state'=>'2');
              echo json_encode($cc, JSON_UNESCAPED_UNICODE);
            }else{
              $bb=Master::edit_admin_actcat($id,$tname,$fonticon,$color,$catran);
              if ($bb) {
                $cc['token']=array('state'=>'0');
                $cc['data']=array('state'=>'0');
                echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                }else{
                $cc['token']=array('state'=>'0');
                $cc['data']=array('state'=>'1');
                echo json_encode($cc, JSON_UNESCAPED_UNICODE);
              }
            } 
          }else{
            $cc['token']=array('state'=>'1');
            echo json_encode($cc, JSON_UNESCAPED_UNICODE);
          }
        }
        //任务令分类编辑结束


        //任务令分类删除开始
        function Master_del_ActCat($token){
          $aa=Master::check_admintoken($token);
          $cc=array();
          if ($aa) {
            if ($id=='') {
              $cc['token']=array('state'=>'0');
              $cc['data']=array('state'=>'2');
              echo json_encode($cc, JSON_UNESCAPED_UNICODE);
            }else{
              $bb=Master::del_admin_actcat($id);
              if ($bb) {
                $cc['token']=array('state'=>'0');
                $cc['data']=array('state'=>'0');
                echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                }else{
                $cc['token']=array('state'=>'0');
                $cc['data']=array('state'=>'1');
                echo json_encode($cc, JSON_UNESCAPED_UNICODE);
              }
            } 
          }else{
            $cc['token']=array('state'=>'1');
            echo json_encode($cc, JSON_UNESCAPED_UNICODE);
          }
        }
        //任务令分类删除结束
       
       //发布任务类
      function Master_add_ActInfo($token,$catid,$title,$actdesc, $actimgurl,$actcode,$crocode,$masterid,$mastername,$masterphone,$actstarttime,$actendtime,$joinstarttime,$joinendtime,$userlimit,$deposit,$actsta,$adminsta,$time,$lockstatus,$count,$content,$longitude,$latitude,$address,$arr,$mapurl,$setvalue,$actran){
          $aa=Master::check_admintoken($token);
          $time = time();
          if ($time < $actstarttime){
               //活动未开始
               $actsta=2;
            }elseif($actstarttime < $time && $time < $actendtime){
              //活动进行中
               $actsta=1;
            }elseif($time > $actendtime){
               //活动已结束
               $actsta=0;
                 
            }
            
          if ($aa) { 

              $bb=Master::add_admin_actinfo($title,$catid,$actdesc, $actimgurl,$actcode,$crocode,$masterid,$mastername,$masterphone,$actstarttime,$actendtime,$joinstarttime,$joinendtime,$userlimit,$deposit,$actsta,$adminsta,$time,$lockstatus,$count,$content,$longitude,$latitude,$address,$arr,$mapurl,$setvalue,$actran);
            
              if ($bb) {
                $actid=Act::get_actid();
                $actid=$actid['actid'];
                $total=0;
                Act::put_act_ranking($actid,1,$total);
                Act::put_act_ranking($actid,2,$total);
                Act::put_act_ranking($actid,3,$total);
                Act::put_act_ranking($actid,4,$total);
                $cc['token']=array('state'=>'0');
                $cc['data']=array('state'=>'0');
                echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                }else{
                $cc['token']=array('state'=>'0');
                $cc['data']=array('state'=>'1');
                echo json_encode($cc, JSON_UNESCAPED_UNICODE);
              }              
          }else{
            $cc['token']=array('state'=>'1');
            echo json_encode($cc, JSON_UNESCAPED_UNICODE);
          }
        }
      
       //提交任务编辑内容
       function Master_edit_ActInfo($id,$token,$catid,$title,$actdesc, $actimgurl,$actcode,$crocode,$masterid,$mastername,$masterphone,$actstarttime,$actendtime,$joinstarttime,$joinendtime,$userlimit,$deposit,$actsta,$adminsta,$time,$lockstatus,$count,$content,$longitude,$latitude,$address,$arr,$mapurl,$setvalue,$actran){
        
          $aa=Master::check_admintoken($token);
           $time = time();
           if ($time < $actstarttime){
               //活动未开始
               $actsta=2;
            }elseif($actstarttime < $time && $time < $actendtime){
              //活动进行中
               $actsta=1;
            }elseif($time > $actendtime){
               //活动已结束
               $actsta=0;
                 
            }
          if ($aa) {            
              $bb=Master::edit_admin_actinfo($id,$catid,$title,$actdesc, $actimgurl,$actcode,$crocode,$masterid,$mastername,$masterphone,$actstarttime,$actendtime,$joinstarttime,$joinendtime,$userlimit,$deposit,$actsta,$adminsta,$time,$lockstatus,$count,$content,$longitude,$latitude,$address,$arr,$mapurl,$setvalue,$actran);
              if ($bb) {
                $cc['token']=array('state'=>'0');
                $cc['data']=array('state'=>'0');
                echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                }else{
                $cc['token']=array('state'=>'0');
                $cc['data']=array('state'=>'1');
                echo json_encode($cc, JSON_UNESCAPED_UNICODE);
              }              
          }else{
            $cc['token']=array('state'=>'1');
            echo json_encode($cc, JSON_UNESCAPED_UNICODE);
          }
        }
       
       //删除任务
       function Master_del_ActInfo($id,$token){
         
          $aa=Master::check_admintoken($token);
          $cc=array();
          if ($aa) {            
                $bb=Master::del_admin_actinfo($id);
              if ($bb) {
                $cc['token']=array('state'=>'0');
                $cc['data']=array('state'=>'0');
                echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                }else{
                $cc['token']=array('state'=>'0');
                $cc['data']=array('state'=>'1');
                echo json_encode($cc, JSON_UNESCAPED_UNICODE);
              }              
          }else{
            $cc['token']=array('state'=>'1');
            echo json_encode($cc, JSON_UNESCAPED_UNICODE);
          }
        }
        
       //获取该任务令下的所有内容
       function Master_get_ActInfo($id,$token){
         
          $aa=Master::check_admintoken($token);
          $cc=array();
          if ($aa) {            
                $bb=Master::get_admin_actinfo($id);
              if ($bb) {
                $cc['token']=array('state'=>'0');
                $cc['data']=array('state'=>'0');
                $cc['response'] = $bb;
                echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                }else{
                $cc['token']=array('state'=>'0');
                $cc['data']=array('state'=>'1');
                echo json_encode($cc, JSON_UNESCAPED_UNICODE);
              }              
          }else{
            $cc['token']=array('state'=>'1');
            echo json_encode($cc, JSON_UNESCAPED_UNICODE);
          }
        }
        
      //获取商品分类
      function get_type()
      {
          $result = Master::get_type();
          echo json_encode($result, JSON_UNESCAPED_UNICODE); 
      }
      //发布商品
      function Master_add_Goods($gid,$token,$goods_name,$abs,$imgurl,$total,$time,$goods_number,$is_real,$count,$content,$url){

            $aa=Master::check_admintoken($token);
            $cc=array();
            if ($aa) {            
                  $bb=Master::add_admin_goods($gid,$goods_name,$abs,$imgurl,$total,$time,$goods_number,$is_real,$count,$content,$url);
                if ($bb) {
                  $cc['token']=array('state'=>'0');
                  $cc['data']=array('state'=>'0');
                  echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                  }else{
                  $cc['token']=array('state'=>'0');
                  $cc['data']=array('state'=>'1');
                  echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                }              
            }else{
              $cc['token']=array('state'=>'1');
              echo json_encode($cc, JSON_UNESCAPED_UNICODE);
            } 
        }

        //删除商品
        function Master_del_Goods($id,$token)
        {
            $aa=Master::check_admintoken($token);
            $cc=array();
            if ($aa) {            
                  $bb=Master::del_admin_goods($id);
                if ($bb) {
                  $cc['token']=array('state'=>'0');
                  $cc['data']=array('state'=>'0');
                  echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                  }else{
                  $cc['token']=array('state'=>'0');
                  $cc['data']=array('state'=>'1');
                  echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                }              
            }else{
              $cc['token']=array('state'=>'1');
              echo json_encode($cc, JSON_UNESCAPED_UNICODE);
            } 
        }

        //编辑商品
        function Master_edit_Goods($id,$gid,$token,$goods_name,$abs,$imgurl,$total,$time,$goods_number,$is_real,$url,$content,$count)
        {    
            
             $aa=Master::check_admintoken($token);
              $cc=array();
              if ($aa) {            
                    $bb=Master::edit_admin_goods($id,$gid,$goods_name,$abs,$imgurl,$total,$time,$goods_number,$is_real,$url,$content,$count);
                  if ($bb) {
                    $cc['token']=array('state'=>'0');
                    $cc['data']=array('state'=>'0');
                    echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                    }else{
                    $cc['token']=array('state'=>'0');
                    $cc['data']=array('state'=>'1');
                    echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                  }              
              }else{
                $cc['token']=array('state'=>'1');
                echo json_encode($cc, JSON_UNESCAPED_UNICODE);
              } 
        }

        //获取单个商品的所有信息
        function Master_get_Goodsdetail($id,$token)
        {
              $aa=Master::check_admintoken($token);
                $cc=array();
                if ($aa) {            
                      $bb=Master::get_admin_goodsdetail($id);
                    if ($bb) {
                      $cc['token']=array('state'=>'0');
                      $cc['data']=array('state'=>'0');
                      $cc['response'] = $bb;
                      echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                      }else{
                      $cc['token']=array('state'=>'0');
                      $cc['data']=array('state'=>'1');
                      echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                    }              
                }else{
                  $cc['token']=array('state'=>'1');
                  echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                } 

        }

       //获取所有商品
      function Master_get_Goods($token,$page,$gid)
      {
            $aa=Master::check_admintoken($token);
                $cc=array();
                if ($aa) {            
                      $bb=Master::get_admin_goods($page,$gid);
                    if ($bb) {
                      $cc['token']=array('state'=>'0');
                      $cc['data']=array('state'=>'0');
                      $cc['response'] = $bb;
                      echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                      }else{
                      $cc['token']=array('state'=>'0');
                      $cc['data']=array('state'=>'1');
                      echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                    }              
                }else{
                  $cc['token']=array('state'=>'1');
                  echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                } 

      }
      //商品部分结束

      //会员订单管理开始
      //获取订单列表
      function Master_get_orders($token,$page,$order_sn)
      {
            $aa=Master::check_admintoken($token);
              $cc=array();
              if ($aa) {            
                    $bb=Master::get_admin_orders($page,$order_sn);
                  if ($bb) {
                    $cc['token']=array('state'=>'0');
                    $cc['data']=array('state'=>'0');
                    $cc['response'] = $bb;
                    echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                    }else{
                    $cc['token']=array('state'=>'0');
                    $cc['data']=array('state'=>'1');
                    echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                  }              
              }else{
                $cc['token']=array('state'=>'1');
                echo json_encode($cc, JSON_UNESCAPED_UNICODE);
              } 

      }
      //二维码更新状态
      function update_shoppingstatus($order_sn)
      {
           $type=Goods::get_oneaddress($order_sn);
           echo json_encode($type,JSON_UNESCAPED_UNICODE);   
   
      }
  
    //删除订单
     function Master_del_Orders($id,$token)
     {
          $aa=Master::check_admintoken($token);
              $cc=array();
              if ($aa) {            
                    $bb=Master::del_admin_orders($id);
                  if ($bb) {
                    $cc['token']=array('state'=>'0');
                    $cc['data']=array('state'=>'0');
                    echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                    }else{
                    $cc['token']=array('state'=>'0');
                    $cc['data']=array('state'=>'1');
                    echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                  }              
              }else{
                $cc['token']=array('state'=>'1');
                echo json_encode($cc, JSON_UNESCAPED_UNICODE);
              } 

     }

    //获取广告列表
    function Master_get_Adv($token,$page,$ad_type)
     {
          $aa=Master::check_admintoken($token);
              $cc=array();
              if ($aa) {            
                    $bb=Master::get_admin_adv($page,$ad_type);
                  if ($bb) {
                    $cc['token']=array('state'=>'0');
                    $cc['data']=array('state'=>'0');
                    $cc['response'] = $bb;
                    echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                    }else{
                    $cc['token']=array('state'=>'0');
                    $cc['data']=array('state'=>'1');
                    echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                  }              
              }else{
                $cc['token']=array('state'=>'1');
                echo json_encode($cc, JSON_UNESCAPED_UNICODE);
              } 

     }
    
    //添加广告
    function Master_add_Adv($token,$title,$imgurl,$link,$lockstatus,$time,$sork,$ad_type)
     {
          $aa=Master::check_admintoken($token);
              $cc=array();
              if ($aa) {            
                    $bb=Master::add_admin_adv($title,$imgurl,$link,$lockstatus,$time,$sork,$ad_type);
                  if ($bb) {
                    $cc['token']=array('state'=>'0');
                    $cc['data']=array('state'=>'0');
                    echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                    }else{
                    $cc['token']=array('state'=>'0');
                    $cc['data']=array('state'=>'1');
                    echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                  }              
              }else{
                $cc['token']=array('state'=>'1');
                echo json_encode($cc, JSON_UNESCAPED_UNICODE);
              } 

     }
     
     //编辑广告
     function Master_edit_Adv($token,$id,$title,$imgurl,$link,$lockstatus,$time,$sork,$ad_type)
     {
          $aa=Master::check_admintoken($token);
              $cc=array();
              if ($aa) {            
                    $bb=Master::edit_admin_adv($id,$title,$imgurl,$link,$lockstatus,$time,$sork,$ad_type);
                  if ($bb) {
                    $cc['token']=array('state'=>'0');
                    $cc['data']=array('state'=>'0');
                    echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                    }else{
                    $cc['token']=array('state'=>'0');
                    $cc['data']=array('state'=>'1');
                    echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                  }              
              }else{
                $cc['token']=array('state'=>'1');
                echo json_encode($cc, JSON_UNESCAPED_UNICODE);
              } 

     }
     //获取单个广告
     function Master_get_oneAdv($token,$id)
     {
          $aa=Master::check_admintoken($token);
              $cc=array();
              if ($aa) {            
                    $bb=Master::get_admin_oneadv($id);
                  if ($bb) {
                    $cc['token']=array('state'=>'0');
                    $cc['data']=array('state'=>'0');
                    $cc['response'] = $bb;
                    echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                    }else{
                    $cc['token']=array('state'=>'0');
                    $cc['data']=array('state'=>'1');
                    echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                  }              
              }else{
                $cc['token']=array('state'=>'1');
                echo json_encode($cc, JSON_UNESCAPED_UNICODE);
              } 

     }
     //删除广告
     function Master_del_Adv($token,$id)
     {
          $aa=Master::check_admintoken($token);
              $cc=array();
              if ($aa) {            
                    $bb=Master::del_admin_adv($id);
                  if ($bb) {
                    $cc['token']=array('state'=>'0');
                    $cc['data']=array('state'=>'0');
                    echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                    }else{
                    $cc['token']=array('state'=>'0');
                    $cc['data']=array('state'=>'1');
                    echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                  }              
              }else{
                $cc['token']=array('state'=>'1');
                echo json_encode($cc, JSON_UNESCAPED_UNICODE);
              } 

     }
     
     //获取学客圈列表
     function Master_get_ccl($token,$page,$uid)
     {
          $aa=Master::check_admintoken($token);
              $cc=array();
              if ($aa) {            
                    $bb=Master::get_admin_ccl($page,$uid);
                  if ($bb) {
                    $cc['token']=array('state'=>'0');
                    $cc['data']=array('state'=>'0');
                    $cc['response'] = $bb;
                    echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                    }else{
                    $cc['token']=array('state'=>'0');
                    $cc['data']=array('state'=>'1');
                    echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                  }              
              }else{
                $cc['token']=array('state'=>'1');
                echo json_encode($cc, JSON_UNESCAPED_UNICODE);
              } 

     }
     
     //删除学客圈
     function Master_del_ccl($token,$id)
     {
          $aa=Master::check_admintoken($token);
              $cc=array();
              if ($aa) {            
                    $bb=Master::del_admin_ccl($id);
                  if ($bb) {
                    $cc['token']=array('state'=>'0');
                    $cc['data']=array('state'=>'0');
                    echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                    }else{
                    $cc['token']=array('state'=>'0');
                    $cc['data']=array('state'=>'1');
                    echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                  }              
              }else{
                $cc['token']=array('state'=>'1');
                echo json_encode($cc, JSON_UNESCAPED_UNICODE);
              } 

     }
     

     //获取会员列表
     function Master_get_userinfo($token,$page,$name)
     {
          $aa=Master::check_admintoken($token);
              $cc=array();
              if ($aa) {            
                    $bb=Master::get_admin_userinfo($page,$name);
                  if ($bb) {
                    $cc['token']=array('state'=>'0');
                    $cc['data']=array('state'=>'0');
                    $cc['response'] = $bb;
                    echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                    }else{
                    $cc['token']=array('state'=>'0');
                    $cc['data']=array('state'=>'1');
                    echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                  }              
              }else{
                $cc['token']=array('state'=>'1');
                echo json_encode($cc, JSON_UNESCAPED_UNICODE);
              } 

     }
     //获取学令牌app用户
     function Master_app_user($token,$tel)
     {
          $aa=Master::check_admintoken($token);
              $cc=array();
              if ($aa) {            
                    $bb=Master::get_app_userinfo($tel);
                  if ($bb) {
                    $cc['token']=array('state'=>'0');
                    $cc['data']=array('state'=>'0');
                    $cc['response'] = $bb;
                    echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                    }else{
                    $cc['token']=array('state'=>'0');
                    $cc['data']=array('state'=>'1');
                    echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                  }              
              }else{
                $cc['token']=array('state'=>'1');
                echo json_encode($cc, JSON_UNESCAPED_UNICODE);
              }       
     }
     function agent_subordinate($token,$form,$user_id,$tel)
     {
          $aa=Master::check_admintoken($token);
              $cc=array();
              if ($aa) {            
                    $bb=Master::agent_subordinate($form,$user_id,$tel);
                  if ($bb) {
                    $cc['token']=array('state'=>'0');
                    $cc['data']=array('state'=>'0');
                    $cc['response'] = $bb;
                    echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                    }else{
                    $cc['token']=array('state'=>'0');
                    $cc['data']=array('state'=>'1');
                    echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                  }              
              }else{
                $cc['token']=array('state'=>'1');
                echo json_encode($cc, JSON_UNESCAPED_UNICODE);
              }   
     }
     function agent_invest($token,$money,$user_id)
     {
          $aa=Master::check_admintoken($token);
              $cc=array();
              if ($aa) {            
                    $result = User_info::check_agent_relation($user_id);
                    $money = $money+$result['investment'];
                    switch ($money) {
                      case '10000':
                        $upper_limit = 60000;
                        break;
                      case '50000':
                        $upper_limit = 330000;
                        break;
                      case '300000': 
                        $upper_limit= 2600000;
                        break;
                      case '980':
                        $upper_limit = 6000;
                        break;   
                      default:
                        # code...
                        break;
                    }
                    $time = time();
                    $bb = User_info::add_agent_earnings($user_id,$money,$result['pid'],$result['top_pid'],$upper_limit);
                    if($result['pid'])
                    {
                      User_info::winmoney_log($log="二级推荐分红",$result['pid'],$credit=$money*0.12,$type='',$actid='');  
                    }
                    else if ($result['top_pid']) {
                      User_info::winmoney_log($log="推荐奖励分红",$result['top_pid'],$credit=$money*0.38,$type='',$actid='');
                    }        
                  if ($bb) {
                    $cc['token']=array('state'=>'0');
                    $cc['data']=array('state'=>'0');
                    echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                    }else{
                    $cc['token']=array('state'=>'0');
                    $cc['data']=array('state'=>'1');
                    echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                  }              
              }else{
                $cc['token']=array('state'=>'1');
                echo json_encode($cc, JSON_UNESCAPED_UNICODE);
              } 
     }
     function update_user_card($token,$id,$card_number)
     {
              $aa=Master::check_admintoken($token);
              $cc=array();
              if ($aa) {            
                    $bb=Master::update_user_card($id,$card_number);
                  if ($bb) {
                    $cc['token']=array('state'=>'0');
                    $cc['data']=array('state'=>'0');
                    echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                    }else{
                    $cc['token']=array('state'=>'0');
                    $cc['data']=array('state'=>'1');
                    echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                  }              
              }else{
                $cc['token']=array('state'=>'1');
                echo json_encode($cc, JSON_UNESCAPED_UNICODE);
              }   
     }
     //编辑用户资料
     function update_user_info($token,$id,$winmoney,$nickname,$headimgurl,$bareheaded_photo,$credit,$bust_shot,$id_card,$role)
     {
            $aa=Master::check_admintoken($token);
              $cc=array();
              if ($aa) {            
                    $bb=Master::update_user_info($id,$winmoney,$nickname,$headimgurl,$bareheaded_photo,$credit,$bust_shot,$id_card,$role);
                  if ($bb) {
                    $cc['token']=array('state'=>'0');
                    $cc['data']=array('state'=>'0');
                    echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                    }else{
                    $cc['token']=array('state'=>'0');
                    $cc['data']=array('state'=>'1');
                    echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                  }              
              }else{
                $cc['token']=array('state'=>'1');
                echo json_encode($cc, JSON_UNESCAPED_UNICODE);
              }
     }
     function check_user_info($token,$id)
     {
              $aa=Master::check_admintoken($token);
              $cc=array();
              if ($aa) {            
                    $bb=Master::check_user_info($id);
                  if ($bb) {
                    $cc['token']=array('state'=>'0');
                    $cc['data']=array('state'=>'0');
                    $cc['response'] = $bb;
                    echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                    }else{
                    $cc['token']=array('state'=>'0');
                    $cc['data']=array('state'=>'1');
                    echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                  }              
              }else{
                $cc['token']=array('state'=>'1');
                echo json_encode($cc, JSON_UNESCAPED_UNICODE);
              }
     }
     //改变实名认证状态
     function Master_info_status($token,$user_id,$status)
     {
          $aa=Master::check_admintoken($token);
              $cc=array();
              if ($aa) {
                    $device_id = Master::check_device($user_id);
                    $status?'1=1':jiguang_push($device_id,$content='个人信息审核不通过，请重新登陆上传');             
                    $bb=Master::update_info_status($user_id,$status);
                  if ($bb) {
                    $cc['token']=array('state'=>'0');
                    $cc['data']=array('state'=>'0');
                    echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                    }else{
                    $cc['token']=array('state'=>'0');
                    $cc['data']=array('state'=>'1');
                    echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                  }              
              }else{
                $cc['token']=array('state'=>'1');
                echo json_encode($cc, JSON_UNESCAPED_UNICODE);
              }
     }
     //屏蔽用户学客圈
     function Master_shield_ccl($token,$id,$user_id,$status)
     {
              $aa=Master::check_admintoken($token);
              $cc=array();
              if ($aa) {
                    $device_id = Master::check_device($user_id);
                    $time = time();
                    $status?'1=1':jiguang_push($device_id,$content='系统消息:发表不良内容，该学客圈已被删除');
                    Master::add_system_news($user_id,$title='系统消息',$news='发表不良内容，该学客圈已被删除',$time);              
                    $bb=Master::Master_shield_ccl($id,$status);
                  if ($bb) {
                    $cc['token']=array('state'=>'0');
                    $cc['data']=array('state'=>'0');
                    echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                    }else{
                    $cc['token']=array('state'=>'0');
                    $cc['data']=array('state'=>'1');
                    echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                  }              
              }else{
                $cc['token']=array('state'=>'1');
                echo json_encode($cc, JSON_UNESCAPED_UNICODE);
              }
     }
     function Master_get_record($token,$uid)
     {
          $aa=Master::check_admintoken($token);
              $cc=array();
              if ($aa) {            
                    $bb=Master::get_record($uid);
                  if ($bb) {
                    $cc['token']=array('state'=>'0');
                    $cc['data']=array('state'=>'0');
                    $cc['response'] = $bb;
                    echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                    }else{
                    $cc['token']=array('state'=>'0');
                    $cc['data']=array('state'=>'1');
                    echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                  }              
              }else{
                $cc['token']=array('state'=>'1');
                echo json_encode($cc, JSON_UNESCAPED_UNICODE);
              }
     }
     function agent_manage($token,$tel)
     {
          $aa=Master::check_admintoken($token);
              $cc=array();
              if ($aa) {            
                    $bb=Master::agent_manage($tel);
                  if ($bb) {
                    $cc['token']=array('state'=>'0');
                    $cc['data']=array('state'=>'0');
                    $cc['response'] = $bb;
                    echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                    }else{
                    $cc['token']=array('state'=>'0');
                    $cc['data']=array('state'=>'1');
                    echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                  }              
              }else{
                $cc['token']=array('state'=>'1');
                echo json_encode($cc, JSON_UNESCAPED_UNICODE);
              }
     }
     function Master_consent_application($token,$id,$status,$user_id)
     {
          $aa=Master::check_admintoken($token);
              $cc=array();
              if ($aa) {
                    $device_id = Master::check_device($user_id);
                    $time = time();
                    if(!$status)
                    {
                      jiguang_push($device_id,$content='您收到一条系统消息，请点击查看');
                      Master::add_system_news($user_id,$title='系统消息',$news='提现申请不同意，提现金额已返回账户',$time);   
                    }
                    else
                    {
                      jiguang_push($device_id,$content='您收到一条系统消息，请点击查看');
                      Master::add_system_news($user_id,$title='系统消息',$news='提现申请已同意',$time); 
                    }            
                    $bb=Master::consent_application($id,$status);
                  if ($bb) {
                    $cc['token']=array('state'=>'0');
                    $cc['data']=array('state'=>'0');
                    echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                    }else{
                    $cc['token']=array('state'=>'0');
                    $cc['data']=array('state'=>'1');
                    echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                  }              
              }else{
                $cc['token']=array('state'=>'1');
                echo json_encode($cc, JSON_UNESCAPED_UNICODE);
              }
     }
     function Master_app_act($token,$status,$user_id)
     {
            $aa=Master::check_admintoken($token);
              $cc=array();
              if ($aa) {            
                    $bb=Master::app_act($status,$user_id);
                  if ($bb) {
                    $cc['token']=array('state'=>'0');
                    $cc['data']=array('state'=>'0');
                    $cc['response'] = $bb;
                    echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                    }else{
                    $cc['token']=array('state'=>'0');
                    $cc['data']=array('state'=>'1');
                    echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                  }              
              }else{
                $cc['token']=array('state'=>'1');
                echo json_encode($cc, JSON_UNESCAPED_UNICODE);
              }
     }
     function update_user_act($token,$id,$title,$actimgurl,$tel,$actstarttime,$userlimit,$actsta,$address,$subject,$start_img,$underway_img,$end_img,$latitude,$longitude)
     {
          $aa=Master::check_admintoken($token);
          $cc=array();
          if ($aa) {            
                $bb=Master::update_user_act($token,$id,$title,$actimgurl,$tel,$actstarttime,$userlimit,$actsta,$address,$subject,$start_img,$underway_img,$end_img,$latitude,$longitude);
              if ($bb) {
                $cc['token']=array('state'=>'0');
                $cc['data']=array('state'=>'0');
                echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                }else{
                $cc['token']=array('state'=>'0');
                $cc['data']=array('state'=>'1');
                echo json_encode($cc, JSON_UNESCAPED_UNICODE);
              }              
          }else{
            $cc['token']=array('state'=>'1');
            echo json_encode($cc, JSON_UNESCAPED_UNICODE);
          } 
     }
     function show_user_act($token,$id)
     {
              $aa=Master::check_admintoken($token);
              $cc=array();
              if ($aa) {            
                    $bb=Master::show_user_act($id);
                  if ($bb) {
                    $cc['token']=array('state'=>'0');
                    $cc['data']=array('state'=>'0');
                    $cc['response'] = $bb;
                    echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                    }else{
                    $cc['token']=array('state'=>'0');
                    $cc['data']=array('state'=>'1');
                    echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                  }              
              }else{
                $cc['token']=array('state'=>'1');
                echo json_encode($cc, JSON_UNESCAPED_UNICODE);
              }
     }
      //极光推送(别名推送)
     // function jiguang_push($registrationid_arr,$content,$user_id)
     // {         
     //    $push = new Jgpush();
     //    $data = $push->getDevices($registrationid_arr);
       
     //    if($data['body']['alias'])
     //    {
     //       $alias=array($data['body']['alias']);
     //    }
     //    else
     //    {
     //       $push->updateAlias($registrationid_arr,$user_id);
     //       $alias = array($user_id); 
     //    }
     //    $res = $push->push_a($alias,$content);  
       
     // }
    //极光推送(设备号推送)
     function jiguang_push($registrationid_arr,$content)
     {
        $push = new Jgpush();
        $registrationid_arr = array($registrationid_arr);  
        $push->registrationid_push($registrationid_arr,$content);

     }
     function Master_act_img($token)
     {
            $aa=Master::check_admintoken($token);
            $cc=array();
            if ($aa) {            
                  $bb=Master::act_img();
                if ($bb) {
                  $cc['token']=array('state'=>'0');
                  $cc['data']=array('state'=>'0');
                  $cc['response'] = $bb;
                  echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                  }else{
                  $cc['token']=array('state'=>'0');
                  $cc['data']=array('state'=>'1');
                  echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                }              
            }else{
              $cc['token']=array('state'=>'1');
              echo json_encode($cc, JSON_UNESCAPED_UNICODE);
            }
     }
     function Master_update_actimg($token,$id,$imgurl)
     {
          $aa=Master::check_admintoken($token);
          $cc=array();
          if ($aa) {            
                $bb=Master::Master_update_actimg($id,$imgurl);
              if ($bb) {
                $cc['token']=array('state'=>'0');
                $cc['data']=array('state'=>'0');
                echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                }else{
                $cc['token']=array('state'=>'0');
                $cc['data']=array('state'=>'1');
                echo json_encode($cc, JSON_UNESCAPED_UNICODE);
              }              
          }else{
            $cc['token']=array('state'=>'1');
            echo json_encode($cc, JSON_UNESCAPED_UNICODE);
          }   
     }
     function  Master_xlp_ccl($token)
     {
          $aa=Master::check_admintoken($token);
          $cc=array();
          if ($aa) {            
                $bb=Master::xlp_ccl();
              if ($bb) {
                $cc['token']=array('state'=>'0');
                $cc['data']=array('state'=>'0');
                $cc['response'] = $bb;
                echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                }else{
                $cc['token']=array('state'=>'0');
                $cc['data']=array('state'=>'1');
                echo json_encode($cc, JSON_UNESCAPED_UNICODE);
              }              
          }else{
            $cc['token']=array('state'=>'1');
            echo json_encode($cc, JSON_UNESCAPED_UNICODE);
          }
     } 
     function Master_act_check($token,$id,$status,$user_id)
     {
            $aa=Master::check_admintoken($token);
              $cc=array();
              if ($aa) {            
                    $bb=Master::act_check($id,$status);
                    $device_id = Master::check_device($user_id);
                    if(!$status)
                    {
                      $time = time();
                      jiguang_push($device_id,$content='系统消息:您的活动审核不通过！');
                      Master::add_system_news($user_id,$title='系统消息',$news='活动图片审核不通过',$time);        
                    }
                  if ($bb) {
                    $cc['token']=array('state'=>'0');
                    $cc['data']=array('state'=>'0');
                    echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                    }else{
                    $cc['token']=array('state'=>'0');
                    $cc['data']=array('state'=>'1');
                    echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                  }              
              }else{
                $cc['token']=array('state'=>'1');
                echo json_encode($cc, JSON_UNESCAPED_UNICODE);
              }
     }
     function Master_product_refund($token,$uid)
     {
            $aa=Master::check_admintoken($token);
              $cc=array();
              if ($aa) {            
                    $bb=Master::product_refund($uid);
                  if ($bb) {
                    $cc['token']=array('state'=>'0');
                    $cc['data']=array('state'=>'0');
                    $cc['response'] = $bb;
                    echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                    }else{
                    $cc['token']=array('state'=>'0');
                    $cc['data']=array('state'=>'1');
                    echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                  }              
              }else{
                $cc['token']=array('state'=>'1');
                echo json_encode($cc, JSON_UNESCAPED_UNICODE);
              }    
     }
     function Master_consent_refund($token,$id,$status)
     {
            $aa=Master::check_admintoken($token);
              $cc=array();
              if ($aa) {            
                    $bb=Master::consent_refund($id,$status);
                  if ($bb) {
                    $cc['token']=array('state'=>'0');
                    $cc['data']=array('state'=>'0');
                    echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                    }else{
                    $cc['token']=array('state'=>'0');
                    $cc['data']=array('state'=>'1');
                    echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                  }              
              }else{
                $cc['token']=array('state'=>'1');
                echo json_encode($cc, JSON_UNESCAPED_UNICODE);
              } 
     }
     function Master_user_card($token,$uid)
     {
            $aa=Master::check_admintoken($token);
              $cc=array();
              if ($aa) {            
                    $bb=Master::user_card($uid);
                  if ($bb) {
                    $cc['token']=array('state'=>'0');
                    $cc['data']=array('state'=>'0');
                    $cc['response'] = $bb;
                    echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                    }else{
                    $cc['token']=array('state'=>'0');
                    $cc['data']=array('state'=>'1');
                    echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                  }              
              }else{
                $cc['token']=array('state'=>'1');
                echo json_encode($cc, JSON_UNESCAPED_UNICODE);
              }
     }
     function Master_user_usb($token,$uid)
     {
            $aa=Master::check_admintoken($token);
              $cc=array();
              if ($aa) {            
                    $bb=Master::user_usb($uid);
                  if ($bb) {
                    $cc['token']=array('state'=>'0');
                    $cc['data']=array('state'=>'0');
                    $cc['response'] = $bb;
                    echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                    }else{
                    $cc['token']=array('state'=>'0');
                    $cc['data']=array('state'=>'1');
                    echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                  }              
              }else{
                $cc['token']=array('state'=>'1');
                echo json_encode($cc, JSON_UNESCAPED_UNICODE);
              } 
     }
     function Master_user_xlp($token,$uid)
     {
            $aa=Master::check_admintoken($token);
              $cc=array();
              if ($aa) {            
                    $bb=Master::user_xlp($uid);
                  if ($bb) {
                    $cc['token']=array('state'=>'0');
                    $cc['data']=array('state'=>'0');
                    $cc['response'] = $bb;
                    echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                    }else{
                    $cc['token']=array('state'=>'0');
                    $cc['data']=array('state'=>'1');
                    echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                  }              
              }else{
                $cc['token']=array('state'=>'1');
                echo json_encode($cc, JSON_UNESCAPED_UNICODE);
              }
     }
     function Master_winmoney_detail($token,$user_id,$page,$name)
     {
          $aa=Master::check_admintoken($token);
              $cc=array();
              if ($aa) {            
                    $bb=Master::get_winmoney_detail($user_id,$page,$name);
                  if ($bb) {
                    $cc['token']=array('state'=>'0');
                    $cc['data']=array('state'=>'0');
                    $cc['response'] = $bb;
                    echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                    }else{
                    $cc['token']=array('state'=>'0');
                    $cc['data']=array('state'=>'1');
                    echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                  }              
              }else{
                $cc['token']=array('state'=>'1');
                echo json_encode($cc, JSON_UNESCAPED_UNICODE);
              }      
     }
     function update_shipping_status($token,$shipping_status)
     {
          $aa=Master::check_admintoken($token);
          $cc=array();
          if ($aa) {            
                $bb=Master::update_shipping_status($id,$shipping_status);
              if ($bb) {
                $cc['token']=array('state'=>'0');
                $cc['data']=array('state'=>'0');
                echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                }else{
                $cc['token']=array('state'=>'0');
                $cc['data']=array('state'=>'1');
                echo json_encode($cc, JSON_UNESCAPED_UNICODE);
              }              
          }else{
            $cc['token']=array('state'=>'1');
            echo json_encode($cc, JSON_UNESCAPED_UNICODE);
          }   
     }
     function Master_earnings_detail($token,$user_id)
     {
          $aa=Master::check_admintoken($token);
          $cc=array();
          if ($aa) {            
                $bb=Master::Master_earnings_detail($user_id);
              if ($bb) {
                $cc['token']=array('state'=>'0');
                $cc['data']=array('state'=>'0');
                $cc['response'] = $bb;
                echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                }else{
                $cc['token']=array('state'=>'0');
                $cc['data']=array('state'=>'1');
                echo json_encode($cc, JSON_UNESCAPED_UNICODE);
              }              
          }else{
            $cc['token']=array('state'=>'1');
            echo json_encode($cc, JSON_UNESCAPED_UNICODE);
          }
     }
    
     //编辑会员学分
     function Master_winmoney_edit($token,$id,$winmoney)
     {
          $aa=Master::check_admintoken($token);
              $cc=array();
              if ($aa) {            
                    $bb=Master::edit_admin_winmoney($id,$winmoney);
                  if ($bb) {
                    $cc['token']=array('state'=>'0');
                    $cc['data']=array('state'=>'0');
                    $cc['response'] = $bb;
                    echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                    }else{
                    $cc['token']=array('state'=>'0');
                    $cc['data']=array('state'=>'1');
                    echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                  }              
              }else{
                $cc['token']=array('state'=>'1');
                echo json_encode($cc, JSON_UNESCAPED_UNICODE);
              } 

     }
    
    //回收站
    function Master_recovery($id,$token,$tabname)
    {
          $aa=Master::check_admintoken($token);
              $cc=array();
              if ($aa) {            
                    $bb=Master::recovery_admin($id,$tabname);
                  if ($bb) {
                    $cc['token']=array('state'=>'0');
                    $cc['data']=array('state'=>'0');
                    echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                    }else{
                    $cc['token']=array('state'=>'0');
                    $cc['data']=array('state'=>'1');
                    echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                  }              
              }else{
                $cc['token']=array('state'=>'1');
                echo json_encode($cc, JSON_UNESCAPED_UNICODE);
              } 

    }

    //显示商品回收站列表
     function Master_getgoods_recovery($token,$page,$gid)
     {
          $aa=Master::check_admintoken($token);
              $cc=array();
              if ($aa) {            
                    $bb=Master::get_recovery_goods($page,$gid);
                  if ($bb) {
                    $cc['token']=array('state'=>'0');
                    $cc['data']=array('state'=>'0');
                    $cc['response'] = $bb;
                    echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                    }else{
                    $cc['token']=array('state'=>'0');
                    $cc['data']=array('state'=>'1');
                    echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                  }              
              }else{
                $cc['token']=array('state'=>'1');
                echo json_encode($cc, JSON_UNESCAPED_UNICODE);
              }        
     }
     //获取任务令回收站列表
     function Master_getact_recovery($token,$page,$title)
     {
          $aa=Master::check_admintoken($token);
              $cc=array();
              if ($aa) {            
                    $bb=Master::get_recovery_act($page,$title);
                  if ($bb) {
                    $cc['token']=array('state'=>'0');
                    $cc['data']=array('state'=>'0');
                    $cc['response'] = $bb;
                    echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                    }else{
                    $cc['token']=array('state'=>'0');
                    $cc['data']=array('state'=>'1');
                    echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                  }              
              }else{
                $cc['token']=array('state'=>'1');
                echo json_encode($cc, JSON_UNESCAPED_UNICODE);
              }        
     }
   //获取学客圈回收站列表
    function Master_getccl_recovery($token,$page,$uid)
     {
          $aa=Master::check_admintoken($token);
              $cc=array();
              if ($aa) {            
                    $bb=Master::get_recovery_ccl($page,$uid);
                  if ($bb) {
                    $cc['token']=array('state'=>'0');
                    $cc['data']=array('state'=>'0');
                    $cc['response'] = $bb;
                    echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                    }else{
                    $cc['token']=array('state'=>'0');
                    $cc['data']=array('state'=>'1');
                    echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                  }              
              }else{
                $cc['token']=array('state'=>'1');
                echo json_encode($cc, JSON_UNESCAPED_UNICODE);
              }        
     }
   //恢复回收站
     function Master_recover($id,$token,$tabname)
     {
          $aa=Master::check_admintoken($token);
              $cc=array();
              if ($aa) {            
                    $bb=Master::recover_admin($id,$tabname);
                  if ($bb) {
                    $cc['token']=array('state'=>'0');
                    $cc['data']=array('state'=>'0');
                    echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                    }else{
                    $cc['token']=array('state'=>'0');
                    $cc['data']=array('state'=>'1');
                    echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                  }              
              }else{
                $cc['token']=array('state'=>'1');
                echo json_encode($cc, JSON_UNESCAPED_UNICODE);
              }
     }
