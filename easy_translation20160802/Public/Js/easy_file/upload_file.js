// JavaScript Document
var swfu;
$(function() {
	//alert(window.location.href);
	var settings = {
		
		// 按钮背景图片（从上到下包括按钮的四个状态：原始，悬浮，点击等），宽度=按钮宽度，长度=按钮长度*4：
		button_image_url:public_url+"/swfupload/images/upload_btn.png",
		flash_url :public_url+ "/swfupload/swfupload/swfupload.swf",
	//	flash9_url : "__PUBLIC__/swfupload/swfupload/swfupload_fp9.swf",
		upload_url: app_url+"/File/tranFile/username/"+username,	//后台处理上传操作路径，并且将用户名作为参数传过去
     	post_params: {"PHPSESSID" : session_id},	//将session_id作为参数传过去
		file_size_limit : "100 MB",
		file_types : "*.txt;*.doc;*.docx",
		file_types_description : "文本文件,Word(doc)文档",
		file_upload_limit : 1000,  //配置上传个数
		file_queue_limit : 0,
		custom_settings : {
			progressTarget : "fsUploadProgress",	//用于显示上传队列的div控件id
			cancelButtonId : "sm_btn_cancel"	//用于控制取消上传按钮的控件id，一定要指定，否则会报错，可以指定一个hidden元素，
		},
		debug: false,	
		
		button_width: "93",	//这两个属性要和制作的img尺寸匹配
		button_height: "24",
		button_placeholder_id: "sm_btn_add_file",	//上传按钮id
		button_text: '<span class="theFont">上传文件</span>',
		button_text_style: ".theFont { font-size: 13; color:#000000; font-family:'微软雅黑';}",
		button_text_left_padding: 19,
		button_text_top_padding: 3,
     	button_cursor : SWFUpload.CURSOR.HAND,
		button_window_mode : SWFUpload.WINDOW_MODE.TRANSPARENT,
//		debug:false,
		//下面这些函数是默认的事件处理函数，最好不要修改，需要的功能我都留了其他接口
				// The event handler functions are defined in handlers.js
			file_queued_handler : fileQueued,
			file_queue_error_handler : fileQueueError,
			file_dialog_complete_handler : fileDialogComplete,
			upload_start_handler : uploadStart,
			upload_progress_handler : uploadProgress,
			upload_error_handler : uploadError,
			upload_success_handler : uploadSuccess,
			upload_complete_handler : uploadComplete,
			queue_complete_handler : queueComplete,
			
			
	
	};
	swfu = new SWFUpload(settings);
	
 });

/*
$(function(){
 
 $("a").click(function (e) {
     alert("默认行为被禁止喽");
     e.preventDefault();
  });
});  */

function file_upload_cancel(){
	 var cnt=swfu.getStats().files_queued;
	 doc_height=document.documentElement.clientHeight;	
	// $(".content_container").css("height",(doc_height-129-80+cnt*55));
 }
 
function uploadFinish(cnt)
{
	$('#win-waiting').window('close');
	//$("#fsUploadProgress").fadeOut("slow");

	$.messager.alert('提示','一共提交了 '+cnt+' 个文件','info');
	$("#getBoxy").modal("hide");
	$('#listfile').datagrid("reload");
	doc_height=document.documentElement.clientHeight;	
	// $(".content_container").css("height",(doc_height-129-80));
}

//点击开始上传按钮
var reg_direction =/^\d+$/;
function subtrans(){
	/*if(swfu.getStats().files_queued<1)
	{
		$.messager.alert('提示','请点击左边按钮选择需要翻译的文件','info');
		return ;
	}
	if(type=="unknown")
	{
		$.messager.alert('提示','请选择翻译方向','info');
		return ;
	}*/
	//swfu.setPostParams({'type':'ty','PHPSESSID':'1234','username':'test'});	//动态传递参数
	if(reg_direction.test($('#direction').combobox('getValue')))
	{
		var dirid=$("#direction").combobox('getValue');
		//alert(dirid);
		swfu.setPostParams({"type":"ty","dirid":dirid });
		swfu.startUpload();
		$("#sm_send_btn").addClass("disabled").attr("disabled",true).text(" 正在上传...");
		$('#listfile').datagrid("reload");
	}
	else
	{
		alert('请选择翻译方向');
	    swfu.cancelQueue();
		$('#getBoxy').modal('hide');
	}
}
 //从文件上传框选择文件后显示弹出框(若为选中文件，则不显示)
 function start_upload()
 {
	$("#sm_send_btn").text('开始上传');
	if(swfu.getStats().files_queued>5){
		$("#getBoxy .modal-header #warning_tip").text("您选择的文件过多，为避免长时间上传，您可以选择取消并分次上传");
		$("#getBoxy").modal({"backdrop":true,'show':true});
	}
	else if(swfu.getStats().files_queued>0){
		$("#getBoxy").modal({"backdrop":true,'show':true});
	}
 }
 //上传队列结束后
function upload_finish(file_counts,file_ID)
{ 
	var file_id=[];
	var file_name=[];
	var id;
	var start;
	var end;
	for(var i=0;i<file_ID.length;i++){
		id=obj2str(file_ID[i]);
		start=id.indexOf(",");
		end=id.indexOf("]");
		file_id[i]=id.substring(start+1,end);
		file_name[i]=$("#fsUploadProgress .progressContainer .progressName:eq("+i+")").text();
	}
	window.location.href=app_url+"/Trans/index";

 }
 
//点击取消按钮
 function sm_btn_cancel(){
	swfu.cancelQueue();
	$("#getBoxy").modal("hide");
}

//对象类型转换为字符串类型
function obj2str(o){				//object转换为string
   var r = [];
   if(typeof o == "string" || o == null) {
     return o;
   }
   if(typeof o == "object"){
     if(!o.sort){
       r[0]="{"
       for(var i in o){
         r[r.length]=i;
         r[r.length]=":";
         r[r.length]=obj2str(o[i]);
         r[r.length]=",";
       }
       r[r.length-1]="}"
     }else{
       r[0]="["
       for(var i =0;i<o.length;i++){
         r[r.length]=obj2str(o[i]);
         r[r.length]=",";
       }
       r[r.length-1]="]"
     }
     return r.join("");
   }
   return o.toString();
}

//状态提醒
function set_mystate(text){
	$(".my_state").fadeIn("slow");
	$(".my_state").text(text);
	setTimeout("$('.my_state').fadeOut('slow');",4000);
}

