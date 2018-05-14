$(document).ready(function() {

	var table = document.getElementById("vote-table");
    if(table){
        sortTable();
    }

	jQuery('.upvote').each(function(index, element){
		jQuery(element).on('click', function(){

			if(!jQuery(element).hasClass('active-vote'))
			{ 		
				jQuery(element).addClass('active-vote');

				if(jQuery(element).siblings('.downvote').hasClass('active-vote'))
				{
					jQuery(element).siblings('.downvote').removeClass('active-vote');
				}
			}
			else 
			{
				jQuery(element).removeClass('active-vote');
			}
		});
	});

	jQuery('.downvote').each(function(index, element){
		jQuery(element).on('click', function(){	

			if(!jQuery(element).hasClass('active-vote'))
			{
				jQuery(element).addClass('active-vote');

				if(jQuery(element).siblings('.upvote').hasClass('active-vote'))
				{
					jQuery(element).siblings('.upvote').removeClass('active-vote');
				}
			} 
			else
			{
				jQuery(element).removeClass('active-vote');
			}
		});
	});
});

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
		for (var i = 1, row1; row1 = table.rows[i]; i++) 
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

/* ANTE's FUNCTIONS */
$(document).ready(function() {
	$(".logo-loginpage").click(function(){
		$(".login-content:not(.login-selector)").fadeOut(-5000);
		$(".button-hostlogin").fadeIn();
		$(".button-guestlogin").fadeIn();
	});
});

$(document).ready(function() {
	$(".button-hostlogin").click(function(){
		$(".button-hostlogin").fadeOut(-5000);
		$(".button-guestlogin").fadeOut(-5000);
		$(".host-login").fadeIn();
	});
});

$(document).ready(function() {
	$(".button-guestlogin").click(function(){
		$(".button-hostlogin").fadeOut(-5000);
		$(".button-guestlogin").fadeOut(-5000);
		$(".guest-login").fadeIn();
	});
});