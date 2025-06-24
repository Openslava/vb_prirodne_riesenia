<?php

spl_autoload_register(function($type) {
	static $paths = array(
		'SmartSellingClient\\AuthenticationException' => 'exceptions.php',
		'SmartSellingClient\\HttpGateway\\CapturingHttpClient' => 'HttpGateway/CapturingHttpClient.php',
		'SmartSellingClient\\HttpGateway\\CurlHttpClient' => 'HttpGateway/CurlHttpClient.php',
		'SmartSellingClient\\HttpGateway\\HttpException' => 'HttpGateway/HttpException.php',
		'SmartSellingClient\\HttpGateway\\HttpMethod' => 'HttpGateway/HttpMethod.php',
		'SmartSellingClient\\HttpGateway\\HttpRequest' => 'HttpGateway/HttpRequest.php',
		'SmartSellingClient\\HttpGateway\\HttpResponse' => 'HttpGateway/HttpResponse.php',
		'SmartSellingClient\\HttpGateway\\IHttpClient' => 'HttpGateway/IHttpClient.php',
		'SmartSellingClient\\HttpGateway\\MockHttpClient' => 'HttpGateway/MockHttpClient.php',
		'SmartSellingClient\\ISmartSellingApiClient' => 'ISmartSellingApiClient.php',
		'SmartSellingClient\\ISmartSellingApiClientFactory' => 'ISmartSellingApiClientFactory.php',
		'SmartSellingClient\\ISmartSellingLoginClient' => 'ISmartSellingLoginClient.php',
		'SmartSellingClient\\InvalidAccessTokenException' => 'exceptions.php',
		'SmartSellingClient\\InvalidArgumentException' => 'exceptions.php',
		'SmartSellingClient\\InvalidClientIdException' => 'exceptions.php',
		'SmartSellingClient\\InvalidClientSecretException' => 'exceptions.php',
		'SmartSellingClient\\InvalidEmailException' => 'exceptions.php',
		'SmartSellingClient\\InvalidPasswordException' => 'exceptions.php',
		'SmartSellingClient\\InvalidStateException' => 'exceptions.php',
		'SmartSellingClient\\SampleSmartSellingLoginClient' => 'SampleSmartSellingLoginClient.php',
		'SmartSellingClient\\SmartSellingApiClient' => 'SmartSellingApiClient.php',
		'SmartSellingClient\\SmartSellingApiClientFactory' => 'SmartSellingApiClientFactory.php',
		'SmartSellingClient\\SmartSellingLoginClient' => 'SmartSellingLoginClient.php',
		'SmartSellingClient\\Utils\\Callback' => 'Utils/Callback.php',
		'SmartSellingClient\\Utils\\Json' => 'Utils/Json.php',
		'SmartSellingClient\\Utils\\JsonException' => 'Utils/Json.php',
	);

	$type = ltrim($type, '\\'); // PHP namespace bug #49143

	if (isset($paths[$type])) {
		require_once __DIR__ . '/' . $paths[$type];
	}
});
