<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once (APPPATH . 'vendor/autoload.php');

class Vedio extends SczController {

    public function __construct() {
        parent::__construct();
        $this->load->model('redis/redisString');
    }

    public function detail() {
        $vedioId = $_GET['vedioId'];
//        $isLogin = parent::isLogin();
//        if ($isLogin == false) {
//            $this->jsonOutput();
//            return;
//        }
        $vedio = $this->db->select('*')->from('video')->where('id', $vedioId)->get()->result_array()[0]; //获取视频 
        $cooperation = $this->db->select('*')->from('config')->where('key', 'cooperation')->get()->result()[0];
        $wechatScript = new \Wechat\WechatScript($this->config->item('wx'));
        $url = strtolower('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        $data['vedioInfo'] = $vedio;
        $data['cooperation'] = !empty($cooperation->value) ? $cooperation->value : '暂无信息';
        $data['jsSign'] = $wechatScript->getJsSign($url);
        $data['vedioInfo']['shareLink'] = 'http://' . $_SERVER['HTTP_HOST'] . "/comment/publicity/" . $vedioId;
        $this->result['data'] = $data;
        $this->jsonOutput();
    }
    
    public function like()
    {
        $videoId=intval($this->input->post('videoId'));
        $sql="update video set praiseNum = praiseNum + 1 WHERE id = $videoId";
        $bool=$this->db->query($sql);
        if($bool){
                $video=$this->db->select('*')->from('video')->where('id',$videoId)->get()->result()[0];	//获取视频
                $this->result['data']['praiseNum'] = $video->praiseNum;
        }else{
                 $this->result['data']['praiseNum'] = 0;
        }
        $this->jsonOutput();
    }

  

    

}
