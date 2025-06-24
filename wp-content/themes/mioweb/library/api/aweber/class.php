<?php

require_once('aw-api-library/aweber_api/aweber_api.php');

define('AWEBER_AUTHORIZED' , '##aweber_authorized');


class MioWebApiBridge_Aweber extends MioWebApiBridgeBase
{
    /** @var bool       If debugging of the API is enabled. */
    private $debug = true;

    /**
     * True to enable printing, false otherwise. Set using the constructor
     * @var boolean
     * @access private
     */
    private $errorsOn = false;

    /** @var AWeberApi      Global instance of an official API wrapper. It is initialized
     *      during a successful {@link setApiKey()} method. */
    private $aweber  = null;

    /** @var string         Unique access key. */
    private $accessKey = '';
    /** @var string         Unique access secret. */
    private $accessSecret = '';
    /** @var int            Identification number of the account. This is retrieved by {@link setApiKey()} method. */
    private $accountId = 0;


    public static function getApiName()
    {
        return "AWeber";
    }

    public function getApiUrl() {
        return 'NOT SUPPORTED';
    }

    /**
     * Check cURL extension is loaded and that an API key has been passed, also enables or disables error printing
     * @param string $apiKey            API key/authorization code
     * @param boolean $print_errors     If set to <code>true</code>, then error message string will be returned on errors.
     *      Otherwise only error result value will be returned.
    */
    public function __construct($apiKey = null, $print_errors = false)
    {
        if (!extension_loaded('curl'))
            trigger_error('AWeber requires PHP cURL', E_USER_ERROR);
        $this->apiKey = '';
        $this->errorsOn = ($print_errors) ? true : false;
    }

    public function first_authorize(&$login, &$password) {
        $this->clearLastError();

        if (!$password) {
            $this->lastError = __('No authorization code has been provided.', 'cms');
            return false;
        }

        $codes = explode('|', $password);
        $count = count($codes);

        if ($login == AWEBER_AUTHORIZED || ($count > 0 && $codes[0]==AWEBER_AUTHORIZED)) {
            //Already authorized. Finish it.
            return true;
        }

        switch($count) {
            case 5:
            case 6:
                try {
                    /** @var array ($consumerKey, $consumerSecret, $accessToken, $accessSecret) */
                    $accessArr = AWeberAPI::getDataFromAweberID($password);
                } catch (AWeberAPIException $e) {
                    $this->lastError = __('Access tokens could not be retrieved.', 'cms')
                        . ($e->message ? ' '.$e->message : '');
                    $accessArr = null;
                }
                if (!$accessArr) {
                    if (!$this->getLastError())
                        $this->lastError = __('Access tokens could not be retrieved.', 'cms');
                    return false;
                }
                break;

            default:
                $this->lastError = 'Invalid authorization code. Repeat the authorization procedure.';
                return false;
                break;
        }

        //Update the authorization data
//        $login = AWEBER_AUTHORIZED;
        array_splice($accessArr, 0, 0, AWEBER_AUTHORIZED); //insert first element as mark of passed authorization
        $password = implode('|', $accessArr);

        return true;
    }


    /**
     * Sets already authorized API key and prepares internal API wrapper.
     * @param $apiKey string        API key to be used in successive API calls.
     * @return bool                 Returns false on error and fills {@link $lastError}.
     *      On success <code>true</code> is returned and internal {@link $aweber}, {@link $accessKey},
     *      {@link $accessSecret} and {@link $accountId} are set.
     */
    protected function setApiKey($apiKey) {
//       [AWEBER_AUTHORIZED, application key, application secret, request token, request token secret]
        $this->clearLastError();

        $codes = explode('|', $apiKey);
        if (empty($codes) || !is_array($codes) || count($codes)!==5 || $codes[0]!==AWEBER_AUTHORIZED) {
            $this->lastError = __('API key is not valid or open auth validation has not been finished.', 'cms');
            return false;
        }
        
        $this->aweber = new AWeberAPI($codes[1], $codes[2]);
        $this->accessKey = $codes[3];
        $this->accessSecret = $codes[4];
            # set this to true to view the actual api request and response
        $this->aweber->debug = $this->debug;

        //Get $accountId, which is used through all the API calls.
        $resp = $this->sendRequest('GET', '/accounts');
        if($resp && is_array($resp) && isset($resp['entries'][0]['id']))
            $this->accountId = $resp['entries'][0]['id'];

        return ($this->accountId != 0);
    }

