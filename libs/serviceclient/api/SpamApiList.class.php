<?php
/**
 * 
 * Date: 6/10/15
 * Time: 2:19 PM
 * @author jintanlu <jintanlu@meilishuo.com>
 */

namespace Libs\Serviceclient\Api;


class SpamApiList extends \Libs\Serviceclient\Api\ApiList {
    protected static $apiList = array(
        'anti-fraud/porn_check/check_content' => array(
            'service' => 'spam',
            'method' => 'GET',
            'opt' => array(
                'timeout' => 1
            )
        ),
        'anti-fraud/brandnames_check/luxury_brand' => array(
            'service' => 'spam',
            'method' => 'GET',
            'opt' => array(
                'timeout' => 1
            )
        )
    );
}