<?php
class ProductObjectShow extends \Db{
	private $_table = '';
	private $_async = true;
	function __construct($async=false) {
		parent::__construct();
		//$this->_async = $async;
		$this->_table = $this->_prefix.'PRODUCT_OBJECT_SHOW';
	}
	function GetProductsObjInfoByNp($node_code,$p_code,$cache=false) {
		$sql = "SELECT
					GROUPCODE,
					OBJECTCODE,
					OBJECTTYPE
				FROM
					".$this->_table."
				WHERE
					NodeCode = ".$node_code." AND
					ProductCode = '".$p_code."' AND
					ObjectType <> 'S'
				ORDER BY
					OBJECTTYPE DESC";
		//print_r($this->fetchAll($sql,array(),$this->_async,$cache));
		return $this->fetchAll($sql,array(),$this->_async,$cache);
	}
}