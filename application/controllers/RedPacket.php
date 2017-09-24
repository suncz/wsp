<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once (APPPATH . 'vendor/autoload.php');

class RedPacket extends SczController {

    public function __construct() {
        parent::__construct();
        $this->load->model('redis/redisString');
        $this->load->model('redis/redisList');
        $this->load->model('redis/redisZSet');
        $this->load->library('redPacketAlgorithm');
    }

    /**
     * 红包派发页面
     */
    public function redPacketReceivePage() {
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
        $this->result['data']['redPackInfo'] = $redPackInfo[0];
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
                    $displayWord = '已领取' . $receivedNum . '/' . $redPackInfo['num'] . '个红包共' . ($totalReceiveMoney / 100) . '/' . ($redPackInfo['money'] / 100) . '元';
                }
            }
            //人气红包
            else if ($redPackInfo['type'] == 1) {
                //红包派发完了
                if ($receivedNum == $redPackInfo['num']) {
                    $displayWord = $redPackInfo['num'] . '个红包共' . ($redPackInfo['money'] / 100) . '元，已全部被抢光';
                } else {
                    $displayWord = '已领取' . $receivedNum . '/' . $redPackInfo['num'] . '个红包共' . ($totalReceiveMoney / 100) . '/' . ($redPackInfo['money'] / 100) . '元';
                }
            }
        }
        //红包发放者不是登陆者本人
        else {
            //普通红包
            if ($redPackInfo['type'] == 1) {
                //红包派发完了
                if ($receivedNum == $redPackInfo['num']) {
                    $displayWord = $redPackInfo['num'] . '个红包共' . ($redPackInfo['money'] / 100) . '元';
                } else {
                    $displayWord = '已领取' . $receivedNum . '/' . $redPackInfo['num'] . '个红包共' . ($totalReceiveMoney / 100) . '/' . ($redPackInfo['money'] / 100) . '元';
                }
            }
            //人气红包
            else if ($redPackInfo['type'] == 1) {
                //红包派发完了
                if ($receivedNum == $redPackInfo['num']) {
                    $displayWord = $redPackInfo['num'] . '个红包共，已全部被抢光';
                } else {
                    $displayWord = '已领取' . $receivedNum . '/' . $redPackInfo['num'] . '个红包';
                }
            }
        }
        $this->result['data']['userReceiveMoney'] = $userReceiveMoney;
        $this->result['data']['displayWord'] = $displayWord;
        $this->result['data']['redPackInfo'] = $redPackInfo;
        $this->result['data']['redPacketLogList'] = $redPacketLogList;
        $this->jsonOutput();
    }

    /**
     * 用户发放红包 生成红包id
     */
    public function sendRedPacket() {
        parent::isLogin();
        $type = $_POST['type']; //红包类型 人气 普通
        $num = $_POST['num']; //红包数量
        $money = $_POST['money']; //红包金额 单位分
        $codeWord = $_POST['codeWord']; //红包文案
        $videoId = $_POST['videoId']; //红包文案
        if (!$type || !$num || !$money) {
            $this->result['ret'] = 1001;
            $this->result['msg'] = '参数错误';
            $this->jsonOutput();
            return;
        }
        if ($num > 100) {
            $this->result['ret'] = 2001;
            $this->result['msg'] = '红包数量不得超过100个';
            $this->jsonOutput();
            return;
        }
        if ($money < $num) {
            $this->result['ret'] = 2002;
            $this->result['msg'] = '金额至少为1元';
            $this->jsonOutput();
            return;
        }
//        print_r($this->userInfo);exit;
        //生成红包
        $data = array(
            'videoId' => $videoId,
            'userId' => $this->userInfo['userId'],
            'nickName' => $this->userInfo['nickName'],
            'headImgUrl' => $this->userInfo['headimgurl'],
            'num' => $num,
            'receivedNum' => 0,
            'money' => $money,
            'codeWord' => $codeWord,
            'type' => $type,
            'payStatus' => $this->isDebug?2:0,
        );
        $this->db->insert('redPacket', $data);
        $redpacketId = $this->db->insert_id();
//        echo mt_rand(1, 5);exit;
        $redPacketAlgorithm = new redPacketAlgorithm();
        //普通
        if ($type == 1) {
            $randomMoney = $redPacketAlgorithm->getBonus($money, $num, $money / $num, 1);
        }//人气
        else if ($type == 2) {
//            print_r($this);exit;
//            print_r($this->redPacketAlgorithm);exit;
            $randomMoney = $redPacketAlgorithm->getBonus($money, $num, $money / $num + $money * (mt_rand(1, 200) / 100), 1);
        }
        foreach ($randomMoney as $v) {
            $this->redisList->lpush(RedisKey::RED_PACKET_RANDOM_LIST_ID . $redpacketId, $v);
        }
        //插入comment表
        $insertComment = [
            'videoId' => $videoId,
            'userId' => $this->userInfo['userId'],
            'userNickName' => $this->userInfo['nickName'],
            'userHeadImgUrl' => $this->userInfo['headimgurl'],
            'redPacketId' => $redpacketId,
            'redPacketLogId' => 0,
            'redPacketUserId' => $this->userInfo['userId'],
            'redPacketUserNickName' => $this->userInfo['nickName'],
            'type' => 3,
            'status' =>  $this->isDebug?1:0,
        ];
        $this->db->insert('comment', $insertComment);
        $this->result['data']['redpacketId'] = $redpacketId;
        $this->jsonOutput();
    }

    /**
     * 抢红包
     */
    public function grabRedPacket() {
        parent::isLogin();
        $redPacketId = $_POST['redPacketId'];
        $userId = $this->userInfo['userId'];
        $redPackInfos = $this->db->select('*')->from('redPacket')->where('id', $redPacketId)->get()->result_array();
        if (count($redPackInfos) == 0) {
            $this->result['ret'] = 1001;
            $this->result['msg'] = '参数错误';
            $this->jsonOutput();
            return;
        }
        $this->result['data']['grabRedPacketMoney'] = 0;
        $redPackInfo = $redPackInfos[0];

        if (strtotime($redPackInfo['createTime']) < time() - 86400) {
            $this->result['ret'] = 2002; //抢红包失败，红包已过期
            $this->result['msg'] = '红包已过期';
            $this->jsonOutput();
            return;
        }

        if ($redPackInfo['num'] == $redPackInfo['receivedNum']) {
            $this->result['ret'] = 2003; //抢红包失败，红包已过期
            $this->result['msg'] = '手慢了，红包已经领完了';
            $this->jsonOutput();
            return;
        }
        if ($redPackInfo['payStatus'] != 2) {
            $this->result['ret'] = 2004;
            $this->result['msg'] = '红包还未生效';
            $this->jsonOutput();
            return;
        }
        //查看自己是否领取过红包
        $sql = "select id,receiveMoney from redPacketLog where redPacketId=$redPacketId and ReceiverUserId=$userId";
        $userRedPacketLog = $this->db->query($sql)->row_array();
        if (count($userRedPacketLog) > 0) {
            $this->result['ret'] = 2005; //抢红包失败，您已经抢过红包了
            $this->result['msg'] = '您已经抢过红包了';
            $this->jsonOutput();
        }
        //发放红包
        $money = $this->redisList->lpop(RedisKey::RED_PACKET_RANDOM_LIST_ID . $redPacketId);
        if (empty($money)) {
            $this->result['ret'] = 2003;
            $this->result['msg'] = '手慢了，红包已经领完了';
            $this->jsonOutput();
            return;
        }
        try {
            //生成红包
            $data = array(
                'redPacketId' => $redPacketId,
                'payerUserId' => $this->userInfo['userId'],
                'payerUserNickName' => $this->userInfo['nickName'],
                'payerUserheadImgUrl' => $this->userInfo['headimgurl'],
                'receiverUserId' => $this->userInfo['userId'],
                'receiverUserNickName' => $this->userInfo['nickName'],
                'receiverUserheadImgUrl' => $this->userInfo['headimgurl'],
                'receiveMoney' => $money
            );
            $this->db->insert('redPacketLog', $data);
            $redPacketLogInsertId = $this->db->insert_id();
            $updateRedPacketSql = "update redPacket set receivedNum=receivedNum+1 where id=$redPacketId";
            $rows = $this->db->query($updateRedPacketSql);
            //插入comment表
            $insertComment = [
                'videoId' => $redPackInfo['videoId'],
                'userId' => $this->userInfo['userId'],
                'userNickName' => $this->userInfo['nickName'],
                'userHeadImgUrl' => $this->userInfo['headimgurl'],
                'getRedPacketMoney' => $money,
                'redPacketId' => $redPackInfo['id'],
                'redPacketId' => $redPackInfo['id'],
                'redPacketLogId' => $redPacketLogInsertId,
                'redPacketUserId' => $redPackInfo['userId'],
                'redPacketUserNickName' => $redPackInfo['nickName'],
                'redPacketUserNickName' => $redPackInfo['nickName'],
                'type' => 4,
            ];
            $this->db->insert('comment', $insertComment);

            if ($rows == 0) {
                throw new Exception("网络繁忙", 1002);
            }
        } catch (Exception $exc) {
            $this->redisList->lpush(RedisKey::RED_PACKET_RANDOM_LIST_ID . $redPacketId, $money);
            $this->result['ret'] = $exc->getCode(); //抢红包失败，网络繁忙
            $this->result['msg'] = $exc->getMessage();
            $this->jsonOutput();
            return;
        }
        $this->jsonOutput();
    }

    /**
     * 红包是否支付
     */
    function isPayed() {
        $isLogin = parent::isLogin();
        if ($isLogin == false) {
            $this->jsonOutput();
        }
        $redPacketId = (int) $_GET['redPacketId'];
        $redPackeInfo = $this->db->from('redpacket')->where('id', $redPacketId)->where('payStatus', 2)->get()->row();
        $this->result['data']['isPayed'] = 1;
        if ($redPackeInfo == NULL) {
            $this->result['data']['isPayed'] = 0;
        }
        $this->jsonOutput();
    }

    /**
     * 获取打赏榜
     */
    public function getRewardRank() {
        $isLogin = parent::isLogin();
        if ($isLogin == false) {
            $this->jsonOutput();
        }
        $videoId=$_GET['videoId'];
        $rewardRankKey = RedisKey::REWARD_RANK_VIDEOID_DAY .$videoId. date('-Y-m-d', time());
        $list = $this->redisZSet->zRevRange($rewardRankKey, 0, 10, true);
        $userRankList = [];
        $myselfRankInfo = [];
//        exit;
        if (count($list) == 0) {
            $this->result['data'] = [];
        } else {
            $userIds = array_keys($list);
            if (array_key_exists($this->userInfo['userId'], $list) == false) {
                $myselfRank = $this->redisZSet->zRevRank($rewardRankKey, $this->userInfo['userId']);
                //无排名
                if ($myselfRank == false) {
                    
                } else {
                    $money = $this->redisZSet->score($rewardRankKey, $this->userInfo['userId']);
                    $myselfRankInfo['rank'] = $myselfRank;
                    $myselfRankInfo['money'] = $money;
                    $myselfRankInfo['userId'] = $this->userInfo['userId'];
                    $myselfRankInfo['headImgUrl'] = $this->userInfo['headimgurl'];
                    $myselfRankInfo['nickname'] = $this->userInfo['nickName'];
                }
            }
//            print_r($userIds);exit;
            $userInfos = $this->db->select("id as userId,headimgurl as headImgUrl,nickname")->from("user")->where_in('id', $userIds)->get()->result_array();

            foreach ($userInfos as $userInfo) {
                $newUserInfos[$userInfo['userId']] = $userInfo;
            }
            $i = 1;
            foreach ($list as $userId => $money) {
                $userInfo['userId'] = $userId;
                $userInfo['headImgUrl'] = $newUserInfos[$userId]['headImgUrl'];
                $userInfo['nickname'] = $newUserInfos[$userId]['nickname'];
                $userInfo['money'] = $money;
                $userInfo['rank'] = $i;
                $userRankList[] = $userInfo;
                $i++;
            }
        }
        $this->result['userRankList'] = $userRankList;
        $this->result['myselfRankInfo'] = $myselfRankInfo;
        $this->jsonOutput();
    }
    /**
     * 用户发放红包 生成红包id
     */
    public function sendRedPacketToPlatform() {
        parent::isLogin();
        $money = $_POST['money']; //红包金额 单位分
         $videoId=$_GET['videoId'];
        if (!$money||!$videoId) {
            $this->result['ret'] = 1001;
            $this->result['msg'] = '参数错误';
            $this->jsonOutput();
            return;
        }
       
//        print_r($this->userInfo);exit;
        //生成红包
        $data = array(
            'videoId' => $videoId,
            'userId' => $this->userInfo['userId'],
            'nickName' => $this->userInfo['nickName'],
            'headImgUrl' => $this->userInfo['headimgurl'],
            'num' => 1,
            'receivedNum' => 0,
            'money' => $money,
            'codeWord' => '',
            'type' => 3
        );
        $this->db->insert('redPacket', $data);
        $redpacketId = $this->db->insert_id();
        $this->result['data']['redpacketId'] = $redpacketId;
        $this->jsonOutput();
    }
    

}
