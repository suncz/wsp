<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 
 
/**
* Weixin_oauth 类库，主要是远程抓取页面，默认http请求，也可以使用https请求，
* 可以在初始化的时候通过传入可选参数https为true，设置为https请求
*/
class Wx_oauth
{
        const TIMEOUT = 5;                        // 设置超时时间
 
        private $ci;                                // CI对象
        private $ch;                                // curl对象
        
        function __construct()
        {
                $this->ci =& get_instance();
                $this->ci->config->load('wx_oauth');                // 载入配置文件
        }
 
        /**
         * 验证配置接口信息
         * @param array 从微信接口发送来的信息，通过$_GET获得
         */
        public function interface_valid($get_request)
        {
                $signature = $get_request['signature'];
                $timestamp = $get_request['timestamp'];
                $nonce = $get_request['nonce'];        
 
                $token = $this->ci->config->item('token');
                $tmpArr = array($token, $timestamp, $nonce);
                sort($tmpArr);
                $tmpStr = implode( $tmpArr );
                $tmpStr = sha1( $tmpStr );
 
                if( $tmpStr == $signature ){
                        echo $get_request['echostr'];
                        exit;
                }
        }
 
        /**
         * 生成用户授权的地址
         * @param string 自定义需要保持的信息
         * @param bool 是否是通过公众平台方式认真
         */
        public function authorize_addr($state='', $mp=false)
        {
                if ($mp) {
                        $data = array(
                                'appid'=>$this->ci->config->item('appid'),
                                'secret'=>$this->ci->config->item('secret'),
                                'grant_type'=>'client_credential');
                        $url = $this->ci->config->item('mp_authorize_url');
                } else {
                        $data = array(
                                'appid'=>$this->ci->config->item('appid'),
                                'redirect_uri'=>urlencode($this->ci->config->item('redirect_uri')),
                                'response_type'=>'code',
                                'scope'=>$this->ci->config->item('scope'),
                                'state'=>$state,
                                '#wechat_redirect'=>'');
                        $url = $this->ci->config->item('authorize_url');
                }
                
                return $url . $this->create_url($data);
        }
 
        /**
         * 获取 access token
         * @param string 用于换取access token的code，微信提供
         */
        public function access_token($code)
        {
// 			echo '<br>1.获取 access token;';
// 			echo '<br>[appid]='.$this->ci->config->item('appid');
// 			echo '<br>[secret]='.$this->ci->config->item('secret');
// 			echo '<br>[code]='.$code;
            $data = array(
				'appid'=>$this->ci->config->item('appid'),
				'secret'=>$this->ci->config->item('secret'),
           		'code'=>$code,
            	'grant_type'=>'authorization_code'
            );
			// 生成授权url
			$url = $this->ci->config->item('access_token_url');
// 			echo '<br>[URL]='.$url;
			return $this->send_request($url, $data);
        }
 
        /**
         * 刷新 access token
         * @param string 用于刷新的token
         */
        public function refresh_token($refresh_token)
        {
			echo '2.刷新 access token;';
                $data = array(
                        'appid'=>$this->ci->config->item('appid'),
                        'refresh_token'=>$refresh_token,
                        'grant_type'=>'refresh_token');
                // 生成授权url
                $url = $this->ci->config->item('refresh_token_url');
                return $this->send_request($url, $data);
        }
 
        /**
         * 获取用户信息
         * @param string access token
         * @param string 用户的open id
         */
        public function userinfo($token, $openid)
        {
// 			echo '3.获取用户信息;';
                $data = array(
                        'access_token'=>$token,
                        'openid'=>$openid,
                        'lang'=>$this->ci->config->item('lang'));
                // 生成授权url
                $url = $this->ci->config->item('userinfo_url');
                return $this->send_request($url, $data);
        }
 
        /**
         * 检验access token 是否有效
         * @param string access token
         * @param string 用户的open id
         */
        public function valid($token, $openid)
        {
			echo '4.检验access token 是否有效;';
                $data = array(
                        'access_token'=>$token,
                        'openid'=>$openid);
                // 生成授权url
                $url = $this->ci->config->item('valid_token_url');
                return $this->send_request($url, $data);
        }
 
        /**
         * 发送curl请求，并获取请求结果
         * @param string 请求地址
         * @param array 如果是post请求则需要传入请求参数
         * @param string 请求方法，get 或者 post， 默认为get
         * @param bool 是否以https协议请求
         */
        private function send_request($request, $params, $method='get', $https=true)
        {
                // 以get方式提交
                if ($method == 'get') {
                        $request = $request . $this->create_url($params);
                }
// 				echo '<br>[request]='.$request;
                $this->ch = curl_init($request);
                curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);// 设置不显示结果，储存入变量
                curl_setopt($this->ch, CURLOPT_TIMEOUT, self::TIMEOUT); // 设置超时限制防止死循环
 
                // 判断是否以https方式访问
                if ($https) {
                        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
                }
 
                if ($method == 'post') {        // 以post方式提交
                        curl_setopt($this->ch, CURLOPT_POST, 1); // 发送一个常规的Post请求
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, $params); // Post提交的数据包
                }
                
                $tmpInfo = curl_exec($this->ch); // 执行操作
                if (curl_errno($this->ch)) {
                        echo 'Errno:'.curl_error($this->ch);//捕抓异常
                }
                curl_close($this->ch); // 关闭CURL会话
 
                return $tmpInfo; // 返回数据
        }
 
        /**
         * 生成url
         */
        private function create_url($data)
        {
                $temp = '?';
                foreach ($data as $key => $item) {
                        $temp = $temp . $key . '=' . $item . '&';
                }
                return substr($temp, 0, -1);
        }
}
