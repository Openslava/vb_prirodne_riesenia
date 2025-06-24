<?php
class FapiHelpers
{
	public static function getClientFields($fapiUsername, $fapiPassword, $email)
	{
		$fapiClient = new FAPIClient($fapiUsername, $fapiPassword);
		$clients = $fapiClient->client->search(array('email' => trim($email)));

			if (!$clients['clients']) {
      return null;
      }
    
    $client = $clients['clients'][0];

		return array(
			'name' => !empty($client['first_name']) ? $client['first_name'] : null,
			'surname' => !empty($client['last_name']) ? $client['last_name'] : null,
			'email' => !empty($client['email']) ? $client['email'] : null,
			'mobil' => !empty($client['phone']) ? $client['phone'] : null,
			'street' => !empty($client['address']['street']) ? $client['address']['street'] : null,
			'city' => !empty($client['address']['city']) ? $client['address']['city'] : null,
			'postcode' => !empty($client['address']['zip']) ? $client['address']['zip'] : null,
			'state' => !empty($client['address']['country']) ? $client['address']['country'] : null,
			'company' => !empty($client['company']) ? $client['company'] : null,
			'ic' => !empty($client['ic']) ? $client['ic'] : null,
			'dic' => !empty($client['dic']) ? $client['dic'] : null,
		);
	}

	public static function escapeJs($s)
	{
		// Based on method Latte\Runtime\Filters::escapeJs() from Nette Framework.
		// Copyright (c) 2004 David Grudl (http://davidgrudl.com)
		// Licensed under the New BSD License.
		$json = json_encode($s);
		return str_replace(array("\xe2\x80\xa8", "\xe2\x80\xa9", ']]>', '<!'), array('\u2028', '\u2029', ']]\x3E', '\x3C!'), $json);
	}
}





function after_save_connection() {
    if($_POST['ve_connect_fapi']['login']!=$_POST['ve_connect_fapi']['connection']['login'] || $_POST['ve_connect_fapi']['password']!=$_POST['ve_connect_fapi']['connection']['password']) {

        $validCredentials = is_fapi_connected($_POST['ve_connect_fapi']['login'], $_POST['ve_connect_fapi']['password']);

        $save=$_POST['ve_connect_fapi'];
        $save['connection']['status']=$validCredentials;
        $save['connection']['login']=$_POST['ve_connect_fapi']['login'];
        $save['connection']['password']=$_POST['ve_connect_fapi']['password'];
        update_option('ve_connect_fapi',$save);
        $_POST['ve_connect_fapi']=$save;
    }
} 
function is_fapi_connected($login,$password) {
    require_once dirname(__FILE__) . '/FAPIClient.php';
    $fapiClient = new FAPIClient($login,$password);
    try {
        $fapiClient->checkConnection();
        $validCredentials = 1;
    } catch (Exception $e) {
        $validCredentials = 0;
    }
    return $validCredentials;
}

function field_type_fapi_form_select($field, $meta, $group_id) {
    $content=($meta!="") ? $meta : ((isset($field['content']))? $field['content']:"");
    $login=get_option('ve_connect_fapi');
    $select='';
    if(isset($login['connection']['status']) && $login['connection']['status']) {
        $select=get_fapi_forms($login, $group_id.'['.$field['id'].']', $group_id.'_'.$field['id'], $content); 
    }
    
    if(!$select) {
        echo '<div id="fapi_connection_container">';
        generate_fapi_connection_form($login, $group_id.'['.$field['id'].']', $group_id.'_'.$field['id']);
        echo '</div>';
    }
    else echo $select;
}

function get_fapi_forms($login, $name, $id, $content='') {
    require_once dirname(__FILE__) . '/FAPIClient.php';
    $form = false;
    $fapiClient = new FAPIClient($login['login'], $login['password']);
    try {
        $fapiClient->checkConnection();
        $forms = $fapiClient->form->getAll(); // vrací pole formulářů
        $options='';
        foreach ($forms as $option) {
            $options='<option value=\''.$option['html_code_without_style'].'\' '. (str_replace('&','&amp;',stripslashes($content)) == $option['html_code_without_style'] ? ' selected="selected"' : ''). '>'. $option['name'].'</option>'.$options;
        }
        $form='<select name="'.$name.'" id="'.$id.'">'.$options.'</select>';
    } catch (Exception $e) {
    }

    if($form===false)
        $form = '<div class="cms_error_box">'._('Seznam formulářů z FAPI se nepodařilo načíst. Opakujte pokus později.', 'cms_ve').'</div>';
    return $form;
}

function generate_fapi_connection_form($login, $name, $id, $error=0) {
    echo '<div class="cms_connection_form">';
    if($error==0) echo '<div class="cms_error_box">'.__('Pro vypsání seznamu formulářů jen nutné níže zadat vaše přihlašovací údaje do FAPI. Tuto akci provedete pouze jednou.','cms_ve').'</div>';
    if($error==1) echo '<div class="cms_error_box">'.__('Nepodařilo se přihlásit do FAPI. Zkontrolujte, zda jste zadali přihlašovací údaje správně.','cms_ve').'</div>';
    ?>
    <div class="cms_connection_form_in">
    <input type="text" class="cms_cf_text" name="fapi_login" id="fapi_login" value="<?php echo $login['login']; ?>" />
    <input type="password" class="cms_cf_text" name="fapi_password" autocomplete="off" id="fapi_password" value="<?php echo $login['password']; ?>" />
    <button id="add_fapi_connection" data-tagid="<?php echo $id; ?>" data-name="<?php echo $name; ?>" class="cms_button"><?php echo __('Uložit přístupy','cms_ve'); ?></button>
    </div>
    <?php
    echo '</div>';
}

function fapi_save_connection() {
    $save['login']=$_POST['fapi_login'];
    $save['password']=$_POST['fapi_password'];
    $save['connection']['login']=$_POST['fapi_login'];
    $save['connection']['password']=$_POST['fapi_password'];
    
    $status=is_fapi_connected($_POST['fapi_login'], $_POST['fapi_password']);
    $save['connection']['status']=$status;
    
    update_option('ve_connect_fapi',$save);
    
    if($status) echo get_fapi_forms($save, $_POST['tag_name'], $_POST['tag_id']); 
    else generate_fapi_connection_form($save, $_POST['tag_name'], $_POST['tag_id'],1);
    die();
}
add_action('wp_ajax_fapi_save_connection', 'fapi_save_connection');

function fapi_save_simple_connection() {
    $save['login']=$_POST['fapi_login'];
    $save['password']=$_POST['fapi_password'];
    $save['connection']['login']=$_POST['fapi_login'];
    $save['connection']['password']=$_POST['fapi_password'];

    $status=is_fapi_connected($_POST['fapi_login'], $_POST['fapi_password']);

    $save['connection']['status']=$status;
    
    if($status) {
        update_option('ve_connect_fapi',$save);
        $res = json_encode(array(
  				'status' => 1,
  				'text' => __('Spojení s FAPI bylo úspěšné', 'mwshop'),
  			));
    }
    else {
        $res = json_encode(array(
  				'status' => 0,
  				'text' => __('Zadané přihlašovací jméno a api klíč nejsou správné. Nepodařilo se spojit s FAPI', 'mwshop'),
  			));
    }
    wp_send_json($res);
    die();
}
add_action('wp_ajax_fapi_save_simple_connection', 'fapi_save_simple_connection');