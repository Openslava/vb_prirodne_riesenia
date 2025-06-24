<?php
/**
 * Template for single product.
 */

/** @var $post WP_Post */
global $post, $vePage;

if (MWS()->inMioweb) {
	get_skin_header('mwshop');
	get_blog_sidebar('mwshop');
} else {
	get_header('mwshop');
}

wp_enqueue_script( 've_lightbox_script' );
wp_enqueue_style( 've_lightbox_style' );   
    
$product = MwsProduct::createNew($post);
MWS()->current()->product = $product;
?>
<div class="mws_shop_container mws_single_product_container">
	<div class="mws_shop_content row_fix_width">
    <?php
			mwsRenderParts('product', 'detail');
		?>
    <div class="mws_product_tabs mw_tabs_element_style_3">
        <?php 

			$group='mw_product_'.$post->ID;

			$tabs=array();
			if(MWS()->edit_mode || !empty($vePage->layer)) $tabs['description']=__('Popis','mwshop');
			if(count($product->properties))
				$tabs['properties']=__('Parametry','mwshop');
			if(!$product->hideComments) {
				$tabs['discusion']=__('Diskuse','mwshop');
				if(get_comments_number( $post->ID )) $tabs['discusion'].=' ('.get_comments_number( $post->ID ).')';
			}

			if(!empty($tabs)) {
			?>
			<ul class="mw_tabs mw_tabs_<?php echo $group; ?> title_element_container">
				<?php
				$i=0;
				foreach($tabs as $tab_id=>$tab_name) {
					echo '<li><a href="#mws_product_'.$tab_id.'" data-group="'.$group.'" '.($i==0? 'class="active"':'').'>'.$tab_name.'</a></li>';
					$i++;
				}
				?>
			</ul>

			<ul class="mw_tabs_container <?php echo $group; ?>_container">
				<?php
				if(isset($tabs['description'])) { ?>
					<li id="mws_product_description">
						<?php the_content(); ?>
					</li>

					<?php
				}
				// product properties tab
				if(isset($tabs['properties'])) { ?>
					<li id="mws_product_properties">

						<table class="mws_prodcut_properties_table mw_table mw_table_style_3">
							<?php
							/** @var MwsPropertyValue $propValue */
							foreach($product->properties as $propValue) {
								echo '
									<tr>
											<th>
													'.$propValue->propertyDef->name.'
													'.($propValue->propertyDef->excerpt ? '<span class="mws_property_info">(<a href="" title="'.$propValue->propertyDef->excerpt.'" data-property="'.$propValue->propertyDef->name.'">?</a>)</span>':'').'
											</th>
											<td>'.$propValue->name.' '.$propValue->propertyDef->unit.'</td>
									</tr>';
							}
							?>
						</table>
					</li>
					<?php
				}
				if(isset($tabs['discusion'])) {
					?>

					<li id="mws_product_discusion">
						<?php 
						echo '<div class="element_comment_1 blog_comments">';
						comments_template('/skin/comments.php');   
						echo '</div>'; ?>
					</li>

					<?php
				}
				}
				?>
			</ul>
		</div>
      
      <?php
      if($product->showSimilar) {
          $query=array(); 

          $count=0;  
          if($product->meta['show_type_similar_products']=='custom' && isset($product->meta['similar_products'])) {
              foreach($product->meta['similar_products'] as $sim_product) { 
                  $query[]=get_post($sim_product['product_id']);
                  $count++;
              }
          } else {
              $cats=array();

              $categories=get_the_terms($product->post->ID, MWS_PRODUCT_CAT_SLUG);
              if(!empty($categories)) {
                  foreach($categories as $c) {
                      $cats[]=$c->term_id;    
                  }
                  $args = array ( 
                      'post_type' => MWS_PRODUCT_SLUG,
                      'post__not_in' => array($product->id),
                      'tax_query' => array(
                          array(
                              'taxonomy' => MWS_PRODUCT_CAT_SLUG,
                              'field' => 'term_id',
                              'terms' => $cats,
                          )
                      ), 
                  );

                  $sim_query=new WP_Query( $args );
                  $query=$sim_query->posts;
                  $count=$sim_query->post_count;
              }
          }  
         
          if($count) {
              

              $cols=MWS()->visual_setting['cols'];
              $style=MWS()->visual_setting['product_style'];
              if($vePage->is_mobile || $style==2) $cols=1;
              
              $slider=false; 
              $row_class='';
              $element_class='';
              $carousel_set='';
              
              if($count>$cols) {
                  $slider=true; 
                    
                  wp_enqueue_script( 've_miocarousel_script' );
                  wp_enqueue_style( 've_miocarousel_style' );
                  
                  $element_class.=' miocarousel miocarousel_style_1';

                  $carousel_set.=' data-autoplay="0" data-animation="slide"';
                  $row_class=' slide';
               }
              
                echo '<div class="mws_similar_products_container">'; 
                echo '<h2 class="mws_product_detail_title">'.__('Podobné zboží','mwshop').'</h2>';
                echo '<div class="mws_product_list mws_product_list_style_'.$style.' '.$element_class.'" '.$carousel_set.'>'; 
                  
                if($slider) echo '<div class="miocarousel-inner">'; 
                   
                echo MWS()->writeProducts($query, $cols, $style, $row_class);
                  
                if($slider) {
                      echo '</div>';  //slider end
                      echo '<div class="mc_arrow_container mc_arrow_container-left"><span></span></div>';
                      echo '<div class="mc_arrow_container mc_arrow_container-right"><span></span></div>';
                }
                  
                echo '</div>';
                echo '</div>';
              
          }
      }
      ?>

  </div>
</div>
<?php
if (MWS()->inMioweb) {
	get_skin_footer('mwshop');
} else {
	get_header('mwshop');
}
?>
