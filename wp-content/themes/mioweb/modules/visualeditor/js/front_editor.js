jQuery(document).ready(function($) {

    $(".ve_prevent_default").click(function(){
        return false;
    });
    
    // Open row setting
    $(".row_edit").live("click",function(){  
        var element=$(this).closest('.row');
        $(".row_edited").removeClass('row_edited');        
        element.addClass('row_edited');
        var code=element.find('.row_content_textarea').val();
        
        openCmsLightbox({ajax_action:'save_row_setting',title:$(this).attr('title'),prefix:"cms_lightbox_editor",width:"970px"});
        var rowid = $(this).attr('data-id');
        $.post(ajaxurl, {"action":"open_row_setting","code": code,"row_id":element.attr('id')}, function(data) {
            addContentCmsLightbox(data,{prefix:"cms_lightbox_editor"}); 
            $('.cms_color_input').minicolors(); 
            createSortedItems();
        }); 
        return false;
    });
    // Save row setting
    $('#cmsl_submit_save_row_setting').live("click",function(e) {
        var form=$('#cms_lightbox_editor .cms_lightbox_form').serialize();
        var row_id=$("[name='row_id']").val();
        closeCmsLightbox({prefix:"cms_lightbox_editor"});
        $(".row_edited").append('<div class="cms_big_loading"></div>');
        $.post(ajaxurl, 'action=save_row_setting&'+form+'&row_id='+row_id , function(data) {
            if(data.reload_row) {
              $(".cms_big_loading").remove(); 
              $('#'+row_id).replaceWith(data.row_content);
            }
            else {
                $(".row_edited").removeClass( "row_fixed row_basic row_full row_window_height row_centered_content row_window_height_noheader" ).addClass( data.row_class );
                $(".row_edited .row_content_textarea").val(data.code);
                $(".row_edited > style").replaceWith(data.style);  
                $(".row_edited .row_background_container").html(data.background);   
                $(".mw_scroll_tonext_icon").remove(); 
                $(".cms_big_loading").remove(); 
                $('.row_edited').css('min-height',data.min_height);
                setWindowHeight('.row_edited.row_window_height', false);
                setWindowHeight('.row_edited.row_window_height_noheader', true);
                $('.row_edited .row_fix_width').css({marginTop:0});
                setCenteredContent('.row_fix_width','.row_edited.row_centered_content');      
                if(data.scroll_arrow) $(".row_edited").append(data.scroll_arrow); 
                $(".row_edited").removeClass('row_edited'); 
            }
            if(data.font) $("head").append("<link href='https://fonts.googleapis.com/css?family="+data.font+"&subset=latin,latin-ext' rel='stylesheet' type='text/css'>");
            
            $("#edited_page").val("1");
            createSorted();
        });   
        return false;
    });
    // open add row
    $(".row_add").live("click",function(){  
        if($(this).attr("data-last")==1) var element=$(this).closest('.add_row_last');
        else var element=$(this).closest('.row');
        $(".row_edited").removeClass('row_edited');        
        element.addClass('row_edited');
        openCmsLightbox({footer:false,title:$(this).attr('title'),prefix:"cms_lightbox_editor",zindex:120});
        $.ajax({
            type:'POST',
            data:{"action":"open_row_select"},
            url: ajaxurl,
            success: function(content) {
               addContentCmsLightbox(content,{prefix:"cms_lightbox_editor"});           
            }
      
        }); 
        return false;
    });
    // add new row
    $(".add_new_row").live("click",function(){  
        var rowtype = $(this).attr('data-type');
        var rowcontent = $(this).attr('data-content');
        closeCmsLightbox({prefix:"cms_lightbox_editor"});
        $.ajax({
            type:'POST',
            data:{"action":"add_new_row","rowtype": rowtype,"content": rowcontent},
            url: ajaxurl,
            success: function(content) {
               $(".row_edited").before(content.row);             
               if(content.row_type=='slider') {
                 $('#row_'+content.id+' .miocarousel').MioCarousel({}); 
               } else {
                 setWindowHeight('#row_'+content.id+'.row_window_height', false);
                 setWindowHeight('#row_'+content.id+'.row_window_height_noheader', true);
                 $('#row_'+content.id+' .row_fix_width').css({marginTop:0});
                 setCenteredContent('.row_fix_width','#row_'+content.id+'.row_centered_content');
               }
               $(".row_edited").removeClass('row_edited'); 
               $("#edited_page").val("1");   
               createSorted();      
            }
      
        }); 
        return false;
    });
    // row delete
    $(".row_delete").live("click",function(){  
        if(confirm('Opravdu chcete tento řádek smazat?')) {
           $(this).closest('.row').slideUp('slow', function() {$(this).remove();});
           $("#edited_page").val("1"); 
        }        
        return false;
    });
    // Copy row
    $(".row_copy").live("click",function(){  
        var element=$(this).closest('.row');
        var oldid=element.attr('id');
        var newid='copy_'+$.now();
        var elid;
        var newelement=element.clone();
        var content=$(".row_content_textarea",element).val();
        newelement.insertAfter(element);
        newelement.attr('id',newid);
        $('#'+newid+' .row_content_textarea').val(content);
        $('#'+newid+' style').each(function(){
            $(this).html($(this).html().replace(new RegExp(oldid,"g"),newid));            
        });
        $('#'+newid+' .element_container').each(function(){
            elid=$(this).attr('id');
            $(this).attr('id','copy_'+elid);
            $('style',this).each(function(){
                $(this).html($(this).html().replace(new RegExp(elid,"g"),'copy_'+elid));            
            });
            //$(this).html($(this).html().replace(new RegExp(elid,"g"),'copy_'+elid));            
        });
        if($('#'+newid).hasClass('row_slider')) {
          $('#'+newid+' .miocarousel .indicators').remove();
          $('#'+newid+' .miocarousel').MioCarousel({}); 
        }
        $("#edited_page").val("1");
        createSorted(); 
        return false;
    });
    // Copy row to memory
    $(".row_copy_memory").live("click",function(){  
        var element=$(this).closest('.row');
        var content=$(".row_content_textarea",element).val();
        openCmsLightbox({button_text:'OK',storno:false,header:false, width:'300px', ajax_action : 'ok' ,footer:'hide',prefix:"cms_lightbox_editor"});
        
        var textarea;
        var c=0;
        var e=0;
        var s=0;
        var i=0;
        var elements = []; 
        var subelements = []; 

          elements = []; 
          subelements = [];
          c=0;
          $(".sortable-col",element).not(".subcol", element).each(function(){
             elements[c]=[];
             subelements[c]=[];
             e=0;
             $(".element_container",this).not(".subcol .element_container", this).each(function(){   
                textarea=$(".element_content_textarea",this).not(".subcol .element_content_textarea", this);
                elements[c][e]=textarea.val();
                subelements[c][e]=[];
                subelements[c][e][0]=[];  
                s=0;              
                $(".subcol-first .element_container", this).each(function(){
                    subelements[c][e][0][s]=[];
                    textarea=$(".element_content_textarea",this);
                    subelements[c][e][0][s]=textarea.val();
                    s++;
                });
                subelements[c][e][1]=[];  
                s=0;              
                $(".subcol-last .element_container", this).each(function(){
                    subelements[c][e][1][s]=[];
                    textarea=$(".element_content_textarea",this);
                    subelements[c][e][1][s]=textarea.val();
                    s++;
                });
                e++;
             });
             c++;
          });

        
        $.ajax({
            type:'POST',
            data:{"action":"copy_row","row":content,"element":elements,"subelement":subelements},
            url: ajaxurl,       
            success: function(content) {
               $('.row_paste').show();  
               showCmsLightboxButtons({prefix:"cms_lightbox_editor"});
               addContentCmsLightbox(content,{prefix:"cms_lightbox_editor"});

            } 
      
        }); 
        
        
        return false;
    });
    // Paste row
    $(".row_paste").live("click",function(){  
        if($(this).attr("data-last")==1) var element=$(this).closest('.add_row_last');
        else var element=$(this).closest('.row');
        $(".row_edited").removeClass('row_edited');   
        $(this).addClass('cms_loading_w');     
        element.addClass('row_edited');
        var row=getCookie("ve_copy_row"); 
        $.ajax({
            type:'POST',
            data:{"action":"paste_row","content":row},
            url: ajaxurl,
            success: function(data) {

               $(".row_edited").before(data.content);
               $(".row_edited").removeClass('row_edited'); 
               $("#edited_page").val("1");  
               //document.cookie = 've_copy_row=0; expires=Thu, 01 Jan 1970 00:00:01 GMT; path=/';  
               $('.cms_loading_w').removeClass('cms_loading_w'); 
               //$('.row_paste').hide();   
               setCenteredContent('.row_fix_width','.row_centered_content');
               if(data.font) $("head").append("<link href='https://fonts.googleapis.com/css?family="+data.font+"&subset=latin,latin-ext' rel='stylesheet' type='text/css'>");
               createSorted(); 
            }
      
        }); 
        return false;
    });
    
    function getLayer(target) {
        var textarea;        
        var r=0;
        var c=0;
        var e=0;
        var s=0;
        var i=0;
        var rows = [];
        var elements = []; 
        var single_elements = {}; 
        var subelements = []; 
        $(target+" #sortable-content > .row").each(function(){
          elements[r] = []; 
          subelements[r] = [];
          rows[r]=$(".row_content_textarea",this).val();  
          c=0;
          $(".sortable-col",this).not(".subcol", this).each(function(){
             elements[r][c]=[];
             subelements[r][c]=[];
             e=0;
             $(".element_container",this).not(".subcol .element_container", this).each(function(){   
                textarea=$(".element_content_textarea",this).not(".subcol .element_content_textarea", this);
                elements[r][c][e]=textarea.val();
                subelements[r][c][e]=[];
                subelements[r][c][e][0]=[];  
                s=0;              
                $(".subcol-first .element_container", this).each(function(){
                    subelements[r][c][e][0][s]=[];
                    textarea=$(".element_content_textarea",this);
                    subelements[r][c][e][0][s]=textarea.val();
                    s++;
                });
                subelements[r][c][e][1]=[];  
                s=0;              
                $(".subcol-last .element_container", this).each(function(){
                    subelements[r][c][e][1][s]=[];
                    textarea=$(".element_content_textarea",this);
                    subelements[r][c][e][1][s]=textarea.val();
                    s++;
                });
                e++;
             });
             c++;
          });
          r++;
        });
        
        var tn;
        $(".element_single").each(function(){                       
            textarea=$(".element_content_textarea",this);
            tn=textarea.attr('name');
            single_elements[tn]={};
            single_elements[tn]=textarea.val(); 
        });
        
        var ret;
        ret=[];
        ret['elements']=elements;
        ret['rows']=rows;
        ret['subelements']=subelements;
        ret['single_elements']=single_elements;
        return ret;
    }
    
    // Save page
    $(".ev_save_page").click(function(el) { 
        var layer;
        layer=getLayer('#content');
        var status=$(this).attr('data-status');
        var old_status=$(this).attr('data-ostatus');
        var form=$('#ve_save_post_form').serialize();
        if(old_status!=status) action='save_page_reload';
        else action='save_page_ok';
        openCmsLightbox({button_text:'STORNO',storno:false,header:false, width:'300px', ajax_action : 'ok' ,footer:'hide',prefix:"cms_lightbox_editor"});
        $.post(ajaxurl, {"action":'save_page',"form":form,"row":layer['rows'],"element":layer['elements'],"single_elements":layer['single_elements'],"subelement":layer['subelements'],"status":status,"old_status":old_status,"post_id":$("#ev_post_id").val(),"page_type":$("#ve_page_type").val()}, function(data) {
            closeCmsLightbox({prefix:"cms_lightbox_editor"}); 
            $("#edited_page").val("0");
        }) 
        .fail( function () {
            addContentCmsLightbox('<div style="padding: 25px; text-align: center;">'+texts.storno_save_page_info+'</div>',{prefix:"cms_lightbox_editor"}); 
            showCmsLightboxButtons({prefix:"cms_lightbox_editor"});    
        });   
        el.preventDefault();
    });
    
    $("#cmsl_submit_ok").live("click",function(e) {
        closeCmsLightbox({prefix:"cms_lightbox_editor"});  
        e.preventDefault();
    });
    $("#cmsl_submit_save_page_reload").live("click",function(e) {
        closeCmsLightbox({prefix:"cms_lightbox_editor"});
        location.reload();
        e.preventDefault();
    });
    
    // Delete page
    $("#ve_delete_page").click(function(e) {
        if(confirm('Opravdu chcete tuto stránku smazat?')) return true;
        else return false;
    });
    
    // Open element setting
    $(".ece_edit").live("click",function(){  
        var element=$(this).closest('.element_container');
        var type=$(this).attr('data-type');
        $(".element_container_edited").removeClass('element_container_edited');        
        element.addClass('element_container_edited');
        var code=element.find('> .element_content_textarea').val();
        openCmsLightbox({title:$(this).attr('title'),prefix:"cms_lightbox_editor"});
        $.ajax({
            type:'POST',
            data:{"action":"open_element_setting","code": code,"type":type,'post_id':$("#ev_post_id").val()},
            url: ajaxurl,
            success: function(content) {
               addContentCmsLightbox(content,{prefix:"cms_lightbox_editor"}); 
               $('.cms_color_input').minicolors(); 
               $('.cms_datepicker').datepicker({ dateFormat: "dd.mm.yy" }); 
               $.datepicker.setDefaults($.datepicker.regional["cs"]);
               createSortedItems();
               
               
            }
      
        }); 
        return false;
    });
    // Open element config
    $(".ece_config").live("click",function(){  
        var element=$(this).closest('.element_container');
        var type=$(this).attr('data-type');
        $(".element_container_edited").removeClass('element_container_edited');        
        element.addClass('element_container_edited');
        var code=element.find('> .element_content_textarea').val();
        
        openCmsLightbox({title:$(this).attr('title'),ajax_action:'save_element_config',prefix:"cms_lightbox_editor"});
        $.ajax({
            type:'POST',
            data:{"action":"open_element_config","code": code,"type":type,"post_id":$("#ev_post_id").val()},
            url: ajaxurl,
            success: function(content) {
               addContentCmsLightbox(content,{prefix:"cms_lightbox_editor"});  
            }      
        }); 
        return false;
    });
    // Save element config
    $('#cmsl_submit_save_element_config').live("click",function(e) {
        var form=$('#cms_lightbox_editor .cms_lightbox_form').serialize();
        closeCmsLightbox({prefix:"cms_lightbox_editor"});
        $(".element_container_edited").append('<div class="cms_loading"></div>');
        $.post(ajaxurl, 'action=save_element_config&'+form , function(data) {            
            if(data.type=="subelement") var elc=$(".element_container_edited .subcol-replace").html();
            $(".element_container_edited").replaceWith(data.content);
            setCenteredContent('.row_fix_width','.row_centered_content');
            if(data.type=="subelement") $("#element_"+data.newkey+" .subcol-replace").html(elc);
            $("#edited_page").val("1");
            FB.XFBML.parse(); 
            createSorted();  
        });   
        
        return false;
    });
    
    // Add element
    $(".add_element").live("click",function(){  
        $(".add_element_edited").removeClass("add_element_edited");
        var subelement=$(this).attr('data-subelement');
        var group=$(this).attr('data-group');
        $(this).addClass("add_element_edited");
        openCmsLightbox({ajax_action:'save_new_element',width:'970px',footer:'hide',zindex:200,title:$(this).attr('title'),prefix:"cms_lightbox_editor"});
        $.ajax({
            type:'POST',
            data:{"action":"open_element_select", "subelement":subelement, "group":group},
            url: ajaxurl,
            success: function(content) {
               addContentCmsLightbox(content,{prefix:"cms_lightbox_editor"});          
            }
      
        }); 
        return false;
    });
    
    // Back to Add element
    $(".back_to_add_element").live("click",function(){  
        $.ajax({
            type:'POST',
            data:{"action":"open_element_select", "subelement":0},
            url: ajaxurl,
            success: function(content) {
                var editor=jQuery( "#cms_lightbox_contentin" ).find( ".wp-editor-container" );
                if(editor.length > 0) {
                    id=jQuery( "#cms_lightbox_contentin .wp-editor-container textarea" ).attr('id');
                    tinyMCE.execCommand('mceFocus', false, id);     
                    tinymce.EditorManager.execCommand('mceRemoveEditor', false, id);               
                } 
                addContentCmsLightbox(content,{prefix:"cms_lightbox_editor"}); 
                hideCmsLightboxButtons({prefix:"cms_lightbox_editor"}); 
                hideCmsBackLink('back_to_add_element',{prefix:"cms_lightbox_editor"});             
            }      
        }); 
        return false;
    });
    
    
    // Add element setting
    $(".open_new_element_setting").live("click",function(){  
        var eltype = $(this).attr('data-type');
        changeContentCmsLightbox({prefix:"cms_lightbox_editor"});
        $.ajax({
            type:'POST',
            data:{"action":"open_new_element_setting","type": eltype,'post_id':$("#ev_post_id").val()},
            url: ajaxurl,
            success: function(content) {
               addContentCmsLightbox(content,{prefix:"cms_lightbox_editor"}); 
               showCmsLightboxButtons({prefix:"cms_lightbox_editor"});
               showCmsBackLink('back_to_add_element',{prefix:"cms_lightbox_editor"});
               $('.cms_color_input').minicolors(); 
               $('.cms_datepicker').datepicker({ dateFormat: "dd.mm.yy" });
               $.datepicker.setDefaults($.datepicker.regional["cs"]);
             
            }
      
        }); 
        return false;
    });
    
    // Save element setting
    $('#cmsl_submit_save_element_setting').live("click",function(e) {
        var element_id=$(".element_container_edited").attr('id');
        var single=0;
        if($(".element_container_edited").hasClass('element_single')) single=1; 
        tinyMCE.triggerSave();
        var form=$('#cms_lightbox_editor .cms_lightbox_form').serialize();
        closeCmsLightbox({prefix:"cms_lightbox_editor"});
        $(".element_container_edited").append('<div class="cms_loading"></div>');  
        $.post(ajaxurl, 'action=save_element_setting&el_id='+element_id+'&single='+single+'&'+form , function(data) {
            if(data.type=="subelement") var elc=$(".element_container_edited .subcol-replace").html();
            $(".element_container_edited").replaceWith(data.content);
            if(data.font) $("head").append("<link href='https://fonts.googleapis.com/css?family="+data.font+"&subset=latin,latin-ext' rel='stylesheet' type='text/css'>");

            if(data.type=="subelement") $("#element_"+data.newkey+" .subcol-replace").html(elc);
            $("#edited_page").val("1");
            FB.XFBML.parse();
            createSorted();
            
            setCenteredContent('.row_fix_width','.row_centered_content');

            $( document ).trigger( 'mio_saved_element_setting' );
        });   
        
        return false;
    });
    
    // Save new element
    $('#cmsl_submit_save_new_element').live("click",function(e) {
        var element_id=$("[name='id_row']").val()+'_'+$("[name='id_col']").val();
        tinyMCE.triggerSave();            
        var form=$('#cms_lightbox_editor .cms_lightbox_form').serialize();  
        closeCmsLightbox({prefix:"cms_lightbox_editor"});
        $(".add_element_edited").before('<div id="new_element_container"><div class="cms_loading"></div></div>');
        $.post(ajaxurl, 'action=save_element_setting&newelement=1&'+form , function(data) {

            $("#new_element_container").replaceWith(data.content);
            $(".add_element_edited").removeClass("add_element_edited");
            if(data.font) $("head").append("<link href='https://fonts.googleapis.com/css?family="+data.font+"&subset=latin,latin-ext' rel='stylesheet' type='text/css'>");
            $("#edited_page").val("1");
            FB.XFBML.parse();
            createSorted();
            setCenteredContent('.row_fix_width','.row_centered_content');
            $( document ).trigger( 'mio_saved_element_setting' );
        });   
        return false;
    });
    
    // delete element
    $('.ece_delete').live("click",function(e) {
        if(confirm('Opravdu chcete tento element smazat?')) {
           $(this).closest('.element_container').slideUp(200, function() {$(this).remove();});
           setCenteredContent('.row_fix_width','.row_centered_content');
           $("#edited_page").val("1"); 
        }
        return false;
    });
    
    // Copy element
    $(".ece_copy").live("click",function(){ 
        var element=$(this).closest('.element_container');
        if(element.find('input[name="element_id"]').val()) {
            alert(texts.element_copy);
        }
        else {
            var oldid=element.attr('id');
            var newid='copy_'+$.now(); 
            var newelement=element.clone();
            newelement.insertAfter(element);
            newelement.attr('id',newid);
            $('#'+newid+' style').each(function(){
                $(this).html($(this).html().replace(new RegExp(oldid,"gi"),newid));
            });
            $("#edited_page").val("1");
        }
        setCenteredContent('.row_fix_width','.row_centered_content');
        return false;
    });    
    
    $('#cmsl_submit_ignore_edited').live("click",function(e) {
        $("#edited_page").val("0");
    });
    
    // Open page setting
    $(".ve_open_page_setting").live("click",function(){  
        openCmsLightbox({title:$(this).attr('title'), width: '98%', ajax_action: 'ignore_edited' });
        var elid = $(this).attr('data-id');
        var setid = $(this).attr('data-setid'); 
        
        var layer;
        layer=getLayer('#content');
        var edited;
        edited=$("#edited_page").val();
        
        $.ajax({
            type:'POST',
            data:{"action":"open_page_setting","post_id": elid,"set_id": setid,"row":layer['rows'],"element":layer['elements'],"subelement":layer['subelements'],"edited":edited},
            url: ajaxurl,
            success: function(content) {
               addContentCmsLightbox(content); 
               $('.cms_color_input').minicolors();  
               $('.cms_datepicker').datepicker({ dateFormat: "dd.mm.yy" });
               $.datepicker.setDefaults($.datepicker.regional["cs"]);
               createSortedItems();
            }
      
        }); 
        return false;
    });  
    // Open basic page setting
    $(".ve_open_basic_page_setting").live("click",function(){  
        openCmsLightbox({title:$(this).attr('title'), width: '98%', ajax_action: 'ignore_edited' });
        var elid = $(this).attr('data-id');
        
        var layer;
        layer=getLayer('#content');
        var edited;
        edited=$("#edited_page").val();
        
        $.ajax({
            type:'POST',
            data:{"action":"open_page_setting","post_id": elid,"single": 1,"set_id": "page_set","row":layer['rows'],"element":layer['elements'],"subelement":layer['subelements'],"edited":edited},
            url: ajaxurl,
            success: function(content) {
               addContentCmsLightbox(content);  
               createSortedItems();
            }
      
        }); 
        return false;
    }); 
    
    // Open single setting
    $(".ve_open_page_single_setting").live("click",function(){  
        openCmsLightbox({title:$(this).attr('title'), width: '98%', ajax_action: 'ignore_edited', body_class: 'cms_lightbox_nopadding' });
        var elid = $(this).attr('data-id');
        var setid = $(this).attr('data-setid');
        var tabid = $(this).attr('data-tabid');
        
        var layer;
        layer=getLayer('#content');
        var edited;
        edited=$("#edited_page").val();
        
        $.ajax({
            type:'POST',
            data:{"action":"open_page_single_setting","post_id": elid,"set_id": setid,"tab_id": tabid,"row":layer['rows'],"element":layer['elements'],"subelement":layer['subelements'],"edited":edited},
            url: ajaxurl,
            success: function(content) {
               addContentCmsLightbox(content); 
               $('.cms_color_input').minicolors();
               $('.cms_datepicker').datepicker({ dateFormat: "dd.mm.yy" });
               $.datepicker.setDefaults($.datepicker.regional["cs"]); 
               createSortedItems();
            }
      
        }); 
        return false;
    });   
    
    // Open global setting
    $(".open-setting").live("click",function(){  
        openCmsLightbox({title:$(this).attr('title'), width: '98%', ajax_action: 'ignore_edited', body_class: 'cms_lightbox_nopadding' });
        var setting = $(this).attr('data-setting');
        var type = $(this).attr('data-type');
        var action = "open_global_setting";
        if(type=='group') action = "open_global_single_setting";
        if(type=='single_tab') action = "open_global_single_setting_tab";
        
        var layer;
        layer=getLayer('#content');
        var edited;
        edited=$("#edited_page").val();
         
        $.ajax({
            type:'POST',
            data:{"action":action,"setting": setting,"row":layer['rows'],"element":layer['elements'],"subelement":layer['subelements'],"edited":edited,"post_id": $("#ev_post_id").val()},
            url: ajaxurl,
            success: function(content) {
               addContentCmsLightbox(content); 
               $('.cms_color_input').minicolors(); 
               $('.cms_datepicker').datepicker({ dateFormat: "dd.mm.yy" });
               $.datepicker.setDefaults($.datepicker.regional["cs"]);
            }
      
        }); 
        return false;
    }); 
    // Open member setting
    $(".open-member-setting").live("click",function(){  

        openCmsLightbox({title:$(this).attr('title'), width: '98%', ajax_action: 'ignore_edited', body_class: 'cms_lightbox_nopadding' });
        
        var layer;
        layer=getLayer('#content');
        var edited;
        edited=$("#edited_page").val();
         
        $.ajax({
            type:'POST',
            data:{"action":"open_member_setting","row":layer['rows'],"element":layer['elements'],"subelement":layer['subelements'],"edited":edited,"post_id": $("#ev_post_id").val()},
            url: ajaxurl,
            success: function(content) {
               addContentCmsLightbox(content); 
               $('.cms_color_input').minicolors(); 
               $('.cms_datepicker').datepicker({ dateFormat: "dd.mm.yy" });
               $.datepicker.setDefaults($.datepicker.regional["cs"]);
            }
      
        }); 
        return false;
    }); 

    // hide / show editor features
    $('.hide-editor-features').live('click',function(){
        $(this).html(texts.editor_features_show);
        $('body').addClass('ve_hidden_features');
        $(this).removeClass('hide-editor-features');
        $(this).addClass('show-editor-features');
        document.cookie = 've_hidden_features=1; path=/';
        return false;
    });
    $('.show-editor-features').live('click',function(){
        $(this).html(texts.editor_features_hide);
        $('body').removeClass('ve_hidden_features');
        $(this).removeClass('show-editor-features');
        $(this).addClass('hide-editor-features');
        document.cookie = 've_hidden_features=0; path=/'; 
        return false;
    });
    
    // hide / show editor panel
    $('.shp-hide-panel').live("click",function(){
      $(this).removeClass('shp-hide-panel');
      $(this).addClass('shp-show-panel');
        var animate_duration = 200;
      $("#ve_editor_panel").animate({ left: "-=185px"}, animate_duration );
      $("html").animate({ 'padding-left': "0"}, animate_duration );
      $("body").removeClass('ve_editor_panel_visible');
      document.cookie = 've_hidden_panel=1; path=/';

        //Let other scripts know about panel hiding and how long it takes
        $( document ).trigger( 'mio_editor_hide_panel', { duration: animate_duration } );
        
      return false;
    });
    $('.shp-show-panel').live("click",function(){
      $(this).removeClass('shp-show-panel');
      $(this).addClass('shp-hide-panel');
        var animate_duration = 200;
      $("#ve_editor_panel").animate({ left: "+=185px"}, animate_duration );
      $("html").animate({ 'padding-left': "185px"}, animate_duration );
      $("body").addClass('ve_editor_panel_visible');
      document.cookie = 've_hidden_panel=0; path=/';

        //Let other scripts know about panel showing and how long it takes
        $( document ).trigger( 'mio_editor_show_panel', { duration: animate_duration } );
        
      return false;
    });    
    
    
    // alert before leaving page
    window.onbeforeunload = confirmExit;
    function confirmExit() {
      if ($("#edited_page").val()=="1") {
            return texts.before_leave_page;
      }
    }
    
    $('.mioweb_new_version_info').click(function(){
      openCmsLightbox({ajax_action:'',footer:false,title:$(this).attr('title'),width:'800px'}); 
      var content=$('.mioweb_new_version_info_popup').html();
      addContentCmsLightbox(content);
      return false;
    });    
    
    // add feature
    $(".ve_add_simple_feature").live("click",function(){       
            var id = $(this).attr('data-id');
            var tagid = $(this).attr('data-tagid');
            var tagname = $(this).attr('data-name');
            var fields = $(this).attr('data-fields');
            var link=$(this);  
            var container= $(this).closest('.ve_items_feature_container').find('.ve_features_container');        
            $(this).attr('data-id',parseInt(id)+1);
            container.append('<div class="ve_item_feature_'+id+' ve_item_feature_container"><div class="miocms_loading"></div></div>');   
            $.post(ajaxurl, {"action":"ve_generate_simple_feature_ajax","id": id,"tagid": tagid,"tagname":tagname,"fields":fields}, function(data) {
                $(".miocms_loading").remove();
                $(".ve_item_feature_"+id,container).html(data);
            }); 
        return false;
    }); 
    // delete feature
    $(".ve_delete_feature").live("click",function(){   
        var text=$(this).attr('title'); 
        if(confirm(text))
            $(this).closest('.ve_item_feature_container').slideUp('fast', function() {$(this).remove();});
        return false;
    });
    
    //testamonials  ******************************************************************
    // add testamonial
    $("#ve_add_testimonial").live("click",function(){          
            var id = $(this).attr('data-id');
            var tagid = $(this).attr('data-tagid');
            var tagname = $(this).attr('data-name');
            var link=$(this);
            $(this).attr('data-id',parseInt(id)+1);
            $("#ve_testimonials_container").append('<div id="ve_testimonial_'+id+'" class="ve_item_container ve_setting_container ve_sortable_item"><div class="miocms_loading"></div></div>');   
            $.post(ajaxurl, {"action":"ve_generate_testimonial_ajax","id": id,"tagid": tagid,"tagname":tagname}, function(data) {
                $(".miocms_loading").remove();
                $("#ve_testimonial_"+id).html(data);
                createSortedItems();
            }); 
        return false;
    });
  
  
  //bullets  ************************************************************************
    // add bullet
    $("#ve_add_bullet").live("click",function(){          
            var id = $(this).attr('data-id');
            var tagid = $(this).attr('data-tagid');
            var setting = $(this).attr('data-setting');
            var tagname = $(this).attr('data-name');
            var link=$(this);
            $(this).attr('data-id',parseInt(id)+1);
            $("#ve_items_container").append('<div id="ve_item_'+id+'" class="ve_item_container ve_setting_container ve_sortable_item"><div class="miocms_loading"></div></div>');   
            $.post(ajaxurl, {"action":"ve_generate_bullet_ajax","id": id,"tagid": tagid,"tagname":tagname,"setting":setting}, function(data) {
                $(".miocms_loading").remove();
                $("#ve_item_"+id).html(data);
                createSortedItems();
            }); 
        return false;
    });
    
    
    //features  ************************************************************************
    // add feature
    $("#ve_add_feature").live("click",function(){          
            var id = $(this).attr('data-id');
            var tagid = $(this).attr('data-tagid');
            var tagname = $(this).attr('data-name');
            var link=$(this);          
            $(this).attr('data-id',parseInt(id)+1);
            $("#ve_items_container").append('<div id="ve_item_'+id+'" class="ve_item_container ve_setting_container ve_sortable_item"><div class="miocms_loading"></div></div>');   
            $.post(ajaxurl, {"action":"ve_generate_feature_ajax","id": id,"tagid": tagid,"tagname":tagname}, function(data) {
                $(".miocms_loading").remove();
                $("#ve_item_"+id).html(data);
                $('.cms_color_input').minicolors(); 
                createSortedItems();
            }); 
        return false;
    });
    
    //peoples  ************************************************************************
    // add person
    $("#ve_add_person").live("click",function(){          
            var id = $(this).attr('data-id');
            var tagid = $(this).attr('data-tagid');
            var tagname = $(this).attr('data-name');
            var link=$(this);          
            $(this).attr('data-id',parseInt(id)+1);
            $("#ve_items_container").append('<div id="ve_item_'+id+'" class="ve_item_container ve_setting_container  ve_sortable_item"><div class="miocms_loading"></div></div>');   
            $.post(ajaxurl, {"action":"ve_generate_person_ajax","id": id,"tagid": tagid,"tagname":tagname}, function(data) {
                $(".miocms_loading").remove();
                $("#ve_item_"+id).html(data);
                $('.cms_color_input').minicolors(); 
                createSortedItems();
            }); 
        return false;
    });
    
    
    //customform  ************************************************************************
    // add formitem
    $(".ve_add_formitem").live("click",function(){          
            var id = $(this).attr('data-id');
            var tagid = $(this).attr('data-tagid');
            var tagname = $(this).attr('data-name');
            var link=$(this);          
            $(this).attr('data-id',parseInt(id)+1);
            $(".ve_items_container_"+tagid).append('<div id="ve_item_'+id+'" class="ve_item_container ve_setting_container  ve_sortable_item"><div class="miocms_loading"></div></div>');   
            $.post(ajaxurl, {"action":"ve_generate_formitem_ajax","id": id,"tagid": tagid,"tagname":tagname}, function(data) {
                $(".miocms_loading").remove();
                $(".ve_items_container_"+tagid+" #ve_item_"+id).html(data);
                $('.cms_color_input').minicolors(); 
                createSortedItems();
            }); 
        return false;
    });
    // add formitem item
    $(".ve_add_formitem_subitem").live("click",function(){          
            var id = $(this).attr('data-id');
            var itemid = $(this).attr('data-itemid');
            var tagid = $(this).attr('data-tagid');
            var tagname = $(this).attr('data-name');
            var link=$(this);          
            $(this).attr('data-id',parseInt(id)+1);

            
            $(".ve_items_feature_container_"+tagid+" .ve_items_feature_container_"+itemid).append('<div id="ve_item_feature_'+itemid+'_'+id+'" class="ve_item_feature_container"><div class="miocms_loading"></div></div>');   

            $.post(ajaxurl, {"action":"ve_generate_formitem_item_ajax","id": id,"tagid": tagid,"tagname":tagname}, function(data) {
                $(".miocms_loading").remove();
                $(".ve_items_feature_container_"+tagid+" .ve_items_feature_container_"+itemid+" #ve_item_feature_"+itemid+"_"+id).html(data);
            }); 

        return false;
    }); 
    // select formitem type
    $(".formitem_select_type").live("change",function(){          
        var type = $(this).val();

        if(type=='textarea' || type=='text' || type=='password') {
            $(this).closest('.ve_item_body').find('.formitem_subitems').hide();
            $(this).closest('.ve_item_body').find('.formitem_content').show();
            $(this).closest('.ve_item_body').find('.formitem_agree').hide();
        } else if(type=='agree') {
            $(this).closest('.ve_item_body').find('.formitem_agree').show();
            $(this).closest('.ve_item_body').find('.formitem_content').hide();
            $(this).closest('.ve_item_body').find('.formitem_subitems').hide();
        } else {
            $(this).closest('.ve_item_body').find('.formitem_subitems').show();
            $(this).closest('.ve_item_body').find('.formitem_content').hide();
            $(this).closest('.ve_item_body').find('.formitem_agree').hide();
        }
    }); 
    
    //multipage select  ******************************************************************
    // add multipage
    $("#ve_add_multipage").live("click",function(){          
            var id = $(this).attr('data-id');
            var tagid = $(this).attr('data-tagid');
            var tagname = $(this).attr('data-name');
            var link=$(this);
            $(this).attr('data-id',parseInt(id)+1);
            $("#ve_multipageselect_container").append('<div id="ve_multipageselect_'+id+'" class="ve_item_multipageselect"><div class="miocms_loading"></div></div>');   
            $.post(ajaxurl, {"action":"ve_generate_multipageselect_ajax","id": id,"tagid": tagid,"tagname":tagname}, function(data) {
                $(".miocms_loading").remove();
                $("#ve_multipageselect_"+id).html(data);
            }); 
        return false;
    }); 
    // delete
    $(".ve_delete_select").live("click",function(){    
        if(confirm(texts.delete_confirm))
            $(this).closest('.ve_item_multipageselect').slideUp('fast', function() {$(this).remove();});
        return false;
    }); 
    // reset page statistics
    $("#ve_reset_page_statistics").live("click",function(){          
            var id = $(this).attr('data-id');
            if(confirm(texts.ab_reset)) {
                $(this).closest('.set_form_row').html(texts.ab_nodata);   
                $.post(ajaxurl, {"action":"ve_reset_page_statistics","post_id": id}, function() {}); 
            }
        return false;
    }); 
     
    
    //edit menu  ************************************************************************
    
    $(".ve_edit_menu").live("click",function(){  
        var id = $(this).attr('data-menuid');
        var modul = $(this).attr('data-modul');
        var post_id = $("#ev_post_id").val();
        $('.menu_edited').removeClass('menu_edited');
        parent=$(this).closest('.menu_editbar_container').parent();
        parent.closest('nav').addClass('menu_edited');
        parent_id=parent.attr('id');
        openCmsLightbox({ajax_action:'save_menu',title:$(this).attr('title')}); 
        $.post(ajaxurl, {"action":"open_menu_setting","menu_id": id,"post_id": post_id,"modul": modul,"location": parent_id}, function(data) {
            addContentCmsLightbox(data);
            createSortedItems( true );
        });
        return false;
    });
    $(".ve_add_menu").live("click",function(){  
        var modul = $(this).attr('data-modul');
        var post_id = $("#ev_post_id").val();
        var location = $(this).attr('data-location');
        $('.menu_edited').removeClass('menu_edited');
        parent=$(this).closest('.add_menu_container');
        parent.addClass('menu_edited');
        openCmsLightbox({ajax_action:'save_menu',title:$(this).attr('title')}); 
        $.post(ajaxurl, {"action":"open_menu_setting","menu_id": "","post_id": post_id,"modul": modul,"location": location}, function(data) {
            addContentCmsLightbox(data);
            createSortedItems( true );
        }); 
        return false;
    });
    
    // single menu
    
    $(".open_menuselect_editor").live("click",function(){  
        var id = $(this).attr('data-id');
        $(this).closest('.ve_menuselect_container').addClass('ve_menuselect_container_create');
        openCmsLightbox({ajax_action:'save_single_menu', title:$(this).attr('title'),zindex:999999,prefix:'cms_lightbox_create_menu'}); 
        $.post(ajaxurl, {"action":"open_single_menu_setting", "menu_id": id}, function(data) {
            addContentCmsLightbox(data,{prefix:'cms_lightbox_create_menu'});
            createSortedItems( true );
        });
        return false;
    });
    
    // toggle menu item
    $(".ve_pack_setting_container_head").live("click",function(){  
        $(this).closest('.ve_pack_setting_container').find('.ve_pack_setting_container_body:first').toggle();
        return false;
    });
    
    // delete menu item
    $(".ve_pack_setting_delete").live("click",function(){    
        if(confirm(texts.delete_confirm)) $(this).closest('.ve_pack_setting_container').slideUp('slow', function() {$(this).remove();});
        return false;
    });
    
    // add menu item
    $("#ve_add_menu_item").live("click",function(){          
        var id = $(this).attr('data-id');
        $(this).attr('data-id',parseInt(id)+1);

        var new_item = $('<li/>');
        new_item.attr( 'class', 've_nestedsortable__item ve_pack_setting_container' );
        new_item.attr( 'id', 've_item_' + id );

        var loading = $( '<div/>' );
        loading.addClass( 'miocms_loading' );
        new_item.append( loading );

        $( '#ve_menu_selected_menu_container .ve_nestedsortable' ).append( new_item );

        $.post(ajaxurl, {"action":"ve_generate_edit_menu_item","id": id}, function(data) {
            $(".miocms_loading").remove();
            $("#ve_item_"+id).html(data);
        });

        return false;
    });
    // change url input
    $(".edit-menu-item-custom").live("change",function(){          
          var id = $(this).attr('data-id');
          $("#edit-menu-item-url-"+id).toggle();
          $("#edit-menu-item-page-"+id).toggle(); 
    });
    
    //change menu
    $('#ve_menu_selector').live("change",function(){          
          var menu_id = $(this).val();
          if(menu_id!='') {
              $("#ve_menu_selected_menu_container").html('<div class="cms_loading"></div>');
              $.post(ajaxurl, {"action":"ve_change_menu_setting","menu_id": menu_id}, function(data) {
                  $("#ve_menu_selected_menu_container").html(data);  
                  createSortedItems( true );
              }); 
          }
          else {
              $("#ve_menu_selected_menu_container").html('');
          }
    });
    // add new menu
    $("#ve_add_new_menu").live("click",function(){                
        $("#add_new_menu_container").show();
        $("#ve_menu_selected_menu_container").hide();
        return false;
    });
    // storno add new menu
    $("#ve_storno_new_menu").live("click",function(){                
        $("#add_new_menu_container").hide();
        $("#ve_menu_selected_menu_container").show();
        return false;
    });
    // save new menu
    $('#ve_save_new_menu').live("click",function(e) {
        var name=$('#add_new_menu_name').val();
        if(name) { 
        $.post(ajaxurl, {"action":"ve_create_new_menu","name": name}, function(data) {
            if(data=='false') {
                alert(texts.menu_conflict);
            } else {
                $(".add_new_menu_container").hide();
                $("#ve_menu_selected_menu_container").show();
                $("#ve_menu_selected_menu_container").html(data);  
                createSortedItems( true );
            }  
        });  
        }
        else alert(texts.enter_menu_name);
        return false;
    });
    // save menu
    $('#cmsl_submit_save_menu').live("click",function(e) {
        var form=$('.cms_lightbox_form').serialize();
        closeCmsLightbox();
        $(".menu_edited").prepend('<div class="cms_loading"></div>');
        $( '#mobile_nav' ).remove();
        $.post(ajaxurl, 'action=save_menu_setting&'+form , function(data) {
            $(".menu_edited").replaceWith(data);
            $( document ).trigger( 'mio_editor__replaced_menu' );
        });   
        return false;
    });
    
    // save single menu
    $('#cmsl_submit_save_single_menu').live("click",function(e) {
        var form=$('#cms_lightbox_create_menu_form').serialize();
        var type=$('#single_menu_action').val();
        closeCmsLightbox({prefix:'cms_lightbox_create_menu'});
        $.post(ajaxurl, 'action=save_menu_setting&'+form , function(data) {   
            if(type=="create") {
                $('.ve_menuselect_container_create .ve_menuselect_selector').append('<option value="'+data.id+'">'+data.title+'</option>');
                $('.ve_menuselect_container_create .ve_menuselect_selector').val( data.id ); 
                $('.ve_menuselect_container_create .edit_menuselect_editor').attr('data-id',data.id);
                $('.ve_menuselect_container_create .delete_menuselect_editor').attr('data-id',data.id);
                $('.ve_menuselect_container_create .ve_menuselect_tools').show();
                $('.ve_menuselect_container_create').removeClass('ve_menuselect_container_create');
            }
        });   
        return false;
    });
    
    // delete single menu
    
    $(".delete_menuselect_editor").live("click",function(){   
       if(confirm(texts.delete_menu_confirm)) {      
        var id=$(this).attr('data-id'); 
        var newid;  
        parent=$(this).closest('.ve_menuselect_container');
        parent.addClass('menu_edited');
        $.post(ajaxurl, {"action":"delete_menu", 'page_id':id}, function(data) { 
            $('.menu_edited .ve_menuselect_selector option:selected').removeAttr('selected').prev('option').attr('selected', 'selected');
            $(".menu_edited .ve_menuselect_selector option[value="+id+"]").remove();
            newid=$('.menu_edited .ve_menuselect_selector').val();
            if(newid=='') $('.menu_edited .ve_menuselect_tools').hide();
            else {
                $('.menu_edited .edit_menuselect_editor').attr('data-id',newid);
                $('.menu_edited .delete_menuselect_editor').attr('data-id',newid);
            }
            parent.removeClass('menu_edited'); 
        }); 
      }    
        return false;
    });
    
    // select field type selectmenu
    $(".ve_menuselect_selector").live("change",function(){       
        var val=$(this).val();
        var tools=$(this).closest('.ve_menuselect_container').find('.ve_menuselect_tools');
        if(val=="") tools.hide();  
        else {
            tools.show(); 
            $(this).closest('.ve_menuselect_container').find('.edit_menuselect_editor').attr('data-id',val);
            $(this).closest('.ve_menuselect_container').find('.delete_menuselect_editor').attr('data-id',val);
        }
        return false;
    });
    
    //page selector  ************************************************************************
    
    var ps_height=($("#ve_page_selector").height()-125);
    $('#ve_page_list').css('height',ps_height+'px');
    
    // hide / show page selector
    $('.ve_open_page_selector').live("click",function(){
      $("#ve_page_selector").animate({ left: "+=550px"}, 200 );
      $("#ve_page_search").focus();
      return false;
    });
    $('.ve_close_page_selector').live("click",function(){
      $("#ve_page_selector").animate({ left: "-=550px"}, 200 );
      return false;
    }); 
    
    // delete page
    $(".ve_delete_page_ajax").click(function(){  
        if(confirm(texts.delete_page_confirm)) {
           var page_id=$(this).attr('data-id');
           var current_id=$('ev_post_id').val();
           $(this).closest('div').slideUp('slow', function() {$(this).remove();});
           $.post(ajaxurl, {"action":"delete_page","page_id": page_id,"delete_totrash": '1'}, function(data) {   
           });  
        }        
        return false;
    });
    
    //search page
    
    $( "#ve_page_search" ).keyup(function() {
        var searched=$(this).val().toLowerCase();
        var i;
        var j=0;
        var k;
        var text;
        if(searched!="") {
            $(".ve_page_selector_tabs a").removeClass('active');
            $(".ve_page_selector_tabs a[data-target='all']").addClass('active');
            $("#ve_pagelist_empty_search").hide();
            $(".ve_page_selector_list").show();
            $(".ve_page_selector_list li div").hide();
            $(".ve_page_list_home").hide();
            
            $(".ve_page_selector_list").each(function(){
                i=0;
                $("li > div", this).each(function(){
                    text=$(".ve_page_item .ve_page_item_title",this).html().toLowerCase();
                    if(text.search(searched)>0) {
                        $(this).show();
                        i++;
                    }                                       
                });               
                if(i==0) $(this).closest(".ve_page_selector_list").hide();
                else j++;
            });
            if(j==0) $("#ve_pagelist_empty_search").show();
        }
        else {
          $(".ve_page_list_home").show();
          $(".ve_page_selector_list li div").show();
          $(".ve_page_selector_list").show();
        }
    });
        
    //page list
    
    $(".ve_page_selector_tabs a").click(function(){  
        var target = $(this).attr('data-target');
        $(".ve_page_selector_tabs a").removeClass('active');
        $(this).addClass('active');
        $('.ve_page_selector_list').hide();
        $('.ve_psl_'+target).show();
        return false;
    });
    $(".ve_page_list_name").click(function(){ 
        var target = $(this).attr('data-target');
        $(this).toggleClass("ve_pln_open");
        $(this).toggleClass("ve_pln_close");
        $(".ve_page_list_"+target).toggle();
        return false;
    });
    
    
    // sortable ********************************************************************
    createSorted();
    $('.ece_move').live("click",function(){          
        return false;
    });
    $('.row_move').live("click",function(){          
        return false;
    });
});

