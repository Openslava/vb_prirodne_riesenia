<?php

global $apiConnection;
$apiConnection = New mioWebApiConnection(); 

class mioWebApiConnection {

    /**
     * Array of all available APIs, indexed by name of the subfolders. Each entry must be an name-indexed array
     * with following structure <code>(name='Plugin name', fields=bool)</code>,
     * where "name" states for name of the plugin (visible in fronteend), "fields" has <code>true</code> if API supports
     * retrieve of fields in forms.
     *
     * When a plugin is loaded by a call to {@link getClient()} , additional values are prepended to the plugin's info.
     *
     * All data, that should be available before specific plugin is loaded and used, must be specified here.
     *
     * @var array [apiId=[name,fields], apiID=[name,fields]...]
     */
    var $api_list=array(
        'se'=>array('name'=>'SmartEmailing','fields'=>true),
        'getresponse'=>array('name'=>'GetResponse','fields'=>false),
        'mailchimp'=>array('name'=>'MailChimp','fields'=>true),
        'aweber'=>array('name'=>'AWeber','fields'=>false),
    );

    /**
     * @var string      If the last API call failed, than this contains detailed error message. It can contain
     *      semi-technical information. Useful for troubleshooting.
     */
    public $lastError = '';

    public function clearLastError() {
        $this->lastError = '';
    }

    /** Returns last error message of the last API call ({@link $lastError}). */
    public function getLastError() {
        return $this->lastError;
    }

    /**
     * This call is used at the begining of a plugin file to register it into the MioWeb API.
     *
     * Passed $className must inherit from {@link mioWebEmailClientApiBase}.
     * @param $id string            Mioweb API ID = pluging subfolder's name
     * @param $className string     Name of the implementing class in the plugin file, ancestor of {@link MioWebApiBridgeBase}
     */
    function registerApiClass($id, $className) {
        $sampleClass = new $className();
        $this->api_list[$id]['name'] = $sampleClass::getApiName();
        $this->api_list[$id]['classname'] = $className;
        unset($sampleClass);
    }

    /**
     * Entry point to work with a specific plugin. This call loads the plugin and initializes necessary structures.
     *
     * @param $apiId string         Name of tha API to be initiated, so called "apiId" (se, getresponse...).
     * @return MioWebApiBridgeBase  New instance of the plugin class. On failure terminates PHP execution.
     */
    function getClient($apiId) {
        require_once(__DIR__.'/'.$apiId.'/class.php');
        $className = $this->api_list[$apiId]['classname'];
        //Kontrola pro pripad, ze nekdo zapomente zaregistrovat tridu nebo pokud je registrace spatna.
        if (!class_exists($className)) {
            exit("API class for [$apiId] is missing. Is it registered?");
        }
        return new $className();
    }

    /**
     * Creates new instance of the API managing class. Hooks several filters into Wordpress (ajax calls, after save...).
     */
    function __construct(){
        add_action('wp_ajax_mioweb_api_save_connection', array($this,'save_api_connection'));
        add_action('wp_ajax_mw_api_change_selector', array($this,'api_change_selector'));
        if(isset($_POST["ve_connect_se"])) {
            add_action( 'init', array($this,'after_save_connection') );  
            add_action( 've_after_save_options', array($this,'after_save_connection') ); 
        } 
    }

    function get_connection($api) {
        $connect=get_option('ve_connect_se');
        if($api=='se') $option_name='connection';
        else $option_name=$api.'_connection';
        if(isset($connect[$option_name])) return $connect[$option_name];
        else return false;

    }

