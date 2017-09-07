<?php

require_once (APPPATH . 'vendor/autoload.php');

class CommentNew extends SczController {

    public function __construct() {
        parent::__construct();
        $this->load->model('redis/redisString');
    }

    /**
     * 评论分页
     * @return type
     */
    public function commentList() {
        $vedioId = $_GET['vedioId'];
        $commentId = $_GET['commentId'];
        $type = $_GET['type'];
        if ($type == 'new') {
            if ($commentId == 0) {
                $array = array('videoId =' => $vedioId);
                $this->db->order_by('id', 'DESC');
            } else {
                $array = array('id >' => $commentId, 'videoId =' => $vedioId);
                $this->db->order_by('id', 'ASC');
            }
        } else if ($type == 'old') {
            $array = array('id <' => $commentId, 'videoId =' => $vedioId);
            $this->db->order_by('id', 'DESC');
        } else if ($type != 'new' || $type != 'old') {
            $this->result['ret'] = 1001;
            $this->result['msg'] = '参数错误';
            $this->jsonOutput();
            return;
        }
        $comentList = $this->db->select('*')->from('comment')->where($array)->limit(10)->get()->result_array();
        $this->result['data'] = $comentList;
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
