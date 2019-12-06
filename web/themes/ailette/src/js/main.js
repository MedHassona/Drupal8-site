$(function() {
  $('a[href*=#]').on('click', function(e) {
    e.preventDefault();
    $('html, body').animate({ scrollTop: $($(this).attr('href')).offset().top}, 500, 'linear');
  });
});

$(window).scroll(function() {
    var height = $(window).scrollTop();

    if (height > 600) {
        $('#back2Top').fadeIn();
    } else {
        $('#back2Top').fadeOut();
    }

    if ($(window).scrollTop() >= 75) {
        console.log('scrolling');
        $("#header-bottom").addClass('fixed-header');
    }
    else {
        $("#header-bottom").removeClass('fixed-header');
    }
});

$(document).ready(function() {
    $("#back2Top").click(function(event) {
        event.preventDefault();
        $("html, body").animate({ scrollTop: 0 }, "slow");
        return false;
    });
    $('#back2Top').fadeOut();
});