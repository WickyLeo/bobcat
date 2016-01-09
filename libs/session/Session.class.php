<?php
/***************************************************************************
 *
 * Copyright (c) 2015 Meilishuo.com, Inc. All Rights Reserved
 *
 **************************************************************************/



/**
 * @file   Session.class.php
 * @author 李守岩(shouyanli@meilishuo.com)
 * @date   2015/11/27
 * @brief  passport sdk session
 *
 **/


namespace Libs\Session;

use \Libs\Cache\Memcache;
use \Libs\Serviceclient\PassportCurl;
use \Libs\Util\ConfUtilities;

use \Libs\Log\ScribeLogCollector;
use \Libs\Log\ProxyLogWriter;

class DBSwanHelper extends \Libs\DB\DBConnManager {
    const _DATABASE_ = 'swan';
}

class Session {

    const ONLINE_URL = 'http://passport.mlservice.meilishuo.com/';
    const NEWLAB_URL = 'http://passport.mlservice.newlab.meilishuo.com/';
    const DEVLAB_URL = 'http://passport.mlservice.shouyanli.rdlab.meilishuo.com/';

    private $session=array();
    private $ticket = "";
    private $s_ticket = "";
    private $login_from = 0;
    private $conf = array();


    public function __construct() {
        $this->loadSession();
    }

    private function loadSession() {
        $this->ticket = $this->getTicket();
        $this->s_ticket = $this->getSTicket();

        if ($this->checkLogin()) {
            return;
        }
        if ($this->checkSLogin()) {
            return;
        }
    }

    public function checkLogin() {
        if (empty($this->ticket)) {
            return FALSE;
        }

        $this->conf = \Frame\ConfigFilter::instance()->getConfig('passport');
        if (empty($this->conf)) {
            $session = $this->get_session();
        } else {
            $session = $this->get_session_by_api($this->ticket, $this->conf['env']);
        }

        $this->session = $session;
        if (!empty($this->session)) {
            $this->session && $this->session['session_data'] && $this->session = $this->session['session_data'];

            if ($this->session && $this->session['user_id']) {
                $this->session['login_type'] = 1;
                $this->session['login_from'] = $this->login_from;
                return TRUE;
            }
        }
        return FALSE;
    }

    //获取session  
    public function get_session() {

        $session = Memcache::instance()->get($this->ticket);
        if (empty($session) && stripos($this->ticket, 'Mob:Session:AccessToken:') !== false) {
            $sqlComm = 'select * from t_swan_oauth_access_token_new where token = :token AND (expiration = 0 || expiration >= :_timestamp)';
            $token   = str_replace('Mob:Session:AccessToken:', '', $this->ticket);
            $sqlData = array(
                    'token' => $token,
                    '_timestamp' => $_SERVER['REQUEST_TIME'],
                    );

            try {
                $result = DBSwanHelper::getConn()->read($sqlComm, $sqlData);
            } catch(\Exception $e) {
                return false;
            }

            if (!empty($result)) {
                $result = $result[0];
                $user_id = $result['user_id'];
                $session = array();
                $session['user_id'] = $user_id;
                Memcache::instance()->set($this->ticket, $session, 14400);
            }
        }
        return $session;
    }

    private function get_passport_url($env = 'online') {
        if ($env === 'newlab') {
            $url = self::NEWLAB_URL;
        } elseif ($env === 'devlab') {
            $url = self::DEVLAB_URL;
        } else {
            $url = self::ONLINE_URL;
        }
        return $url;

    }

    public function get_session_by_api($access_token, $env = 'online') {

        if (stripos($access_token, 'Mob:Session:AccessToken:') !== false) {
            $access_token   = str_replace('Mob:Session:AccessToken:', '', $access_token);

            $source = 'mob';
        } else {
            $source = 'web';
        }

        if (empty($access_token)) {
            //$this->loger('passport.'.$this->conf['app_id'], json_encode($_COOKIE) );
            return false;    
        }

        $url = $this->get_passport_url($env);	

        $param = array(
            'session_id'   => $access_token, 
            'app_id'       => $this->conf['app_id'], 
            'app_key'      => $this->conf['app_key'],
            'source'       => $source,
        );
        
        if (!empty($_COOKIE['_pzfxnvpc'])) {
                $param['inverse_key']  = $_COOKIE['_pzfxnvpc'];
        }

        $timeout = $this->conf['timeout_ms'] ? $this->conf['timeout_ms'] : 500;
        $res   = $this->post($url, 'user/is_login', $param, $timeout);

        if (empty($res) 
           || !isset($res['error_code']) 
           || $res['error_code'] != 0 
           || empty($res['data'])) {
            return false;
        }
        return $res['data'];
    } 

    public function post ($url, $api, $params, $timeout = 500) {
        $http = new PassportCurl();
        $http->setTimeOutMs($timeout);
        $res  = $http->post($url.$api, $params);	
        $res  = json_decode($res, true);
        return $res;
    } 

    public function checkSLogin() {
        if (empty($this->s_ticket)) {
            return FALSE;
        }
        $this->session = Memcache::instance()->get($this->s_ticket);
        if (!empty($this->session)) {
            if($this->session && $this->session['user_id']) {
                $this->session['login_type'] = 2;
                $this->session['login_from'] = $this->login_from;
                return TRUE;
            }
        }
        return FALSE;
    }

    /**
     * 普通登录态的session_key
     * @return string
     */
    public function getTicket() {
        return "";
    }

    /**
     * 强登录态校验
     * @return string
     */
    public function getSTicket() {
        return "";
    }

    public function __get($arg) {
        if (isset($this->session[$arg])) {
            return $this->session[$arg];
        } else if ($arg=="session") {
            return $this->session;
        }

        return FALSE;
    }

    public function setLoginFrom($from) {
        $this->login_from = $from;
    }
    
    public function loger($name, $str) {
        $scribe = new ScribeLogCollector(); 
        $logger = new ProxyLogWriter($scribe);
        $logger->write($name, $str);
    }
}


/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
