<?php

namespace SmartSellingClient;


class SampleSmartSellingLoginClient implements ISmartSellingLoginClient
{
	/**
	 * @var string[]
	 */
	private $users = array(
		'johndoe@example.com' => 'xxx',
	);

	/**
	 * @var string
	 */
	private $accessTokens = array(
		'johndoe@example.com' => 'randomtoken',
	);

	/**
	 * @var string
	 */
	private $autoLoginUrl;


	/**
	 * @param string $autoLoginUrl
	 */
	public function __construct($autoLoginUrl)
	{
		$this->autoLoginUrl = $autoLoginUrl;
	}


	public function tryToLogin($email, $password, $remember, $redirectCallback)
	{
		$email = strtolower($email);
		if (!isset($this->users[$email])) {
			throw new InvalidEmailException();
		}
		if ($this->users[$email] !== $password) {
			throw new InvalidPasswordException();
		}
		call_user_func($redirectCallback, $this->autoLoginUrl, array(
			'smartselling_login' => '1',
			'access_token' => $this->accessTokens[$email],
		));
	}


	public function handleAutologin(array $postData, $loginCallback)
	{
		if (!isset($postData['smartselling_login'], $postData['access_token'])) {
			return;
		}

		$accessToken = (string) $postData['access_token'];
		$email = array_search($accessToken, $this->accessTokens);
		if ($email === false) {
			throw new InvalidAccessTokenException();
		}
		call_user_func($loginCallback, $email);
	}
}
