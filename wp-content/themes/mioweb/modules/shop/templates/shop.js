jQuery(document).ready(function($) {

    /**
     * AJAX shop handler.
     */
    $(".shop-action").live("click", function () {
        
        var but = $(this);
        var action = $(this).attr('data-operation');
        var product = $(this).attr('data-product');
        var variantProduct = $(this).attr('data-variant-product');
        var count = $(this).attr('data-count');
        if (count < 1) {
            count = 1;
        }
        var isQuick = $(this).attr('data-isQuick');
        if(typeof isQuick === typeof undefined) {
            isQuick = false;
        } else {
            isQuick = (isQuick == 1);
        }
        var canQuickAddToCart = $(this).attr('data-canQuickAddToCart');
        if(typeof canQuickAddToCart === typeof undefined) {
            canQuickAddToCart = false;
        } else {
            canQuickAddToCart = (canQuickAddToCart == 1);
        }
        if(!product) {
          if(variantProduct) {
              // Variant product, no specific variation selected.
              //$(this).closest('.mws_add_to_cart_part').find('.mws_dropdown').addClass('mws_dropdown_opened');
              //alert($(this).closest('.mws_add_to_cart_part').find('.mws_dropdown').html());

              $.colorbox({
                  html: $(this).closest('.mws_add_to_cart_part').find('.mws_variant_list_container').html(),
                  maxWidth: '95%',
                  width: '550px',
                  className: 'mws_select_variant_lightbox',
                  onComplete: function () {
                      $(this).colorbox.resize()
                  }
              });
          } else {
              //Variant can not be bought
              return false;
          }
        } else {

            $('span.ve_but_icon',but).addClass('working');

            switch(action) {
                case 'mws_cart_add':
                    $.ajax({
                        type: 'POST',
                        data: {
                            "action": action,
                            "product": product,
                            "count": count,
                            "isQuick": isQuick,
                            "canQuickAddToCart": canQuickAddToCart,
                        },
                        url: ajaxurl,
                        success: function (content) {
                            var res = JSON.parse(content);
                            $.colorbox({
                                html: res.content,
                                maxWidth: '95%',
                                width: '550px',
                                className: 'mws_add_to_cart_lightbox',
                                onComplete: function () {
                                    $(this).colorbox.resize()
                                }
                            });
                            if (!isQuick) {
                                $('.mws_cart_items_count').text(res.cart_count);
                                if (res.added) {
                                    if ($('#mw_header_cart tr#mws_product_id-' + res.added).length)
                                        $('#mw_header_cart tr#mws_product_id-' + res.added).replaceWith(res.added_hover);
                                    else
                                        $('#mw_header_cart table').append(res.added_hover);
                                    $('#mw_header_cart .mws_header_empty').hide();
                                    $('#mw_header_cart .mws_header_cart_footer').show();
                                }
                            }
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            console.log('FAIL');
                            console.log('xhr=', jqXHR);
                            console.log('textStatus=', textStatus);
                            console.log('error=', errorThrown);
                            alert(window.textError_AjaxError);
                        },
                        complete: function (jqXHR, textStatus) {
                            $('span.ve_but_icon', but).removeClass('working');
                            var clearAfterBuy = but.attr('data-clearafterbuy');
                            if(clearAfterBuy) {
                                but.removeAttr('data-clearafterbuy');
                                but.removeAttr('data-product');
                            }
                        }
                    });
                    break;
            }
        }

        return false;
    });
    
    
    $(".mws_close_cart_box").live("click", function () {
        $.colorbox.close();
        return false;
    });

    /**
     * Click on remove button in the cart removes the line.
     */

    $(".shop-action-remove").live("click", function (event) {
        //TODO animate pending progress of line, disable UI

        var button=$(this);
        var action = button.attr('data-operation');
        var product = button.attr('data-product');
        var item=".mws_product_id-" + product;

        $(item).addClass('working');
        button.addClass('cms_loading');
        var hrefBkp = button.attr('href');
        button.removeAttr('href');
        
        $.ajax({
            type: 'POST',
            data: {
                "action": action,
                "product": product,
            },
            url: ajaxurl,
            success: function (content) {
                var res = JSON.parse(content);
                if(res.result==true) {
                    //Delete line from cart icon
                    $('#mw_header_cart tr#mws_product_item_'+res.productId).remove();
                    //Update cart icon count
                    $('.mws_cart_items_count').text(res.cart_count);
                    //Cart is empty?
                    if(!res.cart_count) {
                        $('.mws_shop_order_content .mws_cart').addClass('mws_cart_empty');
                        $('.mws_header_cart_hover').addClass('mws_header_cart_hover_empty');
                         
                        $('.mws_cart_continue_but').remove();
                        //window.location.href = res.cart_url;  
                    }
                    //Delete line from the cart with animation
                    //$(item).slideUp('slow', function() {$(item).remove();});
                    if(typeof res.newCart !== 'undefined') {
                        $('.mws_cart_container').replaceWith(res.newCart);
                    } else
                        $(item).remove();
                } else {
                    $.colorbox({html:res.message,className:'mws_add_to_cart_lightbox'});
                }
            },
            complete: function(jqXHR, textStatus) {
                button.removeClass('cms_loading');
                button.attr('href', hrefBkp);
            }
        });
        return false;
    });

    /** Cart count modifications  */

    $('.mws_count_reload').live("click", function(event) {
        var action = 'mws_order_step';
        var formdata = $("#mws_order_form").serialize();
        var nextUrl = '';
        var bkpUrl = $(this).attr('href');
        var srcElem = $(this);
        //console.log(formdata);

        srcElem.addClass('cms_loading');
        srcElem.removeAttr('href');

        var performUrl = ajaxurl;
        var isReloading = false;

        var fncAjaxSuccess =
            function(content, textStatus, jqXHR) {
                //console.log('DONE');
                //console.log('content=', content);
                var formElems = $("#mws_order_form");

                //Clear all errors
                console.log('clearing errors');
                formElems.find('input').removeClass('mw_input_error');
                formElems.find('.mw_input_error_text').remove();

                if(content.success==true) {
                    //Force implicit recount on fire reload
                    isReloading = true;
                    window.location.reload();
                } else {
                    // Remove product that are not in the cart any more
                    $.each(content.data.deleteProductIds, function(index, value) {
                        console.log("removing product: " + value);
                        var toDelete = formElems.find('tr.mws_product_id-' + value);
                        toDelete.remove();
                    });
                    // Add error CSS + error instruction to invalid inputs
                    console.log('validation errors');
                    var errors = content.data.errors;
                    var topMin = -1;
                    $.each(errors, function(index, value) {
                        console.log("error item: " + index + " = " + value);
                        var filter = ":enabled[name='" + index + "']:first";
                        //console.log("filter = " + filter);
                        var elem=formElems.find(filter);
                        elem.addClass('mw_input_error');
                        elem.parent().after('<span class="mw_input_error_text">'+value+'</span>');
                        var top=elem.first().offset().top;
                        //console.log('elem error: ' + top);
                        if((topMin == -1) || (top<topMin)) {
                            topMin = top;
                        }
                        // Clear data
                        var row = elem.parents('tr.mws_cart_item');
                        var cols = row.find(".mws_cart_item_availability,.mws_cart_item_price");
                        cols.empty();
                    });
                    // Scroll to first error
                    //console.log('topMin='+topMin);
                    if(topMin > -1) {
                        $('html, body').stop().animate({
                            'scrollTop': topMin - 50
                        }, 500, 'swing');
                    }
                }
            };

        var fncAjaxFail =
            function(jqXHR, textStatus, errorThrown) {
                console.log('FAIL');
                console.log('xhr=',jqXHR);
                console.log('textStatus=',textStatus);
                console.log('error=',errorThrown);
                alert(window.textError_AjaxError);
            };

        var fncAjaxComplete =
            function(jqXHR, textStatus) {
                //console.log('COMPLETE');
                if(isReloading) {
                    //special drawing when reloading page?
                    console.log('...RELOADING');
                } else {
                    srcElem.attr('href', bkpUrl);
                    srcElem.removeClass('cms_loading');
                }
            };

        $.ajax({
            type: 'POST',
            data: {
                "action": action,
                "form": formdata,
                "nextUrl": nextUrl,
                "curStep": window.orderStep,
                "subaction": 'recount',
            },
            dataType: "json",
            timeout: 50000,
            url: performUrl,
            success: fncAjaxSuccess,
            error: fncAjaxFail,
            complete: fncAjaxComplete,
        });

        //console.log('EXIT');
        return event.preventDefault();
    });

    /** Order step submit */
    $(".mws_cart_continue_but").live("click", function(event) {
        var action = 'mws_order_step';
        var formdata = $("#mws_order_form").serialize();
        var nextUrl = $(this).attr('href');
        var bkpUrl = nextUrl;
        var srcElem = $(this);
        //console.log(formdata);

        srcElem.addClass('working');
        srcElem.attr('href', '');

        var performUrl = ajaxurl;
        var isReloading = false;

        var rq = $.ajax({
            type: 'POST',
            data: {
                "action": action,
                "form": formdata,
                "nextUrl": nextUrl,
                "curStep": window.orderStep,
            },
            dataType: "json",
            timeout: 50000,
            url: performUrl,
            success:
                function(content) {
                    //console.log(content);
                    var formElems = $("#mws_order_form");

                    var topMin = -1;

                    //Flash message
                    var elFlashMsgs = $("#mws_flash_messages");
                    elFlashMsgs.html('');
                    if (typeof content.data.flashMessage !== "undefined") {
                        if ($.isArray(content.data.flashMessage)) {
                            $.each(content.data.flashMessage, function (index, value) {
                                elFlashMsgs.append(value);
                            });
                        } else {
                            elFlashMsgs.html(content.data.flashMessage);
                        }
                        var top = elFlashMsgs.first().offset().top;
                        if ((topMin == -1) || (top < topMin)) {
                            topMin = top;
                        }
                    }

                    if(content.success==true) {
                        var data = content.data;
                        if (typeof data.nextUrl !== "undefined") {
                            console.log("redirecting to " + data.nextUrl);
                            nextUrl = data.nextUrl;
                            isReloading = true;
                            // similar behavior as an HTTP redirect
                            window.location.replace(nextUrl);
                        }
                    } else {
                        //Clear all errors
                        if(content.data.deleteErrors) {
                            console.log('clearing errors');
                            formElems.find('.mw_input_error').removeClass('mw_input_error');
                            formElems.find('.mw_input_error_text').remove();
                        }
                        //Add error CSS + error instruction to invalid inputs
                        console.log('errors are present');
                        if (typeof content.data.shouldReload !== "undefined") {
                            console.log('should reload --> reloading page');
                            isReloading = true;
                            window.location.reload();
                        } else {
                            // Remove product that are not in the cart any more
                            $.each(content.data.deleteProductIds, function(index, value) {
                                console.log("removing product: " + value);
                                var toDelete = formElems.find('tr.mws_product_id-' + value);
                                toDelete.remove();
                            });
                            var errors = content.data.errors;
                            $.each(errors, function (index, value) {
                                console.log("error item: " + index + " = " + value);
                                // Cart structure
                                var filter = ":enabled[name='" + index + "']:first";
                                //console.log("filter = " + filter);
                                var elem = formElems.find(filter);
                                elem.addClass('mw_input_error');
                                elem.parent().append('<span class="mw_input_error_text">'+value+'</span>');
                                if(elem.first().length > 0) {
                                    var top = elem.first().offset().top;
                                    //console.log('elem error: ' + top);
                                    if ((topMin == -1) || (top < topMin)) {
                                        topMin = top;
                                    }
                                }
                                // Clear data in cart
                                var row = elem.parents('tr.mws_cart_item');
                                var cols = row.find(".mws_cart_item_availability,.mws_cart_item_price");
                                cols.empty();

                                // Summarize structure
                                filter = ".mws_product_id-" + index + " td.mws_cart_item_title span";
                                elem = formElems.find(filter);
                                elem.first().after('<span class="mw_input_error_text">'+value+'</span>');
                            });
                            // Scroll to first error
                            //console.log('topMin='+topMin);
                            if (topMin > -1) {
                                $('html, body').stop().animate({
                                    'scrollTop': topMin - 50
                                }, 500, 'swing');
                            }
                        }
                    }
                },
            error:
                function(jqXHR, textStatus) {
                    alert(window.textError_AjaxError);
                },
            complete:
                function(jqXHR, textStatus) {
                    //console.log('COMPLETE');
                    if(isReloading) {
                        //special drawing when reloading page?
                        console.log('...RELOADING');
                    } else {
                        srcElem.attr('href', bkpUrl);
                        srcElem.removeClass('working');
                    }
                }
        });

        return event.preventDefault();
    });
    
    $('.mws_property_info a').click(function(){
    
        var text=$(this).attr('title');
        var title=$(this).attr('data-property');
        $.colorbox({html:'<div class="mw_colorbox_text_info"><h2>'+title+'</h2>'+text+'</div>', maxWidth:'95%', width:'550px'});
    
        return event.preventDefault(); 
    });

    /* ****** QUICK BUY - jQuery hooks ******** */
    var quickBuyInProgress = false;
    $(".mws_cart_quickbuy").live("click", function(event) {
        var button = $(this);
        var res = validateAndQuickBuy(button, productId, count, price, "#" + formId, nonce, isShippingRequired);
        return false;
    });

    $("select[name=\'mws_shipping\']").live("change", function () {
        var elem = $(this);
        var payId = elem.attr("value");
        elem = elem.find("option[value='"+payId+"']");
        var checked = elem.attr("checked");
        var isCodEnabled = elem.hasClass("mws_cod_enabled");
        var isPersonal = elem.hasClass("mws_personal_pickup");
        var dbgInfo = ((isCodEnabled) ? " isCod" : "") + ((isPersonal) ? " isPersonalPickup" : "");
        console.log("switched to " + payId + " " + (dbgInfo.length > 0 ? "options:"+dbgInfo : ""));
        //Update correct method
        setDisabledPayType_quick(codPayType, !isCodEnabled);
        updatePrice_quick();
    }).change();

    $("select[name='mws_payment']").live("change", function () {
        updatePrice_quick();
    });

    /* ********************* Toggle target ******************** */
    $(".mw_toggle_container_quick").live("click", function(event){
        var tar = $(this).attr('data-target');
        $('#'+tar).toggle();
        $.colorbox.resize();
    });

    /* ***************** Variant dropdown list ************** */

    $(".shop-variant-select").live("click", function () {
        var elem = $(this);
        var productId = elem.attr('data-product');
        var availabilityHtml = elem.attr('data-msg-availability');
        var availabilityCss = elem.attr('data-availability-css');
        var priceHtml = elem.attr('data-msg-price');
        var saleHtml = elem.attr('data-msg-sale');
        var buttonHtml = elem.attr('data-msg-buy-button');
        var titleHtml = elem.find('.mws_product_title_variant').html();
        var canBuy = !!productId;

        var elemContent=elem.closest('.mws_dropdown_content');
        var allAvailabilityCSS = elemContent.attr('data-all-availability-css');

        console.log('variant selected', productId, canBuy);
        // console.log('availability:', availabilityCss, availabilityHtml);
        // console.log('texts: ', buttonHtml, priceHtml, saleHtml);
        
        var elemBuySection = elem.closest('.mws_add_to_cart_part,.pay_button_element_container');
        var elemBuyBtn = elemBuySection.find('.shop-action');
        if(elemBuyBtn.length) {
            if(canBuy) {
                elemBuyBtn.attr('data-product', productId);
                elemBuyBtn.attr('data-clearafterbuy', true);
                elemBuyBtn.click();
                // var txt = elemBuyBtn.text();
                // console.log('text=', txt);
            } else {
                elemBuyBtn.removeAttr('data-product');
            }
            // elemBuyBtn.find('span.ve_but_text').text(buttonHtml);
        }
        // var elemProduct = elem.closest('.mws_product');
        // if(elemProduct.length) {
            // elemProduct.removeClass(allAvailabilityCSS).addClass(availabilityCss);
            // elemProduct.find('.mws_product_availability').replaceWith(availabilityHtml);
            // elemProduct.find('.mws_product_title').html(titleHtml);
            // elemProduct.find('.mws_product_price').html(priceHtml);
            // elemProduct.find('.mws_product_sale').html(saleHtml);
        // }
        return false;
    });

    checkForMobileCategoryMenu();
});

