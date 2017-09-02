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
            $this->load->model('redis/redisSet');
        }
        public function publicity()
        {
          
            $this->snsapiWeixin();
            $videoId=intval($this->uri->segment(3));
     
            $video=$this->db->select('*')->from('video')->where('id',$videoId)->get()->result()[0];	//获取视频
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
            $this->load->view('comment/publicity',$data);
        }
        public function getToken()
        {
             log_message('error', 'Some variable did not contain a value.');
              $dbUserInfo = $this->db->select('*')->from('user')->where('openId', 'o4GSVjm7AeAe-3_8HiXzqqSczA2g')->get()->result_array();
              var_dump($dbUserInfo);
            $userSessionData['a']=111;
            $userSessionData['b']=111;
            $this->redisHash->mset(redisKey::USER_INFO_HASH_ID,$userSessionData);
//            $tToken=$_GET['tToken'];
           print_r($this->redisSet->smembers('test'));exit;

        }
}