<?php
/**
 * ConvertKit API
 *
 * @package    ConvertKit
 * @subpackage ConvertKit_API
 * @author     ConvertKit
 */

namespace ConvertKit_API;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

/**
 * ConvertKit API Class
 */
class ConvertKit_API
{
    /**
     * The SDK version.
     *
     * @var string
     */
    public const VERSION = '1.0.0';

    /**
     * ConvertKit API Key
     *
     * @var string
     */
    protected $api_key;

    /**
     * ConvertKit API Secret
     *
     * @var string
     */
    protected $api_secret;

    /**
     * Version of ConvertKit API
     *
     * @var string
     */
    protected $api_version = 'v3';

    /**
     * ConvertKit API URL
     *
     * @var string
     */
    protected $api_url_base = 'https://api.convertkit.com/';

    /**
     * API resources
     *
     * @var array<int|string, array<int|string, mixed|\stdClass>>
     */
    protected $resources = [];

    /**
     * Additional markup
     *
     * @var array<string, string>
     */
    protected $markup = [];

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
     * Guzzle Http Client
     *
     * @var \GuzzleHttp\Client
     */
    protected $client;


    /**
     * Constructor for ConvertKitAPI instance
     *
     * @param string  $api_key    ConvertKit API Key.
     * @param string  $api_secret ConvertKit API Secret.
     * @param boolean $debug      Log requests to debugger.
     */
    public function __construct(string $api_key, string $api_secret, bool $debug = false)
    {
        $this->api_key    = $api_key;
        $this->api_secret = $api_secret;
        $this->debug      = $debug;

        // Specify a User-Agent for API requests.
        $this->client = new Client(
            [
                'headers' => [
                    'User-Agent' => 'ConvertKitPHPSDK/' . self::VERSION . ';PHP/' . phpversion(),
                ],
            ]
        );

        if ($debug) {
            $this->debug_logger = new Logger('ck-debug');
            $stream_handler     = new StreamHandler(__DIR__ . '/logs/debug.log', Logger::DEBUG);
            $this->debug_logger->pushHandler(
                $stream_handler // phpcs:ignore Squiz.Objects.ObjectInstantiation.NotAssigned
            );
        }
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
        if ($this->debug) {
            $this->debug_logger->info($message);
        }
    }

    /**
     * Gets the current account
     *
     * @return false|mixed
     */
    public function get_account()
    {
        return $this->get(
            'account',
            [
                'api_secret' => $this->api_secret,
            ]
        );
    }

    /**
     * Gets all forms.
     *
     * @since 1.0.0
     *
     * @return false|mixed
     */
    public function get_forms()
    {
        return $this->get_resources('forms');
    }

    /**
     * Gets all landing pages.
     *
     * @since 1.0.0
     *
     * @return false|mixed
     */
    public function get_landing_pages()
    {
        return $this->get_resources('landing_pages');
    }

    /**
     * Gets all sequences
     *
     * @return false|mixed
     */
    public function get_sequences()
    {
        return $this->get(
            'sequences',
            [
                'api_key' => $this->api_key,
            ]
        );
    }

    /**
     * Gets subscribers to a sequence
     *
     * @param integer $sequence_id Sequence ID.
     * @param string  $sort_order  Sort Order (asc|desc).
     *
     * @return false|mixed
     */
    public function get_sequence_subscriptions(int $sequence_id, string $sort_order = 'asc')
    {
        return $this->get(
            sprintf('sequences/%s/subscriptions', $sequence_id),
            [
                'api_secret' => $this->api_secret,
                'sort_order' => $sort_order,
            ]
        );
    }

    /**
     * Adds a subscriber to a sequence by email address
     *
     * @param integer $sequence_id Sequence ID.
     * @param string  $email       Email Address.
     *
     * @return false|mixed
     */
    public function add_subscriber_to_sequence(int $sequence_id, string $email)
    {
        return $this->post(
            sprintf('courses/%s/subscribe', $sequence_id),
            [
                'api_key' => $this->api_key,
                'email'   => $email,
            ]
        );
    }

