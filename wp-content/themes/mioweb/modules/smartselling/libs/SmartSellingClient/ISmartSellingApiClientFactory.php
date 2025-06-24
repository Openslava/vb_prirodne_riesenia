<?php

namespace SmartSellingClient;


interface ISmartSellingApiClientFactory
{
	/**
	 * @param string $clientId
	 * @param string $clientSecret
	 * @return ISmartSellingApiClient
	 * @throws InvalidClientIdException
	 * @throws InvalidClientSecretException
	 * @throws InvalidEmailException
	 * @throws InvalidPasswordException
	 * @throws InvalidStateException
	 */
	function createClient($clientId, $clientSecret);
}
