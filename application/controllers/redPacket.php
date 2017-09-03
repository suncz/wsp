<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once (APPPATH . 'vendor/autoload.php');

class Home extends SczController {

    public function __construct() {
        parent::__construct();
        $this->load->model('redis/redisString');
    }
/**
     * 红包派发页面
     */
    public function redPacketReceive() {
        $isLogin = parent::isLogin();
        if ($isLogin === false) {
            $this->jsonOutput();
            return;
        }
        $redPacketId = $_GET['redPacketId'];
        $userId = $this->userInfo['userId'];
        $redPackInfo = $this->db->select('*')->from('redPacket')->where('id', $redPacketId)->get()->result_array();
        if (count($redPackInfo) == 0) {
            $this->result['ret'] = 1001;
            $this->result['msg'] = '参数错误';
            $this->jsonOutput();
            return;
        }
        $redPacketLogList = $this->db->select('*')->from('redPacketLog')->where('redPacketId', $redPacketId)->get()->result_array();
        $isReceived = 0;
        $tempMoney = 0;
        foreach ($redPacketLogList as $key => &$value) {
            if ($value['ReceiverUserId'] == $userId) {
                $isReceived = 1;
            } else {
                $isReceived = 0;
            }
            $tempMoney = $value['receiveMoney'];
        }
        //红包领完了
        if (count($redPacketLogList) == $redPackInfo[0]['num']) {
            $isSendEnd = 1;
        }
        //红包未领完
        else {
            $isSendEnd = 0;
        }
        $this->result['data']['redPackInfo'] = $redPackInfo;
        $this->result['data']['isReceived'] = $isReceived; //是否领取 0 未领取 1：已领取
        $this->result['data']['isSendEnd'] = $isSendEnd; //是否派发完了 0 未派发完 1派发完了 
        $this->result['data']['isExpired'] = 0; //是否过期 0未过期 1 已过期
        $this->jsonOutput();
    }

    /**
     * 红包详情页
     */
    public function redPacketDetail() {
        $isLogin = parent::isLogin();
        if ($isLogin === false) {
            $this->jsonOutput();
            return;
        }
        $redPacketId = $_GET['redPacketId'];
        $userId = $this->userInfo['userId'];
        $redPackInfos = $this->db->select('*')->from('redPacket')->where('id', $redPacketId)->get()->result_array();
        if (count($redPackInfos) == 0) {
            $this->result['ret'] = 1001;
            $this->result['msg'] = '参数错误';
            $this->jsonOutput();
            return;
        }
        $redPackInfo = $redPackInfos[0];
        //获取红包列表
        $redPacketLogList = $this->db->select('*')->from('redPacketLog')->where('redPacketId', $redPacketId)->get()->result_array();
        $receivedNum = count($redPacketLogList);
        $tempMoney = 0;
        $userReceiveMoney = 0;
        $totalReceiveMoney = 0;
        $isRedPacketHostUser = false;
        foreach ($redPacketLogList as $key => &$value) {
            $value['isBestLuck'] = 0;
            //是否要需要体现手气最佳  人气红包 并且全部被抢了才需要显示人气最佳
            if ($redPackInfo['type'] == '2' && $receivedNum == $redPackInfo['num'] && $value['receiveMoney'] > $tempMoney) {
                $redPacketLogList[$key - 1]['isBestLuck'] = 0;
                $redPacketLogList[$key]['isBestLuck'] = 1;
            }
            if ($value['ReceiverUserId'] == $userId) {
                $userReceiveMoney = $value['receiveMoney'] / 100;
            }
            //红包发放者是此登录用户
            if ($redPackInfo['userId'] == $userId) {
                $isRedPacketHostUser = true;
            }
            $tempMoney = $value['receiveMoney'];
            $totalReceiveMoney += $value['receiveMoney'];
        }
        //红包发放者是此登录用户
        if ($isRedPacketHostUser) {
            //普通红包
            if ($redPackInfo['type'] == 1) {
                 //红包派发完了
                if ($receivedNum == $redPackInfo['num']) {
                    $displayWord = $redPackInfo['num'] . '个红包共' . ($redPackInfo['money'] / 100) . '元';
                } else {
                    $displayWord = '已领取' . $receivedNum . '/' . $redPackInfo['num'] . '个红包共' . ($totalReceiveMoney/ 100) . '/' . ($redPackInfo['money'] / 100) . '元';
                }
            }
            //人气红包
            else if ($redPackInfo['type'] == 1) {
                //红包派发完了
                if ($receivedNum == $redPackInfo['num']) {
                    $displayWord = $redPackInfo['num'] . '个红包共' . ($redPackInfo['money'] / 100) . '元，已全部被抢光';
                } else {
                    $displayWord = '已领取' . $receivedNum . '/' . $redPackInfo['num'] . '个红包共' . ($totalReceiveMoney/ 100) . '/' . ($redPackInfo['money'] / 100) . '元';
                }
            }
        }
        //红包发放者不是登陆者本人
        else
        {
            //普通红包
            if ($redPackInfo['type'] == 1) {
                 //红包派发完了
                if ($receivedNum == $redPackInfo['num']) {
                    $displayWord = $redPackInfo['num'] . '个红包共' . ($redPackInfo['money'] / 100) . '元';
                } else {
                    $displayWord = '已领取' . $receivedNum . '/' . $redPackInfo['num'] . '个红包共' . ($totalReceiveMoney/ 100) . '/' . ($redPackInfo['money'] / 100) . '元';
                }
            }
            //人气红包
            else if ($redPackInfo['type'] == 1) {
                //红包派发完了
                if ($receivedNum == $redPackInfo['num']) {
                    $displayWord = $redPackInfo['num'] . '个红包共，已全部被抢光';
                } else {
                    $displayWord = '已领取' . $receivedNum . '/' . $redPackInfo['num'] . '个红包' ;
                }
            }
        }
        $this->result['data']['userReceiveMoney'] = $userReceiveMoney;
        $this->result['data']['redPackInfo'] = $redPackInfo;
        $this->result['data']['redPacketLogList'] = $redPacketLogList;
        $this->jsonOutput();
    }
    /**
     * 用户发放红包
     */
    public function sendRedPacket()
    {
           $type=$_POST['type'];
           $num=$_POST['num'];
           $money=$_POST['money'];
           
    }
}
