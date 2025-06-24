jQuery(document).ready(function($) {


    //select member
    $("#member_select").live("change",function(){  
        $(".member_section").hide();
        $("#member_section_"+$(this).val()).show();
        $(".member_delete_member_section").attr('data-id',$(this).val());
        return false;
    });
    // show add new member
    $("#member_show_add_new_member").live("click",function(){  
        $("#member_select_member_container").hide();
        $(".member_section").hide();
        $("#member_add_new_container").show();
        return false;
    });
    
    // storno add new member     
    $("#member_storno_new_member").live("click",function(){         
        $("#member_add_new_container").hide();
        $("#member_select_member_container").show();
        $("#member_section_"+$("#member_select").val()).show();
        return false;
    });
    // add member
    $("#member_save_member").live("click",function(){          
        var name=$("#member_add_new_member_name").val();
        if(name!="") {  
            var id = $(this).attr('data-id');
            var tagid = $(this).attr('data-tagid');
            var tagname = $(this).attr('data-name');
            $(this).attr('data-id',parseInt(id)+1);
            $("#member_add_new_member_name").val('');  
            $("#member_section_container").append('<div id="member_section_'+id+'" class="member_section"><div class="miocms_loading"></div></div>');   
            $(".member_section").hide();
            $("#member_section_"+id).show();
            $.post(ajaxurl, {"action":"add_new_member","id": id,"name":name,"tagid": tagid,"tagname":tagname}, function(data) {
                $(".miocms_loading").remove();
                $("#member_section_"+id).replaceWith(data);
                $('#member_select').append('<option value="' + id + '">' + name + '</option>');
                $('#member_select').val(id).change();
                $('.member_control_select_container').show();
                $("#member_select_member_container").show();
                $("#member_add_new_container").hide();
            }); 
        }
        else alert(mem_texts.new_member_name);
        return false;
    });
    
    // add level 
    $(".member_add_level").live("click",function(){      
        var before=$(this).closest('.member_add_level_container');    
        var name=before.find('.member_input_level_name').val();
        if(name!="") {  
            var id = $(this).attr('data-id');
            var tagid = $(this).attr('data-tagid');
            var tagname = $(this).attr('data-name');
            $(this).attr('data-id',parseInt(id)+1);
            before.find('.member_input_level_name').val('');  
            before.before('<div id="'+tagid+'_level_row_'+id+'" class="member_level_row"><div class="miocms_loading"></div></div>');   
            $.post(ajaxurl, {"action":"add_new_member_level","id": id,"name":name,"tagid": tagid,"tagname":tagname}, function(data) {
                $(".miocms_loading").remove();
                $('#'+tagid+'_level_row_'+id).html(data);
            }); 
        }
        else alert(mem_texts.new_memberlevel_name);
        return false;
    });
    
    // delete member
    $(".member_delete_member_section").live("click",function(){  
        if(confirm(mem_texts.member_delete_confirm)) {
          var id=$(this).attr('data-id');
          $('#member_section_'+id).remove();
          $('#member_select option[value='+id+']').remove();
          if($("#member_select").val()) {
              $("#member_section_"+$("#member_select").val()).show();
              $(".member_delete_member_section").attr('data-id',$("#member_select").val());
          } else {
             $(".member_control_select_container").hide();
          }
        }
        return false;
    });
    
    // delete level
    $(".member_delete_level").live("click",function(){  
        if(confirm(mem_texts.level_delete_confirm)) {
          $(this).closest('.member_level_row').remove();
        }
        return false;
    });
    
    // toggle level setting
    $(".member_level_setting_toggle").live("click",function(){  
        $(this).toggleClass('member_level_setting_toggle_active');
        $(this).closest('.member_level_row').find('.member_level_setting').toggle();
        return false;
    });
    
   //file downlaod
    // add file
    $("#member_add_new_file").live("click",function(){          
            var id = $(this).attr('data-id');
            var tagid = $(this).attr('data-tagid');
            var tagname = $(this).attr('data-name');
            var link=$(this);
            $(this).attr('data-id',parseInt(id)+1);
            $("#member_downloadfiles_container").append('<div id="member_downloadfile_'+id+'" class="member_downloadfile_container ve_item_container ve_setting_container ve_sortable_item"><div class="miocms_loading"></div></div>');   
            
            $.post(ajaxurl, {"action":"member_generate_downloadfile_ajax","id": id,"tagid": tagid,"tagname":tagname}, function(data) {
                $(".miocms_loading").remove();
                $("#member_downloadfile_"+id).html(data);
                createSortedItems();
            }); 
        return false;
    });

    //change member section and show right member levels
    $(".member_select_member_level").live("change",function(){
        $(".member_levels_container").hide();
        $("#member_levels_container_"+$(this).val()).show(); 
    });
});
