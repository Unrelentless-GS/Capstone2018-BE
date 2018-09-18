/*
Spotify Jukebox - Adding a song to a party.
By Alden Viljoen

This connects to my server, don't break it.
*/

var DEBUG = true;
var SITE = "";

if(DEBUG == true) {
	SITE = "http://localhost/xampp/SpotifyJukebox/";
}else{
	SITE = "https://spotify-jukebox.viljoen.industries/";
}

var CurrentlyPlayingID = -10;

//Updates Currently Playing Song Information - Brendan
function UpdateCurrentlyPlaying()
{
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() 
	{
		if(this.readyState == 4 && this.status == 200) 
		{
			if (this.responseText == "No session")
			{
				var element = document.createElement("p");
				element.setAttribute("id", "partyClosedErrorMessageText");
				modalCon.appendChild(element);
				modal.style.display = "block";
				jQuery('#partyClosedErrorMessageText').text('Party has been closed, redirecting...');

				setTimeout(function () 
				{
   					location.reload();
   				}, 10000);
			}
			else if (this.responseText == "null")
			{

			}
			else
			{
				var result = JSON.parse(this.responseText);
	
				//Update Song Name and Artist
				jQuery('.currentSongName').text(result.SongName);
				jQuery('.currentArtistName').text(result.SongArtists);
				jQuery('.currentSongValue').text(result.Value);
				// Ensure current-song row is showing
				jQuery('.currently-playing').removeClass('display-hide');
	
				//Update picture
				var src = result.SongImageLink;
				var img = document.createElement('img');
				img.src = src;
				img.setAttribute("height", "64");
				img.setAttribute("width", "64");
				var imgParent = document.getElementById("artworkparent");
				imgParent.replaceChild(img, imgParent.firstChild);

				//Set CurrentlyPlayingID
				CurrentlyPlayingID = result.SongID;
			}
			//Call Updates Votes
			// Do it this way so that when currently playing is changed, the old song is imediately deleted, so its not in the list twice.
			UpdateVotes();
		}
	}

	var partyID = jQuery('.party-id').val();
	xhttp.open("GET", SITE + "vote.php?Action=UpdateCP&PartyID=" + partyID, true);
	xhttp.send();
	
	console.log("Updating CurrentlyPlayingInfo...");
}

//Array of songs that only exist as dom elements, added using javascript.
var tempSongArray = [];

//GetVoteCountForSongID
function GetVoteCount(item,songID)
{
	//If is song ID, return vote value
	if (songID == item.SongID)
	{
		return item.VoteCount;
	}
	return null;
}

