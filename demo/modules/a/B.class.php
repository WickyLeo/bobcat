<?php
namespace Demo\Modules\A;

class B extends \Frame\Module {

    public function run() {
        $sql = "select * from t_dolphin_user_profile where user_id = 1227713";
        $result = BDB::getConn()->read($sql, array());
        die();
        $this->app->response->setBody($result);
        $this->app->log->log('testlogname', $result);
    }
    
    public function asyncJob() {
        $this->log->log('test_asynjob', 'this is test asynjob');
    }
}

class BDB extends \Libs\DB\DBConnManager {

    const _DATABASE_ = 'dolphin';
    static $readretry = 100; 

}
