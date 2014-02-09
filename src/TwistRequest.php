<?php

/**
 * Request class for TwistExecuter.
 * 
 * @property-read string $host
 * @property-read string $endpoint
 * @property-read string $method
 * @property-read array $extraParams
 * @property-read bool $streaming
 * @property-read bool $multipart
 * @property-read bool $waitResponse
 * @property-read bool $throw
 * @property-read bool $login
 * @property-read TwistCredential $credential
 * @property-read array $params
 * @property-read mixed $response
 *
 * @inherited method final protected static mixed TwistBase::filter()
 */
class TwistRequest extends TwistBase {
    
   /**
    * Request.
    * 
    * @var string
    * @var string
    * @var string
    * @var array
    */
    private $host;
    private $endpoint;
    private $method;
    private $extraParams;
    
   /**
    * Request flags.
    *
    * @var bool
    * @var bool
    * @var bool
    * @var bool
    */
    private $streaming;
    private $multipart;
    private $waitResponse;
    private $throw;
    
   /**
    * Para-xAuth authorization flag.
    * 
    * @var bool
    */
    private $login = false;
    
   /**
    * Request parameters.
    * 
    * @var TwistCredential
    * @var array
    */
    private $credential;
    private $params = array();
    
   /**
    * Responses.
    * 
    * @var mixed
    */
    private $response;
    
    /**
     * Create instance for "GET" endpoints.
     * 
     * @final
     * @static
     * @access public
     * @param string [$endpoint]
     *   e.g. "statuses/home_timeline"
     *        "1/account/generete"
     *        "user"
     * @param mixed [$params]
     *   e.g. "count=1"
     *        array("count" => 1)
     * @param TwistCredential [$credential]
     * @return TwistRequest
     */
    // Normal.
    final public static function get($endpoint = '', $params = array(), TwistCredential $credential = null) {
        $args = get_defined_vars();
        $args += array(
            'method'       => 'GET',
            'waitResponse' => true,
            'throw'        => false,
        );
        return new self($args);
    }
    // Automatically throw TwistException.
    final public static function getAuto($endpoint = '', $params = array(), TwistCredential $credential = null) {
        $args = get_defined_vars();
        $args += array(
            'method'       => 'GET',
            'waitResponse' => true,
            'throw'        => true,
        );
        return new self($args);
    }
    
    /**
     * Create instance for "POST" endpoints.
     * Filenames are specified with putting "@" on its KEY.
     * 
     * @final
     * @static
     * @access public
     * @param string $endpoint
     *   e.g. "account/update_profile_image"
     * @param mixed [$params]
     *   e.g. "@image=me.jpg"
     *        array("@image" => "me.jpg")
     * @param TwistCredential [$credential]
     * @return TwistRequest
     */
    // Normal.
    final public static function post($endpoint = '', $params = array(), TwistCredential $credential = null) {
        $args = get_defined_vars();
        $args += array(
            'method'       => 'POST',
            'waitResponse' => true,
            'throw'        => false,
        );
        return new self($args);
    }
    // Automatically throw TwistException.
    final public static function postAuto($endpoint = '', $params = array(), TwistCredential $credential = null) {
        $args = get_defined_vars();
        $args += array(
            'method'       => 'POST',
            'waitResponse' => true,
            'throw'        => true,
        );
        return new self($args);
    }
    // Receive no response.
    final public static function send($endpoint = '', $params = array(), TwistCredential $credential = null) {
        $args = get_defined_vars();
        $args += array(
            'method'       => 'POST',
            'waitResponse' => false,
            'throw'        => false,
        );
        return new self($args);
    }
    
    /**
     * Create instance for Para-xAuth authorization.
     * TwistCredential instance cannot be changed.
     *
     * @final
     * @static
     * @access public
     * @param TwistCredential $credential
     * @return TwistRequest
     */
    final public static function login(TwistCredential $credential) {
        $args = array(
            'endpoint'     => 'oauth/request_token',
            'method'       => 'POST',
            'params'       => array(),
            'credential'   => $credential,
            'waitResponse' => true,
            'throw'        => true,
        );
        $self = new self($args);
        $self->login = true; // set flag
        return $self;
    }
    
