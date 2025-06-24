jQuery(document).ready(function($) {
var target; 
$('.cms_color_input').minicolors(); 

// Upload image
    var target; 
    // Old upload image
    $('.cms_upload_image_button2').live("click",function() {
        
        original_send_to_editor = window.send_to_editor;
        target = $(this).attr('target');  
        window.send_to_editor = function(html) {
            imgurl = $('img',html).attr('src').replace(siteurl, ""); 
            $('#'+target).val(imgurl);
            $('#image_'+target+' img').attr('src',siteurl+imgurl);
            $('#image_'+target).show();
            $('#cms_clear_image_'+target).removeClass('cms_nodisp');
            tb_remove();
            window.send_to_editor = original_send_to_editor;
        };
        tb_show('', wpadmin+'media-upload.php?type=image&amp;TB_iframe=true');
        return false;
    });
    
  // New upload image
  var _custom_media = true;
  $('.cms_upload_image_button').live("click",function(e) {
    var _orig_send_attachment = wp.media.editor.send.attachment;
  
    var send_attachment_bkp = wp.media.editor.send.attachment;
    var button = $(this);
    target = $(this).attr('target');  
    _custom_media = true;
    wp.media.editor.send.attachment = function(props, attachment){
      if ( _custom_media ) {
        imgurl = attachment['sizes'][$('.size').val()]['url'].replace(siteurl, ""); 
        $('#'+target).val(imgurl);
        $('#'+target+'_imageid').val(attachment['id']);
        $('#'+target+'_pattern').val('');
        $('#image_'+target+' img').attr('src',siteurl+imgurl);
        $('#image_'+target).show();
        $("#cms_bgimage_pattern_"+target).hide();
        $('#cms_clear_image_'+target).removeClass('cms_nodisp');
        $('.cms_upload_image_container_'+target).addClass('cms_upload_image_uploaded');

      } else {
        return _orig_send_attachment.apply( this, [props, attachment] );
      }
    };

    wp.media.editor.open(button);
    return false;
  });

    //Upload gallery
    $('.cms_upload_gallery_button').live("click",function(e) {
        e.preventDefault();

        var target=$(this).attr('target');
        var name=$(this).attr('data-name');
        var editable=$(this).attr('data-editable');
        var workflow = wp.media.editor.get( 'mio_gallery_upload' );

        //If WF already exist, just open modal
        if( typeof workflow !== 'undefined' ) {
            workflow.open();
            return;
        }

        //Create new WF and bind events
        workflow = wp.media.editor.add( 'mio_gallery_upload', {
            frame:    'post',
            state:    'insert',
            title:    wp.media.view.l10n.addMedia,
            multiple: true
        } );    

        //Insert new images to image list
        workflow.on( 'insert', function( selection ){

            var gallery_wrap = $( '#image_'+target );
            var image_list = gallery_wrap.find( '.cms_image_gallery__wrap' );

            var state = workflow.state();
            console.log(selection);
            selection = selection || state.get('selection');

            console.log(selection);

            if ( ! selection )
                return;

            //show image list
            gallery_wrap.removeClass( 'cms_nodisp' );

            //add new images to the end of list
            $.each( selection.models, function( index, image ){
                var new_image = $( '<img/>' );
                var new_image_url;
                
                if(typeof image.attributes.sizes.thumbnail !== 'undefined') new_image_url = image.attributes.sizes.thumbnail.url;
                else new_image_url = image.attributes.sizes.full.url; 
                
                new_image.attr( 'src', new_image_url );

                var close_button = $( '<button/>' );
                var close_button_text = ( typeof MioAdminjs !== 'undefined' ) ? MioAdminjs.image_gallery_delete_image : 'Odstranit obrázek';
                close_button.text( close_button_text );
                close_button.attr( 'title', close_button_text );
                close_button.addClass( 'cms_image_gallery__item__close_button' );
                if(editable=='1') {
                    var edit_button = $( '<button/>' );
                    var edit_button_text = ( typeof MioAdminjs !== 'undefined' ) ? MioAdminjs.image_gallery_edit_image : 'Upravit obrázek';
                    edit_button.text( edit_button_text );
                    edit_button.attr( 'title', edit_button_text );
                    edit_button.addClass( 'cms_image_gallery__item__edit_button' );  
                } else {
                  var edit_button='';
                }
                var input = $( '<input/>' );
                input.attr( 'type', 'hidden' );
                input.attr( 'name', name+'[]' );
                input.attr( 'value', image.attributes.id );
                input.attr( 'style', 'display: none;' );

                var new_element = $( '<div/>' );
                new_element.addClass( 'cms_image_gallery__item' ); 
                new_element.prepend( new_image );
                new_element.append( edit_button );
                new_element.append( close_button );
                new_element.append( input );

                image_list.append( new_element );   
            } );

        } );

        //Open editor
        workflow.open();

    });

    $( '.cms_image_gallery__item__close_button' ).live( 'click', function( evt ){
        evt.preventDefault();

        var current_button = $( this );
        var item_wraper = current_button.parent( '.cms_image_gallery__item' );
        item_wraper.addClass('is-deleted');
        setTimeout( function(){
            item_wraper.remove();
        }, 300 );
    } );


    $( '.cms_image_gallery__item__edit_button' ).live( 'click', function( evt ){
        evt.preventDefault();

        var clicked_button = $( this ),
            attachment_id = parseInt( clicked_button.parent().find( 'input[name="ve_style[image_gallery_items][]"]' ).val() );


        var query_frame = wp.media.query( { post__in: [ attachment_id ] } );

        var spinner = $( '.cms_image_gallery__spinner' );
        spinner.show();

        query_frame.more().done( function() {

            spinner.hide();

            var attachment = this.first();

            attachment.set( 'attachment_id', attachment.get( 'id' ) ); //WP 'bug'
            console.log(attachment.toJSON());
            var media_frame = Object.create( wp.media( {
                title: 'test',
                frame: 'image',
                state: 'image-details',
                metadata: attachment.toJSON(),
                id: 'mio-image-gallery-detail-modal',
                editing: true
            } ) );

            media_frame.on( 'open', function(){
                var title_text = ( typeof MioAdminjs !== 'undefined' ) ? MioAdminjs.image_gallery__image_detail__heading : '';
                $( '.media-frame-title h1' ).text( title_text );
            } );

            media_frame.on( 'update', function( attachmentObj ){

                spinner.show();

                var xhr = $.ajax( {
                    method: 'POST',
                    url: ajaxurl,
                    data: {
                        action: 'mio_image_gallery_edit_meta',
                        id: attachmentObj.id,
                        caption: attachmentObj.caption,
                        alt: attachmentObj.alt
                    }
                } );

                attachment.set( 'alt', attachmentObj.alt );
                attachment.set( 'caption', attachmentObj.caption );

                xhr.done( function(){
                    spinner.hide();
                } );

            });


            media_frame.open(); // finally open the frame

        } );



    } );


    //Image Gallery sorting
    $( '.cms_image_gallery__wrap' ).live( 'init_sortable', function( evt ){
        var gallery = $( this );

        gallery.sortable( {
            placeholder: "cms_image_gallery__item__placeholder",
            forcePlaceholderSize: true
        } );

    } );


  $('.add_media').live('click', function(){
    _custom_media = false;
  });

   
// select pattern
$('.pattern_select a').live("click",function() {
    var group=$(this).attr('data-group');
    var val=$(this).attr('data-value');
    var pattern=$(this).attr('data-pattern');

    //remove image
    $('#'+group).val('');
    $('#'+group+'_imageid').val('');
    $('#image_'+group).hide();
    
    //add pattern
    $('#'+group+'_pattern').val(val);
    $("#cms_bgimage_pattern_"+group).show();
    $("#cms_bgimage_pattern_"+group).css('backgroundImage','url('+pattern+')');
    
    $('#cms_clear_image_'+group).removeClass('cms_nodisp');

    return false;   
}); 

// clear image
$('.cms_clear_image_button').live("click",function() {
    target = $(this).attr('target');   
    $('#'+target).val('');
    $('#'+target+'_imageid').val('');
    $('#'+target+'_pattern').val('');
    $('#image_'+target).hide();
    $('#cms_bgimage_pattern_'+target).hide();
    $(this).addClass('cms_nodisp');
     $(this).closest('.cms_upload_image_uploaded').removeClass('cms_upload_image_uploaded');
    return false;
}); 
// background filter
$('.mw_bgimage_check_cover').live("change",function() {
    var container=$(this).closest('cms_uploaded_image');
    if(this.checked) {
        $('.mw_bgimage_color_filter').show();
        $('.mw_bgimage_repeat_container').hide();
    } else {
        $('.mw_bgimage_color_filter').hide();
        $('.mw_bgimage_repeat_container').show();
        $('.mw_bgimage_check_color_filter').attr('checked', false);
        $('.mw_bgimage_color_filter_setting').hide();
        $('.cms_bgimage_image_preview_container span').hide();
    }
    return false;
}); 
$('.mw_bgimage_check_color_filter').live("change",function() {
    var container=$(this).closest();
    if(this.checked) {
        $('.mw_bgimage_color_filter_setting').show();
        $('.cms_bgimage_image_preview_container span').show();
    } else {
        $('.mw_bgimage_color_filter_setting').hide();
        $('.cms_bgimage_image_preview_container span').hide();
    }
    return false;
}); 

// Upload file - OLD
$('.cms_upload_file_button2').live("click",function() {
    original_send_to_editor = window.send_to_editor;
    target = $(this).attr('target');  
    window.send_to_editor = function(html) {
        hrefurl = $(html).attr('href');
        $('#'+target).val(hrefurl);
        tb_remove();
        window.send_to_editor = original_send_to_editor;
    };
    var formfield = $('#'+target).attr('name');
    tb_show('', wpadmin+'media-upload.php?type=image&amp;TB_iframe=true');
    return false;
});

// Upload file - NEW
$('.cms_upload_file_button').live("click",function(e) {
    var _orig_send_attachment = wp.media.editor.send.attachment;
  
    var send_attachment_bkp = wp.media.editor.send.attachment;
    var button = $(this);
    target = $(this).attr('target');  
    _custom_media = true;
    wp.media.editor.send.attachment = function(props, attachment){
      if ( _custom_media ) {
        //alert(attachment.toSource());
        imgurl = attachment['url']; 
        $('#'+target).val(imgurl);
      } else {
        return _orig_send_attachment.apply( this, [props, attachment] );
      }
    };

    wp.media.editor.open(button);
    return false;
  });

// clear upload file
$('.cms_clear_upload_button').live("click",function() {
       target = $(this).attr('target');   
       $('#'+target).val('');
       return false;
}); 

// tooltips
$('.cms_toggle_tooltip').live({
    mouseenter: function () {
      $('.cms_tooltip',this).show();
    },
    mouseleave: function () {
      $('.cms_tooltip',this).hide();
    }
});


// Tablist

$(".cms_tabs a").live('click',function() {
    var target = $(this).attr('href'); 
    var group = $(this).attr('data-group'); 
		$("."+group+"_tab a").removeClass("active"); 
		$(this).addClass("active"); 
		$("."+group+"_container").hide(); 
    $(target+'_radio').prop('checked', true);
		$(target).show(); 
		return false;
}); 
$(".cms_small_tabs a").live('click',function() {
    var target = $(this).attr('href'); 
    var group = $(this).attr('data-group'); 
		$("."+group+"_tab a").removeClass("active"); 
		$(this).addClass("active"); 
		$("."+group+"_container").hide(); 
		$(target).show(); 
		return false;
}); 

// tags

    $(".mw_select_tag").live('click',function() {
        var target = $(this).attr('data-tag'); 
        var container = $(this).attr('data-container'); 
    		$(".mw_select_tag").removeClass("active"); 
    		$(this).addClass("active"); 
    		$(".mw_tag_item").hide(); 
    		$(".mw_tag_item_"+target).show(); 
    		return false;
    }); 

// Link switch 

$('.fl_switch_url_type').live('click',function() {
    var container=$(this).closest('.field_link_container');
    if($(this).prop("checked") == true){
       $('.fl_custom_url_container',container).show(); 
       $('.fl_page_selector_container',container).hide();
    }
    else {
       $('.fl_page_selector_container',container).show(); 
       $('.fl_custom_url_container',container).hide(); 
    }
});

// Permalink selector
function permalinkGetEditValue(elEdit) {
    var perm = elEdit.val();
    if(!perm)
        perm = elEdit.attr("placeholder");
    return perm;
}
function permalinkGetParentValue(parentId){
    var elParent = $('.field_permalink_id_'+parentId);
    if(elParent.length > 0)
        return permalinkGetActiveValue(elParent);
    else
        return '';
}
function permalinkGetActiveValue(elDivPermalink){
    var elBasic = elDivPermalink.find('input[type="text"].field_permalink_basic');
    var elNested = elDivPermalink.find('input[type="text"].field_permalink_nested');
    if(elBasic.length && elBasic.is(':visible'))
        return permalinkGetEditValue(elBasic.first());
    else if(elNested.length && elNested.is(':visible'))
        return permalinkGetEditValue(elNested.first());
    else
        return '';
}
function permalinkPreview(elDivPermalink, perm, parentPerm){
    var elPreview = elDivPermalink.find('.field_permalink_preview');
    var baseUri = elDivPermalink.attr('data-base-uri');
    var newPerm = (baseUri ? baseUri + '/' : 'http:/???/') + (parentPerm ? parentPerm+'/': '') +perm;
    elPreview.html(newPerm);
}
// Edit text in basic permalink changed
$('.field_permalink_basic').live('keyup', function(){
    var elContainer = $(this).closest('.field_permalink_container');
    var elActive = elContainer.find('.field_permalink_basic');
    if(elActive.length /*&& elActive.is(':visible')*/) {
        var perm = permalinkGetEditValue(elActive);
        //console.log("edit basic permalink=", perm);
        permalinkPreview(elContainer, perm, '');
        //Update possible derivated permalink setters.
        $('.field_permalink_nested').keyup();
    }
});//.keyup();
// Edit text in nested permalink changed
$('.field_permalink_nested').live('keyup', function(){
    var elContainer = $(this).closest('.field_permalink_container');
    var elActive = elContainer.find('.field_permalink_nested');
    if(elActive.length && elActive.is(':visible')) {
        var perm = permalinkGetEditValue(elActive);
        var parentId = elActive.attr('data-parent-id');
        var parentPerm = permalinkGetParentValue(parentId);
        //console.log("edit nested permalink=" + perm + " parentPermalink=" + parentPerm);
        permalinkPreview(elContainer, perm, parentPerm);
    }
});//.keyup();
$('.field_permalink_use_nested').live('change', function(){
    var elContainer = $(this).closest('.field_permalink_container');
    var checked = (elContainer.find('.field_permalink_use_nested').prop("checked") == true);
    var elBasic = elContainer.find('.field_permalink_basic');
    var elNested = elContainer.find('.field_permalink_nested');
    var elActive, parentPerm='';
    if(checked) {
        elBasic.hide();
        elNested.show();
        elActive = elNested;
        var parentId = elNested.attr('data-parent-id');
        parentPerm = permalinkGetParentValue(parentId);
        //console.log("switch parent permalink=",parentPerm);
    } else {
        elBasic.show();
        elNested.hide();
        elActive = elBasic;
    }
    var perm = permalinkGetEditValue(elActive);
    //console.log("switch permalink=",perm);
    permalinkPreview(elContainer, perm, parentPerm);
});//.change();

// Style selector

$(".cms_open_style_selector").live('click',function() {
    var selector=$(this).closest('.cms_style_selector_container').find(".cms_style_selector").first();
    selector.css('max-height',jQuery(window).height()-80);
    
    var left_margin=($(window).width()-selector.outerWidth())/2;
    $(selector).css('left',left_margin+'px');
    selector.show().animate({ opacity: 1, top: "50px" }, 200);
    $(this).closest('.cms_style_selector_container').find(".cms_style_selector_bg").first().show();
    return false;
});
$(".cms_close_style_selector").live('click',function() {   
    $(this).closest('.cms_style_selector_container').find(".cms_style_selector").animate({ opacity: 0, top: "200px" }, 100,function(){
        $(this).hide();
        $(this).closest('.cms_style_selector_container').find(".cms_style_selector_bg").hide();
    });
    return false;
});

// Imageselect

$(".cms_is_item a").live('click',function() {
    var group=$(this).attr('data-group');
    var val=$(this).attr('data-value');
    var img=$("img",this).attr('src');
    $("#cms_image_selector_"+group+" .cms_is_item_active").removeClass('cms_is_item_active');
    $("#cms_is_item_"+group+"_"+val).addClass('cms_is_item_active');
    $("#cms_image_select_"+group+" .cms_image_selected img").attr('src',img);
    $("#"+group).val(val);
    return false;
});

// Imageoption

$(".cms_image_option_item").live('click',function() {
    var group=$(this).attr('data-group');
    var val=$(this).attr('data-value');
    $("#cms_image_options_"+group+" .cms_image_option_item").removeClass('cms_current_image_option_item');
    $("#cms_image_option_item_"+group+"_"+val).addClass('cms_current_image_option_item');
    $("#cms_image_option_item_"+group+"_"+val+" input").prop("checked", true);
    return false;
});

// Iconselect

$(".cms_icon_item i").live('click',function() {
    var group=$(this).attr('data-group');
    var val=$(this).attr('data-value');
    $("#cms_icon_selector_"+group+" .cms_icon_item_active").removeClass('cms_icon_item_active');
    $("#cms_icon_item_"+group+"_"+val).addClass('cms_icon_item_active');
    $("#cms_icon_select_"+group+" .cms_icon_selected i").removeClass();
    $("#cms_icon_select_"+group+" .cms_icon_selected i").addClass('icon-'+val);
    $("#"+group).val(val);
    return false;
});

$(".cms_icon_select_tabs a").live('click',function() {
    var target=$(this).attr('data-target');
    var container=$(this).closest('.cms_icon_select');
    container.find('.cms_icon_select_tab_active').removeClass('cms_icon_select_tab_active');
    container.find('.cms_icon_select_tab_'+target).addClass('cms_icon_select_tab_active');
    container.find('.cms_icon_select_tabs a').removeClass('active');
    $(this).addClass('active');
    container.find('.cms_icon_select_tab_input').val(target);
    return false;
});


// SVG Iconselect

$(".cms_svg_icon_item").live('click',function() {
    var group=$(this).attr('data-group');
    var val=$(this).attr('data-value');
    var con=$(this).html();
    var width=$(".cms_icon_preview_"+group+" svg").width();
    var color=$(".cms_icon_preview_"+group+" svg path").css("fill");
    $("#cms_icon_selector_"+group+" .cms_icon_item_active").removeClass('cms_icon_item_active');
    $("#cms_icon_item_"+group+"_"+val).addClass('cms_icon_item_active');
    
    $(".cms_icon_preview_"+group+" .cms_icon_background").html(con);
    $(".cms_change_icon_container_"+group).html(con);
    
    $(".cms_icon_preview_"+group+" svg").css("width", width+"px");
    $(".cms_icon_preview_"+group+" svg").css("height", width+"px");
    $(".cms_icon_preview_"+group+" svg path").css("fill", color);
    
    $("#"+group).val(val);
    $("#"+group+"_code").val(con);

    return false;
});

$(".cms_icon_use_background").live('click',function() {
    var container=$(this).closest('.cms_icon_selected_setting');
    $(".cms_icon_background",container).toggleClass('cms_icon_background_hide');
    $(".cms-icon-bg-setting",container).toggle();
});

// Multiple Imageselect
$(".cms_mis_item a").live('click',function() {
    var group=$(this).attr('data-group');
    var val=$(this).attr('data-value');
    var type=$(this).attr('data-type');
    //var img=$("img",this).attr('src');
    $(".cms_mis_item_active").removeClass('cms_mis_item_active');
    $("#cms_mis_item_"+val).addClass('cms_mis_item_active');
    //$("#cms_image_select_"+group+" .cms_image_selected img").attr('src',img);
    $("#"+group+"_item").val(val);
    $("#"+group+"_itemtype").val(type);
    return false;
});
// Button select
$(".cms_is_item_button a").live('click',function() {
    var group=$(this).attr('data-group');
    var val=$(this).attr('data-value');
    var old=$("#"+group).val();
    var container=$(this).closest('.cms_style_selector_container');
    
    if($(this).attr('data-butset')) {
        var setting=eval("new Array(" + $(this).attr('data-butset') + ")");
        
        // font *****************
        $('#'+group+'_font_size').val(setting[0].font_size);
        $('#'+group+'_font_color').minicolors('value',setting[0].font_color);
        $('#'+group+'_font_shadow').val(setting[0].font_shadow);

        var font=setting[0].font_family;
        var selected_font=$('#'+group+'_font_font a[data-font="'+setting[0].font_family+'"]');
        
        $('#'+group+'_font_font input').val(font);

        var weights = eval("new Array(" + selected_font.attr('data-weights') + ")");
        var options='';
        for (i = 0; i < weights.length; ++i) {
            options+='<option value="'+weights[i].id+'">'+weights[i].name+'</option>';
        }
        
        $('#'+group+'_font_weight').html(options);
        $('#'+group+'_font_weight').val(setting[0].font_weight);
        
        if(font=="") font=selected_font.attr('data-text');            
        $('#'+group+'_font_font .font_selected').html(font);

        // set font
        if(setting[0].font_family) $("#cms_image_select_"+group+" .ve_content_button_forchange").css('font-family',setting[0].font_family);
        else $("#cms_image_select_"+group+" .ve_content_button_forchange").css('font-family','inherit'); 
        //$("#cms_image_select_"+group+" .ve_content_button_forchange").css('font-weight',setting[0].font_weight);
        $("#cms_image_select_"+group+" .cms_image_select_container .ve_content_button_forchange").css('font-size',setting[0].font_size+"px");

        if(setting[0].font_shadow=='dark') $("#cms_image_select_"+group+" .ve_content_button_forchange").css('text-shadow','1px 1px 1px rgba(0, 0, 0, 0.5)');
        if(setting[0].font_shadow=='light') $("#cms_image_select_"+group+" .ve_content_button_forchange").css('text-shadow','1px 1px 1px rgba(255, 255, 255, 0.5)');
        if(setting[0].font_shadow=='none') $("#cms_image_select_"+group+" .ve_content_button_forchange").css('text-shadow','0 0 0 rgba(255, 255, 255, 0)');
  
        // background *****************
        $('#'+group+'_background_color1').minicolors('value',setting[0].background_color1);
        $('#'+group+'_background_color2').minicolors('value',setting[0].background_color2);
        
        // padding *****************
        $('#'+group+'_height_padding').val(setting[0].height);       
        $('#'+group+'_height_padding_slider').slider({value: setting[0].height});
        $('#'+group+'_height_padding_val span').html(setting[0].height);
        
        $('#'+group+'_width_padding').val(setting[0].width);       
        $('#'+group+'_width_padding_slider').slider({value: setting[0].width});
        $('#'+group+'_width_padding_val span').html(setting[0].width);
        
        $("#cms_image_select_"+group+" .cms_image_select_container .ve_content_button_forchange").css("padding-top",setting[0].height+"em");
        $("#cms_image_select_"+group+" .cms_image_select_container .ve_content_button_forchange").css("padding-bottom",setting[0].height+"em");
        
        var leftWidth=setting[0].width;
        if($("#cms_image_select_"+group+" .cms_image_select_container .ve_content_button_forchange").hasClass("ve_content_button_icon")) leftWidth=setting[0].width-0.8;
        $("#cms_image_select_"+group+" .cms_image_select_container .ve_content_button_forchange").css("padding-left",leftWidth+"em");
        $("#cms_image_select_"+group+" .cms_image_select_container .ve_content_button_forchange").css("padding-right",setting[0].width+"em");
        
        
        
        //border *****************
        
        $('#'+group+'_border_color').minicolors('value',setting[0].border_color);
        
        //$("#cms_image_select_"+group+" .ve_content_button").css('border-color',setting[0].border_color);
        
        // corner *****************
        $('#'+group+'_corner').val(setting[0].corner);       
        $('#'+group+'_corner_slider').slider({value: setting[0].corner});
        $('#'+group+'_corner_val span').html(setting[0].corner);
        
        $("#cms_image_select_"+group+" .ve_content_button_forchange").css("-moz-border-radius",setting[0].corner+"px");
        $("#cms_image_select_"+group+" .ve_content_button_forchange").css("-webkit-border-radius",setting[0].corner+"px");
        $("#cms_image_select_"+group+" .ve_content_button_forchange").css("-khtml-border-radius",setting[0].corner+"px");
        $("#cms_image_select_"+group+" .ve_content_button_forchange").css("border-radius",setting[0].corner+"px");
        
        // hover *****************
        $('#'+group+'_hover_font_color').minicolors('value',setting[0].hover_font_color);
        $('#'+group+'_hover_color1').minicolors('value',setting[0].hover_color1);
        $('#'+group+'_hover_color2').minicolors('value',setting[0].hover_color2);
        $('#'+group+'_hover_effect').val(setting[0].hover_effect);
        $('#'+group+'_hover_border_color').minicolors('value',setting[0].border_hover_color);

    }
    
    $(".cms_button_setting_optioned", container).hide();      
    var options = eval("new Array(" + $(this).data('set') + ")");
    var op;
    for (i = 0; i < options.length; ++i) {
        op=options[i].substr(0,options[i].indexOf('='));
        $(".cms_bs_"+op,container).show();   
    } 
    $("#cms_image_selector_"+group+" .cms_is_item_active").removeClass('cms_is_item_active');
    $("#cms_is_item_"+group+"_"+val).addClass('cms_is_item_active');
    $(".cms_image_selected .ve_content_button",container).removeClass('ve_content_button_'+old);
    $(".cms_image_selected .ve_content_button",container).addClass('ve_content_button_'+val);
    $("#"+group).val(val);
    return false;
});

// font change
$(".cms_button_setting .button_font_font_select a").live('click',function() {    
    var container=$(this).closest('.cms_button_select_container');
    var font=$(this).attr('data-font');  
    var weights = eval("new Array(" + $(this).data('weights') + ")");
    if($('#button_set_font').length) {
        $('#button_set_font').replaceWith("<link id='button_set_font' href='https://fonts.googleapis.com/css?family="+(font.replace(' ', '+'))+":"+weights[0].id+"&subset=latin,latin-ext' rel='stylesheet' type='text/css'>");  
    }
    else $("head").append("<link id='button_set_font' href='https://fonts.googleapis.com/css?family="+(font.replace(' ', '+'))+":"+weights[0].id+"&subset=latin,latin-ext' rel='stylesheet' type='text/css'>");
    $(".ve_content_button_forchange", container).css('font-family',font);
    $(".ve_content_button_forchange", container).css('font-weight',weights[0].id);
    return false;
});

// button setting change exclude font and corner
$(".cms_button_setting input, .cms_button_setting select").live('change',function() {
   var container=$(this).closest('.cms_button_select_container');
   var id=$(this).attr('id');
   
   if($(this).hasClass( "button_color1" ) || $(this).hasClass( "button_color2" )) {
      var color1=$(".button_color1", container).val();
      var color2=$(".button_color2", container).val();
      
      if(color1 && color2) {          
          $(".ve_content_button_forchange", container).css('background','background: -moz-linear-gradient(top, '+color1+', '+color2+')');
          $(".ve_content_button_forchange", container).css('background','-webkit-gradient(linear, left top, left bottom, from('+color1+'), to('+color2+')');
          $(".ve_content_button_forchange", container).css('filter',"progid:DXImageTransform.Microsoft.gradient(startColorstr='"+color1+"', endColorstr='"+color2+"'");
          $(".ve_content_button_forchange", container).css('background','linear-gradient(to bottom, '+color1+' 0%, '+color2+' 100%)');
      }
      else if(color1) {
        $(".ve_content_button_forchange", container).css('background',color1);
      }
      else if(color2) {
        $(".ve_content_button_forchange", container).css('background',color2);
      }
      else $(".ve_content_button_forchange", container).css('background','transparent');
   }
   
   if($(this).hasClass( "font_weight_select" )) {
      var font=$(".font_selected_input",container).val();
      
      if($('#button_set_font').length) {
        $('#button_set_font').replaceWith("<link id='button_set_font' href='https://fonts.googleapis.com/css?family="+(font.replace(' ', '+'))+":"+$(this).val()+"&subset=latin,latin-ext' rel='stylesheet' type='text/css'>");  
      }
      else $("head").append("<link id='button_set_font' href='https://fonts.googleapis.com/css?family="+(font.replace(' ', '+'))+":"+$(this).val()+"&subset=latin,latin-ext' rel='stylesheet' type='text/css'>");
      
      $(".ve_content_button_forchange", container).css('font-family',font);
      $(".ve_content_button_forchange", container).css('font-weight',$(this).val());
   }
   if($(this).hasClass( "button_font_color" )) {
      $(".ve_content_button_forchange", container).css('color',$(this).val());
   }
   if($(this).hasClass( "button_font_size" )) {
      $(".cms_image_select_container .ve_content_button", container).css('font-size',$(this).val()+"px");
   }
   if($(this).hasClass( "button_font_shadow" )) {
      if($(this).val()=='dark') $(".ve_content_button_forchange", container).css('text-shadow','1px 1px 1px rgba(0, 0, 0, 0.5)');
      if($(this).val()=='light') $(".ve_content_button_forchange", container).css('text-shadow','1px 1px 1px rgba(255, 255, 255, 0.5)');
      if($(this).val()=='none') $(".ve_content_button_forchange", container).css('text-shadow','0 0 0 rgba(255, 255, 255, 0)');
   }
   if($(this).hasClass( "button_border" )) {
      $(".ve_content_button_forchange", container).css('border-color',$(this).val());
   }
});

//Font select 
/*
$(".font_select_container").live('click',function() {
    $(".font_select", this).toggle();
    return false;
});   */
$(".font_select a").live('click',function() {
    var fonttext=$(this).html();
    var font=$(this).attr('data-font');
    
    var weights = eval("new Array(" + $(this).data('weights') + ")");
    var options='';
    for (i = 0; i < weights.length; ++i) {
        options+='<option value="'+weights[i].id+'">'+weights[i].name+'</option>';
    }
    $(this).closest('.cms_font_setting_container').find(".font_weight_select").html(options);
    $(this).closest('.font_select_container').find(".font_selected_input").val(font);
    if(font=="") font=$(this).attr('data-text');
    $(this).closest('.font_select_container').find(".font_selected").html(font);
    return false;
});

//More button
$(".cms_more_setting").live('click',function() {
    var group=$(this).attr('data-group');
    $('.cms_more_group_'+group).toggle();
    $(this).toggleClass('cms_more_setting_l');
    return false;
});

//all element items  ******************************************************************
    
    $(".ve_delete_setting").live("click",function(){    
        if(confirm(MioAdminjs.delete_confirm))
            $(this).closest('.ve_item_container').slideUp('slow', function() {$(this).remove();});
        return false;
    });
    $(".ve_item_head").live("click",function(){    
        $(this).closest('.ve_item_container').find('.ve_item_body').slideToggle();
        return false;
    });
    $(".ve_delete_subitem").live("click",function(){    
        if(confirm(MioAdminjs.delete_confirm))
            $(this).closest('.ve_item_feature_container').slideUp('fast', function() {$(this).remove();});
        return false;
    });
    
    /*
         Single level sortables
         */
        $( ".ve_sortable_items" ).sortable(
            $.extend( {
                handle: '.ve_sortable_handler',
                items: ".ve_sortable_item"
            }, 
            {
            placeholder: "sortable-col-hilelight",
            start: function (event, ui) {
                ui.item.addClass('sortable-col-nob');
                ui.placeholder.height(50);
            },
            stop: function (event, ui) {
                ui.item.removeClass('sortable-col-nob');
            }})
        );
    
    //multielement  ******************************************************************
    // add multielement

    $('.ve_add_multielement').live('click',function(){
        var elemButtonAdd = $(this);
        var id = elemButtonAdd.attr('data-id');
        var tagid = elemButtonAdd.attr('data-tagid');
        var tagname = elemButtonAdd.attr('data-name');
        var settings = elemButtonAdd.attr('data-set');
        // var link=elemButtonAdd;
        var elemContainer = $(this).siblings('div.ve_multielement_container').first();
        elemButtonAdd.attr('data-id',parseInt(id)+1);
        elemContainer.append(
            '<div class="ve_multielement-'+id+' ve_item_container ve_setting_container ve_sortable_item"><div class="miocms_loading"></div></div>'
        );
        $.post(
            ajaxurl,
            {
                "action":"cms_generate_multielement",
                "id": id,
                "tagid": tagid,
                "tagname": tagname,
                "setting": settings
            },
            function(data) {
                elemContainer.find('.ve_multielement-'+id+' .miocms_loading').remove();
                elemContainer.find('.ve_multielement-'+id).html(data);
                $('.cms_color_input').minicolors();
                createSortedItems();
            }
        );
        return false;
    });
    
    // toggle group
    
    $('.mw_toggle_group_head').live("click",function(){          
        $(this).closest('.mw_toggle_group').toggleClass("mw_toggle_group_open");
        var checkBox = $('input', this);
        if(checkBox.length) {
            var isChecked = checkBox.prop("checked");
            checkBox.prop("checked", !isChecked);
            // checkBox.first().checked = !isChecked;
        }
        return false;
    });

});  
