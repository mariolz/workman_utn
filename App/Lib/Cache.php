<?php
//namespace App\Lib;
/**
 * php5的缓存客户端
 * @author Administrator
 *
 */
class Cache {
    private $m = null;
    private $prefix = '';
    private $_servers = array(
    						array('192.168.2.228', 11211, 33)
						    //array('mem2.domain.com', 11211, 33)
						);
    private static $_memcache;
    function __construct() {
    	if(defined('MEMCACHE')) {
    		$server       = unserialize(MEMCACHE);
    		//print_r($server);
    		$this->_servers = $server;
    		$this->prefix = MEMPREFIX;
    		$server_list  = array();
    		self::$_memcache = new Memcached();
    		self::$_memcache->setOption(Memcached::OPT_CONNECT_TIMEOUT, 10);
    		self::$_memcache->setOption(Memcached::OPT_DISTRIBUTION, Memcached::DISTRIBUTION_CONSISTENT);
    		self::$_memcache->setOption(Memcached::OPT_SERVER_FAILURE_LIMIT, 2);
    		self::$_memcache->setOption(Memcached::OPT_REMOVE_FAILED_SERVERS, true);
    		self::$_memcache->setOption(Memcached::OPT_RETRY_TIMEOUT, 1);
    		
    		//var_dump($this->_servers);
    		self::$_memcache->addServers($this->_servers);
    		//var_dump(self::$_memcache->getServerList());
    	}
    	

    }
    /**
     * 设置缓存
     * @param $key 键名字
     * @param $val 键值
     */
    function set($key, $val, $duration='')
    {
    	if(empty($val)) {
    	    return false;
    	}
    	if($duration == '') { $duration = 0; }
    	self::$_memcache->set(md5($this->prefix.$key),$val,$duration);
    	if(self::$_memcache->getResultCode() != MEMCACHED_SUCCESS ) {
    		var_dump(md5($this->prefix.$key).'---'.$this->prefix.$key.'---'.self::$_memcache->getResultCode()); 
    	}
    	return self::$_memcache->getResultMessage();
    }
    function get($key) {
    	//self::$_memcache->addServers($this->_servers);
    	//var_dump(md5($this->prefix.$key));
    	$result = self::$_memcache->get(md5($this->prefix.$key));
        return $result;
    }

}