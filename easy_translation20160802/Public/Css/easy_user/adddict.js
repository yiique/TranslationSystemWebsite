// JavaScript Document

function add_dict() {

    if($('#dictname').validatebox('isValid')==false)
	{
		$('#dictname').validatebox('validate');
		return ;
	}
	if($('#type').combobox('isValid')==false)
	{
		$('#type').combobox('validate');
		return ;
	}
	if($('#direction').combobox('isValid')==false)
	{
		$('#direction').combobox('validate');
		return ;
	}
	
	var dictname=$("#dictname").val();
	var type=$("#type").val('getValue');
	var direction=$("#direction").combobox('getValue');
	
	$.post(app_url+'/Dict/updateDict',{'dictname':dictname,'type':type,'srclanguage':srclanguage,'isactive':isactive,'description':description,'issystem':issystem},function(data){
		if(data.info=="yes")
		{
			$.messager.alert('提示','添加成功','info');
			$('#listdict').datagrid('reload');
    		$("#win-adddict").window("close");
    		
		}
		else
		{
			$.messager.alert('错误',data.info,'error');			
		}

    },'json');
	$("#adddict").modal("hide");
}
}
