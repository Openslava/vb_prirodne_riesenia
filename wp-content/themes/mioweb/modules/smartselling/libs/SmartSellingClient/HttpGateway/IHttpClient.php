<?php

namespace SmartSellingClient\HttpGateway;


interface IHttpClient
{
	/**
	 * @param HttpRequest $httpRequest
	 * @return HttpResponse
	 */
	function sendHttpRequest(HttpRequest $httpRequest);
}
