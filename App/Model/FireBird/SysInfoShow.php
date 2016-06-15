<?php
class SysInfoShow extends \Db{
	private $_table = 'SYSINFO_SHOW';
	private $_async = false;
	private $_cache = false;
	function __construct($async=false) {
		parent::__construct();
	    $this->_table = $this->_prefix.'SYSINFO_SHOW';
	}
	function GetAllInfoByCond($columns = '*',$cond = array('cond'=>array(),'split'=>' AND '),$cache=false) {
		$where   = array();
		//$columns = array();
		$values  = array();
		$cond1   = $cond['cond'];
		$split   = !empty($cond['split'])?$cond['split']:' AND ';
		$fcond   = '';
		if(!empty($cond1)) {
			$fcond .= ' where ';
			$keys = array_keys($cond1);
			$values  = array_values($cond1);
			foreach($keys as $k=>$v) {
				$where[$k]= $v."='".$values[$k]."'";
			}
			//print_r($where);
			if(isset($where) && !empty($where)) {
				$fcond .= implode(" $split ", $where);
			}
		}
		//echo $fcond;
		$sql = "SELECT ".$columns." from ".$this->_table.$fcond;
		return $this->fetchAll($sql,array(),$this->_async,$cache);
	}
	function GetInfoByCond($columns = '*',$cond = array('cond'=>array(),'split'=>' AND '),$cache=false) {
		$where   = array();
		//$columns = array();
		$values  = array();
		$cond1   = $cond['cond'];
		$split   = !empty($cond['split'])?$cond['split']:' AND ';
		$fcond   = '';
		if(!empty($cond1)) {
			$fcond .= ' where ';
			$keys = array_keys($cond1);
			$values  = array_values($cond1);
			foreach($keys as $k=>$v) {
				$where[$k]= $v."='".$values[$k]."'";
			}
			//print_r($where);
			if(isset($where) && !empty($where)) {
				$fcond .= implode(" $split ", $where);
			}
		}
		//echo $fcond;
		$sql = "SELECT ".$columns." from ".$this->_table.$fcond;
		return $this->fetch($sql,array(),$this->_async,$cache);
	}
	function GetPrice($node_code,$p_code,$cache=false) {
		$sql = "SELECT PRICELEVEL, PRICE, 0 as TOTALSEATS FROM ".$this->_table." WHERE NODECODE = ".$node_code." AND PRODUCTCODE = '".$p_code."'";
		return $this->fetchAll($sql,array(),false,$cache);
	}
	function GetLcdsPrice($node_code,$p_code,$cache=false) {
		$sql = "Select PRICELEVEL, PRICE from ".$this->_table." where  NodeCode = ".$node_code." and ProductCode = '".$p_code."' and Price > 0";
		return $this->fetchAll($sql,array(),false,$cache);
	}
}