<?php

function ve_element_pay_button($element, $css_id) {
    global $vePage;   
    wp_enqueue_script('shop_front_script');
    wp_enqueue_style('mwsShop');
    wp_enqueue_script( 've_lightbox_script' );
    wp_enqueue_style( 've_lightbox_style' );

    $content='';
    
    if($element['style']['product_id']) {
    
        if($vePage->edit_mode && !$element['style']['product_id']) $content.='<div class="cms_error_box admin_feature">'.__('Není vybrán žádný produkt pro vložení do košíku.','mwshop').'</div>';
         
        if(isset($element['config']['max_width'])) {
            $element['style']['button']['width']=$element['config']['max_width'];    
            $element['style']['button']['max-width']=$element['config']['max_width']; 
        }
    
    		$product = MwsProduct::getById($element['style']['product_id']);
    		$isVariantRoot = $product->structure === MwsProductStructureType::Variants;
        $count = (isset($element['style']['count_default']) ? $element['style']['count_default'] : 1);
    		$status = $product->getAvailabilityStatus($count);
        $but_set=array(
            'style'=>$element['style']['button'],
            'show'=>isset($element['style']['show'])? $element['style']['show']:'',
            'popup'=>isset($element['style']['popup'])? $element['style']['popup']:'',
            'link'=>'#',
            'text'=>(isset($element['content']) && !empty($element['content']) ? $element['content'] : esc_html($product->getBuyButtonText())),
            'align'=>$element['style']['align'],
        );
        $isQuick = isset($element['style']['kind']) && ($element['style']['kind'] == 'quick' || $element['style']['kind'] == 'quick_with_cart');
        $canQuickAddToCart = isset($element['style']['kind']) && $element['style']['kind'] == 'quick_with_cart';
    		if($product && $product->canBuy_Count($count)) {
    			// buying possible
    
    			$variantList = '';
    			if($isVariantRoot) {
    				$variantPricesAreEqual = $product->variantPricesAreEqual;
    				$varProduct = MwsProductRoot::getById($product->id);
            $variantList .= '<div class="mws_variant_list_container">';
    				$variantList .= '	<div class="mws_variant_list_content"'
    					. ' data-all-availability-css="'.esc_attr(implode(' ', MwsProductAvailabilityStatus::getAllCSSArray())).'"'
    					.'>';
              
            $variantList .= '<div class="mws_add_to_cart_header mws_variant_list_header">Vybrat variantu pro <strong>'.$product->post->post_title.'</strong>
    								<a href="#" class="mws_close_cart_box">' . file_get_contents(MWS()->getTemplateFileDir("img/icons/close.svg"), true) . '</a>
    						</div>';
                
    				/** @var MwsProductVariant $variant */ /*
    				foreach ($varProduct->variants as $variant) {
    					$count = 1;
    					$availability = $variant->getAvailabilityStatus($count);
    					$css = $variant->getAvailabilityCSS($availability);
    					$variantList .= '	<a href="#" class="shop-variant-select '.$css.'"'
    						. ($variant->canBuy() ? ' data-product="'.$variant->id.'"' : '')
    						. ' data-msg-buy-button="'.esc_attr(esc_html($variant->getBuyButtonText($availability))).'"'
    						. ' data-msg-availability="'.esc_attr($variant->htmlAvailabilityMessage($availability)).'"'
    						. ' data-availability-css="'.esc_attr($css).'"'
    						. ' data-msg-price="'.esc_attr($variant->htmlPriceSaleFull()).'"'
    						. ' data-msg-sale="'.esc_attr($variant->htmlPriceSaleFull(null,$count,array('vatExcluded','vatIncluded','salePrice'))).'"'
    						. '>'
    						. '	<span class="mws_product_title_variant">'.esc_html($variant->name).'</span>'
    						. ($variantPricesAreEqual
    							? ''
    							: ' <div class="mws_product_price">'.$variant->htmlPriceSaleFull(null,1,array('vatExcluded')).'</div>'
    						)
    						. ' ' . $variant->htmlAvailabilityMessage($availability)
    						. ' '
    						. '	</a>';
    				}   */
            foreach ($varProduct->variants as $variant) {
        			$count = 1;
        			$availability = $variant->getAvailabilityStatus($count);
        			$css = $variant->getAvailabilityCSS($availability);
        			$variantList .= '<a href="#" class="shop-variant-select shop-action '.$css.'"'
        				. ($variant->canBuy() ? ' data-product="'.$variant->id.'"' : '')
                . ' data-operation="mws_cart_add"'
                . ' data-count="1"'
                . ($isQuick ? ' data-isQuick="1"' : '')
    				    . ($canQuickAddToCart ? ' data-canQuickAddToCart="1"' : '')
                . ' data-backurl="'.$_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"].'"'
                /*
        				. ' data-msg-buy-button="'.esc_attr(esc_html($variant->getBuyButtonText($availability))).'"'
        				. ' data-msg-availability="'.esc_attr($variant->htmlAvailabilityMessage($availability)).'"'
        				. ' data-availability-css="'.esc_attr($css).'"'
        				. ' data-msg-price="'.esc_attr($variant->htmlPriceSaleFull()).'"'
        				. ' data-msg-sale="'.esc_attr($variant->htmlPriceSaleFull(null,$count,array('vatExcluded','vatIncluded','salePrice'))).'"'
                */
        				. '>';
    
                $variantList .= '<table class="mws_variant_list_item">'
                	.'<tr>'
                		.'<td class="mws_variant_list_item_thumb">'
                        .get_the_post_thumbnail($product->id,MWS()->thumb_name.'5')
                    .'</td>'
                		.'<td class="mws_variant_list_info">';
        							/** @var MwsPropertyValue $variant_value */
        							foreach($variant->variantVals as $variant_value) {
                          $variantList .= '<div class="mw_variant_info">';
                          $variantList .= '<span class="mw_variant_info_name">'.$variant_value->propertyDef->name.'</span>';
                          $variantList .= '<span class="mw_variant_info_value">'.$variant_value->name.'</span>';
                          $variantList .= '</div>';   
                      }
                	  $variantList .= '</td>'
                    .'<td class="mws_variant_list_price">'
                      .'<div class="mws_product_price">'.$variant->htmlPriceSaleFull(null,1,array('vatExcluded')).'</div>'
        				      .$variant->htmlAvailabilityMessage($availability)
                	  .'</td>'
                  .'</tr>'
                .'</table>'
                .'<span class="ve_but_icon"></span>';
    
        				$variantList .= '</a>';
        		}
    				$variantList .= '	</div>';
            $variantList .= '	</div>';
    			}
    
    			$content .= ($isVariantRoot ? '<div class="mws_add_to_cart_part">' : '');
    			$content .= $vePage->create_button($but_set, $css_id . ' .ve_content_button',
    				'shop-action '. $product->getAvailabilityCSS($status)
    				. ($isVariantRoot ? ' mws_dropdown_button' : '')
    				,
    				''
    				. 'data-operation="mws_cart_add"'
    				. ' ' . ($isVariantRoot ? 'data-variant-product' : 'data-product') . '="'.$product->id.'"'
    				. ' data-count="' . $count . '"'
    				. ($isQuick ? ' data-isQuick="1"' : '')
    				. ($canQuickAddToCart ? ' data-canQuickAddToCart="1"' : '')
    				. ' data-backurl="' . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] . '"');
    
    			$content .= $variantList;
    			$content .= ($isVariantRoot ? '</div>' : '');
    		} else {
    			// no buy
    			$but_set['text'] = $product->getBuyButtonText($status);
    			$content .= $vePage->create_button($but_set, $css_id . ' .ve_content_button', 'shop-action '. $product->getAvailabilityCSS($status), ''
    				. 'data-operation="mws_cart_add"'
    				. ' ' . ($isVariantRoot ? 'data-variant-product' : 'data-product') . '="'.$product->id.'"'
    				. ' data-product="' . $element['style']['product_id'] . '"'
    				. ' data-count="' . $count . '"'
    				. ($isQuick ? ' data-isQuick="1"' : '')
    				. ($canQuickAddToCart ? ' data-canQuickAddToCart="1"' : '')
    				. ' data-backurl="' . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] . '"');
    		}
        $content.='<div class="cms_clear"></div>';
    } else if($vePage->edit_mode) $content='<div class="cms_error_box admin_feature">'.__('Není vybrán žádný produkt. Vyberte produkt, který chcete prodávat nebo element smažte.','mwshop').'</div>';
    
    return $content;
}

