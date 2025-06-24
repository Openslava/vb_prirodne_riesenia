<?php

namespace SmartSellingClient;

use SmartSellingClient\HttpGateway\IHttpClient;


class SmartSellingLoginClient implements ISmartSellingLoginClient
{
	/**
	 * @var string
	 */
	private $baseUrl;

	/**
	 * @var string
	 */
	private $clientId;

	/**
	 * @var string
	 */
	private $clientSecret;

	/**
	 * @var IHttpClient
	 */
	private $httpClient;

	/**
	 * @var SmartSellingApiClientFactory
	 */
	private $clientFactory;


	/**
	 * @param string $baseUrl
	 * @param string $clientId
	 * @param string $clientSecret
	 * @param IHttpClient $httpClient
	 */
	public function __construct(
		$baseUrl,
		$clientId,
		$clientSecret,
		IHttpClient $httpClient
	) {
		$this->baseUrl = rtrim($baseUrl, '/');
		$this->clientId = $clientId;
		$this->clientSecret = $clientSecret;
		$this->httpClient = $httpClient;
		$this->clientFactory = new SmartSellingApiClientFactory($this->baseUrl . '/api/', $this->httpClient);
	}


	public function tryToLogin($email, $password, $remember, $redirectCallback)
	{
		if (!is_string($email)) {
			throw new \InvalidArgumentException('Parameter email must be a string.');
		}

		if (!is_string($password)) {
			throw new \InvalidArgumentException('Parameter password must be a string.');
		}

		if (!is_callable($redirectCallback)) {
			throw new \InvalidArgumentException('Parameter redirectCallback must be callable.');
		}

		if (strpos($email, '@') === false) {
			throw new InvalidEmailException();
		}

		$client = $this->clientFactory->createClient($email, $password);
		$connections = $client->getConnections(array(
			'oauth_client.client_id' => $this->clientId,
			'limit' => 1,
		));

		if (!isset($connections[0]['id'])) {
			throw new InvalidStateException('Connection does not exist.');
		}

		$url = $this->baseUrl . '/?' . http_build_query(array('connectionId' => $connections[0]['id']));

		$postData = array();
		$postData['email'] = $email;
		$postData['password'] = $password;

		if ($remember) {
			$postData['remember'] = 'on';
		}

		$postData['do'] = 'signInControl-signInForm-submit';

		call_user_func($redirectCallback, $url, $postData);
	}


	public function handleAutologin(array $postData, $loginCallback)
	{
		if (!isset($postData['smartselling_login'], $postData['access_token']) || !is_string($postData['access_token'])) {
			return;
		}

		$client = new SmartSellingApiClient($this->baseUrl . '/api/', $postData['access_token'], $this->httpClient);
		$connection = $client->getCurrentConnection();

		if (!isset($connection['user']['email']) || !is_string($connection['user']['email'])) {
			throw new InvalidStateException('Response is not valid.');
		}

		if (!isset($connection['oauth_client']['client_id']) || !is_string($connection['oauth_client']['client_id'])) {
			throw new InvalidStateException('Response is not valid.');
		}

		$email = $connection['user']['email'];
		$clientId = $connection['oauth_client']['client_id'];

		if ($clientId !== $this->clientId) {
			throw new InvalidStateException('Invalid client ID.');
		}

		call_user_func($loginCallback, $email);
	}
}
