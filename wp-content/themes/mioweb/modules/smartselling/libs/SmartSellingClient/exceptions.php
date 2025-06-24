<?php

namespace SmartSellingClient;


class InvalidArgumentException extends \InvalidArgumentException
{
}


class InvalidStateException extends \RuntimeException
{
}


class AuthenticationException extends \Exception
{
}


class InvalidEmailException extends AuthenticationException
{
}


class InvalidPasswordException extends AuthenticationException
{
}


class InvalidClientIdException extends AuthenticationException
{
}


class InvalidClientSecretException extends AuthenticationException
{
}


class InvalidAccessTokenException extends AuthenticationException
{
}