function ve_element_product_list($element, $css_id, $post_id, $edit_mode, $added) {
    global $vePage;  
    
    wp_enqueue_script('shop_front_script');
    wp_enqueue_style('mwsShop');
     
    $content='';
    //print_r($element['style']);
    
    $orderby=isset($element['style']['order'])? $element['style']['order'] : 'date';
    
    $args = array ( 'post_type' => MWS_PRODUCT_SLUG, 'posts_per_page' => -1, 'orderby' => $orderby );
    
    if($orderby=='bestseller') {
      $args['orderby'] = 'meta_value_num';
      $args['meta_key'] = 'ordered_count';
      $args['order'] = 'DESC';
    }
    else if($orderby=='menu_order') {
      $args['order'] = 'ASC';
    }
    else if($orderby=='title') {
      $args['order'] = 'ASC';
    }
        
    $content.=$vePage->print_styles_array(array(        
        array(
            'styles'=>array('font'=>array('color'=>$element['style']['font']['color'])),
            'element'=>$css_id." .in_element_content, ".$css_id." .title_element_container, ".$css_id." .mws_price_sale_vatincluded",
        ),
        array(
            'styles'=>isset($element['config']['max_width'])? array('max-width'=>$element['config']['max_width']) : '',
            'element'=>$css_id.' .in_element_content',
        ),
        array(
            'styles'=>(isset($element['style']['background']) && $element['style']['background'])? array('background-color'=>$element['style']['background']) : '',
            'element'=>$css_id.' .mws_product',
        ),
    ));

    
    if($element['style']['show']=='custom') {
        $query=array();  
        if(!empty($element['style']['custom_products'])) {    
            foreach($element['style']['custom_products'] as $product) 
                $query[]=get_post($product['product_id']);
        }  
    }
    else if($element['style']['show']=='category') {
        $args['tax_query'] = array(
            array(
                'taxonomy' => MWS_PRODUCT_CAT_SLUG,
                'field' => 'term_id',
                'terms' => $element['style']['category'],
            )
        );
        $wp_query=new WP_Query( $args );
        $query=$wp_query->posts;
    }
    else if($element['style']['show']=='bestsellers') {
        $args['orderby'] = 'meta_value_num';
        $args['meta_key'] = 'ordered_count';
        $args['order'] = 'DESC';
        $args['own_per_page'] = 1;
        $args['posts_per_page'] = ($element['style']['bestsellers_count'])? $element['style']['bestsellers_count'] : '3';
        
        $wp_query=new WP_Query( $args );
        
        $query=$wp_query->posts;
    }
    else {
        $wp_query=new WP_Query( $args );
        $query=$wp_query->posts;
    }

    if(count($query)) {
        $cols=$element['style']['cols'];
        $style=$element['style']['product_style'];
        
        
        if(isset($element['style']['use_slider'])) {
            wp_enqueue_script( 've_miocarousel_script' );
            wp_enqueue_style( 've_miocarousel_style' );
            if($vePage->is_mobile) $element['style']['cols']=1;
        } 
        $element_class='in_element_content mws_product_list mws_product_list_style_'.$style;
        $carousel_set='';
        if(isset($element['style']['use_slider'])) {
            $element_class.=' miocarousel miocarousel_style_1';
            if($element['style']['color_scheme']) $element_class.=' miocarousel_'.$element['style']['color_scheme'];
            if(isset($element['style']['off_autoplay'])) $carousel_set.=' data-autoplay="0"';
            if($element['style']['delay']) $carousel_set.=' data-duration="'.$element['style']['delay'].'"';
            if($element['style']['speed']) $carousel_set.=' data-speed="'.$element['style']['speed'].'"';
            if($element['style']['animation'] && $element['style']['animation']!='fade') $carousel_set.=' data-animation="'.$element['style']['animation'].'"';
        }
        
        $content.='<div class="'.$element_class.'" '.$carousel_set.'>';
        if(isset($element['style']['use_slider']))
                $content .= '<div class="miocarousel-inner">';
              
        if(isset($element['style']['use_slider'])) {
            $row_class=' slide';
        } else {
            $row_class='mw_list_row mw_list_row_c'.$cols;
        }
        
        if (isset($element['style']['background']) && $element['style']['background']) {
            $row_class.=' mws_product_list_wbg';
        }
      
        $content.=MWS()->writeProducts($query, $cols, $style, $row_class, $element['style']);
        
        if(isset($element['style']['use_slider'])) {
            $content .= '</div>';  //slider end
            $content .= '<div class="mc_arrow_container mc_arrow_container-left"><span></span></div>';
            $content .= '<div class="mc_arrow_container mc_arrow_container-right"><span></span></div>';
            if($added) {
            $content .= "";
            }
        }
        
        $content.='</div>';
    } else if($vePage->edit_mode) {
        if($element['style']['show']=='bestsellers') $content.='<div class="cms_error_box admin_feature">'.__('Zatím nebyl prodán žádný produkt, proto nelze vypsat nejprodávanější produkty.','mwshop').'</div>';
        else $content.='<div class="cms_error_box admin_feature">'.__('Výpis produktů je prázdný.','mwshop').'</div>';
    }
    return $content;
}

