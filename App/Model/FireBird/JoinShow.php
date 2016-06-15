<?php
class JoinShow extends \Db{
	private $_table1 = '';
	private $_table2 = '';
	private $_table3 = '';
	private $_async = false;
	private $_cache = false;
	function __construct($async=false) {
		parent::__construct();
		$this->_table1 = $this->_prefix.'TICKET_SHOW';
		$this->_table2 = $this->_prefix.'PRODUCT';
		$this->_table3 = $this->_prefix.'PLACE';
		$this->_async = $async;
	}
	function TicketPpShowByCond($node_code,$bar_code,$cache=false) {
		$sql ="SELECT
					t.NodeCode,
					t.ProductCode,
					p.CName as ProductCName,
					p.EName as ProductEName,
					p.HappenTime,
					t.PlaceCode,
					pl.CName as PlaceCName,
					pl.EName as PlaceEName,
					t.PriceLevel,
					t.Price,
					t.SectionName,
					t.FloorName,
					t.RowName,
					t.SeatName,
					t.Status,
					t.ParValue 
				FROM
					".$this->_table1." t
						INNER JOIN
						".$this->_table2." p
						ON
						t.ProductCode = p.ProductCode
						INNER JOIN
						".$this->_table3." pl
						ON
						t.PlaceCode = pl.PlaceCode
				WHERE
					t.NodeCode = '".$node_code."' AND
					t.BarCode = '".$bar_code."'";
       return $this->fetchAll($sql,array(),$this->_async,$cache);
	}
	function GetSection2ByCond($node_code,$p_code,$cache=false) {
		$sql = "SELECT
					m.BACKPIC as MAPBACKPIC,
					s.CNAME as SECTIONNAME,
					s.SECTIONCODE,
					s.EXTRASTRING
				FROM
					utn_map_show m
						INNER JOIN
						utn_section_show s
						ON
						m.NodeCode = s.NodeCode AND
					m.mapcode = s.mapcode
				WHERE
					m.NodeCode = '".$node_code."' AND
					m.productcode = '".$p_code."'
					";
		$res = $this->fetchAll($sql,array(),false,$cache);
		//print_r($res);
		$this->lib    = new Load('Lib/','Algorithm');
		$this->format = new Load('Lib/Format','Format');
		$this->lib    = new Algorithm();
		$this->format = new Format();
		$position     = $res['EXTRASTRING'];
		$result       = $this->lib->getCoorDinate('Common',$position);
		//print_r($result);
		$coords       = implode(',', $result);
		return $this->format->getXmlResult($res['MAPBACKPIC'],$res['SECTIONNAME'],$res['SECTIONCODE'],$coords);
		
	}
	function GetSectionAndGroupByCond($node_code,$s_code,$cache=false) {
		$sql = "SELECT
					count(*) as RecordCount
				FROM
					utn_section_show se
						INNER JOIN
						utn_sectiongroup_show sg
						ON
						se.productcode = sg.productcode AND
					se.nodecode = sg.nodecode AND
					se.SWSC_SECTIONGROUPCODE = sg.sectiongroupcode AND
					sg.sectiongroupclass = 2 AND
					se.nodecode = ".$node_code." AND
					sectioncode = '".$s_code."'
					";
		return $this->fetch($sql,array(),false,$cache);
	}
	function test() {
		$sql = "select * from UTN_PLACE ";
		return $this->fetch($sql,array(),false,true);
		//return $this->getServerInfo();
	}
	function spp($node_code,$p_code,$cache=false) {
		$sql = "Select p.ObjectType,p.OBJECTCODE, p.GROUPCODE, p.SALECLASSCODE, p.SALECLASSLEVEL, o.ISVALID 
from UTN_SALECLASS_PRIVILEGE p inner join UTN_PRODUCT_OBJECT_SHOW o on p.NODECODE = o.NODECODE 
and p.PRODUCTCODE = o.PRODUCTCODE and p.ObjectCode = o.ObjectCode and p.GroupCode = o.GroupCode 
and p.ObjectType = o.ObjectType where p.NODECODE = '".$node_code."' and p.PRODUCTCODE = '".$p_code."' 
and   o.PrivilegeType = 'S' and ((p.GROUPCODE = '".S_MPWS_GROUPCODE."'  and p.ObjectType = 'L' and p.OBJECTCODE = '".S_MPWS_USERLEVEL."') 
or (p.ObjectType = 'M'))";
		return $res = $this->fetchAll($sql,array(),$this->_async,$cache);
		
	}
	function fsq($node_code,$p_code,$cache) {
		$sql = "Select s.SaleClassCode, s.CName as SaleClassName, s.SortCode, s.TicketNum, s.IsSuite,s.ISPRINTED, l.PriceLevel
, p.Price, l.TotalPrices, l.GiftNum, l.Discount 
from UTN_SALECLASS s 
inner join UTN_SALECLASSLEVEL l on s.NodeCode = l.NodeCode and s.ProductCode = l.ProductCode and s.SaleClassCode = l.SaleClassCode 
inner join UTN_PRICE_SHOW p on l.Productcode = p.Productcode and l.Nodecode = p.Nodecode and l.PriceLevel = p.PriceLevel 
where s.NodeCode = '".$node_code."' and s.ProductCode = '".$p_code."' and s.SortCode = '01' and p.Price <> 0 
and (s.applytime <= cast('now' as timestamp) and s.endtime >= cast('now' as timestamp)) 
order by s.SaleClassCode, l.PriceLevel";
		return $res = $this->fetchAll($sql,array(),$this->_async,$cache);
	}
	function sectionGroupSeatPriceShow($node_code,$p_code,$cache) {
		$sql = "select s.PRICELEVEL,count(*) as AMOUNT,e.PRICE from utn_section_show t 
left join utn_sectiongroup_show g on(t.SWSC_SECTIONGROUPCODE=g.sectiongroupcode and t.productcode=g.productcode and t.nodecode=g.nodecode) 
left join utn_seat_show s on (t.nodecode=s.nodecode and t.mapcode=s.mapcode and s.sectioncode=t.sectioncode) 
left join utn_price_show e on (t.nodecode=e.nodecode and t.productcode=e.productcode and s.pricelevel=e.pricelevel) 
where  t.NodeCode = ".$node_code." and t.productcode='".$p_code."' and s.STATUS='F' and (KIND='W' or (t.SWSC_SECTIONGROUPCODE<>'' and KIND='N'))
group by s.pricelevel,e.PRICE";
		return $res = $this->fetchAll($sql,array(),$this->_async,$cache);
	}
	function SectionAndSectionGroupShow($node_code,$section_code,$cache = false) {
		$sql = "select count(*) as RecordCount from utn_section_show se 
inner join utn_sectiongroup_show sg on se.productcode = sg.productcode 
and se.nodecode = sg.nodecode and se.SWSC_SECTIONGROUPCODE = sg.sectiongroupcode 
and sg.sectiongroupclass = 2 and se.nodecode = '".$node_code."' and sectioncode = '".$section_code."'";
		return $res = $this->fetch($sql,array(),$this->_async,$cache);
	}
	function GetSalePolicy($node_code,$p_code,$cache=false) {
		$sql = "Select s.SaleClassCode, s.CName as SaleClassName, s.SortCode, s.TicketNum, s.IsSuite,s.ISPRINTED, l.PriceLevel
, p.Price, l.TotalPrices, l.GiftNum, l.Discount 
from UTN_SALECLASS s 
inner join UTN_SALECLASSLEVEL l on s.NodeCode = l.NodeCode and s.ProductCode = l.ProductCode and s.SaleClassCode = l.SaleClassCode 
inner join UTN_PRICE_SHOW p on l.Productcode = p.Productcode and l.Nodecode = p.Nodecode and l.PriceLevel = p.PriceLevel 
where s.NodeCode = '".$node_code."' and s.ProductCode = '".$p_code."' and s.SortCode = '01' and p.Price <> 0 
and (s.applytime <= cast('now' as timestamp) and s.endtime >= cast('now' as timestamp)) 
order by s.SaleClassCode, l.PriceLevel";
		$res = $this->fetchAll($sql,array(),$this->_async,$cache);
		if(!empty($res)) {
			foreach($res as $k=>$v) {
				if(!is_null($v['TOTALPRICES']) && !empty($v['TOTALPRICES'])) {
					$res[$k]['SALECLASSTYPE']  = 'T';
					$res[$k]['SALECLASSVALUE'] = $v['TOTALPRICES'];
				} else if(!is_null($v['GIFTNUM']) && !empty($v['GIFTNUM'])) {
					$res[$k]['SALECLASSTYPE']  = 'G';
					$res[$k]['SALECLASSVALUE'] = $v['GIFTNUM'];
				} else if(!is_null($v['DISCOUNT']) && !empty($v['DISCOUNT'])) {
					//var_dump(!is_null($v['DISCOUNT']) || !empty($v['DISCOUNT']));
					$res[$k]['SALECLASSTYPE']  = 'D';
					$res[$k]['SALECLASSVALUE'] = $v['DISCOUNT'];
					//var_dump($res[$k]['SALECLASSTYPE']);
				}
			}
		}
		return $res;
	}
}