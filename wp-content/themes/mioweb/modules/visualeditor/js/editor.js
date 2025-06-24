jQuery(document).ready(function($) {    
    
    // change template
    $(".ve_change_template_but").click(function() {
        var id=$(this).attr('data-id');
        var type=$(this).attr('data-type');
        openCmsLightbox({title:$(this).attr('title'), ajax_action:'change_template', width: '98%',zindex:999999});
        $.post(ajaxurl, {action: 've_change_template', post_id: id,  post_type: type}, function(content) {  
             addContentCmsLightbox(content); 
        });    
    		return false;
    });
    $('#cmsl_submit_change_template').live("click",function(e) {
      if(!$("#keep_content").prop('checked')) {
          if(!confirm(ed_texts.change_theme_confirm)) return false;
      }
    });
    
    //create new page
    $(".create-new-page").click(function() {
        var type=$(this).attr('data-type');
        openCmsLightbox({title:$(this).attr('title'), ajax_action:'create_new_page', width: '98%'});
        $.post(ajaxurl, {action: 've_create_page', 'page_type': type}, function(content) {  
             addContentCmsLightbox(content); 
        });
    		return false;
    });
    
    //copy pag
    $(".create-page-copy").click(function() {
        var id=$(this).attr('data-id');
        openCmsLightbox({title:$(this).attr('title'), ajax_action:'create_new_page'});
        var type=$(this).attr('data-type');
        $.post(ajaxurl, {action: 've_create_page', post_id: id, copy: true, 'page_type': type}, function(content) {  
             addContentCmsLightbox(content); 
        });
    		return false;
    });

    $('#cmsl_submit_create_new_page').live("click",function(e) {
        var url=$("#ve_post_url").val();
        var parent=$("#ve_post_parent_id").val();
        var req=true;
        var ret=true;
        $( "#cms_lightbox .required" ).each(function() {
            if($(this).val()=="") {
                req=false;
                $(this).addClass('cms_required_alert');
            }
        });
        
        if(!req) {
            alert(ed_texts.create_page_required);
            $("#cms_lightbox_content").animate( { scrollTop: 0,  },  800);
            ret=false;
        } 
        if(ret) {
            $.post(ajaxurl, 'action=ve_check_url&url='+url+'&parent='+parent , function(data) {
                if(data==0) $("#cms_lightbox_form").submit();
                else {
                    alert(ed_texts.create_page_url);
                    $("#ve_post_url").addClass('cms_required_alert');
                }
            });  
        } 
        return false;
    });    
    
    $("#tempate-cat-list a").live("click",function() {
        var id=$(this).attr('data-id');
        $(".ve_template_selbox_active").removeClass('ve_template_selbox_active');
        $(".ve_template_cat_active").removeClass('ve_template_cat_active');
        $(this).addClass('ve_template_cat_active');
        $("#ve_template_selbox_"+id).addClass('ve_template_selbox_active');
    });
    
    $(".ve_template_box").live("click",function() {
        var id=$(this).attr('data-id');
        $(".ve_template_box_select").removeClass('ve_template_box_select');
        $(this).addClass('ve_template_box_select');
        $("#sel_template_rad_"+id).attr('checked', true);
    });
    
    $("#ve_post_title").live("focusout",function() {
        if($("#ve_post_url").val()=="") {
            var url = $(this).val();
            $("#ve_post_url").val(ve_make_slug(url));   
        }
        
    });
    $("#ve_post_url").live("focusout",function() {
        var url = $(this).val();
        $(this).val(ve_make_slug(url));           
    });
    
    // Add shortcode setting
    $(".open_new_shortcode_setting").live("click",function(){  
        
        var eltype = $(this).attr('data-type');
        changeContentCmsLightbox({prefix:"mw_shortcodes"});
        $.ajax({
            type:'POST',
            data:{"action":"open_new_shortcode_setting","type": eltype,'post_id':$("#ev_post_id").val()},
            url: ajaxurl,
            success: function(content) {
               addContentCmsLightbox(content,{prefix:"mw_shortcodes"}); 
               showCmsLightboxButtons({prefix:"mw_shortcodes"});
               $('.cms_color_input').minicolors(); 
               $('.cms_datepicker').datepicker({ dateFormat: "dd.mm.yy" });
               $.datepicker.setDefaults($.datepicker.regional["cs"]);
             
            }
      
        });   
        return false;
    });
    
    // Save new shortcode
    $('#cmsl_submit_save_new_shortcode').live("click",function(e) {
        var selected_text = tinyMCE.activeEditor.selection.getContent( {format : "text"} );
        var form=$('#mw_shortcodes .cms_lightbox_form').serialize();
        closeCmsLightbox({prefix:"mw_shortcodes"});
        $.post(ajaxurl, 'action=save_shortcode_setting&text='+selected_text+'&'+form , function(data) {

            //if(data.font) $("head").append("<link href='https://fonts.googleapis.com/css?family="+data.font+"&subset=latin,latin-ext' rel='stylesheet' type='text/css'>");
            tinyMCE.execCommand('mceInsertContent', 0, data);

        });   
        return false;
    });
    
    // show element/shortcode info
    $(".open_new_element_setting, .open_new_shortcode_setting").live("hover",function(){  
        var desc = $(this).attr('data-desc');
        var title = $(this).html();
        $("#select_element_info").html('<h2>'+title+'</h2><p>'+desc+'</p>');
        var padding=$( "#cms_lightbox_content" ).scrollTop();
        if(padding>60) $("#select_element_info").css( "padding-top",padding-60);  
        else $("#select_element_info").css( "padding-top",0);      
    });
        
});

function ve_make_slug(url){

    url=url.toLowerCase();
    var from = "ãàáäâčďẽèéëêěìíïîňõòóöôřšťůùúüûñžý";
    var to   = "aaaaacdeeeeeeiiiinooooorstuuuuunzy";
    for (var i=0, l=from.length ; i<l ; i++) {
      url = url.replace(new RegExp(from.charAt(i), 'g'), to.charAt(i));
    }
    url = url.replace(/[^a-zA-Z0-9]+/g,'-');
    return url;

};
