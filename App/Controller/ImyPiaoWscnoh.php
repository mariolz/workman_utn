<?php
//use Workerman\Worker;
class ImyPiaoWscnoh {
	private $model       = null;
	private $_worker_cfg = null;
	private $_filter     = null;
	private $_service    = null;
	private $_class_name = '';
	function __construct($worker_cfg) {
		$filter             = new Load('Lib','Filter');
		$this->_filter      = new Filter();
		$this->_class_name  = get_class($this);
		$this->_service     = new Load('Service',$this->_class_name.'Service');
		$service_class      = $this->_class_name.'Service';
		$this->_service     = new $service_class;
	}
	function index() {}
	function GetProducts() {
		$this->model = new Load('Model/'.DBDRIVER,'Products');
		$node_code    = $this->_filter->post('NodeCode');
		$this->model = new Products();
		$res = $this->model->getInfoByNodeCode($node_code,false);
		return json_encode($res,true);
	}
	/**
	 * 取消订单
	 * 成功返回影响的行数或true否则返回false
	 * @return int/boolean
	 */
	function CancelOrder() {
		$this->model = new Load('Model/'.DBDRIVER,'OrderShow');
		$this->model = new OrderShow();
		$node_code     = $this->_filter->post('NodeCode');
		$order_code    = $this->_filter->post('OrderCode');
		$res = $this->model->UpOrderShowByCond($node_code,$order_code);
		return json_encode($res,true);
	}
	/**
	 * 编辑发货信息
	 * @return string
	 */
	function EditShippingInfo() {
		$node_code     = $this->_filter->post('NodeCode');
		$order_code    = $this->_filter->post('OrderCode');
		$c_name        = $this->_filter->post('CustomerName');	
		$mobile_no     = $this->_filter->post('MobileNo');
	    $shipping_addr = $this->_filter->post('ShippingAddress');
		//$is_eticket    = $this->_filter->post('IsETicket');
		//$m_id          = $this->_filter->post('MemberID');
		$this->model   = new Load('Model/'.DBDRIVER,'OrderClientShow');
		$this->model   = new OrderClientShow();
		$res = $this->model->UpOrderClientShowByCond($node_code,$order_code,$c_name,$mobile_no,$shipping_addr);
		return json_encode($res,true);
	}
	/**
	 * 获取票信息
	 * @return string
	 */
	function GetTicketInfo() {
		$node_code     = $this->_filter->post('NodeCode');
		$bar_code      = $this->_filter->post('BarCode');
		$this->model   = new Load('Model/'.DBDRIVER,'JoinShow');
		$this->model   = new JoinShow();
		$res           = $this->model->TicketPpShowByCond($node_code,$bar_code,false);
		return json_encode($res,true);
	}
	function GetPriceArr() {
		$node_code     = $this->_filter->post('NodeCode');
		$p_code        = $this->_filter->post('ProductCode');
		//var_dump($node_code);
		$this->model   = new Load('Model/'.DBDRIVER,'PriceShow');
		$this->model   = new PriceShow();
		$res           = $this->model->GetPrice($node_code,$p_code);
		return json_encode($res,true);
	}
	function GetSections2() {
		$node_code     = $this->_filter->post('NodeCode');
		$p_code        = $this->_filter->post('ProductCode');	
		$this->model   = new Load('Model/'.DBDRIVER,'JoinShow');
		$this->model   = new JoinShow();
		$res           = $this->model->GetSection2ByCond($node_code, $p_code);
		$res           = $this->_filter->filterXml($res);
		return json_encode(array($res),true);
	}
	/**
	 * 获取座位状态
	 * @return string
	 */
	function GetSeatStatus() {
		$node_code     = $this->_filter->post('NodeCode');
		$p_code        = $this->_filter->post('ProductCode');
		$s_code        = $this->_filter->post('SectionCode');
		$res           = $this->_service->GetSeatStatus($node_code,$p_code,$s_code);
		return json_encode(array($res),true);
	}
	/**
	 * 
	 * @return string
	 */
	function GetPopedom() {
		try{
			$node_code     = $this->_filter->post('NodeCode');
			$p_code        = $this->_filter->post('ProductCode');
			$res = $this->_service->GetPopedom($node_code,$p_code);
			return json_encode(array($res),true);
		}catch (Exception $e) {
			return '4294967295';
		}

	}
	function GetBuyPopedom() {
		$node_code     = $this->_filter->post('NodeCode');
		$p_code        = $this->_filter->post('ProductCode');
		$xml           = $this->_service->GetBuyPopedom($node_code,$p_code);
		$res = $this->_filter->filterXml($xml);
		return json_encode(array($res),true);
	}
	function GetLevelCount() {
		$node_code     = $this->_filter->post('NodeCode');
		$p_code        = $this->_filter->post('ProductCode');
		$xml = $this->_service->GetLevelCount($node_code,$p_code);
		$res = $this->_filter->filterXml($xml);
		return json_encode(array($res),true);
	}
	function UpdateOrderPass() {
		$node_code     = $this->_filter->post('NodeCode');
		$order_code    = $this->_filter->post('OrderCode');
		$passwd        = $this->_filter->post('Password');
		$res = $this->_service->UpdateOrderPass($node_code,$order_code,$passwd);
		return json_encode(array($res),true);
	}
	function BingOrder() {
		$node_code     = $this->_filter->post('NodeCode');
		$order_code    = $this->_filter->post('OrderCode');
		$p_code        = $this->_filter->post('ProductCode');
		$m_code        = $this->_filter->post('MemberID');
		$m_class_id    = $this->_filter->post('MemberClassID');
		$c_name        = $this->_filter->post('CustomerName');
		$sex           = $this->_filter->post('Sex');
		$tel           = $this->_filter->post('Telephone');
		$shipping_addr = $this->_filter->post('ShippingAddress');
		$is_eticket    = $this->_filter->post('IsETicket');	
		$res = $this->_service->BingOrder($node_code,$order_code,$p_code,$m_code,$m_class_id,$c_name,$sex,$tel,$shipping_addr,$is_eticket);
		$res           = $this->_filter->filterXml($res);
		return json_encode(array($res),true);
	}
	function GetMemberClassWebSale() {
		$node_code          = $this->_filter->post('NodeCode');
		$sale_class_code    = $this->_filter->post('SaleClassCode');
		$p_code             = $this->_filter->post('ProductCode');
		$m_class_id         = $this->_filter->post('MemberClassID');
		$res                = $this->_service->GetMemberClassWebSale($node_code, $p_code, $sale_class_code, $m_class_id);
		return json_encode(array($res),true);
	}
	function Account2() {
		$node_code     = $this->_filter->post('NodeCode');
		$order_code    = $this->_filter->post('OrderCode');
		$p_code        = $this->_filter->post('ProductCode');
		$order_passwd  = $this->_filter->post('OrderPassword');
		$res           = $this->_service->Account2($node_code,$order_code,$p_code,$order_passwd);
		return json_encode(array($res),true);
	}
	function Account3() {
		$node_code     = $this->_filter->post('NodeCode');
		$order_code    = $this->_filter->post('OrderCode');
		$p_code        = $this->_filter->post('ProductCode');
		$order_passwd  = $this->_filter->post('OrderPassword');
		$py_code       = $this->_filter->post('PayNodeCode');
		$pg_code       = $this->_filter->post('PayGroup');
		$pu_code       = $this->_filter->post('PayUser');
		$ship_way      = $this->_filter->post('ShippingWay');
		$ship_addr     = $this->_filter->post('ShippingAddress');
		$res           = $this->_service->Account3($node_code,$order_code,$p_code,$order_passwd,$py_code,$pg_code,$pu_code,$ship_way,$ship_addr);
		return json_encode(array($res),true);
	}
	function Account4() {
		$node_code     = $this->_filter->post('NodeCode');
		$order_code    = $this->_filter->post('OrderCode');
		$p_code        = $this->_filter->post('ProductCode');
		$order_passwd  = $this->_filter->post('OrderPassword');
		$py_code       = $this->_filter->post('PayNodeCode');
		$pg_code       = $this->_filter->post('PayGroup');
		$pu_code       = $this->_filter->post('PayUser');
		$pm_way        = $this->_filter->post('PayModeCode');
		$res           = $this->_service->Account4($node_code,$order_code,$p_code,$order_passwd,$py_code,$pg_code,$pu_code,$pm_way);
		return json_encode(array($res),true);
	}
	/**
	 *UTN_SEASONPOLICY 没有表，待解决
	 */
	function Season_GetSeatStatus() {
		$node_code     = $this->_filter->post('NodeCode');
		$sp_code       = $this->_filter->post('SeasonPolicyCode');
		$s_code        = $this->_filter->post('SectionCode');
		$res           = $this->_service->Season_GetSeatStatus($node_code,$sp_code,$s_code);
		return json_encode(array($res),true);
	}
	function Account3_sdjc() {
		$node_code     = $this->_filter->post('NodeCode');
		$order_code    = $this->_filter->post('OrderCode');
		$p_code        = $this->_filter->post('ProductCode');
		$order_passwd  = $this->_filter->post('OrderPassword');
		$py_code       = $this->_filter->post('PayNodeCode');
		$pg_code       = $this->_filter->post('PayGroup');
		$pu_code       = $this->_filter->post('PayUser');
		$comment       = $this->_filter->post('Comment');
		$res           = $this->_service->Account3_sdjc($node_code,$order_code,$p_code,$order_passwd,$py_code,$pg_code,$pu_code,$comment);
		return json_encode(array($res),true);
	}
	function GetSeatingArrangement() {
		$node_code       = $this->_filter->post('NodeCode');
		$section_code    = $this->_filter->post('SectionCode');
		$xml             = $this->_service->GetSeatingArrangement($node_code,$section_code);
		$res             = $this->_filter->filterXml($xml);
		return json_encode(array($res),true);
	}
    function GetSalePolicyByMinDiscount() {
    	$node_code       = $this->_filter->post('NodeCode');
    	$p_code          = $this->_filter->post('ProductCode');
    	$seat_list       = $this->_filter->post('SeatList');
    	$xml             = $this->_service->GetSalePolicyByMinDiscount($node_code,$p_code,$seat_list);
    	$res             = $this->_filter->filterXml($xml);
    	return json_encode(array($res),true);
    }
    /**
     * @todo 明天把MYSQL的集成好
     * @return string
     */
    function CreateOrderAndBooking4() {
    	$node_code       = $this->_filter->post('NodeCode');
    	$p_code          = $this->_filter->post('ProductCode');
    	$s_code          = $this->_filter->post('SectionCode');
    	$seatl_code      = $this->_filter->post('SeatList');
    	$sale_code       = $this->_filter->post('SaleClassCode');
    	$rec_total_code  = $this->_filter->post('ReceivableTotalPrices');
    	$m_code          = $this->_filter->post('MemberID');
    	$c_type          = $this->_filter->post('CustomerType');
    	$c_name          = $this->_filter->post('CustomerName');
    	$sex             = $this->_filter->post('Sex');
    	$tel_code        = $this->_filter->post('Telephone');
    	$ship_way        = $this->_filter->post('ShippingWay');
    	$ship_addr       = $this->_filter->post('ShippingAddress');
    	$id_type         = $this->_filter->post('IdentityType');
    	$id_no           = $this->_filter->post('IdentityNo');
    	$invo_head       = $this->_filter->post('InvoiceHead');
    	$pay_code        = $this->_filter->post('PayModeCode');
    	$cg_code         = $this->_filter->post('CreateGroup');
    	$xml             = $this->_service->CreateOrderAndBooking4($node_code,$p_code,$s_code,$seatl_code,$sale_code,$rec_total_code,$m_code,$c_type,$c_name,$sex,$tel_code,$ship_way,$ship_addr,$id_type,$id_no,$invo_head,$pay_code,$cg_code);
    	$res             = $this->_filter->filterXml($xml);
    	return json_encode(array($res),true);
    }
    function Test() {
    	//$str = $this->_filter->post('str');
    	$str      = 'B0A';
    	new Load('Lib/','Algorithm');
    	$a        = new Algorithm();
    	$a->GetDBCode('Common',$str);
    }
}