<?php
namespace App\Config;
use Workerman\Worker;
class XmlProtocol {
	static private $_instance = null;
	private $_config          = 'JsonNL://0.0.0.0:2346';
	static function getInstance() {
		self::$_instance = new Http();
		return self::$_instance;
	} 
	function getConfig() {
		return $this->_config;
	}
}