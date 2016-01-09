<?php

namespace Demo\Scripts;

class Demo extends \Frame\Script {

    public function run() {
        var_dump($this->app->request);
        $this->response->setBody("script demo");
    }
}
