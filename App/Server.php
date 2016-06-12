<?php
namespace App;
use Workerman\Worker;
use App\Config\Http;
use App\Router\Despatcher;
class Server {
	private $_config = array();
	function __construct() {
		
		$http = Http::getInstance();
		$this->_config = $http->getConfig();
		//$_connections = new Worker($this->_config);
	}
	function createServer() {
		
		$_connections = new Worker($this->_config);
		$config = $this->_config;
		// 启动4个进程对外提供服务
		$_connections->count = 2;
		$_connections->name  = 'Myinstance1';
		$_connections->id    = 'Myinstance1';
		$_connections->user  = 'daemon';
		Worker::$stdoutFile = '/tmp/stdout.log';       
		$router = new Despatcher();
		//$router->route($this->_config);
		// 接收到浏览器发送的数据时回复hello world给浏览器
		$_connections->onWorkerStart = function($_connections) use($config)
		{
			$_connections->onMessage = function($connection, $data) use($config){
				$router = new Despatcher();
				$res = $router->route($config);
				//var_dump($res);
				$connection->send($res);
			};
		};
		// 运行worker
		Worker::runAll();
	}
}