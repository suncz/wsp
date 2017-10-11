<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once (APPPATH . 'vendor/autoload.php');
require_once APPPATH."third_party/WxpayAPI/lib/WxPay.Api.php";
require_once APPPATH."third_party/WxpayAPI/example/WxPay.JsApiPay.php";
require_once APPPATH.'third_party/WxpayAPI/example/log.php';
class Pay extends SczController {

    //红包微信支付
    function wxHtml() {
        ini_set('date.timezone', 'Asia/Shanghai');
        $isLogin=parent::isLogin();
        if ($isLogin == false) {
            $this->jsonOutput();
        }
        $redPacketId = $_GET['redPacketId'];
        $redPackeInfo = $this->db->from('redPacket')->where('id', $redPacketId)->get()->row();
        if ($redPackeInfo->payStatus==2) {
            $this->result['ret'] = 2008;
            $this->result['msg'] = "";
        }
        $data = ['redPacketId' => $redPacketId, 'UserId' => $this->userInfo['userId'], 'way' => 1, 'createTime' => date('Y-m-d H:i:s')];
        $this->db->insert('pay', $data);
        $payId = $this->db->insert_id();

        //error_reporting(E_ERROR);
        
//初始化日志
        $logHandler = new CLogFileHandler("../logs/" . 'pay.'.date('Y-m-d') . '.log');
        $log = Log::Init($logHandler, 15);


        //echo 11;exit;
        //①、获取用户openid
        $tools = new JsApiPay();
        //$openId = $tools->GetOpenid();
        $openId = $this->userInfo['openId'];
        //②、统一下单
        $input = new WxPayUnifiedOrder();
        $input->SetBody("直播红包");
        $input->SetAttach("直播红包");
        $input->SetOut_trade_no($payId);
        $input->SetTotal_fee($redPackeInfo->money);
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 600));
        $input->SetGoods_tag("test");
        //$input->SetNotify_url("http://paysdk.weixin.qq.com/example/notify.php");
        $input->SetNotify_url("http://wsp.mzlicai.cn/WxpayAPI/example/notify.php");
        $input->SetTrade_type("JSAPI");
        $input->SetOpenid($openId);
        sleep(10);
        $order = WxPayApi::unifiedOrder($input);
        print_r($order);
        //printf_info($order);
        $jsApiParameters = $tools->GetJsApiParameters($order);
        print_r($jsApiParameters);
    }

}
