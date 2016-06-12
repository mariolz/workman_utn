<?php
class ProductPrivilegeShow extends \Db{
	private $_table = '';
	private $_async = true;
	function __construct($async=false) {
		parent::__construct();
		//$this->_async = $async;
		$this->_table = $this->_prefix.'PRODUCT_PRIVILEGE_SHOW';
	}
	function GetProductsPrivInfoByNp($node_code,$p_code,$obj_code,$obj_type,$cache=false) {
		$sql = "SELECT
					Count(*) as NUM
				FROM
					".$this->_table."
				WHERE
					NodeCode = ".$node_code." AND
					ProductCode = '".$p_code."' AND
					ObjectCode in('".$obj_code."') AND
					ObjectType in('".$obj_type."') AND
					ActionCode = '1'
					";
		return $this->fetch($sql,array(),$this->_async,$cache);
	}
}