function createSorted() {

     jQuery( "#sortable-content" ).sortable({ 
        handle: '.row_move',
        items: ".row",
        placeholder: "sortable-row-hilelight",
        scroll: true, 
        scrollSensitivity: 80, 
        scrollSpeed: 30,
        opacity: 0.7,
        start: function (event, ui) {
          ui.placeholder.height(ui.item.height()); 
          ui.placeholder.height(50);       
        }, 
        stop: function (event, ui) {
          jQuery("#edited_page").val("1");
        }, 
    });
    
    jQuery( ".sortable-col" ).sortable({ 
        handle: '.ece_move',
        items: ".element_container",
        cancel:".sortable-disabled",
        connectWith: ".sortable-col",
        placeholder: "sortable-col-hilelight",
        scroll: true, 
        scrollSensitivity: 80, 
        scrollSpeed: 30,
        opacity: 0.7,
        tolerance: "pointer",
        start: function (event, ui) {
          ui.item.addClass('sortable-col-nob');
          ui.placeholder.height(50);    
        }, 
        beforeStop: function (event, ui) {
          var type=jQuery(".ece_move", ui.item).attr('data-type');
          if(type=="subelement" && jQuery(ui.helper).parents('.subcol').length)
            return false;
        },
        stop: function (event, ui) {
          ui.item.removeClass('sortable-col-nob');  
          jQuery("#edited_page").val("1");
          setCenteredContent('.row_fix_width','.row_centered_content');
        }, 
    });  
};

