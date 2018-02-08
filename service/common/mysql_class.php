<?php
     

    //数据库封装类
    class DbConn
    {
        private $conn = NULL;//连接对象
        
        // //连接数据库 loachost
        private function __construct()
        { 

            $url = "mysql:host=localhost;dbname=wechat";
            $user = "wechat";
            $pwd = "005381406";
            $this->conn = new PDO($url,$user,$pwd);
            $this->conn->query("set names utf8");
        }
        //连接数据库 server
        // private function __construct()
        // { 

        //     $url = "mysql:host=localhost;dbname=wechat";
        //     $user = "wechat";
        //     $pwd = "005381406";
        //     $this->conn = new PDO($url,$user,$pwd);
        //     $this->conn->query("set names utf8");
        // }
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
            $result = $this->conn;
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
        //通过openid查询用户是否存在
        public static function getOpenid($openid)
        {
            $sql = "select * from user_info where openid='{$openid}'";
            $conn = DbConn::getInstance();
            $result = $conn->queryOne($sql);
            return $result;
        }
        //插入一条用户信息
        public static function addUser($openid,$nickname,$sex,$city,$country,$province,$headimgurl,$regtime)
        {
            $sql = "insert into user_info(openid,nickname,sex,city,country,province,headimgurl,regtime)values('{$openid}','{$nickname}',{$sex},'{$city}','{$country}','{$province}','{$headimgurl}',{$regtime})";
            $conn = DbConn::getInstance();
            $result = $conn->exec($sql);
            return $result;
        }
        //更改学分
        public static function up_winmoney($openid,$total)
        {
            $sql = "update `user_info` set `winmoney`='{$total}' where openid='{$openid}'";
            $conn = DbConn::getInstance();
            $result = $conn->exec($sql);
            return $result;
        }
        //增加学分
        public static function up_uwinmoney($userid,$total)
        {
            $sql = "update `user_info` set `winmoney`=winmoney+'{$total}' where id='{$userid}'";
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
        //为代理商增加收益(学令牌)
        public static function add_agent_earnings($user_id,$money,$pid,$top_pid,$upper_limit)
        {
            $conn = DbConn::getInstance();
            $sql = "update xlp_app.`earnings_incorrect` set `investment`=investment+'{$money}',`upper_limit` = '{$upper_limit}' where user_id='{$user_id}';update xlp_app.`earnings_incorrect` set `investment`=investment+'{$money}'*0.38 where user_id='{$pid}';update xlp_app.`earnings_incorrect` set `investment`=investment+'{$money}'*0.12 where user_id='{$top_pid}'";
            //echo $sql;exit;
            $result = $conn->exec($sql);
            return $result;
        }
        //查看三级代理关系
        public static function check_agent_relation($user_id)
        {
           $sql = "select pid,top_pid,upper_limit,investment from xlp_app.earnings_incorrect where user_id = '{$user_id}'";
           $conn = DbConn::getInstance();
           $result = $conn->queryOne($sql);
           return $result;   
        }
        //记录操作(学令牌)
        public static function winmoney_log($log,$user_id,$credit,$type='',$actid='')
        {
            $conn = DbConn::getInstance();
            $shouzhi = $credit;
            $log_time = time();
            $sql  = "INSERT INTO xlp_app.`winmoney_log` (`user_id`,`log`,`shouzhi`,`log_time`,`type`,`actid`)VALUES('{$user_id}','{$log}','{$shouzhi}','{$log_time}','{$type}','{$actid}')";
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
            public static function get_job_user($jobid,$get)
            {   
                if($get)
                {
                    $get = 'limit 0,'. $get;
                }
                else
                {
                    $get = '';
                }
                $sql = "select b.nickname,b.headimgurl,b.openid,a.poll,a.id as job_user_id from act_job_user as a inner join user_info as b on a.user=b.openid where a.f_id='{$jobid}' order by a.poll desc $get";
                //$sql = "select a.user,a.poll,a.id as job_user_id from act_job_user as a where a.f_id='{$jobid}' order by a.poll desc $get";
                $conn = DbConn::getInstance();
                $result = $conn->queryAll($sql);
                return $result;
            }
            //查询用户是否竞选成功
            public static function check_user_success($openid,$actid)
            {
                $sql = "select * from act_job_user where actid='{$actid}' and user = '{$openid}' and job_success = 1";
                $conn = DbConn::getInstance();
                $result = $conn->queryOne($sql);
                return $result;

            }
            //为成功者标记状态
            public static function job_success($id)
            {
                $sql = "update act_job_user set job_success = 1 where id = '{$id}'";
                $conn = DbConn::getInstance();
                $result = $conn->exec($sql);
                return $result;

            }
            //获取竞选失败者
            public static function get_unsuccessful_user($actid)
            {
                $sql = "select a.user,b.jobname from act_job_user as a left join act_job as b on a.f_id = b.id where actid = '{$actid}' and job_success = 0";
                $conn = DbConn::getInstance();
                $result = $conn->queryAll($sql);
                return $result;

            }
            //判断是否是队长
            public static function check_captain($userid,$actid,$tem)
            {
                $sql = "select * from act_signtime where userid = '{$userid}' and actid = '{$actid}' and troops = '{$tem}' and captain = 1";
                $conn = DbConn::getInstance();
                $result = $conn->queryOne($sql);
                return $result;

            }
            //成为队长
            public static function up_captain($actid,$openid,$tem)
            {
                $conn = DbConn::getInstance();
                $sql1 = "update act_signtime set captain = 0 where actid = '{$actid}' and troops = '{$tem}';";
                $conn->exec($sql1);
                $sql  = "update act_signtime set captain = 1 where actid = '{$actid}' and troops = '{$tem}' and userid = '{$openid}'";
                $result = $conn->exec($sql);
                return $result;

            }
            //队伍成员(队伍打分界面)
            public static function get_trem_member($actid,$tem)
            {
                $sql = "SELECT b.id, c.nickname, b.headimgurl,e.jobname FROM act_signtime AS a LEFT JOIN user_info AS b ON a.userid = b.openid LEFT JOIN act_payorder AS c ON c.userid = a.userid AND a.actid = c.actid LEFT JOIN act_job_user as d ON a.userid = d.`user` and a.actid = d.actid and d.job_success = 1 LEFT JOIN act_job as e ON d.f_id = e.id  WHERE a.actid = '{$actid}' AND a.troops = '{$tem}' AND captain = 0";
                $conn = DbConn::getInstance();
                $result = $conn->queryAll($sql);
                return $result;
            }
            //查看队伍成员
            public static function check_trem_member($actid,$tem)
            {
                $sql = "SELECT b.id, c.nickname, b.headimgurl,e.jobname FROM act_signtime AS a LEFT JOIN user_info AS b ON a.userid = b.openid LEFT JOIN act_payorder AS c ON c.userid = a.userid AND a.actid = c.actid LEFT JOIN act_job_user as d ON a.userid = d.`user` and a.actid = d.actid and d.job_success = 1 LEFT JOIN act_job as e ON d.f_id = e.id  WHERE a.actid = '{$actid}' AND a.troops = '{$tem}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryAll($sql);
                return $result;

            }


            //获取队伍成员userid
            public static function get_trem_userid($tem,$actid,$sum)
            {
                $sql = "select userid from act_signtime where troops = '{$tem}' and actid = '{$actid}'";
                $sql1 = "update `act_ranking` set `total`=`total`+'{$sum}' where actid='{$actid}' && troid='{$tem}'";
                $conn = DbConn::getInstance();
                $conn->exec($sql1);
                $result = $conn->queryAll($sql);
                return $result;

            }
            //获取活动队长userid
            public static function check_act_capatin($actid)
            {
                
                $sql = "select userid,troops from act_signtime where actid = '{$actid}' and captain = '1'";
                $conn = DbConn::getInstance();
                $result = $conn->queryAll($sql);
                return $result;
            }
            //读取队伍分值
            public static function get_temtotal($actid,$tem)
            {
                $sql = "select total from act_ranking where actid = '{$actid}' and troid = '{$tem}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryOne($sql);
                return $result;

            }
            //获取队伍成员个数
            public static function get_member_num($actid,$tem)
            {
                $sql = "select count(id )as count from act_signtime where actid = '{$actid}' and troops = '{$tem}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryOne($sql);
                return $result;
            }
            //获取队长详细信息
            public static function get_captain_info($tem,$actid)
            {
                $sql = "select * from act_signtime where actid = '{$actid}' and troops = '{$tem}' and captain = 1";
                $conn = DbConn::getInstance();
                $result = $conn->queryOne($sql);
                return $result;

            }
            public static function get_jobname($actid)
            {
                $sql = "select * from act_job where f_id = '{$actid}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryAll($sql);
                return $result;    
            }
            public static function up_ready_tream($troid,$id)
            {
               $sql = "update act_job_user set `ready_tream` = '{$troid}' where id = {$id}";
               $conn = DbConn::getInstance();
               $result = $conn->exec($sql);
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
                $result = $conn->last_id($sql);
                return $result;
            }

            //根据活动id 查询用户是否报名活动角色
            public static function job_check_user($actid,$openid)
            {
                $sql = "select * from act_job_user where actid='{$actid}' && user='{$openid}' and job_success = 1";
                $conn = DbConn::getInstance();
                $result = $conn->queryOne($sql);
                return $result;
            }

            //根据活动id 查询活动角色信息
            public static function act_get_job($actid)
            {
                $sql = "select id,bonus,jobname,joblimit,job_surplus from act_job where f_id='{$actid}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryAll($sql);
                return $result;
            }

            //查询活动信息
            public static function get_act()
            {
                $sql = "select actsta,t1.id,title,actimgurl,actdesc,actstarttime,actendtime,address,count(userid)as countuser,userlimit  from (act_info as t1 left join act_lbs as t2 on t1.id=t2.actid)  left join act_payorder as t3 on (t1.id=t3.actid) where t1.lockstatus=0 group by t1.id order by actran desc limit 0,5";
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
                $sql = "select a.id,title,count,actimgurl,address,actstarttime,actendtime,joinstarttime,joinendtime,mastername,masterphone,crocode,paystate,state,actsign,`mapurl`,b.id as jid from act_info as a join act_payorder as b on a.id=b.actid join act_lbs as c on a.id=c.actid where b.userid='{$openid}'";
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
                $sql = "select adminsta,setvalue,marknum,grade from act_info where id='{$actid}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryOne($sql);
                return $result;
            }
            //查询活动分数上限
            public static function score_limit($actid)
            {
                $sql = "select act_setvalue from act_info where id='{$actid}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryOne($sql);
                return $result['act_setvalue'];   
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
                $sql = "SELECT  `troid`, cast((`total`) as decimal(10,3)) as total FROM `act_ranking` WHERE actid = '{$actid}' order by total desc";
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
            public static function del_act($actid)
            {
                    $sql = "UPDATE `act_info` SET `lock`=1 WHERE id='{$actid}'"; 
                    $conn = DbConn::getInstance(); 
                    $result = $conn->exec($sql);
                    return $result;
            }
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
             //10021
            public static function get_user($openid)
            {
                    $sql = "SELECT `id`,`winmoney`, `nickname`, `sex`, `city`, `country`, `province`, `headimgurl`, `tel`, `address`, `birthday`, `level`, `email`, `role`, `acivity`, `regtime` FROM `user_info` WHERE openid='{$openid}'"; 
                    $conn = DbConn::getInstance(); 
                    $result = $conn->queryOne($sql);
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
            //获取个人信息
             public static function get_userinfo($openid)
            {
                    $sql = "select nickname from user_info where openid='{$openid}'"; 
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
                    $sql = "select `title`, `imgurl`, `link`, `time` from `ad_home` where `lock`=0 && ad_type='{$ad_type}' order by sork desc"; 
                    $conn = DbConn::getInstance();
                    $result = $conn->queryAll($sql);
                    return $result;
            }
            //获取某个分类下的活动列表
            public static function get_typeact($catid)
            {
                $sql = "select actsta,t1.id,title,actimgurl,actdesc,actstarttime,actendtime,address,count(userid)as countuser,userlimit  from (act_info as t1 left join act_lbs as t2 on t1.id=t2.actid)  left join act_payorder as t3 on (t1.id=t3.actid) where t1.lockstatus=0 and t1.catid='{$catid}' group by t1.id order by actran desc";
                
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
                    $sql = "SELECT nickname,troops,t2.id as userid FROM act_signtime as t1 inner join user_info as t2 on t1.userid=t2.openid  WHERE t1.actid ='{$actid}' && t1.userid='{$openid}'";
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
            public static function del_user_job($actid,$openid)
            {
                $sql = "DELETE from act_job_user WHERE actid = '{$actid}' and user = '{$openid}'";
                $conn = DbConn::getInstance();
                $result = $conn->exec($sql);
                return $result;

            }
            //删除活动
            public static function delete_act($id)
            {
                $sql = "DELETE from act_payorder WHERE id = '{$id}'";
                $conn = DbConn::getInstance();
                $result = $conn->exec($sql);
                return $result;
                
            }
            //查看该职位详情
            public static function get_electordetail($actid,$id)
            {
                $sql = "select * from act_job WHERE id = '{$id}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryAll($sql);
                return $result; 
            }
            //更新职位剩余人数
            public static function up_jobsurplus($str,$id)
            {
                $sql = "update act_job set job_surplus = '{$str}' where id = '{$id}'";
                $conn = DbConn::getInstance();
                $result = $conn->exec($sql);
                return $result;   
            }
            //更改职位打分状态
            public static function up_grade_status($id)
            {
                $sql = "update act_job set grade_status = 1 where id = {$id}";
                $conn = DbConn::getInstance();
                $result = $conn->exec($sql);
                return $result; 
            }
           //更改
            public static function up_grade($grade,$actid)
            {
                $sql = "update act_info set grade = {$grade} where id = {$actid}";
                $conn = DbConn::getInstance();
                $result = $conn->exec($sql);
                return $result; 
            }
            //活动个排名
            public static function get_user_ranking($actid)
            {
                $sql = "SELECT b.headimgurl,b.nickname,a.userid,sum(a.mark) as total FROM act_grade_user AS a LEFT JOIN user_info AS b ON a.`userid` = b.openid where a.actid = '{$actid}' GROUP BY a.`userid` ORDER BY mark desc ";
                $conn = DbConn::getInstance();
                $result = $conn->queryAll($sql);
                return $result; 
            }
            //获取职位名称
            public static function get_job($userid,$actid)
            {
                $sql = "SELECT b.jobname from act_job_user as a left join act_job as b on a.f_id = b.id WHERE user = '{$userid}' AND actid = '{$actid}' and a.job_success = 1";
                $conn = DbConn::getInstance();
                $result = $conn->queryOne($sql);
                return $result['jobname'];
            }
            //列出加油用户
            public static function check_cheer($actid,$openid)
            {
                $sql = "SELECT c.nickname,c.headimgurl FROM act_job_user AS a LEFT JOIN act_job_add AS b ON a.id = b.f_id left join user_info as c on b.user = c.openid where a.user ='{$openid}' and a.actid='{$actid}'";
                //echo $sql;exit;
                $conn = DbConn::getInstance();
                $result = $conn->queryAll($sql);
                return $result;   
            }
            //查询用户所获得的总票数
            public static function job_select_user($openid,$actid)
            {
                $sql = "select poll from act_job_user where user = '{$openid}' and actid = '{$actid}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryOne($sql);
                return $result; 
            }
            //学客圈回复
            public static function ccl_replier($id,$uid,$replier,$content,$time)
            {
                $sql = "insert into ccl_rew (uid,fid,content,replier,time)values('{$uid}','{$id}','{$content}','{$replier}','{$time}')";
                $conn = DbConn::getInstance();
                $result = $conn->exec($sql);
                return $result;    
            }
            //获取当前角色竞选成功的用户
            public static function get_successful_user($id,$actid,$userid)
            {
                $sql = "select * from act_job_user where f_id = '{$id}' and actid = '{$actid}' and user = '{$userid}' and job_success = 1";
                $conn = DbConn::getInstance();
                $result = $conn->queryOne($sql);
                return $result;    

            }
            //获取当前用户活动参加的学分
            public static function get_act_winmoney($actid,$userid)
            {
                $sql = "select round(sum(shouzhi),2) as count from winmoney_log where actid = '{$actid}' and user_id = '{$userid}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryOne($sql);
                return $result;    
            }
            //获取用户在角色打分中获得的学分
            public static function get_job_winmoney($actid,$userid)
            {
                $sql = "SELECT round(sum(mark),2) as count FROM act_grade_user WHERE actid = '{$actid}' AND `userid` = '{$userid}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryOne($sql);
                return $result;    
 
            }
           
        }
        //商品
        class Goods
        {   
            //获取商品分类
            public static function get_goodstype()
            {
                $sql = "SELECT `id`,`tname`,`fonticon`,`color`,`f_id` FROM goods_type"; 
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

            //读取热门商品
            public static function get_typegoods_hot($num)
            {
                $sql = "SELECT `id`, `goods_name`, `abs`, `imgurl`, `total`, `time` ,`count` FROM wechat.goods order by `count` desc LIMIT {$num}";
                $conn = DbConn::getInstance();
                $result = $conn->queryAll($sql);
                return $result;
            }


            //读取商品id下的图片
            public static function get_gimg($goodsid)
            {
                $sql = "SELECT `url` FROM goods_img WHERE  goodsid='{$goodsid}'";
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
            //获取推荐商品
            public static function get_regoods() 
            {
                $sql = "SELECT * FROM goods WHERE is_recommend = 1";
                $conn = DbConn::getInstance();
                $result = $conn->queryAll($sql);
                return $result;    
            }
            //添加收货地址
            public static function add_address($uid,$consignee,$province,$city,$district,$address,$zipcode,$tel,$default)
            {
                $conn = DbConn::getInstance();
                if($default)
                {
                  $sql1 = "update wechat.user_address set `default` = '0' where uid = '{$user_id}'";
                  $conn->exec($sql1);         
                }
                $sql = "INSERT INTO user_address (uid,consignee,province,city,district,address,zipcode,tel,`default`)values('{$uid}','{$consignee}','{$province}','{$city}','{$district}','{$address}','{$zipcode}','{$tel}','{$default}')";
                $result = $conn->exec($sql);
                return $result;    

            }
            //编辑收货地址
            public static function edit_address($address_id,$uid,$consignee,$province,$city,$district,$address,$zipcode,$tel,$default)
            {
                $conn = DbConn::getInstance();
                if($default)
                {
                  $sql1 = "update wechat.user_address set `default` = '0' where uid = '{$user_id}'";
                  $conn->exec($sql1);         
                }
                $sql = "update user_address set uid = '{$uid}',consignee = '{$consignee}',province = '{$province}',city = '{$city}',district = '{$district}',address = '{$address}',zipcode = '{$zipcode}',tel = '{$tel}',`default` = '{$default}' where address_id = '{$address_id}'";
                $result = $conn->exec($sql);
                return $result;
            }
            //删除收货地址
            public static function del_address($id)
            {
                $sql = "delete from user_address where address_id = '{$id}'";
                $conn = DbConn::getInstance();
                $result = $conn->exec($sql);
                return $result;
            }
            //更改默认地址
            public static function update_address($address_id,$uid)
            {
                $sql = "update user_address set `default` = '0' where uid = '{$uid}'";
                $sql1 = "update user_address set `default` = '1' where address_id = '{$address_id}'";
                $conn = DbConn::getInstance();
                $conn->exec($sql);
                $result = $conn->exec($sql1);  
                return $result;
 
            }
            //获取个人收货地址
            public static function get_address($uid,$is_default)
            {
                $sql = "select * from user_address where uid = '{$uid}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryAll($sql);
                //var_dump($result);exit;  
                return $result;

            }
            //获取个人积分
            public static function get_winmoney($uid)
            {
                $sql = "select winmoney from user_info where id = '{$uid}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryOne($sql);
                return $result;   
            }
            //获取单条地址
            public static function get_oneaddress($id)
            {
                $sql = "select * from user_address where address_id = {$id}";
                $conn = DbConn::getInstance();
                $result = $conn->queryOne($sql);
                return $result;   
            }
            //添加订单
            public static function add_orders($order_sn,$uid,$order_status,$shipping_status,$pay_status,$consignee,$tel,$province,$city,$district,$address,$goods_amount,$add_time,$zipcode,$goods_id,$goods_name,$goods_number,$send_num,$is_real,$goods_price)
            {   
                $validity = time()+7*24*3600;
                $sql = "INSERT INTO goods_info(order_sn,user_id,order_status,shipping_status,pay_status,consignee,tel,province,city,district,address,goods_amount,add_time,zipcode,validity)values('{$order_sn}','{$uid}','{$order_status}','{$shipping_status}','{$pay_status}','{$consignee}','{$tel}','{$province}','{$city}','{$district}','{$address}','{$goods_amount}','{$add_time}','{$zipcode}','{$validity}')";
                $conn = DbConn::getInstance();
                $id = $conn->last_id($sql);
                $sql1 = "INSERT INTO goods_order(ord_id,goods_id,goods_name,goods_number,send_num,is_real,goods_price)values('{$id}','{$goods_id}','{$goods_name}','{$goods_number}','{$send_num}','{$is_real}','{$goods_price}')";
                $result = $conn->exec($sql1);
                return $result;
                   
            }
            //获取用户订单
            public static function get_orders($uid)
            {
                $sql = "select * from goods_info as a left join goods_order as b on a.order_id = b.ord_id LEFT JOIN goods AS c on b.goods_id = c.id
where a.user_id = '{$uid}'";
               //echo $sql;exit;
                $conn = DbConn::getInstance();
                $result = $conn->queryAll($sql);
                return $result;
            }
            //更新订单状态
            public static function update_order($uid,$goods_name,$total,$winmoney,$pay_mode)
            {
                $sql = "update goods_info set pay_status = '2',order_status = '1' where user_id = '{$uid}' order by add_time desc limit 1";
                $conn = DbConn::getInstance();
                $result = $conn->exec($sql);
                return $result;
            }
            //获取未完成订单
            public static function get_unfinished_orders($uid)
            {
                $sql = "select * from goods_info as a left join goods_order as b on a.order_id = b.ord_id LEFT JOIN goods AS c on b.goods_id = c.id where b.is_real = 0 and a.pay_status = 2 and a.user_id = '{$uid}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryAll($sql);
                return $result;   
            }
            //获取完成的订单
           public static function get_finished_orders($uid)
           {
                $sql = "select * from goods_info as a left join goods_order as b on a.order_id = b.ord_id LEFT JOIN goods AS c on b.goods_id = c.id where a.shipping_status = 2 and a.pay_status = 2 and a.user_id = '{$uid}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryAll($sql);
                return $result;
           }
           //获取已过期的订单
          public static function get_overdue_orders($uid)
          {
                $sql = "select * from goods_info as a left join goods_order as b on a.order_id = b.ord_id LEFT JOIN goods AS c on b.goods_id = c.id where a.validity < unix_timestamp(now())";
                $conn = DbConn::getInstance();
                $result = $conn->queryAll($sql);
                return $result;
          }
            
        }
        //app
        class App
        {
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
                $sql = "select t1.id,t1.last_reply_at,t1.content,t1.thumb,t2.nickname,t2.headimgurl,t2.id as uid from ccl_content as t1 inner join user_info as t2 on t1.uid=t2.id";
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
                $sql = "select t1.id,t1.last_reply_at,t1.content,t1.thumb,t2.nickname,t2.headimgurl,t2.id as uid from ccl_content as t1 inner join user_info as t2 on t1.uid=t2.id where t1.id='{$id}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryOne($sql);
                return $result;
            }
            public static function cll_rew($id)
            {
                $sql = "select t1.fid,t1.content,t1.time,t1.replier,t2.nickname,t2.headimgurl,t2.id as uid from ccl_rew as t1 inner join user_info as t2 on t1.uid=t2.id where t1.fid='{$id}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryAll($sql);
                
                foreach($result as $k => $v)
                {
                    
                     if($result[$k]['replier'])
                    {
                       
                       $sql1 = "select nickname,headimgurl,id from user_info where id = '{$result[$k]['replier']}'";
                       $res = $conn->queryOne($sql1);
                       $result[$k]['replier'] = $res;
                       
                    }
                
                }

                return $result;
            }
            //点赞数，评论数，转发数
            public static function cll_count($cid)
            {
                $sql = "SELECT fid,sum(wrzm) wrzm,sum(mysh) mysh,sum(sgbh) sgbh FROM (

                SELECT  fid,count(*) wrzm,0 mysh,0 sgbh FROM ccl_like
                WHERE status = 1
                GROUP BY fid
                UNION ALL

                SELECT  fid,0 wrzm,count(*) mysh,0 sgbh FROM ccl_rew
                GROUP BY fid

                UNION ALL

                SELECT fid,0 wrzm,0 mysh,count(*) sgbh FROM ccl_smit 
                GROUP BY fid

                ) t WHERE fid = '{$cid}' GROUP BY fid";
                $conn = DbConn::getInstance();
                $result = $conn->queryOne($sql);
                return $result;
            }
            //10045
            public static function cll_put_list($userid,$content,$thumb,$time)
            {   
                if($thumb)
                {
                 $thumb = implode(",", $thumb);
                }
                $createtime = time();
                $sql = "INSERT INTO `ccl_content`(`uid`, `last_reply_at`,`content`,`thumb`,createtime) VALUES ('{$userid}','{$time}','{$content}','{$thumb}','{$createtime}')";
                $conn = DbConn::getInstance();
                $result = $conn->exec($sql);
                return $result;
            }
            //10046
            public static function cll_put_rew($userid,$content,$id,$time)
            {
                $sql = "INSERT INTO `ccl_rew`(`uid`, `fid`,`content`,`time`) VALUES ('{$userid}','{$id}','{$content}','{$time}')";
                $conn = DbConn::getInstance();
                $result = $conn->exec($sql);
                return $result;
            }
            //是否存在点赞
            public static function like_why($userid,$id)
            {
                $sql = "SELECT `status` FROM `ccl_like` WHERE fid='{$id}' && uid='{$userid}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryOne($sql);
                return $result;
            }
            //更改点赞状态
            public static function cll_up_like($userid,$id,$status)
            {
                $sql = "UPDATE `ccl_like` SET `status`='{$status}' WHERE fid='{$id}' && uid='{$userid}'";
                $conn = DbConn::getInstance();
                $result = $conn->exec($sql);
                return $result;
            }
            //查看单条点赞数
            public static function cll_get_like($id)
            {
                $sql = "SELECT count(*) as sum FROM `ccl_like` WHERE fid='{$id}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryOne($sql);
                return $result;
            }
            //10047
            public static function cll_put_like($userid,$id,$time)
            {
                $sql = "INSERT INTO `ccl_like`(`uid`, `fid`,`time`) VALUES ('{$userid}','{$id}','{$time}')";
                $conn = DbConn::getInstance();
                $result = $conn->exec($sql);
                return $result;
            }
            //获取学客圈用户id
            public static function get_cll_user($id)
            {
                $sql = "SELECT `uid` FROM `ccl_content` WHERE id='{$id}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryOne($sql);
                return $result;
            }

            public static function cll_get_rew($id)
            {
                $sql = "SELECT * FROM `ccl_rew` WHERE fid='{$id}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryAll($sql);
                return $result;
  
            }
             //查看操作对应的学分
            public static function get_log_winmoney($log)
            {
                $sql = "select * from winmoney where log_name = '{$log}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryOne($sql);
                return $result; 
            }
            //记录学分操作
            public static function banwinmoney($log,$uid,$total,$actid)
            {   
                $log_time = time();
                $sql = "insert into winmoney_log (log,user_id,shouzhi,log_time,actid)values('{$log}','{$uid}','{$total}','{$log_time}','{$actid}')";
                $conn = DbConn::getInstance();
                $result = $conn->exec($sql);
                return $result;    
            }
            //获取学分记录
            public static function get_winmoney_log($user_id,$actid)
            {   
                if($actid)
                {
                  $sql = "select * from winmoney_log where user_id = '{$user_id}' and actid = '{$actid}'";                    
                }
                else
                {
                  $sql = "select * from winmoney_log where user_id = '{$user_id}'";
                }

                $conn = DbConn::getInstance();
                $result = $conn->queryAll($sql);
                return $result;    

            }
            public static function cll_smit($id,$uid)
            {  
                $time = time();
                $sql ="INSERT INTO ccl_smit (fid,uid,`time`)values('{$id}','{$uid}','{$time}')";
                $conn = DbConn::getInstance();
                $result = $conn->exec($sql);
                return $result;    

            }
            public static function get_ccl_smit($id,$uid)
            {
                $sql = "select * from ccl_smit where uid='{$uid}' and fid = '{$id}'";
                $conn = DbConn::getInstance();
                $result = $conn->queryOne($sql);
                return $result;    

            }
            public static function cll_get_usercll($uid)
            {
                $sql = "select * from ccl_content where uid={$uid}";
               // var_dump($sql);exit;
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
            public static function add_admin_actinfo($title,$catid,$actdesc, $actimgurl,$actcode,$crocode,$masterid,$mastername,$masterphone,$actstarttime,$actendtime,$joinstarttime,$joinendtime,$userlimit,$deposit,$actsta,$adminsta,$time,$lockstatus,$count,$content,$longitude,$latitude,$address,$arr,$mapurl,$setvalue,$actran)
            {
               $sql = "INSERT INTO  `act_info` (`title` ,`catid` ,`actdesc` ,`actimgurl` ,`actcode` ,`crocode`,`masterid`,`mastername`,`masterphone`,`actstarttime`,`actendtime`,`joinstarttime`,`joinendtime`,`userlimit`,`deposit`,`actsta`,`adminsta`,`time`,`lockstatus`,`count`,`actran`,`act_setvalue`)VALUES ('{$title}','{$catid}','{$actdesc}','{$actimgurl}','{$actcode}','{$crocode}','{$masterid}','{$mastername}','{$masterphone}','{$actstarttime}','{$actendtime}','{$joinstarttime}','{$joinendtime}','{$userlimit}','{$deposit}','{$actsta}','{$adminsta}','{$time}','{$lockstatus}','{$count}','{$actran}','{$act_setvalue}');";
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
                      $sql3 = "INSERT INTO  `act_job` (`f_id` ,`jobname`,`bonus`,`joblimit`,`job_surplus`)VALUES ('{$id}','{$arr[$k]['jobname']}','{$arr[$k]['bonus']}','{$arr[$k]['joblimit']}','{$arr[$k]['joblimit']}');" ;
                      $conn->exec($sql3);      
                   }
                
                }
                $conn->exec($sql1);
                $conn->exec($sql2);
                
                return $result;
            }
            
             //编辑任务
            public static function edit_admin_actinfo($id,$catid,$title,$actdesc, $actimgurl,$actcode,$crocode,$masterid,$mastername,$masterphone,$actstarttime,$actendtime,$joinstarttime,$joinendtime,$userlimit,$deposit,$actsta,$adminsta,$time,$lockstatus,$count,$content,$longitude,$latitude,$address,$arr,$mapurl,$setvalue,$actran)
            {
              
               $sql = "UPDATE  `act_info` as t1,`act_content` as t2,`act_lbs` as t3 SET t1.title = '{$title}' ,t1.catid = '{$catid}' ,t1.actdesc = '{$actdesc}' ,t1.actimgurl='{$actimgurl}' ,t1.actcode = '{$actcode}' ,t1.crocode = '{$crocode}', t1.masterid = '{$masterid}',t1.mastername='{$mastername}',t1.masterphone = '{$masterphone}',t1.actstarttime = '{$actstarttime}',t1.actendtime = '{$actendtime}',t1.joinstarttime = '{$joinstarttime}',t1.joinendtime = '{$joinendtime}',t1.userlimit = '{$userlimit}',t1.deposit = '{$deposit}',t1.actsta = '{$actsta}',t1.adminsta = '{$adminsta}',t1.time = '{$time}',t1.lockstatus = '{$lockstatus}',t1.count = '{$count}',t1.actran = '{$actran}',t1.act_setvalue = '{$setvalue}',t2.content = '{$content}',t3.address = '{$address}',t3.longitude = '{$longitude}',t3.latitude = '{$latitude}',t3.mapurl = '{$mapurl}' WHERE t1.id = '{$id}' AND t2.actid = '{$id}' AND t3.actid = '{$id}'";
                $conn = DbConn::getInstance();
                $result = $conn->exec($sql);
                
                $sql2 = "DELETE FROM act_job WHERE f_id = '{$id}'";
                $res = $conn->exec($sql2);
               
              
                if(is_array($arr))
                {
                   
                   foreach($arr as $k => $v)
                   {
                    
                     $sql3 = "INSERT INTO  `act_job` (`f_id` ,`jobname`,`bonus`,`joblimit`,`job_surplus`)VALUES ('{$id}','{$arr[$k]['jobname']}','{$arr[$k]['bonus']}','{$arr[$k]['joblimit']}','{$arr[$k]['joblimit']}');" ;
                      $conn->exec($sql3);      
                     
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
            //获取商品分类
            public static function get_type($id = 0)
            {
                $sql = "SELECT `id`,`tname`,`fonticon`,`color`,`f_id` FROM `goods_type` WHERE f_id= '{$id}' "; 
                $conn = DbConn::getInstance();
                $result = $conn->queryAll($sql);//查询子类
                $tree = []; 
                foreach($result as $k => $v)
                {

                        $tree[] = [ 
                                'id' => $result[$k]['id'], 
                                'name' => $result[$k]['tname'], 
                                'parent_id' => $result[$k]['f_id'],
                                //递归调用 
                                'children' => self::get_type($result[$k]['id']), 
                        ]; 
                     
                }
                return $tree;              
            }
            //商品发布
            public static function add_admin_goods($gid,$goods_name,$abs,$imgurl,$total,$time,$goods_number,$is_real,$count,$content,$url)
            {
                $sql = "INSERT INTO `goods`(`gid`,`goods_name`,`abs`,`imgurl`,`total`,`time`,`goods_number`,`is_real`,`count`) VALUES ('{$gid}','{$goods_name}','{$abs}','{$imgurl}','{$total}','{$time}','{$goods_number}','{$is_real}','{$count}')";
                               
                $conn = DbConn::getInstance();
                $id = $conn->last_id($sql);
                $sql1 = "INSERT INTO `goods_content`(`goodsid`,`content`) VALUES ('{$id}','{$content}');";
                $result = $conn->exec($sql1);
                $url = implode(",",$url);
               
                //多张图片单独进行操作
                $sql2 = "INSERT INTO  `goods_img` (`goodsid` ,`rank`,`url`)VALUES ('{$id}',0,'{$url}');" ;
                $conn->exec($sql2);      
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

                $sql = "UPDATE `goods` as t1,`goods_content` as t2 SET t1.gid = '{$gid}', t1.goods_name = '{$goods_name}',t1.count = '{$count}',t1.abs = '{$abs}',t1.imgurl = '{$imgurl}',t1.total = '{$total}',t1.time = '{$time}',t1.goods_number = '{$goods_number}',t1.is_real = '{$is_real}',t2.content = '{$content}' WHERE t1.id = '{$id}' and t2.goodsid = '{$id}'";
                $conn = DbConn::getInstance();
                $result = $conn->exec($sql);
                if($url)
                {
                  $url = implode(",",$url);
                  $sql2 = "UPDATE `goods_img` SET  `url` = '{$url}' WHERE goodsid = '{$id}';"; 
                  $arr = $conn->exec($sql2);
                }
                else
                {
                    $arr = '';
                }
               //var_dump($arr);exit;
                if($result || $arr)
                {
                    $result = 1;
                }

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
                      $result[$k]['total'] = $result[$k]['total'];
                }
               
                return $result;
            }
            //商品管理结束
            //代理商管理
            public static function agent_manage($tel)
            {
                $conn = DbConn::getInstance();
                $where = $tel?"b.tel like '%{$tel}%'":'1=1'; 
                $sql = "SELECT a.*,b.tel FROM xlp_app.earnings_incorrect AS a LEFT JOIN user_info AS b ON a.user_id = b.id where $where";
                $result = $conn->queryAll($sql);
                return $result; 
            }
            //代理商下级
            public static function agent_subordinate($form,$user_id,$tel)
            {
                $conn = DbConn::getInstance();
                $where = $tel?"b.tel like '%{$tel}%'":'1=1'; 
                $sql = "SELECT a.*, b.tel FROM xlp_app.earnings_incorrect AS a LEFT JOIN user_info AS b ON a.pid = b.id where a.{$form} = '{$user_id}'";
                //echo $sql;exit;
                $result = $conn->queryAll($sql);
                return $result; 
            }
            //代理商投资
            public static function agent_invest($money)
            {
               $sql = "UPDATE  `earnings_incorrect` SET  `catname` =  '{$tname}',`fonticon` =  '{$fonticon}',`color` =  '{$color}',`catran` =  '{$catran}' WHERE  `id` ='{$id}';";
                $conn = DbConn::getInstance();
                $result = $conn->exec($sql);
                return $result;
            }

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
                  $where = empty($page)? "order_sn = '{$order_sn}' order by order_id desc limit 0,7":"order_sn = {$order_sn}' order by order_id desc limit {$page},7 ";  
                  $sql = "SELECT * FROM goods_info where $where ";

                }else
                {  
                   $where = empty($page)? "order by order_id desc limit 0,7" : "order by order_id desc limit {$page},7";
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
            //获取学令牌用户
            public static function get_app_userinfo($tel)
            {
                $conn = DbConn::getInstance();
                $where = $tel?"a.tel like '%{$tel}%'":'1=1';  
                $sql = "SELECT a.id,a.winmoney,a.nickname,a.headimgurl,a.tel,a.role,a.regtime,a.login_ip,a.complete_info,a.bind_card,a.activate_status,a.credit,a.registrationid_id,b.bareheaded_photo,b.bust_shot,b.id_card FROM user_info as a left join xlp_app.user_detail as b on a.id = b.user_id where a.registrationid_id !='' and $where ";
                $result = $conn->queryAll($sql);
                return $result;   
            }
            //编辑用户资料
            public static function update_user_info($id,$winmoney,$nickname,$headimgurl,$bareheaded_photo,$credit,$bust_shot,$id_card,$role)
            {
                $conn = DbConn::getInstance();
                if($role == 1)
                {
                    $sql = "UPDATE user_info as a,xlp_app.user_detail as b SET a.winmoney = '{$winmoney}',a.nickname = '{$nickname}',a.headimgurl='{$headimgurl}',a.credit='{$credit}',b.bareheaded_photo = '{$bareheaded_photo}',b.bust_shot = '{$bust_shot}',b.id_card = '{$id_card}' WHERE a.id = '{$id}' and b.user_id = '{$id}'";
                    $result = $conn->exec($sql);
                }
                else
                {
                    $sql = "UPDATE user_info as a,xlp_app.user_detail as b SET a.winmoney = '{$winmoney}',a.nickname = '{$nickname}',a.headimgurl='{$headimgurl}',a.credit='{$credit}' WHERE a.id = '{$id}'";
                    $result = $conn->exec($sql);
                }

                return $result; 
            }
            public static function check_user_info($id)
            {
                $conn = DbConn::getInstance();
                $sql = "SELECT a.winmoney,a.nickname,a.headimgurl,a.tel,a.role,a.regtime,a.login_ip,a.complete_info,a.bind_card,a.activate_status,a.credit,a.registrationid_id,b.bareheaded_photo,b.bust_shot,b.id_card FROM user_info as a left join xlp_app.user_detail as b on a.id = b.user_id where a.id = '{$id}'";
                $result = $conn->queryOne($sql);
                return $result; 
            }
            public static function get_winmoney_detail($id,$name,$page)
            {
                $conn = DbConn::getInstance();
                $where = empty($page)? "user_id = {$id} limit 0,7" : "user_id = {$id} limit {$page},7";
                $sql = "SELECT * FROM winmoney_log where $where";
                $result = $conn->queryAll($sql);
                return $result; 

            }
            public static function update_shipping_status($id,$shipping_status)
            {
                 $conn = DbConn::getInstance();
                $where = empty($page)? "user_id = {$id} limit 0,7" : "user_id = {$id} limit {$page},7";
                $sql = "update goods_info set shipping_status = 1 where id = {$id}";
                $result = $conn->queryAll($sql);
                return $result; 
            }
            public static function Master_earnings_detail($user_id)
            {  
                $conn = DbConn::getInstance();
                $where = $user_id?"user_id = '{$user_id}'":'1=1';  
                $sql = "SELECT * FROM xlp_app.winmoney_log where $where";
                $result = $conn->queryAll($sql);
                return $result; 
            }
            public static function edit_admin_winmoney($id,$winmoney)
            {
                $conn = DbConn::getInstance();
                $sql = "UPDATE user_info SET winmoney = {$winmoney} WHERE id = {$id}";
                $result = $conn->exec($sql);
                return $result; 
            }
            public static function update_info_status($user_id,$status)
            {
                $conn = DbConn::getInstance();
                $sql = "UPDATE user_info SET complete_info = {$status} WHERE id = {$user_id}";
                $result = $conn->exec($sql);
                return $result;   
            }
            //用户提现记录
            public static function get_record($uid)
            {
                $conn = DbConn::getInstance();
                $where = $uid?"a.user_id = '{$uid}'":'1=1';  
                $sql = "SELECT a.*,b.card_number FROM xlp_app.withdrawal_record as a left join xlp_app.user_card as b on a.w_id = b.id where $where";
                $result = $conn->queryAll($sql);
                return $result;   
            }
            //同意/不同意用户提现申请
            public static function consent_application($id,$status)
            {
                $conn = DbConn::getInstance();
                $sql = "UPDATE xlp_app.withdrawal_record SET `status` = '{$status}' WHERE id in({$id})";
                $result = $conn->exec($sql);
                return $result;   
            }
            //学令牌活动列表
            public static function app_act($status,$user_id)
            {
                $conn = DbConn::getInstance();
                $where =  $user_id?"and user_id = $user_id":'and 1=1';
                $sql = "SELECT * FROM xlp_app.act_info where `actsta` = '{$status}' $where order by `actstarttime` desc ";
                $result = $conn->queryAll($sql);
                return $result;   
            }
            //修改用户银行卡号
            public static function update_user_card($id,$card_number)
            {
                $conn = DbConn::getInstance();
                $sql = "UPDATE xlp_app.user_card SET `card_number` = '{$card_number}' WHERE id = '{$id}'";
                $result = $conn->exec($sql);
                return $result;
            }
            //修改用户活动
            public static  function update_user_act($token,$id,$title,$actimgurl,$tel,$actstarttime,$userlimit,$actsta,$address,$subject,$start_img,$underway_img,$end_img,$latitude,$longitude)
            {
                $conn = DbConn::getInstance();
                $sql = "UPDATE xlp_app.act_info SET `title` = '{$title}',`actimgurl` = '{$actimgurl}',`tel` = '{$tel}',`actstarttime` = '{$actstarttime}',`userlimit` = '{$userlimit}',`actsta` = '{$actsta}',`address` = '{$address}',`subject` = '{$subject}',`start_img` = '{$start_img}',`underway_img` = '{$underway_img}',`end_img` = '{$end_img}',`latitude` = '{$latitude}',longitude = '{$longitude}' WHERE id = '{$id}'";
                $result = $conn->exec($sql);
                return $result;
            }
            public static function show_user_act($id)
            {
                $conn = DbConn::getInstance();
                $sql = "SELECT a.*,b.content FROM xlp_app.act_info as a left join xlp_app.act_content as b on a.id = b.actid where a.id = '{$id}'";
                $result = $conn->queryOne($sql);
                return $result; 
            }
            //查询用户设备号
            public static function check_device($user_id)
            {
                $conn = DbConn::getInstance();
                $sql = "SELECT registrationid_id FROM user_info where id = {$user_id}";
                $result = $conn->queryOne($sql);
                return $result['registrationid_id'];
            }
            //插入系统消息
            public static function add_system_news($user_id,$title,$news,$time)
            {
                $conn = DbConn::getInstance();
                $sql = "insert into xlp_app.user_news (`user_id`,`title`,`news`,`time`)values('{$user_id}','{$title}','{$news}','{$time}')";
                $result = $conn->exec($sql);
                return $result;
            }
            //一键审核
            public static function act_check($id,$status)
            {
                $conn = DbConn::getInstance();
                $sql = "UPDATE xlp_app.act_info SET `lockstatus` = '{$status}' WHERE id in({$id})";
                $result = $conn->exec($sql);
                return $result;
            }
            public static function product_refund($uid)
            {
                $conn = DbConn::getInstance();
                $where = $uid?"userid = '{$uid}'":'1=1';  
                $sql = "SELECT  * FROM xlp_app.product_refund where $where order by time desc";
                $result = $conn->queryAll($sql);
                return $result;   
            }
            public static function act_img()
            {
                $conn = DbConn::getInstance();  
                $sql = "SELECT  * FROM xlp_app.act_img";
                $result = $conn->queryAll($sql);
                return $result;
            }
            public static function Master_update_actimg($id,$imgurl)
            {
                $conn = DbConn::getInstance();  
                $sql = "update xlp_app.act_img set imgurl = '{$imgurl}' where id = '{$id}'";
                $result = $conn->exec($sql);
                return $result;   
            }
            public static function xlp_ccl()
            {
                $conn = DbConn::getInstance();  
                $sql = "SELECT  * FROM ccl_content where type = 2 order by last_reply_at desc";
                $result = $conn->queryAll($sql);
                return $result;   
            }
            public static function user_card($uid)
            {
                $conn = DbConn::getInstance();
                $where = $uid?"user_id = '{$uid}'":'1=1';  
                $sql = "SELECT  * FROM xlp_app.user_card where $where order by time desc";
                $result = $conn->queryAll($sql);
                return $result;   
            }
            public static function user_usb($uid)
            {
                $conn = DbConn::getInstance();
                $where = $uid?"pid = '{$uid}'":'1=1';           
                $sql = "SELECT  * FROM xlp_app.user_usb where pid !='' and $where";
                $result = $conn->queryAll($sql);
                return $result;   
            }
            public static function user_xlp($uid)
            {
                $conn = DbConn::getInstance();
                $where = $uid?"uid = '{$uid}'":'1=1'; 
                $sql = "SELECT  * FROM xlp_app.user_xlp where uid !='' and $where";
                $result = $conn->queryAll($sql);
                return $result;   
            }
            public static function consent_refund($id,$status)
            {
                $conn = DbConn::getInstance();
                $sql = "UPDATE xlp_app.product_refund SET `status` = '{$status}' WHERE id in({$id})";
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
            public static function Master_shield_ccl($id,$status)
            {
                 $sql = "UPDATE ccl_content SET `is_recovery` = '{$status}' where `id`in ({$id})";
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