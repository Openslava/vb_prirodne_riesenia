<?php

namespace SmartSellingClient;


interface ISmartSellingApiClient
{
	/**
	 * @param array $parameters
	 * @return array[]
	 * @throws InvalidStateException
	 */
	function getConnections(array $parameters);
}
