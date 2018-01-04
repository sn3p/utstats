
$(document).ready(function($) {
      $(".clickableRow").click(function() {
			if(event.which == 1) {
				window.document.location = $(this).attr("href");
			} 
      });
});
