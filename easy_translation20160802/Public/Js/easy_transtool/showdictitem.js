var editcount=0;

$(function() {
	var settings = {
		    button_image_url:public_url+"/swfupload/images/upload_btn.png",// Relative to the Flash file
		    flash_url :public_url+ "/swfupload/swfupload/swfupload.swf",
			upload_url: app_url+"/Dict/uploadDict/dictid/",
			post_params: {"PHPSESSID" : "<?php echo session_id(); ?>"},
			file_size_limit : "200 MB",
			file_types : "*.txt;",
			file_types_description : "文本文件",
			file_upload_limit : 1000,  //配置上传个数
			file_queue_limit : 0,
			custom_settings : {
				progressTarget : "fsUploadProgress",
				cancelButtonId : "btnCancel"
			},
			debug: false,

			// Button settings
		    button_width: "93",	//这两个属性要和制作的img尺寸匹配
		    button_height: "24",
			button_placeholder_id: "spanButtonPlaceHolder",
			button_text: "<span class='theFont'>选择词条文件</span>",
			button_text_style: ".theFont { font-size: 13; color:#000000;}",
		    button_text_left_padding: 6,
		    button_text_top_padding: 2,
			button_cursor : SWFUpload.CURSOR.HAND,
			
			debug:false,
			file_queued_handler : fileQueued,
			file_queue_error_handler : fileQueueError,
			file_dialog_complete_handler : fileDialogComplete,
			upload_start_handler : uploadStart,
			upload_progress_handler : uploadProgress,
			upload_error_handler : uploadError,
			upload_success_handler : uploadSuccess,
			upload_complete_handler : uploadComplete,
			queue_complete_handler : queueComplete
		};
	swfu = new SWFUpload(settings);	
	//alert(swfu);
	

});

function getNowFormatDate() {
    var date = new Date();
    var seperator1 = "";
    var seperator2 = "";
    var month = date.getMonth() + 1;
    var strDate = date.getDate();
    if (month >= 1 && month <= 9) {
        month = "0" + month;
    }
    if (strDate >= 0 && strDate <= 9) {
        strDate = "0" + strDate;
    }
    var currentdate = date.getFullYear() + seperator1 + month + seperator1 + strDate
            + " " + date.getHours() + seperator2 + date.getMinutes()
            + seperator2 + date.getSeconds();
    return currentdate;
}

function initSWF(tid)
{
	
	//$("#hide_dictid").value=tid;
	ID("hide_dictid").value=tid;
	//swfu.customSettings.upload_url=app_url+"/Dict/uploadDict/dictid/"+tid+"/username/"+username;
}

