<?php

namespace MwShop\HttpClient;

use MwShop\HttpClient\Utils\Json;


class CurlHttpClient implements IHttpClient
{
	public function __construct()
	{
		if (!extension_loaded('curl')) {
			throw new NotSupportedException('cURL extension must be installed.');
		}
	}


	public function sendHttpRequest(HttpRequest $httpRequest)
	{
		$handle = curl_init();
		curl_setopt($handle, CURLOPT_URL, $httpRequest->getUrl());
		curl_setopt($handle, CURLOPT_CUSTOMREQUEST, $httpRequest->getMethod());
		curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($handle, CURLOPT_HEADER, true);
		curl_setopt($handle, CURLOPT_CAINFO, __DIR__ . '/ca-bundle.pem');

		$options = $httpRequest->getOptions();

		if (isset($options['headers'])) {
			if (isset($options['form_params'])) {
				static::setDefaultContentType($options['headers'], 'application/x-www-form-urlencoded');
			}

			if (isset($options['json'])) {
				static::setDefaultContentType($options['headers'], 'application/json');
			}
		}

		foreach ($options as $key => $value) {
			if ($key === 'form_params') {
				curl_setopt($handle, CURLOPT_POSTFIELDS, http_build_query($value, '', '&', PHP_QUERY_RFC1738));
			} elseif ($key === 'headers') {
				curl_setopt($handle, CURLOPT_HTTPHEADER, $this->formatHeaders($value));
			} elseif ($key === 'auth') {
				curl_setopt($handle, CURLOPT_USERPWD, $value[0] . ':' . $value[1]);
			} elseif ($key === 'body') {
				curl_setopt($handle, CURLOPT_POSTFIELDS, $value);
				if (defined('CURLOPT_SAFE_UPLOAD')) {
					curl_setopt($handle, CURLOPT_SAFE_UPLOAD, true);
				}
			} elseif ($key === 'json') {
				curl_setopt($handle, CURLOPT_POSTFIELDS, Json::encode($value));
				if (defined('CURLOPT_SAFE_UPLOAD')) {
					curl_setopt($handle, CURLOPT_SAFE_UPLOAD, true);
				}
			} elseif ($key === 'cookies') {
				if ($value !== null) {
					throw new NotSupportedException('CurlHttpClient does not support option cookies.');
				}
			} elseif ($key === 'timeout') {
				if ($value !== null) {
					curl_setopt($handle, CURLOPT_TIMEOUT, $value);
				}
			} elseif ($key === 'connect_timeout') {
				if ($value !== null) {
					curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, $value);
				}
			} else {
				throw new InvalidArgumentException("Option '$key' is not supported.");
			}
		}

		$result = curl_exec($handle);
		if ($result === false) {
			$error = curl_error($handle);
			$errno = curl_errno($handle);

			if ($errno === CURLE_OPERATION_TIMEOUTED) {
				throw new TimeLimitExceededException($error, $errno);
			}

			throw new HttpClientException($error, $errno);
		}

		$headerSize = curl_getinfo($handle, CURLINFO_HEADER_SIZE);
		$header = substr($result, 0, $headerSize);
		$body = (string) substr($result, $headerSize);

		$statusCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
		$headers = $this->parseHeaders($header);
		$httpResponse = new HttpResponse($statusCode, $headers, $body);

		curl_close($handle);

		return $httpResponse;
	}


	/**
	 * @param array $headers
	 * @return array
	 */
	private function formatHeaders($headers)
	{
		$result = array();
		foreach ($headers as $key => $values) {
			$values = is_array($values) ? $values : array($values);
			foreach ($values as $value) {
				$result[] = $key . ': ' . $value;
			}
		}
		return $result;
	}


	/**
	 * @param string $header
	 * @return array
	 */
	private function parseHeaders($header)
	{
		$headers = array();
		foreach (explode("\n", $header) as $line) {
			$line = trim($line);
			preg_match('#^([A-Za-z\-]+): (.*)\z#', $line, $match);
			if ($match) {
				$headers[$match[1]][] = $match[2];
			}
		}
		return $headers;
	}


	/**
	 * @param array $headers
	 * @param string $contentType
	 * @return void
	 */
	private static function setDefaultContentType(&$headers, $contentType)
	{
		foreach ($headers as $key => $value) {
			if (strcasecmp($key, 'Content-Type') === 0) {
				return;
			}
		}

		$headers['Content-Type'] = $contentType;
	}
}
