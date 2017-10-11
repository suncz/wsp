<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once (APPPATH . 'vendor/autoload.php');
require_once APPPATH . "third_party/WxpayAPI/lib/WxPay.Api.php";
require_once APPPATH . "third_party/WxpayAPI/example/WxPay.JsApiPay.php";
require_once APPPATH . 'third_party/WxpayAPI/example/log.php';

class Pay extends SczController {

    //红包微信支付 微信公众号支付
    function wxHtml() {
        $this->load->library("fn");
        ini_set('date.timezone', 'Asia/Shanghai');
        $isLogin = parent::isLogin();
        if ($isLogin == false) {
            $this->jsonOutput();
            return;
        }
        $redPacketId = $_GET['redPacketId'];
        $redPackeInfo = $this->db->from('redPacket')->where('id', $redPacketId)->get()->row();
        if ($redPackeInfo->payStatus == 2) {
            $this->result['ret'] = 2008;
            $this->result['msg'] = "红包已支付";
            $this->jsonOutput();
            return;
        }
        $paySn = Fn::getSn(time());

        $data = ['redPacketId' => $redPacketId, 'UserId' => $this->userInfo['userId'], 'way' => 1, 'createTime' => date('Y-m-d H:i:s'), 'paySn' => $paySn];
        $this->db->insert('pay', $data);
//        $this->db->insert_id();
        //error_reporting(E_ERROR);
//初始化日志
        $logHandler = new CLogFileHandler(APPPATH . "logs/" . 'pay.' . date('Y-m-d') . '.log');
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
        $input->SetOut_trade_no($paySn);
        $input->SetTotal_fee($redPackeInfo->money);
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 600));
        $input->SetGoods_tag("test");
        //$input->SetNotify_url("http://paysdk.weixin.qq.com/example/notify.php");
        $input->SetNotify_url("http://wsp.mzlicai.cn/WxpayAPI/example/notify.php");
        $input->SetTrade_type("JSAPI");
        $input->SetOpenid($openId);
        //sleep(10);:
        $order = WxPayApi::unifiedOrder($input);
//        print_r($order);
        //printf_info($order);
        $jsApiParameters = $tools->GetJsApiParameters($order);
        $this->result['data'] = json_decode($jsApiParameters, true);
        $this->jsonOutput();
    }

    /**
     * 微信支付回调通知
     */
    function weChatNotice() {
        $resultXML = file_get_contents("php://input");
        $arrResult = json_decode(json_encode(simplexml_load_string($resultXML, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        //如果返回正确
        try {
            if (array_key_exists("return_code", $arrResult) && array_key_exists("result_code", $arrResult) && $arrResult["return_code"] == "SUCCESS" && $arrResult["result_code"] == "SUCCESS") {
                $sourceSign = $arrResult['sign'];
                unset($arrResult['sign']);
                if ($sourceSign == $this->md5Sign($arrResult)) {
                    $result['return_code'] = "SUCCESS";
                    $result['return_msg'] = "OK";

                    $out_trade_no = $arrResult['out_trade_no'];
                    $transaction_id = $arrResult['transaction_id'];
                    //更新数据
                    $payInfo = $this->db->select('*')->from('pay')->where('paySn', $out_trade_no)->get()->row();
                    if ($payInfo == NULL) {
                        throw new Exception("支付信息不存在:" . $out_trade_no, 3002);
                    } else {
                        //支付表
                        $payUpdata = ['PayTime' => date("Y-m-d H:i:s"), 'transactionId' => $transaction_id, 'bankType' => $arrResult['bank_type']];
                        $this->db->where('paySn', $out_trade_no)->update('pay', $payUpdata);
                        //红包表
                        $redPacketUpdata=['payId'=>$payInfo->id,'payStatus'=>2];
                        $this->db->where('id',$payInfo->redPacketId)->update('redPacket',$redPacketUpdata);
                        //聊天记录多一个红包记录
                        $payInfo = $this->db->select('*')->from('comment')->where('paySn', $out_trade_no)->get()->row();
                    }
                } else {
                    throw new Exception("签名失败:", 3003);
                }
            } else {
                $result['return_code'] = "FAIL";
                $result['return_msg'] = "OK";
            }
        } catch (Exception $exc) {
            $result['return_code'] = "FAIL";
            $result['return_msg'] = "OK";
        }


        echo $this->ToXml($result);
    }

    /**
     * 签名
     * @return type
     */
    function md5Sign($arr) {
        //签名步骤一：按字典序排序参数
        ksort($arr);
        $string = $this->ToUrlParams($arr);
        //签名步骤二：在string后加入KEY
        $string = $string . "&key=" . WxPayConfig::KEY;
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);
        return $result;
    }

    /**
     * 格式化参数格式化成url参数
     */
    public function ToUrlParams($arr) {
        $buff = "";
        foreach ($arr as $k => $v) {
            if ($k != "sign" && $v != "" && !is_array($v)) {
                $buff .= $k . "=" . $v . "&";
            }
        }

        $buff = trim($buff, "&");
        return $buff;
    }

    /**
     * 输出xml字符
     * @throws WxPayException
     * */
    public function ToXml($arr) {
        if (!is_array($arr) || count($arr) <= 0) {
            throw new WxPayException("数组数据异常！");
        }

        $xml = "<xml>";
        foreach ($arr as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
            }
        }
        $xml .= "</xml>";
        return $xml;
    }

}
