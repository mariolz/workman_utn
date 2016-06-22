<?php
/**
 * 定义的值必须与Lib中的文件目录名字一样
 * @var GOLBALCACHE 全局cache
 */
$ip_list = array();//允许请求的白名单
$http_cluster = array('localhost:1','192.168.2.228:2');//http服务器集群 host:weight
define('DBDRIVER', 'FireBird');
//define('DBDRIVER', 'MySQL_ASYNC');
define('GOLBALCACHE',false);
define('MEMPREFIX','UTN_');

//$ip_list = array('192.168.2.168','192.168.2.228');
define('TOKEN',serialize($ip_list));
define('HTTPCLUSTER',serialize($http_cluster));
define('S_MPWS_USERCODE', 'MYPIAO');
define('S_MPWS_GROUPCODE', 'CTCOM');
define('S_MPWS_USERLEVEL', 'N');
define('S_MPWS_SYSTEM_OBJECTCODE', '(SYSTEM)');
define('UserCode', 'MYPIAO');
define('TerminalCode', 'PIAO');
define('BASE32_DIGITS','0123456789ABCDEFGHJKLMNPQRSTUWXY');
define('PRICEELVELCOLOR', serialize(array('A'=>'红','B'=>'黄','C'=>'草绿','D'=>'深绿','E'=>'天蓝','F'=>'深蓝','G'=>'橙','H'=>'紫','J'=>'棕','K'=>'浅蓝','M'=>'浅绿','N'=>'粉')));

