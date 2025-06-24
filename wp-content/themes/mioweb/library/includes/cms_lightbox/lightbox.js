function openCmsLightbox(o) {
    
    var defaults        = {
            width           : '900px',
            body_class      : '',
            title           : '',      
            header          : true, 
            footer          : true, 
            storno          : true, 
            button_text     : lightbox_texts.save, 
            ajax_action     : 'save_element_setting', 
            form_action     : '', 
            zindex          : 100,
            prefix          : 'cms_lightbox',
            iframe          : false
    };
    
    var setting = jQuery.extend({}, defaults, o);
    
    var reserve=50;
    
    var lightbox = 
        '<div class="cms_lightbox_background" id="'+setting.prefix+'_background" style="z-index: '+setting.zindex+'"></div>'+
        '<div class="cms_lightbox" id="'+setting.prefix+'" style="width: 98%; max-width: '+setting.width+'; z-index: '+(setting.zindex+5)+';">'+
        '<form class="cms_lightbox_form" id="'+setting.prefix+'_form" method="post" enctype="multipart/form-data" action="'+setting.form_action+'">';
    // header
    if(setting.header) {
        lightbox +='<div class="cms_lightbox_handle" id="'+setting.prefix+'_handle"><a class="cms_lightbox_back_link" id="'+setting.prefix+'_back_link" href="#" title="'+lightbox_texts.back+'"><span></span></a><span>'+setting.title+'</span>';
        if(setting.storno) lightbox+='<a data-target="'+setting.prefix+'" class="cms_close_lightbox" href="#"></a>';
        lightbox +='</div>';
        reserve+=40;        
    }
    if(setting.footer==true || setting.footer=='hide') reserve+=40;
    // content
    lightbox +='<div class="cms_lightbox_content" id="'+setting.prefix+'_content" style="max-height: '+(jQuery(window).height()-reserve)+'px;">';
    if(setting.iframe) lightbox +='<iframe src="'+setting.iframe+'" width="100%" height="'+(jQuery(window).height()-reserve)+'px"></iframe>';
    else lightbox +='<div class="cms_lightbox_contentin '+setting.body_class+' cms_lightbox_contentin_loading"></div>';
    lightbox +='</div>';
    // footer
    lightbox +='<div class="cms_lightbox_footer" id="'+setting.prefix+'_footer">';
    if(setting.footer==true || setting.footer=='hide') {
        if(setting.footer=='hide') {
           lightbox +='<div class="cms_lightbox_hidden">';  
        }
        lightbox +='<div class="cms_lightbox_footer_in">';
        lightbox +='<button id="cmsl_submit_'+setting.ajax_action+'" class="cms_button cms_lightbox_main_but" type="submit" >'+setting.button_text+'</button>';
        if(setting.storno) lightbox +='<a href="#" data-target="'+setting.prefix+'" class="cms_lightbox_storno_but cms_button cms_gray_button cms_lightbox_main_but">'+lightbox_texts.storno+'</a>'; 
        lightbox +='</div>';
        if(setting.footer=='hide') {
           lightbox +='</div>';  
        }       
    }    
    lightbox +='</div></form>';
    lightbox +='</div>';
    
    jQuery('body').append(lightbox);
    var left_margin=(jQuery(window).width()-jQuery('#'+setting.prefix).width())/2;
    jQuery('#'+setting.prefix).css('left',left_margin+'px');
    jQuery('html').addClass('cms_open_lightbox');  
}

