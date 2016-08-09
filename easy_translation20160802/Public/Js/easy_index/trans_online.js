var reg_transdir =/^\d+$/;

$(function (){
	var default_select=null;

	// 读取cookie中的翻译方向
	if(window.localStorage){
		default_select = window.localStorage.getItem('otrans_direction');
	}
	else if (getCookie("otrans_direction")!=null && getCookie("otrans_direction")!=""){
		default_select=getCookie("otrans_direction");
	}
	
	// 读取cookie中的input和output
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

	// 加载cookie中的翻译方向，根据选择设置cookie中的otrans_direction
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
	
	// 清空控件和cookie
	$(".clear").click(function(){
		$(".input_area").val("");			//清空输入内容		
		$("#result").html("");				//清空翻译结果
		$(".clear").hide();					//隐藏领域选择
		
		if(window.sessionStorage){
			window.sessionStorage.removeItem('otrans_input');
			window.sessionStorage.removeItem('otrans_output');
		}
	});
	
	// 字体控制
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
	
	// 复制功能
	var copy_client = new ZeroClipboard($('#trans-online-copy'));
	copy_client.on( 'ready', function(event) {  	//  加载swf
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

// function trans
function trans()
{
	$('#tags').tagsInput();
	var dirid=$("#direction").combobox('getValue');
	var content=$("#input_area").val();
	if(dirid == "" || !reg_transdir.test(dirid)){
		$("#result").html("<font color='red'>请选择有效翻译方向</font>");
	}
	else if(content==""){ 
		$("#result").html("<font color='red'>请输入翻译内容</font>");
	}
	else{
		//存储数据
		if(window.sessionStorage){
			window.sessionStorage.setItem('otrans_input', content);
		}
		
		$("button.trans_btn").eq(0).text('正在翻译...');
		$("#result").html("");
		$.post(app_url+'/Trans/tranSent',{'dirid':dirid,'content':content},function(data){
			var test=data.data;
			if(window.sessionStorage){
				window.sessionStorage.setItem('otrans_output', test.join('|||'));
			}
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
			}
			if(output_area[0].scrollHeight > input_area[0].scrollHeight){
				input_area.css('height',output_area[0].scrollHeight);
				output_area.css('height',output_area[0].scrollHeight);
			}
			$("button.trans_btn").eq(0).text('开始翻译');
		},'json');
	}
}

// function input_text
function input_text() {
		
	if($(".input_area").val()=="")
	{
		$(".clear").hide();
		$("#result").html("");
	}
	else {
		$(".clear").fadeIn("slow");
	}
}