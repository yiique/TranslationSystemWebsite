<?php
	//import("@.ORG.LogRecord");
	//import("@.ORG.SockConn");
	require_once("LogRecord.class.php");
	require_once("SockConn.class.php");
		
	class SearchRes{
		public function commServer($ip,$port,$str){
			$str = auto_charset($str,C('DEFAULT_CHARSET'),"gbk");
			//$str = iconv("utf-8","gbk",$str);
			//return $str;
			$res = SockConn::read_server($ip,$port,$str);
			//return $res;
			//$res = iconv("gbk","utf-8",$res);
			$res = auto_charset($res,"gbk",C('DEFAULT_CHARSET'));		
			$res = addslashes($res);
			return $res;
		}
	
		public function encapQuery($reqType,$corpType,$keywords,$from,$to){			
			$reqType = trim($reqType);
			$corpType = trim($corpType);
			//$keywords = trim(stripslashes($keywords));
			
			if($reqType == ""||$corpType == "" || $keywords == ""){
				return false;
			}
			
			if(is_nan($from) || is_nan($to)){
				return false;
			}
			
			$str = "<Msg>";
			$str .= "<From>$from</From>";
			$str .= "<To>$to</To>";
			$str .= "<ReqType>$reqType</ReqType>";
			$str .= "<Corpus>$corpType</Corpus>";
			$str .= "<Keyword>$keywords</Keyword>";
			$str .= "</Msg>";
			
			// echo "<xmp>Msg:$str</xmp>";
			
			return $str;
		}

		public function get_resnum($res){
			//$str = iconv("gbk","utf-8",$res);
			
			$ret = preg_match("/<num>(\d+)<\/num>/i",$res,$curres);
			if($ret == 1){
				
				return $curres[1];
			}
			
			return 0;
		}
		
		public function highlight_res(&$arr,$keywords,$style){
			$words = split(" ",$keywords);
			
				for($i=0;$i<count($arr);$i++){					
					for($j=0;$j<count($words);$j++){
						// echo "<div>lwh[$i]=".$arr[$i]."</div>";
						
						$pattern = "/".addslashes($words[$j])."/i";
						$repstr = "<span class='$style'>".$words[$j]."</span>";
						$arr[$i] = preg_replace($pattern,$repstr,$arr[$i]);
					}
			}//for
		}
	};
	
	
	class SearchSent extends SearchRes{		
		public function get_sentences($res){
			$ret = preg_match_all("/<sent>(.+?)<\/sent>/i",$res,$curres);	//+? means ungreedy match
			
			if($ret === false){
				return false;
			}
			
			if($ret > 0){
				return $curres[1];
			}
			
			return 0;
		}
		
	};
	
	class ItemInfo{
		public $_buf;
		public $_freq;
		
		public $_sents;
		
		function __construct(){
			$this->_buf = "";
			$this->_freq = 0;
			$this->_sents = array();
		}
	};
	
	class SearchColl extends SearchRes{
		public function get_colls($res){
			//$str = iconv("gbk","utf-8",$res);
			$tag_s = "<collo>";
			$tag_e = "</collo>";
			
			$tag_sl = strlen($tag_s);
			$tag_el = strlen($tag_e);
			
			$fre_s = "<freq>";
			$fre_e = "</freq>";
			
			$fre_sl = strlen($fre_s);
			$fre_el = strlen($fre_e);
			
			$sent_s = "<sent>";
			$sent_e = "</sent>";
			
			$sent_sl = strlen($sent_s);
			$sent_el = strlen($sent_e);
			
			$tail = strlen($res);
			$start = 0;
			
			while($start<$tail){
				$pos1 = strpos($res,$tag_s,$start);
				if($pos1!==false){
					$pos2 = strpos($res,$tag_e,$pos1+$tag_sl);
					
					if($pos2!==false){
						$buf = substr($res,$pos1+$tag_sl,$pos2-$pos1-$tag_sl);
						
						$pos3 = strpos($res,$fre_s,$pos2);
						if($pos3!==false){
							$pos4 = strpos($res,$fre_e,$pos3+$fre_sl);
							
							if($pos4!==false){
								$freq = substr($res,$pos3+$fre_sl,$pos4-$pos3-$fre_sl);
								$start = $pos4+$fre_el;
							}
							else{
								$start = $pos3+$fre_sl;
							}
						}else{
							$start = $pos2+$fre_el;
						}//if
						
						if($buf!="" && $freq!=""){
							$item = new ItemInfo;
							
							$item->_buf = $buf;
							$item->_freq = intval($freq);
							
							$pos5 = strpos($res,$sent_s,$pos2);
							
							if($pos5!==false){
								$pos6 = strpos($res,$tag_s,$pos5);
								
								if($pos6 !== false){
									
								}
								else if($pos6<$tail){
									$pos6 = $tail;	
								}//if
								
								$sent_str = substr($res,$pos5,$pos6-$pos5);
								
								if($sent_str!=""){
									$ret = preg_match_all("/<sent>(.+?)<\/sent>/i",$sent_str,$curres);
									
									for($i=0;$i<count($curres[1]);$i++){
										$item->_sents[$i] = stripslashes($curres[1][$i]);
									}//for
								}//if
							}//if
														
							$colls[count($colls)] = $item;
						}//if	

						
					}else{
						$start = $pos1 + $tag_sl; 
						break; 
					}//if
					

				}
				else{
					break;
				}//if
				
			}//while
			
			return $colls;
		}
	};
	
	class SearchSeg extends SearchRes{
		public function get_segs($res){

			$tag_s = "<seg>";
			$tag_e = "</seg>";
			
			$tag_sl = strlen($tag_s);
			$tag_el = strlen($tag_e);
			
			$fre_s = "<freq>";
			$fre_e = "</freq>";
			
			$fre_sl = strlen($fre_s);
			$fre_el = strlen($fre_e);
			
			$sent_s = "<sent>";
			$sent_e = "</sent>";
			
			$sent_sl = strlen($sent_s);
			$sent_el = strlen($sent_e);
			
			$tail = strlen($res);
			$start = 0;
			
			while($start<$tail){
				$pos1 = strpos($res,$tag_s,$start);
				if($pos1!==false){
					$pos2 = strpos($res,$tag_e,$pos1+$tag_sl);
					
					if($pos2!==false){
						$buf = substr($res,$pos1+$tag_sl,$pos2-$pos1-$tag_sl);
						
						$pos3 = strpos($res,$fre_s,$pos2);
						if($pos3!==false){
							$pos4 = strpos($res,$fre_e,$pos3+$fre_sl);
							
							if($pos4!==false){
								$freq = substr($res,$pos3+$fre_sl,$pos4-$pos3-$fre_sl);
								$start = $pos4+$fre_el;
							}
							else{
								$start = $pos3+$fre_sl;
							}
						}else{
							$start = $pos2+$fre_el;
						}//if
						
						if($buf!="" && $freq!=""){
							$item = new ItemInfo;
							
							$item->_buf = $buf;
							$item->_freq = intval($freq);
							
							$pos5 = strpos($res,$sent_s,$pos2);
							
							if($pos5!==false){
								$pos6 = strpos($res,$tag_s,$pos5);
								
								if($pos6 !== false){
								}
								else if($pos6<$tail){
									$pos6 = $tail;
								}//if

								
								$sent_str = substr($res,$pos5,$pos6-$pos5);
								
								if($sent_str!=""){
									$ret = preg_match_all("/<sent>(.+?)<\/sent>/i",$sent_str,$curres);
									
									for($i=0;$i<count($curres[1]);$i++){
										$item->_sents[$i] = stripslashes($curres[1][$i]);
									}//for
								}//if
							}//if
														
							$segs[count($segs)] = $item;
						}//if	

						
					}else{
						$start = $pos1 + $tag_sl; 
						break; 
					}//if
					

				}
				else{
					break;
				}//if
				
			}//while
			
			return $segs;
		}
	};
?>