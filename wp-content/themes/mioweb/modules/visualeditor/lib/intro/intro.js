jQuery(document).ready(function($) {
    $('.start_intro_tutorial').click(function(){    
        startIntroTut($(this).attr('data-tut'));
    });

});

function end_intro_tut(tut) {
    jQuery.post(ajaxurl, {"action":"intro_save_tutorial","id": tut}, function(data) {}); 
}