<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Fn{
    /**
     * 计算时间过去了多久
     * 小于分钟 用秒 小于小时 用分钟 小于天数 用小时 ……小于年数用天数
     * @param $time
     * @return array
     */
    public static function getTimeToNowText($time)
    {
        $pastTime = time() - $time;
        if ($pastTime < 60) {
            return '刚刚';
        } elseif ($pastTime < 3600) {
            return floor($pastTime / 60) . '分钟前';
        } elseif ($pastTime < 3600 * 24) {
            return floor($pastTime / 3600) . '小时前';
        } elseif ($pastTime < 3600 * 24 * 7) {
            return floor($pastTime / (3600 * 24)) . '天前';
        } elseif ($pastTime < 3600 * 24 * 30) {
            return floor($pastTime / (3600 * 24 * 7)) . '周前';
        } elseif ($pastTime < 3600 * 24 * 365) {
            return floor($pastTime / (3600 * 24 * 30)) . '月前';
        } else {
            return floor($pastTime / (3600 * 24 * 365)) . '年前';
        }
    }
    /**
     * 计算时间过去了多久
     * 小于分钟 用秒 小于小时 用分钟 小于天数 用小时 ……小于年数用天数
     * @param $time
     * @return array
     */
    public static function getTimeFormat($time)
    {
        $weekarray=array("日","一","二","三","四","五","六"); //先定义一个数组
        //今天
         $todayTimeStart=strtotime(date('Y-m-d'));
        if ($time < ($todayTimeStart+86400)&&$time>$todayTimeStart) {
            return date("h:ia",$time);
            //昨天
        } elseif ($time < $todayTimeStart && $time>$todayTimeStart-86400) {
            return '昨天'.date("h:ia",$time);
        }//一周内  
        elseif ($time <$todayTimeStart-86400&&$time> $todayTimeStart-86400*7 ) {
            return "星期".$weekarray[date("w")].date("h:ia",$time);
        }else
        {
            return date("Y-m-d H:i",$time);
        }
    }
}