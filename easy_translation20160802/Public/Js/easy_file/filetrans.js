// JavaScript Document
var doc_height=document.documentElement.clientHeight;

//set file translation status auto update
//add by yangli 2015-07-12
var trans_status_check = false;
var file_trans_checker = setInterval(function(){
	if(trans_status_check)reload();
}, 1000*20);

var reg_transdir =/^\d+$/;

$(function(){
	//$('#ftranstatus').draggable();
	/*$(window).resize(function(){
		doc_height=document.documentElement.clientHeight;
		doc_width=document.documentElement.clientWidth;
		var opt=$("#listfile").datagrid({
			height:doc_height-215
		});
		$('#win-query').window({
			left:(doc_width-760)/2,			
			top:(doc_height-210)/2
		});
	});*/
	
	var default_select=null;
	if(window.localStorage){
		default_select = window.localStorage.getItem('ftrans_direction');
	}
	else if (getCookie("ftrans_direction")!=null && getCookie("ftrans_direction")!=""){
		default_select=getCookie("ftrans_direction");
	}

	$("#direction").combobox({
		onLoadSuccess:function(){
			$(".combo-panel .combobox-item").click(function(){
				var dirid=$(this).attr('value');
				if(dirid != "" && reg_transdir.test(dirid)){
					if(window.localStorage){
						window.localStorage.setItem('ftrans_direction', dirid);
					}
					else{
						setCookie('ftrans_direction', dirid);
					}
				}
			});
			if(default_select != null)
				$("#direction").combobox('setValue',default_select);
		}
	});
	
	$('#listfile').datagrid({

		url: app_url+'/File/listFile',
		idField:'username',

		title: '文件列表',
		pageSize:20,
		width:980,		
		height:doc_height-265,
		//singleSelect:true,
		fitColumns: true,
		nowrap:false,
		pageList:[10,15,20,25,30,40,50,100,200,500,1000],
		remoteSort:true,
		rownumbers:true,
		pagination:true,
		loadMsg:'数据载入中...',
		columns:[[

					{field:'filename',sortable:true,title:'翻译文件名',
						formatter:function(val,rec){
						var str="<a href='"+app_url+"/File/download/fname/"+rec.filename+"/sname/"+rec.srcname+"'>"+rec.filename+"</a>";
						return str;
            		},width:180,align:'center'},
					//{field:'dirinfo',sortable:true,title:'翻译方向',width:130,align:'center'},
					{field:'srclanguage',sortable:true,title:'源语言',width:130,align:'center'},
					{field:'tgtlanguage',sortable:true,title:'目标语言',width:130,align:'center'},
					/*{field:'type',sortable:true,title:'翻译类别',width:130,align:'center',
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
						}
					}, */
					{field:'subtime',sortable:true,title:'提交时间',width:140,align:'center'},		
					//{field:'finishtime',sortable:true,title:'完成时间',width:140,align:'center'},
					{field:'transtatus',sortable:true,title:'翻译状态',width:140,align:'center',
						formatter:function(val,rec){
							if(val=="100%"&&rec.transstate=="FINISH"){
								return "翻译完成&nbsp;(<a href='#' onclick='download_file(\""+rec.guid+"\",\""+rec.filename+"\");'>下载</a>)";
							}
							else if(rec.isdeleted == 1){
								return "<span class='run'><font color='#ccc'>任务已删除</font></span>";
							}
							else if(rec.transstate=="SUBMITTING"){
								trans_status_check = true;
								return "<span class='run'><font color='green'>任务等待提交...</font></span>";
							}
							else if(rec.transstate == 'SUSPEND'){
								trans_status_check = true;
								return "<span class='run'><font color='green'>任务挂起等待...</font></span>";
							}
							else if(rec.transstate=="RUNNING"){
	   	            			trans_status_check = true;
	   	            			return '<span class="file-trans"><a class="file-trans-prossing" style="width:'+val+';"></a></span> '+val;
	   	            		}  
							else if(rec.transstate=='ERROR'){
								if(rec.errorcode=="21")
									return "<font color='red'>该领域语种翻译不存在</font>";
								else
									return "<font color='red'>翻译错误</font>";
							}
							else if(rec.transstate=='CANCEL'){
								return "<font color='red'>任务被取消</font>";
							}
						}
					}
					,{field:'action',title:'&nbsp;<input type="checkbox" id="-1" onclick="javascript:selectAll(this);"/>',width:100,align:'center',
						formatter:function(val,rec){
							return "<input type='checkbox' id='filelist_"+rec.tid+"'/>";
						}
					}
				]],
				onSortColumn:function(sort,order){
					var queryParams = $('#listfile').datagrid('options').queryParams;
				    queryParams.sortName = sort;
				    queryParams.sortOrder = order;
				 	//alert("1");
				    $("#listfile").datagrid('reload');
				},
				onClickRow:function(rowIndex,rowData){
				    $(this).datagrid('unselectRow', rowIndex);
				},
			//end
			});
	});
	
    //下载
	function download_file(guid,filename)
	{
	
	//	return ;

		var nfilename = filename.replace(/\s+/g, '');
		var pos=nfilename.lastIndexOf(".");
		if(pos!=-1)
		{
			var truefile=nfilename.substr(0,pos);
			var ext=nfilename.substr(pos+1,nfilename.length-pos-1);

			if(ext=="pdf")
				ext = "pdf";
			nfilename = truefile+".easytrans."+ext;
		}

		$('#win-waiting').window('open');
		$.post(app_url+'/File/download_file',{'guid':guid},function(data){
			if(data.info=="yes")
			{
				$('#win-waiting').window('close');
				var re=data.data;
		
				//$.messager.alert('提示',re,'info');
				//$.post('__APP__/Trans/download',{'sname':re,'fname':nfilename},function(data){},'json');
				window.location.href=app_url+"/File/download/sname/"+re+"/fname/"+encodeURIComponent(nfilename);
			
			}
			else
			{
				$.messager.alert('错误',data.data,'error');
			}

	    },'json');
	}

	
