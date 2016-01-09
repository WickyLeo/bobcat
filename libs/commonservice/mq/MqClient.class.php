<?php

namespace Libs\CommonService\Mq;

/**
 * 
 * @deprecated use MqProxyClient instead
 *
 */
class MqClient {
	protected $zkHosts;
	protected $zkTimeout = 1000;
	protected $producerRequireAck = - 1;
	protected $producer = null;
	/**
	 *
	 * @param array $config        	
	 *
	 * @return MqClient
	 */
	public static function getClient($config) {
		if (! class_exists ( '\Zookeeper', true )) {
			throw new \Exception ( 'Zookeeper client not installed' );
		}
		if (! class_exists ( '\Kafka\Produce', true )) {
			throw new \Exception ( 'Kafka client not installed' );
		}
		
		return new self ( $config );
	}
	private function __construct($config) {
		$this->zkHosts = $config ['zkHosts'];
		if (array_key_exists ( 'zkTimeout', $config )) {
			$this->zkTimeout = $config ['zkTimeout'];
		}
		if (array_key_exists ( 'producerRequireAck', $config )) {
			$this->producerRequireAck = $config ['producerRequireAck'];
		}
	}
	private function getProducer() {
		if (! $this->producer) {
			$this->producer = \Kafka\Produce::getInstance ( $this->zkHosts, $this->zkTimeout );
		}
		
		return $this->producer;
	}
	public function publish_message($topic, $message, $partition = 0) {
		$stime = microtime ( true );
		$rtn = false;
		$offset = false;
		if (! is_string ( $message )) {
			$message = json_encode ( $message );
		}
		
		$p = $this->getProducer ();
		$p->setRequireAck ( $this->producerRequireAck );
		$p->setMessages ( $topic, $partition, $message );
		try {
			$result = $p->send ();
		} catch ( \Exception $e ) {
			$catched_exception = $e;
			\Libs\Log\LevelLogWriter::warning ( "send to kafka failed,msg:" . $e->getMessage (), $e->getCode () );
			$rtn = false;
		}
		
		if ($result && isset ( $result [$topic] [$partition] ['errCode'] ) && $result [$topic] [$partition] ['errCode'] == 0) {
			$rtn = $offset = $result [$topic] [$partition] ['offset'];
		} else {
			$rtn = false;
		}
		
		$logData = array ();
		$logData ['zk'] = $this->zkHosts;
		$logData ['topic'] = $topic;
		$logData ['message'] = $message;
		$logData ['partition'] = $partition;
		$logData ['offset'] = $offset;
		$logData ['exception'] = isset ( $catched_exception ) ? json_encode ( $catched_exception ) : '';
		$logData ['timecost'] = number_format ( (microtime ( true ) - $stime) * 1000, 2 );
		\Libs\Log\LevelLogWriter::selfLog ( "mqclient", "publish_message", $logData );
		
		return $rtn;
	}
}