//提交词典文件
var guid;
function subDict()
{	
	if(swfu.getStats().files_queued<1)
	{
		$.messager.alert('提示','请点击左边按钮选择需要上传的词条文件','info');
		return ;
	}
	//alert(ID("hide_dictid").value);
	guid=get_guid();
	
	swfu.setPostParams({"dictid":ID("hide_dictid").value,"guid":guid});
	//
	swfu.startUpload();		
	$("#win-waiting").window("open");	
	
}
/*
function query_result()
{
	var str=app_url+"/Dict/listDictItem";
	if($("#q_src").val()!="")
		str+="/src/"+$("#q_src").val();
	if($("#q_tgt").val()!="")
		str+="/tgt/"+$("#q_tgt").val();
	if($("#q_subtime").datebox("getValue")!="")
		str+="/subtime/"+$("#q_subtime").datebox("getValue");
	
	$('#listitem').datagrid({
        url:str
    });
	$("#win-query").window("close");
}*/
var arr_query=new Array();
function query_result()
{
	//alert("333");
	var str=app_url+"/Dict/searchlistDictItem";
if ($("#q_src").val() != "")
			str += "/src/" + $("#q_src").val();
		if ($("#q_tgt").val() != "")
			str += "/tgt/" + $("#q_tgt").val();
		if ($("#q_subtime").datebox("getValue") != "")
			str += "/subtime/" + $("#q_subtime").datebox("getValue");
		str += "/q_type/" + $("#q_type option:selected").val();
		
    var obj=new Object();
	obj.src=$("#q_src").val(); 
	obj.tgt=$("#q_tgt").val(); 
	obj.subtime=$("#q_subtime").datebox("getValue"); 
	arr_query.push(obj);
	$('#listitem').datagrid({
        url:str,
		/*
        onLoadSuccess:function(data)
		{
		alert(data['total']);
			if(data['total']==0)
			{
				alert('您搜素的词条不存在！');
			}
		}
		*/
    });
	$("#win-query").window("close");
}
function query_cancel()
{
	$("#win-query").window("close");
}
//绑定词条表格
var T=0;
function bindItemTable(tid,dictname)
{    //selectItems =[];
//alert(window.selectItems.length);
	$('#listitem').datagrid({
		toolbar:[{
			text:'显示全部',
			id:'tb_viewall',			
			handler:function(){
				var str=app_url+"/Dict/listDictItem";
				$('#listitem').datagrid({
		            url:str
		        });	
			}
		},
		
		{
			text:'查询',
			id:'tb_search',	
			iconCls:'icon-search',			
			handler:function(){				
		        document.getElementById('q_src').value="";
				document.getElementById('q_tgt').value="";
				document.getElementById('q_subtime').value="";
				$("#win-query").window("open");

			}
		},
		
		{
			text:'添加词条',
			id:'tb_add',			
			iconCls:'icon-add',
			handler:function(){			
				//$('#listitem').datagrid('beginEdit',index);
			//	$("#win-adddictitem").panel("refresh",app_url+"/Dict/adddictitem/did/"+tid);
				//$("#win-adddictitem").window("open");
				$('#listitem').datagrid('insertRow',{
					index:0,
					row:{
					'src':'',
					'tgt':'',			
					'action':''

					}});
				
				    var lastIndex = 0;
				//	var lastIndex=$('#listdict').datagrid('getRows').length-1;
				//	$('#listdict').datagrid('getRows')[lastIndex].editing=true;
					$('#listitem').datagrid('selectRow', lastIndex);

					$('#listitem').datagrid('beginEdit', lastIndex);
			}
		},'-',{
				id:'tb_import',
				text:'文件上传词典条目',
				iconCls:'icon-add',
				//disabled:true,
				handler:function(){									
					var item_status=$("#newitem").css("display")=="none"?true:false;
					if(item_status)
						$("#newitem").fadeIn("slow");
					else
						$("#newitem").fadeOut("slow");
				}
		},'-',{
			id:'tb_bat_del_item',
			text:'批量删除',
			iconCls:'icon-remove',
			handler:bat_del_item
		}
		,{
			id:'tb_bat_download_item',
			text:'批量下载',
			iconCls:'icon-save',
			handler:bat_download_item
		}
		],

		url: app_url+'/Dict/listDictItem',
		idField:'tid',
		//queryParams:{'dictid':tid},
		pageList:[10,15,20,25,30,40,50,100,200,500,1000],
		fit:false,
		width:740,
		height:400,
		//singleSelect:true,
		fitColumns: true,
		nowrap:false,
		//remoteSort:false,
		rownumbers:true,
		pagination:true,
		loadMsg:'数据载入中...',
		columns:[[

					{field:'src',title:'源语言',editor:{type:'validatebox',options:{required:true,validType:length[1,100]}},
	                    width:125,align:'center'},
                    {field:'tgt',title:'目标语言',editor:{type:'validatebox',options:{required:true,validType:length[1,100]}},
	                    width:125,align:'center'},
					{field:'action',title:'操作',width:100,align:'center',formatter:function(val,rec,index){
						    var str="";
						//	rec.ischecked=1;
							if(rec.editing)
							{
								str+="<a href='#' id='save_btn' style='color:red' onclick='javascript:saveItemRow("+index+","+rec.tid+");'>保存&nbsp;&nbsp;</a>";
								str+="<a href='#' style='color:red' onclick='javascript:cancelItemRow("+index+");'>取消</a>";
							}
							else
							{
								str+="<a href='#' onclick='javascript:editItemRow("+index+");'>修改&nbsp;</a>";
								str+="<a href='#' onclick='javascript:delItemRow("+index+","+rec.dictid+","+rec.tid+");'>删除&nbsp;</a>";

							}
	
						return str;
						}
					},
					{field:'action2',title:'&nbsp;<input type="checkbox" id="-1" onclick="javascript:dictitemlist_selectall(this);"/>',width:100,align:'center',
						formatter:function(val,rec){
						//alert(window.selectItems);
							if ($.inArray(rec.tid, window.selectItems)==-1)
								return "<input type='checkbox' id='dictitemlist_"+rec.tid+"'/>";
							else
							return "<input type='checkbox' checked='checked' id='dictitemlist_"+rec.tid+"'/>";
						}
					}
				]],

				onClickRow:function(rowIndex)
				{
					lastItemIndex = rowIndex;
					$(this).datagrid('unselectRow', rowIndex);
				},
				onBeforeEdit:function(index,row)
				{
					row.editing = true;
					$('#listitem').datagrid('refreshRow', index);
					edititemcount++;
				//	updateActions();
				},
				onAfterEdit:function(index,row)
				{
					row.editing = false;
					$('#listitem').datagrid('refreshRow', index);
					edititemcount--;
				},
				onCancelEdit:function(index,row)
				{
					row.editing = false;
					$('#listitem').datagrid('refreshRow', index);
					edititemcount--;
				},
				onBeforeLoad:function(param)
				{T+=1;
				//alert(T);
				if (T==2){
				window.selectItems=[];
				
				}
				//alert(window.selectItems);
					var cbks=$("[id^=dictitemlist_]");	
					var cnt=0;
					for(var i=0;i<cbks.length;++i)
					{			
						if(cbks[i].checked==true)
						{			
							cnt++;
							var subId = cbks[i].id.slice(13);
							if ($.inArray(subId, window.selectItems)==-1)
							{window.selectItems.push(subId);}
						}
						
						else
						{
							//cnt++;
							var subId = cbks[i].id.slice(13);
							//alert($.inArray(subId, selectItems));
							if ($.inArray(subId, window.selectItems)!=-1)
							{//alert($.inArray(subId, selectItems));
							window.selectItems.splice($.inArray(subId,window.selectItems),1);
							
							}
						}
						
					}	
					//alert(selectItems);
					if(edititemcount>0)
					{
						$.messager.alert('提示','请先保存或取消正在修改的条目','info');
						return false;
					}
				}
	});
}

