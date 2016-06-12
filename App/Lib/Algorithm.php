<?php
/**
 * 算法库
 * @author sun_wu
 *
 */
class Algorithm {
	private $obj    = null;
	private $method = null;
    function __construct() {
    	
    } 
    /**
     * 
     * @param unknown $name
     * @param array $args $args[0] class $name method
     */
    function __call($name,array $args) {
    	if(isset($args[0])) {
    		require_once APPPATH.'Lib/Algorithm/'.$args[0].'.php';
    		$this->obj    = new $args[0];
    		$this->method = $name;
    		return call_user_func_array(array($this->obj,$this->method), array($args[1]));
    	} else {
    		echo "the class is not exists!!!";
    	}
    	
    }
}