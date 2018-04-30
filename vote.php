<?php
	/*
	An endpoint for handling upvote/downvote commands.
	Also handles song adding.
	
	Written by Alden Viljoen
	
	WARNING! This class is UNTESTED at 30/04/2018
	*/
	
	require_once("data/backend/srvr_info.php");
	require_once("data/backend/network.php");
	require_once("data/backend/funcs.php");
	
	require_once("data/auth.php");
	require_once("data/party.php");
	require_once("data/user.php");
	
	if(!class_exists("CVote")) {
		class CVote extends CNetwork {
			function __construct() {
				parent::__construct("Vote", "", "", array ());
			}
		}
	}
	
	$vote = new CVote;
?>