    /**
     * Getter for properties.
     * 
     * @magic
     * @final
     * @access public
     * @param string $name
     * @throw OutOfRangeException(LogicException)
     * @return mixed
     */
    final public function __get($name) {
        if (!property_exists($this, $name = self::filter($name))) {
            throw new OutOfRangeException("Invalid property name: {$name}");
        }
        return $this->$name;
    }
    
    /**
     * Bind or unset request parameters.
     *
     * @final
     * @access public
     * @param mixed [$params]
     * @throw BadMethodCallException(LogicException)
     * @return TwistRequest $this
     */
    final public function setParams($params = array()) {
        if ($this->login) {
            throw new BadMethodCallException(
                'This object is created by TwistRequest::login() call.'
            );
        }
        $this->params = 
            is_array($params) ?
            self::filter($params, 1) :
            self::parseQuery(self::filter($params))
        ; 
        return $this;
    }
    
    /**
     * Bind or unset TwistCredential instance.
     * 
     * @final
     * @access public
     * @param TwistCredential [$credential]
     * @throw BadMethodCallException(LogicException)
     * @return TwistRequest $this
     */
    final public function setCredential(TwistCredential $credential = null) {
        if ($this->login) {
            throw new BadMethodCallException(
                'This object is created by TwistRequest::login() call.'
            );
        }
        $this->credential = $credential;
        return $this;
    }
    
    /**
     * Bind or unset response.
     *
     * @final
     * @access public
     * @param mixed [$body]
     * @return TwistRequest $this
     */
    final public function setResponse($body = null) {
        $this->response = $body;
        return $this;
    }
    
    /**
     * Easy execution using TwistIterator.
     * 
     * @final
     * @access public
     * @return mixed stdClass or array or TwistException or NULL
     */
    final public function execute() {
        foreach (new TwistIterator($this) as $request) {
            return $request instanceof TwistException ? $request : $request->response;
        }
    }
    
    /**
     * Proceed Para-xAuth authorization step.
     * TwistCredential instance cannot be changed.
     *
     * @final
     * @access public
     * @return TwistRequest $this
     */
    final public function proceed() {
        switch (true) {
            case !$this->login:
                throw new BadMethodCallException(
                    'This object is not created by TwistRequest::login() call.'
                );
            case $this->response instanceof TwistException:
            case $this->endpoint === '/oauth/access_token':
                $args = array(
                    'method'   => 'POST',
                    'endpoint' => '/oauth/request_token',
                );
                break;
            case $this->endpoint === '/oauth/request_token':
                $args = array(
                    'method'   => 'GET',
                    'endpoint' => '/oauth/authorize',
                );
                break;
            case $this->endpoint === '/oauth/authorize'
            and $this->method === 'GET':
                $args = array(
                    'method'   => 'POST',
                    'endpoint' => '/oauth/authorize',
                );
                break;
            case $this->endpoint === '/oauth/authorize'
            and $this->method === 'POST':
                $args = array(
                    'method'   => 'POST',
                    'endpoint' => '/oauth/access_token',
                );
                break;
            default:
                throw new BsdMethodCallException('Unexpected endpoint.');
        }
        $args += array(
            'params'       => array(),
            'credential'   => $this->credential,
            'waitResponse' => true,
            'throw'        => true,
        );
        $this->__construct($args);
        return $this;
    }
    
