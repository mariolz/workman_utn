<?php
class RowShow extends \Db{
	private $_table = 'shows';
	private $_async = false;
	function __construct($async=false) {
		parent::__construct();
		$this->_async = $async;
		$this->_table = $this->_prefix.'ROW_SHOW';
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
		return $this->fetchAll($sql,array(),$this->_async,$cache);
	}
	function test() {
		$sql = "select * from UTN_PLACE ";
		return $this->fetch($sql);
		//return $this->getServerInfo();
	}
}