    /**
     * Generates HTML form to set up an API connection info.
     *
     * @param array|false $login    Current authorization values if present (as "login" and "password" indexes) or false.
     * @param string $el_name       Form element name (e.g. "ve[content]").
     * @param string $el_id         Form element ID (e.g. "ve_content").
     * @param string $api           ID of the API (e.g. "mailchimp").
     * @param int $type             Value for 'data-type' attribute of the HTML button element (e.g. "forms").
     * @param int $error            Identifies purpose of the form generation: 0=first connection/no credentials, 1=invalid credentials
     */
    function generate_connection_form($login, $el_name, $el_id, $api, $type, $error=0) {
        echo '<div class="cms_connection_form">';
            if($error==0) echo '<div class="cms_error_box">'.__('Nejdříve je nutné propojit tento systém s MioWebem. Zadejte, prosím, údaje pro přístup do API.','cms').'</div>';
            if($error==1) echo '<div class="cms_error_box">'.__('Nepodařilo se přihlásit. Zkontrolujte, zda jste zadali přihlašovací údaje správně.','cms').'</div>';
            ?>
            <div class="cms_connection_form_in">
                <input type="text" class="cms_cf_text api_login" name="api_login" placeholder="Email" value="<?php echo $login['login']; ?>" />
                <input type="password" class="cms_cf_text api_password" name="api_password" placeholder="API token" autocomplete="off" value="<?php echo $login['password']; ?>" />
                <button class="add_api_connection cms_button" data-type="<?php echo $type; ?>" data-api="<?php echo $api; ?>" data-tagid="<?php echo $el_id; ?>" data-name="<?php echo $el_name; ?>"><?php echo __('Uložit přístupy','cms'); ?></button>
            </div>
            <?php
        echo '</div>';
    }

    function after_save_connection() {
      if(isset($_POST['ve_connect_se'])) {
        foreach($this->api_list as $api=>$val) {
        
            $pre=($api!='se')? $api.'_':'';
        
            if(@$_POST['ve_connect_se'][$pre.'login']!=$_POST['ve_connect_se'][$pre.'connection']['login'] || $_POST['ve_connect_se'][$pre.'password']!=$_POST['ve_connect_se'][$pre.'connection']['password'] || !$_POST['ve_connect_se'][$pre.'connection']['status']) {
            
                $client = $this->getClient($api);

                $newLogin = empty($_POST['ve_connect_se'][$pre.'login']) ? '' : $_POST['ve_connect_se'][$pre.'login'];
                $newPassword = empty($_POST['ve_connect_se'][$pre.'password']) ? '' : $_POST['ve_connect_se'][$pre.'password'];

                //For oAuth and similar method first authorization requires a special care.
                if ($client->first_authorize($newLogin, $newPassword))
                    $newStatus = $client->is_connected($newLogin, $newPassword);
                else
                    $newStatus = false;

                $save=$_POST['ve_connect_se'];
                $save[$pre.'connection']['status']=$newStatus;
                $save[$pre.'connection']['login']=$newLogin;
                $save[$pre.'connection']['password']=$newPassword;
                //Save possible error from authorization.
                $save[$pre.'connection']['connect_error']=($newStatus ? '' : $client->getLastError());

                //TODO FIX?? After successful authorization settings and status are saved as the option. Correctly.
                //  But afterwards in an undetected moment following two options appear in the root level of the
                //  option with the old values.
                $save[$pre.'login']=$newLogin;
                $save[$pre.'password']=$newPassword;


                update_option('ve_connect_se',$save);
                $_POST['ve_connect_se']=$save;
            }
        }
      }


    }
    // save connection from form inside element

    function save_api_connection() {

        $api=$_POST['api'];
        $pre=($api!='se')? $api.'_':'';

        $save=get_option('ve_connect_se');

        $save[$pre.'login']=$_POST['login'];
        $save[$pre.'password']=$_POST['password'];
        $save[$pre.'connection']['login']=$_POST['login'];
        $save[$pre.'connection']['password']=$_POST['password'];

        $client = $this->getClient($api);
        $status=$client->is_connected($_POST['login'], $_POST['password']);

        $save[$pre.'connection']['status']=$status;

        update_option('ve_connect_se',$save);
        if($status) {
            $this->api_object_selector($_POST['tag_name'], $_POST['tag_id'], '', $api, $_POST['type']);
        }
        else $this->generate_connection_form(array('login'=>$_POST['login'],'password'=>$_POST['password']), $_POST['tag_name'], $_POST['tag_id'],$api,$_POST['type'],1);
        die();
    }
    function get_forms_list($api,$login) {
        $client = $this->getClient($api);
        $result = $client->get_forms_list($login);
        $this->lastError = $client->getLastError();
        return $result;
    }

    function get_lists_list($api,$login) {
        $client = $this->getClient($api);
        $result = $client->get_lists_list($login);
        $this->lastError = $client->getLastError();
        return $result;
    }

