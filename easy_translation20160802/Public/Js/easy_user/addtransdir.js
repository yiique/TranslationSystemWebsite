// JavaScript Document
var entrance="retrivel";
var doc_height=document.documentElement.clientHeight;
var editcount=0;
var lastIndex=0;
var reg_isnumber =/^\d+$/;

$(function(){
	
	$(window).resize(function(){							  

	
		$("#listdict").datagrid({
		   height:doc_height-215
		});
	});
	
	
	$('#listdict').datagrid({
		url: app_url+'/User/listDir',
		idField:'dirid',
		title: '翻译方向列表',
		pageSize:20,
		width: 980,
		pageList:[10,15,20,25,30,40,50,100,200,500,1000],
		height:doc_height-255,
		//singleSelect:true,
		fitColumns: true,
		nowrap:false,
		remoteSort:true,
		rownumbers:true,
		pagination:true,
		loadMsg:'数据载入中...',
		columns:[[
					{field:'dirinfo',sortable:true,title:'翻译方向',width:220,align:'center'},
					{field:'srclanguage',sortable:true,title:'源语言',width:120,align:'center'},
					{field:'tgtlanguage',sortable:true,title:'目标语言',width:120,align:'center'},  
					
					{field:'inserttime',sortable:true,title:'插入/修改时间',width:130,align:'center'},
					{field:'is_space',title:'切分方式',sortable:true,width:80,align:'center',formatter:function(val,rec,index){
							var str="";
							if(rec.isspace==0)							
								str="按字切分";
							else 
								str="按词切分";
							return str;
							}
					},
					
					{field:'action',title:'操作',width:100,align:'center',formatter:function(val,rec,index){
							var str="";
							
														
								str+="<a href='#' onclick='javascript:editRow("+rec.dirid+",\""+rec.srclanguage+"\",\""+rec.tgtlanguage+"\",\""+rec.dirinfo+"\",\""+rec.isspace+"\");'>修改</a>&nbsp;&nbsp;";
								str+="<a href='#' onclick='javascript:delRow("+index+",\""+rec.dirid+"\");'>删除</a>";	
								
							
							
							return str;
							}
					},
					 {field:'action2',title:'&nbsp;<input type="checkbox" id="-1" onclick="javascript:dictlist_selectall(this);"/>',width:80,align:'center',
						formatter:function(val,rec){
						return "<input type='checkbox' id='dictlist_"+rec.dirid+"'/>";
						}
					}
					
					
				]],
					onClickRow:function(rowIndex)
					{
						lastIndex = rowIndex;
						$(this).datagrid('unselectRow', rowIndex);
					},
					onBeforeEdit:function(index,row)
					{
						row.editing = true; 
						$('#listdict').datagrid('refreshRow', index); 
						editcount++; 
					//	updateActions(); 
					}, 
					onAfterEdit:function(index,row)
					{ 
						row.editing = false; 
						$('#listdict').datagrid('refreshRow', index); 
						editcount--; 
					//	updateActions(); 
					}, 
					onCancelEdit:function(index,row)
					{ 
						row.editing = false; 
						$('#listdict').datagrid('refreshRow', index); 
						editcount--; 
					//	updateActions(); 
					},
					onBeforeLoad:function(param)
					{
						if(editcount>0)
						{
							$.messager.alert('提示','请先保存或取消正在修改的条目','info');	
							return false;		
						}
					}
				
	});
});
		//编辑	
		function editRow(dirid,srclanguage,tgtlanguage,dirinfo,isspace)
		{
			  $("#getBoxy2").modal({
							'backdrop':true,
							'show':true,
							});
			//$("#csrclan").val(srclanguage);
			$("#csrclan").combobox('setValue',srclanguage);
			if(tgtlanguage != 'chinese'){
				$.messager.alert('消息','自动更新目标语种 “'+tgtlanguage+'” 为 “chinese”','info');	
				tgtlanguage = 'chinese';
			}
			$("#ctgtlan").val(tgtlanguage);
			$("#ctrandir").val(dirinfo);
			$("#cc2").combobox('setValue',isspace);
			$("#cdirid").val(dirid);
		}
		function update_transdir()
		{
			var dirid=$("#cdirid").val();
			var srclan=$.trim($("#csrclan").combobox("getValue"));
			var tgtlan=$.trim($("#ctgtlan").val());
			var trandir=$.trim($("#ctrandir").val());
			var isspace=$.trim($("#cc2").combobox("getValue"));
			
			if(srclan == "自定义语种"){
				$.messager.alert('消息',"请选择或填写有效的语种名称", 'info');	
				return;
			}
			if(isspace =="" || !reg_isnumber.test(isspace)){
				$.messager.alert('消息','请选择有效切分方式','info');	
				return;
			}

			$.post(app_url+'/User/update_Dir',{"dirid":dirid, "srclanguage":srclan, "tgtlanguage":tgtlan, "dirinfo": trandir, "isspace":isspace}, function(data){	
			
					if(data.info=="yes")
					{	
						$('#listdict').datagrid('reload');				
					}
					else
					{
						$.messager.alert('错误',data.info,'error');
						
					}				
			    	
			    },'json');
			
		}
		//更新列表
		function updateActions(){  
		    var rowcount = $('#listdict').datagrid('getRows').length;  
		    for(var i=0; i<rowcount; i++){  
		        $('#listdict').datagrid('updateRow',{  
		            index:i,  
		            row:{
		            action:''}  
		        });  
		    }  
		}
		//删除记录
		function delRow(index,dirid)
		{
			if(editcount>0)
			{
				$.messager.alert('提示','请先保存或取消正在修改的条目','info');	
				return ;		
			}
			//alert(dirid);
			$.messager.confirm('删除提示', '您确认要删除该条记录?', function(r){

				if (r){
					$('#listdict').datagrid('deleteRow', index);
					$('#listdict').datagrid('acceptChanges');	
					
					$('#listdict').datagrid('reload');	
					$.post(app_url+'/User/deldir',{'dirid':dirid},function(data){	
				
						if(data.info=="yes")
						{						
							$('#listdict').datagrid('reload');					
						}
						else
						{
							$.messager.alert('错误',data.info,'error');
							$('#listdict').datagrid('rejectChanges');
						}				
				    	
				    },'json');
					
				}

			});
		
		}
		//取消操作
		function cancelRow(index)
		{
			$('#listdict').datagrid('rejectChanges',index);
			$('#listdict').datagrid('refreshRow', index); 
		}
		//保存操作
		function saveRow(index)
		{		
			$('#listdict').datagrid('endEdit',index);		
			var rows=$('#listdict').datagrid('getChanges');		
			$('#listdict').datagrid('loading');	
			if(rows.length>0)
			{
				var json=JSON.stringify(rows[0]);	
				$('#listdict').datagrid('reload');
				//alert(json);		
				$.post(app_url+'/User/update_Dir',rows[0],function(data){					
					if(data.info=="yes")
					{	
										
					}
					else
					{
						$.messager.alert('错误',data.info,'error');
						$('#listdict').datagrid('rejectChanges');
					}				
			    	
			    },'json');
			}
			updateActions();
			$('#listdict').datagrid('loaded');
		}

