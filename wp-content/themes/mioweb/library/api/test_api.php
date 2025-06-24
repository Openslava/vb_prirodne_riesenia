<?php
/**
 * Testing of Mioweb API integrations
 *
 * User: jakub.konas@gmail.com
 * Date: 2015-10-01
 *
 * -------------
 * Changelog
 * -------------
 * 2015-10-08
 *      - added compatibility for PHP 5.3, tests passed using 5.3.29
 *
 *
 */

define('TEST_EMAIL_DOMAIN', 'gmail.com');

require_once('api_class.php');

global $apiConnection;

function newLine() {
    global $_SERVER;
    $newLine = (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') !== false ? "<br />" : "") ."\n";
    return $newLine;
}

/**
 * Provede test a vypise zpravu na vystup.
 *
 * @param $condition bool Testovana podminka
 * @param $method string Nazev testovane metody
 * @param string $message
 */
function check($condition, $method, $message = "") {
    echo
        (strpos($method, 'TestClientApi::')===false ? '> ' : '')
        . str_pad("$method ", 60, "."). " "  . ($condition ? "OK" : "FAILED" . ($message ? " >> $message" : ''))
        . newLine();
    if (!$condition)
        die(1);
}


function info($message="", $noNewLine=false) {
    echo ($message ? $message : '') . ($noNewLine ? '' : newLine());
}

//fake
function add_action($a, $b) {}
define('SE_API','../..'.'/library/api/se/base.php');
define('FAPI_API','../..'.'/library/api/fapi/FAPIClient.php');

/** Simulate WP function get_option(). */
function get_option() {
    //fake implementace, ktera simuluje vraceni login udaju pro API ulozene jako option ve WP.
    //Zde si vystaci vytazenim loginu z aktualni testovaci tridy.
    global $testApis;


    $apiOptionPrefix='';
    $login='';
    $password='';

    if (isset($testApis) && isset($testApis->curTestSuite)) {
        $apiOptionPrefix = $testApis->curTestSuite->getApiId();
        $login = $testApis->curTestSuite->getLogin();
        $password = $testApis->curTestSuite->getPassword();
    }

    if ($apiOptionPrefix=='se')
        $apiOptionPrefix = '';
    if ($apiOptionPrefix!=='')
        $apiOptionPrefix = $apiOptionPrefix . '_';

    return array(
        $apiOptionPrefix.'login' => $login,
        $apiOptionPrefix.'password' => $password
    );
}

/** Simulate WP localization function. */
function __($text, $domain='') {
    return $text;
}




/**
 * Class TestClientApi is a test suite of one API.
 *
 * An instance is created with login credentials of an API. To run all tests call the @link{run()} method.
 * Methods starting with the "test_" prefix are test cases.
 */
class TestClientApi {

    /**
     * An instance of the tested class. It is a ancestor of @link(MioWebApiBridgeBase) class.
     * @var MioWebApiBridgeBase
     */
    private $inst;
    /**
     * Login to the API.
     * @var string
     */
    private $login;
    /**
     * Password to the API.
     * @var string
     */
    private $password;
    /** API id of tested class
     * @var string
     */
    private $apiId;

    public function getApiId() {
        return $this->apiId;
    }

    /**
     * Creates a new test suite class.
     * @param $apiId string     ID of an API that should be tested
     * @param $login string     Login name for tested API
     * @param $password string  Password for tested API
     */
    function __construct($apiId, $login, $password) {
        global $apiConnection;

        $this->apiId = $apiId;
        $this->login = $login;
        $this->password = $password;
        $this->inst = $apiConnection->getClient($apiId);
    }

    /** Checks the connection status. */
    function test_is_connected() {
        $inst = $this->inst;
        $res = $inst->is_connected($this->login, $this->password);
        check($res, __METHOD__, $inst->getLastError());
    }

    /** Check whether list of forms can be retrieved. To pass the test at least one form should be present. */
    function test_get_forms_list() {
        $inst = $this->inst;
        $res = $inst->get_forms_list(array(
            'login' => $this->login,
            'password' => $this->password
        ));
        check($res, __METHOD__, $inst->getLastError());
        check(is_object($res), '  is_object(result)');
        check(isset($res->item), '  res->item exists');
        check(is_array($res->item), ' res->item is array');
        check((count($res->item) > 0), '  res->item has some items');
        check(isset(reset($res->item)->id), '  first_item->id is set');
        check(isset(reset($res->item)->name), '  first_item->name is set');
//        $this->printOnlyFields_List($res, array('name', 'guid'), '  ');
    }

    /** Check that details of the first form from the list of forms can be retrieved. */
    function test_get_form() {
        global $apiConnection;

        $inst = $this->inst;
        //Zjistit ID prvniho dostupneho formulare.
        $forms = $inst->get_forms_list(array(
            'login' => $this->login,
            'password' => $this->password
        ));
        $res = false;
        if ($forms && isset($forms->item) && is_array($forms->item)) {
            $id = (string) reset($forms->item)->id;
            $res = $inst->get_form($id);
        }
        check($res, __METHOD__, $inst->getLastError());
        // Does it supports fields? Then check necessary values.
        if ($apiConnection->api_list[$this->apiId]['fields']) {
            check(is_array($res), '  is_array(result)');
            check(is_array($res['fields']), '  is_array(fields)');
            check((count($res['fields']) > 0), '  fields[] has one item at least');
            check(isset($res['url']), '  URL is set');
            check(isset($res['submit']), '  SUBMIT text is set');
        }
//        check(isset($res['form']->name), '  res->form->name is set');
//        $this->printOnlyFields_List($res->form, array('name', 'guid'), '  ');
    }

    /** Checks that list of mailing lists can be retrieved. To pass the test at least one list should be present. */
    function test_get_lists_list() {
        $inst = $this->inst;
        $res = $inst->get_lists_list(array(
            'login' => $this->login,
            'password' => $this->password
        ));
        check($res, __METHOD__, $inst->getLastError());
        check(is_object($res), '  is_object(result)');
        check(isset($res->item), '  res->item exists');
        check(is_array($res->item), ' res->item is array');
        check((count($res->item) > 0), '  res->item has some items');
        check(isset(reset($res->item)->id), '  first_item->id is set');
        check(isset(reset($res->item)->name), '  first_item->name is set');
//        $this->printOnlyFields_List($res, array('name', 'guid'), '  ');
    }

    /** Checks that registration of an email works. At least on email list must be present. */
    function test_save_to_list() {
        $inst = $this->inst;
        //ID of the first list
        $lists = $inst->get_lists_list(array(
            'login' => $this->login,
            'password' => $this->password
        ));
        $res = false;
        if ($lists && isset($lists->item) && is_array($lists->item)) {
            $listId = (string) reset($lists->item)->id;
            $newEmail = "mirekraw". round(microtime(true)*1000) . "@" . TEST_EMAIL_DOMAIN;
            $res = $inst->save_to_list($listId, $newEmail);
        }
        check($res, __METHOD__, $inst->getLastError());
        echo ">-  $newEmail" . newLine();
    }

    /** Checks that registration of an already registered email works. At least on email list must be present. */
    function test_save_to_list_update() {
        $inst = $this->inst;
        //ID of the first list
        $lists = $inst->get_lists_list(array(
            'login' => $this->login,
            'password' => $this->password
        ));
        $res = false;
        if ($lists && isset($lists->item) && is_array($lists->item)) {
            $listId = (string) reset($lists->item)->id;
            $newEmail = "mirekraw". round(microtime(true)*1000) . "@" . TEST_EMAIL_DOMAIN;
            $res1 = $inst->save_to_list($listId, $newEmail);
//            check($res1, __METHOD__, $inst->getLastError());
            $res = $inst->save_to_list($listId, $newEmail);
        }
        check(true,  __METHOD__, $inst->getLastError());
        check(true, ' overriding contacts IS' . ($res ? '':' NOT') . ' supported');
        if ($inst->getLastError())
            check(true, $inst->getLastError());
//        echo "  $newEmail" . newLine();
    }

    /** Checks that registration of an email with details works. At least on email list must be present. */
    function test_save_to_list_detail() {
        $inst = $this->inst;
        //Id of the first list
        $lists = $inst->get_lists_list(array(
            'login' => $this->login,
            'password' => $this->password
        ));
        $res = false;
        if ($lists && isset($lists->item) && is_array($lists->item)) {
            $listId = (string) reset($lists->item)->id;
            $unique = round(microtime(true)*1000);
            $newEmail = "mirekdetail". $unique . "@" . TEST_EMAIL_DOMAIN;
            $res = $inst->save_to_list_details($listId, $newEmail, array(
                'name' => "MirekDetail {$unique}",
                'surname' => "Detail{$unique}",
                'ip' => '192.168.1.1'
              ),
              array(
                  //Custom fields
              )
            );
        }
        check($res, __METHOD__, $inst->getLastError());
//        echo "  $newEmail" . newLine();
    }

    /** Checks that method to get last added email works correctly. */
    function test_get_last_enter() {
        $inst = $this->inst;
        //Id of the first list.
        $lists = $inst->get_lists_list(array(
            'login' => $this->login,
            'password' => $this->password
        ));
        $res = false;
        if ($lists && isset($lists->item) && is_array($lists->item)) {
            $listId = (string) reset($lists->item)->id;
            $res = $inst->get_last_enter($listId);
        }
        check($res, __METHOD__, $inst->getLastError());
        if ($res) {
            date_default_timezone_set('Europe/Prague');
            echo "  name={$res['name']}  time=" . date('Y-m-d H:i:s', $res['time']) . "  count={$res['count']}" . newLine();
        }

    }

    /** Checks that number of contacts in a list can be received. At least one mailing list must be present.*/
    function test_get_list_count() {
        $inst = $this->inst;
        $lists = $inst->get_lists_list(array(
            'login' => $this->login,
            'password' => $this->password
        ));
        $res = false;
        if ($lists && isset($lists->item) && is_array($lists->item)) {
            $listId = (string) reset($lists->item)->id;
            $res = $inst->get_list_count($listId);
        }
        check($res!==false, __METHOD__, $inst->getLastError());
        if ($res) {
            check(is_numeric($res), ' result is a number');
            echo "  count={$res}" . newLine();
        }
    }

    /**
     * Run all tests.
     */
    function run() {
        echo "" . newLine();
        echo "-----------------------------------------" . newLine();
        echo "Testing API of [". $this->inst->getApiName() ."]" . newLine();
        echo "-----------------------------------------" . newLine();
        $this->test_is_connected();
        $this->test_get_forms_list();
        $this->test_get_form();
        $this->test_get_lists_list();
        $this->test_save_to_list();
        $this->test_save_to_list_update();
        $this->test_save_to_list_detail();
        $this->test_get_last_enter();
        $this->test_get_list_count();
    }

    /**
     * Skip all tests. Print out only information that the tests were skipped.
     */
    function skip() {
        echo "" . newLine();
        echo "-----------------------------------------" . newLine();
        echo "Testing API of [". $this->inst->getApiName() ."]  -- SKIPPED" . newLine();
        echo "-----------------------------------------" . newLine();
    }

    /**
     * Gets the API login name.
     * @return string
     */
    public function getLogin() {
        return $this->login;
    }

    /**
     * Gets the API login password.
     * @return string
     */
    public function getPassword() {
        return $this->password;
    }

    private function printOnlyFields_List(& $itemized, $fieldNames, $prefix = '') {
        $i = 0;
        foreach($itemized->item as $value) {
            echo $prefix  ."[$i] " . newLine();
            $this->printOnlyFields($value, $fieldNames, $prefix.'  ');
            $i++;
        }
    }
    /**
     * Vypise hodnoty tech fieldu nebo atributu polozky $item, ktere jsou vyjmenovany ve $fieldNames.
     * @param $item array|object Prvek, jehoz atributy maji byt vypsany.
     * @param $fieldNames array Seznam nazvu poli, ktere se maji vypsat. Ostatni hodnoty vypsany nebudou.
     */
    private function printOnlyFields($item, $fieldNames, $prefix = '') {
        if (!is_array($fieldNames))
            $fieldNames = array($fieldNames);
        if (!$item)
            echo "null" . newLine();
        else {
            $_arr = is_object($item) ? get_object_vars($item) : $item;
            foreach($_arr as $key => $value) {
                if (in_array($key, $fieldNames))
                  echo $prefix . $key. "=" . substr($value, 0) . newLine();
            }
        }
    }
}


/**
 * Class TestApis is a master class that tops the test procedures. It contains necessary login credentials of registered
 * APIs, have possibility to test only a specific API.
 */
class TestApis {
    private $apis = array();
    /**
     * Contains current test suite class.
     * @var TestClientApi
     */
    public $curTestSuite = null;

    function __construct() {
        global $apiConnection;

        foreach($apiConnection->api_list as $key => $definition) {
            $this->registerApi($key, '', '', True);
        }
    }

    /**
     * Sets login parameters of an API denoted by {@link $apiId}. Can be used to disable testing of an API
     * by passing <code>false</code> to {@link $enabled}.
     *
     * @param $apiId string         Identifier on an API.
     * @param $login string         Login credential.
     * @param $password string      Password credential.
     * @param bool|true $enabled    Whether API should be tested (<code>true</code>) or not (<code>false</code>).
     */
    public function registerApi($apiId, $login, $password, $enabled=true) {
        $item = array('login'=>$login, 'password'=>$password, 'enabled'=>$enabled);
        $this->apis[$apiId] = $item;
    }

    public function runTests() {
        global $apiConnection;

        info("Credentials:");
        foreach($this->apis as $key => $definition) {
            info(sprintf("  %s: enabled=%s login=%s passw=%s",
                $key, $definition['enabled'], $definition['login'], $definition['password'])
            );
        }
        newLine();

        foreach($apiConnection->api_list as $key => $definition) {
            $settings = $this->apis[$key];
            $this->curTestSuite = new TestClientApi($key, $settings['login'], $settings['password']);
            if ($settings['enabled'])
                $this->curTestSuite->run();
            else
                $this->curTestSuite->skip();
        }

    }
}

info("PHP version: " . phpversion());
info();

//Create master class to run all tests.
$testApis = new TestApis();

//Fill in login credentials from an external file if the file is present.
//File should contain calls of a global variable $testApis->registerApi().
chdir(__DIR__);
if (file_exists('test_api_credentials.php')) {
    info("Loading file with credentials.");
    require_once('test_api_credentials.php');
} else {
    info("File with credentials is missing!");
    die();
}


//Run tests of enabled APIs.
$testApis->runTests();