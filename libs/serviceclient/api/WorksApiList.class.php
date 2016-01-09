<?php
/**
 * 
 * Date: 6/10/15
 * Time: 2:20 PM
 * @author jintanlu <jintanlu@meilishuo.com>
 */

namespace Libs\Serviceclient\Api;


class WorksApiList extends \Libs\Serviceclient\Api\ApiList {
    protected static $apiList = array(
        'service_menuService/getAdminRoles' => array(
            'service' => 'works',
            'method' => 'POST',
            'opt' => array(
                'timeout' => 3
            )
        ),
        'service_twitterVerify/delTwitterVerify' => array(
            'service' => 'works',
            'method' => 'POST',
            'opt' => array(
                'timeout' => 3
            )
        ),
        'service_shop/getShopVerifyStatus' => array(
            'service' => 'works',
            'method' => 'POST',
            'opt' => array(
                'timeout' => 3
            )
        ),
    );
}