$(document).ready(function() {
  // Tooltips
  $(".tooltip").tooltipster();

  // Click rows
  $(".clickableRow").click(function() {
    if (event.which == 1) {
      window.document.location = $(this).attr("href");
    }
  });

  // Credits changelog toggle
  $(".changeLog").click(function() {
    $("#contentChangeLog").slideToggle(300);
  });
});
