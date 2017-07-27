<?php
require_once (APPPATH.'vendor/autoload.php');
class Comment extends CI_Controller{

	private $videoId;
	private $userId;
	private $content;

	//初始化加载
	public function index($state=''){
            if(isset($_GET['state']))
            {
                $isCallback=200;
            }
            else
            {
                $isCallback=0;
            }
            if(isset($_SESSION['userId'])&&$_SESSION['userId']>0)
            {
                $this->userId=$_SESSION['userId'];
            }
		$videoId=intval($this->uri->segment(3));
		if($videoId==0){
			if($state!=''){
                            $videoId=$state;
				
			}else{
				$videoId=1;
			}
		}

		$video=$this->db->select('*')->from('video')->where('id',$videoId)->get()->result()[0];	//获取视频
		$addr = $this->wx_oauth->authorize_addr($video->id);//获取微信登录地址
             
		$data=array(
				'video'=>$video,
				'codeUrl'=>$addr,
				'commentCounts'=>$this->getMsgCount($videoId),
				'userId'=>$this->userId
		);
                 $wechatScript = new \Wechat\WechatScript($this->config->item('wx'));
                $url= strtolower('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
    //            print_r($video);
                $data['jsSign']=$wechatScript->getJsSign($url);
                $data['videoId']=$videoId;
                $data['isCallback']=$isCallback;
                $data['share']['shareTitle']=$video->shareTitle;
                $data['share']['shareContent']=$video->shareContent;
                $data['share']['shareIcon']=$video->shareIcon;
                $data['share']['shareLink']='http://'.$_SERVER['HTTP_HOST']."/comment/publicity/".$videoId;
                $data['publicityCover']=$video->publicityCover;
                $data['playUrl']='http://'.$_SERVER['HTTP_HOST']."/comment/index/".$videoId;
		$this-> load -> view('comment/index',$data);
	}

	//微信回调函数
	public function callback()
	{
		$code = $_GET['code'];
		$state = $_GET['state'];
		$response = $this->wx_oauth->access_token($code);
		//echo '<br>'.$response;
		$r = json_decode($response);
		//echo '<br>[access_token]='.$r->access_token.'<br>[openid]='.$r->openid;
		$res = $this->userInfo($r->access_token, $r->openid);
		$this->userId=$this->addUser($res);
                $_SESSION['userId']=$this->userId;
//                header("Location : http://".$_SERVER['HTTP_HOST']."/comment/index/".$state);
		$this-> index($state);
	}

	//获取微信用户信息
	public function userInfo($access_token, $openid)
	{
		header("Content-type: text/html; charset=utf-8");
		$response = $this->wx_oauth->userinfo($access_token, $openid);
		$res = json_decode($response);
		//echo '<br>openid:'.$res->openid;
		//echo '<br>nickname:'.$res->nickname;
		//echo '<br>headimgurl:'.$res->headimgurl;
		return $res;
	}

	//新增用户
	public function addUser($user){
		$result=$this->db->select('*')->from('user')->where('openId',$user->openid)->get()->result();//获取人员信息
		$u = null;
		foreach ($result as $v)
		{
			$u = $v;
		}
		if($u==null){
			$data = array(
					'openId'=>$user->openid,
					'nickName'=>$user->nickname,
					'headImgUrl'=>$user->headimgurl
			);
			$bool = $this->db->insert('user',$data);
			if($bool){
				return $this->db->insert_id();
			}
		}else{
			return $u->id;
		}
		return null;
	}

	//获取评论
	public function getComments(){
		$videoId=intval($this->input->post('videoId'));
		$page_size=intval($this->input->post('len'));
		$offset=intval($this->input->post('start'));
		//获取评论
		if($page_size==0){
			$page_size=5;
		}
		$sql="select * from comment c,user u where c.userId=u.id and videoId=$videoId order by c.id desc limit $offset,$page_size";
		$comments=$this->db->query($sql)->result();
		echo json_encode($comments);
	}
	
	//获取评论数量
	public function getMsgCount($videoId){
		$sql="select * from comment where videoId=$videoId";
		$comments=$this->db->query($sql)->result();
		return count($comments);
	}

	//发布评论
	public function publishComment(){
		$this->videoId=intval($this->input->post('videoId'));//视频ID
		if($this->videoId==0){
			$this->videoId=1;
		}
		$this->userId=$this->input->post('userId');//用户ID
		$this->content=$this->input->post('content');//评论内容

		$createTime=date("Y-m-d H:i:s");
		$data = array(
				'videoId'=>$this->videoId,
				'userId'=>$this->userId,
				'content'=>$this->content,
				'createTime'=>$createTime
		);
		$bool = $this->db->insert('comment',$data);
		if($bool){
			echo '[{"msgNum":'.$this->getMsgCount($this->videoId).'}]';
		}else{
			echo json_encode($bool);		
		}
	}

	//浏览次数
	public function pvNum(){
		$videoId=intval($this->input->post('videoId'));
		$sql="update video set pvNum = pvNum + 1 WHERE id = $videoId";
		$bool=$this->db->query($sql);
		if($bool){
			$video=$this->db->select('*')->from('video')->where('id',$videoId)->get()->result()[0];	//获取视频
			echo '[{"pvNum":'.$video->pvNum.'}]';
		}else{
			echo $bool;
		}
	}

	//点赞
	public function praise(){
		$videoId=intval($this->input->post('videoId'));
		$sql="update video set praiseNum = praiseNum + 1 WHERE id = $videoId";
		$bool=$this->db->query($sql);
		if($bool){
			$video=$this->db->select('*')->from('video')->where('id',$videoId)->get()->result()[0];	//获取视频
			echo '[{"praiseNum":'.$video->praiseNum.'}]';
		}else{
			echo $bool;
		}
	}
        
        public function publicity()
        {
            $videoId=intval($this->uri->segment(3));
            
            $video=$this->db->select('*')->from('video')->where('id',$videoId)->get()->result()[0];	//获取视频
           
            $wechatScript = new \Wechat\WechatScript($this->config->item('wx'));
            $url= strtolower('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
//            print_r($video);
            $data['jsSign']=$wechatScript->getJsSign($url);
            $data['videoId']=$videoId;
            $data['share']['shareTitle']=$video->shareTitle;
            $data['share']['shareContent']=$video->shareContent;
            $data['share']['shareIcon']=$video->shareIcon;
            $data['share']['shareLink']='http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
            $data['publicityCover']=$video->publicityCover;
            $data['playUrl']='http://'.$_SERVER['HTTP_HOST']."/comment/index/".$videoId;
            $this->load->view('comment/publicity',$data);
        }
}