    /**
     * Performs the underlying HTTP request. Uses REST API interface of MailChimp
     * @param string $http_verb     The HTTP verb to use: get, post, put, patch, delete.
     * @param string $uri           The API resource/method to be called.
     * @param array $args           Associative array of parameters to be passed with the call. For GET request this
     *      is used as QUERY part of the request.
     * @param array $options        What kind of result should be returned. It is used as $options parameter for
     *      {@link OAuthApplication::makeRequest()}. E.g. <code>[return] => status|headers|integer</code>
     * @return mixed                Associative array of decoded result.
     */
    private function sendRequest($http_verb, $uri, $args=array(), $options=array())
    {
        // Currently we are dependant on the internal CURL implementing class's settings.
//        $this->aweber->adapter->curl->
//        curl_setopt($handle, CURLOPT_USERAGENT, 'MioWeb/AWeber-API (mioweb.cz)');
//        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 2);
//        curl_setopt($handle, CURLOPT_TIMEOUT, $timeout);
//        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, $this->verifySslPeer);

        // Use already authorized tokens.
        /** @var OAuthApplication $adapter */
        $adapter = $this->aweber->adapter;
        if ($this->accessKey && $this->accessSecret) {
            if ($adapter->user && ($adapter->user instanceof OAuthUser))
                $user = $adapter->user;
            else {
                $user = new OAuthUser();
                $adapter->user = $user;
            }
            $user->accessToken = $this->accessKey;
            $user->tokenSecret = $this->accessSecret;
        }

        try {
            $resp = $adapter->request($http_verb, $uri, $args, $options);
        }catch(AWeberAPIException $e) {
            $resp = false;
            $this->lastError = 'AWeber API call failed. ' . $e->message
                . ' Server returned status code "'.$e->status.'", error type "'.$e->type.'". '
                . $e->documentation_url
            ;
            if ($this->errorsOn)
                trigger_error($this->lastError, E_USER_ERROR);
        }catch(Exception $e) {
            $resp = false;
            $this->lastError = 'AWeber API call failed. ' . $e->getMessage();
            if ($this->errorsOn)
                trigger_error($this->lastError, E_USER_ERROR);
        }

//        curl_close($handle);
        return $resp;
    }

    public function is_connected($login, $password)
    {
        $this->clearLastError();
        if (!$this->setApiKey($password))
            return false;

        return ($this->accountId != 0);
    }

/*    private function get_listform_fields($listId, $includeEmailField=true) {
        $result = $this->sendMcRequest('get', 'lists/' . $listId . '/merge-fields', array(
            'fields' => 'merge_fields.merge_id,merge_fields.tag,merge_fields.name,merge_fields.required'
                        .',merge_fields.type,merge_fields.default_value,merge_fields.public'
                        .',merge_fields.help_text,merge_fields.display_order'
        ));
        if ($result) {
            //Standardize output format according to SE field format.
            foreach($result->merge_fields as $key => $value) {
                $value->id = $value->merge_id;
                unset($value->merge_id);
            }

            if ($includeEmailField) {
                //Add the email field, which is not returned by the MailChimp api, but that is always present in the subscribe forms.
                $field = new stdClass();
                $field->id = 0;
                $field->tag = 'EMAIL';
                $field->name = __('Email Address', 'cms_ve');
                $field->required = true;
                $field->type = 'email';
                $field->default_value = null;
                $field->public = true;
                $field->help_text = '';
                $field->display_order = 0;

                $result->merge_fields[] = $field;
            }

            //Make the array "itemized".
            $itemized = new stdClass();
            $itemized->item = $result->merge_fields;
            return $itemized;
        }
        else
            return false;
    }*/

