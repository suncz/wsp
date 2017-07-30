<?php
require APPPATH.'vendor/autoload.php';
class Login extends sczController{
    //授权地址  
    function index()
    { 
        $this->config->load('weixin');
        $wechatOauth=new Wechat\WechatOauth($this->config->item('weixin'));
        $callback='http://wsp.mzlicai.cn/login/code';
        $authorizeUrl=$wechatOauth->getOauthRedirect($callback, 'STATE','snsapi_userinfo');
        header("Location: $authorizeUrl");
        exit;
    }
    function code()
    {
        print_r($_REQUEST);
        $this->config->load('weixin');
        $wechatOauth=new Wechat\WechatOauth($this->config->item('weixin'));
        $accessToekenInfo=$wechatOauth->getOauthAccessToken();
        /**
         * Array
        (
            [access_token] => flh_06SWcX-L5y4MPWve2KON6M4TP7IhU-BOKrDXNr_mm_ludqZi1OAGSCYaFyljQuNKFunFiVAYKpMpWT3c5WG075GXp6FmvA5v6lnbiO8
            [expires_in] => 7200
            [refresh_token] => -Cd2OK2xEptWZfO9m-STzhGUxcqPSv6FGvhHrbxM9Jo3e4ZBPDqHJSJFAJ2PIfxAwEJn2E52AcSYC6raivHBPNBDdMmc5wDcYsHIpXc5V0E
            [openid] => o4GSVjm7AeAe-3_8HiXzqqSczA2g
            [scope] => snsapi_base
        )
        */
        var_dump($accessToekenInfo);
        //获取用户信息
        $userInfo=$wechatOauth->getOauthUserInfo($accessToekenInfo['access_token'], $accessToekenInfo['openid']);
        print_r($userInfo);
    }
}
