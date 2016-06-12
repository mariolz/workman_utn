<?php
class ImyPiaoWscnohService {
	private $cache = false;
	function getFinalSeat(array $seat_info,$lwhole_seat,$s_price_level) {
		$this->format  = new Load('Lib/Format','Format');
		$this->format  = new Format();
		foreach($seat_info as $k=>$v) {
			if($v['STATUS'] == 'F' ) {
				if($lwhole_seat) {
					if($v['KIND'] != 'N' && $v['KIND'] != 'W') {
						$seat_info[$k]['STATUS'] = 'B';
					}
				} else if($v['KIND'] != 'W'){
					$seat_info[$k]['STATUS'] = 'B';
				}
	
			} else {
				$seat_info[$k]['STATUS'] = 'B';
			}
		}
		return $this->format->getSplitString($seat_info, '-','','KIND');
	}
	function ResultToPriceLevelStr($node_code,$p_code) {
		$this->model   = new Load('Model/'.DBDRIVER,'PriceShow');
		$this->model   = new PriceShow();
		$res           = $this->model->GetPrice($node_code,$p_code,$this->cache);
		$this->format  = new Load('Lib/Format','Format');
		$this->format  = new Format();
		$res           = $this->format->getSplitString($res, '-','-','TOTALSEATS');
		return $res;
	}
	function GetSectionShowAndGroup($node_code,$s_code) {
		$this->model   = new Load('Model/'.DBDRIVER,'JoinShow');
		$this->model   = new JoinShow();
		$res = $this->model->GetSectionAndGroupByCond($node_code, $s_code,$this->cache);
		return isset($res['RECORDCOUNT']) && intval($res['RECORDCOUNT']) == 1?true:false;
	}
	function GetSeatStatusString($node_code,$s_code) {
		$this->model   = new Load('Model/'.DBDRIVER,'SeatShow');
		$this->model   = new SeatShow();
		return $this->model->GetSeatStatus($node_code,$s_code,$this->cache);
	}
	function GetSeatStatus($node_code,$p_code,$s_code) {
		$s_price_level = $this->ResultToPriceLevelStr($node_code,$p_code);
		$lwhole_seat   = $this->GetSectionShowAndGroup($node_code,$s_code);
		$seat_info     = $this->GetSeatStatusString($node_code,$s_code);
		$s_price_level_seat_statu = $this->getFinalSeat($seat_info,$lwhole_seat,$s_price_level);
		$res           = $s_price_level.'|'.$s_price_level_seat_statu;
		return $res;
	}
	function GetPopedom($node_code,$p_code) {
		
		$p_status   = $this->GetProductsByNp($node_code,$p_code);
		return $this->GetFinalPopedom($node_code,$p_code,$p_status);
	}
	function GetProductsByNp($node_code,$p_code) {
		$this->model = new Load('Model/'.DBDRIVER,'Products');
		$this->model = new Products();
		$res         = $this->model->GetProductsInfoByNp($node_code,$p_code,$this->cache);
		//print_r($res);
		return intval($res['num']) == 1 ?true:50;
	}
	function GetProbjByNp($node_code,$p_code) {
		$this->model      = new Load('Model/'.DBDRIVER,'ProductObjectShow');
		$this->model      = new ProductObjectShow();
		return $this->model->GetProductsObjInfoByNp($node_code,$p_code,$this->cache);
		//$this->priv_model->GetProductsPrivInfoByNp($node_code, $p_code, $obj_code, $obj_type)
	    
	}
	/**
	 * GetPopedom主要业务逻辑
	 * @param unknown $node_code
	 * @param unknown $p_code
	 * @param unknown $p_obj
	 * @param unknown $p_status
	 */
	function GetFinalPopedom($node_code,$p_code,$p_status) {
		//var_dump($p_status===true);
		if($p_status===true) {
			$obj_code_list    = array();
			$obj_type_list    = array();
			$this->priv_model = new Load('Model/'.DBDRIVER,'ProductPrivilegeShow');
			$this->priv_model = new ProductPrivilegeShow();
			$p_obj_info = $this->GetProbjByNp($node_code,$p_code);
			//var_dump($p_obj_info);
			foreach($p_obj_info as $k=>$v) {
				if(($v['OBJECTTYPE'] == 'U' &&  $v['GROUPCODE'] == S_MPWS_GROUPCODE && $v['OBJECTCODE']==S_MPWS_USERCODE)
				 || ($v['OBJECTTYPE'] == 'L' && $v['GROUPCODE'] == S_MPWS_GROUPCODE && $v['OBJECTCODE']==S_MPWS_USERLEVEL.$v['GROUPCODE']) 
				 || ($v['OBJECTTYPE'] == 'G' && $v['GROUPCODE'] == S_MPWS_GROUPCODE && $v['OBJECTCODE']==S_MPWS_GROUPCODE) 
				 || ($v['OBJECTTYPE'] == 'D' && $v['OBJECTCODE']==S_MPWS_SYSTEM_OBJECTCODE)) {
					$obj_code_list[] = $v['OBJECTCODE'];
					$obj_type_list[] = $v['OBJECTTYPE'];
				}
			}
			$obj_code = implode("','", $obj_code_list);
			$obj_type = implode("','", $obj_type_list);
			$res = $this->priv_model->GetProductsPrivInfoByNp($node_code, $p_code, $obj_code, $obj_type);
			if(intval($res['NUM'])==1) {
				return 49;
			}
		} else {
			return $p_status;
		}
	}
	function GetBuyPopedom($node_code,$p_code) {
		$this->model                = new Load('Model/'.DBDRIVER,'JoinShow');
		$this->model                = new JoinShow();
		$fcds_sale_policy_privilege = $this->model->spp($node_code, $p_code,$this->cache);
		$fsq                        = $this->model->fsq($node_code, $p_code, $this->cache);
		return $this->AvailablePrivilegeObj($fcds_sale_policy_privilege,$fsq);
		
	}
	function AvailablePrivilegeObj($fspp,$fsq) {
        $res1 = $this->processGetBuyPopedom1($fspp);
        return $this->processGetBuyPopedom($fspp,$fsq);
	}
	function processGetBuyPopedom1(array $data) {
		$result = array();
		foreach($data as $k=>$v) {
			if($v['OBJECTCODE'] == 'CTCOM' && $v['ISVALID'] == 'Y' && $v['OBJECTTYPE'] == 'L') {
				unset($v);
			}
			$result[$k]['OBJECTTYPE']        = $v['OBJECTTYPE'];
			$result[$k]['OBJECTCODE']        = $v['OBJECTCODE'];
			$result[$k]['GROUPCODE']         = $v['GROUPCODE'];
			$result[$k]['SALECLASSCODE']     = $v['SALECLASSCODE'];
			$result[$k]['SALECLASSLEVEL']    = $v['SALECLASSLEVEL'];
			$result[$k]['ISVALID']           = $v['ISVALID'];
			if($result[$k]['OBJECTTYPE'] == 'M') {
				unset($result[$k]);
			}
		}
	}
	function processGetBuyPopedom(array $data1,array $data2) {
		$result       = array();
		$f_res        = array();
		$class_desc   = array();
		$f_res        = array();
		$lformat_str  = '';
		$lgroup_code  = '';
		$lobject_code = '';
		if(!empty($data1) && !empty($data2)) {
			$color_info = unserialize(PRICEELVELCOLOR);
			foreach($data1 as $k=>$v) {
				$i=0;
				foreach($data2 as $kk=>$vv) {
					$result[$vv['SALECLASSCODE']]['SALECLASSVALUE'][$kk]      = '';
					$result[$vv['SALECLASSCODE']]['GIFTNUM'][$kk]             = '';
					$result[$vv['SALECLASSCODE']]['SALECLASSTPYE'][$kk]       = '';
					$result[$vv['SALECLASSCODE']]['SALECLASSDESC'][$kk]       = '';
					if(!is_null($vv['TOTALPRICES']) && !empty($vv['TOTALPRICES'])) {
						$result[$vv['SALECLASSCODE']]['SALECLASSTPYE'][$kk]   = 'T';
						$result[$vv['SALECLASSCODE']]['SALECLASSVALUE'][$kk]  = $vv['TOTALPRICES'];
						$class_desc[$vv['SALECLASSCODE']][$i][$kk]            = $result[$vv['SALECLASSCODE']]['SALECLASSVALUE'][$kk];
						$result[$vv['SALECLASSCODE']]['split']                = ';';
					} else if(!is_null($vv['GIFTNUM']) && !empty($vv['GIFTNUM'])) {
						$class_desc[$vv['SALECLASSCODE']][$i][$kk]            = $result[$vv['SALECLASSCODE']]['SALECLASSVALUE'][$kk];
						$result[$vv['SALECLASSCODE']]['SALECLASSTYPE'][$kk]   = 'G';
						$result[$vv['SALECLASSCODE']]['GIFTNUM'][$kk]         = $vv['GIFTNUM'];
						$class_desc[$vv['SALECLASSCODE']][$i][$kk]            = $result[$vv['SALECLASSCODE']]['SALECLASSVALUE'][$kk];
						$result[$vv['SALECLASSCODE']]['split']                = '、';
					} else if(!is_null($vv['DISCOUNT']) && !empty($vv['DISCOUNT'])){
						$class_desc[$vv['SALECLASSCODE']][$i][$kk]            = $result[$vv['SALECLASSCODE']]['SALECLASSVALUE'][$kk];
						$result[$vv['SALECLASSCODE']]['SALECLASSTYPE'][$kk]   = 'D';
						$result[$vv['SALECLASSCODE']]['split']                = '、';
						$result[$vv['SALECLASSCODE']]['SALECLASSVALUE'][$kk]  = $vv['DISCOUNT'];
					} 
					if($result[$vv['SALECLASSCODE']]['SALECLASSTYPE'][$kk]   == 'T') {
						$lformat_str                                          =  '购买'.$vv['TICKETNUM'].'张'.$color_info[$vv['PRICELEVEL']].'色区域票（'.number_format($vv['PRICE'],1).'元/张）总价'.$class_desc[$vv['SALECLASSCODE']][0][$kk].'元';
						$result[$vv['SALECLASSCODE']]['SALECLASSDESC'][$kk]   =  $lformat_str;
					} else if($result[$vv['SALECLASSCODE']]['SALECLASSTYPE'][$kk]   == 'D') {
						if(intval($result[$vv['SALECLASSCODE']]['SALECLASSVALUE'][$kk]) !=100) {
							$num                                                  = (!empty($result[$vv['SALECLASSCODE']]['SALECLASSVALUE'][0])?$result[$vv['SALECLASSCODE']]['SALECLASSVALUE'][0]:0)/10;
							$lprice_level                                         = $color_info[$vv['PRICELEVEL']] + '色区域票（'.$vv['PRICE']. '元/张）、';
							$lformat_str                                          =  '选择'.$lprice_level.'，购买同等价位'.$vv['TICKETNUM'].'张及以上，可享受'.number_format($num,1).'折优惠';
							$result[$vv['SALECLASSCODE']]['SALECLASSDESC'][$kk]   = $lformat_str;
						}
					} else if($result[$vv['SALECLASSCODE']]['SALECLASSTPYE'][$kk]   == 'G') {
						$lprice_level =  $color_info[$vv['PRICELEVEL']] + '色区域票(' .$vv['PRICE'].'元/张）、';
						$lformat_str  = '选择'.$lprice_level.'，购买同等价位张数符合'.$vv['TICKETNUM'].'*n张，可赠同等价票'.$result[$vv['SALECLASSCODE']]['SALECLASSVALUE'][0].'*n张（赠票需同时在票区图中选定）';
						$result[$vv['SALECLASSCODE']]['SALECLASSDESC'][$kk]   = $lformat_str;
					}
					if($v['GROUPCODE'] != S_MPWS_GROUPCODE && $v['OBJECTCODE'] != S_MPWS_USERLEVEL && $v['SALECLASSCODE'] != $vv['SALECLASSCODE']) {
							if($v['OBJECTCODE'] != S_MPWS_USERLEVEL && $v['SALECLASSCODE'] != $vv['SALECLASSCODE']) {
								continue;
							}
							$lgroup_code  = $v['GROUPCODE'];
							$lobject_code = $v['OBJECTCODE'];
							if($v['GROUPCODE'] == $lgroup_code && $v['OBJECTCODE'] == $lobject_code && $v['SALECLASSCODE'] == $vv['SALECLASSCODE'] && $v['SALECLASSLEVEL'] == $vv['PRICELEVEL']) {
								$result[$vv['SALECLASSCODE']]['PRICELEVEL'][$kk]     = $vv['PRICELEVEL'];
							}
					} else {
						if($v['ISVALID'] == 'N') {
							continue;
						}
						$lgroup_code  = $v['GROUPCODE'];
						$lobject_code = $v['OBJECTCODE'];
						if($v['GROUPCODE'] == $lgroup_code && $v['OBJECTCODE'] == $lobject_code && $v['SALECLASSCODE'] == $vv['SALECLASSCODE'] && $v['SALECLASSLEVEL'] == $vv['PRICELEVEL']) {
							$result[$vv['SALECLASSCODE']]['PRICELEVEL'][$kk]     = $vv['PRICELEVEL'];
						}
					}
					$result[$vv['SALECLASSCODE']]['SALECLASSCODE'][$kk] = $vv['SALECLASSCODE'];
					$result[$vv['SALECLASSCODE']]['SALECLASSNAME'][$kk] = $vv['SALECLASSNAME'];
					$result[$vv['SALECLASSCODE']]['SORTCODE'][$kk]      = $vv['SORTCODE'];
					$result[$vv['SALECLASSCODE']]['TICKETNUM'][$kk]     = $vv['TICKETNUM'];
					$result[$vv['SALECLASSCODE']]['ISSUITE'][$kk]       = $vv['ISSUITE'];
					$result[$vv['SALECLASSCODE']]['ISPRINTED'][$kk]     = $vv['ISPRINTED'];
					$result[$vv['SALECLASSCODE']]['PRICE'][$kk]         = $vv['PRICE'];
					$result[$vv['SALECLASSCODE']]['TOTALPRICES'][$kk]    = $vv['TOTALPRICES'];
					$result[$vv['SALECLASSCODE']]['GIFTNUM'][$kk]       = $vv['GIFTNUM'];
					$result[$vv['SALECLASSCODE']]['DISCOUNT'][$kk]      = $vv['DISCOUNT'];
				    $i++;
				}
			}
		}
		//print_r($result);
        foreach($result as $k=>$v) {
        	$result[$k]['SALECLASSDESCS'] = implode($result[$k]['split'], $v['SALECLASSDESC']);
        }
        //print_r($result);
		return $this->getXmlResultBYPopdoms($result);
	}
	function getXmlResultBYPopdoms($data) {
		//print_r($data);
		$rows = '';
		foreach($data as $k=>$v) {
			$rows .= '<Row SaleClassCode="'.$k.'" SaleClassName="'.$v['SALECLASSNAME'][0].'" SaleClassType="'.$v['SALECLASSTYPE'][0].'" TicketNum="'.$v['TICKETNUM'][0].'"';
			$price_levels = isset($v['PRICELEVEL'])?implode('-',$v['PRICELEVEL']):'';
			$sale_class_values = isset($v['SALECLASSVALUE'])?implode('-',$v['SALECLASSVALUE']):'';
			/**
             *@明天待定
			 */
			$sale_class_desc   = $v['SALECLASSDESCS'];
			$rows .=' PriceLevels="'.$price_levels.'" SaleClassValues="'.$sale_class_values.'" SaleClassDesc="'.$sale_class_desc.'"/>';
		}
		$string =  '<?xml version="1.0" encoding="UTF-8"?>
				<UTN_ONLINESALETICKET_DATAPACKET>
					<METADATA>
						<BuyPopdoms>
							<Fields>
								<Field Name="SaleClassCode"/>
								<Field Name="SaleClassName"/>
								<Field Name="SaleClassType"/>
								<Field Name="TicketNum"/>
								<Field Name="PriceLevels"/>
				                <Field Name="SaleClassValues"/>
				                <Field Name="SaleClassDesc"/>
							</Fields>
						</BuyPopdoms>
					</METADATA>
					<ROWDATA>
				        <BuyPopdoms>
							'.$rows.'
						</BuyPopdoms>
					</ROWDATA>
				</UTN_ONLINESALETICKET_DATAPACKET>';
		return $string;
	}
	function GetLevelCount($node_code,$p_code) {
		$this->model       = new Load('Model/'.DBDRIVER,'PriceShow');
		$this->join_model  = new Load('Model/'.DBDRIVER,'JoinShow');
		$this->model       = new PriceShow();
		$this->join_model  = new JoinShow();
	    $lcds_price        = $this->model->GetLcdsPrice($node_code, $p_code);
	    $fsql              = $this->join_model->sectionGroupSeatPriceShow($node_code,$p_code,$this->cache);
	    //print_r($fsql);
	    return $this->ProcessLevelCount($lcds_price, $fsql);
	}
	function ProcessLevelCount($lcds_price,$fsql) {
		$xml = '<?xml version="1.0" encoding="UTF-8"?><UTN_ONLINESALETICKET_DATAPACKET>';
		$lprice  = '';
		$lamount = '';
		if(!empty($lcds_price) && !empty($fsql)) {
			foreach($lcds_price as $k=>$v) {
				foreach($fsql as $kk=>$vv) {
					//var_dump($v['PRICELEVEL'] == $vv['PRICELEVEL']);
					if($v['PRICELEVEL'] == $vv['PRICELEVEL']) {
						$lprice   = $vv['PRICE'];
						$lamount  = $vv['AMOUNT'];
						$xml .= '<'.$v['PRICELEVEL'].' Price="'.$lprice.'"'.' Amount="'.$lamount.'" />';
						break 1;
					} else {
						$lprice   = $v['PRICE'];
						$lamount  = 0;
					}
				}
				if(empty($lamount)) {
					$xml .= '<'.$v['PRICELEVEL'].' Price="'.$lprice.'"'.' Amount="'.$lamount.'" />';
				}
			}
		} else {
			$xml .= '';
		}
        
		return $xml .= '</UTN_ONLINESALETICKET_DATAPACKET>';
	}
	function UpdateOrderPass($node_code,$order_code,$passwd) {
		$this->model       = new Load('Model/'.DBDRIVER,'OrderShow');
		$this->model       = new OrderShow();
		if($this->model->SetPasswdByNodeAndOrder($passwd, $node_code, $order_code)) {
			return true;
		} else {
			return false;
		}
	}
	function BingOrder($node_code,$order_code,$p_code,$m_code,$m_class_id,$c_name,$sex,$tel,$shipping_addr,$is_eticket) {
		$this->order_show_model       = new Load('Model/'.DBDRIVER,'OrderShow');
		$this->seat_show_model        = new Load('Model/'.DBDRIVER,'SeatShow');
		//$this->order_client_show_model= new Load('Model/'.DBDRIVER,'SeatShow');
		$this->order_show_model       = new OrderShow();
		$this->seat_show_model        = new SeatShow();
		//$this->order_client_show_model= new OrderClientShow();
		$res1 = $this->order_show_model->getAllInfoByCond($node_code, $order_code, $p_code,$this->cache);
		$res2 = $this->seat_show_model->getAllInfoByOrderCode($order_code,$this->cache);
		$sql1 = $this->order_show_model->SetMemIdAndIsEticketByOrderNodeCode($m_code, $is_eticket, $order_code, $node_code,$c_name,$sex,$tel,$shipping_addr);
		
		//$sql2 = $this->order_client_show_model->SetCnameSexTelCaddrByNodeOrderCode($node_code, $order_code);
		return $this->processBingOrder($sql1,$res1,$res2);
	}
	function processBingOrder($sql1,$res1,$res2) {
		
		//var_dump(!empty($res));
		$a_result = 'false';
		$a_error  = '';
		$aseat    = '';
		if(!empty($res1)) {
			
			if($sql1) {
				$aseat      = $this->processRes2BySplit($res2);
				if(!empty($aseat)) {
					$a_result = 'true';
					//var_dump($a_result);
				} else {
					$a_error  = '获取座位信息失败，请与管理员联系。';
				}
			} else {
				$a_error  = '更新客户信息失败，请与管理员联系。';
			}
		} else {
			$a_error  = '该订单与演出不匹配，请核对后重试。';
		}
		
		$xml = '<?xml version="1.0" encoding="UTF-8"?><UTN_ONLINESALETICKET_DATAPACKET><METADATA RESULT="'.$a_result.'" ERROR="'.$a_error.'" SEATS="'.$aseat.'"/></UTN_ONLINESALETICKET_DATAPACKET>';
	    return $xml;
	}
	function processRes2BySplit($data) {
		$this->format  = new Load('Lib/Format','Format');
		$this->format  = new Format();
		return $this->format->getSplitString($data, '|','-');
		
	}
	function GetMemberClassWebSale($node_code, $p_code, $sale_class_code, $m_class_code) {
		$this->model       = new Load('Model/'.DBDRIVER,'ProductMemberLimit');
		$this->model       = new ProductMemberLimit();
		$res               = $this->model->GetInfoByNodeProductSaleClassMemberClassCode($node_code, $p_code, $sale_class_code, $m_class_code);
		if(empty($res)) {
			return 0;
		} else {
			return $res;
		}

	}
	function Account2($node_code,$order_code,$p_code,$order_passwd) {
		$this->model       = new Load('Model/'.DBDRIVER,'OrderShow');
		$this->model       = new OrderShow();
		$where             = array('cond'=>array('NODECODE'=>$node_code,'ProductCode'=>$p_code,'OrderCode'=>$order_code),'split'=>' AND ');
		$res = $this->model->GetInfoByCond('ORDERSTATUS, SALECLASSCODE, RECEIVABLES',$where,$this->cache);
		return $this->processAccount2($res,$this->model);
		//$this->model->SetInfoByCond($order_passwd,$node_code,$order_code);
	}
	function processAccount2($data,$order_show_model) {
		try{
			$result          = '4294967295';
			$sale_class_code = $data['SALECLASSCODE'];
			$receivables     = $data['RECEIVABLES'];
			$order_status    = $data['ORDERSTATUS'];
			if($order_status!='N') {
				return $result = 81;
			} else {
				$res = $order_show_model->SetInfoByCond($order_passwd,$node_code,$order_code);
				if($res) {
					return 0;
				} 
				return $result;
			}
		} catch (Exception $e) {
			return $result=$e->getCode();
		}

	}
	function Account3($node_code,$order_code,$p_code,$order_passwd,$py_code,$pg_code,$pu_code,$ship_way,$ship_addr) {
		$this->model       = new Load('Model/'.DBDRIVER,'OrderShow');
		$this->model       = new OrderShow();
		$where             = array('cond'=>array('NODECODE'=>$node_code,'ProductCode'=>$p_code,'OrderCode'=>$order_code),'split'=>' AND ');
		$res = $this->model->GetInfoByCond('ORDERSTATUS, SALECLASSCODE, RECEIVABLES',$where,$this->cache);
	    return $this->processAccount3($res,$node_code,$order_passwd,$ship_way,$order_code,$ship_addr,$p_code,$pu_code,$pg_code,$py_code,$this->model);
	}
	function processAccount3($data,$node_code,$order_passwd,$ship_way,$order_code,$ship_addr,$p_code,$pu_code,$pg_code,$py_code,$show_order_model) {
		$result = false;
		if($data['ORDERSTATUS'] == 'R') {
			return $result = 1;
		} 
		if($data['ORDERSTATUS'] == 'N' && $data['ORDERSTATUS'] != 'B') {
			return $result = 81;
		}
		$res = $show_order_model->SetAccount3($node_code,$order_passwd,$ship_way,$order_code,$ship_addr,$p_code,$pu_code,$pg_code,$py_code);
		if(!$res) {
			return $result;
		}
		return $result = 1;
	}
	function Account4($node_code,$order_code,$p_code,$order_passwd,$py_code,$pg_code,$pu_code,$pm_way) {
		$this->model       = new Load('Model/'.DBDRIVER,'OrderShow');
		$this->model       = new OrderShow();
		$where             = array('cond'=>array('NODECODE'=>$node_code,'ProductCode'=>$p_code,'OrderCode'=>$order_code),'split'=>' AND ');
		$res = $this->model->GetInfoByCond('ORDERSTATUS, SALECLASSCODE, RECEIVABLES',$where,$this->cache);		
	    return $this->processAccount4($res, $node_code, $order_passwd, $order_code, $pu_code, $pg_code, $py_code, $pm_way,$this->model);
	}
	function processAccount4($data,$node_code,$order_passwd,$order_code,$pu_code,$pg_code,$py_code,$pm_way,$show_order_model) {
		$result          = '4294967295';
		$sale_class_code = $data['SALECLASSCODE'];
		$receivables     = $data['RECEIVABLES'];
		$order_status    = $data['ORDERSTATUS'];
		if($order_status !='N' && $order_status !='B') {
			return $result = 81;
		}
		$res             = $show_order_model->SetAccount4($node_code,$order_passwd,$order_code,$pu_code,$pg_code,$py_code,$pm_way);
		if($res) {
			return $result;
		}
		return $result =1;
	}
	function Season_GetSeatStatus($node_code,$sp_code,$s_code) {
		$this->model       = new Load('Model/'.DBDRIVER,'SeaSonPolicy');
		$this->model       = new SeaSonPolicy();
		$where             = array('cond'=>array('POLICYCODE'=>$sp_code));
		$res               = $this->model->GetInfoByCond('DELEGATEPRODUCT',$where,$this->cache);
        return $this->processSeason_GetSeatStatus($res,$node_code,$s_code);

	}
	function processSeason_GetSeatStatus($data,$node_code,$s_code) {
		$this->model       = new Load('Model/'.DBDRIVER,'PriceShow');
		$this->join_model  = new Load('Model/'.DBDRIVER,'JoinShow');
		$this->seat_model  = new Load('Model/'.DBDRIVER,'SeatShow');
		$this->model       = new PriceShow();
		$this->join_model  = new JoinShow();
		$this->seat_model  = new SeatShow();
		$p_code            = isset($data['DELEGATEPRODUCT']) && !empty($data['DELEGATEPRODUCT'])?$data['DELEGATEPRODUCT']:'';
		if(!empty($p_code)) {
			$where         = array('cond'=>array('NodeCode'=>$node_code,'ProductCode'=>$p_code));
			$res           = $this->model->GetInfoByCond('PRICELEVEL, PRICE',$where,$this->cache);
			$res1          = $this->format->getSplitString($res, '-','-');
		} else {
			return '数据集1中无数据';
		}
		$join_res          = $this->join_model->SectionAndSectionGroupShow($node_code, $s_code,$this->cache);
		if(isset($join_res['RecordCount']) && intval($join_res['RecordCount']) == 1) {
			$lwhole_seat   = true;
		} else {
			$lwhole_seat   = false;
		}
		$where1            = array('cond'=>array('NodeCode'=>$node_code,'SECTIONCODE'=>$s_code));
		$seat_res          = $this->seat_model->GetInfoByCond(' STATUS, KIND, PRICELEVEL',$where1,$this->cache);
		foreach($seat_res as $k=>$v) {
			if($v['STATUS'] == 'F') {
				if($lwhole_seat) {
					if($v['KIND'] !='N' && $v['KIND'] != 'W') {
						$seat_res[$k]['STATUS'] = 'B';
					} 
				}else if($v['KIND'] != 'W'){
						$seat_res[$k]['STATUS'] = 'B';
				}
			} else {
				$seat_res[$k]['STATUS'] = 'B';
			}
		}
		$s_price_level_seat_status = $this->format->getSplitString($seat_res, '-','-');
		if(!empty($res1) && !empty($s_price_level_seat_status)) {
			return $res1.'|'.$s_price_level_seat_status;
		} else {
			return '结果集为空';
		}
	}
	function Account3_sdjc($node_code,$order_code,$p_code,$order_passwd,$py_code,$pg_code,$pu_code,$comment) {
		$this->model       = new Load('Model/'.DBDRIVER,'OrderShow');
		$this->model       = new OrderShow();
		$where             = array('cond'=>array('NODECODE'=>$node_code,'ProductCode'=>$p_code,'OrderCode'=>$order_code),'split'=>' AND ');
		$res = $this->model->GetInfoByCond('ORDERSTATUS, SALECLASSCODE, RECEIVABLES',$where,$this->cache);
	    return $this->processAccount3_sdjc($res,$node_code,$order_code,$comment,$py_code,$pu_code,$pg_code,$p_code,$order_passwd,$this->model);
	}
	function processAccount3_sdjc($data,$node_code,$order_code,$comment,$py_code,$pu_code,$pg_code,$p_code,$order_passwd,$order_show_model) {
		$this->model       = new Load('Model/'.DBDRIVER,'OrderClientShow');
		$this->model       = new OrderClientShow();
		$result            = '4294967295';
		$class_code        = isset($data['SALECLASSCODE'])?$data['SALECLASSCODE']:'';
		$rec               = isset($data['RECEIVABLES'])?$data['RECEIVABLES']:'';
		$order_status      = isset($data['ORDERSTATUS'])?$data['ORDERSTATUS']:'';
		$columns           = 'COMMENT = '.$comment;
		$where             = array('cond'=>array('NODECODE'=>$node_code,'ORDERCODE'=>$order_code),'split'=>' AND ');
		
		if(!empty($py_code) && !empty($pg_code) && !empty($pu_code)) {
			$py_code = $py_code;
			$pu_code = $pu_code;
			$pg_code = $pg_code;
		} else {
			$py_code = '';
			$pu_code = '';
			$pg_code = '';
		}
		$s3 = $order_show_model->SetAccount3_sdjc($order_passwd,$node_code,$order_code,$py_code,$pg_code,$pu_code,$p_code);
		if(!empty($comment)) {
			$res               = $this->model->SetCommentByCond($columns,$where);
			if(!res) {
				return $result = 114;
			}
		}
	    if($order_status == 'R') {
	    	return 1;
	    }
	    if($order_status != 'N' && $order_status != 'B') {
	    	return 81;
	    }
	    if($s3) {
	    	return $result = 1;
	    }
	    return $result;
	}
	function GetSeatingArrangement($node_code,$section_code) {
	    $this->model1       = new Load('Model/'.DBDRIVER,'FloorShow');
	    $this->model2       = new Load('Model/'.DBDRIVER,'RowShow');
	    $this->model3       = new Load('Model/'.DBDRIVER,'SeatShow');
		$this->model1       = new FloorShow();
		$this->model2       = new RowShow();
		$this->model3       = new SeatShow();
		$where1             = array('cond'=>array('NODECODE'=>$node_code,'SECTIONCODE'=>$section_code),'split'=>' AND ');
		$where2             = array('cond'=>array('NODECODE'=>$node_code,'SECTIONCODE'=>$section_code),'split'=>' AND ');
		$where3             = array('cond'=>array('NODECODE'=>$node_code,'SECTIONCODE'=>$section_code),'split'=>' AND ');
		$res1               = $this->model1->GetInfoByCond('FLOORNO, NAME as F',$where1);
		
		$res2               = $this->model2->GetInfoByCond('FLOORNO, ROWNO, NAME as R',$where2);
		$res3               = $this->model3->GetInfoByCond('SEATCODE as C, FLOORNO, ROWNO, SEATNO as N,X,Y',$where3);
		//var_dump($res3);
		return $this->processGetSeatingArrangement($res1,$res2,$res3,$node_code,$section_code,$this->model1,$this->model2,$this->model3);
	}
	function processGetSeatingArrangement($data1,$data2,$data3,$node_code,$section_code,$mod1,$mod2,$mod3) {
		$xml = '';
		if(empty($data3)) {
			return false;
		} else {
			$where3             = array('cond'=>array('NODECODE'=>$node_code,'SECTIONCODE'=>$section_code),'split'=>' AND ');
			$res                = $mod3->GetFilesInfoByCond('SEATCODE as C, FLOORNO, ROWNO, SEATNO as N,X,Y',$where3);
			$xml = '<?xml version="1.0" encoding="UTF-8"?><UTN_ONLINESALETICKET_DATAPACKET><METADATA><SEATS><FIELDS>';
			foreach($res as $k=>$v) {
				if($v['name'] == 'FLOORNO') {
					$xml .='<FIELD Name="F"/>';
				} else if($v['name'] == 'ROWNO') {
					$xml .='<FIELD Name="R"/>';
				} else {
					$xml .='<FIELD Name="'.(isset($v['alias'])?$v['alias']:$v['name']).'"/>';	
				}
			}
			$xml .= '</FIELDS></SEATS></METADATA><ROWDATA><SEATS>';
			foreach($data3 as $k=>$v) {
				$lfloor_no = intval($v['FLOORNO']);
				$lrow_no   = intval($v['ROWNO']);
				$list      = '';
				$fileds    = '';
				$c         = '';
				$f         = '';
				$r         = '';
				$n         = '';
				$x         = '';
				$y         = '';
				foreach($data1 as $kk=>$vv) {
					if(intval($vv['FLOORNO']) == $lfloor_no) {
						$f = 'F="'.$vv['F'].'"';
						break 1;
					} else {
						$f = 'F=""';
						break 1;
					}
				}
				foreach($data2 as $kk=>$vv) {
					if(intval($vv['FLOORNO']) == $lfloor_no && intval($vv['ROWNO']) == $lrow_no) {
						$r = 'R="'.$vv['R'].'"';
						break 1;
					} else {
						$r = 'R=""';
						break 1;
					}
				}
				foreach($v as $kk=>$vv) {
					if($kk == 'FLOORNO' || $kk == 'ROWNO' ) {
						continue;
					}
					if($kk == 'C') {
						$c = $kk.'="'.$vv.'"';
					}
					if($kk == 'N') {
						$n = $kk.'="'.$vv.'"';
					}
					if($kk == 'X') {
						$x = $kk.'="'.$vv.'"';
					}
					if($kk == 'Y') {
						$y = $kk.'="'.$vv.'"';
					}
					$fileds = $c.$f.$r.$n.$x.$y;
				}
				$xml .='<ROW '.$fileds.'/>';
			}
			$xml .='</SEATS></ROWDATA></UTN_ONLINESALETICKET_DATAPACKET>';
		}
		return $xml;
	}
}