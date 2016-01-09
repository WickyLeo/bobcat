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
 * @since: 15/7/1 下午5:28
 * @description:
 */

namespace Libs\Util;

class ImageUtil {
    /**
     * 根据原图链接获取原图的宽高
     * @params:
     * $url : string, url of picture
     * @return:array('width'=>xxx, 'height'=>xxx)
     */
    public static function getOrigWidthHeight($url) {
        if (empty($url)) {
            return null;
        }
        $urlArgs = parse_url($url);
        $uri = ltrim($urlArgs['path'], '/');
        if (empty($uri)) {
            return null;
        }
        $parts = explode("_", $uri);
        if (count($parts) < 4) {
            return null;
        }
        $width = (int)$parts[2];
        $parts2 = explode(".", $parts[3]);
        if (count($parts2) < 2) {
            return null;
        }
        $height = (int)$parts2[0];
        return array('width'=>$width, 'height'=>$height);
    }

    /**
     * 根据原图链接生成缩略图链接
     * @params:
     * $origUrl: string, url of original picture
     * $sType: string, 切割算法，具体参考wiki上的文档
     * $thumbWidth: int, 缩放后的宽度
     * $thumbHeight: int, 缩放后的高度
     * $sharpenFlag: bool, 是否加锐化效果, true: 增加锐化
     * @return: string
     */
    public static function generateThumbUrl($origUrl, $sType, $thumbWidth, $thumbHeight, $sharpenFlag=false) {
        if (empty($origUrl)) {
            return '';
        }
        $urlArgs = parse_url($origUrl);
        $uri = ltrim($urlArgs['path'], '/');
        if (empty($uri)) {
            return '';
        }
        if ($sharpenFlag) {
            $amount = 150;
            $radius = 5;
            $threshold = 0;
            $token = $amount . $radius . $threshold . $thumbWidth . $thumbHeight . "meilishuonewyearhappy";
        }
        else {
            $token = $thumbWidth . $thumbHeight . "meilishuonewyearhappy";
        }
        $md5sum = md5($uri . $token);
        $eightM = substr($md5sum, 0, 8);
        $target = $origUrl . "_{$eightM}_{$sType}";
        $sharpenFlag && $target .= "_q0_{$amount}_{$radius}_{$threshold}";
        $target .= "_{$thumbWidth}_{$thumbHeight}.jpg";
        return $target;
    }

    /**
     * 判断链接是否是原图链接
     * @params:
     * $url : string, url of picture
     * @return:int
     * -1: invalid url
     *  0: not original
     *  1: original
     */
    public static function isOrigUrl($url) {
        if (empty($url)) {
            return -1;
        }
        $urlArgs = parse_url($url);
        $uri = ltrim($urlArgs['path'], '/');
        if (empty($uri)) {
            return -1;
        }
        if (strstr($uri, '_o') == false) {
            return 0;
        } else {
            if (substr_count($uri, ".") <= 2 && substr_count($uri, "_") <= 3) {
                return 1;
            } else {
                return 0;
            }
        }
    }
}