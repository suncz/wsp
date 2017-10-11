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
    
     /**
     * 生成唯一编号 待优化  
     * @param type $time
     * @param type $type 1:goodsSn 2 skuSn 3. orderSn
     * @param orderType 和订单表里面的hh_orders type 1：嘻哈商城订单 2.演出票订单 3.转票订单 4.活动票订单', 
     * @parm buyPlatform 订单购买平台：1为PC，2为App，3为小程序，4为浏览器，5为其它
     * @return string
     * @author sunchangzhi
     */
    static function getSn($time, $type = 1,$orderType=1,$buyPlatform=5) {
         //订单
        if($type==3)
        {
            $redis=\spp\model\CRedisString::getInstance(\lib\CRedisKey::ORDER_NUM_SET.'.'.date("Ymd"));
            $orderNum=$redis-> incrBy(1);
            if($orderNum==1)
            {
                $redis->expire(3600*24*3);
            }
            $strOrderNum=str_pad($orderNum, 4,"0",STR_PAD_LEFT);
            $random = mt_rand(1000, 9999);
            $orderSn= date("Ymd").$buyPlatform.$orderType.$strOrderNum.$random;
            return substr($orderSn, 2); 
        }
        //生成24位唯一编码，格式：YYYY-MMDD-HHII-SS-NNNN,NNNN-CC，其中：YYYY=年份，MM=月份，DD=日期，HH=24格式小时，II=分，SS=秒，NNNNNNNN=随机数，CC=检查码
        //唯一编码主体（YYYYMMDDHHIISSNNNNNNNN）
        $order_id_main = date('YmdHis', $time) . mt_rand(10000000, 99999999);
        //唯一编码主体长度
        $order_id_len = strlen($order_id_main);
        $order_id_sum = 0;
        for ($i = 0; $i < $order_id_len; $i++) {
            $order_id_sum += (int) (substr($order_id_main, $i, 1));
        }
        //唯一编码（YYYYMMDDHHIISSNNNNNNNNCC）
        $sn = $order_id_main . str_pad((100 - $order_id_sum % 100) % 100, 2, '0', STR_PAD_LEFT);
        return $sn;
    }
}