<?php
class OrderShow extends \Db{
	private $_table = '';
	private $_async = false;
	private $_cache = false;
	function __construct($async=false) {
		parent::__construct();
		$this->_table = $this->_prefix.'ORDER_SHOW';
		$this->_async = $async;
	}
	function SetInfoByCond($order_passwd,$node_code,$order_code) {
		$this->transBegin();
		$sql = "UPDATE UTN_ORDER_SHOW SET ORDERSTATUS = 'R', 
   ORDERPASSWORD = '".$order_passwd."', PAYEENODE = '".$node_code."', PAYEEGROUP = '".S_MPWS_GROUPCODE."', 
   PAYEE = '".S_MPWS_USERCODE."', GATHERINGTIME = cast('now' as timestamp), 
   OrderHandlerUser = '".S_MPWS_USERCODE."', OrderHandlerTime = cast('now' as timestamp), 
   ENDTIME = cast('now' as timestamp) WHERE NODECODE = '".$node_code."' and 
   ORDERCODE = '".$order_code."' and (ORDERSTATUS = 'N' OR ORDERSTATUS = 'B') ";
		$res = $this->query($sql,array(),$this->_async);
		if($res == 1) {
			return $this->transCommit();
		} else {
			return false;
		}
		//return $this->query($sql,array(),$this->_async);
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
	function UpOrderShowByCond($node_code,$order_code) {
		$sql ="UPDATE
					".$this->_table."
				SET
					ENDTIME = cast('now' as timestamp),
					ORDERSTATUS = 'U'
				WHERE
					NODECODE = ".$node_code." AND
					ORDERCODE = '".$order_code."'  AND ORDERSTATUS = 'N'";
        return $this->query($sql,array(),$this->_async);
	}
	function SetPasswdByNodeAndOrder($passwd,$node_code,$order_code) {
		$sql = "UPDATE ".$this->_table." SET  ORDERPASSWORD = '".$passwd."' WHERE ORDERCODE = '".$order_code."' AND NODECODE =".$node_code;
		return $this->query($sql,array(),$this->_async);
	}
	function getAllInfoByCond($node_code,$order_code,$p_code,$cache=false) {
		$sql = "SELECT * FROM UTN_ORDER_SHOW WHERE NODECODE = ".$node_code." AND ORDERCODE = '".$order_code."' AND PRODUCTCODE = '".$p_code."'";
		return $this->fetchAll($sql,array(),$this->_async,$cache);
	}
	function SetMemIdAndIsEticketByOrderNodeCode($mem_id,$is_ticket,$order_code,$node_code,$c_name,$sex,$tel,$caddr) {
		$result = false;
		$this->transBegin();
		$sql  = "UPDATE UTN_ORDER_SHOW SET MEMBERID ='".$mem_id."', ISETICKET = '".$is_ticket."' WHERE ORDERCODE = '".$order_code."' AND NODECODE = '".$node_code."'";
		$sql2 = $sql = "UPDATE UTN_ORDER_CLIENT_SHOW SET CNAME = '".$c_name."', SEX = '".$sex."', TEL = '".$tel."', CADDRESS = '".$caddr."' WHERE NODECODE = '".$node_code."' AND ORDERCODE = '".$order_code."'";
		$res1 = $this->query($sql,array(),$this->_async);
		$res2 = $this->query($sql2,array(),$this->_async);
		if(!$res1 || !$res2) {
			$this->transRollBack();
		} else {
			$this->transCommit();
			$result = true;
		}
		return $result;
	}
	function SetAccount3($node_code,$order_passwd,$ship_way,$order_code,$ship_addr,$p_code,$pu_code,$pg_code,$py_code) {
		if(!empty($py_code) && !empty($pg_code) && !empty($pu_code)) {
			$pg_code = $pg_code;
			$py_code = $py_code;
			$pu_code = $pu_code;
		} else {
			$pg_code = '';
			$py_code = '';
			$pu_code = '';
		}
		$this->transBegin();
		$sql1 = "UPDATE UTN_ORDER_SHOW SET ORDERSTATUS = 'R', 
 ORDERPASSWORD = '".$order_passwd."', PAYEENODE = '".$py_code."', PAYEEGROUP = '".$pg_code."', 
 PAYEE = '".$pu_code."', GATHERINGTIME = cast('now' as timestamp), 
  OrderHandlerUser = '".$pu_code."',FETCHWAY='".$ship_way."', OrderHandlerTime = cast('now' as timestamp), 
  ENDTIME = cast('now' as timestamp) WHERE NODECODE = '".$node_code."' and 
  ORDERCODE = '".$order_code."' and (ORDERSTATUS = 'N' OR ORDERSTATUS = 'B')";
		$sql2 = "UPDATE UTN_ORDER_CLIENT_SHOW
SET CADDRESS='".$ship_addr."'
WHERE NODECODE = '".$node_code."' and ORDERCODE='".$order_code."' and ProductCode='".$p_code."'";
		$res1 = $this->query($sql1,array(),$this->_async);
		$res2 = $this->query($sql2,array(),$this->_async);
		if($res1 == 1 && $res2 == 1) {
			return $this->transCommit();
		} else {
			$this->transRollBack();
			return false;
		}
	}
	function SetAccount4($node_code,$order_passwd,$order_code,$pu_code,$pg_code,$py_code,$pm_way) {
		if(!empty($py_code) && !empty($pg_code) && !empty($pu_code)) {
			$pg_code = $pg_code;
			$py_code = $py_code;
			$pu_code = $pu_code;
		} else {
			$pg_code = '';
			$py_code = '';
			$pu_code = '';
		}
		$this->transBegin();
		$sql1 = "UPDATE UTN_ORDER_SHOW SET PAYMODECODE = '".$pm_way."' ,ORDERSTATUS = 'R', 
 ORDERPASSWORD = '".$order_passwd."', PAYEENODE = '".$py_code."', PAYEEGROUP = '".$pg_code."', 
  PAYEE = '".$pu_code."', GATHERINGTIME = cast('now' as timestamp), 
  OrderHandlerUser = '".$pu_code."', OrderHandlerTime = cast('now' as timestamp), 
  ENDTIME = cast('now' as timestamp) WHERE NODECODE = '".$node_code."' and 
  ORDERCODE = '".$order_code."' and (ORDERSTATUS = 'N' OR ORDERSTATUS = 'B')";
		$res = $this->query($sql1,array(),$this->_async);
		if($res == 1) {
			return $this->transCommit();
		} else {
			$this->transRollBack();
			return false;
		}
	}
	function SetAccount3_sdjc($order_passwd,$node_code,$order_code,$py_code,$pg_code,$pu_code,$p_code) {
		$this->transBegin();
		$sql1 = "UPDATE UTN_ORDER_SHOW SET ORDERSTATUS = 'R', 
 ORDERPASSWORD = '".$order_passwd."', PAYEENODE = '".$py_code."', PAYEEGROUP = '".$pg_code."', 
 PAYEE = '".$pu_code."', GATHERINGTIME = cast('now' as timestamp), 
  OrderHandlerUser = '".$pu_code."', OrderHandlerTime = cast('now' as timestamp), 
  ENDTIME = cast('now' as timestamp) WHERE NODECODE = '".$node_code."' and 
  ORDERCODE = '".$order_code."' and (ORDERSTATUS = 'N' OR ORDERSTATUS = 'B')";
		$res = $this->query($sql1,array(),$this->_async); 
		if($res == 1) {
			return $this->transCommit();
		} else {
			$this->transRollBack();
			return false;
		}
		//return $this->query($sql,array(),$this->_async);
	}
}