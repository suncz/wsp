<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title><?= $video->name ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no">
        <meta name="description" content="">
        <meta name="keywords" content="">
        <link rel="stylesheet" href="/css/comment.css">
        <link rel="stylesheet" href="/css/font-awesome/css/font-awesome.min.css">
        <script type="text/javascript" src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
        <script type="text/javascript" src="/static/js/jquery-1.1.1.min.js"></script>
        <script type="text/javascript" >
        var isCallback=<?php echo $isCallback;?>;
        if(isCallback>0)
        {
            location.href="<?php echo $share['shareLink'];?>";
        }
        </script>
    </head>
    <body>
        <div id="play"></div>
        <div style="margin: 5px 10px;float: right;">
            <div id="tempMsg"></div>
            <i class="fa fa-user-o" style="color: #000000;"></i>		
            <span class="nub" id="pvNum" style="font-size: 14px;color: #B6B6B6;"><?= $video->pvNum ?></span>
            <i class="fa fa-thumbs-o-up" style="color: #000000;" onclick="praise();"></i>	        
            <span class="nub" id="praiseNum" style="font-size: 14px;color: #B6B6B6;"><?= $video->praiseNum ?></span>
            <i class="fa fa-comments-o" style="color: #000000;"></i>		
            <span class="nub" id="msgNum" style="font-size: 14px;color: #B6B6B6;"><?= $commentCounts ?></span>
        </div>
        <section class="container page-swiper-panel" id="page-swiper-panel" style="background: #fff;">
                <!-- <div id="temp"><?= $userId ?></div> -->
            <ul class="kinerNav">
                <li class="active">聊天互动</li>
                <li>联系方式</li>
                <!-- 		        <li>直播详情</li>         -->
            </ul>
            <div class="box">
                <div class="kinerContent">
                    <div class="wrapper">
                        <div class="kinerItem">
                            <div class="dms-message-container"> 
                                <ul id="dmsMessage"> 
                                    <!-- <li>
                                            <div class="dms-header">
                                            <img src="/images/icon02.png" />
                                    </div>
                                    <div class="message">
                                            <div class="message-info">
                                                    <div class="nick">张三</div> 
                                                    <div class="date">2017-03-26 22:00</div> 
                                                    <div class="clear"></div> 
                                            </div> 
                                            <div class="message-content">hello</div> 
                                    </div> 
                                 </li> -->
                                </ul> 
                            </div>
                            <div class="dms-send-container"> 
                                <div class="emt"></div> 
                                <div id="login" class="dms-publish-btn" onclick="login();">登录</div> 
                                <div id="publish" class="dms-publish-btn" onclick="publish();">发布</div> 
                                <div class="dms-textarea-container"> 
                                    <input type="text" placeholder="请输入聊天文字" id="aodianyun-dms-text" onkeydown="javascript:if (event.keyCode == 13) {
                                                                            dmsLayoutHandle.publish();
                                                                        }" /> 
                                </div> 
                                <div class="dms-cb"></div> 
                            </div>
                        </div>
                        <div class="kinerItem" style="overflow:auto;">
                            <div id="cooperation"></div>
                        </div>
                        <!-- 		                <div class="kinerItem"> -->
                        <!-- 		                	<div>详情描述</div> -->
                        <!-- 		                </div>           -->
                    </div>

                </div>
            </div>
        </section>

        <script src="/js/jquery-1.11.3.min.js"></script>
        <script src="/js/kiner-swiper-panel.min.js"></script>
        <script src="/js/jquery.qqFace.js"></script>
        <script src="/js/player.js"></script>
        <script>
                                                            //解决在移动端顶部出现空白的问题
                                                            if ($('#play').position().top > 0) {
                                                                $('#play').css('margin-top', -$('#play').position().top);
                                                                $('.box').css('margin-top', $('#play').position().top);
                                                            }

                                                            var w = parseInt(document.body.scrollWidth);//视频播放窗口的宽度
                                                            var h = parseInt(document.body.scrollWidth / (16 / 9));//视频播放窗口的高度
                                                            //奥点云的播放器	    	
                                                            var objectPlayer = new aodianPlayer({
                                                                container: 'play', //播放器容器ID，必要参数
                                                                rtmpUrl: '<?= $video->lssUrl ?>', //控制台开通的APP rtmp地址，必要参数
                                                                hlsUrl: '<?= $video->vodUrl ?>', //控制台开通的APP rtmp地址，必要参数
                                                                /* 以下为可选参数*/
                                                                width: w, //播放器宽度，可用数字、百分比等
                                                                height: h, //播放器高度，可用数字、百分比等
                                                                //     autostart: true,//是否自动播放，默认为false
                                                                bufferlength: '1', //视频缓冲时间，默认为3秒。hls不支持！手机端不支持
                                                                maxbufferlength: '2', //最大视频缓冲时间，默认为2秒。hls不支持！手机端不支持
                                                                stretching: '1', //设置全屏模式,1代表按比例撑满至全屏,2代表铺满全屏,3代表视频原始大小,默认值为1。hls初始设置不支持，手机端不支持
                                                                controlbardisplay: 'enable', //是否显示控制栏，值为：disable、enable默认为disable。
                                                                adveDeAddr: '<?= $video->img ?>', //封面图片链接
                                                                adveWidth: w, //封面图宽度
                                                                adveHeight: h, //封面图高度
                                                                adveReAddr: '', //封面图点击链接
                                                                isclickplay: true, //是否单击播放，默认为false
                                                                isfullscreen: true//是否双击全屏，默认为true
                                                            });

                                                            //用户ID
                                                            var userId = '<?= $userId ?>';
                                                            var pageJson = '';
                                                            $(function () {
                                                                pvNum();//更新浏览次数
                                                                getComments();//获取评论
                                                                if (userId != '') {
                                                                    $('#login').hide();
                                                                    $('#publish').show();
                                                                } else {
                                                                    $('#login').show();
                                                                    $('#publish').hide();
                                                                }

                                                                //直播合作加入图片
                                                                var lssImg = new Image();//创建图片对象   
                                                                lssImg.src = '<?= $this->config->item('cooperation_imgUrl') ?>';//直播合作的图片地址
                                                                //加载完成执行 
                                                                lssImg.onload = function () {
                                                                    $('#cooperation').html(lssImg);//向DIV中添加img标签
                                                                    $('#cooperation img').css('width', '100%');//设置图片宽度100%显示
                                                                };

                                                                //表情
                                                                $('.emt').qqFace({
                                                                    id: 'facebox',
                                                                    assign: 'aodianyun-dms-text',
                                                                    path: '/images/arclist/' //表情存放的路径
                                                                });
                                                            });

                                                            //展示表情
                                                            function replace_em(str) {
                                                                var str1 = str.substring(str.indexOf('[') + 1, str.lastIndexOf(']'));
                                                                if (str1 != "" && dmsFaceArr2[str1] != undefined) {
                                                                    str1 = '[' + dmsFaceArr2[str1] + ']';
                                                                    str1 = str1.replace(/\</g, '&lt;');
                                                                    str1 = str1.replace(/\>/g, '&gt;');
                                                                    str1 = str1.replace(/\n/g, '<br/>');
                                                                    str1 = str1.replace(/\[em_([0-9]*)\]/g, '<img src="/images/arclist/$1.gif" border="0" />');
                                                                    return str1;
                                                                }
                                                                return str;
                                                            }

                                                            //微信登录
                                                            function login() {
                                                                var url = '<?= $codeUrl ?>';
                                                                window.location.href = url;
                                                            }

                                                            //发布评论
                                                            function publish() {
                                                                var content = $('#aodianyun-dms-text').val();
                                                                if (content != "") {
                                                                    $.ajax({
                                                                        url: "/index.php/comment/publishComment",
                                                                        type: 'POST',
                                                                        timeout: 5000, //超时时间设置，单位毫秒
                                                                        async: true,
                                                                        data: {
                                                                            'videoId': '<?= $video->id ?>',
                                                                            'userId': '<?= $userId ?>',
                                                                            'content': content
                                                                        }, success: function (msg) {
                                                                            $('#temp').html(msg + "成功！<?= $userId ?>");
                                                                            $('#aodianyun-dms-text').val('');
                                                                            msg = msg.substring(msg.indexOf('[{'), msg.lastIndexOf('}]') + 2);
                                                                            msg = eval(msg);
                                                                            $.each(msg, function (k, v) {
                                                                                $('#msgNum').text(v.msgNum);
                                                                                getComments(v.msgNum);
                                                                            });
                                                                        }, error: function (msg) {
                                                                            $('#temp').html(msg + "失败！<?= $userId ?>");
                                                                            $('#aodianyun-dms-text').val('');
                                                                            msg = msg.substring(msg.indexOf('[{'), msg.lastIndexOf('}]') + 2);
                                                                            msg = eval(msg);
                                                                            $.each(msg, function (k, v) {
                                                                                $('#msgNum').text(v.msgNum);
                                                                                getComments(v.msgNum);
                                                                            });
                                                                        }
                                                                    });
                                                                }
                                                            }

                                                            //浏览次数
                                                            function pvNum() {
                                                                $.ajax({
                                                                    url: "/index.php/comment/pvNum",
                                                                    type: 'POST',
                                                                    timeout: 5000, //超时时间设置，单位毫秒
                                                                    async: true,
                                                                    data: {
                                                                        'videoId': '<?= $video->id ?>'
                                                                    }, success: function (msg) {
                                                                        msg = msg.substring(msg.indexOf('[{'), msg.lastIndexOf('}]') + 2);
                                                                        msg = eval(msg);
                                                                        $.each(msg, function (k, v) {
                                                                            $('#pvNum').text(v.pvNum);
                                                                        });
                                                                    }, error: function (msg) {

                                                                    }
                                                                });
                                                            }

                                                            //点赞
                                                            function praise() {
                                                                $.ajax({
                                                                    url: "/index.php/comment/praise",
                                                                    type: 'POST',
                                                                    timeout: 5000, //超时时间设置，单位毫秒
                                                                    async: true,
                                                                    data: {
                                                                        'videoId': '<?= $video->id ?>'
                                                                    }, success: function (msg) {
                                                                        msg = msg.substring(msg.indexOf('[{'), msg.lastIndexOf('}]') + 2);
                                                                        msg = eval(msg);
                                                                        $.each(msg, function (k, v) {
                                                                            $('#praiseNum').text(v.praiseNum);
                                                                            $('.fa-thumbs-o-up').css('color', '#ff0000');
                                                                            $('.fa-thumbs-o-up').removeAttr('onclick');
                                                                        });

                                                                    }, error: function (msg) {

                                                                    }
                                                                });
                                                            }


                                                            var start = 0;
                                                            var len = 10;
                                                            var tempLen = 0;
                                                            var count = -1;
                                                            var msgCount = '<?= $commentCounts ?>';
                                                            //分页获取评论
                                                            function getComments(msgNum) {
                                                                if (msgNum != null && msgNum != "") {
                                                                    msgCount = msgNum;
                                                                }
                                                                start = 0;
                                                                len = 10;
                                                                $("#dmsMessage li").remove();
                                                                jsonData = "";
                                                                pageInit({
                                                                    start: start,
                                                                    len: len,
                                                                    lord: function () {
                                                                        if (start < msgCount) {
                                                                            if (start + len > msgCount) {
                                                                                tempLen = len;
                                                                                len = msgCount - start;
                                                                            }
                                                                            if (pageJson == '') {
                                                                                $.ajax({
                                                                                    type: 'POST',
                                                                                    url: '/index.php/comment/getComments',
                                                                                    cache: false,
                                                                                    async: false,
                                                                                    data: {
                                                                                        'videoId': '<?= $video->id ?>',
                                                                                        start: start,
                                                                                        len: len
                                                                                    },
                                                                                    success: function (msg) {
                                                                                        msg = msg.substring(msg.indexOf('[{'), msg.lastIndexOf('}]') + 2);
                                                                                        msg = eval(msg);
                                                                                        if (msg.count <= 0) {
                                                                                            //										$("#dmsMessage").append("<li>暂无相关评论</li>");
                                                                                        } else {
// 											$('#dmsMessage').find('li:last').remove();
                                                                                            var str = '';
                                                                                            $.each(msg, function (k, v) {
                                                                                                str += '<li><div class="dms-header"><img src="' + v.headImgUrl + '" /></div><div class="message"><div class="message-info"><div class="nick">' + v.nickName + '</div><div class="date">' + v.createTime + '</div><div class="clear"></div></div><div class="message-content">' + replace_em(v.content) + '</div></div></li>';
                                                                                            });
                                                                                            $('#dmsMessage').append(str);
                                                                                            if (tempLen != 0) {
                                                                                                len = tempLen;
                                                                                            }
                                                                                            start = start + len;
                                                                                            pageJson = '';
                                                                                        }
                                                                                    },
                                                                                    error: function (xmlHttpRequest, textStatus, errorThrown) {
                                                                                        alert('加载错误');
                                                                                    }
                                                                                });
                                                                            } else {
                                                                                if (pageJson.count <= 0) {
                                                                                    //								$("#dmsMessage").append("<li>暂无相关评论</li>");
                                                                                } else {
                                                                                    var str = '';
                                                                                    $.each(pageJson, function (k, v) {
                                                                                        str += '<li><div class="dms-header"><img src="' + v.headImgUrl + '" /></div><div class="message"><div class="message-info"><div class="nick">' + v.nickName + '</div><div class="date">' + v.createTime + '</div><div class="clear"></div></div><div class="message-content">' + v.content + '</div></div></li>';
                                                                                    });
                                                                                    $('#dmsMessage').append(str);
                                                                                    pageJson = '';
                                                                                }
                                                                            }
                                                                        }
                                                                    },
                                                                    yes: function () {
// 						$("#dmsMessage").append("<li>下拉获取更多评论↓</li>");
                                                                    },
                                                                    no: function () {
// 						$("#dmsMessage").append("<li>没有更多评论</li>");
                                                                    }
                                                                });
                                                            }

                                                            //分页方法
                                                            function pageInit(object) {
                                                                start = object.start;
                                                                len = object.len;
                                                                ;
                                                                object.lord();
                                                                if (start != count) {
                                                                    object.yes();
                                                                } else {
                                                                    object.no();
                                                                }
                                                                $(".dms-message-container").scroll(function () {
                                                                    if (($('#dmsMessage').height() + $('#dmsMessage').position().top - $('#dmsMessage').find('li:last').height()) <= $('.dms-message-container').height()) {
                                                                        if (start != count) {
                                                                            object.lord();
                                                                            if (start != count) {
                                                                                object.yes();
                                                                            } else {
                                                                                object.no();
                                                                            }
                                                                        }
                                                                    }
                                                                });
                                                            }
        </script>
        <script type="text/javascript">
            wx.config({
                debug: false,
                appId: "<?php echo $jsSign['appId']; ?>",
                timestamp: <?php echo $jsSign['timestamp']; ?>,
                nonceStr: "<?php echo $jsSign['nonceStr']; ?>",
                signature: "<?php echo $jsSign['signature']; ?>",
                url: "<?php echo $jsSign['url']; ?>",
                jsApiList: ["onMenuShareTimeline", "onMenuShareAppMessage", "addCard"]
            });
            wx.ready(function () {
//    alert("ready okay");
                shareTimeline();
                shareAppMessage();
            });
            wx.error(function (res) {
//    alert("failed");
                // config信息验证失败会执行error函数，如签名过期导致验证失败，
                // 具体错误信息可以打开config的debug模式查看，也可以在返回的res参数中查看，对
                // 于SPA可以在这里更新签名。
            });
            function shareTimeline() {
                wx.onMenuShareTimeline({
                    title: "<?php echo $share['shareTitle']; ?>",
                    desc: "<?php echo $share['shareContent']; ?>",
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
            function shareAppMessage() {
                wx.onMenuShareAppMessage({
                    title: "<?php echo $share['shareTitle']; ?>",
                    desc: "<?php echo $share['shareContent']; ?>",
                    link: "<?php echo $share['shareLink']; ?>",
                    imgUrl: "<?php echo $share['shareIcon']; ?>",
                    success: function () {
                    },
                    cancel: function () {

                    }
                });

            }
        </script>
    </body>
</html>
