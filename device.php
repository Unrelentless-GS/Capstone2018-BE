<?php
	/*
	An endpoint for handling device commands.
	
	Written by Brendan based off designs by Alden Viljoen
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
				elseif(isset($_POST["Action"]))
				{
					$action = $_POST["Action"];
				}
			
				switch($action) {
					case "GetDevices":
						$partyid = $this->_NET_SESSION["PartyID"];
						$this->GetDevices($partyid);
						break;
						
					case "PlayOnDevice":
						$partyid = $this->_NET_SESSION["PartyID"];
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

				if(isset($_GET["DeviceID"]))
				{
					$deviceid = $_GET["DeviceID"];
				}
				elseif(isset($_POST["DeviceID"]))
				{
					$deviceid = $_POST["DeviceID"];
				}
				
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
				$this->DropNetMessage(array( "Status"	=>	"Success"));
			}
		}
	}
	
	$device = new CDevice;
?>