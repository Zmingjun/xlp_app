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
    
    
    function check_token($token)
    {
       $redis = new redis();  
       $redis->connect('127.0.0.1', 6379);
       $_SESSION['uid'] = isset($_SESSION['uid'])?$_SESSION['uid']:'';
       
       if($token == $redis->get($_SESSION['uid'].'token')&&empty(!$token))
       {  
          return true; 
       }
       else
       {
          return false;
       } 
    } 
    function custom_function_for_salt(){
            return $salt = '$2y$11$' . substr(md5(uniqid(rand(), true)), 0, 15);
    }     
    function user_register($tel,$pwd,$role,$code,$inviter)
    {   

        $redis = new redis();  
        $redis->connect('127.0.0.1', 6379);
        $_SESSION['phone'] = isset($_SESSION['phone'])?$_SESSION['phone']:'';  
        if($code = $redis->get($_SESSION['phone'].'code')&&empty(!$code))
        {   
            $regtime = time();
            $user = User_info::getuser($tel,$role);
            if($user['role'] != $role)
            {
              $options = [
              'salt' => custom_function_for_salt(), 
              'cost' => 8 
              ];
              $time = time();
              $credential = password_hash($pwd, PASSWORD_DEFAULT,$options);
              $id = User_info::add_user($tel,$credential,$role,$time);
              if($id)
              {
                 if($inviter)
                 {
                    check_agent($inviter,$id);
                 }
                 $result = json_encode(array('status'=>'1'));
              }
              else
              {
                 $result = json_encode(array('status'=>'0')); 
              }
              echo $result;
            }
            else{
              //号码已经注册
              echo json_encode(array('status'=>'2')); 
            }
        }
        else
        {
           echo json_encode(array('status'=>'4'));
        }
 
    }
    //查看代理商是否存在
    function check_agent($inviter,$id)
    {
       $data = User_info::get_dealer($inviter);
       if($data)
       {
          User_info::add_relation($id,$inviter,time());
          $top = User_info::top_agent($inviter);
          $time = time();
          User_info::add_agent_relation($id,$inviter,$top['top_pid'],$time);
       }
       else
       {
          echo json_encode(array('status'=>'6'));exit;//代理商不存在
       } 
    }
    function user_login($tel,$pwd,$role,$registrationid_id)
    {
        $user = User_info::getuser($tel,$role);
        if($user){ 
          $result = User_info::checkpwd($user['id']);
           if(password_verify($pwd,$result['credential'])){ 
              //密码正确
              if($user['registrationid_id']&&($user['registrationid_id'] != $registrationid_id))
              {
                 login_jiguang_push($user['registrationid_id'],$content='请注意您的账号已在其他设备登陆，请重新登陆',$user['id']);
                 
              }
              $_SESSION['uid'] = $user['id'];
              $redis = new redis();  
              $redis->connect('127.0.0.1', 6379);
              $cc['status'] = '1';  
              $cc['data']=$user;
              $cc['token'] = md5($user['id'].time());
              $redis->set($user['id'].'token',$cc['token']);
              User_info::update_registrationid_id($user['id'],$registrationid_id);
              echo json_encode($cc, JSON_UNESCAPED_UNICODE);
           }
           else{
             //密码错误
             echo json_encode(array('status'=>'0')); 
           } 
         
        }
        else{
          //账号不存在
          echo json_encode(array('status'=>'2'));
        }
    }
    function user_editpwd($tel,$pwd,$code,$role)
    {
        $redis = new redis();  
        $redis->connect('127.0.0.1', 6379);
        $_SESSION['phone'] = isset($_SESSION['phone'])?$_SESSION['phone']:'';    
        if($code = $redis->get($_SESSION['phone'].'code')&&empty(!$code))
        {  
           $options = [
            'salt' => custom_function_for_salt(), //write your own code to generate a suitable salt
            'cost' => 8 // the default cost is 10
            ];
            $result = User_info::get_app_user($tel,$role);
            $credential = password_hash($pwd,PASSWORD_DEFAULT,$options);
            $result = User_info::user_editpwd($credential,$result['id']);
            echo $result = $result?json_encode(array('status'=>'1')):json_encode(array('status'=>'0'));               
        }
        else
        {
            //验证码不正确
            echo json_encode(array('status'=>'2')); 
        }
    }
    function edit_img($user_id,$headimgurl,$token)
    {
       $result = check_token($token);
       if($result)
       {
          $result = User_info::edit_img($user_id,$headimgurl);               
          echo $result = $result?json_encode(array('status'=>'1')):json_encode(array('status'=>'0'));
       }
       else
       {
          echo json_encode(array('status'=>'5'));
       } 
    }
    function login_quit($user_id)
    {
       echo json_encode(array('status'=>'1'));    
    }
    function bind_bankcard($token,$user_id,$card_number,$time,$code)
    {
       $result = check_token($token);
       if($result)
       {  
          $result = User_info::bind_bankcard($user_id,$card_number,$time);
          echo $result = $result?json_encode(array('status'=>'1')):json_encode(array('status'=>'0'));                       
       }
       else
       {
          echo json_encode(array('status'=>'5'));
       } 
    }
    function upload_user_data($bareheaded_photo,$bust_shot,$id_card,$address,$name,$user_id)
    {

          $result = User_info::upload_user_data($bareheaded_photo,$bust_shot,$id_card,$address,$name,$user_id);                    
          echo $result = $result?json_encode(array('status'=>'1')):json_encode(array('status'=>'0'));
    }
    function user_withdraw($token,$user_id,$money,$w_id,$code)
    {
       $result = check_token($token);
       if($result)
       {  
          $redis = new redis();  
          $redis->connect('127.0.0.1', 6379);
          $_SESSION['phone'] = isset($_SESSION['phone'])?$_SESSION['phone']:'';   
          if($code == $redis->get($_SESSION['phone'].'code')&&empty(!$code))
          {
            $time = time();
            $data = Goods::get_winmoney($user_id);
            $total = $data['winmoney'] - $money;
            User_info::up_winmoney($user_id,$total);
            User_info::banwinmoney($log='发起提现',$user_id,$credit='-'.$total,$type='',$actid='');
            $result = User_info::user_withdraw($user_id,$money,$w_id,$time,$type='1');                    
            echo $result = $result?json_encode(array('status'=>'1')):json_encode(array('status'=>'0'));
          }
          else
          {
            echo json_encode(array('status'=>'3'));
          }
       }
       else
       {
          echo json_encode(array('status'=>'5'));
       } 
    }
    //代理商提现
    function agent_withdraw($token,$user_id,$money,$w_id,$code)
    {
       $result = check_token($token);
       if($result)
       {  
          $redis = new redis();  
          $redis->connect('127.0.0.1', 6379);
          $_SESSION['phone'] = isset($_SESSION['phone'])?$_SESSION['phone']:'';   
          if($code == $redis->get($_SESSION['phone'].'code')&&empty(!$code))
          {
            $time = time();
            $data = User_info::agent_check_earnings($user_id);
            if($data['upper_limit']>$data['earnings'])
            {
              $total = $data['earnings'] - $money;     
              User_info::up_earnings($user_id,$total);
              User_info::banwinmoney($log='发起提现',$user_id,$credit='-'.$money,$type='',$actid='');
              $result = User_info::user_withdraw($user_id,$money,$w_id,$time,$type='2');                    
              echo $result = $result?json_encode(array('status'=>'1')):json_encode(array('status'=>'0'));
            }
            else
            {  
               //收益大于最高赢利
               echo json_encode(array('status'=>'6'));
            }
            
          }
          else
          {
            echo json_encode(array('status'=>'3'));
          }
       }
       else
       {
          echo json_encode(array('status'=>'5'));
       }    
    }
    function card_list($token,$user_id)
    {
       $result = check_token($token);
       if($result)
       {  
          $cc['data'] = User_info::card_list($user_id)?User_info::card_list($user_id):'';                    
          $winmoney = User_info::get_userinfo($user_id);
          $cc['winmoney'] = $winmoney['winmoney']?$winmoney['winmoney']:0;
          echo $result = $result?json_encode($cc, JSON_UNESCAPED_UNICODE):json_encode(array('status'=>'0'));
       }
       else
       {
          echo json_encode(array('status'=>'5'));
       } 
    }
    function binding_xlp($user_id,$key)
    {
          $res = User_info::get_user_xlp($key);   
          if($res)
          {
            $time = time();
            $result = User_info::binding_xlp($user_id,$key,$time);
            echo json_encode(array('status'=>'1'));  
          }
          else
          {
             echo json_encode(array('status'=>'3'));
          }
    }
    //使用阿里云物流查询接口
    function search_logistics($tracking_number,$logistics_company,$type)
    {
        $host = "http://jisukdcx.market.alicloudapi.com";
        $path = "/express/query";
        $method = "GET";
        $appcode = "44a19c3dda39498db33e838fade6a38c";
        $headers = array();
        array_push($headers, "Authorization:APPCODE " . $appcode);
        $querys = "number=$tracking_number&type=$logistics_company";
        $bodys = "";
        $url = $host . $path . "?" . $querys;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        if (1 == strpos("$".$host, "https://"))
        {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        $result = curl_exec($curl);
        $jsonarr = json_decode($result,true);
        if($jsonarr['result'])
        {
          if($type)
          {
            return $result = $jsonarr['result']['list']?$jsonarr['result']['list']['0']:'';
          }
          else
          {
            echo $result = $jsonarr['result']['list']?json_encode($jsonarr,JSON_UNESCAPED_UNICODE):'';  
          }
        }
        //echo json_encode($jsonarr,JSON_UNESCAPED_UNICODE);
        /*$result = $jsonarr['result'];
        
        if($result['issign'] == 1) 
          echo '已签收'.'<br />';
        else 
          echo '未签收'.'<br />';
        foreach($result['list'] as $val)
        {
            echo $val['time'].' '.$val['status'].'<br />';
        }*/
    }
    function order_detail($token,$order_id)
    {
       $result = check_token($token);
       if($result)
       {  
          $result = Goods::order_detail($order_id);
          $result['status'] = search_logistics($result['tracking_number'],$result['logistics_company'],$type=1);
          echo $result = $result?json_encode($result,JSON_UNESCAPED_UNICODE):json_encode(array('status'=>'0'));  
       }
       else
       {
          echo json_encode(array('status'=>'5'));
       }      
    }
    function order_over($token,$order_id)
    {
       $result = check_token($token);
       if($result)
       {  
          $result = Goods::order_over($order_id);
          echo $result = $result?json_encode(array('status'=>'1')):json_encode(array('status'=>'0'));  
       }
       else
       {
          echo json_encode(array('status'=>'5'));
       } 
    }
    function apply_for_return($token,$order_id)
    {
       $result = check_token($token);
       if($result)
       {  
          $result = Goods::apply_for_return($order_id);
          echo $result = $result?json_encode(array('status'=>'1')):json_encode(array('status'=>'0'));  
       }
       else
       {
          echo json_encode(array('status'=>'5'));
       } 
    }
    function edit_usb_detail($token,$stu_name,$stu_img,$id)
    {
       $result = check_token($token);
       if($result)
       {  
          $result = User_info::edit_usb_detail($stu_name,$stu_img,$id);
          echo $result = $result?json_encode(array('status'=>'1')):json_encode(array('status'=>'0'));  
       }
       else
       {
          echo json_encode(array('status'=>'5'));
       }  
    }
    function show_usb($token,$key)
    {
       $result = check_token($token);
       if($result)
       {  
          $result = User_info::show_usb($key);
          $data = User_info::act_num($key);
          $data['data'] = $result;
          $data['count'] = $data['count']?$data['count']:0;
          echo $result = $result['usb_id']?json_encode($data,JSON_UNESCAPED_UNICODE):json_encode(array('status'=>'0'));  
       }
       else
       {
          echo json_encode(array('status'=>'5'));
       } 
    }
    function show_credit($token,$user_id)
    {
       $result = check_token($token);
       if($result)
       {  
          $result = User_info::show_credit($user_id);
          echo $result = $result?json_encode($result, JSON_UNESCAPED_UNICODE):json_encode(array('status'=>'0'));  
       }
       else
       {
          echo json_encode(array('status'=>'5'));
       }  
    }
    function show_bill($token,$user_id)
    {
       $result = check_token($token);
       if($result)
       {  
          $result = User_info::show_bill($user_id);
          echo $result = $result?json_encode($result, JSON_UNESCAPED_UNICODE):json_encode(array('status'=>'0'));  
       }
       else
       {
          echo json_encode(array('status'=>'5'));
       } 
    }
    function edit_username($token,$user_id,$nickname)
    {
       $result = check_token($token);
       if($result)
       {  
          $result = User_info::edit_username($user_id,$nickname);
          echo $result = $result?json_encode(array('status'=>'1')):json_encode(array('status'=>'0'));  
       }
       else
       {
          echo json_encode(array('status'=>'5'));
       } 
    }
    function show_subject($token)
    {
       $result = check_token($token);
       if($result)
       {  
          $result = User_info::show_subject();
          echo $result = $result?json_encode($result, JSON_UNESCAPED_UNICODE):json_encode(array('status'=>'0'));  
       }
       else
       {
          echo json_encode(array('status'=>'5'));
       } 
    }
    function show_img($token)
    {
       $result = check_token($token);
       if($result)
       {  
          $result = User_info::show_img();
          echo $result = $result?json_encode($result, JSON_UNESCAPED_UNICODE):json_encode(array('status'=>'0'));  
       }
       else
       {
          echo json_encode(array('status'=>'5'));
       }
    }
    function show_activate_member($token,$user_id)
    {
       $result = check_token($token);
       if($result)
       {  
          $data['data'] = User_info::show_activate_member($user_id);
          echo $result = $data['data']?json_encode($data, JSON_UNESCAPED_UNICODE):json_encode(array('status'=>'0'));  
       }
       else
       {
          echo json_encode(array('status'=>'5'));
       }
    }
    function show_unactivate_member($token,$user_id)
    {
       $result = check_token($token);
       if($result)
       {  
          $result =  User_info::stu_member($user_id);
          //$data['count'] = count($result);
          $data['data'] = array();
          if($result)
          {  

            foreach($result as $k=>$v)
            {
               if(!$result[$k]['score']||!$result[$k]['pid'])
               {  
                  $data['data'][$k] = $result[$k];
               }
            }
              
          }
          echo $result = $data['data']?json_encode($data, JSON_UNESCAPED_UNICODE):json_encode(array('status'=>'0'));  
       }
       else
       {
          echo json_encode(array('status'=>'5'));
       }
    }
    //用户投资
    function user_investment($token,$user_id,$money)
    {
       $result = check_token($token);
       if($result)
       {  
         $result = Use_info::check_agent_relation($user_id);
          switch ($money+$result['upper_limit']) {
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
          User_info::add_agent_earnings($user_id,$money,$result['pid'],$result['top_pid'],$upper_limit);
          User_info::banwinmoney($log="二级推荐分红",$result['pid'],$credit=$money*0.12,$type='',$actid='');
          User_info::banwinmoney($log="推荐奖励分红",$result['top_pid'],$credit=$money*0.38,$type='',$actid='');
          echo $result = $result?json_encode(array('status'=>'1')):json_encode(array('status'=>'0'));
       }   
       else
       {
          echo json_encode(array('status'=>'5'));
       }  
    }
    function card_unbundling($token,$id)
    {
       $result = check_token($token);
       if($result)
       {  
          $result = User_info::card_unbundling($id);
          echo $result = $result?json_encode(array('status'=>'1')):json_encode(array('status'=>'0'));
       }   
       else
       {
          echo json_encode(array('status'=>'5'));
       }
    }
    function show_attestation_data($token,$user_id)
    {
       $result = check_token($token);
       if($result)
       {  
          $result = User_info::show_attestation_data($user_id);
          echo $result = $result?json_encode($result,JSON_UNESCAPED_UNICODE):json_encode(array('status'=>'0'));
       }   
       else
       {
          echo json_encode(array('status'=>'5'));
       }
    }
    function show_income($token,$user_id,$form)
    {
       $result = check_token($token);
       if($result)
       {  
          $data['week'] = Act::show_income($user_id,$form)?Act::show_income($user_id,$form):'';
          $data['total'] = Act::show_income_total($user_id,$form)?Act::show_income_total($user_id,$form):'';
          $data['data'] = Act::week_show_income($user_id,$form)?Act::week_show_income($user_id,$form):'';
          echo $result = $data['total']?json_encode($data,JSON_UNESCAPED_UNICODE):json_encode(array('status'=>'0'));
       }   
       else
       {
          echo json_encode(array('status'=>'5'));
       }
    }
    function week_show_income($token,$user_id,$form)
    {
       $result = check_token($token);
       if($result)
       {  
          $result = Act::week_show_income($user_id,$form);
          echo $result = count($result)?json_encode($result,JSON_UNESCAPED_UNICODE):json_encode(array('status'=>'0'));
       }   
       else
       {
          echo json_encode(array('status'=>'5'));
       } 
    }
    function show_income_detail($token,$user_id,$form)
    {
       $result = check_token($token);
       if($result)
       {  
          $result = Act::show_income_detail($user_id,$form);
          echo $result = $result?json_encode($result,JSON_UNESCAPED_UNICODE):json_encode(array('status'=>'0'));
       }   
       else
       {
          echo json_encode(array('status'=>'5'));
       } 
    }
    function check_user_role($tel)
    {

          $result = User_info::check_role($tel);
          echo $result = $result?json_encode($result,JSON_UNESCAPED_UNICODE):json_encode(array('status'=>'0'));
    }
    function center_show_usb($token,$user_id)
    {
       $result = check_token($token);
       if($result)
       {  
          $data['data'] = User_info::center_show_usb($user_id);
          echo $result = $data['data']?json_encode($data,JSON_UNESCAPED_UNICODE):json_encode(array('status'=>'0'));
       }   
       else
       {
          echo json_encode(array('status'=>'5'));
       } 
    }
    function center_user($token,$user_id)
    {
       $result = check_token($token);
       if($result)
       {  
          $data['count_actvate'] = count(User_info::show_activate_member($user_id))?count(User_info::show_activate_member($user_id)):0;
          $data['count_actvate_product'] = count(Act::xlp_list($user_id))?count(Act::xlp_list($user_id)):0;
          $str = User_info::check_winnmoney($user_id);
          $data['winmoney'] = $str['winmoney']?$str['winmoney']:0;
          $arr = User_info::sum_freeze_money($user_id);
          $data['money'] = $arr['money']?$arr['money']:0;
          $result =  User_info::stu_member($user_id);
          $data['count_not_actvate'] = array();
          //$data['count'] = count($result);
          if($result)
          {  

            foreach($result as $k=>$v)
            {
               if(!$result[$k]['score']||!$result[$k]['pid'])
               {  
                  $data['count_not_actvate'][$k] = $result[$k];
               }
            }
              $data['count_not_actvate'] = count($data['count_not_actvate']);
          }
          
          $data['count_not_actvate'] = $data['count_not_actvate']?$data['count_not_actvate']:0;
          if($data['count_actvate']||$data['count_actvate_product']||$data['winmoney']||$data['money']||$data['count_not_actvate'])
          {
             echo json_encode($data,JSON_UNESCAPED_UNICODE);
          }
          else
          {
            echo json_encode(array('status'=>'0'));
          }
       }   
       else
       {
          echo json_encode(array('status'=>'5'));
       }
    }
    function act_add($user_id,$token,$title,$userlimit,$subject,$time,$tel,$address,$detail,$actstarttime,$actimgurl,$latitude,$longitude)
    {
       $result = check_token($token);
       if($result)
       {  
          $result = Act::act_add($user_id,$title,$userlimit,$subject,$time,$tel,$address,$detail,$actstarttime,$actimgurl,$latitude,$longitude);
          echo $result = $result?json_encode(array('status'=>'1')):json_encode(array('status'=>'0'));
       }   
       else
       {
          echo json_encode(array('status'=>'5'));
       } 
    }
    function act_get_info($id)
    {
       $result = check_token($token);
       if($result)
       {  
          $result = Act::act_detail($id);
          echo $result = $result?json_encode(array('status'=>'1')):json_encode(array('status'=>'0'));
       }   
       else
       {
          echo json_encode(array('status'=>'5'));
       } 
    }
    function show_grade_record($actid,$token)
    {
       $result = check_token($token);
       if($result)
       {  
          $result = Act::show_grade_record($actid);
          echo $result = $result?json_encode($result,JSON_UNESCAPED_UNICODE):json_encode(array('status'=>'0'));
       }   
       else
       {
          echo json_encode(array('status'=>'5'));
       } 
    }
    function user_detail($token,$user_id,$un_xlp)
    {
       
       if(!$un_xlp)
      {   
         $result = check_token($token);
         if($result)
         {  
            $data['status'] = 1;
            $data['data'] = User_info::user_detail($user_id);
            echo $result = $data?json_encode($data,JSON_UNESCAPED_UNICODE):json_encode(array('status'=>'0'));
         }   
         else
         {
            echo json_encode(array('status'=>'5'));
         }
      }
      else
      {
          $data['status'] = 1;
          $data['data'] = User_info::user_detail($user_id);
          echo $result = $data?json_encode($data,JSON_UNESCAPED_UNICODE):json_encode(array('status'=>'0'));
      } 
    }
    function act_join($token,$act_id,$user_id)
    {
       $result = check_token($token);
       if($result)
       {  
          $time = time();
          $result = Act::act_join($user_id,$act_id,$time);
          echo $result = $result?json_encode(array('status'=>'1')):json_encode(array('status'=>'0'));
       }   
       else
       {
          echo json_encode(array('status'=>'5'));
       } 
    }
    //代理商下级具体收益
    function detail_earnings($token,$user_id)
    {
       $result = check_token($token);
       if($result)
       {  
          $data = User_info::detail_earnings($user_id);
           if($data)
          {
            $result = array();
            $result['code'] = 1;
            $result['data'] = $data;
            $result['count'] = count($data);
            $result['msg'] = '';
            echo json_encode($result,JSON_UNESCAPED_UNICODE);
          }
          else
          { 
            $result = array();
            $result['code'] = 0;
            $result['code'] = '';
            $result['data'] = [];
            $result['count'] = '';
            $result['msg'] = '暂无数据';
            echo json_encode($result,JSON_UNESCAPED_UNICODE);
          }
       }   
       else
       {
          echo json_encode(array('status'=>'5'));
       } 
    }
    function week_earnings($token,$user_id)
    {
       $result = check_token($token);
       if($result)
       {  
          $result = User_info::week_earnings($user_id)?User_info::week_earnings($user_id):'';
          echo $result = $result?json_encode($result,JSON_UNESCAPED_UNICODE):json_encode(array('status'=>'0'));
       }   
       else
       {
          echo json_encode(array('status'=>'5'));
       } 
    }
    function act_detail($token,$id)
    {
       $result = check_token($token);
       if($result)
       {  
          
          $result = Act::act_detail($id);
          echo $result = $result?json_encode($result,JSON_UNESCAPED_UNICODE):json_encode(array('status'=>'0'));
       }   
       else
       {
          echo json_encode(array('status'=>'5'));
       } 
    }
    function check_isjoin($token,$user_id,$act_id)
    {
       $result = check_token($token);
       if($result)
       {  
          $time = time();
          $result = Act::check_isjoin($token,$user_id,$act_id);
          echo $result = $result?json_encode(array('status'=>'1')):json_encode(array('status'=>'0'));
       }   
       else
       {
          echo json_encode(array('status'=>'5'));
       }
    }
    function  tea_marking($token,$key,$score,$act_id,$time,$user_id)
    {
       $result = check_token($token);
       if($result)
       {  
          $today_time = date('Y-m-d',$time);
          $winmoney = Act::check_key_winmoney($key);
          $res = Act::count_act_score($key,$act_id,$time);
          $last_time = $res['time']?date('Y-m-d',$res['time']):'';
          //个人总分不能超过750 
          if($winmoney < 750)
          {
             //判断是否同一天
             if($today_time == $last_time)
             {
               if($res['score']+$score<=100)
               {
                  $result = Act::tea_marking($key,$score,$act_id,$time,$user_id);
                  echo $result = $result?json_encode(array('status'=>'1')):json_encode(array('status'=>'0'));
               }
               else
               {  //用户一天只能接受100分上限
                  echo json_encode(array('status'=>'4'));
               }
             }
             else
             {
                  $result = Act::tea_marking($key,$score,$act_id,$time,$user_id);
                  echo $result = $result?json_encode(array('status'=>'1')):json_encode(array('status'=>'0')); 
             }
          } 
          else
          {
            echo json_encode(array('status'=>'3'));
          } 
       }   
       else
       {
          echo json_encode(array('status'=>'5'));
       } 
    }
    function xlp_list($token,$user_id)
    {
       $result = check_token($token);
       if($result)
       {  
            $result = Act::xlp_list($user_id);
            $data['data'] = Act::xlp_list($user_id);
            if($data['data'])
            {
                foreach ($data['data'] as $k => $v) {
                  $data['data'][$k]['status'] = empty($data['data'][$k]['status'])?'':$data['data'][$k]['status']; 
                }
            }
            $data['count'] =count($result); 
            echo $result = $result?json_encode($data,JSON_UNESCAPED_UNICODE):json_encode(array('status'=>'0')); 
       }   
       else
       {
          echo json_encode(array('status'=>'5'));
       } 
    }
    function application_return($token,$user_id,$content,$key,$name,$tel)
    {
       $result = check_token($token);
       if($result)
       {    
            $time = time();
            $result = Act::application_return($user_id,$content,$key,$name,$tel,$time);
            echo $result = $result?json_encode(array('status'=>'1')):json_encode(array('status'=>'0')); 
       }   
       else
       {
          echo json_encode(array('status'=>'5'));
       } 
    }
    function start_act($token,$id,$actsta,$user_id)
    {
       $result = check_token($token);
       if($result)
       {    
            $result = Act::start_act($id,$actsta);
            echo $result = $result?json_encode(array('status'=>'1')):json_encode(array('status'=>'0')); 
       }   
       else
       {
          echo json_encode(array('status'=>'5'));
       } 
    }
    function act_list($token,$actsta,$user_id)
    {
       $result = check_token($token);
       if($result)
       {    
            $result = Act::act_list($user_id,$actsta);
            echo $result = $result?json_encode($result,JSON_UNESCAPED_UNICODE):json_encode(array('status'=>'0')); 
       }   
       else
       {
          echo json_encode(array('status'=>'5'));
       } 
    }
    function search_ccl_user($token,$name,$user_id,$role)
    {
       $result = check_token($token);
       if($result)
       {    
            $data['act'] = Act::search_act($name,$user_id,$role)?Act::search_act($name,$user_id,$role):'';
            $data['user'] = User_info::search_user($name)?User_info::search_user($name):'';
            echo $result = $data?json_encode($data,JSON_UNESCAPED_UNICODE):json_encode(array('status'=>'0')); 
       }   
       else
       {
          echo json_encode(array('status'=>'5'));
       } 
    }
    function get_user_act($user_id,$token,$actsta)
    {
       $result = check_token($token);
       if($result)
       {    
            $data = Act::get_user_act($user_id,$actsta);
            echo $result = $data?json_encode($data,JSON_UNESCAPED_UNICODE):json_encode(array('status'=>'0')); 
       }   
       else
       {
          echo json_encode(array('status'=>'5'));
       } 
    }
    function update_act_display($id,$token)
    {
       $result = check_token($token);
       if($result)
       {    
            //  if($id)
            // {
            //   $id = str_replace("\\","",$id);
            //   $id = json_decode($id);
            //   $id = implode(',',$id);
            // } 
            $result = Act::update_actjoin($id);
            echo $result = $result?json_encode(array('status'=>'1')):json_encode(array('status'=>'0')); 
       }   
       else
       {
          echo json_encode(array('status'=>'5'));
       }   
    }
    function perfect_usb($token,$stu_img,$stu_name,$log,$key)
    {
       $result = check_token($token);
       if($result)
       {    
            $data = Act::check_key($key);
            if($data)
            {
              $time = time();
              $result = Act::perfect_usb($stu_img,$stu_name,$log,$data['id']);
              echo $result = $result?json_encode(array('status'=>'1')):json_encode(array('status'=>'0')); 
            }
            else
            {  
               //该手环不存在
               echo json_encode(array('status'=>'3'));
            }
       }   
       else
       {
          echo json_encode(array('status'=>'5'));
       } 
    }
    function check_usb_status($token,$key)
    {
       $result = check_token($token);
       if($result)
       {    
            $data = Act::check_key($key);
            if($data)
            {
              $result = Act::check_usb_status($key);
              echo $result = $result['stu_img']?json_encode($result,JSON_UNESCAPED_UNICODE):json_encode(array('status'=>'0')); 
            }
            else
            {  
               //该手环不存在
               echo json_encode(array('status'=>'3'));
            }
       }   
       else
       {
          echo json_encode(array('status'=>'5'));
       }
    }
    function del_act($token,$id)
    {
       $result = check_token($token);
       if($result)
       {   
          $result = Act::del_act($id);
          echo $result = $result?json_encode(array('status'=>'1')):json_encode(array('status'=>'0')); 
       }   
       else
       {
          echo json_encode(array('status'=>'5'));
       }
    }
    function check_subordinate($token,$user_id)
    {
       $result = check_token($token);
       if($result)
       {   
          $data = User_info::check_subordinate($user_id)?User_info::check_subordinate($user_id):'';
          if($data)
          { 
              foreach($data as $k=>$v)
              {
                  switch ($data[$k]['role'])
                 {
                    case '1':
                      $data[$k]['role'] = '学生家长';
                      break;
                    case '2':
                      $data[$k]['role'] = '助教';
                       break;
                    case '3':
                       $data[$k]['role'] = '代理商';
                       break;
                    default:
                      break;
                  }
              }
            $result = array();
            $result['code'] = 1;
            $result['data'] = $data;
            $result['count'] = count($data);
            $result['msg'] = '';
            echo json_encode($result,JSON_UNESCAPED_UNICODE);
          }
          else
          {
            $result = array();
            $result['code'] = 0;
            $result['code'] = '';
            $result['data'] = [];
            $result['count'] = '';
            $result['msg'] = '暂无数据';
            echo json_encode($result,JSON_UNESCAPED_UNICODE);
          }
           
       }   
       else
       {
          echo json_encode(array('status'=>'5'));
       }
    }
    function dayily_earnings($token,$user_id)
    {
       $result = check_token($token);
       if($result)
       {   
          $data = User_info::dayily_earnings($user_id)?User_info::dayily_earnings($user_id):'';
          if($data)
          { 
            $result = array();
            $result['code'] = 1;
            $result['data'] = $data;
            $result['count'] = count($data);
            $result['msg'] = '';
            echo json_encode($result,JSON_UNESCAPED_UNICODE);
          }
          else
          {
            $result = array();
            $result['code'] = 0;
            $result['code'] = '';
            $result['data'] = '';
            $result['count'] = '';
            $result['msg'] = '暂无数据';
            echo json_encode($result,JSON_UNESCAPED_UNICODE);
          }
           
       }   
       else
       {
          echo json_encode(array('status'=>'5'));
       }
    }
    function receive_winmoney($token,$key,$user_id)
    {
       $result = check_token($token);
       if($result)
       {  
          $data = Act::check_key($key); 
          if(!$data['pid'])
          {   
             $result = Act::check_user_usb($user_id);
             if(!$result)
             {  
                $time = time();
                Act::binding_usb($key,$user_id,$time);
             }
             else
             { 
               //用户之前有绑定过手环
               echo json_encode(array('status'=>'6'));exit; 
             }
          }
          else
          {
             if($data['pid'] != $user_id)
             {
                //不是手环持有者
                echo json_encode(array('status'=>'4'));exit; 
             }
          }
          $winmoney = Act::act_winmoney($key);
          if(time() < $winmoney['time']+24*3600*2)
          { 
            $tea = Act::get_tea($key);
            $total = 100;
            foreach($tea as $k => $v)
            {
               User_info::up_uwinmoney($tea[$k]['follower_id'],$total);
               User_info::activate_relation($key,$tea[$k]['follower_id']);
               User_info::banwinmoney($log='学员收益',$tea[$k]['follower_id'],$credit = '+'.$total,$type = 1,$actid = $winmoney['actid']); 
               $total = $total*0.8;
            }    
            //家长领取学分,老师收益
            Act::update_actscore_status($winmoney['actid'],$key);
            User_info::up_uwinmoney($user_id,$winmoney['score']);
            User_info::up_uwinmoney($winmoney['userid'],$total=50);
            User_info::banwinmoney($log='活动参与奖',$winmoney['userid'],$credit = '+50',$type = 2,$actid = $winmoney['actid']); 
            User_info::banwinmoney($log='扫码领取',$user_id,$credit = '+'.$winmoney['score'],$type = '',$actid); 
            echo json_encode(array('status'=>'1'));
          }
          else
          {  //超时领取
             echo json_encode(array('status'=>'2'));
          } 
       }   
       else
       {
          echo json_encode(array('status'=>'5'));
       }
    }
    function member_ranking($token,$actid)
    {
       $result = check_token($token);
       if($result)
       {   
          $result = Act::member_ranking($actid);
          echo $result = $result?json_encode($result,JSON_UNESCAPED_UNICODE):json_encode(array('status'=>'0')); 
       }   
       else
       {
          echo json_encode(array('status'=>'5'));
       }
    }
    function agent_subordinate($token,$user_id)
    {
       $result = check_token($token);
       if($result)
       {   
          $result = User_info::agent_subordinate($user_id);
          echo $result = $result?json_encode($result,JSON_UNESCAPED_UNICODE):json_encode(array('status'=>'0')); 
       }   
       else
       {
          echo json_encode(array('status'=>'5'));
       }
    }
    function agent_detail($token,$user_id)
    {
       $result = check_token($token);
       if($result)
       {   
          $result = User_info::agent_detail($user_id);
          $result['count'] = count(User_info::agent_subordinate($user_id))?count(User_info::agent_subordinate($user_id)):0;
          echo $result = $result?json_encode($result,JSON_UNESCAPED_UNICODE):json_encode(array('status'=>'0')); 
       }   
       else
       {
          echo json_encode(array('status'=>'5'));
       }
    }
    //按月份搜索收益
    function earnings_search($token,$month,$user_id)
   {
       $result = check_token($token);
       if($result)
       {   
          $data = User_info::month_earnings($month,$user_id)?User_info::month_earnings($month,$user_id):'';
          if($data)
          { 
            $result = array();
            $result['code'] = 1;
            $result['data'] = $data;
            $result['count'] = count($data);
            $result['msg'] = '';
            echo json_encode($result,JSON_UNESCAPED_UNICODE);
          }
          else
          {
            $result = array();
            $result['code'] = 0;
            $result['code'] = '';
            $result['data'] = '';
            $result['count'] = '';
            $result['msg'] = '暂无数据';
            echo json_encode($result,JSON_UNESCAPED_UNICODE);
          }
           
       }   
       else
       {
          echo json_encode(array('status'=>'5'));
       }
   }
    function upload_actimg($token,$actid,$img,$img_type)
    {
       $result = check_token($token);
       if($result)
       {   
          $result = Act::upload_actimg($actid,$img,$img_type);
          echo $result = $result?json_encode(array('status'=>'1')):json_encode(array('status'=>'0')); 
       }   
       else
       {
          echo json_encode(array('status'=>'5'));
       }
    }
    function check_user_join($token,$user_id,$actid)
    {
       $result = check_token($token);
       if($result)
       {   
          $result = Act::check_user_join($user_id,$actid);
          echo $result = $result?json_encode(array('status'=>'1')):json_encode(array('status'=>'0')); 
       }   
       else
       {
          echo json_encode(array('status'=>'5'));
       }
    }
    function show_act_info($token,$key)
    {
       $result = check_token($token);
       if($result)
       {   
          $data = User_info::show_usb($key);
          if($data)
          {
            $result = Act::show_act_info($key);
            echo $result = $result['score']?json_encode($result,JSON_UNESCAPED_UNICODE):json_encode(array('status'=>'0')); 
          }
          else
          {
             echo json_encode(array('status'=>'3'));
          }
       }   
       else
       {
          echo json_encode(array('status'=>'5'));
       }
    }
     function upload_img($accessKeyId,$accessKeySecret,$endpoint,$bucket){   
              
        $id= $accessKeyId;
        $key= $accessKeySecret;;
        $host = 'https://123win.oss-cn-shenzhen.aliyuncs.com'; //bucket+endpoint
        //$callbackUrl = "https://oss-demo.aliyuncs.com:23450";
        $callback_param = array( 
                     'callbackBody'=>'filename=${object}&size=${size}&mimeType=${mimeType}&height=${imageInfo.height}&width=${imageInfo.width}', 
                     'callbackBodyType'=>"application/json");
        $callback_string = json_encode($callback_param);

        $base64_callback_body = base64_encode($callback_string);
        $now = time();
        $expire = 3000; //设置该policy超时时间是10s. 即这个policy过了这个有效时间，将不能访问
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
    function user_news($token,$user_id)
    {
       $result = check_token($token);
       $data['rew'] = array();
       if($result)
       {   
            $result = App::user_news($user_id);
            if($result)
            {
                foreach($result as $k => $v)
                { 
                  if($result[$k]['uid'] == $user_id)
                  {
                      if($result[$k]['replier'] != '')
                      {
                          array_push($data['rew'],App::user_rew_news($result[$k]['id']));                        
                      }            
                   }
                   else 
                   {  
                      if(!$result[$k]['replier'])
                      {
                         array_push($data['rew'],App::user_content_news($result[$k]['id']));
                      }
                   }


                }
            }
            else
            {
              $data['rew']='';
            }
            $data['rew'] = empty($data['rew'])?'':$data['rew'];
            $data['like'] = App::ccl_all_like($user_id)?App::ccl_all_like($user_id):'';

          echo $result = $data?json_encode($data,JSON_UNESCAPED_UNICODE):json_encode(array('status'=>'0')); 
       }   
       else
       {
          echo json_encode(array('status'=>'5'));
       }
    }
    function empty_news($token,$id,$user_id)
    {
       $result = check_token($token);
       if($result)
       {   
          //清空点赞
          App::empty_ccl_like($user_id); 
          App::empty_ccl_news($id);
          echo json_encode(array('status'=>'1'));
       }   
       else
       {
          echo json_encode(array('status'=>'5'));
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
    function cll_content($id,$user_id)
    {

     $list=App::cll_content($id,$user_id);
     $rew=App::cll_rew($id);
       $count=App::cll_count($list['id']);
       $is_like = App::like_why($user_id,$id);
       $list['is_like'] = $is_like['status']?$is_like['status']:'';
       $list['like']=$count['wrzm'];
       $list['rew']=$count['mysh'];
       $list['smit']=$count['sgbh'];
       if($list['thumb'])
       {
        $url=explode(',',$list['thumb']);
        $img=array();
        foreach ($url as $kk => $vv) {
         if($vv)
         {
          $img[$kk]['url']=$vv;
         }
       }
       $list['thumb']=$img;
       $con=count($url);
       }
       $list['rewlist'] = App::cll_rew($id)?App::cll_rew($id):'';
       $data = App::like_member($id);
       foreach ($data as $i => $j) {
                 $data[$i] =  $data[$i]['nickname'];
        }
       $str = $data?implode(",",$data):'';
       $list['like_member'] = $str;

       
       if ($list['thumb']=='') {
         $list['type']=1;
       }else if($con==1){
        $list['type']=2;
      }else if($con>1){
        $list['type']=3;
      }
     $get=array();
     $get['data']=$list;
     echo json_encode($get, JSON_UNESCAPED_UNICODE);
     //var_dump($list);//var_dump($count);

  }
    function del_ccl($token,$id)
    {   
       $result = check_token($token);
       if($result)
       {
         $cll= App::del_ccl($id);
         echo $cll = $cll?json_encode(array('status'=>'1')):json_encode(array('status'=>'0'));
       }
       else
       {
           echo json_encode(array('status'=>'5'));
       }
    }
    function user_ccl($token,$user_id)
    {
       $result = check_token($token);
       if($result)
       { 
         $data['list'] = array();
         $data['list']=App::user_ccl($user_id)?App::user_ccl($user_id):'';
         if($data['list'])
         {
           foreach($data['list'] as $k => $v)
           {
              $data['list'][$k]['thumb'] = explode(',',$data['list'][$k]['thumb']);
           }
         }
         $result = User_info::get_userinfo($user_id);
         $data['nickname'] = $result['nickname'];
         $data['headimgurl'] = $result['headimgurl'];
         $data['count'] = empty($data['list'])?'0':count($data['list']);
         echo $cll = $data?json_encode($data, JSON_UNESCAPED_UNICODE):json_encode(array('status'=>'0'));
       }
       else
       {
           echo json_encode(array('status'=>'5'));
       }  
    }
    function cll_put_list($token,$content,$thumb,$user_id)
    {
        
       $result = check_token($token);
       if($result)
       {
         $time=time();
         $cll=App::cll_put_list($user_id,$content,$thumb,$time);
         echo $cll = $cll?json_encode(array('status'=>'1')):json_encode(array('status'=>'0'));
         // $total = App::get_log_winmoney('发表学客圈');
         // $total = $total['winmoney'];
         // User_info::up_uwinmoney($userid,$total);
         // $total = '+'.$total;
         // $actid ='';
         // App::banwinmoney('发表学客圈',$userid,$total,$actid);
       }
       else
       {
           echo json_encode(array('status'=>'5'));
       }
      
    }
        //10046
        function cll_put_rew($user_id,$content,$id,$token)
        {   
            $result = check_token($token);
            if($result)
            {
              $time=time();
              $cll=App::cll_put_rew($user_id,$content,$id,$time);
              $data['data'] = App::cll_rew($id);
              $data['count'] = count($data['data']);
              $res = User_info::get_user($id);
              $res = User_info::get_ccl_registrationid_id($id);
              if($res['id'] != $user_id)
              {
                jiguang_push($res['registrationid_id'],$content='您收到一个评论，请点击查看');
              }
              echo $cll = $cll?json_encode($data, JSON_UNESCAPED_UNICODE):json_encode(array('status'=>'0')); 
            }
            else
            {
               echo json_encode(array('status'=>'5')); 
            }
        }
        function ccl_push_rew($user_id,$token,$content,$id,$replier)
        {
            $result = check_token($token);
            if($result)
            {
              
              $time=time();
              $cll=App::cll_push_rew($user_id,$content,$id,$time,$replier);
              $data['data'] = App::cll_rew($id);
              $data['count'] = count($data['data']);
              if($replier != $user_id)
              {
                $res = User_info::get_user($user_id);
                jiguang_push($res['registrationid_id'],$content='您收到一个回复，请点击查看');  
              }
              echo $cll = $cll?json_encode($data, JSON_UNESCAPED_UNICODE):json_encode(array('status'=>'0')); 
            }
            else
            {
               echo json_encode(array('status'=>'5')); 
            } 
        }
        function del_ccl_rew($rew_id,$token,$id)
        {
            $result = check_token($token);
            if($result)
            {
              $cll=App::del_ccl_rew($rew_id);
              $data['data'] = App::cll_rew($id)?App::cll_rew($id):'';
              $data['count'] = empty($data['data'])?'0':count($data['data']);  
              echo $cll = $cll?json_encode($data, JSON_UNESCAPED_UNICODE):json_encode(array('status'=>'0')); 
            }
            else
            {
               echo json_encode(array('status'=>'5')); 
            }    
        }
        //获取各操作的学分
        function get_log_winmoney($log_name)
        {
           $cc = App::get_log_winmoney();
        }
        //10047
        function cll_put_like($user_id,$id,$token)
        {

          $result = check_token($token);
          if($result)
          {   
              $time=time();
              $userid=User_info::get_user($user_id);
              if ($userid) {
                $like=App::like_why($user_id,$id);
              if ($like) {
                switch ($like['status']) {
                  case '0':
                    $status=1;
                    $cll=App::cll_up_like($user_id,$id,$status);
                    $data = App::like_member($id);
                    $cc['count'] = count($data);
                    foreach ($data as $i => $j) {
                     $data[$i] =  $data[$i]['nickname'];
                    }
                    $str = $data?implode(",",$data):'';
                    $cc['like_member']=$str;
                    $cc['status'] = 1;
                    $res = User_info::get_ccl_registrationid_id($id);
                    if($res['id'] != $user_id)
                    {
                     jiguang_push($res['registrationid_id'],$content='您收到一个点赞，请点击查看');
                    }
                    echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                    break;
                  case '1':
                      $status=0;
                      $cll=App::cll_up_like($user_id,$id,$status);
                      $data = App::like_member($id);
                      $cc['count'] = count($data);
                      foreach ($data as $i => $j) {
                        $data[$i] =  $data[$i]['nickname'];
                      }
                      $str = $data?implode(",",$data):'';
                      $cc['like_member']=$str;
                      $cc['status'] = 0;
                      echo json_encode($cc, JSON_UNESCAPED_UNICODE);
                    break;
                }
                
              }else{
                $cll=App::cll_put_like($user_id,$id,$time);
                $data = App::like_member($id);
                $cc['count'] = count($data);
                foreach ($data as $i => $j) {
                     $data[$i] =  $data[$i]['nickname'];
                }
                 $str = $data?implode(",",$data):'';
                 $cc['like_member']=$str;
                 $cc['status'] = 1;
                 $res = User_info::get_ccl_registrationid_id($id);
                 if($res['id'] != $user_id)
                 {
                    jiguang_push($res['registrationid_id'],$content='您收到一个点赞，请点击查看');
                 }
                 echo json_encode($cc, JSON_UNESCAPED_UNICODE);
            }
          }
        }
        else
        {
          echo json_encode(array('status'=>'5'));
        }
      }
      function system_news($token,$user_id)
      {
          $result = check_token($token);
          if($result)
          {
            $data=App::system_news($user_id);  
            echo $data = $data?json_encode($data,JSON_UNESCAPED_UNICODE):json_encode(array('status'=>'0')); 
          }
          else
          {
             echo json_encode(array('status'=>'5')); 
          } 
      }
      function weixin_pay($token,$user_id,$order_sn,$goods_name,$order_total)
      { 
          $result = check_token($token);
          if($result)
          {
             $input = new WxPayUnifiedOrder();
             $input->SetBody($goods_name);//商品描述
             $input->SetAttach($goods_name);//置附加数据 
             $input->SetOut_trade_no($order_sn); // 商户订单号
             $input->SetTotal_fee(intval($order_total*100)); 
             $input->SetTime_start(date("YmdHis"));//订单生成时间
             $input->SetTime_expire(date("YmdHis", time() + 60));//订单失效时间
             $input->SetGoods_tag($goods_name); //商品标记  
             $input->SetNotify_url("https://wechat.123win.com.cn/xlp_app/common/action_class.php");// 支付成功后的回调地址,
             $input->SetTrade_type("APP");
             $order = WxPayApi::unifiedOrder($input);
             //var_dump($order);exit;
             $info= weixin_signature($order['prepay_id']);
             echo json_encode($info, JSON_UNESCAPED_UNICODE);
          }
          else
          {
             echo json_encode(array('status'=>'5')); 
          } 
      }
      function  weixin_signature($prepay_id)
      {
          new WxPayDataBase();
          $info = array();
          //账号的信息一般都放在配置文件里面,用到的地方也很多
          $info['appid'] = WxPayConfig::APPID;
          $info['partnerid'] = WxPayConfig::MCHID;
          $info['package'] = 'Sign=WXPay'; 
          $info['noncestr'] = getNonceStr($length = 32);//生成随机数,下面有生成实例,统一下单接口需要
          $info['timestamp'] = time();
          $info['prepayid'] = $prepay_id;
          //var_dump($info);exit;
          $info['sign'] = MakeSign($info);//生成签名
          return $info;
      }
      function getNonceStr($length = 32) 
      {
         $chars = "abcdefghijklmnopqrstuvwxyz0123456789";  
         $str ="";
         for ( $i = 0; $i < $length; $i++ )  {  
            $str .= substr($chars, mt_rand(0, strlen($chars)-1), 1);  
         } 
         return $str;
      }
      function MakeSign($info)
     {
        //签名步骤一：按字典序排序参数
        ksort($info);
        $string = ToUrlParams($info);
        //签名步骤二：在string后加入KEY
        $string = $string . "&key=".WxPayConfig::KEY;
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);
        return $result;
    }
    function ToUrlParams($info)
    {
         $buff = "";
         foreach ($info as $k => $v)
         {
           if($k != "sign" && $v != "" && !is_array($v)){
            $buff .= $k . "=" . $v . "&";
           }
         }
        
         $buff = trim($buff, "&");
         return $buff;
   }
   //极光推送
   function jiguang_push($registrationid_arr,$content)
   {  
      if($registrationid_arr)
      {
        $push = new Jgpush();
        $registrationid_arr = array($registrationid_arr);  
        $push->registrationid_push($registrationid_arr,$content);
      } 
   }
   //极光推送(别名推送)
   function login_jiguang_push($registrationid_arr,$content,$user_id)
   {         
      $push = new Jgpush();
      $data = $push->getDevices($registrationid_arr);
     
      if($data['body']['alias'])
      {
         $alias=array($data['body']['alias']);
      }
      else
      {
         $push->updateAlias($registrationid_arr,$user_id);
         $alias = array($user_id); 
      }
      $res = $push->push_a($alias,$content);  
     
   }
   function print_code()
   {
      $res = User_info::print_code();
      foreach ($res as $k => $v) {
        file_put_contents("log.txt",$res[$k]['key'].'.xly1.cn'.PHP_EOL, FILE_APPEND);
      }
   }
   // // //订单查询
   function update_order_status($token,$order_sn)
   {
        
      $result = check_token($token);
      if($result)
      {
        $result=Goods::update_order_status($order_sn);   
        echo $result = $result?json_encode(array('status'=>'1')):json_encode(array('status'=>'0'));
      }
      else
      {
         echo json_encode(array('status'=>'5')); 
      } 
   }
   //商城首页广告
   function get_adhome($ad_type)
   {
     
      $result = Act::get_adhome($ad_type);
      echo json_encode($result,JSON_UNESCAPED_UNICODE);
    }
   function  empty_system_news($token,$id)
   {
      $result = check_token($token);
      if($result)
      {
        $result=User_info::empty_system_news($id);   
        echo $result = $result?json_encode(array('status'=>'1')):json_encode(array('status'=>'0'));
      }
      else
      {
         echo json_encode(array('status'=>'5')); 
      } 
   }
   function cancel_order($token,$order_sn)
   {
      $result = check_token($token);
      if($result)
      {
        $result=Goods::cancel_order($order_sn);   
        echo $result = $result?json_encode(array('status'=>'1')):json_encode(array('status'=>'0'));
      }
      else
      {
         echo json_encode(array('status'=>'5')); 
      } 
   }

        























































































































































































































































































































































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
        // function del_act($actid){
        //   $cat=Act::del_act($actid);
        //   if($cat){
        //     //放到回收站成功
        //     echo json_encode(array('states'=>'1'));
        //   }else{
        //     //放到回收站失败
        //     echo json_encode(array('states'=>'0'));
        //   }  
        // }
        // //10017
        // function get_recycle()
        // {
        //  $act=Act::get_recycle();
        //   echo json_encode($act, JSON_UNESCAPED_UNICODE);
        // }
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
       
        //添加收货地址
        function add_address($user_id,$consignee,$province,$city,$district,$address,$zipcode,$tel,$default,$token)
        {        
          
          $result = check_token($token);
          if($result)
          {       
            $bb = Goods::add_address($user_id,$consignee,$province,$city,$district,$address,$zipcode,$tel,$default);
            if($bb==1){
                  echo json_encode(array('status'=>'1'));
                }else{
                  echo json_encode(array('status'=>'0'));
                }         
          }
          else
          {
             echo json_encode(array('status'=>'5'));
          }
        }
        //编辑收货地址
        function edit_address($address_id,$user_id,$consignee,$province,$city,$district,$address,$zipcode,$tel,$default,$token)
        { 
          $result = check_token($token);       
          if($result)
          {
            $bb = Goods::edit_address($address_id,$user_id,$consignee,$province,$city,$district,$address,$zipcode,$tel,$default);
            if($bb==1){
                echo json_encode(array('status'=>'1'));
              }else{
                echo json_encode(array('status'=>'0'));
              }
          }
          else
          {
             echo json_encode(array('status'=>'5'));
          }         
        }
        //删除收货地址
        function del_address($id,$token)
        {        
          $result = check_token($token);
          if($result)
          {
            $bb = Goods::del_address($id);
            if($bb==1){
                echo json_encode(array('status'=>'1'));
              }else{
                echo json_encode(array('status'=>'0'));
              }
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
        function get_address($user_id,$is_default,$token)
        {
          $result = check_token($token);
          if($result)
          {
            $type=Goods::get_address($user_id,$is_default);
            echo $result = $type?json_encode($type,JSON_UNESCAPED_UNICODE):json_encode(array('status'=>'0'));   
          }
          else
          {
             echo json_encode(array('status'=>'5'));
          }
        }
        //更新默认地址
        function update_address($address_id,$user_id,$token)
        { 
          $result = check_token($token);
          if($result)
          {
           $type=Goods::update_address($address_id,$user_id);
           echo $result = $type?json_encode(array('status'=>'1')):json_encode(array('status'=>'0'));    
          }
          else
          {
             echo json_encode(array('status'=>'5'));
          }
        }
        //获取个人积分
        function get_winmoney($user_id,$token)
        {
          $result = check_token($token);
          if($result)
          { 
            $type=Goods::get_winmoney($user_id);
            echo json_encode($type,JSON_UNESCAPED_UNICODE);   
          }
          else
          {
             echo json_encode(array('status'=>'5'));
          }
        }
        //生成唯一订单号
        function build_order_no()
        {
         $order_sn =  date('YmdHis') . str_pad(mt_rand(1, 999999), 2, '0', STR_PAD_LEFT);
         return $order_sn;
        }
       
       //插入订单
       function add_orders($user_id,$order_status,$shipping_status,$pay_status,$consignee,$tel,$province,$city,$district,$address,$goods_amount,$add_time,$zipcode,$goods_id,$goods_name,$goods_number,$send_num,$is_real,$goods_price,$token,$pay_type)
       {        
              $result = check_token($token);
              if($result)
              {
                $order_sn = build_order_no();
                $goods_amount = $pay_type?$goods_amount:$goods_amount*5;
                $bb=Goods::add_orders($order_sn,$user_id,$order_status,$shipping_status,$pay_status,$consignee,$tel,$province,$city,$district,$address,$goods_amount,$add_time,$zipcode,$goods_id,$goods_name,$goods_number,$send_num,$is_real,$goods_price);
                if($bb==1){
                  $data['order_sn'] = $order_sn;
                  $data['status'] = '1';
                  echo json_encode($data,JSON_UNESCAPED_UNICODE);
                }else{
                  echo json_encode(array('status'=>'0'));
                }
              }
              else
              {
                 echo json_encode(array('status'=>'5'));
              }         

       }
        //获取用户订单
        function get_user_orders($user_id,$token)
        {

            $result = check_token($token);
            if($result)
            {
               $bb = Goods::get_orders($user_id);
               if($bb)
               {
                   foreach ($bb as $k => $v) {
                      $bb[$k]['status'] = search_logistics($bb[$k]['tracking_number'],$bb[$k]['logistics_company'],$type=1);
                   }
               }
               echo $result = $bb?json_encode($bb,JSON_UNESCAPED_UNICODE):json_encode(array('status'=>'0'));;
            }
            else
            {
               echo json_encode(array('status'=>'5'));
            }
         }
        
        //更新订单状态
        function update_order($user_id,$goods_name,$total,$winmoney,$pay_mode,$token)
        { 
          $result = check_token($token);
          if($result)
          {
            $arr = Goods::get_winmoney($user_id); 
            $winmoney = $arr['winmoney'];
            $winmoney = $winmoney - $total; 
            $log = '购买'.$goods_name;
            $credit = '-'.$total;
            //学分充足的时候才可以进行下一步 
           
           
            if($arr['winmoney'] - $total >= 0 )
            {
                 $type=Goods::update_order($user_id,$goods_name,$total,$winmoney,$pay_mode);
                 User_info::up_winmoney($user_id,$winmoney);
                 User_info::banwinmoney($log,$user_id,$credit,$type='',$actid='');
                 echo json_encode(array('status'=>'1'));
            }else
            {    
                 //学分不足
                 echo json_encode(array('status'=>'0'));
            }
          }
          else
          {
             echo json_encode(array('status'=>'5'));
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
        function get_unfinished_orders($user_id,$token)
        {
           $result = check_token($token);
           if($result)
           {  
              $type = Goods::get_unfinished_orders($user_id);
              if($type)
              {
                foreach ($type as $k => $v) {
                  if($type[$k]['tracking_number'])
                  {
                   $type[$k]['status'] = search_logistics($type[$k]['tracking_number'],$type[$k]['logistics_company'],$type=1);
                  }
                 }
              }
              echo $result = $type?json_encode($type,JSON_UNESCAPED_UNICODE):json_encode(array('status'=>'0'));  
               
           }
           else
           {
             echo json_encode(array('status'=>'5'));
           }
        }
        function delete_order($token,$order_sn)
        {
              $result = check_token($token);
              if($result)
              {
                $result=Goods::delete_order($order_sn);   
                echo $result = $result?json_encode(array('status'=>'1')):json_encode(array('status'=>'0'));
              }
              else
              {
                 echo json_encode(array('status'=>'5')); 
              }
        }
        function get_finished_orders($openid)
        {
           $uid = User_info::getOpenid($openid);
           $uid = $uid['id'];
           $type = Goods::get_finished_orders($uid);
           echo json_encode($type,JSON_UNESCAPED_UNICODE);  

        }
        function get_return_orders($user_id,$token)
        {
           $result = check_token($token);
           if($result)
           {  
              $bb = Goods::get_return_orders($user_id);
              if($bb)
              {
                foreach ($bb as $k => $v) {
                   $bb[$k]['status'] = search_logistics($bb[$k]['tracking_number'],$bb[$k]['logistics_company'],$type=1);
                 }
              }
              echo $result = $bb?json_encode($bb,JSON_UNESCAPED_UNICODE):json_encode(array('status'=>'0'));  
               
           }
           else
           {
             echo json_encode(array('status'=>'5'));
           } 
        }
        function near_act($token,$latitude,$longitude)
        {
           $result = check_token($token);
           if($result)
           {  
              $res = array();
              $data=Act::un_start_act();
              if($data)
              {
              foreach ($data as $k => $v) {
                      $num=round(distance($data[$k]['latitude'],$data[$k]['longitude'],$latitude,$longitude));       
                      if($num<100000)
                      {
                         $res[$k] = $data[$k]; 
                      }
                   }
               }     
              echo $result = $res?json_encode($res,JSON_UNESCAPED_UNICODE):json_encode(array('status'=>'0'));;   
           }
           else
           {
             echo json_encode(array('status'=>'5'));
           }
        }
        function get_oneaddres($id,$token)
        {
           $result = check_token($token);
           if($result)
           {
              $type=Goods::get_oneaddress($id);
              echo $result = $type?json_encode($type,JSON_UNESCAPED_UNICODE):json_encode(array('status'=>'0'));;   
           }
           else
           {
             echo json_encode(array('status'=>'5'));
           }
   
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

           $get_ginfo=Goods::get_ginfo($goodsid);
           $goods=Goods::get_gcontent($goodsid);
           $get_gimg=Goods::get_gimg($goodsid);
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
        function zuce_user($phone)
        {      
          $smscode=(string)rand(111111,999999);
          $instance = new SmsDemo();
          $bb=$instance->sendSms($phone,$smscode);
          switch ($bb)
          {
              case 'OK':
                $redis = new redis();  
                $redis->connect('127.0.0.1', 6379);
                $_SESSION['phone'] = $phone;   
                $redis->set($phone.'code',$smscode);
                $redis->expire($phone.'code',60);
                echo json_encode(array('status'=>'1'));
                break;
              case '触发分钟级流控Permits:1':
                echo json_encode(array('status'=>'3'));
                break;
              case '触发小时级流控Permits:5':
                echo json_encode(array('status'=>'2'));
                break;
              default:
                echo json_encode(array('status'=>'5')); 

          }                         
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
        function cll_list($user_id)
        {
         $list=App::cll_list()?App::cll_list():'';
         if($list)
         {
           foreach ($list as $k => $v) {
             $count=App::cll_count($v['id']);
             $is_like = App::like_why($user_id,$v['id']);
             $list[$k]['is_like'] = $is_like['status']?$is_like['status']:'';
             $list[$k]['like']=$count['wrzm']?$count['wrzm']:0;
             $list[$k]['rew']=$count['mysh']?$count['mysh']:0;
             $list[$k]['smit']=$count['sgbh']?$count['sgbh']:0;
             $list[$k]['rewlist'] = App::cll_rew($v['id'])?App::cll_rew($v['id']):0;
             $data = App::like_member($v['id']);
             foreach ($data as $i => $j) {
                   $data[$i] =  $data[$i]['nickname'];
             }
             $str = $data?implode(",",$data):'';
             $list[$k]['like_member'] = $str;
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
         }
         $get=array();
         $get['data']=$list;
         echo json_encode($get, JSON_UNESCAPED_UNICODE);
         //var_dump($list);//var_dump($count);
        }

        /*格式化时间戳$data='字段 字段'需要格式化的字段用空格分开
         $type为类型，1为精确到秒 2为精确到天
         *arr为数组
         */
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
          $request->setSignName("学令牌");

          // 必填，设置模板CODE
          $request->setTemplateCode("SMS_91785037");

          // 可选，设置模板参数
          
          $request->setTemplateParam("{\"name\":\"$smscode\"}");

          // 发起访问请求
          $acsResponse = $this->acsClient->getAcsResponse($request);

          // 打印请求结果
           // var_dump($acsResponse);exit;

          return $acsResponse->Message;

      }
  }
