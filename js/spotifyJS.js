$(document).ready(function() {

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

	jQuery('.form-control').on('keyup', function(){
		console.log(jQuery('.form-control').val().length);
		if(jQuery('.form-control').val().length > 0) {
			jQuery('.form-control').addClass('form-active');
			jQuery('.main-frame').addClass('display-hide');
			jQuery('.header-row').addClass('display-hide');
			jQuery('.results-row').removeClass('display-hide');
		} else {
			jQuery('.form-control').removeClass('form-active');
			jQuery('.main-frame').removeClass('display-hide');
			jQuery('.header-row').removeClass('display-hide');
			jQuery('.results-row').addClass('display-hide');
		}
	});
});