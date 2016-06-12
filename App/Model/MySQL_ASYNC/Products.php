<?php
class Products extends \Db{
	private $_table = '';
	private $_async = true;
	private $_cache = false;
	function __construct($async=false) {
		parent::__construct();
		$this->_table = $this->_prefix.'Product';
		//$this->_async = $async;
	}
	function getInfoByNodeCode($node_code,$cache=false) {
        $sql = "SELECT
						p.PRODUCTCODE,
						p.CNAME,
						p.PLACECODE,
						p.HAPPENTIME,
						p.PRODUCTCLASSCODE,
						l.CNAME as PLACENAME
					FROM
						".$this->_table." p
							INNER JOIN
							UTN_PLACE l
							ON
							p.NODECODE = l.NODECODE AND
						p.PLACECODE = l.PLACECODE
					WHERE
						p.NODECODE = ".$node_code." AND
						p.STATUS >= 5 AND
						p.STATUS <= 6 AND
						p.SALEBEGINTIME <= NOW() AND
						p.SALEENDTIME >= NOW() AND
						p.ISREGIE = 'N'";
        //var_dump($this->_async);
        return $this->fetchAll($sql,array(),$this->_async,$this->_cache);
	}
	function GetProductsInfoByNp($node_code,$p_code,$cache=false) {
		$sql = "SELECT
					Count(*) as num
				FROM
					UTN_PRODUCT
				WHERE
					NodeCode = ".$node_code." AND
					ProductCode = '".$p_code."' AND
					(STATUS = '5' OR
					STATUS = '6')
					";
		return $this->fetch($sql,array(),$this->_async,$cache);
	}
	function test() {
		$sql = "select * from UTN_PLACE ";
		return $this->fetch($sql,array(),false,$cache);
		//return $this->getServerInfo();
	}
}