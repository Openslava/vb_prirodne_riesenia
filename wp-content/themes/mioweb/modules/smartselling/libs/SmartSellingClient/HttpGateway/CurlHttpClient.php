<?php

namespace SmartSellingClient\HttpGateway;

use SmartSellingClient\InvalidArgumentException;


class CurlHttpClient implements IHttpClient
{
	public function sendHttpRequest(HttpRequest $httpRequest)
	{
		$handle = curl_init();
		curl_setopt($handle, CURLOPT_URL, $httpRequest->getUrl());
		curl_setopt($handle, CURLOPT_CUSTOMREQUEST, $httpRequest->getMethod());
		curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($handle, CURLOPT_HEADER, true);
		curl_setopt($handle, CURLOPT_CAINFO, __DIR__ . '/ca-bundle.pem');

		$options = $httpRequest->getOptions();

		foreach ($options as $key => $value) {
			if ($key === 'form_params') {
				curl_setopt($handle, CURLOPT_POSTFIELDS, $value);
			} elseif ($key === 'headers') {
				curl_setopt($handle, CURLOPT_HTTPHEADER, $this->formatHeaders($value));
			} elseif ($key === 'exceptions') {
				// skip
			} else {
				throw new InvalidArgumentException("Option '$key' is not supported.");
			}
		}

		$result = curl_exec($handle);
		if ($result === false) {
			throw new HttpException(curl_error($handle));
		}

		$headerSize = curl_getinfo($handle, CURLINFO_HEADER_SIZE);
		$header = substr($result, 0, $headerSize);
		$body = substr($result, $headerSize);

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
		foreach ($headers as $key => $value) {
			$result[] = $key . ': ' . $value;
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
}