//全选复选框
function dictlist_selectall(obj)
{
	var cbks=$("[id^=dictlist_]");	
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

function modify_pass()
{	
	//$("#ma_mod_pwd_close").show();
	//$("#getBoxy").modal('toggle');
    $("#getBoxy").modal({
			'backdrop':true,
			'show':true,
	});
}


function insert_transdir()
{	
	
	var srclan=$.trim($("#srclan").combobox("getValue"));
	var tgtlan=$.trim($("#tgtlan").val());
	var trandir=$.trim($("#trandir").val());
	var isspace=$.trim($("#cc1").combobox("getValue"));
	
	if(srclan == "自定义语种"){
		$.messager.alert('消息',"请选择或填写有效的语种名称", 'info');	
		return;
	}
	if(isspace =="" || !reg_isnumber.test(isspace)){
		$.messager.alert('消息','请选择有效切分方式','info');	
		return;
	}
	
	//$('#myModal').modal('hide');
	$.post(app_url+'/User/insert_trandir', {'srclan':srclan, 'tgtlan':tgtlan, 'dirinfo':trandir, 'isspace':isspace}, function(data){
		if(data.info=="yes")
				{	
					
					//$.messager.alert('yes',data.data,'info');	
					$('#listdict').datagrid('reload');
					
				}
		else
				{
					$.messager.alert('错误',"您没有权限添加翻译方向",'error');							
				}

							    },'json');
	
}

//批量删除词典
function bat_del()
{
	var cbks=$("[id^=dictlist_]");	
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
		$.messager.alert('提示','请勾选需要批量处理的条目','info');
		return ;
	}
	$.messager.confirm('操作提示', '您确认要删除选中的'+cnt+'条翻译方向?', function(r){
		if (r){
			$("#win-waiting").window("open");
			for(var i=0;i<cbks.length;++i)
			{	
				if(cbks[i].checked==true)
				{
					
					var pos=(cbks[i].id).indexOf('_');
					var tid=(cbks[i].id).substr(pos+1,(cbks[i].id).length-pos-1);
					
					$.post(app_url+'/User/deldir',{'dirid':tid},function(data){
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
			$('#listdict').datagrid('reload');
			$("#win-waiting").window("close");
			
		}
	});
}


function refresh_list(){
	$('#listdict').datagrid('reload');
}





















