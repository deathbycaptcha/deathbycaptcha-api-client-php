# Quick Start Guide

Get started with DeathByCaptcha PHP client in 5 minutes.

## Prerequisites

- PHP 7.4 or higher
- DeathByCaptcha account ([Sign up here](https://deathbycaptcha.com))
- Your API username and password

## Basic Setup

```php
<?php
require_once 'deathbycaptcha.php';

// Set your credentials
$username = 'your_username';
$password = 'your_password';

// Create a client (HTTP is recommended)
$client = new DeathByCaptcha_HttpClient($username, $password);

// Check your balance
$balance = $client->get_balance();
echo "Your balance: $" . number_format($balance, 2) . "\n";
```

## Solving a Simple Image CAPTCHA

```php
<?php
require_once 'deathbycaptcha.php';

$client = new DeathByCaptcha_HttpClient('your_username', 'your_password');

// Upload a CAPTCHA from file
$captcha = $client->decode('path/to/captcha.jpg');

if ($captcha) {
    echo "CAPTCHA {$captcha['captcha']} solved: {$captcha['text']}\n";
    
    // If the solution is incorrect, report it
    if ($captcha['text'] !== 'expected_text') {
        $client->report($captcha['captcha']);
        echo "Incorrect CAPTCHA reported\n";
    }
} else {
    echo "Failed to solve CAPTCHA\n";
}
```

## Solving reCAPTCHA v2

```php
<?php
require_once 'deathbycaptcha.php';

$client = new DeathByCaptcha_HttpClient('your_username', 'your_password');

// reCAPTCHA v2 parameters
$token_params = json_encode([
    'googlekey' => 'your_site_key',
    'pageurl' => 'https://example.com/page-with-recaptcha'
]);

// Solve reCAPTCHA
$captcha = $client->decode([
    'type' => 4,  // reCAPTCHA v2 type
    'token_params' => $token_params
]);

if ($captcha) {
    echo "CAPTCHA {$captcha['captcha']} solved\n";
    echo "Token: {$captcha['text']}\n";
    
    // Use $captcha['text'] as the g-recaptcha-response value
} else {
    echo "Failed to solve reCAPTCHA\n";
}
```

## Solving reCAPTCHA v3

```php
<?php
require_once 'deathbycaptcha.php';

$client = new DeathByCaptcha_HttpClient('your_username', 'your_password');

// reCAPTCHA v3 parameters
$token_params = json_encode([
    'googlekey' => 'your_site_key',
    'pageurl' => 'https://example.com/page-with-recaptcha',
    'action' => 'submit',  // The action name
    'min_score' => 0.3     // Minimum score (0.1, 0.3, 0.5, 0.7, or 0.9)
]);

// Solve reCAPTCHA v3
$captcha = $client->decode([
    'type' => 5,  // reCAPTCHA v3 type
    'token_params' => $token_params
]);

if ($captcha) {
    echo "Token: {$captcha['text']}\n";
} else {
    echo "Failed to solve reCAPTCHA v3\n";
}
```

## Solving hCaptcha (Turnstile)

```php
<?php
require_once 'deathbycaptcha.php';

$client = new DeathByCaptcha_HttpClient('your_username', 'your_password');

// Turnstile parameters
$token_params = json_encode([
    'sitekey' => 'your_site_key',
    'pageurl' => 'https://example.com/page-with-hcaptcha'
]);

// Solve hCaptcha/Turnstile
$captcha = $client->decode([
    'type' => 7,  // Turnstile type
    'token_params' => $token_params
]);

if ($captcha) {
    echo "Token: {$captcha['text']}\n";
} else {
    echo "Failed to solve Turnstile\n";
}
```

## Using Socket Client (Alternative)

If you prefer sockets over HTTP:

```php
<?php
require_once 'deathbycaptcha.php';

// Use Socket client instead of HTTP
$client = new DeathByCaptcha_SocketClient('your_username', 'your_password');

// The rest of the code is identical
$balance = $client->get_balance();
echo "Balance: $" . number_format($balance, 2) . "\n";
```

## Common Operations

### Get Account Balance

```php
$balance = $client->get_balance();
echo "Current balance: $" . number_format($balance, 2) . "\n";
```

### Get CAPTCHA Status

```php
$captcha_id = 12345;
$captcha = $client->get_captcha($captcha_id);

if ($captcha) {
    echo "Status: " . ($captcha['is_correct'] ? 'Solved' : 'Pending') . "\n";
    echo "Text: {$captcha['text']}\n";
}
```

### Report Incorrect CAPTCHA

```php
// Get a refund for incorrectly solved CAPTCHAs
$captcha_id = 12345;
if ($client->report($captcha_id)) {
    echo "CAPTCHA reported successfully\n";
}
```

## Error Handling

```php
<?php
require_once 'deathbycaptcha.php';

try {
    $client = new DeathByCaptcha_HttpClient('username', 'password');
    $balance = $client->get_balance();
    echo "Balance: $" . number_format($balance, 2) . "\n";
} catch (DeathByCaptcha_AccessDeniedException $e) {
    echo "Access denied: Invalid credentials\n";
} catch (DeathByCaptcha_InvalidCaptchaException $e) {
    echo "Invalid CAPTCHA: " . $e->getMessage() . "\n";
} catch (DeathByCaptcha_ServiceOverloadException $e) {
    echo "Service overloaded, try again later\n";
} catch (DeathByCaptcha_Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
```

## CAPTCHA Types Reference

| Type | Service | Description |
|------|---------|-------------|
| 1 | Image CAPTCHA | Standard image-based CAPTCHA |
| 2 | Audio CAPTCHA | Audio-based CAPTCHA |
| 3 | reCAPTCHA v2 (old) | Legacy reCAPTCHA |
| 4 | reCAPTCHA v2 | Google reCAPTCHA v2 (checkbox) |
| 5 | reCAPTCHA v3 | Google reCAPTCHA v3 (score-based) |
| 7 | Turnstile | Cloudflare Turnstile / hCaptcha |
| 8 | FunCaptcha | Arkose Labs FunCaptcha |
| 9 | GeeTest | GeeTest CAPTCHA v3/v4 |

## More Examples

Check the [examples/](examples/) directory for complete working examples:

- `example.Normal_Captcha.php` - Basic image CAPTCHA
- `example.reCAPTCHA_v2.php` - Google reCAPTCHA v2
- `example.reCAPTCHA_v3.php` - Google reCAPTCHA v3
- `example.Turnstile.php` - Cloudflare Turnstile
- `example.Geetest_v3.php` - GeeTest v3
- `example.Geetest_v4.php` - GeeTest v4
- And many more...

## Need Help?

- **Full Documentation**: [README.md](README.md)
- **Installation Guide**: [INSTALL.md](INSTALL.md)  
- **API Documentation**: https://deathbycaptcha.com/api
- **Support Email**: info@deathbycaptcha.com

## Tips for Best Results

1. **Always check your balance** before solving CAPTCHAs to avoid errors
2. **Use HTTPS** - The client uses secure connections by default
3. **Report incorrect solutions** to get refunds and improve accuracy
4. **Handle exceptions** properly to make your code more robust
5. **Use HTTP client** unless you have specific reasons to use sockets
6. **Follow the API rate limits** to avoid service overload exceptions

---

Now you're ready to integrate CAPTCHA solving into your PHP applications! 🚀
