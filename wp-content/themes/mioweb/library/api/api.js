jQuery(document).ready(function($) {
    $(".add_api_connection").live("click",function(){  
        var container=$(this).closest('.api_connection_container'); 
        var tagid = $(this).attr('data-tagid');
        var tagname = $(this).attr('data-name');  
        var api = $(this).attr('data-api');   
        var type = $(this).attr('data-type');   
        var login = $(".api_login",container).val();
        var password = $(".api_password",container).val();  
         
        container.html('<div class="miocms_loading"></div>');  
        
        $.post(ajaxurl, {"action":"mioweb_api_save_connection","api": api,"tag_id": tagid,"tag_name":tagname,"login": login,"password":password,"type": type}, function(data) {
            container.html(data);
        });        
        return false;
    });
    $(".change_api_selector").live("change",function(){  
        var container=$(this).closest('.mw_api_connection_container'); 
        var tagid = $(this).attr('data-id');
        var tagname = $(this).attr('data-name');  
        var api = $(this).val();   
        var type = $(this).attr('data-type');   
        
        if(api!='se') $('.form_look_setting').hide();
        else $('.form_look_setting').show();
         
        $('.mw_api_selector_container',container).html('<div class="miocms_loading"></div>');  
        
        $.post(ajaxurl, {"action":"mw_api_change_selector", "api":api, "tag_id":tagid, "tag_name":tagname, "type": type}, function(data) {
            $('.mw_api_selector_container',container).html(data);
        });        
        return false;
    });
});