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
 * @since: 15/6/25 下午2:31
 * @description:
 */

namespace Libs\QueryBuilder;;


class Raw {

    /**
     * @var string
     */
    protected $value;

    /**
     * @var array
     */
    protected $bindings;

    public function __construct($value, $bindings = array())
    {
        $this->value = (string)$value;
        $this->bindings = (array)$bindings;
    }

    public function getBindings()
    {
        return $this->bindings;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->value;
    }

}