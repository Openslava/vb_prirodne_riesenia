jQuery(document).ready(function($) { 
    $('.ve_select_web_item').live('click',function() {
        $('.ve_select_web_item').removeClass('ve_select_web_item_selected');
        $('input[name="web_to_install"]').attr('checked', false);
        $(this).addClass('ve_select_web_item_selected');
        $('input[name="web_to_install"]',this).attr('checked', true);
    }); 
    $('.ve_select_web_variants a').live('click',function() {
        var select=$(this).attr('data-select');
        var container=$(this).closest('.ve_select_web_item');
        $(this).closest('.ve_select_web_variants').find('a').removeClass('ve_selected_web_variant');
        $('input',this).attr('checked', true);
        $(this).addClass('ve_selected_web_variant');
        $('img',container).hide();
        $('.ve_select_image_'+select,container).show();
    }); 
    $('.ve_installer_container .cms_lightbox_content').height($(window).height()-$('.ve_installer_steps').height()-$('.cms_lightbox_footer').height()-40);

    // open web install
    $(".open-install-web").live("click",function(){ 
     
        $('body').append('<div id="cms_lightbox_background" class="cms_lightbox_background"></div><div id="cms_lightbox" class="cms_lightbox ve_installer_container" style="width: 98%; left: 1%;"><div class="cms_lightbox_contentin_loading"></div></div> ');
                
        $.ajax({
            type:'POST',
            data:{"action":'install_web_popup'},
            url: ajaxurl,
            success: function(content) {  
               $('.ve_installer_container').html(content);
               $('.ve_installer_container .cms_lightbox_content').height($(window).height()-$('.ve_installer_steps').height()-$('.cms_lightbox_footer').height()-40);  
            }
      
        }); 
        return false;
    }); 
    
    // open web import
    $(".open-import-web").live("click",function(){ 

        openCmsLightbox({ajax_action:'', form:false, footer: false, title:$(this).attr('title'), prefix:"cms_lightbox_editor"}); 
               
        $.ajax({
            type:'POST',
            data:{"action":'import_web_popup'},
            url: ajaxurl,
            success: function(content) {  
               addContentCmsLightbox(content,{prefix:"cms_lightbox_editor"});
            }
      
        }); 
        return false;
    });

});