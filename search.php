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
?>