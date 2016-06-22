<?php
/**
 * 算法库
 * @author sun_wu
 *
 */
class Common {
	private $instance;
    function __construct() {
    	
    } 
    /**
     * 求出坐标区域 278|351|31|101
     * 公式: 
     * @param string $param 求出坐标所需参数
     * @param int $top 坐标值 278
     * @param int $left 坐标值 351
     * @param int $right 坐标值 $left+101
     * @param int $bottom 坐标值 $top+31
     */
    function getCoorDinate($param) {
        $result        = explode('|',$param);
        $arr['top']    = $result[0];
        $arr['left']   = $result[1];
        $arr['right']  = $arr['left']+$result[3];
        $arr['bottom'] = $arr['top']+$result[2];
        //print_r($arr);
        return $arr;
    }
    /**
     * @规则：有如 str=8-A|2-A|1-B|5-B|3-A|3-B|2-B|1-A|5-A，
     * 1 将其按字母分组
     * 2将分组中的数字按小到大排列
     * 3连续的数字成组
     *
     * 获取不连续的一串数，连续组的成一组
     */
    function getArrByStr($str) {
    	$arr  = explode('|',$str);
    	$res  = array();
    	$fres = array();
    	foreach($arr as $k=>$v) {
    		$arr2 = explode('-',$v);
    		$res[$arr2[1]][] = $arr2[0];
    		asort($res[$arr2[1]]);
    		$str = implode(',', $res[$arr2[1]]);
    		
    		$fres[$arr2[1]] = $this->groupNum($str);
    	}
    	//print_r($res);
    	//print_r($fres);
    	return $fres;
    }
    /**
     * 将连续的数字成组,不连续的数字也成组
     * @param string $str 2,4,5,6,7,9,10,12,21,22,23,50
     */
    function groupNum($str) {
    	$str1         = '';
    	$arr          = array();
    	$sc           = explode(",", $str);
    	foreach($sc as $k=>$v) {
    		$res = intval($v)+1;
    		if(!in_array($res, $sc) ){
    			$str1 .=$v.'|';
    		} else {
    			$str1 .=$v.','; 
    		}
    	}
    	$str1 = substr($str1,0,-1);
    	$arr         = explode('|', $str1);
    	return $arr;
    }
    function Base32ToInt64($str,$base) {
    	
    	$c       = strlen($str);
    	$tmp_val = 0;
    	
    	for($i=0;$i<$c;$i++) {
    		$pos     = strpos($base, $str[$i]);
    		if($pos !== false) {
    			$tmp_val = $tmp_val*32+$pos-1;
    			
    		}
    	}
    	return $tmp_val;
    }
    function Int64ToBase32($val,$digits) {
    	$div       = $val;
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
    function GetDBCode($str) {
    	try{
    		$res = $this->Base32ToInt64($str,BASE32_DIGITS);
    		return strval($res>>32);
    		
    	}catch (Exception $e) {
    		return '';
    	}
    	
    }
}