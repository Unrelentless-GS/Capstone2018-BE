<?php
	/*
	Summary
	The base class for a remote connection from a user.
	This should be extended by an endpoint script.
	
	Written by Alden Viljoen
	*/
	
	require_once("jukeboxdb.php");
	require_once("encryption.php");
	
	if(!class_exists("CNetwork")) {
		class CNetwork extends CJukeboxDB {
			private $_userAgent = ""; // The only user agent that should ever contact this script.
			
			private $_queryValid = FALSE;
			private $_receivedValues = NULL;
			
			protected $_NET_SESSION = NULL;
			
			function __construct($name, $user_agent, $key, $required_vars) {
				parent::__construct($name);
				
				$this->_receivedValues = array();
				$this->_userAgent = $user_agent;
			}
			
			public function QueryValid() {
				return $this->_queryValid;
			}
			
			public function GetValue($name) {
				if(!isset($this->_receivedValues[$name]))
					return FALSE;
				
				return $this->_receivedValues[$name];
			}
			
			protected function SessionRequired() {
				if(!isset($_COOKIE["JukeboxCookie"]))
					return FALSE;
				
				$userHash = $_COOKIE["JukeboxCookie"];
				if($userHash == NULL) {
					print("no presence");
					return;
				}
				
				$this->_NET_SESSION = $this->GetSessionInfo($userHash);
				if($this->_NET_SESSION === NULL)
					return FALSE;
				return TRUE;
			}

			private function MakeURLSafe($text) {
				$text = str_replace("+", "-", $text);
				$text = str_replace("/", "_", $text);
				
				return $text;
			}
		}
	}
?>