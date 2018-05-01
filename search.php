<<<<<<< HEAD
<?php
	/*
	A script for facilitating Spotify API searches.
	
	Written by Alden Viljoen
	*/
	
	require_once("data/backend/srvr_info.php");
	require_once("data/backend/funcs.php");
	
	if(!class_exists("CSearch")) {
		class CSearch {
			function __construct() {
				if(!isset($_GET["Term"])) {
					print("No term");
					return;
				}
				
				$term = $_GET["Term"];

				if(!isset($_GET["Type"])){
					$type = "album,artist,playlist,track";
				}else{
					$type = $_GET["Type"];
				}
				
				$this->AuthorizeAndSearch($term, $type);
			}
			
			private function AuthorizeAndSearch($term, $type) {
				global $JUKE;
				
				$JUKE->PostRequest(
					"https://accounts.spotify.com/api/token",
					"Authorization: Basic " . base64_encode(CLIENT_ID . ":" . CLIENT_SECRET),
					array(
						"grant_type"			=> "client_credentials"
					), 
					function($response, $state) {
						if($response === FALSE) {
							/*
							The request failed, maybe some more delicate handling in the future? :)
							Like maybe 'at least its not Apple Music'
							*/
							
							print("POST request failed!");
							return NULL;
						}

						$json = json_decode($response, TRUE);
						$token = $json["access_token"];
						
						$this->Search($state["Term"], $state["Type"], $token);
					},
					array(
						"Term"		=> $term,
						"Type"		=> $type
					)
				);
			}
			
			private function Search($term, $type, $token) {
				global $JUKE;
				
				if($token === NULL) {
					print("Failed to authorize!");
					return;
				}
				
				$request = 
				"https://api.spotify.com/v1/search" .
				"?q=" . urlencode($term) . 
				"&type=" . $type;
				
				$results = $JUKE->GetRequest($request, "Authorization: Bearer " . $token);
				
				print($results);
			}
		}
	}
	
	$search = new CSearch;
=======
<?php
	/*
	A script for facilitating Spotify API searches.
	
	Written by Alden Viljoen
	*/
	
	require_once("data/backend/srvr_info.php");
	require_once("data/backend/network.php");
	require_once("data/backend/funcs.php");
	
	require_once("data/auth.php");
	require_once("data/party.php");
	require_once("data/user.php");
	
	if(!class_exists("CSearch")) {
		class CSearch extends CNetwork {
			function __construct() {
				parent::__construct("Search", "", "", array ());
				
				if(!isset($_GET["Term"])) {
					print("No term");
					return;
				}
				
				$term = $_GET["Term"];
				
				$type = NULL;
				if(isset($_GET["Type"]))
					$type = $_GET["Type"];
				else
					$type = "album,artist,playlist,track";
				
				$mode = NULL;
				if(isset($_GET["Mode"]))
					$mode = $_GET["Mode"];
				else
					$mode = "ImplicitGrant"; // ImplicitGrant or AuthorisationCode
				
				if($mode == "ImplicitGrant") {
					$this->AuthoriseAndSearch($term, $type);
				}elseif($mode == "AuthorisationCode") {
					$this->SearchViaParty($term, $type);
				}
			}
			
			/*
			TODO:
			Update to use CNetwork::SessionRequired.
			*/
			private function SearchViaParty($term, $type) {
				global $PARTY;
				global $AUTHORISATION;
				global $USER;
				
				$userHash = $_COOKIE["JukeboxCookie"];
				if($userHash == NULL) {
					print("no presence");
					return;
				}
				
				$userrow = $USER->FindUserWithUserHash($userHash);
				if($userrow == NULL){
					print("no user presence");
					return;
				}
				
				$authrow = $AUTHORISATION->GetAuthRowWithPartyID($userrow["PartyID"]);
				if($authrow == NULL) {
					print("no auth presence");
					return;
				}
				
				// Now we can perform a search, given the access token and the term and type.
				$this->Search($term, $type, $authrow["AuthAccessToken"]);
			}
			
			private function AuthoriseAndSearch($term, $type) {
				global $JUKE;
				
				$JUKE->PostRequest(
					"https://accounts.spotify.com/api/token",
					"Authorization: Basic " . base64_encode(CLIENT_ID . ":" . CLIENT_SECRET),
					array(
						"grant_type"			=> "client_credentials"
					), 
					function($response, $state) {
						if($response === FALSE) {
							/*
							The request failed, maybe some more delicate handling in the future? :)
							Like maybe 'at least its not Apple Music'
							*/
							
							print("POST request failed!");
							return NULL;
						}

						$json = json_decode($response, TRUE);
						$token = $json["access_token"];
						
						$this->Search($state["Term"], $state["Type"], $token);
					},
					array(
						"Term"		=> $term,
						"Type"		=> $type
					)
				);
			}
			
			private function Search($term, $type, $token) {
				global $JUKE;
				
				if($token === NULL) {
					print("Failed to authorize!");
					return;
				}
				
				$request = 
				"https://api.spotify.com/v1/search" .
				"?q=" . urlencode($term) . 
				"&type=" . $type;
				
				$results = $JUKE->GetRequest($request, "Authorization: Bearer " . $token);
				
				print($results);
			}
		}
	}
	
	$search = new CSearch;
>>>>>>> 0768fc5... Update.
?>