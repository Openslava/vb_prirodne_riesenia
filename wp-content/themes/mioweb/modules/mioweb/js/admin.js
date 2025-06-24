jQuery(document).ready(function($) {
    //select campaign
    $("#mioweb_select_campaign").live("change",function(){  
        $(".mioweb_campaign").hide();
        $("#mioweb_campaign_"+$(this).val()).show();
        return false;
    });
    // show add new campaign
    $("#mioweb_show_add_new_campaign").live("click",function(){  
        $("#mioweb_select_campaign_container").hide();
        $(".mioweb_campaign").hide();
        $("#mioweb_add_new_container").show();
        return false;
    });
    
    // storno add new campaign
    $("#mioweb_storno_new_campaign").live("click",function(){         
        $("#mioweb_add_new_container").hide();
        $("#mioweb_select_campaign_container").show();
        $("#mioweb_campaign_"+$("#mioweb_select_campaign").val()).show();
        return false;
    });
    // add campaign
    $("#mioweb_save_campaign").live("click",function(){          
        var name=$("#mioweb_add_new_campaing_name").val();
        if(name!="") {  
            var id = $(this).attr('data-id');
            var tagid = $(this).attr('data-tagid');
            var tagname = $(this).attr('data-name');
            var link=$(this);
            $(this).attr('data-id',parseInt(id)+1);
            $("#mioweb_add_new_campaing_name").val('');  
            $("#mioweb_campaigns_container").append('<div id="mioweb_campaign_'+id+'" class="mioweb_campaign"><div class="miocms_loading"></div></div>');   
            $(".mioweb_campaign").hide();
            $("#mioweb_campaign_"+id).show();
            $.post(ajaxurl, {"action":"add_new_campaign","id": id,"campaign_name":name,"tagid": tagid,"tagname":tagname}, function(data) {
                $(".miocms_loading").remove();
                $("#mioweb_campaign_"+id).html(data);
                $('#mioweb_select_campaign').append('<option value="' + id + '">' + name + '</option>');
                $('#mioweb_select_campaign').val(id).change();
                $('#mioweb_select_campaign').show();
                $("#mioweb_select_campaign_container").show();
                $("#mioweb_add_new_container").hide();
            }); 
        }
        else alert(campaign_texts.create_campaing_name);
        return false;
    });
    
    // add page to campaign
    $(".mioweb_add_campaign_page").live("click",function(){  
        var id = $(this).attr('data-id');
        var tagid = $(this).attr('data-tagid');
        var tagname = $(this).attr('data-name');
        $(this).attr('data-id',parseInt(id)+1);
        link=$(this);
        link.before('<div class="miocms_loading"></div>');
        $.post(ajaxurl, {"action":"add_campaign_page","id": id,"tagid": tagid,"tagname":tagname}, function(data) {
            link.before('<div class="campaing_set campaign_set_box">'+data+'</div>');
            $(".miocms_loading").remove();
        }); 
        return false;
    });
    
    // delete page from campaign
    $(".mioweb_delete_campaign_page").live("click",function(){  
        if(confirm(campaign_texts.delete_page_confirm)) {
            $(this).closest('.campaing_set').slideUp(200, function() {$(this).remove();});
        }
        return false;
    });
    // delete campaign
    $(".mioweb_delete_campaign").live("click",function(){  
        if(confirm(campaign_texts.delete_campaing_confirm)) {
          var id=$(this).attr('data-id');
          $('#mioweb_campaign_'+id).remove();
          $('#mioweb_select_campaign option[value='+id+']').remove();
          if($("#mioweb_select_campaign").val()) {
              $("#mioweb_campaign_"+$("#mioweb_select_campaign").val()).show();
          } else {
             $("#mioweb_select_campaign").hide();
          }
        }
        return false;
    });
    
    // toggle page setting
    $(".mioweb_setting_campaign_page").live("click",function(){  
        $(this).toggleClass('mioweb_setting_campaign_page_active');
        $(this).closest('.campaing_set').find('.campaign_page_set').toggle();
        return false;
    });
    // select campaign page in selector
    $(".campaing_select_page").live("change",function(){  
        var used=false;
        var val=$(this).val();
        var id=$(this).attr('id');
        if(val) {
        $(".campaing_select_page").each(function( index ) {
            if(val==$(this).val() && $(this).attr('id')!=id) used=true;
        });
        if(used) alert(campaign_texts.page_conflict);
        }
    });
    

});
