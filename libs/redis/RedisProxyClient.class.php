<?php

namespace Libs\Redis;

final class RedisProxyClient {

    /**
     * Holds initialized Redis connections.
     * @var object
     */
    private $connection = NULL;

    private $prefix = NULL;

    private $timeout = 0;
    private $conn_retry = 0;
    private $current_retry = 0;
    private $read_timeout = 0;
    private $current_server = array();

    private $error_msg = '';

    private $log_name = ''; //先给去掉 redis_proxy_access
    private $log_obj = NULL;

    const DEFAULT_TIMEOUT = 2;
    const DEFAULT_CONN_RETRY = 0;
    const MIN_CONN_RETRY = 0;
    const MAX_CONN_RETRY = 3;

    /**
     * get the RedisProxyClient
     *
     * @var array $config
     * @return RedisProxyClient
     */
    public static function getClient($config) {
        return new self($config);
    }

    private function __construct($config) {
        if (!is_array($config) || !array_key_exists('nutHosts', $config) || !is_array($config['nutHosts']) || count($config['nutHosts']) <= 0) {
            throw new \Exception('redis hosts config error');
        }

        $this->servers = $config['nutHosts'];

        $this->timeout = self::DEFAULT_TIMEOUT;
        if (isset($config['timeout'])) {
            $this->timeout = $config['timeout'];
        }

        $this->conn_retry = self::DEFAULT_CONN_RETRY;
        if (isset($config['connect_retry'])) {
            $this->setConnRetry($config['connect_retry']);
        }

        if (isset($config['read_timeout'])) {
            $this->read_timeout = $config['read_timeout'];
        }

        $log_collector = new \Libs\Log\ScribeLogCollector();
        $log_writer = new \Libs\Log\ProxyLogWriter($log_collector);
        $this->log_obj = new \Libs\Log\Log($log_writer);
    }

    /**
     * set connection timeout in seconds
     * this is not phpredis setTimeout
     * @var float
     */
    public function setTimeout($timeout) {
        $this->timeout = $timeout;
    }

    /**
     * set retry times for connection
     * 0 means no retry
     * retry tims ranges in [0, 3]
     */
    public function setConnRetry($retry) {
        $retry = (int)$retry;
        if ($retry < self::MIN_CONN_RETRY) $retry = MIN_CONN_RETRY;
        if ($retry > self::MAX_CONN_RETRY) $retry = MAX_CONN_RETRY;
        $this->conn_retry = $retry;
    }

    /**
     * explicitly set read timeout in seconds
     * if not set, phpredis will take the connect timeout as read timeout
     * $var float
     */
    public function setReadTimeout($read_timeout = 0) {
        if (empty($read_timeout)) {
            if (empty($this->read_timeout))
                return FALSE;
            else
                $read_timeout = $this->read_timeout;
        }

        try {
            $this->error_msg = '';
            $redis = $this->getRedis();
            $redis->setOption(\Redis::OPT_READ_TIMEOUT, $read_timeout);
        } catch (\RedisException $e) {
            $this->error_msg = $e->getMessage();

            if (!empty($this->log_name)) {
                $json_args = json_encode(array('read_timeout' => $read_timeout));
                $log_str = "[setReadTimeout]\t[{$json_args}]\t[{$this->error_msg}]\t[{$this->current_server['host']}:{$this->current_server['port']}]";
                $this->log_obj->log($this->log_name, $log_str);
            }

            return FALSE;
        }
        return TRUE;
    }

    public function getLastError() {
        return $this->error_msg;
    }

    public function setOption($opt, $value) {
        switch ($opt) {
        case 'log_name':
            $this->log_name = $value;
            break;
//        case 'transport_retry':
//            $this->trans_retry = (int)$value;
//            break;
        default:
            break;
        }
    }

