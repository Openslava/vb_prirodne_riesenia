<?php

namespace SmartSellingClient\Utils;


/**
 * JSON encoder and decoder.
 *
 * This solution is mostly based on Nette Framework (c) David Grudl (http://davidgrudl.com), new BSD license
 *
 * @author     David Grudl
 */
class Json
{
	const FORCE_ARRAY = 1;
	const PRETTY = 2;

	/** @var array */
	private static $messages = array(
		JSON_ERROR_DEPTH => 'The maximum stack depth has been exceeded',
		JSON_ERROR_STATE_MISMATCH => 'Syntax error, malformed JSON',
		JSON_ERROR_CTRL_CHAR => 'Unexpected control character found',
		JSON_ERROR_SYNTAX => 'Syntax error, malformed JSON',
		5 /*JSON_ERROR_UTF8*/ => 'Invalid UTF-8 sequence', // exists since 5.3.3, but is returned since 5.3.1
	);


	/**
	 * Returns the JSON representation of a value.
	 * @param mixed $value
	 * @param int $options
	 * @return string
	 * @throws JsonException
	 */
	public static function encode($value, $options = 0)
	{
		$flags = PHP_VERSION_ID >= 50400 ? (JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | ($options & self::PRETTY ? JSON_PRETTY_PRINT : 0)) : 0;

		if (PHP_VERSION_ID < 50500) {
			$json = Callback::invokeSafe('json_encode', array($value, $flags), function($message) { // needed to receive 'recursion detected' error
				throw new JsonException($message);
			});
		} else {
			$json = json_encode($value, $flags);
		}

		if ($error = json_last_error()) {
			$message = isset(static::$messages[$error]) ? static::$messages[$error]
				: (PHP_VERSION_ID >= 50500 ? json_last_error_msg() : 'Unknown error');
			throw new JsonException($message, $error);
		}

		$json = str_replace(array("\xe2\x80\xa8", "\xe2\x80\xa9"), array('\u2028', '\u2029'), $json);
		return $json;
	}


	/**
	 * Decodes a JSON string.
	 * @param string $json
	 * @param int $options
	 * @return mixed
	 * @throws JsonException
	 */
	public static function decode($json, $options = 0)
	{
		$json = (string) $json;
		if (!preg_match('##u', $json)) {
			throw new JsonException('Invalid UTF-8 sequence', 5); // workaround for PHP < 5.3.3 & PECL JSON-C
		}

		$forceArray = (bool) ($options & self::FORCE_ARRAY);
		if (!$forceArray && preg_match('#(?<=[^\\\\]")\\\\u0000(?:[^"\\\\]|\\\\.)*+"\s*+:#', $json)) { // workaround for json_decode fatal error when object key starts with \u0000
			throw new JsonException(static::$messages[JSON_ERROR_CTRL_CHAR]);
		}
		$args = array($json, $forceArray, 512);
		if (PHP_VERSION_ID >= 50400 && !(defined('JSON_C_VERSION') && PHP_INT_SIZE > 4)) { // not implemented in PECL JSON-C 1.3.2 for 64bit systems
			$args[] = JSON_BIGINT_AS_STRING;
		}
		$value = call_user_func_array('json_decode', $args);

		if ($value === NULL && $json !== '' && strcasecmp($json, 'null')) { // '' is not clearing json_last_error
			$error = json_last_error();
			throw new JsonException(isset(static::$messages[$error]) ? static::$messages[$error] : 'Unknown error', $error);
		}
		return $value;
	}

}


/**
 * The exception that indicates error of JSON encoding/decoding.
 */
class JsonException extends \Exception
{
}
