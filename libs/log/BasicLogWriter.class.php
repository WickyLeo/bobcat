<?php

namespace Libs\Log;

class BasicLogWriter extends LogWriter {

	private $LOGPATH = '/home/work/webdata/logs/';

    public function write($mark, $str) {
        $path_parts = pathinfo($mark);
        $realpath = $this->LOGPATH . $path_parts["dirname"];
        if (!is_dir($realpath)) {
            system("mkdir -p " . $realpath . ";chmod -R 777 " . $realpath);
        }
        $realfile = $path_parts["basename"] . "." . date("YmdH");

		$currentTime = date("Y-m-d H:i:s");
		$file = $realpath . "/" . $realfile;
		@file_put_contents($file, $str . PHP_EOL, FILE_APPEND);
    }

}
