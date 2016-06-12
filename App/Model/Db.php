<?php
use \Workerman\Worker;
use \Workerman\Connection\AsyncTcpConnection;
class Db {
	protected  $_db;
	protected  $_prefix = 'UTN_';
	function __construct() {
		require_once APPPATH.'Lib/'.DBDRIVER.'/class.pdo.DBPDO.php';
		$this->_db = new DBPDO();
	}
	/**
	 * 
	 * @param string $query sql
	 * @param array() $value pdo用的针对占位符的
	 * @param string $_async 是否开启db异步,目前只对mysql有效
	 * @param bool $cache 是否使用memcache true/false
	 * @return Ambigous <multitype:, string, mixed>
	 */
	function fetch($query,$value=array(),$_async = false,$cache=false) {
		//var_dump('tcp://'.DATABASE_HOST.':3050');

		if($_async) {
			return $this->_db->fetch($query,$value,$cache)->fetch_assoc();
			
		} else {
			return $this->_db->fetch($query,$value,$cache);
		}
		
	}
	function getFiledsInfo($query,$value=array(),$_async = false,$cache=false) {
		if($_async) {
			$arr = array();
			$res = $this->_db->fetchAll($query,$value,$cache)->fetch_fields();
			foreach ($res as $k=>$v) {
				$arr[$k]['name']     = $v->orgname; 
				$arr[$k]['alias']    = $v->name;
				$arr[$k]['relation'] = $v->table;
			}
			return $arr;
		} else {
			return $this->_db->getFieldsInfo($query,$value,$cache);
		}
	}
	function fetchAll($query,$value=array(),$_async = false,$cache=false) {
		if($_async) {
			$res = $this->_db->fetchAll($query,$value,$cache)->fetch_all(MYSQLI_ASSOC);
			return $res;
				
		} else {
			return $this->_db->fetchAll($query,$value,$cache);
		}
	
	}
	/**
	 * 
	 * @return string
	 */
	function query($sql,$value=array(),$_async = false) {
		return $this->_db->query($sql,$value);
	}
	function transBegin() {
		return $this->_db->trans_begin();
		
	}
	function transCommit() {
		return $this->_db->trans_commit();
	}
	function transRollBack() {
		return $this->_db->trans_rollback();
	}
	function getServerInfo() {
		return $this->_db->getServerInfo();
	}
	
}