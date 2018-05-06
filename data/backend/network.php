<?php
	/*
	Summary
	The base class for a remote connection from a user.
	This should be extended by an endpoint script.
	
	Written by Alden Viljoen
	*/
	
	require_once("jukeboxdb.php");
	require_once("funcs.php");
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
				
				$this->CheckIfTokenIsUsable();
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

			// TODO: Test this.
			// Checks if the AuthAccessToken is valid, if not, requests a new one using refresh token.
			private function CheckIfTokenIsUsable() {
				if($this->_NET_SESSION === NULL && $this->SessionRequired() === FALSE)
					return;
				
				if(time() > $this->_NET_SESSION["AuthExpires"]) {
					global $JUKE;
					
					error_log("Token is NOT usable");
					// Authorisation has expired.
					// Request a new token.
					
					$JUKE->PostRequest(
						"https://accounts.spotify.com/api/token",
							"Content-type: application/x-www-form-urlencoded\r\n" .
							"Authorization: Basic " . base64_encode(CLIENT_ID . ":" . CLIENT_SECRET),
						array(
							"grant_type"		=> "refresh_token",
							"refresh_token"		=> $this->_NET_SESSION["AuthRefreshToken"]
						),
						
						function($response, $state) {
							$json = json_decode($response, TRUE);
							if($json !== NULL) {
								$accessToken = $json["access_token"];
								$expiresAt = time() + $json["expires_in"];
								
								$refreshToken = NULL;
								if(isset($json["refresh_token"]))
									$refreshToken = $json["refresh_token"];
								else
									$refreshToken = $this->_NET_SESSION["AuthRefreshToken"];
								
								$this->UpdateAuthInfo($this->_NET_SESSION["PartyID"], $accessToken, $refreshToken, $expiresAt);
							}
						},
						
						NULL
					);
				}else{
					error_log("Token is usable");
				}
			}
			
			private $_updateAuthInfo = "
				UPDATE authentication a
				
				INNER JOIN party p
				ON p.AuthID=a.AuthID
				
				SET 
					AuthAccessToken=:access, 
					AuthRefreshToken=:refresh, 
					AuthExpires=:expires
				
				WHERE p.PartyID=:partyid
			";
			private function UpdateAuthInfo($partyid, $accessToken, $refreshToken, $expiresAt) {
				$this->RunQuery($this->_updateAuthInfo,
					[
						"access"			=> $accessToken,
						"refresh"			=> $refreshToken,
						"expires"			=> $expiresAt,
						"partyid"			=> $partyid
					]);
			}
			
			private function MakeURLSafe($text) {
				$text = str_replace("+", "-", $text);
				$text = str_replace("/", "_", $text);
				
				return $text;
			}
		}
	}
?>