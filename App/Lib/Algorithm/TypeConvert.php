<?php
/**
 * 类型转换加密算法库
 * @author sun_wu
 *
 */
class TypeConvert {
	private $instance;
    function __construct() {
    	
    } 
    function Base32ToInt64($str,$base) {
    	$c       = strlen($str);
    	$tmp_val = 0;
    	
    	for($i=0;$i<$c;$i++) {
    		$pos     = strpos($base, $str[$i]);
    		
    		if($pos !== false) {
    			//var_dump($str[$i].'===='.$pos);
    			$tmp_val = $tmp_val*32+$pos;
    			
    		}
    	}
    	return $tmp_val;
    }
    function Int64ToBase32($param) {
    	$div       = $param[1];
    	$digits    = $param[2];
    	
    	$base      = BASE32_DIGITS;
    	$str       = '';
    	$remainder = 0;
    	$len       = 0;
    	while($div > 0) {
    		$remainder = $div % 32;
    		$div       =  intval($div/32);
    		$str      +=   $base[$remainder+1];
    	}  
    	$len       = strlen($str);
    	if($len < $digits) {
    		$f = $digits - $len;
    		$str .= str_repeat('0',$f );
    		return strrev($str);
    	}
    	return $str;
    }
    function GetDBCode($param) {
    	try{
    		
    		$res = $this->Base32ToInt64($param[1],BASE32_DIGITS);
    		return strval($res>>32);
    		
    	}catch (Exception $e) {
    		return '';
    	}
    	
    }
}