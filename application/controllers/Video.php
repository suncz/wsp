<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once (APPPATH . 'vendor/autoload.php');

class Video extends SczController {

    public function __construct() {
        parent::__construct();
        $this->load->model('redis/redisString');
        $this->load->model('redis/redisZSet');
        $this->load->library("fn");
    }

    public function detail() {
        $videoId= $_GET['videoId'];
        $isLogin = parent::isLogin();
        if ($isLogin == false) {
            $this->jsonOutput();
            return;
        }
        $sql="update video set pvNum = pvNum + 1 WHERE id = $videoId";
        $this->db->query($sql);
        $vedio = $this->db->select('*')->from('video')->where('id', $videoId)->get()->result_array()[0]; //获取视频 
        $vedio['praiseNum']=$this->fn->formatNumber($vedio['praiseNum']);
        $vedio['pvNum']=$this->fn->formatNumber($vedio['pvNum']);
        $cooperation = $this->db->select('*')->from('config')->where('key', 'cooperation')->get()->result()[0];
//        print_r($_SERVER);exit;
        $wechatScript = new \Wechat\WechatScript($this->config->item('wxYD'));
//        $wechatScript = new \Wechat\WechatScript(['appid'=>'wxcbf5123494909b33','appsecret'=>'7f2259347555e4a693246c31a4cb6d59']);
//        print_r($wechatScript);exit;
//        $url = strtolower('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        $url='http://hwsp.mzlicai.cn/?uid='.$this->userInfo['userId'].'&token='.$this->userInfo['token'].'&videoId='.$videoId;
        $data['vedioInfo'] = $vedio;
        $data['cooperation'] = !empty($cooperation->value) ? $cooperation->value : '暂无信息';
        $data['jsSign'] = $wechatScript->getJsSign($url);
        $data['vedioInfo']['shareLink'] = 'http://' . $_SERVER['HTTP_HOST'] . "/home/publicity/" . $videoId.'/'.$this->userInfo['userId'];
     
        $this->result['data'] = $data;
        $this->jsonOutput();
    }
    
    public function like()
    {
        $videoId=intval($this->input->post('videoId'));
        $sql="update video set praiseNum = praiseNum + 1 WHERE id = $videoId";
        $bool=$this->db->query($sql);
        if($bool){
                $video=$this->db->select('*')->from('video')->where('id',$videoId)->get()->result()[0];	//获取视频
                $this->result['data']['praiseNum'] = $this->fn->formatNumber($video->praiseNum);
        }else{
                 $this->result['data']['praiseNum'] = 0;
        }
        $this->jsonOutput();
    }
    
   function getInviteRank() {
        $isLogin = parent::isLogin();
        if ($isLogin == false) {
            $this->jsonOutput();
        }
        $videoId=$_GET['videoId'];
        $rewardRankKey = RedisKey::INVITE_RANK_VIDEOID_DAY .$videoId. '-'.date('Y-m-d', time());
        $list = $this->redisZSet->zRevRange($rewardRankKey, 0, 10, true);

//        exit;
        $userRankList = [];
        $myselfRankInfo = [];
        if (count($list) == 0) {
            $this->result['data'] = [];
        } else {
            $userIds = array_keys($list);
            if (array_key_exists($this->userInfo['userId'], $list) == false) {
                $myselfRank = $this->redisZSet->zRevRank($rewardRankKey, $this->userInfo['userId']);
                //无排名
                if ($myselfRank == false) {
                    
                } else {
                    $num = $this->redisZSet->score($rewardRankKey, $this->userInfo['userId']);
                    $myselfRankInfo['rank'] = $myselfRank;
                    $myselfRankInfo['num'] = $num;
                    $myselfRankInfo['userId'] = $this->userInfo['userId'];
                    $myselfRankInfo['headImgUrl'] = $this->userInfo['headImgUrl'];
                    $myselfRankInfo['nickname'] = $this->userInfo['nickName'];
                }
            }
//            print_r($userIds);exit;
            $userInfos = $this->db->select("id as userId,headImgUrl as headImgUrl,nickname")->from("user")->where_in('id', $userIds)->get()->result_array();

            foreach ($userInfos as $userInfo) {
                $newUserInfos[$userInfo['userId']] = $userInfo;
            }
            $i = 1;
            foreach ($list as $userId => $num) {
                $userInfo['userId'] = $userId;
                $userInfo['headImgUrl'] = $newUserInfos[$userId]['headImgUrl'];
                $userInfo['nickname'] = $newUserInfos[$userId]['nickname'];
                $userInfo['num'] = $num;
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
     * 获取邀请函信息
     */
    function inviteInfo()
    {
        $videoId=$_GET['videoId'];
        $fromUserId=$_GET['fromUserId'];
        $videoInfo=$this->db->select('*')->from('video')->where('id',$videoId)->get()->result()[0];	//获取视频
        $fromUserInfo = $this->redisHash->all(redisKey::USER_INFO_HASH_ID . $fromUserId);
        $this->result['data']['videoInfo']=$videoInfo;
        $this->result['data']['qrCodeUrl']=$this->config->item('authRedirectUrl','weixin').'/'.$videoId.'/'.$fromUserId;
        $this->result['data']['userInfo']['nickName']=$fromUserInfo['nickName'];
        $this->result['data']['userInfo']['headImgUrl']=$fromUserInfo['headImgUrl'];
        $this->jsonOutput();
    }

  

    

}
