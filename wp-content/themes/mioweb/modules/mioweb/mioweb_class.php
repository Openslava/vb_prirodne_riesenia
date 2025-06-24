<?php  

class MioWeb {
var $edit_mode;
var $campaigns;
var $first_campaign;
var $script_version;
var $js_texts;

function __construct(){  
    if ( current_user_can('edit_pages') ) $this->edit_mode=true;  
    else $this->edit_mode=false;
    
    $this->script_version=filemtime(get_template_directory().'/style.css');
    
    require_once(__DIR__.'/js/js_texts.php');
    $this->js_texts=$js_texts;
    
    // get all campaigns  
    $this->campaigns=get_option('campaign_basic');  

    if($this->edit_mode) {  
        if(isset($_POST["mioweb_save_campaign_setting"])) {
            add_action( 'init', array($this, 'after_save_campaign') );
            add_action( 've_after_save_options', array($this, 'after_save_campaign') );   
        }            
        //ajax
        add_action('wp_ajax_add_new_campaign', array($this, 'add_new_campaign'));
        add_action('wp_ajax_add_campaign_page', array($this, 'add_campaign_page'));
    
        //scripts
        add_action('admin_enqueue_scripts', array($this, 'load_admin_scripts') );           
        
        //functions
        add_action('customize_save_after', array($this, 'after_save_option')); 
        
        add_action( 'wp', array($this,'init'));    
        
        //get first campaign for menu
        if(!empty($this->campaigns)) $this->first_campaign=reset($this->campaigns['campaigns']);  
        
        //create page
        add_action( 've_create_page', array($this, 'action_create_page') );  
    }
    add_action('cms_after_facebook_meta', array($this, 'check_cookies')); 
    add_action( 'wp_enqueue_scripts', array($this, 'load_front_scripts')) ; 
    
    if(isset($_GET["setuser"])) add_action('wp', array($this, 'set_cookies'));
    if(isset($_GET["clear_cookie"])) add_action('wp', array($this, 'clear_mioweb_access_cookie')); 
}

function init() {
    global $vePage, $post;
    if(isset($post->ID)) {
        $campaign_id = get_post_meta( $post->ID, 'mioweb_campaign',true );
        if(isset($campaign_id['campaign']) && $campaign_id['campaign']!=="") {
            $vePage->modul_type='campaign';           
        } 
        
    }    
}

function load_admin_scripts() {
    $current_screen = get_current_screen();
    if ( isset($_GET['page']) && $_GET['page']=="campaign_option" )  {   
        wp_enqueue_script('mioweb_admin_script', MIOWEB_DIR.'js/admin.js', array('jquery'),$this->script_version);
        wp_enqueue_style('mioweb_admin_css',MIOWEB_DIR.'css/admin.css',array(),$this->script_version ); 
        
        wp_localize_script( 'mioweb_admin_script', 'campaign_texts', $this->js_texts['admin']);
    }
}   
function load_front_scripts() { 
    if($this->edit_mode) {  
        wp_enqueue_script('mioweb_admin_script', MIOWEB_DIR.'js/admin.js', array('jquery'),$this->script_version);
        wp_enqueue_style('mioweb_admin_css',MIOWEB_DIR.'css/admin.css',array(),$this->script_version ); 
        
        wp_localize_script( 'mioweb_admin_script', 'campaign_texts', $this->js_texts['admin']);
    }
    wp_enqueue_style('mioweb_content_css',MIOWEB_DIR.'css/content.css',array(),$this->script_version ); 
}   
//after save campaings 
function after_save_campaign() {
    $pages = get_pages( array('meta_key'=>'mioweb_campaign') );
    foreach($pages as $page) {
        delete_post_meta($page->ID, 'mioweb_campaign');
    }
    if(isset($_POST['campaign_basic']['campaigns'])) {
        $content=$_POST['campaign_basic']['campaigns'];
        if(is_array($content)) {
            foreach($content as $id=>$campaign) {
                foreach($campaign['page'] as $page) {                    
                    update_post_meta($page['page'], 'mioweb_campaign', array('campaign'=>$id, 'type'=>'page'));
                }
																update_post_meta($campaign['squeeze'], 'mioweb_campaign', array('campaign'=>$id, 'type'=>'squeeze'));
            }
        }
    }

} 

// add campaign on create page
function action_create_page($page_id) {    
    if(isset($_POST['ve_post_campaign'])) {

        $count=count($this->campaigns['campaigns'][$_POST['ve_post_campaign']]['page']);
        if(!$this->campaigns['campaigns'][$_POST['ve_post_campaign']]['page'][$count-1]['page']) $new_id=$count-1;
        else $new_id=$count;
        
        $this->campaigns['campaigns'][$_POST['ve_post_campaign']]['page'][$new_id]=array(
            'page'=>$page_id,
        );  
        
        update_option('campaign_basic',$this->campaigns);       
        update_post_meta($page_id, 'mioweb_campaign', array('campaign'=>$_POST['ve_post_campaign'], 'type'=>'page'));
    }
}

// Add campaign page
function add_campaign_page() {
    $pages = get_pages(array('post_status'=>'publish')); 
    $this->campaign_page($_POST['tagid'].'_'.$_POST['id'], $_POST['tagname'].'[page]['.($_POST['id']).']', array('page'=>''), ($_POST['id']+1).'. '.__('Stránka s obsahem zdarma','cms_mioweb'), $pages);
    die();
} 

// Campaign setting
function generate_campaing_setting($campaign, $id, $pages, $tagname, $tagid) {
    ?>
        <input type="hidden" name="<?php echo $tagname; ?>[name]" value="<?php echo $campaign['name']; ?>">
        <h4><?php echo __('Stránky kampaně','cms_mioweb'); ?></h4> 
        <div class="campaing_set campaign_set_box">
            <label class="campaign_set_box_label" for="<?php echo $tagid.'_squeeze'; ?>"><?php echo __('Vstupní stránka','cms_mioweb'); ?></label>
            <div class="campaign_set_box_content">  
            <?php Cms::select_page($pages, (isset($campaign['squeeze'])? $campaign['squeeze']: ''), $tagname.'[squeeze]', $tagid.'_squeeze','campaing_select_page'); ?>
            </div>
        </div>  
        <?php if(!isset($campaign['page'])) { ?>
            <div class="campaing_set campaign_set_box">    
                <?php $this->campaign_page($tagid.'_page_0', $tagname.'[page][0]', array('page'=>''), '1. '.__('Stránka s obsahem zdarma','cms_mioweb'), $pages, false); ?>            
            </div>
            <?php 
            $newid=1;
        }
        else {
            $i=0;
            foreach($campaign['page'] as $pid=>$page) {
                ?>
                <div class="campaing_set campaign_set_box">
                   <?php $this->campaign_page($tagid.'_page_'.$i, $tagname.'[page]['.$i.']', $campaign['page'][$pid], ($i+1).'. '.__('Stránka s obsahem zdarma','cms_mioweb'), $pages, (($i>0)? true : false)); ?>
                   <div class="cms_info_box_gray"><?php echo __('Pro odkázání uživatele na tuto stránku použijte odkaz:','cms_mioweb'); ?> <strong><?php echo get_permalink($campaign['page'][$pid]['page']); if(get_option('permalink_structure')) echo "?"; else echo "&"; ?>setuser=<?php echo $campaign['code']; ?></strong></div>
                </div>
                <?php
                $i++;               
            }
            $newid=$i;
        }
        ?>
        <button class="mioweb_add_campaign_page cms_button_secondary" data-id="<?php echo $newid; ?>" data-name="<?php echo $tagname; ?>"  data-tagid="<?php echo $tagid; ?>"><?php echo __('Přidat stránku do kampaně','cms_mioweb'); ?></button>
        <h4><?php echo __('Nastavení kampaně','cms_mioweb'); ?></h4>
        <div class="campaing_set">
            <div class="label"><?php echo __('Přístupový kód','cms_mioweb'); ?></div>
            <input class="cms_text_input" type="text" name="<?php echo $tagname.'[code]'; ?>" id="<?php echo $tagid.'_code'; ?>" value="<?php echo $campaign['code']; ?>" />
            <div class="cms_description"><?php echo __('Tento kód budete používat jako hodnotu atributu setuser v URL adrese, na kterou budete směrovat registrované návštěvníky. Díky tomuto kódu se jim uloží cookies a umožní se jim přístup na stránky kampaně. Přístupový kód musí být jedinečný pro každou kampaň.','cms_mioweb'); ?></div>
            
            <div class="label"><?php echo __('Délka platnosti přístupu','cms_mioweb'); ?></div>
            <input class="cms_text_input cms_text_input_size" placeholder="365" type="text" name="<?php echo $tagname.'[duration]'; ?>" id="<?php echo $tagid.'_duration'; ?>" value="<?php if(isset($campaign['duration'])) echo $campaign['duration']; ?>" />
            <?php echo __('dní','cms_mioweb'); ?>
            <div class="cms_description"><?php echo __('Po vypršení této doby se zneplatní přístup uživatele do kampaně. Při dalším přístupu se vynulují všechny odpočty a kampaň začne od začátku. Pokud nic nevyplníte platnost se nastaví na jeden rok.','cms_mioweb'); ?></div>
            
            <div class="label"><?php echo __('Přesměrování ze vstupní stránky','cms_mioweb'); ?></div>
            <label for="<?php echo $tagid.'_noredirect'; ?>">
              <input type="checkbox" name="<?php echo $tagname.'[noredirect]'; ?>" <?php if(isset($campaign['noredirect'])) echo 'checked="checked"'; ?> id="<?php echo $tagid.'_noredirect'; ?>" value="1" />
              <?php echo __('Nepřesměrovávat uživatele ze vstupní stránky, když je v kampani přihlášený','cms_mioweb'); ?>
            </label>
            <div class="cms_description"><?php echo __('Defaultně je každý uživatel, který má do kampaně přístup automatický přesměrován na 1. stránku s obsahem zdarma.','cms_mioweb'); ?></div>
        </div>
        <h4><?php echo __('Evergreen','cms_mioweb'); ?></h4>  
        <div class="campaing_set">
            <label for="<?php echo $tagid.'_evergreen'; ?>">
              <input type="checkbox" name="<?php echo $tagname.'[evergreen]'; ?>" <?php if(isset($campaign['evergreen'])) echo 'checked="checked"'; ?> id="<?php echo $tagid.'_evergreen'; ?>" value="1" />
              <?php echo __('Aktivovat evergreen mód','cms_mioweb'); ?>
            </label>
            <div class="cms_description"><?php echo __('Pokud je evergreen mód u kampaně aktivní, znamená to, že každému novému návštěvníkovi, který se registruje, budou zpřístupněny pouze ty stránky, na které ho přímo odkážete (za použití výše nastaveného přístupového kódu v URL). Můžete tak stránky kampaně zpřístupňovat postupně například pomocí e-mailové kampaně, s e-maily odkazujícími na jednotlivé stránky. Pokud je evergreen mód aktivní, deaktivuje se nastavení data zveřejnění u všech stránek kampaně.','cms_mioweb'); ?></div>
        </div>   
        <h4><?php echo __('Smazat kampaň','cms_mioweb'); ?></h4>
        <a class="mioweb_delete_campaign" data-id="<?php echo $id; ?>" href="#"><?php echo __('Smazat kampaň','cms_mioweb'); ?></a>
    <?php
}

function campaign_page($id, $name, $content, $title, $pages, $delete=true) {
    ?>
    <label class="campaign_set_box_label" for="<?php echo $id; ?>"><?php echo $title; ?></label>
    <div class="campaign_set_box_content">   
                    <?php Cms::select_page($pages, $content['page'], $name.'[page]', $id,'campaing_select_page'); ?>                   
                    <a class="mioweb_setting_campaign_page" href="#"><?php echo __('Nastavení stránky','cms_mioweb'); ?><span></span></a>
                    <table class="campaign_page_set">
                        <tr>
                            <th><label for="<?php echo $id.'_exclude'; ?>"><?php echo __('Nevypisovat v menu kampaně','cms_mioweb'); ?></label></th>
                            <td><?php cms_generate_field_checkbox($name.'[exclude]',$id.'_exclude',isset($content['exclude'])? $content['exclude']: null); ?></td>
                        </tr> 
                        <tr>
                            <th><label for="<?php echo $id.'_publishdate'; ?>"><?php echo __('Datum zveřejnění','cms_mioweb'); ?></label></th>
                            <td><?php cms_generate_field_datetime($name.'[publishdate]',$id.'_publishdate',isset($content['publishdate'])? $content['publishdate']: null); ?></td>
                        </tr> 
                        <tr>
                            <th><label for="<?php echo $id.'_name'; ?>"><?php echo __('Název stránky v menu','cms_mioweb'); ?></label></th>
                            <td><?php echo cms_generate_field_text($name.'[name]',$id.'_name',isset($content['name'])? stripslashes($content['name']): null); ?></td>
                        </tr>
                        <tr>
                            <th><label for="<?php echo $id.'_csname'; ?>"><?php echo __('Název před zveřejněním','cms_mioweb'); ?></label></th>
                            <td><?php echo cms_generate_field_text($name.'[csname]',$id.'_csname',isset($content['csname'])? stripslashes($content['csname']): null); ?></td>
                        </tr>
                        <tr>
                            <th><label for="<?php echo $id.'_thumb'; ?>"><?php echo __('Náhledový obrázek v menu','cms_mioweb'); ?></label></th>
                            <td><?php echo cms_generate_field_upload($name.'[thumb]',$id.'_thumb',isset($content['thumb'])? stripslashes($content['thumb']): null); ?></td>
                        </tr>
                        <tr>
                            <th><label for="<?php echo $id.'_csthumb'; ?>"><?php echo __('Náhledový obrázek před zveřejněním','cms_mioweb'); ?></label></th>
                            <td><?php echo cms_generate_field_upload($name.'[csthumb]',$id.'_csthumb',isset($content['csthumb'])? stripslashes($content['csthumb']): null); ?></td>
                        </tr>   
                    </table>
                    <?php if($delete) { ?><a class="mioweb_delete_campaign_page" href="#" title="<?php echo __('Odstranit','cms_mioweb'); ?>"></a><?php } ?>
    </div>
    <?php
}

// New campaign
function add_new_campaign() {
    $pages = get_pages(array('post_status'=>'publish'));
    $campaign=array(
        'name'=>$_POST['campaign_name'],
        'code'=>'1199',
    );
    $this->generate_campaing_setting($campaign, $_POST['id'], $pages, $_POST['tagname'].'['.$_POST['id'].']', $_POST['tagid'].'_'.$_POST['id']);
    die();
}

// Menu

function create_mioweb_menu() {
 $menu='<ul>';
 if(isset($this->campaigns['campaigns'])) { 
      $menu.='<li><a class="create-new-page" data-type="campaign" title="'.__('Vytvořit novou stránku','cms_mioweb').'" href="#">'.__('Nová stránka kampaně','cms_mioweb').'</a></li>';
      $count=count($this->campaigns['campaigns']);
      if($count>0) {

            $menu.='<li><a class="ve_prevent_default ve_menu_has_submenu" href="#">'.__('Přejít na kampaň','cms_mioweb').'</a>';
                $menu.='<ul>';
                foreach($this->campaigns['campaigns'] as $camp) {
                    $menu.='<li><a href="'.get_permalink($camp['squeeze']).'">'.$camp['name'].'</a></li>';    
                }
                $menu.='</ul>
            </li>';        
      } 
  }   
  $menu.='<li><a class="open-setting" data-type="group" data-setting="campaign_option" title="'.__('Kampaně','cms_mioweb').'" href="'.admin_url('admin.php?page=campaign_option').'">'.(isset($this->campaigns['campaigns'])?__('Správa kampaní','cms_mioweb'):__('Vytvořit kampaň','cms_mioweb')).'</a></li>
  </ul>';
  return $menu;
} 

// Cookies

function check_cookies() {
    global $post;
    if(!current_user_can('administrator') && isset($post->ID)){
        

        $campaign_id = get_post_meta( $post->ID, 'mioweb_campaign',true );
  
        if(isset($campaign_id['campaign'])) {
            $redirect=true;
            $sq_redirect=false;
            
            if(isset($_COOKIE['mioweb_campaign_access'])) {
                $access=unserialize(stripslashes($_COOKIE['mioweb_campaign_access']));
                if(is_array($access)) {
                    $campaigns=get_option( 'campaign_basic' );
                    foreach($access as $id=>$c_access) {
                        if($id===$campaign_id['campaign']) {                          
                            if(isset($campaigns['campaigns'][$campaign_id['campaign']]['evergreen'])){
                              if($access[$id]=='all' || in_array($post->ID,explode(",",$access[$id]))) $redirect=false;                           
                            }
                            else {
                               foreach($campaigns['campaigns'][$campaign_id['campaign']]['page'] as $page) {
                                  if($page['page']==$post->ID && current_time( 'timestamp' )>strtotime($page['publishdate']['date']." ".$page['publishdate']['hour'].":".$page['publishdate']['minute'].":0")) $redirect=false;
                               } 
                            } 
                            $sq_redirect=true;     
                        }  
                    }
                }
            }
            if($redirect && $campaign_id['type']=='page') {
                $campaigns=get_option( 'campaign_basic' );
                if($campaigns['campaigns'][$campaign_id['campaign']]['squeeze']) {
                    $url = get_permalink( $campaigns['campaigns'][$campaign_id['campaign']]['squeeze'] ).$this->makeatt($_GET);
                }
                else {
                    $url = get_home_url().$this->makeatt($_GET);
                }
            }
            else if($sq_redirect && $campaign_id['type']=='squeeze' && (in_array($campaigns['campaigns'][$campaign_id['campaign']]['page'][0]['page'],explode(",",$access[$campaign_id['campaign']])) || $access[$campaign_id['campaign']]=='all')) {
                  $campaigns=get_option( 'campaign_basic' );
                  if(!isset($campaigns['campaigns'][$campaign_id['campaign']]['noredirect'])) $url = get_permalink( $campaigns['campaigns'][$campaign_id['campaign']]['page'][0]['page']).$this->makeatt($_GET);
                  else $url=null;
            }
            else {
                $url = null;
            }
            if ($url !== null) {
                if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
                    echo $this->format_post_redirect_script($url, $_POST);
                }
                else {
                    echo $this->format_get_redirect_script($url);
                }
                die();
            }
        } 
    }

}
function format_get_redirect_script($url) {
    $html = '<script type="text/javascript">' . "\n";
    $html .= 'window.location.href = ' . json_encode($url) . ';' . "\n";
    $html .= '</script>' . "\n";
    return $html;
}
function format_post_redirect_script($url, $postData) {
    $html = '<form action="' . htmlspecialchars($url) . '" method="POST" name="frm">' . "\n";
    foreach ($postData as $key => $value) {
        $html .= '<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars((string) $value) . '" />' . "\n";
    }
    $html .= '</form>' . "\n";
    $html .= '<script type="text/javascript">' . "\n";
    $html .= 'document.frm.submit();' . "\n";
    $html .= '</script>' . "\n";
    return $html;
}
function clear_mioweb_access_cookie() {
  unset($_COOKIE['mioweb_campaign_access']);
  setcookie('mioweb_campaign_access', '' ,current_time('timestamp') - 3600, "/");
}
function set_cookies() {    	
    global $post; 
    $campaign_id = get_post_meta( $post->ID, 'mioweb_campaign',true );
    if(isset($campaign_id['campaign']) && $campaign_id['type']=='page') {
        $campaigns=get_option( 'campaign_basic' );
        if($_GET['setuser']==$campaigns['campaigns'][$campaign_id['campaign']]['code']) {
            
            // generate list of pages for evergreen       
            if(isset($campaigns['campaigns'][$campaign_id['campaign']]['evergreen'])) {
                $pages=array();
                foreach($campaigns['campaigns'][$campaign_id['campaign']]['page'] as $c_page) {
                      $pages[]=$c_page['page'];
                      if($c_page['page']==$post->ID) break;  
                }       
            }
        
            if(isset($_COOKIE['mioweb_campaign_access'])) {
                $access=unserialize(stripslashes($_COOKIE['mioweb_campaign_access']));
            }
            else $access=array();
            
            // if evergreen save smaller page, if no evergreen set access to all pages
            if(isset($campaigns['campaigns'][$campaign_id['campaign']]['evergreen'])) $access[$campaign_id['campaign']]=implode(',',$pages);
            else $access[$campaign_id['campaign']]='all';   
            if(!isset($access['time'][$campaign_id['campaign']]) || isset($_GET['reset_time'])) $access['time'][$campaign_id['campaign']]=current_time('timestamp'); 
             
            if(isset($campaigns['campaigns'][$campaign_id['campaign']]['duration']) && $campaigns['campaigns'][$campaign_id['campaign']]['duration']!=='') $days=(int)$campaigns['campaigns'][$campaign_id['campaign']]['duration'];
            else $days=365;        
            setcookie('mioweb_campaign_access', serialize($access) ,current_time('timestamp') + (60*60*24*$days), "/");
            
            $url = get_permalink( $post->id ).$this->makeatt($_GET);
            if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
                echo $this->format_post_redirect_script($url, $_POST);
                die();
            } else {
                header("Location: " . $url);
            }	
        }	
    }						
}
function makeatt($q) {
    $att="?";
    $return=false;
    if(is_array($q)) {
        foreach($q as $k => $v) {
            if($k!="setuser" && $k!="Errors" && $k!="clear_cookie" && $k!="p" && $k!="page_id") {
                $att.=$k."=".urlencode($v)."&";
                $return=true;
            }
        }
    }
    if($return) return $att;
    else return '';
}


}

?>
