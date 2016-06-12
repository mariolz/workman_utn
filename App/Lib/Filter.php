<?php
/**
 * 过滤参数的类
 * @author sun_wu
 *
 */
class Filter {
	private $_filter = array();
	function __construct() {
		$this->filterGlobalReq();
	}
	function get($param) {
		$this->_filter = $_GET[$param];
		return $this->_filter;
	}
	function post($param) {
		$this->_filter = $_POST[$param];
		return $this->_filter;
	}
	function addslashes_deep($value)
	{
		if (empty($value))
		{
			return $value;
		}
		else
		{
			return is_array($value) ? array_map(array('Filter','addslashes_deep') , $value) : addslashes($value);
		}
	}
    function filterGlobalReq() {
    	if (!get_magic_quotes_gpc())
    	{
    		if (!empty($_GET))
    		{
    			$_GET  = $this->addslashes_deep($_GET);
    		}
    		if (!empty($_POST))
    		{
    			$_POST = $this->addslashes_deep($_POST);
    		}
    	
    		$_COOKIE   = $this->addslashes_deep($_COOKIE);
    		$_REQUEST  = $this->addslashes_deep($_REQUEST);
    	}
    }
    /**
     * 
     * @param string $xml
     */
    function filterXml($xml) {
    	//print_r(stripslashes($xml));
    	return preg_replace("#\r|\n|\t#", "", stripslashes($xml));
    }

}