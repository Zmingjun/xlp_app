<?php
   $ip=$_SERVER['REMOTE_ADDR']."\n";
   $result = fopen('./ip.txt', 'a+');
   fwrite($result ,$ip);
