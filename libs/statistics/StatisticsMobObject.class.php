<?php
namespace Libs\Statistics;

class StatisticsMobObject {

    protected $refer = NULL;
    protected $module = NULL;
    protected $action = NULL;

    public function __construct($refer, $module, $action) {
        $this->refer = $refer;
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
        $origPairs = array();        
        if (isset($_REQUEST['_page_id'])) {
            $origPairs['_page_id'] = $_REQUEST['_page_id'];
        }
        
        if (isset($_REQUEST['_page_code'])) {
            $origPairs['_page_code'] = $_REQUEST['_page_code'];
        }        
        
        if (!empty($pairs)) {  
            $origPairs = array_merge($origPairs, $pairs);
        }
        
        $referNode = array(
            'paths' => array($this->module, $this->action),
            'pairs' => $origPairs,
        );
        return $referNode;
    }
}
