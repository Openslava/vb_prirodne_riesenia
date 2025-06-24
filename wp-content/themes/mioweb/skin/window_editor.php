<?php
global $vePage; 

get_skin_header();
?>  
<div id="window_content_container" class="<?php echo $_GET['window_editor']; ?>_content_container">   
       <?php 
         echo $vePage->write_content($vePage->layer,$vePage->post_id,'cms_popup')
        ?>
</div>
<?php 

get_skin_footer(); 
