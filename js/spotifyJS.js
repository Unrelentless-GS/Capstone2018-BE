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

	jQuery('.searchbar').on('keypress', function(){
		jQuery('.searchbar.section').addClass('header-active');
		jQuery('.main-frame').fadeOut();
		jQuery('.results-row').fadeIn();
	});

	jQuery('.form-control').on('keyup', function(){
		console.log(jQuery('.form-control').val().length);
		if(jQuery('.form-control').val().length > 0) {
			jQuery('.form-control').addClass('form-active');
			jQuery('.main-frame').addClass('display-hide');
			jQuery('.results-row').removeClass('display-hide');
		} else {
			jQuery('.form-control').removeClass('form-active');
			jQuery('.main-frame').removeClass('display-hide');
			jQuery('.results-row').addClass('display-hide');
		}
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