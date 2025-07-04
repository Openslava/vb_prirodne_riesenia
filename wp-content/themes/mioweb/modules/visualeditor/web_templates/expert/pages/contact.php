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
			'background_image'=>array(
				'position'=>'center bottom',
				'repeat'=>'no-repeat',
				'image'=>$color_set['background_image'],
				'pattern'=>'0',
			),
			'font'=>array(
				'font-size'=>'',
				'font-family'=>'',
				'weight'=>'',
				'color'=>$color_set['bg_text'],
			),
			'link_color'=>'',
			'type'=>'basic',
			'padding_top'=>'50',
			'padding_bottom'=>'90',
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
		),
		'content'=>array(
			'0'=>array(
				'type'=>'col-one',
				'class'=>'',
				'content'=>array(
					'0'=>array(
						'type'=>'title',
						'content'=>'<h1 style="text-align: center;">'.__('Kontaktujte nás','cms_ve').'</h1>',
						'style'=>array(
							'font'=>array(
								'font-size'=>'55',
								'font-family'=>'',
								'weight'=>'',
								'line-height'=>'',
								'color'=>'#ffffff',
								'text-shadow'=>'none',
							),
							'style'=>'1',
							'border'=>array(
								'size'=>'0',
								'color'=>'',
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
							'margin_bottom'=>'15',
							'delay'=>'',
							'animate'=>'',
							'id'=>'',
							'class'=>'',
						),
					),
					'1'=>array(
						'type'=>'text',
						'content'=>'<p style="text-align: center;">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi eget <br />arcu id lacus laoreet porttitor ut nec velit. Duis ut ante vestibulum, <br />varius neque nec, mollis eros.</p>',
						'style'=>array(
							'font'=>array(
								'font-size'=>'18',
								'font-family'=>'',
								'weight'=>'',
								'line-height'=>'',
								'color'=>'',
							),
							'li'=>'',
						),
						'config'=>array(
							'max_width'=>'800',
							'margin_top'=>'0',
							'margin_bottom'=>'30',
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
				'pattern'=>'0',
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
			'padding_bottom'=>'80',
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
		),
		'content'=>array(
			'0'=>array(
				'type'=>'col-twothree',
				'class'=>'',
				'content'=>array(
					'0'=>array(
						'type'=>'title',
						'content'=>'<p>'.__('Kontaktní formulář','cms_ve').'</p>',
						'style'=>array(
							'font'=>array(
								'font-size'=>'26',
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
							'align'=>'center',
						),
					),
					'1'=>array(
						'type'=>'contactform',
						'content'=>'',
						'style'=>array(
							'email'=>'@',
							'button_text'=>__('Odeslat dotaz','cms_ve'),
							'form-style'=>'1',
							'form-font'=>array(
								'font-size'=>'15',
								'color'=>'',
							),
							'background'=>'#eeeeee',
							'button'=>array(
								'style'=>'1',
								'font'=>array(
									'font-size'=>'30',
									'font-family'=>'',
									'weight'=>'',
									'color'=>'#ffffff',
									'text-shadow'=>'none',
								),
								'background_color'=>array(
									'color1'=>'#e4960e',
									'color2'=>'',
								),
								'hover_color'=>array(
									'color1'=>'',
									'color2'=>'',
								),
								'corner'=>'0',
								'border-color'=>'',
							),
						),
					),
				),
			),
			'1'=>array(
				'type'=>'col-three',
				'class'=>'',
				'content'=>array(
					'0'=>array(
						'type'=>'title',
						'content'=>'<p>'.__('Název firmy','cms_ve').'</p>',
						'style'=>array(
							'font'=>array(
								'font-size'=>'26',
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
							'align'=>'center',
						),
					),
					'1'=>array(
						'type'=>'text',
						'content'=>'<p>'.__('Ulice 45<br />Praha 4, 123 45','cms_ve').'</p>
<p><br />'.__('IČ: 123456789<br />DIČ: CZ123465798','cms_ve').'</p>',
						'style'=>array(
							'font'=>array(
								'font-size'=>'',
								'font-family'=>'',
								'weight'=>'',
								'line-height'=>'',
								'color'=>'',
							),
							'li'=>'',
						),
					),
					'2'=>array(
						'type'=>'title',
						'content'=>'<p>'.__('E-mail','cms_ve').'</p>',
						'style'=>array(
							'font'=>array(
								'font-size'=>'26',
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
							'align'=>'center',
						),
					),
					'3'=>array(
						'type'=>'text',
						'content'=>'<p>'.__('email@email.cz','cms_ve').'</p>',
						'style'=>array(
							'font'=>array(
								'font-size'=>'',
								'font-family'=>'',
								'weight'=>'',
								'line-height'=>'',
								'color'=>'',
							),
							'li'=>'',
						),
					),
					'4'=>array(
						'type'=>'title',
						'content'=>'<p>'.__('Telefon','cms_ve').'</p>',
						'style'=>array(
							'font'=>array(
								'font-size'=>'26',
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
							'align'=>'center',
						),
					),
					'5'=>array(
						'type'=>'text',
						'content'=>'<p>'.__('(+420) 123 456 789','cms_ve').'</p>',
						'style'=>array(
							'font'=>array(
								'font-size'=>'',
								'font-family'=>'',
								'weight'=>'',
								'line-height'=>'',
								'color'=>'',
							),
							'li'=>'',
						),
					),
				),
			),
		),
	),
);
$page=array(
  'page'=>array(      
      'title' => __('Kontakt','cms_ve'),   
      'slug' => __('contact','cms_ve'), 
      'theme' => 'page/1/',
  ),
  'setting'=>array (),
  'layer'=>base64_encode(serialize($temp_layer)),
);
