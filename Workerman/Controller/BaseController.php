<?php
namespace Workerman\Controller;
use Workerman\Lib\Load;
class BaseController {
	protected  $load = null;
	function __construct() {
		$this->load = new Load();
	}
}