<?php
	require_once("LogRecord.class.php");
	//import("@.ORG.LogRecord");
	class SockConn{		
		static public $_timeout = 60;
		static public $_buffsize = 8196;

		static function get_timeout(){
			return self::$_timeout;
		}

		static function get_buffsize(){
			return self::$_buffsize;
		}
		
		static function set_timeout($timeout){
			if(isNaN($timeout)){
				return false;
			}
			
			self::$_timeout = $timeout;
		}
		
		static function set_buffsize($buffsize){
			if(isNaN($buffsize)){
				return false;
			}
			
			self::$_buffsize = $buffsize;
		}
		
		static function receive_data($fp,&$tmp)
		{
			//global $curlog;
			$f=fopen("log.txt","w+");
			$tmp = "";
			$iLeft = 1;
			$first = true;
							
			while($iLeft > 0)
			{
				//$buf = fread($fp,self::$_buffsize);
				$buf = fread($fp,8096);
				if(strlen($buf) == 0){
					break;
				}
				
				//$curlog->write_log(LOG_DEBUG,"buf:".$buf);
				//fwrite($f,$iLeft."\n");
				if ($first)
				{
					$pos = strpos($buf," ");

					if ($pos === false)
					{
						break;
					}//if

					$strsize = substr($buf,0,$pos);
					$istrsize = intval($strsize);
				
					$total = $istrsize + $pos + 1;
					$iLeft = $total;				
					$iLeft -= strlen($buf);

					$first = false;
					
					$tmp .= substr($buf,$pos+1);
					
					continue;
				}//if

				$iLeft -= strlen($buf);

				$tmp .= $buf;

				//fwrite($fp,$buf);
			}//while
		
			//fwrite($f,$tmp."\n");
			if ($tmp == '-1')
			{
				//$curlog->write_log(LOG_FATAL,"Error: cannot process the request");
			}//if

			return $tmp;
		}//receivedata

		static function read_server($server,$port,$sockstr)
		{
			//global $curlog;
			
			//$fp = fsockopen($server,$port,$errno,$errstr,self::$_timeout);
			$fp = fsockopen($server,$port,$errno,$errstr,800);
			if (!$fp)
			{
				//$curlog->write_log(LOG_FATAL,"connect $server:$port error.".$errstr);
				return false;
			}//if
			

			fwrite($fp,$sockstr);
			
			$content = "";
			if(self::receive_data($fp,$content)==false){
				fclose(fp);
				return false;
			}

			fclose($fp);
			
			return $content;
		}		
	};
?>