    public function get_forms_list($login)
    {
        error_log(__METHOD__);
        $this->clearLastError();

        if (!$this->setApiKey($login['password'])) {
            error_log(__METHOD__ . "/n" . $this->getLastError());
            return false;
        }

        $url = "/accounts/{$this->accountId}";
        $params = array(
            'ws.op' => 'getWebForms',
            'ws.size' => '10000'
        );
        $result = $this->sendRequest('GET', $url, $params);

        if (!$result) {
            error_log(__METHOD__ . "/n" . $this->getLastError());
            return false;
        }

        // Normalize output format according to SE data format. Arrays and subarrays are returned in a wrapping object
        // in its "item" field. Items are objects.
        /*
            id
            name
            fields (obj)
                item (obj)
                    id
                    label - caption
                    required - 1|0
                    type
                    defaults
        */

        $wrapped = array();
        foreach ($result as $key => $val) {
            $item = new stdClass();
            $item->name = $val['name'];
            $item->id = $val['id'];
            $item->html = $val['html_source_link'];
            $item->url = $val['javascript_source_link'];
            $item->api_link = $val['self_link'];
            $item->orig_id = $val['id'];
            $item->enabled = $val['is_active'];

            //Modify name to include "inactive" flag.
            if (!$item->enabled)
                $item->name .= ' ' . __('(inactive form)', 'cms');

            //!! Hack form AWeber printing forms. Use script URL as ID.
            $item->id = $item->url;

            //Add to resulting array
            $wrapped[$item->id] = $item;
        }

        //Compose the result object with "item" field.
        $itemized = new stdClass();
        $itemized->item = $wrapped;
        return $itemized;
    }

    public function get_form($id)
    {
        //!! Hack form AWeber printing forms. Using script URL as ID.

        return $id;
    }

