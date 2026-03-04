<?php
/**
 * PHPUnit Bootstrap File
 * 
 * This file is executed before the test suite runs.
 * It handles the initialization of test dependencies.
 */

// Define the base path
define('BASE_PATH', dirname(__DIR__));

// Load .env file (if present) for integration tests and local CI runs
$dotenvPath = BASE_PATH . '/.env';
if (is_readable($dotenvPath)) {
	$lines = file($dotenvPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	foreach ($lines as $line) {
		$line = trim($line);
		if ($line === '' || strpos($line, '#') === 0 || strpos($line, '=') === false) {
			continue;
		}

		[$key, $value] = array_map('trim', explode('=', $line, 2));
		$value = trim($value, "\"'");

		if ($key !== '' && getenv($key) === false) {
			putenv($key . '=' . $value);
			$_ENV[$key] = $value;
			$_SERVER[$key] = $value;
		}
	}
}

// Require the main library file
require_once BASE_PATH . '/deathbycaptcha.php';

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');