jQuery(window).load(function() {

    var height=0;
    var excerpt;
    jQuery('.mws_product_list_style_1 .mw_list_row').each(function(){
        height=0;
        var hcont=jQuery(this);
        jQuery('.mws_product', jQuery(this)).each(function(){
            if(jQuery(this).height()>height) height=jQuery(this).height(); 
        });
        jQuery('.mws_product', jQuery(this)).each(function(){
          if(jQuery('.mws_product_excerpt', jQuery(this)).length) {
              excerpt=jQuery('.mws_product_excerpt', jQuery(this));
          } else {
              excerpt=jQuery('.mws_product_title', jQuery(this));
          }
          excerpt.height(excerpt.height()+height-jQuery(this).height());  
        });
    });
});

function checkForMobileCategoryMenu() {
    var available_width = 0,
        all_items_width = 0;

    for( var i = 0, length = jQuery('.mws_category_menu_list li').length; i < length; i++ ) {
        all_items_width = all_items_width + jQuery('.mws_category_menu_list li').eq( i ).outerWidth( true );
    }

    available_width = jQuery('.mws_category_list').width();

    if( available_width <= all_items_width ) {
        jQuery('.mws_top_panel .mws_category_menu_list').hide();
        jQuery('.mws_top_panel .mws_category_menu_select_container').show();
    }
}

