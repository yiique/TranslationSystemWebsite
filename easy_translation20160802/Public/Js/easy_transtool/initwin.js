$(function(){
	if($.browser.msie){
	}else{
	$(window).resize(function(){
		doc_height=document.documentElement.clientHeight;	
		doc_width=document.documentElement.clientWidth;	
		
		$('#win-moddict').window({
			left:(doc_width-520)/2,			
			top:doc_height/2-100
		});
		$('#win-confirmupload').window({
			title: '导入词条预览',
			width: 760,
			modal: false,
			shadow: false,
			closed: true,
			inline:false,
			resizable:false,
			minimizable:false,
			maximizable:false,
			left:(doc_width-760)/2,	
			zIndex:10000,
			top:(doc_height-460)/2,
			collapsible:false
		});
		$('#win-retrivelitem').window({
			left:(doc_width-760)/2,			
			top:(doc_height-460)/2
		});
		$('#win-adddict').window({
			left:(doc_width-520)/2,			
			top:doc_height/2-100
		});
		$('#win-dictitem').window({
			left:(doc_width-760)/2,			
			top:(doc_height-410)/2
		});
	});
	}
		$('#win-query').window({
			title: '查询词条',
			width: 760,
			height:100,
			modal: true,
			shadow: false,
			closed: true,
			inline:false,
			resizable:false,
			minimizable:false,
			maximizable:false,
			left:(doc_width-760)/2,			
			top:(doc_height-210)/2,
			collapsible:false
			
		});
		$('#win-confirmupload').window({
			title: '导入词条预览',
			width: 760,
			modal: true,
			shadow: false,
			closed: true,
			inline:false,
			resizable:false,
			minimizable:false,
			maximizable:false,
			left:(doc_width-760)/2,	
			zIndex:100000,
			top:(doc_height-460)/2,
			collapsible:false
		});
		$('#win-dictitem').window({
			title: '编辑词条',
			width:doc_width,// 760,
			modal: false,
			shadow: false,
			closed: true,
			inline:false,
			resizable:false,
			minimizable:false,
			maximizable:false,
			left:0,//(doc_width-760)/2,			
			top:0,//(doc_height-410)/2,
			collapsible:false
		});
		$('#win-import').window({
			title: '导入词典',
			width: 760,
			modal: false,
			shadow: false,
			closed: true,
			inline:false,
			resizable:false,
			minimizable:false,
			maximizable:false,
			left:(doc_width-760)/2,			
			top:(doc_height-460)/2,
			collapsible:false
			
		});
		$('#win-importitem').window({
			title: '导入词条',
			width: 760,
			modal: false,
			shadow: false,
			closed: true,
			inline:false,
			resizable:false,
			minimizable:false,
			maximizable:false,
			left:(doc_width-760)/2,			
			top:(doc_height-400)/2,
			collapsible:false
			
		});
		$('#win-importview').window({
			title: '导入词典历史',
			width: 760,
			modal: false,
			shadow: false,
			closed: true,
			inline:false,
			resizable:false,
			minimizable:false,
			maximizable:false,
			left:(doc_width-760)/2,			
			top:(doc_height-460)/2,
			collapsible:false
			
		});
		$('#win-retrivelitem').window({
			title: '检索词条',
			width: 760,
			loadingMessage:'载入中',
			href:app_url+'/Dict/retrivelitem',
			modal: false,
			shadow: false,
			closed: true,
			inline:false,
			resizable:false,
			minimizable:false,
			maximizable:false,
			left:(doc_width-760)/2,			
			top:(doc_height-460)/2,
			collapsible:false
			
		});
		$('#win-adddict').window({
			title: '添加词典',
			width: 520,
			loadingMessage:'载入中',
			href:app_url+'/Dict/adddict',
			modal: true,
			shadow: false,
			closed: true,
			inline:false,
			resizable:false,
			minimizable:false,
			maximizable:false,
			left:(doc_width-520)/2,			
			top:doc_height/2-100,
			collapsible:false
			
		});
		$('#win-moddict').window({
			title: '编辑词典',
			width: 520,
			loadingMessage:'载入中',
			href:app_url+'/Dict/moddict',
			modal: true,
			shadow: false,
			closed: true,
			inline:false,
			resizable:false,
			minimizable:false,
			maximizable:false,
			left:(doc_width-520)/2,			
			top:doc_height/2-100,
			collapsible:false
			
		});
		$('#win-adddictitem').window({
			title: '添加词条',
			width: 520,
			loadingMessage:'载入中',
			href:app_url+'/Dict/adddictitem',
			modal: true,
			shadow: false,
			closed: true,
			inline:false,
			resizable:false,
			minimizable:false,
			maximizable:false,
			left:(doc_width-520)/2,			
			top:doc_height/2-100,
			collapsible:false
			
		});
		$('#win-moddictitem').window({
			title: '编辑词条',
			width: 520,
			loadingMessage:'载入中',
			href:app_url+'/Dict/moddictitem',
			modal: true,
			shadow: false,
			closed: true,
			inline:false,
			resizable:false,
			minimizable:false,
			maximizable:false,
			left:(doc_width-520)/2,			
			top:doc_height/2-100,
			collapsible:false
			
		});
	});