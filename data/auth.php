<?php
	/*
	Spotify Jukebox Research
	Written by Alden Viljoen

	The Authorization result page. Successful and failed authorization requests will be redirected here.
	Here's where we can request an access and refresh token for the client.
	*/
	
	require_once("backend/srvr_info.php");
	require_once("backend/jukeboxdb.php");
	require_once("backend/funcs.php");

	if(!class_exists("CAuthResult")) {
		class CAuthResult extends CJukeboxDB {
			private $_redirectURI;
			private $_jukeScopes = "user-modify-playback-state user-read-playback-state playlist-read-private user-library-read user-read-email user-read-private user-read-birthdate";
			
			function __construct() {
				parent::__construct("AuthResult");
				
				$this->_redirectURI = REDIRECT_URI;
			}
			
			private $_insertAuth = "
				INSERT INTO authentication(AuthID, AuthAccessToken, AuthRefreshToken, AuthExpires, AuthSpotifyUserID)
				VALUES (NULL, :atoken, :rtoken, :expires, :userid)
			";
			public function CreateAuthInstance($accessToken, $refreshToken, $expiresAt, $spotUserID) {
				return $this->RunQuery_GetLastInsertID($this->_insertAuth,
					[
						"atoken"			=> $accessToken,
						"rtoken"			=> $refreshToken,
						"expires"			=> $expiresAt,
						"userid"			=> $spotUserID
					])["InsertID"];
			}
			
			private $_findAuthRow = "
				SELECT a.*
				FROM authentication a
				INNER JOIN party p
				ON p.AuthID=a.AuthID
				WHERE p.PartyID=:id
			";
			public function GetAuthRowWithPartyID($partyid) {
				$result = $this->RunQuery($this->_findAuthRow,
					[
						"id"			=> $partyid
					]);
					
				if($result === NULL || $result->rowCount() <= 0)
					return NULL;
				
				return $this->GetRow($result);
			}
			
			// This function redirects the user to Spotify to authorise.
			// The result from this transaction will be sent to the redirect URI above.
			public function AuthoriseUser() {
				$request = "https://accounts.spotify.com/authorize/?client_id=" . CLIENT_ID
				. "&response_type=code&redirect_uri=" . $this->_redirectURI 
				. "&scope=" . rawurlencode($this->_jukeScopes)
				. "&state=" . STATE;
						
				header("Location: " . $request);
				exit();
			}
			
			/* This function handles a user decision for authorising their account.
			   Two anonymous functions required;
					function(response, state) - https://beta.developer.spotify.com/documentation/general/guides/authorization-guide/#authorization-code-flow
					function(reason)
			*/		
			public function HandleUserDecision($successCallback, $failureCallback) {
				// The user's made a decision, here's where we'll see what we can work with.
				$state = $_GET["state"];
				if($state !== STATE) {
					// Report possible malicious activity.
					return;
				}
				
				if(isset($_GET["code"])) {
					$this->ProcessSuccessful($_GET["code"], $state, $successCallback);
				}elseif(isset($_GET["error"])) {
					$this->ProcessFailure($state, $failureCallback);
				}
			}
			
			// An addon function to neatly handle the intersection of mobile.
			// I didn't want to make ProcessSuccessful public, to avoid confusion and encourage code reuse.
			// A.V.
			public function CompleteMobileAuthorisation($code, $state, $success) {
				$this->ProcessSuccessful($code, $state, $success);
			}
			
			// Internal code, finalises a user authorisation request.
			// Edited to always accept code as a parameter.
			private function ProcessSuccessful($code, $state, $success) {
				global $JUKE;
				
				$JUKE->PostRequest(
					"https://accounts.spotify.com/api/token",
					array(
						"Content-type: application/x-www-form-urlencoded",
						"Authorization: Basic " . base64_encode(CLIENT_ID . ":" . CLIENT_SECRET)
					),
					array(
						"grant_type"			=> "authorization_code",
						"code"					=> $code,
						"redirect_uri"			=> $this->_redirectURI
					), 
					$success,
					$state
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