    function generate_api_select($name, $id, $value, $field, $type) {
        
        $content=array();
        
        if(!is_array($value)) $content['id']=$value;
        else $content=$value;

        if(!isset($content['api'])) $content['api']='se';
        if(!isset($content['id'])) $content['id']='';
        
        if($type=='forms') {
            $text1=__('Získat formulář z','cms');
            $text2=__('Seznam formulářů','cms');
        } else {
            $text1=__('Získat seznamy z','cms');
            $text2=__('Seznamy','cms');
        }
        ?>
                
        <div class="mw_api_connection_container">
            <div class="float-setting">
                <div class="sublabel"><?php echo $text1; ?></div>       
                <?php $this->api_selector($name, $id, $content, $field, $type); ?>
            </div>
            <div class="float-setting">
                <div class="sublabel"><?php echo $text2; ?></div>  
                <div class="mw_api_selector_container">
                    <?php $this->api_object_selector($name, $id, $content['id'], $content['api'], $type); ?>
                </div>
            </div>
            <div class="cms_clear"></div>
        </div>
        
        <?php
        
        if($content['api']!='se') {
            echo '<style>.form_look_setting {display: none;}</style>';
        }
    }
    
    function api_object_selector($name, $id, $content, $api, $type) {
    
        $login=$this->get_connection($api);
                
        if(isset($login['status']) && $login['status']) { 
            
              if($type=='forms') $obj=$this->get_forms_list($api, $login);
              else $obj=$this->get_lists_list($api, $login);
              
              if($obj) {
                  $options=''; 
                              
                  foreach ($obj->item as $option) {
                        $options='<option value=\''.$option->id.'\' '. ($content == $option->id ? ' selected="selected"' : ''). '>'. $option->name. '</option>'.$options;
                  }
                  
                  echo '<select name="'.$name.'[id]" id="'.$id.'_id">'.$options.'</select>'; 
              }
              else {
                  $form='<div class="cms_error_box">'.__("Služba je dočasně nedostupná. Prosím, zkuste to později znovu.",'cms_ve').'</div>';
                  if ($this->getLastError())
                      $form = $form.'<div class="cms_error_box_detail">' . $this->getLastError() .'</div>';
                  echo $form;
              }

        }
        else {
            echo '<div class="api_connection_container">';
            $this->generate_connection_form($login, $name, $id, $api, $type);
            echo '</div>';
        }
    
    }
    
    function api_change_selector() {
        $this->api_object_selector($_POST['tag_name'], $_POST['tag_id'], '', $_POST['api'], $_POST['type']); 
        die();
    }

    
    function api_selector($name, $id, $content, $field, $type) {
        if(count($this->api_list)>1) {
        
            // back compatibility (temporary)
            if(!isset($content['api'])) {
                if(isset($content['id'])) $val='se';
                else {
                    foreach($this->api_list as $key => $api) {
                        $login=$this->get_connection($key);   
                        if(isset($login['status']) && $login['status']) 
                            $val=$key;
                    }
                }
            }
            else $val=$content['api'];
            // end temporary
        
            $select='<select class="change_api_selector" name="'.$name.'[api]" id="'.$id.'_api" data-type="'.$type.'" data-name="'.$name.'" data-id="'.$id.'">'; 
            foreach ($this->api_list as $key => $api) {
                $select.='<option value="'.$key.'" '. ($val == $key ? ' selected="selected"' : ''). '>'. $api['name']. '</option>';
            }
            $select.='</select>'; 
        } else {
            $select='<input type="hidden" name="'.$name.'[api]" value="se" />';
        }            
        echo $select;
    }
    
    function get_form($content, $edit_mode) {
    
        $api=$content['api'];
        $form_id=$content['id'];

        $this->clearLastError();
        $client = $this->getClient($api);       
        
        $form=array();  

        // cached variant
        if($this->api_list[$api]['fields']) {
        
            $cached_form=get_option('mioweb_'.$api.'form_'.$form_id);

            if($edit_mode) delete_transient( 'mioweb_'.$api.'form_'.$form_id.'transient' ); 
            if(get_transient( 'mioweb_'.$api.'form_'.$form_id.'transient') && $cached_form) {
                $form=$cached_form;
            }
            else {  
                    $form=$client->get_form($form_id);   
                    if(!empty($form)) {
                        update_option('mioweb_'.$api.'form_'.$form_id,$form);
                        if(!$edit_mode) set_transient( 'mioweb_'.$api.'form_'.$form_id.'transient', 1, 60*5 );
                    } else if($cached_form) {
                        $form=$cached_form;
                        if(!$edit_mode) set_transient( 'mioweb_'.$api.'form_'.$form_id.'transient', 1, 60*5 );
                    }   
                    
            }  
        
        } 
        // not cached variant
        else {
            $form = $client->get_form($form_id);
            $this->lastError = $client->getLastError();
        }
         
        return $form;
    }
    
