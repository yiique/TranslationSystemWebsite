// JavaScript Document
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

function translate(org,rst){
	var s_lan="chinese";
	var t_lan="english";
	var tags=[];
	var count=0;
	
	//等待响应
//	wait_answer();
	
	//翻译请求
	ajaxPost=$.post(app_url+"/Trans/post_trans_result",{
					"type":"hxhg",
					"src_lan":s_lan,
					"tgt_lan":t_lan,
					"content":org.text(),
					"tags":tags
					},function(data){
				//if(data.length==0)return true;																										
				var d_data=eval('('+data+')');
				d_data=eval('('+d_data.data+')');    //alert(d_data);
				var org_r=d_data.src;			    //获取原文（加标签）
				var rst_r=d_data.tgt;			   //获取译文(加标签)
				var transid=d_data.transID;
				//alert(transid);
				
				org.html(org_r);
				rst.html(rst_r);
				
				//响应成功
			//	$("body").unmask();
		
				//set_mystate("翻译完成！");
				//$(".my_state").fadeOut("slow");
//..........................................................................................................
			/*	var left_text=$("#area").text();
				var right_text=$("#result").text();//alert(left_text==right_text);
				if(left_text==right_text)
				{
					set_mystate("翻译不成功，请检查源语言是否对应！");	
				}
				setTimeout("$('.my_state').fadeOut('slow');",3000);*/
//...........................................................................................................
				$("#t_grade").css("background","url("+public_url+"/Image/online_public/u163_original.png) bottom no-repeat");
				
				clearTimeout(time);				//取消计时
				
				//var src_word="";				//词语原文
				//var tgt_word="";				//词语译文
				//源端高亮
				org.find(".align").each(function(){					
					$(this).hover(function(){
						$(this).addClass("show");
						$(this).attr("title","点击可查询");
						rst.find("#"+this.id).addClass("show");
						
					//	src_word=$(this).text();				//词语原文
						//$(this).css("background","#fc9");				   
					},function(){
						//$(this).css("background","none");
						$(this).removeClass("show");
						rst.find(".align").removeClass("show");
					});								 
				});
				//目标端高亮
				rst.find(".align").each(function(){
					$(this).hover(function(){
						$(this).addClass("show");
						$(this).attr("title","点击可编辑");
						org.find("#"+this.id).addClass("show");
						
						//src_word=$(this).text();				//词语原文
						//$(this).css("background","#fc9");				   
					},function(){
						//$(this).css("background","none");
						$(this).removeClass("show");
						org.find(".align").removeClass("show");
					});								 
				});
				var left;
				var top;
				//点击显示加入单词本（源端）
				$("#area span").click(function(e){//alert("a");
					$(".tool_tip").css("display","block");
					//alert(e.target.tagName);
					$(".tool_tip .save img").attr("src","Public/Image/online_public/stickers_002.png");
					//显示相应翻译结果词语
					var idname=$(this).attr("id");
					$(".tool_tip #tip_h3").html($("#result").find("#"+idname).text());
					left=$(this).position().left+44+"px";
					top=$(this).position().top+140+"px";
					$(".tool_tip").css({"left":left,"top":top});
					tip_top=$(this).position().top;
					//查词典显示相应词语
					var search_t=this.innerText;
					dict_move();
					getRst(search_t);
					
				});
				
				//点击其他地方加入单词本隐藏
				hide_tip();
				

				//点击显示加入单词本（目标端）
				$("#result span").click(function(){
					//目标端加入单词本，暂不用
					/*$(".tool_tip").css("display","block");
					$(".tool_tip .save img").attr("src","Public/Image/online_public/stickers_002.png");
					//显示相应翻译结果词语
					var idname=$(this).attr("id");
					$(".tool_tip #tip_h3").html($("#area").find("#"+idname).text());
					var left=$(this).position().left-1+"px";
					var top=$(this).position().top+22+"px";
					$(".tool_tip").css({"left":left,"top":top});*/
					tip_top=$(this).position().top;
					var search_t=this.innerText;
					dict_move();
					//查询该词
					getRst(search_t);
					//点击目标端词语可编辑
					$(this).attr("contentEditable","true");			
				});
				//点击其他地方可编辑失效
				$("#result span").blur(function(){
					$(this).attr("contentEditable","false");			 
				});
				
					
					
				//监听翻译结果，并出现相应打分入口操作
				$(".t_main_wrapper .t_tgt_result .tgt_grade").css("display","block");
				
				//显示打分板，原始状态
				$("#t_grade").click(function(){
					$("#t_stars").css("display","block");	
					hide_grade();
				});
				$("#t_stars .star").click(function(){
							$("#t_stars .star").unbind();
							var grade=$(this).index()+1;
							//alert(transid);
							$.post(app_url+"/Index/do_grade",{"ID":transid,"value":grade},function(data){
								var d=eval('('+data+')');
								d=eval('('+d.data+')');
								if(d.errorCode=="0"){
									$("#t_grade").css("background","url("+public_url+"/Image/online_public/r_mark.png) bottom no-repeat");
									set_mystate("打分成功，感谢参与！");
									$(".my_state").fadeOut("slow");
								}
								else
									set_mystate("打分失败！");
									
							});
						});
				//翻译结果打分
				$(" #t_stars .star").hover(function(){
						var num=$(this).index();//alert(num);
						$("#t_stars .star:lt("+num+")").css("background","url("+public_url+"/Image/online_public/stars.png) 0 2px no-repeat");
						$(this).css("background","url("+public_url+"/Image/online_public/stars.png) 0 2px no-repeat");		//鼠标悬浮显示分值
						
					},function(){
						//鼠标离开打分板时返回原始状态
						$("#t_stars .star").css("background","url("+public_url+"/Image/online_public/stars.png) bottom no-repeat");																			
					});
		});
	
}
//查词典检索功能
$(document).ready(function(){
	var tip_top=0;
	var ajaxPost;
	var time;
	//点击查词典按钮
	$(".t_dict .t_dict_main .t_dict_input img").click(function(){
		//初始化保存按钮
		$(".dict_result_title #save img").attr("src","Public/Image/online_public/stickers_002.png");
		var t=$(".t_dict .t_dict_main .t_dict_input input").val();
		getRst(t);
		});
	//键盘enter键
	key_press();
	area_key();
	quick_key();
	// win_key();
	//加入单词本功能 小词典
	/*$(".dict_result_title #save img").click(function(){	
			var sr=$(".t_dict_result .dict_result_title #word").text();
			var b=$(".dict_result_basic .dict_basic_content").text();					  
			save_it(sr,b,this);
	});*/
	
	//加入单词本
					$(".save img").click(function(){
						//alert(src_word+"--"+tgt_word);
						var s_w=$(".dict_result_title #word").text();
						var t_w=$(".tool_tip #tip_h3").text();
						if(confirm("确定将("+s_w+"-->"+t_w+")加入到您的个人术语库吗？"))
							save_it(s_w,t_w,this);
						
					});
					$(".read_save .sent_tip").click(function(){
						set_mystate("纸条发送成功！");
						$(".my_state").fadeOut("slow");
					});
	//点击tool_tip可编辑--不用
	/*$(".tool_tip .tip_t #tip_h3").click(function(){
		$(this).attr("contentEditable","true");											
	});*/
	//点击关闭单词本
	/*$(".tool_tip .read_save .close_tip").click(function(){
		$(".tool_tip").hide();												   
	});*/
	
	//添加标签
	
});