/* ****** QUICK BUY ******** */

$ = jQuery;

function setDisabledPayType_quick(payType, disabled) {
    var elemSelect = $("select[name='mws_payment']");
    var selectInvalid = false;
    elemSelect.find("option").prop("disabled", false);
    if(disabled) {
        var elem = $("option[value=" + payType + "]");
        console.log("disabling", elem);
        elem.prop("disabled", disabled);
        if(elemSelect.attr("value") == payType) {
            selectInvalid = true;
            elemSelect.val("0").change();
        }
    }
}

function updatePrice_quick() {
    console.log("updating price");
    var selected = null;
    var shipId = 0;
    var payType = "";
    var isAllowedPayType = true;
    selected = $("select[name='mws_shipping']");
    if(selected.length > 0) {
        shipId = selected.val();
    }
    selected = $("select[name='mws_payment']");
    if(selected.length > 0) {
        isAllowedPayType = !selected.prop("disabled");
        if(isAllowedPayType) {
            payType = selected.val();
        }
    }
    console.log("ship&pay: " + shipId + " & " + payType);

    // if(typeof isShippingRequired === 'undefined') {
    //     For case event is fired too early.
        // var isShippingRequired = false;
    // }
    console.log('shipping', isShippingRequired);

    var newPriceVal = 0;
    var newPriceText = '';
    if (!isAllowedPayType) {
        newPriceVal = 0;
        newPriceText = '';//text_InvalidPayType;
    } else if(isShippingRequired) {
        if (shipId != 0 && payType != "") {
            //Both settings are present and are valid (COD is checked when getting value)
            newPriceVal = prices[shipId].price.priceVatIncluded;
            if (payType == codPayType) {
                newPriceVal += prices[shipId].codPrice.priceVatIncluded;
            }
            if (newPriceVal != 0) {
                newPriceText = "&nbsp;+&nbsp" + newPriceVal;// + " " + priceUnit;
            }
        } else {
            newPriceVal = 0;//text_makeSelectionPayment;
        }
    }
    if(newPriceVal===0) {
        newPriceText = '';//text_zeroPrice;
    }

    // Update totalPrice
    cartTotalPrice = price.priceVatIncluded * count + newPriceVal;
    console.log('new cartcartTotalPrice', cartTotalPrice);

    // Update UI
    var elemPrice = $("#mws_quick_order .mws_price_vatincluded");
    if (elemPrice.length != 1) {
        return;
    }
    var elemPriceShipping = elemPrice.find("span.num span.shipping");
    if(elemPriceShipping.length == 0) {
        elemPrice.find("span.num").append("<span class='shipping'></span>");
        elemPriceShipping = elemPrice.find("span.num span.shipping");
    }
    elemPriceShipping.html(newPriceText);

    //Update visibility of descriptions
//			$(".mws_shipping_radio .mws_shipping_description").hide();
//			$(".mws_payment_radio .mws_payment_description").hide();
//			$(".mws_shipping_description_"+shipId).show();
//			$(".mws_payment_description_"+payType).show();
}