function ve_element_eshop_category_list($element, $css_id, $post_id, $edit_mode, $added) {
    global $vePage;  
    
    wp_enqueue_script('shop_front_script');
    wp_enqueue_style('mwsShop');
     
    $content='';
    //print_r($element['style']);
    
    $content.=$vePage->print_styles_array(array(        
        array(
            'styles'=>array('font'=>$element['style']['font']),
            'element'=>$css_id." .mw_element_item_title, ".$css_id." .mws_category_item",
        ),
    ));   
    
    $args = array ( 'taxonomy' => MWS_PRODUCT_CAT_SLUG, 'hide_empty'=>0, 'parent'=>0 );
    
    if($element['style']['show']=='sub') {
        if($element['style']['category_parent']) $args['parent'] = $element['style']['category_parent'];
    }

    $categories=get_categories( $args );   

    if($element['style']['style']=='v1') {
      $content.='<div class="in_element_content mw_vertical_menu mw_vertical_menu_center">'.MWS()->getShopCategories('title_element_container', 0, $categories).'</div>';
    } else {
        
        if(count($categories)) {
            $cols=(isset($element['style']['cols']) && $element['style']['cols'])? $element['style']['cols'] : 3;
            $style=$element['style']['style'];

            $content.='<div class="in_element_content mw_element_items mw_element_items_style_'.$style.'">';
            
            $rows = array_chunk( $categories, $cols );
      
            $i=1;
            foreach( $rows as $row ){
                $content.='<div class="mw_element_row '.(($i==count($rows))?'mw_element_row_last':'').'">';
                foreach ($row as $cat) {
                
                    $cat_meta = get_option( "mws_eshop_category_fields_".$cat->term_id);   
                
                    $args=array(
                        'style'=>$element['style']['style'],
                        'cols'=>$cols,
                        'link'=>get_term_link($cat->term_id,MWS_PRODUCT_CAT_SLUG),
                        'imageid'=>$cat_meta['category_image']['imageid'],
                        'thumb'=>'mio_columns_'.$cols,
                        'title'=>$cat->name,
                        'edit_button'=>$vePage->edit_button($cat->term_id, get_edit_term_link($cat->term_id,MWS_PRODUCT_CAT_SLUG))
                    );
                
                    $content.=$vePage->generate_element_item($args);

                }
                $content.='<div class="cms_clear"></div></div>';
                $i++;
            }
    
            //$content.=MWS()->writeProducts($query, $cols, $style, $row_class);
    
            $content.='</div>';
        } else if($vePage->edit_mode) {
            $content.='<div class="cms_error_box admin_feature">'.__('Výběru neodpovídá žádná kategorie.','mwshop').'</div>';
        }
        
    }
    return $content;
}

