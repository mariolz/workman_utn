<?php
class Shows extends \Db{
	private $_table = 'shows';
	private $_async = false;
	function __construct($async=false) {
		parent::__construct();
		$this->_async = $async;
	}
	function fetchInfo($column = '*',$cond='') {
		$where = ' WHERE 1 ';
		if(!empty($cond)) {
			$where .= $cond;
		}
        $sql = 'select '.$column.' from '.$this->_table.$where;
        return $this->fetch($sql,array(),$this->_async);
	}
	function test() {
		$sql = "select * from UTN_PLACE ";
		return $this->fetch($sql);
		//return $this->getServerInfo();
	}
}