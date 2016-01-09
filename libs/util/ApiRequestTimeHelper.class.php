<?php

/* 
 * 用来统计一个接口各个地方的调用耗时情况，并打印到日志
 * @author xiangbowu
 */

namespace Libs\Util;

class ApiRequestTimeHelper {
    private static $uri = '';
    private static $logName = 'ApiRequestTime';
    private static $needHandle = FALSE;
    private static $queue = array();
    
    /*
     * 初始化，
     * $randMax 抽样随机数
     * $logName 日志名
     * $uri 接口名，默认从URL里取得 
     */
    public static function Initialize($randMax=1, $logName='', $uri='') {
        if (empty($uri)) {
            $uri = $_SERVER['REQUEST_URI'];
            $pos = stripos($uri,'?');
            if (empty($pos)) {
                self::$uri = $uri;
            } else {
                self::$uri = substr($uri, 0,$pos);                
            }               
        } else {
            self::$uri = $uri;
        }
        
        if ($randMax == 1) {
            self::$needHandle = TRUE;            
        } elseif (rand(1, $randMax) == 1) {
            self::$needHandle = TRUE;
        }  
    }
    
    /*
     * 在每个调用的开始和结束都要调用这个， $stepName是调用名
     */
    public static function StepLog($stepName) {
        if (!self::$needHandle) {
            return;
        }  
        $time = microtime(TRUE);
        if (!isset(self::$queue[$stepName])) {
            self::$queue[$stepName] = array('start_time' => $time);
            return;
        }
        
        if (isset(self::$queue[$stepName]['start_time'])) {
            self::$queue[$stepName]['end_time'] = $time;    
        } else {
            self::$queue[$stepName]['start_time'] = $time;                
        }       
    }
    
    public static function GetLogStr() {        
        if (empty(self::$queue)) {
            return '';
        }
        
        $stepArr = array();
        foreach (self::$queue as $key => $item) {
            $startTime = $item['start_time'];
            $endTime = $item['end_time'];
            
            if (empty($endTime) || empty($startTime)) {
                continue;
            }
            
            $delta = $endTime - $startTime;
            $delta = intval($delta * 1000);
            if ($delta < 0) {
                continue;
            }
            $stepArr[] = "[{$key}:{$delta}]";
        }
        
        if (empty($stepArr)) {
            return '';
        }
        
        $stepStr = implode("\t", $stepArr);
        return $stepStr;
    }
    
    public static function WriteLog() {
        if (!self::$needHandle) {
            return;
        }
        
        $logStr = self::GetLogStr();
        if (empty($logStr)) {
            return;
        }
        
        $logWriter = new \Libs\Log\BasicLogWriter();
        $logObj = new \Libs\Log\Log($logWriter);
        
        $uri = self::$uri;
        $tmp = "[{$uri}]\t{$logStr}";
        $logObj->log(self::$logName, $tmp);       
    }    
}

