<?php
namespace App\Config;
use Workerman\Worker;
class Tcp {
	static private $_instance = null;
	private $_config          = 'Text://0.0.0.0:12345';
	static function getInstance() {
		self::$_instance = new Tcp();
		return self::$_instance;
	} 
	function getConfig() {
		return $this->_config;
	}
}