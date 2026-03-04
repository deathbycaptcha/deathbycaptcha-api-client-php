# [DeathByCaptcha](https://deathbycaptcha.com/)

[![Tests](https://github.com/deathbycaptcha/deathbycaptcha-api-client-php/actions/workflows/tests.yml/badge.svg)](https://github.com/deathbycaptcha/deathbycaptcha-api-client-php/actions/workflows/tests.yml)
[![Coverage](https://img.shields.io/endpoint?url=https://raw.githubusercontent.com/deathbycaptcha/deathbycaptcha-api-client-php/master/.coverage/badge.json)](https://github.com/deathbycaptcha/deathbycaptcha-api-client-php/actions/workflows/tests.yml)

## Documentation Index

### Start Here

- [Installation Guide](INSTALL.md)
- [Quick Start](QUICKSTART.md)
- [GitHub Actions CI](GITHUB_ACTIONS.md)
- [GitLab CI/CD](GITLAB_CI.md)
- [Changelog](CHANGELOG.md)

### README Contents

- [Introduction](#introduction)
- [How to Use DBC API Clients](#how-to-use-dbc-api-clients)
    - [Thread-safety notes](#thread-safety-notes)
    - [Common Clients' Interface](#common-clients-interface)
    - [CAPTCHA objects/details hashes](#captcha-objectsdetails-hashes)
    - [Example](#example)
- [New Recaptcha API support](#new-recaptcha-api-support)
    - [Coordinates API FAQ](#coordinates-api-faq)
    - [Image Group API FAQ](#image-group-api-faq)
- [New Recaptcha by Token API support (reCAPTCHA v2 and reCAPTCHA v3)](#new-recaptcha-by-token-api-support-recaptcha-v2-and-recaptcha-v3)
    - [reCAPTCHA v2 API FAQ](#recaptcha-v2-api-faq)
    - [reCAPTCHA v3 API FAQ](#recaptcha-v3-api-faq)
- [API Metadata Submodule](#api-metadata-submodule)
- [Continuous Integration](#continuous-integration)
    - [GitHub Actions](#github-actions)
    - [GitLab CI/CD](#gitlab-cicd)
- [Test Status by PHP Version](#test-status-by-php-version)
- [Code Quality](#code-quality)

## Introduction

DeathByCaptcha offers APIs of two types — HTTP and socket-based, with the latter being recommended for having faster responses and overall better performance. Switching between different APIs is usually as easy as changing the client class and/or package name, the interface stays the same.

When using the socket API, please make sure that outgoing TCP traffic to *api.dbcapi.me* to the ports range *8123–8130* is not blocked on your side.

## How to Use DBC API Clients

### Thread-safety notes

*PHP* itself is not multithreaded so the clients are not thread-safe.

### Common Clients' Interface

All clients have to be instantiated with two string arguments: your DeathByCaptcha account's *username* and *password*.

All clients provides a few methods to handle your CAPTCHAs and your DBC account. Below you will find those methods' short summary and signatures in pseudo-code. Check the example scripts and the clients' source code for more details.

#### Upload()

Uploads a CAPTCHA to the DBC service for solving, returns uploaded CAPTCHA details on success, `NULL` otherwise. Here are the signatures in pseudo-code:

```php
array DeathByCaptcha_Client->upload(resource $imageFile)

array DeathByCaptcha_Client->upload(string $imageFileName)
```  

#### GetCaptcha()

Fetches uploaded CAPTCHA details, returns `NULL` on failures.

```php
array DeathByCaptcha_Client->get_captcha(int $captchaId)
```

#### Report()

Reports incorrectly solved CAPTCHA for refund, returns `true` on success, `false` otherwise.

Please make sure the CAPTCHA you're reporting was in fact incorrectly solved, do not just report them thoughtlessly, or else you'll be flagged as abuser and banned.

```php
bool DeathByCaptcha.Client->report(int $captchaId)
```

#### Decode()

This method uploads a CAPTCHA, then polls for its status until it's solved or times out; returns solved CAPTCHA details on success, `NULL` otherwise.

```php
array DeathByCaptcha.Client->decode(resource $imageFile, int $timeout)

array DeathByCaptcha.Client->decode(string $imageFileName, int $timeout)
```

#### GetBalance()

Fetches your current DBC credit balance (in US cents).

```php
float DeathByCaptcha.Client->get_balance()
```


### CAPTCHA objects/details hashes

Use simple hashes (dictionaries, associative arrays etc.) to store CAPTCHA details, keeping numeric IDs under "captcha" key, CAPTCHA text under "text" key, and the correctness flag under "is_correct" key.

### Example

Below you can find a DBC API client usage examples.


```php
require_once "deathbycaptcha.php";

/* Put your DBC account username and password here.
   Use DeathByCaptcha_HttpClient for HTTP API. */
$client = new DeathByCaptcha_SocketClient($username, $password);
try {
    $balance = $client->get_balance();

    /* Put your CAPTCHA file name or opened file handler, and optional
       solving timeout (in seconds) here: */
    $captcha = $client->decode($captcha_file_name, $timeout);
    if ($captcha) {
        /* The CAPTCHA was solved; captcha["captcha"] item holds its
           numeric ID, and captcha["text"] item its text. */
        echo "CAPTCHA {$captcha["captcha"]} solved: {$captcha["text"]}";

        if (/* check if the CAPTCHA was incorrectly solved */) {
            $client->report($captcha["captcha"]);
        }
    }
} catch (DeathByCaptcha_AccessDeniedException) {
    /* Access to DBC API denied, check your credentials and/or balance */
}
```

# New Recaptcha API support

## What's "new reCAPTCHA/noCAPTCHA"?

They're new reCAPTCHA challenges that typically require the user to identify and click on certain images. They're not to be confused with traditional word/number reCAPTCHAs (those have no images).

For your convinience, we implemented support for New Recaptcha API. If your software works with it, and supports minimal configuration, you should be able to decode captchas using New Recaptcha API in no time.

We provide two different types of New Recaptcha API:

-   **Coordinates API**: Provided a screenshot, the API returns a group of coordinates to click.
-   **Image Group API**: Provided a group of (base64-encoded) images, the API returns the indexes of the images to click.

## Coordinates API FAQ:

**What's the Coordinates API URL?**  
To use the **Coordinates API** you will have to send a HTTP POST Request to <http://api.dbcapi.me/api/captcha>

What are the POST parameters for the **Coordinates API**?  

-   **`username`**: Your DBC account username
-   **`password`**: Your DBC account password
-   **`captchafile`**: a Base64 encoded or Multipart file contents with a valid New Recaptcha screenshot
-   **`type`=2**: Type 2 specifies this is a New Recaptcha **Coordinates API**

**What's the response from the Coordinates API?**  
-   **`captcha`**: id of the provided captcha, if the **text** field is null, you will have to pool the url <http://api.dbcapi.me/api/captcha/captcha_id> until it becomes available

-   **`is_correct`**: (0 or 1) specifying if the captcha was marked as incorrect or unreadable

-   **`text`**: a json-like nested list, with all the coordinates (x, y) to click relative to the image, for example:

                  [[23.21, 82.11]]
              
    where the X coordinate is 23.21 and the Y coordinate is 82.11

****

## Image Group API FAQ:

**What's the Image Group API URL?**  
To use the **Image Group API** you will have to send a HTTP POST Request to <http://api.dbcapi.me/api/captcha>

**What are the POST parameters for the Image Group API?** 

-   **`username`**: Your DBC account username
-   **`password`**: Your DBC account password
-   **`captchafile`**: the Base64 encoded file contents with a valid New Recaptcha screenshot. You must send each image in a single "captchafile" parameter. The order you send them matters
-   **`banner`**: the Base64 encoded banner image (the example image that appears on the upper right)
-   **`banner_text`**: the banner text (the text that appears on the upper left)
-   **`type`=3**: Type 3 specifies this is a New Recaptcha **Image Group API**
-   **`grid`**: Optional grid parameter specifies what grid individual images in captcha are aligned to (string, width+"x"+height, Ex.: "2x4", if images aligned to 4 rows with 2 images in each. If not supplied, dbc will attempt to autodetect the grid.

**What's the response from the Image Group API?**  
-   **`captcha`**: id of the provided captcha, if the **`text`** field is null, you will have to pool the url <http://api.dbcapi.me/api/captcha/captcha_id> until it becomes available

-   **`is_correct`**: (0 or 1) specifying if the captcha was marked as incorrect or unreadable

-   **`text`**: a json-like list of the index for each image that should be clicked. for example:

                  [1, 4, 6]
              
    where the images that should be clicked are the first, the fourth and the six, counting from left to right and up to bottom


# New Recaptcha by Token API support (reCAPTCHA v2 and reCAPTCHA v3)


## What's "new reCAPTCHA by Token"?


They're new reCAPTCHA challenges that typically require the user to identify and click on certain images. They're not to be confused with traditional word/number reCAPTCHAs (those have no images).

For your convenience, we implemented support for New Recaptcha by Token API. If your software works with it, and supports minimal configuration, you should be able to decode captchas using Death By Captcha in no time.

-   **Token Image API**: Provided a site url and site key, the API returns a token that you will use to submit the form in the page with the reCaptcha challenge.

## reCAPTCHA v2 API FAQ:

**What's the Token Image API URL?**   
To use the Token Image API you will have to send a HTTP POST Request to <http://api.dbcapi.me/api/captcha>

**What are the POST parameters for the Token image API?**

-   **`username`**: Your DBC account username
-   **`password`**: Your DBC account password
-   **`type`=4**: Type 4 specifies this is a New Recaptcha Token Image API
-   **`token_params`=json(payload)**: the data to access the recaptcha challenge
json payload structure:
    -   **`proxy`**: your proxy url and credentials (if any). Examples:
        -   <http://127.0.0.1:3128>
        -   <http://user:password@127.0.0.1:3128>
    
    -   **`proxytype`**: your proxy connection protocol. For supported proxy types refer to Which proxy types are supported?. Example:
        -   HTTP
    
    -   **`googlekey`**: the google recaptcha site key of the website with the recaptcha. For more details about the site key refer to What is a recaptcha site key?. Example:
        -   6Le-wvkSAAAAAPBMRTvw0Q4Muexq9bi0DJwx_mJ-
    
    -   **`pageurl`**: the url of the page with the recaptcha challenges. This url has to include the path in which the recaptcha is loaded. Example: if the recaptcha you want to solve is in <http://test.com/path1>, pageurl has to be <http://test.com/path1> and not <http://test.com>.

    -   **`data-s`**: This parameter is only required for solve the google search tokens, the ones visible, while google search trigger the robot protection. Use the data-s value inside the google search response html. For regulars tokens don't use this parameter.
    
The **`proxy`** parameter is optional, but we strongly recommend to use one to prevent token rejection by the provided page due to inconsistencies between the IP that solved the captcha (ours if no proxy is provided) and the IP that submitted the token for verification (yours).

**Note**: If **`proxy`** is provided, **`proxytype`** is a required parameter.

Full example of **`token_params`**:
```json
{
  "proxy": "http://127.0.0.1:3128",
  "proxytype": "HTTP",
  "googlekey": "6Le-wvkSAAAAAPBMRTvw0Q4Muexq9bi0DJwx_mJ-",
  "pageurl": "http://test.com/path_with_recaptcha"
}
```

Example of **`token_params`** for google search captchas:
```json
{
  "googlekey": "6Le-wvkSA...",
  "pageurl": "...",
  "data-s": "IUdfh4rh0sd..."
}
```

**What's the response from the Token image API?**  
The token image API response has the same structure as regular captchas' response. Refer to Polling for uploaded CAPTCHA status for details about the response. The token will come in the text key of the response. It's valid for one use and has a 2 minute lifespan. It will be a string like the following:
```bash
"03AOPBWq_RPO2vLzyk0h8gH0cA2X4v3tpYCPZR6Y4yxKy1s3Eo7CHZRQntxrdsaD2H0e6S3547xi1FlqJB4rob46J0-wfZMj6YpyVa0WGCfpWzBWcLn7tO_EYsvEC_3kfLNINWa5LnKrnJTDXTOz-JuCKvEXx0EQqzb0OU4z2np4uyu79lc_NdvL0IRFc3Cslu6UFV04CIfqXJBWCE5MY0Ag918r14b43ZdpwHSaVVrUqzCQMCybcGq0yxLQf9eSexFiAWmcWLI5nVNA81meTXhQlyCn5bbbI2IMSEErDqceZjf1mX3M67BhIb4"
```

## What's "new reCAPTCHA v3"?

This API is quite similar to the tokens(reCAPTCHA v2) API. Only 2 new parameters were added, one for the `action` and other for the **minimal score(`min-score`)**

reCAPTCHA v3 returns a score from each user, that evaluate if user is a bot or human. Then the website uses the score value that could range from 0 to 1 to decide if will accept or not the requests. Lower scores near to 0 are identified as bot.

The `action` parameter at reCAPTCHA v3 is an additional data used to separate different captcha validations like for example **login**, **register**, **sales**, **etc**.

## reCAPTCHA v3 API FAQ:

**What is `action` in reCAPTCHA v3?**  
Is a new parameter that allows processing user actions on the website differently.

To find this we need to inspect the javascript code of the website looking for call of grecaptcha.execute function. Example: 
```javascript
grecaptcha.execute('6Lc2fhwTAAAAAGatXTzFYfvlQMI2T7B6ji8UVV_f', {action: something})
```
Sometimes it's really hard to find it and we need to look through all javascript files. We may also try to find the value of action parameter inside ___grecaptcha_cfg configuration object. Also we can call grecaptcha.execute and inspect javascript code. The API will use "verify" default value it if we won't provide action in our request.

**What is `min-score` in reCAPTCHA v3 API?**  
The minimal score needed for the captcha resolution. We recommend using the 0.3 min-score value, scores highers than 0.3 are hard to get.

**What are the POST parameters for the reCAPTCHA v3 API?**

-   **`username`**: Your DBC account username
-   **`password`**: Your DBC account password
-   **`type`=5**: Type 5 specifies this is reCAPTCHA v3 API
-   **`token_params`**=json(payload): the data to access the recaptcha challenge
json payload structure:
    -   **`proxy`**: your proxy url and credentials (if any).Examples:
        -   <http://127.0.0.1:3128>
        -   <http://user:password@127.0.0.1:3128>
    
    -   **`proxytype`**: your proxy connection protocol. For supported proxy types refer to Which proxy types are supported?. Example: 
        -   HTTP
    
    -   **`googlekey`**: the google recaptcha site key of the website with the recaptcha. For more details about the site key refer to What is a recaptcha site key?. Example:
        -   6Le-wvkSAAAAAPBMRTvw0Q4Muexq9bi0DJwx_mJ-
    
    -   **`pageurl`**: the url of the page with the recaptcha challenges. This url has to include the path in which the recaptcha is loaded. Example: if the recaptcha you want to solve is in <http://test.com/path1>, pageurl has to be <http://test.com/path1> and not <http://test.com>.
    
    -   **`action`**: The action name.
    
    -   **`min_score`**: The minimal score, usually 0.3
    
The **`proxy`** parameter is optional, but we strongly recommend to use one to prevent rejection by the provided page due to inconsistencies between the IP that solved the captcha (ours if no proxy is provided) and the IP that submitted the solution for verification (yours).

**Note**: If **`proxy`** is provided, **`proxytype`** is a required parameter.

Full example of **`token_params`**:
```json
{
  "proxy": "http://127.0.0.1:3128",
  "proxytype": "HTTP",
  "googlekey": "6Le-wvkSAAAAAPBMRTvw0Q4Muexq9bi0DJwx_mJ-",
  "pageurl": "http://test.com/path_with_recaptcha",
  "action": "example/action",
  "min_score": 0.3
}
```

**What's the response from reCAPTCHA v3 API?**  
The response has the same structure as regular captcha. Refer to [Polling for uploaded CAPTCHA status](https://deathbycaptcha.com/api#polling-captcha) for details about the response. The solution will come in the **text** key of the response. It's valid for one use and has a 1 minute lifespan.

## API Metadata Submodule

This repository includes the [deathbycaptcha-agent-api-metadata](https://github.com/deathbycaptcha/deathbycaptcha-agent-api-metadata) submodule in the `api-metadata/` directory. This submodule contains structured API specifications and documentation that enable AI assistants, code analyzers, and automated tools to better understand the DeathByCaptcha API without requiring additional context requests. The metadata includes OpenAPI specs, validation schemas, and detailed endpoint documentation.

## Continuous Integration

This project is configured for automated testing on multiple platforms:

### GitHub Actions

- **Tested PHP Versions**: 7.4 (LTS), 8.1 (LTS), 8.3 (latest)
- **Coverage**: Code coverage reports generated in each workflow run
- **Caching**: Composer dependencies cached for faster builds
- **Configuration**: See [GITHUB_ACTIONS.md](GITHUB_ACTIONS.md) for details

### GitLab CI/CD

- **Tested PHP Versions**: 7.4 (LTS), 8.1 (LTS), 8.3 (latest)
- **Coverage**: Code coverage reports with Xdebug
- **Static Analysis**: PHPStan for code quality
- **Configuration**: See [GITLAB_CI.md](GITLAB_CI.md) for details

Both CI systems run the full test suite (74 tests, 158 assertions) on every push and pull request, ensuring compatibility across PHP versions 7.4+.

---

### Test Status by PHP Version

| PHP Version | Status |
|-------------|--------|
| 7.4 LTS | [![PHP 7.4](https://img.shields.io/endpoint?url=https://raw.githubusercontent.com/deathbycaptcha/deathbycaptcha-api-client-php/master/.badges/php74/badge.json)](https://github.com/deathbycaptcha/deathbycaptcha-api-client-php/actions/workflows/tests.yml) |
| 8.1 LTS | [![PHP 8.1](https://img.shields.io/endpoint?url=https://raw.githubusercontent.com/deathbycaptcha/deathbycaptcha-api-client-php/master/.badges/php81/badge.json)](https://github.com/deathbycaptcha/deathbycaptcha-api-client-php/actions/workflows/tests.yml) |
| 8.3 | [![PHP 8.3](https://img.shields.io/endpoint?url=https://raw.githubusercontent.com/deathbycaptcha/deathbycaptcha-api-client-php/master/.badges/php83/badge.json)](https://github.com/deathbycaptcha/deathbycaptcha-api-client-php/actions/workflows/tests.yml) |

### Code Quality

| Check | Status |
|-------|--------|
| Integration Tests | [![Integration](https://img.shields.io/endpoint?url=https://raw.githubusercontent.com/deathbycaptcha/deathbycaptcha-api-client-php/master/.badges/integration/badge.json)](https://github.com/deathbycaptcha/deathbycaptcha-api-client-php/actions/workflows/tests.yml) |
| PHP Linting | [![Lint](https://img.shields.io/endpoint?url=https://raw.githubusercontent.com/deathbycaptcha/deathbycaptcha-api-client-php/master/.badges/lint/badge.json)](https://github.com/deathbycaptcha/deathbycaptcha-api-client-php/actions/workflows/tests.yml) |
| PHPStan Analysis | [![PHPStan](https://img.shields.io/endpoint?url=https://raw.githubusercontent.com/deathbycaptcha/deathbycaptcha-api-client-php/master/.badges/phpstan/badge.json)](https://github.com/deathbycaptcha/deathbycaptcha-api-client-php/actions/workflows/tests.yml) |
