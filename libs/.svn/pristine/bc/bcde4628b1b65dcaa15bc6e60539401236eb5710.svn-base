<?php
/**
 * Created by PhpStorm.
 * User: scl
 * Date: 15-4-24
 * Time: 上午10:58
 */

namespace Libs\Cache;


class HMemcache extends \Libs\Cache\Memcache {

    private static $Hsingleton = NULL;

    /**
     * Constructor.
     */
    protected function __construct() {
        is_null($this->config) && $this->config = \Frame\ConfigFilter::instance()->getConfig('hmemcache');
        parent::__construct();
    }

    public static function instance() {
        $class = get_called_class();
        is_null(self::$Hsingleton) && self::$Hsingleton = new $class();
        return self::$Hsingleton;
    }


}