    /**
     * Adds a tag to a subscriber
     *
     * @param integer              $tag     Tag ID.
     * @param array<string, mixed> $options Array of user data.
     *
     * @throws \InvalidArgumentException If the provided arguments are not of the expected type.
     *
     * @return false|object
     */
    public function add_tag(int $tag, array $options)
    {
        if (!is_int($tag)) {
            throw new \InvalidArgumentException();
        }
        if (!is_array($options)) {
            throw new \InvalidArgumentException();
        }

        // Add API Key to array of options.
        $options['api_key'] = $this->api_key;

        return $this->post(
            sprintf('tags/%s/subscribe', $tag),
            $options
        );
    }

    /**
     * Gets a resource index
     * Possible resources: forms, landing_pages, subscription_forms, tags
     *
     * GET /{$resource}/
     *
     * @param string $resource Resource type.
     *
     * @throws \InvalidArgumentException If the provided arguments are not of the expected type.
     *
     * @return array<int|string, mixed|\stdClass> API response
     */
    public function get_resources(string $resource)
    {
        if (!is_string($resource)) {
            throw new \InvalidArgumentException();
        }

        // Return cached resource if it exists.
        if (array_key_exists($resource, $this->resources)) {
            return $this->resources[$resource];
        }

        // Assign the resource to the request variable.
        $request = $resource;

        // Landing pages are included in the /forms endpoint.
        if ($resource === 'landing_pages') {
            $request = 'forms';
        }

        // Fetch resources.
        $resources = $this->get(
            $request,
            [
                'api_key' => $this->api_key,
            ]
        );

        $this->create_log(sprintf('%s response %s', $resource, json_encode($resources)));

        // Return a blank array if no resources exist.
        if (!$resources) {
            $this->create_log('No resources');
            return [];
        }

        // Build array of resources.
        $_resource = [];
        switch ($resource) {
            // Forms.
            case 'forms':
                // Bail if no forms are set.
                if (!isset($resources->forms)) {
                    $this->create_log('No form resources');
                    return [];
                }

                // Build array of forms.
                foreach ($resources->forms as $form) {
                    // Exclude archived forms.
                    if (isset($form->archived) && $form->archived) {
                        continue;
                    }

                    // Exclude hosted forms.
                    if ($form->type === 'hosted') {
                        continue;
                    }

                    $_resource[] = $form;
                }
                break;

            // Landing Pages.
            case 'landing_pages':
                // Bail if no landing pages are set.
                if (!isset($resources->forms)) {
                    $this->create_log('No landing page resources');
                    return [];
                }

                foreach ($resources->forms as $form) {
                    // Exclude archived landing pages.
                    if (isset($form->archived) && $form->archived) {
                        continue;
                    }

                    // Exclude non-hosted (i.e. forms).
                    if ($form->type !== 'hosted') {
                        continue;
                    }

                    $_resource[] = $form;
                }
                break;

            // Subscription Forms.
            case 'subscription_forms':
                // Exclude archived subscription forms.
                foreach ($resources as $mapping) {
                    if (isset($mapping->archived) && $mapping->archived) {
                        continue;
                    }

                    $_resource[$mapping->id] = $mapping->form_id;
                }
                break;

            // Tags.
            case 'tags':
                // Bail if no tags are set.
                if (!isset($resources->tags)) {
                    $this->create_log('No tag resources');
                    return [];
                }

                foreach ($resources->tags as $tag) {
                    $_resource[] = $tag;
                }
                break;

            default:
                throw new \InvalidArgumentException('An unsupported resource was specified.');
        }//end switch

        // Cache resources and return.
        $this->resources[$resource] = $_resource;

        return $this->resources[$resource];
    }

