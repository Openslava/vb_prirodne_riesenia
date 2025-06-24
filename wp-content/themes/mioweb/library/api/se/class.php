<?php

global $apiConnection;

class MioWebApiBridge_SE extends MioWebApiBridgeBase
{
    /** @var bool            If CURL call should verify SSL certificate. */
    private $verifySslPeer = false;

    public static function getApiName()
    {
        return "SmartEmailing";
    }

    public function getApiUrl()
    {
        return 'https://app.smartemailing.cz/api/v2';
    }

    // check if is se connected
    public function is_connected($login, $password)
    {
        $this->clearLastError();
        $xml = '
        <xmlrequest>
            <username>' . $login . '</username>
            <usertoken>' . $password . '</usertoken>
            <requesttype>Users</requesttype>
            <requestmethod>testCredentials</requestmethod>
            <details>
            </details>
        </xmlrequest>
        ';

        $result = $this->sendRequest($xml);

        if ($result === false) {
            return 0;
        } else {
            $data = @simplexml_load_string($result);
            if ($data->status == "SUCCESS") return 1;
            else return 0;
        }
    }

    // return list of all forms
    public function get_forms_list($login)
    {
        $this->clearLastError();
        
        $xml = "
          <xmlrequest>
              <username>{$login['login']}</username>
              <usertoken>{$login['password']}</usertoken>
              <requesttype>Webforms</requesttype>
              <requestmethod>getAllFormNames</requestmethod>
              <details>
              </details>
          </xmlrequest>
          ";

        $result = $this->sendRequest($xml);
        if ($result === false) {
            return false;
        } else {
            $data = @simplexml_load_string($result);
            if (isset($data->data)) {
                //Normalize result.
                $arr = array();
                foreach($data->data->item as $val) {
                    $arr[] = $val;
                }

                $itemized = new stdClass();
                $itemized->item = $arr;

                return $itemized;
            }
        }

        return false;
    }

    public function get_form($id)
    {            
        $this->clearLastError();
        $login = get_option('ve_connect_se');
        
        $url = LICENSE_SERVER . 'license/activate';
        
        $url = 'https://app.smartemailing.cz/api/v3/web-form-structure/'.$id;
        
        $response = wp_remote_post( $url, array(
              'method' => 'GET',              
              'timeout' => 45,
              'redirection' => 5,
              'httpversion' => '1.1',
              'blocking' => true,
              'headers' => array(
                'Authorization' => 'Basic ' . base64_encode( trim($login['login']) . ':' . trim($login['password']) )
              ),
        ));   
        
        if ( is_wp_error( $response )) {
            return false;
        }  else {
                
            $ret = json_decode(wp_remote_retrieve_body($response)); 
            $seform=json_decode(json_encode((array)$ret->data), TRUE);

            $save_form=array();
            $save_form['url']=$seform['form_action'];
            $save_form['fields']=$seform['structure'];   
            $save_form['submit_in_new_window']=$seform['submit_in_new_window']; 
            if(isset($seform['purposes'])) $save_form['purposes']=$seform['purposes'];                                               
            $save_form['fields']['do']=array(
                'label'=>'',
                'html_input_name'=>'do',
                'content'=>'webFormRenderer-webForm-submit',
                'html_input_type'=>'hidden',              
            );
            $save_form['submit']=$seform['submit'];
            
            return $save_form;
            
        }

        return false;
    }

