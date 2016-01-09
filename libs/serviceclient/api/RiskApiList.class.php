<?php

namespace Libs\Serviceclient\Api;

class RiskApiList extends \Libs\Serviceclient\Api\ApiList {

    protected static $apiList = array(
        'risk/risk_proxy' => array('service' => 'risk', 'method' => 'GET', 'opt' => array('timeout' => 1)), 
    );

}
