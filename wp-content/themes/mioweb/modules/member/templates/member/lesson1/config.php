<?php
$temp_layer=array(
	'0'=>array(
		'class'=>'',
		'style'=>array(
			'font'=>array(
				'font-size'=>'',
				'font-family'=>'',
				'weight'=>'',
				'color'=>'#9bbcd1',
			),
			'link_color'=>'#ffffff',
			'background_color'=>array(
				'color1'=>'#0b3e5c',
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
			'padding_bottom'=>'25',
		),
		'content'=>array(
			'0'=>array(
				'type'=>'col-one',
				'class'=>'',
				'content'=>array(
					'0'=>array(
						'type'=>'title',
						'content'=>'<p style="text-align: center;">'.__('Lekce 1','cms_member').'</p>',
						'style'=>array(
							'font'=>array(
								'font-size'=>'36',
								'font-family'=>'',
								'weight'=>'',
								'color'=>'#ffffff',
								'text-shadow'=>'none',
							),
						),
						'config'=>array(
							'max_width'=>'',
							'margin_top'=>'0',
							'margin_bottom'=>'15',
							'delay'=>'',
							'class'=>'',
						),
					),
					'1'=>array(
						'type'=>'text',
						'content'=>'<p style="text-align: center;">Lorem ipsum dolor sit amet, consectetur adipiscing elit. </p>',
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
	'1'=>array(
		'class'=>'',
		'style'=>array(
			'background_color'=>array(
				'color1'=>'#fff',
				'color2'=>'',
			),
			'link_color'=>'',
			'font'=>array(
				'font-size'=>'',
				'font-family'=>'',
				'weight'=>'',
				'color'=>'',
			),
		),
		'content'=>array(
			'0'=>array(
				'type'=>'col-four',
				'class'=>'',
				'content'=>array(
					'0'=>array(
						'type'=>'member_subpages',
						'content'=>'',
						'style'=>array(
							'page'=>'',
							'style'=>'3',
							'cols'=>'1',
							'font'=>array(
								'font-family'=>'',
								'weight'=>'',
							),
							'color'=>'#219ed1',
							'setting'=>array(
								'hide_comments'=>'hide_comments',
								'hide_desc'=>'hide_desc',
							),
						),
					),
				),
			),
			'1'=>array(
				'type'=>'col-threefour',
				'class'=>'',
				'content'=>array(
					'0'=>array(
						'type'=>'video',
						'content'=>'',
						'style'=>array(
							'code'=>'',
						),
					),
					'1'=>array(
						'type'=>'text',
						'content'=>'<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec facilisis nulla at arcu scelerisque mollis. Sed pretium consectetur semper. Cras odio dui, suscipit commodo placerat in, iaculis ac enim. Suspendisse accumsan, orci ut tincidunt mattis, nisl est ultricies nunc, in hendrerit sem nisi vel neque. Donec tincidunt nulla ac dignissim iaculis. Cras sed elementum mauris. Proin imperdiet auctor nisi, eget rhoncus urna vehicula a. Aliquam interdum tortor eu pulvinar pulvinar. Donec sit amet massa odio. Praesent sit amet augue sit amet elit euismod dictum nec vitae odio. Aenean ornare facilisis eros, sed sagittis lorem placerat in. Mauris gravida eu erat sit amet egestas. Fusce tristique dignissim libero ut scelerisque.</p>',
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
					'2'=>array(
						'type'=>'title',
						'content'=>__('Soubory ke stažení','cms_member'),
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
							'margin_top'=>'40',
							'margin_bottom'=>'20',
							'delay'=>'',
							'class'=>'',
						),
					),
					'3'=>array(
						'type'=>'member_download',
						'content'=>array(
							'0'=>array(
								'name'=>__('Soubor ke stažení','cms_member'),
								'file'=>__('Zde vložte odkaz.','cms_member'),
								'desc'=>'',
								'icon'=>'1',
							),
						),
						'style'=>array(
							'style'=>'4',
							'color'=>'#0b3e5c',
						),
					),
					'4'=>array(
						'type'=>'title',
						'content'=>__('Komentáře','cms_member'),
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
							'margin_top'=>'40',
							'margin_bottom'=>'20',
							'delay'=>'',
							'class'=>'',
						),
					),
					'5'=>array(
						'type'=>'wpcomments',
						'content'=>'',
						'style'=>array(
							'style'=>'1',
						),
					),
				),
			),
		),
	),
);
$config['layer']=base64_encode(serialize($temp_layer));
$config['setting']=array();
$config['config']=array();