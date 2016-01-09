<?php

namespace Libs\Redis;

class Redis {

	/**
	 * Holds initialized Redis connections.
	 *
	 * @var array
	 */
	protected static $connections = array();
	protected static $timeout = 2;
    protected static $retryTimes = 2;

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
	 * @TODO: support master/slave
	 */
	protected static function connect ($host, $port, $timeout = 1) {
		$redis = new \Redis();
		$redis->connect($host, $port, $timeout);
		//$redis->setOption(\Redis::OPT_READ_TIMEOUT, $timeout);
		return $redis;
	}

	/**
	 * Get an initialized Redis connection according to the key.
	 */
	protected static function getRedis($key = NULL, $reconnect = FALSE) {
		$class = get_called_class ();
		$config = $class::getConfig ();

		$count = count ( $config->servers );
		$server_id = is_null ( $key ) ? 0 : (hexdec ( substr ( md5 ( $key ), 0, 2 ) ) % $count);

		$host = $config->servers[$server_id]['host'];
		$port = $config->servers[$server_id]['port'];
		$connect_index = $host . ":" . $port;
        $isConnect = TRUE;
		if (! isset ( self::$connections [$connect_index] )) {
			self::$connections [$connect_index] = self::connect ( $host, $port, $class::$timeout );
            $isConnect = self::$connections [$connect_index]->isConnected();
            if (!$isConnect && !$reconnect) {
                unset(self::$connections [$connect_index]);
                //reconnect
                return self::getRedis($key, TRUE);     
            }
		}
        if (!$isConnect) {
            \Libs\Log\LevelLogWriter::warning("redis server connect error:{$host}:{$port}:{$key}");    
        }
		return self::$connections[$connect_index];
	}

	protected static function getPrefix() {
		$class = get_called_class();
		if (!is_null($class::$prefix)) {
			return $class::$prefix;
		}
		return get_called_class();
	}

    //TODO
	protected static function getConfig() {
	}

	/**
	 * Get real key
	 */
	public static function getKey ($name) {
		$class = get_called_class();
		$prefix = $class::getPrefix();
		$key = "{$prefix}:{$name}";
		return $key;
	}

	public static function __callStatic($method, $args) {
		$class = get_called_class();
		$name = $args[0];
		$key = self::getKey($name);
		$args[0] = $key;

		if (!self::$multiProcesser) {
		    try {
			    $result = call_user_func_array(array($class::getRedis($key), $method), $args);
			}
			catch (\RedisException $e) {
			    $result = NULL;
			}
		}
		else {
			$result = self::$multiProcesser->addCmd ( $class::getRedis ( $key ), $method, $args );
		}

		return $result;
	}

	protected static $multiProcesser = null;

	public static function multi($type = \Redis::PIPELINE) {
		self::$multiProcesser = new RedisMulti ( $type, self::$retryTimes );
	}

	public static function exec($return_servers = false) {
		$rtn = self::$multiProcesser->exec ();
		self::$multiProcesser = null;
		
		if (! $return_servers) {
			$real_rtn = array ();
			foreach ( $rtn as $rtn_item ) {
				if (! is_array ( $rtn_item )) {
					$rtn_item = array ();
				}
				$real_rtn = array_merge ( $real_rtn, $rtn_item );
			}
			$rtn = $real_rtn;
		}
		
		return $rtn;
	}
}
class RedisMulti {
	protected $type = null;
	protected $commands = array ();
	protected $result = array ();
	protected $times = 0;
	protected $commandsResultMap = array ();
	public function __construct($type, $retryTimes) {
		$this->type = $type;
		$this->times = $retryTimes;
	}
	public function addCmd($client, $method, $args) {
        if(!$client instanceof \Redis ){
            return false;
        }
		$serverKey = $client->getHost () . ":" . $client->getPort ();
		if (! array_key_exists ( $serverKey, $this->commands )) {
			$this->commands [$serverKey] = array (
					'redisClient' => $client,
					'cmds' => array () 
			);
		}
		
		$this->commands [$serverKey] ['cmds'] [] = array (
				'method' => $method,
				'args' => $args 
		);
        return true;
	}
	public function exec() {
        if(!is_array($this->commands)){
            throw new \Exception("redis server connect failed". json_encode($this->commands));
        }
		$type = $this->type;
		$rtn = array ();
		$this->initCommandsResultMap ();
		while ( $count < $this->times ) {
			$rtn = array ();
			
			foreach ( $this->commands as $key => $serverCmds ) {
				$redis = $serverCmds ['redisClient'];
				$chain = $redis->multi ( $type );
				foreach ( $serverCmds ['cmds'] as $cmd ) {
					$method = $cmd ['method'];
					$args = $cmd ['args'];
					call_user_func_array ( array (
							$chain,
							$method 
					), $args );
				}
				$rtn [$key] = $chain->exec ();
			}
			$this->updateCommandsResultMap ( $rtn, $this->commands );
			
			$needRetry = false;
			foreach ( $rtn as $retItem ) {
				if (is_array($retItem) && in_array ( 0, $retItem )) {
					$needRetry = true;
					break;
				}
			}
			if ($needRetry == false) {
				break;
			}
			
			$this->commands = $this->getMultiRetryCmd ( $rtn );
			$count ++;
		}
		return $this->getResult ();
	}
	private function initCommandsResultMap() {
		foreach ( $this->commands as $key => $serverCmds ) {
			foreach ( $serverCmds ['cmds'] as $cmd ) {
				$this->commandsResultMap [$key] [serialize ( $cmd )] = 0;
			}
		}
	}
	private function updateCommandsResultMap($rtn, $commands) {
		foreach ( $commands as $key => $serverCmds ) {
			$count = 0;
			foreach ( $serverCmds ['cmds'] as $cmd ) {
				$this->commandsResultMap [$key] [serialize ( $cmd )] = $rtn [$key] [$count];
				$count ++;
			}
		}
	}
	private function getResult() {
		$rtn = array ();
		
		foreach ( $this->commandsResultMap as $key => $item ) {
			foreach ( $item as $k => $v ) {
				$rtn [$key] [] = $v;
			}
		}
		return $rtn;
	}
	private function getMultiRetryCmd($rtn) {
		$commands = array ();
		$result = $rtn;
		foreach ( $this->commands as $key => $serverCmds ) {
			$commands [$key] ['redisClient'] = $serverCmds ['redisClient'];
            $commands[$key]['cmds'] = array();
			for($i = 0; $i < count ( $result [$key] ); $i ++) {
				if ($result [$key] [$i] == false) {
					$commands [$key] ['cmds'] [] = $serverCmds ['cmds'] [$i];
				}
			}
		}
		return $commands;
	}
}

