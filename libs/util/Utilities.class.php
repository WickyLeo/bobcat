<?php

namespace Libs\Util;

//config for image host
$GLOBALS['PICTURE_DOMAINS'] = array(
    'a' => 'http://imgtest.meiliworks.com', //tx
    'b' => 'http://d04.res.meilishuo.net', //tx
    'c' => 'http://imgtest-dl.meiliworks.com', //dl
    'd' => 'http://d06.res.meilishuo.net', //dl
    'e' => 'http://d05.res.meilishuo.net', //ws
    'f' => 'http://d01.res.meilishuo.net', //ws
    'g' => 'http://d02.res.meilishuo.net', //tx
    'h' => 'http://d03.res.meilishuo.net', //tx
);

$GLOBALS['PICTURE_DOMAINS_ALLOCATION'] = 'aaddbbgggggggggggghhhhhhhhhhhhbbbbbbbbbbccddddddddggdddddddddddddddddeeeeeeeeeeeeeeeeeeeeeeeeeedddff';

class Utilities {

    public static function DataToArray($dbData, $keyword, $allowEmpty = FALSE) {
        $retArray = array ();
        if (is_array ( $dbData ) == false or empty ( $dbData )) {
            return $retArray;
        }
        foreach ( $dbData as $oneData ) {
            if (isset ( $oneData [$keyword] ) and empty ( $oneData [$keyword] ) == false or $allowEmpty) {
                $retArray [] = $oneData [$keyword];
            }
        }
        return $retArray;
    }

    public static function changeDataKeys($data, $keyName, $toLowerCase=false) {
        $resArr = array ();
        if(empty($data)){
            return false;
        }
        foreach ( $data as $v ) {
            $k = $v [$keyName];
            if( $toLowerCase === true ) {
                $k = strtolower($k);
            }
            if(empty($k)) {
                continue;
            }
            $resArr [$k] = $v;
        }
        return $resArr;
    }
	
	/**
	 * 通用图片转换函数
	 * 
	 * @param string $uri        	
	 * @return string $url
	 */
	public static function getPictureUrl($key, $type = "_o", $ignorecase = true) {
		if (empty ( $key ) || empty ( $type )) {
			return '';
		}
		if ($ignorecase) {
			$type = strtolower ( $type );
		}
		$key = str_replace ( '/_o/', '/' . $type . '/', $key );
		
		$key = trim ( $key );
		if (strncasecmp ( $key, 'http://', strlen ( 'http://' ) ) == 0) {
			return $key;
		}
		
		$key = ltrim ( $key, '/' );
		$hostPart = self::getPictureHost ( $key );
		if (empty ( $key )) {
			return $hostPart . '/css/images/noimage.jpg';
		}
		return $hostPart . '/' . $key;
	}
    public static function convertPicture($key) {

        if (strncasecmp($key, 'http://', strlen('http://')) == 0) {
            return $key;
        }

        $key = ltrim($key, '/');
        $hostPart = self::getPictureHost($key);
        if (empty($key)) {
            return $hostPart . '/css/images/0.gif';
        }
        return $hostPart . '/' . $key;
    }

    private static function getPictureHost($key) {
        if (empty($key)) {
            return $GLOBALS['PICTURE_DOMAINS']['a'];
        }
        if (substr($key, 0, 3) === 'css' && defined('CSS_JS_BASE_URL')) {
            return rtrim(CSS_JS_BASE_URL, '/');
        }
        $remain = crc32($key) % 100;
        $remain = abs($remain);
        $hashKey = $GLOBALS['PICTURE_DOMAINS_ALLOCATION'][$remain];
        return $GLOBALS['PICTURE_DOMAINS'][$hashKey];
    }

    public static function sortArray($array, $order_by, $order_type = 'ASC') {
        if (!is_array($array)) {
            return array();
        }
        $order_type = strtoupper($order_type);
        if ($order_type != 'DESC') {
            $order_type = SORT_ASC;
        } else {
            $order_type = SORT_DESC;
        }

        $order_by_array = array ();
        foreach ( $array as $k => $v ) {
            $order_by_array [] = $array [$k] [$order_by];
        }
        array_multisort($order_by_array, $order_type, $array);
        return $array;
    }