    function print_form($api,$element,$form,$css_id, $added) {
        $client = $this->getClient($api);
        $result = $client->print_form($element,$form,$css_id, $added);
        $this->lastError = $client->getLastError();
        return $result;
    }
    
    function repair_content_val($val) {  
        if(!is_array($val)) {
            $old_content=$val;
            $val=array();
            $val['id']=$old_content;  
        }  
        if(!isset($val['api'])) $val['api']='se';
        return $val;
    }
}

function field_type_form_select($field, $meta, $group_id) {
    $content=($meta!="") ? $meta : ((isset($field['content']))? $field['content']:'');
    global $apiConnection;
    
    $apiConnection->generate_api_select($group_id.'['.$field['id'].']',$group_id.'_'.$field['id'],$content, $field, 'forms');
}

function field_type_list_select($field, $meta, $group_id) {
    global $apiConnection;
    $content=($meta!="") ? $meta : ((isset($field['content']))? $field['content']:'');

    $apiConnection->generate_api_select($group_id.'['.$field['id'].']',$group_id.'_'.$field['id'],$content, $field, 'lists');    
}

// field type connection control
function field_type_connection_control($field, $meta, $group_id) {
    $content=($meta!="") ? $meta : ((isset($field['content']))? $field['content']:"");
    if(isset($content['status']) && $content['status'])
        echo '<span class="cms_status_valid">'.__('Připojeno','cms').'</span>';
    else {
        echo '<span class="cms_status_invalid">' . __('Nepřipojeno', 'cms') . '</span>';
        if (isset($content['connect_error']) && $content['connect_error'])
            echo '<div class="cms_info_box_gray">' . $content['connect_error'] . '</div>';
    }
    echo '<input type="hidden" name="',$group_id,'[',$field['id'],'][status]" id="',$group_id,'_', $field['id'],'" value="'.$content['status'].'" />';
    echo '<input type="hidden" name="',$group_id,'[',$field['id'],'][login]" id="',$group_id,'_', $field['id'],'" value="'.$content['login'].'" />';
    echo '<input type="hidden" name="',$group_id,'[',$field['id'],'][password]" id="',$group_id,'_', $field['id'],'" value="'.$content['password'].'" />';
}

// field type - authorization link/button to authorize external API (Aweber)
function field_type_authorize_api($field, $meta, $group_id) {
    $content=($meta!="") ? $meta : ((isset($field['content']))? $field['content']:"");
    echo '<a class="cms_authorization_link" href="'.$content.'" target="_blank">'.__('Vygenerovat autorizační kód.','cms').'</a>';
//    echo '<span class="cms_authorization_link_desc">'.__('Autorizujte přístup k Vašemu účtu. Vygenerovaný kód zkopírujte do pole níže.','cms') .'</span>';
}


/**
 * Base class for external emailing API integrations. Derived classes implements overrides functions. Class defines
 * the contract between MioWeb and API integration.
 *
 * Each API is identified by an <b>ApiID</b>. This ApiId is used in global list of supported APIs in
 * <code>$apiConnection->api_list</code>. Each API implementation class resides in file <code>(ApiId)/class.php</code>.
 *
 * @package mw-lib-api
 * @author Jakub Konas
 * @version 1.0
 * @abstract class MioWebApiBridgeBase
 */
class MioWebApiBridgeBase {
    /**
     * @var string Last error message from the latest API call.
     */
    protected $lastError = '';

    /**
     * Clears the last API call error message.
     *
     * This method should be called at the begining of every API call implementing method.
     */
    public function clearLastError() {
        $this->lastError = '';
    }

    /**
     * Returns the error message of the latest API call. If no error is present then it contains empty string.
     * @return string Error message or empty string.
     */
    public function getLastError() {
        return $this->lastError;
    }

