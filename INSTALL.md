# Installation Guide

## Requirements

- **PHP**: 7.4 or higher
- **Extensions**:
  - `ext-curl`: Required for HTTP API communication
  - `ext-json`: Required for JSON encoding/decoding
  - `ext-sockets`: Optional, but recommended for Socket API client

## Installation Methods

### Manual Installation

1. **Download the library**

   Clone or download this repository:
   
   ```bash
   git clone https://github.com/deathbycaptcha/deathbycaptcha-api-client-php.git
   cd deathbycaptcha-api-client-php
   ```

2. **Include the library in your project**

   Simply require the main file in your PHP script:
   
   ```php
   <?php
   require_once '/path/to/deathbycaptcha.php';
   ```

3. **Alternative: Copy to your project**

   Copy `deathbycaptcha.php` directly into your project directory and include it:
   
   ```php
   <?php
   require_once __DIR__ . '/deathbycaptcha.php';
   ```

## Verify Installation

After installation, verify that the library is working:

```php
<?php
require_once 'deathbycaptcha.php';

// Check if classes are available
if (class_exists('DeathByCaptcha_HttpClient')) {
    echo "DeathByCaptcha library installed successfully!\n";
    echo "Version: " . DeathByCaptcha_Client::API_VERSION . "\n";
} else {
    echo "Installation failed. Please check your setup.\n";
}
```

## Check PHP Extensions

Verify that required extensions are installed:

```bash
php -m | grep -E 'curl|json|sockets'
```

You should see:
- `curl`
- `json`
- `sockets` (optional but recommended)

### Installing Missing Extensions

**On Ubuntu/Debian:**
```bash
sudo apt-get install php-curl php-json php-sockets
```

**On CentOS/RHEL:**
```bash
sudo yum install php-curl php-json php-sockets
```

**On macOS (with Homebrew):**
```bash
brew install php
# Extensions are typically included
```

## Configuration

### API Credentials

You'll need DeathByCaptcha API credentials to use this library. Sign up at [DeathByCaptcha.com](https://deathbycaptcha.com) to get your username and password.

### Client Selection

The library provides two client implementations:

1. **HTTP Client** (Recommended)
   - Uses cURL for HTTP requests
   - More reliable and widely supported
   - Requires `ext-curl`

2. **Socket Client** (Alternative)
   - Direct socket connection
   - Useful when cURL is not available
   - Requires `ext-sockets`

## Quick Start

Once installed, head over to [QUICKSTART.md](QUICKSTART.md) for usage examples.

## Troubleshooting

### "Class not found" error

- Verify the path in your `require_once` statement
- Check file permissions (should be readable)
- Ensure PHP has no syntax errors: `php -l deathbycaptcha.php`

### cURL or Socket errors

- Verify required extensions are installed: `php -m`
- Check firewall settings allow outbound connections
- Ensure your server can reach `api.dbcapi.me` and `api.deathbycaptcha.com`

### SSL/TLS errors

- Update your CA certificates bundle
- On Ubuntu/Debian: `sudo apt-get install ca-certificates`
- Verify OpenSSL is properly configured: `openssl version`

## Development Installation

For contributing or running tests:

```bash
# Clone the repository
git clone https://github.com/deathbycaptcha/deathbycaptcha-api-client-php.git
cd deathbycaptcha-api-client-php

# Install development dependencies
composer install

# Run tests
composer test

# Run linter
composer lint
```

## Live Integration Tests (gitlab-ci-local)

These tests hit the real DeathByCaptcha API and validate core flows:
- Server endpoint reachability
- User status and balance (`>= 0`)
- Upload/decode normal captcha (`type=0`) and wait for solved text
- Fetch captcha by ID and report flow

### 1) Configure credentials

```bash
cp .env.sample .env
```

Edit `.env` and set:
- `DBC_USERNAME`
- `DBC_PASSWORD`

Optional:
- `DBC_INTEGRATION_TIMEOUT` (default: `120`)
- `DBC_TEST_IMAGE` (default: `images/normal.jpg`)

### 2) Run integration tests directly

```bash
composer test-integration
```

### 3) Run from gitlab-ci-local

```bash
gitlab-ci-local run integration:live
```

If credentials are missing, integration tests are skipped automatically.

## Support

- **Documentation**: See [README.md](README.md) and [examples/](examples/)
- **Issues**: Report bugs at the project's issue tracker
- **Email**: info@deathbycaptcha.com

## Next Steps

- Read the [Quick Start Guide](QUICKSTART.md)
- Browse [example scripts](examples/)
- Check the [API documentation](https://deathbycaptcha.com/api)
