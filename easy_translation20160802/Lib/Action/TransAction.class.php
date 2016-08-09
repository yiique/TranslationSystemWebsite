<?php
class TransAction extends Action{
//autocomplete用户返回

 	public function index(){
		if(Session::get('username')=="")
    	{
    		$this->redirect('login');
    		return;
    	} 
		$this->display("index");
    }
	
	// 句子翻译
	public function tranSent()
	{		
		$dirid=$_POST["dirid"];

		$dao=M("Transdir");
		$con["dirid"]=$dirid;
		$list=$dao->where($con)->find();
		$srclan=$list["srclanguage"];
		$tgtlan=$list["tgtlanguage"];
		$transtyle=$list["isspace"];
		
		$src=htmlspecialchars($_POST["content"]);

		// 执行翻译
		$content=(get_trans(Session::get("username"), $src, $srclan, $tgtlan, "ty", $transtyle));
		
		$content = str_replace("&#x0A;", "<br/>", $content);
		$content = html_entity_decode($content);

		$trans = $content;
		$sp = strpos($trans, "<TransRes>");
		$sp += strlen("<TransRes>");
		$ep = strpos($trans, "</TransRes>");
		$trans = substr($trans, $sp, $ep - $sp);

		$res = explode("<br/>", $trans);
		$this->ajaxReturn($res, $transtyle, 1);	
	}

	// 文件上传
	public function upload() {
	
	  $file_name = $_FILES["Filedata"]["name"];

	  $dao=M("Filetrans");
	  $data["filename"]=$file_name;
      $data["username"]=$_GET["username"];
	  $dao->add($data);
	  
	  echo "test";
	  
	}
}
?>