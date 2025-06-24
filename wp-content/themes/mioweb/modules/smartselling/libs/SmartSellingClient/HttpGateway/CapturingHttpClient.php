<?php

namespace SmartSellingClient\HttpGateway;

use Tester\Dumper;


class CapturingHttpClient implements IHttpClient
{
	/**
	 * @var IHttpClient
	 */
	private $httpClient;

	/**
	 * @var HttpRequest[]
	 */
	private $httpRequests = array();

	/**
	 * @var HttpResponse[]
	 */
	private $httpResponses = array();


	public function __construct(IHttpClient $httpClient)
	{
		$this->httpClient = $httpClient;
	}


	public function sendHttpRequest(HttpRequest $httpRequest)
	{
		$httpResponse = $this->httpClient->sendHttpRequest($httpRequest);

		$this->capture($httpRequest, $httpResponse);

		return $httpResponse;
	}


	/**
	 * @param HttpRequest $httpRequest
	 * @param HttpResponse $httpResponse
	 * @return void
	 */
	private function capture(HttpRequest $httpRequest, HttpResponse $httpResponse)
	{
		foreach ($this->httpRequests as $index => $matchedHttpRequests) {
			if ($this->matchHttpRequest($matchedHttpRequests, $httpRequest)) {
				return;
			}
		}

		$this->httpRequests[] = $httpRequest;
		$this->httpResponses[] = $httpResponse;
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


	/**
	 * @param string $fileName
	 * @param string $className
	 * @return void
	 */
	public function writeToPhpFile($fileName, $className)
	{
		preg_match('#^(?:(.*)\\\\)?([^\\\\]+)\z#', $className, $match);
		list(, $namespace, $className) = $match;

		$code = '<?php' . "\n";
		$code .= "\n";

		if ($namespace) {
			$code .= 'namespace ' . $namespace . ';' . "\n";
			$code .= "\n";
		}

		$code .= 'use SmartSellingClient\HttpGateway\HttpRequest;' . "\n";
		$code .= 'use SmartSellingClient\HttpGateway\HttpResponse;' . "\n";
		$code .= 'use SmartSellingClient\HttpGateway\MockHttpClient;' . "\n";
		$code .= "\n";
		$code .= "\n";
		$code .= 'class ' . $className . ' extends MockHttpClient' . "\n";
		$code .= '{' . "\n";
		$code .= "\t" . 'public function __construct()' . "\n";
		$code .= "\t" . '{' . "\n";

		foreach ($this->httpRequests as $index => $httpRequest) {
			$httpResponse = $this->httpResponses[$index];

			$code .= "\t\t" . '$this->add(' . "\n";
			$code .= "\t\t\t" . 'new HttpRequest(' . "\n";
			$code .= "\t\t\t\t" . $this->exportValue($httpRequest->getUrl(), "\t\t\t\t") . ",\n";
			$code .= "\t\t\t\t" . $this->exportValue($httpRequest->getMethod(), "\t\t\t\t") . ",\n";
			$code .= "\t\t\t\t" . $this->exportValue($httpRequest->getOptions(), "\t\t\t\t") . "\n";
			$code .= "\t\t\t" . '),' . "\n";
			$code .= "\t\t\t" . 'new HttpResponse(' . "\n";
			$code .= "\t\t\t\t" . $this->exportValue($httpResponse->getStatusCode(), "\t\t\t\t") . ",\n";
			$code .= "\t\t\t\t" . $this->exportValue($httpResponse->getHeaders(), "\t\t\t\t") . ",\n";
			$code .= "\t\t\t\t" . $this->exportValue($httpResponse->getBody(), "\t\t\t\t") . "\n";
			$code .= "\t\t\t" . ')' . "\n";
			$code .= "\t\t" . ');' . "\n";
		}

		$code .= "\t" . '}' . "\n";
		$code .= '}' . "\n";

		file_put_contents($fileName, $code);
	}


	private function exportValue($value, $indent = '')
	{
		$s = Dumper::toPhp($value);
		$s = str_replace("\n", "\n" . $indent, $s);
		return $s;
	}
}
