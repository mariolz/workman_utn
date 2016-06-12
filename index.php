<?php
use App\Server;
use Workerman\Autoloader;
use App\Lib\Filter;
define('GLOBALWORK',1);
define("ROOTPATH", dirname(__FILE__));
define("APPPATH",ROOTPATH.DIRECTORY_SEPARATOR.'App'.DIRECTORY_SEPARATOR);
define("COREPATH",ROOTPATH.DIRECTORY_SEPARATOR.'Workerman'.DIRECTORY_SEPARATOR);
require_once APPPATH.'Config/Config.php';
require_once APPPATH.'Config/'.DBDRIVER.'/Database.php';
require_once APPPATH.'Config/Memcache.php';
require_once 'Workerman/Autoloader.php';
require_once 'App/Server.php';
$server = new Server();
$server->createServer();