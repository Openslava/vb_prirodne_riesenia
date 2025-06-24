<?php

class MioWebApiBridge_GR extends MioWebApiBridgeBase
{

    /**
     * True to enable printing, false otherwise. Set using the constructor
     * @var boolean
     * @access private
     */
    private $errorsOn = false;
    /**
     * GetResponse API key.
     * API key can be retrieved from https://app.getresponse.com/manage_api.html (2015-01-12).
     * http://www.getresponse.com/my_api_key.html
     * @var string
     */
    private $apiKey = 'PASS_API_KEY_WHEN_INSTANTIATING_CLASS';
    /** @var string         URL for JSON-RPC API calls. */
    private $apiUrl = 'https://api2.getresponse.com';
    /** @var string         URL for REST API calls. */
    private $apiUrlRest = 'BASIC_OR_ENTERPRISE_REST_URI';
    /** @var string         Set by {@link setApiKey()} method when used in enterprise mode. */
    private $enterpriseHeader = '';
    /** @var bool            If CURL call should verify SSL certificate. */
    private $verifySslPeer = false;

    public static function getApiName()
    {
        return "GetResponse";
    }

    public function getApiUrl()
    {
        return $this->apiUrl;
    }

    /**
     * Check cURL extension is loaded and that an API key has been passed, also enables or disables error printing
     * @param string $apiKey GetResponse API key
     * @param boolean $print_errors Pokud je nastaveno na true, tak bude pri chybe namisto vraceni chybove hodnody
     * vypsan a vracen vracen obsah chyby.
     * @return MioWebApiBridge_GR
     */
    public function __construct($apiKey = null, $print_errors = false)
    {
        if (!extension_loaded('curl'))
            trigger_error('GetResponsePHP requires PHP cURL', E_USER_ERROR);
        // apiKey bude doplnen az pri jednotlivych volanich.
//        if(is_null($apiKey))
//            trigger_error('API key must be supplied', E_USER_ERROR);
//        $this->apiKey = $apiKey;
        $this->errorsOn = ($print_errors) ? true : false;
    }

    /**
     * Return array as a JSON encoded string
     * @param string $method    API method to call
     * @param array $params     Array of parameters
     * @param string $id        Unused parameter.
     * @return string           JSON encoded string
     * @access private
     */
    private function prepGrRequest($method, $params = null, $id = null)
    {
        $array = array($this->apiKey);
        if (!is_null($params)) $array[1] = $params;
        $request = json_encode(array('method' => $method, 'params' => $array, 'id' => $id));
        return $request;
    }

    /**
     * Provede API call.
     * @param string $request JSON encoded array
     * @return object|bool Pri uspechu vrati dekodovany JSON. Pri neuspechu bud vrati false ($errorsOn=false) nebo
     * chybovou hlasku ($errorsOf=true).
     * @access private
     */
    private function sendGrRequest($request)
    {
        $handle = curl_init($this->getApiUrl());
        curl_setopt($handle, CURLOPT_POST, 1);
        curl_setopt($handle, CURLOPT_POSTFIELDS, $request);
        curl_setopt($handle, CURLOPT_HEADER, 'Content-type: application/json');
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, $this->verifySslPeer);
        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($handle, CURLOPT_TIMEOUT, 5);
        $response = json_decode(curl_exec($handle));

        try {
            $cError = curl_error($handle);
        } catch (Exception $e) {
            $cError = $e->getMessage();
        }

