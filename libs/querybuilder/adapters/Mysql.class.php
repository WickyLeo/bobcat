<?php
/**
 * ┏┓     ┏┓
 *┏┛┻━━━━━┛┻┓
 *┃         ┃ 　
 *┃    ━    ┃
 *┃ ┳┛   ┗┳ ┃　
 *┃         ┃　　　　
 *┃    ┻    ┃　　
 *┃         ┃　　　　
 *┗━┓     ┏━┛
 *  ┃     ┃   神兽保佑,代码无BUG！　　　　　　　
 *  ┃     ┃
 *  ┃     ┗━┓
 *  ┃       ┣┓　　　　
 *  ┃       ┏┛
 *  ┗┓┓┏━┳┓┏┛
 *   ┃┫┫ ┃┫┫
 *   ┗┻┛ ┗┻┛
 *
 * @author: Tian Shuang
 * @since: 15/6/25 下午3:57
 * @description:
 */

namespace Libs\QueryBuilder\Adapters;


class Mysql extends BaseAdapter {
    /**
     * @var string
     */
    protected $sanitizer = '`';
}