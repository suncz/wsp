<html>

<!--template compile at 2017-02-03 21:55:55-->

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width,user-scalable=no,initial-scale=1,maximum-scale=1,minimum-scale=1">
    <meta name="format-detection" content="telephone=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <link rel="stylesheet" type="text/css" href="/static/css/index.css"></link>
    <script type="text/javascript" src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
    <script type="text/javascript" src="/static/js/jquery-1.1.1.min.js"></script>
    <title><?php echo $share['shareTitle'];?></title>
</head>
<style>
body,html{
    width:100%;
    height: 100%;
    padding: 0px;
    margin: 0px;
    overflow: hidden;
}
</style>
<body>
<script type="text/javascript">
wx.config({
    debug: false,
    appId:  "<?php echo $jsSign['appId']; ?>",
    timestamp: <?php echo $jsSign['timestamp']; ?>,
    nonceStr: "<?php echo $jsSign['nonceStr']; ?>",
    signature: "<?php echo $jsSign['signature']; ?>",
    url:"<?php echo $jsSign['url']; ?>",
    jsApiList: [ "onMenuShareTimeline", "onMenuShareAppMessage", "addCard" ]
});
wx.ready(function() {
//    alert("ready okay");
    shareTimeline();
    shareAppMessage();
});
wx.error(function(res){
//    alert("failed");
    // config信息验证失败会执行error函数，如签名过期导致验证失败，
    // 具体错误信息可以打开config的debug模式查看，也可以在返回的res参数中查看，对
    // 于SPA可以在这里更新签名。
});
function shareTimeline(){
    wx.onMenuShareTimeline({
        title: "<?php echo $share['shareTitle']; ?>",
        desc:  "<?php echo $share['shareContent']; ?>",
        link: "<?php echo $share['shareLink']; ?>",
        imgUrl: "<?php echo $share['shareIcon']; ?>",
        trigger: function (res) {},
        success: function (res) {
        },
        cancel: function (res) {},
        fail: function (res) {
            
        }
    });
}
function shareAppMessage(){
    wx.onMenuShareAppMessage({
        title: "<?php echo $share['shareTitle']; ?>",
        desc:  "<?php echo $share['shareContent']; ?>",
        link: "<?php echo $share['shareLink']; ?>",
        imgUrl: "<?php echo $share['shareIcon']; ?>",
        success: function () { 
        },
        cancel: function () { 
           
        }
    });
    
}
</script>
<a href= "<?php echo $playUrl; ?>">
    <img src="<?php echo $publicityCover; ?>" id="cover"></a>
</body>
</html>