function getRst(search_text){
	//加载中
	$(".t_dict").css("cursor","wait");
	set_mystate("查词中....");
	//查词典显示相应词语
	$(".t_dict_result .dict_result_title #word").text(search_text);
	//查词典显示相应词语基本释义
					$.get(app_url+"/Trans/get_trans_result?usrid=testusr&content="+search_text,function(d){
							var d_d=eval('('+d+')');
							d_d=eval('('+d_d.data+')');
							$(".t_dict_result .dict_basic_content").html("");
							for(key in d_d.result){
								//alert(d_d.result[key]);
								
								$(".t_dict_result .dict_basic_content").append(obj2str(d_d.result[key][1])+"<br/>");	
							}
							$(".t_dict").css("cursor","auto");	//加载成功
							set_mystate("查词成功！");
							$(".my_state").fadeOut("slow");
					});
					//查词典显示相应词语例句
					/*$.get(app_url+"/Trans/get_sent_result?range=0-1&content="+search_text,function(s){
						var d_s=eval('('+s+')');
						//alert(d_s);
						$(".t_dict_result .dict_eg_content").html("");
						for(key in d_s.result){
							if(key!="totalNum"&&key!="currNum"){
							$(".t_dict_result .dict_eg_content").append("<span>"+obj2str(d_s.result[key][1])+"</span><br/><br/>"+obj2str(d_s.result[key][2])+"<br/>");	
							}
						}
					});	*/
}
//保存单词
function save_it(s,t,tgt){
		//alert(t);
		$.get(app_url+"/Index/save_word?src="+s+"&tgt="+t,function(data){
			var d=eval('('+data+')');
			d=eval('('+d.data+')');
			if(d.errorCode=="0"){
				$(tgt).attr("src","Public/Image/online_public/stickers_001.png");
				set_mystate("保存成功！");
				$(".my_state").fadeOut("slow");
				 hide_tip();
			}
			else{
				set_mystate("保存失败,请重试！");
				 hide_tip();
			}
		});	
}
//调整小词典位置
function dict_move(){
	if(tip_top>250){
			$(".t_main_wrapper .t_dict").css("margin-top",(tip_top-50)+"px");
			$(".t_main_wrapper .t_dict .t_dict_link").css("top",(tip_top+30)+"px");
			
		}
	else{
		$(".t_main_wrapper .t_dict").css("margin-top","40px");
		$(".t_main_wrapper .t_dict .t_dict_link").css("top","120px");
	}
}
//监听键盘enter键
function key_press(){
			$(".t_dict .t_dict_main .t_dict_input input[type='text']").keypress(function(e){
				if(e.which==13)
					$(".t_dict .t_dict_main .t_dict_input img").click();
		});
}
/*
//响应等待,取消请求
function wait_answer(){
	$("body").mask("loading....");
	
	//set_mystate("翻译中....");
	//设置等待响应时间
	time=setTimeout(function(){set_mystate("翻译超时，文字内容过多！");$("body").unmask();ajaxPost.abort();},6000);
}*/
//隐藏单词本
function hide_tip(){
		document.onclick=function(){
			var e = window.event; 
			if(e.target!=undefined){
				var tar=e.target;
				if(tar.id!="tool_tip"&&tar.id!="tip_h3"&&tar.id!="hide_shadow"&&tar.tagName!="SPAN")
				{
					$(".tool_tip").hide();	
				}
				else{
					var tar=e.srcElement;
					if(tar.id!="tool_tip"&&tar.id!="tip_h3"&&tar.id!="hide_shadow"&&tar.tagName!="SPAN")
					{
						$(".tool_tip").hide();	
					}
				}
			}
		}
}

