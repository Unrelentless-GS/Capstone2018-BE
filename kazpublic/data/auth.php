<?php
	/*
	Spotify Jukebox Research
	Written by Alden Viljoen

	The Authorization result page. Successful and failed authorization requests will be redirected here.
	Here's where we can request an access and refresh token for the client.
	*/
	
	require_once("backend/srvr_info.php");
	require_once("backend/funcs.php");

	if(!class_exists("CAuthResult")) {
		class CAuthResult {
			// Don't ever show or give this to anyone.
			// You shouldn't even be able to see this.
			private $_redirectURI = "https://spotify-jukebox.viljoen.industries/jukebox.php";
			private $_jukeScopes = "user-modify-playback-state user-read-playback-state playlist-read-private user-library-read";
			
			function __construct() {
				
			}
			
			public function AuthoriseUser() {
				$request = "https://accounts.spotify.com/authorize/?client_id=" . CLIENT_ID
				. "&response_type=code&redirect_uri=" . $this->_redirectURI 
				. "&scope=" . $this->_jukeScopes 
				. "&state=" . STATE;
							
				header("Location: " . $request);
				exit();
			}
			
			public function HandleUserDecision($successCallback, $failureCallback) {
				// The user's made a decision, here's where we'll see what we can work with.
				$state = $_GET["state"];
				if($state !== STATE) {
					// Report possible malicious activity.
					return;
				}
				
				if(isset($_GET["code"])) {
					$this->ProcessSuccessful($state, $successCallback);
				}elseif(isset($_GET["error"])) {
					$this->ProcessFailure($state, $failureCallback);
				}
			}
			
			private function ProcessSuccessful($state, $success) {
				global $JUKE;
				
				// This can be exchanged for an access/refresh token.
				$code = $_GET["code"];
				
				$JUKE->PostRequest(
					"https://accounts.spotify.com/api/token",
					"Authorization: Basic " . base64_encode(CLIENT_ID . ":" . CLIENT_SECRET),
					array(
						"grant_type"			=> "authorization_code",
						"code"					=> $code,
						"redirect_uri"			=> $this->_redirectURI
					), 
					$success
				);
			}
			
			private function ProcessFailure($state, $failed) {
				$error = $_GET["error"];
				$failed($error);
			}
		}
	}
	
	$AUTHORISATION = new CAuthResult();
?>