//批量删除词条
function bat_del_item()
{
	var cbks=$("[id^=dictitemlist_]");	
	var cnt=0;
	for(var i=0;i<cbks.length;++i)
	{			
		if(cbks[i].checked==true)
		{			
			cnt++;
		}
	}	
	if(cnt==0)
	{
		$.messager.alert('提示','请勾选需要批量处理的词条','info');
		return ;
	}
	$.messager.confirm('操作提示', '您确认要删除选中的'+cnt+'个词条?', function(r){
		if (r){
			$("#win-waiting").window("open");
			for(var i=0;i<cbks.length;++i)
			{
				if(cbks[i].checked==true)
				{
					var pos=(cbks[i].id).indexOf('_');
					var tid=(cbks[i].id).substr(pos+1,(cbks[i].id).length-pos-1);
					$.post(app_url+'/Dict/delDictItem',{'tid':tid},function(data){
						if(data.info=="yes")
						{
							
						}
						else
						{
							$.messager.alert('错误',data.data,'error');							
						}
				    },'json');
					
				}
			}
			$.messager.alert('提示','批量删除成功','info');
			$('#listitem').datagrid('reload');
			$("#win-waiting").window("close");
			
		}
	});
}
//批量下载词条
function bat_download_item()
{ 
var cbks=$("[id^=dictitemlist_]");	
					var cnt=0;
					for(var i=0;i<cbks.length;++i)
					{			
						if(cbks[i].checked==true)
						{			
							cnt++;
							var subId = cbks[i].id.slice(13);
							if ($.inArray(subId, window.selectItems)==-1)
							{window.selectItems.push(subId);}
						}
						
						else
						{
							//cnt++;
							var subId = cbks[i].id.slice(13);
							//alert($.inArray(subId, selectItems));
							if ($.inArray(subId, window.selectItems)!=-1)
							{//alert($.inArray(subId, selectItems));
							window.selectItems.splice($.inArray(subId,window.selectItems),1);
							
							}
						}
						
					}	
	var cnt = window.selectItems.length;
	if(cnt==0)
	{
		$.messager.alert('提示','请勾选需要批量处理的词条','info');
		return ;
	}
	$.messager.confirm('操作提示', '您确认要下载选中的'+cnt+'个词条?', function(r){
		if (r){
			$("#win-waiting").window("open");

			var ids = "";
			for(var i=0;i<window.selectItems.length;++i)
			{
			var tid = window.selectItems[i];
			if (ids == ""){ids = ids + tid;}
			else{ ids = ids+","+tid;}
				/*	
				if(cbks[i].checked==true)
				{
					var pos=(cbks[i].id).indexOf('_');
					var tid=(cbks[i].id).substr(pos+1,(cbks[i].id).length-pos-1);
					if (ids == ""){ids = ids + tid;}
					else{ ids = ids+","+tid;}
				}
				*/
			}
			$.post(app_url+'/Dict/download_file',{'guid':ids},function(data){
						//alert(data.info)
						if(data.info=="yes")
						{
					$('#win-waiting').window('close');
					var re=data.data;	
					window.location.href= app_url+"/File/download/sname/"+re+"/fname/"+getNowFormatDate()+".zip";
						}
						else
						{
							$.messager.alert('错误',data.data,'error');							
						}
				    },'json');
			
			$("#win-waiting").window("close");
			
		}
	});
}
//全选复选框

