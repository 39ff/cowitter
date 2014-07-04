Class Description - TwistException
=============================

Simply extended from `RuntimeException`.  
Treats errors caused on Twitter.

Class Description - TwistImage
=============================

Some `TwistOAuth` methods return an instance of `TwistImage` when `Content-Type: image/***` header is detected.

Properties
----------

### TwistImage::$type

```php
(String) $img->type
```

*Readonly*.  
Content-Type. This means the following value.

```php
substr('Content-Type: image/***', 14)
```

### TwistImage::$data

```php
(String) $img->data
```

*Readonly*.  
This means binary image data.

Methods
-------

### TwistImage::getDataUri()

```php
(String) $img->getDataUri()
```

#### Return Value

**Data URI**. This means the following value.

```php
'data:image/png;base64,......'
```

Class Description - TwistOAuth
==================

Properties
----------

### TwistOAuth::$ck<br />TwistOAuth::$cs<br />TwistOAuth::$ot<br />TwistOAuth::$os

All properties are *Readonly*.

```php
(String) $to->ck // consumer_key
(String) $to->cs // consumer_secret
(String) $to->ot // oauth_token (request_token or access_token)
(String) $to->os // oauth_token_secret (request_token_secret or access_token_secret)
```

Basic Methods
-------------

### TwistOAuth::\_\_construct()

Constructor.

```php
new TwistOAuth($ck, $cs, $ot = '', $os = '')
```

#### Arguments

- (String) __*$ck*__<br />consumer\_key.
- (String) __*$cs*__<br />consumer\_secret.
- (String) __*$ot*__<br />oauth\_token. (request\_token or access\_token)
- (String) __*$os*__<br />oauth\_token_secret. (request\_token\_secret or access\_token\_secret)

### TwistOAuth::getAuthenticateUrl()<br />TwistOAuth::getAuthorizeUrl()

Easily generate URL for users to login.

```php
(String) $to->getAuthenticateUrl($force_login = false)
(String) $to->getAuthorizeUrl($force_login = false)
```

#### Arguments

- (bool) __*$force\_login*__<br />Whether we force logined users to relogin.

#### Return Value

A URL for authentication or authorization.

### TwistOAuth::renewWithRequestToken()<br />TwistOAuth::renewWithAccessToken()<br />TwistOAuth::renewWithAccessTokenX()

Fetch tokens and regenerate instance with them.

```php
(TwistOAuth) $to->renewWithRequestToken($proxy = '')
(TwistOAuth) $to->renewWithAccessToken($oauth_verifier, $proxy = '')
(TwistOAuth) $to->renewWithAccessTokenX($username, $password, $proxy = '')
```

#### Arguments

- (string) __*$oauth\_verifier*__
- (string) __*$username*__<br />screen_name or email.
- (string) __*$password*__
- (string) __*$proxy*__<br />Full proxy URL.<br />e.g. `https://111.222.333.444:8080`

#### Return Value

A <ins>new</ins> `TwistOAuth` instance.

#### Exception

Throws `TwistException`.

### TwistOAuth::get()<br />TwistOAuth::post()<br />TwistOAuth::postMultipart()

Execute a request for Twitter.

```php
(mixed) $to->get($url, $params = array(), $proxy = '')
(mixed) $to->post($url, $params = array(), $proxy = '')
(mixed) $to->postMultipart($url, $params = array(), $proxy = '')
```

#### Arguments

- (string) __*$url*__<br />Full or partial endpoint URL.<br />e.g. `statuses/update` `https://api.twitter.com/1.1/statuses/update.json`
- (mixed) __*$params*__<br />1-demensional array or query string.<br />File path annotation is `@` on <ins>key</ins>.(not value)<br />`NULL` is ignored.
- (string) __*$proxy*__<br />Full proxy URL.<br />e.g. `https://111.222.333.444:8080`

Example value of __*$params*__:

```php
$params = 'status=test&in_reply_to_status_id=123456';
```

```php
$params = array(
    'status' => 'test',
    'in_reply_to_status_id' => '123456',
);
```

```php
$params = array(
    'status' => 'test',
    '@media[]' => 'test.jpg',
);
```

#### Return Value

Return value will mainly be `stdClass`, array or `TwistImage`.

#### Exception

Throws `TwistException`.

### TwistOAuth::getOut()<br />TwistOAuth::postOut()<br />TwistOAuth::postMultipartOut()

Execute a request for third party sites using **OAuth Echo**.

```php
(mixed) $to->getOut($url, $params = array(), $proxy = '')
(mixed) $to->postOut($url, $params = array(), $proxy = '')
(mixed) $to->postMultipartOut($url, $params = array(), $proxy = '')
```

#### Arguments

- (string) __*$url*__<br />Full URL.<br />e.g. `http://api.twitpic.com/2/upload.json`
- (mixed) __*$params*__<br />1-demensional array or query string.<br />File path annotation is `@` on <ins>key</ins>.(not value)<br />`NULL` is ignored.
- (string) __*$proxy*__<br />Full proxy URL.<br />e.g. `https://111.222.333.444:8080`

#### Return Value

Return value will mainly be `stdClass`, array or `TwistImage`.

#### Exception

Throws `TwistException`.

