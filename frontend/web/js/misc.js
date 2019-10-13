(function($) {
  'use strict';
  $(function() {
    // $('#sidebar .nav').perfectScrollbar();
    if( navigator.userAgent.toLowerCase().indexOf('firefox') > -1 ){
    	$('.container-scroller').perfectScrollbar({
	    	suppressScrollX: true,
	    	wheelSpeed: 10
	    });
	} 
	if ( navigator.userAgent.toLowerCase().indexOf('chrome') > -1 ){
		$('.container-scroller').perfectScrollbar({
	    	suppressScrollX: true,
	    	wheelSpeed: 1
	    });
	}

    $('[data-toggle="minimize"]').on("click", function () {
      $('body').toggleClass('sidebar-icon-only');
    });
  });

  $(".form-check label,.form-radio label").append('<i class="input-helper"></i>');
})(jQuery);