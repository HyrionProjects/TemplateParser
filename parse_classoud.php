<?php
session_name("Hyrion_Session");
session_start();
class Parse_Class
{
	
	public $file;
	public $content;
	var $errors = '';
	var $getfile = false;
	var $l_delim = '{';
	var $r_delim = '}';
	
	/*
	public function __construct($file=false)	
	{
	} */
	
	
	
	public function parse($filename,$data='')
	{
		if(isset($filename))
		{
			$content = $this->get_file($filename);
			if($content)
			{
				if(isset($data))
				{
					//Hier returnt hij de content naar de controller
					//$setting = new settings();
					//$data['setting.base_url'] = $setting->base_url();
					$content = $this->testen($content);
					return $this->start_parce($content,$data);
				}else{
					//only include
					return $content;
				}
			}else{
				return false;
			}
		}
	}
	
	function get_file($filename)
	{
		$filename = $filename;
		if(file_exists($filename))
		{
			return file_get_contents($filename);
		}else{
			return false;
		}
	}
	
	function start_parce($content,$data)
	{
		
		if($content == '' || empty($content))
		{
			//Als er geen content is dan return False
			return false;
		}
		
		foreach($data as $key => $val)
		{
			if(!is_array($val))
			{
				$content = $this->parse_one($key,$val,$content);		
			}
			else
			{
				//als er meerdere values zijn in de array
				$content = $this->parse_array($key,$val,$content);
			}
		}
		
		return $content;
	}
	
	function parse_one($key, $val, $content)
	{
		$key = "{".$key."}";
		return str_replace($key, $val, $content);
	}
	
	function parse_array($var,$data,$content)
	{
		if (false === ($match = $this->match($content, $var)))
		{
			return $content;
		}
		$data_all = '';
		if(!empty($data))
		{
			foreach($data as $value)
			{
				$cache = $match['1'];
				foreach($value as $key => $val)
				{
					if(is_array($val))
					{
						$cache = $this->parse_array($key,$val,$cache);
					}else{
						$cache = $this->parse_one($key,$val,$cache);
					}
				}
				$data_all .= $cache;
			}
		}
		return str_replace($match['0'], $data_all, $content);
	}
	
	function match($content, $var)
	{
		if(!preg_match("|{".$var."}(.+?){/".$var."}|s", $content, $match))
		{
			return FALSE;
		}else{
			return $match;
		}
	}
	
	function testen($content)
	{
		//Voor de IF en END IF
		if (!preg_match_all("|".preg_quote ('<!-- IF')." (.+?) ".preg_quote ('-->')."(.+?)".preg_quote ('<!-- END IF -->')."|s", $content, $match))
		{
			//echo "false!";
		}else{
			//echo"<pre>";
			//print_r($match);
			//var_dump($match);
			//echo"</pre>";
			
			
				foreach($match[1] as $key2=>$val2)
				{
					if($val2 == "USER == LOGGED_IN")
					{
						if(!preg_match("|(.+?)".preg_quote ('<!-- ELSE -->')."(.+?)|s", $content, $match2))
						{
							if(isset($_SESSION['user_id']))
							{
								//echo "True!!";
								$content = preg_replace("|".preg_quote ('<!-- IF USER == LOGGED_IN -->')."|s", "", $content,1);
								$content = preg_replace("|".preg_quote ('<!-- END IF -->')."|s", "", $content,1);
							}else{
								//echo "True but not login!";
								$content = preg_replace("|".preg_quote ('<!-- IF USER == LOGGED_IN -->')."(.+?)".preg_quote ('<!-- END IF -->')."|s", "", $content);
							}
						}else{
							if(isset($_SESSION['user_id']))
							{
								//echo "True!!";
								$content = preg_replace("|".preg_quote ('<!-- IF ')."(.+?)".preg_quote (' -->')."(.+?)".preg_quote ('<!-- ELSE -->')."(.+?)".preg_quote ('<!-- END IF -->')."|s", "$2", $content);
							}else{
								//echo "True but not login!";
								$content = preg_replace("|".preg_quote ('<!-- IF ')."(.+?)".preg_quote (' -->')."(.+?)".preg_quote ('<!-- ELSE -->')."(.+?)".preg_quote ('<!-- END IF -->')."|s", "$3", $content);
							}
						}
					}
				}
				
		}
		
		
		return $content;
	}
}