    public function get_list($id, $withDetails, $fieldsDef)
    {
        $this->clearLastError();

        $login = get_option('ve_connect_se');
        $arr = array();

        if(!$withDetails) {
            $xml = "
                <xmlrequest>
                    <username>{$login['login']}</username>
                    <usertoken>{$login['password']}</usertoken>
                    <requesttype>ContactLists</requesttype>
                    <requestmethod>getContacts</requestmethod>
                      <details>
                       <id>" . $id . "</id>
                      </details>
                </xmlrequest>";
        } else {
            $xml = "
                <xmlrequest>
                    <username>{$login['login']}</username>
                    <usertoken>{$login['password']}</usertoken>
                    <requesttype>Contacts</requesttype>
                    <requestmethod>getAllInList</requestmethod>
                      <details>
                       <id>" . $id . "</id>
                      </details>
                </xmlrequest>";
        }


        $result = $this->sendRequest($xml, '', array(CURLOPT_TIMEOUT=>60));
        if (!$result) {
            return false;
        } else {
            $data = @simplexml_load_string($result);
            //print_r($data->data);
            if (isset($data->data) && $data->status == "SUCCESS") {
                $data = (array)($data->data);
                if (isset($data['item']) && is_array($data['item'])) {
                    //more contacts
                    $i = count($data['item']);
                    foreach ($data['item'] as $item) {
                        $arr[] = array(
                          'id' => (int)$item->contact_id,
                          'name' => (string)$item->name,
                          'surname' => (string)$item->surname,
                          'email' => (string)$item->emailaddress,
                          'added' => (string)$item->created,
                          'company' => (string)$item->company,
                          'customfields' => $item->customfields,
                        );
                    }
                    $count = $i;
                } else if (isset($data['item']) && isset($data['item']->contact_id)) {
                    //only one contact is present
                    $item = $data['item'];
                    $arr[] = array(
                      'id' => (int)$item->contact_id,
                      'name' => 'xxx',
                      'email' => (string)$item->emailaddress,
                      'added' => (string)$item->added,
                      'company' => (string)$item->company,
                      'customfields' => $item->customfields,
                    );
                    $count = 1;
                }
                return $arr;
            } else {
                $this->lastError = empty($data->errormessage)
                  ? (string)$data->errormessage
                  : __('neznámá chyba při komunikaci', 'cms_ve')
                ;
                return false;
            }
        }
    }

	public function get_contact($contactId, $withDetails) {
		$this->clearLastError();
		$login = get_option('ve_connect_se');

		$xml = "<xmlrequest>
      <username>{$login['login']}</username>
      <usertoken>{$login['password']}</usertoken>
      <requesttype>Contacts</requesttype>
      <requestmethod>getOne</requestmethod>
      <details>
      	<id>" . $contactId . "</id>
      </details>
      </xmlrequest>";
		$result = $this->sendRequest($xml);
		if ($result === false) {
			return false;
		} else {
			$data = @simplexml_load_string($result);
			if($data->status == "SUCCESS" && isset($data->data)) {
				date_default_timezone_set('Europe/Prague'); //potreba pro korektni funkci strtotime()
				$arr = array();
				$arr['id'] = (int)$data->data->id;
				$arr['created'] = strtotime($data->data->created);
				$arr['updated'] = strtotime($data->data->updated);
				$arr['blacklisted'] = (bool)$data->data->blacklisted;
				$arr['email'] = (string)$data->data->emailaddress;
				$arr['name'] = (string)$data->data->name;
				$arr['surname'] = (string)$data->data->surname;
				$arr['titlesbefore'] = (string)$data->data->titlesbefore;
				$arr['titlesafter'] = (string)$data->data->titlesafter;
				$arr['company'] = (string)$data->data->company;
				$arr['street'] = (string)$data->data->street;
				$arr['town'] = (string)$data->data->town;
				$arr['country'] = (string)$data->data->country;
				$arr['postalcode'] = (string)$data->data->postalcode;
				$arr['notes'] = (string)$data->data->notes;
				$arr['phone'] = (string)$data->data->phone;
				$arr['cellphone'] = (string)$data->data->cellphone;
				$arr['affilid'] = (string)$data->data->affilid;
				$arr['gender'] = (string)$data->data->gender;
				$arr['realname'] = (string)$data->data->realname;
				$custFields = array();
				if(isset($data->data->customfields)) {
					$decoded = json_decode(json_encode($data->data->customfields), 1);
					foreach ($decoded as $item) {
						$key = (int)$item['id'];
						$val = $item['value'];
						$custFields[$key] = $val;
					}
				}
				$arr['customfields'] = $custFields;
        unset($result);
				return $arr;
			} else {
				$this->lastError = (string)$data->errormessage;
        unset($result);
				return false;
			}
		}
	}

	public function get_customfields_structure() {
		$this->clearLastError();
		$login = get_option('ve_connect_se');

		$xml = "<xmlrequest>
      <username>{$login['login']}</username>
      <usertoken>{$login['password']}</usertoken>
      <requesttype>CustomFields</requesttype>
      <requestmethod>getAll</requestmethod>
      <details>
      </details>
      </xmlrequest>";
		$result = $this->sendRequest($xml);
		if ($result === false) {
			return false;
		} else {
			$data = @simplexml_load_string($result);
			if($data->status == "SUCCESS" && isset($data->data)) {
				return $data->data;
			} else {
				$this->lastError = (string)$data->errormessage;
        unset($result);
				return false;
			}
		}
	}

