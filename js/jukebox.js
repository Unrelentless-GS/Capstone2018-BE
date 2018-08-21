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

//Updates Currently Playing Song Information - Brendan
function UpdateCurrentlyPlaying()
{
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() 
	{
		if(this.readyState == 4 && this.status == 200) 
		{
			if (this.responseText != "null")
			{
				var result = JSON.parse(this.responseText);
	
				//Update Song Name and Artist
				jQuery('.currentSongName').text(result.SongName);
				jQuery('.currentArtistName').text(result.SongArtists);
	
				//Update picture
				var src = result.SongImageLink;
				var img = document.createElement('img');
				img.src = src;
				img.setAttribute("height", "64");
				img.setAttribute("width", "64");
				var imgParent = document.getElementById("artworkparent");
				imgParent.replaceChild(img, imgParent.firstChild);
			}
		}
	}

	var partyID = jQuery('.party-id').val();
	xhttp.open("GET", SITE + "vote.php?Action=UpdateCP&PartyID=" + partyID, true);
	xhttp.send();
	
	console.log("Updating CurrentlyPlayingInfo...");
}

//Timer for UpdateVotes
setInterval(function(){UpdateCurrentlyPlaying();}, 5000);

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
				//Update votes and remove songs that have been deleted
				jQuery('.song-select').each(
				function(index) 
				{
					var songID = jQuery(this).find('input[name="SongID"]').val()
					for (var i = songData.length - 1; i >= 0; i--) 
					{
						var newVoteCount = GetVoteCount(songData[i],songID);
						if (newVoteCount != null)
						{
							jQuery(this).find('.voteCount').text(newVoteCount);
							return;
						}
					}
					jQuery(this).remove()
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

//Timer for UpdateVotes
setInterval(function(){UpdateVotes();}, 3000);

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
		rows = table.getElementsByTagName("TR");
		for (var i = 2, row1; row1 = table.rows[i]; i++) 
		{
			shouldSwitch = false;
			x = rows[i - 1].getElementsByTagName("TD")[3];
			y = rows[i].getElementsByTagName("TD")[3];
			if (parseInt(x.innerHTML) < parseInt(y.innerHTML)) 
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
			// Reload the page or required controls.
			window.location = window.location.pathname;
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
	var tr = document.createElement("TR"); 

	var title = document.createElement("td");
	var t = document.createTextNode(item.name);
	title.appendChild(t);
	tr.appendChild(title);

    var artist = document.createElement("td");
    var t = document.createTextNode(item.artists[0].name);
	artist.appendChild(t);
	tr.appendChild(artist);

	var id = item.id;

    var button = document.createElement("td");

	button.onclick = function() 
	{
    	AddSong(id);
	};
    var buttoni = document.createElement("i");
    buttoni.setAttribute("class", "fas fa-plus addSongBtn");

    button.appendChild(buttoni);
	tr.appendChild(button);

    var searchresults = document.getElementById("search-results")
    searchresults.appendChild(tr);
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

//Choice Device Code - Brendan

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
			OpenModal();
		}
	}
}

function OpenModal()
{
	//Make XHTTP Query
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
		if(this.readyState == 4 && this.status == 200) 
		{
			var results = JSON.parse(this.responseText);
			console.log(results.devices);

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
								ChangeDevice(results.devices[i].id);
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

// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
    if (event.target == modal) {
        ExitModal();
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

function ChangeDevice($deviceID)
{
	console.log($deviceID);
	//Make XHTTP Query
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
		if(this.readyState == 4 && this.status == 200) 
		{
			console.log(this.responseText);
			ExitModal();
		}
	}
	
	var partyID = jQuery('.party-id').val();
	xhttp.open("GET", SITE + "device.php?Action=PlayOnDevice&PartyID=" + partyID + "&DeviceID=" + $deviceID, true);
	xhttp.send();
	
	console.log("Changing Device..");
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

function Initialise() 
{
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
   				}, 1200);
			}
		}
	});

	UpdateCurrentlyPlaying();

	//If btn extists (if host)
	if (btn !=  null)
	{
		if (getParameter("choosedevice") != false)
		{
			OpenModal();		
		}
		if (getParameter("needtoaddsongs") != false)
		{
			AddSongsError();
		}
	}
}

var modalOpen = false;

Initialise();