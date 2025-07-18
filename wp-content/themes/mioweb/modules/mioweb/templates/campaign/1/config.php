<?php
$temp_layer=array(
	'0'=>array(
		'class'=>'',
		'style'=>array(
			'font'=>array(
				'font-size'=>'',
				'font-family'=>'',
				'weight'=>'',
				'color'=>'',
			),
			'link_color'=>'',
			'background_color'=>array(
				'color1'=>'#047fbd',
				'color2'=>'',
			),
			'background_image'=>array(
				'position'=>'center center',
				'repeat'=>'no-repeat',
				'image'=>'',
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
			'padding_top'=>'50',
			'padding_bottom'=>'10',
		),
		'content'=>array(
			'0'=>array(
				'type'=>'col-twothree',
				'class'=>'',
				'content'=>array(
					'0'=>array(
						'type'=>'video',
						'content'=>'',
						'style'=>array(
							'response'=>'1',
							'vwidth'=>'',
							'height'=>'',
							'code'=>'',
							'align'=>'center',
						),
					),
					'1'=>array(
						'type'=>'share',
						'content'=>'',
						'style'=>array(
							'show'=>array(
								'facebook'=>'facebook',
								'twitter'=>'twitter',
								'google'=>'google',
							),
							'scheme'=>'1',
						),
					),
				),
			),
			'1'=>array(
				'type'=>'col-three',
				'class'=>'',
				'content'=>array(
					'0'=>array(
						'type'=>'mioweb_nav',
						'content'=>'',
						'style'=>array(
							'campaign'=>'0',
							'style'=>'2',
							'font'=>array(
								'font-size'=>'19',
								'font-family'=>'',
								'weight'=>'',
								'color'=>'#c7e0ed',
							),
							'color-active'=>'#ffffff',
						),
					),
				),
			),
		),
	),
	'1'=>array(
		'class'=>'',
		'style'=>array(
			'font'=>array(
				'font-size'=>'',
				'font-family'=>'',
				'weight'=>'',
				'color'=>'',
			),
			'link_color'=>'',
			'background_color'=>array(
				'color1'=>'#ffffff',
				'color2'=>'',
			),
			'background_image'=>array(
				'position'=>'center center',
				'repeat'=>'no-repeat',
				'image'=>'',
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
			'padding_top'=>'40',
			'padding_bottom'=>'40',
		),
		'content'=>array(
			'0'=>array(
				'type'=>'col-twothree',
				'class'=>'',
				'content'=>array(
					'0'=>array(
						'type'=>'text',
						'content'=>'<p>In sit amet dui nec orci pretium dictum. Vivamus vulputate adipiscing sapien, et dapibus dolor aliquet quis. Curabitur mauris ipsum, ornare eget neque quis, consequat tincidunt dui. In elit neque, luctus sit amet luctus vitae, sollicitudin quis magna. Morbi sit amet risus eros. Vestibulum pellentesque, sem et dignissim dapibus, erat tellus congue lectus, id tempor tortor lectus eget arcu. Pellentesque egestas facilisis sem, id placerat tortor. Sed lacinia blandit nisl. Curabitur eu lectus pellentesque, dignissim purus a, porta nulla. Curabitur lacinia venenatis neque non ornare. Aliquam a tellus libero.</p>',
						'style'=>array(
							'font'=>array(
								'font-size'=>'',
								'font-family'=>'',
								'weight'=>'',
								'color'=>'',
							),
							'li'=>'0',
						),
						'config'=>array(
							'max_width'=>'',
							'margin_top'=>'0',
							'margin_bottom'=>'40',
							'delay'=>'',
							'class'=>'',
						),
					),
					'1'=>array(
						'type'=>'title',
						'content'=>__('Přidejte se do diskuze','cms_mioweb'),
						'style'=>array(
							'font'=>array(
								'font-size'=>'30',
								'font-family'=>'',
								'weight'=>'',
								'color'=>'',
								'text-shadow'=>'none',
							),
						),
						'config'=>array(
							'max_width'=>'',
							'margin_top'=>'0',
							'margin_bottom'=>'40',
							'delay'=>'',
							'class'=>'',
						),
					),
					'2'=>array(
						'type'=>'fcomments',
						'content'=>'',
						'style'=>array(
							'width'=>'630',
							'per_page'=>'10',
							'scheme'=>'light',
						),
					),
					'3'=>array(
						'type'=>'wpcomments',
						'content'=>'',
						'style'=>array(
							'style'=>'1',
						),
					),
				),
			),
			'1'=>array(
				'type'=>'col-three',
				'class'=>'',
				'content'=>array(
					'0'=>array(
						'type'=>'likebox',
						'content'=>'https://www.facebook.com/mioweb.cz',
						'style'=>array(
							'height'=>'800',
							'scheme'=>'light',
							'setting'=>array(
								'faces'=>'faces',
							),
						),
					),
				),
			),
		),
	),
);
$config['layer']=base64_encode(serialize($temp_layer));
$config['setting']=array ();
$config['config']=array();