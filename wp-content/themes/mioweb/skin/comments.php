<?php
global $mw_comment_set;

if ( post_password_required() ) {
	return;
}
?>
<div id="comments" class="comments">

	<?php if ( have_comments() ) : ?>

		<ol class="comment-list">
			<?php
				$avatar_size=($mw_comment_set['comment_style']=='3')? 75 : 60;
				
				wp_list_comments( array(
					'short_ping'  => true,
					'reply_text' => __('Odpovědět','cms'),
					'avatar_size' => $avatar_size,
				) );
			?>
		</ol><!-- .comment-list -->

		<?php 

// pagination
$args = array(
    'show_all'     => False,
    'end_size'     => 1,
    'mid_size'     => 2,
    'prev_next'    => True,
    'prev_text'    => file_get_contents(VS_DIR.'/images/icons/left.svg', true).'<span>'.__('Předchozí', 'cms').'</span>',
    'next_text'    => file_get_contents(VS_DIR.'/images/icons/right.svg', true).'<span>'.__('Další', 'cms').'</span>',
    'type'         => 'plain',
    'add_fragment'=>'#comments'
);

		?>
		<div class="cms_comments_pagination">
		<?php paginate_comments_links($args); ?>
		</div>

	<?php endif; // Check for have_comments(). ?>

	<?php
		// If comments are closed and there are comments, let's leave a little note, shall we?
		if ( ! comments_open() && get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) :
	?>
		<p class="no-comments"><?php _e( 'Comments are closed.' ); ?></p>
	<?php endif; ?>

	<?php
	
	if(isset($mw_comment_set['button_hover']) && $mw_comment_set['button_hover']) $but_class=' ve_cb_hover_'.$mw_comment_set['button_hover'];
	else $but_class=''; 
	
	$bstyle=(isset($mw_comment_set['button_style']))? $mw_comment_set['button_style']: 'x';
	
	$form_args=array(
	  'class_submit'=>'ve_content_button ve_content_button_'.$bstyle.$but_class,
	  'label_submit'=>__('Vložit komentář', 'cms'),
	  'cancel_reply_link'=>__('Zrušit odpověď', 'cms'),
	  'title_reply'=>__('Přidat komentář','cms'),
	);
	comment_form( $form_args );
	?>

</div><!-- .comments-area -->
