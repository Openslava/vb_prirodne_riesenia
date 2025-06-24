<?php  

require_once __DIR__ . '/libs/SmartSellingClient/loader.php';


class SmartSelling {
var $edit_mode;
var $connected;
var $connection;
  
function __construct(){  
    if ( current_user_can('edit_pages') ) $this->edit_mode=true;  
    else $this->edit_mode=false;
    
    $this->connected=$this->is_connected();
      
    if($this->edit_mode) {  
    
        add_action('ve_etp_right_icons', array($this,'smartselling_panel')); //add icon to top panel
    
    }

    // modify smartselling connection settings
    if(isset($_POST['smartselling_change_email']) && $_POST['smartselling_change_email']==1)
        $this->smartselling_change_email();
    // rename member
    if(isset($_POST['smartselling_rename_member']) && $_POST['smartselling_rename_member']==1)
        $this->smartselling_rename_member();
    // connect with SmartSelling
    if(isset($_POST['smartselling_connect'])) $this->connect_smartselling();
    if(isset($_POST['smartselling_connect_smartemailing'])) $this->connect_app('se');
    if(isset($_POST['smartselling_connect_fapi'])) $this->connect_app('fapi');
    // add ss tracking code
    if(isset($_POST['smartselling_set_tracking_code'])) $this->add_smartselling_tracking();

    
    if($this->connected) {
        // login to SmartSelling after login to WP
        add_action('wp_authenticate', array($this,'after_wp_authenticate'));
        
        // auto login when is logged to SmartSelling
        add_action('init', array($this,'handle_auto_login'));
    }
    
    // add tracking code on web
    add_action( 'wp_head', array($this,'head_scripts'));

}

/* LOGIN
******************************************************************* */

function get_login_client() {
    $http_client = new SmartSellingClient\HttpGateway\CurlHttpClient();
    return new SmartSellingClient\SmartSellingLoginClient(
        'https://app.smartselling.cz/',
        $this->connection['client_id'],
        $this->connection['client_secret'],
        $http_client
    );
}

function after_wp_authenticate($user_login) {
    if($user_login==$this->connection['admin_email']) {
        $result = $this->try_to_login_to_smartselling();
        if ($result === 'invalid_email') {
            // TODO: display error message "Invalid Email"
        } elseif ($result === 'invalid_password') {
            // TODO: display error message "Invalid Password"
        } elseif ($result === 'connection_failed') {
            // TODO: display error message "Connection Failed"
        }
    }
}

function try_to_login_to_smartselling() {
    try {
        $password=isset($_POST['pwd'])?$_POST['pwd']:'';
        $this->get_login_client()->tryToLogin(
            $this->connection['admin_email'],
            $password,
            !empty($_POST['rememberme']),
            array($this, 'post_redirect')
        );
    } catch (SmartSellingClient\InvalidEmailException $e) {
        return 'invalid_email';
    } catch (SmartSellingClient\InvalidPasswordException $e) {
        return 'invalid_password';
    } catch (Exception $e) {
        // ignore
    }

    return 'connection_failed';
}

function post_redirect($url, $post_data) {
    $html = '<form action="' . htmlspecialchars($url) . '" method="POST" name="frm">' . "\n";
    foreach ($post_data as $key => $value) {
        $html .= '<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($value) . '" />' . "\n";
    }
    $html .= '</form>' . "\n";
    $html .= '<script type="text/javascript">' . "\n";
    $html .= 'document.frm.submit();' . "\n";
    $html .= '</script>' . "\n";
    echo $html;
    exit;
}

function handle_auto_login() {
    if(!isset($_POST['smartselling_login'])) {
        return;
    }

    try {
        $this->get_login_client()->handleAutologin($_POST, array($this, 'login_administrator'));
    } catch (Exception $e) {
        // ignore
    }
}

function login_administrator() {
    $this->login($this->connection['admin_login']);
}

function login($username) {

    $user = get_user_by('login', $username );
        
    if(!$user || is_wp_error( $user )) {
        return false;
        die;
    }  

    wp_clear_auth_cookie();
    wp_set_current_user ( $user->ID );
    wp_set_auth_cookie  ( $user->ID );
            
    $redirect_to = home_url();
    wp_safe_redirect( $redirect_to );
    exit(); 
            
}

function check_login($log,$pass) {
    $user = get_user_by( 'login', $log );
    
    if ( !$user ) {
       return 'invalid_login';
       die;
    } 
    
    if ( !wp_check_password( $pass, $user->data->user_pass, $user->ID) ) {
      return 'invalid_password';
      die;
    }
    if(!in_array( 'administrator', (array) $user->roles )) {
      return 'user_is_not_admin';
      die;      
    }
    
    return 'success';
    die;
}
function check_connection($client_id,$client_secret) {
    
    if ( !$this->connection ) {
       return 'not_conected';
       die;
    } 
    
    if ( $this->connection['client_id']!=$client_id || $this->connection['client_secret']!=$client_secret) {
      return 'not_access';
      die;
    }
    
    return 'success';
    die;
}

