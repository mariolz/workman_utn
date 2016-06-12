<?php
class SeatShow extends \Db{
	private $_table = '';
	private $_async = false;
	function __construct($async=false) {
		parent::__construct();
		$this->_async = $async;
		$this->_table = $this->_prefix.'SEAT_SHOW';
	}
	function GetFilesInfoByCond($columns = '*',$cond = array('cond'=>array(),'split'=>' AND '),$cache=false) {
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
		return $this->getFiledsInfo($sql,array(),$this->_async,$cache);
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
	function GetSeatStatus($node_code,$s_code,$cache=false) {
		$sql = "SELECT
					STATUS,
					KIND,
					PRICELEVEL
				FROM
					".$this->_table."
				WHERE
					NODECODE = ".$node_code." AND
					SECTIONCODE = '".$s_code."'
					";
		return $this->fetchAll($sql,array(),false,$cache);
	}
	function getAllInfoByOrderCode($order_id,$cache) {
		$sql = "SELECT SEATCODE,PRICELEVEL FROM UTN_SEAT_SHOW WHERE ORDERCODE = '".$order_id."'";
		return $this->fetchAll($sql,array(),false,$cache);
	}
}