<?php 

$page_url=MWS()->getUrl_Home();
$searched=(isset($_GET['search_product']) && $_GET['search_product'])? true : false;
$hide_cat=(isset(MWS()->setting['eshop_hide']['categories']))? true : false; 
$hide_search=(isset(MWS()->setting['eshop_hide']['search']))? true : false; 

if(!$hide_search || !$hide_cat) {
    $class='mws_top_panel';
    if($hide_cat) $class.=' mws_top_panel_nocat';
    if($hide_search) $class.=' mws_top_panel_nosearch';
    ?>
    <div class="<?php echo $class; ?>">
        <?php if(!$hide_cat) { ?>
            <div class="mws_category_list mw_vertical_menu">
                <?php echo MWS()->getShopCategories('title_element_container'); ?>
            </div>
        <?php } ?>
        <?php if(!$hide_search) { ?>
        <div class="mws_search_container <?php echo ($searched)? 'mws_search_container_active' : ''; ?>">
            <div class="mws_top_panel_label"><?php echo __('Vyhledávání ','mwshop'); ?></div>
            <form role="search" method="get" id="searchform" class="searchform" action="<?php echo $page_url; ?>">
        					<input type="text" value="<?php echo ($searched)? $_GET['search_product'] : ''; ?>" name="search_product" id="search_product" />
                  <button type="submit"><?php echo file_get_contents(MWS()->getTemplateFileDir("img/icons/search.svg"), true); ?></button>
                  <!-- <a class="mws_search_cancel" href="<?php echo $page_url; ?>"><?php echo file_get_contents(MWS()->getTemplateFileDir("img/icons/close.svg"), true); ?></a>  -->
    			 </form>
        </div>
        <?php } ?>
        <div class="cms_clear"></div>
    </div>
    <?php 
    if($searched) { 
        ?>
        <div class="mws_search_title">
            <?php echo __('Výsledek hledání pro slovo','mwshop').' "'.$_GET['search_product'].'".'; ?>
            <a href="<?php echo $page_url; ?>"><?php  echo __('Zrušit vyhledávání','mwshop'); ?></a>
        </div>
        <?php 
    } 

}
?>


