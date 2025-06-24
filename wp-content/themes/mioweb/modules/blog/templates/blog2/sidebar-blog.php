<?php
global $blog_module;
if(isset($blog_module->appearance['blog_sidebar'])) { ?>
<div id="blog-sidebar">
<?php 
$sidebars=get_option('blog_sidebars');
if(is_single()) $sidebar=$sidebars['sidebar_post'];
else if(is_home()) $sidebar=$sidebars['sidebar_blog'];
else if(is_author()) $sidebar=$sidebars['sidebar_author'];
else if(is_category()) $sidebar=$sidebars['sidebar_category'];
else if(is_archive()) $sidebar=$sidebars['sidebar_category'];
else if(is_tag()) $sidebar=$sidebars['sidebar_tag'];
else if(is_search()) $sidebar=$sidebars['sidebar_search'];

if ( is_active_sidebar( $sidebar ) ) : ?>
	<ul>
		<?php dynamic_sidebar( $sidebar ); ?>
	</ul>
<?php endif; ?>
</div>
<?php } ?>
