<?php

namespace Libs\Serviceclient\Api;

/**
 * 记录api的请求方式和服务模块
 * @author zx
 */
class VirusApiList extends \Libs\Serviceclient\Api\ApiList {

    protected static $apiList = array(
        'cargo/cargo_main' => array('service' => 'virus', 'method' => 'GET', 'opt' => array('timeout' => 3)),
        'cargo/cargo_latest' => array('service' => 'virus', 'method' => 'GET', 'opt' => array('timeout' => 2)),
        'cargo/shop_info' => array('service' => 'virus', 'method' => 'GET', 'opt' => array('timeout' => 3)),
        'cargo/cargo_activity' => array('service' => 'virus', 'method' => 'GET', 'opt' => array('timeout' => 3)),
        'cargo/property_info' => array('service' => 'virus', 'method' => 'GET', 'opt' => array('timeout' => 2)),
        'cargo/cargo_stock' => array('service' => 'virus', 'method' => 'GET', 'opt' => array('timeout' => 3)),

        // 活动相关
        'promote/activity_detail' => array('service' => 'virus', 'method' => 'GET', 'opt' => array('timeout' => 3)),
        'promote/activity_item_list' => array('service' => 'virus', 'method' => 'GET', 'opt' => array('timeout' => 3)),
        'promote/promote_sameshop' => array('service' => 'virus', 'method' => 'GET', 'opt' => array('timeout' => 1)),
        'promote/goods_info' => array('service' => 'virus', 'method' => 'GET', 'opt' => array('timeout' => 3)),
        'promote/activity_top_push' => array('service' => 'virus', 'method' => 'GET', 'opt' => array('timeout' => 3)),
        'promote/activity_group_list' => array('service' => 'virus', 'method' => 'GET', 'opt' => array('timeout' => 3)),
        'promote/activity_ads' => array('service' => 'virus', 'method' => 'GET', 'opt' => array('timeout' => 3)),


        // 搭配购
        'cargo/cargo_match' => array('service' => 'virus', 'method' => 'GET', 'opt' => array('timeout' => 2)),
        'cargo/cargo_match_select' => array('service' => 'virus', 'method' => 'GET', 'opt' => array('timeout' => 2)),
        //new 人气单品推荐接口
        'promote/promote_hotsales_new' => array('service' => 'virus', 'method' => 'GET', 'opt' => array('timeout' => 2)),
        'promote/activity_goods_info' => array('service' => 'virus', 'method' => 'GET', 'opt' => array('timeout' => 3)),
        'promote/activity_get' => array('service' => 'virus', 'method' => 'GET', 'opt' => array('timeout' => 3)),
        'goodslist/get_goods_list' => array('service' => 'virus', 'method' => 'POST', 'opt' => array('timeout' => 5)),
        'taobao/taobao_count_comment' => array('service' => 'virus', 'method' => 'POST', 'opt' => array('timeout' => 2)),
        'user/get_base_info' => array('service' => 'virus', 'method' => 'POST', 'opt' => array('timeout' => 2)),
        'url/get_base_info' => array('service' => 'virus', 'method' => 'POST', 'opt' => array('timeout' => 2)),
        'twitter/get_recent_replyinfo' => array('service' => 'virus', 'method' => 'POST', 'opt' => array('timeout' => 2)),
        'twitter/get_base_show_info' => array('service' => 'virus', 'method' => 'POST', 'opt' => array('timeout' => 2)),
        'group/get_base_info' => array('service' => 'virus', 'method' => 'POST', 'opt' => array('timeout' => 2)),
        'act/get_info_by_tids' => array('service' => 'virus', 'method' => 'POST', 'opt' => array('timeout' => 1)),
        'goods/get_base_info' => array('service' => 'virus', 'method' => 'POST', 'opt' => array('timeout' => 2)),
        'group/get_twitter_group_info' => array('service' => 'virus', 'method' => 'POST', 'opt' => array('timeout' => 2)),
        'picture/get_base_info' => array('service' => 'virus', 'method' => 'POST', 'opt' => array('timeout' => 2)),
        'cargo/cargo_info' => array('service' => 'virus', 'method' => 'GET', 'opt' => array('timeout' => 3)),
        'cargo/cargo_prop' => array('service' => 'virus', 'method' => 'GET', 'opt' => array('timeout' => 3)),
        'fashion/fashion_buyer_recommend' => array('service' => 'virus', 'method' => 'GET', 'opt' => array('timeout' => 2)),
        'shop/partner_info' => array('service' => 'virus', 'method' => 'GET', 'opt' => array('timeout' => 3)),
        'shop/shop_top_banner_get' => array('service' => 'virus', 'method' => 'GET', 'opt' => array('timeout' => 3)),
        'shop/shop_columns_get' => array('service' => 'virus', 'method' => 'GET', 'opt' => array('timeout' => 3)),
        'cargo/twitter_activity' => array('service' => 'virus', 'method' => 'GET', 'opt' => array('timeout' => 3)),
        //店铺白名单
        'shop/shop_white_list' => array('service' => 'virus', 'method' => 'GET', 'opt' => array('timeout' => 3)),
        //店铺统计信息，销量、商品数量
        'shop/shop_stats_list' => array('service' => 'virus', 'method' => 'GET', 'opt' => array('timeout' => 3)),
        'member/get_free_point' => array('service' => 'virus', 'method' => 'POST', 'opt' => array('timeout' => 3)),
        //积分接口
        'member/consume_point' => array('service' => 'virus', 'method' => 'POST', 'opt' => array('timeout' => 3)),
        'shopevent/selectEventGoods' => array('service' => 'virus', 'method' => 'POST', 'opt' => array('timeout' => 1)),
        'shopevent/select_event_goods' => array('service' => 'virus', 'method' => 'POST', 'opt' => array('timeout' => 2)),
        'promote/shop_hot' => array('service' => 'virus', 'method' => 'GET', 'opt' => array('timeout' => 3)),
        //根据条件获取campaign_goods_info表信息
        'shopevent/get_campaign_goods_info' => array('service' => 'virus', 'method' => 'GET', 'opt' => array('timeout' => 3)),
        //根据条件获取campaign_info表信息
        'shopevent/get_campaign_info' => array('service' => 'virus', 'method' => 'GET', 'opt' => array('timeout' => 3)),
        'goods/get_commerce_info' => array('service' => 'virus', 'method' => 'POST', 'opt' => array('timeout' => 2)),        
        'brdwiki/categories' => array('service' => 'virus', 'method' => 'GET', 'opt' => array('timeout' => 2)),        
        'brdwiki/qlist' => array('service' => 'virus', 'method' => 'GET', 'opt' => array('timeout' => 2)),        
        'brdwiki/detail' => array('service' => 'virus', 'method' => 'GET', 'opt' => array('timeout' => 2)),        
        'obanner/banner_share_main' => array('service' => 'virus', 'method' => 'GET', 'opt' => array('timeout' => 3)),
        'shop/category_list' => array('service' => 'virus', 'method' => 'GET','opt' => array('timeout' => 3)),
        'url/short_url' => array('service' => 'virus', 'method' => 'GET','opt' => array('timeout' => 3)),
        'freight/get_campaign' => array('service' => 'virus', 'method' => 'GET','opt' => array('timeout' => 3)),

        // 个人消息-评论 mob
        'alert/alert_add' => array('service' => 'virus', 'method' => 'GET','opt' => array('timeout' => 2)),
        // 搭配一口价
        'shoppair/pair_price' => array('service' => 'virus', 'method' => 'GET','opt' => array('timeout' => 2)),
        'shop/goods_day_new' => array('service' => 'virus', 'method' => 'POST','opt' => array('timeout' => 2)),
		// 购物车数量copy一份到snake
        'order/shopping_cart_number' => array('service' => 'virus', 'method' => 'GET','opt' => array('timeout' => 2)),
        'freight/get_freight' => array('service' => 'virus', 'method' => 'GET','opt' => array('timeout' => 2)),  
        'order/addr_query' => array('service' => 'virus', 'method' => 'GET','opt' => array('timeout' => 2)), 
        'campaign/campaign_query' => array('service' => 'virus', 'method' => 'POST','opt' => array('timeout' => 2)),    
        'club/user_stature' => array('service' => 'virus', 'method' => 'POST','opt' => array('timeout' => 2)),      
        
        'recommend/recommend_seller' => array('service' => 'virus', 'method' => 'POST','opt' => array('timeout' => 2)),                              
        
        // 打标
        'fashion/get_upleft_marks' => array('service' => 'virus', 'method' => 'POST', 'opt' => array('timeout' => 2)),
    );

}
