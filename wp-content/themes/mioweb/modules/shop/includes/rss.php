<?php


class MwsRss {

	public static function addCustomRss() {
			add_feed('heureka', array('MwsRss', 'heureka_rss_feed'));
			add_feed('zbozi', array('MwsRss', 'zbozi_rss_feed'));
	}
	
	public static function getProducts() {
			$args = array ( 'post_type' => MWS_PRODUCT_SLUG, 'posts_per_page' => -1 );
			$wp_query=new WP_Query( $args );
			return $wp_query->posts;
	}
	
	public static function getProductArray($product, $isVariant=false) {
		
		$status = $product->getAvailabilityStatus();
		$canBuy = $product->canBuy($status);
		if($canBuy) {
			
				$imgurl=$product->getThumbnailUrl('large');
				
				$product_array=array(
						'id'=>$product->id,
						'name'=>$product->name,
						'product'=>(($isVariant)? $product->product->name : $product->name),
						'excerpt'=> (($isVariant)? $product->product->post->post_excerpt : $product->post->post_excerpt),
						'url'=>$product->detailUrl,
						'img'=>$imgurl[0], // test if no image
						'price'=>$product->price->priceVatIncluded,
						'vat'=>$product->price->getVatPercentage(),
				);
				
				if($isVariant) {
						$product_array['variant_group']=$product->product->id;
				}
				
				$id_for_taxonomy=($isVariant)? $product->product->id : $product->id;
				$taxonomy_terms = get_the_terms( $id_for_taxonomy, MWS_PRODUCT_CAT_SLUG );
				if(isset($taxonomy_terms[0])) {
						$product_array['category']=$taxonomy_terms[0]->name;
				}
				
				// product gallery
				if($product->gallery && isset($product->gallery['gallery'])) {
						foreach($product->gallery['gallery'] as $gal_image) {
								$target = wp_get_attachment_image_src( $gal_image, 'large' );                      
								$product_array['gallery'][]=$target[0];
						}
				}
				
				// product properties
				if(count($product->properties)) {
						$product_array['param']=$product->properties;
				}
				
				// availability
				$product_array['available'] = ($status)? true : false;
				
				return $product_array;
				
		} else return array();
			
	}
	
	public static function heureka_rss_feed() {
		
		$query=MwsRss::getProducts();
		
		header('Content-Type: '.feed_content_type('rss-http').'; charset='.get_option('blog_charset'), true);
		echo '<?xml version="1.0" encoding="'.get_option('blog_charset').'"?'.'>';

		echo '<SHOP>';

		if(count($query)) { 
				
				foreach ($query as $post) {
						
						$product = MwsProduct::createNew($post);
						
						MWS()->current()->product = $product;
						
						$isVariantRoot = $product->structure === MwsProductStructureType::Variants;
						
						if($isVariantRoot) {
								$varProduct = MwsProductRoot::getById($product->id);
								foreach ($product->variants as $variant) {
										$product_array = MwsRss::getProductArray($variant,true);
										if(!empty($product_array)) MwsRss::printHeurekaItem($product_array);
								}
							
						}
						else {
								$product_array = MwsRss::getProductArray($product);
								if(!empty($product_array)) MwsRss::printHeurekaItem($product_array);
						}

					} 
		}
		echo '</SHOP>';
	}
	
