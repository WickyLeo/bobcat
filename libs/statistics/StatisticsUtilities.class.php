<?php
namespace Libs\Statistics;

USE \Libs\Statistics\StatisticsMobObject;
USE \Libs\Statistics\StatisticsWebObject;

class StatisticsUtilities {

    public static function encodeReferNodeValue($value) {
        $value = urlencode($value);
        $value = str_replace(array('.', '+'), array('%2E', '%20'), $value);
        return $value;
    }

    public static function encodeReferNode($referNode) {
        if (is_null($referNode['paths']) || is_null($referNode['pairs']) || !is_array($referNode['paths']) || !is_array($referNode['pairs']) || empty($referNode['paths'])) {
            return FALSE;
        }

        $refer = '';
        $refer .= implode('-', $referNode['paths']);
        foreach ($referNode['pairs'] as $key => $value) {
            $refer .= ':' . self::encodeReferNodeValue($key) . '=' . self::encodeReferNodeValue($value);
        }
        return $refer;
    }

    public static function attachRefer($refer, $appendReferNodes = array()) {
        foreach ($appendReferNodes as $appendReferNode) {
            $refer .= '.' . self::encodeReferNode($appendReferNode);
        }
        $refer = ltrim($refer, '.');

        return $refer;
    }

    public static function decodeRefer($refer) {
        $referNodes = array();
        if (empty($refer)) {
            return $referNodes;
        }
        $subRefers = explode('.', $refer);
        foreach ($subRefers as $subRefer) {
            $paths = $pairs = array();

            list($pathsStr, $pairsStr) = explode(':', $subRefer, 2);
            $paths = explode('-', $pathsStr);
            if (!empty($pairsStr)) {
                $subPairsStrs = explode(':', $pairsStr);
                foreach ($subPairsStrs as $subPairsStr) {
                    list($pairKey, $pairValue) = explode('=', $subPairsStr);
                    $pairs[$pairKey] = $pairValue;
                }
            }

            $referNodes[] = array(
                'paths' => $paths,
                'pairs' => $pairs,
            );
        }
        return $referNodes;
    }

    public static function getLastPageFromRefer($refer) {
        $referNodes = array();
        if (empty($refer)) {
            return '';
        }
        
        $subRefers = explode('.', $refer);
        $subRefer = array_pop($subRefers);
        if (empty($subRefer)) {
            return '';
        }
            
        list($pathsStr, $pairsStr) = explode(':', $subRefer, 2);
        
        return empty($pathsStr) ? '' : $pathsStr;        
    }
    
    public static function addReferToArray(StatisticsMobObject $statisticsObject, $items, $otherParams= array(), $mixParams = array()) {
        if (empty($items) || !is_array($items)) {
            return $items;
        }
        
        if (!is_object($statisticsObject)) {
            return $items;
        }  
        
        if (!is_array($otherParams)) {
            return $items;
        }  
        
        $mix_key = $mixParams['key'];
        !is_string($mix_key) && $mix_key = '';
        
        $counter = 1; // 从 1 开始
        foreach ($items as $key => $item) {
            if (!is_array($item)) {
                continue;
            }
            
            if (isset($item['r'])) {
                continue;
            }  
            
            $otherParams['pos'] = $counter;
            if (!empty($mix_key) && is_array($mixParams[ $item[$mix_key] ])) {
                $pairs = array_merge($otherParams, $mixParams[ $item[$mix_key] ]);
                $r = $statisticsObject->refer . '.' . $statisticsObject->getCurrentApiRefer($pairs);
            } else {
                $r = $statisticsObject->refer . '.' . $statisticsObject->getCurrentApiRefer($otherParams);
            }
            
            $items[$key]['r'] = ltrim($r, '.');
            
            $counter++;
        }
        
        return $items;
    } 
    
    
    public static function addWebReferToArray($statisticsObject, $items, $otherParams= array(), $mixParams = array()) {
        if (empty($items) || !is_array($items)) {
            return $items;
        }
        
        if (!is_object($statisticsObject)) {
            return $items;
        }  
        
        if (!is_array($otherParams)) {
            return $items;
        }  
        
        $mix_key = $mixParams['key'];
        !is_string($mix_key) && $mix_key = '';
        
        $counter = 1; // 从 1 开始
        foreach ($items as $key => $item) {
            if (!is_array($item)) {
                continue;
            }
            
            if (isset($item['r'])) {
                continue;
            }  
            
            $otherParams['_pos_id'] = $counter;           
            if (!empty($item['r_ext'])) {
                $otherParams = array_merge($otherParams, $item['r_ext']);
                unset($items[$key]['r_ext']);
            }  
            
            if (!empty($mix_key) && is_array($mixParams[ $item[$mix_key] ])) {
                $pairs = array_merge($otherParams, $mixParams[ $item[$mix_key] ]);
                $r = $statisticsObject->refer . '.' . $statisticsObject->getCurrentApiRefer($pairs);
            } else {
                $r = $statisticsObject->refer . '.' . $statisticsObject->getCurrentApiRefer($otherParams);
            }
                     
            $items[$key]['r'] = ltrim($r, '.');
            
            $counter++;
        }
        
        return $items;
    }    
}
