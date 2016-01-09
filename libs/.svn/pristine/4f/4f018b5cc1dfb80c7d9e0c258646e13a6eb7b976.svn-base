<?php

namespace Libs\CommonService\Mq;

class MqProxyClient {
	protected $servers;
	protected $timeout_ms = 1000;
	protected $connect_timeout_ms = 1000;
	protected $retry_times = 3;
	/**
	 *
	 * @param array $config        	
	 *
	 * @return MqClientProxy
	 */
	public static function getClient($config) {
		return new self ( $config );
	}
	private function __construct($config) {
		$this->servers = $config ['servers'];
		if (isset ( $config ['timeout_ms'] )) {
			$this->timeout_ms = $config ['timeout_ms'];
		}
		if (isset ( $config ['connect_timeout_ms'] )) {
			$this->connect_timeout_ms = $config ['connect_timeout_ms'];
		}
		if (isset ( $config ['retry_times'] ) && intval ( $config ['retry_times'] ) > 0) {
			$this->retry_times = intval ( $config ['retry_times'] );
		}
	}
	public function publish_message($topic, $message, $partition_key = 0) {
		$current_retry_time = 0;
		$offset = false;
		
		while ( $current_retry_time < $this->retry_times ) {
			try {
				$offset = $this->do_publish_message ( $topic, $message, $partition_key, $current_retry_time );
				return $offset;
			} catch ( \Frame\Exception\RetryException $ex ) {
				$current_retry_time ++;
				continue;
			} catch ( \Exception $ex ) {
				break;
			}
		}
		
		return $offset;
	}
	protected function do_publish_message($topic, $message, $partition_key = 0, $current_retry_time = 0) {
		$stime = microtime ( true );
		
		$rtn = $offset = $partition = false;
		
		$post_body = http_build_query ( $message );
		
		$headers = array ();
		$headers [] = 'X-Kmq-Topic: ' . $topic;
		$headers [] = 'X-Kmq-Partition-Key: ' . $partition_key;
		$headers [] = 'X-Kmq-Logid: ' . \Libs\Log\LevelLogWriter::logId ();
		
		$endpoint = $this->getEndpoint ();
		$ch = curl_init ( $endpoint );
        curl_setopt ( $ch, CURLOPT_NOSIGNAL, 1);
        if (defined ( 'CURLOPT_TIMEOUT_MS' )) {
            curl_setopt ( $ch, CURLOPT_TIMEOUT_MS, $this->timeout_ms );
        } else {
                // hack 没有毫秒超时的情况，如果当前超时转为秒大于1，则用该时间，否则用1，不按此处理会导致超时设置无效
            curl_setopt ( $ch, CURLOPT_TIMEOUT, max ( 1, intval ( $this->timeout_ms / 1000 ) ) );
        }
        if (defined ( 'CURLOPT_CONNECTTIMEOUT_MS' )) {
            curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT_MS, $this->connect_timeout_ms );
        } else {
            curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT, max ( 1, intval ( $this->connect_timeout_ms / 1000 ) ) );
        }

		curl_setopt ( $ch, CURLOPT_HTTPHEADER, $headers );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt ( $ch, CURLOPT_BINARYTRANSFER, true );
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $post_body );
		
		$proxy_result = curl_exec ( $ch );
		
		$this->rpclog ( $ch );
		
		if (! $proxy_result) {
			$rtn = false;
			$catched_exception = "request error : " . curl_error ( $ch );
		} else {
			$res = json_decode ( $proxy_result, true );
			if (! is_array ( $res )) {
				$rtn = false;
				$catched_exception = "json_decode failed : #$proxy_result#";
			} else {
				if ($res ['errno'] != 0) {
					$rtn = false;
					$catched_exception = "proxy processed failed , errno:" . $res ['errno'] . " , errmsg: " . $res ['errmsg'];
				} else {
					$rtn = $offset = $res ['data'] ['Offset'];
					$partition = $res ['data'] ['Partition'];
				}
			}
		}
		
		$logData = array ();
		$logData ['endpoint'] = $endpoint;
		$logData ['topic'] = $topic;
		$logData ['partition_key'] = $partition_key;
		$logData ['message'] = json_encode ( $message );
		$logData ['partition'] = $partition;
		$logData ['offset'] = $offset;
		$logData ['exception'] = isset ( $catched_exception ) ? json_encode ( $catched_exception ) : '';
		$logData ['timecost'] = number_format ( (microtime ( true ) - $stime) * 1000, 2 );
		$logData ['current_retry_time'] = $current_retry_time;
		if(!$logData ['exception']){
			$logData ['loglevel'] = 'INFO';
		}else{
			if( $logData ['current_retry_time'] >= $this->retry_times - 1 ){
				$logData ['loglevel'] = 'FATAL';
			}else{
				$logData ['loglevel'] = 'WARNING';
			}
		}

        if($logData['logLevel'] == 'FATAL' || $logData['loglevel'] == 'WARNING'){
		    \Libs\Log\LevelLogWriter::selfLog ( "mqproxy", "publish_message", $logData );
        }
		
		$retry_curl_errno = array (
				CURLE_COULDNT_RESOLVE_HOST,
				CURLE_COULDNT_CONNECT 
		);
		if (in_array ( curl_errno ( $ch ), $retry_curl_errno )) {
			throw new \Frame\Exception\RetryException ( curl_error ( $ch ) );
		}
		
		return $rtn;
	}
	private function getEndpoint() {
		$server = $this->servers [array_rand ( $this->servers )];
		
		$server = ltrim ( $server, 'http://' );
		$url = sprintf ( "http://%s/produce?format=json", $server );
		return $url;
	}
	private function rpclog($ch) {
		$curlErrno = curl_errno ( $ch );
		$curlError = curl_error ( $ch );
		$info = curl_getinfo ( $ch );
		
		$logInfo = array ();
		$logInfo ['curl_errno'] = $curlErrno;
		$logInfo ['curl_error'] = $curlError;
		$logInfo ['url'] = $info ['url'];
		$logInfo ['http_code'] = $info ['http_code'];
		$logInfo ['total_time'] = number_format ( $info ['total_time'] * 1000, 0 );
		$logInfo ['time_detail'] = number_format ( $info ['namelookup_time'] * 1000, 0 ) . "," . number_format ( $info ['connect_time'] * 1000, 0 ) . "," . number_format ( $info ['pretransfer_time'] * 1000, 0 ) . "," . number_format ( $info ['starttransfer_time'] * 1000, 0 );
		
		\Libs\Log\LevelLogWriter::selfLog ( 'rpc', "MqProxyClient", $logInfo );
	}
}
