<?php
class Format {
	private $_format = 'xml';
	private $_func   = '';
	function __construct() {
	}

    function getXmlResult($file_name,$section_name,$section_code,$coords) {
		$string =  '<?xml version="1.0" encoding="UTF-8"?>
				<UTN_ONLINESALETICKET_DATAPACKET>
					<METADATA>
						<Sections>
							<Image FileName="'.$file_name.'"/>
							<Fields>
								<Field Name="SECTIONNAME"/>
								<Field Name="SECTIONCODE"/>
								<Field Name="Coords"/>
							</Fields>
						</Sections>
					</METADATA>
					<ROWDATA>
						<Sections>
							<Row SECTIONNAME="'.$section_name.'" SECTIONCODE="'.$section_code.'" Coords="'.$coords.'"/>
						</Sections>
					</ROWDATA>
				</UTN_ONLINESALETICKET_DATAPACKET>';
     	return $string;
    }
    /**
     * 获取拼接后的字符串
     * @param array $data 要拼接的数组
     * @param string $delimiter 分隔符
     * @param string $delimiter 行分割符
     * @param string $column_seperator 列分割符
     */
    function getSplitString(array $data,$delimiter,$column_seperator='-',$exclude='') {
    	//print_r($data);
    	$result = array();
    	foreach($data as $k=>$v) {
    		if(!empty($exclude) && isset($v[$exclude])) {
    			unset($v[$exclude]);
    		}
    		$result[$k] = implode($column_seperator,$v);
    		
    	}
    	return implode($delimiter,$result);
    }
}