    /* CHANGE ADMIN EMAIL
    ******************************************************************* */
    function smartselling_change_email() {
//      'client_id'=>$_POST['client_id'],
//      'client_secret'=>$_POST['client_secret'],
//      'admin_login'=>$_POST['login'],
//      'admin_email'=>$_POST['email'],


        $cliId = isset($_POST['client_id'])?$_POST['client_id']:'';
        $cliSecret = isset($_POST['client_secret'])?$_POST['client_secret']:'';
        $log=$this->check_connection($cliId, $cliSecret);
        if($log==='success') {

            // Update stored value of admin_email.
            $newMail = isset($_POST['email']) ? $_POST['email'] : '';
            if(empty($newMail)) {
                echo json_encode(array('status' => 'error', 'error' => array('message' => 'New admin email address is missing.')));
                exit;
            }

            $this->connection['admin_email'] = $newMail;
            $this->save_connection($this->connection);
            echo json_encode(array('status' => 'success'));

        } elseif ($log === 'not_conected') {
            echo json_encode(array('status' => 'error', 'error' => array('message' => 'Not connected to SmartSelling.')));
        } elseif ($log === 'not_access') {
            echo json_encode(array('status' => 'error', 'error' => array('message' => 'Invalid Client ID or Client Secret.')));
        }

        exit;
    }

