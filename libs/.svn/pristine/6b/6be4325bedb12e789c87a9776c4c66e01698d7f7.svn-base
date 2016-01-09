<?php
namespace Libs\Mark;

use \Libs\Mark\Utilities;

class MarkMem extends \Libs\Cache\NormalCacheBase {
    //打标商品cache

    const PREFIX = 'Frame:Mark:Info:';
    //超时时间(s)
    const TIMEOUT = 300;

    //静态memcache
    static $instance;

    static $prefix;

    static public function setPrefix($str) {
        static::$prefix = date('H') . $str;
    }

    static public function instance() {
        if(!isset(static::$instance)) {
            $prefix = static::PREFIX . static::$prefix;
            static::$instance = new self($prefix);
            static::$instance->setTime(static::TIMEOUT);
        }
        return static::$instance;
    }

}

class FashionMarks {
    private static $position = array(
        'upleft'        => 1,
        'up'            => 2,
        'upright'       => 3,
        'left'          => 4,
        'center'        => 5,
        'right'         => 6,
        'downleft'      => 7,
        'down'          => 8,
        'downright'     => 9,
    );

    private static $direction = array(
        'horizontal'    => 1,
        'tilt'          => 2,
        'vertical'      => 3,
    );

    private static $platforms = array(
        'Web'       => 1,
        'Wap'       => 2,
        'iPhone'    => 3,
        'iPad'      => 4,
        'Android'   => 5,
        'WindowsPhone' => 6,
    );

    private static $types = array(
        'poster'   => 0, // 全站
        'groupon'   => 2, // 团购
        'discount'  => 3, // 特卖
        'weixin' => 4, // 微信商城
        'single' => 5, // 单品页
        'mz_single' => 6, // 美妆特卖单品
        'shouq' => 7, // 手Q商城
        'activity' => 8, // 活动
    );

    private static $attr_types = array(
        'shopid'   => 1, // 店铺id
    );


    // 获得斜标标信息
    public static function getTiltMarks($gids, $platform, $type) {
        empty($platform) && $platform = 'Web';
        $goods_marks = self::getGoodsMark($gids);
        $mark_ids = Utilities::DataToArray($goods_marks, 'mark_id');
        $mark_ids = array_unique($mark_ids);
        $mark_info = self::getMarkInfo($mark_ids, self::$types[$type]);
        $ids = Utilities::DataToArray($mark_info, 'id');
        $mark_detail = self::getMarkDetail($ids, self::$platforms[$platform], self::$position['upright'], self::$direction['tilt']);

        $show_marks = array();
        foreach ($goods_marks as $goods_mark) {
            $goods_id = $goods_mark['goods_id'];
            $mark_id = $goods_mark['mark_id'];
            if (!empty($mark_detail[$mark_id])) {
                $show_marks[$goods_id][] = array_merge($mark_info[$mark_id], $mark_detail[$mark_id]);
            }
        }
        foreach ($show_marks as $goods_id => $show_mark) {
            $show_marks[$goods_id] = Utilities::sortArray($show_mark, 'order', 'ASC');
        }

        return $show_marks;
    }

    public static function getUpLeftMarks($mark_ids, $platform = '', $type = 'discount') {
        if (empty($mark_ids) || !is_array($mark_ids)) {
            return array();
        }
        empty($platform) && $platform = 'Web';

        $mark_info = self::getMarkInfo($mark_ids, self::$types[$type]);
        if (empty($mark_info)) {
            return array();
        }

        $ids = Utilities::DataToArray($mark_info, 'id');
        $mark_detail = self::getMarkDetail($ids, self::$platforms[$platform], self::$position['upleft'], self::$direction['horizontal']);
        $marks = array();
        foreach ($mark_detail as $mark_id => $single_mark) {
            $single_mark['img_url'] = Utilities::convertPicture($single_mark['img_url']);
            $marks[] = array_merge($mark_info[$mark_id], $single_mark);
        }
        $marks = Utilities::sortArray($marks, 'order', 'ASC');
        return $marks;
    }