//Updates votes - Brendan
function UpdateVotes() 
{
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function()
	{
		if(this.readyState == 4 && this.status == 200) 
		{
			var songData = JSON.parse(this.responseText);
			//If not null
			if (songData != null)
			{
				//If addSongMessage exists, delete it
				jQuery('.noSongsErrorMessage').remove();

				//Update votes and remove songs that have been deleted
				jQuery('.song-select').each(
				function(index) 
				{
					var songID = jQuery(this).find('input[name="SongID"]').val()

					//Checks to see if it's the currently playing song, if so, deletes it
					if (songID == CurrentlyPlayingID)
					{
						jQuery(this).remove();
					}

					//Updates votes and removes songs that have been deleted
					for (var i = songData.length - 1; i >= 0; i--) 
					{
						var newVoteCount = GetVoteCount(songData[i],songID);
						if (newVoteCount != null)
						{
							jQuery(this).find('.voteCount').text(newVoteCount);
							return;
						}
					}
					jQuery(this).remove();
				});
	
				//Add new songs
				for (var i = songData.length - 1; i >= 0; i--) 
				{
					var alreadyExists = false;
	
					jQuery('.song-select').each(
					function(index) 
					{
						var songID = jQuery(this).find('input[name="SongID"]').val()
						if (songID == songData[i].SongID)
						{
							alreadyExists = true;
						} 
					});
	
					//Checks against tempSongArray
					for (var j = tempSongArray.length - 1; j >= 0; j--) 
					{
						if (tempSongArray[j] == songData[i].SongID)
						{
							alreadyExists = true;
						} 
					}

					//Check it isn't the currently playing song
					if (songData[i].SongID == CurrentlyPlayingID)
					{
						alreadyExists = true;
					}
	
					if (!alreadyExists)
					{
						//Add to list of temporary(until they vote on something/reload page) songs
						tempSongArray.push(songData[i].SongID);
	
						//Add the song using elements
						var tr = document.createElement("TR"); 
						tr.setAttribute("class", "song-select");
	
						//SongName
						var songName = document.createElement("td");
						songName.setAttribute("class", "song");
						var text = document.createTextNode("" + songData[i].SongName);
						songName.appendChild(text);
						tr.appendChild(songName);
	
						//SongArtist
						var songArtists = document.createElement("td");
						songArtists.setAttribute("class", "artist");
						text = document.createTextNode(songData[i].SongArtists);
						songArtists.appendChild(text);
						tr.appendChild(songArtists);
	
						//Upvote
						var upVote = document.createElement("td");
						upVote.setAttribute("class", "upvote");
						var form = document.createElement("form");
						form.setAttribute("id", "frmVoteUp");
						form.setAttribute("method", "POST");
						form.setAttribute("action", "vote.php");
	
						var inputSongID = document.createElement("input");
						inputSongID.setAttribute("type", "hidden");
						inputSongID.setAttribute("name", "SongID");
						inputSongID.setAttribute("id", "SongID");
						inputSongID.setAttribute("value", songData[i].SongID);
						var inputAction = document.createElement("input");
						inputAction.setAttribute("type", "hidden");
						inputAction.setAttribute("name", "Action");
						inputAction.setAttribute("id", "Action");
						inputAction.setAttribute("value", "Voting");
						var inputValue = document.createElement("input");
						inputValue.setAttribute("type", "hidden");
						inputValue.setAttribute("name", "Value");
						inputValue.setAttribute("id", "Value");
						inputValue.setAttribute("value", "1");
						var button = document.createElement("button");
						button.setAttribute("type", "submit");
						button.setAttribute("name", "btnVoteUp");
						button.setAttribute("id", "btnVoteUp");
						var arrow = document.createElement("i");
						arrow.setAttribute("class", "fas fa-arrow-up");
						button.appendChild(arrow);
	
						form.appendChild(inputSongID);
						form.appendChild(inputAction);
						form.appendChild(inputValue);
						form.appendChild(button);
						upVote.appendChild(form);
						tr.appendChild(upVote);
	
						//VoteCount
						var voteCount = document.createElement("td");
						voteCount.setAttribute("class", "voteCount");
						text = document.createTextNode(songData[i].VoteCount);
						voteCount.appendChild(text);
						tr.appendChild(voteCount);
	
						//Downvote
						var downVote = document.createElement("td");
						downVote.setAttribute("class", "downvote");
						form = document.createElement("form");
						form.setAttribute("id", "frmVoteDown");
						form.setAttribute("method", "POST");
						form.setAttribute("action", "vote.php");
	
						inputSongID = document.createElement("input");
						inputSongID.setAttribute("type", "hidden");
						inputSongID.setAttribute("name", "SongID");
						inputSongID.setAttribute("id", "SongID");
						inputSongID.setAttribute("value", songData[i].SongID);
						inputAction = document.createElement("input");
						inputAction.setAttribute("type", "hidden");
						inputAction.setAttribute("name", "Action");
						inputAction.setAttribute("id", "Action");
						inputAction.setAttribute("value", "Voting");
						inputValue = document.createElement("input");
						inputValue.setAttribute("type", "hidden");
						inputValue.setAttribute("name", "Value");
						inputValue.setAttribute("id", "Value");
						inputValue.setAttribute("value", "-1");
						button = document.createElement("button");
						button.setAttribute("type", "submit");
						button.setAttribute("name", "btnVoteDown");
						button.setAttribute("id", "btnVoteDown");
						arrow = document.createElement("i");
						arrow.setAttribute("class", "fas fa-arrow-down");
						button.appendChild(arrow);
	
						form.appendChild(inputSongID);
						form.appendChild(inputAction);
						form.appendChild(inputValue);
						form.appendChild(button);
						downVote.appendChild(form);
						tr.appendChild(downVote);
	
 						var searchresults = document.getElementById("vote-table-attach-point");
 						searchresults.appendChild(tr);
					}
				}
	
				//Sort Songs
				sortTable();
			}
		}
	}
	var partyID = jQuery('.party-id').val();
	xhttp.open("GET", SITE + "vote.php?Action=Updates&PartyID=" + partyID, true);
	xhttp.send();
	
	console.log("Updating Votes...");
}

//Sorts songs
function sortTable()
{
	//Written by Sam
	//Modified to work properly by Brendan
	var table, rows, switching, i, x, y, shouldSwitch;
	table = document.getElementById("vote-table");
	switching = true;
	while (switching) 
	{
		switching = false;
		rows = table.getElementsByClassName("song-select");
		for (i = 1; i < rows.length; i++) 
		{ 
		    shouldSwitch = false;
			x = parseInt(rows[i-1].getElementsByTagName("TD")[3].innerHTML);
			y = parseInt(rows[i].getElementsByTagName("TD")[3].innerHTML);
			if (x < y) 
		  	{
				shouldSwitch= true;
				break;
			}
		}

		if (shouldSwitch) 
		{
			rows[i].parentNode.insertBefore(rows[i], rows[i-1]);
			switching = true;
		}
	}
}

