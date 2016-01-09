<?php
namespace Libs\Statistics;

class StatisticsWebObject {

    private $refer = '';
    private $page = '';
    private $append_param = '';
    private $module = NULL;
    private $action = NULL;
    private $hdtrc = '';
    private $pageCode = '';    

    public function __construct($refer,$requri,$module, $action) {
        if (!empty($refer)) {  
            $this->refer = $refer;
        }
        $this->page = $this->getPage($requri);
        $this->hdtrc = $this->getHdtrc();
        $this->pageCode = $this->getPageCode($requri);
        $this->module = $module;
        $this->action = $action;
    }

    public function __get($field) {
        return $this->$field;
    }

    public function appendRefer($pairs = array()) {
        $currentReferNode = $this->currentReferNode($pairs);
        $this->refer = StatisticsUtilities::attachRefer($this->refer, array($currentReferNode));
        return $this->refer;
    }

    public function addRefer($refer,$pairs = array()) {
        $currentReferNode = $this->currentReferNode($pairs);
        $refer = StatisticsUtilities::attachRefer($refer, array($currentReferNode));
        return $refer;
    }

    public function getCurrentApiRefer($pairs) {
        $currentReferNode = $this->currentReferNode($pairs);
        return StatisticsUtilities::encodeReferNode($currentReferNode);  
    } 

    protected function currentReferNode($pairs = array()) {
        if (empty($this->page)) {
            return array();
        }
        
        $origPairs = array();
        
        if (!empty($this->append_param)) {
            $origPairs['append_param'] = $this->append_param;
        }  
            
        if (!empty($this->hdtrc)) {
            $origPairs['hdtrc'] = $this->hdtrc;    
        }
        
        if (!empty($this->pageCode)) {
            $origPairs['_page_code'] = $this->pageCode;    
        }        
            
        if ($this->module == 'statistics' && $this->action == 'referer') {
        } else {           
            if (!empty($this->module)) {  
                $origPairs['module'] = $this->module;
            }
            
            if (!empty($this->action)) {  
                $origPairs['action'] = $this->action;
            }
        }
        
        if (!empty($pairs)) {  
            $origPairs = array_merge($origPairs, $pairs);
        }
        
        $referNode = array(
            'paths' => array($this->page),
            'pairs' => $origPairs,
        );
        return $referNode;
    }
    
    /*    
    1、当URL中的/大于等于3个时：
    /a/b/c/d/e
    /a/b 是 controller所在目录（可以省略）
    c是 controller js文件  （可以理解为class）
    d是 controller 里具体入口函数 (可以理解为method)  最后一个/前面为d
    e 是 附加参数（可以省略 ，既无参数）                      最后一个/后面为e
    CM（class_name-method_name)生成规则为：
    当附加参数为非数字时：a_b_c-d_e
    当附加参数为数字时：   a_b_c-d:append_param=e

    举例说明：
    1）团购下方列表：mapp.meilishuo.com/aj/tuan/ajlist
    CM为： aj-tuan_ajlist
    CM为： c-d_e
    2) 秒杀首页：mapp.meilishuo.com/activity/tuan/special/1065
    CM为： activity_tuan-special:append_param=1065
    CM为： b_c-d:append_param=e
    3) 单宝页：m.meilishuo.com/share/item/3080257425
    CM为： share-item:append_param= 3080257425
    CM为： c-d:append_param=e
    2、当URL中的/小于3个时：
    /c/e  
    c是 controller js文件   入口函数是默认值(index) 
    e是 附加参数（可以省略 ，既无参数）
    CM（class_name-method_name)生成规则为：
    当无附加参数时：c-main
    当有附加参数且附加参数为非数字时：c-e
    当有附加参数且附加参数为数字时：   c-main:append_param=e
    举例说明：
    1) 团购首页：mapp.meilishuo.com/tuan
    CM为： tuan-main  
    */ 
    
    private  function getPage($requri) {
        if (empty($requri)) {
            return '';
        } 
        $pos = stripos($requri,'?');
        
        $uri = '';
        if (empty($pos)) {
            $uri = $requri;
        } else {
            $uri = substr($requri, 0, $pos);
        }
        
        $uri = trim($uri,'/');
        $levelArr = explode('/',$uri,8);
        
        $total = count($levelArr);
        
        $page = '';
        if ($total >= 3) {
            $e = array_pop($levelArr);
            $method = array_pop($levelArr);
            $class = array_pop($levelArr);
            
            $levelArr[] = "{$class}-$method";
            if (is_numeric($e)) {
                $this->append_param = $e;
            } else {   
                $levelArr[] = $e;
            }
            $page = implode('_', $levelArr);
        } else {
            $class = $levelArr[0];
            if (empty($class)) {
                $class = 'welcome';
            }
            
            $page = "{$class}-main";                            
            if (!empty($levelArr[1])) {
                if (is_numeric($levelArr[1])) {
                    $this->append_param = $levelArr[1];
                    $page = "{$class}-main";                    
                } else {     
                    $page = "{$class}-" . $levelArr[1];
                }
            }   
        }
        
        return $page;
    }
    
    private function getHdtrc() {
        if (!empty($_GET['hdtrc'])) {
            return $_GET['hdtrc'];
        }        
        
        $requri = $_SERVER['HTTP_REFERER'];
        
        $url_info = parse_url($requri);
        parse_str($url_info['query'], $query_param);
        
        if (!empty($query_param) && isset($query_param['hdtrc'])) {
            return $query_param['hdtrc'];
        }
        return '';       
    } 
    
    private function getPageCode($requri) {
        if (empty($requri)) {
            return '';
        }
        $uri = trim($requri,'/');
        $uriArr = explode('/',$uri,8);        
        
        if ($uriArr[0] == 'aj' || $uriArr[1] == 'aj') {
            $referer = $_SERVER['HTTP_REFERER'];
            if (empty($referer)) {
                return '';
            }
            $url_info = parse_url($referer);  
            return $this->getPage($url_info['path']);            
        } else {
            return $this->getPage($requri);            
        }   
        return '';       
    }     
}
