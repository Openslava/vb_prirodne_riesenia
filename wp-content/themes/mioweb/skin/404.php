<?php

get_skin_header();

global $vePage;

echo $vePage->write_content($vePage->layer,$vePage->edit_mode);

get_skin_footer();

?>
