// JavaScript Document
var swfu;
window.onload = function() {
	//alert(window.location.href);

	var settings = {
		// 按钮背景图片（从上到下包括按钮的四个状态：原始，悬浮，点击等），宽度=按钮宽度，长度=按钮长度*4：
		button_image_url:public_url+"/swfupload/simpledemo/images/upload_btn.png",
		flash_url :public_url+ "/swfupload/swfupload/swfupload.swf",
	//	flash9_url : "__PUBLIC__/swfupload/swfupload/swfupload_fp9.swf",
		upload_url:  app_url+ "Trans/upload",	//后台处理上传操作路径，并且将用户名作为参数传过去
	//	post_params: {"PHPSESSID" : session_id},	//将session_id作为参数传过去
		file_size_limit : "200MB",
		file_types : "*.txt",
		file_types_description : "文本文件",
		file_upload_limit : 1000,  //配置上传个数
		file_queue_limit : 0,
		custom_settings : {
			progressTarget : "fsUploadProgress",	//用于显示上传队列的div控件id
			cancelButtonId : "sm_btn_cancel"	//用于控制取消上传按钮的控件id，一定要指定，否则会报错，可以指定一个hidden元素，
		},
		debug: false,	
		
		button_width: "93",	//这两个属性要和制作的img尺寸匹配
		button_height: "24",
		button_placeholder_id: "sm_btn_add_dict",	//上传按钮id
		button_text: '<span class="theFont">添加词典</span>',
		button_text_style: ".theFont { font-size: 13; color:#000000;}",
		button_text_left_padding: 19,
		button_text_top_padding: 1,
     	button_cursor : SWFUpload.CURSOR.HAND,
		button_window_mode : SWFUpload.WINDOW_MODE.TRANSPARENT,
//		debug:false,
		//下面这些函数是默认的事件处理函数，最好不要修改，需要的功能我都留了其他接口
				// The event handler functions are defined in handlers.js
				swfupload_preload_handler : preLoad,
				swfupload_load_failed_handler : loadFailed,
				file_queued_handler : fileQueued,
				file_queue_error_handler : fileQueueError,
				file_dialog_complete_handler : fileDialogComplete,
				upload_start_handler : uploadStart,
				upload_progress_handler : uploadProgress,
				upload_error_handler : uploadError,
				upload_success_handler : uploadSuccess,
				upload_complete_handler : uploadComplete,
				queue_complete_handler : queueComplete	// Queue plugin event
	};
	
//alert("222");
	swfu = new SWFUpload(settings);
	
 };

/*
$(function(){
 
 $("a").click(function (e) {
     alert("默认行为被禁止喽");
     e.preventDefault();
  });
});  */

 
function start_swf(){
	swfu.setPostParams({"type":"hxhg","sessionid":"1234","username":username});	//动态传递参数
	swfu.startUpload();
	$("#sm_send_btn").addClass("disabled").attr("disabled",true).text(" 正在上传...");
}
 //从文件上传框选择文件后显示弹出框(若为选中文件，则不显示)
 function start_upload()
 {
	// alert("OK");
	if(swfu.getStats().files_queued>5){
			$("#getBoxy .modal-header #warning_tip").text("您选择的文件过多，为避免长时间上传，您可以选择取消并分次上传");
			$("#getBoxy").modal({"backdrop":true,'show':true});
		 }
	else if(swfu.getStats().files_queued>0)
		$("#getBoxy").modal({"backdrop":true,'show':true});
 	
 }
 //点击开始上传按钮
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
			window.location.href=app_url+"/TransTool/index";
			
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