function closeCmsLightbox(o) {
    var defaults        = {
        prefix : 'cms_lightbox'
    };   
    var setting = jQuery.extend({}, defaults, o);
    var editor=jQuery( "#"+setting.prefix+" .cms_lightbox_contentin" ).find( ".wp-editor-container" );
    if(editor.length > 0) {
        id=jQuery( "#"+setting.prefix+" .cms_lightbox_contentin .wp-editor-container textarea" ).attr('id');
        tinyMCE.execCommand('mceFocus', false, id);     
        tinymce.EditorManager.execCommand('mceRemoveEditor', false, id);               
    }
    jQuery("#"+setting.prefix).remove();  
    jQuery("#"+setting.prefix+"_background").remove(); 
    jQuery('html').removeClass('cms_open_lightbox'); 
}
function addContentCmsLightbox(content,o) {
    var defaults        = {
        prefix : 'cms_lightbox'
    };   
    var setting = jQuery.extend({}, defaults, o);

    jQuery("#"+setting.prefix+" .cms_lightbox_contentin").html(content); 
    jQuery("#"+setting.prefix+" .cms_lightbox_contentin").removeClass('cms_lightbox_contentin_loading'); 
}
function changeContentCmsLightbox(o) {
    var width;
    var height;
    var defaults        = {
        prefix : 'cms_lightbox'
    };   
    var setting = jQuery.extend({}, defaults, o);
    
    jQuery('#'+setting.prefix+' .cms_lightbox_content').html( '<div class="cms_lightbox_contentin cms_lightbox_contentin_loading"></div>' ); 
    
}
function changeContentCmsLightboxSlide(direct,o) {
    var width;
    var height;
    var defaults        = {
        prefix : 'cms_lightbox'
    };   
    var setting = jQuery.extend({}, defaults, o);
    
    width=jQuery('#'+setting.prefix+' .cms_lightbox_contentin').width();   
    height=jQuery('#'+setting.prefix+'_content').height(); 
    jQuery( '#'+setting.prefix+'_content' ).height(height);
    jQuery('#'+setting.prefix+' .cms_lightbox_contentin').addClass('cms_lightbox_contentin_slide'); 
    jQuery('#'+setting.prefix+' .cms_lightbox_contentin').removeClass('cms_lightbox_contentin');
    jQuery('#'+setting.prefix+' .cms_lightbox_contentin_slide').after( '<div class="cms_lightbox_contentin cms_lightbox_contentin_loading" style="left: '+width+'px; position: absolute; width: '+width+'px;"></div>' ); 
    jQuery( '#'+setting.prefix+' .cms_lightbox_contentin_slide' ).width(width);
    jQuery( '#'+setting.prefix+' .cms_lightbox_contentin_slide' ).animate({"left":"-"+width+"px"}, 300,function(){
        jQuery(this).remove();
    });
    jQuery( '#'+setting.prefix+' .cms_lightbox_contentin' ).animate({"left":"0px"}, 300,function(){
        jQuery( '#'+setting.prefix+' .cms_lightbox_contentin' ).css({"position": "relative","width": "auto"});
    });
    
}


// show / hide buttons
function showCmsLightboxButtons(o) {
    var defaults        = {
        prefix : 'cms_lightbox'
    };   
    var setting = jQuery.extend({}, defaults, o);
    jQuery('#'+setting.prefix+' .cms_lightbox_hidden').show(); 
}
function hideCmsLightboxButtons(o) {
    var defaults        = {
        prefix : 'cms_lightbox'
    };   
    var setting = jQuery.extend({}, defaults, o);
    jQuery('#'+setting.prefix+' .cms_lightbox_hidden').hide(); 
}

// show / hide back link
function showCmsBackLink(addclass,o) {
    var defaults        = {
        prefix : 'cms_lightbox'
    };   

    var setting = jQuery.extend({}, defaults, o);
    jQuery('#'+setting.prefix+' .cms_lightbox_back_link').show();
    jQuery('#'+setting.prefix+' .cms_lightbox_back_link').addClass(addclass); 
}
function hideCmsBackLink(removeclass,o) {
    var defaults        = {
        prefix : 'cms_lightbox'
    };   
    var setting = jQuery.extend({}, defaults, o);
    jQuery('#'+setting.prefix+' .cms_lightbox_back_link').hide(); 
    jQuery('#'+setting.prefix+' .cms_lightbox_back_link').removeClass(removeclass);
}

// document ready
jQuery(document).ready(function($) {
    $('.cms_close_lightbox, .cms_lightbox_storno_but').live("click",function() {
        var target=jQuery(this).attr('data-target');
        closeCmsLightbox({prefix:target});   
        return false;
    });   
    $('.cms_close_lightbox_window').live("click",function() {
        var target=jQuery(this).attr('data-target');
        if ($("#edited_page").val()=="1") {
            if(confirm(lightbox_texts.close_confirm))
                window.parent.closeCmsLightbox({prefix:target});  
        }
        else window.parent.closeCmsLightbox({prefix:target});   
        return false;
    });       
});

function checkCmsForm(o) {
    var defaults        = {
        prefix : 'cms_lightbox'
    };   
    var setting = jQuery.extend({}, defaults, o);
        var req=true;
        var ret=true;
        $( '#'+setting.prefix+' .required' ).each(function() {
            if($(this).val()=="") {
                req=false;
                $(this).addClass('cms_required_alert');
            }
        });
        
        if(!req) {
            alert(lightbox_texts.required);
            ret=false;
        } 
        return ret;           
}