/**
 * Function to proceed with quick buy. It validates quick order against server and if successful then it creates order.
 * If validation fails for the input fields, then it adds validation errors for fields.
 * @param button jQuery button that fired the event
 * @param productId ID of the product to buy
 * @param count Number of items to buy. Defaults to 1.
 * @param price JSONized {@link MwsPrice::asJson()} object, price of a single item.
 * @param formId ID of the HTML element to get input elements. Should start with "#".
 * @param nonce Wordpress nonce string.
 * @param isShippingRequired If order requires shipping.
 */
function validateAndQuickBuy(button, productId, count, price, formId, nonce, isShippingRequired) {
    if (button.hasClass("working")) {
        return false;
    }

    button.addClass("working");
    console.log("working");

    var $ = jQuery;
    var formElems = $(formId);
    var formdata = formElems.serialize();
    var isReloading = false;

    var rq = $.ajax({
        type: 'POST',
        data: {
            "action": 'mws_quick_buy',
            "nonce": nonce,
            "form": formdata,
            "productId": productId,
            "count": count,
            "price": price,
            "isShippingRequired": isShippingRequired,

            //"nextUrl": nextUrl,
            //"curStep": window.orderStep,
        },
        dataType: "json",
        timeout: 50000,
        url: ajaxurl,
        success:
            function(content) {
                console.log('content=',content);

                //Clear all errors
                console.log('clearing errors');
                formElems.find('.mw_input_error').removeClass('mw_input_error');
                formElems.find('.mw_input_error_text').remove();

                var topMin = -1;

                //Flash message
                var flash = $("#mws_flash_messages");
                if (typeof content.data.flashMessage !== "undefined") {
                    flash.html(content.data.flashMessage);
                    var top = flash.first().offset().top;
                    if ((topMin === -1) || (top < topMin)) {
                        topMin = top;
                    }
                } else {
                    flash.html('');
                }

                if(content.success==true) {
                    var data = content.data;
                    if (typeof data.nextUrl !== "undefined") {
                        console.log("redirecting to " + data.nextUrl);
                        nextUrl = data.nextUrl;
                        isReloading = true;
                        // similar behavior as an HTTP redirect
                        window.location.replace(nextUrl);
                    }
                } else {
                    //Add error CSS + error instruction to invalid inputs
                    console.log('errors are present');
                    var errors = content.data.errors;
                    $.each(errors, function (index, value) {
                        console.log("error item: " + index + " = " + value);
                        var filter = ":enabled[name='" + index + "']:first, label[for='" + index + "']:first";
                        //console.log("filter = " + filter);
                        var elem = formElems.find(filter);
                        if (elem.length) {
                            elem = elem.first();
                            // Use label if associated for checkboxes
                            if (elem.is(':checkbox')) {
                                var label = $('label[for="' + elem.attr('id') + '"]');
                                if (label.length) {
                                    elem = label.first();
                                }
                            }
                            elem.addClass('mw_input_error');
                            elem.after('<span class="mw_input_error_text">' + value + '</span>');
                            var top = elem.offset().top;
                            //console.log('elem error: ' + top);
                            if ((topMin === -1) || (top < topMin)) {
                                topMin = top;
                            }
                        } else {
                            elFlashMsgs.html('<span class="mw_input_error_text">' + value + '</span>');
                        }

                        /*
                                                elem
                                                    .addClass('mw_input_error')
                                                    .after('<span class="mw_input_error_text">' + value + '</span>');
                                                var top = elem.first().offset().top;
                                                //console.log('elem error: ' + top);
                                                if ((topMin == -1) || (top < topMin)) {
                                                    topMin = top;
                                                }
                        */
                    });
                    // Scroll to first error
                    //console.log('topMin='+topMin);
                    if (topMin > -1) {
                        //$('html, body').stop().animate({
                        //    'scrollTop': topMin - 50
                        //}, 500, 'swing');
                    }
                }
            },
        error:
            function(jqXHR, textStatus, errorThrown) {
                alert(window.textError_AjaxError + "\n\n(" + errorThrown + ')');
            },
        complete:
            function(jqXHR, textStatus) {
                //console.log('COMPLETE');
                if(isReloading) {
                    //special drawing when reloading page?
                    console.log('...RELOADING');
                } else {
                    button.removeClass('working');
                    $.colorbox.resize();
                }
            }
    });
}