    private function getRedis() {
        if (empty($this->connection)) {
            $timeout = $this->timeout;
            $retry = $this->conn_retry;

            do {
                $redis = new \Redis();
                $server = $this->getConfig();
                $this->current_server = $server;
                $ret = $redis->connect($server['host'], $server['port'], $timeout);
                $this->current_retry++;
            } while ($ret === FALSE && $retry - $this->current_retry >= 0);

            if ($ret === FALSE) {
                throw new \RedisException("connect fail after {$retry} trials");
            }

            $this->connection = $redis;
        }
        return $this->connection;
    }

    public function getConnTimeout() {
        return $this->timeout;
    }

    public function setPrefix($prefix) {
        $this->prefix = $prefix;
    }

    private function getPrefix() {
        if (!is_null($this->prefix)) {
            return $this->prefix;
        }
        return '';
    }

    private function getConfig() {
        $this->servers = array_values($this->servers);
        if (empty($this->servers)) {
            return array();
        }

        $index = array_rand($this->servers);
        $conf = $this->servers[$index];
        if (count($this->servers) > 1) {
            unset($this->servers[$index]);
        }

        empty($conf['host']) && $conf = array();
        return $conf;
    }

    /**
     * Get real key
     */
    public function getKey($name) {
        if ($name == null) {return $name;}

        $prefix = $this->getPrefix();
        if (!empty($prefix)) {
            $key = "{$prefix}:{$name}";
        } else {
            $key = $name;
        }
        return $key;
    }

    private function filtMethod($method) {
        $unSupport = array('config' => TRUE, 'slaveof' => TRUE, 'info' => TRUE, 'bgrewriteaof' => TRUE,
            'bgsave' => TRUE, 'flushdb' => TRUE, 'flushall' => TRUE, 'save' => TRUE, 'shutdown' => TRUE,
            'rename' => TRUE, 'multi' => TRUE, 'publish' => TRUE, 'subscribe' => TRUE, 'unsubscribe' => TRUE,
            'keys' => TRUE, 'msetnx' => TRUE, 'blpop' => TRUE, 'brpop' => TRUE, 'brpoplpush' => TRUE,
            'bitop' => TRUE, 'rpoplpush' => TRUE, 'sdiff' => TRUE, 'sdiffstore' => TRUE, 'sinter' => TRUE,
            'sinterstore' => TRUE, 'smove' => TRUE, 'sunion' => TRUE, 'sunionstore' => TRUE, 'pfcount' => TRUE,
            'pfmerge' => TRUE, 'evalsha' => TRUE,
        );

        if (isset($unSupport[$method]) && !empty($unSupport[$method])) {
            throw new \RedisException("unsupported command '{$method}'");
        }
        return TRUE;
    }

    public function __call($method, $args) {
        $method = strtolower($method);
        $this->filtMethod($method);

        if (!in_array($method, array('delete', 'mset', 'mget', 'eval'))) {
            is_array($args) && isset($args[0]) && $args[0] = $this->getKey($args[0]);
        } else {
            if (!is_array($args[0]) && !in_array($method, array('delete', 'eval'))) {
                return FALSE;
            }
            if ($method == 'delete') {
                if (!is_array($args[0])) {
                    $args[0] = $this->getKey($args[0]);
                } else {
                    foreach ($args[0] as &$key) {
                        $key = $this->getKey($key);
                    }
                }
            } elseif ($method == 'mget') {
                foreach ($args[0] as &$key) {
                    $key = $this->getKey($key);
                }
            } elseif ($method == 'mset') {
                $new_args = array();
                foreach ($args[0] as $key => $val) {
                    $newkey = $this->getKey($key);
                    $new_args[$newkey] = $val;
                }
                $args = array($new_args);
            }
        }

        try {
            $this->error_msg = '';
            $result = call_user_func_array(array($this->getRedis(), $method), $args);
        } catch (\RedisException $e) {
            $this->error_msg = $e->getMessage();
            $this->connection = NULL;
            $result = NULL;
        }

        if (!empty($this->log_name)) {
            $json_args = json_encode($args);
            $log_str = "[{$method}]\t[{$json_args}]\t[{$this->error_msg}]\t[{$this->current_server['host']}:{$this->current_server['port']}]";
            $this->log_obj->log($this->log_name, $log_str);
        }

        return $result;
    }
}
