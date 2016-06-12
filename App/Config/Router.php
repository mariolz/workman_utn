<?php
/**
 * @todo此功能用于规定url的访问形式，目前暂时没有开放
 * @author sun_wu
 *
 */
namespace App\Config;
use Workerman\Worker;
class Router {
	static private $_instance = null;
	private $_config          = array('/Test'=>'/Test');
	static function getInstance() {
		self::$_instance = new Router();
		return self::$_instance;
	} 
	/**
	 * @param $route_cfg:string对应的路由配置文件
	 * @return multitype:string
	 */
	function getConfig($route_cfg) {
		//return $this->_config;
	}
}