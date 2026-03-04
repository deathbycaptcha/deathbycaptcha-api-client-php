# Unit Tests

This directory contains the PHPUnit test suite for the DeathByCaptcha PHP API Client.

## Structure

```
tests/
├── bootstrap.php                          # Test initialization file
├── Unit/
│   ├── DeathByCaptcha_ExceptionTest.php   # Exception classes tests
│   ├── DeathByCaptcha_ClientTest.php      # Base client functionality
│   ├── DeathByCaptcha_HttpClientTest.php  # HTTP API client tests
│   ├── DeathByCaptcha_SocketClientTest.php # Socket API client tests
│   └── DeathByCaptcha_IntegrationTest.php # Integration tests
└── README.md                              # This file
```

## Running Tests

### Prerequisites

First, install the development dependencies:

```bash
composer install
```

This will install PHPUnit and all required dependencies.

### Run All Tests

```bash
composer test
```

Or directly with PHPUnit:

```bash
./vendor/bin/phpunit
```

### Run Tests with Verbose Output

```bash
composer test-verbose
```

Or:

```bash
./vendor/bin/phpunit -v
```

### Run a Specific Test Suite

```bash
./vendor/bin/phpunit tests/Unit/DeathByCaptcha_ExceptionTest.php
```

### Run a Specific Test Method

```bash
./vendor/bin/phpunit --filter testClientInstantiation tests/Unit/DeathByCaptcha_HttpClientTest.php
```

### Generate Code Coverage Report

```bash
composer test-coverage
```

This generates:
- `coverage/` directory with HTML coverage report (open `coverage/index.html`)
- `coverage.xml` file for CI/CD integration

### Run Tests with Filter

```bash
# Run only Http client tests
./vendor/bin/phpunit --filter HttpClient

# Run only exception tests
./vendor/bin/phpunit --filter Exception
```

## Test Coverage

The test suite includes:

### Exception Tests (DeathByCaptcha_ExceptionTest.php)
- ✓ All exception classes (7 types)
- ✓ Exception hierarchy and inheritance
- ✓ Exception messages and properties

### Base Client Tests (DeathByCaptcha_ClientTest.php)
- ✓ Constants and configuration
- ✓ Static helper methods (parse_json_response, parse_plain_response)
- ✓ Magic getters (__get)
- ✓ Credentials handling
- ✓ Client lifecycle (constructor, destructor, close)

### HTTP Client Tests (DeathByCaptcha_HttpClientTest.php)
- ✓ Client instantiation
- ✓ Required extensions (curl, json)
- ✓ Authentication validation (username/password/authtoken)
- ✓ Configuration options (verbose mode)
- ✓ Constants (BASE_URL, API_VERSION)
- ✓ HTTP-specific functionality

### Socket Client Tests (DeathByCaptcha_SocketClientTest.php)
- ✓ Client instantiation
- ✓ Required extensions and functions
- ✓ Authentication handling
- ✓ Socket-specific constants
- ✓ TCP/Socket configuration

### Integration Tests (DeathByCaptcha_IntegrationTest.php)
- ✓ Both client types work correctly
- ✓ Client interface compatibility
- ✓ Consistent error handling
- ✓ JSON encoding/decoding roundtrips
- ✓ Multiple client instances
- ✓ Credentials switching
- ✓ Exception handling scenarios

## PHP Version Compatibility

Tests are designed to work with:
- ✓ PHP 5.5+
- ✓ PHP 7.x
- ✓ PHP 8.x

Run `composer test` to verify compatibility with your PHP version.

## Test Design Notes

### Unit vs Integration Tests

- **Unit Tests**: Test individual components in isolation (exception classes, method behavior)
- **Integration Tests**: Test how components work together (both clients, interface compatibility)

### Mock API Calls

Tests are designed to NOT require valid API credentials. Most API call tests expect exceptions or null returns when credentials are invalid. This allows tests to run in any environment.

### Extensions Testing

Tests verify that required extensions are loaded:
- `curl` - Required for HttpClient
- `json` - Required for both clients

If an extension is not loaded, tests attempt to gracefully skip or continue.

### Error Suppression (@) Operator

The codebase uses the `@` operator for error suppression. Tests verify this works correctly in PHP 8.x.

## Continuous Integration

For CI/CD pipelines, you can:

1. **Run tests**: 
   ```bash
   composer test
   ```

2. **Generate coverage**:
   ```bash
   composer test-coverage
   ```

3. **Check coverage percentage**:
   ```bash
   ./vendor/bin/phpunit --coverage-text=php://stdout --coverage-clover=coverage.xml
   ```

## Troubleshooting

### No tests found
- Ensure PHPUnit is installed: `composer install`
- Check that test files are in `tests/Unit/` directory
- Verify test class names end with `Test`

### CURL extension not loaded
- You may need PHP with curl support
- Install: `apt-get install php-curl` (Linux) or `brew install php@8.3` (macOS)

### JSON extension not loaded
- JSON is built-in for PHP 5.5+
- Should not be an issue on any modern PHP installation

### Permission denied errors
- Ensure `vendor/bin/phpunit` is executable: `chmod +x vendor/bin/phpunit`

## Contributing Tests

When adding new functionality:

1. Create corresponding test class in `tests/Unit/`
2. Follow naming convention: `ComponentNameTest.php`
3. Use PHPUnit assertions and expectations
4. Ensure tests pass with `composer test`
5. Maintain code coverage above 80%

## References

- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [PHPUnit Best Practices](https://phpunit.de/manual/current/en/best-practices.html)
- [PHP Testing Standards](https://www.php-fig.org/)
