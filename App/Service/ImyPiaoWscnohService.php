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
        $res1 = $this->DeleteRows($fspp);
        return $this->processGetBuyPopedom($fspp,$fsq);
	}
	function DeleteRows(array $data) {
		//$result = array();
		$filter = array();
		foreach($data as $k=>$v) {
			if($v['OBJECTTYPE'] == 'M') {
				$filter[] = $v['SALECLASSCODE'];
			}
		}
		foreach($data as $k=>$v) {
			if($v['OBJECTTYPE'] == 'M'|| ($v['OBJECTCODE'] == 'CTCOM' && $v['ISVALID'] == 'Y' && $v['OBJECTTYPE'] == 'L' && in_array($v['SALECLASSCODE'],$filter))) {
				unset($v);
			}
			/*$result[$k]['OBJECTTYPE']        = $v['OBJECTTYPE'];
			$result[$k]['OBJECTCODE']        = $v['OBJECTCODE'];
			$result[$k]['GROUPCODE']         = $v['GROUPCODE'];
			$result[$k]['SALECLASSCODE']     = $v['SALECLASSCODE'];
			$result[$k]['SALECLASSLEVEL']    = $v['SALECLASSLEVEL'];
			$result[$k]['ISVALID']           = $v['ISVALID'];
			if($result[$k]['OBJECTTYPE'] == 'M') {
				unset($result[$k]);
			}*/
		}
		unset($filter);
		return $data;
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
	function GetSalePolicyByMinDiscount($node_code,$p_code,$seat_list) {
		$seat_codes = $this->SortOutSeatByLevel($seat_list);
		$policy     = $this->GetPolicy($node_code, $p_code);
		$priv       = $this->GetPriv($node_code, $p_code);
		$data2      = $this->GetData2($node_code, $p_code);
		//print_r($data2);
		$custom     = 'POSTTICKETPRICE_SORT';
		$data1      = $this->GetData1($custom);
		//print_r($data1);
		return $this->processSalePolicy($seat_codes,$policy,$priv,$data2,$data1,$node_code,$p_code);
	
	}
	function GetData1($custom) {
		new Load('Model/'.DBDRIVER,'SysInfoShow');
		$model = new SysInfoShow();
		$cond = array('cond'=>array('CUSTOMMODULE'=>$custom));
		return $model->GetInfoByCond('*',$cond);
	}
	function GetData2($node_code, $p_code) {
		new Load('Model/'.DBDRIVER,'PriceShow');
		$model = new PriceShow();
		$cond = array('cond'=>array('NodeCode'=>$node_code,'ProductCode'=>$p_code),'split'=>' AND ');
		return $model->GetAllInfoByCond('PRICELEVEL, PRICE',$cond);
	}
	function processSalePolicy($seat_code,$policy,$priv,$data2,$data1,$node_code,$p_code) {
		//print_r($policy);
		//print_r($priv);
		$result = array();
	    foreach($policy as $k=>$v) {
	    	foreach($priv as $kk=>$vv) {
	    		if($vv['GROUPCODE'] != S_MPWS_GROUPCODE && $vv['OBJECTCODE'] != S_MPWS_USERLEVEL && $vv['SALECLASSCODE'] != $v['SALECLASSCODE']) {
	    			if($vv['OBJECTCODE'] != S_MPWS_USERLEVEL && $vv['OBJECTCODE'] != S_MPWS_USERLEVEL && $v['SALECLASSCODE']) {
	    				unset($v);
	    			}
	    		} else if($vv['ISVALID'] == 'N') {
	    			unset($v);
	    		} else {
	    			if($vv['SALECLASSCODE'] == $v['SALECLASSCODE']) {
	    				$result[$v['SALECLASSCODE']][$k] = $v;
	    			}
	    		}
	    	}
	    }
	    //print_r($result);
	    $total_price = $this->IsPolicyValid($result,$seat_code,$data2,$data1);
	    asort($total_price,SORT_NUMERIC);
	    //print_r($total_price);
	    $r_total = array();
	    foreach($total_price as $k=>$v) {
	    	$r_total[$k] = $v;
	    	break; 
	    }
	    return $this->GetSalePolicyXml($r_total,$node_code,$p_code);
	    //print_r($result);
	    
	    //return $result;
	}
	function GetSalePolicyXml($data,$node_code,$p_code) {
		$key = array_keys($data);
		$val = array_values($data);
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<UTN_ONLINESALETICKET_DATAPACKET>
	<METADATA>
		<BuyPopdoms>
			<FIELDS>
				<FIELD Name="NodeCode"/>
				<FIELD Name="ProductCode"/>
				<FIELD Name="SaleClassCode"/>
				<FIELD Name="SaleClassName"/>
				<FIELD Name="ReceivableTotalPrices"/>
			</FIELDS>
		</BuyPopdoms>
	</METADATA>
	<ROWDATA>
		<BuyPopdoms>
			<ROW NodeCode="'.$node_code.'" ProductCode="'.$p_code.'" SaleClassCode="'.$key[0].'" SaleClassName="普通票" ReceivableTotalPrices="'.$val[0].'"/>
		</BuyPopdoms>
	</ROWDATA>
</UTN_ONLINESALETICKET_DATAPACKET>';
		return $xml;
	}
	function IsPolicyValid($res,$lv_data,$data2,$data1) {
		//print_r($res);
		$fres   = array();
		if(!empty($res)) {
			foreach($res as $k=>$v) {
				$fres[$k] = 0;
				//var_dump($k);
				foreach($v as $kk=>$vv) {
					
					if($vv['SALECLASSTYPE'] == 'T') {
						if(!empty($lv_data)) {
							$c = count($lv_data);
							foreach($lv_data as $a=>$b) {
								if($a!=$vv['PRICELEVEL'] || ($vv['TICKETNUM'] !=1 && $c % $vv['TICKETNUM']!=0)) {
									break 2;
								}
							}
						}
					} else if($vv['SALECLASSTYPE'] == 'D') {
						if(!empty($lv_data)) {
							$c = count($lv_data);
							foreach($lv_data as $a=>$b) {
								if($a!=$vv['PRICELEVEL'] || ($vv['TICKETNUM'] !=1 && $c < intval($vv['TICKETNUM']))) {
									break 2;
								}
							}
						}
					} else if($vv['SALECLASSTYPE'] == 'G') {
						if(!empty($lv_data)) {
							$c = count($lv_data);
							
							foreach($lv_data as $a=>$b) {
								$rs = intval($vv['TICKETNUM'])+ intval($vv['SALECLASSVALUE']);
								//var_dump($a!=$vv['PRICELEVEL'] || ($vv['TICKETNUM'] !=1 && $c % $rs));
								if($a!=$vv['PRICELEVEL'] || ($vv['TICKETNUM'] !=1 && $c % $rs)) {
									break 2;
								}
							}
						}
					}
					//print_r($vv['SALECLASSTYPE']);
					if($vv['SALECLASSTYPE'] == 'T') {
						if(!empty($lv_data) && isset($lv_data[$vv['PRICELEVEL']]) ) {
							$s_value   = $vv['SALECLASSVALUE'];
							$n         = count($lv_data) / $vv['TICKETNUM'];
							$fres[$k] += floatval($s_value) * $n;
							//var_dump(floatval($s_value));
						}
					} else if($vv['SALECLASSTYPE'] == 'D') {
						if(!empty($lv_data) && isset($lv_data[$vv['PRICELEVEL']]) ){
							$s_value   = $vv['SALECLASSVALUE'];
							$s_amount  = count($lv_data);
							//$n         = $s_amount / (intval($vv['TICKETNUM'])+intval($s_value));
							if(!empty($data2)) {
								foreach($data2 as $a=>$b) {
									if(isset($lv_data[$b['PRICELEVEL']])) {
										$price = $b['PRICE'];
										break 1;
									}
								}
							}
							$fres[$k] = $this->Selfroundto($s_value,$s_amount,$data1,$price);
						}
					} else if($vv['SALECLASSTYPE'] == 'G') {
						
						if(!empty($lv_data) && isset($lv_data[$vv['PRICELEVEL']]) ){
							$s_value   = $vv['SALECLASSVALUE'];
							$s_amount  = count($lv_data);
							$n         = $s_amount / (intval($vv['TICKETNUM'])+intval($s_value));
							if(!empty($data2)) {
								foreach($data2 as $a=>$b) {
									if(isset($lv_data[$b['PRICELEVEL']])) {
										$price = $b['PRICE'];
										break 1;
									}
								}
							}
							$fres[$k] += intval($vv['TICKETNUM'])*$price*$n;
							//var_dump(intval($vv['TICKETNUM']),$price,$n);
						}
					}
				}
			}
		}
		return $fres;
	} 
	function Selfroundto($value,$count,$data1,$price) {
		$result = $value/100*$price*$count;
		if($data1['ISVALID'] == 1) {
			$result = round($result);
		} else if($data1['ISVALID'] == 2) {
			$result = intval($result);
		} else {
			$result = round($result,2);
		}
		return $result;
	}
	function SortOutSeatByLevel($list) {
		$result = array();
		if(!empty($list)) {
			$seats = explode('|', $list);
			foreach($seats as $k=>$v) {
				$res = explode('-', $v);
				if(isset($res[1])) {
					$result[$res[1]][] = $res[0];
				}
			}
		}
		return $result;
	}
	function GetPolicy($node_code,$p_code) {
		new Load('Model/'.DBDRIVER,'JoinShow');
		$model  = new JoinShow();
		return $model->GetSalePolicy($node_code, $p_code);
		
	}
	function GetPriv($node_code,$p_code) {
		new Load('Model/'.DBDRIVER,'JoinShow');
		$model  = new JoinShow();
		$data   = $model->spp($node_code, $p_code);
		//print_r($data);
		return $this->DeleteRows($data);
	}
	function CreateOrderAndBooking4($node_code,$p_code,$s_code,$seatl_code,$sale_code,$rec_total_code,$m_code,$c_type,$c_name,$sex,$tel_code,$ship_way,$ship_addr,$id_type,$id_no,$invo_head,$pay_code,$cg_code) {
		return $this->GetCreateOrderAndBooking4Condition($node_code,$s_code,$seatl_code,$p_code,$sale_code,$rec_total_code,$cg_code,$invo_head,$pay_code,$c_name,$sex,$ship_addr,$tel_code,$id_type,$id_no);
	}
	function GetCreateOrderAndBooking4Condition($node_code,$s_code,$seatl_code,$p_code,$sale_code,$rec_total_code,$cg_code,$invo_head,$pay_code,$c_name,$sex,$ship_addr,$tel_code,$id_type,$id_no) {
		$cond1        = $this->GetGCOABCondition1($node_code,$s_code,$seatl_code);
	    $m1           = $this->GetGCOABMethod1($node_code, $p_code, $sale_code,$seatl_code);
	    $cond3        = $this->GetGCOABCondition3($m1,$rec_total_code);
	    //print_r($m1);
	    return $this->processCreateOrderAndBooking4($cond1,$m1,$cond3,$node_code, $p_code, $sale_code,$s_code,$rec_total_code,$cg_code,$invo_head,$pay_code,$c_name,$sex,$ship_addr,$tel_code,$id_type,$id_no,$seatl_code);
	    
	}
	function GetCreateOrderAndBooking4Xml($result_code,$order_code) {
		return '<?xml version="1.0" encoding="UTF-8"?>
<UTN_ONLINESALETICKET_DATAPACKET>
	<METADATA>
		<CreateOrderAndBooking>
			<ResultCode Value="0x"'.$result_code.'"/>
			<OrderCode Value="'.$order_code.'"/>
		</CreateOrderAndBooking>
	</METADATA>
</UTN_ONLINESALETICKET_DATAPACKET>';
	}
	function processCreateOrderAndBooking4($cond1,$m1,$cond3,$node_code, $p_code, $sale_code,$s_code,$rec_total_code,$cg_code,$invo_head,$pay_code,$c_name,$sex,$ship_addr,$tel_code,$id_type,$id_no,$seatl_code) {
		new Load('Lib/','Algorithm');
		$lib          =new Algorithm();
		$seats_info   = $lib->getArrByStr('Common',$seatl_code);
		$result_code  = '4294967295';
		$total_seat_amount = $cond3['TotalSEATAMOUNT'];
		//var_dump($cond1);
		if(!$cond1) {
			$result_code = 66;
			$order_code  = '';
			return $this->GetCreateOrderAndBooking4Xml($result_code,$order_code);
			//return false;
		}
		if(empty($m1)) {
			$result_code = 65;
			$order_code  = '';
			return $this->GetCreateOrderAndBooking4Xml($result_code,$order_code);
			//return false;
		}
		if(!$cond3['boolean']){
			$order_code  = '';
			return $this->GetCreateOrderAndBooking4Xml($result_code,$order_code);
	
		}
		//if(empty($m1))
		$member_id    = '-1';
		$client_type  = '3';
		new Load('Model/'.DBDRIVER, 'JoinShow');
		new Load('Model/'.DBDRIVER, 'OrderShow');
		$join_model   = new JoinShow();
		$order_model  = new OrderShow();
		$a_info       = $join_model->GetInfoA($node_code, $p_code, $sale_code);
		$data7        = $join_model->GetData7InfoByCond($node_code, $p_code, $sale_code);
		$order_code   = $this->GetOrderCode($s_code,$lib);
		$order_code_bar_code = 'test123';
		$data1 = array('NODECODE'=>$node_code,'ORDERCODE'=>$order_code,'DOMAINCODE'=>'A','PRODUCTCODE'=>$p_code,'BARCODE'=>$order_code_bar_code,'SectionCode'=>$s_code,'SECTIONCODE'=>$s_code,'PRINTTICKET'=>'1','ORDERSTATUS'=>'N','ORDERPASSWORD'=>NULL,'LASTTICKETCODE'=>NULL,'ENDTIME'=>NULL,'SEATCOUNT'=>$total_seat_amount,'RECEIVABLES'=>$rec_total_code,'SYSTEMBOOKING'=>'N','CREATENODE'=>$node_code,'CREATEGROUP'=>$cg_code,'CREATEUSER'=>UserCode,'CREATETIME'=>"cast('now' as timestamp)",'TERMINALCODE'=>TerminalCode,'DISCOUNTMENDER'=>NULL,'DISCOUNTCHANGETIME'=>NULL,'PAYEENODE'=>NULL,'PAYEEGROUP'=>NULL,'PAYEE'=>NULL,'GATHERINGTIME'=>NULL,'GATHERINGPLACE'=>NULL,'ISPLACERESERVE'=>'N','ISGIFT'=>'N','ISPRINTPRICE'=>$data7['ISPRINTED'],'SALECLASSCODE'=>$sale_code,'ISSUITE'=>$a_info[0]['ISSUITE'],'ORDERFROM'=>'3','CLIENTTYPE'=>$client_type,'MEMBERID'=>$member_id,'INVOICEHEAD'=>$invo_head,'FETCHWAY'=>$ship_way,'PAYMODECODE'=>$pay_code);
		$data2 = array('NODECODE'=>$node_code,'ORDERCODE'=>$order_code,'PRODUCTCODE'=>$p_code,'CNAME'=>$c_name,'SEX'=>$sex,'CADDRESS'=>$ship_addr,'TEL'=>$tel_code,'IDENTTYPEID'=>$id_type,'IDENTCODE'=>$id_no,'SALESORT'=>'01');
		$data3 = array('columns_insert'=>'NODECODE, 
ORDERCODE, PRICELEVEL, PRINTORIGINAL, TICKETPRICE, DISCOUNT, SEATAMOUNT, 
PRICE, TOTALPRICE','columns_select'=>'`'.$node_code.'`,`'.$order_code.'`','cond'=>"NODECODE = '".$node_code."' 
and SALECLASSCODE = '".$sale_code."' and PRODUCTCODE = '".$p_code."'");
		$result_code  = $order_model->InsertInfoByCond($m1,$data1,$data2,$data3,$seats_info);
	    return $this->GetCreateOrderAndBooking4Xml($result_code,$order_code);
	}
	function GetGCOABCondition3($data,$rec_total_code) {
		$cal_total_prices = 0;
		$cal_total_seat_amount = 0;
		$arr              = array();
		foreach($data as $k=>$v) {
			$cal_total_prices += $v['TOTALPRICE'];
			$cal_total_seat_amount += $v['SEATAMOUNT'];
		}
		if(floatval($rec_total_code) - floatval($cal_total_prices) <=0.01) {
			$arr['boolean'] = true;
			$arr['TotalSEATAMOUNT'] = $cal_total_seat_amount;
			return $arr;
		} else {
			$arr['boolean'] = false;
			$arr['TotalSEATAMOUNT'] = 0;
			return $arr;
		}
	}
	function SelfRound($data,$flag) {

		if(1 == $flag) {
			return round($data);
		} else if(2 == $flag) {
			return intval($data);
		} else {
			return round($result,2);
		}
	}
	function GetGCOABMethod1($node_code, $p_code, $sale_code,$seatl_code) {
		new Load('Model/'.DBDRIVER,'JoinShow');
		new Load('Model/'.DBDRIVER,'PriceShow');
		new Load('Model/'.DBDRIVER, 'SysInfoShow');
		$sys_model = new SysInfoShow();
		$where = array('cond'=>array('CUSTOMMODULE'=>'POSTTICKETPRICE_SORT'));
		$data5 = $sys_model->GetInfoByCond('*',$where);
		$flag  = isset($data5['ISVALID'])?intval($data5['ISVALID']):0;
		new Load('Lib/','Algorithm');
		$lib                 = new Algorithm();
		$price_mode          = new PriceShow();
		$model               = new JoinShow();
		$where               = array('cond'=>array('NodeCode'=>$node_code,'ProductCode'=>$p_code));
		$a_info              = $model->GetInfoA($node_code, $p_code, $sale_code);
		$price               = $price_mode->GetAllInfoByCond('PRICELEVEL, PRICE',$where);
		$result              = $lib->getArrByStr('Common',$seatl_code);
		$c                   = array();
		$result1             = array();
		foreach($result as $k=>$v) {
			$cc = 0;
			foreach($v as $kk=>$vv) {
				$seat_info      = explode(',',$vv);
				$cc         += count($seat_info);
			}
			$c[$k]                            = $cc;
			$result1[$k]['PRICELEVEL']        = $k;
			$result1[$k]['SEATAMOUNT']        = $c[$k];
			$result1[$k]['TOTALPRICE']        = number_format(0,1);                  
			foreach($a_info as $kk=>$vv) {
				if($vv['SALECLASSTPYE'] == 'T') {
					$l_sale_class_value = $vv['SALECLASSVALUE'];
					$n                  = $c[$k] / intval($v['TICKETNUM']);
					$result1[$k]['TOTALPRICE']        += $l_sale_class_value;

				} else if($vv['SALECLASSTPYE'] == 'D') {
					$l_sale_class_value = $vv['SALECLASSVALUE'];
					$l_seat_amount      = $c[$k];
					foreach ($price as $kk=>$vv) {
						if($vv['PRICELEVEL'] == $k) {
							$price2     = $vv['PRICE'];
							break 1;
						}
					}
					$val                = floatval($vv['SALECLASSVALUE'])/100*$price2;
					
					$self_val           = $this->SelfRound($val,$flag);
					$result1[$k]['TOTALPRICE']        += $self_val*$l_seat_amount;  
				} else if($vv['SALECLASSTPYE'] == 'G') {
					foreach ($price as $kk=>$vv) {
						if($vv['PRICELEVEL'] == $k) {
							$price2     = $vv['PRICE'];
							break 1;
						}
					}
					$l_sale_class_value = $vv['SALECLASSVALUE'];
					$l_seat_amount      = $c[$k];
					$val                = intval($l_sale_class_value)+$vv['TICKETNUM'];
					$n                  = $l_seat_amount/$val;
					$result1[$k]['TOTALPRICE']        += $vv['TICKETNUM']*$price2*$n;
				} 
			}
			foreach($price as $kk=>$vv) {
				if($vv['PRICELEVEL'] == $k) {
					$prices = floatval($vv['PRICE']);
				} else {
					$prices = 0;
				}
				$val = ($result1[$k]['TOTALPRICE']*100)/($result1[$k]['SEATAMOUNT']*$prices);
				$result1[$k]['DISCOUNT'] = round($val);
				foreach($a_info as $a=>$b) {
					if($b['SALECLASSTPYE'] == 'G') {
						$result1[$k]['TICKETPRICE'] = $vv['PRICE'];
					} else {
						$vals = $vv['TOTALPRICE']/$vv['SEATAMOUNT'];
						$result1[$k]['TICKETPRICE'] = round($vals,2);
					}
				}
			}
		}
        return $result1;
	}
	function GetGCOABCondition1($node_code,$s_code,$seatl_code) {
		$data1        = array();
		$this->lib    = new Load('Lib/','Algorithm');
		new Load('Model/'.DBDRIVER,'SeatShow');
		$this->lib    = new Algorithm();
		$model        = new SeatShow();
		$return       = false;
		//$seatl_code          = '8-A|2-A|1-B|5-B|3-A|3-B|2-B|1-A|5-A|6-A';
		$result       = $this->lib->getArrByStr('Common',$seatl_code);
		//print_r($result);
		foreach($result as $k=>$v) {
			foreach($v as $kk=>$vv) {
				$seat_info      = explode(',',$vv);
				$c              = count($seat_info)-1;
				$seat_min       = $seat_info[0];
				$seat_max       = $seat_info[$c];
				$seat_level     = $k;
				$return = $model->GetSeatInfoByCond($node_code,$s_code,$seat_min,$seat_max,$seat_level, $this->cache);
			}
		}
		return $return;
		
	}
	function GetOrderCode($s_code,$model) {
		new Load('Model/'.DBDRIVER, 'Systems');
		$order_model = new Systems(); 
		$ldb_code    = $model->GetDBCode('TypeConvert',$s_code);
		$result      = $order_model->GetInfoByCond('GEN_ID(GENE_ORDER_CODE,1)');
	    //print_r($ldb_code);
		$lpk_id      = $result['GEN_ID'];
		return $this->GeneratePKID($ldb_code,$lpk_id,$model);
	}
	function GeneratePKID($ldb_code,$new_id,$model) {
		$value = intval($ldb_code)<<32 + intval($new_id);
		//var_dump($value);
		return $model->Int64ToBase32('TypeConvert',$value,8);
	}
}