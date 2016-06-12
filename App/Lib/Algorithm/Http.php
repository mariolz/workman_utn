<?php
/**
 * 算法库
 * @author sun_wu
 *
 */
class Http {
	private $instance;
    function __construct() {
    	
    } 
    function __call($name,array $args) {
    	require_once APPPATH.'Lib/Algorithm.php';
    	
    }
}