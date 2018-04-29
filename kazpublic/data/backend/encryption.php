<?php
	/*
	Summary
	Exposes encryption related functionality
	
	Written by Alden Viljoen
	*/

	if(!class_exists("CEncryption")) {
		class CEncryption {
			private $_key = NULL;
			private $_iv = NULL;
			
			function __construct($k = NULL, $i = NULL) {
				$this->_key = $k;
				$this->_iv = $i;
			}
			
			public function EncryptData($data, $key = NULL) {
				$key = ($key === NULL) ? $this->_key : $key;
				$iv = ($this->_iv !== NULL) ? $this->_iv : $this->GenerateIV();
				
				$result = openssl_encrypt($data, "AES-128-CBC", $key, 0, $iv);
				if($result !== FALSE) {
					return array(
									"data" => $result,
									"iv" => $iv
								);
				}
				
				return FALSE;
			}
			
			public function EncryptData_GenerateIV($data, $key = NULL) {
				$key = ($key === NULL) ? $this->_key : $key;
				$iv = $this->GenerateIV();
				
				$result = openssl_encrypt($data, "AES-128-CBC", $key, OPENSSL_RAW_DATA, $iv);
				if($result !== FALSE) {
					return array(
									"data" => $result,
									"iv" => $iv
								);
				}
				
				return FALSE;
			}
			
			public function DecryptData($data, $iv = NULL, $key = NULL) {
				$result = "";
				$uk = ($this->_key !== NULL) ? $this->_key : $key;
				$uiv = ($this->_iv !== NULL) ? $this->_iv : $iv;
				
				if(($result = openssl_decrypt($data, "AES-128-CBC", $uk, 0, $uiv)) !== FALSE) {
					return $result;
				}
				return FALSE;
			}
			
			public function DecryptErrorReport($raw, $key, $iv) {
				$str = "";
				$str = $str . "== FAILED TO DECRYPT USER DATA ==";
				$str = $str . "Raw: (" . $this->PrintData($raw, TRUE) . ")\n";
				$str = $str . "Key: (" . $this->PrintData($key, TRUE) . ")\n";
				$str = $str . "IV: (" . $this->PrintData($iv, TRUE) . ")\n";
				
				error_log($str);
			}
			
			public function PrintData($data, $return) {
				$result = "";
				for($i = 0; $i < strlen($data); $i++) {
					$result  = $result . ord($data[$i]) . " ";
				}
				
				if($return === TRUE) {
					return $result;
				}else{
					error_log($result);
				}
			}
			
			private function GenerateIV() {
				$buffer = "";
				
				srand(time());
				for($i = 0; $i < 16; $i++) {
					$buffer = $buffer . chr(rand(48, 122));
				}
				return $buffer;
			}
		}
	}
?>