function dictitemlist_selectall(obj)
{	
	var cbks=$("[id^=dictitemlist_]");
	cbks.push(obj);
	if(obj.checked)
	{
		for(var i=0;i<cbks.length;++i)
		{			
			cbks[i].checked=true;			
		}		
	}
	else
	{
		//$(":checkbox").attr("checked", false);	//这种方法有bug，单选某行后此方法无法全选
		for(var i=0;i<cbks.length;++i)
		{
			cbks[i].checked=false;			
		}		
	}
}
//编辑词典
/*
function editItemRow111(tid)
{
	entrance="item";
	$("#win-moddictitem").panel("refresh",app_url+"/Dict/moddictitem/tid/"+tid);
	$("#win-moddictitem").window("open");
}
*/
function editItemRow(index)
	{
		if(edititemcount>0)
		{
			$.messager.alert('提示','请先保存或取消正在修改的条目','info');			
		}
		else
		{
			$('#listitem').datagrid('beginEdit', index);
		}
}
	
//更新列表
function updateItemActions(){
    var rowcount = $('#listitem').datagrid('getRows').length;
    for(var i=0; i<rowcount; i++){
        $('#listitem').datagrid('updateRow',{
            index:i,
            row:{
            action:''}
        });
    }
}
//删除单个词条
function delItemRow(index,dictid,tid)
{
	if(edititemcount>0)
	{
		$.messager.alert('提示','请先保存或取消正在修改的条目','info');
		return ;
	}

	$.messager.confirm('删除提示', '您确认要删除该条记录?', function(r){ 

		if (r){			
			//$("#win-waiting").window("open");
			$('#listitem').datagrid('deleteRow', index);
			$('#listitem').datagrid('acceptChanges');	
			$('#listitem').datagrid('reload');	
			
			$.post(app_url+'/Dict/delDictItem',{'dictid':dictid,'tid':tid},function(data){
				if(data.info=="yes")
				{
					$("#win-waiting").window("close");
					$('#listitem').datagrid('reload');
				}
				else
				{
					$("#win-waiting").window("close");
					$.messager.alert('错误',data.info,'error');					
				}

		    },'json');

		}

	});

}
function convert_code_upload()
{
	$("#win-waiting").window("open");
	//alert (guid);
	$.post(app_url+'/Dict/upload_dictitem_finish',{'guid':guid,'isutf8':'no'},function(data){
		if(data.info=="yes")
		{
			$("#win-waiting").window("close");
			$("#win-confirmupload").modal("hide");
			$.messager.alert('提示','上传成功','info');
			$('#listitem').datagrid('reload');
		}
		else
		{
			$("#win-waiting").window("close");
			$("#win-confirmupload").modal("hide");
			$.messager.alert('提示','上传失败','info');
			$('#listitem').datagrid('reload');				
		}
    },'json');
	
}
function direct_upload()
{	
	//$("#win-confirmupload").modal("hide");
$.messager.alert('提示','上传已经成功，后台需要更新词条，请耐心等待一会','info');
	$("#win-confirmupload").modal("hide");
	$("#win-dictitem").modal("hide");
	$.post(app_url+'/Dict/upload_dictitem_finish',{'guid':guid,'isutf8':'yes'},function(data){
		if(data.info=="yes")
		{
			
			//$("#win-confirmupload").modal("hide");
			$.messager.alert('提示','上传成功','info');
			$('#listitem').datagrid('reload');
		}
		else
		{
			$("#win-waiting").window("close");
			$("#win-confirmupload").modal("hide");
			$.messager.alert('提示','上传失败','info');
			$('#listitem').datagrid('reload');				
		}
    },'json');
	
}
function uploadFinish(cnt)
{
	//selectItems = [];
	$.post(app_url+'/Dict/get_preview_item',{'guid':guid},function(data){
		if(data.info=="yes")
		{
			$("#win-waiting").window("close");
			$("#preview_dictitem").html(data.data);
			$("#btn_confirm_upload").attr("name",guid);
			$("#btn_cancel_upload").attr("name",guid);
			//$("#win-confirmupload").window("open");
			$("#win-confirmupload").modal({
							     'backdrop':true,
							     'show':true,
							    });
		}
		else
		{
			$("#win-waiting").window("close");
			$("#preview_dictitem").html(data.data);					
		}
    },'json');
	//alert("1");
	//$("#win-waiting").window("close");
	//$.messager.alert('提示','一共提交了 '+cnt+' 个文件','info');
	//$('#listitem').datagrid("reload");
	//$("#newitem").fadeOut("slow");
}

