<?php

namespace SmartSellingClient\HttpGateway;

use GuzzleHttp;


class GuzzleHttpClient implements IHttpClient
{
	/** @var GuzzleHttp\Client */
	private $client;


	public function __construct()
	{
		$this->client = new GuzzleHttp\Client();
	}


	public function sendHttpRequest(HttpRequest $httpRequest)
	{
		$options = $httpRequest->getOptions();
		$options['verify'] = __DIR__ . '/ca-bundle.pem';

		try {
			$response = $this->client->request(
				$httpRequest->getMethod(),
				$httpRequest->getUrl(),
				$options
			);

			$httpResponse = new HttpResponse(
				$response->getStatusCode(),
				$response->getHeaders(),
				(string) $response->getBody()
			);

		} catch (GuzzleHttp\Exception\TransferException $e) {
			throw new HttpException('Failed to execute an HTTP request.', $e->getCode(), $e);
		}

		return $httpResponse;
	}
}