    /**
     * Build headers for request.
     *
     * @final
     * @access public
     * @throw BadMethodCallException(LogicException)
     * @return string
     */
    final public function buildHeaders() {
        if (!($this->credential instanceof TwistCredential)) {
            // TwistCredential instance is required
            throw new BadMethodCallException(
                'Headers cannot be built without TwistCredential instance.'
            );
        }
        $params = $this->solveParams();
        $connection = $this->streaming ? 'keep-alive' : 'close';
        $user_agent = urlencode($this->credential->userAgent);
        $content = $this->buildOAuthPart($params);
        if ($this->method === 'GET') {
            // GET
            if ('' !== $query = self::buildQuery($params)) {
                $content .= "&{$query}";
            }
            $lines = array(
                "{$this->method} {$this->endpoint}?{$content} HTTP/1.1",
                "Host: {$this->host}",
                "User-Agent: {$user_agent}",
                "Connection: {$connection}",
                "",
                "",
            );
        } elseif (!$this->multipart) {
            // POST
            if ('' !== $query = self::buildQuery($params)) {
                $content .= "&{$query}";
            }
            $length = strlen($content);
            $lines = array(
                "{$this->method} {$this->endpoint} HTTP/1.1",
                "Host: {$this->host}",
                "User-Agent: {$user_agent}",
                "Connection: {$connection}",
                "Content-Type: application/x-www-form-urlencoded",
                "Content-Length: {$length}",
                "",
                $content,
            );
        } else {
            // POST Multipart
            $boundary = '--------------------' . sha1(mt_rand() . microtime());
            $authorization = implode(', ', explode('&', $content));
            $content = self::buildMultipartContent($params, $boundary);
            $length = strlen($content);
            $lines = array(
                "{$this->method} {$this->endpoint} HTTP/1.1",
                "Host: {$this->host}",
                "User-Agent: {$user_agent}",
                "Connection: {$connection}",
                "Authorization: OAuth {$authorization}",
                "Content-Type: multipart/form-data; boundary={$boundary}",
                "Content-Length: {$length}",
                "",
                $content,
            );
        }
        if (!$this->streaming) {
            // enable gzip if not streaming
            array_splice($lines, 3, 0, "Accept-Encoding: deflate, gzip");
        }
        if ($this->credential->cookies) {
            // apply cookies
            $cookie = http_build_query($this->credential->cookies, '', '; ');
            array_splice($lines, 3, 0, "Cookie: {$cookie}");
        }
        return implode("\r\n", $lines);
    }
    
    /**
     * Build multipart/form-data.
     *
     * @static
     * @access private
     * @param array $params
     * @param string $boundary
     * @return string
     */
    private static function buildMultipartContent(array $params, $boundary) {
        $lines = array();
        foreach ($params as $key => $value) {
            if ($key === 'media[]') {
                // files
                $filename = md5(mt_rand() . microtime());
                $disposition = "form-data; name=\"{$key}\"; filename=\"{$filename}\"";
            } else {
                // string
                $disposition = "form-data; name=\"{$key}\"";
            }
            array_push(
                $lines,
                "--{$boundary}",
                "Content-Disposition: {$disposition}",
                "Content-Type: application/octet-stream",
                "",
                $value
            );
        }
        $lines[] = "--{$boundary}--";
        return implode("\r\n", $lines);
    }
    
    /**
     * Build query based on RFC 3986.
     *
     * @static
     * @access private
     * @param array $params
     * @param bool [$pair] KEY=VALUE pair ?
     * @return string
     */
    private static function buildQuery(array $params, $pair = true) {
        $new = array();
        foreach ($params as $key => $value) {
            // support for PHP 5.2
            $value = str_replace('%7E', '~', rawurlencode($value));
            $new[$key] = $pair ? "{$key}={$value}" : $value;
        }
        uksort($new, 'strnatcmp');
        return implode('&', $new);
    }
    
    /**
     * Parse query as 1-demention assoc.
     *
     * @static
     * @access private
     * @param string $query
     * @return string
     */
    private static function parseQuery($query) {
        foreach (explode('&', $query) as $pair) {
            list($k, $v) = explode('=', $pair, 2) + array(1 => '');
            $params[$k] = $v;
        }
        if ($params === array('' => '')) {
            // ignore empty element
            $params = array();
        }
        return $params;
    }
    
    /**
     * Constructor.
     *
     * @magic
     * @access private
     * @param array $args
     */
    private function __construct(array $args) {
        $this->params = 
            is_array($args['params']) ?
            self::filter($args['params'], 1) :
            self::parseQuery(self::filter($args['params']))
        ; 
        $this->credential = $args['credential'];
        $this->setEndpoint($args['endpoint']);
        $this->method = $args['method'];
        $this->waitResponse = $args['waitResponse'];
        $this->throw = $args['throw'];
    }
    