//隐藏打分
function hide_grade(){
	document.onclick=function(e){
			var e = window.event;
			var tar ;
			//alert(tar.className);
			if(e.target!=undefined)
				tar=e.target;
			else
				tar=e.srcElement;
			if(tar.id!="t_grade"&&tar.id!="t_stars"&&tar.className!="star")
			{
				$("#t_stars").hide();	
			}
			
		}	
}

//状态提醒
function set_mystate(text){
	$(".my_state").fadeIn("slow");
	$(".my_state").text(text);
}

//按回车......................................................................................................
function area_key(){
	$(".t_src_wrapper #area").keydown(function(e){
				//var e = e||window.event;
				if(e.keyCode==13){
					set_mystate("请点击翻译按钮进行翻译!");
					setTimeout("$('.my_state').fadeOut('slow');",3000);
				}//alert(e.ctrlKey==true &&e.keyCode==122);
				
		});	
}
function quick_key(){//alert("a");
	document.onkeydown =function(){//alert(e.which);
		var e = window.event; 
		if(e.ctrlKey&&e.keyCode==90){
			set_mystate("已撤销！");
			$('.my_state').fadeOut('slow');
		}
		if(e.keyCode == 67 && e.ctrlKey){
		set_mystate("已复制！");
		$('.my_state').fadeOut('slow');
		}
		if(e.keyCode == 86 && e.ctrlKey){
			set_mystate("已黏贴！");
			$('.my_state').fadeOut('slow');
		}
		if(e.keyCode == 89 && e.ctrlKey){
			set_mystate("已恢复！");
			$('.my_state').fadeOut('slow');
		}	
	}
}