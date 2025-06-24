jQuery(document).ready(function($) {
    
    // open eshop activation popup
    $(".mws_eshop_activation").click(function(){  

        openCmsLightbox({title:$(this).attr('title'),prefix:"cms_lightbox_editor", button_text:'Vytvořit eshop', ajax_action : 'mws_save_eshop_activation'});
        
        $.ajax({
            type:'POST',
            data:{"action":"mws_eshop_activation"},
            url: ajaxurl,
            success: function(content) {
               addContentCmsLightbox(content,{prefix:"cms_lightbox_editor"});           
            }
      
        }); 
        return false;
    });
    
    $("#cmsl_submit_mws_save_eshop_activation").live("click",function(){  

        var status = $("#mw_fapi_connection_status").val();
        var url = $("#mw_fapi_connection_url_redirect").val();

        if(status=='1') {
            window.location.href = url;    
        }   
        else  {
            $("#mw_simple_connection_error_container").html('<div class="cms_error_box">Zadejte přihlašovací jméno a API klíč a propojte MioWeb s FAPI.</div>'); 
        }    
        return false;
    });

    $(".mw_save_fapi_connection").live("click",function(){  
        var fapilogin = $("#fapi_login").val();
        var fapipassword = $("#fapi_password").val();
        $("#mw_simple_connection_error_container").html('<div class="miocms_loading"></div>');  
        $.post(ajaxurl, {"action":"fapi_save_simple_connection","fapi_login": fapilogin,"fapi_password":fapipassword}, function(content) {    
            var res = JSON.parse(content);
            if(res.status) {
                $(".mw_fapi_connection_form").html('<div class="cms_confirm_box">'+res.text+'</div>'); 
                $("#mw_fapi_connection_status").val('1');             
            } else {
                $("#mw_simple_connection_error_container").html('<div class="cms_error_box">'+res.text+'</div>'); 
            }
        }); 
        return false;
    });
    
    // Variants
    // *************************************************************************
    
    $('.mws_edit_params_list').live('click',function(){  
        $('.mws_variants_params').show();
        $('.mws_variants_container').hide();
        return false;
    });
    $('.mws_close_params_list').live('click',function(){  
        $('.mws_variants_params').hide();
        $('.mws_variants_container').show();
        return false;
    });
    $('.mws_save_params_list').live('click',function(){ 
    
        var params = new Array();
        var use = false; 
        var paramsToBut;
        
        // disable and hide all properties
        $('.mws_varaints_list .mws_variant_parameter_input').prop('disabled', true);
        $('.mws_varaints_list .mws_variant_parameter_input_container').hide();
        
        
        // find enabled properties in setting
        var i=0;
        $('.mws_variants_param_item input').each(function(){
            if($(this).prop("checked") == true){
                params[i]=$(this).val();
                use=true;
                // enable and show allowed properties
                $('.mws_varaints_list .mws_variant_parameter_input_container_'+$(this).val()+' .mws_variant_parameter_input').prop('disabled', false);
                $('.mws_varaints_list .mws_variant_parameter_input_container_'+$(this).val()).show();
                i++;
            }
        });
        
        if(use) {  
            //alert(var_dump(params));  
            $('.mws_add_variant').attr('data-set',params.join(','));        
            $('.mws_edit_params_list_button').hide();
            $('.mws_varaints_list').show();          
        } else {
            $('.mws_edit_params_list_button').show();
            $('.mws_varaints_list').hide();  
        }
        
        $('.mws_variants_params').hide();
        $('.mws_variants_container').show();
        return false;
    });
    
    
    // add variant item
    $('.mws_add_variant').live('click',function(){    
        var elemButtonAdd = $(this);
        var id = elemButtonAdd.attr('data-id');
        var tagid = elemButtonAdd.attr('data-tagid');
        var tagname = elemButtonAdd.attr('data-name');
        var param = elemButtonAdd.attr('data-set');
        // var link=elemButtonAdd;
        var elemContainer = $(this).siblings('div.ve_multielement_container').first();
        elemButtonAdd.attr('data-id',parseInt(id)+1);
        elemContainer.append(
            '<div class="ve_multielement-'+id+' ve_item_container ve_setting_container ve_sortable_item"><div class="miocms_loading"></div></div>'
        );
        $.post(
            ajaxurl,
            {
                "action":"mws_generate_variant_ajax",
                "id": id,
                "tagid": tagid,
                "tagname": tagname,
                "param": param,
            },
            function(data) {
                elemContainer.find('.ve_multielement-'+id+' .miocms_loading').remove();
                elemContainer.find('.ve_multielement-'+id).html(data);
                createSortedItems();
            }
        );
        return false;
    });

    // Delete variant of a product
    $(".mws_variant_delete").live("click",function(){
        if(confirm(MioAdminjs.delete_confirm))
            $(this).closest('.ve_item_container').slideUp('slow', function() {$(this).remove();});
        return false;
    });

    // Variant - stock_enabled toggle switch
    $('.mws_variant_stock_toggle .mw_toggle_group_head').live("click",function() {
        variantStockUpdateCSS(this);
    });

});

/**
 * Update CSS of a variant definition.
 * @param element MW toggle element for variant
 */
function variantStockUpdateCSS(element) {
    console.log('variantStockUpdate', element);
    var checkBox = jQuery('input', element);
    if(!checkBox.length) {
        return;
    }
    var isChecked = checkBox.prop('checked');
    var dest = jQuery('.mw_toggle_group.mws_variants');
    if(isChecked) {
        dest.removeClass('mws_stock_disabled');
        dest.addClass('mws_stock_enabled');
        // dest.find('.mws_col_stock').removeClass('cms_nodisp');
    } else {
        dest.addClass('mws_stock_disabled');
        dest.removeClass('mws_stock_enabled');
    }
}

