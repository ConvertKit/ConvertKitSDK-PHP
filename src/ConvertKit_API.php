<?php
/**
 * ConvertKit API
 *
 * @author ConvertKit
 */

namespace ConvertKit_API;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Request;

/**
 * ConvertKit API Class
 */
class ConvertKit_API
{
    use ConvertKit_API_Traits;

    /**
     * Debug
     *
     * @var boolean
     */
    protected $debug;

    /**
     * Debug
     *
     * @var \Monolog\Logger
     */
    protected $debug_logger;

    /**
     * Guzzle Http ClientInterface
     *
     * @var \GuzzleHttp\ClientInterface
     */
    protected $client;

    /**
     * Guzzle Http Response
     *
     * @var \Psr\Http\Message\ResponseInterface
     */
    protected $response;


    /**
     * Constructor for ConvertKitAPI instance
     *
     * @param string  $clientID             OAuth Client ID.
     * @param string  $clientSecret         OAuth Client Secret.
     * @param string  $accessToken          OAuth Access Token.
     * @param boolean $debug                Log requests to debugger.
     * @param string  $debugLogFileLocation Path and filename of debug file to write to.
     */
    public function __construct(
        string $clientID,
        string $clientSecret,
        string $accessToken = '',
        bool $debug = false,
        string $debugLogFileLocation = ''
    ) {
        $this->client_id     = $clientID;
        $this->client_secret = $clientSecret;
        $this->access_token  = $accessToken;
        $this->debug         = $debug;

        // Set the Guzzle client.
        $this->client = new Client();

        if ($debug) {
            // If no debug log file location specified, define a default.
            if (empty($debugLogFileLocation)) {
                $debugLogFileLocation = __DIR__ . '/logs/debug.log';
            }

            $this->debug_logger = new Logger('ck-debug');
            $stream_handler     = new StreamHandler($debugLogFileLocation, Logger::DEBUG);
            $this->debug_logger->pushHandler(
                $stream_handler // phpcs:ignore Squiz.Objects.ObjectInstantiation.NotAssigned
            );
        }
    }

