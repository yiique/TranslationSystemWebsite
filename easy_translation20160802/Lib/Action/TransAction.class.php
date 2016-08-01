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
	
	public function upload() {
	
	  $file_name = $_FILES["Filedata"]["name"];
	  
	  $dao=M("Filetrans");
	  
	  $data["filename"]=$file_name;
	  
      $data["username"]=$_GET["username"];
	  
	  $dao->add($data);
	  
	  echo "test";
	  
	}
	
	
	//句子翻译
	
	public function tranSent()
	{		
		$dirid=$_POST["dirid"];
		$dao=M("Transdir");
		$con["dirid"]=$dirid;
		$list=$dao->where($con)->find();
		//$this->ajaxReturn($list["srclanguage"],"翻译服务器没有启动",1);
		$srclan=$list["srclanguage"];
		$tgtlan=$list["tgtlanguage"];
		$transtyle=$list["isspace"];
	//	$src=$_POST["content"];
		
	//	$src=str_replace("\n","&#xA;",$src);
		
		$src=htmlspecialchars($_POST["content"]);
		
		//echo "src:".$src."</br>";
		//$srccon=htmlspecialchars($src);$this->ajaxReturn($srccon,$transtyle,1);
		//$srccon=str_replace("\n","&#x0A;",$srccon);
		$content=(get_trans(Session::get("username"), $src, $srclan, $tgtlan, "ty", $transtyle));
		
		//echo "content:".$content."</br>";
		$content = str_replace("&#x0A;", "<br/>", $content);
		$content = html_entity_decode($content);	//$this->ajaxReturn($content,$transtyle,1);
		
		$trans = $content;
		$sp = strpos($trans, "<TransRes>");
		$sp += strlen("<TransRes>");
		$ep = strpos($trans, "</TransRes>");
		$trans = substr($trans, $sp, $ep - $sp);
		$res = explode("<br/>", $trans);
		$this->ajaxReturn($res, $transtyle, 1);
		
		/*
		$str=$content;
		$sp=strpos($str,"<word>");
		$sp+=strlen("<word>");
		$ep=strpos($str,"</word>");
		$content=substr($str, $sp,$ep-$sp);
		
		$test[0]=explode("	",$content);
		$fin[0]=a_array_unique($test[0]);
		
		$i=0;
		while(($str=strchr($str,'</word>'))){
			$str = substr($str,7);
			$sign=0; 
			$str1 = substr($str,0,5);
			if(strcmp($str1,'<br/>')==0){$sign=1;} 		
			if($sign==1)
			{
				$len=count($fin[$i]);
				$fin[$i][$len]='<br/>';
			}
			$sp=strpos($str,"<word>");
			
			if(strchr($str, '<word>')){
				$sp += strlen("<word>");
				$ep = strpos($str, "</word>");
				$content = substr($str, $sp, $ep-$sp);
				
			//	$content=str_replace("&#x0A;","<br/>",$re);	
				
				$i++;
				$test[$i]=explode("	",$content);
				$fin[$i]=a_array_unique($test[$i]);

			}
		}
		
	
		
		$this->ajaxReturn($fin,$transtyle,1);
		*/
		
	}
}
?>