	public static function printHeurekaItem($product) {
			echo '<SHOPITEM>';
			echo '<ITEM_ID>'.$product['id'].'</ITEM_ID>';
			echo '<PRODUCTNAME>'.$product['name'].'</PRODUCTNAME>';
			echo '<PRODUCT>'.$product['product'].'</PRODUCT>';
			echo '<DESCRIPTION>'.strip_tags($product['excerpt']).'</DESCRIPTION>';
			echo '<URL>'.$product['url'].'</URL>';
			echo '<IMGURL>'.$product['img'].'</IMGURL>';
			
			if(isset($product['gallery'])) {
					foreach($product['gallery'] as $gal_image) {                
							echo '<IMGURL_ALTERNATIVE>'.$gal_image.'</IMGURL_ALTERNATIVE>';
					}
			}
			
			if(isset($product['category']))
					echo '<CATEGORYTEXT>'.$product['category'].'</CATEGORYTEXT>';

			echo '<PRICE_VAT>'.$product['price'].'</PRICE_VAT>';
			echo '<VAT>'.$product['vat'].'%</VAT>';

			if(isset($product['param']) && count($product['param'])) {
					
					foreach($product['param'] as $propValue) {
						echo '<PARAM>';
						echo '<PARAM_NAME>'.$propValue->propertyDef->name.'</PARAM_NAME>';
						echo '<VAL>'.$propValue->name.' '.$propValue->propertyDef->unit.'</VAL>';
						echo '</PARAM>';
					}
					
			}

			if($product['available']) echo '<DELIVERY_DATE>0</DELIVERY_DATE>';

			
			if(isset($product['variant_group'])) echo '<ITEMGROUP_ID>'.$product['variant_group'].'</ITEMGROUP_ID>';
			echo '</SHOPITEM>';
	}
	
	
	public static function zbozi_rss_feed() {
		
		$query=MwsRss::getProducts();
		
		header('Content-Type: '.feed_content_type('rss-http').'; charset='.get_option('blog_charset'), true);
		echo '<?xml version="1.0" encoding="'.get_option('blog_charset').'"?'.'>';

		echo '<SHOP xmlns="http://www.zbozi.cz/ns/offer/1.0">';

		if(count($query)) { 
				
				foreach ($query as $post) {
						
						$product = MwsProduct::createNew($post);
						
						MWS()->current()->product = $product;
						
						$isVariantRoot = $product->structure === MwsProductStructureType::Variants;
						
						if($isVariantRoot) {
								$varProduct = MwsProductRoot::getById($product->id);
								foreach ($product->variants as $variant) {
										$product_array = MwsRss::getProductArray($variant,true);
										if(!empty($product_array)) MwsRss::printZboziItem($product_array);
								}
							
						}
						else {
								$product_array = MwsRss::getProductArray($product);
								if(!empty($product_array)) MwsRss::printZboziItem($product_array);
						}

					} 
		}
		echo '</SHOP>';
	}
	public static function printZboziItem($product) {
			echo '<SHOPITEM>';
			echo '<ITEM_ID>'.$product['id'].'</ITEM_ID>';
			echo '<PRODUCTNAME>'.$product['name'].'</PRODUCTNAME>';
			echo '<PRODUCT>'.$product['product'].'</PRODUCT>';
			echo '<DESCRIPTION>'.strip_tags($product['excerpt']).'</DESCRIPTION>';
			echo '<URL>'.$product['url'].'</URL>';
			echo '<IMGURL>'.$product['img'].'</IMGURL>';
			
			if(isset($product['gallery'])) {
					foreach($product['gallery'] as $gal_image) {                
							echo '<IMGURL>'.$gal_image.'</IMGURL>';
					}
			}
			
			echo '<PRICE_VAT>'.$product['price'].'</PRICE_VAT>';

			if(isset($product['param']) && count($product['param'])) {
					foreach($product['param'] as $propValue) {
						echo '<PARAM>';
						echo '<PARAM_NAME>'.$propValue->propertyDef->name.'</PARAM_NAME>';
						echo '<VAL>'.$propValue->name.' '.$propValue->propertyDef->unit.'</VAL>';
						echo '</PARAM>';
					}	
			}

			if($product['available']) echo '<DELIVERY_DATE>0</DELIVERY_DATE>';
			else echo '<DELIVERY_DATE>-1</DELIVERY_DATE>';

			
			if(isset($product['variant_group'])) echo '<ITEMGROUP_ID>'.$product['variant_group'].'</ITEMGROUP_ID>';
			echo '</SHOPITEM>';
	}

}
