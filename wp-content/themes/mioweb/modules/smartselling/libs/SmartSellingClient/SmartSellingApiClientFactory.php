<?php

namespace SmartSellingClient;

use SmartSellingClient\HttpGateway\HttpMethod;
use SmartSellingClient\HttpGateway\HttpRequest;
use SmartSellingClient\HttpGateway\IHttpClient;
use SmartSellingClient\Utils\Json;
use SmartSellingClient\Utils\JsonException;


class SmartSellingApiClientFactory implements ISmartSellingApiClientFactory
{
	/**
	 * @var string
	 */
	private $apiUrl;

	/**
	 * @var IHttpClient
	 */
	private $httpClient;


	/**
	 * @param string $apiUrl
	 * @param IHttpClient $httpClient
	 */
	public function __construct($apiUrl, IHttpClient $httpClient)
	{
		$this->apiUrl = rtrim($apiUrl, '/');
		$this->httpClient = $httpClient;
	}


	public function createClient($clientId, $clientSecret)
	{
		$accessToken = $this->generateAccessToken($clientId, $clientSecret);
		return new SmartSellingApiClient($this->apiUrl, $accessToken, $this->httpClient);
	}


	/**
	 * @param string $clientId
	 * @param string $clientSecret
	 * @return string
	 * @throws InvalidClientIdException
	 * @throws InvalidClientSecretException
	 * @throws InvalidEmailException
	 * @throws InvalidPasswordException
	 * @throws InvalidStateException
	 */
	private function generateAccessToken($clientId, $clientSecret)
	{
		$httpRequest = new HttpRequest(
			$this->apiUrl . '/access_token',
			HttpMethod::POST,
			array(
				'form_params' => array(
					'client_id' => $clientId,
					'client_secret' => $clientSecret,
					'grant_type' => 'client_credentials',
					'scope' => 'smartselling',
				),
				'headers' => array(
					'Accept' => 'application/json',
				),
				'exceptions' => false,
			)
		);

		$httpResponse = $this->httpClient->sendHttpRequest($httpRequest);

		try {
			$responseData = Json::decode($httpResponse->getBody(), Json::FORCE_ARRAY);
		} catch (JsonException $e) {
			throw new InvalidStateException('Response is not valid.');
		}

		if ($httpResponse->getStatusCode() === 200) {
			if (isset($responseData['access_token']) && is_string($responseData['access_token'])) {
				return $responseData['access_token'];
			}
		}

		if ($httpResponse->getStatusCode() === 403) {
			if (isset($responseData['error']['message']) && is_string($responseData['error']['message'])) {
				$message = $responseData['error']['message'];
				if ($message === 'Invalid client ID.') {
					throw new InvalidClientIdException();
				}
				if ($message === 'Invalid client secret.') {
					throw new InvalidClientSecretException();
				}
				if ($message === 'Invalid email.') {
					throw new InvalidEmailException();
				}
				if ($message === 'Invalid password.') {
					throw new InvalidPasswordException();
				}
			}
		}

		throw new InvalidStateException('Response is not valid.');
	}
}
