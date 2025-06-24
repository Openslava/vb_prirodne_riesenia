<footer>
<?php 
global $vePage;
if($vePage->footer_setting['show']!="nofooter")  {

if (isset($vePage->set_list['footers'][$vePage->footer_setting['appearance']])) {
    if(isset($vePage->footer_setting['custom_footer']) && $vePage->footer_setting['custom_footer']) 
        echo $vePage->weditor->create_content($vePage->footer_setting['custom_footer'],'cms_footer');
    if(!isset($vePage->footer_setting['hide_footer_end'])) 
        load_template( $vePage->set_list['footers'][$vePage->footer_setting['appearance']]['file'], true ); 
}

?>

<?php
}     

wp_footer();     
 ?>
 </footer>
 </div>  <!-- wrapper -->
</body>

</html>
