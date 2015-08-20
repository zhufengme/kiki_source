<?php
if(!defined("KKF_INIT")){
    die("break in");
}

/**
 * 
 * zhufHelper 类，用于放置一些常用函数
 * @author Andie Zhu
 *
 */

class helper {


	/**
	 * 判断是否为合法变量名
	 *
	 * @param string $str 待验证字符串
	 */

	public static function is_vaild_name($str){
		if(preg_match('/^[a-zA-Z][a-zA-Z0-9_]{4,20}$/',$str)){
			return true;
		}else{
			return false;
		}
	}


	/**
	 * 将数组写入 ini 文件
	 * @param array $arr 待写入的数组
	 * @param string $filename 目标ini文件
	 */

	public static function write_ini($arr , $filename){

		$str_data = null;

		foreach($arr as $key => $value){
			$str_data .= $key;
			$str_data .= " = ";
			if(strstr($value,"|") || strstr($value,"\\") || strstr($value,"/")){
				$str_data .= "\"". $value . "\"";
			}else{
				$str_data .=  $value ;
			}
			$str_data.="\n";
		}

		file_put_contents($filename,$str_data);
		return;
	}
	
	/**
	 * 二维数组排序
	 * 
	 * @param array $arr
	 * @param string $keys
	 * @param string $type
	 * @return array
	 */
	public static function array_sort($arr,$keys,$type='asc'){
		$keysvalue = $new_array = array();
		foreach ($arr as $k=>$v){
			$keysvalue[$k] = $v[$keys];
		}
		if($type == 'asc'){
			asort($keysvalue);
		}else{
			arsort($keysvalue);
		}
		reset($keysvalue);
		foreach ($keysvalue as $k=>$v){
			$new_array[$k] = $arr[$k];
		}
		return $new_array;
	}
	

	/**
	 * 
	 * 通过起始标记，获取中间的内容
	 * @param string $str 待提取内容的字符串
	 * @param string $start 起始内容
	 * @param string $end 结束内容
	 */
	
	public static function get_str_by_tag($str, $start, $end) {
		if (! self::instr ( $str, $start )) {
			return false;
		}
		if (! self::instr ( $str, $end )) {
			return false;
		}
		$s = strpos ( $str, $start ) + strlen ( $start );
		$s1 = substr ( $str, $s, strlen ( $str ) );
		$e = strpos ( $s1, $end ) - strlen ( $s1 );
		$s2 = substr ( $s1, 0, $e );
		return $s2;
	}
	
	/**
	 * 
	 * 获得文件名中的扩展名
	 * @param string $path 文件名
	 */
	public static function get_filetype($path) {
		$pos = strrpos ( $path, '.' );
		if ($pos !== false) {
			return substr ( $path, $pos );
		} else {
			return '';
		}
	}
	
	/**
	 * 
	 * 判断字符串是否包含了某个字符串
	 * @param string $str 字符串
	 * @param string $needle 需要查找的字符串
	 */
	