//全选复选框
function selectAll(obj)
{
	var cbks=$(":checkbox");
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
//删除文件
function delFile()
{
	var cbks=$("[id^=filelist_]");	
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
		$.messager.alert('提示','请勾选需要删除的记录','info');
		return ;
	}
	$.messager.confirm('操作提示', '您确认要删除选中的'+cnt+'条记录?', function(r){
		if (r){
			$("#win-waiting").window("open");
			for(var i=0;i<cbks.length;++i)
			{	
				if(cbks[i].checked==true)
				{
					var pos=(cbks[i].id).indexOf('_');
					var tid=(cbks[i].id).substr(pos+1,(cbks[i].id).length-pos-1);
					
					$.post(app_url+'/File/file_del',{'tid':tid,'isdeleted':1},function(data){
						if(data.info=="yes")
						{
						}
						else
						{
							$.messager.alert('错误',data.data,'error');	
							return ;
						}
					},'json');
				}
			}
			$.messager.alert('提示','批量删除成功','info');
			$('#listfile').datagrid('reload');
			$("#win-waiting").window("close");
			
		}
	});	
}
//批量下载结果文件

function loadFile(){
	var cbks=$("[id^=filelist_]");	
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
		$.messager.alert('提示','请勾选需要下载的文件','info');
		return ;
	}
	$.messager.confirm('操作提示', '您确认要下载选中的'+cnt+'个文件?', function(r){
		if (r){
			var tids=" ";
			$("#win-waiting").window("open");
			for(var i=0;i<cbks.length;++i)
			{	
				if(cbks[i].checked==true)
				{
					
					var pos=(cbks[i].id).indexOf('_');
					var tid=(cbks[i].id).substr(pos+1,(cbks[i].id).length-pos-1);
					
					tids=tids+" "+tid;
				}
			}
		
			$.post(app_url+'/File/bat_download_file',{'guid':tids},function(data){
				if(data.info=="yes")
				{
					$('#win-waiting').window('close');
					
					var re=data.data;	
					
					window.location.href= app_url+"/File/download/sname/"+re+"/fname/"+"download.zip";
				}	
				else
				{
					$.messager.alert('错误',data.data,'error');
				}

		    },'json');
					
		
			
			//$.messager.alert('提示','批量下载成功','info');
			//$('#listdict').datagrid('reload');
			//$("#win-waiting").window("close");
			
		
			
		}
			
			//$.messager.alert('提示','批量下载成功','info');
			//$('#listdict').datagrid('reload');
			//$("#win-waiting").window("close");
			
		
	});
}
function loadTempFile(){
	var cbks=$("[id^=filelist_]");	
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
		$.messager.alert('提示','请勾选需要下载的文件','info');
		return ;
	}
	$.messager.confirm('操作提示', '您确认要下载选中的'+cnt+'个文件?', function(r){
		if (r){
			var tids=" ";
			$("#win-waiting").window("open");
			for(var i=0;i<cbks.length;++i)
			{	
				if(cbks[i].checked==true)
				{
					
					var pos=(cbks[i].id).indexOf('_');
					var tid=(cbks[i].id).substr(pos+1,(cbks[i].id).length-pos-1);
					
					tids=tids+" "+tid;
				}
			}
			$.post(app_url+'/Trans/bat_download_tempfile',{'guid':tids},function(data){
				if(data.info=="yes")
				{
					$('#win-waiting').window('close');
					
					var re=data.data;			
					window.location.href=app_url+"/Trans/download/sname/"+re+"/fname/"+"打包下载文件.zip";
				}	
				else
				{
					$.messager.alert('错误',data.data,'error');
				}

		    },'json');
			//$.messager.alert('提示','批量下载成功','info');
			//$('#listdict').datagrid('reload');
			//$("#win-waiting").window("close");
		}
		//$.messager.alert('提示','批量下载成功','info');
		//$('#listdict').datagrid('reload');
		//$("#win-waiting").window("close");
	});
}
function loadBilingualFile(){
	var cbks=$("[id^=filelist_]");	
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
		$.messager.alert('提示','请勾选需要下载的文件','info');
		return ;
	}
	$.messager.confirm('操作提示', '您确认要下载选中的'+cnt+'个文件?', function(r){
		if (r){
			var tids=" ";
			$("#win-waiting").window("open");
			for(var i=0;i<cbks.length;++i)
			{	
				if(cbks[i].checked==true)
				{
					
					var pos=(cbks[i].id).indexOf('_');
					var tid=(cbks[i].id).substr(pos+1,(cbks[i].id).length-pos-1);
					
					tids=tids+" "+tid;
				}
			}
			$.post(app_url+'/Trans/bat_download_bilingualfile',{'guid':tids},function(data){
				if(data.info=="yes")
				{
					$('#win-waiting').window('close');
					
					var re=data.data;			
					window.location.href=app_url+"/Trans/download/sname/"+re+"/fname/"+"打包下载文件.zip";
				}	
				else
				{
					$.messager.alert('错误',data.data,'error');
				}

		    },'json');
			//$.messager.alert('提示','批量下载成功','info');
			//$('#listdict').datagrid('reload');
			//$("#win-waiting").window("close");
		}
		//$.messager.alert('提示','批量下载成功','info');
		//$('#listdict').datagrid('reload');
		//$("#win-waiting").window("close");
	});
}
//show query dialog
function query()
{		
	ID("filename").value="";
	$("#type").combobox('setValue','');
	$("#direction").combobox('setValue','');
	$("#win-query").window("open");
	
}

function reload()
{
	trans_status_check = false;
	$("#listfile").datagrid("reload");
}

function viewall()
{
	 var str=app_url+"/Trans/listFile";
	 $('#listfile').datagrid({
         url:str
     });
}

//show the query result
function query_result()
{
	$("#win-query").window("close");
	var str=app_url+"/Trans/listFile";
	if(ID("filename").value!="")
		str=app_url+"/Trans/listFile/filename/"+ID("filename").value;
	var type=$("#type").combobox('getValue');
	if(type!="")
		str+="/type/"+type;
	var direction=$("#direction").combobox('getValue');
	if(direction!="")
		str+="/srclanguage/"+direction;
	 $('#listfile').datagrid({
            url:str
        });	
}