    private static function getGoodsMark($gids) {
        if (empty($gids)) {
            return array();
        }
        $params = array(
            'goods_id' => $gids,
            'status' => 0,
        );
        return FashionGoodsMarks::getGoodsMark($params, 'goods_id, mark_id');
    }

    private static function getAttrMark($data_ids, $attr_type) {
        if (empty($data_ids) || empty($attr_type)) {
            return array();
        }
        $params = array(
            'data_id' => $data_ids,
            'type' => $attr_type,
            'status' => 0,
        );
        return FashionGoodsMarks::getMark($params, 'data_id, type, mark_id');
    }

    private static function getMarkDetail($mark_ids, $platform, $position, $direction) {
        empty($platform) && $platform = 1;
        if (empty($mark_ids)) {
            return array();
        }
        $params = array(
            'mark_id' => $mark_ids,
            'platform' => $platform,
            'position' => $position,
            'direction' => $direction,
            'status' => 0,
        );
        return FashionGoodsMarks::getMarkDetail($params, 'mark_id, img_url, img_width, img_height, mark_text', FALSE, 'mark_id');
    }

    private static function getMarkInfo($mark_ids, $type) {
        if (empty($mark_ids)) {
            return array();
        }
        $params = array(
            'id' => $mark_ids,
            'type' => $type,
            'status' => 0,
        );
        return FashionGoodsMarks::getMarkInfo($params, '`id`, `order`', FALSE, 'id');
    }

    // 获得横标信息 -- pc/mob/shouq/weixin/haitao --
    public static function getUpleftHorizontalMarks($cargogoods, $platform, $type, $count = 2) {
        $gids = Utilities::DataToArray($cargogoods, "goods_id");
        if (empty($gids) || !is_array($gids)) {
            return array();
        }
    
        MarkMem::setPrefix($platform.$type);
        $cacheHitsData = MarkMem::instance()->getCache($gids);
        $noHitsGids = MarkMem::instance()->notInCache();
        !is_array($cacheHitsData) && $cacheHitsData = array();
    
        $paramData = array();
        $paramData['gids'] = implode(',', $noHitsGids);
        $paramData['platform'] = $platform;
        $paramData['type'] = $type;
        $paramData['count'] = $count;
    
        !empty($noHitsGids) && $markinfo = self::_getUpleftHorizontalMarks($paramData);
        !is_array($markinfo) && $markinfo = array();
        MarkMem::instance()->setCache($markinfo);
    
        $marks = $cacheHitsData + $markinfo;
        foreach ($marks as &$info) {unset($info['to_cache']);}
        return $marks;
    }
    
    
    private static function _getUpleftHorizontalMarks($paramData) {
        $param = array('url' => 'fashion/get_upleft_marks');
        $opt = array('timeout' => 1, 'connect_timeout' => 1);
        \Libs\Serviceclient\SnakeHeaderCreator::setInfos(array('user_id' => 0, 'ip' => '127.0.0.2'));
        $clientObj = new \Libs\Serviceclient\Client();
        $result = $clientObj->call('virus', $param['url'], $paramData, $opt);
    
        if ($result['httpcode'] == 200 && isset($result['content']) && $result['content']['error_code'] == 0) {
            $markinfo = $result['content']['data'];
        } else {
            return array();
        }
    
        $gids = explode(',', $paramData['gids']);
        !is_array($markinfo) && $markinfo = array();
        foreach ($gids as $gid) {
            if (!isset($markinfo[$gid]) || empty($markinfo[$gid])) {
                $markinfo[$gid] = array('to_cache' => '1');
            }
        }
    
        return $markinfo;
    }
}

/*
array(
   'mark_id' => '1999',
   'img_url' => $price_mark_img_url,
   'img_width' => '63',
   'img_height' => '83',
   'mark_text' => '￥' . $off['phone_price'],
   'text_font_color' => '#993300',
   'text_margin_top' => '56',
);
*/
