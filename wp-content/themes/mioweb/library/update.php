<?php

//delete_option('_site_transient_update_themes');
add_filter('pre_set_site_transient_update_themes', 'check_for_update');

function check_for_update($checked_data) {     
	global $wp_version;
	
	if (empty($checked_data->checked))
		return $checked_data;
    
  $api_url = UPDATE_SERVER.'/check-update.php';
	$theme_base = basename(dirname(dirname(__FILE__)));
  
  $license=get_transient('cms_license'); 

  if(isset($license['code']) && $license['code']=='success' && !defined('MW_NO_UPDATE_CHECK')) {

    	// Start checking for an update
    	$send_for_check = array(
    		'body' => array(
    			'action' => 'check_update', 
          'license' => $license['license'],
          'version' => $checked_data->checked[$theme_base],
    		)
    	);
    
    	$raw_response = wp_remote_post($api_url, $send_for_check);  
  
      if (!is_wp_error($raw_response) && ($raw_response['response']['code'] == 200))
    		$response = unserialize($raw_response['body']);   

    	// Feed the update data into WP updater
    	if (isset($response['response']) && !empty($response['response']) && !isset($response['extend']))  
    		$checked_data->response[$theme_base] =  (array)$response['response'];
      
      update_option('mioweb_update_info',$response);  
           
	}          
	return $checked_data;
}

$info=get_option('_site_transient_update_themes');
if(isset($info->response[basename(dirname(dirname(__FILE__)))])) {
  add_action('admin_notices','cms_new_version_notification');
}

function cms_new_version_notification() {
    if(defined('MW_NO_UPDATE_CHECK'))
        return;

    wp_enqueue_script('thickbox'); 
    wp_enqueue_style('thickbox');
    
    $theme=basename(dirname(dirname(__FILE__)));
    $url=wp_nonce_url('update.php?action=upgrade-theme&theme='.$theme, 'upgrade-theme_' .$theme, '_wpnonce');
    $info=get_option('_site_transient_update_themes');
    ?>
    <div id="message" class="update-nag">
        <?php printf(__('K dispozici je nová verze <a class=\"thickbox\" href=\"#TB_inline?width=600&height=550&inlineId=cms_changelog\">MioWeb šablony %s</a>.','cms'),$info->response[$theme]['new_version']); ?>
        <?php echo __('Doporučujeme','cms'); ?> <a href="<?php echo $url; ?>"><?php echo __('Provést aktualizaci','cms'); ?></a>.
    </div>
    <?php if(isset($info->response[$theme]['news'])) { ?>
    <div id="cms_changelog" style="display: none;">
        <div><br />
        <?php print_r($info->response[$theme]['news']); ?>
        </div>
    </div>
    <?php 
    }
}
?>
