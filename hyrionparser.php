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
		 * @access private
		 * @author Maarten Oosting
		 */
		private $content = false;

		/**
		 * This variable is for set the prefix and suffix
		 *
		 * @since 1.0
		 * @access public
		 * @author Maarten Oosting
		 */
		public $p_prefix = '{';
		public $p_suffix = '}';

		/**
		 * This variable is for saving the error's
		 *
		 * @since 1.0
		 * @access private
		 * @author Maarten Oosting
		 */
		private $error = false;

		/**
		 * This variable is for calling the classname of the parser functions
		 *
		 * @since 1.0
		 * @access public
		 * @author Maarten Oosting
		 */
		public $classname_parserfunctions;

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
		public function parse($filename, $data)
		{
			try {
				$action = false;
				if (!empty($filename) && is_array($data)) {
					if ($content = $this->get_file($filename)) {						
						//Hier returnt hij de content naar de controller
						$content = $this->ParseCalledFunctions($content);				
						$content = $this->parce_ifs($content);
						if ($content = $this->start_parce($content, $data)) {
							$this->content = $content;
							$action = true;
						}
					}
				}
				return $action;
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
				$content = $this->content;
				if($content) return $content;

				throw new Exception("State is false", 372);
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
		private function get_file($filename)
		{
			if (file_exists($filename)) {
				return file_get_contents($filename);
			} else return false;
		}

		/**
		 * Start_parce
		 * Check if content is a arrray
		 *
		 * @since 1.0
		 * @access private
		 * @author Maarten Oosting
		 */			
		private function start_parce($content,$data)
		{
			foreach($data as $key => $val)
			{
				if (is_array($val)) {
					$content = $this->parse_array($key,$val,$content);		
				} else $content = $this->parse_one($key,$val,$content);
			}

			return $content;
		}

		/**
		 * Parse the single content
		 *
		 * @since 1.0
		 * @access private
		 * @author Maarten Oosting
		 */			
		private function parse_one($key, $val, $content)
		{
			$key = $this->p_prefix . $key . $this->p_suffix;
			return str_replace($key, $val, $content);
		}


		/**
		 * Parse the array content
		 *
		 * @since 1.0
		 * @access private
		 * @author Maarten Oosting
		 */	
		private function parse_array($var,$data,$content)
		{
			$match = $this->match($content, $var);
			if ($match == false) return $content;

			$data_all = '';
			foreach($data as $value) {
				if (is_array($value)) {
					$cache = $match['1'];
					foreach($value as $key => $val) {
						if (is_array($val)) {
							$cache = $this->parse_array($key,$val,$cache);
						} else $cache = $this->parse_one($key,$val,$cache);
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
		private function match($content, $var)
		{
			$p_prefix = $this->p_prefix;
			$p_suffix = $this->p_suffix;

			if(!preg_match("|". $p_prefix . $var . $p_suffix . "(.+?)" . $p_prefix . '/' . $var . $p_suffix . "|s", $content, $match)) {
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

		public $test_counter = 0;
		public $test_counter2 = 0;
		public $counter_ends = 0;
		public $test_boolean = false;
		public $test_boolean2 = true;
		public $test_array = array();
		public $test_output = '';
		public $test_output2 = '';
		public $find_else = false;

		public $test_temp = 0;

		private function parce_ifs($content)
		{
			$classname = isset($this->classname_parserfunctions) ? $this->classname_parserfunctions : 'Parser_functions';
			$content_array = explode(PHP_EOL, $content);
			//print_r($content_array);

			foreach ($content_array as $key => $value)
			{

				//print_r($this->test_array);

				if(preg_match("|".preg_quote ('<!-- IF ').'(.+?)'.preg_quote ('-->')."|s", $value, $match1))
				{
					if($this->test_boolean2 == true)
					{
						$this->test_array[] = $match1[1];
						//echo 'IF Start: '.$match1[1].$this->test_counter.PHP_EOL;
						$this->test = $match1[1];
						$this->test_boolean = true;

						if(preg_match("|(.+?)\((.+?)\) \=\= ([A-Za-z0-9]{1,})(.+?)|s", $match1[1], $match2))
						{
							//print_r($match2);
							$class = new $classname();
							$val1 = $match2[1];

							if($match2[3] == 'TRUE')
							{
								$conpare = (bool) TRUE;
							}elseif ($match2[3] == 'FALSE') {
								$conpare = (bool) FALSE;
							}else{
								$conpare = $match2[3];
							}


							if($class->$val1($match2[2]) == $conpare)
							{
								//Gaan we verder
								$this->test_boolean2 = true;
								//echo $class->$val1($match2[2])." : ".$conpare.PHP_EOL;
							}else{
								//Gaan we stoppen
								$content_array[$key] = ' ';
								$this->test_boolean2 = false;
								$this->test_counter2 = $this->test_counter+1;

								//$this->test_output2 .= $value.PHP_EOL;
							}
						}
					}else{
						$this->test_output2 .= $value.PHP_EOL;
						$content_array[$key] = ' ';
					}
					$this->test_counter++;
					$this->test_temp++;
				}

				if ($this->test_boolean2 == false) {
					if(!preg_match("|".preg_quote ('<!-- END IF -->')."|s", $value) && !preg_match("|".preg_quote ('<!-- IF ').'(.+?)'.preg_quote ('-->')."|s", $value))
					{
						$this->test_output2 .= $value.'.'.PHP_EOL;
						$content_array[$key] = ' ';
					}
				}

				if ($this->test_boolean == true) {
					$this->test_output .= $value.', '.PHP_EOL;
				}

				if(preg_match("|".preg_quote ('<!-- END IF -->')."|s", $value))
				{
					//echo $this->test_counter.':'.$this->test_temp.'.'.$this->test_boolean2.PHP_EOL;
					//$this->test_counter--;
					//echo $this->test_counter.' : ' . $this->counter_ends . ', ' . $this->test_temp;
					if ($this->test_boolean2 == true) {
						//echo 'END IF: '.$this->test_array[$this->test_counter].$this->test_counter.PHP_EOL;
						unset($this->test_array[$this->test_counter]);
						if ($this->test_counter == 0) {
							$this->test_array = array();
							$this->test_boolean = false;


							//echo $this->test_output.PHP_EOL;
							$this->test_output = '';
							echo PHP_EOL;
						}
					}else{
						/*if ($this->test_counter != 0) {
							$this->test_output2 .= $value.PHP_EOL;
							$content_array[$key] = ' ';
							//$this->test_boolean2 = true;
						}else{
							$content_array[$key] = ' ';
							$this->test_boolean2 = true;
						}*/

						echo $this->test_counter2.':'.$this->test_temp.'.'.$key.PHP_EOL;
						

						if ($this->test_counter2 == $this->test_temp) {

							//echo $this->test_counter.' : ' . $this->counter_ends . ', ' . $this->test_temp;
							$content_array[$key] = ' ';
							$this->test_counter = $this->test_counter -1;
							$this->test_boolean2 = true;
						}else{
							$content_array[$key] = ' ';
							//echo "HIER!";
							//$this->test_boolean2 = true;
							$this->test_temp--;
						}

					}
					echo PHP_EOL;
					$this->counter_ends++;
				}

			}
			//echo $this->test_output2;
			print_r($content_array);
			return "q";
		}

		private function parce_ifs2($content)
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
						//Check IF functions have argument
						//Example Function($argument) == TRUE;
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
									}
								}
							}else{
								//throw error!
							}
						}elseif(preg_match("|(.+?)\(\) \=\= ([A-Za-z0-9]{1,})(.+?)|s", $val2, $match2)){
							if(!preg_match("|[\W]+|s", $match2[1], $match3))
							{
								$functions = new $classname();
								if(!preg_match("|".preg_quote ('<!-- ELSE -->')."|s", $match[2][$key2], $match3))
								{
									if($functions->$match2[1]() == $match2[2])
									{
										$start_tag = "<!-- IF ".$val2." -->";
										$content = preg_replace("|".preg_quote($start_tag)."(.+?)".preg_quote ('<!-- END IF -->')."|s", $match[2][$key2], $content,1);
									}else{
										$start_tag = "<!-- IF ".$val2." -->";
										$content = preg_replace("|".preg_quote($start_tag)."(.+?)".preg_quote ('<!-- END IF -->')."|s", "", $content,1);
									}
								}else{
									$match[2][$key2] .= "<!-- END IF -->";
									preg_match("|(.+?)\<\!\-\- ELSE \-\-\>(.+?)\<\!\-\- END IF \-\-\>|s", $match[2][$key2], $match4);
									if($functions->$match2[1]() == $match2[2])
									{
										$start_tag = "<!-- IF ".$val2." -->";
										$content = preg_replace("|".preg_quote($start_tag)."(.+?)".preg_quote ('<!-- END IF -->')."|s", $match4[1], $content,1);
									}else{
										$start_tag = "<!-- IF ".$val2." -->";
										$content = preg_replace("|".preg_quote($start_tag)."(.+?)".preg_quote ('<!-- END IF -->')."|s", $match4[2], $content,1);
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

		private function ParseCalledFunctions($content)
		{
			$classname = isset($this->classname_parserfunctions) ? $this->classname_parserfunctions : 'Parser_functions';
			if (!class_exists($classname)) throw new Exception("Called function class is not a (valid) class", 458);
			if (preg_match_all("|".preg_quote ('<!-- LOAD_FUNCTION[')."(.+?)".preg_quote ('] -->')."|s", $content, $match))
			{
				$function_class = new $classname();
				foreach ($match[0] as $key1 => $val1) {
					$output_function = '';
					$function_name = $match[1][$key1];
					$output_function = $function_class->$function_name();
					$content = str_replace($val1, $output_function, $content);
				}
			}
			return $content;
		}
	}
?>