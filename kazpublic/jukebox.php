<?php
	require_once("data/auth.php");
	require_once("data/party.php");
	
	/*
	Summary
	An endpoint script for beginning a party.
	
	Written by Alden Viljoen
	*/
	
	// -- Method --
	// Test if the user is already a part of a party.
	// If so, serve party related information from the existing row.
	// If not, serve the authorisation screen.
	$AUTHORISATION->AuthoriseUser();
	
	// After successfully authorised, create a database row for this party instance.
?>