<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>demo</title>
		<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no">
		<meta name="description" content="">
		<meta name="keywords" content="">
		<script src="http://www.jq22.com/jquery/jquery-1.10.2.js"></script>
		<link rel="stylesheet" href="/videoComment/css/zy.media.min.css">
		<link rel="stylesheet" href="/videoComment/css/all.css">
		<link rel="stylesheet" href="/videoComment/css/style.css">
	</head>
	<body>
		<div class="playvideo">
			<div class="zy_media">
	    		<video poster="/videoComment/images/<?=$video->img?>" data-config='{"mediaTitle": "<?=$video->name?>"}'>
	        		<?php if($video->vodUrl!=null&&$video->vodUrl!=""):?>
	        			<source src="<?=$video->vodUrl?>" type="video/mp4">
	        		<?php else:?>
	        			<source src="<?=$video->lssUrl?>" type="video/mp4">	        			
	        		<?php endif;?>
	      	  		您的浏览器不支持HTML5视频
	   	 		</video>
			</div>
			<div id="modelView">&nbsp;</div>
		</div>
		<section class="container page-swiper-panel" id="page-swiper-panel">
			<!-- <div id="temp"><?=$userId?></div> -->
		    <ul class="kinerNav">
		        <li class="active">评论</li>
		        <li>直播合作</li>
<!-- 		        <li>直播详情</li>         -->
		    </ul>
		    <div class="box">
		        <div class="kinerContent">
		            <div class="wrapper">
		                <div class="kinerItem">
		                	<div class="dms-title">
							    <p class="count">
							        <span class="icon">
							            <img src="/videoComment/images/icon02.png" width="31" height="26"></span>
							        <span class="nub" id="pvNum"><?=$video->pvNum?></span>
							        <span class="icon">
							            <img src="/videoComment/images/icon03.png" width="31" height="26" onclick="praise();"></span>
							        <span class="nub" id="praiseNum"><?=$video->praiseNum?></span>
							        <span class="icon">
							            <img src="/videoComment/images/icon04.png" width="31" height="26"></span>
							        <span class="nub" id="msgNum"></span>
							        <span class="icon">
							            <img src="/videoComment/images/icon05.png" width="31" height="26"></span>
							</div>
							<div class="dms-message-container"> 
   								<ul id="dmsMessage"> 
   									<!-- <li>
										<div class="dms-header">
								      		<img src="/videoComment/images/icon02.png" />
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
								<input type="text" placeholder="请输入聊天文字" id="aodianyun-dms-text" onkeydown="javascript:if(event.keyCode==13){dmsLayoutHandle.publish();}" /> 
								</div> 
								<div class="dms-cb"></div> 
							</div>
		                </div>
		                <div class="kinerItem">
							<div><p>&nbsp;&nbsp;联系电话：010-12345678</p></div>
		                </div>
<!-- 		                <div class="kinerItem"> -->
<!-- 		                	<div>详情描述</div> -->
<!-- 		                </div>           -->
		            </div>
		
		        </div>
		    </div>
		</section>
		<script src="/videoComment/js/zy.media.min.js"></script>
    	<script src="/videoComment/js/kiner-swiper-panel.min.js"></script>
    	<script src="/videoComment/js/jquery.qqFace.js"></script>
		<script>
		    zymedia('video',{autoplay: false});
		    var userId = '<?=$userId?>';
		    $(function(){
		    	pvNum();//更新浏览次数
		    	getComments();//获取评论
		    	$('#publish').hide();
			    if(userId!=''){
			    	$('#login').hide();
					$('#publish').show();
				}

				//暂不启用表情
				$('.emt').hide();
				$('.dms-textarea-container').css('margin-left','10px');
				
			  	//表情
// 				$('.emt').qqFace({
// 					id:'facebox',
// 					assign:'aodianyun-dms-text', 
// 		    		path:'/videoComment/images/arclist/' //表情存放的路径
// 		    	});

			});

			//微信登录
		    function login(){
		    	var url = '<?=$codeUrl ?>';
		    	window.location.href = url;
			}

			//发布评论
		    function publish(){
				var content = $('#aodianyun-dms-text').val();
				$.ajax({
			        url: "/videoComment/index.php/comment/publishComment",
			        type : 'POST',
			        timeout : 5000, //超时时间设置，单位毫秒
			        async: true,
			        data:{
				        'videoId':'<?=$video->id?>',
				        'userId':'<?=$userId?>',
				        'content':content
			        },success : function(json){
			        	$('#temp').html(json+"成功！<?=$userId?>");
			        	$('#aodianyun-dms-text').val('');
			        	getComments();
			        } ,error : function(json){
			        	$('#temp').html(json+"失败！<?=$userId?>");
			        	getComments();
			        	$('#aodianyun-dms-text').val('');
			        }		
			    });
			}
			//获取评论
		    function getComments(){
				$.ajax({
			        url: "/videoComment/index.php/comment/getComments",
			        type : 'POST',
			        timeout : 5000, //超时时间设置，单位毫秒
			        async: true,
			        data:{
				        'videoId':'<?=$video->id?>'				        
			        },success : function(msg){
			        	msg = eval(msg);
			        	$('#msgNum').text(msg.length);
			        	var str = '';
			        	$.each(msg,function(k,v){
					        str += '<li><div class="dms-header"><img src="'+v.headImgUrl+'" /></div><div class="message"><div class="message-info"><div class="nick">'+v.nickName+'</div><div class="date">'+v.createTime+'</div><div class="clear"></div></div><div class="message-content">'+v.content+'</div></div></li>';
			        	});
			        	$('#dmsMessage').html(str);
			        } ,error : function(msg){

			        }		
			    });
			}

		  	//浏览次数
			function pvNum(){
				$.ajax({
			        url: "/videoComment/index.php/comment/pvNum",
			        type : 'POST',
			        timeout : 5000, //超时时间设置，单位毫秒
			        async: true,
			        data:{
				        'videoId':'<?=$video->id?>'				        
			        },success : function(msg){
			        	$('#pvNum').text(msg);
			        } ,error : function(msg){

			        }		
			    });
			}
			
		  	//点赞
			function praise(){
				$.ajax({
			        url: "/videoComment/index.php/comment/praise",
			        type : 'POST',
			        timeout : 5000, //超时时间设置，单位毫秒
			        async: true,
			        data:{
				        'videoId':'<?=$video->id?>'				        
			        },success : function(msg){
			        	$('#praiseNum').text(msg);
			        } ,error : function(msg){

			        }		
			    });
			}
		</script>
	</body>
</html>