    public function setLastError($errorMsg) {
        $this->lastError = $errorMsg;
    }

    function __construct() {
    }

    /** Returns full API name to present in UI. */
    public static function getApiName() {
        return "Example API name";
    }

    /** Returns API remote end-point. */
    protected function getApiUrl() {
        return "https://example.com";
    }

    /**
     * Special method called at the moment, when authorization credentials for the API change. In this method an
     * instance of the API can make any further processing to complete authorization. Typical example is to complete
     * sofisticated protocols like openAuth.
     *
     * This method can update passed arguments, e.g. replace preauthorization tokens to finite access tokens.
     *
     * @param $login string         Login credential
     * @param $password string      Password, API key, (pre)authorization token.
     * @return bool                 Returns boolean value, <code>true</code> means success.
     */
    public function first_authorize(&$login, &$password) {
        return true;
    }

    /**
     * Checks wheather passed credentials are valid and communication with a remote API can be established.
     *
     * @param $login string     Login name
     * @param $password string  Password or API key
     * @return int              On success <code>1</code> is returned, otherwise <code>0</code>.
     */
    public function is_connected($login,$password) {}

    /**
     * Returns list of all available forms.
     *
     * @param $login array      Array with fields "login" and "password". Value of "password" can has meaning of API key, depending on the API.
     * @return bool|object      On success returns and object with a single field "item". This field is an array containing
     *      all available information about all forms. Each form is an array containing at least
     *      fields "name" and "id".
     */
    public function get_forms_list($loginPassword) {}

    /**
     * Returns data of a form. This will be printed when embedding form using {@link print_form()} call.
     *
     * Login information are retrieved from the <i><code>get_option('ve_connect_se')</code></i> result array,
     * fields <i><code>(apiId)_login</code></i> and <i><code>(apiId)_password</code></i>.
     *
     * @param string $id Unique identifier of the form. Available forms can be retrieved by a {@link get_forms_list()} call.
     * @return array|bool|string Returns <code>false</code> on failure. On success a compatible input for method {@link print_form()}
     *      is returned. For API that supports form-fields enumeration an array with fields (fields, submit, url) is returned.
     *      For APIs with direct embedding using javascript or HTML form generated on a remote server other specific data
     *      is returned.
     */
    public function get_form($id) {}

    /**
     * Counter-part of {@link get_form()} method. This method receives a pregenerated form data or its specification through a $form
     * parameter and prints it on output.
     *
     * Login information are retrieved from the <i><code>get_option('ve_connect_se')</code></i> result array,
     * fields <i><code>(apiId)_login</code></i> and <i><code>(apiId)_password</code></i>.
     *
     * @param $element          VE form element ??? //TODO really?
     * @param array $form       Array of form data returned by the {@link get_form()} call or some other specific data.
     * @param string $css_id    CSS id of the element.
     * @return string           On success returns HTML code of the form.
     */
    public function print_form($element, $form, $css_id, $added) {}

    /**
     * Returns data of a list.
     *
     * Login information are retrieved from the <i><code>get_option('ve_connect_se')</code></i> result array,
     * fields <i><code>(apiId)_login</code></i> and <i><code>(apiId)_password</code></i>.
     *
     * @param string $id        Unique identifier of a list. Available lists can be retrieved by a {@link get_lists_list()} call.
     * @param bool $withDetails Include all details for contacts.
     * @param array $fieldsDef  Definition of requested field. This must contain items as arrays with indexes
     *                          <code>(name, fieldId, outputName, isCustom)</code>, where
     *                          "name" is the caption, "fieldId" is ID of the field according to API,
     *                          "outputName" is the index in the output array, "isCustom" tells whether
     *                          the source field shoud be found within custom or basic fields.
     * @return array|bool Returns <code>false</code> on failure. On success an array of contacts
     *                          is returned.
     */
    public function get_list($id, $withDetails, $fieldsDef) {
        $this->lastError = __('Funkce pro získání kontaktů ze seznamu není implementována.', 'cms_ve');
        return false;
    }

