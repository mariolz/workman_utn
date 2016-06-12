<?php
/**
 * memcache配置
 * @author sun_wu
 *
 */
$memcache = array(
    array('192.168.2.228', 11211, 33)
    //array('mem2.domain.com', 11211, 33)
);
define('MEMCACHE', serialize($memcache));