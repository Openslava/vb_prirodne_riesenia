jQuery(document).ready(function($) {

    // add window post

    $(".open_window_editor").live("click",function(){       
        var url=$(this).attr('data-url');   
        var id=$(this).attr('data-id'); 
        var type=$(this).attr('data-type');   
        var themes=$(this).attr('data-themes');          
        if(id) openCmsLightbox({header:false, footer:false, width:'98%',zindex:999999,prefix:'cms_lightbox_window_editor',iframe:url+'&id='+id});
        else {
            $(this).closest('.ve_windowselect_container').addClass('ve_windowselect_container_create');
            openCmsLightbox({ajax_action:'create_window_post', width:'98%',title:$(this).attr('title'),zindex:999999,prefix:'cms_lightbox_create_window_post'}); 
            $.post(ajaxurl, {"action":"ve_create_window_post_form", 'post_type':type, 'theme_file':themes}, function(data) {
                addContentCmsLightbox(data,{prefix:'cms_lightbox_create_window_post'});
            }); 
        }
        return false;
    });
    
    // duplicate window post

    $(".create_copy_window_editor").live("click",function(){         
        var id=$(this).attr('data-id');  
        var type=$(this).attr('data-type'); 
        var themes=$(this).attr('data-themes');  
        $(this).closest('.ve_windowselect_container').addClass('ve_windowselect_container_create');
        openCmsLightbox({ajax_action:'create_window_post', width:'98%',title:$(this).attr('title'),zindex:999999,prefix:'cms_lightbox_create_window_post'}); 
        $.post(ajaxurl, {"action":"ve_create_window_post_form", 'post_id':id, 'post_type':type, 'theme_file':themes, 'copy':1}, function(data) {
            addContentCmsLightbox(data,{prefix:'cms_lightbox_create_window_post'});
        }); 
        
        return false;
    });
    
    // delete window post

    $(".delete_window_editor").live("click",function(){   
       if(confirm('Opravdu chcete tuto položku smazat?')) {      
        var id=$(this).attr('data-id'); 
        var newid;  
        $(this).closest('.ve_windowselect_container').addClass('ve_windowselect_container_create');
        $.post(ajaxurl, {"action":"delete_page", 'page_id':id}, function(data) { 
            $('.ve_windowselect_container_create .ve_windowselect_selector option:selected').removeAttr('selected').prev('option').attr('selected', 'selected');
            $(".ve_windowselect_container_create .ve_windowselect_selector option[value="+id+"]").remove();
            newid=$('.ve_windowselect_container_create .ve_windowselect_selector').val();
            if(newid=='') $('.ve_windowselect_container_create .ve_window_tools').hide();
            else {
                $('.ve_windowselect_container_create .edit_window_editor').attr('data-id',newid);
                $('.ve_windowselect_container_create .delete_window_editor').attr('data-id',newid);
                $('.ve_windowselect_container_create .create_copy_window_editor').attr('data-id',newid);
            }
            $('.ve_windowselect_container_create').removeClass('ve_windowselect_container_create'); 
        }); 
      }    
        return false;
    });
    
    //create window post
    $("#cmsl_submit_create_window_post").live("click",function(){   
        var req=true;
        var ret=true;
        $( "#cms_lightbox_create_window_post .required" ).each(function() {
            if($(this).val()=="") {
                req=false;
                $(this).addClass('cms_required_alert');
            }
        });
        
        if(!req) {
            alert(weditor_texts.create_weditor);
            ret=false;
        } 
        if(ret) {   
            var form=$('#cms_lightbox_create_window_post .cms_lightbox_form').serialize();
            addContentCmsLightbox('<div class="miocms_loading"></div>',{prefix:'cms_lightbox_create_window_post'});
            
            $.post(ajaxurl, 'action=ve_create_window_post&'+form , function(data) { 
                closeCmsLightbox({prefix:'cms_lightbox_create_window_post'});
                openCmsLightbox({header:false, footer:false, width:'98%',zindex:999999,prefix:'cms_lightbox_window_editor',iframe:data.url});            
                $('.ve_windowselect_container_create .ve_windowselect_selector').append('<option data-title="'+data.title+'" value="'+data.id+'">'+data.title+'</option>');
                $('.ve_windowselect_container_create .ve_windowselect_selector').val( data.id ); 
                $('.ve_windowselect_container_create .edit_window_editor').attr('data-id',data.id);
                $('.ve_windowselect_container_create .delete_window_editor').attr('data-id',data.id);
                $('.ve_windowselect_container_create .create_copy_window_editor').attr('data-id',data.id);
                $('.ve_windowselect_container_create .ve_window_tools').show();
                $('.ve_windowselect_container_create').removeClass('ve_windowselect_container_create'); 
            }); 
        }
        return false;
    });
    
    $(".ve_windowselect_selector ").live("change",function(){       
        var val=$(this).val();
        var tools=$(this).closest('.ve_windowselect_container').find('.ve_window_tools');
        if(val=="") tools.hide();  
        else {
            tools.show(); 
            $(this).closest('.ve_windowselect_container').find('.edit_window_editor').attr('data-id',val);
            $(this).closest('.ve_windowselect_container').find('.delete_window_editor').attr('data-id',val);
            $(this).closest('.ve_windowselect_container').find('.create_copy_window_editor').attr('data-id',val);
        }
        return false;
    });
    
    $(".ve_open_weditor_setting").live("click",function(){    
        var option=$(this).attr('data-option');   
        var key=$(this).attr('data-key'); 
        var postid=$(this).attr('data-postid'); 
        var type=$(this).attr('data-type'); 
        
        var container=$(this).closest('.weditor_content_container');
        $(".weditor_edited").removeClass('weditor_edited');        
        container.addClass('weditor_edited');
        
        openCmsLightbox({ajax_action:'save_weditor_setting',title:$(this).attr('title'),prefix:"cms_lightbox_weditor",width:"970px"});
        $.post(ajaxurl, {"action":"ve_open_weditor_setting","postid":postid,"option":option,"key":key,"type":type}, function(data) {
            addContentCmsLightbox(data,{prefix:"cms_lightbox_weditor"}); 
        }); 
        return false;
    });
    
    $('#cmsl_submit_save_weditor_setting').live("click",function(e) {
        var form=$('#cms_lightbox_weditor .cms_lightbox_form').serialize();
        
        closeCmsLightbox({prefix:"cms_lightbox_weditor"});
        $(".weditor_edited").append('<div class="cms_big_loading"></div>');
        
        $.post(ajaxurl, 'action=ve_save_weditor_setting&'+form , function(data) {
  
            $(".cms_big_loading").remove(); 
            $(".weditor_edited").html(data.content); 
            setWindowHeight('.weditor_edited .row_window_height', false);
            setWindowHeight('.weditor_edited .row_window_height_noheader', true);
            setCenteredContent('.row_fix_width','.weditor_edited .row_centered_content');
            $(".weditor_edited").removeClass('weditor_edited'); 
                       
            if(data.font) $("head").append("<link href='https://fonts.googleapis.com/css?family="+data.font+"&subset=latin,latin-ext' rel='stylesheet' type='text/css'>");

        });   
        return false;
    });
   
    
    
});