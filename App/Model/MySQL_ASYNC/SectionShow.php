<?php
class SectionShow extends \Db{
	private $_table = '';
	private $_async = false;
	function __construct($async=false) {
		parent::__construct();
		$this->_async = $async;
		$this->_table = $this->_prefix.'SEAT_SHOW';
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