<?php

	/**
	 * UpdateCheck
     * Copyright (C) 2012 Kevin van Steijn, Maarten Oosting
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
	class updateCheck
	{
		/**
		 * Version of the Updatecheck system
		 *
		 * @since 1.0
		 * @access private
		 * @author Kevin van Steijn
		 */
		private $version = '1.2';
	
		/**
		 * This variable is for FTP handle
		 *
		 * @since 1.0
		 * @access private
		 * @author Kevin van Steijn
		 */
		private static $ftp = false;
		
		/**
		 * This variable is for the storage of FTP server
		 *
		 * @since 1.0
		 * @access public
		 * @author Kevin van Steijn
		 */
		public static $ftp_server;
		
		/**
		 * This variable is for the storage of FTP user
		 *
		 * @since 1.0
		 * @access public
		 * @author Kevin van Steijn
		 */
		public static $ftp_user;
		
		/**
		 * This variable is for the storage of FTP password
		 *
		 * @since 1.0
		 * @access public
		 * @author Kevin van Steijn
		 */
		public static $ftp_password;
		
		/**
		 * This variable is used for the storage of information for the updates
		 *
		 * @since 1.2
		 * @access private
		 * @author Kevin van Steijn
		 */
		private $update_list = array();
		
		/**
		 * This variable is used for the storage of errors
		 *
		 * @since 1.2
		 * @access private
		 * @author Kevin van Steijn
		 */
		public $update_error = array();
		
		/**
		 * Add UpdateCheck to the UpdateCheck system
		 *
		 * @since 1.2
		 * @access public
		 * @author Kevin van Steijn
		 */
		public function __construct()
		{
			$this->SetUpdate('http://code.kvansteijn.nl/updatecheck', $this->version);	
		}
		
		/**
		 * Add a system to the UpdateCheck system 
		 *
		 * @since 1.0
		 * @access public
		 * @author Kevin van Steijn
		 */
		public function SetUpdate($url, $current_version)
		{
			$ctx = stream_context_create(array('http' => array('timeout' => 1)));
			$content = @file_get_contents($url . '/install.txt', 0, $ctx);
			if ($content) {
				$lines = explode("\n", $content);
				if (is_numeric($lines[0])) {
					if ($lines[0] > $current_version) {
						$current_version = $lines[0];
						unset($lines[0]);
						
						$information = debug_backtrace();
						$information = array(
							'url' => $url . '/' . $current_version,
							'version' => $current_version,
							'dir' => dirname($information[0]['file']),
							'list' => $lines
						);
						
						$this->update_list[$url] = $information;
					}
				}
			}
		}
		
		/**
		 * Set a FTP connection
		 *
		 * @since 1.0
		 * @access public
		 * @author Kevin van Steijn
		 */
		public static function FTPConnect()
		{
			if (!empty(self::$ftp_server) && !empty(self::$ftp_user) && !empty(self::$ftp_password)) {
				$conn = @ftp_connect(self::$ftp_server);
				if ($conn) {
					if (@ftp_login($conn, self::$ftp_user, self::$ftp_password)) {
						$this->ftp = $conn;
					}
				}
				
				ftp_close($conn);
			}
		}
		
		/**
		 * Remove FTP connection
		 *
		 * @since 1.2
		 * @access public
		 * @author Kevin van Steijn
		 */
		public static function FTPClose()
		{
			if (!empty(self::$ftp)) {
				ftp_close(self::$ftp);
				self::$ftp = false;
			}
		}
		
		/**
		 * Set a FTP connection
		 *
		 * @since 1.0
		 * @access public
		 * @author Kevin van Steijn
		 */
		public static function UChmod($path, $mode)
		{
			$ftp = self::$ftp;
			if(!empty($ftp)) {
				if (!ftp_site($ftp, 'CHMOD ' . $mode . ' ' . $path)) return false;
			} else if(!@chmod($path, $mode)) return false;
			
			return true;		
		}
		
		/**
		 * How the file must be handled
		 *
		 * @since 1.2
		 * @access public
		 * @author Kevin van Steijn
		 */
		public static function FileHandle($path, $content, $mode)
		{
			$dir = dirname($path);
			if (!is_writable($dir)) {
				$chmod = true;
				if(!self::UChmod($dir, 0777)) return false;
			} else $chmod = false;

			$action = false;
			if ($handle = @fopen($path, $mode)) {				
				if (fwrite($handle, $content)) {
					fclose($handle);
					$action = true;
				}
			}
			
			if ($chmod) self::UChmod($dir, '0755');
			
			return $action;
		}
		
		/**
		 * Write a file or add content
		 *
		 * @since 1.0
		 * @access public
		 * @author Kevin van Steijn
		 */
		public static function WriteFile($path, $content)
		{
			return self::FileHandle($path, $content, 'w');
		}
		
		
		/**
		 * Add content to a file
		 *
		 * @since 1.2
		 * @access public
		 * @author Kevin van Steijn
		 */
		public static function AddContent($path, $content)
		{
			return self::FileHandle($path, $content, 'a');
		}
		
		/**
		 * Make a directory
		 *
		 * @since 1.0
		 * @access public
		 * @author Kevin van Steijn
		 */
		public static function MakeDir($current_path, $create_path)
		{
			if (!is_writable($current_path)) {
				$chmod = true;
				if(!self::UChmod($current_path, 0777)) return false;
			} else $chmod = false;
			
			$action = mkdir($create_path, 0755) ? true : false;
			
			if ($chmod) self::UChmod($current_path, '0755');
			
			return $action;
		}
		
		/**
		 * Update files or write new files
		 *
		 * @since 1.0
		 * @access public
		 * @author Kevin van Steijn
		 */
		public function Update()
		{
			$update_list = $this->update_list;
			if (count($update_list) > 0) {
				self::FTPConnect();
				
				foreach($update_list as $url => $arg) {
					$log = "------ Version " . $arg['version'] . " (" . date("F j, Y, g:i a") . ") ------------- \n";
					$error = array();
					
					foreach($arg['list'] as $filename) {
						try {
							$ctx = stream_context_create(array('http' => array('timeout' => 1)));
							$content = @file_get_contents($arg['url'] . '/' . $filename, 0, $ctx);
							if ($content) {
								$expl = explode('.', $filename);
								if(end($expl) == 'txt' && ($amount = count($expl)) > 1) unset($expl[$amount]);
								$filename = implode('.', $expl);
								
								$path_file = $arg['dir'] . '/' . $filename;
								if (!is_dir($dir = dirname($path_file))) {
									if (!self::MakeDir($arg['dir'], $dir)) throw new Exception('Failed to create folders (' . $dir . ')');
								}
								
								if(!self::WriteFile($path_file, $content)) throw new Exception('Failed to update file (' . $path_file .  ')');
							} else throw new Exception('Failed to load a file from ' . $arg['url']);
							$log .= $path_file . "\n";
						} catch (Exception $e) {
							$error[] = $e->getMessage();
							$this->update_error[] = $e;	
						}
					}

					if (count($error) > 0) $log .= "\n\nError:\n" . implode("\n", $error);
					self::AddContent($arg['dir'] . '/log.txt', $log . "\n\n");
				}
				
				self::FTPClose();
			}
		}
	}

?>