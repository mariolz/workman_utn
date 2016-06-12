<?php
class ProductMemberLimit extends \Db{
	private $_table = '';
	private $_async = false;
	function __construct($async=false) {
		parent::__construct();
		$this->_async = $async;
		$this->_table = $this->_prefix.'PRODUCT_MEMBER_LIMIT';
	}
	function GetInfoByNodeProductSaleClassMemberClassCode($node_code,$p_code,$sale_class_code,$m_class_code) {
        $sql = "select MAX_NUM from ".$this->_table." where nodecode='".$node_code."' and productcode='".$p_code."' and saleclasscode='".$sale_class_code."' and member_class_id='".$m_class_code."'";
        $res = $this->fetch($sql,array(),$this->_async);
        return isset($res['MAX_NUM'])?$res['MAX_NUM']:0;
	}
	function test() {
		$sql = "select * from UTN_PLACE ";
		return $this->fetch($sql);
		//return $this->getServerInfo();
	}
}