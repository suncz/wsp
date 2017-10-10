<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once (APPPATH . 'vendor/autoload.php');

class Pay extends SczController {
    
    //红包微信支付
    function wxHtml()
    {
        $redPacketId=$_GET['redPacketId'];
        $redPackeInfo = $this->db->from('redpacket')->where('id', $redPacketId)->where('payStatus', 2)->get()->row();
        if ($redPackeInfo != NULL) {
            $this->result['ret'] = 2008;
            $this->result['msg'] = "红包已经支付了";
        }
        $this->db->insert("");
        
    }
}