	public function print_form($element,$form,$css_id,$added) {
        global $vePage;
        
        $form['fields']['referrer']=array(
            'label'=>'',
            'html_input_name'=>'referrer',
            'content'=>"http" . (($_SERVER['SERVER_PORT'] == 443) ? "s://" : "://") . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
            'html_input_type'=>'hidden',             
        );
        $form['fields']['sessionid']=array(
            'label'=>'',
            'html_input_name'=>'sessionid',
            'content'=>'',
            'html_input_type'=>'hidden',              
        );
    
        $content=$vePage->print_seform($element,$form,$css_id);
        
        $content.='';
        $content.="<script type=\"text/javascript\">
jQuery(function($) {
	window._ssaq = window._ssaq || [];
	window._ssaq.push(['getSessionId', function(sessionId) {
		$('input[name=sessionid]').val(sessionId);
	}]);
});
</script>";
          
        return $content;
    }

    // return list of all lists
    public function get_lists_list($login)
    {
        $this->clearLastError();
        $xml = "
            <xmlrequest>
                <username>{$login['login']}</username>
                <usertoken>{$login['password']}</usertoken>
                <requesttype>ContactLists</requesttype>
                <requestmethod>getAll</requestmethod>
                <details>
                </details>
            </xmlrequest>
            ";

        $result = $this->sendRequest($xml);

        if ($result === false) {
            return false;

        } else {
            $data = @simplexml_load_string($result);
            if (isset($data->data)) {
                //Normalize result.
                $arr = array();
                foreach($data->data->item as $val) {
                    $arr[] = $val;
                }

                $itemized = new stdClass();
                $itemized->item = $arr;
                return $itemized;
            }
        }
        return false;
    }

    // save data to SE list
    public function save_to_list($listId, $email)
    {
        $this->clearLastError();
        $login = get_option('ve_connect_se');
        $xml = "<xmlrequest>
                    <username>{$login['login']}</username>
                    <usertoken>{$login['password']}</usertoken>
                    <requesttype>Contacts</requesttype>
                    <requestmethod>createupdate</requestmethod>
                    <details>
                            <emailaddress>{$email}</emailaddress>
                            <contactliststatuses>
                                <item>
                                    <id>{$listId}</id>
                                    <status>confirmed</status>
                                </item>
                            </contactliststatuses>
                    </details>
                </xmlrequest>";

        $result = $this->sendRequest($xml);
        if(!$result)
            return false;
        else {
            $data = @simplexml_load_string($result);
            if($data->status == "SUCCESS")
                return true;
            else {
                $this->lastError = (string)$data->errormessage;
            }
        }
        return false;
    }

    // save array data user to SE list
    public function save_to_list_details($listId, $email, $contactDetails = array(), $customFields = array())
    {
        $this->clearLastError();
        $login = get_option('ve_connect_se');
        $xml = "<xmlrequest>
                    <username>{$login['login']}</username>
                    <usertoken>{$login['password']}</usertoken>
                    <requesttype>Contacts</requesttype>
                    <requestmethod>createupdate</requestmethod>
                    <details>
                               <emailaddress>{$email}</emailaddress>";
        foreach ($contactDetails as $key => $val) {
            if ($val)
                $xml .= "                               <{$key}>{$val}</{$key}>\n";
        }
        
        //custom fields
        if(count($customFields)) {
            foreach ($customFields as $key => $val) {
            $xml .= "<customfields>";
                if ($val)
                    $xml .= "<item><id>{$key}</id><value>{$val}</value></item>\n";
            }
            $xml .= "</customfields>";
        }
        
        $xml .= "<contactliststatuses>
                                <item>
                                    <id>{$listId}</id>
                                    <status>confirmed</status>
                                </item>
                            </contactliststatuses>
                        </details>
                    </xmlrequest>";
        
        $result = $this->sendRequest($xml);
        if(!$result)
            return false;
        else {
            $data = @simplexml_load_string($result);
            if($data->status == "SUCCESS")
                return true;
            else {
                $this->lastError = (string)$data->errormessage;
            }
        }
        return false;
    }

    // get last field of list in SE