    /**
     * Set the Guzzle client implementation to use for API requests.
     *
     * @param ClientInterface $client Guzzle client implementation.
     *
     * @since 1.3.0
     *
     * @return void
     */
    public function set_http_client(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * Add an entry to monologger.
     *
     * @param string $message Message.
     *
     * @return void
     */
    private function create_log(string $message)
    {
        // Don't log anything if debugging isn't enabled.
        if (!$this->debug) {
            return;
        }

        // Mask the Client ID, Client Secret and Access Token.
        $message = str_replace(
            $this->client_id,
            str_repeat('*', (strlen($this->client_id) - 4)) . substr($this->client_id, - 4),
            $message
        );
        $message = str_replace(
            $this->client_secret,
            str_repeat('*', (strlen($this->client_secret) - 4)) . substr($this->client_secret, - 4),
            $message
        );
        $message = str_replace(
            $this->access_token,
            str_repeat('*', (strlen($this->access_token) - 4)) . substr($this->access_token, - 4),
            $message
        );

        // Mask email addresses that may be contained within the message.
        $message = preg_replace_callback(
            '^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})^',
            function ($matches) {
                return preg_replace('/\B[^@.]/', '*', $matches[0]);
            },
            $message
        );

        // Add to log.
        $this->debug_logger->info((string) $message);
    }

    /**
     * Returns the OAuth URL to begin the OAuth process.
     *
     * @param string $redirectURI Redirect URI.
     *
     * @return string
     */
    public function get_oauth_url(string $redirectURI)
    {
        return $this->oauth_authorize_url . '?' . http_build_query(
            [
                'client_id'     => $this->client_id,
                'redirect_uri'  => $redirectURI,
                'response_type' => 'code',
            ]
        );
    }

    /**
     * Exchanges the given authorization code for an access token and refresh token.
     *
     * @param string $authCode    Authorization Code, returned from get_oauth_url() flow.
     * @param string $redirectURI Redirect URI.
     *
     * @return array<string, int|string> API response
     */
    public function get_access_token(string $authCode, string $redirectURI)
    {
        // Build request.
        $request = new Request(
            method: 'POST',
            uri:    $this->oauth_token_url,
            headers: $this->get_request_headers(
                auth: false
            ),
            body:   (string) json_encode(
                [
                    'code'          => $authCode,
                    'client_id'     => $this->client_id,
                    'client_secret' => $this->client_secret,
                    'grant_type'    => 'authorization_code',
                    'redirect_uri'  => $redirectURI,
                ]
            )
        );

        // Send request.
        $response = $this->client->send(
            $request,
            ['exceptions' => false]
        );

        // Return response body.
        return json_decode($response->getBody()->getContents());
    }

    /**
     * Fetches a new access token using the supplied refresh token.
     *
     * @param string $refreshToken Refresh Token.
     * @param string $redirectURI  Redirect URI.
     *
     * @return array<string, int|string> API response
     */
    public function refresh_token(string $refreshToken, string $redirectURI)
    {
        // Build request.
        $request = new Request(
            method: 'POST',
            uri: $this->oauth_token_url,
            headers: $this->get_request_headers(
                auth: false
            ),
            body: (string) json_encode(
                [
                    'refresh_token' => $refreshToken,
                    'client_id'     => $this->client_id,
                    'client_secret' => $this->client_secret,
                    'grant_type'    => 'refresh_token',
                    'redirect_uri'  => $redirectURI,
                ]
            )
        );

        // Send request.
        $response = $this->client->send(
            $request,
            ['exceptions' => false]
        );

        // Return response body.
        return json_decode($response->getBody()->getContents());
    }

    /**
     * Get markup from ConvertKit for the provided $url.
     *
     * Supports legacy forms and legacy landing pages.
     *
     * Forms and Landing Pages should be embedded using the supplied JS embed script in
     * the API response when using get_forms() or get_landing_pages().
     *
     * @param string $url URL of HTML page.
     *
     * @throws \InvalidArgumentException If the URL is not a valid URL format.
     * @throws \Exception If parsing the legacy form or landing page failed.
     *
     * @return false|string
     */
    public function get_resource(string $url)
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException();
        }

        $resource = '';

        $this->create_log(sprintf('Getting resource %s', $url));

        // Fetch the resource.
        $request  = new Request(
            method: 'GET',
            uri: $url,
            headers: $this->get_request_headers(
                type: 'text/html',
                auth: false
            ),
        );
        $response = $this->client->send($request);

        // Fetch HTML.
        $body = $response->getBody()->getContents();

        // Forcibly tell DOMDocument that this HTML uses the UTF-8 charset.
        // <meta charset="utf-8"> isn't enough, as DOMDocument still interprets the HTML as ISO-8859,
        // which breaks character encoding.
        // Use of mb_convert_encoding() with HTML-ENTITIES is deprecated in PHP 8.2, so we have to use this method.
        // If we don't, special characters render incorrectly.
        $body = str_replace(
            '<head>',
            '<head>' . "\n" . '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">',
            $body
        );

        // Get just the scheme and host from the URL.
        $url_scheme_host_only = parse_url($url, PHP_URL_SCHEME) . '://' . parse_url($url, PHP_URL_HOST);

        // Load the HTML into a DOMDocument.
        libxml_use_internal_errors(true);
        $html = new \DOMDocument();
        $html->loadHTML($body);

        // Convert any relative URLs to absolute URLs in the HTML DOM.
        $this->convert_relative_to_absolute_urls($html->getElementsByTagName('a'), 'href', $url_scheme_host_only);
        $this->convert_relative_to_absolute_urls($html->getElementsByTagName('link'), 'href', $url_scheme_host_only);
        $this->convert_relative_to_absolute_urls($html->getElementsByTagName('img'), 'src', $url_scheme_host_only);
        $this->convert_relative_to_absolute_urls($html->getElementsByTagName('script'), 'src', $url_scheme_host_only);
        $this->convert_relative_to_absolute_urls($html->getElementsByTagName('form'), 'action', $url_scheme_host_only);

