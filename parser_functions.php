<?php
Class Parser_functions
{
	
	function LOGGED_IN($arg1=null)
	{
		$user = 0;
		if(isset($arg1) && $arg1 == "[USER]" && isset($_SESSION['user_id']))
		{
			$user = $_SESSION['user_id'];
		}
		
		if(isset($user) && $user != 0)
		{
			return true;
		}
		return false;
	}
	
}

// <!-- IF LOGGED_IN([USER]) == TRUE; -->
// <!-- END IF -->

// <!-- IF GET_GROUP([USER]) == 'ADMIN'; -->
// <!-- END IF -->

