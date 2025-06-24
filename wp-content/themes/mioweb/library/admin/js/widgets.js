jQuery(document).ready(function($) {

// sidebar name check
$(".cms_create_new_sidebar").click(function() {
    if($("#cms_new_sidebar_name").val()=="") {
        alert('Zadejte název sidebaru');
        $("#cms_new_sidebar_name").focus();
        return false;
    }   
});

//delete sidebar   
$('.cms_delete_widget').live("click", function(){
    var id=$(this).closest('.widgets-sortables').attr('id');
    if(confirm($(this).attr('data-question'))) {
                  $(this).parent().html('<div class="cms_loading"></div>');                  
                  $.post(ajaxurl, {"action":"cms_delete_sidebar","id": id}, function() {
                     $("#"+id).parent().remove();
                  }); 
    }
    return false;
});

});  
