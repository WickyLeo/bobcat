<?php
namespace Libs\Session;

class MobSession extends Session {
    /**
     * 普通登录态的session_key
     * @return string
     */
    public function getTicket() {
        $ticket = "";
        if(isset($_REQUEST['access_token'])) {
            $ticket = trim($_REQUEST['access_token']);
            $ticket = "Mob:Session:AccessToken:".$ticket;
            $this->setLoginFrom("mob");
        }
        return $ticket;
    }

    /**
     * 强登录态校验
     * @return string
     */
    public function getSTicket() {
        return "";
    }
}