        if ($cError) {
            $this->lastError = 'GetResponse API call failed. CURL error: ' . $cError . '';
            if ($this->errorsOn)
                trigger_error($this->lastError, E_USER_ERROR);
            else
                return false;
        }
        $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        if (!(($httpCode == '200') || ($httpCode == '204'))) {
            $this->lastError = 'GetResponse API call failed. Server returned status code ' . $httpCode;
            if ($this->errorsOn)
                trigger_error($this->lastError, E_USER_ERROR);
            else
                return false;
        }
        curl_close($handle);
        if (!$response->error)
            return $response->result;
        else {
            $this->lastError = $response->error->message . " ({$response->error->code})";
            if ($this->errorsOn) {
                var_dump($request);
                return $response->error;
            } else
                return false;
        }
    }

    public function is_connected($login, $password)
    {
        $this->clearLastError();
        $this->apiKey = $password;
        $data = $this->prepGrRequest('ping');
        $result = $this->sendGrRequest($data);
        if ($result && $result->ping == "pong")
            return true;
        return false;
    }

    public function get_forms_list($login)
    {
        $this->clearLastError();
        $this->setApiKey($login['password']);

        $result = $this->sendGrRestRequest('get', 'forms', array(
            'fields' => 'name,camapign,status,scriptUrl'
        ));
        if (!$result) {
            error_log(__METHOD__ . "\n" . $this->getLastError(), E_ERROR);
            return false;
        }
        // Normalize output format according to SE data format. Arrays and subarrays are returned in a wrapping object
        // in its "item" field.
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
        foreach ($result as $val) {
            $val->url = $val->scriptUrl;
            unset($val->scriptUrl);

            $val->id = $val->formId;
            unset($val->formId);
            unset($val->webformId);

            //Add to resulting array
            $wrapped[$val->id] = $val;

            //!! Hack form GetResponse printing forms. Use script URL as ID.
            $val->id = $val->url;
        }

        //Compose the result object with "item" field.
        $itemized = new stdClass();
        $itemized->item = $wrapped;
        return $itemized;
    }

    public function get_form($id)
    {
        //!! Hack form GetResponse printing forms. Using script URL as ID.

        return $id;
    }

    public function print_form($content, $form, $css_id, $added)
    {
        if($added) {

            $content='
            <script type="text/javascript">
                  jQuery(function($) {
                    var target = $("'.$css_id.' .ve_content_form_container")[0];
    
          					var script = document.createElement(\'script\');
          					script.async = true;
          					script.src = \''.$form.'\';
          					script.parentNode = target;
          					target.appendChild(script);

                });

          	</script>';
        } else $content='';
        return '<div class="ve_content_form_container">'.$content.'</div>';
    }

    public function get_lists_list($login)
    {
        $this->clearLastError();
        $login = get_option('ve_connect_se');
        $this->apiKey = $login['getresponse_password'];
        $data = $this->prepGrRequest('get_campaigns');
        $result = $this->sendGrRequest($data);
        if (!$result)
            return false;

        // Zde prehazet vysledky tak, aby to bylo podobnejsi SE. GR vraci asociativni pole, kde klic je ID formu.
        // SE vraci vysledky v poli items + pouziva field "id" jako unikatni ID.
        // Tj. je potreba vzit index asociativniho pole a pouzit jej jako field 'id' zaznamu + zmenit asociativni
        // pole na indexovane.

        $wrapped = array();
        foreach ($result as $key => $val) {
            //rozsirit objekt o field ID
            $val->id = $key;
            $wrapped[] = $val;
        }

        //Nakonec prehodit pole do fieldu 'item' nejakeho objektu.
        $itemized = new stdClass();
        $itemized->item = $wrapped;
        return $itemized;
    }

    /**
     * {@inheritdoc}
     *
     * <b>Warning</b>: Adding contact is not an instant action. It will appear on your list after validation or after validation and confirmation (in case of double-optin procedure). You can set subscribe callback to be notified about successful adding.
     *
     * <b>Warning</b>: To update existing contact use methods such as set_contact_name, set_contact_customs or set_contact_cycle. Old param action is deprecated and ignored.
     *
     * <b>Warning</b>: Optin setting is locked to double optin by default - confirmation email will be sent to newly added contacts. If you want to add contacts already confirmed on your side please contact us using this form and provide us with your campaign name and the description of your business model. We will set single optin for this campaign after short verification.
     *
     * <b>Warning</b>: If you use this method as a way to handle your registration form, then you need to remember that this method does not allow to resubscribe a contact that was unsubscribed via a link, and also it is impossible to resend confirmation email using this kind of form.
     */
    public function save_to_list($listId, $email)
    {
        $this->clearLastError();
        $login = get_option('ve_connect_se');
        $this->apiKey = $login['getresponse_password'];
        $data = $this->prepGrRequest('add_contact', array(
            'campaign' => $listId,
            'email' => $email
        ));
        $result = $this->sendGrRequest($data);
        if (!$result)
            return false;

        if (isset($result->queued) && ($result->queued))
            return true;
        else if (@!empty($result->message))
            $this->lastError = $result->message . " ({$result->code})";

        return false;
    }


    /**
     * {@inheritdoc}
     *
     * @param $contactDetails array Ty podporovane na urovni hlavni objektu se predaji u nej, nezname se predaji jako
     * "custom" fieldy (viz odkaz nize na seznam podporovanych atributu).
     *
     * @see http://apidocs.getresponse.com/en/api/1.5.0/Contacts/add_contact Seznam podporovanych atributu
     */
    public function save_to_list_details($listId, $email, $contactDetails = array(), $customFields=array())
    {
        $this->clearLastError();
        $login = get_option('ve_connect_se');
        $this->apiKey = $login['getresponse_password'];
        $customs = array();
        $params = array(
            'campaign' => $listId,
            'email' => $email
        );
        //Preformatuj vstup dle pozadavku API.
        foreach ($contactDetails as $key => $value) {
            if (empty($key) || empty($value))
                continue;
            if (in_array($key, array('name', 'cycle_day', 'ip'))) {
                //globalni hodnoty nasyp k zakladni entite
                $params[$key] = $value;
            } else {
                //nepodporovane hodnoty jako custom atributy
                $item = new stdClass();
                $item->name = $key;
                $item->content = $value;
                $customs[] = $item;
            }
        }
        if (!empty($customs))
            $params['customs'] = $customs;
        //Vlastni API call
        $data = $this->prepGrRequest('add_contact', $params);
        $result = $this->sendGrRequest($data);
        if (!$result)
            return false;

        if (isset($result->queued) && ($result->queued))
            return true;
        else if (@!empty($result->message))
            $this->lastError = $result->message . " ({$result->code})";

        return false;
    }

    public function get_last_enter($listId)
    {
        $this->clearLastError();
        $login = get_option('ve_connect_se');
        $this->apiKey = $login['getresponse_password'];
        $emptyRes = array(
            'name' => '',
            'time' => 0,
            'count' => 0
        );

        $params = array(
            'campaigns' => array($listId)
        );
        //Vlastni API call
        $data = $this->prepGrRequest('get_contacts', $params);
        $result = $this->sendGrRequest($data);
        if (!$result)
            return $emptyRes;

        //Najit nejmladsi
        date_default_timezone_set('UTC'); //potreba pro korektni funkci strtotime()
        $maxDate = strtotime('1970-01-01 00:00:00');
        $foundCid = '';
        $count = 0;
        foreach ($result as $cid => $val) {
            $count++;
            $createdOn = strtotime($val->created_on);
            if ($createdOn > $maxDate) {
                $maxDate = $createdOn;
                $foundCid = $cid;
            }
        }
        //neexistuje zadny kontakt, seznam je prazdny
        if (empty($foundCid))
            return $emptyRes;
        $contact = $result->{$foundCid};
        return array(
            'name' => (string)$contact->name,
            'time' => strtotime($contact->created_on),
            'count' => $count
        );
    }

    public function get_list_count($listId)
    {
        $this->clearLastError();
        $login = get_option('ve_connect_se');
        $this->apiKey = $login['getresponse_password'];
        $data = $this->prepGrRequest('get_contacts_distinct_amount', array(
            'campaigns' => array($listId)
        ));
        $result = $this->sendGrRequest($data);
        if (!$result)
            return false;

        if (is_int($result))
            return $result;
        if (isset($result->amount))
            return (int)$result->amount;
        else if (@!empty($result->message))
            $this->lastError = $result->message . " ({$result->code})";

        return false;
    }

    /**
     * Sets API key for further use.
     *
     * @param $apiKey string        API key to be used in successive API calls.
     * @param $enterpriseDomain string  If the GetResponse account is registered as 360/enterprise account, then this
     *      value should be set to a domain to with the account belongs to.
     */
    protected function setApiKey($apiKey, $enterpriseDomain = '')
    {
        $this->apiKey = $apiKey;
        if ($enterpriseDomain) {
            $this->apiUrl = 'https://api2.getresponse.com';  //TODO This URL should be specific per customer, not a general one.
            $this->apiUrlRest = 'https://api3.getresponse360.com/v3';
            $this->enterpriseHeader = 'X-Domain: ' . trim($enterpriseDomain);
        } else {
            $this->apiUrl = 'https://api2.getresponse.com';
            $this->apiUrlRest = 'https://api.getresponse.com/v3';
            $this->enterpriseHeader = null;
        }
    }

    /**
     * Performs the underlying HTTP request. Uses REST API interface.
     * @param string $http_verb     The HTTP verb to use: get, post, put, patch, delete.
     * @param string $resource      The API resource/method to be called.
     * @param array $args           Associative array of parameters to be passed with the call. For GET request this
     *      is used as QUERY part of the request.
     * @param int $timeout          Timeout in seconds to wait for result.
     * @return array                Associative array of decoded result.
     */
    private function sendGrRestRequest($http_verb, $resource, $args = array(), $timeout = 10)
    {
        $url = $this->apiUrlRest . '/' . $resource;
        $handle = curl_init();
        curl_setopt($handle, CURLOPT_URL, $url);
        $headers = array(
            'Accept: application/json',
            'Content-Type: application/json',
            'X-Auth-Token: api-key ' . $this->apiKey);
        if (!$this->enterpriseHeader)
            $headers[] = $this->enterpriseHeader;
        curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($handle, CURLOPT_USERAGENT, 'MioWeb/GetResponse/v3 (mioweb.cz)');
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($handle, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, $this->verifySslPeer);
        curl_setopt($handle, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($handle, CURLOPT_ENCODING, 'gzip,deflate');

        switch ($http_verb) {
            case 'post':
                $jsonData = json_encode($args, JSON_FORCE_OBJECT);
                curl_setopt($handle, CURLOPT_POST, true);
                curl_setopt($handle, CURLOPT_POSTFIELDS, $jsonData);
                break;

            case 'get':
                $query = http_build_query($args);
                curl_setopt($handle, CURLOPT_URL, $url . '?' . $query);
                break;

            case 'delete':
                curl_setopt($handle, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;

            case 'patch':
                $jsonData = json_encode($args, JSON_FORCE_OBJECT);
                curl_setopt($handle, CURLOPT_CUSTOMREQUEST, 'PATCH');
                curl_setopt($handle, CURLOPT_POSTFIELDS, $jsonData);
                break;

            case 'put':
                $jsonData = json_encode($args, JSON_FORCE_OBJECT);
                curl_setopt($handle, CURLOPT_CUSTOMREQUEST, 'PUT');
                curl_setopt($handle, CURLOPT_POSTFIELDS, $jsonData);
                break;
        }

        $response = curl_exec($handle);

        try {
            $cError = curl_error($handle);
        } catch (Exception $e) {
            $cError = $e->getMessage();
        }

        if ($cError) {
            $this->lastError = $this->getApiName() . ' API call failed. CURL error: ' . $cError . '';
            if ($this->errorsOn)
                trigger_error($this->lastError, E_USER_ERROR);
            else
                return false;
        }
        $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        if (!(($httpCode == '200') || ($httpCode == '204'))) {
            $error = json_decode($response);
            $this->lastError = $this->getApiName() . ' API call failed. Server returned "' . $error->message
                . '", http code=' . $httpCode . '.'
                . "\nCode " . $error->code. ': ' . ($error->codeDescription ? $error->codeDescription : '')
//                . (isset($error->context) ? "\nContext" . print_r($error->context, true) : '')
            ;
            if ($this->errorsOn) {
                trigger_error($this->lastError, E_USER_ERROR);
            } else
                return false;
        }
        curl_close($handle);
        $response = json_decode($response);
        return $response;


    }
}

global $apiConnection;
$apiConnection->registerApiClass('getresponse', 'MioWebApiBridge_GR');