    /**
     * Returns one contact, optionally with additional details.
     *
     * Login information are retrieved from the <i><code>get_option('ve_connect_se')</code></i> result array,
     * fields <i><code>(apiId)_login</code></i> and <i><code>(apiId)_password</code></i>.
     *
     * @param string $contactId Unique identifier of a contact. Available contact IDs in a list can be retrieved by
     *                   a {@link get_list()} call.
     * @param bool $withDetails Include all details for contacts.
     * @param $withDetails
     * @return array|bool Returns <code>false</code> on failure. On success an array with contact details is returned.
     */
    public function get_contact($contactId, $withDetails) {
        $this->lastError = __('Funkce pro získání údaju kontaktu není implementována.', 'cms_ve');
        return false;
    }

    /**
     * Get definition of custom fields. Returned value is API specific. On error <code>false</code> is returned and
     * {@link lastError} is set.
     *
     * @return mixed|false
     */
    public function get_customfields_structure() {
        $this->lastError = __('Funkce pro získání definice uživatelských polí není implementována.', 'cms_ve');
        return false;
    }

    /**
     * Returns a list of all contact lists.
     *
     * Login information are retrieved from parameter <code>$login</code>. <i><code>get_option('ve_connect_se')</code></i> result array,
     * fields <i><code>(apiId)_login</code></i> and <i><code>(apiId)_password</code></i>.
     *
     * @param $login array      Array with fields <i><code>login</code></i> and <i><code>password</code></i>.
     * @return bool|object      On success returns and object with a single field "item". This field is an array containing
     *      all available information about all lists. Each list item is an array containing at least
     *      fields "name" and "id".
     */
    public function get_lists_list($login) {}

    /**
     * Stores a new mailing contact into specified list.
     *
     * Login information are retrieved from the <i><code>get_option('ve_connect_se')</code></i> result array,
     * fields <i><code>(apiId)_login</code></i> and <i><code>(apiId)_password</code></i>.
     *
     * @param $listId string    ID of the list where the contact should be stored.
     * @param $email string     Email address of the new contact.
     * @return bool             On success <code>true</code> is returned, otherwise <code>false</code>.
     */
    public function save_to_list($listId, $email) {}

    /**
     * Stores a new mailing contact with details into specified list. Supported fields depends on the list instance.
     *
     * Login information are retrieved from the <i><code>get_option('ve_connect_se')</code></i> result array,
     * fields <i><code>(apiId)_login</code></i> and <i><code>(apiId)_password</code></i>.
     *
     * @param $listId               string    ID of the list where the contact should be stored.
     * @param $email                string     Email address of the new contact.
     * @param array $contactDetails Details of the new contact as an associative array, eg. <code>array("name" => "Jan Novak")</code>.
     * @param array $customFields   Optional custom fields.
     * @return bool On success <code>true</code> is returned, otherwise <code>false</code>.
     */
    public function save_to_list_details($listId, $email, $contactDetails=array(), $customFields=array()) {}

    /**
     * Retrieves latest added contact into specified list. Time consuming method!
     *
     * Login information are retrieved from the <i><code>get_option('ve_connect_se')</code></i> result array,
     * fields <i><code>(apiId)_login</code></i> and <i><code>(apiId)_password</code></i>.
     *
     * @param $listId string    ID of the list
     * @return array            On success an array with fields 'name', 'time', 'count' is returned. On error all field
     *      values are empty (empty string, 0, 0).
     */
    public function get_last_enter($listId) {}


    /**
     * Returns number of contacts in the specified list.
     *
     * Login information are retrieved from the <i><code>get_option('ve_connect_se')</code></i> result array,
     * fields <i><code>(apiId)_login</code></i> and <i><code>(apiId)_password</code></i>.

     * @param $listId string    ID of the list
     * @return int|bool         On success returns number of contacts in the list. On error <code>false</code> is returned.
     */
    public function get_list_count($listId) {}

}

/**
 * Converts and array into an object. Indexes of an array will become field names. Values of array wil become field values.
 * @param $arr array            An array that should be converted.
 * @param $recursive bool       If set to <code>true</code> than sub-arrays are converted to objects too.
 * @return stdClass             If the input array is null or empty or is not array then null is returned. Otherwise an object is returned.
 */
function array_to_object($arr, $recursive=false) {
    if (!$arr or !is_array($arr))
        return null;

    $obj = new stdClass();
    foreach($arr as $idx => $val) {
        if (is_array($val) && $recursive)
            $val = array_to_object($val);
        $obj->$idx = $val;
    }
    return $obj;
}