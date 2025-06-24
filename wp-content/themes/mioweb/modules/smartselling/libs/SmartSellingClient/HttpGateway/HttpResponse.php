<?php

namespace SmartSellingClient\HttpGateway;


class HttpResponse
{
	/**
	 * @var int
	 */
	private $statusCode;

	/**
	 * @var string[]
	 */
	private $headers;

	/**
	 * @var string
	 */
	private $body;


	/**
	 * @param int $statusCode
	 * @param string[] $headers
	 * @param string $body
	 */
	public function __construct($statusCode, array $headers, $body)
	{
		$this->statusCode = $statusCode;
		$this->headers = $headers;
		$this->body = $body;
	}


	/**
	 * @return int
	 */
	public function getStatusCode()
	{
		return $this->statusCode;
	}


	/**
	 * @return string[]
	 */
	public function getHeaders()
	{
		return $this->headers;
	}


	/**
	 * @return string
	 */
	public function getBody()
	{
		return $this->body;
	}
}