function saveItemRow(index,tid)
{
	var dictid=$('#listitem').datagrid('options').queryParams["dictid"];
/*	if($('#src').validatebox('isValid')==false)
	{
		$('#src').validatebox('validate');
		return ;
	}
	if($('#tgt').validatebox('isValid')==false)
	{
		$('#tgt').validatebox('validate');
		return ;
	}	*/
	var src=$("#save_btn").parent().parent().prev().prev().children().children().children().children().children().children().val();
	var tgt=$("#save_btn").parent().parent().prev().children().children().children().children().children().children().val();	
	var isactive=1;	
	//alert("这是tid:")
		//alert(tid);
	//alert(src);
	//alert(tgt);
	//$("#win-waiting").window("open");
//alert(isactive);
	//alert(dictid);
	if (tid!=undefined)
	{
		$.post(app_url+'/Dict/updateDictItem',{'tid':tid,'dictid':dictid,'src':src,'tgt':tgt,'isactive':isactive},function(data){
																											 
		//alert (data);
		if(data.info=="yes")
		{
			//$.messager.alert('提示','修改成功','info');
		    $('#listitem').datagrid('endEdit',index);
			updateItemActions();
			$('#listitem').datagrid('reload');
    	//	$("#win-adddictitem").window("close");
    		
		}
		else
		{
			$.messager.alert('错误',data.info,'error');			
		}

    },'json');
	}
	
	else {
	$.post(app_url+'/Dict/updateDictItem',{'dictid':dictid,'src':src,'tgt':tgt,'isactive':isactive},function(data){
																											 
		//alert (data);
		if(data.info=="yes")
		{
			//$.messager.alert('提示','添加成功','info');
		    $('#listitem').datagrid('endEdit',index);
			updateItemActions();
			$('#listitem').datagrid('reload');
    	//	$("#win-adddictitem").window("close");
    		
		}
		else
		{
			$.messager.alert('错误',data.info,'error');			
		}

    },'json');
	}
	//$("#win-waiting").window("close");
		
		//$('#listitem').datagrid('loaded');
}

/*
function cancel_additem()
{
	$("#win-adddictitem").window("close");
}*/

function cancelItemRow(index)
{
	$('#listitem').datagrid('rejectChanges',index);
	$('#listitem').datagrid('refreshRow', index); 
}

