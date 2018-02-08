<?php
     

    //数据库封装类
    class DbConn
    {
        private $conn = NULL;//连接对象
        
        // //连接数据库 loachost
        // private function __construct()
        // { 

        //     $url = "mysql:host=localhost;dbname=local_xlp";
        //     $user = "root";
        //     $pwd = "";
        //     $this->conn = new PDO($url,$user,$pwd);
        //     $this->conn->query("set names utf8");
        // }
        //连接数据库 server
        private function __construct()
        { 

            $url = "mysql:host=localhost;dbname=xlp_app";
            $user = "wechat";
            $pwd = "005381406";
            $this->conn = new PDO($url,$user,$pwd);
            $this->conn->query("set names utf8");
        }
        //防止克隆
        private function __clone()
        {}
        public static function getInstance()
        {
            static $obj = NULL;
            if($obj == NULL)
            {
                $obj = new DbConn();
            }
            return $obj;
        }
        //执行select语句，返回：二维数组
        public function queryAll($sql)
        {   
           
            $st = $this->conn->query($sql);
            $rs = $st->fetchAll(PDO::FETCH_ASSOC);
            return $rs;
        }
        //执行select语句，返回：一维关联数组
        public function queryOne($sql)
        {   
            $st = $this->conn->query($sql);
            $rs = $st->fetchAll(PDO::FETCH_ASSOC);
            if(isset($rs[0]))
            {
                return $rs[0];
            }
            else
            {
                return NULL;
            }
        }
        //执行insert、update、delete语句，返回：受影响的行数
        public function exec($sql)
        {
            $result = $this->conn->exec($sql);
            return $result;
        }
        
        //执行insert操作的最后插入id,返回最后插入id
        public function last_id($sql)
        {
            $result = $this->conn->exec($sql);
            $last_id =$this->conn->lastInsertId(); //最后插入id
            return $last_id;
        }

    }
    //微信用户表
    class User_info
    {  

        public static function add_user($tel,$credential,$role,$time)
        {
            $sql = "insert into wechat.user_info(nickname,tel,role,regtime)values('{$tel}','{$tel}','{$role}','{$time}')";
            $conn = DbConn::getInstance();
            $id = $conn->last_id($sql);
            $sql1 = "insert into user_auths (user_id,account_type,account,credential) values ('{$id}','手机号','{$tel}','{$credential}')";
            $result = $conn->exec($sql1);
            return $id;             
        }
        public static function add_relation($tel,$inviter,$time)
        {
            $conn = DbConn::getInstance();
            $sql = "insert into user_invite(user_id,to_phone,time)values('{$inviter}','{$tel}','{$time}')";
            $result = $conn->exec($sql);
            return $result;   
        }
        public static function print_code()
        {   
            $conn = DbConn::getInstance();
            $sql = "select `key` from user_usb limit 0,500";
            $result = $conn->queryAll($sql);
            return $result; 
        } 
        //通过手机号查询用户是否存在
        public static function getuser($tel,$role)
        {
            if($role)
            {
               $sql = "select * from wechat.user_info where tel='{$tel}' and role = {$role}";
            }
            else
            {
               $sql = "select * from wechat.user_info where tel='{$tel}'";    
            }
            $conn = DbConn::getInstance();
            $result = $conn->queryOne($sql);
            return $result;
        }
        //查询经销商是否存在
        public static function get_dealer($inviter)
        {
            $sql = "select * from wechat.user_info where id='{$inviter}'";
            $conn = DbConn::getInstance();
            $result = $conn->queryOne($sql);
            return $result;
        }
        public static function agent_subordinate($user_id)
        {
            $sql = "select a.investment,a.earnings,b.tel from earnings_incorrect as a left join wechat.user_info AS b on a.user_id = b.id where pid='{$user_id}'";
            $conn = DbConn::getInstance();
            $result = $conn->queryAll($sql);
            return $result;
        }
        public function agent_detail($user_id)
        {
            $sql = "select * from earnings_incorrect where user_id = '{$user_id}'";
            $conn = DbConn::getInstance();
            $result = $conn->queryOne($sql);
            return $result;
        }
        //经销商下级详细收益
        public static function detail_earnings($user_id)
        {
           $sql = "SELECT log,DATE_FORMAT(FROM_UNIXTIME(log_time),'%Y-%m-%d %H:%i:%S') AS time,shouzhi FROM winmoney_log WHERE user_id = '{$user_id}' and shouzhi like '+%'";
           $conn = DbConn::getInstance();
           $result = $conn->queryAll($sql);
           return $result;
        }
        //登陆更新设备号
        public static function update_registrationid_id($id,$registrationid_id)
        {
           $sql = "update wechat.user_info set registrationid_id = '{$registrationid_id}' where id = '{$id}'";
           $conn = DbConn::getInstance();
           $result = $conn->exec($sql);
           return $result; 
        }
        //查看代理商邀请的用户
        public static function check_subordinate($user_id)
        {
           $sql = "SELECT a.id,a.nickname,a.tel,a.role,DATE_FORMAT(FROM_UNIXTIME(b.time),'%Y-%m-%d') as time,a.winmoney*0.01 as winmoney FROM user_invite AS b LEFT JOIN wechat.user_info AS a ON b.to_phone = a.tel WHERE b.user_id = '{$user_id}' && a.role != 3 GROUP BY id ";
           $conn = DbConn::getInstance();
           $result = $conn->queryAll($sql);
           return $result;
        }
        //查看代理商每天收益
        public static function dayily_earnings($user_id)
        {
           $sql = "SELECT sum(earnings) AS earnings,FORMAT((select sum(earnings) from agent_earnings where user_id = '{$user_id}'),2) as total ,DATE_FORMAT(FROM_UNIXTIME(time),'%Y-%m-%d') as s_time FROM agent_earnings WHERE user_id = '{$user_id}' GROUP BY s_time ";
           $conn = DbConn::getInstance();
           $result = $conn->queryAll($sql);
           return $result;
        }
        //代理商一周详细收益
        public static function week_earnings($user_id)
        {
           $sql = "SELECT sum(earnings) AS earnings,DATE_FORMAT(FROM_UNIXTIME(time),'%Y-%m-%d') AS s_time FROM agent_earnings WHERE YEARWEEK(date_format(FROM_UNIXTIME(time),'%Y-%m-%d')) = YEARWEEK(now()) AND user_id = '{$user_id}' GROUP BY s_time";
           $conn = DbConn::getInstance();
           $result = $conn->queryAll($sql);
           return $result;
        }
        //按月份查看收益
        public static function month_earnings($month,$user_id)
        {
            $sql = "SELECT earnings,DATE_FORMAT(FROM_UNIXTIME(time),'%Y-%m-%d') as s_time,FORMAT((SELECT sum(earnings) as total FROM agent_earnings WHERE user_id = '{$user_id}' and date_format(FROM_UNIXTIME(time),'%Y-%m')='{$month}'),2) as total FROM agent_earnings WHERE user_id = '{$user_id}' and date_format(FROM_UNIXTIME(time),'%Y-%m')='{$month}'";
            $conn = DbConn::getInstance();
            $result = $conn->queryAll($sql);
            return $result;
        }
        //查看是否存在顶级代理商
        public static function top_agent($inviter)
        {
           $sql = "select top_pid from earnings_incorrect where user_id = '{$inviter}'";
           $conn = DbConn::getInstance();
           $result = $conn->queryOne($sql);
           return $result;  
        }
        //添加代理商与下级收益关系
        public static function add_agent_relation($user_id,$pid,$top_pid,$time)
        {
           $sql = "insert into earnings_incorrect (`user_id`,`pid`,`top_pid`,`time`)values('{$user_id}','{$pid}','{$top_pid}','{$time}')";
           $conn = DbConn::getInstance();
           $result = $conn->exec($sql);
           return $result;
        }
        //查看三级代理关系
        public static function check_agent_relation($user_id)
        {
           $sql = "select pid,top_pid,upper_limit from earnings_incorrect where user_id = '{$user_id}'";
           $conn = DbConn::getInstance();
           $result = $conn->queryOne($sql);
           return $result;   
        } 
        //获取发表学客圈用户的设备号
        public static function get_ccl_registrationid_id($id)
        {
           $sql = "select b.registrationid_id,b.id from wechat.ccl_content as a left join wechat.user_info as b on a.uid = b.id where a.id = '{$id}'";
           //echo $sql;exit;
           $conn = DbConn::getInstance();
           $result = $conn->queryOne($sql);
           return $result;           
        }
        public static function get_user($id)
        {
           $sql = "select id,registrationid_id from wechat.user_info where id = '{$id}'";
           $conn = DbConn::getInstance();
           $result = $conn->queryOne($sql);
           return $result;            
        }
        public static function check_role($tel)
        {
           $sql = "select role from wechat.user_info where tel = '{$tel}'";
           $conn = DbConn::getInstance();
           $result = $conn->queryAll($sql);
           return $result;    
        }
        public static function checkpwd($uid)
        {
           $sql = "select credential from user_auths where user_id = '{$uid}' and account_type = '手机号'";
           $conn = DbConn::getInstance();
           $result = $conn->queryOne($sql);
           return $result;    
        }
        //查看用户是否绑定微信号
        public static function user_bound_weixin($user_id)
        {
            $sql = "select credential from user_auths where user_id = {$user_id} and account_type = '微信'";
            $conn = DbConn::getInstance();
            $result = $conn->queryOne($sql);
            return $result;

        }
        //清空系统消息
        public static function empty_system_news($id)
        {
            $sql = "delete from user_news where id in ({$id})";
            $conn = DbConn::getInstance();
            $result = $conn->exec($sql);
            return $result;
        }
        //查询用户app的学分
        public static function check_winnmoney($user_id)
        {
            $sql = "select winmoney from user_info where id = {$user_id}";
            $conn = DbConn::getInstance();
            $result = $conn->queryOne($sql);
            return $result;
 
        }
        public static function get_userinfo($user_id)
        {
            $sql = "select * from wechat.user_info where id='{$user_id}'";
            $conn = DbConn::getInstance();
            $result = $conn->queryOne($sql);
            return $result;

        }
        public static function sum_freeze_money($user_id)
        {
            $sql = "select sum(money) as money from withdrawal_record where user_id='{$user_id}'";
            $conn = DbConn::getInstance();
            $result = $conn->queryOne($sql);
            return $result;
        }
       //查询用户详细信息
        public static function get_user_info($uid,$tabname)
        {   
            $conn = DbConn::getInstance();
            $sql = "SELECT * FROM {$tabname} WHERE `user_id` = {$uid}";
            $result = $conn->queryOne($sql);
            return $result;
        }
        //修改密码
        public static function user_editpwd($credential,$uid)
        {
            $conn = DbConn::getInstance();
            $sql = "UPDATE `user_auths` SET `credential` = '{$credential}' where user_id = '{$uid}'";
            $result = $conn->exec($sql);
            return $result;   
        }
        //根据手机和角色代号获取用户
        public static function get_app_user($tel,$role)
        {   
            $sql = "select id from wechat.user_info where tel='{$tel}' and role = '{$role}'";
            $conn = DbConn::getInstance();
            $result = $conn->queryOne($sql);
            return $result;
        }
        public static function edit_img($user_id,$headimgurl)
        {
            $conn = DbConn::getInstance();
            $sql = "UPDATE wechat.`user_info` SET `headimgurl` = '{$headimgurl}' where id = '{$user_id}'";
            $result = $conn->exec($sql);
            return $result;   
        }
        public static function bind_bankcard($user_id,$card_number,$time)
        {
            $conn = DbConn::getInstance();
            $sql  = "insert into user_card (card_number,time,user_id)values('{$card_number}','{$time}','{$user_id}');";
            $sql .= "update wechat.user_info set bind_card = '1' where id = {$user_id}";
            $result = $conn->exec($sql);
            return $result;   
        }
        public static function upload_user_data($bareheaded_photo,$bust_shot,$id_card,$address,$name,$user_id)
        {
            $conn = DbConn::getInstance();
            $sql = "update wechat.user_info set complete_info = '1',nickname = '{$name}' where id = '{$user_id}';";
            $sql .= "insert into user_detail (bareheaded_photo,bust_shot,id_card,address,name,user_id)values('{$bareheaded_photo}','{$bust_shot}','{$id_card}','{$address}','{$name}','{$user_id}')";
            $result = $conn->exec($sql);
            return $result;   
        }
        public static function user_withdraw($user_id,$money,$w_id,$time,$type)
        {
            $conn = DbConn::getInstance();
            $sql = "insert into withdrawal_record (user_id,money,time,w_id,type)values('{$user_id}','{$money}','{$time}','{$w_id}','{$type}')";
            $result = $conn->exec($sql);
            return $result;  
        }
        //查询代理商收益
        public static function agent_check_earnings($user_id)
        {
            $conn = DbConn::getInstance();
            $sql = "select earnings,upper_limit from earnings_incorrect where user_id = '{$user_id}'";
            $result = $conn->queryOne($sql);
            return $result; 
        }
        // public static function card_list($user_id);
        // {
        //     $conn = DbConn::getInstance();
        //     $sql = "select card_number,id from user_card where user_id = '{$user_id}' and status = '1'";
        //     $result = $conn->queryAll($sql);
        //     return $result;   
        // }
        public static function binding_xlp($user_id,$key,$time)
        {
            $conn = DbConn::getInstance();
            $sql = "update user_xlp set uid = '{$user_id}',time = '{$time}' where `key` = '{$key}';";
            $sql .= "update wechat.user_info set activate_status = 1 where id = '{$user_id}'";
            $result = $conn->exec($sql);
            return $result;   
        }
        public static function edit_usb_detail($stu_name,$stu_img,$id)
        {
            $conn = DbConn::getInstance();
            if($stu_name)
            {
              $sql = "update usb_detail set stu_name = '{$stu_name}' where id = '{$id}'";    
            }
            else
            {
              $sql = "update usb_detail set stu_img = '{$stu_img}' where id = '{$id}'";    
            }
            $result = $conn->exec($sql);
            return $result;   
        }
        public static function card_list($user_id)
        {
            $conn = DbConn::getInstance();
            $sql = "select card_number,id from user_card where user_id = '{$user_id}' and status = 1";
            $result = $conn->queryOne($sql);
            return $result;   
        }
        public static function show_usb($key)
        {
            $conn = DbConn::getInstance();
            $sql = "select b.* from user_usb as a left join usb_detail as b on a.id = b.usb_id where a.`key` = '{$key}'";
            $result = $conn->queryOne($sql);
            return $result;   
        }
        public static function get_user_xlp($key)
        {
            $conn = DbConn::getInstance();
            $sql = "select id from user_xlp where `key` = '{$key}'";
            $result = $conn->queryOne($sql);
            return $result;   
        } 
        public static function act_num($key)
        {
            $conn = DbConn::getInstance();
            $sql = "SELECT count(b.id) AS count FROM user_usb AS a LEFT JOIN act_apply AS b ON a.pid = b.userid where `key` = '{$key}'";
            $result = $conn->queryOne($sql);
            return $result;
        }
        public static function show_credit($user_id)
        {
            $conn = DbConn::getInstance();
            $sql = "select credit from wechat.user_info where id = '{$user_id}'";
            $result = $conn->queryOne($sql);
            return $result;   
        }
        public static function show_bill($user_id)
        {
            $conn = DbConn::getInstance();
            $sql = "select shouzhi,log,log_time from winmoney_log where user_id = '{$user_id}'";
            $result = $conn->queryAll($sql);
            return $result;   
        }
        public static function edit_username($user_id,$nickname)
        {
            $conn = DbConn::getInstance();
            $sql = "update wechat.user_info set nickname = '{$nickname}' where id = '{$user_id}'";
            $result = $conn->exec($sql);
            return $result;   
        }
        public static function show_subject()
        {
            $conn = DbConn::getInstance();
            $sql = "select subject from act_subject";
            $result = $conn->queryAll($sql);
            return $result;  
        }
        public static function show_img()
        {
            $conn = DbConn::getInstance();
            $sql = "select imgurl from act_img";
            $result = $conn->queryAll($sql);
            return $result;   
        }
        public static function show_activate_member($user_id)
        {
            $conn = DbConn::getInstance();
            $sql = "SELECT d.stu_name,d.stu_img FROM user_relation AS a LEFT JOIN user_usb AS b ON a.`key` = b.`key` left join usb_detail as d on b.`id` = d.usb_id LEFT JOIN act_socre AS c ON b.`key` = c.`key` where a.follower_id = '{$user_id}' and c.score != '' and b.pid != '' group by d.stu_name ";
            $result = $conn->queryAll($sql);
            return $result;   
        }
        public static function show_unactivate_member($user_id)
        {
            $conn = DbConn::getInstance();
            $sql = "SELECT d.stu_name,d.stu_img FROM user_relation AS a LEFT JOIN user_usb AS b ON a.`key` = b.`key` left join usb_detail as d on b.`id` = d.usb_id LEFT JOIN act_socre AS c ON b.`key` = c.`key` where a.follower_id = '{$user_id}' and b.pid != '' and c.score != ''";
            $result = $conn->queryAll($sql);
            return $result; 
        }
        public static function stu_member($user_id)
        {
            $conn = DbConn::getInstance();
            $sql = "SELECT d.stu_name,d.stu_img,b.pid,c.score FROM user_relation AS a LEFT JOIN user_usb AS b ON a.`key` = b.`key` left join usb_detail as d on b.`id` = d.usb_id LEFT JOIN act_socre AS c ON b.`key` = c.`key` where a.follower_id = '{$user_id}' group by d.stu_name";
            $result = $conn->queryAll($sql);
            return $result;   
        }
        public static function card_unbundling($id)
        {
            $conn = DbConn::getInstance();
            $sql = "update user_card set status = 0 where id = '{$id}'";
            $result = $conn->exec($sql);
            return $result;  
        }
        public static function show_attestation_data($user_id)
        {
            $conn = DbConn::getInstance();
            $sql = "select id_card,name from user_detail where user_id = '{$user_id}'";
            $result = $conn->queryOne($sql);
            return $result; 
        }
        public static function center_show_usb($user_id)
        {
            $conn = DbConn::getInstance();
            $sql = "SELECT b.stu_name,b.stu_img,b.id from user_usb as a left join usb_detail as b on a.id = b.usb_id where a.pid = '{$user_id}'";
            $result = $conn->queryOne($sql);
            return $result;   
        }
        public static function user_detail($user_id)
        {
            $conn = DbConn::getInstance();
            $sql = "SELECT * from wechat.user_info where id = '{$user_id}'";
            $result = $conn->queryOne($sql);
            return $result;   
        }
        public static function center_user($user_id)
        {
            $conn = DbConn::getInstance();
            $sql = "SELECT b.stu_name,b.stu_img,a.id from user_usb as a left join usb_detail as b on a.id = b.usb_id where a.pid = '{$user_id}'";
            $result = $conn->queryOne($sql);
            return $result;   
        }
        //增加粉丝
        public static function user_relation($user_id,$follower_id,$type,$id)
        {
            $conn = DbConn::getInstance();
            $time = time();
            if(!$type)
            {
              $sql1 = "UPDATE user_relation SET `relation_type` = '0' WHERE `id` = '{$id}'";   
              $result = $conn->exec($sql1);
            }
            $sql = "INSERT INTO user_relation(user_id,follower_id,time,relation_type)VALUES('{$user_id}','{$follower_id}','{$time}','{$type}');";
            
            $result = $conn->exec($sql);
            return $result;   
        }
        //查看用户粉丝
        public static function user_get_fans($follower_id)
        {
            $conn = DbConn::getInstance();
            $sql = "SELECT t1.user_id,t2.user_name FROM user_relation as t1 LEFT JOIN user_info as t2 ON t1.user_id = t2.id WHERE t1.follower_id = '{$follower_id}'";
            $result = $conn->queryAll($sql);
            return $result;   
        }
        //取消关注   
        public static function user_del_fans($user_id,$follower_id,$type,$id)
        {
            $conn = DbConn::getInstance();
            if(!$type)
            {
              $sql1 = "UPDATE user_relation SET `relation_type` = '1' WHERE `id` = '{$id}'";
              $result = $conn->exec($sql1);
            }
            $sql = "DELETE FROM user_relation WHERE `follower_id` = {$follower_id} and `user_id` = {$user_id}";
            $result = $conn->exec($sql);
            return $result;   
        }
        //查看对方是否关注自己
        public static function user_attention($user_id,$follower_id)
        {
            $conn = DbConn::getInstance();
            $sql = "SELECT * FROM user_relation WHERE `follower_id` = {$follower_id} AND `user_id` = {$user_id}";
            $result = $conn->queryOne($sql);
            return $result;   
        }
        //用户获取活动
        public static function act_get($user_id)
        {
            $conn = DbConn::getInstance();
            $sql = "SELECT * FROM act_info WHERE `user_id` = {$user_id} and lockstatus = '1'";
            $result = $conn->queryAll($sql);
            return $result;   
        }
        public static function search_user($name)
        {
            $conn = DbConn::getInstance();
            $sql = "SELECT headimgurl,nickname,id as uid FROM wechat.user_info WHERE `nickname` like '%{$name}%'";
            $result = $conn->queryAll($sql);
            return $result; 
        }





        //更改学分
        public static function up_winmoney($user_id,$total)
        {
            $sql = "update wechat.`user_info` set `winmoney`='{$total}' where id='{$user_id}'";
            $conn = DbConn::getInstance();
            $result = $conn->exec($sql);
            return $result;
        }
        //更该代理商收益
        public static function up_earnings($user_id,$money)
        {
            $sql = "update `earnings_incorrect` set `earnings`='{$money}' where user_id='{$user_id}'";
            $conn = DbConn::getInstance();
            $result = $conn->exec($sql);
            return $result;
        }
        //增加学分
        public static function up_uwinmoney($userid,$total)
        {
            
            $res = User_info::get_userinfo($userid);
            $invite = User_info::user_superior($res['tel']);
            if($invite['user_id'])
            {
              $earnings = $total*0.1;
              $sql = "update wechat.`user_info` set `winmoney`=winmoney+'{$earnings}' where id='{$invite['user_id']}';";    
              User_info::earnings_log($invite['user_id'],$userid,$earnings);
            }
            else
            {
                $sql = ''; 
            }            
            $sql .= "update wechat.`user_info` set `winmoney`=winmoney+'{$total}' where id='{$userid}'";
            $conn = DbConn::getInstance();
            $result = $conn->exec($sql);
            return $result;
        }
        //查询用户所属的代理商
        public static function user_superior($tel)
        {
            $conn = DbConn::getInstance();
            $sql = "SELECT user_id FROM user_invite WHERE to_phone = '{$tel}'";
            $result = $conn->queryOne($sql);
            return $result;
        }
        //用户投资
        // public static function user_investment($user_id,$money)
        // {

        // }
        //增加上级代理商和顶级代理商收益
        public static function add_agent_earnings($user_id,$money,$pid,$top_pid,$upper_limit)
        {
            $conn = DbConn::getInstance();
            $sql = "update `earnings_incorrect` set `investment`=investment+'{$money}',set `upper_limit` = '{$upper_limit}' where user_id='{$user_id}';update `earnings_incorrect` set `investment`=investment+'{$investment}'*0.38 where user_id='{$pid}';update `earnings_incorrect` set `investment`=investment+'{$investment}'*0.12 where user_id='{$top_pid}'";
            $result = $conn->exec($sql);
            return $result;
        }
        //查询用户一级和二级代理
        // public static function 
        //扣取学分
        public static function up_dwinmoney($userid,$total)
        {
            $sql = "update `user_info` set `winmoney`=winmoney-'{$total}' where id='{$userid}'";
            $conn = DbConn::getInstance();
            $result = $conn->exec($sql);
            return $result;
        }
        //用户升级
        public static function up_grader($user_id)
        {
            $sql = "update `user_info` set `level`=level+'1' where id='{$user_id}'";
            $conn = DbConn::getInstance();
            $result = $conn->exec($sql);
            return $result;

        }
        //记录学分操作
        public static function banwinmoney($log,$user_id,$credit,$type='',$actid='')
        {   
            $conn = DbConn::getInstance();
            $shouzhi = $credit;
            $log_time = time();
            $sql  = "INSERT INTO `winmoney_log` (`user_id`,`log`,`shouzhi`,`log_time`,`type`,`actid`)VALUES('{$user_id}','{$log}','{$shouzhi}','{$log_time}','{$type}','{$actid}')";
            $conn->exec($sql);

        }
        //代理商收益记录
        public static function earnings_log($user_id,$subordinate,$earnings)
        {
            $conn = DbConn::getInstance();
            $time = time();
            $sql  = "INSERT INTO `agent_earnings` (`user_id`,`subordinate`,`earnings`,`time`)VALUES('{$user_id}','{$subordinate}','{$earnings}','{$time}')";
            $conn->exec($sql);

        }  
        //激活支教老师和学员关系
        public static function activate_relation($key,$follower_id)
        {
            $sql = "update `user_relation` set `status`='1' where `key`='{$key}' and follower_id = '{$follower_id}'";
            $conn = DbConn::getInstance();
            $result = $conn->exec($sql);
            return $result;
        }   
        //插入一条用户临时token
        public static function add_token($token,$id)
        {
            $sql = "update `user_info` set `token`='{$token}' where id='{$id}'";
            $conn = DbConn::getInstance();
            $result = $conn->exec($sql);
            return $result;
        }
        //通过openid查询用户的临时token
        public static function get_ltoken($token)
        {
            $sql = "select id,token from user_info where token='{$token}'";
            $conn = DbConn::getInstance();
            $result = $conn->queryOne($sql);
            return $result;
        }
        //更改头像
        public static function up_imgurl($token,$url)
        {
            $sql = "update `user_info` set `headimgurl`='{$url}' where token='{$token}'";
            $conn = DbConn::getInstance();
            $result = $conn->exec($sql);
            return $result;
        }
        //退出登录 清除token
        public static function login_out($token)
        {
            $sql = "update `user_info` set `token`=null where token='{$token}'";
            $conn = DbConn::getInstance();
            $result = $conn->exec($sql);
            return $result;
        }
    }
    //微信用户表
    class User_init_info
    {
        //插入一条用户数据
        public static function addUser($openid,$nickname,$sex,$city,$country,$province,$headimgurl,$subscribe_time)
        {
            $sql = "insert into user_init_info(openid,nickname,sex,city,country,province,headimgurl,subscribe_time)values('{$openid}','{$nickname}',{$sex},'{$city}','{$country}','{$province}','{$headimgurl}','{$subscribe_time}')";
            $conn = DbConn::getInstance();
            $result = $conn->exec($sql);
            return $result;
        }
    }
    //活动信息表
    class Act
   {
        //发表活动    
        public static function act_add($user_id,$title,$userlimit,$subject,$time,$tel,$address,$detail,$actstarttime,$actimgurl,$latitude,$longitude)
        {  
           $sql = "insert into act_info(title,userlimit,subject,time,tel,address,actstarttime,actimgurl,latitude,longitude,user_id)values('{$title}','{$userlimit}','{$subject}','{$time}','{$tel}','{$address}','{$actstarttime}','{$actimgurl}','{$latitude}','{$longitude}','{$user_id}')";
           $conn = DbConn::getInstance();
           $id = $conn->last_id($sql);
           $sql1 = "insert into act_content(content,actid)values('{$detail}','{$id}')";
           $result = $conn->exec($sql1);
           return $result;
        }
        public static function act_join($user_id,$act_id,$time)
        {
           $sql = "insert into act_apply(act_id,userid,time)values('{$act_id}','{$user_id}','{$time}')";
           $conn = DbConn::getInstance();
           $result = $conn->exec($sql);
           return $result;            
        }
        public static function check_key_winmoney($key)
        {
           $sql = "select winmoney from user_usb as a left join wechat.user_info as b on a.pid = b.id where a.`key` = '{$key}'";
           $conn = DbConn::getInstance();
           $result = $conn->queryOne($sql);
           return $result['winmoney']; 
        }
        public static function check_isjoin($token,$user_id,$act_id)
        {
           $sql = "select id act_info where user_id = '{$user_id}' and act_id = '{$act_id}' and status = '1'";
           $conn = DbConn::getInstance();
           $result = $conn->queryOne($sql);
           return $result;   
        }
        public static function tea_marking($key,$score,$act_id,$time,$user_id)
        {  
           $sql = "insert into user_relation(`key`,follower_id,time)values('{$key}','{$user_id}','{$time}');";
           $sql .= "insert into act_socre(`key`,score,actid,time,userid)values('{$key}','{$score}','{$act_id}','{$time}','{$user_id}')";
          // echo $sql;exit;
           $conn = DbConn::getInstance();
           $result = $conn->exec($sql);
           return $result; 
        }
        //统计打分记录
        public static function count_act_score($key,$act_id,$time)
        {
           $sql = "SELECT sum(score) as score,time FROM act_socre WHERE `key` = '{$key}' and DATE_FORMAT(FROM_UNIXTIME(time),'%Y-%m-%d') LIKE DATE_FORMAT(FROM_UNIXTIME({$time}),'%Y-%m-%d')";
           $conn = DbConn::getInstance();
           $result = $conn->queryOne($sql);
           return $result;
        }
        public static function xlp_list($user_id)
        {
           $sql = "SELECT a.`key`,a.time,b.`status` FROM user_xlp AS a LEFT JOIN product_refund AS b on a.`key` = b.`key` WHERE a.uid = '{$user_id}'";
           $conn = DbConn::getInstance();
           $result = $conn->queryAll($sql);
           return $result;             
        }
        public static function application_return($user_id,$content,$key,$name,$tel,$time)
        {
           $sql = "insert into product_refund(userid,`key`,content,name,tel,time)values('{$user_id}','{$key}','{$content}','{$name}','{$tel}','{$time}')";
           $conn = DbConn::getInstance();
           $result = $conn->exec($sql);
           return $result;  
        }
        public static function start_act($id,$actsta)
        {
           $sql = "update act_info set actsta = '{$actsta}' where id = '{$id}'";
           $conn = DbConn::getInstance();
           $result = $conn->exec($sql);
           return $result;
        }
        public static function act_list($user_id,$actsta)
        {
           if($user_id)
           {
             $sql = "select id,title,actstarttime,address,actimgurl,userlimit from act_info where user_id = '{$user_id}' and actsta = '{$actsta}' and lockstatus = '1' order by id desc"; 
           }
           else
           {
             $sql = "select id,title,actstarttime,address,actimgurl,userlimit from act_info where lockstatus = '1' order by id desc";
           }

           $conn = DbConn::getInstance();
           $result = $conn->queryAll($sql);
           return $result;   
        }
        public static function search_act($name,$user_id,$role)
        {
           if($role == '1')
           {
             $sql = "select title,id,actimgurl,actstarttime as time from act_info where title like '%{$name}%' and lockstatus = '1'";  
           }
           else
           {
              $sql = "select title,id,actimgurl,actstarttime as time from act_info where user_id = '{$user_id}' and title like '%{$name}%' and lockstatus = '1'";
           }
           $conn = DbConn::getInstance();
           $result = $conn->queryAll($sql);
           return $result; 
        }
        public static function get_user_act($user_id,$actsta)
        {
           $sql = "SELECT b.id,b.title,b.actstarttime,b.address,b.actimgurl,b.userlimit,a.id as apply_id FROM act_apply as a LEFT JOIN act_info AS b ON a.`act_id` = b.id WHERE a.userid = '{$user_id}' AND b.actsta = '{$actsta}' AND a.display = 1 and b.lockstatus = '1'";
           $conn = DbConn::getInstance();
           $result = $conn->queryAll($sql);
           return $result;
        }
        public static function update_act_display($id)
        {
            $sql = "select id,title,actstarttime,address,actimgurl,userlimit,latitude,longitude from act_info where actsta = '2'";            
            $conn = DbConn::getInstance();
            $result = $conn->queryAll($sql);
            return $result;             
        }
        //查看未开始的活动
        public static function un_start_act()
        {
            $sql = "select id,title,actstarttime,address,actimgurl,userlimit,latitude,longitude from act_info where actsta = '2' and lockstatus = '1' order by id desc ";            
            $conn = DbConn::getInstance();
            $result = $conn->queryAll($sql);
            return $result;   
        }
        public static function act_detail($id)
        {
           $sql = "select a.start_img,a.underway_img,a.end_img,a.actsta,a.id,a.title,a.actimgurl,a.actstarttime,a.userlimit,b.content,c.nickname,c.headimgurl,a.address,a.tel,count(d.id) as join_num from act_info as a left join act_content as b on a.id = b.actid left join wechat.user_info as c on a.user_id = c.id LEFT JOIN act_apply as d on a.id = d.act_id where a.id = '{$id}'";
           $conn = DbConn::getInstance();
           $result = $conn->queryOne($sql);
           return $result;    
        }
        //显示打分记录
        public static function show_grade_record($actid)
        {
           $sql = "SELECT `stu_name`,`stu_img`,sum(score) AS score FROM act_socre AS a LEFT JOIN user_usb AS b ON a.`key` = b.`key` LEFT JOIN usb_detail as c ON b.id = c.usb_id WHERE actid = '{$actid}' GROUP BY `stu_name`";
           $conn = DbConn::getInstance();
           $result = $conn->queryAll($sql);
           return $result;  
        }
        public static function check_key($key)
        {
           $sql = "select * from user_usb where `key` = '{$key}'";
           $conn = DbConn::getInstance();
           $result = $conn->queryOne($sql);
           return $result;  
        }
        public static function binding_usb($key,$user_id,$time)
        {
           $sql = "update user_usb set pid = '{$user_id}',pdate = '{$time}',`status` = '3' where `key` = '{$key}'";
           $conn = DbConn::getInstance();
           $result = $conn->exec($sql);
           return $result;   
        }
        public static function perfect_usb($stu_img,$stu_name,$log,$usb_id)
        {
           $sql = "insert into usb_detail(stu_name,stu_img,log,usb_id)values('{$stu_name}','{$stu_img}','{$log}','{$usb_id}')";
           $conn = DbConn::getInstance();
           $result = $conn->exec($sql);
           return $result;  
        }
        public static function check_usb_status($key)
        {
           $sql = "select b.stu_name,b.stu_img from user_usb as a left join usb_detail as b on a.id = b.usb_id where a.`key` = '{$key}'";
           $conn = DbConn::getInstance();
           $result = $conn->queryOne($sql);
           return $result;   
        }
        public static function del_act($id)
        {
           $sql = "delete from act_info where id in({$id})";
           $conn = DbConn::getInstance();
           $result = $conn->exec($sql);
           return $result;
        }
        public static function check_user_usb($user_id)
        {
           $sql = "select id from user_usb where pid = '{$user_id}'";
           $conn = DbConn::getInstance();
           $result = $conn->queryOne($sql);
           return $result;    
        }
        public static function get_tea($key)
        {
           $sql = "(SELECT follower_id FROM user_relation WHERE `key` = '{$key}' and status = '0' ORDER BY id ASC LIMIT 1)UNION(SELECT follower_id FROM user_relation WHERE `key` = '{$key}' and status = '0'  ORDER BY id DESC LIMIT 1)";
           $conn = DbConn::getInstance();
           $result = $conn->queryAll($sql);
           return $result;    
        }
        public static function act_winmoney($key)
        {
           $sql = "select sum(score)as score,id,time,actid,userid from act_socre where `key` = '{$key}' and status = 0";
           $conn = DbConn::getInstance();
           $result = $conn->queryOne($sql);
           return $result; 
        }
        //学员上一次参加活动的时间
        public static function act_member_join($key)
        {
           $sql = "select time from act_socre where `key` = '{$key}' order by time desc";
           $conn = DbConn::getInstance();
           $result = $conn->queryOne($sql);
           return $result;   
        }
        public static function show_income($user_id,$form)
        {
           $sql = "SELECT sum(shouzhi) as shouzhi FROM winmoney_log WHERE YEARWEEK(date_format(FROM_UNIXTIME(log_time), '%Y-%m-%d')) = YEARWEEK(now()) and user_id = '{$user_id}' and type = '{$form}'";
           $conn = DbConn::getInstance();
           $result = $conn->queryOne($sql);
           return $result['shouzhi'];   
        }
        public static function show_income_total($user_id,$form)
        {
           $sql = "SELECT sum(shouzhi) as total FROM winmoney_log WHERE user_id = '{$user_id}' and type = '{$form}'";
           $conn = DbConn::getInstance();
           $result = $conn->queryOne($sql);
           return $result['total'];   
        }
        public static function week_show_income($user_id,$form)
        {
           $sql = "SELECT sum(shouzhi) as count,DATE_FORMAT(FROM_UNIXTIME(log_time),'%Y-%m-%d') as time FROM winmoney_log WHERE YEARWEEK(date_format(FROM_UNIXTIME(log_time),'%Y-%m-%d')) = YEARWEEK(now()) AND user_id = '{$user_id}' AND type = '{$form}' GROUP BY time";
           $conn = DbConn::getInstance();
           $result = $conn->queryAll($sql);
           return $result;   
        }
        public static function show_income_detail($user_id,$form)
        {
           $sql = "SELECT sum(a.shouzhi) AS count,b.title,DATE_FORMAT(FROM_UNIXTIME(log_time),'%Y-%m-%d') as daily FROM winmoney_log as a left JOIN act_info as b on a.actid = b.id WHERE a.user_id = '{$user_id}' AND a.type = '{$form}' GROUP BY daily";
           $conn = DbConn::getInstance();
           $result = $conn->queryAll($sql);
           return $result; 
        }
        public static function update_actscore_status($actid,$key)
        {
           $sql = "update act_socre set status = 1 where actid = '{$actid}' and `key`='{$key}'";
           $conn = DbConn::getInstance();
           $result = $conn->exec($sql);
           return $result;
        }
        public static function member_ranking($actid)
        {
           $sql = "SELECT stu_name,stu_img,sum(a.score) as score FROM act_socre AS a LEFT JOIN user_usb AS b ON a.`key` = b.`key` LEFT JOIN usb_detail AS c ON b.id = c.usb_id where a.actid = '{$actid}' GROUP BY a.`key` ORDER BY score desc";
           $conn = DbConn::getInstance();
           $result = $conn->queryAll($sql);
           return $result; 
        }
        public static function upload_actimg($actid,$img,$img_type)
        {
           $sql = "update act_info set {$img_type} = '{$img}' where id = '{$actid}'";
           $conn = DbConn::getInstance();
           $result = $conn->exec($sql);
           return $result; 
        }
        public static function check_user_join($user_id,$actid)
        {
           $sql = "select id from act_apply where act_id = '{$actid}' and userid = '{$user_id}' and status = '0' ";
           $conn = DbConn::getInstance();
           $result = $conn->queryOne($sql);
           return $result; 
        }
        public static function show_act_info($key)
        {
           $sql = "SELECT sum(a.score) as score,b.title,b.address,b.actimgurl,b.time FROM act_socre AS a LEFT JOIN act_info AS b ON a.actid = b.id WHERE a.`key` = '{$key}' and status = '0'";
           $conn = DbConn::getInstance();
           $result = $conn->queryOne($sql);
           return $result;  
        }
        //获取活动口令
        public static function get_command($act_id)
        {  
           $sql = "select * from act_info where id = {$act_id}";
           $conn = DbConn::getInstance();
           $result = $conn->queryOne($sql);
           return $result;

        }
            //根据openid和jobid判断当前用户是否竞选者自己
            public static function get_job_ida($jobid)
            {
                $sql = "select * from act_job_user where id='{$jobid}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryOne($sql);
                return $result;
            }
            public static function get_job_idb($jobid)
            {
                $sql = "select * from act_job where id='{$jobid}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryOne($sql);
                return $result;
            }
            public static function get_job_idc($jobid)
            {
                $sql = "select * from user_info where openid='{$jobid}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryOne($sql);
                return $result;
            }










            //根据openid和jobid判断当前用户是否竞选者自己
            public static function check_job_my($openid,$jobid)
            {
                $sql = "select * from act_job_user where id='{$jobid}' && user='{$openid}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryOne($sql);
                return $result;
            }
            //列出该职位竞选者的fid
            public static function get_job_user_fid($jobid)
            {
                $sql = "select f_id from act_job_user where id='{$jobid}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryOne($sql);
                return $result;
            }
            //列出该职位竞选者
            public static function get_job_user($jobid)
            {
                $sql = "select b.nickname,b.headimgurl,a.poll from act_job_user as a inner join user_info as b on a.user=b.openid where a.f_id='{$jobid}' order by a.poll desc";
                $conn = DbConn::getInstance();
                $result = $conn->queryAll($sql);
                return $result;
            }

            //给竞选者加票纪录
            public static function add_user_job_one($jobid)
            {
                $sql = "update `act_job_user` set `poll`=`poll`+1 where id='{$jobid}'";
                $conn = DbConn::getInstance();
                $result = $conn->exec($sql);
                return $result;
            }
            //给竞选者加票纪录
            public static function add_user_job($openid,$jobid,$time)
            {
                $sql = "insert into act_job_add(f_id,user,time)values('{$jobid}','{$openid}','{$time}')";
                $conn = DbConn::getInstance();
                $result = $conn->exec($sql);
                return $result;
            }

            //查询用户是否给竞选者加油过
            public static function up_job_add_check($openid,$jobid)
            {
                $sql = "select * from act_job_add where user='{$openid}' && f_id='{$jobid}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryOne($sql);
                return $result;
            }

            //查询加油用户是否竞选者自己
            public static function up_job_check($openid,$jobid)
            {
                $sql = "select * from act_job_user where id='{$jobid}' && user='{$openid}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryOne($sql);
                return $result;
            }

            //写入用户竞选角色
            public static function job_up_user($actid,$openid,$jobid,$time)
            {
                $sql = "insert into act_job_user(f_id,actid,user,poll,jiontime)values('{$jobid}','{$actid}','{$openid}','0','{$time}')";
                $conn = DbConn::getInstance();
                $result = $conn->exec($sql);
                return $result;
            }

            //根据活动id 查询用户是否报名活动角色
            public static function job_check_user($actid,$openid)
            {
                $sql = "select * from act_job_user where actid='{$actid}' && user='{$openid}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryOne($sql);
                return $result;
            }

            //根据活动id 查询活动角色信息
            public static function act_get_job($actid)
            {
                $sql = "select id,bonus,jobname,joblimit from act_job where f_id='{$actid}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryAll($sql);
                return $result;
            }

            //查询活动信息
            public static function get_act()
            {
                $sql = "select actsta,t1.id,title,actimgurl,actdesc,actstarttime,actendtime,address,count(userid)as countuser,userlimit  from (act_info as t1 left join act_lbs as t2 on t1.id=t2.actid)  left join act_payorder as t3 on (t1.id=t3.actid) where t1.lock=0 group by t1.id order by actran desc";
                /*$sql = "select actsta,t1.id,title,actimgurl,actdesc,actstarttime,actendtime,address,count(userid)as countuser,userlimit  from act_info as t1 inner join act_payorder as t2 on t1.id=t2.actid inner join act_lbs as t3 on t1.id=t3.actid where t1.lock=0 group by t1.id order by actsta desc";*/
                $conn = DbConn::getInstance();
                $result = $conn->queryAll($sql);
                return $result;
            }
            //根据活动id 查询详细信息
            public static function get_actinfo_db($actid)
            {
                $sql = "select act_info.id,title,count,actimgurl,actdesc,actstarttime,actendtime,joinstarttime,joinendtime,address,content,userlimit,mastername,masterphone,label,crowd,deposit,actsta from (act_info left join act_lbs on act_info.id=act_lbs.actid) left join act_content on (act_info.id=act_content.actid) where act_info.id ='{$actid}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryOne($sql);
                return $result;
            }


            //浏览活动次数加一
            public static function put_actcount($actid)
            {
                $sql = "update `act_info` set `count`=`count`+1 where id='{$actid}'";
                $conn = DbConn::getInstance();
                $result = $conn->exec($sql);
                return $result;
            }
             //根据活动id 用户id判断用户是否已参加活动
            public static function act_pay_user($actid,$openid)
            {
                $sql = "select id from act_payorder where actid='{$actid}' && userid='{$openid}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryOne($sql);
                return $result;
            }
             //根据活动id 用户id判断用户是否支付
            public static function act_check_pay($actid,$openid)
            {
                $sql = "select id,paystate from act_payorder where actid='{$actid}' && userid='{$openid}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryOne($sql);
                return $result;
            }
            // 获取报名时间结束时间与人数限制
            public static function act_info_user($actid)
            {
                $sql = "select joinendtime,userlimit from act_info where id='{$actid}'";
                
                $conn = DbConn::getInstance();
                $result = $conn->queryOne($sql);
                return $result;
            }
            // 获取该活动已报名人数
            public static function act_infolimit($actid)
            {
                $sql = "select count(*)as limi from `act_payorder` where actid='{$actid}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryOne($sql);
                return $result;
            }
             //活动报名
            public static function put_pay_userdb($openid,$actid,$nickname,$phone,$act_payorder,$time)
            {
                $sql = "insert into act_payorder(userid,actid,nickname,phone,paystate,time)values('{$openid}','{$actid}','{$nickname}','{$phone}','{$act_payorder}','{$time}')";
                $conn = DbConn::getInstance();
                $result = $conn->exec($sql);
                return $result;
            }
             //活根据openid列出当前用户参加的全部活动
            public static function get_act_user($openid)
            {
                $sql = "select a.id,title,count,actimgurl,address,actstarttime,actendtime,joinstarttime,joinendtime,mastername,masterphone,crocode,paystate,state,actsign,`mapurl` from act_info as a join act_payorder as b on a.id=b.actid join act_lbs as c on a.id=c.actid where b.userid='{$openid}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryAll($sql);
                return $result;
            }
             //活根据openid列出当前用户参加未开始的活动
            public static function get_actw_user($openid)
            {
                $sql = "select a.id,title,count,actimgurl,address,actstarttime,actendtime,joinstarttime,joinendtime,mastername,masterphone,paystate,state,actsign,`mapurl` from act_info as a join act_payorder as b on a.id=b.actid join act_lbs as c on a.id=c.actid where b.userid='{$openid}' && a.actsta=2";
                $conn = DbConn::getInstance();
                $result = $conn->queryAll($sql);
                return $result;
            }
             //活根据openid列出当前用户参加的已结束的活动
            public static function get_actj_user($openid)
            {
                $sql = "select a.id,title,count,actimgurl,address,actstarttime,actendtime,joinstarttime,joinendtime,mastername,masterphone,paystate,state,actsign,`mapurl` from act_info as a join act_payorder as b on a.id=b.actid join act_lbs as c on a.id=c.actid where b.userid='{$openid}' &&  a.actsta=0";
                $conn = DbConn::getInstance();
                $result = $conn->queryAll($sql);
                return $result;
            }
            //根据活动id 用户id判断用户是否已签到
            public static function act_time_user($actid,$openid)
            {
                $sql = "select id,troops from act_signtime where actid='{$actid}' && userid='{$openid}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryOne($sql);
                return $result;
            }
             //根据活动id 用户id更新用户更新用户签到状态
            public static function act_sign_user($type,$actid,$openid)
            {
                $sql = "update act_payorder set actsign='{$type}' where actid='{$actid}' && userid='{$openid}'";
                $conn = DbConn::getInstance();
                $result = $conn->exec($sql);
                return $result;
            }

             //根据openid列出当前用户可签到的活动
            public static function get_act_start($openid)
            {
                $sql = "select a.id,title,actstarttime,actendtime,joinstarttime,joinendtime,mastername,masterphone,paystate,state,actsign from act_info as a join act_payorder as b on a.id=b.actid where b.userid='{$openid}' && a.adminsta=1 && b.actsign=0";
                $conn = DbConn::getInstance();
                $result = $conn->queryAll($sql);
                return $result;
            }
            //根据actid 查出该条活动lbs
            public static function put_act_lbs($actid)
            {
                $sql = "SELECT actid,`latitude`,`longitude` FROM `act_lbs` WHERE `actid`='{$actid}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryOne($sql);
                return $result;
            }
             //用户签到
            public static function put_act_sign($openid,$actid)
            {
                $sql = "UPDATE `act_payorder` SET `actsign`=1 WHERE `actid`='{$actid}' && `userid`='{$openid}'";
                $conn = DbConn::getInstance();
                $result = $conn->exec($sql);
                return $result;
            }
            //已进行中的活动
            public static function get_act_pro($openid)
            {
                $sql = "select actid from act_info as a join act_payorder as b on a.id=b.actid where b.userid='{$openid}' && a.adminsta >0";
                $conn = DbConn::getInstance();
                $result = $conn->queryAll($sql);
                return $result;
            } 
             //签到表加一条记录
            public static function sign_user($openid,$actid,$mintroop,$time)
            {
                $sql = "INSERT INTO act_signtime(`actid`, `userid`, `time`, `troops`) VALUES ('{$actid}','{$openid}','{$time}','{$mintroop}')";
                $conn = DbConn::getInstance();
                $result = $conn->exec($sql);
                return $result;
            }
            //查询该用户签到情况
            public static function get_pay_one($openid,$actid)
            {
                $sql = "select actsign,adminsta from act_info as a join act_payorder as b on a.id=b.actid where b.userid='{$openid}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryOne($sql);
                return $result;
            }
            //查询该活动打分次数
            public static function mark_num($actid)
            {
                $sql = "select marknum from act_info where id='{$actid}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryOne($sql);
                return $result;
            }
            //查询该用户属于哪个队伍
            public static function get_troop($actid,$openid)
            {
                $sql = "select troops from act_signtime where actid='{$actid}' && userid='{$openid}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryOne($sql);
                return $result;
            }
            //查询该活动是否可以打分状态
            public static function get_mark($actid)
            {
                $sql = "select adminsta,setvalue,marknum from act_info where id='{$actid}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryOne($sql);
                return $result;
            }
            //写入打分
            public static function put_tro_mark($openid,$actid,$troid,$marknum,$mark,$scotime)
            {
                $sql = "insert into `act_score`(`openid`, `actid`, `troid`, `marknum`, `mark`, `scotime`) values ('{$openid}','{$actid}','{$troid}','{$marknum}','{$mark}','{$scotime}')";
                $conn = DbConn::getInstance();
                $result = $conn->exec($sql);
                return $result;
            }
             //10010
            public static function get_ranking($actid)
            {
                /*$sql = "select act_score.troid, sum(mark) as sum from `act_score` where act_score.actid = '{$actid}'group by act_score.troid order by sum desc";*/ 
                $sql = "SELECT  `troid`, `total` FROM `act_ranking` WHERE actid = '{$actid}' order by total desc";
                $conn = DbConn::getInstance(); 
                $result = $conn->queryAll($sql);
                return $result;
            }
             //获取用户已经打分数
            public static function get_scosum($actid,$openid,$marknum)
            {
                $sql = "select sum(mark) as sum from `act_score` where act_score.actid = '{$actid}' && openid='{$openid}' && marknum='{$marknum}'"; 
                //$sql = "SELECT  `troid`, `total` FROM `act_ranking` WHERE actid = '{$actid}' order by total desc";
                $conn = DbConn::getInstance();
                $result = $conn->queryOne($sql);
                return $result;
            }
              //创建活动
            public static function put_actinfo($title,$catid,$actdesc,$actimgurl,$masterid,$mastername,$masterphone,$actstarttime,$actendtime,$joinstarttime,$joinendtime,$userlimit,$deposit,$time)
            {
                $sql = "INSERT INTO `act_info`(`title`, `catid`, `actdesc`, `actimgurl`, `masterid`, `mastername`, `masterphone`, `actstarttime`, `actendtime`, `joinstarttime`, `joinendtime`, `userlimit`, `deposit`,`time`) VALUES ('{$title}','{$catid}','{$actdesc}','{$actimgurl}','{$masterid}','{$mastername}','{$masterphone}','{$actstarttime}','{$actendtime}','{$joinstarttime}','{$joinendtime}','{$userlimit}','{$deposit}','{$time}')"; 
                    $conn = DbConn::getInstance(); 
                    $result = $conn->exec($sql);
                    return $result;
            }
             //查出最新添加的活动id
            public static function get_actid()
            {
                $sql = "select max(id) as actid from act_info"; 
                $conn = DbConn::getInstance(); 
                $result = $conn->queryOne($sql);
                return $result;
            }
            //创建活动地址
            public static function put_actlbs($actid,$address,$latitude,$longitude)
            {
                    $sql = "INSERT INTO `act_lbs`(actid,`address`, `latitude`, `longitude`) VALUES ('{$actid}','{$address}','{$latitude}','{$longitude}')"; 
                    $conn = DbConn::getInstance(); 
                    $result = $conn->exec($sql);
                    return $result;
            }
            //创建活动详细内容介绍
            public static function put_actcontent($actid,$content)
            {
                    $sql = "INSERT INTO `act_content`(actid,`content`) VALUES ('{$actid}','{$content}')"; 
                    $conn = DbConn::getInstance(); 
                    $result = $conn->exec($sql);
                    return $result;
            }
            //分值表初始化
            public static function put_act_ranking($actid,$troid,$total)
            {
                    $sql = "INSERT INTO `act_ranking`(`actid`, `troid`, `total`) VALUES ('{$actid}','{$troid}','{$total}')"; 
                    $conn = DbConn::getInstance(); 
                    $result = $conn->exec($sql);
                    return $result;
            }
            //读出分类
            public static function get_actcat()
            {
                    $sql = "SELECT `id`,`catname`, `catran` FROM `act_cat`"; 
                    $conn = DbConn::getInstance(); 
                    $result = $conn->queryAll($sql);
                    return $result;
            }
            //10014
            public static function get_actlist($page,$id)
            { 
                 $conn = DbConn::getInstance();
                 $str = empty($id)? '' : "and a.id = {$id}";
                 $sql1 = "SELECT a.id,`title`, `time`,`catname` from act_info as a join act_cat as b on a.catid=b.id where a.lockstatus=0 and is_recovery = '1' $str ";
                 $total = count($conn->queryAll($sql1));


                if($id)
                { 
                  $where = empty($page)? "and a.id={$id} limit 0,7":"and a.id={$id} limit {$page},7";  
                  $sql = "SELECT a.id,`title`, `time`,`catname` from act_info as a join act_cat as b on a.catid=b.id where a.lockstatus=0 and is_recovery = '1' $where ";

                }else
                {  
                   $where = empty($page)? "limit 0,7" : "limit {$page},7";
                   $sql = "SELECT a.id,`title`, `time`,`catname` from act_info as a join act_cat as b on a.catid=b.id where a.lockstatus=0 and is_recovery = '1' $where";
                }
                $result = $conn->queryAll($sql);

                foreach($result as $k => $k)
                {     
                      $result[$k]['total'] = $total;
                }
                    
                    return $result;
            }
            //10015
            public static function update_act($actid)
            {
                    $sql = "select `title`, `catid`, `actdesc`, `actimgurl`, `masterid`, `mastername`, `masterphone`, `actstarttime`, `actendtime`, `joinstarttime`, `joinendtime`, `userlimit`, `deposit`,`time`,`content`,`address`, `latitude`, `longitude` from act_info as t1 inner join act_content as t2 on t1.id=t2.actid inner join act_lbs as t3 on t1.id=t3.actid where t1.id='{$actid}'"; 
                    $conn = DbConn::getInstance(); 
                    $result = $conn->queryOne($sql);
                    return $result;
            }
            //10016
            // public static function del_act($actid)
            // {
            //         $sql = "UPDATE `act_info` SET `lock`=1 WHERE id='{$actid}'"; 
            //         $conn = DbConn::getInstance(); 
            //         $result = $conn->exec($sql);
            //         return $result;
            // }
            //更改活动状态
            public static function up_actsta($sta,$actid)
            {
                    $sql = "UPDATE `act_info` SET `actsta`='{$sta}' WHERE id='{$actid}'"; 
                    $conn = DbConn::getInstance(); 
                    $result = $conn->exec($sql);
                    return $result;
            }
            //10017
            public static function get_recycle()
            {
                    $sql = "SELECT a.id,`title`, `time`,`catname` from act_info as a join act_cat as b on a.catid=b.id where a.lock=1"; 
                    $conn = DbConn::getInstance(); 
                    $result = $conn->queryAll($sql);
                    return $result;
            }
            //10018 修改avt_info
            public static function up_info($actid,$title,$catid,$actdesc,$actimgurl,$masterid,$mastername,$masterphone,$actstarttime,$actendtime,$joinstarttime,$joinendtime,$userlimit,$deposit,$time)
            {
                    $sql = "UPDATE `act_info` SET `title`='{$title}',`catid`='{$catid}',`actdesc`='{$actdesc}',`actimgurl`='{$actimgurl}',`masterid`='{$masterid}',`mastername`='{$mastername}',`masterphone`='{$masterphone}',`actstarttime`='{$actstarttime}',`actendtime`='{$actendtime}',`joinstarttime`='{$joinstarttime}',`joinendtime`='{$joinendtime}',`userlimit`='{$userlimit}',`deposit`='{$deposit}',`time`='{$time}' WHERE id='{$actid}'"; 
                    $conn = DbConn::getInstance(); 
                    $result = $conn->exec($sql);
                    return $result;
            }
            //10018 修改avt_content
            public static function up_content($actid,$content)
            {
                    $sql = "UPDATE `act_content` SET `content`='{$content}' WHERE actid='{$actid}'"; 
                    $conn = DbConn::getInstance(); 
                    $result = $conn->exec($sql);
                    return $result;
            }
            //10018 修改avt_lbs
            public static function up_lbs($actid,$address,$latitude,$longitude)
            {
                    $sql = "UPDATE `act_lbs` SET `address`='{$address}',`latitude`='{$latitude}',`longitude`='{$longitude}' WHERE actid='{$actid}'"; 
                    $conn = DbConn::getInstance(); 
                    $result = $conn->exec($sql);
                    return $result;
            }
            //该用户总打分值
            public static function user_mark($actid,$openid)
            {
                    $sql = "SELECT sum(mark) as num FROM `act_grade_user` WHERE actid='{$actid}' && openid='{$openid}'"; 
                    $conn = DbConn::getInstance(); 
                    $result = $conn->queryOne($sql);
                    return $result;
            }
            //10020
            public static function put_user_mark($openid,$actid,$userid,$mark,$time)
            {
                    $sql = "INSERT INTO `act_grade_user`(`openid`, `actid`, `mark`, `userid`, `time`) VALUES ('{$openid}','{$actid}','{$mark}','{$userid}','{$time}')"; 
                    $conn = DbConn::getInstance(); 
                    $result = $conn->exec($sql);
                    return $result;
            }
            //根据token查询个人信息
            public static function get_user_token($openid)
            {
                    $sql = "SELECT `id`,`openid`, `nickname`, `sex`, `city`, `country`, `province`, `headimgurl`, `tel`, `address`, `birthday`, `level`, `email`, `role`, `acivity`, `regtime` FROM `user_info` WHERE token='{$openid}'"; 
                    $conn = DbConn::getInstance(); 
                    $result = $conn->queryOne($sql);
                    return $result;
            }
             //10022
            public static function get_qiandao($actid)
            {
                    $sql = "SELECT nickname,headimgurl,t1.time FROM act_signtime as t1 inner join user_info as t2 on t1.userid=t2.openid  WHERE t1.actid ='{$actid}' ORDER BY time ASC LIMIT 0, 10"; 
                    $conn = DbConn::getInstance(); 
                    $result = $conn->queryAll($sql);
                    return $result;
            }
            //10023
            public static function get_actsum($actid,$trop)
            {
                    //$sql = "select actid,troid,sum(mark) as sum from act_score where actid='{$actid}' && troid='{$trop}'";
                    $sql = "select troid,total from act_ranking where actid='{$actid}' && troid='{$trop}'"; 
                    $conn = DbConn::getInstance(); 
                    $result = $conn->queryOne($sql);
                    return $result;
            }
            //获取活动控制状态
             public static function get_adminsta($actid)
            {
                    $sql = "select adminsta from act_info where id='{$actid}'"; 
                    $conn = DbConn::getInstance(); 
                    $result = $conn->queryOne($sql);
                    return $result;
            }
             //根据id获取openid
             public static function get_openid($userid)
            {
                    $sql = "select openid from user_info where id='{$userid}'"; 
                    $conn = DbConn::getInstance(); 
                    $result = $conn->queryOne($sql);
                    return $result;
            }
            //根据openid获取userid
             public static function get_userid($openid)
            {
                    $sql = "select id from user_info where openid='{$openid}'"; 
                    $conn = DbConn::getInstance(); 
                    $result = $conn->queryOne($sql);
                    return $result;
            }
            //用户队伍更换
             public static function up_troops($troops,$userid,$actid)
            {
                    $sql = "update `act_signtime` set `troops`='{$troops}' where userid='{$userid}' && actid='{$actid}'"; 
                    $conn = DbConn::getInstance(); 
                    $result = $conn->exec($sql);
                    return $result;
            }
             //10026
             public static function get_adhome($ad_type)
            {
                    $sql = "select `title`, `imgurl`, `link`, `time` from wechat.`ad_home` where `lock`=0 && ad_type='{$ad_type}' order by sork desc";
                    $conn = DbConn::getInstance();
                    $result = $conn->queryAll($sql);
                    return $result;
            }
            //获取某个分类下的活动列表
            public static function get_typeact($catid)
            {
                $sql = "select actsta,t1.id,title,actimgurl,actdesc,actstarttime,actendtime,address,count(userid)as countuser,userlimit  from (act_info as t1 left join act_lbs as t2 on t1.id=t2.actid)  left join act_payorder as t3 on (t1.id=t3.actid) where t1.lock=0 and t1.catid='{$catid}' group by t1.id order by actran desc";
                
                $conn = DbConn::getInstance();

                $result = $conn->queryAll($sql);
                return $result;
            }
            //10027
            public static function admin_act($actid,$openid)
            {
                $sql = "select id from `act_info` where id='{$actid}' && masterid='{$openid}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryOne($sql);
                return $result;
            }
            //获取参加活动的用户
            public static function get_payuser($actid)
            {
                $sql = "select userid,nickname from `act_payorder` where actid='{$actid}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryAll($sql);
                return $result;
            }
            //获取参加活动的未签到的用户
            public static function get_payuserw($actid)
            {
                $sql = "select userid,nickname from `act_payorder` where actid='{$actid}' && actsign=0";
                $conn = DbConn::getInstance();
                $result = $conn->queryAll($sql);
                return $result;
            }
             //获取参加活动的已签到的用户
            public static function get_payusery($actid)
            {
                $sql = "select userid,nickname from `act_payorder` where actid='{$actid}' && actsign=1";
                $conn = DbConn::getInstance();
                $result = $conn->queryAll($sql);
                return $result;
            }
            //获取活动地址
            public static function get_paylbs($actid)
            {
                $sql = "select `address` from `act_lbs`  where actid='{$actid}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryOne($sql);
                return $result;
            }
             //获取活动名称
            public static function get_paytit($actid)
            {
                $sql = "select `title` from `act_info`  where id='{$actid}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryOne($sql);
                return $result;
            }
            //更改活动管理状态
            public static function up_adminsta($actid,$adminsta)
            {
                $sql = "update `act_info` set `adminsta`='{$adminsta}' where id='{$actid}'";
                $conn = DbConn::getInstance();
                $result = $conn->exec($sql);
                return $result;
            }
             //获取lbs地图链接
            /*public static function get_mapurl($actid)
            {
                $sql = "select `mapurl` from `act_lbs`  where actid='{$actid}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryOne($sql);
                return $result;
            }*/
             //获取活动已经签到用户
            public static function get_payqd($actid,$openid)
            {
                $sql = "select `id`,`troops` from `act_signtime`  where actid='{$actid}' && userid='{$openid}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryOne($sql);
                return $result;
            }
             //获取徒步活动开始时间
            public static function get_statime($actid,$userid)
            {
                $sql = "select `statime`,`endtime` from `act_foot`  where actid='{$actid}' && userid='{$userid}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryOne($sql);
                return $result;
            }
             //写入徒步活动
            public static function put_foot($actid,$userid,$tro,$time)
            {
                $sql = "insert into `act_foot`(`actid`, `userid`,`tro`,`statime`) values ('{$actid}','{$userid}','{$tro}','{$time}')";
                $conn = DbConn::getInstance();
                $result = $conn->exec($sql);
                return $result;
            }
             //更新徒步活动
            public static function up_foot($actid,$userid,$result,$time)
            {
                $sql = "update `act_foot` set `endtime`='{$time}',`result`='{$result}' where actid='{$actid}' && userid='{$userid}'";
                $conn = DbConn::getInstance();
                $result = $conn->exec($sql);
                return $result;
            }
             //获取徒步活动时间以队伍统计
            public static function get_trosum($actid,$tro)
            {
                $sql = "select sum(result) as sum from `act_foot`  where actid='{$actid}' && tro='{$tro}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryOne($sql);
                return $result;
            }
             //增加队伍分值
            public static function put_ranking($sum,$actid,$tro)
            {
                $sql = "update `act_ranking` set `total`=`total`+'{$sum}' where actid='{$actid}' && troid='{$tro}'";
                $conn = DbConn::getInstance();
                $result = $conn->exec($sql);
                return $result;
            }
            //获取活动设定积分值
            public static function get_footscore($actid)
            {
                $sql = "select footscore,footlock from `act_info`  where id='{$actid}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryOne($sql);
                return $result;
            }
            //更改活动表footlock状态
            public static function up_actfootlock($actid,$footlock)
            {
                $sql = "update `act_info` set `footlock`='{$footlock}' where id='{$actid}'";
                $conn = DbConn::getInstance();
                $result = $conn->exec($sql);
                return $result;
            }
             //输出活动用户并按签到排序
            public static function get_paysignuser($actid)
            {
                $sql = "SELECT  `userid`,t1.nickname,headimgurl,t1.phone,t1.actsign FROM `act_payorder` as t1 inner join user_info as t2 on t1.userid=t2.openid WHERE t1.actid='{$actid}' order by t1.actsign asc";
                $conn = DbConn::getInstance();
                $result = $conn->queryAll($sql);
                return $result;
            }
            //更改活动打分次数
            public static function up_marknum($actid)
            {
                $sql = "update `act_info` set `marknum`=marknum+1 where id='{$actid}'";
                $conn = DbConn::getInstance();
                $result = $conn->exec($sql);
                return $result;
            }
            //更改活动管理员设定分值
            public static function up_setvalue($actid,$setvalue)
            {
                $sql = "update `act_info` set `setvalue`='{$setvalue}' where id='{$actid}'";
                $conn = DbConn::getInstance();
                $result = $conn->exec($sql);
                return $result;
            }
            //更改活动徒步奖励分值
            public static function up_footscore($actid,$footscore)
            {
                $sql = "update `act_info` set `footscore`='{$footscore}' where id='{$actid}'";
                $conn = DbConn::getInstance();
                $result = $conn->exec($sql);
                return $result;
            }
            //获取活动地址标题
            public static function get_title($actid)
            {
                $sql = "select title,address from act_info as t1 inner join act_lbs as t2 on t1.id=t2.actid  where t1.id='{$actid}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryOne($sql);
                return $result;
            }
            //获取签到队伍人数
            public static function get_sumsign($actid,$troops)
            {
                $sql = "select count(*) as count from act_signtime where actid='{$actid}' && troops='{$troops}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryOne($sql);
                return $result;
            }
            //获取签到队伍人数
            public static function get_sumuser($actid,$troops)
            {
                $sql = "select userid from act_signtime where actid='{$actid}' && troops='{$troops}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryAll($sql);
                return $result;
            }
             //读出用户用户名和所属队伍
            public static function get_qianuser($actid,$openid)
            {
                    $sql = "SELECT nickname,troops FROM act_signtime as t1 inner join user_info as t2 on t1.userid=t2.openid  WHERE t1.actid ='{$actid}' && t1.userid='{$openid}'"; 
                    $conn = DbConn::getInstance(); 
                    $result = $conn->queryOne($sql);
                    return $result;
            }
            //活动管理状态
            public static function get_actsta($actid)
            {
                $sql = "select adminsta,marknum,setvalue from act_info where id='{$actid}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryOne($sql);
                return $result;
            }
            //更新报名活动列表状态
            public static function update_actjoin($id)
            {
                $sql = "update act_apply set display = 0 where id = '{$id}'";
                $conn = DbConn::getInstance();
                $result = $conn->exec($sql);
                return $result;   
            }

        }
        //商品
        class Goods
        {
            //获取商品分类
            public static function get_goodstype()
            {
                $sql = "SELECT `id`,`tname`,`fonticon`,`color`,`f_id` FROM wechat.goods_type"; 
                $conn = DbConn::getInstance();
                $result = $conn->queryAll($sql);//查询子类
                return $result; 
            }  
            //读取全部商品列表
            public static function get_goods()
            {
                $sql = "SELECT `id`, `gid`, `goods_name`, `abs`, `imgurl`, `cid`, `total`, `time` FROM wechat.goods";
                $conn = DbConn::getInstance();
                $result = $conn->queryAll($sql);
                return $result;
            }
            //读取分类下的商品列表
            public static function get_typegoods($gtypeid)
            {
                $sql = "SELECT `id`, `gid`, `goods_name`, `abs`, `imgurl`, `cid`, `total`, `time` FROM wechat.goods WHERE gid='{$gtypeid}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryAll($sql);
                return $result;
            }

            //读取新品商品
            public static function get_typegoods_new($num)
            {
                $sql = "SELECT `id`, `goods_name`, `abs`, `imgurl`, `total`, `time`,`count` FROM wechat.goods order by `time` desc LIMIT {$num}";
                $conn = DbConn::getInstance();
                $result = $conn->queryAll($sql);
                return $result;
                // print($sql);
            }
            //读取商品id下的图片
            public static function get_gimg($goodsid)
            {
                $sql = "SELECT `url` FROM wechat.goods_img WHERE  goodsid='{$goodsid}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryAll($sql);
                return $result;
            }

            //读取商品id下的信息
            public static function get_ginfo($goodsid)
            {
                $sql = "SELECT `goods_name`, `abs`, `total`, `goods_number`, `count`,`imgurl` FROM wechat.goods WHERE  id='{$goodsid}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryOne($sql);
                return $result;
            }

            //读取商品id下的详情
            public static function get_gcontent($goodsid)
            {
                $sql = "SELECT `content` FROM wechat.goods_content WHERE  goodsid='{$goodsid}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryOne($sql);
                return $result;
            }
            //读取热门商品
            public static function get_typegoods_hot($num)
            {
                $sql = "SELECT `id`, `goods_name`, `abs`, `imgurl`, `total`, `time` ,`count` FROM wechat.goods order by `count` desc LIMIT {$num}";
                $conn = DbConn::getInstance();
                $result = $conn->queryAll($sql);
                return $result;
            }
            //获取推荐商品
            public static function get_regoods() 
            {
                $sql = "SELECT * FROM goods WHERE is_recommend = 1";
                $conn = DbConn::getInstance();
                $result = $conn->queryAll($sql);
                return $result;    
            }
            //添加收货地址
            public static function add_address($user_id,$consignee,$province,$city,$district,$address,$zipcode,$tel,$default)
            {
                $conn = DbConn::getInstance();
                if($default)
                {
                  $sql1 = "update wechat.user_address set `default` = '0' where uid = '{$user_id}'";
                  $conn->exec($sql1);         
                }
                $sql = "INSERT INTO wechat.user_address (uid,consignee,province,city,district,address,zipcode,tel,`default`)values('{$user_id}','{$consignee}','{$province}','{$city}','{$district}','{$address}','{$zipcode}','{$tel}','{$default}')";
                $result = $conn->exec($sql);
                return $result;    

            }
            //编辑收货地址
            public static function edit_address($address_id,$user_id,$consignee,$province,$city,$district,$address,$zipcode,$tel,$default)
            {
                $conn = DbConn::getInstance();
                if($default)
                {
                  $sql1 = "update wechat.user_address set `default` = '0' where uid = '{$user_id}'";
                  $conn->exec($sql1);         
                }
                $sql = "update wechat.user_address set consignee = '{$consignee}',province = '{$province}',city = '{$city}',district = '{$district}',address = '{$address}',zipcode = '{$zipcode}',tel = '{$tel}',`default` = '{$default}' where address_id = '{$address_id}'";
                $result = $conn->exec($sql);
                return $result;
            }
            //删除收货地址
            public static function del_address($id)
            {
                $sql = "delete from wechat.user_address where address_id = '{$id}'";
                $conn = DbConn::getInstance();
                $result = $conn->exec($sql);
                return $result;
            }
            //更改默认地址
            public static function update_address($address_id,$user_id)
            {
                $sql = "update wechat.user_address set `default` = '0' where uid = '{$user_id}'";
                $sql1 = "update wechat.user_address set `default` = '1' where address_id = '{$address_id}'";
                $conn = DbConn::getInstance();
                $conn->exec($sql);
                $result = $conn->exec($sql1);  
                return $result;
 
            }
            //获取个人收货地址
            public static function get_address($user_id,$is_default)
            {
                if($is_default)
                {
                  $sql = "select * from wechat.user_address where uid = '{$user_id}' and `default` = '1'";    
                }
                else
                {
                  $sql = "select * from wechat.user_address where uid = '{$user_id}'";
                }
                $conn = DbConn::getInstance();
                $result = $conn->queryAll($sql);
                //var_dump($result);exit;  
                return $result;

            }
            //获取个人积分
            public static function get_winmoney($user_id)
            {
                $sql = "select winmoney from wechat.user_info where id = '{$user_id}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryOne($sql);
                return $result;   
            }
            //获取单条地址
            public static function get_oneaddress($id)
            {
                $sql = "select * from wechat.user_address where address_id = {$id}";
                $conn = DbConn::getInstance();
                $result = $conn->queryOne($sql);
                return $result;   
            }
            //获取订单详情
            public static function order_detail($order_id)
            {
                $sql = "select a.add_time,a.address,a.goods_amount,b.goods_number,b.goods_name,imgurl,c.abs,a.logistics_company,a.tracking_number,a.consignee,a.tel,c.total,c.id,a.shipping_status,order_status from wechat.goods_info as a left join wechat.goods_order as b on a.order_id = b.ord_id left join wechat.goods as c on b.goods_id = c.id where a.order_id = {$order_id}";
                $conn = DbConn::getInstance();
                $result = $conn->queryOne($sql);
                return $result;   
            }
            public static function order_over($order_id)
            {   
                $sql = "update wechat.goods_info set shipping_status = '2' where order_id = '{$order_id}'";
                $conn = DbConn::getInstance();
                $result = $conn->exec($sql);
                return $result; 
            }
            public static function apply_for_return($order_id)
            {
                $sql = "update wechat.goods_info set shipping_status = '5' where order_id = '{$order_id}'";
                $conn = DbConn::getInstance();
                $result = $conn->exec($sql);
                return $result;   
            }
            //添加订单
            public static function add_orders($order_sn,$user_id,$order_status,$shipping_status,$pay_status,$consignee,$tel,$province,$city,$district,$address,$goods_amount,$add_time,$zipcode,$goods_id,$goods_name,$goods_number,$send_num,$is_real,$goods_price)
            {   
                $validity = time()+7*24*3600;
                $sql = "INSERT INTO wechat.goods_info(order_sn,user_id,order_status,shipping_status,pay_status,consignee,tel,province,city,district,address,goods_amount,add_time,zipcode,validity)values('{$order_sn}','{$user_id}','{$order_status}','{$shipping_status}','{$pay_status}','{$consignee}','{$tel}','{$province}','{$city}','{$district}','{$address}','{$goods_amount}','{$add_time}','{$zipcode}','{$validity}')";
                $conn = DbConn::getInstance();
                $id = $conn->last_id($sql);
                $sql1 = "INSERT INTO wechat.goods_order(ord_id,goods_id,goods_name,goods_number,send_num,is_real,goods_price)values('{$id}','{$goods_id}','{$goods_name}','{$goods_number}','{$send_num}','{$is_real}','{$goods_price}')";
                $result = $conn->exec($sql1);
                return $result;
                   
            }
            //获取用户订单
            public static function get_orders($user_id)
            { 
                $sql = "select *,b.goods_number as goods_number from wechat.goods_info as a left join wechat.goods_order as b on a.order_id = b.ord_id LEFT JOIN wechat.goods AS c on b.goods_id = c.id where a.user_id = '{$user_id}' order by a.add_time desc";
                $conn = DbConn::getInstance();
                $result = $conn->queryAll($sql);
                return $result;
            }
            //更新订单状态
            public static function update_order($user_id,$goods_name,$total,$winmoney,$pay_mode)
            {
                $sql = "update wechat.goods_info set pay_status = '2',order_status = '1' where user_id = '{$user_id}' order by add_time desc limit 1";
                $conn = DbConn::getInstance();
                $result = $conn->exec($sql);
                return $result;
            }
            public static function cancel_order($order_sn)
            {
                $sql = "update wechat.goods_info set order_status = '2' where order_sn = '{$order_sn}'";
                $conn = DbConn::getInstance();
                $result = $conn->exec($sql);
                return $result;
            }
            public static function delete_order($order_sn)
            {
                $sql = "delete from wechat.goods_info where order_sn = '{$order_sn}'";
                $conn = DbConn::getInstance();
                $result = $conn->exec($sql);
                return $result;
            }
            //获取未收货订单
            public static function get_unfinished_orders($user_id)
            {
                $sql = "select * from wechat.goods_info as a left join wechat.goods_order as b on a.order_id = b.ord_id LEFT JOIN wechat.goods AS c on b.goods_id = c.id where b.is_real = 1 and a.pay_status = 2 and a.shipping_status = 1 and a.order_status != 2 and a.user_id = '{$user_id}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryAll($sql);
                return $result;   
            }
            //获取完成的订单
           public static function get_finished_orders($uid)
           {
                $sql = "select * from goods_info as a left join goods_order as b on a.order_id = b.ord_id LEFT JOIN goods AS c on b.goods_id = c.id where a.shipping_status = 2 and a.pay_status = 2 and a.order_status != 2 and a.user_id = '{$uid}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryAll($sql);
                return $result;
           }
           //获取退换货的订单
          public static function get_return_orders($user_id)
          {
                $sql = "select * from wechat.goods_info as a left join wechat.goods_order as b on a.order_id = b.ord_id LEFT JOIN wechat.goods AS c on b.goods_id = c.id where a.shipping_status BETWEEN 5 and 6 and a.pay_status = 2 and a.user_id = '{$user_id}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryAll($sql);
                return $result;
          }
          //更新微信支付的订单
          public static function update_order_status($order_sn)
          {
                $sql = "update wechat.goods_info set pay_status = '2',order_status = '1' where order_sn = '{$order_sn}'";
                $conn = DbConn::getInstance();
                $result = $conn->exec($sql);
                return $result;
          }
            
        }
        //app
        class App
        {  


            public static function user_news($user_id)
            {
                $sql = "(select * from wechat.ccl_rew WHERE fid IN (SELECT id FROM wechat.ccl_content WHERE uid = '{$user_id}') and display_center = '1')UNION(select * from wechat.ccl_rew where uid = '{$user_id}' and replier != '' and display_center = '1')";
                $conn = DbConn::getInstance();
                $result = $conn->queryAll($sql);
                return $result;
            }
            public static function user_rew_news($id)
            {
                $sql = "SELECT b.id,b.fid as ccl_id,a.nickname,b.content,a.headimgurl,b.time FROM wechat.user_info AS a LEFT JOIN wechat.ccl_rew AS b ON b.replier = a.id WHERE b.id = '{$id}' order by b.`time`";
                $conn = DbConn::getInstance();
                $result = $conn->queryOne($sql);
                return $result;
            }
            public static function user_content_news($id)
            {
                $sql = "SELECT b.id,b.fid as ccl_id,a.nickname,b.content,a.headimgurl,b.time FROM wechat.user_info AS a LEFT JOIN wechat.ccl_rew AS b ON b.uid = a.id WHERE b.id = '{$id}' order by b.`time`";
                $conn = DbConn::getInstance();
                $result = $conn->queryOne($sql);
                return $result;
            }
            public static function empty_ccl_news($id)
            {   
                $sql = "update wechat.ccl_rew set display_center = '0' where `id` in ({$id})";
                $conn = DbConn::getInstance();
                $result = $conn->exec($sql);
                return $result;   
            }
            public static function system_news($user_id)
            {
                $sql = "select * from user_news where user_id = '{$user_id}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryAll($sql);
                return $result;   
            }
            public static function empty_ccl_like($user_id)
            {
                $sql = "update wechat.ccl_like set display_center = '0' WHERE fid IN (SELECT id FROM wechat.ccl_content WHERE uid = '{$user_id}')";
                $conn = DbConn::getInstance();
                $result = $conn->exec($sql);
                return $result; 
            }
            public static function ccl_all_like($user_id)
            {
                $sql = "SELECT c.nickname,a.`status`,a.time,c.headimgurl,a.fid as ccl_id FROM wechat.ccl_like AS a left JOIN wechat.ccl_content AS b ON a.fid = b.id left join wechat.user_info as c on a.uid = c.id WHERE b.uid = '{$user_id}' and a.`status` = '1' and a.display_center = '1' order by `time` desc";
                //echo $sql;exit;
                $conn = DbConn::getInstance();
                $result = $conn->queryAll($sql);
                return $result;
            }
            public static function get_user_like($user_id)
            {
                $sql = "SELECT a.nickname,b.content,a.headimgurl FROM wechat.user_info AS a LEFT JOIN wechat.ccl_like AS b ON b.uid = a.id WHERE b.id = '{$id}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryOne($sql);
                return $result;
            }
            //获取注册信息时间
            public static function get_code($phone)
            {
                $sql = "SELECT `time`,`smscode` FROM `user_code` WHERE phone='{$phone}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryOne($sql);
                return $result;
            }
            //插入一注册信息数据
            public static function put_code($phonekey,$phone,$smscode,$time)
            {
                $sql = "INSERT INTO `user_code`(`phonekey`, `phone`, `smscode`, `time`) VALUES ('{$phonekey}','{$phone}','{$smscode}','{$time}')";
                $conn = DbConn::getInstance();
                $result = $conn->exec($sql);
                return $result;
            }
            //更新注册信息表信息
            public static function up_codetime($phone,$smscode,$time)
            {
                $sql = "update `user_code` set `phone`='{$phone}',`smscode`='{$smscode}',`time`='{$time}' where phone='{$phone}'";
                $conn = DbConn::getInstance();
                $result = $conn->exec($sql);
                return $result;
            }
            //插入一条用户数据
            public static function put_userinfo($phone,$password,$time)
            {
                $sql = "INSERT INTO `user_info`( `password`, `tel`, `regtime`) VALUES ('{$password}','{$phone}','{$time}')";
                $conn = DbConn::getInstance();
                $result = $conn->exec($sql);
                return $result;
            }
            //手机登录
            public static function login_user($phone,$password)
            {
                $sql = "SELECT `id` FROM `user_info` WHERE tel='{$phone}' && password='{$password}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryOne($sql);
                return $result;
            }
            //用户名登录
            public static function login_yuser($phone,$password)
            {
                $sql = "SELECT `id` FROM `user_info` WHERE id='{$phone}' && password='{$password}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryOne($sql);
                return $result;
            }
             //微信登录
            public static function login_wuser($openid)
            {
                $sql = "SELECT `id` FROM `user_info` WHERE openid='{$openid}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryOne($sql);
                return $result;
            }
            //根据手机号码获取用户数据 防止二次注册
            public static function get_usertel($phone)
            {
                $sql = "SELECT `id` FROM `user_info` WHERE tel='{$phone}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryOne($sql);
                return $result;
            }
            //10043
            public static function cll_list()
            {
                $sql = "select t1.id,t1.last_reply_at,t1.content,t1.thumb,t2.nickname,t2.headimgurl,t2.id as uid from wechat.ccl_content as t1 inner join wechat.user_info as t2 on t1.uid=t2.id where type = 2 and display_center = '1' and t1.is_recovery = '1' order by t1.id desc";
                $conn = DbConn::getInstance();
                $result = $conn->queryAll($sql);
                // $arr = $conn->cll_rewlist();
                // foreach($result as $k => $v)
                // {
                //      $result[$k]['comment'] = $arr[$k][] 
                // }              
                return $result;
            }
    
            //10044
            public static function cll_content($id)
            {
                $sql = "select t1.id,t1.last_reply_at,t1.content,t1.thumb,t2.nickname,t2.headimgurl,t2.id as uid from wechat.ccl_content as t1 inner join wechat.user_info as t2 on t1.uid=t2.id where t1.id='{$id}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryOne($sql);
                return $result;
            }
            public static function cll_rew($id)
            {
                $sql = "select t1.fid,t1.content,t1.time,t1.replier,t1.id as rew_id,t2.nickname,t2.headimgurl,t2.id as uid from wechat.ccl_rew as t1 inner join wechat.user_info as t2 on t1.uid=t2.id where t1.fid='{$id}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryAll($sql);
                
                foreach($result as $k => $v)
                {
                    
                     if($result[$k]['replier'])
                    {
                       
                       $sql1 = "select nickname,headimgurl,id from wechat.user_info where id = '{$result[$k]['replier']}'";
                       $res = $conn->queryOne($sql1);
                       $result[$k]['replier'] = $res;
                       
                    }
                
                }

                return $result;
            }
            public static function del_ccl_rew($rew_id)
            {
                $sql = "delete FROM wechat.ccl_rew WHERE id='{$rew_id}'";
                $conn = DbConn::getInstance();
                $result = $conn->exec($sql);
                return $result;
            }
            public static function del_ccl($id)
            {
                $sql = "update wechat.ccl_content set display_center = '0' WHERE id='{$id}'";
                $conn = DbConn::getInstance();
                $result = $conn->exec($sql);
                return $result;   
            }
            public static function user_ccl($user_id)
            {
                $sql = "select id,last_reply_at,content,thumb from wechat.ccl_content where uid = '{$user_id}' and display_center = '1' and is_recovery = '1' order by last_reply_at desc";
                $conn = DbConn::getInstance();
                $result = $conn->queryAll($sql);
                return $result;   
            }
            //点赞数，评论数，转发数
            public static function cll_count($cid)
            {
                $sql = "SELECT fid,sum(wrzm) wrzm,sum(mysh) mysh,sum(sgbh) sgbh FROM (

                SELECT  fid,count(*) wrzm,0 mysh,0 sgbh FROM wechat.ccl_like where status = '1'
                GROUP BY fid

                UNION ALL

                SELECT  fid,0 wrzm,count(*) mysh,0 sgbh FROM wechat.ccl_rew
                GROUP BY fid

                UNION ALL

                SELECT fid,0 wrzm,0 mysh,count(*) sgbh FROM wechat.ccl_smit 
                GROUP BY fid

                ) t WHERE fid = '{$cid}' GROUP BY fid";
                $conn = DbConn::getInstance();
                $result = $conn->queryOne($sql);
                return $result;
            }
            public static function like_member($id)
            {
                $sql = "SELECT c.nickname FROM wechat.ccl_content as a left join wechat.ccl_like AS b on a.id = b.fid  LEFT JOIN wechat.user_info AS c ON b.uid = c.id WHERE a.id = '{$id}' and b.status = '1' order by b.`time` asc";
                $conn = DbConn::getInstance();
                $result = $conn->queryAll($sql);
                return $result;
            }
            //10045
            public static function cll_put_list($userid,$content,$thumb,$time)
            {
                $sql = "INSERT INTO wechat.`ccl_content`(`uid`, `last_reply_at`,`content`,`thumb`,type) VALUES ('{$userid}','{$time}','{$content}','{$thumb}',2)";
                $conn = DbConn::getInstance();
                $result = $conn->exec($sql);
                return $result;
            }
            //10046
            public static function cll_put_rew($userid,$content,$id,$time)
            {
                $sql = "INSERT INTO wechat.`ccl_rew`(`uid`, `fid`,`content`,`time`) VALUES ('{$userid}','{$id}','{$content}','{$time}')";
                $conn = DbConn::getInstance();
                $result = $conn->exec($sql);
                return $result;
            }
            public static function cll_push_rew($user_id,$content,$id,$time,$replier)
            {
                $sql = "INSERT INTO wechat.`ccl_rew`(`uid`, `fid`,`content`,`time`,`replier`) VALUES ('{$user_id}','{$id}','{$content}','{$time}','{$replier}')";
                $conn = DbConn::getInstance();
                $result = $conn->exec($sql);
                return $result;   
            }
            //是否存在点赞
            public static function like_why($user_id,$id)
            {   
                $sql = "SELECT `status` FROM wechat.`ccl_like` WHERE fid='{$id}' && uid='{$user_id}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryOne($sql);
                return $result;
            }
            //更改点赞状态
            public static function cll_up_like($user_id,$id,$status)
            {   
                $time = time();
                $sql = "UPDATE wechat.`ccl_like` SET `status`='{$status}',`display_center` = '1',`time` = '{$time}' WHERE fid='{$id}' && uid='{$user_id}'";
                $conn = DbConn::getInstance();
                $result = $conn->exec($sql);
                return $result;
            }
            //查看单条点赞数
            public static function cll_get_like($id)
            {
                $sql = "SELECT count(*) as sum FROM wechat.`ccl_like` WHERE fid='{$id}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryOne($sql);
                return $result;
            }
            //10047
            public static function cll_put_like($user_id,$id,$time)
            {
                $sql = "INSERT INTO wechat.`ccl_like`(`uid`, `fid`,`time`) VALUES ('{$user_id}','{$id}','{$time}')";
                $conn = DbConn::getInstance();
                $result = $conn->exec($sql);
                return $result;
            }
            //获取学客圈用户id
            public static function get_cll_user($id)
            {
                $sql = "SELECT `uid` FROM wechat.`ccl_content` WHERE id='{$id}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryOne($sql);
                return $result;
            }

            public static function cll_get_rew($id)
            {
                $sql = "SELECT * FROM wechat.`ccl_rew` WHERE fid='{$id}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryAll($sql);
                return $result;
  
            }
        }

    class Master
    {
            //查询帐号密码是否存在
            public static function check_adminuandp($username,$password)
            {
                $sql = "SELECT * FROM  `admin_user` WHERE  `username` =  '{$username}'AND  `password` =  '{$password}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryOne($sql);
                return $result;
            }

            //写入用户登录时间、ip、token
            public static function updata_admintoken($id,$time,$ip,$token)
            {
                $sql = "UPDATE `admin_user` SET `last_login` = '{$time}',`last_ip` = '{$ip}',`token` = '{$token}' WHERE `id` = '{$id}'";
                $conn = DbConn::getInstance();
                $result = $conn->exec($sql);
                return $result;
            }

            //检查管理员token
            public static function check_admintoken($token)
            {
                $sql = "SELECT * FROM  `admin_user` WHERE  `token` =  '{$token}'";
                //var_dump($sql);exit;
                $conn = DbConn::getInstance();
                $result = $conn->queryOne($sql);
                return $result;
            }

            //取得任务令分类
            public static function get_admin_actcat($page,$id)
            {

                 $conn = DbConn::getInstance();
                 $str = empty($id)? '1=1' : "id = {$id}";
                 $sql1 = "SELECT * FROM act_cat where $str ";
                 $total = count($conn->queryAll($sql1));

                if($id)
                { 
                  $where = empty($page)? "id={$id} limit 0,7":"id={$id} limit {$page},7 ";  
                  $sql = "SELECT * FROM act_cat where $where ";

                }else
                {  
                   $where = empty($page)? "limit 0,7" : "limit {$page},7";
                   $sql = "SELECT * FROM act_cat $where";
                }
                $result = $conn->queryAll($sql);
                foreach($result as $k => $k)
                {     
                      $result[$k]['total'] = $total;
                } 
                return $result;
            }

            //添加任务令分类
            public static function add_admin_actcat($tname,$fonticon,$color,$catran)
            {
                $sql = "INSERT INTO  `act_cat` (`id` ,`f_id` ,`catname` ,`fonticon` ,`color` ,`catran`)VALUES (NULL ,  '0',  '{$tname}',  '{$fonticon}',  '{$color}',  '{$catran}');";
                $conn = DbConn::getInstance();
                $result = $conn->exec($sql);
                return $result;
            }

            //编辑任务令分类
            public static function edit_admin_actcat($id,$tname,$fonticon,$color,$catran)
            {
                $sql = "UPDATE  `act_cat` SET  `catname` =  '{$tname}',`fonticon` =  '{$fonticon}',`color` =  '{$color}',`catran` =  '{$catran}' WHERE  `id` ='{$id}';";
                $conn = DbConn::getInstance();
                $result = $conn->exec($sql);
                return $result;
            }

            //删除任务令分类
            public static function del_admin_actcat($id)
            {
                $sql = "DELETE FROM `act_cat` where `id` in({$id});";
                $conn = DbConn::getInstance();
                $result = $conn->exec($sql);
                return $result;
            }

            //发布任务
            public static function add_admin_actinfo($title,$catid,$actdesc, $actimgurl,$actcode,$crocode,$masterid,$mastername,$masterphone,$actstarttime,$actendtime,$joinstarttime,$joinendtime,$userlimit,$deposit,$actsta,$adminsta,$time,$lockstatus,$count,$content,$longitude,$latitude,$address,$arr,$mapurl)
            {
               $sql = "INSERT INTO  `act_info` (`title` ,`catid` ,`actdesc` ,`actimgurl` ,`actcode` ,`crocode`,`masterid`,`mastername`,`masterphone`,`actstarttime`,`actendtime`,`joinstarttime`,`joinendtime`,`userlimit`,`deposit`,`actsta`,`adminsta`,`time`,`lockstatus`,`count`)VALUES ('{$title}','{$catid}','{$actdesc}','{$actimgurl}','{$actcode}','{$crocode}','{$masterid}','{$mastername}','{$masterphone}','{$actstarttime}','{$actendtime}','{$joinstarttime}','{$joinendtime}','{$userlimit}','{$deposit}','{$actsta}','{$adminsta}','{$time}','{$lockstatus}','{$count}');";
                //var_dump($sql);exit;
                $conn = DbConn::getInstance();
                $result = $conn->exec($sql);
               
                //最新活动id
                $actid = Act::get_actid();
                $id= $actid['actid'];
                //同时对act_content、act_lbs表进行插入操作
                $sql1 = "INSERT INTO  `act_lbs` (`actid` ,`address` ,`latitude` ,`longitude` ,`precision` ,`mapurl`)VALUES ('{$id}','{$address}','{$latitude}','{$longitude}','0','{$mapurl}');"; //precison和mapurl 的值暂时设为空，必要时可更改
                $sql2 = "INSERT INTO  `act_content` (`actid` ,`content`)VALUES ('{$id}','{$content}');";
                if(is_array($arr))
                {
                   
                   foreach($arr as $k => $v)
                   {
                      $sql3 = "INSERT INTO  `act_job` (`f_id` ,`jobname`,`bonus`,`joblimit`)VALUES ('{$id}','{$arr[$k]['jobname']}','{$arr[$k]['bonus']}','{$arr[$k]['joblimit']}');" ;
                      $conn->exec($sql3);      
                   }
                
                }
                $conn->exec($sql1);
                $conn->exec($sql2);
                
                return $result;
            }
            
             //编辑任务
            public static function edit_admin_actinfo($id,$catid,$title,$actdesc, $actimgurl,$actcode,$crocode,$masterid,$mastername,$masterphone,$actstarttime,$actendtime,$joinstarttime,$joinendtime,$userlimit,$deposit,$actsta,$adminsta,$time,$lockstatus,$count,$content,$longitude,$latitude,$address,$arr,$mapurl)
            {
               $sql = "UPDATE  `act_info` as t1,`act_content` as t2,`act_lbs` as t3 SET t1.title = '{$title}' ,t1.catid = '{$catid}' ,t1.actdesc = '{$actdesc}' ,t1.actimgurl='{$actimgurl}' ,t1.actcode = '{$actcode}' ,t1.crocode = '{$crocode}', t1.masterid = '{$masterid}',t1.mastername='{$mastername}',t1.masterphone = '{$masterphone}',t1.actstarttime = '{$actstarttime}',t1.actendtime = '{$actendtime}',t1.joinstarttime = '{$joinstarttime}',t1.joinendtime = '{$joinendtime}',t1.userlimit = '{$userlimit}',t1.deposit = '{$deposit}',t1.actsta = '{$actsta}',t1.adminsta = '{$adminsta}',t1.time = '{$time}',t1.lockstatus = '{$lockstatus}',t1.count = '{$count}',t2.content = '{$content}',t3.address = '{$address}',t3.longitude = '{$longitude}',t3.latitude = '{$latitude}',t3.mapurl = '{$mapurl}' WHERE t1.id = '{$id}' AND t2.actid = '{$id}' AND t3.actid = '{$id}'";
                
               
                $conn = DbConn::getInstance();
                $result = $conn->exec($sql);
                
                $sql2 = "SELECT id FROM act_job WHERE f_id = {$id}";
                $res = $conn->queryAll($sql2);
                
              
                if(is_array($arr))
                {
                   
                   foreach($arr as $k => $v)
                   {
                    
                      $sql3 = "UPDATE `act_job` SET `jobname` = '{$arr[$k]['jobname']}',`bonus` = '{$arr[$k]['bonus']}',`joblimit` = '{$arr[$k]['joblimit']}' WHERE `id` = '{$res[$k]['id']}' " ;
                      $str = $conn->exec($sql3);      
                     
                   }
                
                }
                

                if($result || $str)
                {
                    $result = 1;

                }else
                {
                    $result = 0;
                }
                

                return $result;
            }

            
            //删除任务
            public static function del_admin_actinfo($id)
            {
                $sql  = "DELETE FROM `act_info` where `id` in({$id});";
                $sql .= "DELETE FROM `act_content` where `actid` in({$id})";
                $sql .= "DELETE FROM `act_lbs` where `actid` in({$id})";
                $sql .= "DELETE FROM `act_job` where `f_id` in({$id})";
                $conn = DbConn::getInstance();
                $result = $conn->exec($sql);
                return $result;
            }
            
            //获取该任务令下的所有内容
            public static function get_admin_actinfo($id)
            {
                $sql = "SELECT t1.*,content,address,latitude,longitude,mapurl FROM act_info AS t1 LEFT JOIN act_lbs AS t2 ON t1.id = t2.actid LEFT JOIN act_content AS t3 ON t1.id = t3.actid WHERE t1.id = {$id}";
               
                $conn = DbConn::getInstance();
                $result = $conn->queryOne($sql);
                $sql1 = "SELECT bonus,jobname,joblimit FROM act_job WHERE f_id = {$id}"; 
                $str = $conn->queryAll($sql1);
                $result['job'] = $str;
                return $result;
            }

            //商品发布
            public static function add_admin_goods($gid,$goods_name,$abs,$imgurl,$total,$time,$goods_number,$is_real,$count,$content,$url)
            {
                $sql = "INSERT INTO `goods`(`gid`,`goods_name`,`abs`,`imgurl`,`total`,`time`,`goods_number`,`is_real`,`count`) VALUES ('{$gid}','{$goods_name}','{$abs}','{$imgurl}','{$total}','{$time}','{$goods_number}','{$is_real}','{$count}')";
                
                $conn = DbConn::getInstance();
                $id = $conn->last_id($sql);
                $sql1 = "INSERT INTO `goods_content`(`goodsid`,`content`) VALUES ('{$id}','{$content}');";
                $result = $conn->exec($sql1);
                
                //多张图片单独进行操作
                foreach($url as $k => $v)
                   {
                      $sql2 = "INSERT INTO  `goods_img` (`goodsid` ,`rank`,`url`)VALUES ('{$id}',0,'{$url[$k]}');" ;
                      $conn->exec($sql2);      
                   }

                return $result; 
            }

            //商品删除
            public static function del_admin_goods($id)
            {
                $sql  = "DELETE t1,t2,t3 FROM goods AS t1 LEFT JOIN goods_content AS t2 ON t1.id = t2.goodsid LEFT JOIN goods_img AS t3 ON t1.id = t3.goodsid WHERE t1.id in ({$id})";
                $conn = DbConn::getInstance();
                $result = $conn->exec($sql);
                return $result;
            }

            //提交商品编辑
            public static function edit_admin_goods($id,$gid,$goods_name,$abs,$imgurl,$total,$time,$goods_number,$is_real,$url,$content,$count)
            {
                  $count = $count;
                 
                 $sql = "UPDATE `goods` as t1,`goods_content` as t2 SET t1.gid = '{$gid}', t1.goods_name = '{$goods_name}',t1.count = '{$count}',t1.abs = '{$abs}',t1.imgurl = '{$imgurl}',t1.total = '{$total}',t1.time = '{$time}',t1.goods_number = '{$goods_number}',t1.is_real = '{$is_real}',t2.content = '{$content}' WHERE t1.id = '{$id}' and t2.goodsid = '{$id}'";
                
                $conn = DbConn::getInstance();
                   
                 foreach($url['0'] as $k => $v)
                   {
                      $sql2 = "UPDATE `goods_img` SET  `url` = '{$url['0'][$k]}' WHERE goodsid = '{$id}';"; 
                      $conn->exec($sql2);      
                   }
              
                
                $result = $conn->exec($sql);
                return $result;
                
            }

            //获取单个商品信息
            public static function get_admin_goodsdetail($id)
            {
                $sql = "SELECT t1.*,t2.content ,t3.tname FROM goods AS t1 LEFT JOIN goods_content AS t2 ON t1.id = t2.goodsid LEFT JOIN goods_type as t3 ON t1.gid = t3.id WHERE t1.id = {$id}";
                $conn = DbConn::getInstance();
                $result = $conn->queryOne($sql);
                $sql1 = "SELECT url FROM goods_img WHERE goodsid = {$id}"; 
                $str = $conn->queryAll($sql1); 
                $result['url'] = $str;
                return $result;
            }

            //获取所有商品
            public static function get_admin_goods($page,$gid)
            {    
                 $conn = DbConn::getInstance();
                 $str = empty($gid)? '' : "and gid = {$gid}";
                 $sql1 = "SELECT * FROM goods where is_recovery = '1' $str ";
                 $total = count($conn->queryAll($sql1));

                if($gid)
                { 
                  $where = empty($page)? "and gid={$gid} limit 0,7":"and gid={$gid} limit {$page},7";  
                  $sql = "SELECT * FROM goods where is_recovery = '1' $where ";

                }else
                {  
                   $where = empty($page)? "limit 0,7" : "limit {$page},7";
                   $sql = "SELECT * FROM goods where is_recovery = '1' $where";
                }
                $result = $conn->queryAll($sql);

                foreach($result as $k => $k)
                {     
                      $sql = "SELECT tname FROM goods_type WHERE id = '{$result[$k]['gid']}'";
                      $res = $conn->queryOne($sql);
                      $result[$k]['tname'] = $res['tname']; 
                      $result[$k]['num'] = count($result);
                      $result[$k]['total'] = $total;
                }
               
                return $result;
            }
            //商品管理结束


            //会员订单管理
            //获取会员订单
            public static function get_admin_orders($page,$order_sn)
            {
            
                $conn = DbConn::getInstance();
                $str = empty($order_sn)? '1=1' : "order_sn = '{$order_sn}'";
                $sql1 = "SELECT * FROM goods_info where $str ";
               
                $total = count($conn->queryAll($sql1));

               
                if($order_sn)
                { 
                  $where = empty($page)? "order_sn = '{$order_sn}' limit 0,7":"order_sn = {$order_sn}' limit {$page},7 ";  
                  $sql = "SELECT * FROM goods_info where $where ";

                }else
                {  
                   $where = empty($page)? "limit 0,7" : "limit {$page},7";
                   $sql = "SELECT * FROM goods_info $where";
                }
                
                $result = $conn->queryAll($sql);
                foreach($result as $k => $k)
                {     
                      $result[$k]['total'] = $total;
                }
               
                return $result;
            }

            //添加会员订单
            public static function add_admin_orders($order_sn,$user_id,$order_status,$shipping_status,$pay_status,$consignee,$tel,$province,$city,$district,$address,$goods_amount,$add_time,$zipcode)
            {
                 $sql = "INSERT INTO goods_info(`order_sn`,`user_id`,`order_status`,`shipping_status`,`pay_status`,`consignee`,`tel`,`province`,`city`,`district`,`address`,`goods_amount`,`add_time`,`zipcode`)VALUES('{$order_sn}','{$user_id}','{$order_status}','{$shipping_status}','{$pay_status}','{$consignee}','{$tel}','{$province}','{$city}','{$district}','{$address}','{$goods_amount}','{$add_time}','{$zipcode}')";
                 $conn = DbConn::getInstance();
                 $id = $conn->last_id($sql);

            }
            //删除订单
            public static function del_admin_orders($id)
            {
                $sql  = "DELETE t1,t2 FROM goods_info AS t1 LEFT JOIN goods_order AS t2 ON t1.order_id = t2.ord_id  WHERE t1.order_id in ({$id})";
                $conn = DbConn::getInstance();
                $result = $conn->exec($sql);
                return $result;
            }

            //获取广告列表
            public static function get_admin_adv($page,$ad_type)
            {
                 $conn = DbConn::getInstance();
                 $str = empty($ad_type)? '1 = 1' : " ad_type = {$ad_type}";
                 $sql1 = "SELECT * FROM ad_home where $str";
                 $total = count($conn->queryAll($sql1));


                if($ad_type)
                { 
                  $where = empty($page)? " ad_type={$ad_type} limit 0,7":"ad_type={$ad_type} limit {$page},7 ";  
                  $sql = "SELECT * FROM ad_home where $where ";

                }else
                {  
                   $where = empty($page)? "limit 0,7" : "limit {$page},7";
                   $sql = "SELECT * FROM ad_home $where";
                }
                 
                $result = $conn->queryAll($sql);
                foreach($result as $k => $k)
                {     
                      $result[$k]['total'] = $total;
                }
                return $result;

            }
            //添加广告
            public static function add_admin_adv($title,$imgurl,$link,$lockstatus,$time,$sork,$ad_type)
            {
                $sql  = "INSERT INTO ad_home(`title`,`imgurl`,`link`,`lockstatus`,`time`,`sork`,`ad_type`)VALUES('{$title}','{$imgurl}','{$link}','{$lockstatus}','{$time}','{$sork}','{$ad_type}')";
                $conn = DbConn::getInstance();
                $result = $conn->exec($sql);
                return $result;
            }
            //编辑广告
            public static function edit_admin_adv($id,$title,$imgurl,$link,$lockstatus,$time,$sork,$ad_type)
            {
                $sql  = "UPDATE ad_home SET `title` = '{$title}',`imgurl` = '{$imgurl}',`link` = '{$link}',`lockstatus` = '{$lockstatus}', `time` = '{$time}',`sork` = '{$sork}',`ad_type` = '{$ad_type}' WHERE id = {$id}";
                $conn = DbConn::getInstance();
                $result = $conn->exec($sql);
                return $result;
            }
            //获取单个广告
            public static function get_admin_oneadv($id)
            {
                $sql  = "SELECT * FROM ad_home WHERE id = {$id}";
                $conn = DbConn::getInstance();
                $result = $conn->queryOne($sql);
                return $result;
            }
            //删除广告
            public static function del_admin_adv($id)
            {
                $sql  = "DELETE  FROM ad_home WHERE id in ({$id})";
                $conn = DbConn::getInstance();
                $result = $conn->exec($sql);
                return $result;
            }

            //获取学客圈列表
            public static function get_admin_ccl($page,$uid)
            {   
                 $conn = DbConn::getInstance();
                 $str = empty($uid)? '' : "and uid = {$uid}";
                 $sql1 = "SELECT * FROM ccl_content where status = '1'and is_recovery = '1' $str ";
                 
                 $total = count($conn->queryAll($sql1));

                if($uid)
                { 
                  $where = empty($page)? "and uid={$uid} limit 0,7":"and uid={$uid} limit {$page},7 ";  
                  $sql = "SELECT * FROM ccl_content where status = '1'and is_recovery = '1' $where ";

                }else
                {  
                   $where = empty($page)? "limit 0,7" : "limit {$page},7";
                   $sql = "SELECT * FROM ccl_content where status = '1'and is_recovery = '1' $where";
                }
                $result = $conn->queryAll($sql);
                foreach($result as $k => $k)
                {     
                      $result[$k]['total'] = $total;
                }
                return $result;

            }
            //删除学客圈
            public static function del_admin_ccl($id)
            {
                $sql  = "DELETE  FROM ccl_content WHERE id in ({$id})";
                $conn = DbConn::getInstance();
                $result = $conn->exec($sql);
                return $result;
            }            
            //获取会员列表
            public static function get_admin_userinfo($page,$name)
            {
                
                $conn = DbConn::getInstance();
                $str = empty($name)? '1=1' : "nickname like '%{$name}%'";
                $sql1 = "SELECT * FROM user_info where $str ";
                $total = count($conn->queryAll($sql1));
                
                if($name)
                { 
                  $where = empty($page)? "nickname like '%{$name}%' limit 0,7":"nickname like '%{$name}%' limit {$page},7 ";  
                  $sql = "SELECT * FROM user_info where $where ";

                }else
                {  
                   $where = empty($page)? "limit 0,7" : "limit {$page},7";
                   $sql = "SELECT * FROM user_info $where";
                }
                
                $result = $conn->queryAll($sql);
                foreach($result as $k => $k)
                {     
                      $result[$k]['total'] = $total;
                }
               
                return $result;
            }
            public static function edit_admin_winmoney($id,$winmoney)
            {
                $conn = DbConn::getInstance();
                $sql = "UPDATE user_info SET winmoney = {$winmoney} WHERE id = {$id}";
                $result = $conn->exec($sql);
                return $result; 
            }

            //进入回收站

            public static function recovery_admin($id,$tabname)
            {
                 $sql = "UPDATE {$tabname} SET `is_recovery` = '0' where `id`in ({$id})";
                 $conn = DbConn::getInstance();
                 $result = $conn->exec($sql);
                 return $result;
            }

            //显示商品回收站列表
            public static function get_recovery_goods($page,$gid)
            {
                 $conn = DbConn::getInstance();
                 $str = empty($gid)? '' : "and gid = {$gid}";
                 $sql1 = "SELECT * FROM goods where is_recovery = '0' $str ";
                 $res = count($conn->queryAll($sql1));

                if($gid)
                { 
                  $where = empty($page)? "and gid={$gid} limit 0,7":"and gid={$gid} limit {$page},7";  
                  $sql = "SELECT * FROM goods where is_recovery = '0' $where ";

                }else
                {  
                   $where = empty($page)? "limit 0,7" : "limit {$page},7";
                   $sql = "SELECT * FROM goods where is_recovery = '0' $where";
                }
                $result = $conn->queryAll($sql);

                foreach($result as $k => $k)
                {     
                      $sql = "SELECT tname FROM goods_type WHERE id = '{$result[$k]['gid']}'";
                      $res = $conn->queryOne($sql);
                      $result[$k]['tname'] = $res['tname']; 
                      $result[$k]['num'] = count($result);
                      $result[$k]['res'] = $res;
                }
                return $result;
            }
            
            //显示任务令回收站列表
            public static function get_recovery_act($page,$title)
            {
                 $conn = DbConn::getInstance();
                 $str = empty($title)? '' : "and a.title like '%{$title}%'";
                 $sql1 = "SELECT a.id,`title`, `time`,`catname` from act_info as a join act_cat as b on a.catid=b.id where a.lockstatus=0 and is_recovery = '0' $str ";
                 $total = count($conn->queryAll($sql1));


                if($title)
                { 
                  $where = empty($page)? "and a.id like '%{$title}%' limit 0,7":"and a.id like '%{$title}%' limit {$page},7";  
                  $sql = "SELECT a.id,`title`, `time`,`catname` from act_info as a join act_cat as b on a.catid=b.id where a.lockstatus=0 and is_recovery = '0' $where ";

                }else
                {  
                   $where = empty($page)? "limit 0,7" : "limit {$page},7";
                   $sql = "SELECT a.id,`title`, `time`,`catname` from act_info as a join act_cat as b on a.catid=b.id where a.lockstatus=0 and is_recovery = '0' $where";
                }
                $result = $conn->queryAll($sql);

                foreach($result as $k => $k)
                {     
                      $result[$k]['total'] = $total;
                }
                    
                    return $result; 
            }
            //显示学客圈回收站列表  
            public static function get_recovery_ccl($page,$uid)
            {
                 $conn = DbConn::getInstance();
                 $str = empty($uid)? '' : "and uid = {$uid}";
                 $sql1 = "SELECT * FROM ccl_content where status = '1'and is_recovery = '0' $str ";
                 
                 $total = count($conn->queryAll($sql1));

                if($uid)
                { 
                  $where = empty($page)? "and uid={$uid} limit 0,7":"and uid={$uid} limit {$page},7 ";  
                  $sql = "SELECT * FROM ccl_content where status = '1'and is_recovery = '0' $where ";

                }else
                {  
                   $where = empty($page)? "limit 0,7" : "limit {$page},7";
                   $sql = "SELECT * FROM ccl_content where status = '1'and is_recovery = '0' $where";
                }
                $result = $conn->queryAll($sql);
                foreach($result as $k => $k)
                {     
                      $result[$k]['total'] = $total;
                }
                return $result;
            }  
            //还原回收站列表
            public static function recover_admin($id,$tabname)
            {
                 $sql = "UPDATE {$tabname} SET `is_recovery` = '1' where `id` in ({$id})";
                 $conn = DbConn::getInstance();
                 $result = $conn->exec($sql);
                 return $result;   
            }
            
    
    }