    public static function nginx_userid_decode($str) {
        $str_unpacked = unpack('h*', base64_decode(str_replace(' ', '+', $str)));
        $str_split = str_split(current($str_unpacked), 8);
        $str_map = array_map('strrev', $str_split);
        $str_dedoded = strtoupper(implode('', $str_map));

        return $str_dedoded;
    }
	
	/**
	 * Get the real remote client's IP
	 *
	 * @return string
	 */
	public static function getClientIP() {
		if (isset ( $_SERVER ['HTTP_X_FORWARDED_FOR'] ) && $_SERVER ['HTTP_X_FORWARDED_FOR'] != '127.0.0.1') {
			$ips = explode ( ',', $_SERVER ['HTTP_X_FORWARDED_FOR'] );
			$ip = $ips [0];
		} elseif (isset ( $_SERVER ['HTTP_X_REAL_IP'] )) {
			$ip = $_SERVER ['HTTP_X_REAL_IP'];
		} elseif (isset ( $_SERVER ['HTTP_CLIENTIP'] )) {
			$ip = $_SERVER ['HTTP_CLIENTIP'];
		} elseif (isset ( $_SERVER ['REMOTE_ADDR'] )) {
			$ip = $_SERVER ['REMOTE_ADDR'];
		} else {
			$ip = '127.0.0.1';
		}
		
		$pos = strpos ( $ip, ',' );
		if ($pos > 0) {
			$ip = substr ( $ip, 0, $pos );
		}
		
		$pos = strpos($ip, ':');
	        if($pos > 0){
	            $ip = substr ($ip, 0, $pos);
	        }
		
		return trim ( $ip );
	}
    /**
     * 获取服务器IP
     * @return string
     */
    public static function getServerIp()
    {
        static $ip;
        if ($ip) {
            return $ip;
        }
        if (isset($_SERVER['SERVER_ADDR'])) {
            if($_SERVER['SERVER_ADDR'] == '127.0.0.1'){
                $ip = $_SERVER['HTTP_X_REAL_IP'];
            }else{
                $ip = $_SERVER['SERVER_ADDR'];
            }
        } else {
            $cmd = "/sbin/ifconfig|grep 'inet addr'|awk '{print $2}'|awk -F':' '{print $2}'|awk '$1 !~ /127.0.0.1/{print}'|tail -n 1";
            $handle = popen($cmd, 'r');
            $ip = trim(fread($handle, 1024));
            pclose($handle);
        }
        return $ip;
    }

    public static function encodeMeilishuoURL($url_type = '', $json_params = array(), $title = '') {
        if (!empty($title)) {
            $json_params['title'] = $title;
        }

        $params = urlencode(json_encode($json_params));
        return "meilishuo://{$url_type}.meilishuo?json_params={$params}";
    }

    /**
     * 客户端version
     */
    public static function mobileVersion() {
        $version = '';
        $agent = $_SERVER['HTTP_USER_AGENT'];
        if (!empty($agent)) {
            $ua = explode(' ', $agent); 
            $version = end($ua);
        }
        return $version;
    }

    //110001 -- 111000
    public static function platform($client_id) {
        $platform = 'Unkown';
        if (!empty($client_id)) {
            if ($client_id <= 100) {
                $platform = 'iPhone';
            } elseif ($client_id >= 2001 && $client_id < 3000) {
                $platform = 'iPad';
            } elseif ($client_id >= 5001 && $client_id <= 10000) {
                $platform = 'iPhoneSub';
            } elseif ($client_id >= 10001 && $client_id <= 90000) {
                $platform = 'Android';
                // for hack 30320-30329为MeilishuoTV
                if ($client_id >= 30320 && $client_id <= 30329) {
                    $platform = 'MeilishuoTV';
                }
            } elseif ($client_id == 90001) {
                $platform = 'WindowsPhone';
            } elseif ($client_id >= 100001 && $client_id <= 100100) {
                $platform = 'iPhoneHuahua';
            } elseif ($client_id >= 100201 && $client_id <= 100500) {
                $platform = 'AndroidHuahua';
            } elseif ($client_id >= 110001 && $client_id <= 111000) {
                $platform = 'AndroidPad';
            } elseif ($client_id >= 120001 && $client_id <= 121000) {
                $platform = 'iPhoneWeiQuan';
            } elseif ($client_id >= 121001 && $client_id <= 125000) {
                $platform = 'AndroidWeiQuan';
            } else {
                $platform = 'WindowsPhone';
            }
        }
        return $platform;
    }

}
