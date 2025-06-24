<?php

namespace SmartSellingClient;

use SmartSellingClient\HttpGateway\HttpMethod;
use SmartSellingClient\HttpGateway\HttpRequest;
use SmartSellingClient\HttpGateway\IHttpClient;
use SmartSellingClient\Utils\Json;
use SmartSellingClient\Utils\JsonException;


class SmartSellingApiClient implements ISmartSellingApiClient
{
	/**
	 * @var string
	 */
	private $apiUrl;

	/**
	 * @var string
	 */
	private $accessToken;

	/**
	 * @var IHttpClient
	 */
	private $httpClient;


	/**
	 * @param string $apiUrl
	 * @param string $accessToken
	 * @param IHttpClient $httpClient
	 */
	public function __construct($apiUrl, $accessToken, IHttpClient $httpClient)
	{
		$this->apiUrl = rtrim($apiUrl, '/');
		$this->accessToken = $accessToken;
		$this->httpClient = $httpClient;
	}


	public function getCurrentConnection()
	{
		return $this->sendRequest('/current-connection', HttpMethod::GET);
	}


	public function getConnections(array $parameters)
	{
		return $this->sendRequest('/connections?' . http_build_query($parameters), HttpMethod::GET);
	}


	/**
	 * @param string $path
	 * @param string $method
	 * @return array
	 * @throws InvalidAccessTokenException
	 */
	private function sendRequest($path, $method)
	{
		$httpRequest = new HttpRequest(
			$this->apiUrl . $path,
			$method,
			array(
				'headers' => array(
					'Accept' => 'application/json',
					'Authorization' => 'Bearer ' . $this->accessToken,
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
			return $responseData;
		}

		if ($httpResponse->getStatusCode() === 403) {
			throw new InvalidAccessTokenException();
		}

		throw new InvalidStateException('Response is not valid.');
	}
}
