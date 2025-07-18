<?php
$web_setting=array(
've_header' => array(
		'show'=>'page',
		'logo_setting'=>'text',
		'logo'=>'',
		'logo_text'=>__('Designérka','cms_ve'),
		'logo_font'=>array(
			'font-size'=>'23',
			'font-family'=>'Enriqueta',
			'weight'=>'700',
			'color'=>'#111111',
		),
		'menu'=>$installed_menus['main'],
		'background_color'=>array(
			'color1'=>'',
			'color2'=>'',
			'transparency'=>'100',
		),
		'appearance'=>'type1',
		'menu_font'=>array(
			'font-size'=>'18',
			'font-family'=>'',
			'weight'=>'',
			'color'=>'#575757',
		),
		'menu_active_color'=>'#c90a30',
		'menu_submenu_text_color'=>'#ffffff',
		'menu_bg'=>array(
			'color1'=>'#121212',
			'color2'=>'',
		),
		'before_header'=>'',
		'background_image'=>array(
			'overlay_color'=>'#158ebf',
			'overlay_transparency'=>'80',
			'position'=>'center center',
			'repeat'=>'no-repeat',
			'image'=>'',
			'imageid'=>'',
		),
		'header_width'=>array(
			'size'=>'',
			'unit'=>'px',
		),
		'header_padding'=>'30',
		'background_color_fix'=>array(
			'color1'=>'',
			'color2'=>'',
			'transparency'=>'100',
		),
		'header_padding_fix'=>array(
			'size'=>'',
		),
	),
        
've_footer' => array(
		'show'=>'page',
		'custom_footer'=>'',
		'appearance'=>'type1',
		'text'=>'',
		'menu'=>'',
		'background_color'=>array(
			'color1'=>'',
			'color2'=>'',
			'transparency'=>'100',
		),
		'background_image'=>array(
			'overlay_color'=>'#158ebf',
			'overlay_transparency'=>'80',
			'position'=>'center center',
			'repeat'=>'no-repeat',
			'image'=>'',
			'imageid'=>'',
		),
		'font'=>array(
			'font-size'=>'15',
			'font-family'=>'',
			'weight'=>'',
			'color'=>'#7a7a7a',
		),
		'footer_width'=>array(
			'size'=>'',
			'unit'=>'px',
		),
	),
  
've_appearance' => array(
		'background_color'=>'#ffffff',
		'background_setting'=>'image',
		'background_image'=>array(
			'overlay_color'=>'#158ebf',
			'overlay_transparency'=>'80',
			'position'=>'center top',
			'repeat'=>'no-repeat',
			'image'=>MW_IMAGE_LIBRARY.'bg/background.jpg',
			'imageid'=>'1783',
			'pattern'=>'',
		),
		'background_video_mp4'=>'',
		'background_video_webm'=>'',
		'background_video_ogg'=>'',
		'video_setting'=>array(
			'is_saved'=>'1',
		),
		'title_font'=>array(
			'font-family'=>'Enriqueta',
			'weight'=>'400',
			'color'=>'',
		),
		'font'=>array(
			'font-size'=>'17',
			'font-family'=>'Open Sans',
			'weight'=>'400',
			'line-height'=>'',
			'color'=>'',
		),
		'link_color'=>'',
		'h1_font'=>array(
			'font-size'=>'',
			'color'=>'',
		),
		'h2_font'=>array(
			'font-size'=>'',
			'color'=>'',
		),
		'h3_font'=>array(
			'font-size'=>'',
			'color'=>'',
		),
		'h4_font'=>array(
			'font-size'=>'',
			'color'=>'',
		),
		'h5_font'=>array(
			'font-size'=>'',
			'color'=>'',
		),
		'h6_font'=>array(
			'font-size'=>'',
			'color'=>'',
		),
		'li'=>'',
		'page_width'=>array(
			'size'=>'',
			'unit'=>'px',
		),
	),
  
'blog_sidebars' => array(
          	'sidebar_blog'=>$installed_sidebars['main'],
          	'sidebar_category'=>$installed_sidebars['main'],
          	'sidebar_post'=>$installed_sidebars['main'],
          	'sidebar_author'=>$installed_sidebars['main'],
          	'sidebar_tag'=>$installed_sidebars['main'],
          	'sidebar_search'=>$installed_sidebars['main'],
),    
'blog_appearance' => array(
	'appearance'=>'style1',
	'structure'=>'right',
	'post_look'=>'1',
	'background_color'=>'#ffffff',
	'background_image'=>array(
		'overlay_color'=>'#158ebf',
		'overlay_transparency'=>'80',
		'position'=>'center center',
		'repeat'=>'no-repeat',
		'image'=>'',
		'imageid'=>'',
		'pattern'=>'',
	),
	'title_font'=>array(
		'font-family'=>'Enriqueta',
		'weight'=>'700',
		'color'=>'',
	),
	'font'=>array(
		'font-size'=>'16',
		'font-family'=>'Open Sans',
		'weight'=>'400',
		'line-height'=>'',
		'color'=>'#111111',
	),
	'link_color'=>'#c90a30',
	'tb_background'=>array(
		'color1'=>'#c90a30',
		'color2'=>'',
	),
	'tb_font'=>array(
		'font-size'=>'25',
		'font-family'=>'',
		'weight'=>'',
		'line-height'=>'',
		'color'=>'#ffffff',
	),
	'article_font'=>array(
		'font-size'=>'35',
		'font-family'=>'',
		'weight'=>'',
		'line-height'=>'',
		'color'=>'',
	),
	'article_font_text'=>array(
		'font-size'=>'',
		'font-family'=>'',
		'weight'=>'',
		'line-height'=>'',
		'color'=>'',
	),
	'sidebar_font'=>array(
		'font-size'=>'20',
		'font-family'=>'',
		'weight'=>'',
		'line-height'=>'',
		'color'=>'',
	),
	'h1_font'=>array(
		'font-size'=>'30',
		'color'=>'',
	),
	'h2_font'=>array(
		'font-size'=>'23',
		'color'=>'',
	),
	'h3_font'=>array(
		'font-size'=>'18',
		'color'=>'',
	),
	'h4_font'=>array(
		'font-size'=>'14',
		'color'=>'',
	),
	'h5_font'=>array(
		'font-size'=>'14',
		'color'=>'',
	),
	'h6_font'=>array(
		'font-size'=>'14',
		'color'=>'',
	),
	'li'=>'1',
),

'blog_footer' => array(
	'show'=>'global'
),
'blog_header' => array(
	'show'=>'global',
)
);