function ve_element_product_detail($element, $css_id, $post_id, $edit_mode, $added, $row_set) {
    global $vePage, $product;   
    
    wp_enqueue_style('mwsShop');
    wp_enqueue_script('shop_front_script');

    $content='';
    if($element['style']['product_id']) {
        $productId=(int)($element['style']['product_id']);
        $post=get_post($productId);
        $product = MwsProduct::createNew($post);

        MWS()->current()->product = $product;

        $content.=mwsRenderParts('product', 'detail', true);
        
        $gallery=get_post_meta($productId,'product_gallery',true);
        
        if($added && $gallery && isset($gallery['gallery'])) {
            $content .= "<script>
            jQuery(function() {
                function imageLoaded() {
                   counter--; 
                   if( counter === 0 ) {
                        jQuery('".$css_id." .miocarousel').MioCarousel({});
                   }
                }
                var images = jQuery('".$css_id." img');
                var counter = images.length; 

                images.each(function() {
                    if( this.complete ) {
                        imageLoaded.call( this );
                    } else {
                        jQuery(this).one('load', imageLoaded);
                    }
                });
            });
            </script>";
        }
        
    } else if($vePage->edit_mode) {
			$content.='<div class="cms_error_box admin_feature">'.__('Pro výpis detailu musíte vybrat produkt.','mwshop').'</div>';
		}
    return $content;
}

function ve_element_product_price($element, $css_id) {
    global $vePage;   
    wp_enqueue_script('shop_front_script');
    wp_enqueue_style('mwsShop');
    
    $content='';
    
    if($element['style']['product_id']) {
        if(isset($element['config']['max_width'])) {
    
        }  
        $content.=$vePage->print_styles_array(array(        
            array(
                'styles'=>array('font'=>$element['style']['font']),
                'element'=>$css_id." .in_element_content, ".$css_id." .mws_price_vatincluded",
            ),
        ));
        
        $hide=array('salePercentage');
        if(isset($element['style']['hide']['salePrice'])) $hide[]='salePrice';
        if(isset($element['style']['hide']['vatExcluded'])) $hide[]='vatExcluded';
        
        $product = MwsProduct::getById(isset($element['style']['product_id']) ? $element['style']['product_id'] : 0);
				if($product) {
					MWS()->current()->product = $product;

					$content .= '<div class="mws_product_price in_element_content in_element_product_price">' . $product->htmlPriceSaleFull(null, 1, $hide) . '</div>';
				} else {
					$content .= '<div class="mws_product_price in_element_content in_element_product_price">' . __('(zvolte produkt)', 'mwshop') . '</div>';
				}
        
    } else if($vePage->edit_mode) $content.='<div class="cms_error_box admin_feature">'.__('Není vybrán žádný produkt pro vypsání ceny.','mwshop').'</div>';   
    return $content;
}
