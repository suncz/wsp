<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once (APPPATH.'vendor/autoload.php');
class Home extends SczController{
        public function __construct() {
            parent::__construct();
            $this->load->model('redis/redisString');
            $this->load->model('redis/redisZSet');
        }
        public function publicity()
        {
            $this->snsapiWeixin();
            if(isset($_GET['code']))
            {
                $home=parse_url($_SERVER['REQUEST_URI'])['path'];
                header("Location: $home");
            }
            $videoId=intval($this->uri->segment(3));
            $fromUserId=intval($this->uri->segment(4));
            //视频邀请列表
            $inviteUser=$this->db->from('inviteUser')->where('videoId',$videoId)->where('userId', $this->userInfo['userId'])->get()->row_array();
            if($inviteUser==NULL)
            {
                $insertInviteUser['userId']=$this->userInfo['userId'];
                $insertInviteUser['fromUserId']=$fromUserId;
                $insertInviteUser['videoId']=$videoId;
                $insertInviteUser['isRegister']=$this->userInfo['videoId']==$videoId?1:0;
                $this->db->insert('inviteUser',$insertInviteUser);
                if($fromUserId)
                {
                    $this->redisZSet->zincrBy(RedisKey::INVITE_RANK_VIDEOID.$videoId, $fromUserId);
                }
                
            }
            $video=$this->db->select('*')->from('video')->where('id',$videoId)->get()->result();	//获取视频
            if(count($video)==0)
            {
                $this->result['ret']='1001';
                $this->result['msg']='参数错误';
                $this->jsonOutput();
                return;
            }
            $video=$video[0];
            $wechatScript = new \Wechat\WechatScript($this->config->item('wx'));
            $url= strtolower('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
//            print_r($video);
            $data['jsSign']=$wechatScript->getJsSign($url);
            $data['videoId']=$videoId;
            $data['share']['shareTitle']=$video->shareTitle;
            $data['share']['shareContent']=$video->shareContent;
            $data['share']['shareIcon']=$video->shareIcon;
            $data['share']['shareLink']='http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
            $data['publicityCover']=$video->publicityCover;
            $data['playUrl']='http://'.$_SERVER['HTTP_HOST']."/comment/index/".$videoId;
            $data['playUrl']='http://hwsp.mzlicai.cn?uid='.$this->userInfo['userId'].'&token='.$this->userInfo['token'].'&videoId='.$videoId;
            $this->load->view('comment/publicity',$data);
        }
        public function testToken()
        {
            print_r($_SERVER);exit;
            $isLogin=parent::isLogin();
            var_dump($isLogin);
        }
        
}
