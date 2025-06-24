<?php

namespace SmartSellingClient\HttpGateway;

use SmartSellingClient\InvalidArgumentException;


class MockHttpClient implements IHttpClient
{
	/**
	 * @var HttpRequest[]
	 */
	private $httpRequests = array();

	/**
	 * @var HttpResponse[]
	 */
	private $httpResponses = array();


	/**
	 * @param HttpRequest $httpRequest
	 * @param HttpResponse $httpResponse
	 * @return void
	 */
	public function add(HttpRequest $httpRequest, HttpResponse $httpResponse)
	{
		$this->httpRequests[] = $httpRequest;
		$this->httpResponses[] = $httpResponse;
	}


	/**
	 * @param HttpRequest $httpRequest
	 * @return HttpResponse
	 */
	public function sendHttpRequest(HttpRequest $httpRequest)
	{
		foreach ($this->httpRequests as $index => $matchedHttpRequests) {
			if ($this->matchHttpRequest($matchedHttpRequests, $httpRequest)) {
				return $this->httpResponses[$index];
			}
		}

		throw new InvalidArgumentException('Unknown HTTP request.');
	}


	/**
	 * @param HttpRequest $expected
	 * @param HttpRequest $actual
	 * @return bool
	 */
	private function matchHttpRequest(HttpRequest $expected, HttpRequest $actual)
	{
		return $expected->getUrl() === $actual->getUrl()
			&& $expected->getMethod() === $actual->getMethod()
			&& $expected->getOptions() === $actual->getOptions();
	}
}