<?php
/**
     * 二维码生成
     */
    
        $data="http://www.baidu.com";
        $size=6;
        include  'phpqrcode/phpqrcode.php';
        \QRcode::png($data,false,\QR_ECLEVEL_H,$size,0);
   