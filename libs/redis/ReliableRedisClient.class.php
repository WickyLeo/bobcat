<?php
namespace Libs\Redis;

class ReliableRedisClient {

    const PIPELINE_MODE_NPL = 0; // not pipeline
    const PIPELINE_MODE_RAW = 1; // raw pipeline
    const PIPELINE_MODE_EXT = 2; // extended pipeline

    public static $prefix = 'X42X';
    
    private static $host = array();
    private static $timeout = 0;
    private static $read_timeout = 0;
    private static $retry_times = 0;
    private static $logger = null;
    
    private static $pipeline_mode = NULL;
    private static $commands_seq = array();
    
    protected static function loadConfig() {}
    protected static function beforeExecuteHook() {}
    protected static function afterExecuteHook() {}

    public static function getPipelineMode() {
        return self::$pipeline_mode;
    }
    
    public static function getCommandsSeq() {
        return self::$commands_seq;
    }
    
    public static function setHost($host) {
        self::$host = $host;
    }
    
    public static function setTimeout($timeout) {
        self::$timeout = $timeout;
    }
    
    public static function setReadTimeOut($read_timeout) {
        self::$read_timeout = $read_timeout;
    }
    
    public static function setRetryTimes($retry_times) {
        self::$retry_times = $retry_times;
    }
    
    public static function setLogger($logger) {
        if (is_callable(array($logger, 'log'))) {
            self::$logger = $logger;
        } else {
            self::$logger = null;
        }
    }
    
    private static function clearContext() {
        self::$pipeline_mode = self::PIPELINE_MODE_NPL;
        self::$commands_seq = array();
    }
    
    public static function __callStatic($method, $args) {
        if ($method == 'pipeline') {
            if (self::$pipeline_mode) {
                throw new \Exception("recursive pipeline");
            }

            $mode = current($args);
            $args = array();

            self::clearContext();
            $mode != self::PIPELINE_MODE_EXT && $mode = self::PIPELINE_MODE_RAW;
            self::$pipeline_mode = $mode;
        }
        
        
        self::$commands_seq[] = array(
            'method' => strtolower($method),
            'args' => $args,
            'prefix' => static::$prefix,
        );

        return self::execute($method);
    }
    
    private static function execute($method) {
        static $load_conf = null;
        if (is_null($load_conf)) {static::loadConfig(); $load_conf = true;}
        
        if (empty(self::$commands_seq)) return null;
        if (self::$pipeline_mode && $method != 'exec') return null;
       
        static::beforeExecuteHook();
        
        $current_retry = 0; $result = null;
        do {
            $retry = false;
            $start_time = microtime(true);
            try {
                $result = self::_execute();
            } catch (\Exception $e) {
                $retry = true;
                $redis = RedisRawClient::getInstance(self::$host);
                $current_host = $redis->getCurrentHost();
                self::log("[host:" . json_encode($current_host) . "]"
                    . " [time:" . (microtime(true) - $start_time) . "]"
                    . " [exception: " . $e->getMessage() . "]"); 
            }
        } while (self::$retry_times - $current_retry++ > 0 && $retry);
        
        $retry && self::log("commands seq error: " . json_encode(self::$commands_seq));
        
        static::afterExecuteHook();
        
        self::clearContext();
        return $result;
    }
    
    private static function _execute() {
        $redis = RedisRawClient::getInstance(self::$host);
        $redis->setTimeout(self::$timeout);
        $redis->setReadTimeOut(self::$read_timeout);
        $redis->connect();
        
        $pipeline_seq_id = 0;
        $pipeline_key_map = array();
        foreach (self::$commands_seq as $command) {
            $args = $command['args'];
            $method = $command['method'];
            
            self::$pipeline_mode == self::PIPELINE_MODE_EXT && !in_array($method, array('pipeline', 'exec'))
                && $pipeline_key_map[array_pop($args)] = $pipeline_seq_id++;
            
            $redis->setPrefix($command['prefix']);
            $result_raw = $redis->__call($method, $args);
        }
        
        $result = null;
        foreach ($pipeline_key_map as $key => $idx) {
            $result[$key] = $result_raw[$idx];
        }

        return is_null($result) ? $result_raw : $result;
    }
    
    private static function log($log_str) {
        static $prefix = null;
        if (empty($prefix)) {
            $prefix = "[pid:" . getmypid() . "]";

            if (isset($_REQUEST['logid'])) {
                $prefix .= " [logid:{$_REQUEST['logid']}]";
            }
            
            if (isset($_SERVER['REQUEST_URI'])) {
                $parse = parse_url($_SERVER['REQUEST_URI']);
                $prefix .= " [uri:{$parse['path']}]";
            }
            
            $prefix .= " ";
        }
        
        self::$logger && self::$logger->log('Couponservice_redis_client', $prefix . $log_str);
    }
}

class RedisRawClient {
    private $hosts = array();
    private $timeout = 0;
    private $read_timeout = 0;
    
    private $client = NULL;
    private $prefix = NULL;
    private $current_host = array();
    
    const MAX_TIMEOUT = 1.0;
    const MAX_READ_TIMEOUT = 1.0;
    
    public static function getInstance($hosts) {
        if (empty($hosts)) return null;
        $key = crc32(serialize($hosts));
        static $instance = array();
        !isset($instance[$key]) && $instance[$key] = new self($hosts);
        return $instance[$key];
    }

    private function __construct($hosts) {
        $this->hosts = $hosts;
        $this->timeout = self::MAX_TIMEOUT;
        $this->read_timeout = self::MAX_READ_TIMEOUT;
    }
    
    public function getCurrentHost() {
        return $this->current_host;
    }
    
    public function setTimeout($timeout) {
        if (is_numeric($timeout) && $timeout <= self::MAX_TIMEOUT && $timeout > 0) {
            $this->timeout = $timeout;
        }
    }
    
    public function setReadTimeOut($read_timeout) {
        if (is_numeric($read_timeout) && $read_timeout <= self::MAX_READ_TIMEOUT && $read_timeout > 0) {
            $this->read_timeout = $read_timeout;
        }
    }
    
    public function setPrefix($prefix) {
        $this->prefix = $prefix;
    }
    
    public function connect() {
        static $read_timeout = 0;
        if ($this->client && !$this->client->getLastError()) {
            if ($read_timeout == $this->read_timeout) 
                return $this->client;
            $read_timeout = $this->read_timeout;
            if ($this->client->setReadTimeout($read_timeout)) 
                return $this->client;
        }
        
        $this->client = null;
        $this->current_host = array();
        $read_timeout = $this->read_timeout;
        
        $idx = array_rand($this->hosts);
        $this->current_host = $this->hosts[$idx];
        unset($this->hosts[$idx]);
        if (empty($this->current_host)) 
            throw new RedisRawClientException("no valid redis host");

        $client = \Libs\Redis\RedisProxyClient::getClient(array('nutHosts' => array($this->current_host)));
        $client->setTimeout($this->timeout);
        if (! $client->setReadTimeout($this->read_timeout))
            throw new RedisRawClientException("set read timeout error");
        
        $this->client = $client;
        return $this->client;
    }
    
    public function __call($method, $args) {
        if ($this->client == null)
            throw new RedisRawClientException("redis client not valid");
        
        $this->client->setPrefix($this->prefix);
        $result = $this->client->__call($method, $args);
        if ($this->client->getLastError()) {
            $msg = $this->client->getLastError();
            $this->client = null;
            throw new RedisRawClientException($msg);
        }
        
        return $result;
    }
}

class RedisRawClientException extends \Exception  {
    
}

