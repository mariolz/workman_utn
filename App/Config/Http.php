<?php
namespace App\Config;
use Workerman\Worker;
class Http {
	static private $_instance = null;
	private $_config          = 'http://0.0.0.0:23456';
	static function getInstance() {
		self::$_instance = new Http();
		return self::$_instance;
	} 
	function getConfig() {
		$cluster = unserialize(HTTPCLUSTER);
		if(!empty($cluster)) { 
		}
		return $this->_config;
	}
}