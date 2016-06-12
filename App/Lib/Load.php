<?php
class Load {
	function __construct($path,$model) {
		if(!file_exists(APPPATH.$path.'/'.$model.'.php')) {
			echo 'file '.APPPATH.$path.'/'.$model.'.php'.' not exits';
		} else {
			require_once APPPATH.$path.'/'.$model.'.php';
		}
		
	}
	
}