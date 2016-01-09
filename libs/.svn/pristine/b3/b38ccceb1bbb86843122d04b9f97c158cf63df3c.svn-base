<?php
namespace Libs\Serviceclient;
/**
 * 处理请求的client
 * @author zx 
 */

class Client {
    /*
     * service 服务标识，比如virus、doota 
     * apiName 接口名
     * params 接口需要的参数
     * opt, array('method' => 'GET','timeout' => 1) 可选配置， 比如method、timeout等    
     */
    public function call($service, $apiName, $params, $opt = array()) {
        $callback = 'solo';
        $request = Request::createRequest();
        $request->setApi($service, $apiName, $opt);
        $request->setParam($params);
        $response = Transport::exec(array($callback => $request), $opt);
        return $response[$callback];
    }
}
