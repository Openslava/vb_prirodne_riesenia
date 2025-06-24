jQuery(document).ready(function($) {
    $("#add_fapi_connection").live("click",function(){  
        var tagid = $(this).attr('data-tagid');
        var tagname = $(this).attr('data-name');
        var fapilogin = $("#fapi_login").val();
        var fapipassword = $("#fapi_password").val();
        $("#fapi_connection_container").html('<div class="miocms_loading"></div>');  
        $.post(ajaxurl, {"action":"fapi_save_connection","tag_id": tagid,"tag_name":tagname,"fapi_login": fapilogin,"fapi_password":fapipassword}, function(data) {
            $("#fapi_connection_container").html(data);
        }); 
        return false;
    });
});