### TwistOAuth::streaming()

Execute a streaming request for Twitter.

```php
(void) $to->streaming($url, callable $callback, $params = array(), $proxy = '')
```

#### Arguments

- (string) __*$url*__<br />Full or partial endpoint URL.<br />e.g. `statuses/filter` `https://stream.twitter.com/1.1/statuses/filter.json`
- (callable) __*$callback*__<br />A callback function.<br />1 argument for each statuses.<br />Return true for disconnecting.
- (mixed) __*$params*__<br />1-demensional array or query string.<br />File path annotation is `@` on <ins>key</ins>.(not value)<br />`NULL` is ignored.
- (string) __*$proxy*__<br />Full proxy URL.<br />e.g. `https://111.222.333.444:8080`

Example value of __*$callback*__:

```php
// A callback closure, which displays tweets unlimitedly.
$callback = function ($status) {
    // Treat only tweets
    if (isset($status->text)) {
        printf(
            "@%s: %s\n",
            $status->user->screen_name,
            htmlspecialchars_decode($status->text, ENT_NOQUOTES)
        );
        flush();
    }
};
```

```php
// A callback closure, which displays 10 tweets and then disconnect.
$callback = function ($status) {
    static $i = 0;
    if ($i > 10) {
        // Return true for disconnecting.
        return true;
    }
    // Treat only tweets
    if (isset($status->text)) {
        printf(
            "@%s: %s\n",
            $status->user->screen_name,
            htmlspecialchars_decode($status->text, ENT_NOQUOTES)
        );
        ++$i;
        flush();
    }
};
```

#### Exception

Throws `TwistException`.

Abusing Methods
---------------

### TwistOAuth::login()

**Direct OAuth**. (Scraping Login)

```php
(TwistOAuth) TwistOAuth::login($ck, $cs, $username, $password, $proxy = '')
```

#### Arguments

- (String) __*$ck*__<br />consumer\_key.
- (String) __*$cs*__<br />consumer\_secret.
- (String) __*$username*__<br />screen\_name or email.
- (String) __*$password*__
- (String) __*$proxy*__<br />Full proxy URL.<br />e.g. `https://111.222.333.444:8080`

#### Return Value

A new instance of `TwistOAuth`.

#### Exception

Throws `TwistException`.

### TwistOAuth::multiLogin()

Multiple **Direct OAuth**. (Scraping Logins)

```php
(array) TwistOAuth::multiLogin(array $credentials)
```

#### Arguments

- (array) __*$credentials*__<br />An array consisting of the following structure.

```php
$credentials = array(
    'YOUR SCREEN_NAME 0' => array(
        'YOUR CONSUMER KEY 0',
        'YOUR CONSUMER SECRET 0',
        'YOUR USERNAME 0',
        'YOUR PASSWORD 0',
    ),
    'YOUR SCREEN_NAME 1' => array(
        'YOUR CONSUMER KEY 1',
        'YOUR CONSUMER SECRET 1',
        'YOUR USERNAME 1',
        'YOUR PASSWORD 1',
    ),
    'YOUR SCREEN_NAME 2' => array(
        'YOUR CONSUMER KEY 2',
        'YOUR CONSUMER SECRET 2',
        'YOUR USERNAME 2',
        'YOUR PASSWORD 2',
    ),
    ...
);
```

#### Return Value

An array consisting of the following structure.

```php
$return_value = array(
    'YOUR SCREEN_NAME 0' => new TwistOAuth(...),
    'YOUR SCREEN_NAME 1' => new TwistOAuth(...),
    'YOUR SCREEN_NAME 2' => new TwistOAuth(...),
    ...
);
```

#### Exception

Throws `TwistException`.

### TwistOAuth::curlPostRequestToken()<br />TwistOAuth::curlPostAccessToken()<br />TwistOAuth::curlGet()<br />TwistOAuth::curlGetOut()<br />TwistOAuth::curlPost()<br />TwistOAuth::curlPostOut()<br />TwistOAuth::curlPostMultipart()<br />TwistOAuth::curlPostMultipartOut()<br />TwistOAuth::curlStreaming()

```php
(resource) $to->curlPostRequestToken($proxy = '')
(resource) $to->curlPostAccessToken($oauth_verifier, $proxy = '')
(resource) $to->curlGet($url, $params = array(), $proxy = '')
(resource) $to->curlGetOut($url, $params = array(), $proxy = '')
(resource) $to->curlPost($url, $params = array(), $proxy = '')
(resource) $to->curlPostOut($url, $params = array(), $proxy = '')
(resource) $to->curlPostMultipart($url, $params = array(), $proxy = '')
(resource) $to->curlPostMultipartOut($url, $params = array(), $proxy = '')
(resource) $to->curlStreaming($url, callable $callback, $params = array(), $proxy = '')
```

#### Arguments

(Omitted)

#### Return Value

A cURL resource.

### TwistOAuth::curlMultiExec()<br />TwistOAuth::curlMultiStreaming()

```php
(array) $to->curlMultiExec(array $curls)
(void) $to->curlMultiStreaming(array $curls)
```

#### Arguments

- (array) __*$curls*__<br />An array of cURL resources.


#### Return Value

(Omitted)

#### Exception

Throws `TwistException`.