var reg_transdir =/^\d+$/;

function trans()
{
	$('#tags').tagsInput();
	//$("#result").html("<font color='red'>该语种翻译不存在</font>");
	var dirid=$("#direction").combobox('getValue');
	var content=$("#input_area").val();
	//var content=document.getElementById('input_area').innerText;
	if(dirid == "" || !reg_transdir.test(dirid)){
		$("#result").html("<font color='red'>请选择有效翻译方向</font>");
	}
	else if(content==""){ 
		$("#result").html("<font color='red'>请输入翻译内容</font>");
	}
	else{
		//alert(dirid);
		//存储数据
		if(window.sessionStorage){
			window.sessionStorage.setItem('otrans_input', content);
		}
		
		$("button.trans_btn").eq(0).text('正在翻译...');
		$("#result").html("");
		$.post(app_url+'/Trans/tranSent',{'dirid':dirid,'content':content},function(data){
			//alert(data.info);
			//if(data.info.indexOf("<ResCode>21</ResCode>")!=-1)
			var test=data.data;
			if(window.sessionStorage){
				window.sessionStorage.setItem('otrans_output', test.join('|||'));
			}
			/*
			var st;
			if(document.getElementById("rememberme").checked)
				st=0;
			else
				st=1;
			*/
			$("button.trans_btn").eq(0).text('翻译完成');

			var i,j,words;
			var input_area = $("#input_area");
			var output_area = $('#result');
			$('#trans-online-copy').data('data-clipboard-text', test.join('\n'));
			output_area.html('');
			for(i=0;i<test.length;i++)
			{
				if(test[i] == '')
					words=$("<br />");
				else{
					words=$("<div class='mul' ></div>");
					words.append(test[i]);
				}
				output_area.append(words);
		   /*
				var len=test[i].length;len=len-1;
				if(test[i][len]=='<br/>')
				{
					var words=$("<div class='mul' style='float: left' ></div><br/>");
				}
				else
				{
					var words=$("<div class='mul' style='float: left' ></div>");
				}
				var sp=$("<span>"+test[i][0]+"</span>");
				words.append(sp);
	           // $('ul').hide();
				var segs=$("<ul class='segs trans_wrapper'></ul>");
				for(j=0;j<test[i].length && j<5;j++)
				{
					var seg=$("<li style='list-style-type:none'>"+test[i][j]+"</li>");
					segs.append(seg);segs.hide();
					seg.click(function(){
					//alert($(this).text());
					$(this).parent().parent().children("span").text($(this).text());
					});
				}
			
				words.append(segs);
				
				if(st==0)
					words.append("&nbsp;");
				segs.hide();
				$("#result").append(words);
				words.click(function(){
					//alert(words.attr("id"));
					$(this).children("ul").show();
			
					$(this).children("ul").css("background-color","white");
					$(this).children("span").css({
					"background-color":"white",
					"border":"1px solid blue"
					});
					});
				words.mouseleave(function(){
					//alert(words.attr("id"));
					$(this).children("ul").hide();
					$(this).children("span").css({
					"background-color":"white",
					"border":"0px solid blue"
					
					});
					
					
					});
				words.mouseover(function(){
					//alert(words.attr("id"));
					$(this).children("span").css("background-color","yellow");
					});
				*/
			}
			if(output_area[0].scrollHeight > input_area[0].scrollHeight){
				input_area.css('height',output_area[0].scrollHeight);
				output_area.css('height',output_area[0].scrollHeight);
			}
			$("button.trans_btn").eq(0).text('开始翻译');
		},'json');
	}
}

function input_text() {
		//$(".input_area *").removeAttr("style");
		
		if($(".input_area").val()=="")
		{
			$(".clear").hide();
		    $("#result").html("");
		}
		else {
			$(".clear").fadeIn("slow");
		}
	}

$(function (){
	var default_select=null;
	if(window.localStorage){
		default_select = window.localStorage.getItem('otrans_direction');
	}
	else if (getCookie("otrans_direction")!=null && getCookie("otrans_direction")!=""){
		default_select=getCookie("otrans_direction");
	}
	
	if(window.sessionStorage){
		var input_area = $("#input_area");
		var output_area = $('#result');
		var trans_input = window.sessionStorage.getItem('otrans_input');
		if(trans_input){
			input_area.val(trans_input);
			$(".clear").fadeIn("slow");
			input_area.css('height',input_area[0].scrollHeight);
			output_area.css('height',input_area[0].scrollHeight);
		}
		var trans_output = window.sessionStorage.getItem('otrans_output');
		if(trans_output){
			var words = '';
			trans_output = trans_output.split('|||');
			$('#trans-online-copy').data('data-clipboard-text', trans_output.join('\n'));
			for(var i=0;i<trans_output.length;i++)
			{
				if(trans_output[i] == '')
					words=$("<br />");
				else{
					words=$("<div class='mul' ></div>");
					words.append(trans_output[i]);
				}
				output_area.append(words);
			}
			if(output_area[0].scrollHeight > input_area[0].scrollHeight){
				input_area.css('height',output_area[0].scrollHeight);
				output_area.css('height',output_area[0].scrollHeight);
			}
		}
	}
	//加载数据
	$("#direction").combobox({
		onLoadSuccess:function(){
			$(".combo-panel .combobox-item").click(function(){
				var dirid=$(this).attr('value');
				if(dirid != "" && reg_transdir.test(dirid)){
					if(window.localStorage){
						window.localStorage.setItem('otrans_direction', dirid);
					}
					else{
						setCookie("otrans_direction",dirid);
					}
				}
			});
			if(default_select!=null)
				$("#direction").combobox('setValue',default_select);
		}
	});

	$(".input_area").bind("keyup",input_text);
	
	$(".clear").click(function(){
		 $(".input_area").val("");				//清空输入内容
				//清空翻译结果
		 $("#result").html("");
		//$(this).hide();							//隐藏关闭按钮
		 $(".clear").hide();		//隐藏领域选择
		//清除数据
		if(window.sessionStorage){
			window.sessionStorage.removeItem('otrans_input');
			window.sessionStorage.removeItem('otrans_output');
		}
	});
	
	//字体控制及复制功能逻辑
	var font_size_node = $('#trans-online-size');
	var font_size = parseInt(font_size_node.text());
	$('#trans-online-sizeup').click(function(){
		if(font_size < 24){
			font_size++;
			font_size_node.text(font_size);
			$("#result,#input_wrapper textarea").css('font-size', font_size);
		}
	});
	$('#trans-online-sizedown').click(function(){
		if(font_size > 12){
			font_size--;
			font_size_node.text(font_size);
			$("#result,#input_wrapper textarea").css('font-size', font_size);
		}
	});
	
	var copy_client = new ZeroClipboard($('#trans-online-copy'));
	copy_client.on( 'ready', function(event) {  //  加载swf
		copy_client.on('copy', function(event) {
			// 添加复制操作
			event.clipboardData.setData('text/plain', $('#trans-online-copy').data('data-clipboard-text'));
		});
		copy_client.on('aftercopy', function(event) {
			console.log('Copied: ' + event.data['text/plain']);
			alert('已复制到剪切板');
        });
		copy_client.on( 'error', function(event) {
			console.log( 'ZeroClipboard error of type "' + event.name + '": ' + event.message );
			ZeroClipboard.destroy();
		});
	});
});