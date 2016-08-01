<?php
class DictAction extends Action{
	
	//显示导入词典列表，不包括系统词典返回jason格式
	function listImportDict()
	{
		if(Session::get('username')=="")
    	{
    		$this->redirect('login');
    		return;
    	}
		$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
		$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
		$offset = ($page-1)*$rows;
		$dao=M("Dictinfo");
    	$map["isdeleted"]= 0;
        $map["username"]=Session::get("username");
        $map["issystem"]=array('neq',1);
        $count = $dao->where($map)->count();
        $rs=$dao->where($map)->order('createtime desc')->limit($offset.','.$rows)->select();
        $rows = array();
		if($count==0)
			$result["rows"]=$rows;
		else
			$result["rows"] = $rs;
    	$result["total"]=$count;
       // dump($list);
        echo json_encode($result);
	}
	//返回所有词典列表，用于combobox
	function get_dict_list()
	{
		if(Session::get('username')=="")
    	{
    		$this->redirect('login');
    		return;
    	}
		$dao=M("Dictinfo");
		$con["username"]=$_SESSION["username"];
		$con["issystem"]=array('neq',1);
		$con["isdeleted"]=0;
		$rs=$dao->join("lanlist on lanlist.lanname=srclanguage")
		->join("typelist on typelist.typename=type")
		->where($con)->order("createtime desc")->select();
		echo json_encode($rs);
	}
	//返回所有词典列表，用于combobox
	function get_dict_list_eng()
	{
		if(Session::get('username')=="")
    	{
    		$this->redirect('login');
    		return;
    	}
		$dao=M("Dictinfo");
		$con["username"]=$_SESSION["username"];
		$con["issystem"]=0;
		$con["isdeleted"]=0;
		$rs=$dao->join("lanlist_eng on lanlist_eng.lanname=srclanguage")
		->join("typelist_eng on typelist_eng.typename=type")
		->where($con)->order("createtime desc")->select();
		echo json_encode($rs);
	}
	//显示用户词条列表
	function list_user_item()
	{
		if(Session::get('username')=="")
    	{
    		$this->redirect('login');
    		return;
    	}
		$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
		$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
		$offset = ($page-1)*$rows;
		$dao=D("Dictiteminfo");
    	
    	if(Session::get('type')=="admin")
    		$con="dictiteminfo.isdeleted=0 and dictinfo.isdeleted=0";
	   	else
	    	$con="dictiteminfo.isdeleted=0 and dictinfo.isdeleted=0 and dictinfo.username='".$_SESSION["username"]."'";
	    $_SESSION["redict_key"]="";
	    $_SESSION["redict_type"]="";
	    $_SESSION["redict_lan"]="";
	    $_SESSION["redict_time"]="";
	    if(isset($_GET["key"]))
        {
        	$con=$con." and (src like '%".$_GET['key']."%' or tgt like '%".$_GET['key']."%')";
        	 $_SESSION["redict_key"]=" and (src like '%".$_GET['key']."%' or tgt like '%".$_GET['key']."%')";
        }
    	if(isset($_GET["type"]))
    	{
    		$con=$con." and type='".$_GET["type"]."'";
    		$_SESSION["redict_type"]=" and type='".$_GET["type"]."'";
    	}	
		if(isset($_GET["srclanguage"]))
    	{
    		$con=$con." and srclanguage='".$_GET["srclanguage"]."'";
    		$_SESSION["redict_lan"]=" and srclanguage='".$_GET["srclanguage"]."'";
    	}
		if(isset($_GET["createtime"]))
    	{
    		$con=$con." and dictiteminfo.createtime='".$_GET["createtime"]."'";
    		$_SESSION["redict_time"]=" and dictiteminfo.createtime='".$_GET["createtime"]."'";
    	}
    	$count=$dao->join("dictinfo on dictiteminfo.dictid=dictinfo.tid")
        ->join("lanlist on lanlist.lanname=dictinfo.srclanguage")
        ->join("typelist on typelist.typename=dictinfo.type")
        ->where($con)->count();
        $rs=$dao->join("dictinfo on dictiteminfo.dictid=dictinfo.tid")
        ->join("lanlist on lanlist.lanname=dictinfo.srclanguage")
        ->join("typelist on typelist.typename=dictinfo.type")
        ->where($con)->field("src,tgt,dictname,username,dictiteminfo.createtime,typevalue,lanvalue,dictinfo.issystem,dictinfo.action,dictiteminfo.tid as itemid")->
        order('dictinfo.tid desc')->limit($offset.','.$rows)->select();
       // dump($dao);
        $rows = array();
        //$count=count($rs);
		if($count==0)
			$result["rows"]=$rows;
		else
			$result["rows"] = $rs;
    	$result["total"]=$count;
       // dump($list);
        echo json_encode($result);
	}
	//下载查询词条
	function down_user_retrivel_item()
	{
		if(Session::get('username')=="")
    	{
    		$this->redirect('login');
    		return;
    	}
		$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
		$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
		$offset = ($page-1)*$rows;
		$dao=D("Dictiteminfo");
    	
    	if(Session::get('type')=="admin")
    		$con="dictiteminfo.isdeleted=0 and dictinfo.isdeleted=0";
	   	else
	    	$con="dictiteminfo.isdeleted=0 and dictinfo.isdeleted=0 and dictinfo.username='".$_SESSION["username"]."'";
	    
	    if($_SESSION["redict_key"]!="")
        {
        	$con=$con.$_SESSION["redict_key"];
        }
		if($_SESSION["redict_type"]!="")
        {
        	$con=$con.$_SESSION["redict_type"];
        }
		if($_SESSION["redict_lan"]!="")
        {
        	$con=$con.$_SESSION["redict_lan"];
        }
		if($_SESSION["redict_time"]!="")
        {
        	$con=$con.$_SESSION["redict_time"];
        }
        $rs=$dao->join("dictinfo on dictiteminfo.dictid=dictinfo.tid")
        ->join("lanlist on lanlist.lanname=dictinfo.srclanguage")
        ->join("typelist on typelist.typename=dictinfo.type")
        ->where($con)->order('dictiteminfo.createtime desc')->select();
      
        $save_path = C("FILE_PATH");
        $guid=get_guid();
	   	$file_name=$guid.".txt";
	   	//写入文件
	   	$fp=fopen($save_path.$file_name,"a");	   
	   	for($i=0;$i<count($rs);++$i)
	   	{
	   		fwrite($fp, $rs[$i]["src"]."\t".$rs[$i]["tgt"]."\r\n");
	   	}
	   	fclose($fp);
        $this->ajaxReturn($file_name,"yes",1);
	}
	//显示导入词典列表，用于管理员
	function listAllImportDict()
	{
		if(Session::get('username')=="")
    	{
    		$this->redirect('login');
    		return;
    	}
		$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
		$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
		$offset = ($page-1)*$rows;
		$dao=M("Dictinfo");
    	$map["isdeleted"]= 0;
        //$map["username"]=Session::get("username");
        $map["issystem"]=array('neq',1);
        $count = $dao->where($map)->count();
        $rs=$dao->where($map)->order('createtime desc')->limit($offset.','.$rows)->select();
        $rows = array();
		if($count==0)
			$result["rows"]=$rows;
		else
			$result["rows"] = $rs;
    	$result["total"]=$count;
       // dump($list);
        echo json_encode($result);
	}
	//显示导入词典历史列表
	function listImportDictView()
	{
		if(Session::get('username')=="")
    	{
    		$this->redirect('login');
    		return;
    	}
		$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
		$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
		$offset = ($page-1)*$rows;
		$dao=M("Dictimport");
    	$map["dictimport.isdeleted"]= 0;
    	$map["dictimport.toid"]=intval($_GET["toid"]);
        $map["importer"]=Session::get("username");
       
        $count = $dao->where($map)->count();
        $rs=$dao->join("dictiteminfo f on f.tid=dictimport.fromid")
        ->join("dictinfo t on t.tid=dictimport.toid")
        ->where($map)->field("f.src,f.tgt,t.dictname as toname,importtime,dictimport.status,
        dictimport.tid,fromid,toid")
        ->order('importtime desc')->limit($offset.','.$rows)->select();
       
        $rows = array();
		if($count==0)
			$result["rows"]=$rows;
		else
			$result["rows"] = $rs;
    	$result["total"]=$count;
       // dump($list);
        echo json_encode($result);
	}
	//显示所有导入词典历史列表，用于管理员审核
	function listAllImportDictView()
	{
		if(Session::get('username')=="")
    	{
    		$this->redirect('login');
    		return;
    	}
		$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
		$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
		$offset = ($page-1)*$rows;
		$dao=M("Dictimport");
    	$map["dictimport.isdeleted"]= 0;
    	//$map["dictimport.status"]= 0;
    	if(isset($_GET["toid"]))
    		$map["dictimport.toid"]=intval($_GET["toid"]);
       // $map["importer"]=Session::get("username");
       $map["status"]=array('lt',2);
        $count = $dao->where($map)->count();
        $rs=$dao->join("dictiteminfo f on f.tid=dictimport.fromid")
        ->join("dictinfo t on t.tid=dictimport.toid")
         ->join("dictinfo fd on fd.tid=f.dictid")
        ->where($map)->field("f.src,f.tgt,fd.tid as fromdictid,fd.dictname as fromname,t.dictname as toname,importtime,dictimport.status,
        dictimport.tid,fromid,toid,importer")
        ->order('status,importtime desc')->limit($offset.','.$rows)->select();
		//dump($dao);       
        $rows = array();
		if($count==0)
			$result["rows"]=$rows;
		else
			$result["rows"] = $rs;
    	$result["total"]=$count;
       // dump($list);
        echo json_encode($result);
	}
	//词条是否已被导入
	function itemisimported($fromid,$toid)
	{	
		if(Session::get('username')=="")
    	{
    		$this->redirect('login');
    		return;
    	}	
		$dao=M("Dictimport");
		$con["fromid"]=intval($fromid);
		$con["toid"]=intval($toid);
		$con["isdeleted"]=0;
		$rs=$dao->where($con)->select();
		if(count($rs)>0)
			return true;
		else
			return false;
	}
	//导入词典到系统词典
	function importDict()
	{
		if(Session::get('username')=="")
    	{
    		$this->redirect('login');
    		return;
    	}
		if(Session::get('username')=="")
    	{
    		$this->redirect('login');
    		return;
    	}
		if(!isset($_POST["tid"]))
		{
			$this->error("删除错误");
		}	
		/*	
		$content=import_dict_socket($_POST["tid"], $_POST["dict_id"]);
		if($content!="ERROR404")
			$this->ajaxReturn($content,"yes",1);
		else
			$this->ajaxReturn("no","no",1);
		//$dao->where($con)->save($data);
		 * 
		 */
		//修改为web控制
		$dao=M("Dictiteminfo");
		$con["dictid"]=intval($_POST["tid"]);
		$con["isactive"]=1;
		$con["isdeleted"]=0;
		$rs=$dao->where($con)->select();
		for($i=0;$i<count($rs);++$i)
		{
			if($this->itemisimported($rs[$i]["tid"],intval($_POST["dict_id"]))==false)
			{
				$dao=M("Dictimport");
				$data["fromid"]=$rs[$i]["tid"];
				$data["toid"]=intval($_POST["dict_id"]);
				$data["importer"]=$_SESSION["username"];
				$data["importtime"]=date('Y-m-d G:i:s');
				$dao->add($data);
			}
			else
			{
				//$this->ajaxReturn($rs[$i]["tid"].intval($_POST["dict_id"]),"no",1);
			}
		}
		
		$this->ajaxReturn("yes","yes",1);
	}
	//导入词条系统词典
	function importDictItem()
	{
		if(Session::get('username')=="")
    	{
    		$this->redirect('login');
    		return;
    	}
		if(Session::get('username')=="")
    	{
    		$this->redirect('login');
    		return;
    	}
		if(!isset($_POST["tid"]))
		{
			$this->error("删除错误");
		}	
		/*	
		$content=import_dict_socket($_POST["tid"], $_POST["dict_id"]);
		if($content!="ERROR404")
			$this->ajaxReturn($content,"yes",1);
		else
			$this->ajaxReturn("no","no",1);
		//$dao->where($con)->save($data);
		 * 
		 */
		//修改为web控制
		if($this->itemisimported(intval($_POST["tid"]),intval($_POST["dict_id"]))==false)
		{
			$dao=M("Dictimport");
			$data["fromid"]=intval($_POST["tid"]);
			$data["toid"]=intval($_POST["dict_id"]);
			$data["importer"]=$_SESSION["username"];
			$data["importtime"]=date('Y-m-d G:i:s');
			$dao->add($data);
		}
		$this->ajaxReturn("yes","yes",1);
	}
	//撤销导入，修改状态isdeleted=1，且只有未审核才可以这个操作
	function delImportDict()
	{
		if(Session::get('username')=="")
    	{
    		$this->redirect('login');
    		return;
    	}    	
		$con["tid"]=intval($_POST["tid"]);
		$data["isdeleted"]=1;
		$dao=M("Dictimport");
		$dao->where($con)->save($data);
		$this->ajaxReturn("yes","yes",1);
	}
	//显示词典列表，返回jason格式()
	function listDict()
	{
		if(Session::get('username')=="")
    	{
    		$this->redirect('login');
    		return;
    	}
		$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
		$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
		$offset = ($page-1)*$rows;
                             
        $dao2 = M("Userinfo");
        $condition["username"] = Session::get("username");
        $issystem = $dao2->where($condition)->find()["issystem"];
            
        $dao=M("Dictinfo");
        $map["isdeleted"]= 0;
        if ($issystem == 1){
            $map["username"]=Session::get("username");
            $con="isdeleted=0 and (username='".Session::get("username")."')";
            $count = $dao->where($con)->count();
        }
        else{
            $map["authuser1"]=Session::get("username");
            $con="isdeleted=0 and (authuser1='".Session::get("username")."')";
            $count = $dao->where($con)->count();
            if ($count==0){
                $map["authuser2"]=Session::get("username");
                $con="isdeleted=0 and (authuser2='".Session::get("username")."')";
                $count = $dao->where($con)->count();
            }
        }

        $rs=$dao->where($con)->order('tid desc')->limit($offset.','.$rows)->select();
        if ($issystem == 1){
			for ($i=0; $i<sizeof($rs); $i++){
				$rs[$i]["action"] = "1";
			}
        }
		
        $rows = array();
		if($count==0)
			$result["rows"]=$rows;
		else
			$result["rows"] = $rs;
    	$result["total"]=$count;
    	
        echo json_encode($result);
	}
	//显示所有词典列表，返回jason格式
	function listAllDict()
	{
		if(Session::get('username')=="")
    	{
    		$this->redirect('login');
    		return;
    	}
		$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
		$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
		$offset = ($page-1)*$rows;
		$dao=M("Dictinfo");
    	$map["isdeleted"]= 0;
        $map["username"]=Session::get("username");
        $con="isdeleted=0 and (username='".Session::get("username")."' or issystem=1)";
        $count = $dao->where($map)->count();
        $rs=$dao->where($map)->order('tid desc')->limit($offset.','.$rows)->select();
        //$count=count($rs);
        //dump($dao);
        $rows = array();
		if($count==0)
			$result["rows"]=$rows;
		else
			$result["rows"] = $rs;
    	$result["total"]=$count;
       // dump($list);
        echo json_encode($result);
	}
	
	//修改授权
	function updateAuth(){
		//$this->ajaxReturn("yes","yes",1);
		if(Session::get('username')=="")
    	{
    		$this->redirect('login');
    		return;
    	}
	
		$tid = $_POST["tid"];
		$authuser1 = $_POST["authuser1"];
		$authuser2 = $_POST["authuser2"];
	
		$dao = M("Dictinfo");

		$data['tid'] = $tid;
		$data['authuser1'] = $authuser1;
		$data["authuser2"] = $authuser2;

		$info = $dao->save($data);
		//$this->ajaxReturn("yes",$Dict->getLastSql(),1);
		//$this->ajaxReturn("yes",$info,1);
		$this->ajaxReturn("yes","yes",1);
	}
	
	//修改词典属性，包括修改和新建操作
	function updateDict()
	{
		if(Session::get('username')=="")
    	{
    		$this->redirect('login');
    		return;
    	}
		$desc="";
		if($_POST["description"]!="")
		{
			$desc=substr($_POST["description"],0,250);
		}
		$dictname="";
		if($_POST["dictname"]!="")
		{
			$dictname=substr($_POST["dictname"],0,100);
		}

		htmlspecialchars($dictname);
		//$dictname = auto_charset($dictname,C('DEFAULT_CHARSET'),"gbk");
		htmlspecialchars($desc);
		//$desc = auto_charset($desc,C('DEFAULT_CHARSET'),"gbk");
       
		//更新
		$data["description"]=$desc;
		$data["dictname"]=$dictname;
		$data["isactive"]=intval($_POST["isactive"]);
		//$data["dirid"]=$_POST["dirid"];
		$username=Session::get("username");
        
		$data["issystem"]=0;
		if(isset($_POST["issystem"]))
			$data["issystem"]=intval($_POST["issystem"]);
		$data["type"]=$_POST["type"];

		$dirid=$_POST["dirid"];
	    $dao=M("Transdir");
		$con["dirid"]=$dirid;
		$list=$dao->where($con)->find();
		//$this->ajaxReturn($list["srclanguage"],"翻译服务器没有启动",1);
		$srclan=$list["srclanguage"];
		$tgtlan=$list["tgtlanguage"]; 
		
	    $tid=$_POST["tid"];
       	
		if(isset($_POST["tid"]))
		{
			//更新操作
			$con["tid"]=$_POST["tid"];
          //  $tid=$_POST["tid"];
			//if($info=$dao->where($con)->save($data))
			$content=mod_dict_socket($_POST["tid"], $srclan,$tgtlan,$_POST["type"], $dictname, $desc, $data["isactive"],$data["issystem"]);
			if($content!="ERROR404")
			{
				$this->ajaxReturn("yes","yes",1);
			}
			else
			{
				$this->ajaxReturn("yes","修改错误",1);
			}
		}
		else
		{
			//插入操作
			$data["createtime"]=date('Y-m-d G:i:s');
			$data["isdeleted"]=0;
			$data["username"]=Session::get("username");
            
			$content=add_dict_socket($username, $list["srclanguage"],$list["tgtlanguage"],$_POST["type"], $dictname, $desc, $data["isactive"],$data["issystem"]);
			
			
			//if($info=$dao->add($data))
			//$this->ajaxReturn($content,"yes",1);
			if($content!="ERROR404")
			{
				$this->ajaxReturn("yes","yes",1);
			}
			else
			{
				$this->ajaxReturn("yes","添加错误",1);
			}
		}

	}
	
	//删除词典
	function delDict()
	{
		if(Session::get('username')=="")
    	{
    		$this->redirect('login');
    		return;
    	}
		if(!isset($_POST["tid"]))
		{
			$this->error("删除错误");
		}
		$con["tid"]=$_POST["tid"];
		$con["username"]=Session::get("username");
		$data["isdeleted"]=1;
		$dao=M("Dictinfo");
		$content=del_dict_socket($_POST["tid"]);
		if($content!="ERROR404")
			$this->ajaxReturn("yes","yes",1);
		else
			$this->ajaxReturn("no","no",1);
		//$dao->where($con)->save($data);
	}
	//恢复被删除删除词典
	function recoverDict()
	{
		if(Session::get('username')=="")
    	{
    		$this->redirect('login');
    		return;
    	}
		if(!isset($_POST["tid"]))
		{
			$this->error("删除错误");
		}
		$con["tid"]=$_POST["tid"];
		$con["username"]=Session::get("username");
		$data["isdeleted"]=0;
		$dao=M("Dictinfo");
		$content=recover_dict_socket($_POST["tid"]);
		if($content!="ERROR404")
			$this->ajaxReturn("yes","yes",1);
		else
			$this->ajaxReturn("no","no",1);
		//$dao->where($con)->save($data);
	}
	//获取编辑词典内容
	function moddict()
	{
		if(Session::get('username')=="")
    	{
    		$this->redirect('login');
    		return;
    	}
		$_GET["tid"]="122";
		$con["tid"]=$_GET["tid"];
		$dao=M("Dictinfo");
		$re=$dao->join('transdir ON dictinfo.srclanguage = transdir.srclanguage and dictinfo.tgtlanguage = transdir.tgtlanguage')->where($con)->find();
		//dump ($re);
		$this->assign("dictname",$re["dictname"]);
		$this->assign("isactive",$re["isactive"]=="1");
		$issystem="0";
	/*	if($re["issystem"]==1)
			$issystem="1";
		else if($re["issystem"]==2)
			$issystem="2";*/
		$this->assign("issystem",$issystem);
		$this->assign("type",$re["type"]);
		$this->assign("tid",$con["tid"]);		
		$this->assign("dirinfo",$re["dirinfo"]);
		
	/*	if($re["srclanguage"]=="english")
			$this->assign("direction","e2c");
		else
			$this->assign("direction","c2e");*/
		$this->assign("description",$re["description"]);
	    $this->display();
		//$this->ajaxReturn($re,($re),1);
	}
	
	//搜索词典词条列表，返回jason格式
	function searchlistDictItem()
	{  
		if(Session::get('username')=="")
    	{
    		$this->redirect('login');
    		return;
    	}
				
		if(isset($_POST["dictid"]))
			$dictid=$_POST["dictid"];
		else
			$dictid=-1;

		$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
		$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
		$offset = ($page-1)*$rows;
		$dao=M("Dictiteminfo");
    	$map["isdeleted"]= 0;
    	$map["dictid"]=$_POST["dictid"];
		
    	if(isset($_GET["src"]))
    		$map["src"]=array('like',"%".$_GET["src"]."%");
    	if(isset($_GET["tgt"]))
    		$map["tgt"]=array('like',"%".$_GET["tgt"]."%");
    	if(isset($_GET["subtime"]))
    		$map["createtime"]=$_GET["subtime"];
		
		$condition["isdeleted"]= 0;	
		$condition["dictid"]=$dictid;
	
         $count = $dao->where($map)->count();
		//$count = $dao->where($condition)->count();
        $rs=$dao->where($map)->order('createtime desc')->limit($offset.','.$rows)->select();
		//$rs=$dao->where($condition)->order('createtime desc')->limit($offset.','.$rows)->select();
		//dump($rs);
        $rows = array();
		if($count==0)
			$result["rows"] = $rows;
		else
			$result["rows"]=$rs;
    	$result["total"]=$count;
       // dump($list);
        echo json_encode($result);
	}
	
	
	
	//显示词典词条列表，返回jason格式
	function listDictItem()
	{  
		if(Session::get('username')=="")
    	{
    		$this->redirect('login');
    		return;
    	}
				
		if(isset($_POST["dictid"]))
			$dictid=$_POST["dictid"];
		else
			$dictid=-1;

		$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
		$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
		$offset = ($page-1)*$rows;
		$dao=M("Dictiteminfo");
    	$map["isdeleted"]= 0;
    	$map["dictid"]=$_POST["dictid"];
		
    	if(isset($_GET["src"]))
    		$map["src"]=array('like',"%".$_GET["src"]."%");
    	if(isset($_GET["tgt"]))
    		$map["tgt"]=array('like',"%".$_GET["tgt"]."%");
    	if(isset($_GET["subtime"]))
    		$map["createtime"]=$_GET["subtime"];
		
		$condition["isdeleted"]= 0;	
		$condition["dictid"]=$dictid;
	
         //$count = $dao->where($map)->count();
		$count = $dao->where($condition)->count();
        //$rs=$dao->where($map)->order('createtime desc')->limit($offset.','.$rows)->select();
		$rs=$dao->where($condition)->order('createtime desc')->limit($offset.','.$rows)->select();
		//dump($rs);
        $rows = array();
		if($count==0)
			$result["rows"] = $rows;
		else
			$result["rows"]=$rs;
    	$result["total"]=$count;
       // dump($list);
        echo json_encode($result);
	}
	
	//显示词典所有词条列表，返回jason格式
	function listAllDictItem()
	{
		if(Session::get('username')=="")
    	{
    		$this->redirect('login');
    		return;
    	}
		if(isset($_POST["dictid"]))
			$dictid=$_POST["dictid"];
		else
			$dictid=-1;

		$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
		$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
		$offset = ($page-1)*$rows;
		$dao=M("Dictiteminfo");
    	$map["isdeleted"]= 0;
    	$map["dictid"]=$dictid;
    	if(isset($_GET["src"]))
    		$map["src"]=array('like',"%".$_GET["src"]."%");
    	if(isset($_GET["tgt"]))
    		$map["tgt"]=array('like',"%".$_GET["tgt"]."%");
    	if(isset($_GET["subtime"]))
    		$map["createtime"]=$_GET["subtime"];	
        $count = $dao->where($map)->count();
        $rs=$dao->where($map)->order('isdeleted,createtime desc')->limit($offset.','.$rows)->select();
        $rows = array();
		if($count==0)
			$result["rows"] = $rows;
		else
			$result["rows"]=$rs;
    	$result["total"]=$count;
       // dump($list);
        echo json_encode($result);
	}
	public function adddictitem()
	{
		if(Session::get('username')=="")
    	{
    		$this->redirect('login');
    		return;
    	}
		$this->assign("dictid",$_GET["did"]);
		$this->display("adddictitem");
	}
	
	//获取翻译方向id 
	function getDirid()
	{
	    if(isset($_POST["srclanguage"]))
			$srclanguage=$_POST["srclanguage"];
			
		if(isset($_POST["tgtlanguage"]))
			$srclanguage=$_POST["tgtlanguage"];
			
		$dao=M("Transdir");
		$con["srclanguage"]=$_POST["srclanguage"];
		$con["tgtlanguage"]=$_POST["tgtlanguage"];
		$rs= $dao->where($con)->getField("dirid");
		
  //	dump($rs);
		
		$this->ajaxReturn($rs,"yes",1);
	}
	
	//获取编辑词条内容
	function moddictitem()
	{
		if(Session::get('username')=="")
    	{
    		$this->redirect('login');
    		return;
    	}
		$con["tid"]=$_GET["tid"];
		$dao=M("Dictiteminfo");
		$re=$dao->where($con)->find();
		$this->assign("src",$re["src"]);
		$this->assign("isactive",$re["isactive"]==1?"1":"0");
		$this->assign("tgt",$re["tgt"]);
		$this->assign("tid",$con["tid"]);
		$this->assign("dictid",$re["dictid"]);
		$this->display("moddictitem");
	}
	//上传词典条目
	public function uploadDict()
	{
		/*上传不能使用session检测，因为此时session无效*/
		
		if(isset($_POST["type"]))
			$type=$_POST["type"];
		if(isset($_GET["username"]))
			$username=$_GET["username"];
		if (isset($_POST["PHPSESSID"])) {
			session_id($_POST["PHPSESSID"]);
		} else if (isset($_GET["PHPSESSID"])) {
			session_id($_GET["PHPSESSID"]);
		}
		//echo $_GET["dictid"];
		//return ;
		//session_start();
		$save_path = C("FILE_PATH");
		$upload_name = "Filedata";
	    $file_info=pathinfo($_FILES[$upload_name]["name"]);
		//获取文件扩展名
		$file_ext=$file_info["extension"];
	   	$file_name=mktime().".".$file_ext;//.$_FILES[$upload_name]["name"];
	   	$file_name=$guid.".txt";
		$save_path = C("FILE_PATH");
		$upload_name = "Filedata";
	    $file_info=pathinfo($_FILES[$upload_name]["name"]);
		//获取文件扩展名
		$file_ext=$file_info["extension"];
		if($file_ext=="htm")
			$file_ext="html";
	   	$file_name=mktime().".".$file_ext;//.$_FILES[$upload_name]["name"];
	   	//windows
	   	$guid=get_guid();
	   	$file_name=$guid.".txt";
		print_r($save_path);
		print_r($_FILES[$upload_name]["tmp_name"]);
	   	if(!is_dir($save_path))
	   	{
	   		die(json_encode(array(0 => "1",1 => '上传路径不存在')));
	   	}
		if (!@move_uploaded_file($_FILES[$upload_name]["tmp_name"], $save_path.$file_name)) {
			die(json_encode(array(0 => "1",1 => '上传路径不存在')));
		}
		else
		{

			//通信
			//$socket=upload_item_socket($_POST["dictid"], $file_name);
			//此处修改为插入数据库，需要用户预览通过后通信后台添加
			$dao=M("Dictiteminfo_temp");
			$data["guid"]=$_POST["guid"];
			$data["dictid"]=$_POST["dictid"];
			$data["filename"]=$file_name;
			$data["srcname"]=$_FILES[$upload_name]["name"];
			
			$dao->add($data);
	        die(json_encode(array(0 => '',1 => 'ok')));

			die(json_encode(array(0 => '',1 => $_FILES[$upload_name]["filename"])));

		}

		die(json_encode(array(0 => '',1 => 'ok')));
	//	exit(0);
	}
	//确认上传，预览结束
	function upload_dictitem_finish()
	{
		$dao=M("Dictiteminfo_temp");
		
		$con["guid"]=$_POST["guid"];

		$rs=$dao->where($con)->select();
		//$this->ajaxReturn(count($rs),"yes",1);
		//dump ($rs);
		$str="";
		for($i=0;$i<count($rs);++$i)
		{
			//$str.=$rs[$i]["dictid"].$rs[$i]["filename"].$_POST["isutf8"];
			$socket=upload_item_socket($rs[$i]["dictid"], $rs[$i]["filename"],$_POST["isutf8"]);
		}
		$this->ajaxReturn($str,"yes",1);  //$str
	}
	//预览上传条目
	function get_preview_item()
	{
		$dao=M("Dictiteminfo_temp");
		$con["guid"]=$_POST["guid"];
		//$con["guid"]="6d5f75b9-a4ea-1b83-60de-5b577449ceea";
		//$cnt=0;
		//while ($cnt!=$_POST["queue_cnt"])
		{
			$rs=$dao->where($con)->select();
		//	$cnt=count($rs);
		}		
		//$this->ajaxReturn(count($rs),"yes",1);
		$re="";
		for($i=0;$i<count($rs);++$i)
		{
			$re.="<h2>".$rs[$i]["srcname"]."<font color='red'>(只显示部分内容，如显示不正常则需要将编码转为utf8)</font></h2><br/>";
			//$re.="2";
			if(!file_exists(C("FILE_PATH").$rs[$i]["filename"]))
				$this->ajaxReturn("文件不存在".C("FILE_PATH").$rs[$i]["filename"],"no",0);
				
			$file_handle = fopen(C("FILE_PATH").$rs[$i]["filename"], "r");
			$cnt=1;
			while (!feof($file_handle)) {
			  $re.= fgets($file_handle)."<br/>";
			  $cnt++;
			  if($cnt>10)
			  	break;
			}
			fclose($file_handle);
			$re.="<br/>";
		}
		$this->ajaxReturn($re,"yes",1);
	}
	
	//修改词典词条属性，包括修改和添加操作
	function updateDictItem()
	{
		if(Session::get('username')=="")
    	{
    		$this->redirect('login');
    		return;
    	}
	//	$this->ajaxReturn("error",$_POST["src"],1);
	//	return ;
		

		$src="";
		if($_POST["src"]!="")
		{
			$src=substr($_POST["src"],0,1000);
		}
		$tgt="";
		if($_POST["tgt"]!="")
		{
			$tgt=substr($_POST["tgt"],0,1000);
		}
		
	
		
		htmlspecialchars($src);
		//$src = auto_charset($src,C('DEFAULT_CHARSET'),"gbk");
		htmlspecialchars($tgt);
		//$tgt = auto_charset($tgt,C('DEFAULT_CHARSET'),"gbk");

		//更新
		$data["src"]=$src;
		$data["tgt"]=$tgt;
		$data["isactive"]=$_POST["isactive"];
		$data["dictid"]=intval($_POST["dictid"]);
	//	$data["ischecked"]=1;
	//	if(isset($_POST["ischecked"]))
	//		$data["ischecked"]=$_POST["ischecked"];
		$dao=M("Dictiteminfo");
		
		
		if(isset($_POST["tid"]))
		{
			//更新操作
			$con["tid"]=intval($_POST["tid"]);
			//if($info=$dao->where($con)->save($data))
			$content=mod_item_socket($_POST["dictid"],$_POST["tid"], $src, $tgt, $_POST["isactive"]);
			
				//	echo "content:".$content ;, $data["ischecked"]
			if($content!="ERROR404")
			{
				$this->ajaxReturn("yes","yes",1);
			}
			else
			{
				$this->ajaxReturn("yes","修改错误",1);
			}
		}
		else
		{
			//插入操作
			$data["createtime"]=date('Y-m-d G:i:s');
			$data["isdeleted"]=0;

			$content=add_item_socket($_POST["dictid"], $src, $tgt, $_POST["isactive"]);
		
		    
		//	echo "content:".$content;
			
			if($content!="ERROR404")
			//if($info=$dao->add($data)), $data["ischecked"]
			{
				$this->ajaxReturn("yes","yes",1);
			}
			else
			{
				$this->ajaxReturn("yes","添加错误",1);
			}
	
		}
	}
	function delDictItem()
	{
		if(Session::get('username')=="")
    	{
    		$this->redirect('login');
    		return;
    	}
	//	$_POST["tid"]="1364";
		if(!isset($_POST["tid"]))
		{
			$this->error("删除错误");
		}
		
		
		$con["tid"]=$_POST["tid"];			
		$data["isdeleted"]=1;
		$dao=M("Dictiteminfo");
		$rs=$dao->where($con)->find();
	//	dump ($dao);
		$content=del_item_socket($rs["dictid"], $_POST["tid"]);
		
	//	echo $content;
		if($content!="ERROR404")
			$this->ajaxReturn($content,"yes",1);
		else
			$this->ajaxReturn("no","no",1);
		//$dao->where($con)->save($data);
	}
	
    //下载字典
    function download_file()
    {
		
	if(Session::get('username')=="")
    	{
    	    $this->redirect('login');
    	    return;
    	}
	
    	require_once 'PHPZip.class.php';
	$str=trim($_POST["guid"]);
	$arr=split(",",$str);
		
	//先创建一个文件夹
	$save_path = "Public/Upload/";
	$guid=date('ymdHis',time());
	$dir_name=$save_path.$guid;  
	mkdir($dir_name,0777);	   
	$save_path=$save_path.$guid."/";
	//读取词条写入文件
    	for($i=0;$i<count($arr);++$i)
    	{
    	    $con["tid"]=intval($arr[$i]);
    	    $dao=M("Dictiteminfo");
    	    $re=$dao->where($con)->select();
    		
    	    //获取词典名称
	    $dao2=M('Dictinfo');
	    $con2["tid"]=intval($re["dictid"]);
	    $re2=$dao2->where($con2)->find();
	    $file_name=$re2["dictname"]."_".$re2["type"]."_".$re2["srclanguage"]."_".$re2["tgtlanguage"].".txt";
    	    $file_name=mb_convert_encoding($file_name, "GBK", "UTF-8"); 
    	    //写入文件
	    $fp=fopen($save_path.$file_name,"a");
    	    for($j=0;$j<count($re);++$j){
	        fwrite($fp, $re[$j]["src"]."\tab".$re[$j]["tgt"]."\r\n");
	    }
	    fclose($fp);
    	  	
    	}   
    	//生成压缩文件
    	$zip=new PclZip("Public/Upload/".$guid.'.zip');	
    	//打包并且移除压缩包内的文件夹嵌套结构，将文件移到第一层目录
	$v_list = $zip->create($dir_name,PCLZIP_OPT_REMOVE_PATH, 'Public/Upload/'.$guid);
	//删除临时文件
	rmdir($dir_name);
	$this->ajaxReturn($guid.'.zip','yes',1);	
	 		
	//移到上传文件目录	 	
	//copy($save_path.'dict.zip',C("FILE_PATH").$guid.'.zip');
	 	
    	//$this->ajaxReturn($guid.'.zip',"yes",1); 	
    }	


    

    //批量下载词典
    function bat_download_dict()
    {
		
        if(Session::get('username')=="")
    	{
    	    $this->redirect('login');
    	    return;
    	}
	
        require_once 'PHPZip.class.php';
        $str=trim($_POST["guid"]);
        $arr=split(",",$str);
		
        //先创建一个文件夹
        $save_path = "Public/Upload/";
        $guid=date('ymdHis',time());
        $dir_name=$save_path.$guid;  
        mkdir($dir_name,0777);	   
        $save_path=$save_path.$guid."/";
	
        //读取词条写入文件
    	for($i=0;$i<count($arr);++$i)
    	{
    	    $con["dictid"]=intval($arr[$i]);
            $con["isdeleted"] = '0';
    	    $dao=M("Dictiteminfo");
            $maxid = $dao->where($con)->max('tid');
            $minid = $dao->where($con)->min('tid');
            $pollcount = ($maxid-$minid)/10000;

            //$this->ajaxReturn($pollcount,'no',0);
    	    //$re=$dao->where($con)->select();//where('dictid=' + $con)->select();
    		
    	    //获取词典名称
            $dao2=M('Dictinfo');
            $con2["tid"]=intval($arr[$i]);
            $re2=$dao2->where($con2)->find();
	    
            $file_name=$re2["dictname"]."_".$re2["type"]."_".$re2["srclanguage"]."_".$re2["tgtlanguage"].".txt";
    	    $file_name=mb_convert_encoding($file_name, "GBK", "UTF-8");
	    
    	    //写入文件
            $fp=fopen($save_path.$file_name,"a");
            for ($poll=0; $poll<=$pollcount; $poll++){
                $firstid = $poll*10000 + $minid;
                $lastid = ($poll+1)*10000 + $minid - 1;
                if ($lastid > $maxid)
                    $lastid = $maxid;
                
                $con["tid"] = array('between', array((string)$firstid, (string)$lastid));
                $re=$dao->where($con)->select();
                
                //$this->ajaxReturn($maxid, 'no', 0);
                for($j=0;$j<count($re);++$j){
                    fwrite($fp, $re[$j]["src"]."\t".$re[$j]["tgt"]."\r\n");
                }
               
            }
            fclose($fp);
            
            
            
    	  	
    	}   
    	//生成压缩文件
    	$zip=new PclZip("Public/Upload/".$guid.'.zip');	
    	//打包并且移除压缩包内的文件夹嵌套结构，将文件移到第一层目录
        $v_list = $zip->create($dir_name,PCLZIP_OPT_REMOVE_PATH, 'Public/Upload/'.$guid);
        //删除临时文件
        rmdir($dir_name);
        $this->ajaxReturn($guid.'.zip','yes',1);	
	
    }

}



?>