/*
	Summary
	Adding and searching for songs section
*/
function AddSong(spotify_track_id) {
	var xhttp = new XMLHttpRequest();
	xhttp.open("POST", SITE + "vote.php", true);
	
	xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xhttp.onreadystatechange = function() {
		if(this.readyState == 4 && this.status == 200) {
			//Reset forms and css
			jQuery('.form-control').val("");
			jQuery('.form-control').removeClass('form-active');
			jQuery('.content-row').removeClass('display-hide');
			jQuery('.header-row').removeClass('display-hide');
			jQuery('.results-row').addClass('display-hide');

			//Updates Votes and Songs
			UpdateVotes();
		}
	}
	
	xhttp.send("Action=Songs&SpotifySongID=" + spotify_track_id);
}

function PerformQuery() {
	// Get our search term.
	var sTerm = document.getElementById("Term").value;
	console.log("Performing search for " + sTerm);
	
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
		if(this.readyState == 4 && this.status == 200) {
			var tracks = JSON.parse(this.responseText).tracks.items;

			//Clears previous results before displaying new one's. - Brendan
			var searchresults = document.getElementById("search-results")
			while (searchresults.firstChild) {
				searchresults.removeChild(searchresults.firstChild);
			}

			//Add to list
			tracks.forEach(AddToList)
			
		}
	}
	
	xhttp.open("GET", SITE + "search.php?Type=track&Term=" + sTerm, true);
	xhttp.send();
	
	console.log("Querying...");
}

function AddToList(item, index)
{
	var button = document.createElement("TR");
	button.setAttribute("class", "addSongButton");

	var id = item.id;

	button.onclick = function() 
	{
		AddSong(id);
	};

	var title = document.createElement("td");
	var t = document.createTextNode(item.name);
	title.appendChild(t);
	button.appendChild(title);

    var artist = document.createElement("td");
    var t = document.createTextNode(item.artists[0].name);
	artist.appendChild(t);
	button.appendChild(artist);

    var searchresults = document.getElementById("search-results")
    searchresults.appendChild(button);
}

//Appears if songs need to be added to playlist
function AddSongsError()
{
	var device = document.createElement("p");
	text = document.createTextNode("Need to add songs to playlist.");
	device.appendChild(text);
	modalCon.appendChild(device);
	modal.style.display = "block";
}

//Choose Device Code - Brendan

// Get the button that opens the modal
var btn = document.getElementById("chooseDeviceModalBtn");
//Get the modal
var modal = document.getElementById('chooseDeviceModal');
//Get the modal-content div
var modalCon = document.getElementById('modal-content');

//If btn extists (if host)
if (btn !=  null)
{
	// When the user clicks on the button
	btn.onclick = function() 
	{
		if (!modalOpen)
		{
			modalOpen = true;
			OpenModal(false);
		}
	}
}

function OpenModal($playsong)
{
	//Make XHTTP Query
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
		if(this.readyState == 4 && this.status == 200) 
		{
			//console.log(this.responseText);
			var results = JSON.parse(this.responseText);
			//console.log(results.devices);

			if (results.devices.length < 1)
			{
				var device = document.createElement("p");
				text = document.createTextNode("No Spotify devices found.");
				device.appendChild(text);
				modalCon.appendChild(device);
			}
			else
			{
				var device = document.createElement("p");
				text = document.createTextNode("Select a device to play through.");
				device.appendChild(text);
				modalCon.appendChild(device);
			}

			//Create item for each device that isn't restricted.
			for (i in results.devices) 
			{
				if (results.devices[i].is_restricted == false)
				{
					if (results.devices[i].is_active == true)
					{
						var device = document.createElement("p");
						device.setAttribute("class", "device-active");
					}
					else
					{
						var device = document.createElement("button");

						//Needed to warp onclick function to preserve the scope of i
						device.onclick = (function(i){
							return function(){
								//Change's device to selected device.
								ChangeDevice(results.devices[i].id, $playsong);
							};
						})(i);
					}
					
					text = document.createTextNode(results.devices[i].name);
					device.appendChild(text);
					modalCon.appendChild(device);
				}
			}

			//Display modal
			modal.style.display = "block";
		}
	}
	
	var partyID = jQuery('.party-id').val();
	xhttp.open("GET", SITE + "device.php?Action=GetDevices&PartyID=" + partyID, true);
	xhttp.send();
	
	console.log("Fetching Devices..");
}


