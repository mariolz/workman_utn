<?php
//use Workerman\Worker;
class Test {
	private $model   = null;
	private $_worker_cfg = null;
	function __construct($worker_cfg) {
		$this->_worker_cfg = $worker_cfg;
	}
	function index() {
		$this->model = new Load('Model/'.DBDRIVER,'Shows');
		$this->model = new Shows();
		$res = $this->model->test();
		return json_encode($res,true);
	}
	
}