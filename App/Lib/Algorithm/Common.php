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
}