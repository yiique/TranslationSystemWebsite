// JavaScript Document
var entrance="retrivel";
var doc_height=document.documentElement.clientHeight;


$(function(){
	
	$(window).resize(function(){							  

	
		$("#listdict").datagrid({								
	    
		   height:doc_height-215
		});
	});
	
	//$('#ftranstatus').draggable();
	$('#listdict').datagrid({
		
		url: app_url+'/Dict/listDict',
		idField:'tid',
		title: '词典列表',
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

					{field:'dictname',sortable:true,title:'词典名称',width:140,align:'center'},
	 
			  		{field:'type',sortable:true,title:'应用类别',width:100,align:'center',	                    	
	                    	formatter:function(val,rec){
							if (val =='ctzy')
		                		return '传统中医';
		            		else if(val=='hxhg')
		                		return '化学化工';
		            		else if(val=='yiyao')
		                		return '医药';
		            		else if(val=='machine')
		                		return '机械';
		            		else if(val=='phy')
		                		return '物理';
		            		else if(val=='zonghe')
		            			return '综合';
                		else
                    		return '通用';
						}
					},  
										
					//{field:'dirinfo',sortable:true,title:'翻译方向',width:180,align:'center'},
					
					{field:'srclanguage',sortable:true,title:'源语言',width:120,align:'center'},
					
					{field:'tgtlanguage',sortable:true,title:'目标语言',width:120,align:'center'},
					
					{field:'action',title:'操作',width:140,align:'center',formatter:function(val,rec,index){
						var str="";
				            str+="<a href='javascript:void(0);' class='easyui-linkbutton' iconCls='icon-mod' onclick='javascript:editRow("+rec.tid+",\""+rec.dictname+"\",\""+rec.srclanguage+"\",\""+rec.tgtlanguage+"\");'>修改</a>&nbsp;&nbsp;&nbsp;";
							//alert(rec.authuser1);
                            if (val == '1'){
                                str+="<a href='javascript:void(0);' onclick='javascript:authDict("+rec.tid+",\""+rec.dictname+"\",\""+rec.authuser1+"\",\""+rec.authuser2+"\");'>授权</a>&nbsp;&nbsp;&nbsp;";
                  
                                str+="<a href='javascript:void(0);' onclick='javascript:delRow("+rec.tid+");'>删除</a>&nbsp;&nbsp;|&nbsp;&nbsp;";
                            }
                            else{
                                str+="<a href='javascript:void(0);' onclick='javascript:delRow("+rec.tid+");'>删除</a>&nbsp;&nbsp;|&nbsp;&nbsp;";
                            }
						
			                str+="<a href='javascript:void(0);' onclick=javascript:viewItem("+rec.tid+",'"+rec.dictname+"');>查看词条</a>";
					
						return str;
						}
					},
		           {field:'action2',title:'<input type="checkbox" id="-1" onclick="javascript:dictlist_selectall(this);"/>',width:120,align:'center',
						formatter:function(val,rec){
						return "<input type='checkbox' id='dictlist_"+rec.tid+"'/>";
						}
					}

				]],
				onRowContextMenu:function(e,rowIndex,rowData)
				{

					
				},
				onSortColumn:function(sort,order){
					var queryParams = $('#listdict').datagrid('options').queryParams;
				    queryParams.sortName = sort;
				    queryParams.sortOrder = order;
				
				    $("#listdict").datagrid('reload');
				},
				onDblClickRow:function(rowIndex,rowData)
				{					
					viewItem(rowData["tid"],rowData["dictname"]);
				},
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
				},
				onAfterEdit:function(index,row)
				{
					row.editing = false;
					$('#listdict').datagrid('refreshRow', index);
					editcount--;
				},
				onCancelEdit:function(index,row)
				{
					row.editing = false;
					$('#listdict').datagrid('refreshRow', index);
					editcount--;
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


function refresh_list()
{
	$("#listdict").datagrid("reload");
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
		$.messager.alert('提示','请勾选需要批量处理的词典','info');
		return ;
	}
	$.messager.confirm('操作提示', '您确认要删除选中的'+cnt+'部词典?', function(r){
		if (r){
			$("#win-waiting").window("open");
			for(var i=0;i<cbks.length;++i)
			{	
				if(cbks[i].checked==true)
				{
					
					var pos=(cbks[i].id).indexOf('_');
					var tid=(cbks[i].id).substr(pos+1,(cbks[i].id).length-pos-1);
					
					$.post(app_url+'/Dict/delDict',{'tid':tid},function(data){
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
function delRow(tid)
{
	$.messager.confirm('操作提示', '您确认要删除选中的的词典?', function(r){
		if (r){
					
			$.post(app_url+'/Dict/delDict',{'tid':tid},function(data){
				if(data.info=="yes")
				{
					
				}
				else
				{
					$.messager.alert('错误',data.data,'error');							
				}
		    },'json');
					
				
			$.messager.alert('提示','删除成功','info');
			$('#listdict').datagrid('reload');
			
		}
	});

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
//查看词条
function viewItem(tid,dictname)
{	
	
	initSWF(tid);
	$("#newitem").hide();
	
	bindItemTable(tid,dictname);

	$('#listitem').datagrid('loadData',{total:0,rows:[]});
	
		
	$('#listitem').datagrid('load',{
		  dictid:tid
	});
	
	$("#win-dictitem").modal({
							'backdrop':true,
							'show':true,
							});

}

//修改词典

function editRow(tid,dictname,srclanguage,tgtlanguage)
{	
    
	//$('#win-moddict').window('open');	 
	  $("#dictname2").val(dictname);
	  
	  $.post(app_url+'/Dict/getDirid',{'srclanguage':srclanguage,'tgtlanguage':tgtlanguage},function(data){
	
		if(data.info=="yes")
		{
			$("#direction2").combobox('select',data.data);
		}
		else
		{
			$.messager.alert('错误',data.info,'error');			
		}

      },'json');

	  $("#tid").val(tid);
	  $("#updatedict").modal({
							'backdrop':true,
							'show':true,
							});
	
}


//词典授权

function authDict(tid,dictname,authuser1,authuser2)
{	
    
	//$('#win-moddict').window('open');	 
	  $("#dictname3").val(dictname);
	  
			$("#auth1").combobox('select',authuser1);
			$("#auth2").combobox('select',authuser2);


	  $("#tid").val(tid);
	  $("#authdict").modal({
							'backdrop':true,
							'show':true,
							});
	
}

//显示添加词典弹窗
function show_add_dict()
{	
	
    $("#dictname").val("");
	$("#direction").val("");
    $("#adddict").modal({
							'backdrop':true,
							'show':true,
							});
}

function add_dict()
{	
	var dirid=$("#direction").combobox('getValue');
	//alert(dirid);
	var dictname=$("#dictname").val();
	//var tid=$('#listitem').datagrid('#idField').queryParams["tid"];
	//var tid=$("#tid").val();
	var type="ty";
	var description="1";
	var issystem="0";
	var isactive="1";

	$.post(app_url+'/Dict/updateDict',{'dictname':dictname,'type':type,'dirid':dirid,'isactive':isactive,'description':description,'issystem':issystem},function(data){
	
		if(data.info=="yes")
		{
			//$.messager.alert('提示','添加成功','info');
			//$('#listdict').datagrid('reload');
    	//	$("#adddict").modal("hide");
    		
		}
		else
		{
			$.messager.alert('错误',data.info,'error');			
		}

    },'json');

	$('#listdict').datagrid('reload');
}

function update_auth()
{
	var auth1 = $("#auth1").combobox('getValue');
	var auth2 = $("#auth2").combobox('getValue');
	var tid = $("#tid").val();
	var dictname = $("#dictname3").val();
	//alert(dictname);
	
	$.post(app_url + '/Dict/updateAuth', {'tid':tid, "dictname":dictname, 'authuser1':auth1, 'authuser2':auth2}, function(data){
		if (data.info=="yes"){
			$.messager.alert('提示','修改成功','info');
		}
		else{
			$.messager.alert('错误', data.info, 'error');
		}
	
	}, 'json');
	$('#listdict').datagrid('reload');
}

function update_dict()
{
	var dirid=$("#direction2").combobox('getValue');
	//alert(dirid);
	var dictname=$("#dictname2").val();
	
	var tid=$("#tid").val();
	var type="ty";
	var description="1";
	var issystem="0";
	var isactive="1";
	//$("#win-waiting").window("open");

	$.post(app_url+'/Dict/updateDict',{'tid':tid,'dictname':dictname,'type':type,'dirid':dirid,'isactive':isactive,'description':description,'issystem':issystem},function(data){
		if(data.info=="yes")
		{
			//$.messager.alert('提示','修改成功','info');
			//$('#listdict').datagrid('reload');
    		//$("#adddict").modal("hide");
			//$('#listdict').datagrid('reload');
    		
		}
		else
		{
			$.messager.alert('错误',data.info,'error');			
		}

    },'json');
	$('#listdict').datagrid('reload');
	//$("#win-waiting").window("close");
}

//批量下载词典
function bat_download()
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
	$.messager.alert('提示','请勾选需要批量处理的词典','info');
	return ;
    }
    $.messager.confirm('操作提示', '您确认要下载选中的'+cnt+'部词典?', function(r){
	if (r){
	    $("#win-waiting").window("open");

	    var ids = "";
		    
	    for(var i=0;i<cbks.length;++i)
	    {	
		if(cbks[i].checked==true)
		{
					
		    var pos=(cbks[i].id).indexOf('_');
		    var tid=(cbks[i].id).substr(pos+1,(cbks[i].id).length-pos-1);

		    if (ids == ""){ids = ids + tid;}
		    else {ids = ids + "," + tid;}			
		}
	    }
		//alert(ids);
	    $.post(app_url+'/Dict/bat_download_dict',{'guid':ids},function(data){
		if(data.info=="yes")
		{
		    $('#win-waiting').window('close');
		    var re = data.data;
		    window.location.href = app_url+"/File/download/sname/"+re+"/fname/"+getNowFormatDate()+".zip";
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
