// JavaScript Document

function modify_pass(username)
{	
   $("#ma_mod_pwd_close").show();
	/*$("#login_modal").modal('toggle');*/
   $("#login_modal").modal({
							'backdrop':true,
							'show':true,
							});
   document.getElementById('oldpass').value="";
      document.getElementById('newpass').value="";
	     document.getElementById('surepass').value="";
						
	   
}
function sureedit(usr)
{
	    if($('#newpass').val()=="")
		{
			$.messager.alert('提示','新密码不可为空','info');
			return false;
		}
		var valid=/^[A-Za-z0-9]+$/;
		if(!valid.test($('#newpass').val()))
		{
			$.messager.alert('提示','密码格式不正确，密码为6-12位字符或数字','info');
			return false;
		}
		if($('#newpass').val().length<6||$('#newpass').val().length>12)
		{
			$.messager.alert('提示','密码长度不正确，密码为6-12位字符或数字','info');
			return false;
		}
		
		if($('#newpass').val()==$('#surepass').val())
		{
	    $.post(app_url+'/User/updatePassword',{'username':usr,'oldpassword':$('#oldpass').val(),'password':$('#newpass').val()},function(data){
		if(data.info=="yes")
		{
			$.messager.alert('提示','密码修改成功','info');
			//$.messager.alert('提示','密码修改成功','info');
			location.href=app_url+"/Index/login";
		}
		else
		{
			$.messager.alert('错误',data.info,'error');
		}
    	
    },'json');
		}
		else
		{
			$.messager.alert('提示','两次输入密码不一致','info');
			return false;
		}
	
}