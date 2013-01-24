<?php
	
	/**
	 * Hyrion Parser
	 * Copyright (C) 2012 Maarten Oosting
	 *
	 * This program is free software; you can redistribute it and/or modify
	 * it under the terms of the GNU General Public License as published by
	 * the Free Software Foundation; either version 2 of the License, or
	 * (at your option) any later version.
	 * 
	 * This program is distributed in the hope that it will be useful,
	 * but WITHOUT ANY WARRANTY; without even the implied warranty of
	 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	 * GNU General Public License for more details.
	 *
	 * You should have received a copy of the GNU General Public License along
	 * with this program; if not, write to the Free Software Foundation, Inc.,
	 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
	 */
	class Hyrion_parser
	{
		/**
		 * This variable is for saving the output
		 *
		 * @since 1.0
		 * @access public
		 * @author Maarten Oosting
		 */
		Public static $content;

		/**
		 * This variable is for set the prefix and suffix
		 *
		 * @since 1.0
		 * @access public
		 * @author Maarten Oosting
		 */
		public static $p_prefix = '{';
		public static $p_suffix = '}';

		/**
		 * This variable is for checking the state of the parser
		 *
		 * @since 1.0
		 * @access public
		 * @author Maarten Oosting
		 */
		Public static $state = false;

		/**
		 * This variable is for saving the error's
		 *
		 * @since 1.0
		 * @access private
		 * @author Maarten Oosting
		 */
		Private static $error = false;


		/**
		 * This variable is for calling the classname of the parser functions
		 *
		 * @since 1.0
		 * @access public
		 * @author Maarten Oosting
		 */
		Public $classname_parserfunctions;

		/**
		 * Constructor
		 *
		 * @since 1.0
		 * @access public
		 * @author Maarten Oosting
		 */
		public function __construct()
		{
			/**
			 * UpdateCheck
			 * Copyright (C) 2012 KvanSteijn
			 */
				//UpdateCheck::SetUpdate('http://hyrion.com/updates/parser/standalone/', 1.1);
			/**
			 * End UpdateCheck
			 */
		}

		/**
		 * setFunctionClass
		 * You can set the class name for the function Class
		 *
		 * @since 1.0
		 * @access public
		 * @author Maarten Oosting
		 */

		public function setFunctionClass($class)
		{
			$this->classname_parserfunctions = $class;
		}
		
		/**
		 * Parse
		 * You can call this function for parse a file
		 *
		 * @since 1.0
		 * @access public
		 * @author Maarten Oosting
		 */
		public function parse($filename,$data='')
		{
			try {
				if(isset($filename))
				{
					$this->get_file($filename);
					self::$state = true;
					if(isset(self::$content))
					{
						if(isset($data))
						{
							//Hier returnt hij de content naar de controller
							$this->ParseCalledFunctions();
							self::$content = $this->parce_ifs(self::$content);
							$this->start_parce(self::$content,$data);

							return true;
						}else{
							//only include
							return true;
						}
					}else{
						return false;
					}
				}
			} catch (Exception $e) {
				print_r($e->getMessage());
				exit();
			}
		}

		/**
		 * getContent
		 * This function return the parsered content
		 *
		 * @since 1.0
		 * @access public
		 * @author Maarten Oosting
		 */			
		public function getContent()
		{
			try{
				if(self::$state == true)
				{
					return self::$content;
				}else throw new Exception("State is false", 372);
			} catch (Exception $e) {
				print_r($e);
				exit();
			}
		}

		/**
		 * get_files
		 *
		 * @since 1.0
		 * @access private
		 * @author Maarten Oosting
		 */		
		Private function get_file($filename)
		{
			if(file_exists($filename)) {
				self::$content = file_get_contents($filename);
				return true;
			}else{
				return false;
			}
		}

		/**
		 * Start_parce
		 * Check if content is a arrray
		 *
		 * @since 1.0
		 * @access private
		 * @author Maarten Oosting
		 */			
		Private function start_parce($content,$data)
		{
			$output = self::$content;
			if($output == '' || empty($output)) return false;
			
			foreach($data as $key => $val)
			{
				if(!is_array($val))
				{
					$output = $this->parse_one($key,$val,$output);		
				}
				else
				{
					//als er meerdere values zijn in de array
					$output = $this->parse_array($key,$val,$output);
				}
			}
			
			//Set output in content
			self::$content = $output;
			return true;
		}

		/**
		 * Parse the single content
		 *
		 * @since 1.0
		 * @access private
		 * @author Maarten Oosting
		 */			
		Private function parse_one($key, $val, $content)
		{
			$key = "{".$key."}";
			return str_replace($key, $val, $content);
		}
		

		/**
		 * Parse the array content
		 *
		 * @since 1.0
		 * @access private
		 * @author Maarten Oosting
		 */	
		Private function parse_array($var,$data,$content)
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
		
		/**
		 * -
		 *
		 * @since 1.0
		 * @access private
		 * @author Maarten Oosting
		 */	

		Private function match($content, $var)
		{
			if(!preg_match("|".self::$p_prefix.$var.self::$p_suffix."}(.+?){/".self::$p_prefix.$var.self::$p_suffix."}|s", $content, $match))
			{
				return FALSE;
			}else{
				return $match;
			}
		}

		/**
		 * Parse the IF statments
		 *
		 * @since 1.0
		 * @access private
		 * @author Maarten Oosting
		 */			
		private function parce_ifs($content)
		{
			$classname = isset($this->classname_parserfunctions) ? $this->classname_parserfunctions : 'Parser_functions';
			if (!class_exists($classname)) {
				throw new Exception("Called function class is not a (valid) class", 458);
			}else{
				if (!preg_match_all("|".preg_quote ('<!-- IF')." (.+?) ".preg_quote ('-->')."(.+?)".preg_quote ('<!-- END IF -->')."|s", $content, $match))
				{
					//echo "false!";
				}else{
					foreach($match[1] as $key2=>$val2)
					{
						if(preg_match("|(.+?)\((.+?)\) \=\= ([A-Za-z0-9]{1,})(.+?)|s", $val2, $match2))
						{
							if(!preg_match("|[\W]+|s", $match2[1], $match3))
							{
								$functions = new $classname();
								
								if(!preg_match("|".preg_quote ('<!-- ELSE -->')."|s", $match[2][$key2], $match3))
								{
									if(isset($match2[2]))
									{
										if($functions->$match2[1]($match2[2]) == $match2[3])
										{
											$start_tag = "<!-- IF ".$val2." -->";
											$content = preg_replace("|".preg_quote($start_tag)."(.+?)".preg_quote ('<!-- END IF -->')."|s", $match[2][$key2], $content,1);
										}else{
											$start_tag = "<!-- IF ".$val2." -->";
											$content = preg_replace("|".preg_quote($start_tag)."(.+?)".preg_quote ('<!-- END IF -->')."|s", "", $content,1);
										}
									}else{
										if($functions->$match2[1]() == $match2[3])
										{
											$start_tag = "<!-- IF ".$val2." -->";
											$content = preg_replace("|".preg_quote($start_tag)."(.+?)".preg_quote ('<!-- END IF -->')."|s", $match[2][$key2], $content,1);
										}else{
											$start_tag = "<!-- IF ".$val2." -->";
											$content = preg_replace("|".preg_quote($start_tag)."(.+?)".preg_quote ('<!-- END IF -->')."|s", "", $content,1);
										}
									}
								}else{
									if(isset($match2[2]))
									{
										$match[2][$key2] .= "<!-- END IF -->";
										preg_match("|(.+?)\<\!\-\- ELSE \-\-\>(.+?)\<\!\-\- END IF \-\-\>|s", $match[2][$key2], $match4);
										if($functions->$match2[1]($match2[2]) == $match2[3])
										{
											$start_tag = "<!-- IF ".$val2." -->";
											$content = preg_replace("|".preg_quote($start_tag)."(.+?)".preg_quote ('<!-- END IF -->')."|s", $match4[1], $content,1);
										}else{
											$start_tag = "<!-- IF ".$val2." -->";
											$content = preg_replace("|".preg_quote($start_tag)."(.+?)".preg_quote ('<!-- END IF -->')."|s", $match4[2], $content,1);
										}
									}else{
										$match[2][$key2] .= "<!-- END IF -->";
										preg_match("|(.+?)\<\!\-\- ELSE \-\-\>(.+?)\<\!\-\- END IF \-\-\>|s", $match[2][$key2], $match4);
										if($functions->$match2[1]() == $match2[3])
										{
											$start_tag = "<!-- IF ".$val2." -->";
											$content = preg_replace("|".preg_quote($start_tag)."(.+?)".preg_quote ('<!-- END IF -->')."|s", $match4[1], $content,1);
										}else{
											$start_tag = "<!-- IF ".$val2." -->";
											$content = preg_replace("|".preg_quote($start_tag)."(.+?)".preg_quote ('<!-- END IF -->')."|s", $match4[2], $content,1);
										}
									}
								}
							}else{
								//throw error!
							}
						}
					}
				}
			}
			return $content;
		}

		Private function ParseCalledFunctions()
		{
			$classname = isset($this->classname_parserfunctions) ? $this->classname_parserfunctions : 'Parser_functions';
			if (!class_exists($classname)) {
				throw new Exception("Called function class is not a (valid) class", 458);
			}else{
				if (preg_match_all("|".preg_quote ('<!-- LOAD_FUNCTION[')."(.+?)".preg_quote ('] -->')."|s", self::$content, $match))
				{
					$output = self::$content;
					$function_class = new $classname();
					foreach ($match[0] as $key1 => $val1) {
						$output_function = '';
						$function_name = $match[1][$key1];
						$output_function = $function_class->$function_name();
						$output = str_replace($val1, $output_function, $output);
					}
					self::$content = $output;
				}
			}
		}
	}
?>