	public static function instr($str, $needle) {
		if (strstr ( $str, $needle )) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * 
	 * 计算分页索引
	 * @param int $_tc 总记录数
	 * @param int $_ps 每页记录数 
	 * @param int $_cp 当前页数
	 * 
	 * @return array 返回结果
	 * 					tp -> 总页数
	 * 					si -> 当前页面记录起始索引
	 * 					ei -> 当前页面记录结束索引
	 * 	
	 */
	public static function pagecalc($_tc, $_ps, $_cp) {
		$tp = $_tc / $_ps;
		if ($_tc % $_ps != 0) {
			$tp = intval ( $tp ) + 1;
		}
		
		if ($_tc < $_ps)
			$tp = 1;
		if ($_cp > $tp)
			$_cp = $tp;
		
		$t ['tp'] = $tp;
		
		$si = ($_cp - 1) * $_ps;
		if ($_cp >= $tp)
			$ei = $_tc + 1;
		else
			$ei = ($_ps * $_cp) - 1;
		
		$t ['si'] = $si;
		$t ['ei'] = $ei;
		$t ['cp'] = $_cp;
		
		if ($_cp > 1) {
			$t ['up'] = $_cp - 1;
		}
		
		if ($_cp < $tp) {
			$t ['np'] = $_cp + 1;
		}
		
		return $t;
	}
	
	/**
	 * 从数组中取出值
	 *
	 * @arr       数组
	 * @key         post键名
	 * @default   如值不存在，则赋值为
	 *
	 * @return  取得后字符串
	 */
	
	public static function get_value_from_array($arr, $key, $default = false) {
		if(!is_array($arr)){
			return $default;
		}
		if (! array_key_exists ( $key, $arr )) {
			return $default;
		}
		if (! $arr [$key]) {
			return $default;
		}
		if (is_array ( $arr [$key] )) {
			return $arr [$key];
		} else {
			return trim ( $arr [$key] );
		}
	
	}
	
	/**
	 * 将一个字串中含有全角的数字字符、字母、空格或'%+-()'字符转换为相应半角字符
	 *
	 * @access  public
	 * @param   string       $str         待转换字串
	 *
	 * @return  string       $str         处理后字串
	 */
	function make_semiangle($str) {
		$arr = array ('０' => '0', '１' => '1', '２' => '2', '３' => '3', '４' => '4', '５' => '5', '６' => '6', '７' => '7', '８' => '8', '９' => '9', 'Ａ' => 'A', 'Ｂ' => 'B', 'Ｃ' => 'C', 'Ｄ' => 'D', 'Ｅ' => 'E', 'Ｆ' => 'F', 'Ｇ' => 'G', 'Ｈ' => 'H', 'Ｉ' => 'I', 'Ｊ' => 'J', 'Ｋ' => 'K', 'Ｌ' => 'L', 'Ｍ' => 'M', 'Ｎ' => 'N', 'Ｏ' => 'O', 'Ｐ' => 'P', 'Ｑ' => 'Q', 'Ｒ' => 'R', 'Ｓ' => 'S', 'Ｔ' => 'T', 'Ｕ' => 'U', 'Ｖ' => 'V', 'Ｗ' => 'W', 'Ｘ' => 'X', 'Ｙ' => 'Y', 'Ｚ' => 'Z', 'ａ' => 'a', 'ｂ' => 'b', 'ｃ' => 'c', 'ｄ' => 'd', 'ｅ' => 'e', 'ｆ' => 'f', 'ｇ' => 'g', 'ｈ' => 'h', 'ｉ' => 'i', 'ｊ' => 'j', 'ｋ' => 'k', 'ｌ' => 'l', 'ｍ' => 'm', 'ｎ' => 'n', 'ｏ' => 'o', 'ｐ' => 'p', 'ｑ' => 'q', 'ｒ' => 'r', 'ｓ' => 's', 'ｔ' => 't', 'ｕ' => 'u', 'ｖ' => 'v', 'ｗ' => 'w', 'ｘ' => 'x', 'ｙ' => 'y', 'ｚ' => 'z', '（' => '(', '）' => ')', '〔' => '[', '〕' => ']', '【' => '[', '】' => ']', '〖' => '[', '〗' => ']', '“' => '[', '”' => ']', '‘' => '[', '’' => ']', '｛' => '{', '｝' => '}', '《' => '<', '》' => '>', '％' => '%', '＋' => '+', '—' => '-', '－' => '-', '～' => '-', '：' => ':', '。' => '.', '、' => ',', '，' => '.', '、' => '.', '；' => ',', '？' => '?', '！' => '!', '…' => '-', '‖' => '|', '”' => '"', '’' => '`', '‘' => '`', '｜' => '|', '〃' => '"', '　' => ' ' );
		
		return strtr ( $str, $arr );
	}
	
	/**
	 * 递归方式的对变量中的特殊字符进行转义
	 *
	 * @access  public
	 * @param   mix     $value
	 *
	 * @return  mix
	 */
	public static function addslashes_deep($value) {
		if (empty ( $value )) {
			return $value;
		} else {
			return is_array ( $value ) ? array_map ( 'self::addslashes_deep', $value ) : addslashes ( $value );
		}
	}
	
	/**
	 * 递归方式的对变量中的HTML特殊字符进行转义
	 *
	 * @access  public
	 * @param   mix     $value
	 *
	 * @return  mix
	 */
	
	public static function htmlspecialchars_deep($value) {
		if (empty ( $value )) {
			return $value;
		} else {
			return is_array ( $value ) ? array_map ( 'self::htmlspecialchars_deep', $value ) : htmlspecialchars ( $value );
		}
	}
	
	/**
	 * 检查目标文件夹是否存在，如果不存在则自动创建该目录
	 *
	 * @access      public
	 * @param       string      folder     目录路径。不能使用相对于网站根目录的URL
	 *
	 * @return      bool
	 */
	public static function make_dir($folder) {
		$reval = false;
		
		if (! file_exists ( $folder )) {
			/* 如果目录不存在则尝试创建该目录 */
			@umask ( 0 );
			
			/* 将目录路径拆分成数组 */
			preg_match_all ( '/([^\/]*)\/?/i', $folder, $atmp );
			
			/* 如果第一个字符为/则当作物理路径处理 */
			$base = ($atmp [0] [0] == '/') ? '/' : '';
			
			/* 遍历包含路径信息的数组 */
			foreach ( $atmp [1] as $val ) {
				if ('' != $val) {
					$base .= $val;
					
					if ('..' == $val || '.' == $val) {
						/* 如果目录为.或者..则直接补/继续下一个循环 */
						$base .= '/';
						
						continue;
					}
				} else {
					continue;
				}
				
				$base .= '/';
				
				if (! file_exists ( $base )) {
					/* 尝试创建目录，如果创建失败则继续循环 */
					if (@mkdir ( rtrim ( $base, '/' ), 0774 )) {
						@chmod ( $base, 0774 );
						$reval = true;
					}
				}
			}
		} else {
			/* 路径已经存在。返回该路径是不是一个目录 */
			$reval = is_dir ( $folder );
		}
		
		clearstatcache ();
		
		return $reval;
	}
	
	/**
	 * 获得用户的真实IP地址
	 *
	 * 
	 * @return  string
	 */
	public static function real_ip() {
		
		static $realip = NULL;
		
		if ($realip !== NULL) {
			return $realip;
		}
		
		if (isset ( $_SERVER )) {
			if (isset ( $_SERVER ['HTTP_X_FORWARDED_FOR'] )) {
				$arr = explode ( ',', $_SERVER ['HTTP_X_FORWARDED_FOR'] );
				
				/* 取X-Forwarded-For中第一个非unknown的有效IP字符串 */
				foreach ( $arr as $ip ) {
					$ip = trim ( $ip );
					
					if ($ip != 'unknown') {
						$realip = $ip;
						
						break;
					}
				}
			} elseif (isset ( $_SERVER ['HTTP_CLIENT_IP'] )) {
				$realip = $_SERVER ['HTTP_CLIENT_IP'];
			} else {
				if (isset ( $_SERVER ['REMOTE_ADDR'] )) {
					$realip = $_SERVER ['REMOTE_ADDR'];
				} else {
					$realip = '0.0.0.0';
				}
			}
		} else {
			if (getenv ( 'HTTP_X_FORWARDED_FOR' )) {
				$realip = getenv ( 'HTTP_X_FORWARDED_FOR' );
			} elseif (getenv ( 'HTTP_CLIENT_IP' )) {
				$realip = getenv ( 'HTTP_CLIENT_IP' );
			} else {
				$realip = getenv ( 'REMOTE_ADDR' );
			}
		}
		
		preg_match ( "/[\d\.]{7,15}/", $realip, $onlineip );
		$realip = ! empty ( $onlineip [0] ) ? $onlineip [0] : '0.0.0.0';
		
		return $realip;
	}
	
	/**
	 * 计算字符串的长度（汉字按照两个字符计算）
	 *
	 * @param   string      $str        字符串
	 *
	 * @return  int
	 */
	public static function str_len($str) {
		$length = strlen ( preg_replace ( '/[\x00-\x7F]/', '', $str ) );
		
		if ($length) {
			return strlen ( $str ) - $length + intval ( $length / 3 ) * 2;
		} else {
			return strlen ( $str );
		}
	}
	
	/**
	 * 生成UUID
	 * 注意：仅支持Linux系统
	 * 
	 * @return string 56位长的UUID，格式 (32)-(8)-(14)
	 */
	public static function create_UUID() {
		$v1 = md5 ( shell_exec ( "ifconfig -a" ) );
		$v2 = self::make_rand_string ( 8 );
		$v3 = date ( "YmdHis", time () );
		$result = $v1 . "-" . $v2 . "-" . $v3;
		return $result;
	}
	
	/**
	 * 生成指定位数的随机字符串
	 * 
	 * @param int $len 随机字符串长度
	 * @param bool $include_number 是否允许包含数字
	 * 
	 */
	
	public static function make_rand_string($len, $include_number = false) {
		$string_list = array ('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K', 'L', 'M', 'N', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'j', 'k', 'm', 'n', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z' );
		
		$string_include_number_list = array ('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K', 'M', 'N', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'j', 'k', 'm', 'n', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', '2', '3', '4', '5', '6', '7', '8', '9' );
		
		if ($include_number) {
			$arr = $string_include_number_list;
		} else {
			$arr = $string_list;
		}
		
		$rand_max = count ( $arr ) - 1;
		$return_string = null;
		
		for($i = 0; $i < $len; $i ++) {
			$rand = mt_rand ( 0, $rand_max );
			$return_string .= $arr [$rand];
		}
		
		return $return_string;
	
	}
	
	/**
	 * 
	 * 日期运算
	 * @param int $time 初始时间戳
	 * @param char $type 增加的日期单位，m-月,d-日,y-年
	 * @param int $value 增加的长度
	 * @param string $timezone 依据的时区
	 * 
	 * @return int 运算完毕的时间戳，返回当天零时的时间戳
	 * 
	 * @author Andie Zhu
	 */
	public static function dateadd($time, $type, $value, $timezone = "UTC") {
		$current_timezone = date_default_timezone_get ();
		date_default_timezone_set ( $timezone );
		
		$day = date ( "d", $time );
		$month = date ( "m", $time );
		$year = date ( "Y", $time );
		switch ($type) {
			case "m" :
				$month = $month + $value;
				break;
			case "d" :
				$day = $day + $value;
				break;
			case "Y" :
				$year = $year + $value;
				break;
			case "y" :
				$year = $year + $value;
				break;
		}
		
		$result = mktime ( 0, 0, 0, $month, $day, $year );
		
		date_default_timezone_set ( $current_timezone );
		
		return $result;
	}
	
	/**
	 * 
	 * 发起一个HTTP请求，支持https
	 * 
	 * @param string $url URL
	 * @param string $method 请求方法，GET/POST
	 * @param string $data POST数据
	 * @param string $timeout 请求超时
	 * 
	 * @return string 服务器返回的结果
	 * 
	 * @author Andie Zhu
	 * 
	 */
	public static function http_request($url, $method = 'GET', $data = null, $timeout = 15) {
		$curl = curl_init ();
		curl_setopt ( $curl, CURLOPT_TIMEOUT, $timeout );
		curl_setopt ( $curl, CURLOPT_CONNECTTIMEOUT, $timeout );
		curl_setopt ( $curl, CURLOPT_URL, $url );
		if ($method == 'POST') {
			curl_setopt ( $curl, CURLOPT_POST, 1 );
			curl_setopt ( $curl, CURLOPT_POSTFIELDS, $data );
		}
		if (substr ( $url, 0, 5 ) == 'https') {
			curl_setopt ( $curl, CURLOPT_SSL_VERIFYPEER, false );
			curl_setopt ( $curl, CURLOPT_SSL_VERIFYHOST, false );
		}
		curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, 1 );
		$result = curl_exec ( $curl );
		return $result;
	}
	
	/**
	 * 验证输入的邮件地址是否合法
	 *
	 * @access  public
	 * @param   string      $email      需要验证的邮件地址
	 *
	 * @return bool
	 */
	public static function is_email($user_email) {
		$chars = "/^([a-z0-9+_]|\\-|\\.)+@(([a-z0-9_]|\\-)+\\.)+[a-z]{2,6}\$/i";
		if (strpos ( $user_email, '@' ) !== false && strpos ( $user_email, '.' ) !== false) {
			if (preg_match ( $chars, $user_email )) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	
	/**
	 *
	 * 判断是否为国际手机号（含国际区号）
	 * @param string $mobile
	 */
	public static function is_international_mobile($mobile) {
		if (substr ( $mobile, 0, 1 ) != "+") {
			return false;
		}
		if (! is_numeric ( substr ( $mobile, 1, strlen ( $mobile ) - 1 ) )) {
			return false;
		}
		return true;
	}
	
	
	/**
	 * 
	 * 判断是否为中国手机号
	 * 
	 * @param string $str 手机号
	 * 
	 * 
	 */
	public static function is_mobile($str) {
		if (strlen ( $str ) != 11) {
			return false;
		}
		if (! is_numeric ( $str )) {
			return false;
		}
		$prefix = substr ( $str, 0, 2 );
		if ($prefix == '10' || $prefix == '11' || $prefix == '12') {
			return false;
		}
		return true;
	}

}