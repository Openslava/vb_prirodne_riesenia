<?php
$temp_layer=array(
	'0'=>array(
		'class'=>'',
		'style'=>array(
			'background_color'=>array(
				'color1'=>'#158ebf',
				'color2'=>'',
				'transparency'=>'100',
			),
			'background_image'=>array(
				'position'=>'center center',
				'repeat'=>'no-repeat',
				'image'=>'',
				'imageid'=>'',
				'pattern'=>'',
			),
			'font'=>array(
				'font-size'=>'',
				'font-family'=>'',
				'weight'=>'',
				'color'=>'#ffffff',
			),
			'link_color'=>'#ffffff',
			'type'=>'basic',
			'padding_top'=>'80',
			'padding_bottom'=>'60',
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
			'min-height'=>'',
			'css_class'=>'',
		),
		'content'=>array(
			'0'=>array(
				'type'=>'col-one',
				'class'=>'',
				'content'=>array(
					'0'=>array(
						'type'=>'title',
						'content'=>'<p style="text-align: center;">CO O NÁS ŘÍKAJÍ KLIENTI</p>',
						'style'=>array(
							'font'=>array(
								'font-size'=>'50',
								'font-family'=>'',
								'weight'=>'',
								'line-height'=>'1.2',
								'color'=>'#ffffff',
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
							'align'=>'center',
						),
						'config'=>array(
							'max_width'=>'',
							'margin_top'=>'0',
							'margin_bottom'=>'5',
							'delay'=>'',
							'animate'=>'',
							'id'=>'',
							'class'=>'',
						),
					),
					'1'=>array(
						'type'=>'text',
						'content'=>'<p style="text-align: center;">Jsme rádi za takto nadšené klienty ...</p>',
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
					),
					'2'=>array(
						'type'=>'like',
						'content'=>array(
							'page'=>'',
							'link'=>'',
						),
						'style'=>array(
							'layout'=>'button_count',
							'scheme'=>'light',
							'setting'=>array(
								'share'=>'share',
							),
							'align'=>'center',
						),
					),
				),
			),
		),
	),
	'1'=>array(
		'class'=>'',
		'style'=>array(
			'background_color'=>array(
				'color1'=>'#ffffff',
				'color2'=>'',
				'transparency'=>'100',
			),
			'background_image'=>array(
				'position'=>'center center',
				'repeat'=>'no-repeat',
				'image'=>'',
				'imageid'=>'',
				'pattern'=>'',
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
			'padding_bottom'=>'60',
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
			'min-height'=>'',
			'css_class'=>'',
		),
		'content'=>array(
			'0'=>array(
				'type'=>'col-one',
				'class'=>'',
				'content'=>array(
					'0'=>array(
						'type'=>'testimonials',
						'content'=>'',
						'style'=>array(
							'testimonials'=>array(
								'0'=>array(
									'text'=>'Byl jsem velmi spokojen.',
									'name'=>'Honza Novák',
									'company'=>'majitel společnosti ABC s.r.o.',
									'image'=>array(
										'image'=>MW_IMAGE_LIBRARY.'misc/face-m.jpg',
										'imageid'=>'99',
									),
								),
								'1'=>array(
									'text'=>'S Davidem Kiršem se mi dobře spolupracovalo. Mohu doporučit :)',
									'name'=>'Petra Svobodová',
									'company'=>'Marketing konzultant',
									'image'=>array(
										'image'=>MW_IMAGE_LIBRARY.'misc/face-w.jpg',
										'imageid'=>'99',
									),
								),
							),
							'cols'=>'two',
							'style'=>'2',
							'font'=>array(
								'font-size'=>'15',
								'font-family'=>'',
								'weight'=>'',
								'line-height'=>'',
								'color'=>'',
							),
							'font-author'=>array(
								'font-size'=>'',
								'font-family'=>'',
								'weight'=>'',
								'color'=>'',
							),
						),
					),
				),
			),
		),
	),
);

$page=array(
  'page'=>array(      
      'title' => __('Reference','cms_ve'),   
      'slug' => __('testimonials','cms_ve'), 
      'theme' => 'page/1/',
  ),
  'setting'=>array (),
  'layer'=>base64_encode(serialize($temp_layer)),
);