function mwsSynchronizeGateway(button, gatewayId, nonce) {
    if (button.hasClass("working")) {
        return false;
    }

    button.addClass("working");
    console.log("working");

    var $ = jQuery;
    var isReloading = false;

    var formId = button.data('formid');

    var rq = $.ajax({
        type: 'POST',
        data: {
            "action": 'mws_gate_sync',
            "nonce": nonce,
            "gatewayId": gatewayId,
            "formId": formId,
        },
        dataType: "json",
        timeout: 120000,
        url: ajaxurl,
        success:
            function (content) {
                console.log('content=', content);

                if (content.success === true) {
                    var data = content.data;
                    if (typeof data.html === "undefined" || typeof data.id === "undefined") {
                        // Missing updated HTML data.
                        console.log("missing new HTML data or HTML id");
                        alert('Došlo k chybě při zpracování. Opakujte poslední akci.');
                    } else {
                        $(data.id).html(data.html);
                    }
                } else {
                    //Add error CSS + error instruction to invalid inputs
                    console.log('errors are present');
                    var errors = content.data.errors;
                    $.each(errors, function (index, value) {
                        console.log("error item: " + index + " = " + value);
                    });
                    // Scroll to first error
                    //console.log('topMin='+topMin);
                    if (topMin > -1) {
                        //$('html, body').stop().animate({
                        //    'scrollTop': topMin - 50
                        //}, 500, 'swing');
                    }
                }
            },
        error:
            function (jqXHR, textStatus) {
                alert(window.textError_AjaxError);
            },
        complete:
            function (jqXHR, textStatus) {
                //console.log('COMPLETE');
                if (isReloading) {
                    //special drawing when reloading page?
                    console.log('...RELOADING');
                } else {
                    button.removeClass('working');
                    // $.colorbox.resize();
                }
            }
    });

}

