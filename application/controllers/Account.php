<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Account extends SczController {
    public $limitWithdrawalsMoney=100;
    //提现申请
    function withdrawalsApply()
    {
        parent::isLogin();
        $money=$_POST['money'];
        $userInfo=$this->db->select('*')->from('user')->where('id',$this->userInfo['userId'])->get()->row();
        $withdrawalsLog=$this->db->select('*')->from('withdrawalsLog')->where('userId',$this->userInfo['userId'])->where('status',0)->get()->row();
        if($withdrawalsLog!=NULL)
        {
            $this->result['ret']=2050;
            $this->result['msg']="提现申请失败，您已有一笔提现正在处理中";
            $this->jsonOutput();
            return;
        }
        if($money>$userInfo->account)
        {
            $this->result['ret']=2051;
            $this->result['msg']="您没有这么多余额可提现";
            $this->jsonOutput();
            return;
        }
        if($money<$this->limitWithdrawalsMoney)
        {
            $this->result['ret']=2051;
            $this->result['msg']="提现金额必须大于".($this->withdrawalsApply/100)."元";
            $this->jsonOutput();
            return;
        }
        if($userInfo->account<$this->limitWithdrawalsMoney)
        {
            $this->result['ret']=2052;
            $this->result['msg']="提现金额必须大于".($this->withdrawalsApply/100)."元";
            $this->jsonOutput();
            return;
        }
        $insertData=['userId'=>$this->userInfo['userId'],'money'=>$money,'applyTime'=> date("Y-m-d H:i:s"),'status'=>0];
        $this->db->insert('withdrawalsLog',$insertData);
        $this->result['ret']=0;
        $this->result['msg']="提现申请成功";
        $this->jsonOutput(); 
    }
    function userMoney()
    {
        $userInfo=$this->db->select('*')->from('user')->where('id',$this->userInfo['userId'])->get()->row();
        $money=$userInfo->account;
        $this->result['data']['money']=$money;
        $this->result['data']['limitWithdrawalsMoney']=$this->limitWithdrawalsMoney;
        $this->jsonOutput();
    }
}