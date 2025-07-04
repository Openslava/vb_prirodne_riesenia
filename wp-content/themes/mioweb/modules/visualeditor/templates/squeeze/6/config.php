<?php
$temp_layer=array(
	'0'=>array(
		'class'=>'',
		'style'=>array(
			'font'=>array(
				'font-size'=>'',
				'font-family'=>'',
				'weight'=>'',
				'color'=>'#949494',
			),
			'link_color'=>'#949494',
			'background_color'=>array(
				'color1'=>'',
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
				'type'=>'col-one',
				'class'=>'',
				'content'=>array(
					'0'=>array(
						'type'=>'text',
						'content'=>'<p style="text-align: center;">'.__('Vytvořte si web během minuty...','cms_ve').'</p>',
						'style'=>array(
							'font'=>array(
								'font-size'=>'20',
								'font-family'=>'',
								'weight'=>'',
								'color'=>'',
							),
							'li'=>'',
						),
					),
					'1'=>array(
						'type'=>'title',
						'content'=>'<p style="text-align: center;">'.__('Stáhněte si jednoduchý návod, jak si během chvilky vytvořit web, který bude opravdu prodávat.','cms_ve').'</p>',
						'style'=>array(
							'font'=>array(
								'font-size'=>'40',
								'font-family'=>'',
								'weight'=>'',
								'color'=>'#ffffff',
								'text-shadow'=>'none',
							),
						),
						'config'=>array(
							'max_width'=>'700',
							'margin_top'=>'0',
							'margin_bottom'=>'50',
							'delay'=>'',
							'class'=>'',
						),
					),
					'2'=>array(
						'type'=>'box',
						'content'=>array(
							'0'=>array(
								'0'=>array(
									'type'=>'seform',
									'content'=>'',
									'style'=>array(
										'form-style'=>'1',
										'form-look'=>'10',
										'form-font'=>array(
											'font-size'=>'15',
											'color'=>'',
										),
										'background'=>'#ffffff',
										'button_text'=>'',
										'button'=>array(
											'style'=>'6',
											'font'=>array(
												'font-size'=>'30',
												'font-family'=>'Open Sans',
												'weight'=>'600',
												'color'=>'#2b2b2b',
												'text-shadow'=>'none',
											),
											'background_color'=>array(
												'color1'=>'#ffde21',
												'color2'=>'#ffcc00',
											),
											'corner'=>'5',
											'border-color'=>'#d1a700',
										),
										'popup_text'=>__('Stáhnout návod zdarma','cms_ve'),
										'popup_title'=>__('Zadejte svůj e-mail a stáhněte si návod zdarma.','cms_ve'),
										'popupbutton'=>array(
											'style'=>'3',
											'font'=>array(
												'font-size'=>'38',
												'font-family'=>'Open Sans',
												'weight'=>'600',
												'color'=>'#2b2b2b',
												'text-shadow'=>'none',
											),
											'background_color'=>array(
												'color1'=>'#ffde21',
												'color2'=>'#ffcc00',
											),
											'corner'=>'0',
											'border-color'=>'#d1a700',
										),
										'link_font'=>array(
											'font-size'=>'',
											'color'=>'',
										),
									),
									'config'=>array(
										'max_width'=>'100%',
										'margin_top'=>'0',
										'margin_bottom'=>'20',
										'delay'=>'',
										'class'=>'',
									),
								),
								'1'=>array(
									'type'=>'text',
									'content'=>'<p style="text-align: center;">'.__('Vaše údaje jsou na 100 % v bezpečí.','cms_ve').'</p>',
									'style'=>array(
										'font'=>array(
											'font-size'=>'',
											'font-family'=>'',
											'weight'=>'',
											'color'=>'',
										),
										'li'=>'',
									),
									'config'=>array(
										'max_width'=>'',
										'margin_top'=>'0',
										'margin_bottom'=>'0',
										'delay'=>'',
										'class'=>'',
									),
								),
							),
						),
						'style'=>array(
							'background_color'=>array(
								'color1'=>'#1f1f1f',
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
								'line-height'=>'',
								'color'=>'',
							),
							'link-color'=>'',
							'border'=>array(
								'size'=>'0',
								'color'=>'',
							),
							'corner'=>'10',
							'padding'=>array(
								'top'=>'40',
								'bottom'=>'30',
								'left'=>'40',
								'right'=>'40',
							),
							'box-shadow'=>array(
								'horizontal'=>'0',
								'vertical'=>'0',
								'size'=>'0',
								'transparency'=>'10',
							),
							'title'=>'',
							'title-font'=>array(
								'font-size'=>'20',
								'font-family'=>'',
								'weight'=>'',
								'line-height'=>'',
								'align'=>'center',
								'color'=>'',
							),
							'title_bg'=>array(
								'color1'=>'#eeeeee',
								'color2'=>'',
								'transparency'=>'100',
							),
							'title_border'=>array(
								'size'=>'0',
								'color'=>'',
							),
						),
						'config'=>array(
							'max_width'=>'500',
							'margin_top'=>'0',
							'margin_bottom'=>'20',
							'delay'=>'',
							'animate'=>'',
							'id'=>'',
							'class'=>'',
						),
					),
					'3'=>array(
						'type'=>'text',
						'content'=>'<p style="text-align: center;"><a href="" target="_blank">'.__('Obchodní podmínky','cms_ve').'</a></p>',
						'style'=>array(
							'font'=>array(
								'font-size'=>'',
								'font-family'=>'',
								'weight'=>'',
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
$config['layer']=base64_encode(serialize($temp_layer));

$config['setting']=array (  
 've_header' => array('show' => 'noheader'),
 've_footer' => array('show' => 'nofooter'), 
 've_appearance' => array('background_image' => array('pattern' => '1', 'image' =>  ''),'background_color'=>'#383838') 
);

$config['config']=array(
);
