<?php
$temp_layer=array(
	'0'=>array(
		'class'=>'',
		'style'=>array(
			'font'=>array(
				'font-size'=>'15',
				'font-family'=>'',
				'weight'=>'',
				'color'=>'#cccccc',
			),
			'link_color'=>'',
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
						'type'=>'title',
						'content'=>'<h1 style="text-align: center;">'.__('Název vysílaného webináře','cms_ve').'</h1>',
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
							'max_width'=>'800',
							'margin_top'=>'0',
							'margin_bottom'=>'20',
							'delay'=>'',
							'class'=>'',
						),
					),
					'1'=>array(
						'type'=>'video',
						'content'=>'',
						'style'=>array(
							'code'=>'',
							'align'=>'center',
						),
					),
					'2'=>array(
						'type'=>'like',
						'content'=>'',
						'style'=>array(
							'layout'=>'button_count',
							'scheme'=>'dark',
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
			'font'=>array(
				'font-size'=>'',
				'font-family'=>'',
				'weight'=>'',
				'color'=>'#d5f5ee',
			),
			'link_color'=>'',
			'background_color'=>array(
				'color1'=>'#0c9978',
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
			'padding_bottom'=>'10',
		),
		'content'=>array(
			'0'=>array(
				'type'=>'col-one',
				'class'=>'',
				'content'=>array(
					'0'=>array(
						'type'=>'title',
						'content'=>'<p style="text-align: center;">'.__('Informace k průběhu webináře','cms_ve').'</p>',
						'style'=>array(
							'font'=>array(
								'font-size'=>'24',
								'font-family'=>'',
								'weight'=>'',
								'color'=>'#ffffff',
								'text-shadow'=>'none',
							),
						),
						'config'=>array(
							'max_width'=>'800',
							'margin_top'=>'0',
							'margin_bottom'=>'25',
							'delay'=>'',
							'class'=>'',
						),
					),
					'1'=>array(
						'type'=>'text',
						'content'=>'<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec ornare nibh non tellus varius egestas. Nunc quis purus justo. Etiam sodales sagittis dui eu luctus. Nullam id leo nec orci varius porttitor vehicula in ante. Ut eu nulla eget augue laoreet ultrices. Praesent quis sapien enim. Curabitur arcu risus, pharetra nec elit at, fermentum luctus nisi. Proin sit amet vestibulum ligula. Nullam leo augue, tincidunt in arcu ac, auctor faucibus dolor. Nunc et nisi lectus.</p>',
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
							'margin_bottom'=>'30',
							'delay'=>'',
							'class'=>'',
						),
					),
				),
			),
		),
	),
	'2'=>array(
		'class'=>'',
		'style'=>array(
			'font'=>array(
				'font-size'=>'15',
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
			'padding_top'=>'60',
			'padding_bottom'=>'60',
		),
		'content'=>array(
			'0'=>array(
				'type'=>'col-one',
				'class'=>'',
				'content'=>array(
					'0'=>array(
						'type'=>'title',
						'content'=>'<p style="text-align: center;">'.__('Máte dotaz?','cms_ve').'</p>',
						'style'=>array(
							'font'=>array(
								'font-size'=>'24',
								'font-family'=>'',
								'weight'=>'',
								'color'=>'',
								'text-shadow'=>'none',
							),
						),
						'config'=>array(
							'max_width'=>'800',
							'margin_top'=>'0',
							'margin_bottom'=>'25',
							'delay'=>'',
							'class'=>'',
						),
					),
					'1'=>array(
						'type'=>'html',
						'content'=>'<p><br><br>'.__('Zde vložte javascript kód pluginu pro vypsání komentářů, které chcete na stránce použít. Pro účely vysílání webináře je potřeba, aby bylo možné napsat komentář bez nutnosti znovunačtení stránky. Vhodné jsou například komentáře <a href="https://disqus.com" target="_blank">disqus</a>.','cms_ve').'<br><br></p>',
						'style'=>array(
						),
					),
				),
			),
		),
	),
	'3'=>array(
		'class'=>'',
		'style'=>array(
			'font'=>array(
				'font-size'=>'',
				'font-family'=>'',
				'weight'=>'',
				'color'=>'#cccccc',
			),
			'link_color'=>'',
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
						'content'=>'<p style="text-align: center;">Lorem ipsum dolor set</p>',
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
 've_appearance' => array('background_color' => '#141414') 
);
$config['config']=array(
  'body_class'=>'fixed_narrow_width_page'
);