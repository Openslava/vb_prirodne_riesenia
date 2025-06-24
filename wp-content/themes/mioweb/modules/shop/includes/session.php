<?php
/**
 * Simple session helper class. Singleton. Helper accessors to session values.
 * Inspired by http://php.net/manual/en/function.session-start.php#102460
 *
 * User: kuba
 * Date: 09.03.16
 * Time: 10:54
 */


class MwsSessionHelper {
	const SESSION_STARTED = true;
	const SESSION_NOT_STARTED = false;

	/** @var bool If the session is started. */
	private $sessionActive = self::SESSION_NOT_STARTED;

	/** @var MwsSessionHelper THE only instance of the class */
	private static $instance;

	private function __construct() {}

	/**
	 * Returns THE instance of 'Session'.
	 * The session is automatically initialized if it wasn't.
	 * @return    MwsSessionHelper
	 **/
	public static function getInstance()
	{
		if ( !isset(self::$instance) ) {
			self::$instance = new self;
//			add_action( 'shutdown', array(self::$instance, 'saveSession' ), 20 );
		}
		self::$instance->startSession();
		return self::$instance;
	}

	/**
	 * (Re)starts the session.
	 * @return    bool    TRUE if the session has been initialized, else FALSE.
	 **/
	public function startSession()
	{
		$this->sessionActive = isset($_SESSION);
		if (!$this->sessionActive)
			$this->sessionActive = session_start();

		return $this->sessionActive;
	}

	public function saveSession() {
		mwshoplog(__METHOD__.' ...saving', MWLL_DEBUG, 'session');
		$this->destroy();
	}

	/**
	 *    Stores data into the session.
	 *    Example: $instance->foo = 'bar';
	 *
	 *    @param $name string Name of the data.
	 *    @param $value mixed Your data.
	 *    @return void
	 **/
	public function __set( $name , $value )
	{
		$_SESSION[$name] = $value;
	}

	/**
	 *    Gets datas from the session. Missing properties return null.
	 *    Example: echo $instance->foo;
	 *
	 *    @param $name string Name of the data to get.
	 *    @return mixed|null Data stored in session.
	 **/
	public function __get( $name )
	{
		if (isset($_SESSION[$name]))
			return $_SESSION[$name];
		else
			return null;
	}


	public function __isset( $name )
	{
		return isset($_SESSION[$name]);
	}


	public function __unset( $name )
	{
		unset( $_SESSION[$name] );
	}


	/**
	 * Destroys the current session.
	 * @return    bool    TRUE is session has been really destroyed, else FALSE.
	 **/
	public function destroy()
	{
		if ($this->sessionActive)
		{
			mwshoplog(__METHOD__, MWLL_DEBUG, 'session');
			$this->sessionActive = !session_destroy();
			unset( $_SESSION );
			return !$this->sessionActive;
		}
		return false;
	}
}