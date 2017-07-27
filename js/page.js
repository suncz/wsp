 start=0;
 len=10;
 count=-1;
function pageInit(object){
	start=object.start;
	len=object.len;;
	object.lord();
	if(start!=count){
     	object.yes();
     }else{
     	object.no();
     }
//	$(window).scroll(function () {
    $(".dms-message-container").scroll(function() {
//    	var bot = 50; //bot是底部距离的高度
//    	if ((bot + $(window).scrollTop()) >= ($(document).height() - $(window).height())) { 
	    if(($('#dmsMessage').height()+$('#dmsMessage').position().top-$('#dmsMessage').find('li:last').height())<=$('.dms-message-container').height()){
            if(start!=count){
            	object.lord();
            	 if(start!=count){
                 	object.yes();
                 }else{
                 	object.no();
                 }
            }
           
        }
    });
}
/*
 * 用法演示
 pageInit({
	start:0,开始位置
	len:10, 每次加载条数
	lord:function(){
		$.ajax({
			type : 'post',
			url : 'points/pointsGrantRecord.do',
			cache : false,
			async : false,
			dataType : 'json',
			data:{start:start,len:len},
			success : function(json) {
				$(json).each(function(){
					count=json.count;		记录总条数  必须加
					$('.tab').find('tr:last').remove();
					$(json.list).each(function(){
						var tr=$("<tr></tr>");
						var td=$('<td>'+this.datetime+'</td><td>'+this.type+'</td><td>'+this.operation+'</td><td>'+this.personId+'</td>');
						tr.append(td);
						$('.tab').append(tr);
						start++;			当前条数  必须加
					});
				
				});
			},
			error : function(xmlHttpRequest, textStatus, errorThrown) {
				alert('网络错误');
			}
		});
	},
	yes:function(){  当后面还有数据时调用的方法
		var tr='<tr id="null" ><td colspan="4" style="text-align: center;">下拉获取更多数据</td></tr>';	
		$('.tab').append(tr);
	},
	no:function(){  当后面没有数据时调用的方法
		 var tr='<tr id="null" ><td colspan="4" style="text-align: center;">拉到底了</td></tr>';	 
		$('.tab').append(tr); 
	}
	
});*/