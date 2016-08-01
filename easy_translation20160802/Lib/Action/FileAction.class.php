<?php
	
	class FileAction extends Action
	{
	
	function listFile()
	{
	    if(Session::get('username')=="")
    	{
    		$this->redirect('login');
    		return;
    	}  	
		
		$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
		$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
	
		$offset = ($page-1)*$rows;
		$dao=M("Filetrans");
    	$map["isdeleted"]= 0;
        $map["username"]=Session::get("username");
        
        if(isset($_GET["filename"]))
        {
        	$map["filename"]= array('like','%'.$_GET["filename"].'%');
        }
        $collection = $dao->where($map);
		$count = $collection->count();
        // dump($map);
     	if(!isset($_POST["sortName"])){
			//->join('transdir ON filetrans.srclanguage = transdir.srclanguage and filetrans.tgtlanguage = transdir.tgtlanguage')
			$rs=$collection->order('subtime desc')->limit($offset.','.$rows)->select();
		}
        else{
			$rs=$collection->order($_POST["sortName"].' '.$_POST["sortOrder"])->limit($offset.','.$rows)->select();
		}
        //  dump($rs);
        $rows = array();
		if($count==0)
			$result["rows"]=$rows;
		else
			$result["rows"]=$rs;
    	$result["total"]=$count;
		// dump($list);
        echo json_encode($result);
	}
	
		//文件翻译登陆
/*		public function index(){
			$this->display("index");
		}
*/		
	
		//文件翻译
	public function tranFile()
	{	
		if(isset($_POST["type"]))
			$type=$_POST["type"];
		if(isset($_GET["username"]))
			$username=$_GET["username"];
		if (isset($_POST["PHPSESSID"])) {
			session_id($_POST["PHPSESSID"]);
		} else if (isset($_GET["PHPSESSID"])) {
			session_id($_GET["PHPSESSID"]);
		}
	
	    $_POST["type"]="ty";
		$socketType=$_POST["type"];
	

		$dirid=intval($_POST["dirid"]);
		$dao1=M("Transdir");
		$con["dirid"]=$dirid;
		
		$list=$dao1->where($con)->find();
		//$this->ajaxReturn($list["srclanguage"],"翻译服务器没有启动",1);
		$srclan=$list["srclanguage"];
		$tgtlan=$list["tgtlanguage"];
		$style=$list["isspace"];
			
		//session_start();
		$save_path = C("FILE_PATH");
		$upload_name = "Filedata";		
	    $file_info=pathinfo($_FILES[$upload_name]["name"]);
		//获取文件扩展名
		$file_ext=$file_info["extension"];		
		if($file_ext=="htm")
			$file_ext="html";
	   	//$file_name=mktime().".".$file_ext;//.$_FILES[$upload_name]["name"];	  
	   	//windows
	   	$guid=get_guid();
	   	//$file_name=$guid.".txt";	  
		$file_name=$guid.".".$file_ext;	  		
	   	if(!is_dir($save_path))
	   	{
	   		die(json_encode(array(0 => "1",1 => '上传路径不存在')));	   	
	   	}	    	
		if (!@move_uploaded_file($_FILES[$upload_name]["tmp_name"], $save_path.$file_name)) {			
			die(json_encode(array(0 => "1",1 => '上传路径不存在')));				
		}
		else
		{			
			//数据库操作
				
			$dao=M("Filetrans");
			$data=array(
			"username"=>$username,
			//Session::get("username"),
			"filename"=>$_FILES[$upload_name]["name"],
			"fileext"=>$file_ext,
			"subtime"=>date('Y-m-d G:i:s'),
			"isdeleted"=>0,
			"srclanguage"=>$srclan,
			"tgtlanguage"=>$tgtlan,
			"transtatus"=>"0%",
			"type"=>$_POST["type"],
			"guid"=>$guid,
			"srcname"=>$file_name
			);
			//echo "data:".$data;
			
			if($lastInsId = $dao->add($data))
			{
				//通信
				$socket=get_file_trans($username, $srclan,$tgtlan,$socketType,$guid,$style);						
		        die(json_encode(array(0 => '',1 => 'ok')));
		    }
		    else
		    {
		        //echo "插入数据失败";
		        $this->ajaxReturn("1".$lastInsId ,"数据库写入失败");
		        exit(0);
   			}			
			 die(json_encode(array(0 => '',1 => $_FILES[$upload_name]["filename"])));
			
		}
		die(json_encode(array(0 => '',1 => 'ok')));		
	}
	
		//下载文件
	function download_file()
	{
		$re=get_file_download($_POST["guid"]);
		$re=strip_tags($re);
		$array=explode(" ",$re);
		$this->ajaxReturn($array[count($array)-5],"yes",1);
	}	
		//单文件翻译
	function file_trans(){
			$fileID=$_GET["fileID"];
			$fID=$_GET["vname"];
			$fileName=$_GET["name"];
			if(isset($_GET["vers"]))
				$this->assign("vers_num","(第".$_GET["vers"]."版)");
			if($_GET["next"]==-1){
			
				$dao=M("Filetrans");
				//第一条和最后一条数据ID
				$first=$dao->min('tid');
				$last=$dao->max('tid');
				//$this->assign("f",$first);
				//$this->assign("l",$last);
				$con["guid"]=$fileID;
				$list=$dao->where($con)->find();
				
				$c["tid"]=$list["tid"]-1;
				$c["isdeleted"]=0;
				$c["username"]=$_SESSION["username"];
				//上一篇id不连续
				
				while(!$dao->where($c)->find()){
					$c["tid"]--;
				}
					
				$list=$dao->where($c)->find();
				$fileID=$list["guid"];
				$fileName=$list["filename"];//dump($dao);
				
			}else if($_GET["next"]==1){
				$dao=M("Filetrans");
				$con["guid"]=$fileID;
				$list=$dao->where($con)->find();
				
				$c["tid"]=$list["tid"]+1;
				$c["isdeleted"]=0;
				$c["username"]=$_SESSION["username"];
				//下一篇id不连续
				
				while(!$dao->where($c)->find()){
					$c["tid"]++;
				}
				$list=$dao->where($c)->find();
				$fileID=$list["guid"];
				$fileName=$list["filename"];//dump($dao);
			}
			$this->assign("filename",$fileName);
			$this->assign("fileID",$fileID);
			$this->assign("fID",$fID);
			
			if(isset($_GET["offset"])){
				$getTrans=post_file_trans($fileID,$_GET["offset"]);
				
				$getTrans=strstr($getTrans,"{");
				$obj=json_decode($getTrans);
				
				$array=$obj->{'result'};
				$state=$obj->{'state'};
				$errorCode=$obj->{'errorCode'};
				
				$this->ajaxReturn($array,$state,$errorCode);
			}
			else{
				$getTrans=post_file_trans($fileID, "0");
				$getTrans=strstr($getTrans,"{");
				$obj=json_decode($getTrans);
				
				$array=$obj->{'result'};
				$state=$obj->{'state'};
				
				
				$a=array();
				for($i=0;$i<count($array);$i++)
				{
					$a[$i]=array('id'=>$array[$i]->{'id'},'src'=>$array[$i]->{'src'},'tgt'=>str_ireplace("\\'","'",$array[$i]->{'tgt'}));
				}
				$do=M("Filetrans");
				$tr["username"]=$_SESSION["username"];
				$tr["isdeleted"]=0;
				$trans_n=$do->where($tr)->select();
				
				for($j=0;$j<count($trans_n);$j++){
					if($trans_n[$j]["filename"]==$fileName)
						$this->assign("f_num",$j+1);
				}
				
				$this->assign("t_num",count($trans_n));		//文件总数
				$this->assign("sent",$a);
				$this->assign("state",$state);
				$this->display("file_trans");
			}
		}
		
		//文件管理
		function file_admin(){
			$IDs=$_GET["fileIDs"];
			$IDs=explode(",",$IDs);
			
			$dao=M("Filetrans");
			if($_GET["isdel"]){
				$con["isdeleted"]=$_GET["isdel"];
				$con["username"]=$_SESSION["username"];
			}
			else{
				$con["isdeleted"]=0;
				$con["username"]=$_SESSION["username"];
			}
			
			$total=$dao->where($con)->count();
			$list = $dao->where($con)->select();
		

			//版本
			$ver=M("File_versions");
			$nver=M("File_versions");
			
			for($f=0;$f<count($list);$f++){
				$c["tid"]=$list[$f]["tid"];
				
				$cv["tid"]=$list[$f]["tid"];
				$cv["lastver"]=1;
				
				$vsn=$ver->where($c)->select();
				$lastv=$nver->where($cv)->find();
				//dump($nver);
				if($vsn){
					//id、版本号都需要
					for($i=0;$i<count($vsn);$i++){
						$li[$i]=$i+1;
						$list[$f]["vers"][$i]=array("1"=>$li[$i]);
					}
					$list[$f]["editstatus"]=$lastv["editstatus"];
					
				}
				else{
					$list[$f]["vers"][0]=array("1"=>1);
				}
				$list[$f]["lastver"]=$lastv["version"];
			}
			
			$this->assign("list", $list);
			$this->assign("all",$total);
			
			$this->display("file_admin");
		}
		
		//文件上传
		function file_upload(){
		if(isset($_POST["type"]))
			$type=$_POST["type"];
		/*if(isset($_GET["username"]))
			$username=$_GET["username"];*/
		if(isset($_POST["username"]))
			$username=$_POST["username"];
		
		Session::set('username',$username);
		//$username=Session::is_set('username');
		
		$socketType=$_POST["type"];
		
		$save_path = C("FILE_PATH");
		$upload_name = "Filedata";
	    $file_info=pathinfo($_FILES[$upload_name]["name"]);
		
		//获取文件扩展名
		$file_ext=$file_info["extension"];
		if($file_ext=="htm")
			$file_ext="html";
	   	$file_name=get_guid().".".$file_ext;//.$_FILES[$upload_name]["name"];
	   
	   	if(!is_dir($save_path))
	   	{
	   		die(json_encode(array(0 => "1",1 => '上传路径不存在')));
	   	}
		if (!@move_uploaded_file($_FILES[$upload_name]["tmp_name"], $save_path.$file_name)) {
			die(json_encode(array(0 => "1",1 => '上传路径不存在')));
		}
		else
		{
			
			$fileUrl="http://10.28.0.169/patent_online/Public/Upload/files/".$file_name;
				//通信
				$socket=post_file($username, $socketType,$fileUrl);
				
				//检测报文长度是否完整		
				$f_p=strpos( $socket,":"); 
				if(!$f_p)
					$this->ajaxReturn("报文错误","no",0);		
				$e_p=strpos( $socket,"Content-Length:");		
				if(!$e_p)
					$this->ajaxReturn("报文错误","no",0);
				$len=substr( $socket,$f_p+1,$e_p-1-$f_p);		
				 $socket=strstr( $socket,"{");
				if($len!=strlen( $socket))
					$this->ajaxReturn("报文错误","no",0);
					
				$obj=json_decode($socket);
				$arr=$obj->{'fileID'};
				$err=$obj->{'errorCode'};
				
				$f_oid=json_encode($arr[0]);
				$f_id=substr($f_oid,strpos( $f_oid,",\"")+2,36);
				
				//数据库操作--online_file
				$dao=M("Filetrans");
				$cond["username"]=$username;
				$cond["filename"]=$_FILES[$upload_name]["name"];
				$cond["isdeleted"]=0;
				$dr=$dao->where($cond)->find();
				if(!$dr){
					$data=array(
						"username"=>$username,
						"filename"=>$_FILES[$upload_name]["name"],
						"fileext"=>$file_ext,
						"subtime"=>date('Y-m-d'),
						"isdeleted"=>0,
						"transtatus"=>"100%",
						"domain"=>$_POST["type"],
						"guid"=>$f_id
					);
					$dao->add($data);
					$v_con["guid"]=$f_id;
					$v_id=$dao->where($v_con)->find();
					
					$ver=M("File_versions");
					$fdata=array(
					"tid"=>$v_id["tid"],
					"username"=>$username,
					"filename"=>$_FILES[$upload_name]["name"],
					"fileID"=>$f_id,
					"oldID"=>$f_id,
					"date"=>date('Y-m-d G:i:s'),
					"version"=>1,
					"lastver"=>1
					);
					if(!$ver->add($fdata))
					{
						die(json_encode(array(0 =>"数据库写入失败",1 =>1)));
						exit(0);
					}
					else
						die(json_encode(array(0 => $f_id,1 =>0)));
				}
				else{
					//数据库操作--online_version
					//$do=M("Filetrans");
					//$v_con["guid"]=$f_id;
					//$v_id=$do->where($v_con)->find();//dump($dao);
					
					$v_cond["tid"]=$dr["tid"];
					$v_cond["isdeleted"]=0;
					
					$ver=M("File_versions");
					$r=$ver->where($v_cond)->select();//dump($ver);
					$vr["lastver"]=0;
						
					$v=count($r)+1;					//最新版本号
					$ver->where($v_cond)->save($vr);		//之前版本lastver设为0
					
					$fdata=array(
						"tid"=>$dr["tid"],
						"username"=>$username,
						"filename"=>$_FILES[$upload_name]["name"],
						"fileID"=>$f_id,
						"oldID"=>$dr["guid"],
						"date"=>date('Y-m-d G:i:s'),
						"version"=>$v,
						"lastver"=>1
					);
					if(!$ver->add($fdata))
					{
						die(json_encode(array(0 =>"数据库写入失败",1 =>1)));
						exit(0);
					}
					else
						die(json_encode(array(0 => $f_id,1 =>0)));
				}
			}
			
		}
		
		//删除文件
		function file_del(){
			/*
			$files=$_POST["files"];
			$f=M("Filetrans");
			$fd=M("File_versions");
			
			for($i=0;$i<count($files);$i++){
				$con['guid']=$files[$i];
				$rest=$f->where($con)->find();
				$cond["tid"]=$rest["tid"];
				$fd->where($cond)->setField('isdeleted',1);		//删除该文件所有版本
				$f->where($con)->setField('isdeleted',1);		//删除该文件
			}*/
			if(!isset($_POST["tid"]))
			{
				$this->ajaxReturn("no","文件不存在",1);	
			}
				
			$con["tid"]=$_POST["tid"];
			//$con["username"]=Session::get("username");
			$data["isdeleted"]=1;
			$dao=M("Filetrans");
			$dao->where($con)->save($data);
			$this->ajaxReturn('yes',"yes",1);	
		}
		
		//清空
		function file_clr(){
			$files=$_POST["files"];
			$f=M("Filetrans");
			$fd=M("File_versions");
			
			for($i=0;$i<count($files);$i++){
				$con['guid']=$files[$i];
				$res=$f->where($con)->find();
				//$this->ajaxReturn($rest["filename"],$rest["username"],0);//select()结果集为二维数组，find()结果集为一维数组
				$cond["tid"]=$res["tid"];
				$fd->where($cond)->delete();		//清空该文件所有版本
				$f->where($con)->delete();//dump($fd);
			}
		}
		//隐藏
		/*function file_hid(){
			$files=$_POST["files"];
			$f=M("Filetrans");
			for($i=0;$i<count($files);$i++){
				$con['guid']=$files[$i];
				$f->where($con)->setField('action2',1);//dump($f);
			}
		}*/
		
		//恢复
		function file_recover(){
			$files=$_POST["files"];
			$f=M("Filetrans");
			$fd=M("File_versions");
			
			for($i=0;$i<count($files);$i++){
				$con['guid']=$files[$i];
				$res=$f->where($con)->find();
				$cond["tid"]=$res["tid"];
				$fd->where($cond)->setField('isdeleted',0);
				$f->where($con)->setField('isdeleted',0);//dump($f);
			}
		}
		
		//获取结果文件
		function file_result(){
			$fileID=$_POST["fileID"];
			$re=post_file_result($fileID);
			
			$f_p=strpos($re,":"); 
			if(!$f_p)
				$this->ajaxReturn("报文错误","no",1);		
			$e_p=strpos($re,"Content-Length:");		
			if(!$e_p)
				$this->ajaxReturn("报文错误","no",1);
			$len=substr($re,$f_p+1,$e_p-1-$f_p);		
			$re=strstr($re,"{");
			if($len!=strlen($re))
				$this->ajaxReturn("报文错误","no",1);
			
			$f=json_decode($re);
			$fileurl=$f->{"fileUrl"};
			$state=$f->{"errorCode"};
			
			
			if($fileID==""){
				$this->redirect("index","Index",1); 
			}
			$this->ajaxReturn(base64_encode($fileurl),$fileurl,0);
			
		}
		
		//获取上次位置
		function get_position(){
			$f["fileID"]=$_POST["file"];
			$s_p=M("File_versions");
			$r=$s_p->where($f)->find();//dump($s_p);
			if($r)
				$this->ajaxReturn($r["position"],"yes",1);
			else
				$this->ajaxReturn("查找失败","no",0);
		}
		
		//记录上次位置
		function save_position(){
			$pos=$_POST["con"].":".$_POST["drag"].":".$_POST["index"];
			$f["fileID"]=$_POST["file"];
			$cd["lastver"]=0;
			
			$s_p=M("File_versions");
			$cv=$s_p->where($f)->find();
			$c["tid"]=$cv["tid"];
			$cr=$s_p->where($c)->save($cd);//dump($s_p);
			
			$data['position']=$pos;
			$data['editstatus']=$_POST["edit"];
			$data["lastver"]=1;
			
			$r=$s_p->where($f)->save($data);//dump($s_p);
			if($r&&$cr)
				$this->ajaxReturn($r,"yes",0);
			else if(!$r)
				$this->ajaxReturn("历史位置保存失败！","no",1);
			else if(!$cr)
				$this->ajaxReturn("版本信息保存失败！","no",1);
			else
				$this->ajaxReturn("数据库操作失败！","no",1);
		}
		
		//完成编辑
		function edit_done(){
			$f["fileID"]=$_POST["id"];
			
			$s_p=M("File_versions");
			$data['editstatus']="100%";
			$r=$s_p->where($f)->save($data);//dump($s_p);
			if($r)
				$this->ajaxReturn($r,"yes",0);
			else{
				$f=$s_p->where($f)->find();
				if($f['editstatus']=="100%")
					$this->ajaxReturn(1,"yes",0);
				else
					$this->ajaxReturn("修改失败","no",1);
			}
		}
		
		//发送邮件
		public function send_mail()
		{
		//$email= trim($_POST['email']); //用户通过表单POST过来的email
		
		//获取邮件地址
		$u=	$_POST["username"];
		$user=M("Useronline");
		$fd["username"]=$u;
		$rst=$user->where($fd)->find();
		
		//获取翻译结果文件
		$sfile=post_file_result($_POST["fileID"]);
		$sfile=strstr($sfile,"{");
		$sf=json_decode($sfile);
		$furl=$sf->{'fileUrl'};

		if(!$rst)  
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
			$mail->AddAddress($rst["email"],$u); //收件人地址，收件人名称   
			  
			//$mail->WordWrap = 50;                                 //自动分行    
			//$mail->AddAttachment($furl);         // 附件   
			//$mail->AddAttachment("/tmp/image.jpg", "new.jpg");    // 附件,新文件名   
			$mail->IsHTML(true);
			                                  // HTML格式   
			/*$reg_num=md5($u);//生成随机码
			$dao=M("Useronline");
			$condition["username"]=$u;
			$data["reg_num"]=$reg_num;
			$id=$dao->where($condition)->save($data);//将随机码存入数据库*/
				
			$mail->Subject    = "专译家翻译结果文件";
			$mail->Body       = "感谢您使用专译家！以下是您翻译的结果文件，请点击获取：<br/>".$furl;			   
			if(!$mail->Send())
			{
				$this->ajaxReturn($mail->ErrorInfo,"fail",0);
			}else{
				$this->ajaxReturn("邮件发送成功",$furl,1);
			}
		}  
	
	}
		
		//获取翻译进度
		function file_state(){
			$fileID=$_POST["fileID"];
			$re=post_file_state($fileID);
			
			/*$f_p=strpos($re,":"); 
			if(!$f_p)
				$this->ajaxReturn("报文错误","no",0);		
			$e_p=strpos($re,"Content-Length:");		
			if(!$e_p)
				$this->ajaxReturn("报文错误","no",0);
			$len=substr($re,$f_p+1,$e_p-1-$f_p);	*/	
			$re=strstr($re,"{");
			//if($len!=strlen($re))
			//	$this->ajaxReturn("报文错误","no",0);
			$sub=json_decode($re);
			$status=$sub->{'errorCode'};
			$this->ajaxReturn($sub,$furl,$status);
		}
		
		/*//下载
		function file_url(){
			$fileUrl=$_POST["fileUrl"];
			$f=strstr($fileUrl,"10010/");
			get_file_url($f);
		}*/
		
		//保存单句
		function save_sent(){
			$sentID=$_POST["sentID"];
			$tgt=addcslashes($_POST["tgt"],"'");  //单引号解析
			$re=post_save_sent($sentID,$tgt);
			$re=strstr($re,"{");
			$sub=json_decode($re);
			$status=$sub->{'errorCode'};
			$this->ajaxReturn($re,"yes",$status);
		}
		
		//保存版本
		function save_version(){
			$version=$_POST["vers"];
			$ftid=$_POST["tid"];
			if(!$_POST["vers"])
				$this->ajaxReturn("noinfo","no",0);
			else{
				$oldId=$_POST["oldId"];
				$sents=array();
				$t=array();
				for($v=0;$v<count($version);$v++)
				{
					$sents[$v]=$version[$v][0];
					$t[$v]=$version[$v][1];
				}
				$ret=post_save_version($oldId,$sents,$t);
				$ret=strstr($ret,"{");
				
				//写入数据库
				$subre=json_decode($ret);
				$state=$subre->{'errorCode'};
				if($state=="0"){
					$newID=$subre->{'newfileID'};
					
					$newdata=M("File_versions");
					
					$cond["tid"]=$ftid;
					$cn["lastver"]=0;
					$newdata->where($cond)->save($cn);//dump($newdata);
					$r=$newdata->where($cond)->select();
					if($r)
						$v=count($r)+1;
					else
						$v=1;
					$new=array(
						"tid"=>$ftid,
						"username"=>$_SESSION["username"],
						"filename"=>$_POST["name"],
						"fileID"=>$newID,
						"oldID"=>$oldId,
						"date"=>date('Y-m-d G:i:s'),
						"version"=>$v,
						"lastver"=>1
					);
					if($newdata->add($new)){
						$this->ajaxReturn($ret,"yes",0);}
					else{
						$this->ajaxReturn($ret,"插入数据库失败！",1);
					}
				}
				else
					$this->ajaxReturn($ret,"no",$state);
			}
		}
		
		//统计信息
		function trans_statistics(){
			$fileID=$_POST["fileID"];
			$info=do_statistics($fileID);
			$info=strstr($info,"{");
			$inf=json_decode($info);
			$state=$inf->{"errorCode"};
			if($state==0)
				$info=$inf->{"result"};
			$this->ajaxReturn($info,"ok",$state);
		}
		
		//获取tid
		function get_tt(){
			$file["fileID"]=$_POST["fid"];
			$f=M("File_versions");
			$finfo=$f->where($file)->find();
			if(!$finfo){
				//$this->ajaxReturn("版本信息丢失，您可以进入文件管理获取版本信息！","no",1);
				die(json_encode(array(0 => "1",1 => "版本信息丢失，您可以进入文件管理获取版本信息！")));
			}
			else{
				die(json_encode(array(0 => "0",1 =>$finfo["tid"],"2"=>$finfo["version"])));
			}
		}
		//获取对应版本
		function get_the_vers(){
			$ftid=$_POST["ver_name"];
			$vers=$_POST["ver_n"];
			$username=$_SESSION["username"];
			
			$db=M("File_versions");
			$con["tid"]=$ftid;
			$con["version"]=$vers;
			
			$data=$db->where($con)->find();
			if($data)
				$this->ajaxReturn($data["fileID"],"查找成功",0);
			else
				$this->ajaxReturn("查找失败","fail",1);
		}
		//添加标签
		function do_add_pagetags(){
			$tag=$_POST["tags"];
			$fid=$_POST["fid"];
			$re=add_page_tags($fid,$tag);
			if($re){
				$re=strstr($re,"{");
				$sub=json_decode($re);
				$status=$sub->{'errorCode'};
				$this->ajaxReturn("yes",$re,$status);
			}
			else{
				$this->ajaxReturn("no","no",1);
			}
			
		}
		//预览
		function trans_preview(){
			$prev=$_POST["file"];
			$re=get_preview($prev);
			$re=strstr($re,"{");
			$sub=json_decode($re);
			$status=$sub->{'errorCode'};
			$this->ajaxReturn(nl2br($sub->{'preview'}),"yes",$status);
		}
		
		//原文摘要
		function file_abstract(){
			$abstr=$_POST["id"];
			$re=get_abstract($abstr);
			$re=strstr($re,"{");
			$subre=json_decode($re);
			$status=$subre->{'errorCode'};
			$this->ajaxReturn($subre,"yes",$status);
		}
		//下载文件函数，sname为文件路径，fname为对话框中显示保存的文件名字
		function download()
		{
			/*if(Session::get('username')=="")
	    	{
	    		$this->redirect('Login/');
	    		return;
	    	}
			//sname:保存位置
			//fname:显示下载名称
			if(isset($_GET["sname"]))
				$sname=$_GET["sname"];
			else if(isset($_POST["sname"]))
				$sname=$_POST["sname"];
			if(isset($_GET["fname"]))
				$fname=$_GET["fname"];
			else if(isset($_POST["fname"]))
				$fname=$_POST["fname"];			
			import("@.ORG.Http");
			Http::download(base64_decode($sname),$fname);
			*/
			
			//sname:保存位置
		//fname:显示下载名称
		
		
		if(isset($_GET["sname"]))
			$sname=$_GET["sname"];
		else if(isset($_POST["sname"]))
			$sname=$_POST["sname"];
		if(isset($_GET["fname"]))
			$fname=urlencode(htmlspecialchars(urldecode($_GET["fname"])));
		else if(isset($_POST["fname"]))
			$fname=$_POST["fname"];
		if(is_file("Public/Upload/".$sname))
		{
			import("@.ORG.Http");
		//	Http::download("Public/Upload/".$sname,$fname);
			Http::download( "Public/Upload/".$sname,$fname);
		}
		else if(is_file(C("FILE_PATH").$sname))
		{
			import("@.ORG.Http");
		//	Http::download("Public/Upload/".$sname,$fname);
			Http::download( C("FILE_PATH").$sname,$fname);
		}
		else if(is_file(C("EVAL_PATH").$sname))
		{
			import("@.ORG.Http");
		//	Http::download("Public/Upload/".$sname,$fname);
			Http::download( C("EVAL_PATH").$sname,$fname);
		}
		else if(is_file(C("ALIGN_FILE_PATH").$sname))
		{
			import("@.ORG.Http");
		//	Http::download("Public/Upload/".$sname,$fname);
			Http::download( C("ALIGN_FILE_PATH").$sname,$fname);
		}
		
		}
	
	//打包下载
	function zip_download()
		{
			$arr=$_POST["fileID"];
			$name=$_POST["name"];
			require_once 'PHPZip.class.php';
		
			//先创建一个文件夹
			$save_path = "Public/Upload/";
			$guid=get_guid(); //"翻一下文件打包下载";
			$dir_name=$save_path.$guid;  
			mkdir($dir_name,0777);	   
			$save_path=$save_path.$guid."/";
			
			//结果文件写入新的文件
			for($i=0;$i<count($arr);$i++)
			{
				$filename=$name[$i];
				$filename=mb_convert_encoding($filename, "GBK", "UTF-8"); 
				
				//写入文件
				$re=post_file_result($arr[$i]);
				$re=strstr($re,"{");
				$subre=json_decode($re);
				$file=$subre->{'fileUrl'};
				
				$ref=file_get_contents($file);
				
				$new=fopen($save_path.$filename,"w");
				fwrite($new,$ref);
				fclose($new);
				
			}
			//生成压缩文件
			$zip=new PclZip("Public/Upload/".$guid.'.zip');	
			
			//打包并且移除压缩包内的文件夹嵌套结构，将文件移到第一层目录
			$v_list = $zip->create($dir_name,PCLZIP_OPT_REMOVE_PATH, 'Public/Upload/'.$guid);
			
			//删除临时文件
			rmdir($dir_name);
			$this->ajaxReturn($guid,"yes",0);
					//$this->ajaxReturn($re,"no",1); 	
	}
 //批量下载选中文件
 
	function bat_download_file()
	{
		if(Session::get('username')=="")
    	{
    		$this->redirect('Index/timeout');
    		return;
    	}
    	//require_once 'PHPZip.class.php';
		$str=trim($_POST["guid"]);
		$arr=split(" ",$str);
		//$this->ajaxReturn($arr," ",1);		
      
		require_once 'PHPZip.class.php';		

		//先创建一个文件夹
		$save_path = "Public/Upload/";
		$guid=get_guid();	
	   	$dir_name=$save_path.$guid;  
	   	mkdir($dir_name,0777);	   
	   	$save_path=$save_path.$guid."/";
	   	//copy到文件
    	for($i=0;$i<count($arr);++$i)
    	{
    		$con["tid"]=intval($arr[$i]);
    		$dao=M("Filetrans");
    		$res=$dao->where($con)->find();
    		//$this->ajaxReturn($res,"no",1);
    		//$this->ajaxReturn($res,"no",1); 
			$file_name=$res["filename"];
			//$this->ajaxReturn($file_name,"no",1);
    		$file_name=mb_convert_encoding($file_name, "GBK", "UTF-8"); 
    		
    		if($res["transstate"]=="FINISH"){
    			
		  
    			$re=get_file_download($res["guid"]);
				
				$sp=strpos($re,"<ResultFileName>");
				$ep=strpos($re,"</ResultFileName>");
				$result=substr($re,$sp+16,$ep-$sp-16);
    			
    			
    			//取出扩展名
    			$extpos=strrpos($result,".");
    			$result_ext=substr($result,$extpos+1,strlen($result)-$extpos-1);
    			copy(C("FILE_PATH").$result,$save_path.$file_name."_".$res["type"]."_".$res["srclanguage"]."-".$res["tgtlanguage"]."_result.".$result_ext);
		
				
			
    		}
    	}
    	//生成压缩文件
    	$zip=new PclZip("Public/Upload/".$guid.'.zip');		
    	//打包并且移除压缩包内的文件夹嵌套结构，将文件移到第一层目录
	 	$v_list = $zip->create($dir_name,PCLZIP_OPT_REMOVE_PATH, 'Public/Upload/'.$guid);
	 	//删除临时文件
	 	rmdir($dir_name); 	 
	 	$this->ajaxReturn($guid.'.zip',"yes",1); 
	 		
	 	//移到上传文件目录	 	
	 	//copy($save_path.'trabsfile.zip',C("FILE_PATH").$guid.'.zip');
	 	
    	//$this->ajaxReturn($guid.'.zip',"yes",1); 	
	}	
}
	
?>