    /**
     * Adds a subscriber to a form.
     *
     * @param integer               $form_id Form ID.
     * @param array<string, string> $options Array of user data (email, name).
     *
     * @throws \InvalidArgumentException If the provided arguments are not of the expected type.
     *
     * @return false|object
     */
    public function form_subscribe(int $form_id, array $options)
    {
        if (!is_int($form_id)) {
            throw new \InvalidArgumentException();
        }
        if (!is_array($options)) {
            throw new \InvalidArgumentException();
        }

        // Add API Key to array of options.
        $options['api_key'] = $this->api_key;

        return $this->post(
            sprintf('forms/%s/subscribe', $form_id),
            $options
        );
    }

    /**
     * Remove subscription from a form
     *
     * @param array<string, string> $options Array of user data (email).
     *
     * @throws \InvalidArgumentException If the provided arguments are not of the expected type.
     *
     * @return false|object
     */
    public function form_unsubscribe(array $options)
    {
        if (!is_array($options)) {
            throw new \InvalidArgumentException();
        }

        // Add API Secret to array of options.
        $options['api_secret'] = $this->api_secret;

        return $this->put('unsubscribe', $options);
    }

    /**
     * Get the ConvertKit subscriber ID associated with email address if it exists.
     * Return false if subscriber not found.
     *
     * @param string $email_address Email Address.
     *
     * @throws \InvalidArgumentException If the provided arguments are not of the expected type.
     *
     * @return false|integer
     */
    public function get_subscriber_id(string $email_address)
    {
        if (!is_string($email_address)) {
            throw new \InvalidArgumentException();
        }
        if (!filter_var($email_address, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException();
        }

        $subscribers = $this->get(
            'subscribers',
            [
                'api_secret'    => $this->api_secret,
                'status'        => 'all',
                'email_address' => $email_address,
            ]
        );

        if (!$subscribers) {
            $this->create_log('No subscribers');
            return false;
        }

        if ($subscribers->total_subscribers === 0) {
            $this->create_log('No subscribers');
            return false;
        }

        // Return the subscriber's ID.
        return $subscribers->subscribers[0]->id;
    }

    /**
     * Get subscriber by id
     *
     * @param integer $subscriber_id Subscriber ID.
     *
     * @throws \InvalidArgumentException If the provided arguments are not of the expected type.
     *
     * @return false|integer
     */
    public function get_subscriber(int $subscriber_id)
    {
        if (!is_int($subscriber_id) || $subscriber_id < 1) {
            throw new \InvalidArgumentException();
        }

        return $this->get(
            sprintf('subscribers/%s', $subscriber_id),
            [
                'api_secret' => $this->api_secret,
            ]
        );
    }

    /**
     * Get a list of the tags for a subscriber.
     *
     * @param integer $subscriber_id Subscriber ID.
     *
     * @throws \InvalidArgumentException If the provided arguments are not of the expected type.
     *
     * @return false|array<int,\stdClass>
     */
    public function get_subscriber_tags(int $subscriber_id)
    {
        if (!is_int($subscriber_id) || $subscriber_id < 1) {
            throw new \InvalidArgumentException();
        }

        return $this->get(
            sprintf('subscribers/%s/tags', $subscriber_id),
            [
                'api_key' => $this->api_key,
            ]
        );
    }

    /**
     * List purchases.
     *
     * @param array<string, string> $options Request options.
     *
     * @throws \InvalidArgumentException If the provided arguments are not of the expected type.
     *
     * @return false|object
     */
    public function list_purchases(array $options)
    {
        if (!is_array($options)) {
            throw new \InvalidArgumentException();
        }

        // Add API Secret to array of options.
        $options['api_secret'] = $this->api_secret;

        return $this->get('purchases', $options);
    }

    /**
     * Creates a purchase.
     *
     * @param array<string, string> $options Purchase data.
     *
     * @throws \InvalidArgumentException If the provided arguments are not of the expected type.
     *
     * @return false|object
     */
    public function create_purchase(array $options)
    {
        if (!is_array($options)) {
            throw new \InvalidArgumentException();
        }

        // Add API Secret to array of options.
        $options['api_secret'] = $this->api_secret;

        return $this->post('purchases', $options);
    }

    /**
     * Get markup from ConvertKit for the provided $url.
     *
     * Supports legacy forms and legacy landing pages.
     * Forms and Landing Pages should be embedded using the supplied JS embed script in
     * the API response when using get_resources().
     *
     * @param string $url URL of HTML page.
     *
     * @throws \InvalidArgumentException If the provided arguments are not of the expected type.
     * @throws \Exception If parsing the legacy form or landing page failed.
     *
     * @return false|string
     */
    public function get_resource(string $url)
    {
        if (!is_string($url)) {
            throw new \InvalidArgumentException();
        }
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException();
        }

        $resource = '';

        $this->create_log(sprintf('Getting resource %s', $url));

        // If the resource was already fetched, return the cached version now.
        if (isset($this->markup[$url])) {
            $this->create_log('Resource already set');
            return $this->markup[$url];
        }

        // Fetch the resource.
        $request  = new Request(
            'GET',
            $url,
            ['Accept-Encoding' => 'gzip']
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

        // Cache and return.
        $this->markup[$url] = $resource;
        return $resource;
    }

    /**
     * Converts any relative URls to absolute, fully qualified HTTP(s) URLs for the given
     * DOM Elements.
     *
     * @param \DOMNodeList<\DOMElement> $elements  Elements.
     * @param string                    $attribute HTML Attribute.
     * @param string                    $url       Absolute URL to prepend to relative URLs.
     *
     * @since 1.0.0
     *
     * @return void
     */
    private function convert_relative_to_absolute_urls(\DOMNodeList $elements, string $attribute, string $url) // phpcs:ignore Squiz.Commenting.FunctionComment.IncorrectTypeHint, Generic.Files.LineLength.TooLong
    {
        // Anchor hrefs.
        foreach ($elements as $element) {
            // Skip if the attribute's value is empty.
            if (empty($element->getAttribute($attribute))) {
                continue;
            }

            // Skip if the attribute's value is a fully qualified URL.
            if (filter_var($element->getAttribute($attribute), FILTER_VALIDATE_URL)) {
                continue;
            }

            // Skip if this is a Google Font CSS URL.
            if (strpos($element->getAttribute($attribute), '//fonts.googleapis.com') !== false) {
                continue;
            }

            // If here, the attribute's value is a relative URL, missing the http(s) and domain.
            // Prepend the URL to the attribute's value.
            $element->setAttribute($attribute, $url . $element->getAttribute($attribute));
        }
    }

    /**
     * Strips <html>, <head> and <body> opening and closing tags from the given markup,
     * as well as the Content-Type meta tag we might have added in get_html().
     *
     * @param string $markup HTML Markup.
     *
     * @since 1.0.0
     *
     * @return string              HTML Markup
     */
    private function strip_html_head_body_tags(string $markup)
    {
        $markup = str_replace('<html>', '', $markup);
        $markup = str_replace('</html>', '', $markup);
        $markup = str_replace('<head>', '', $markup);
        $markup = str_replace('</head>', '', $markup);
        $markup = str_replace('<body>', '', $markup);
        $markup = str_replace('</body>', '', $markup);
        $markup = str_replace('<meta http-equiv="Content-Type" content="text/html; charset=utf-8">', '', $markup);

        return $markup;
    }

    /**
     * Performs a GET request to the API.
     *
     * @param string                    $endpoint API Endpoint.
     * @param array<string, int|string> $args     Request arguments.
     *
     * @throws \InvalidArgumentException If the provided arguments are not of the expected type.
     *
     * @return false|mixed
     */
    public function get(string $endpoint, array $args = [])
    {
        // Log if debugging enabled.
        $this->create_log(sprintf('GET %s: %s', $endpoint, json_encode($args)));

        // Make request and return results.
        return $this->make_request($endpoint, 'GET', $args);
    }

    /**
     * Performs a POST request to the API.
     *
     * @param string                    $endpoint API Endpoint.
     * @param array<string, int|string> $args     Request arguments.
     *
     * @throws \InvalidArgumentException If the provided arguments are not of the expected type.
     *
     * @return false|mixed
     */
    public function post(string $endpoint, array $args = [])
    {
        // Log if debugging enabled.
        $this->create_log(sprintf('POST %s: %s', $endpoint, json_encode($args)));

        // Make request and return results.
        return $this->make_request($endpoint, 'POST', $args);
    }

    /**
     * Performs a PUT request to the API.
     *
     * @param string                    $endpoint API Endpoint.
     * @param array<string, int|string> $args     Request arguments.
     *
     * @throws \InvalidArgumentException If the provided arguments are not of the expected type.
     *
     * @return false|mixed
     */
    public function put(string $endpoint, array $args = [])
    {
        // Log if debugging enabled.
        $this->create_log(sprintf('PUT %s: %s', $endpoint, json_encode($args)));

        // Make request and return results.
        return $this->make_request($endpoint, 'PUT', $args);
    }

    /**
     * Performs a DELETE request to the API.
     *
     * @param string                    $endpoint API Endpoint.
     * @param array<string, int|string> $args     Request arguments.
     *
     * @throws \InvalidArgumentException If the provided arguments are not of the expected type.
     *
     * @return false|mixed
     */
    public function delete(string $endpoint, array $args = [])
    {
        // Log if debugging enabled.
        $this->create_log(sprintf('DELETE %s: %s', $endpoint, json_encode($args)));

        // Make request and return results.
        return $this->make_request($endpoint, 'DELETE', $args);
    }

    /**
     * Performs an API request using Guzzle.
     *
     * @param string                    $endpoint API Endpoint.
     * @param string                    $method   Request method (POST, GET, PUT, PATCH, DELETE).
     * @param array<string, int|string> $args     Request arguments.
     *
     * @throws \InvalidArgumentException If the provided arguments are not of the expected type.
     * @throws \Exception If JSON encoding arguments failed.
     *
     * @return false|mixed
     */
    public function make_request(string $endpoint, string $method, array $args = [])
    {
        if (!is_string($endpoint)) {
            throw new \InvalidArgumentException();
        }
        if (!is_string($method)) {
            throw new \InvalidArgumentException();
        }
        if (!is_array($args)) {
            throw new \InvalidArgumentException();
        }

        // Build URL.
        $url = $this->api_url_base . $this->api_version . '/' . $endpoint;

        $this->create_log(sprintf('Making request on %s.', $url));

        // Build request body.
        $request_body = json_encode($args);

        $this->create_log(sprintf('%s, Request body: %s', $method, $request_body));

        // Bail if an error occured encoind the arguments.
        if (!$request_body) {
            throw new \Exception('Error encoding arguments');
        }

        if ($method === 'GET') {
            if ($args) {
                $url .= '?' . http_build_query($args);
            }

            $request = new Request($method, $url);
        } else {
            $request = new Request(
                $method,
                $url,
                [
                    'Content-Type'   => 'application/json',
                    'Content-Length' => strlen($request_body),
                ],
                $request_body
            );
        }

        // Send request.
        $response = $this->client->send(
            $request,
            ['exceptions' => false]
        );

        // Inspect response.
        $status_code = $response->getStatusCode();

        // If not between 200 and 300.
        if (!preg_match('/^[2-3][0-9]{2}/', (string) $status_code)) {
            $this->create_log(sprintf('Response code is %s.', $status_code));
            return false;
        }

        // Inspect response body.
        $response_body = json_decode($response->getBody()->getContents());

        if ($response_body) {
            $this->create_log('Finish request successfully.');
            return $response_body;
        }

        $this->create_log('Failed to finish request.');
        return false;
    }
}
