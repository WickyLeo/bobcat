<?php

namespace Libs\Redis;

class RedisNutProxy {

    /**
     * Holds initialized Redis connections.
     *
     * @var object
     */
    protected static $connection = null;
    protected static $timeout = 2;

    /**
     * By default the prefix of Redis key is the same as class name. But it
     * can be specified manually.
     *
     * @var string
     */
    protected static $prefix = NULL;

    protected static $configfile = 'Redis';

    /**
     * the server_id of the Redis Cluster
     *
     * @var int
     */

    protected static function setTimeout($timeout) {
        self::$timeout = $timeout;
    }

    /**
     * Initialize a Redis connection.
     */
    protected static function connect($host, $port, $timeout = 1) {
        $redis = new \Redis();
        $redis->connect($host, $port, $timeout);
        //$redis->setOption(\Redis::OPT_READ_TIMEOUT, $timeout);
        return $redis;
    }

    /**
     * Get an initialized Redis connection according to the key.
     */
    protected static function getRedis() {
        if (empty(self::$connection)) {
            $config = self::getConfig();
            self::$connection = self::connect($config['host'], $config['port'],self::$timeout);
        }

        return self::$connection;
    }

    protected static function getPrefix() {
        $class = get_called_class();
        if (!is_null($class::$prefix)) {
            return $class::$prefix;
        }
        return get_called_class();
    }

    /**
     * Get host conf
     */
    protected static function getConfig() {
        static $config;
        is_null($config) && $config = \Frame\ConfigFilter::instance()->getConfig('redis');
        $nutConfigs = $config['nutHosts'];
        $count = count($nutConfigs);
        $index = rand(0, $count - 1);
        $conf = $nutConfigs[$index];
        empty($conf['host']) && $conf = array();
        return $conf;
    }

    /**
     * Get real key
     */
    public static function getKey($name) {
        if ($name == null) {return $name;}

        $class = get_called_class();
        $prefix = $class::getPrefix();
        $key = "{$prefix}:{$name}";
        return $key;
    }

    protected static function filtMethod($method) {
        $unSupport = array('config' => TRUE, 'slaveof' => TRUE, 'info' => TRUE, 'bgrewriteaof' => TRUE,
            'bgsave' => TRUE, 'flushdb' => TRUE, 'flushall' => TRUE, 'save' => TRUE, 'shutdown' => TRUE,
            'rename' => TRUE, 'multi' => TRUE, 'publish' => TRUE, 'subscribe' => TRUE, 'unsubscribe' => TRUE,
            'keys' => TRUE, 'msetnx' => TRUE, 'blpop' => TRUE, 'brpop' => TRUE, 'brpoplpush' => TRUE,
        );

        if (isset($unSupport[$method]) && !empty($unSupport[$method])) {
            throw  new \RedisException("unsupported command '{$method}'");
        }
        return TRUE;
    }

    public static function __callStatic($method, $args) {
        $method = strtolower($method);
        $class = get_called_class();
        is_array($args) && isset($args[0]) && $args[0] = self::getKey($args[0]);

        self::filtMethod($method);

        try {
            $result = call_user_func_array(array($class::getRedis(), $method), $args);
        }
        catch (\RedisException $e) {
            $result = NULL;
        }

        return $result;
    }

}
