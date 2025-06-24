<?php

namespace SmartSellingClient;


interface ISmartSellingLoginClient
{
	/**
	 * @param string $email
	 * @param string $password
	 * @param bool $remember
	 * @param callable $redirectCallback function(string $url, array $postParameters)
	 * @return void
	 * @throws InvalidEmailException
	 * @throws InvalidPasswordException
	 * @throws InvalidStateException
	 */
	function tryToLogin($email, $password, $remember, $redirectCallback);

	/**
	 * @param array $postData
	 * @param callable $loginCallback function(string $email)
	 * @return void
	 * @throws InvalidAccessTokenException
	 * @throws InvalidStateException
	 */
	function handleAutologin(array $postData, $loginCallback);
}
