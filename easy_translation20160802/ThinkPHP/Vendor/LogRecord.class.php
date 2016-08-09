<?php
	define("LOG_NOERR",0);
	define("LOG_WARN",1);
	define("LOG_DEBUG",2);
	define("LOG_FATAL",3);

	define("LOG_FILE","lwh_fa.log");

	class LogRecord{
		private $_log;
		private $_logerr;
		
		public function __construct($file){
			$this->_log = fopen($file,"a+");
			if($this->_log === false){
				echo "<div>open $file error</div>";
			}
						
			$this->_logerr = array(LOG_NOERR=>"No Error",LOG_WARN=>"Warning",LOG_DEBUG=>"Debug",LOG_FATAL=>"Error");
		}
		
		public function __destruct(){
			if($this->_log !== false){
				fclose($this->_log);
			}
		}
				
		public function write_log($err_level,$str){
			if($err_level<LOG_NOERR || $err_level>LOG_FATAL){
				return false;
			}
			
			$wouldblock = true;
			if($this->_log && flock($this->_log,LOCK_EX+LOCK_NB,$wouldblock)){
				$line = date("Y-m-d H:i:s")."\t".$this->_logerr[$err_level].":".$str."\n";
				fwrite($this->_log,$line);
				flock($this->_log,LOCK_UN);
				
				return true;
			}
			
			return false;
		}
	};
	
	$curlog = new LogRecord(LOG_FILE);
?>