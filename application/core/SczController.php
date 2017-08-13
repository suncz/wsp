<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class SczController extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('session');
        $this->config->load('weixin');
    }

    public $result = ['ret' => 0, 'msg' => 'ok'];

    public function jsonOutput($isExit = true) {
        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

        $allow_origin = array(
            'http://a.m.com',
            'http://b.m.com',
            'http://c.m.com',
            'http://s.runningdreamer.com',
            'http://p.runningdreamer.com',
            'http://a.runningdreamer.com',
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
        if ($this->session->has_userdata('userInfo')) {
            return;
        }
        $videoId = intval($this->uri->segment(3));
        $wechatOauth = new Wechat\WechatOauth($this->config->item('weixin'));
        //code
        //静默授权的回调地址，获取到code
        if (isset($_GET['code']) && $_GET['state'] = 'base') {
            $accessToekenInfo = $wechatOauth->getOauthAccessToken();
            //获取用户信息
            $userInfo = $wechatOauth->getOauthUserInfo($accessToekenInfo['access_token'], $accessToekenInfo['openid']);
            //静默授权获取用户信息失败 采用主动授权
            if ($userInfo == FALSE) {
                $authorizeUrl = $wechatOauth->getOauthRedirect($this->config->item('authRedirectUrl','weixin') . '/' . $videoId, 'base', 'userInfo');
                header("Location: $authorizeUrl");
                exit;
            }
        }//主动授权获取用户信息
        else if (isset($_GET['code']) && $_GET['state'] = 'userInfo') {
            $accessToekenInfo = $wechatOauth->getOauthAccessToken();
            $userInfo = $this->db->select('*')->from('user')->where('openId', $accessToekenInfo['openid'])->get()->result(); //获取人员信息
            if (!empty($userInfo)) {
                $userSessionData = array(
                    'openId' => $userInfo['openid'],
                    'nickName' => $userInfo['nickname'],
                    'sex' => $userInfo['sex'],
                    'province' => $userInfo['province'],
                    'city' => $userInfo['city'],
                    'headimgurl' => $userInfo['headimgurl'],
                    'unionid' => isset($userInfo['unionid']) ? $userInfo['unionid'] : '',
                    'userId'=>$userInfo['id']
                );
            } else {
                $weixinUserInfo = $wechatOauth->getOauthUserInfo($accessToekenInfo['access_token'], $accessToekenInfo['openid']);
                $userSessionData = array(
                    'openId' => $weixinUserInfo['openid'],
                    'nickName' => $weixinUserInfo['nickname'],
                    'sex' => $weixinUserInfo['sex'],
                    'province' => $weixinUserInfo['province'],
                    'city' => $weixinUserInfo['city'],
                    'headimgurl' => $weixinUserInfo['headimgurl'],
                    'unionid' => isset($weixinUserInfo['unionid']) ? $weixinUserInfo['unionid'] : '',
                );

                $bool = $this->db->insert('user', $userSessionData);
                $userId = $this->db->insert_id();
                $userSessionData['userId']=$userId;
            }
            $this->session->set_userdata($userSessionData);
        }
        //静默授权，拼接授权地址（设置会调地址），并跳转
        else {
            $authorizeUrl = $wechatOauth->getOauthRedirect($this->config->item('authRedirectUrl','weixin'). '/' . $videoId, 'base', 'snsapi_base');
            header("Location: $authorizeUrl");
            exit;
        }
    }

    /**
     * 主动授权
     */
    public function snsapiUserInfo() {
        
    }

}
