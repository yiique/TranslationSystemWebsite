<?php
class UserAction extends Action{
	
	function myAccount()
	{
	//	if(!isLogin())
	//	{
	//		$this->display("Login/login");
	//		return ;
	//	}
		/*$username=$_SESSION["username"];
		$dao=M("Useronline");
		$con["username"]=$username;
		$rs=$dao->where($con)->find();
		$this->assign("email",$rs["email"]);
		$this->assign("username",$username);
		$this->assign("truename",$rs["truename"]);*/
		if(Session::get('username')=="")
    	{
    		$this->redirect('login');
    		return;
    	}      
	    else if(Session::get('type')!="admin")
		{
		$this->display("myAccount");
		}
		else
		{
		$this->display("manage_admin");
		}
	}
/*显示账户信息*/	
	public function showUserAccount()
	{
		$u=	$_POST["username"];
		$dao=M();
		$queryline="select uo.username,ua.nickname,uo.password,uo.email,ua.emailverify,uo.sex,ua.selProvince,ua.selCity,ua.sign from useraccount ua,useronline uo where ua.username = uo.username and uo.username='".$u."'";
		
		$rs=$dao->query($queryline);
		$da=M();
		$q="select * from userattlanguage where username='".$u."'";
		$r=$da->query($q);
		
		if($rs ) //如果存在该用户
		{
			$this->ajaxReturn($rs,$r,1);
		}
		else
		{
			$this->ajaxReturn("no","no",0);	
		}
	}
	function get_info()
	{
		if($_SESSION["username"]=="")
			return ;
		$con["username"]=$_SESSION["username"];
		$dao=M("Useronline");
		$rs=$dao->where($con)->find();
		$this->ajaxReturn($rs,"yes",0);
	}
	function save_info()
	{
		//$this->ajaxReturn("yes","yes",0);
		if(!isset($_POST["username"]))
			return ;
		if($_POST["username"]!=$_SESSION["username"])
			return ;
		
		$dao=M("Useronline");
		$con["email"]=$_POST["email"];
		$rs_check=$dao->where($con)->find();
		if($rs_check)
		{
			if($rs_check["username"]!=$_POST["username"])
				$this->ajaxReturn("email","",1);
		}
		//$this->ajaxReturn("yes","yes",0);
		
		$con=array();
		$con["username"]=$_POST["username"];
		if(isset($_POST["truename"]))
			$data["truename"]=remove_xss($_POST["truename"]);
		if(isset($_POST["sex"]))
			$data["sex"]=remove_xss($_POST["sex"]);
		if(isset($_POST["job"]))
			$data["job"]=remove_xss($_POST["job"]);
		if(isset($_POST["language"]))
			$data["language"]=remove_xss($_POST["language"]);
		if(isset($_POST["goodat"]))
			$data["goodat"]=remove_xss($_POST["goodat"]);
		if(isset($_POST["signature"]))
			$data["signature"]=remove_xss($_POST["signature"]);
		$data["email"]=$_POST["email"];
		//$dao=M("Useronline");
		$rs=$dao->where($con)->save($data);
		$this->ajaxReturn("yes","yes",0);
	}
	function mod_pwd()
	{
		if($_SESSION["username"]=="")
			return ;
		$con["username"]=$_SESSION["username"];
		$con["password"]=md5($_POST["op"]);
		//$this->ajaxReturn($con["password"],"",1);
		$dao=M("Useronline");
		$rs=$dao->where($con)->find();
		if(!$rs)
			$this->ajaxReturn("wrongpassword","",1);
		$data["password"]=md5(remove_xss($_POST["np"]));
		$con=array();
		$con["username"]=$_SESSION["username"];
		$dao->where($con)->save($data);
		$this->ajaxReturn("yes","",0);
	}
	/*显示用户的擅长领域*/
	public function showUserdomain()
	{
		$u=$_POST["username"];
		$dao=M("userdomain");
		$con["username"]=$u;
		$list=$dao->where($con)->select();
		$this->assign("domainlist", $list);
		$this->display("myAccount");		
	}
	/*添加擅长领域*/
	public function adduserdomain()
	{
		$u=$_POST["username"];
		$ud=$_POST["u_domain"];
		$data=array();
		$data["username"]=$u;
		$data["u_domain"]=$ud;
		$con["username"]=$u;
		$dao=M("Userdomain");
		$count=$dao->where($con)->count();
		if($count<10)
		{
			$rs=$dao->getByU_domain($ud);
			if($rs){$this->ajaxReturn("用户已有该标签",$ud,0);}
			else
			{
				$id=$dao->add($data);
				if($id)
				{
					$this->ajaxReturn("yes","yes",1);
				}
				else
				{
					$this->ajaxReturn(0,"no",0);
				}
			}
		}
		else $this->ajaxReturn("用户最多可添加10个标签","no",0);
	}
	/*删除擅长领域*/
	public function deleteuserdomain()
	{
		$u=$_POST["username"];
		$ud=$_POST["u_domain"];
		$con["u_domain"]=$ud;
		$con["username"]=$u;
		$dao=M("Userdomain");
		$id=$dao->where($con)->delete();
		if($id)
			{$this->ajaxReturn("yes","yes",1);}
		else
			{$this->ajaxReturn(0,"no",0);}
	}
	/*自动搜索擅长领域*/
	public function searchdomain()
	{
		$v=$_POST["value"];
		$da=M();
		$q="select distinct u_domain from userdomain where u_domain like '%".$v."%' limit 10";
		$r=$da->query($q);
		/*$this->assign("domainlist", $list);
		$this->display("myAccount");*/
		if($r)
		{
			 $this->ajaxReturn($r,"yes",1);
		}
		else{
		 $this->ajaxReturn("no","no",0);
		 }
			
	}
	/*发验证邮件*/
	public function sendverifymail()
	{
		$email= trim($_POST['email']); //用户通过表单POST过来的email
		$u=	$_POST["username"];
		$p=	$_POST["password"];
		if($email=="")  
			$this->ajaxReturn("nomail","nomail",0);//邮件为空时
		else
		{
			vendor('PHPMailer.class#phpmailer');
			vendor('PHPMailer.class#smtp');
			
			$mail = new PHPMailer();
			$mail->IsSMTP();  
			$mail->CharSet='UTF-8'; 
			$mail->Encoding ="base64";                                  // set mailer to use SMTP   
			$mail->Host = "smtp.163.com";  // SMTP服务器   
			$mail->Port = 25;
			$mail->SMTPAuth = true;     // SMTP认证？   
			$mail->Username = "zhuanyijiaverify@163.com";  // 用户名   
			$mail->Password = "zhuanyijia163"; // 密码   
			$mail->From = "zhuanyijiaverify@163.com"; //发件人地址   
			$mail->FromName = "专译家"; //发件人   
			$mail->AddAddress($email,$u); //收件人地址，收件人名称   
			  
			$mail->WordWrap = 50;                                 //    
			//$mail->AddAttachment("/var/tmp/file.tar.gz");         // 附件   
			//$mail->AddAttachment("/tmp/image.jpg", "new.jpg");    // 附件,新文件名   
			$mail->IsHTML(true);
			                                  // HTML格式   
			$reg_num=md5($u);//生成随机码
			$dao=M("Useronline");
			$condition["username"]=$u;
			$data["reg_num"]=$reg_num;
			$id=$dao->where($condition)->save($data);//将随机码存入数据库
				
			$mail->Subject    = "专译家注册认证邮件";
			$mail->Body       = "<p>请点击下面的链接，验证您的邮箱：</p>
					<a href='http://10.28.0.169/patent_online/index.php/User/hasverifymail?reg_num=".$reg_num."&username=".$u."'>http://10.28.0.169/patent_online/index.php/User/myAccount?reg_num=".$reg_num."</a>";			   
			if(!$mail->Send())
			{
				$this->ajaxReturn("no111","no111",0);
			}else{
				$this->ajaxReturn("yes","yes",1);
			}
		}  
	
	}
	/*验证成功，改状态位*/
	public function hasverifymail()
	{
		$un=$_REQUEST["username"];
		$r=	$_REQUEST["reg_num"];
		$dao=M("Useronline");
		$rs=$dao->getByUsername($un);
		$condition["username"]=$un;
			$d=M("Useraccount");
			$data=array();
			$data["emailverify"]=1;
		if($rs["reg_num"]==$r)
		{	
			
			$id=$d->where($condition)->save($data);
			if($id)
				{$this->display("myAccount");}
			else
				{$this->ajaxReturn($id,"no1",0);}
		}
		else $this->ajaxReturn("no2","no2",0);
	}
	/*改用户密码*/
	public function verifypassword()
	{
		$u=$_POST["username"];
		$p=	$_POST["password"];
		$np=$_POST["inputconpass"];
		$condition["username"]=$u;
		$dao=M("Useronline");
		$rs=$dao->getByPassword(md5($p));
		if($rs ) //如果密码正确
		{
			$data=array();
			$data["password"]=md5($np);
			$condition["username"]=$u;
			$id=$dao->where($condition)->save($data);//保存修改后的密码
			if($id)
				$this->ajaxReturn("yes","yes",1);
			else
				$this->ajaxReturn("no1","no1",0);
		}
		else
		{
			$this->ajaxReturn("no","no",0);	
		}
	}
	/*保存修改后的账户UserAccount信息*/
	public function saveUserAccountInfo()
	{
		$data=array();
		$u=$_POST["username"];
		$n=$_POST["nickname"];
		$selProvince=$_POST["selProvince"];
		$selCity=$_POST["selCity"];		
		$sign=$_POST["sign"];
		$condition["username"]=$u;
		$data["nickname"]=$n;
		$data["selProvince"]=$selProvince;
		$data["selCity"]=$selCity;
		$data["sign"]=$sign;
		$dao=M("Useraccount");
		$id=$dao->where($condition)->save($data);
		if($id)
		{
			$this->ajaxReturn("账户信息保存成功","yes",1);
		}
		else 
		{
			$this->ajaxReturn($id,"no1",1);
		}
	}
	/*保存修改后的UserOnline账户信息*/
	public function saveUserOnlineInfo()
	{
		$u=$_POST["username"];
		/*$r=$_POST["realname"];*/
		$sex=$_POST["sex"];
		$e=$_POST["email"];
		$data=array();
		/*$data["realname"]=$r;*/
		$data["sex"]=$sex;
		$data["email"]=$e;
		$dao=M("Useronline");
		$condition["username"]=$u;
		$id=$dao->where($condition)->save($data);
		if($id)
		{
			$this->ajaxReturn("用户信息保存成功","yes",1);
		}
		else 
		{
			$this->ajaxReturn($id,"no2",1);
		}
	}
	/*保存修改后的关注语言*/
	public function saveUserLanguageInfo()
	{
		$l=$_POST["language"];
		$u=$_POST["username"];
		$a=array();
		$a = explode(",", $l,12);
		$dao=M("Userattlanguage");
		$data=array();
		$rs=$dao->getByUsername($u);
		if(!$rs){
			for ($i = 0; $i<count($a)-1;$i++) {
				$data["username"]=$u;
				$data["language"]=$a[$i];
				$id=$dao->add($data);
			}
			$this->ajaxReturn("添加关注语言成功","yes",1);
		}
		else
		{
			$con["username"]=$u;
			$del=$dao->where($con)->delete();
			if($del)
			{
				for ($i = 0; $i<count($a)-1;$i++) 
				{
				$data["username"]=$u;
				$data["language"]=$a[$i];
				$id=$dao->add($data);
				}
				$this->ajaxReturn("先删除后添加关注语言成功","yes",1);
			}
			else
			{
				$this->ajaxReturn("删除语言失败","nono",0);
			}
		}
	}
	/*传纸条userpaper*/
	public function sendpaper()
	{
		$u=$_POST["username"];
		$pc=$_POST["pcontent"];
		$dao=M("Userpaper");
		$con["username"]=$u;
		$countp=$dao->where($con)->count();
		if($countp>=6){$this->ajaxReturn("用户一天传送纸条数不超过6个..","no",2);}
		else {$data=array();
		$data["papercontent"]=$pc;
		$data["username"]=$u;
		$datetime=date("Y-m-d H:i");
		$data["sendtime"]=$datetime;
		$id=$dao->add($data);
		if($id)
		{
			$this->ajaxReturn("yes","yes",1);
		}
		else
		{
			$this->ajaxReturn("no","no",0);
		}
		}
	}
	/*接收纸条userpaper*/
	public function recvpaper()
	{
		$u=$_POST["username"];
		$dao=M();
		$queryline="select username,papercontent,sendtime from userpaper where username not like'".$u."'";
		$rs=$dao->query($queryline);
		
		$queryline="select language from userattlanguage where username not like'".$u."'";
		$rs1=$dao->query($queryline);
		
		
		if($rs)
		{
			$this->ajaxReturn($rs,$rs1,1);
		}
		else
		{
			$this->ajaxReturn("null","no",0);
		}
	}
	/*点击回复和查看时显示的发过来纸条的用户信息*/
	public function showrecv()
	{	
		$u=$_POST["username"];
		$dao=M();
		$queryline="select username,papercontent,sendtime from userpaper where username like'".$u."'";
		$rs=$dao->query($queryline);
		
		$queryline="select language from userattlanguage where username like'".$u."'";
		$rs1=$dao->query($queryline);
		
		
		if($rs)
		{
			$this->ajaxReturn($rs,$rs1,1);
		}
		else
		{
			$this->ajaxReturn("null","no",0);
		}
		
	}
	/*收纸条的回复*/
	public function sendRecvpaper()
	{
		$da=M("Userpaper");
		$con["username"]=$_POST["recvusername"];
		$countp=$da->where($con)->count();
		if($countp>=6){$this->ajaxReturn("用户一天传送纸条数不超过6个..","no",2);}
		else{
		$sendusername=$_POST["sendusername"];
		$sendcontent=$_POST["sendcontent"];
		$sendtime=$_POST["sendtime"];
		$recvusername=$_POST["recvusername"];
		$recvcontent=$_POST["recvcontent"];
		$datetime=date("Y-m-d H:i");
		$dao=M("paperinfo");
		$data=array();
		$data["sendusername"]=$sendusername;
		$data["sendcontent"]=$sendcontent;
		$data["sendtime"]=$sendtime;
		$data["recvusername"]=$recvusername;
		$data["recvcontent"]=$recvcontent;
		$data["recvtime"]=$datetime;
		$id=$dao->add($data);
		
		$d=array();
		$d["username"]=$recvusername;
		$d["papercontent"]=$recvcontent;
		$datet=date("Y-m-d H:i");
		$d["sendtime"]=$datet;
		$id2=$da->add($d);
		if($id&&$id2)
		{
			$this->ajaxReturn("yes","yes",1);
		}
		else
		{
			$this->ajaxReturn("no","no",0);
		}	}
	}
	/*发送的纸条列表*/
	public function sendpaperlist()
	{
		$u=$_POST["username"];
		$dao=M("Userpaper");
		$con["username"]=$u;
		$list=$dao->where($con)->select();
		$this->assign("list", $list);
		$this->display("interact");		
	}
	/*接收纸条列表*/
	public function recvpaperlist()
	{
		$recvusername=$_POST["recvusername"];
		$dao=M("Paperinfo");
		$con["recvusername"]=$recvusername;
		$list=$dao->where($con)->select();
		$this->assign("recvlist", $list);
		$this->display("interact");
	}
	/*显示用户收到的纸条，不超过6个*/
	public function shownewpaper()
	{
		$u=$_POST["username"];
		$dao=M();
		$queryline="select * from userpaper where username not like '".$u."' limit 6";
		$list=$dao->query($queryline);
		$this->assign("reclist", $list);
		$this->display("interact");
	}
	/*获得当前时间*/
	public function getNowDate()
	{
		$this->ajaxReturn(date("Y-m-d H:i:s"),"yes",1);
	}
	public function mytaskshow(){
		$u=$_POST["username"];
		$data=array();
		$dao=M("Useraccount");
		$con["username"]=$u;
		$rs=$dao->getByUsername($u);
		if($rs["emailverify"]==1)  $data["isemail"]=1;
		else $data["isemail"]=0;
		
		if($rs["selProvince"]&&$rs["selCity"]) $data["islocation"]=1;
		else $data["islocation"]=0;
		
		if($rs["sign"]) $data["issign"]=1;
		else $data["issign"]=0;
		
		$dao1=M("Userattlanguage");
		$rs1=$dao1->getByUsername($u);
		if($rs1) $data["islanguage"]=1;
		else $data["islanguage"]=0; 
		
		$dao2=M("Userdomain");
		$rs2=$dao2->getByUsername($u);
		if($rs2) $data["isdomain"]=1;
		else $data["isdomain"]=0; 
		
		$this->ajaxReturn($data,"yes",1);
		
		
	}
	public function expshow(){
		$u=$_POST["username"];
		$dao=M("Userdaydetailexp");
		$rs=$dao->getByUsername($u);
		if($rs) 
		{
			$total=$rs["daylogin"]+$rs["addentry"]+$rs["applyentry"]+$rs["appliedentry"]+$rs["suggestentry"]+$rs["intoplist"];
			$this->ajaxReturn($total,$rs,1);
		}
	}
	public function showact(){
		$u=$_POST["username"];
		$dao=M("Userdaydetailactive");
		$rs=$dao->getByUsername($u);
		if($rs) 
		{
			$total=$rs["daylogin"]+$rs["addentry"]+$rs["applyentry"]+$rs["appliedentry"]+$rs["suggestentry"]+$rs["intoplist"];
			$this->ajaxReturn($total,$rs,1);
		}
	}
	public function showranking(){
		$u=$_POST["username"];
		$dao=M("Useraccount");
		$rs=$dao->getByUsername($u);
		if($rs) 
		{
			$this->ajaxReturn("yes",$rs,1);
		}
	}
	

	
    //列出所有用户，返回json格式
    public function listUser()
    {
    	$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
		$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
		$offset = ($page-1)*$rows;
    	$dao=M("Userinfo");
    	$map["issystem"] = 0;
    	$count = $dao->where($map)->count();
    	$rs= $dao->where($map)->order('registertime desc')->limit($offset.','.$rows)->select();
        
	    $rows = array();
		if($count==0)
			$result["rows"] = $rows;
		else
			$result["rows"]=$rs;
    	$result["total"]=$count;
        
        echo json_encode($result);
    }
	//新建用户
	public function newUser()
	{		
		if(Session::get('type')!="admin")
		{
			return $this->ajaxReturn("no","您没有权限添加用户",1);
			return ;
		}
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
				$map['truename']=$map['username'];
				$map['isactive']=1;
				$map['issystem']=0;
				$map['type']=$_POST["type"];
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
	//删除用户
	function delUser()
	{
					
		$con["username"]=$_POST['username'];		
		$dao=M("Userinfo");
		$dao->where($con)->delete();
		return $this->ajaxReturn("yes","yes",1);
	}
	//检测用户是否存在
	function checkUser()
	{
		$con["username"]=$_POST["username"];
		$dao=M("Userinfo");
		$count=$dao->where($con)->count();
		//dump($count);
		if($count!=0)
		{
			return $this->ajaxReturn("no","no",1);
		}
		else
		{
			return $this->ajaxReturn("yes","yes",1);
		}
	}
	//更新用户信息
	function updateUser()
	{
		if(Session::get('type')!="admin")
		{
			return $this->ajaxReturn("no","您没有权限修改用户",1);
		}
		if(!empty($_POST['username']))
		{
			$User	=	M("Userinfo");
			$con['username']=$_POST['username'];
			if(!$User->where($con)->select())
			{
				return $this->ajaxReturn("no","用户不存在",1);
			}
			else
			{				
				$map['isactive']=$_POST["isactive"];				
				$map['type']=$_POST["type"];							
				if($re=$User->where($con)->save($map))
				{									
					return $this->ajaxReturn("yes","yes",1);								
					
				}
				else
				{
					return $this->ajaxReturn("no","更新失败",1);
				}
			}
			
		}
		else
		{
			return $this->ajaxReturn("no","请重新登录",1);
		}
	}
		//修改密码
	function updatePassword()
	{
		if(Session::get('type')!="admin"&&Session::get('username')!=$_POST['username'])
		{			
			return $this->ajaxReturn("no","您没有权限修改他人密码",1);		
			return ;
		}				
		if(isset($_POST['oldpassword']))
		{
			if(md5($_POST["oldpassword"])!=Session::get("password"))
			{				
				return $this->ajaxReturn("no","旧的密码不正确",1);		
			}
		}
		
		$con["username"]=$_POST["username"];
		$data["password"]=md5($_POST["password"]);
		$dao=M("Userinfo");
		$dao->where($con)->save($data);
		
		$this->error("yes");
	}
	//管理员插入翻译方向
	function insert_trandir()
	{
	//dump(Session::get('type'));
		if(Session::get('type')!="admin")
			return $this->ajaxReturn(Session::get('type'),"no",1);
		if(isset($_POST["srclan"]))
			$srclan=$_POST["srclan"];
		if(isset($_POST["tgtlan"]))
			$tgtlan=$_POST["tgtlan"];
		if(isset($_POST["dirinfo"]))
			$trandir=$_POST["dirinfo"];
		if(isset($_POST["isspace"]))
			$tran_style=$_POST["isspace"];
		if($trandir == "")
			$trandir=$srclan."->".$tgtlan;
		$con["srclanguage"]=$srclan;
		$con["tgtlanguage"]=$tgtlan;
		$con['inserttime']=date('Y-m-d G:i:s');
		$con["dirinfo"]=$trandir;
		$con["isspace"]=$tran_style;
		$con["type"]="ty";
		
		$Transdir=M('transdir');
		$re=$Transdir->add($con);
		if($re)
			return $this->ajaxReturn($tran_style,"yes",1);
		else
			return $this->ajaxReturn($tran_style,"no",1);
		
		
	}
	 //列出翻译方向列表
    public function listDir()
    {
    	$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
		$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
		$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'inserttime'; 
		$order = isset($_POST['order']) ? strval($_POST['order']) : 'desc'; 
		$offset = ($page-1)*$rows;
    	$dao=M("Transdir");
		$con["type"]="ty";
    	$count = $dao->where($con)->count();
    	$rs= $dao->where($con)->order($sort.' '.$order)->limit($offset.','.$rows)->select();
        
	    $rows = array();
		if($count==0)
			$result["rows"] = $rows;
		else
			$result["rows"]=$rs;
    	$result["total"]=$count;
        
        echo json_encode($result);
    }
	//保存修改的翻译方向列表
	function update_Dir()
	{
		if(Session::get('type')!="admin")
		{
			return $this->ajaxReturn("no","您没有权限修改翻译方向",1);
		}
		if(!empty($_POST['dirid']))
		{
			$Transdir=M("Transdir");
			//dump($Transdir);
			$con['dirid']=$_POST['dirid'];
			if(!$Transdir->where($con)->select())
			{
				return $this->ajaxReturn("no","更改方向不存在",1);
			}
			else
			{				
				$map['srclanguage']=$_POST["srclanguage"];				
				$map['tgtlanguage']=$_POST["tgtlanguage"];	
				$map['inserttime']=date('Y-m-d G:i:s');
				$map['dirinfo']=$_POST["dirinfo"];
				if($map['dirinfo'] == ""){
					$map['dirinfo']=$map['srclanguage']."->".$map['tgtlanguage'];
				}
				$map["isspace"]=$_POST["isspace"];
				if($re=$Transdir->where($con)->save($map))
				{									
					return $this->ajaxReturn("yes","yes",1);								
					
				}
				else
				{
					return $this->ajaxReturn("no","更新失败",1);
				}
			}
			
		}
		else
		{
			return $this->ajaxReturn("no","请重新登录",1);
		}
	}
	function deldir()
	{
		if(Session::get('type')!="admin")
		{
			return $this->ajaxReturn("no","您没有权限修改用户",1);
		}
		if(!empty($_POST['dirid']))
		{
			$Transdir	=	M("Transdir");
			//dump($Transdir);
			$con['dirid']=intval($_POST['dirid']);
			//dump($con['dirid']);
			if(!$Transdir->where($con)->delete())
			{
				return $this->ajaxReturn("no","更改方向不存在",1);
			}
			
			
		}
		else
		{
			return $this->ajaxReturn("no","请重新登录",1);
		}
	}
	
}
?>

