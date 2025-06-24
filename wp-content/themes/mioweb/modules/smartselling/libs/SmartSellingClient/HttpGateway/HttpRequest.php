<?php

namespace SmartSellingClient\HttpGateway;

use SmartSellingClient\InvalidArgumentException;


class HttpRequest
{
	/**
	 * @var string
	 */
	private $url;

	/**
	 * @var string
	 */
	private $method;

	/**
	 * @var array
	 */
	private $options;


	/**
	 * @param string $url
	 * @param string $method
	 * @param array $options
	 */
	public function __construct($url, $method = HttpMethod::GET, array $options = array())
	{
		if (!is_string($url)) {
			throw new InvalidArgumentException('Parameter url must be an URL.');
		}

		if (!HttpMethod::isValid($method)) {
			throw new InvalidArgumentException('Parameter method must be a HTTP method.');
		}

		$this->url = $url;
		$this->method = $method;
		$this->options = $options;
	}


	/**
	 * @return string
	 */
	public function getUrl()
	{
		return $this->url;
	}


	/**
	 * @return string
	 */
	public function getMethod()
	{
		return $this->method;
	}


	/**
	 * @return array
	 */
	public function getOptions()
	{
		return $this->options;
	}
}
