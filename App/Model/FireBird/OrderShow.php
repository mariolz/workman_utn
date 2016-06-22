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
		$sql = "UPDATE ".$this->_table." SET ORDERSTATUS = 'R', 
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
		$sql = "SELECT * FROM ".$this->_table." WHERE NODECODE = ".$node_code." AND ORDERCODE = '".$order_code."' AND PRODUCTCODE = '".$p_code."'";
		return $this->fetchAll($sql,array(),$this->_async,$cache);
	}
	function SetMemIdAndIsEticketByOrderNodeCode($mem_id,$is_ticket,$order_code,$node_code,$c_name,$sex,$tel,$caddr) {
		$result = false;
		$this->transBegin();
		$sql  = "UPDATE ".$this->_table." SET MEMBERID ='".$mem_id."', ISETICKET = '".$is_ticket."' WHERE ORDERCODE = '".$order_code."' AND NODECODE = '".$node_code."'";
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
		$sql1 = "UPDATE ".$this->_table." SET ORDERSTATUS = 'R', 
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
		$sql1 = "UPDATE ".$this->_table." SET PAYMODECODE = '".$pm_way."' ,ORDERSTATUS = 'R', 
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
		$sql1 = "UPDATE ".$this->_table." SET ORDERSTATUS = 'R', 
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
	function InsertInfoByCond($methods1,$data1,$data2,$data3,$seats_info,$node_code,$cache=false) {
		//print_r($methods1);
		$f_res = 0;
		if(!empty($data1) && !empty($data2) && !empty($data3)) {
			$this->transBegin();
			$where   = '';
			if(!empty($data3['cond'])) {
				$where = ' where '.$data3['cond'];
			}
			$ks1     = array_keys($data1);
			$v1      = array_values($data1);
			$keys1   = implode(',', $ks1);
			$vals1   = implode('","', $v1);
			$ks2     = array_keys($data2);
			$v2      = array_values($data2);
			$keys2   = implode(',', $ks2);
			$vals2   = implode('","', $v2);
			$sql1    = "INSERT INTO ".$this->_table.'('.$keys1.')'."values(\"".$vals1."\")";
			$sql2    = "INSERT INTO UTN_ORDER_CLIENT_SHOW ".'('.$keys2.')'."values(\"".$vals2."\")";
			$res1    = $this->query($sql1,array(),$this->_async);
			$res2    = $this->query($sql2,array(),$this->_async);
			foreach($methods1 as $k=>$v) {
				$sql3    = "INSERT INTO UTN_ORDER_DISCOUNT_SHOW (".$data3['columns_insert'].") SELECT(".$data3['columns_select'].")
						SELECT ".$data3['columns_select'].",`".$v['PRICELEVEL']."`ISPRINTORIGINALLY`,`,`".$v['TICKETPRICE']."`,`".$v['DISCOUNT']."`,`".$v['SEATAMOUNT']."`,`".$v['PRICE']."`,`".$v['TOTALPRICE']."` FROM UTN_SALECLASS ".$data['cond'];
				$res3   = $this->query($sql3,array(),$this->_async);
				if($res3 !=1) {
					
					$f_res = '4294967295';
					return $f_res;
				}
			}
			foreach ($seats_info as $k=>$v) {
				foreach($v as $kk=>$vv) {
					$s   = explode(',',$vv);
					$c   = count($s)-1;
					$min = isset($s[0])?intval($s[0]):0;
					$max = isset($s[$c])?intval($s[$c]):0;
					$sql4= "UPDATE UTN_SEAT_SHOW SET STATUS = 'B', ORDERCODE = '".$order_code."'
WHERE NodeCode = '".$node_code."' and SECTIONCODE = '".$s_code."' and
(SEATCODE >= ".$min." and SEATCODE <= ".$max.") and STATUS = 'F'";
					$res4= $this->query($sql4,array(),$this->_async);
					if(count($s) != intval($res4)) {
						//$this->GetCreateOrderAndBooking4Xml();
						$f_res =  '4294967295';
						return $f_res;
					}
				}
			}
		 
			if($this->transCommit())
				return $f_res;
			} else {
				$this->transRollBack();
				return $f_res;
			}
			
		}
	}
}