<?php
// 本文档自动生成，仅供测试运行
class IndexAction extends Action
{
    /**
    +----------------------------------------------------------
    * 默认操作
    +----------------------------------------------------------
    */
    public function index()
    {
        if(Session::get('username')=="")
    	{
    		$this->display('login');
    	}
    	else
    	{
    		$this->assign("username",Session::get('username'));
    		$this->assign("password",md5(Session::get('password')));
    		$this->assign("type",Session::get('type'));
    		$this->display('index');
    	}
    }

    //检测登陆是否正确
	public function checkLogin()
	{		
		if(!empty($_POST['username']))
		{
			if($_POST['ischecked']=="1")
				Cookie::set('username',$_POST['username']);
			else 				
				Cookie::clear('username');

			$User=M("Userinfo");
			$map['username']=$_POST['username'];
			
			if(!$User->where($map)->select())
			{
				$this->error("用户名不存在");
			}
			else
			{
				$map['password']=md5($_POST['password']);
				if($re=$User->where($map)->select())
				{
					if($re[0]['isactive']==0)
					{
						$this->error('用户未激活!');
					}
					else
					{
						if($_POST['ischecked']=="1")
							Cookie::set('password',$_POST['password']);
						else 
							Cookie::clear('password');
						Session::set('username',$_POST['username']);
						Session::set('password',md5($_POST['password']));
						Session::set('type',$re[0]['type']);
						Session::set('isactive',$re[0]['isactive']);
						Session::set('others',md5($re[0]['issystem']));
						$this->success('yes');
					}					
				}
				else
				{
					$this->error('密码错误!');
				}
			}
		}
		else
		{
			$this->error('用户名不能为空');
		}
	}
	
	//注册用户
	public function registerUser()
	{		
		if(!empty($_POST['username']))
		{
			$User	=	M("Userinfo");
			$map['username']=$_POST['username'];
			if($User->where($map)->select())
			{
				return $this->ajaxReturn("no","用户名已经存在",1);
			}
			else
			{
				$map['password']=md5($_POST['password']);
				if($_POST['truename']!="")
					$map['truename']=$_POST['truename'];
				else 
					$map['truename']=$_POST['username'];
				$map['isactive']=0;
				$map['issystem']=0;
				$map['type']='normal';
				$map['registertime']=date('Y-m-d G:i:s');	
							
				if($re=$User->add($map))
				{									
					return $this->ajaxReturn("yes","yes",1);									
					
				}
				else
				{
					return $this->ajaxReturn("no","添加失败",1);
				}
			}
			
		}
		else
		{
			return $this->ajaxReturn("yes","用户名不能为空",1);
		}
	}

	//选择翻译方向
	function listTranDir()
	{
		$dao=M("Transdir");
		$lists=$dao->select();
		//dump($lists);
		echo json_encode($lists);
		
	}
	
	function listUser()
	{
		$dao=M("Userinfo");
		$con["issystem"]=0;
		$lists=$dao->where($con)->select();
		//dump($lists);
		echo json_encode($lists);
		
	}
	
	function logout()
	{
		Session::destroy();
		$this->redirect('index');
	}
	
    /**
    +----------------------------------------------------------
    * 探针模式
    +----------------------------------------------------------
    */
    public function checkEnv()
    {
        load('pointer',THINK_PATH.'/Tpl/Autoindex');//载入探针函数
        $env_table = check_env();//根据当前函数获取当前环境
        echo $env_table;
    }

}
?>