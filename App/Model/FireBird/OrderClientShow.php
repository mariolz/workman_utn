<?php
class OrderClientShow extends \Db{
	private $_table = '';
	private $_async = false;
	private $_cache = false;
	function __construct($async=false) {
		parent::__construct();
		$this->_table = $this->_prefix.'ORDER_CLIENT_SHOW';
		$this->_async = $async;
	}
	function UpOrderClientShowByCond($node_code,$order_code,$c_name,$mobile_no,$shipping_addr,$is_eticket='',$m_id=0) {
		$sql ="UPDATE
					".$this->_table."
				SET
					CNAME = '".$c_name."' ,
					CADDRESS = '".$shipping_addr."',
					CELLPHONE = '".$mobile_no."'
				WHERE
					NODECODE = ".$node_code." AND
					ORDERCODE = '".$order_code."'";
        return $this->query($sql,array(),$this->_async);
	}
	function SetCnameSexTelCaddrByNodeOrderCode($node_code,$order_code) {
		$sql = "UPDATE UTN_ORDER_CLIENT_SHOW SET CNAME = '参数1', SEX = '参数2', TEL = '参数3', CADDRESS = '参数4' WHERE NODECODE = '".$node_code."' AND ORDERCODE = '".$order_code."'";
		return $this->query($sql,array(),$this->_async);
	}
	function SetCommentByCond($columns,$cond = array('cond'=>array(),'split'=>' AND ')) {
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
		$sql = "UPDATE ".$this->_table." SET ".$columns.$fcond;
		$this->transBegin();
		$res = $this->query($sql,array(),$this->_async);
		if($res == 1) {
			return $this->transCommit();
		} else {
			$this->transRollBack();
			return false;
		}
		
	}
}