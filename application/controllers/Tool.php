<?php

require APPPATH . 'vendor/php-sdk-7.2.1/autoload.php';

use Qiniu\Auth;
use Qiniu\Storage\UploadManager;

class Tool extends sczController {
    function getQiniuToken()
    {
//        $isLogin=parent::isLogin();
//        if($isLogin===false)
//        {
//            $this->jsonOutput();
//            return;
//        }
        $accessKey = 'Nvn2WQOsP8jUF8b7rXCaj9Td1V8yUrAZxZoL2X6c';
        $secretKey = 'WI6vG6ATtmvrMBVM9lkpAML9ulTyLGJEWIetzuz4';
        $auth = new Auth($accessKey, $secretKey);
        $bucket="resource";
//        $baseKey = '/$(year)/$(mon)/$(day)/' . md5(uniqid(microtime() . mt_rand(1, 100))) . '$(ext)';
//        $saveKey = 'pic'. $baseKey;
//        $upToken = $auth->uploadToken($bucket, $saveKey);
        $upToken = $auth->uploadToken($bucket);
        $this->result['data']['token']=$upToken;
        $this->jsonOutput();
    }
    
    /**
     * 二维码生成
     */
    public function qrCode() {
        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
        $allow_origin = array(
            'http://www.baidu.com',
            'http://localhost:8000',
            'http://hwsp.mzlicai.cn',
            'http://192.168.1.141:8000',
        );
        if (in_array($origin, $allow_origin)) {
            $this->output->set_header('Access-Control-Allow-Origin:' . $origin);
        }
        $this->output->set_header('Access-Control-Allow-Credentials:true');
        include APPPATH . 'libraries/phpqrcode/phpqrcode.php';
        $data = $_GET['data'];
        \QRcode::png($data, false, \QR_ECLEVEL_H, 6, 0);
    }

}
