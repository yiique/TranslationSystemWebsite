<?php
class TransToolAction extends Action{
	
	function index()
	{
		if(Session::get('username')=="")
    	{
    		$this->redirect('login');
    		return;
    	} 
		$this->display("index");
	}

	
}
?>