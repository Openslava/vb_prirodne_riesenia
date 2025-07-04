<?php
$temp_layer=array(
	'0'=>array(
		'class'=>'',
		'style'=>array(
			'background_color'=>array(
				'color1'=>'',
				'color2'=>'',
				'transparency'=>'100',
			),
			'background_setting'=>'image',
			'background_image'=>array(
				'overlay_color'=>'',
				'overlay_transparency'=>'90',
				'position'=>'center center',
				'repeat'=>'no-repeat',
				'image'=>'',
				'imageid'=>'',
				'pattern'=>'',
			),
			'background_delay'=>'3000',
			'background_speed'=>'1500',
			'background_video_mp4'=>'',
			'background_video_webm'=>'',
			'background_video_ogg'=>'',
			'video_setting'=>array(
				'is_saved'=>'1',
			),
			'font'=>array(
				'font-size'=>'',
				'font-family'=>'',
				'weight'=>'',
				'color'=>'',
			),
			'link_color'=>'',
			'type'=>'basic',
			'padding_top'=>'80',
			'padding_bottom'=>'120',
			'padding_left'=>array(
				'size'=>'',
				'unit'=>'px',
			),
			'padding_right'=>array(
				'size'=>'',
				'unit'=>'px',
			),
			'margin_t'=>array(
				'size'=>'',
			),
			'margin_b'=>array(
				'size'=>'',
			),
			'border-top'=>array(
				'size'=>'0',
				'style'=>'solid',
				'color'=>'',
			),
			'border-bottom'=>array(
				'size'=>'0',
				'style'=>'solid',
				'color'=>'',
			),
			'height_setting'=>array(
				'arrow_color'=>'#fff',
			),
			'min-height'=>'',
			'css_class'=>'',
			'delay'=>'',
		),
		'content'=>array(
			'0'=>array(
				'type'=>'col-one',
				'class'=>'',
				'content'=>array(
					'0'=>array(
						'type'=>'image',
						'content'=>'',
						'style'=>array(
							'image'=>array(
								'image'=>MW_IMAGE_LIBRARY.'misc/bgface.jpg',
								'imageid'=>'',
							),
							'click_action'=>'none',
							'alert'=>'',
							'popup'=>'',
							'link'=>array(
								'page'=>'',
								'link'=>'',
							),
							'large_image'=>array(
								'image'=>'',
								'imageid'=>'',
							),
							'align'=>'center',
							'max-width'=>'',
							'label'=>'',
							'style'=>'6',
						),
						'config'=>array(
							'max_width'=>'300',
							'margin_top'=>'0',
							'margin_bottom'=>'35',
							'delay'=>'',
							'animate'=>'',
							'id'=>'',
							'class'=>'',
						),
					),
					'1'=>array(
						'type'=>'title',
						'content'=>'<p style="text-align: center;">'.__('Pavel Blogger','cms_ve').'</p>',
						'style'=>array(
							'font'=>array(
								'font-size'=>'45',
								'font-family'=>'',
								'weight'=>'',
								'line-height'=>'1.2',
								'color'=>'',
								'text-shadow'=>'none',
							),
							'style'=>'1',
							'border'=>array(
								'size'=>'1',
								'color'=>'#d5d5d5',
							),
							'background-color'=>array(
								'color1'=>'#efefef',
								'color2'=>'',
								'transparency'=>'100',
							),
							'decoration-color'=>'#158ebf',
							'align'=>'center',
						),
						'config'=>array(
							'max_width'=>'700',
							'margin_top'=>'0',
							'margin_bottom'=>'60',
							'delay'=>'',
							'animate'=>'',
							'id'=>'',
							'class'=>'',
						),
					),
					'2'=>array(
						'type'=>'text',
						'content'=>'<p style="text-align: center;">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris maximus posuere nibh eu aliquet. Pellentesque pulvinar elementum nibh. Curabitur sed commodo nulla, vel viverra sem. Phasellus et lobortis lectus. Nulla facilisi. Mauris suscipit nunc metus, nec tempor leo cursus vitae. Phasellus consectetur turpis quis tellus vehicula, ut efficitur sapien porttitor. Fusce auctor dolor ac erat elementum, id sollicitudin quam cursus. Nam vel metus ex. Proin eget aliquet nisl, sit amet pellentesque turpis. Fusce tempus neque ac congue malesuada.</p>',
						'style'=>array(
							'font'=>array(
								'font-size'=>'',
								'font-family'=>'',
								'weight'=>'',
								'line-height'=>'',
								'color'=>'',
							),
							'li'=>'',
							'style'=>'1',
							'p-background-color'=>array(
								'color1'=>'#e8e8e8',
								'color2'=>'',
								'transparency'=>'100',
							),
						),
						'config'=>array(
							'max_width'=>'700',
							'margin_top'=>'0',
							'margin_bottom'=>'20',
							'delay'=>'',
							'animate'=>'',
							'id'=>'',
							'class'=>'',
						),
					),
				),
			),
		),
	),
);
$page=array(
  'page'=>array(      
      'title' => __('O mně','cms_ve'),   
      'slug' => __('o-mne','cms_ve'), 
      'theme' => 'page/1/',
  ),
  'setting'=>array (),
  'layer'=>base64_encode(serialize($temp_layer)),
);
