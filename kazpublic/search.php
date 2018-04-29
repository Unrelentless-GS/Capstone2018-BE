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

			private function SearchViaParty($term, $type) {
				global $PARTY;
				global $AUTHORISATION;
				global $USER;

				$userHash = $_COOKIE["JukeboxCookie"] or NULL;
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
					//print_r($json);

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
				// Sends the request to Spotify for searching
				$request =
				"https://api.spotify.com/v1/search" .
				"?q=" . urlencode($term) . // for what to search
				"&type=" . $type .	// what type (album,artist,track,playlist)
				//"&market=from_token" . //(AT THE END IF YOU WANT TO ONLY DISPLAY RESULTS BASED ON THE TOKENS AREA E.G(AU OR US), THEN REMOVE COMMENT, HOWEVER MUST HAVE A VALID ACCESS TOKEN IN &token)
				"&limit=50";	// limit how many results returned

				//Displays the request sent to the Spotify Servers
					print($request);




				// RETURNS JSON OF RESULTS
				$results = $JUKE->GetRequest($request, "Authorization: Bearer " . $token);


													// KAZ - SORTING THE RESULTS
				// Decodes tracks so it can be manipulated
				$decodedResults = json_decode( $results, true );
				// Stores searched items into variable
				$items = $decodedResults["tracks"]["items"];


				// Sorts items based on popularity (1-100), highest number is sorted earlier in list
				usort($items, function($a, $b)
				{ //Sort the array using a user defined function
					return $a["popularity"] > $b["popularity"] ? -1 : 1; //Compare the scores
				});


				// PRINTS ONLY TRACKS SORTED BY POPULARITY (IN JSON)
				print(json_encode($items));


				// DISPLAYS THE NAMES OF THE SEARCHED RESULTS (NOT IN JSON)
				//foreach ( $items as $index => $name )
				//{
					//print(json_encode($name["name"]));
						//echo "<br />\n";
				//}


				//PRINTS RAW RESULTS WITH EVERYTHING (TRACKS,ALBUMS,PLAYLISTS)
				//print($results);



			}
		}
	}

	$search = new CSearch;
?>
