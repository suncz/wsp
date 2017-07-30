<?php
require APPPATH.'vendor/autoload.php';
class Login extends sczController{
    //授权地址  
    function index()
    { 
        $this->config->load('weixin');
        $wechatOauth=new Wechat\WechatOauth($this->config->item('weixin'));
        $callback='http://wsp.mzlicai.cn/login/code';
        $authorizeUrl=$wechatOauth->getOauthRedirect($callback, 'STATE');
        header("Location: $authorizeUrl");
        exit;
    }
    function code()
    {
        print_r($_REQUEST);
    }
}
