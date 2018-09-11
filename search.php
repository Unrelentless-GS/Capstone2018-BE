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
				
				if(!$this->GetParameter("Term")) {
					print("No term");
					return;
				}
				
				$term = $this->GetParameter("Term");
				
				$type = NULL;
				if($this->GetParameter("Type"))
					$type = $this->GetParameter("Type");
				else
					$type = "album,artist,playlist,track";
				
				$mode = NULL;
				if($this->GetParameter("Mode"))
					$mode = $this->GetParameter("Mode");
				else
					$mode = "ImplicitGrant"; // ImplicitGrant or AuthorisationCode
				
				if($mode == "ImplicitGrant") {
					$this->AuthoriseAndSearch($term, $type);
				}elseif($mode == "AuthorisationCode") {
					$this->SearchViaParty($term, $type);
				}
			}
			
			private function SearchViaParty($term, $type) {
				global $PARTY;
				global $AUTHORISATION;
				global $USER;
				
				if(!$this->IsSessionValid()) {
					error_log("no user presence");
					return;
				}
				
				$this->Search($term, $type, $this->_NET_SESSION["AuthAccessToken"]);
			}
			
			private function AuthoriseAndSearch($term, $type) {
				global $JUKE;
				
				$JUKE->PostRequest(
					"https://accounts.spotify.com/api/token",
					array(
						"Content-type: application/x-www-form-urlencoded",
						"Authorization: Basic " . base64_encode(CLIENT_ID . ":" . CLIENT_SECRET)
					),
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
				"&market=AU" .
				"&type=" . $type . 
				"&limit=" . 10;
				
				$results = $JUKE->GetRequest($request, array("Authorization: Bearer " . $token));
				
				print($results);
			}
			
			private function GetParameter($name) {
				if(isset($_GET[$name]))
					return $_GET[$name];
				elseif(isset($_POST[$name]))
					return $_POST[$name];
				else
					return NULL;
			}
		}
	}
	
	$search = new CSearch;
?>