    /**
     * Initialize request endpoint.
     *
     * @access private
     * @throw InvalidArgumentException(LogicException)
     * @param string $endpoint
     */
    private function setEndpoint($endpoint) {
        static $streamings = array('filter', 'sample', 'firehose');
        $endpoint = self::filter($endpoint);
        switch (true) {
            case !$p = parse_url($endpoint):
            case !isset($p['path']):
            case !$count = preg_match_all('/(?![\d.])[\w.]++/', $p['path'], $parts):
                throw new InvalidArgumentException("invalid endpoint: {$endpoint}");
        }
        $streaming = $multipart = $old = !$host = 'api.twitter.com';
        foreach ($parts[0] as $i => &$part) {
            $part = strtolower($part);
            if ($count === $i + 1) {
                switch (true) {
                    case $parts[0][0] === 'oauth2':
                        throw new InvalidArgumentException(
                            "this library does not support OAuth 2.0 authentication"
                        );
                    case $parts[0][0] === 'oauth':
                        $part = basename($part, '.json');
                        break 2;
                    case $old = $parts[0][0] === 'urls' and $host = 'urls.api.twitter.com':
                    case $old = $parts[0][0] === 'generate':
                    case $streaming = $parts[0][0] === 'user' and $host = 'userstream.twitter.com':
                    case $streaming = $parts[0][0] === 'site' and $host = 'sitestream.twitter.com':
                    case $streaming = in_array($part, $streamings) and $host = 'stream.twitter.com':
                    default:
                        $multipart = $part === 'update_with_media';
                        $part = basename($part, '.json') . '.json';
                        array_splice($parts[0], 0, 0, $old ? '1' : '1.1');
                        break 2;
                }
            }
        }
        $this->host        = $host;
        $this->endpoint    = '/' . implode('/', $parts[0]);
        $this->extraParams = isset($p['query']) ? self::parseQuery($p['query']) : array();
        $this->streaming   = $streaming;
        $this->multipart   = $multipart;
        return $this;
    }
    
    /**
     * Normalize parameters and replace filenames into filedata.
     *
     * @access private
     * @throw InvalidArgumentException(LogicException)
     */
    private function solveParams() {
        $new = array();
        // put priority on $this->params
        $params = $this->params + $this->extraParams;
        foreach ($params as $key => $value) {
            if ($value === null) {
                // FALSE should be treated as "0"
                continue;
            }
            if ($value === false) {
                // FALSE should be treated as "0"
                $value = '0';
            }
            $value = self::filter($value);
            if (strpos($key, '@') === 0) {
                // solve filenames
                if (!is_readable($value) or !is_file($value)) {
                    throw new InvalidArgumentException("File not found: {$value}");
                }
                $key = (string)substr($key, 1);
                $value = file_get_contents($value);
                if (!$this->multipart) {
                    // BASE64 option
                    $value = base64_encode($value);
                }
            }
            $new[$key] = $value;
        }
        return $new;
    }
    
    /**
     * Build assoc including oauth_signature.
     *
     * @access private
     * @param array &params
     * @return array
     */
    private function buildOAuthPart(array $params) {
        if (in_array(
            $this->endpoint,
            array('/oauth/authorize', '/oauth/authenticate'),
            true
        )) {
            $bodies['oauth_token'] = $this->credential->requestToken;
            $bodies['force_login'] = '1';
            if ($this->method === 'POST') {
                $bodies['authenticity_token'] = $this->credential->authenticityToken;
                $bodies['session[username_or_email]'] = $this->credential->screenName;
                $bodies['session[password]'] = $this->credential->password;
            }
            return self::buildQuery($bodies);
        }
        $bodies = array(
            'oauth_consumer_key'     => $this->credential->consumerKey,
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp'        => time(),
            'oauth_version'          => '1.0a',
            'oauth_nonce'            => sha1(mt_rand() . microtime()),
        );
        $keys = array($this->credential->consumerSecret, '');
        if ($this->endpoint === '/oauth/access_token') {
            $bodies['oauth_token'] = $this->credential->requestToken;
            $bodies['oauth_verifier'] = $this->credential->verifier;
            $keys[1] = $this->credential->requestTokenSecret;
        } elseif ($this->endpoint !== '/oauth/request_token') {
            $bodies['oauth_token'] = $this->credential->accessToken;
            $keys[1] = $this->credential->accessTokenSecret;
        }
        $copy = $bodies;
        if (!$this->multipart) {
            $copy += $params;
        }
        $url = "https://{$this->host}{$this->endpoint}";
        $copy = self::buildQuery(array($this->method, $url, self::buildQuery($copy)), false);
        $keys = self::buildQuery($keys, false);
        $bodies['oauth_signature'] = base64_encode(hash_hmac('sha1', $copy, $keys, true));
        return self::buildQuery($bodies);
    }
    
}