    public function get_last_enter($listId)
    {
        $this->clearLastError();
        $login = get_option('ve_connect_se');
        $emptyRes = array(
            'name' => '',
            'time' => 0,
            'count' => 0
        );

        $xml = "   
        <xmlrequest>
            <username>{$login['login']}</username>
            <usertoken>{$login['password']}</usertoken>
            <requesttype>ContactLists</requesttype>
            <requestmethod>getContacts</requestmethod>
              <details>
               <id>" . $listId . "</id>
              </details>
        </xmlrequest>";

        $result = $this->sendRequest($xml);
        if (!$result) {
            return $emptyRes;
        } else {
            $data = @simplexml_load_string($result);
            //print_r($data->data);
            $user_id = '';
            if (isset($data->data)) {
                $data = (array)($data->data);
                if (isset($data['item']) && is_array($data['item'])) {
                    $i = count($data['item']);
                    $user_id = $data['item'][$i - 1]->contact_id;
                    $time = $data['item'][$i - 1]->added;
                    $count = $i;
                } else if (isset($data['item']) && isset($data['item']->contact_id)) {
                    $user_id = $data['item']->contact_id;
                    $time = $data['item']->added;
                    $count = 1;
                }
            }
            if ($user_id) {
                $xml = " <xmlrequest>
                      <username>{$login['login']}</username>
                      <usertoken>{$login['password']}</usertoken>
                      <requesttype>Contacts</requesttype>
                      <requestmethod>getOne</requestmethod>
                      <details>
                          <id>" . $user_id . "</id>
                      </details>
                  </xmlrequest>";
                $result = $this->sendRequest($xml);
                if ($result === false) {
                    return $emptyRes;
                } else {
                    $data = @simplexml_load_string($result);
                    date_default_timezone_set('Europe/Prague'); //potreba pro korektni funkci strtotime()
                    return array(
                        'name' => (string)$data->data->name,
                        'time' => strtotime($time),
                        'count' => $count
                    );
                }

            } else return $emptyRes;
        }
    }

    // get list count

    public function get_list_count($listId)
    {
        $this->clearLastError();
        $login = get_option('ve_connect_se');

        $xml = "
        <xmlrequest>
            <username>{$login['login']}</username>
            <usertoken>{$login['password']}</usertoken>
            <requesttype>ContactLists</requesttype>
            <requestmethod>countContacts</requestmethod>
            <details>
                <id>" . $listId . "</id>
            </details>
        </xmlrequest>
        ";

        $result = $this->sendRequest($xml);
        if (!$result) {
            return false;
        } else {
            $data = @simplexml_load_string($result);
            return (int)($data->data);
        }
    }

    /**
     * @param $payload    string Vlastni pozadavek, neni-li jiz specifikovan pomocu URL.
     * @param string $url Volitelne URL, pokud se nema pouzit jednotne URL celeho API.
     * @param array $curlOptions Optional override of default CURL parameters. Include values like <code>
     *                           (CURLOPT_TIMEOUT => 20)</code> that should be overridden.
     * @return false|mixed Vrati false, pokud volani zcela selze. Jinak vrati prichozi data.
     */
    public function sendRequest($payload, $url = '', $curlOptions = array())
    {
        if (!$url)
            $url = $this->getApiUrl();
        $handle = curl_init($url);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($handle, CURLOPT_POST, 1);
        curl_setopt($handle, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, $this->verifySslPeer);
        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT ,2);
        curl_setopt($handle, CURLOPT_TIMEOUT, empty($curlOptions[CURLOPT_TIMEOUT]) ? 5 : (int)$curlOptions[CURLOPT_TIMEOUT]);

        try {
            $result = curl_exec($handle);

            try {
                $cError = curl_error($handle);
            } catch (Exception $e) {
                $cError = $e->getMessage();
            }
            if ($cError) {
                $this->lastError = 'SmartEmailing API call failed. CURL error: ' . $cError . '';
                $result = false;
            }
        } catch (Exception $e) {
            $this->lastError = 'SmartEmailing API call failed. CURL exec error: ' . $e->getMessage() . '';
            $result = false;
        }

        curl_close($handle);
        return $result;
    }
    
    public function getNewApi($login, $password) {
        $xml = '
            <xmlrequest>
                <username>' . $login . '</username>
                <usertoken>' . $password . '</usertoken>
                <requesttype>Users</requesttype>
                <requestmethod>generateApiKey</requestmethod>
                <details>
                    <application>'.get_home_url().'</application>  
                </details>
            </xmlrequest>
            ';
    
            $result = $this->sendRequest($xml);
    
            if ($result === false) {
                return 0;
            } else {
                $data = @simplexml_load_string($result);
                if ($data->status == "SUCCESS") return (string)$data->data; //return $data->data[0];  
                else return 0;
            }
    }

}

$apiConnection->registerApiClass('se', 'MioWebApiBridge_SE');
