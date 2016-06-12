<?php
namespace App\Router;
use App\Config\Router;
use Workerman\Worker;
class Despatcher {
	private $_config = array();
	private $_controller = '';
	private $_action     = '';
	private $_info       = array();
	private $_args       = array();
	private $_model      = '';
	private $_check      = false;
	function __construct() {
		if(defined('TOKEN')) {
			$ip_list = unserialize(TOKEN);
			//print_r($ip_list);
			$this->check     = empty($ip_list)?true:(isset($_SERVER['REMOTE_ADDR']) && in_array($_SERVER['REMOTE_ADDR'], $ip_list)?true:false);
			if(isset($_SERVER['REQUEST_URI']) && $this->check) {
				$url   = parse_url($_SERVER['REQUEST_URI']) ;
				$path  = isset($url['path'])?$url['path']:'/';
				$query = isset($url['query'])?$url['query']:'';
				//print_r($_POST);
				$this->_info       = explode("/",$path);
				//print_r($this->_info);
				array_shift($this->_info);
				$this->_controller = array_shift($this->_info);
				//$this->_action     = array_shift($this->_info);
				$this->_action     =  isset($_REQUEST['method'])?$_REQUEST['method']:'index';
				
				//$obj               = Router::getInstance();
				//$this->_config     = $obj->getConfig($this->_controller);
				if(empty($this->_controller)) {
					$this->_controller = 'Index';
				}
				if(empty($this->_action)) {
					$this->_action = 'index';
				}
			} else {
				echo "REQUEST_URI IS NOT Permitted";
			}
		}

	}
	function route($work_cfg)
	{
		try{
			if(file_exists(APPPATH.'Controller/'.$this->_controller.'.php') && $this->check) {
				if(GOLBALCACHE) {
					require_once APPPATH.'Lib/Cache.php';
				}
				require_once APPPATH.'Lib/Load.php';
				require_once APPPATH.'Config/Config.php';
				require_once APPPATH.'Model/Db.php';
				require_once APPPATH.'Controller/'.$this->_controller.'.php';
				$cc = $this->_controller;
				$obj = new $cc($work_cfg);
				//var_dump( $this->_action);
			    if(!empty($this->_controller) && !empty($this->_action)) {
			    	//var_dump($this->_controller,$this->_action);
					$res = call_user_func_array(array($obj, $this->_action), $this->_args);
					return $res;
				}
				
			} else {
				echo "REQUEST_URI IS NOT Permitted1111";
			}

		} catch (\Exception $e) {
			var_dump($e->getMessage());
		}
		//Worker::runAll();
	}
	
}