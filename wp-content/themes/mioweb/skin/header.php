<?php 
global $vePage, $cms; ?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1">   
    <title><?php echo $cms->get_cms_title(); ?></title>          
    <link rel="stylesheet" type="text/css" media="all" href="<?php echo get_skin_stylesheet(); ?>" />
    <!--[if lt IE 9]>
    	
      
    <![endif]-->
        
    <?php wp_head(); ?>
    	
</head>
<body <?php body_class(); ?>>
<div id="wrapper">
<?php 
$cms->facebook_script(); 

if(!$vePage->window_editor) {
?>

<header <?php echo (isset($vePage->header_setting['fixed_header']) && $vePage->header_setting['show']!="noheader")?'class="ve_fixed_header"':''; ?>>
<?php 
if($vePage->header_setting['show']!="noheader" && isset($vePage->set_list['headers'][$vePage->header_setting['appearance']])) {

    if(isset($vePage->header_setting['before_header']) && $vePage->header_setting['before_header']) 
        echo $vePage->weditor->create_content($vePage->header_setting['before_header'],'ve_header');
        
    load_template( $vePage->set_list['headers'][$vePage->header_setting['appearance']]['file'], true ); 
} 
?>
</header>
<?php } ?>
