<?php
class webInstallator {
var $webs=array(); 
var $tags=array();      
var $installed_web;
var $menus=array();
var $contents=array();
var $images=array();
var $edit_mode=false;
  
function __construct(){ 

    $this->installed_web=get_option('ve_installed_web'); 
    
    add_action('init', array($this,'init'));
    
    if (current_user_can('edit_pages')) $this->edit_mode = true;
    else $this->edit_mode = false;
    
    if($this->edit_mode) {

        add_action( 'wp_footer', array( $this, 'show_install_steps' ));
        add_action('wp_ajax_install_web_popup',  array($this, 'install_steps_popup'));
        add_action('wp_ajax_import_web_popup',  array($this, 'import_popup'));
        
        // install web action
        if(isset($_POST['web_to_install'])) add_action('init',array( $this, 'install_web'));
        
        if(isset($_GET["export_mioweb_template"])) {
            $this->export_theme_zip();        
        } 
        if(isset($_POST["export_web_from_mw"])) {
            $this->export_web_zip();        
        } 
        if(isset($_POST["import_web_upload"])) {
            add_action('init',array( $this, 'import_web_zip'));     
        } 
    }
}

function init() {
    if(!$this->installed_web) {
        $to_install=get_option('mw_web_to_install');
        if($to_install && isset($this->webs[$to_install])) {
            $this->install_web($to_install);
            wp_redirect(get_home_url());
            delete_option( 'mw_web_to_install' );
            die();
        } 
    }
}

function export_theme_zip() {
    
    $post_id=$_GET['export_mioweb_template'];
    
    // create zip
    $zipname = tempnam(WP_CONTENT_DIR,'zip');
    $zip = new ZipArchive;
    $zip->open($zipname, ZipArchive::CREATE);

    // get page setting
    $page=$this->getPage($post_id, $zip); 
    
    $zip->addFromString("config.php", $page);
    $zip->close();
    
    //filename
    $post = get_post($post_id);
    $slug = $post->post_name;    
    
    header('Content-Type: application/zip');
    header('Content-disposition: attachment; filename='.$slug.'.zip');
    readfile($zipname); 
    unlink($zipname);

  die();      
} 

function export_web_zip() {
    global $vePage;

    if(isset($_POST['export']['all_pages'])) {
        $pages = get_pages();
    } else if(isset($_POST['export']['pages'])) {
        $pages=array();
        foreach($_POST['export']['pages'] as $p) {
            $pages[]=get_page($p);
        }
    }

    // create zip
    $zipname = tempnam(WP_CONTENT_DIR,'zip');
    $zip = new ZipArchive;
    $zip->open($zipname, ZipArchive::CREATE);
    
    $install=array();
    $setting=array();
    $image_list=array();
    
    $install['pages']=array();
    $install['menus']=array();
    $install['contents']=array();
    
    // visual setting
    
    if(isset($_POST['export']['web_look'])) {
        $setting['ve_header']=$this->get_layer_vars(get_option('ve_header'),$zip,$image_list);
        $setting['ve_footer']=$this->get_layer_vars(get_option('ve_footer'),$zip,$image_list);
        $setting['ve_appearance']=$this->get_layer_vars(get_option('ve_appearance'),$zip,$image_list);
        $setting['image_list']=$image_list;
    }
    if(isset($_POST['export']['export_blog']['setting'])) {
        $setting['blog_header']=$this->get_layer_vars(get_option('blog_header'),$zip,$image_list);
        $setting['blog_footer']=$this->get_layer_vars(get_option('blog_footer'),$zip,$image_list);
        $setting['blog_appearance']=$this->get_layer_vars(get_option('blog_appearance'),$zip,$image_list);
        $setting['blog_comments']=$this->get_layer_vars(get_option('blog_comments'),$zip,$image_list);
        $setting['image_list']=$image_list;
    }
    $zip->addFromString('mw_web_setting.php', $vePage->code($setting));

    $home_page=get_option( 'page_on_front' );
    $blog_page=get_option( 'page_for_posts');
    
    // blog content
    $pagetozip=$this->getLayerContent($zip);
    if($pagetozip) $zip->addFromString('mw_blog_content.php', $pagetozip);
    
    //pages
    foreach($pages as $page) {
      
        $page_name=$page->post_name.'_'.$page->post_parent;

        $pagetozip=$this->getPage($page->ID, $zip);
        $zip->addFromString($page_name.'.php', $pagetozip);
        $install['pages'][$page_name]=array(
          'id'=>$page->ID,
          'post'=>array(
              'post_title' => $page->post_title,
              'post_name' => $page->post_name,
              'post_status' => $page->post_status,
              'comment_status' => $page->comment_status,
              'post_type'=> 'page',
              'post_author' => 1,
              'post_content' => '',
              'post_excerpt' => $page->post_excerpt,
              'post_parent'=> $page->post_parent, 
              'menu_order'=> $page->menu_order, 
          )
        );   
        if($page->ID==$home_page) $install['pages'][$page_name]['page']='home';
        if($page->ID==$blog_page) $install['pages'][$page_name]['page']='blog';    
    }
    
    //menus
    foreach($this->menus as $menu_id) {
        if(is_nav_menu( $menu_id )) {
            $menu = wp_get_nav_menu_object( $menu_id );
            $menu_items=wp_get_nav_menu_items($menu_id);
            $install['menus'][$menu_id]=array(
                'name'=>$menu->name,
                'items'=>array()
            );
            foreach((array)$menu_items as $menu_item) {
                $page=get_post($menu_item->object_id);
                $install['menus'][$menu_id]['items'][$menu_item->ID]=array(
                    'type'=>($menu_item->type=='custom')?'link':'page',
                    'page'=>($menu_item->type=='custom')?'':$page->post_name.'_'.$page->post_parent,
                    'link'=>($menu_item->type=='custom')?$menu_item->url:'',
                    'parent'=>$menu_item->menu_item_parent,
                    'title'=>$menu_item->post_title,
                    'target'=>$menu_item->target,
                    'order'=>$menu_item->menu_order,
                );
            }
        }
    }
    //contents
    foreach($this->contents as $content_id=>$type) {
        $page=get_post($content_id);
        if($page) {
            $pagetozip=$this->getPage($content_id, $zip, $type); 
            $zip->addFromString($page->post_name.'.php', $pagetozip);
            $install['contents'][$page->post_name]=array(
              'id'=>$page->ID,
              'post'=>array(
                'post_title' => $page->post_title,
                'post_name' => $page->post_name,
                'post_status' => $page->post_status,
                'post_type'=> $page->post_type,
                'post_author' => 1,
                'post_content' => '',
              ) 
            );   
        }     
    }
    
    $zip->addFromString('mw_web_install.php', $vePage->code($install));

    $zip->close();   
    
    header('Content-Type: application/zip');
    header('Content-disposition: attachment; filename=mioweb_export.zip');
    readfile($zipname); 
    unlink($zipname);  
                      
    die();      
} 

function getPage($post_id, &$zip, $type='page', $code=true) {
    global $wpdb, $vePage;
    
    $result=$wpdb->get_row("SELECT * FROM ".$wpdb->prefix."ve_posts_layer WHERE vpl_type='".$type."' AND vpl_post_id=".$post_id);
    
    $layer=$vePage->decode($result->vpl_layer);
    
    $ve_header=get_post_meta($post_id,'ve_header',true);  
    $ve_footer=get_post_meta($post_id,'ve_footer',true);
    $ve_appearance=get_post_meta($post_id,'ve_appearance',true);
    
    // page images
    $image_list=array();
    $layer=$this->get_layer_vars($layer,$zip,$image_list);
    $ve_header=$this->get_layer_vars($ve_header,$zip,$image_list);
    $ve_footer=$this->get_layer_vars($ve_footer,$zip,$image_list);
    $ve_appearance=$this->get_layer_vars($ve_appearance,$zip,$image_list);

    $config=array();
    $config['page_template']=get_post_meta($post_id, 've_page_template', true);
    $config['image_list']=$image_list;
    $config['layer']=$layer;
    $config['setting']=array(
        've_header'=>$ve_header,
        've_footer'=>$ve_footer,
        've_appearance'=>$ve_appearance,
    );

    return ($code)? $vePage->code($config) : $config;
}

function getLayerContent(&$zip, $type='blog', $post_id=0) {
    global $wpdb, $vePage;
    
    $result=$wpdb->get_row("SELECT * FROM ".$wpdb->prefix."ve_posts_layer WHERE vpl_type='".$type."' AND vpl_post_id=".$post_id);
    if ($wpdb->num_rows) {
        $layer=$vePage->decode($result->vpl_layer);
    
        // layer images
        $image_list=array();
        $layer=$this->get_layer_vars($layer,$zip,$image_list);

        $config=array();
        $config['image_list']=$image_list;
        $config['layer']=$layer;

        return $vePage->code($config);
    }
    else return '';
}

// find all images in layer for export
function get_layer_vars($setting, &$zip, &$image_list) {
    if(is_array($setting)) {
    foreach($setting as $key=>$val) { /*
        if($key==='image_gallery_items') {
            foreach($val as $img_key=>$img_id) {
                $g_image=wp_get_attachment_image_src( $img_id, 'full' );
                $path_parts = pathinfo($g_image[0]);
                $image_name = $path_parts['basename'];
                $fullsize_path = get_attached_file( $img_id );
                if(file_exists($fullsize_path)) {
                    $image_list[$image_name]=$image_name;
                    $image_list[$image_name.'_ID']=$img_id;
                    $zip->addFile($fullsize_path,$image_name);
                    $setting[$key][$img_key]='%%replace_image_'.$image_name.'%%';
                } else unset($setting[$key][$img_key]);
            }
        } else */
        if(is_array($val)) {     
               
            $setting[$key]=$this->get_layer_vars($val, $zip, $image_list);
            
            // delete gallery images
            if(isset($setting[$key]['image_gallery_items'])) {
                //print_r($setting[$key]);
                if(count($setting[$key]['image_gallery_items'])) {
                    foreach($setting[$key]['image_gallery_items'] as $img_key=>$img_id) {

                        if($img_id) {
                            $val = wp_get_attachment_image_src( $img_id, 'full' );
                            
                            $image_src = str_replace(get_home_url().'/','',$val[0]);

                            $path_parts = pathinfo($image_src);
                            $image_name=$path_parts['basename'];
                            
                            if(file_exists(ABSPATH.$image_src)) {                              
                                $image_list[$image_name]=$image_name;
                                $zip->addFile(ABSPATH.'/'.$val,$image_name);
                                $setting[$key]['image_gallery_items'][$img_key]='%%replace_image_'.$image_name.'%%';
                            } else unset($setting[$key]['image_gallery_items'][$img_key]);

                        }
                    }
                }
                
                //$setting[$key]['image_gallery_items']=array();
            }
            
            // delete id of images
            if(isset($setting[$key]['imageid'])) $setting[$key]['imageid']='';            
            
            // delete se form id
            if(isset($setting[$key]['type']) && $setting[$key]['type']=='seform') {
                $setting[$key]['content']=''; 
            }  
            // variable content
            if(isset($setting[$key]['type']) && $setting[$key]['type']=='variable_content') {
                $this->contents[$setting[$key]['content']]='ve_elvar';
            } 
        }
        else if(($key=='image' || $key=='large_image' || $key=='custom_image' || $key=='logo') && $val && substr($val, 0, 7)!='http://') {
            $path_parts = pathinfo($val);
            $image_name=$path_parts['basename'];
            if(file_exists(ABSPATH.'/'.$val)) {
                $image_list[$image_name]=$image_name;
                $zip->addFile(ABSPATH.'/'.$val,$image_name);
                $setting[$key]='%%replace_image_'.$image_name.'%%';
            } else $setting[$key]='';
        } else if($key=='menu') {
            if($val) $this->menus[$val]=$val;
        } else if($key=='before_header') {
            if($val) $this->contents[$val]='ve_header';
        } else if($key=='custom_footer') {
            if($val) $this->contents[$val]='cms_footer';
        } else if($key=='slider_content') {
            if($val) $this->contents[$val]='mw_slider';
        } 
        
    }
    }
    return $setting;
}

// find all images in layer for import
function insert_layer_vars($setting, $image_list, $path) {
    if(is_array($setting)) {
    foreach($setting as $key=>$val) { /* 
        if($key==='image_gallery_items') {
            foreach($val as $img_key=>$img) {
                if(strpos($img,'%%replace_image_')!==false) {
                    $name=str_replace('%%replace_image_','',$img);
                    $name=str_replace('%%','',$name);
                    if(isset($image_list[$name.'_ID'])) {
                        if(file_exists($path.'/'.$image_list[$name])) $setting[$key][$img_key]=$this->images[$name];
                        else unset($setting[$key][$img_key]);
                    }
                }
            }
        } else */
        if(is_array($val)) {
            $setting[$key]=$this->insert_layer_vars($val, $image_list, $path);
            
            if(isset($setting[$key]['type']) && $setting[$key]['type']=='variable_content') {
                if(isset($val['content']) && $val['content'] && isset($setting[$key])) 
                    $setting[$key]['content']=$this->contents[$val['content']];
            } 
            
            if(isset($setting[$key]['image_gallery_items'])) {
                //print_r($setting[$key]);
                if(count($setting[$key]['image_gallery_items'])) {
                    foreach($setting[$key]['image_gallery_items'] as $image_key=>$img_val) {
                            if(strpos($img_val,'%%replace_image_')!==false) {
                                $name=str_replace('%%replace_image_','',$img_val);
                                $name=str_replace('%%','',$name);
                                if(isset($image_list[$name])) {
                                    if(file_exists($path.'/'.$image_list[$name])) $setting[$key]['image_gallery_items'][$image_key]=get_home_url().'/'.str_replace(ABSPATH,'',$path).'/'.$image_list[$name];
                                    else unset($setting[$key]['image_gallery_items'][$image_key]);
                                }
                            }
                    }
                }
                
                //$setting[$key]['image_gallery_items']=array();
            }
        }
        else if(($key=='image' || $key=='large_image' || $key=='custom_image' || $key=='logo') && $val && substr($val, 0, 7)!='http://') { 
            if(strpos($val,'%%replace_image_')!==false) {
                $name=str_replace('%%replace_image_','',$val);
                $name=str_replace('%%','',$name);
                if(isset($image_list[$name])) {
                    if(file_exists($path.'/'.$image_list[$name])) $setting[$key]='/'.str_replace(ABSPATH,'',$path).'/'.$image_list[$name];
                    else $setting[$key]='';
                }
            }
        } else if($key=='menu') {
            if($val && isset($setting[$key])) $setting[$key]=$this->menus[$val];
        } else if($key=='before_header') {
            if($val && isset($setting[$key])) $setting[$key]=$this->contents[$val];
        } else if($key=='custom_footer') {
            if($val && isset($setting[$key])) $setting[$key]=$this->contents[$val];
        } else if($key=='slider_content') {
            if($val && isset($setting[$key])) $setting[$key]=$this->contents[$val];
        } 
    }
    }
    return $setting;
}

// find all old page ids in setting and replace it
function replace_pages_id($setting, $installed_pages) {
    if(is_array($setting)) {
    foreach($setting as $key=>$val) { 
        if(is_array($val)) {
            $setting[$key]=$this->replace_pages_id($val, $installed_pages);
        }
        else if($key=='page') { 
            if(isset($installed_pages[$val])) {
                $setting[$key]=$installed_pages[$val];
            }
        } 
    }
  }
  return $setting;  
}

function import_page_zip() {
    global $wpdb, $vePage;
    
    $zip = new ZipArchive;
    $res = $zip->open($_FILES['import_template_upload']['tmp_name']);
    if ($res === TRUE) {
    
        if (!function_exists('wp_generate_attachment_metadata')){
            require_once(ABSPATH . "wp-admin" . '/includes/image.php');
            require_once(ABSPATH . "wp-admin" . '/includes/file.php');
            require_once(ABSPATH . "wp-admin" . '/includes/media.php');
        }
        
    
        WP_Filesystem(); 
        $folder = wp_upload_dir(); 
    
        $zip_config = zip_open($_FILES['import_template_upload']['tmp_name']);
    
        $images=array();
        // extract config
        if ($zip_config) {
            while ($zip_entry = zip_read($zip_config)) {
                $filename=zip_entry_name($zip_entry);
                if($filename=='config.php') {
                    $config_code = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
                    zip_entry_close($zip_entry);
                    
                }
                // save list of images in zip
                else {
                    $filetype = wp_check_filetype($filename, null );
                    if( preg_match( '#^image/#', $filetype['type'] ) && !file_exists($folder['path'].'/'.$filename)) $images[]=$filename;
                }                    
            }
        }
        zip_close($zip_config);
        
        if(isset($config_code)) {

            //extract and save images from zip
            $zip->extractTo($folder['path'], $images);
            $zip->close();
            
            foreach($images as $filename) {
                  if(file_exists($folder['path'].'/'.$filename)) {
                      $filetype = wp_check_filetype($filename, null );
                    	$attachment = array(
                    		'post_mime_type' => $filetype['type'],
                    		'post_title' => preg_replace('/\.[^.]+$/', '', $filename),
                    		'post_content' => '',
                    		'post_status' => 'inherit',
                        'guid' => $folder['url'].'/'.$filename, 
                    	); 
                      
                    	$attachment_id = wp_insert_attachment( $attachment, $folder['path'].'/'.$filename );
                        
                    	if (!is_wp_error($attachment_id)) {
                    		$attachment_data = $this->mw_generate_attachment_metadata( $attachment_id, $folder['path'].'/'.$filename );
                    		wp_update_attachment_metadata( $attachment_id,  $attachment_data );
                    	}   
                  }
            } 


            $new_post = array(
                'post_title' => $_POST['ve_post_title'],
                'post_name' => $_POST['ve_post_url'],
                'post_status' => 'publish',
                'comment_status' => 'open',
                'post_type'=>'page',
                'post_author' => 1,
                'post_content' => '',
                'post_excerpt' => '',
                'post_parent'=>$_POST['ve_post_parent_id'], 
                'menu_order'=>$_POST['ve_post_menu_order'], 
            );
            
            $post_id=$this->import_page($new_post, $config_code, $folder);
              
            wp_redirect(get_permalink( $post_id ));  
        } else echo __('Stránku nelze importovat. Soubor neobsahuje MioWeb šablonu.','cms_ve');
    } else echo __('Stránku nelze importovat. Soubor není ve formátu ZIP.','cms_ve');
    die();
}

// import web
function import_web_zip() {
    global $wpdb, $vePage;
    if(isset($_FILES['import_file']['tmp_name']) && $_FILES['import_file']['tmp_name']) {
        $zip = new ZipArchive;
        $res = $zip->open($_FILES['import_file']['tmp_name']);
        if ($res === TRUE) {
        
            if (!function_exists('wp_generate_attachment_metadata')){
                require_once(ABSPATH . "wp-admin" . '/includes/image.php');
                require_once(ABSPATH . "wp-admin" . '/includes/file.php');
                require_once(ABSPATH . "wp-admin" . '/includes/media.php');
            }
            
        
            WP_Filesystem(); 
            $folder = wp_upload_dir(); 
        
            $zip_config = zip_open($_FILES['import_file']['tmp_name']);
        
            $images=array();
            $files=array();
            // extract pages
            if ($zip_config) {
                while ($zip_entry = zip_read($zip_config)) {
                    $filename=zip_entry_name($zip_entry);
                    
                    $filetype = wp_check_filetype($filename, null );
                    if(pathinfo($filename, PATHINFO_EXTENSION)=='php') {
                        $files[$filename] = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
                        zip_entry_close($zip_entry); 
                    }
                    // save list of images in zip
                    else if( preg_match( '#^image/#', $filetype['type'] ) && !file_exists($folder['path'].'/'.$filename)) {
                        $images[]=$filename;
                    }                    
                }
            }
            zip_close($zip_config);
            
            if(isset($files['mw_web_install.php']) && isset($files['mw_web_setting.php'])) {
                
                $install=$vePage->decode($files['mw_web_install.php']);
                $setting=$vePage->decode($files['mw_web_setting.php']);
    
                //extract and save images from zip
                $zip->extractTo($folder['path'], $images);            
                foreach($images as $filename) {
                      if(file_exists($folder['path'].'/'.$filename)) {
                          $filetype = wp_check_filetype($filename, null );
                        	$attachment = array(
                        		'post_mime_type' => $filetype['type'],
                        		'post_title' => preg_replace('/\.[^.]+$/', '', $filename),
                        		'post_content' => '',
                        		'post_status' => 'inherit',
                            'guid' => $folder['url'].'/'.$filename, 
                        	); 
                          
                        	$attachment_id = wp_insert_attachment( $attachment, $folder['path'].'/'.$filename );
                            
                        	if (!is_wp_error($attachment_id)) {
                        		$attachment_data = $this->mw_generate_attachment_metadata( $attachment_id, $folder['path'].'/'.$filename );
                        		wp_update_attachment_metadata( $attachment_id,  $attachment_data );
                            $this->images[$filename]=$attachment_id;
                        	}   
                      }
                } 
                
                $installed_pages=array();
                $installed_contents=array();
                $installed_menus=array();
                $new_pages_id=array();

               //install menus           
               if(isset($install['menus']) && is_array($install['menus'])) {
                   foreach($install['menus'] as $id=>$menu) {
                      //$menu_id=$this->install_menu($menu, $pages_to_install);
                      $menu_id = $this->install_create_menu($menu['name']);
                      
                      $installed_menus[$id]=$menu_id;
                   } 
               }  
               $this->menus=$installed_menus;
               
               //install contents
               if(isset($install['contents']) && is_array($install['contents'])) {
                   foreach($install['contents'] as $slug=>$page) {
                      if(isset($files[$slug.'.php'])) {   
                          //$content_set=$vePage->decode($files[$slug.'.php']);                                         
                          $post_id=$this->import_page($page['post'], $files[$slug.'.php'], $folder, $page['post']['post_type']);                       
                          $installed_contents[$page['id']]=$post_id; 
                      }
                   } 
               }
               $this->contents=$installed_contents;
               
              
              // install pages
              foreach($install['pages'] as $slug=>$page) {
                    if(isset($files[$slug.'.php'])) {   
                                   
                        $post_id=$this->import_page($page['post'], $files[$slug.'.php'], $folder);
                        $new_pages_id[$page['id']]=$post_id;
                        
                        if(isset($page['page'])) {
                            if($page['page']=='home') {  
                                update_option( 'page_on_front', $post_id );
                                update_option( 'show_on_front', 'page' );
                            }
                            if($page['page']=='blog')  
                                update_option( 'page_for_posts', $post_id );
                        }
                        $installed_pages[$slug]=$post_id; 
                    }
                } 
                
                // save blog content
                if(isset($files['mw_blog_content.php'])) {
                    $config=$vePage->decode($files['mw_blog_content.php']);
                    
                    // replace images, contents in layer
                    $config['layer']=$this->insert_layer_vars($config['layer'], $config['image_list'], $folder['path']);        
                    $vePage->save_layer(0, 'blog', $vePage->code($config['layer']), true);
                }
                
                // replace old parent id with new parent id
                foreach($new_pages_id as $ipage_id) {
                    $old_parent_id=wp_get_post_parent_id( $ipage_id );
                    if($old_parent_id) 
                        wp_update_post(array(
                            'ID'           => $ipage_id,
                            'post_parent'   => $new_pages_id[$old_parent_id],
                        ));
                        
                        // TO-DO change id of pages in links and buttons
                        $result=$wpdb->get_row("SELECT * FROM ".$wpdb->prefix."ve_posts_layer WHERE vpl_type='page' AND vpl_post_id=".$ipage_id);
                        if($result) {
                            $player=$vePage->decode($result->vpl_layer);
                            $player=$this->replace_pages_id($player, $new_pages_id);
                            $vePage->save_layer($ipage_id, 'page', $vePage->code($player), true);
                        }
                }
                
               //install menu items          
               if(count($installed_menus)) {
                   foreach($installed_menus as $id=>$menu_id) {
                      $this->install_menu($install['menus'][$id], $installed_pages, $menu_id);
                   } 
               }  
                
              // web setting
              if(isset($setting) && is_array($setting)) {
                  foreach($setting as $key=>$val) {
                      $val=$this->insert_layer_vars($val, $setting['image_list'], $folder['path']);
                      update_option( $key, $val );
                  }
              }
               
              // web imported info
              $ve_imported_web=get_option('ve_imported_web');
              if(!$ve_imported_web) $ve_imported_web=array();
              $ve_imported_web[]=array(
                  'pages'=>$installed_pages,
                  'menus'=>$installed_menus,
                  'contents'=>$installed_contents,
              );
              update_option( 've_imported_web', $ve_imported_web );
               
              wp_redirect( home_url() );   
                
            } else echo __('Import nelze provést. Soubor neobsahuje MioWeb šablonu.','cms_ve'); 
            
            $zip->close();
            
        } else echo __('Import nelze provést. Soubor není ve formátu ZIP.','cms_ve');
    } else echo __('Nebyl nahrán žádný soubor k importu.','cms_ve');
    die();
}

function import_page($post, $page_code, $folder, $type='page') {
            global $vePage;

            $config=$vePage->decode($page_code);
            
            // replace images, contents in layer
            $config['layer']=$this->insert_layer_vars($config['layer'], $config['image_list'], $folder['path']);        
            $post['post_content'] = $vePage->code($config['layer']);

            if($type=='page') $post_id=$vePage->save_new_page($post, $config['page_template']['directory'],$vePage->code($config['layer']));  
            else $post_id=$vePage->save_new_window_post($post, $config['page_template']['directory'],$vePage->code($config['layer']),$type);    
        
            // save setting and replace imported images to setting
            foreach($config['setting'] as $key=>$val) {
                $val=$this->insert_layer_vars($val, $config['image_list'], $folder['path']);
                update_post_meta( $post_id, $key,$val);
            }
            
            return $post_id;

}

function mw_generate_attachment_metadata( $attachment_id, $file ) {
        $attachment = get_post( $attachment_id );
        $metadata = array();
        $support = false;
        if ( preg_match('!^image/!', get_post_mime_type( $attachment )) && file_is_displayable_image($file) ) {
                $imagesize = getimagesize( $file );
                $metadata['width'] = $imagesize[0];
                $metadata['height'] = $imagesize[1];
                // Make the file path relative to the upload dir
                $metadata['file'] = _wp_relative_upload_path($file);
                // make thumbnails and other intermediate sizes
                global $_wp_additional_image_sizes;
                /*
                $sizes = array();
                foreach ( array('thumbnail','medium') as $s ) {
                        $sizes[$s] = array( 'width' => '', 'height' => '', 'crop' => false );
                        if ( isset( $_wp_additional_image_sizes[$s]['width'] ) )
                                $sizes[$s]['width'] = intval( $_wp_additional_image_sizes[$s]['width'] ); // For theme-added sizes
                        else
                                $sizes[$s]['width'] = get_option( "{$s}_size_w" ); // For default sizes set in options
                        if ( isset( $_wp_additional_image_sizes[$s]['height'] ) )
                                $sizes[$s]['height'] = intval( $_wp_additional_image_sizes[$s]['height'] ); // For theme-added sizes
                        else
                                $sizes[$s]['height'] = get_option( "{$s}_size_h" ); // For default sizes set in options
                        if ( isset( $_wp_additional_image_sizes[$s]['crop'] ) )
                                $sizes[$s]['crop'] = intval( $_wp_additional_image_sizes[$s]['crop'] ); // For theme-added sizes
                        else
                                $sizes[$s]['crop'] = get_option( "{$s}_crop" ); // For default sizes set in options
                }

                if ( $sizes ) {
                        $editor = wp_get_image_editor( $file );
                        if ( ! is_wp_error( $editor ) )
                                $metadata['sizes'] = $editor->multi_resize( $sizes );
                } else {
                        $metadata['sizes'] = array();
                }    
                */
                // fetch additional metadata from exif/iptc
                $image_meta = wp_read_image_metadata( $file );
                if ( $image_meta )
                        $metadata['image_meta'] = $image_meta;
        } 


        return $metadata;
}


function show_install_steps() {
   global $cms, $vePage;

   //$this->install_steps('install',1);
   
   if(!$cms->valid_license()) {
      if($this->installed_web) $this->install_steps('licence');
      else $this->install_steps('install');
  } else if(!$this->installed_web) $this->install_steps('install',1);   
   
}

function add_webs($webs) {
    $this->webs=array_merge($this->webs,$webs);
}
function add_web_tags($tags) {
    $this->tags=array_merge($this->tags,$tags);
}

function install_steps($type='install',$current=0) {
    ?>    
    <div class="cms_lightbox_background ve_installer_background"></div>
    <div class="cms_lightbox ve_installer_container" style="width: 98%; left: 1%;">
          <?php $this->get_install_steps($type,$current); ?>
    </div>
    <?php
} 
function install_steps_popup() {
    $this->get_install_steps('web');
    die();
} 

function get_install_steps($type='install',$current=0) {
    //install
    if($type=='install')
        $steps=array(
            array(
                'title'=>__('Zadat licenci','cms_ve'),
                'id'=>'licence'
            ),
            array(
                'title'=>__('Zvolit instalaci webu','cms_ve'),
                'id'=>'web_select'
            ),
            array(
                'title'=>__('Dokončeno','cms_ve'),
                'id'=>'done'
            ),
        );
    // only web
    else if($type=='web')
        $steps=array(
            array(
                'title'=>__('Zvolit instalaci webu','cms_ve'),
                'id'=>'web_select',
                'setting'=>array(
                    'storno_but'=>1,
                    'reinstall'=>1
                ),
            ),
            array(
                'title'=>__('Dokončeno','cms_ve'),
                'id'=>'done'
            ),
        );
    // only licence    
    else if($type=='licence')
        $steps=array(
            array(
                'title'=>__('Vložte platnou licenci','cms_ve'),
                'id'=>'licence'
            ),
        );

 
            if(is_array($steps)) {
                echo '<div class="ve_installer_steps">';
                foreach($steps as $id=>$step) {
                    ?>
                    <div class="ve_installer_step ve_isw_<?php echo count($steps); ?> <?php if($id==$current) echo 've_installer_step_current'; if(count($steps)<2) echo ' ve_installer_step_single' ?>">
                        <?php if(count($steps)>1){ ?><div class="ve_installer_step_number"><?php echo $id+1; ?></div> <?php } ?>
                        <span><?php echo $step['title']; ?></span>
                    </div>    
                    <?php
                    if($id==$current) $current_step=$step;
                }
                echo '</div>';   
                $setting=(isset($current_step['setting']))? $current_step['setting'] :array();
                $this->write_step($current_step['id'], $setting);
            }

} 

function write_step($id,$setting) {
    if($id=='licence') $this->write_step_licence($setting);
    if($id=='web_select') $this->write_step_web_select($setting);
}
function write_step_licence($setting) {
    ?>
    <div class="cms_lightbox_content">
        <div class="cms_lightbox_contentin">
            <form id="add_license_key_lightbox" action="" method="post">
                    <p><?php echo __('Vložte své licenční číslo pro ověření platnosti této šablony. Seznam licenčních čísel naleznete na svém účtu na <a href="http://www.mioweb.cz/member/" target="_blank">http://www.mioweb.cz/member/</a>. Přístupové údaje do členské sekce vám byly zaslány na váš e-mail při zakoupení šablony.','cms_ve'); ?></p>
                    
                    <?php $license_key=get_option('web_option_license'); ?>
                    <input type="text" class="cms_text_input" placeholder="<?php echo __('Vložte své licenční číslo.','cms_ve'); ?>" name="licence_key" value="<?php echo $license_key['license']; ?>" />
                    <?php 
                    if(isset($license_key['license'])) cms_check_license_code($license_key['license']);
                    wp_nonce_field('add_license_key','add_license_key_field'); ?>
                    <div class="button_area">
                        <input type="submit" class="cms_button" value="<?php echo __('Uložit licenční číslo','cms_ve'); ?>" />
                    </div>
                    <small><?php echo __('Pokud vám nejde licenční číslo zadat, nacházíte se pravděpodobně v náhledu webu, kde je web nefunkční. Zavřete náhled a přepněte se na domovskou stránku webu, kde můžete licenční číslo zadat.','cms_ve'); ?></small>
            </form>
        </div>
    </div>
    <?php
}
function write_step_web_select($setting) {
    ?>
    <form action="" method="post">
    <div class="cms_lightbox_content ">
        <div class="cms_lightbox_contentin ve_web_select_container">
            <?php 
            if(isset($setting['reinstall'])) {
                echo '<div class="cms_error_box">'.__('Pozor! Instalace webu se nedoporučuje dělat na MioWebu, na kterém už máte postavený svůj web. Instalace změní některá vaše nastavení, a tak naruší vzhled vašeho původního webu. Instalaci tedy proveďte pouze v případě, že vám to nevadí a chcete udělat web znovu.','cms_ve').'</div>';
            }
            ?>
            <div class="ve_div_table">
                <div class="ve_left_setting_menu ve_select_web_tags_menu">
                    <ul>
                        <li><a class="mw_select_tag active" data-container="ve_select_web_container" data-tag="all" href="#"><?php echo __('Všechny','cms_ve'); ?></a></li>
                        <?php
                        foreach($this->tags as $key=>$val) {
                            if(!is_array($val)) echo '<li><a class="mw_select_tag" data-container="ve_select_web_container" data-tag="'.$key.'" href="#">'.$val.'</a></li>';
                        }
                        ?>
                    </ul>
                    <div style="padding: 40px 20px 10px; text-align: right; font-size: 12px;"><?php echo __('Připravujeme','cms_ve'); ?></div>
                    <ul>
                        <?php
                        foreach($this->tags as $key=>$val) {
                            if(is_array($val)) echo '<li class="empty">'.$val[0].'</li>';
                        }
                        ?>
                    </ul>
                </div>
                <div class="ve_right_content ve_select_web_container">
                    <?php
                    foreach($this->webs as $key=>$val) {
                        $this->get_web_item($key);
                    }
                    ?>
                </div>
            </div>
            <div class="cms_clear"></div>
        </div>
    </div>  
    <div id="cms_lightbox_footer" class="cms_lightbox_footer">
        <div class="cms_lightbox_footer_in">
            <input type="submit" class="cms_button ve_select_web_send_but" <?php if(isset($setting['reinstall'])) { ?>onclick="return confirm('<?php echo __('Opravdu chcete tento web nainstalovat? Instalace přepíše některé z vašich nastavení!','cms_ve') ?>')" <?php } ?> value="<?php echo __('Nainstalovat web','cms_ve'); ?>" />
            <?php if(isset($setting['storno_but'])) { ?><a href="#" data-target="cms_lightbox" class="cms_lightbox_storno_but ve_select_web_storno_but cms_button cms_gray_button cms_lightbox_main_but"><?php echo __('Storno','cms_ve'); ?></a><?php } ?>
        </div>
    </div>    
    </form>
    <?php    
}
function get_web_item($id) {
    global $cms;
  
    $path=$this->webs[$id];
    require_once($path.'install.php');
    
    $show=true;
    if(isset($web['group']) && is_array($web['group'])) {
        $buy_group=(isset($cms->licence_info['source']))? $cms->licence_info['source']->group : '';
        if(!in_array($buy_group, $web['group'])) $show=false;
    }
    
    $lang=get_locale();
    
    // hide expert EA webs for EN
    if(($id=='expert' || $id=='expert2') && $lang=='en_US' )
      $show=false;
    
    if($show) {
        $tag_class='mw_tag_item mw_tag_item_all';
        if(isset($web['tags'])) {
            foreach($web['tags'] as $tag) {
                $tag_class.=' mw_tag_item_'.$tag;
            }
        }
                
        if($lang=='en_US' && isset($web['thumb_en'])) $thumb=$web['thumb_en'];
        else $thumb=$web['thumb'];
        
        ?>
        <div class="ve_select_web_item <?php echo $tag_class; ?> <?php if($id=='ea_web') echo 've_select_web_item_selected'; ?>">
            <input name="web_to_install" id="ve_ws_<?php echo $id; ?>" type="radio" value="<?php echo $id; ?>" <?php if($id=='ea_web') echo 'checked="checked"'; ?> />
            <div class="ve_select_web_image">
                <img src="<?php echo $thumb; ?>" alt="" />
                <?php 
                if(isset($web['variants']) && is_array($web['variants'])) {
                    foreach($web['variants'] as $var_key=>$var_val) {
                        echo '<img class="ve_select_image_'.$var_key.' ve_select_web_image_variant" src="'.$var_val['thumb'].'" alt="" />';
                    }
                }
                ?>
                <span class="ve_select_selected_ico"></span>
                <?php if(isset($web['demo']) && $web['demo']) { ?>
                    <a href="<?php echo $web['demo']; ?>" class="ve_template_demo_button" target="_blank"><?php echo __('Náhled šablony','cms_ve'); ?></a>
                <?php } ?>
            </div>
            <div class="ve_select_web_title">
                <h2><?php echo $web['title']; ?></h2>
                <span class="ve_select_web_use"><?php echo __('Vyberte kliknutím','cms_ve'); ?></span>
                <span class="ve_select_web_used"><?php echo __('Vybráno k instalaci','cms_ve'); ?></span>
            </div>
            <div class="cms_clear"></div>
            <div class="ve_select_web_desc">
                
                <?php 
                
                if(isset($web['variants']) && is_array($web['variants'])) {
                    echo '<div class="ve_select_web_variants">';
                    echo '<div class="ve_select_web_variants_container">';
                    $i=1;
                    foreach($web['variants'] as $var_key=>$var_val) {
     
                        echo '<a '.(($i===1)? 'class="ve_selected_web_variant"':'').' data-select="'.$var_key.'" href="#" style="background: '.$var_val['color'].'">
                            <input name="web_color_to_install_'.$id.'" id="ve_wsc_'.$id.'_'.$var_key.'" type="radio" value="'.$var_key.'" '.(($i===1)? 'checked="checked"':'').' />
                            <span></span>
                        </a>';
                        $i++;   
                    }
                    echo '</div>';
                    echo '<div class="ve_select_web_variants_container">'.__('Vyberte variantu','cms_ve').'</div>';
                    echo '<div class="cms_clear"></div></div>';
                    
                } else //echo '<span>'.__('Nemá varianty','cms_ve').'</span>'; 
                
                ?>
                
                <p><?php echo $web['desc']; ?></p>
            </div>
            <div class="ve_select_web_info">
                <div class="ve_select_web_taglist">
                    <?php 
                    echo '<span>'.file_get_contents(VS_SERVER_DIR."images/tag.svg", true).'</span>';
                    
                    $tags=array();
                    foreach($web['tags'] as $tag) {
                        $tags[]=$this->tags[$tag];
                    }
                    echo implode(', ',$tags);
                    ?>    
                </div>
                <div class="ve_select_web_pagelist">
                    <?php 
                    
                    echo '<span>'.file_get_contents(VS_SERVER_DIR."images/page.svg", true).'</span>';
                    
                    $pages=array();
                    foreach($web['pages'] as $page) {
                        $pages[]=$page['title'];
                    }
                    echo implode(', ',$pages);
                    ?>
                </div>
            </div>
            <div class="cms_clear"></div>
        </div>
        <?php
    } // show
}

function import_popup() {
    ?>
    
    <ul class="cms_tabs">
        <li class="cms_tab element_set_groups_tab">
            <a href="#mw_import_tab" data-group="element_set_groups" class="active"><?php echo __('Importovat web', 'cms_ve'); ?></a>
        </li>
        <li class="cms_tab element_set_groups_tab">
            <a href="#mw_export_tab" data-group="element_set_groups"><?php echo __('Exportovat web', 'cms_ve'); ?></a>
        </li>
    </ul>
    <div id="mw_import_tab" class="cms_setting_block_content cms_tab_container element_set_groups_container cms_tab_container_active">

                <form id="" action="" method="post" enctype="multipart/form-data">
                <?php 
                $import_setting=array(
                    array(
                        'id'=>'info',
                        'content'=>__('Nahrajte zip soubor obsahující export webu z MioWebu. Váš hosting může mít omezení na velikost nahrávaných souborů. Pokud je importovaný soubor větší než je povolená velikost, požádejte svůj hosting o zvýšení limitu velikosti nahrávaných souborů.','cms_ve'),
                        'type'=>'info',
                        'color'=>'blue'
                    ), 
                    array(
                        'id'=>'import_file',
                        'title'=>__('Naimportovat web ze souboru:','cms_ve'),
                        'type'=>'file',
                    ),
                );
                
                write_meta($import_setting, array(), 'import', 'import'); 
                ?>
                <div class="set_form_row">
                    <input type="submit" class="cms_button" name="import_web_upload" value="<?php echo __('Naimportovat web','cms_ve'); ?>" />
                </div>
                </form>

    </div>
    <div id="mw_export_tab" class="cms_setting_block_content cms_tab_container element_set_groups_container">
    <form action="" method="post">
        <?php 
        $export_setting=array(
            array(
                'id'=>'info',
                'content'=>__('Z MioWebu lze exportovat pouze nastavení vzhledu webu a stránky (včetně jejich nastavení). Nelze exportovat kampaně, členské sekce ani jiné nastavení.','cms_ve'),
                'type'=>'info',
                'color'=>'blue'
            ), 
            array(
                'id'=>'web_look',
                'title'=>__('Nastavení vzhledu webu','cms_ve'),
                'label'=>__('Exportovat nastavení vzhledu webu','cms_ve'),
                'type'=>'checkbox',
                'content'=>1,
            ), 
            array(
                'id'=>'all_pages',
                'title'=>__('Stránky webu','cms_ve'),
                'label'=>__('Exportovat všechny stránky','cms_ve'),
                'type'=>'checkbox',
                'content'=>1,
                'show'=>'allpages',
                'show_type' => 'hide',
            ),
            array(
                'id'=>'pages',
                'title'=>__('Exportovat vybrané stránky','cms_ve'),
                'type'=>'pagecheck',
                'show_group' => 'allpages',
            ),
            array(
                'id'=>'export_blog',
                'title'=>__('Exportovat blog','cms_ve'),
                'type'=>'multiple_checkbox',
                'options' => array(
                    array('name' => __('exportovat vzhled a nastavení blogu', 'cms_ve'), 'value' => 'setting'),
                ),
            ), 
        );
        
        write_meta($export_setting, array(), 'export', 'export'); 
        ?>
        <div class="set_form_row">
            <input type="submit" class="cms_button" name="export_web_from_mw" value="<?php echo __('Exportovat web','cms_ve'); ?>" />
        </div>
    </form>
    </div>
    <?php
    die();
} 


// web instalation
// **********************************************************************

function install_web($web_to_install='empty') {
  global $vePage;
  
  if(isset($_POST['web_to_install'])) $web_to_install=$_POST['web_to_install'];
  
  $installed_web=get_option('ve_installed_web' );
  if($installed_web) {
      if(isset($installed_web['pages']) && is_array($installed_web['pages'])) {
          foreach($installed_web['pages'] as $del) {
              wp_delete_post( $del );
          }
      }
      if(isset($installed_web['posts']) && is_array($installed_web['posts'])) {
          foreach($installed_web['posts'] as $del) {
              wp_delete_post( $del );
          }
      }
      if(isset($installed_web['menus']) && is_array($installed_web['menus'])) {
          foreach($installed_web['menus'] as $del) {
              wp_delete_nav_menu( $del );
          }
      }
      if(isset($installed_web['contents']) && is_array($installed_web['contents'])) {
          foreach($installed_web['contents'] as $del) {
              wp_delete_post( $del );
          }
      }    /*
      if(isset($installed_web['sidebars']) && is_array($installed_web['sidebars'])) {
          $sidebars = get_option('cms_sidebars');
          $deleted=array();
          if($sidebars && is_array($sidebars)) {
              foreach($installed_web['sidebars'] as $del) {          
            		  foreach($sidebars as $sidebar) {
                      if($sidebar['id']!=$del) $deleted[]=$sidebar;
                  }          
              }
              update_option('cms_sidebars', $deleted );
          }
      }  */
  }
  
   $path=$this->webs[$web_to_install];
   $color_set=array();
   if(isset($_POST['web_color_to_install_'.$web_to_install])) {
      $color=$_POST['web_color_to_install_'.$web_to_install];
      require_once($path.'variants/'.$color.'.php');
   }
   require_once($path.'install.php');
   
   if(isset($web['home']))
      update_option( 'show_on_front', $web['home'] );
   
   // install pages
   $installed_pages=array();
   if(isset($web['pages']) && is_array($web['pages'])) {
       foreach($web['pages'] as $id=>$page) {
          $post_id=$this->install_page($id, $path, $color_set,$installed_pages);
          if(isset($page['page'])) {
              if($page['page']=='home')   
                  update_option( 'page_on_front', $post_id );
              if($page['page']=='blog')  
                  update_option( 'page_for_posts', $post_id );
          }
          $installed_pages[$id]=$post_id;
       } 
   }
   $installed_posts=array();
   if(isset($web['posts']) && is_array($web['posts'])) {
       foreach($web['posts'] as $id=>$post) {
          $post_id=$this->install_post($id, $post);
          $installed_posts[$id]=$post_id;
       } 
   }
   //menus
   $installed_menus=array();
   if(isset($web['menus']) && is_array($web['menus'])) {
       foreach($web['menus'] as $id=>$menu) {
          $menu_id=$this->install_menu($menu, $installed_pages);
          $installed_menus[$id]=$menu_id;
       } 
   }
   //contents
   $installed_contents=array();
   if(isset($web['content_blocks']) && is_array($web['content_blocks'])) {
       foreach($web['content_blocks'] as $id=>$c) {
          $c_id=$this->install_content($id, $path, $color_set);
          $installed_contents[$id]=$c_id;
       } 
   }
   //sidebars
   $installed_sidebars=array();
   if(isset($web['sidebars']) && is_array($web['sidebars'])) {
       foreach($web['sidebars'] as $id=>$sidebar) {
          $sidebar_id=$this->install_sidebar($id,$sidebar);
          $installed_sidebars[$id]=$sidebar_id;
       } 
   }
   
   // web setting
   if(file_exists($path.'setting.php')) {
       require_once($path.'setting.php');
       if(isset($web_setting) && is_array($web_setting)) {
          foreach($web_setting as $key=>$val) {
              update_option( $key, $val );
          }
       }
   }
   
   // web installed info
   $ve_installed_web=array(
      'web_theme'=>$web_to_install,
      'pages'=>$installed_pages,
      'posts'=>$installed_posts,
      'menus'=>$installed_menus,
      'sidebars'=>$installed_sidebars,
      'contents'=>$installed_contents,
   );
   update_option( 've_installed_web', $ve_installed_web );
   
   wp_redirect( home_url() );   
   die();
   
}
function install_page($id, $path, $color_set, $installed) {
  global $vePage;
  $post_id='';

  require_once($path.'pages/'.$id.'.php');  
  if(isset($page['layer'])) {
      $layer=$page['layer'];
      $setting=$page['setting'];
  }
  else {
      global $cms;
      $temp=explode("/",$page['page']['theme']); 
      require_once($vePage->get_template_dir($temp[0]).$cms->p_templates[$temp[0]]['path'].$temp[1].'/config.php');
      $layer=$config['layer'];
      $setting=$config['setting'];
  }
  
  if((!isset($page['page']['page_type']) || !$page['page']['page_type']=='home_blog') || (isset($page['page']['page_type']) && $page['page']['page_type']=='blog')) {
      $new_post = array(
            'post_title' => $page['page']['title'],   
            'post_name' => $page['page']['slug'],
            'post_parent' => (isset($page['page']['parent']))? $installed[$page['page']['parent']] : 0,
            'post_content' =>  $layer,
            'post_status' => 'publish',
            'post_type'=>'page',
      );
      
      $post_id=$vePage->save_new_page($new_post, $page['page']['theme'], $layer);
  }
  
  if(isset($page['page']['page_type'])) {
        if($page['page']['page_type']=='blog' || $page['page']['page_type']=='home_blog') { 
              global $wpdb;
              $wpdb->query("DELETE FROM " . $wpdb->prefix . "ve_posts_layer WHERE vpl_post_id=0 AND vpl_type='blog'");
              $vePage->save_layer(0, 'blog', $layer);
        }
  }
  if(!empty($setting)) {
      foreach($setting as $key=>$val) {
          update_post_meta( $post_id, $key, $val );
      }
  } 
  return $post_id;  
  
}
function install_post($id, $new_post) {
  global $vePage;

  if(!isset($new_post['title'])) $new_post['title']=__('Název článku','cms_ve');
  if(!isset($new_post['content'])) $new_post['content']='Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean eu imperdiet neque, eget volutpat enim. Donec tellus est, dictum sed eros id, condimentum aliquam metus. Quisque a auctor nisi. Nam tristique hendrerit lectus, non sollicitudin neque porta sed. Mauris ac bibendum diam, eu posuere sem. Nunc libero nulla, bibendum vel accumsan a, pulvinar condimentum urna. Vivamus eu neque in tellus fringilla viverra. Suspendisse sit amet arcu posuere, faucibus est in, aliquam eros. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Phasellus sed urna finibus eros elementum aliquet.

Aliquam erat volutpat. Duis suscipit vehicula dolor, ut molestie ex lacinia eget. Vivamus maximus, eros nec malesuada convallis, lacus eros congue nisi, vel vulputate dolor magna vel nisl. Pellentesque ut risus at eros feugiat porttitor. Fusce pharetra libero sed hendrerit dapibus. Maecenas in risus laoreet lectus fermentum tempor eget vitae eros. Donec id mauris id sapien commodo efficitur. Morbi vitae auctor odio.';
  
  $new_post_args = array(
        'post_title' => $new_post['title'],   
        'post_name' => $id,
        'post_content' =>  $new_post['content'],
        'post_status' => 'publish',
        'post_type'=>'post',
  );
  
  $post_id = wp_insert_post($new_post_args);  
  
  if(isset($new_post['image']) && $new_post['image']) {
      update_post_meta( $post_id, '_thumbnail_id', $new_post['image'] );
  }

  return $post_id;  
  
}
function install_content($id, $path, $color_set) {
  global $vePage;

  require_once($path.'content_blocks/'.$id.'.php');  

  if(isset($page['layer'])) {
      $layer=$page['layer'];
      $setting=$page['setting'];
  }
  else {
      global $cms;
      $temp=explode("/",$page['page']['theme']); 
      require_once($vePage->get_template_dir($temp[0]).$cms->p_templates[$temp[0]]['path'].$temp[1].'/config.php');
      $layer=$config['layer'];
      $setting=$config['setting'];
  }
  
  $new_post = array(
            'post_title' => $page['page']['title'],
            'post_status' => 'publish',
            'post_type'=>$page['page']['post_type'],
            'post_author' => 1, 
  );
  $c_id=$vePage->save_new_window_post($new_post, $page['page']['theme'],$layer,$page['page']['post_type']); 
  
  if(!empty($setting)) {
      foreach($setting as $key=>$val) {
          update_post_meta( $post_id, $key, $val );
      }
  } 
  return $c_id;  
  
}
function install_create_menu($name,$i=0)  {
    $new_name=($i>0)? $name.'_'.$i:$name;
    $menu_exists = wp_get_nav_menu_object( $new_name );
    if( !$menu_exists){
        $menu_id = wp_create_nav_menu($new_name);
    }
    else $menu_id = $this->install_create_menu($name,$i+1);
    
    return $menu_id;
}
function install_menu($menu_setting, $pages, $menu_id=0) {

    if(!$menu_id) $menu_id = $this->install_create_menu($menu_setting['name']); 

    if(isset($menu_setting['items']) && is_array($menu_setting['items'])) {

          $installed_items = array();
          
          $i=1;
          foreach($menu_setting['items'] as $key=>$menu_item) {
              $item=array();
              if($menu_item['type']=='link') {
                      $item['menu-item-type']='custom';
                      $item['menu-item-object']='custom';
                      $item['menu-item-object-id']='0';
                      $item['menu-item-url']=$menu_item['link'];
                  } else {
                      $item['menu-item-type']='post_type';
                      $item['menu-item-object']='page';
                      $item['menu-item-object-id']=$pages[$menu_item['page']];
                  }
                  if(isset($menu_item['target']) && $menu_item['target']) $item['menu-item-target']='_blank';
                  $item['menu-item-title'] = (isset($menu_item['title']))? $menu_item['title']:'';
                  $item['menu-item-parent-id']=(isset($menu_item['parent']) && $menu_item['parent'])? $installed_items[$menu_item['parent']]:0;
                  $item['menu-item-position'] = (isset($menu_item['order']))? $menu_item['order']:$i;
                  $item['menu-item-status']='publish';
                  $new_item_id = wp_update_nav_menu_item( $menu_id, 0, $item );
                  
                  $installed_items[$key]=$new_item_id;

              $i++;

          }

    } 
    return $menu_id;
}

function install_sidebar($sidebar_id, $sidebar, $i=1) {
    $new_id=($i>1)? $sidebar_id.'_'.$i:$sidebar_id;
    $new_name=($i>1)? $sidebar['name'].' '.$i:$sidebar['name'];
    $sidebars=get_option( 'cms_sidebars' );
    $exist=false;
    if(is_array($sidebars)) {
        foreach($sidebars as $sid) {
            if($sid['id']==$new_id) $exist=true;  
        }
    }
    if($exist) {
        $new_id=$this->install_sidebar($sidebar_id, $sidebar, $i+1);
    }
    else {
        $sidebars[]= array(
            'name' => $new_name,
            'id' => $new_id,
            'description' => $sidebar['desc'],
        );
        update_option('cms_sidebars',$sidebars); 
        
        if(isset($sidebar['widgets'])) {
            foreach($sidebar['widgets'] as $wid=>$widget) {
                $this->install_widget($widget, $wid, $new_id);    
            }
        }
     } 
     return $new_id;
}
function install_widget($widget, $type, $sidebar_id) {
    $active_sidebars = get_option( 'sidebars_widgets' ); 
    $widget_options = get_option( 'widget_'.$type );
    $widget_options[] = $widget;
    $widget_keys=array_keys($widget_options);
    $new_id=array_pop($widget_keys);    
    $active_sidebars[$sidebar_id][] = $type.'-'.$new_id; //add a widget to sidebar
    update_option('widget_'.$type, $widget_options); //update widget default options
    update_option('sidebars_widgets', $active_sidebars); //update sidebars
}


}
