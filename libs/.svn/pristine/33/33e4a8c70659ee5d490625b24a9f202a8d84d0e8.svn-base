<?php

namespace Libs\Mq;
use Libs\Log\MLog;

/**
 *
 * @see http://redmine.meilishuo.com/projects/doota/wiki/%E5%BC%82%E6%AD%A5%E9%98%9F%E5%88%97%E7%B3%BB%E7%BB%9F%E4%BB%8B%E7%BB%8D
 */
class MqProxyClient {
    protected $servers;
    protected $failover_servers = array ();
    protected $timeout_ms = 1000;
    protected $connect_timeout_ms = 1000;
    protected $retry_times = 3;
    private $last_server_index = null;
    /**
     * tag check
     * @param array $config         
     *
     * @return MqProxyClient
     */
    public static function getClient($config) {
        return new self ( $config );
    }
    private function __construct($config) {
        if (! is_array ( $config ) || ! array_key_exists ( 'servers', $config ) || ! is_array ( $config ['servers'] ) || count ( $config ['servers'] ) <= 0) {
            throw new \Exception ( 'no valid servers assign' );
        }
        
        $this->servers = $config ['servers'];
        if (isset ( $config ['failover_servers'] )) {
            $this->failover_servers = $config ['failover_servers'];
        }
        if (isset ( $config ['timeout_ms'] )) {
            $this->timeout_ms = $config ['timeout_ms'];
        }
        if (isset ( $config ['connect_timeout_ms'] )) {
            $this->connect_timeout_ms = $config ['connect_timeout_ms'];
        }
        if (isset ( $config ['retry_times'] )) {
            $this->retry_times = $config ['retry_times'];
        }
        MLog::setLogApp('mq');
    }
    public function publish_message($topic, $message, $partition_key = 0) {
        $current_retry_time = 0;
        $offset = false;
	    while ( $current_retry_time <= $this->retry_times ) {
            try {
                $offset = $this->do_publish_message ( $topic, $message, $partition_key, $current_retry_time );
                break;
            } catch ( MqClientRetryException $ex ) {
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
        $headers [] = 'X-Kmq-Logid: 0';
        
        $endpoint = $this->getEndpoint ( $current_retry_time );
        $ch = curl_init ( $endpoint );

        curl_setopt($ch, CURLOPT_NOSIGNAL, 1); 
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
            $catched_exception = "request error : curl_errno : " . curl_errno ( $ch ) . ' , curl_error : ' . curl_error ( $ch );
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
        
        // 只有这些curl错误才会引发重试，为了避免重发消息，超时不会重试
        
        $will_retry = false;
        $retry_curl_errno = array (
                CURLE_COULDNT_RESOLVE_HOST,
                CURLE_COULDNT_CONNECT,
                CURLE_OPERATION_TIMEOUTED,
                CURLE_GOT_NOTHING 
        );
        if (in_array ( curl_errno ( $ch ), $retry_curl_errno )) {
            $will_retry = true;
        }

        $logData = array ();
        $logData ['endpoint'] = $endpoint;
        $logData ['topic'] = $topic;
        $logData ['partition_key'] = $partition_key;
        $logData ['message'] = $message;
        $logData ['partition'] = $partition;
        $logData ['offset'] = $offset;
        $logData ['exception'] = isset ( $catched_exception ) ? json_encode ( $catched_exception ) : '';
        $logData ['timecost'] = number_format ( (microtime ( true ) - $stime) * 1000, 2 );
        $logData ['current_retry_time'] = $current_retry_time;
        
        // log级别：如果判定无异常，则INFO，否则如果判定不能重试，则为FATAL，否则判定在重试次数内，则WARNING，否则FATAL
        
        if (! $logData ['exception']) {
            $loglevel = 'INFO';
        } else {
            if ($will_retry) {
                if ($logData ['current_retry_time'] >= $this->retry_times - 1) {
                    $loglevel = 'FATAL';
                } else {
                    $loglevel = 'WARNING';
                }
            } else {
                $loglevel = 'FATAL';
            }
        }
        
        $logStr = "[publish_message]\t$loglevel\t" . json_encode ( $logData );
       if($loglevel == 'FATAL' || $loglevel == 'WARNING'){
            MLog::notice ( $logStr, "mqproxy", "INFO" );
       }
        if ($will_retry) {
            //失败的server剔除出servers数组，若servers只剩一台服务器，不剔除
            if(count($this->servers) > 1){
                array_splice($this->servers, $this->last_server_index, 1); 
            }
            throw new MqClientRetryException ( curl_error ( $ch ) );
        }
        
        return $rtn;
    }
    private function getEndpoint($current_retry_time = 0) {
        $server_count = count ( $this->servers );
        
        // 重试策略:随机选取server
        $this->last_server_index = rand(0, $server_count - 1);
        $server = $this->servers [ $this->last_server_index ];
        
        // 如果设置了failover_servers，则处理failover的逻辑：在最后一次重试时，往failover里面发
        if (is_array ( $this->failover_servers ) && count ( $this->failover_servers ) > 0) {
            if ($current_retry_time > 0 && $current_retry_time == $this->retry_times - 1) {
                $server = $this->failover_servers [array_rand ( $this->failover_servers )];
            }
        }
        
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
        
        $logStr = '[mqrpc] ' . json_encode ( $logInfo );
        MLog::notice ( $logStr, "mqrpc", "INFO" );
    }
}
class MqClientRetryException extends \Exception {
}
