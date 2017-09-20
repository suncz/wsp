<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class SczController extends CI_Controller {

    static public $tokenExpire = 86400 * 7;
    public $userInfo;

    public function __construct() {
        parent::__construct();
        $this->load->library('session');
        $this->config->load('weixin');
        $this->load->model('redis/redisHash');
        $this->load->model('redis/redisString');
        $_REQUEST= array_merge($_GET,$_POST,$_REQUEST);
    }

    public $result = ['ret' => 0, 'msg' => 'ok'];

    public function jsonOutput($isExit = true) {
        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
        $allow_origin = array(
            'http://www.baidu.com',
            'http://localhost:8000'
        );
        if (in_array($origin, $allow_origin)) {
            $this->output->set_header('Access-Control-Allow-Origin:' . $origin);
        }
        $this->output->set_header('Access-Control-Allow-Credentials:true');
        $this->output->set_content_type('json')->set_output(json_encode($this->result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE))->_display();
        if ($isExit) {
            exit;
        }
    }

    /**
     * 静默授权
     */
    public function snsapiWeixin() {
        log_message('error', 'session Id is:'.session_id());
        $userId = $this->redisHash->get(redisKey::USER_SESSION_ID_HASH, session_id());
        if ($userId) {
            log_message('error', '从redis中查找到了userId');
            $this->userInfo = $this->redisHash->all(redisKey::USER_INFO_HASH_ID . $userId);
//            var_dump($this->userInfo);exit;
            log_message('error', print_r($this->userInfo,TRUE));
            if (time()-$this->userInfo['tokenExpire'] > self::$tokenExpire) {
                log_message('error', 'redis的用户信息'.print_r($this->userInfo,TRUE));
                return;
            }
        }
        $videoId = intval($this->uri->segment(3));
        $wechatOauth = new Wechat\WechatOauth($this->config->item('weixin'));
        //code
        //静默授权的回调地址，获取到code
        if (isset($_GET['code']) && $_GET['state'] = 'base') {
            log_message('error', "静默授权开始");
            $accessToekenInfo = $wechatOauth->getOauthAccessToken();
            //获取用户信息
            $weixinUserInfo = $wechatOauth->getOauthUserInfo($accessToekenInfo['access_token'], $accessToekenInfo['openid']);
            log_message('error', print_r($weixinUserInfo,TRUE));
            //静默授权获取用户信息失败 采用主动授权
            if ($weixinUserInfo == FALSE) {
                log_message('error', '静默授权没有获取到微信的基本信息，改为主动授权');
                $authorizeUrl = $wechatOauth->getOauthRedirect($this->config->item('authRedirectUrl', 'weixin') . '/' . $videoId, 'base', 'snsapi_userinfo');
                header("Location: $authorizeUrl");
                exit;
            } else {
                log_message('error', '静默授权获取到微信的基本信息');
                $dbUserInfo = $this->db->select('*')->from('user')->where('openId', $accessToekenInfo['openid'])->get()->result_array(); //获取人员信息
                log_message('error', print_r($dbUserInfo,true));
                log_message('error', $this->db->last_query());
                if (!empty($dbUserInfo)) {
                    $dbUserInfo=$dbUserInfo[0];
                    log_message('error', "从数据库获取到用户信息");
                    $userInfo = array(
                        'openId' => $dbUserInfo['openId'],
                        'nickName' => $dbUserInfo['nickName'],
                        'sex' => $dbUserInfo['sex'],
                        'province' => $dbUserInfo['province'],
                        'city' => $dbUserInfo['city'],
                        'headimgurl' => $dbUserInfo['headimgurl'],
                        'unionid' => $dbUserInfo['unionid'],
                        'userId' => $dbUserInfo['id']
                    );
                    log_message('error', print_r($userInfo,TRUE));
                    $userId = $dbUserInfo['id'];
                } else {
                    $weixinUserInfo = $wechatOauth->getOauthUserInfo($accessToekenInfo['access_token'], $accessToekenInfo['openid']);
                    $userInfo = array(
                        'openId' => $weixinUserInfo['openid'],
                        'nickName' => $weixinUserInfo['nickname'],
                        'sex' => $weixinUserInfo['sex'],
                        'province' => $weixinUserInfo['province'],
                        'city' => $weixinUserInfo['city'],
                        'headimgurl' => $weixinUserInfo['headimgurl'],
                        'unionid' => isset($weixinUserInfo['unionid']) ? $weixinUserInfo['unionid'] : '',
                    );
                    $this->db->insert('user', $userInfo);
                    $userId = $this->db->insert_id();
                    $userInfo['userId'] = $userId;
                }
                $userInfo['token'] = md5($userId . time() . rand(1, 1000));
                $userInfo['tokenExpire'] = self::$tokenExpire;
                log_message('error', '开始插入redis，userId is'.$userId);
                log_message('error', '开始插入redis，userInfo is '.print_r($userInfo,TRUE));
                $this->redisHash->mset(redisKey::USER_INFO_HASH_ID . $userId, $userInfo);
                $this->redisHash->set(redisKey::USER_SESSION_ID_HASH, session_id(), $userId);
//                $this->redisString->setex(RedisKey::USER_TOKEN_STRING.$userInfo['token'], json_encode($userInfo),86400*7);
                $this->userInfo=$userInfo;
            }
        }//主动授权获取用户信息
        else if (isset($_GET['code']) && $_GET['state'] = 'userInfo') {
            $accessToekenInfo = $wechatOauth->getOauthAccessToken();
            $dbUserInfo = $this->db->select('*')->from('user')->where('openId', $accessToekenInfo['openid'])->get()->result_array(); //获取人员信息
            if (!empty($userInfo)) {
                $dbUserInfo=$dbUserInfo[0];
                $userInfo = array(
                    'openId' => $userInfo['openId'],
                    'nickName' => $userInfo['nickName'],
                    'sex' => $userInfo['sex'],
                    'province' => $userInfo['province'],
                    'city' => $userInfo['city'],
                    'headimgurl' => $userInfo['headimgurl'],
                    'unionid' => isset($userInfo['unionid']) ? $userInfo['unionid'] : '',
                    'userId' => $userInfo['id']
                );
                $userId = $userInfo['id'];
            } else {
                $weixinUserInfo = $wechatOauth->getOauthUserInfo($accessToekenInfo['access_token'], $accessToekenInfo['openid']);
                $userInfo = array(
                    'openId' => $weixinUserInfo['openid'],
                    'nickName' => $weixinUserInfo['nickname'],
                    'sex' => $weixinUserInfo['sex'],
                    'province' => $weixinUserInfo['province'],
                    'city' => $weixinUserInfo['city'],
                    'headimgurl' => $weixinUserInfo['headimgurl'],
                    'unionid' => isset($weixinUserInfo['unionid']) ? $weixinUserInfo['unionid'] : '',
                );

                $this->db->insert('user', $userInfo);
                $userId = $this->db->insert_id();
                $userInfo['userId'] = $userId;
            }
            $userInfo['token'] = md5($userId . time() . rand(1, 1000));
            $userInfo['tokenExpire'] = self::$tokenExpire;
            $this->redisHash->mset(redisKey::USER_INFO_HASH_ID . $userId, $userInfo);
            $this->redisHash->set(redisKey::USER_SESSION_ID_HASH, session_id(), $userId);
//            $this->redisString->setex(RedisKey::USER_TOKEN_STRING.$userInfo['token'], json_encode($userInfo),self::$tokenExpire);
            $this->userInfo=$userInfo;
        }
        //静默授权，拼接授权地址（设置会调地址），并跳转
        else {
            $authorizeUrl = $wechatOauth->getOauthRedirect($this->config->item('authRedirectUrl', 'weixin') . '/' . $videoId, 'base', 'snsapi_base');
            header("Location: $authorizeUrl");
            exit;
        }
    }
    /**
     * 获取token
     */
    public function isLogin()
    { 
        if(!isset($_REQUEST['token'])||!isset($_REQUEST['userId']))
        {
            
            $this->result['ret']=1001;
            $this->result['msg']="参数错误";
            return false;
        }
         $this->userInfo = $this->redisHash->all(redisKey::USER_INFO_HASH_ID . $_REQUEST['userId']);
        if(!empty( $this->userInfo)&& $this->userInfo['token']==$_REQUEST['token'])
        {
            return TRUE;
        }
        else
        {
            $this->result['ret']=1000;
            $this->result['msg']="登录失效,请重新登录";
            return false;
        } 

    }

}
