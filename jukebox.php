<?php
	/*
	Summary
	An endpoint script that'll act as the centre for all operations.
	
	Written by Alden Viljoen
	*/
	
	require_once("data/backend/network.php");
	require_once("data/forms/newparty.php");
	
	require_once("data/auth.php");
	require_once("data/party.php");
	require_once("data/user.php");
	
	if(!class_exists("CJukebox")) {
		class CJukebox extends CNetwork {
			private $_serveAs = NULL;
			
			function __construct() {
				parent::__construct("Jukebox", "", "", array ());
				$this->Start();
			}
			
			public function Start() {
				global $AUTHORISATION;
				
				// Will this be served in HTML or JSON?
				// TODO: implement for JSON.
				if(isset($_POST["txtServeType"]))
					$this->_serveAs = $_POST["txtServeType"];
				else
					$this->_serveAs = "HTML";

				if(isset($_COOKIE["JukeboxCookie"])) {
					$this->ServeExistingInfo();
				}elseif(isset($_POST["txtPartyName"])) {
					// User has filled out all their personal info about the party.
					// Finish the setup.
					$this->FinishCreatingParty();
				}elseif(isset($_POST["btnHost"])) {
					// User is wanting to start a party.
					// Ask for their permission.
					$AUTHORISATION->AuthoriseUser();
				}elseif(isset($_GET["state"])) {
					// User has given us permission.
					// Serve the custom info screen.
					$this->RequestPartyInfo();
				}elseif(isset($_POST["btnGuest"])) {
					// TODO.
					return;
				}
			}
			
			private function ServeExistingInfo() {
				$userHash = $_COOKIE["JukeboxCookie"];
				
				// Check if user's the host, if so, serve host options as well otherwise just serve user.
				print("Welcome to your party!");
			}
			
			private function RequestPartyInfo() {
				global $AUTHORISATION;
				
				$AUTHORISATION->HandleUserDecision(
					// The user has allowed us to continue.
					function($response, $state) {
						$json = json_decode($response, TRUE);
						$accessToken 		= $json["access_token"];
						$tokenType 			= $json["token_type"];
						$permissions 		= $json["scope"];
						$expiresIn 			= $json["expires_in"];
						$refreshToken		= $json["refresh_token"];

						// Using the above information, we can now request beginning a party.
						$party = new CCreateParty();
						$party->ServeForm($accessToken, $expiresIn, $refreshToken);
						
						return;
					},
					
					// Some error occured. Who cares lel
					function($reason) {
						
						return;
					}
				);
			}
			
			private function FinishCreatingParty() {
				global $PARTY;
				global $AUTHORISATION;
				global $USER;
			
				$accessToken 		= $_POST["txtAccessToken"];
				$expiresIn 			= $_POST["txtExpiresIn"];
				$refreshToken 		= $_POST["txtRefreshToken"];
				$userID				= $_POST["txtUserID"];
				
				/*$party = NULL;
				if(($party = $PARTY->FindPartyWithHostID($userID) !== NULL)) {
					// The host does exist, send them to their party.
					$this->ServeExistingInfo();
					return;
				}*/
				
				$partyName = $_POST["txtPartyName"];
				$nickname = $_POST["txtNickname"];
				$uniqueString = $PARTY->GenerateUniqueString($partyName, $nickname, time());

				$authid = $AUTHORISATION->CreateAuthInstance($accessToken, $refreshToken, time() + $expiresIn, $userID);
				$partyid = $PARTY->CreateParty($authid, $partyName, $uniqueString);
				
				$userHash = md5($partyid . "[B;WLw@',2<76{CN" . $nickname);
				setcookie("JukeboxCookie", $userHash);
				
				// Now, finally, we can create a new user. and serve the existing info.
				$USER->CreateUser($nickname, 1, $userHash, $partyid);
				header("Location: jukebox.php");
			}
		}
	}
	
	$jukebox = new CJukebox();
?>