    public function print_form($element,$form,$css_id,$added)
    {
        if($added) {
            
            $content='<script type="text/javascript">
                jQuery(document).ready(function($) {                             
          					var $target = $("'.$css_id.' .ve_content_form_container");
          
          					$target.html(\'<div class="AW-Form-'.basename($form, ".js").'"></div>\');
          
          					var script = document.createElement("script");
          					script.async = true;
          					script.src = "'.$form.'";
          					$target[0].appendChild(script);
        				});

          	</script>';
        } else $content='';
        return '<div class="ve_content_form_container">'.$content.'</div>';
    }

    public function get_lists_list($login)
    {
        $this->clearLastError();

        if (!$this->setApiKey($login['password']))
            return false;

        $url = "/accounts/{$this->accountId}/lists";
        $params = array(
            'ws.size' => '100'  //100 is maximum for this resource
        );


        // Normalize output format according to SE data format. Arrays and subarrays are returned in a wrapping object
        // in its "item" field, indexed by ID. Items are arrays.
        /*
            id
            name
        */

        $wrapped = array();
        while ($url) {
            $result = $this->sendRequest('GET', $url, $params);

            if (!$result)
                return false;

            foreach ($result['entries'] as $key => $val) {
                $item = new stdClass();
                $item->name = $val['name'];
                $item->id = $val['id'];
                $item->api_link = $val['self_link'];
                $item->unique_list_id = $val['unique_list_id'];

                //Add to resulting array
                $wrapped[$item->id] = $item;
            }

            if (isset($result['next_collection_link']))
                $url = $result['next_collection_link'];
            else
                $url = '';
        }

        //Compose the result object with "item" field.
        $itemized = new stdClass();
        $itemized->item = $wrapped;
        return $itemized;
    }

    /**
     * {@inheritdoc}
     *
     * <b>Warning</b>: This method is used to subscribe (or add) a Subscriber to a List. Subscribers added via the API require confirmation.
     * You must only subscribe someone to a List if they specifically requested to be subscribed.
     *
     * Read more about this in the link below: https://help.aweber.com/entries/21729456-can-i-use-this-list
     */
    public function save_to_list($listId, $email)
    {
        $this->clearLastError();
        $login = get_option('ve_connect_se');
        if (!$this->setApiKey($login['aweber_password']))
            return false;
        $url = "/accounts/{$this->accountId}/lists/{$listId}/subscribers";
        $params = array(
            'ws.op' => 'create',
            'email' => $email,
            'name' => array_shift(explode('@', $email))
        );

/*
        {
            email
            name
            custom_fields[key:value]
            ip
            is_verified
            verified_at
            status = "subscribed", "unsubscribed", "unconfirmed"
            subscribed_at
            subscription_method
        }
*/
        $result = $this->sendRequest('POST', $url, $params
            , array('return' => 'headers')
        );

        if (!$result)
            return false;

        if ($result['Status-Code'] == 201) {
            $subscriber_id = array_pop(explode('/', $result['Location']));
            return true;
        }

        return false;
    }


    /**
     * {@inheritdoc}
     *
     * <b>Warning</b>: This method is used to subscribe (or add) a Subscriber to a List. Subscribers added via the API require confirmation.
     * You must only subscribe someone to a List if they specifically requested to be subscribed.
     *
     * Read more about this in the link below: https://help.aweber.com/entries/21729456-can-i-use-this-list
     */
    public function save_to_list_details($listId, $email, $contactDetails = array(), $customFields=array())
    {
        $this->clearLastError();
        $login = get_option('ve_connect_se');
        if (!$this->setApiKey($login['aweber_password']))
            return false;
        $url = "/accounts/{$this->accountId}/lists/{$listId}/subscribers";
        $params = array(
            'ws.op' => 'create',
        );

        /*
                {
                    email
                    name
                    custom_fields[key:value]
                    ip
                    is_verified
                    verified_at
                    status = "subscribed", "unsubscribed", "unconfirmed"
                    subscribed_at
                    subscription_method
                }
        */

        //Adapt input to API format.
        $hContact = array();
        $hContact['custom_fields'] = array();
        foreach($contactDetails as $key => $value) {
            if(empty($key) || empty($value))
                continue;
            if ($key=='ip') $key='ip_address';
            if(in_array($key, array('email', 'ad_tracking', 'ip_address', 'last_followup_message_number_sent', 'misc_notes', 'name'))) {
                //root values
                $hContact[$key] = $value;
            } else {
                //other values save as custom fields
                $hContact['custom_fields'][$key] = $value;
            }
        }
        //Email address
        $hContact['email'] = $email;
        if (empty($hContact['name']))
            $hContact['name'] = array_shift(explode('@', $email));
        $params = array_merge($params, $hContact);

        $result = $this->sendRequest('POST', $url, $params
            , array('return' => 'headers')
        );

        if (!$result)
            return false;

        if ($result['Status-Code'] == 201) {
            $subscriber_id = array_pop(explode('/', $result['Location']));
            return true;
        }

        return false;
    }

    public function get_last_enter($listId)
    {
        $this->clearLastError();
        $login = get_option('ve_connect_se');
        if (!$this->setApiKey($login['aweber_password']))
            return false;

        $emptyRes = array(
            'name' => '',
            'time' => 0,
            'count' => 0
        );

        $url = "/accounts/{$this->accountId}/lists/{$listId}/subscribers";
        $params = array(
            'ws.size' => '100'  //100 is maximum for this resource
        );


        //Find youngest
        date_default_timezone_set('UTC'); //necessity for strtotime to behave correctly, we are working in UTC
        $maxDate = strtotime('1970-01-01 00:00:00');
        $foundCid = '';
        $count = 0;

        $urlIterate = $url;
        while ($urlIterate) {
            $result = $this->sendRequest('GET', $urlIterate, $params);
            if (!$result)
                return $emptyRes;

            foreach ($result['entries'] as $cid => $val) {
                $count++;
                $createdOn = strtotime($val['subscribed_at']);
                if ($createdOn > $maxDate) {
                    $maxDate = $createdOn;
                    $foundCid = $val['id'];
                }
            }
            if (isset($result['next_collection_link']))
                $urlIterate = $result['next_collection_link'];
            else
                $urlIterate = '';
        }
        //No contact found, list is empty.
        if (empty($foundCid))
            return $emptyRes;

        $url = $url."/{$foundCid}";
        $result = $this->sendRequest('GET', $url);
        if (!$result)
            return $emptyRes;

        $contact = $result;
        return array(
            'name' => (string)
                trim($contact['name']),
            'time' => strtotime($contact['subscribed_at']),
            'count' => $count
        );
    }


    public function get_list_count($listId)
    {
        $this->clearLastError();
        $login = get_option('ve_connect_se');
        if (!$this->setApiKey($login['aweber_password']))
            return false;

        $url = "/accounts/{$this->accountId}/lists/{$listId}/subscribers";
        $params = array(
            'ws.size' => '1',  //get only minimum amount of data
        );

        $result = $this->sendRequest('GET', $url, $params);
        if (!$result)
            return false;

        if (is_int($result['total_size']))
            return (int)$result['total_size'];

        return false;
    }

}

global $apiConnection;
$apiConnection->registerApiClass('aweber', 'MioWebApiBridge_Aweber');