window.onclick = function(event) {
	// When the user clicks anywhere outside of the modal, close it
    if (event.target == modal) {
        ExitModal();
    }

    //Toggle searchbar css when its clicked on or clicked away from
    if (jQuery('.form-control').is(":focus") == true)
	{
		jQuery('.form-control').addClass('form-active');
		jQuery('.content-row').addClass('display-hide');
		jQuery('.header-row').addClass('display-hide');
		jQuery('.results-row').removeClass('display-hide');
	}
	else
	{
		jQuery('.form-control').removeClass('form-active');
		jQuery('.content-row').removeClass('display-hide');
		jQuery('.header-row').removeClass('display-hide');
		jQuery('.results-row').addClass('display-hide');
	}
}

//Remove everthing from Modal and close it
function ExitModal ()
{
	//Clears Modal
	while (modalCon.firstChild) {
		modalCon.removeChild(modalCon.firstChild);
	}

	//Make the modal disappear
	modal.style.display = "none";
	modalOpen = false;
}

function ChangeDevice($deviceID, $playsong)
{
	//Make XHTTP Query
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
		if(this.readyState == 4 && this.status == 200) 
		{
			if ($playsong == true)
			{
				Play();
			}
			else
			{
				ExitModal();
			}
		}
	}
	
	var partyID = jQuery('.party-id').val();
	xhttp.open("GET", SITE + "device.php?Action=PlayOnDevice&PartyID=" + partyID + "&DeviceID=" + $deviceID, true);
	xhttp.send();
	
	console.log("Changing Device..");
}

function Play()
{
	//Make XHTTP Query
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
		if(this.readyState == 4 && this.status == 200) 
		{
			ExitModal();
		}
	}
	
	var partyID = jQuery('.party-id').val();
	xhttp.open("GET", SITE + "player.php?PartyID=" + partyID + "&TP=''", true);
	xhttp.send();
	
	console.log("Playing Song After Device Set..");
}

//Credit to Nathan of TechnicalOverload.com for the getParameter Function
function getParameter(theParameter) { 
  var params = window.location.search.substr(1).split('&');
 
  for (var i = 0; i < params.length; i++) {
    var p=params[i].split('=');
	if (p[0] == theParameter) {
	  return decodeURIComponent(p[1]);
	}
  }
  return false;
}

function UpdatePlayer()
{	
	//Make XHTTP Query
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
		if(this.readyState == 4 && this.status == 200) 
		{
			$playing = this.responseText;
			if ($playing == 1)
			{
				jQuery('.playButton').text("Pause");
			}
			else if ($playing == 0)
			{
				jQuery('.playButton').text("Resume");
			}
			else
			{
				jQuery('.playButton').text("Start Party");
			}
		}
	}
	
	var partyID = jQuery('.party-id').val();
	xhttp.open("GET", SITE + "player.php?PartyID=" + partyID, true);
	xhttp.send();

	console.log("Updating Player...");
}

function Initialise() 
{
	UpdatePlayer();
	UpdateCurrentlyPlaying();

	var input = document.getElementById("Term");
	// Execute a function when the user releases a key on the keyboard
	var timer;
	input.addEventListener("keyup", function(event)
	{	
		// Cancel the default action, if needed
		event.preventDefault();
		// Reset timer
		clearTimeout(timer);
		// If term is not empty
		if(jQuery('.form-control').val().length > 0){
			//If key == enter, search immediately
			if (event.keyCode === 13)
			{
				PerformQuery()
			}
			else
			{
				//Else Set timer for 2 seconds
    			timer = setTimeout(function (event){
        			PerformQuery()
   				}, 1000);
			}
		}
	});

	//If btn extists (if host)
	if (btn !=  null)
	{
		if (getParameter("choosedevice") != false)
		{
			//Pass true, "pressing" play after the device is selected.
			OpenModal(true);
		}
		if (getParameter("needtoaddsongs") != false)
		{
			AddSongsError();
		}

		jQuery(".focusSearchBar").click(function() 
		{
			jQuery(".form-control").focus();
			jQuery('.form-control').addClass('form-active');
			jQuery('.content-row').addClass('display-hide');
			jQuery('.header-row').addClass('display-hide');
			jQuery('.results-row').removeClass('display-hide');
		});

	}

	//Timer for UpdateCurrentlyPlaying, which then calls UpdateVotes
	setInterval(function(){UpdateCurrentlyPlaying();}, 4000);
	//Timer for UpdatePlayer
	setInterval(function(){UpdatePlayer();}, 2000);
}

var modalOpen = false;

Initialise();