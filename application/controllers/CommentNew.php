<?php

require_once (APPPATH . 'vendor/autoload.php');

class CommentNew extends SczController {

    public function __construct() {
        parent::__construct();
        $this->load->model('redis/redisString');
        $this->load->library("fn");
    }

    /**
     * 评论分页
     * @return type
     */
    public function commentList() {
        $isLogin = parent::isLogin();
        if ($isLogin == false) {
            $this->jsonOutput();
            return;
        }
        $videoId = $_GET['videoId'];
        $commentId = $_GET['commentId'];
        $timeReferencePoint = isset($_GET['timeReferencePointLine'])?$_GET['timeReferencePointLine']:time();//时间参照点
        $type = $_GET['type'];
        if ($type == 'new') {
            if ($commentId == 0) {
                $array = array('videoId =' => $videoId);
                $this->db->order_by('id', 'DESC');
            } else {
                $array = array('id >' => $commentId, 'videoId =' => $videoId);
                $this->db->order_by('id', 'ASC');
            }
        } else if ($type == 'old') {
            $array = array('id <' => $commentId, 'videoId =' => $videoId);
            $this->db->order_by('id', 'DESC');
        } else if ($type != 'new' || $type != 'old') {
            $this->result['ret'] = 1001;
            $this->result['msg'] = '参数错误';
            $this->jsonOutput();
            return;
        }
        $comentList = $this->db->select('*')->from('comment')->where($array)->limit(10)->get()->result_array();

        $i=0;
        $commentListNew=[];
        foreach($comentList as $key =>$value)
        { 
            $value['getRedPacketMoney']=$value['getRedPacketMoney']/100;
            $createTime=strtotime($value['createTime']);
            $commentListNew[$i]=$value;
            $i++;
//            echo $createTime."<br />";
            //在五分钟之内
            if(abs($createTime-$timeReferencePoint)<5*60)
            {
                 
            }
            else
            {
                $timeReferencePoint=$createTime;
                $commentListNew[$i]['content']=$this->fn->getTimeFormat($createTime);
                $commentListNew[$i]['timeReferencePointLine']=$createTime;
                $commentListNew[$i]['type']=10;  
                $i++;
            }
        } 
        $this->result['data'] = $commentListNew;
        $this->jsonOutput();
    }

    public function add() {
        $isLogin = parent::isLogin();
        if ($isLogin == false) {
            $this->jsonOutput();
            return;
        }
//        print_r($this->userInfo);exit;
        $content = $_POST['content'];
        if(mb_strlen($content)>500)
        {
            $this->result['ret']=3001;
            $this->result['msg']='评论字数过多';
            $this->jsonOutput();
            return ;
        }
        $pic = $_POST['pic'];
        $videoId = $_POST['videoId'];
        $data = array(
            'videoId'=>$videoId,
            'userId'=>$this->userInfo['userId'],
            'userNickName'=>$this->userInfo['nickName'],
            'userHeadImgUrl'=>$this->userInfo['headimgurl'],
            'pic' => $pic,
            'content' => $content,
            'createTime'=> date('Y-m-d H:i:s',time()),
            'type'=> strlen($pic)>1?2:1
        );
        
        $this->db->insert('comment', $data); 
        $this->jsonOutput();
    }

}