        // Save HTML.
        $resource = $html->saveHTML();

        // If the result is false, return a blank string.
        if (!$resource) {
            throw new \Exception(sprintf('Could not parse %s', $url));
        }

        // Remove some HTML tags that DOMDocument adds, returning the output.
        // We do this instead of using LIBXML_HTML_NOIMPLIED in loadHTML(), because Legacy Forms
        // are not always contained in a single root / outer element, which is required for
        // LIBXML_HTML_NOIMPLIED to correctly work.
        $resource = $this->strip_html_head_body_tags($resource);

        return $resource;
    }

    /**
     * Performs an API request using Guzzle.
     *
     * @param string                                                                                                     $endpoint API Endpoint.
     * @param string                                                                                                     $method   Request method.
     * @param array<string, bool|integer|float|string|null|array<int|string, float|integer|string|array<string|string>>> $args     Request arguments.
     *
     * @throws \Exception If JSON encoding arguments failed.
     *
     * @return false|mixed
     */
    public function request(string $endpoint, string $method, array $args = [])
    {
        // Build URL.
        $url = $this->api_url_base . $this->api_version . '/' . $endpoint;

        // Log request.
        $this->create_log(sprintf('%s %s', $method, $endpoint));
        $this->create_log(sprintf('%s', json_encode($args)));

        // Build request.
        switch ($method) {
            case 'GET':
                if ($args) {
                    $url .= '?' . http_build_query($args);
                }

                $request = new Request(
                    method: $method,
                    uri: $url,
                    headers: $this->get_request_headers(),
                );
                break;

            default:
                $request = new Request(
                    method:  $method,
                    uri:     $url,
                    headers: $this->get_request_headers(),
                    body:    (string) json_encode($args),
                );
                break;
        }//end switch

        // Send request.
        $this->response = $this->client->send(
            $request,
            ['exceptions' => false]
        );

        // Get response.
        $response_body = $this->response->getBody()->getContents();

        // Log response.
        $this->create_log(sprintf('Response Status Code: %s', $this->response->getStatusCode()));
        $this->create_log(sprintf('Response Body: %s', $response_body));
        $this->create_log('Finish request successfully');

        // Return response.
        return json_decode($response_body);
    }

    /**
     * Returns the response interface used for the last API request.
     *
     * @since 2.0.0
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getResponseInterface()
    {
        return $this->response;
    }

    /**
     * Returns the headers to use in an API request.
     *
     * @param string  $type Accept and Content-Type Headers.
     * @param boolean $auth Include authorization header.
     *
     * @since 2.0.0
     *
     * @return array<string,string>
     */
    public function get_request_headers(string $type = 'application/json', bool $auth = true)
    {
        $headers = [
            'Accept'       => $type,
            'Content-Type' => $type . '; charset=utf-8',
            'User-Agent'   => $this->get_user_agent(),
        ];

        // If no authorization header required, return now.
        if (!$auth) {
            return $headers;
        }

        // Add authorization header and return.
        $headers['Authorization'] = 'Bearer ' . $this->access_token;
        return $headers;
    }

    /**
     * Returns the maximum amount of time to wait for
     * a response to the request before exiting.
     *
     * @since 2.0.0
     *
     * @return integer     Timeout, in seconds.
     */
    public function get_timeout()
    {
        $timeout = 10;

        return $timeout;
    }

    /**
     * Returns the user agent string to use in all HTTP requests.
     *
     * @since 2.0.0
     *
     * @return string
     */
    public function get_user_agent()
    {
        return 'ConvertKitPHPSDK/' . self::VERSION . ';PHP/' . phpversion();
    }
}