    /* CHANGE ADMIN EMAIL
    ******************************************************************* */
    function smartselling_rename_member() {
//        $_POST parametry:
//          client_id = client ID pro SmartSelling API
//          client_secret = client secret pro SmartSelling API
//          billing_user_id = ID uživatele v billingu
//          new_email = nový email uživatele

        global $wpdb;

        $cliId = isset($_POST['client_id'])?$_POST['client_id']:'';
        $cliSecret = isset($_POST['client_secret'])?$_POST['client_secret']:'';
        $log=$this->check_connection($cliId, $cliSecret);
        if($log==='success') {
            //Process request
            $billingId = isset($_POST['billing_user_id']) ? $_POST['billing_user_id'] : -1;
            if(empty($billingId)) {
                wp_send_json(array('status' => 'error', 'error' => array('message' => 'Billing user id is missing.')));
            }
            $newMail = isset($_POST['new_email']) ? $_POST['new_email'] : '';
            if(empty($newMail)) {
                wp_send_json(array('status' => 'error', 'error' => array('message' => 'New user\'s email address is missing.')));
            }

            //Find user by billing ID
            $users=get_users(array('meta_key'=>META_BILLING_ID, 'meta_value'=>$billingId));
            if(empty($users)) {
                wp_send_json(array('status' => 'error', 'error' => array('message' => 'User with this billing ID does not exists.')));
            }
            if(is_array($users) && count($users) > 1) {
                wp_send_json(array('status' => 'error', 'error' => array('message' => 'More users with this billing ID are present.')));
            }
            /** @var WP_User $user */
            $user = $users[0];

            $collision = get_user_by('login', $newMail);
            if($collision && $collision->ID != $user->ID)
                wp_send_json(array('status' => 'error', 'error' => array('message' => 'Different user with this login already exists.')));

            //Login name has to be updated directly in DB.
            // Sanitize the new username
            $new_username       = esc_sql(sanitize_user($newMail));
//            $current_username   = esc_sql($user->user_email);
            $q = $wpdb->prepare( "UPDATE $wpdb->users SET user_login = %s WHERE ID = %d", $new_username, $user->ID );
            $queryRes = $wpdb->query($q);
            if( $queryRes === false )
                wp_send_json(array('status' => 'error', 'error' => array('message' => 'User\'s login could not be updated.')));


            //Update user information
            $args = array(
              'ID'=>$user->ID,
//              'user_login' => $newMail,
              'user_email' => $newMail,
            );
            if($user->nickname===$user->user_email)
                $args['nickname'] = $newMail;
            if($user->display_name===$user->user_email)
                $args['display_name'] = $newMail;
            $res = wp_update_user($args);

            if(is_wp_error($res))
                wp_send_json(array('status' => 'error', 'error' => array('message' => 'Error during user meta update. '
                  .$res->get_error_message())));

            wp_send_json(array('status' => 'success'));

        } elseif ($log === 'not_conected') {
            wp_send_json(array('status' => 'error', 'error' => array('message' => 'Not connected to SmartSelling.')));
        } elseif ($log === 'not_access') {
            wp_send_json(array('status' => 'error', 'error' => array('message' => 'Invalid Client ID or Client Secret.')));
        }

        exit;
    }


/* CONNECTION
******************************************************************* */

function connect_smartselling() {
    $log=$this->check_login($_POST['login'],$_POST['password']);
    if($log==='success') {
        $this->save_connection(array(
            'client_id'=>$_POST['client_id'],
            'client_secret'=>$_POST['client_secret'],
            'admin_login'=>$_POST['login'],
            'admin_email'=>$_POST['email'],
        )); 
       
      /* //create user 
      if ( !username_exists( $_POST['email']) && email_exists( $_POST['email']) == false ) {
        	$random_password = wp_generate_password( $length=12, $include_standard_special_chars=false );
        	wp_create_user( $_POST['email'], $random_password, $_POST['email'] );
      }  */

        echo json_encode(array('status' => 'success','base_url' => get_home_url()));
    } elseif ($log === 'invalid_login') {
        echo json_encode(array('status' => 'error', 'error' => array('message' => 'Invalid login.')));
    } elseif ($log === 'invalid_password') {
        echo json_encode(array('status' => 'error', 'error' => array('message' => 'Invalid password.')));
    } elseif ($log === 'user_is_not_admin') {
        echo json_encode(array('status' => 'error', 'error' => array('message' => 'User is not administrator.')));
    }

    exit;
}

function connect_app($app) {
    $log=$this->check_connection($_POST['client_id'],$_POST['client_secret']);
    if($log==='success') {
        
        $connection=get_option('ve_connect_'.$app);
        
        if($connection && isset($connection['connection']['status']) && $connection['connection']['status']) {
            echo json_encode(array('status' => 'error', 'error' => array('message' => 'Connection already exists.')));
            die;
        }

        $connection=array(
            'login'=>$_POST['username'],
            'password'=>$_POST['api_token'],
            'connection'=>array(
                'login'=>$_POST['username'],
                'password'=>$_POST['api_token'],
                'status'=>'1',
            )
        );
        update_option('ve_connect_'.$app,$connection);
        echo json_encode(array('status' => 'success'));
        
    } elseif ($log === 'not_conected') {
        echo json_encode(array('status' => 'error', 'error' => array('message' => 'Not connected to SmartSelling.')));
    } elseif ($log === 'not_access') {
        echo json_encode(array('status' => 'error', 'error' => array('message' => 'Invalid Client ID or Client Secret.')));
    }

    exit;
}

function save_connection($connection) {
    update_option('smartselling_connection',$connection);
}

function get_connection() { 
    return get_option('smartselling_connection');
}

/* TRACKING 
******************************************************************* */


function add_smartselling_tracking() {
    $log=$this->check_connection($_POST['client_id'],$_POST['client_secret']);
    if($log==='success') {

        $trackingCode = stripslashes($_POST['tracking_code']);
        update_option('mw_smartselling_tracking_code', $trackingCode);
        
        //delete hand added code
        $codes=get_option('web_option_codes');
        $codes['head_scripts']=$this->removeSmartSellingTrackingCode($codes['head_scripts']); 
        $codes['footer_scripts']=$this->removeSmartSellingTrackingCode($codes['footer_scripts']);
        update_option('web_option_codes', $codes);
        
        echo json_encode(array('status' => 'success'));
        
    } elseif ($log === 'not_conected') {
        echo json_encode(array('status' => 'error', 'error' => array('message' => 'Not connected to SmartSelling.')));
    } elseif ($log === 'not_access') {
        echo json_encode(array('status' => 'error', 'error' => array('message' => 'Invalid Client ID or Client Secret.')));
    }

    exit;
}

function head_scripts() {    
    $code=get_option('mw_smartselling_tracking_code'); 
    if($code) echo $code;
}

function removeSmartSellingTrackingCode($code)
{
    $code = stripslashes($code);
	  $code = preg_replace('##', '', $code);
    return addslashes($code);
}

/* ICON
******************************************************************* */

function smartselling_panel() {   
    if($this->is_connected()) {
    ?>
    <li class="ve_etp_sss">
        <a class="ve_etp_sss_icon" title="<?php echo __('SmartSelling','cms_ve'); ?>" href="https://app.smartselling.cz/dashboard/"></a>
    </li>
    <?php
    }
}

function is_connected() {
    $this->connection=$this->get_connection();
    //DEBUG
//    $this->connection=array(
//      'client_id'=>111,
//      'client_secret'=>222,
//      'admin_login'=>'1234',
//      'admin_email'=>'sample@xxx.yy',
//    );
    if($this->connection) return true;
    else return false;
}

}

