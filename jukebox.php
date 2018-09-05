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
				}elseif(isset($_POST["txtFinishCreatingParty"])) {
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
				}elseif(isset($_POST["Code"]) && isset($_POST["PartyName"]) && isset($_POST["HostNickname"])) { // <-- CREATE PARTY FROM MOBILE.
					// This is a create party request from a mobile device.
					// The user has authorised our app to use their Spotify. Our app informs us of their intent,
					// from here we will inform Spotify on their behalf that this is a secure connection, and create the party.
					error_log("Creating party mobile");
					$this->CreatePartyMobile();
				}elseif(isset($_POST["btnGuest"])) {
					// TODO.
					return;
				}else{
					error_log("OTHER!");
					header("Location: index.php");
				}
			}
			
			private function ServeExistingInfo() {
				$userHash = $_COOKIE["JukeboxCookie"];
				
				if($this->IsSessionValid() === FALSE) {
					setcookie("JukeboxCookie", "");
					header("Location: index.php");
					
					return;
				}
				
				require_once("data/forms/party.php");
				$party = new CPartyForm();
				
				$party->ServeForm($userHash);
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
			
			private function FinishCreatingParty($REDIRECT = TRUE) {
				global $PARTY;
				global $AUTHORISATION;
				global $USER;
			
				$accessToken 		= $_POST["txtAccessToken"];
				$expiresIn 			= $_POST["txtExpiresIn"];
				$refreshToken 		= $_POST["txtRefreshToken"];
				$userID				= $_POST["txtUserID"];
				
				$nickname 			= $_POST["txtNickname"];
				
				/* 
				TODO
				We need to define what 'expired' means.
				For now, it'll bring the party back no matter how long its been inactive, just under the new name and host nickname.
				*/
				
				$party = $PARTY->FindPartyWithHostID($userID);
				if($party !== NULL) {
					// The host does exist, send them to their party.
					// Modified to return the new userhash (compatibility for mobile) A.V.
					$userhash = $USER->UpdateHostUserHash($party["PartyID"], $nickname);
					
					if($REDIRECT == TRUE)
						header("Location: jukebox.php");
					return $userhash;
				}
				
				$uniqueString = $PARTY->GenerateUniqueString($nickname, time());

				$authid = $AUTHORISATION->CreateAuthInstance($accessToken, $refreshToken, time() + $expiresIn, $userID);
				$partyid = $PARTY->CreateParty($authid, $uniqueString);
				
				$userhash = $USER->EnterNewUser($partyid, $nickname, 1);
				
				if($REDIRECT == TRUE)
					header("Location: jukebox.php");
				
				return $userhash;
			}
			
			private function CreatePartyMobile() {
				global $AUTHORISATION;
				
				$hostNickname 	= $_POST["HostNickname"];
				$partyName 		= $_POST["PartyName"];
				
				$code 			= $_POST["Code"];
				
				// Immediately request a refresh token - make this authorisation official.
				$AUTHORISATION->CompleteMobileAuthorisation($code, array( "Nick"=>$hostNickname, "PartyName"=>$partyName ),
					function($response, $state) {
						global $USER;
						
						$json = json_decode($response, TRUE);
						$userID = $USER->GetUserID($json["access_token"]);
						
						// I don't really want to rewrite the logic for determining if we already have a party - so I'll just do this.
						// A.V.
						$_POST["txtAccessToken"] 		= $json["access_token"];
						$_POST["txtExpiresIn"] 			= $json["expires_in"];
						$_POST["txtRefreshToken"] 		= $json["refresh_token"];
						$_POST["txtUserID"]				= $userID;
						
						$_POST["txtPartyName"]			= $state["PartyName"];
						$_POST["txtNickname"]			= $state["Nick"];
						
						// We'll call finishcreatingparty,
						// this logic will in turn create a party or return the user's existing one.
						$userhash = $this->FinishCreatingParty(FALSE);
						
						// The userhash is the key to the party, so we'll give it to the user.
						// Also, this is where we'd attach existing data, such as current playlist.
						
						$this->DropNetMessage(array( "UserHash"		=> 		$userhash ));
					}
				);
			}
		}
	}
	
	$jukebox = new CJukebox();
?>