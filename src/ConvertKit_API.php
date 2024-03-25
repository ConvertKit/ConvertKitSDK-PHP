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
    /**
     * The SDK version.
     *
     * @var string
     */
    public const VERSION = '2.0.0';

    /**
     * ConvertKit OAuth Application Client ID
     *
     * @var string
     */
    protected $client_id = '';

    /**
     * ConvertKit OAuth Application Client Secret
     *
     * @var string
     */
    protected $client_secret = '';

    /**
     * Access Token
     *
     * @var string
     */
    protected $access_token = '';

    /**
     * OAuth Authorization URL
     *
     * @var string
     */
    protected $oauth_authorize_url = 'https://app.convertkit.com/oauth/authorize';

    /**
     * OAuth Token URL
     *
     * @var string
     */
    protected $oauth_token_url = 'https://api.convertkit.com/oauth/token';

    /**
     * Version of ConvertKit API
     *
     * @var string
     */
    protected $api_version = 'v4';

    /**
     * ConvertKit API URL
     *
     * @var string
     */
    protected $api_url_base = 'https://api.convertkit.com/';

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

        // Set headers.
        $headers = [
            'Accept'       => 'application/json',
            'Content-Type' => 'application/json; charset=utf-8',
            'User-Agent'   => 'ConvertKitPHPSDK/' . self::VERSION . ';PHP/' . phpversion(),
        ];
        if (!empty($this->access_token)) {
            $headers['Authorization'] = 'Bearer ' . $this->access_token;
        }

        // Set the Guzzle client.
        $this->client = new Client(
            ['headers' => $headers]
        );

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
     * Gets the current account
     *
     * @see https://developers.convertkit.com/#account
     *
     * @return false|mixed
     */
    public function get_account()
    {
        return $this->get('account');
    }

    /**
     * Gets the account's colors
     *
     * @see https://developers.convertkit.com/v4.html#list-colors
     *
     * @return false|mixed
     */
    public function get_account_colors()
    {
        return $this->get('account/colors');
    }

    /**
     * Gets the account's colors
     *
     * @param array<string, string> $colors Hex colors.
     *
     * @see https://developers.convertkit.com/v4.html#list-colors
     *
     * @return false|mixed
     */
    public function update_account_colors(array $colors)
    {
        return $this->put(
            endpoint: 'account/colors',
            args: ['colors' => $colors]
        );
    }

    /**
     * Gets the Creator Profile
     *
     * @see https://developers.convertkit.com/v4.html#get-creator-profile
     *
     * @return false|mixed
     */
    public function get_creator_profile()
    {
        return $this->get('account/creator_profile');
    }

    /**
     * Gets email stats
     *
     * @see https://developers.convertkit.com/v4.html#get-email-stats
     *
     * @return false|mixed
     */
    public function get_email_stats()
    {
        return $this->get('account/email_stats');
    }

    /**
     * Gets growth stats
     *
     * @param \DateTime $starting Gets stats for time period beginning on this date. Defaults to 90 days ago.
     * @param \DateTime $ending   Gets stats for time period ending on this date. Defaults to today.
     *
     * @see https://developers.convertkit.com/v4.html#get-growth-stats
     *
     * @return false|mixed
     */
    public function get_growth_stats(\DateTime $starting = null, \DateTime $ending = null)
    {
        return $this->get(
            'account/growth_stats',
            [
                'starting' => (!is_null($starting) ? $starting->format('Y-m-d') : ''),
                'ending'   => (!is_null($ending) ? $ending->format('Y-m-d') : ''),
            ]
        );
    }

    /**
     * Gets all forms.
     *
     * @since 1.0.0
     *
     * @see https://developers.convertkit.com/#forms
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
     * @see https://developers.convertkit.com/#forms
     *
     * @return false|mixed
     */
    public function get_landing_pages()
    {
        return $this->get_resources('landing_pages');
    }

    /**
     * Adds a subscriber to a form by email address
     *
     * @param integer $form_id Form ID.
     * @param string  $email   Email Address.
     *
     * @see https://developers.convertkit.com/v4.html#add-subscriber-to-form-by-email-address
     *
     * @return false|mixed
     */
    public function add_subscriber_to_form(int $form_id, string $email)
    {
        return $this->post(
            endpoint: sprintf('forms/%s/subscribers', $form_id),
            args: ['email_address' => $email]
        );
    }

    /**
     * Adds a subscriber to a form by subscriber ID
     *
     * @param integer $form_id       Form ID.
     * @param integer $subscriber_id Subscriber ID.
     *
     * @see https://developers.convertkit.com/v4.html#add-subscriber-to-form
     *
     * @since 2.0.0
     *
     * @return false|mixed
     */
    public function add_subscriber_to_form_by_subscriber_id(int $form_id, int $subscriber_id)
    {
        return $this->post(sprintf('forms/%s/subscribers/%s', $form_id, $subscriber_id));
    }

    /**
     * List subscribers for a form
     *
     * @param integer   $form_id          Form ID.
     * @param string    $subscriber_state Subscriber State (active|bounced|cancelled|complained|inactive).
     * @param \DateTime $created_after    Filter subscribers who have been created after this date.
     * @param \DateTime $created_before   Filter subscribers who have been created before this date.
     * @param \DateTime $added_after      Filter subscribers who have been added to the form after this date.
     * @param \DateTime $added_before     Filter subscribers who have been added to the form before this date.
     * @param string    $after_cursor     Return results after the given pagination cursor.
     * @param string    $before_cursor    Return results before the given pagination cursor.
     * @param integer   $per_page         Number of results to return.
     *
     * @see https://developers.convertkit.com/v4.html#list-subscribers-for-a-form
     *
     * @return false|mixed
     */
    public function get_form_subscriptions(
        int $form_id,
        string $subscriber_state = 'active',
        \DateTime $created_after = null,
        \DateTime $created_before = null,
        \DateTime $added_after = null,
        \DateTime $added_before = null,
        string $after_cursor = '',
        string $before_cursor = '',
        int $per_page = 100
    ) {
        // Build parameters.
        $options = [];

        if (!empty($subscriber_state)) {
            $options['status'] = $subscriber_state;
        }
        if (!is_null($created_after)) {
            $options['created_after'] = $created_after->format('Y-m-d');
        }
        if (!is_null($created_before)) {
            $options['created_before'] = $created_before->format('Y-m-d');
        }
        if (!is_null($added_after)) {
            $options['added_after'] = $added_after->format('Y-m-d');
        }
        if (!is_null($added_before)) {
            $options['added_before'] = $added_before->format('Y-m-d');
        }

        // Build pagination parameters.
        $options = $this->build_pagination_params(
            params: $options,
            after_cursor: $after_cursor,
            before_cursor: $before_cursor,
            per_page: $per_page
        );

        // Send request.
        return $this->get(
            endpoint: sprintf('forms/%s/subscribers', $form_id),
            args: $options
        );
    }

    /**
     * Gets sequences
     *
     * @param string  $after_cursor  Return results after the given pagination cursor.
     * @param string  $before_cursor Return results before the given pagination cursor.
     * @param integer $per_page      Number of results to return.
     *
     * @see https://developers.convertkit.com/v4.html#list-sequences
     *
     * @return false|mixed
     */
    public function get_sequences(string $after_cursor = '', string $before_cursor = '', int $per_page = 100)
    {
        return $this->get(
            endpoint: 'sequences',
            args: $this->build_pagination_params(
                after_cursor: $after_cursor,
                before_cursor: $before_cursor,
                per_page: $per_page
            )
        );
    }

    /**
     * Adds a subscriber to a sequence by email address
     *
     * @param integer $sequence_id Sequence ID.
     * @param string  $email       Email Address.
     *
     * @see https://developers.convertkit.com/v4.html#add-subscriber-to-sequence-by-email-address
     *
     * @return false|mixed
     */
    public function add_subscriber_to_sequence(int $sequence_id, string $email)
    {
        return $this->post(
            endpoint: sprintf('sequences/%s/subscribers', $sequence_id),
            args: ['email_address' => $email]
        );
    }

    /**
     * Adds a subscriber to a sequence by subscriber ID
     *
     * @param integer $sequence_id   Sequence ID.
     * @param integer $subscriber_id Subscriber ID.
     *
     * @see https://developers.convertkit.com/v4.html#add-subscriber-to-sequence
     *
     * @since 2.0.0
     *
     * @return false|mixed
     */
    public function add_subscriber_to_sequence_by_subscriber_id(int $sequence_id, int $subscriber_id)
    {
        return $this->post(sprintf('sequences/%s/subscribers/%s', $sequence_id, $subscriber_id));
    }

    /**
     * List subscribers for a sequence
     *
     * @param integer   $sequence_id      Sequence ID.
     * @param string    $subscriber_state Subscriber State (active|bounced|cancelled|complained|inactive).
     * @param \DateTime $created_after    Filter subscribers who have been created after this date.
     * @param \DateTime $created_before   Filter subscribers who have been created before this date.
     * @param \DateTime $added_after      Filter subscribers who have been added to the form after this date.
     * @param \DateTime $added_before     Filter subscribers who have been added to the form before this date.
     * @param string    $after_cursor     Return results after the given pagination cursor.
     * @param string    $before_cursor    Return results before the given pagination cursor.
     * @param integer   $per_page         Number of results to return.
     *
     * @see https://developers.convertkit.com/v4.html#list-subscribers-for-a-sequence
     *
     * @return false|mixed
     */
    public function get_sequence_subscriptions(
        int $sequence_id,
        string $subscriber_state = 'active',
        \DateTime $created_after = null,
        \DateTime $created_before = null,
        \DateTime $added_after = null,
        \DateTime $added_before = null,
        string $after_cursor = '',
        string $before_cursor = '',
        int $per_page = 100
    ) {
        // Build parameters.
        $options = [];

        if (!empty($subscriber_state)) {
            $options['status'] = $subscriber_state;
        }
        if (!is_null($created_after)) {
            $options['created_after'] = $created_after->format('Y-m-d');
        }
        if (!is_null($created_before)) {
            $options['created_before'] = $created_before->format('Y-m-d');
        }
        if (!is_null($added_after)) {
            $options['added_after'] = $added_after->format('Y-m-d');
        }
        if (!is_null($added_before)) {
            $options['added_before'] = $added_before->format('Y-m-d');
        }

        // Build pagination parameters.
        $options = $this->build_pagination_params(
            params: $options,
            after_cursor: $after_cursor,
            before_cursor: $before_cursor,
            per_page: $per_page
        );

        // Send request.
        return $this->get(
            endpoint: sprintf('sequences/%s/subscribers', $sequence_id),
            args: $options
        );
    }

    /**
     * Gets tags
     *
     * @param string  $after_cursor  Return results after the given pagination cursor.
     * @param string  $before_cursor Return results before the given pagination cursor.
     * @param integer $per_page      Number of results to return.
     *
     * @see https://developers.convertkit.com/v4.html#list-tags
     *
     * @return false|mixed
     */
    public function get_tags(string $after_cursor = '', string $before_cursor = '', int $per_page = 100)
    {
        return $this->get(
            endpoint: 'tags',
            args: $this->build_pagination_params(
                after_cursor: $after_cursor,
                before_cursor: $before_cursor,
                per_page: $per_page
            )
        );
    }

    /**
     * Creates a tag.
     *
     * @param string $tag Tag Name.
     *
     * @since 1.0.0
     *
     * @see https://developers.convertkit.com/v4.html#create-a-tag
     *
     * @return false|mixed
     */
    public function create_tag(string $tag)
    {
        return $this->post(
            endpoint: 'tags',
            args: [
                'name' => $tag,
            ]
        );
    }

    /**
     * Creates multiple tags.
     *
     * @param array<int,string> $tags Tag Names.
     * @param string $callback_url    URL to notify for large batch size when async processing complete.
     *
     * @since 1.1.0
     *
     * @see https://developers.convertkit.com/v4.html#bulk-create-tags
     *
     * @return false|mixed
     */
    public function create_tags(array $tags, string $callback_url = '')
    {
        // Build parameters.
        $options = [
            'tags' => [],
        ];
        foreach ($tags as $i => $tag) {
            $options['tags'] = [
                'name' => (string) $tag,
            ];
        }

        if (!empty($callback_url)) {
            $options['callback_url'] = $callback_url;
        }

        // Send request.
        return $this->post(
            endpoint: 'bulk/tags',
            args: $options
        );
    }

    /**
     * Tags a subscriber with the given existing Tag.
     *
     * @param integer               $tag_id     Tag ID.
     * @param string                $email      Email Address.
     *
     * @see https://developers.convertkit.com/v4.html#tag-a-subscriber-by-email-address
     *
     * @return false|mixed
     */
    public function tag_subscriber(int $tag_id, string $email) {
        return $this->post(
            endpoint: sprintf('tags/%s/subscribers', $tag_id),
            args: ['email_address' => $email]
        );
    }

    /**
     * Tags a subscriber by subscriber ID with the given existing Tag.
     *
     * @param integer               $tag_id     Tag ID.
     * @param string                $email      Email Address.
     *
     * @see https://developers.convertkit.com/v4.html#tag-a-subscriber
     *
     * @return false|mixed
     */
    public function tag_subscriber_by_subscriber_id(int $tag_id, int $subscriber_id)
    {
        return $this->post(sprintf('tags/%s/subscribers/%s', $tag_id, $subscriber_id));
    }

    /**
     * Removes a tag from a subscriber.
     *
     * @param integer $tag_id        Tag ID.
     * @param integer $subscriber_id Subscriber ID.
     *
     * @since 1.0.0
     *
     * @see https://developers.convertkit.com/v4.html#remove-tag-from-subscriber
     *
     * @return false|mixed
     */
    public function remove_tag_from_subscriber(int $tag_id, int $subscriber_id)
    {
        return $this->delete(sprintf('tags/%s/subscribers/%s', $tag_id, $subscriber_id));
    }

    /**
     * Removes a tag from a subscriber by email address.
     *
     * @param integer $tag_id Tag ID.
     * @param string  $email  Subscriber email address.
     *
     * @since 1.0.0
     *
     * @see https://developers.convertkit.com/v4.html#remove-tag-from-subscriber-by-email-address
     *
     * @return false|mixed
     */
    public function remove_tag_from_subscriber_by_email(int $tag_id, string $email)
    {
        return $this->delete(
            sprintf('tags/%s/subscribers', $tag_id),
            ['email_address' => $email]
        );
    }

    /**
     * List subscribers for a tag
     *
     * @param integer   $tag_id           Tag ID.
     * @param string    $subscriber_state Subscriber State (active|bounced|cancelled|complained|inactive).
     * @param \DateTime $created_after    Filter subscribers who have been created after this date.
     * @param \DateTime $created_before   Filter subscribers who have been created before this date.
     * @param \DateTime $tagged_after     Filter subscribers who have been tagged after this date.
     * @param \DateTime $tagged_before    Filter subscribers who have been tagged before this date.
     * @param string    $after_cursor     Return results after the given pagination cursor.
     * @param string    $before_cursor    Return results before the given pagination cursor.
     * @param integer   $per_page         Number of results to return.
     *
     * @see https://developers.convertkit.com/v4.html#list-subscribers-for-a-tag
     *
     * @return false|mixed
     */
    public function get_tag_subscriptions(
        int $tag_id,
        string $subscriber_state = 'active',
        \DateTime $created_after = null,
        \DateTime $created_before = null,
        \DateTime $tagged_after = null,
        \DateTime $tagged_before = null,
        string $after_cursor = '',
        string $before_cursor = '',
        int $per_page = 100
    ) {
        // Build parameters.
        $options = [];

        if (!empty($subscriber_state)) {
            $options['status'] = $subscriber_state;
        }
        if (!is_null($created_after)) {
            $options['created_after'] = $created_after->format('Y-m-d');
        }
        if (!is_null($created_before)) {
            $options['created_before'] = $created_before->format('Y-m-d');
        }
        if (!is_null($added_after)) {
            $options['added_after'] = $added_after->format('Y-m-d');
        }
        if (!is_null($added_before)) {
            $options['added_before'] = $added_before->format('Y-m-d');
        }

        // Build pagination parameters.
        $options = $this->build_pagination_params(
            params: $options,
            after_cursor: $after_cursor,
            before_cursor: $before_cursor,
            per_page: $per_page
        );

        // Send request.
        return $this->get(
            endpoint: sprintf('tags/%s/subscribers', $tag_id),
            args: $options
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
     * @throws \InvalidArgumentException If the resource argument is not a supported resource type.
     *
     * @return array<int|string, mixed|\stdClass> API response
     */
    public function get_resources(string $resource)
    {
        // Assign the resource to the request variable.
        $request = $resource;

        // Landing pages are included in the /forms endpoint.
        if ($resource === 'landing_pages') {
            $request = 'forms';
        }

        // Fetch resources.
        $resources = $this->get($request);

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

        return $_resource;
    }

    /**
     * Get subscribers.
     *
     * @param string    $subscriber_state Subscriber State (active|bounced|cancelled|complained|inactive).
     * @param string    $email_address    Search susbcribers by email address. This is an exact match search.
     * @param \DateTime $created_after    Filter subscribers who have been created after this date.
     * @param \DateTime $created_before   Filter subscribers who have been created before this date.
     * @param \DateTime $updated_after    Filter subscribers who have been updated after this date.
     * @param \DateTime $updated_before   Filter subscribers who have been updated before this date.
     * @param string    $sort_field       Sort Field (id|updated_at|cancelled_at).
     * @param string    $sort_order       Sort Order (asc|desc).
     * @param string    $after_cursor     Return results after the given pagination cursor.
     * @param string    $before_cursor    Return results before the given pagination cursor.
     * @param integer   $per_page         Number of results to return.
     *
     * @since 2.0.0
     *
     * @see https://developers.convertkit.com/v4.html#list-subscribers
     *
     * @return false|mixed
     */
    public function get_subscribers(
        string $subscriber_state = 'active',
        string $email_address = '',
        \DateTime $created_after = null,
        \DateTime $created_before = null,
        \DateTime $updated_after = null,
        \DateTime $updated_before = null,
        string $sort_field = 'id',
        string $sort_order = 'desc',
        string $after_cursor = '',
        string $before_cursor = '',
        int $per_page = 100
    ) {
        // Build parameters.
        $options = [];

        if (!empty($subscriber_state)) {
            $options['status'] = $subscriber_state;
        }
        if (!empty($email_address)) {
            $options['email_address'] = $email_address;
        }
        if (!is_null($created_after)) {
            $options['created_after'] = $created_after->format('Y-m-d');
        }
        if (!is_null($created_before)) {
            $options['created_before'] = $created_before->format('Y-m-d');
        }
        if (!is_null($updated_after)) {
            $options['updated_after'] = $updated_after->format('Y-m-d');
        }
        if (!is_null($updated_before)) {
            $options['updated_before'] = $updated_before->format('Y-m-d');
        }
        if (!empty($sort_field)) {
            $options['sort_field'] = $sort_field;
        }
        if (!empty($sort_order)) {
            $options['sort_order'] = $sort_order;
        }

        // Build pagination parameters.
        $options = $this->build_pagination_params(
            params: $options,
            after_cursor: $after_cursor,
            before_cursor: $before_cursor,
            per_page: $per_page
        );

        // Send request.
        return $this->get(
            endpoint: 'subscribers',
            args: $options
        );
    }

    /**
     * Create a subscriber.
     *
     * Behaves as an upsert. If a subscriber with the provided email address does not exist,
     * it creates one with the specified first name and state. If a subscriber with the provided
     * email address already exists, it updates the first name.
     *
     * @param string                $email_address    Email Address.
     * @param string                $first_name       First Name.
     * @param string                $subscriber_state Subscriber State (active|bounced|cancelled|complained|inactive).
     * @param array<string, string> $fields           Custom Fields.
     *
     * @since 2.0.0
     *
     * @see https://developers.convertkit.com/v4.html#create-a-subscriber
     *
     * @return mixed
     */
    public function create_subscriber(
        string $email_address,
        string $first_name = '',
        string $subscriber_state = '',
        array $fields = []
    ) {
        // Build parameters.
        $options = ['email_address' => $email_address];

        if (!empty($first_name)) {
            $options['first_name'] = $first_name;
        }
        if (!empty($subscriber_state)) {
            $options['state'] = $subscriber_state;
        }
        if (count($fields)) {
            $options['fields'] = $fields;
        }

        // Send request.
        return $this->post(
            endpoint: 'subscribers',
            args: $options
        );
    }

    /**
     * Create multiple subscribers.
     *
     * @param array<int,array<string,string>> $subscribers  Subscribers.
     * @param string                          $callback_url URL to notify for large batch size when async processing complete.
     *
     * @since 2.0.0
     *
     * @see https://developers.convertkit.com/v4.html#bulk-create-subscribers
     *
     * @return mixed
     */
    public function create_subscribers(array $subscribers, string $callback_url = '')
    {
        // Build parameters.
        $options = ['subscribers' => $subscribers];

        if (!empty($callback_url)) {
            $options['callback_url'] = $callback_url;
        }

        // Send request.
        return $this->post(
            endpoint: 'bulk/subscribers',
            args: $options
        );
    }

    /**
     * Get the ConvertKit subscriber ID associated with email address if it exists.
     * Return false if subscriber not found.
     *
     * @param string $email_address Email Address.
     *
     * @throws \InvalidArgumentException If the email address is not a valid email format.
     *
     * @see https://developers.convertkit.com/v4.html#get-a-subscriber
     *
     * @return false|integer
     */
    public function get_subscriber_id(string $email_address)
    {
        $subscribers = $this->get(
            'subscribers',
            ['email_address' => $email_address]
        );

        if (!count($subscribers->subscribers)) {
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
     * @see https://developers.convertkit.com/v4.html#get-a-subscriber
     *
     * @return false|integer
     */
    public function get_subscriber(int $subscriber_id)
    {
        return $this->get(sprintf('subscribers/%s', $subscriber_id));
    }

    /**
     * Updates the information for a single subscriber.
     *
     * @param integer               $subscriber_id Existing Subscriber ID.
     * @param string                $first_name    New First Name.
     * @param string                $email_address New Email Address.
     * @param array<string, string> $fields        Updated Custom Fields.
     *
     * @see https://developers.convertkit.com/v4.html#update-a-subscriber
     *
     * @return false|mixed
     */
    public function update_subscriber(
        int $subscriber_id,
        string $first_name = '',
        string $email_address = '',
        array $fields = []
    ) {
        // Build parameters.
        $options = [];

        if (!empty($first_name)) {
            $options['first_name'] = $first_name;
        }
        if (!empty($email_address)) {
            $options['email_address'] = $email_address;
        }
        if (!empty($fields)) {
            $options['fields'] = $fields;
        }

        // Send request.
        return $this->put(
            sprintf('subscribers/%s', $subscriber_id),
            $options
        );
    }

    /**
     * Unsubscribe an email address.
     *
     * @param string $email Email Address.
     *
     * @see https://developers.convertkit.com/v4.html#unsubscribe-subscriber
     *
     * @return false|object
     */
    public function unsubscribe(string $email)
    {
        return $this->post(
            sprintf(
                'subscribers/%s/unsubscribe',
                $this->get_subscriber_id($email)
            )
        );
    }

    /**
     * Unsubscribe the given subscriber ID.
     *
     * @param integer $subscriber_id Subscriber ID.
     *
     * @see https://developers.convertkit.com/v4.html#unsubscribe-subscriber
     *
     * @return false|object
     */
    public function unsubscribe_by_id(int $subscriber_id)
    {
        return $this->post(sprintf('subscribers/%s/unsubscribe', $subscriber_id));
    }

    /**
     * Get a list of the tags for a subscriber.
     *
     * @param integer $subscriber_id Subscriber ID.
     * @param string  $after_cursor  Return results after the given pagination cursor.
     * @param string  $before_cursor Return results before the given pagination cursor.
     * @param integer $per_page      Number of results to return.
     *
     * @see https://developers.convertkit.com/v4.html#list-tags-for-a-subscriber
     *
     * @return false|array<int,\stdClass>
     */
    public function get_subscriber_tags(
        int $subscriber_id,
        string $after_cursor = '',
        string $before_cursor = '',
        int $per_page = 100
    ) {
        return $this->get(
            endpoint: sprintf('subscribers/%s/tags', $subscriber_id),
            args: $this->build_pagination_params(
                after_cursor: $after_cursor,
                before_cursor: $before_cursor,
                per_page: $per_page
            )
        );
    }

    /**
     * Gets a list of broadcasts.
     *
     * @see https://developers.convertkit.com/#list-broadcasts
     *
     * @return false|array<int,\stdClass>
     */
    public function get_broadcasts()
    {
        return $this->get('broadcasts');
    }

    /**
     * Creates a broadcast.
     *
     * @param string    $subject               The broadcast email's subject.
     * @param string    $content               The broadcast's email HTML content.
     * @param string    $description           An internal description of this broadcast.
     * @param boolean   $public                Specifies whether or not this is a public post.
     * @param \DateTime $published_at          Specifies the time that this post was published (applicable
     *                                         only to public posts).
     * @param \DateTime $send_at               Time that this broadcast should be sent; leave blank to create
     *                                         a draft broadcast. If set to a future time, this is the time that
     *                                         the broadcast will be scheduled to send.
     * @param string    $email_address         Sending email address; leave blank to use your account's
     *                                         default sending email address.
     * @param string    $email_layout_template Name of the email template to use; leave blank to use your
     *                                         account's default email template.
     * @param string    $thumbnail_alt         Specify the ALT attribute of the public thumbnail image
     *                                         (applicable only to public posts).
     * @param string    $thumbnail_url         Specify the URL of the thumbnail image to accompany the broadcast
     *                                         post (applicable only to public posts).
     *
     * @see https://developers.convertkit.com/#create-a-broadcast
     *
     * @return false|object
     */
    public function create_broadcast(
        string $subject = '',
        string $content = '',
        string $description = '',
        bool $public = false,
        \DateTime $published_at = null,
        \DateTime $send_at = null,
        string $email_address = '',
        string $email_layout_template = '',
        string $thumbnail_alt = '',
        string $thumbnail_url = ''
    ) {
        $options = [
            'content'               => $content,
            'description'           => $description,
            'email_address'         => $email_address,
            'email_layout_template' => $email_layout_template,
            'public'                => $public,
            'published_at'          => (!is_null($published_at) ? $published_at->format('Y-m-d H:i:s') : ''),
            'send_at'               => (!is_null($send_at) ? $send_at->format('Y-m-d H:i:s') : ''),
            'subject'               => $subject,
            'thumbnail_alt'         => $thumbnail_alt,
            'thumbnail_url'         => $thumbnail_url,
        ];

        // Iterate through options, removing blank entries.
        foreach ($options as $key => $value) {
            if (is_string($value) && strlen($value) === 0) {
                unset($options[$key]);
            }
        }

        // If the post isn't public, remove some options that don't apply.
        if (!$public) {
            unset($options['published_at'], $options['thumbnail_alt'], $options['thumbnail_url']);
        }

        // Send request.
        return $this->post('broadcasts', $options);
    }

    /**
     * Retrieve a specific broadcast.
     *
     * @param integer $id Broadcast ID.
     *
     * @see https://developers.convertkit.com/#retrieve-a-specific-broadcast
     *
     * @return false|object
     */
    public function get_broadcast(int $id)
    {
        return $this->get(sprintf('broadcasts/%s', $id));
    }

    /**
     * Get the statistics (recipient count, open rate, click rate, unsubscribe count,
     * total clicks, status, and send progress) for a specific broadcast.
     *
     * @param integer $id Broadcast ID.
     *
     * @see https://developers.convertkit.com/#retrieve-a-specific-broadcast
     *
     * @return false|object
     */
    public function get_broadcast_stats(int $id)
    {
        return $this->get(sprintf('broadcasts/%s/stats', $id));
    }

    /**
     * Updates a broadcast.
     *
     * @param integer   $id                    Broadcast ID.
     * @param string    $subject               The broadcast email's subject.
     * @param string    $content               The broadcast's email HTML content.
     * @param string    $description           An internal description of this broadcast.
     * @param boolean   $public                Specifies whether or not this is a public post.
     * @param \DateTime $published_at          Specifies the time that this post was published (applicable
     *                                         only to public posts).
     * @param \DateTime $send_at               Time that this broadcast should be sent; leave blank to create
     *                                         a draft broadcast. If set to a future time, this is the time that
     *                                         the broadcast will be scheduled to send.
     * @param string    $email_address         Sending email address; leave blank to use your account's
     *                                         default sending email address.
     * @param string    $email_layout_template Name of the email template to use; leave blank to use your
     *                                         account's default email template.
     * @param string    $thumbnail_alt         Specify the ALT attribute of the public thumbnail image
     *                                         (applicable only to public posts).
     * @param string    $thumbnail_url         Specify the URL of the thumbnail image to accompany the broadcast
     *                                         post (applicable only to public posts).
     *
     * @see https://developers.convertkit.com/#create-a-broadcast
     *
     * @return false|object
     */
    public function update_broadcast(
        int $id,
        string $subject = '',
        string $content = '',
        string $description = '',
        bool $public = false,
        \DateTime $published_at = null,
        \DateTime $send_at = null,
        string $email_address = '',
        string $email_layout_template = '',
        string $thumbnail_alt = '',
        string $thumbnail_url = ''
    ) {
        $options = [
            'content'               => $content,
            'description'           => $description,
            'email_address'         => $email_address,
            'email_layout_template' => $email_layout_template,
            'public'                => $public,
            'published_at'          => (!is_null($published_at) ? $published_at->format('Y-m-d H:i:s') : ''),
            'send_at'               => (!is_null($send_at) ? $send_at->format('Y-m-d H:i:s') : ''),
            'subject'               => $subject,
            'thumbnail_alt'         => $thumbnail_alt,
            'thumbnail_url'         => $thumbnail_url,
        ];

        // Iterate through options, removing blank entries.
        foreach ($options as $key => $value) {
            if (is_string($value) && strlen($value) === 0) {
                unset($options[$key]);
            }
        }

        // If the post isn't public, remove some options that don't apply.
        if (!$public) {
            unset($options['published_at'], $options['thumbnail_alt'], $options['thumbnail_url']);
        }

        // Send request.
        return $this->put(
            sprintf('broadcasts/%s', $id),
            $options
        );
    }

    /**
     * Deletes an existing broadcast.
     *
     * @param integer $id Broadcast ID.
     *
     * @since 1.0.0
     *
     * @see https://developers.convertkit.com/#destroy-webhook
     *
     * @return false|object
     */
    public function destroy_broadcast(int $id)
    {
        return $this->delete(sprintf('broadcasts/%s', $id));
    }

    /**
     * Creates a webhook that will be called based on the chosen event types.
     *
     * @param string $url       URL to receive event.
     * @param string $event     Event to subscribe to.
     * @param string $parameter Optional parameter depending on the event.
     *
     * @since 1.0.0
     *
     * @see https://developers.convertkit.com/#create-a-webhook
     *
     * @throws \InvalidArgumentException If the event is not supported.
     *
     * @return false|object
     */
    public function create_webhook(string $url, string $event, string $parameter = '')
    {
        // Depending on the event, build the required event array structure.
        switch ($event) {
            case 'subscriber.subscriber_activate':
            case 'subscriber.subscriber_unsubscribe':
            case 'purchase.purchase_create':
                $eventData = ['name' => $event];
                break;

            case 'subscriber.form_subscribe':
                $eventData = [
                    'name'    => $event,
                    'form_id' => $parameter,
                ];
                break;

            case 'subscriber.course_subscribe':
            case 'subscriber.course_complete':
                $eventData = [
                    'name'      => $event,
                    'course_id' => $parameter,
                ];
                break;

            case 'subscriber.link_click':
                $eventData = [
                    'name'            => $event,
                    'initiator_value' => $parameter,
                ];
                break;

            case 'subscriber.product_purchase':
                $eventData = [
                    'name'       => $event,
                    'product_id' => $parameter,
                ];
                break;

            case 'subscriber.tag_add':
            case 'subscriber.tag_remove':
                $eventData = [
                    'name'   => $event,
                    'tag_id' => $parameter,
                ];
                break;

            default:
                throw new \InvalidArgumentException(sprintf('The event %s is not supported', $event));
        }//end switch

        // Send request.
        return $this->post(
            'automations/hooks',
            [
                'target_url' => $url,
                'event'      => $eventData,
            ]
        );
    }

    /**
     * Deletes an existing webhook.
     *
     * @param integer $rule_id Rule ID.
     *
     * @since 1.0.0
     *
     * @see https://developers.convertkit.com/#destroy-webhook
     *
     * @return false|object
     */
    public function destroy_webhook(int $rule_id)
    {
        return $this->delete(sprintf('automations/hooks/%s', $rule_id));
    }

    /**
     * List custom fields.
     *
     * @since 1.0.0
     *
     * @see https://developers.convertkit.com/#list-fields
     *
     * @return false|object
     */
    public function get_custom_fields()
    {
        return $this->get('custom_fields');
    }

    /**
     * Creates a custom field.
     *
     * @param string $label Custom Field label.
     *
     * @since 1.0.0
     *
     * @see https://developers.convertkit.com/#create-field
     *
     * @return false|object
     */
    public function create_custom_field(string $label)
    {
        return $this->post(
            'custom_fields',
            [
                'label' => [$label],
            ]
        );
    }

    /**
     * Creates multiple custom fields.
     *
     * @param array<string> $labels Custom Fields labels.
     *
     * @since 1.0.0
     *
     * @see https://developers.convertkit.com/#create-field
     *
     * @return false|object
     */
    public function create_custom_fields(array $labels)
    {
        return $this->post(
            'custom_fields',
            ['label' => $labels]
        );
    }

    /**
     * Updates an existing custom field.
     *
     * @param integer $id    Custom Field ID.
     * @param string  $label Updated Custom Field label.
     *
     * @since 1.0.0
     *
     * @see https://developers.convertkit.com/#update-field
     *
     * @return false|object
     */
    public function update_custom_field(int $id, string $label)
    {
        return $this->put(
            sprintf('custom_fields/%s', $id),
            ['label' => $label]
        );
    }

    /**
     * Deletes an existing custom field.
     *
     * @param integer $id Custom Field ID.
     *
     * @since 1.0.0
     *
     * @see https://developers.convertkit.com/#destroy-field
     *
     * @return false|object
     */
    public function delete_custom_field(int $id)
    {
        return $this->delete(sprintf('custom_fields/%s', $id));
    }

    /**
     * List purchases.
     *
     * @param array<string, string> $options Request options.
     *
     * @see https://developers.convertkit.com/#list-purchases
     *
     * @return false|object
     */
    public function list_purchases(array $options)
    {
        return $this->get('purchases', $options);
    }

    /**
     * Retuns a specific purchase.
     *
     * @param integer $purchase_id Purchase ID.
     *
     * @see https://developers.convertkit.com/#retrieve-a-specific-purchase
     *
     * @return false|object
     */
    public function get_purchase(int $purchase_id)
    {
        return $this->get(sprintf('purchases/%s', $purchase_id));
    }

    /**
     * Creates a purchase.
     *
     * @param array<string, string> $options Purchase data.
     *
     * @see https://developers.convertkit.com/#create-a-purchase
     *
     * @return false|object
     */
    public function create_purchase(array $options)
    {
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
     * Adds pagination parameters to the given array of existing API parameters.
     *
     * @param array<string, string|integer> $params        API parameters.
     * @param string                        $after_cursor  Return results after the given pagination cursor.
     * @param string                        $before_cursor Return results before the given pagination cursor.
     * @param integer                       $per_page      Number of results to return.
     *
     * @since 2.0.0
     *
     * @return array<string, string|integer>
     */
    private function build_pagination_params(
        array $params = [],
        string $after_cursor = '',
        string $before_cursor = '',
        int $per_page = 100
    ) {
        if (!empty($after_cursor)) {
            $params['after'] = $after_cursor;
        }
        if (!empty($before_cursor)) {
            $params['before'] = $before_cursor;
        }
        if (!empty($per_page)) {
            $params['per_page'] = $per_page;
        }

        return $params;
    }

    /**
     * Performs a GET request to the API.
     *
     * @param string                                                     $endpoint API Endpoint.
     * @param array<string, int|string|array<string, int|string>|string> $args     Request arguments.
     *
     * @return false|mixed
     */
    public function get(string $endpoint, array $args = [])
    {
        return $this->make_request($endpoint, 'GET', $args);
    }

    /**
     * Performs a POST request to the API.
     *
     * @param string                                                                                $endpoint API Endpoint.
     * @param array<string, bool|integer|string|array<int|string, int|string|array<string|string>>> $args     Request arguments.
     *
     * @return false|mixed
     */
    public function post(string $endpoint, array $args = [])
    {
        return $this->make_request($endpoint, 'POST', $args);
    }

    /**
     * Performs a PUT request to the API.
     *
     * @param string                                                              $endpoint API Endpoint.
     * @param array<string, bool|integer|string|array<string, int|string>|string> $args     Request arguments.
     *
     * @return false|mixed
     */
    public function put(string $endpoint, array $args = [])
    {
        return $this->make_request($endpoint, 'PUT', $args);
    }

    /**
     * Performs a DELETE request to the API.
     *
     * @param string                                                     $endpoint API Endpoint.
     * @param array<string, int|string|array<string, int|string>|string> $args     Request arguments.
     *
     * @return false|mixed
     */
    public function delete(string $endpoint, array $args = [])
    {
        return $this->make_request($endpoint, 'DELETE', $args);
    }

    /**
     * Performs an API request using Guzzle.
     *
     * @param string                                                                                $endpoint API Endpoint.
     * @param string                                                                                $method   Request method.
     * @param array<string, bool|integer|string|array<int|string, int|string|array<string|string>>> $args     Request arguments.
     *
     * @throws \Exception If JSON encoding arguments failed.
     *
     * @return false|mixed
     */
    public function make_request(string $endpoint, string $method, array $args = [])
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
                    uri: $url
                );
                break;

            default:
                $request = new Request(
                    method: $method,
                    uri:    $url,
                    body:   (string) json_encode($args),
                );
                break;
        }

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
}
