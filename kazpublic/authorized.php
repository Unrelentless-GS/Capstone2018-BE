<?php
	/*
	The file that contains funcitonality for Spotify Jukebox.
	This will allow us to search for a song.
	This class is currently a dud, only really exists for the demonstration.
	
	Written by Alden Viljoen
	*/
	
	if(!isset($_COOKIE["Jukebox_Token"]) && !isset($GLOBALS["Jukebox_Token"])) {
		print("Token not set");
		return;
	}
	
	if(isset($_COOKIE["Jukebox_Token"])) {
		$token = $_COOKIE["Jukebox_Token"];
	} else {
		$token = $GLOBALS["Jukebox_Token"];
	}
	
	if(isset($_COOKIE["Jukebox_RToken"])) {
		$refresh = $_COOKIE["Jukebox_RToken"];
	} else {
		$refresh = $GLOBALS["Jukebox_RToken"];
	}
	
	echo "<input type=\"hidden\" id=\"txtToken\" value=\"" . $token . "\">";
	echo "<input type=\"hidden\" id=\"txtRefresh\" value=\"" . $refresh . "\">";
?>

<h4>You're authorised!</h4>