<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
<html>
<head>
  <title></title>
</head>
<body style="background: #000; text-align: center; color: #fff;">

		<div id="container" class="single-attachment">






					<h2 class="entry-title"><?php the_title(); ?></h2>
<?php if ( wp_attachment_is_image() ) :
	$attachments = array_values( get_children( array( 'post_parent' => $post->post_parent, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => 'ASC', 'orderby' => 'menu_order ID' ) ) );
	foreach ( $attachments as $k => $attachment ) {
		if ( $attachment->ID == $post->ID )
			break;
	}
	$k++;
	// If there is more than 1 image attachment in a gallery
	if ( count( $attachments ) > 1 ) {
		if ( isset( $attachments[ $k ] ) )
			// get the URL of the next image attachment
			$next_attachment_url = get_attachment_link( $attachments[ $k ]->ID );
		else
			// or get the URL of the first image attachment
			$next_attachment_url = get_attachment_link( $attachments[ 0 ]->ID );
	} else {
		// or, if there's only 1 image attachment, get the URL of the image
		$next_attachment_url = wp_get_attachment_url();
	}
?>
						<p class="attachment"><?php
							$attachment_width  = apply_filters( 'twentyten_attachment_size', 900 );
							$attachment_height = apply_filters( 'twentyten_attachment_height', 900 );
							echo wp_get_attachment_image( $post->ID, array( $attachment_width, $attachment_height ) ); // filterable image width with, essentially, no limit for image height.
						?></p>


<?php endif; ?>







		</div><!-- #container -->
</body>
</html>
<?php endwhile; // end of the loop. ?>