function createSortedItems( is_menu ) {

    ( function( $ ){

        var common_options = {
            placeholder: "sortable-col-hilelight",
            start: function (event, ui) {
                ui.item.addClass('sortable-col-nob');
                ui.placeholder.height(50);
            },
            stop: function (event, ui) {
                ui.item.removeClass('sortable-col-nob');
            }
        };

        /*
        If we are dealing with menu editing, add update function which handles menu item parent id switching
         */
        if( is_menu === true ) {
            $.extend( common_options, {
                maxLevels: 3,
                update: function( evt, ui ) {

                    var changed_item = ui.item,
                        new_parent_id;

                    if( changed_item.parent( '.ve_nestedsortable' ).length > 0 ) {
                        new_parent_id = 0;
                    } else {
                        var parent_item = changed_item.parents( '.ve_nestedsortable__item:first' );
                        new_parent_id = parent_item.children( '.ve_nestedsortable__item__wrap' ).find( '.menu-item-data-db-id' ).val();
                    }

                    changed_item.children( '.ve_nestedsortable__item__wrap' ).find( '.menu-item-data-parent-id' ).val( new_parent_id );

                }
            } );
        }
        
        /*
         Single level sortables
         */
        $( ".ve_sortable_items" ).sortable(
            $.extend( {
                handle: '.ve_sortable_handler',
                items: ".ve_sortable_item"
            }, common_options )
        );
        
        /*
         Nested sortables
         */
        $('.ve_nestedsortable').nestedSortable(
            $.extend( {
                handle: '.ve_nestedsortable__item__wrap',
                items: '.ve_nestedsortable__item',
                toleranceElement: '> .ve_nestedsortable__item__wrap'
            }, common_options )
        );

    } )( jQuery );

}

function getCookie(cname)
{
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for(var i=0; i<ca.length; i++)
      {
      var c = ca[i].trim();
      if (c.indexOf(name)==0) return c.substring(name.length,c.length);
    }
    return "";
} 
