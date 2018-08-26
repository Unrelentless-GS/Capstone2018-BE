<?php
	/*
	An endpoint for handling device commands.
	
	Written by Brendan based of designs by Alden Viljoen
	*/
	
	require_once("data/backend/srvr_info.php");
	require_once("data/backend/network.php");
	require_once("data/backend/funcs.php");
	
	require_once("data/auth.php");
	require_once("data/party.php");
	require_once("data/user.php");
	require_once("data/playlist.php");
	
	if(!class_exists("CDevice")) {
		class CDevice extends CNetwork {
			function __construct() {
				parent::__construct("Device", "", "", array ( "Action" ));
				
				if(!$this->SessionRequired()) {
					print("No session");
					return;
				}

				if(isset($_GET["Action"]))
				{
					$action = $_GET["Action"];
				}
			
				switch($action) {
					case "GetDevices":
						$partyid = $_GET["PartyID"];
						$this->GetDevices($partyid);
						break;
						
					case "PlayOnDevice":
						$partyid = $_GET["PartyID"];
						$this->PlayOnDevice($partyid);
						break;
				}
			}

			/**----------------------------**
			Returns all connected devices
			*/
			private function GetDevices($partyid) {
				global $JUKE;

				global $PARTY;
				$row = $PARTY->FindPartyWithID($partyid);
				
				$result = $JUKE->GetRequest(
					"https://api.spotify.com/v1/me/player/devices",
					array( 
						"Content-Type: application/json",
						"Accept: application/json",
						"Authorization: Bearer " . $row["AuthAccessToken"]
					),
					NULL,
					NULL,
					NULL
				);
				print($result);
			}

			/**----------------------------**
			Changes playback to be on specified device
			*/
			private function PlayOnDevice($partyid) {
				global $JUKE;

				global $PARTY;
				$row = $PARTY->FindPartyWithID($partyid);
				$deviceid = $_GET["DeviceID"];
				
				$result = $JUKE->PutRequest(
					"https://api.spotify.com/v1/me/player",
					array( 
						"Content-Type: application/json",
						"Accept: application/json",
						"Authorization: Bearer " . $row["AuthAccessToken"]
					),
					json_encode(array("device_ids" => array($deviceid))),
					NULL,
					NULL
				);
				print($result);
			}
		}
	}
	
	$device = new CDevice;
?>