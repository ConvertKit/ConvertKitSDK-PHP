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
    public const VERSION = '1.1.0';

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
     * Constructor for ConvertKitAPI instance
     *
     * @param string  $api_key              ConvertKit API Key.
     * @param string  $api_secret           ConvertKit API Secret.
     * @param boolean $debug                Log requests to debugger.
     * @param string  $debugLogFileLocation Path and filename of debug file to write to.
     */
    public function __construct(string $api_key, string $api_secret, bool $debug = false, string $debugLogFileLocation = '')
    {
        $this->api_key    = $api_key;
        $this->api_secret = $api_secret;
        $this->debug      = $debug;

        // Set the Guzzle client.
        $this->client = new Client(
            [
                'headers' => [
                    'User-Agent' => 'ConvertKitPHPSDK/' . self::VERSION . ';PHP/' . phpversion(),
                ],
            ]
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

        // Mask the API Key and Secret.
        $message = str_replace(
            $this->api_key,
            str_repeat('*', (strlen($this->api_key) - 4)) . substr($this->api_key, - 4),
            $message
        );
        $message = str_replace(
            $this->api_secret,
            str_repeat('*', (strlen($this->api_secret) - 4)) . substr($this->api_secret, - 4),
            $message
        );

        // Add to log.
        $this->debug_logger->info($message);
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
     * Adds a subscriber to a form.
     *
     * @param integer               $form_id Form ID.
     * @param array<string, string> $options Array of user data (email, name).
     *
     * @deprecated 1.0.0 Use add_subscriber_to_form($form_id, $email, $first_name, $fields, $tag_ids).
     *
     * @throws \InvalidArgumentException If the provided arguments are not of the expected type.
     *
     * @see https://developers.convertkit.com/#add-subscriber-to-a-form
     *
     * @return false|object
     */
    public function form_subscribe(int $form_id, array $options)
    {
        // This function is deprecated in 1.0, as we prefer functions with structured arguments.
        trigger_error(
            'form_subscribe() is deprecated in 1.0.
            Use add_subscriber_to_form($form_id, $email, $first_name, $fields, $tag_ids) instead.',
            E_USER_NOTICE
        );

        // Add API Key to array of options.
        $options['api_key'] = $this->api_key;

        return $this->post(
            sprintf('forms/%s/subscribe', $form_id),
            $options
        );
    }

    /**
     * Adds a subscriber to a form by email address
     *
     * @param integer               $form_id    Form ID.
     * @param string                $email      Email Address.
     * @param string                $first_name First Name.
     * @param array<string, string> $fields     Custom Fields.
     * @param array<string, int>    $tag_ids    Tag ID(s) to subscribe to.
     *
     * @see https://developers.convertkit.com/#add-subscriber-to-a-form
     *
     * @return false|mixed
     */
    public function add_subscriber_to_form(
        int $form_id,
        string $email,
        string $first_name = '',
        array $fields = [],
        array $tag_ids = []
    ) {
        // Build parameters.
        $options = [
            'api_key' => $this->api_key,
            'email'   => $email,
        ];

        if (!empty($first_name)) {
            $options['first_name'] = $first_name;
        }
        if (!empty($fields)) {
            $options['fields'] = $fields;
        }
        if (!empty($tag_ids)) {
            $options['tags'] = $tag_ids;
        }

        // Send request.
        return $this->post(
            sprintf('forms/%s/subscribe', $form_id),
            $options
        );
    }

    /**
     * List subscriptions to a form
     *
     * @param integer $form_id          Form ID.
     * @param string  $sort_order       Sort Order (asc|desc).
     * @param string  $subscriber_state Subscriber State (active,cancelled).
     * @param integer $page             Page.
     *
     * @see https://developers.convertkit.com/#list-subscriptions-to-a-form
     *
     * @return false|mixed
     */
    public function get_form_subscriptions(
        int $form_id,
        string $sort_order = 'asc',
        string $subscriber_state = 'active',
        int $page = 1
    ) {
        return $this->get(
            sprintf('forms/%s/subscriptions', $form_id),
            [
                'api_secret'       => $this->api_secret,
                'sort_order'       => $sort_order,
                'subscriber_state' => $subscriber_state,
                'page'             => $page,
            ]
        );
    }

    /**
     * Gets all sequences
     *
     * @see https://developers.convertkit.com/#list-sequences
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
     * Adds a subscriber to a sequence by email address
     *
     * @param integer               $sequence_id Sequence ID.
     * @param string                $email       Email Address.
     * @param string                $first_name  First Name.
     * @param array<string, string> $fields      Custom Fields.
     * @param array<string, int>    $tag_ids     Tag ID(s) to subscribe to.
     *
     * @see https://developers.convertkit.com/#add-subscriber-to-a-sequence
     *
     * @return false|mixed
     */
    public function add_subscriber_to_sequence(
        int $sequence_id,
        string $email,
        string $first_name = '',
        array $fields = [],
        array $tag_ids = []
    ) {
        // Build parameters.
        $options = [
            'api_key' => $this->api_key,
            'email'   => $email,
        ];

        if (!empty($first_name)) {
            $options['first_name'] = $first_name;
        }
        if (!empty($fields)) {
            $options['fields'] = $fields;
        }
        if (!empty($tag_ids)) {
            $options['tags'] = $tag_ids;
        }

        // Send request.
        return $this->post(
            sprintf('sequences/%s/subscribe', $sequence_id),
            $options
        );
    }

    /**
     * Gets subscribers to a sequence
     *
     * @param integer $sequence_id      Sequence ID.
     * @param string  $sort_order       Sort Order (asc|desc).
     * @param string  $subscriber_state Subscriber State (active,cancelled).
     * @param integer $page             Page.
     *
     * @see https://developers.convertkit.com/#list-subscriptions-to-a-sequence
     *
     * @return false|mixed
     */
    public function get_sequence_subscriptions(
        int $sequence_id,
        string $sort_order = 'asc',
        string $subscriber_state = 'active',
        int $page = 1
    ) {
        return $this->get(
            sprintf('sequences/%s/subscriptions', $sequence_id),
            [
                'api_secret'       => $this->api_secret,
                'sort_order'       => $sort_order,
                'subscriber_state' => $subscriber_state,
                'page'             => $page,
            ]
        );
    }

    /**
     * Gets all tags.
     *
     * @since 1.0.0
     *
     * @see https://developers.convertkit.com/#list-tags
     *
     * @return false|mixed
     */
    public function get_tags()
    {
        return $this->get_resources('tags');
    }

    /**
     * Creates a tag.
     *
     * @param string $tag Tag Name.
     *
     * @since 1.0.0
     *
     * @see https://developers.convertkit.com/#create-a-tag
     *
     * @return false|mixed
     */
    public function create_tag(string $tag)
    {
        return $this->post(
            'tags',
            [
                'api_key' => $this->api_key,
                'tag'     => ['name' => $tag],
            ]
        );
    }

    /**
     * Creates multiple tags.
     *
     * @param array<int,string> $tags Tag Names.
     *
     * @since 1.1.0
     *
     * @see https://developers.convertkit.com/#create-a-tag
     *
     * @return false|mixed
     */
    public function create_tags(array $tags)
    {
        // Build API compatible array of tags.
        $apiTags = [];
        foreach ($tags as $i => $tag) {
            $apiTags[] = [
                'name' => (string) $tag,
            ];
        }

        return $this->post(
            'tags',
            [
                'api_key' => $this->api_key,
                'tag'     => $apiTags,
            ]
        );
    }

    /**
     * Tags a subscriber with the given existing Tag.
     *
     * @param integer               $tag_id     Tag ID.
     * @param string                $email      Email Address.
     * @param string                $first_name First Name.
     * @param array<string, string> $fields     Custom Fields.
     *
     * @see https://developers.convertkit.com/#tag-a-subscriber
     *
     * @return false|mixed
     */
    public function tag_subscriber(
        int $tag_id,
        string $email,
        string $first_name = '',
        array $fields = []
    ) {
        // Build parameters.
        $options = [
            'api_secret' => $this->api_secret,
            'email'      => $email,
        ];

        if (!empty($first_name)) {
            $options['first_name'] = $first_name;
        }
        if (!empty($fields)) {
            $options['fields'] = $fields;
        }

        // Send request.
        return $this->post(
            sprintf('tags/%s/subscribe', $tag_id),
            $options
        );
    }

    /**
     * Adds a tag to a subscriber.
     *
     * @param integer              $tag     Tag ID.
     * @param array<string, mixed> $options Array of user data.
     *
     * @deprecated 1.0.0 Use tag_subscriber($tag_id, $email, $first_name, $fields).
     *
     * @see https://developers.convertkit.com/#tag-a-subscriber
     *
     * @return false|object
     */
    public function add_tag(int $tag, array $options)
    {
        // This function is deprecated in 1.0, as we prefer functions with structured arguments.
        trigger_error(
            'add_tag() is deprecated in 1.0.  Use tag_subscribe($tag_id, $email, $first_name, $fields) instead.',
            E_USER_NOTICE
        );

        // Add API Key to array of options.
        $options['api_key'] = $this->api_key;

        return $this->post(
            sprintf('tags/%s/subscribe', $tag),
            $options
        );
    }

    /**
     * Removes a tag from a subscriber.
     *
     * @param integer $tag_id        Tag ID.
     * @param integer $subscriber_id Subscriber ID.
     *
     * @since 1.0.0
     *
     * @see https://developers.convertkit.com/#remove-tag-from-a-subscriber
     *
     * @return false|mixed
     */
    public function remove_tag_from_subscriber(int $tag_id, int $subscriber_id)
    {
        return $this->delete(
            sprintf('subscribers/%s/tags/%s', $subscriber_id, $tag_id),
            [
                'api_secret' => $this->api_secret,
            ]
        );
    }

    /**
     * Removes a tag from a subscriber by email address.
     *
     * @param integer $tag_id Tag ID.
     * @param string  $email  Subscriber email address.
     *
     * @since 1.0.0
     *
     * @see https://developers.convertkit.com/#remove-tag-from-a-subscriber-by-email
     *
     * @return false|mixed
     */
    public function remove_tag_from_subscriber_by_email(int $tag_id, string $email)
    {
        return $this->post(
            sprintf('tags/%s/unsubscribe', $tag_id),
            [
                'api_secret' => $this->api_secret,
                'email'      => $email,
            ]
        );
    }

    /**
     * List subscriptions to a tag
     *
     * @param integer $tag_id           Tag ID.
     * @param string  $sort_order       Sort Order (asc|desc).
     * @param string  $subscriber_state Subscriber State (active,cancelled).
     * @param integer $page             Page.
     *
     * @see https://developers.convertkit.com/#list-subscriptions-to-a-tag
     *
     * @return false|mixed
     */
    public function get_tag_subscriptions(
        int $tag_id,
        string $sort_order = 'asc',
        string $subscriber_state = 'active',
        int $page = 1
    ) {
        return $this->get(
            sprintf('tags/%s/subscriptions', $tag_id),
            [
                'api_secret'       => $this->api_secret,
                'sort_order'       => $sort_order,
                'subscriber_state' => $subscriber_state,
                'page'             => $page,
            ]
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

        return $_resource;
    }

    /**
     * Get the ConvertKit subscriber ID associated with email address if it exists.
     * Return false if subscriber not found.
     *
     * @param string $email_address Email Address.
     *
     * @throws \InvalidArgumentException If the email address is not a valid email format.
     *
     * @see https://developers.convertkit.com/#list-subscribers
     *
     * @return false|integer
     */
    public function get_subscriber_id(string $email_address)
    {
        if (!filter_var($email_address, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Email address is not a valid email format.');
        }

        $subscribers = $this->get(
            'subscribers',
            [
                'api_secret'    => $this->api_secret,
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
     * @see https://developers.convertkit.com/#view-a-single-subscriber
     *
     * @return false|integer
     */
    public function get_subscriber(int $subscriber_id)
    {
        return $this->get(
            sprintf('subscribers/%s', $subscriber_id),
            [
                'api_secret' => $this->api_secret,
            ]
        );
    }

    /**
     * Updates the information for a single subscriber.
     *
     * @param integer               $subscriber_id Existing Subscriber ID.
     * @param string                $first_name    New First Name.
     * @param string                $email_address New Email Address.
     * @param array<string, string> $fields        Updated Custom Fields.
     *
     * @see https://developers.convertkit.com/#update-subscriber
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
        $options = [
            'api_secret' => $this->api_secret,
        ];

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
     * Unsubscribe an email address from all forms and sequences.
     *
     * @param string $email Email Address.
     *
     * @see https://developers.convertkit.com/#unsubscribe-subscriber
     *
     * @return false|object
     */
    public function unsubscribe(string $email)
    {
        return $this->put(
            'unsubscribe',
            [
                'api_secret' => $this->api_secret,
                'email'      => $email,
            ]
        );
    }

    /**
     * Remove subscription from a form
     *
     * @param array<string, string> $options Array of user data (email).
     *
     * @see https://developers.convertkit.com/#unsubscribe-subscriber
     *
     * @return false|object
     */
    public function form_unsubscribe(array $options)
    {
        // This function is deprecated in 1.0, as we prefer functions with structured arguments.
        // This function name is also misleading, as it doesn't just unsubscribe the email
        // address from forms.
        trigger_error(
            'form_unsubscribe() is deprecated in 1.0.  Use unsubscribe($email) instead.',
            E_USER_NOTICE
        );

        // Add API Secret to array of options.
        $options['api_secret'] = $this->api_secret;

        return $this->put('unsubscribe', $options);
    }

    /**
     * Get a list of the tags for a subscriber.
     *
     * @param integer $subscriber_id Subscriber ID.
     *
     * @see https://developers.convertkit.com/#list-tags-for-a-subscriber
     *
     * @return false|array<int,\stdClass>
     */
    public function get_subscriber_tags(int $subscriber_id)
    {
        return $this->get(
            sprintf('subscribers/%s/tags', $subscriber_id),
            [
                'api_key' => $this->api_key,
            ]
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
        return $this->get(
            'broadcasts',
            [
                'api_secret' => $this->api_secret,
            ]
        );
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
            'api_secret'            => $this->api_secret,
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
        return $this->get(
            sprintf('broadcasts/%s', $id),
            [
                'api_secret' => $this->api_secret,
            ]
        );
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
        return $this->get(
            sprintf('broadcasts/%s/stats', $id),
            [
                'api_secret' => $this->api_secret,
            ]
        );
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
            'api_secret'            => $this->api_secret,
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
        return $this->delete(
            sprintf('broadcasts/%s', $id),
            [
                'api_secret' => $this->api_secret,
            ]
        );
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
                'api_secret' => $this->api_secret,
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
        return $this->delete(
            sprintf('automations/hooks/%s', $rule_id),
            [
                'api_secret' => $this->api_secret,
            ]
        );
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
        return $this->get(
            'custom_fields',
            [
                'api_key' => $this->api_key,
            ]
        );
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
                'api_secret' => $this->api_secret,
                'label'      => [$label],
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
            [
                'api_secret' => $this->api_secret,
                'label'      => $labels,
            ]
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
            [
                'api_secret' => $this->api_secret,
                'label'      => $label,
            ]
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
        return $this->delete(
            sprintf('custom_fields/%s', $id),
            [
                'api_secret' => $this->api_secret,
            ]
        );
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
        // Add API Secret to array of options.
        $options['api_secret'] = $this->api_secret;

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
        return $this->get(
            sprintf('purchases/%s', $purchase_id),
            [
                'api_secret' => $this->api_secret,
            ]
        );
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
     * Performs a GET request to the API.
     *
     * @param string                                                     $endpoint API Endpoint.
     * @param array<string, int|string|array<string, int|string>|string> $args     Request arguments.
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
     * @param string                                                                                $endpoint API Endpoint.
     * @param array<string, bool|integer|string|array<int|string, int|string|array<string|string>>> $args     Request arguments.
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
     * @param string                                                              $endpoint API Endpoint.
     * @param array<string, bool|integer|string|array<string, int|string>|string> $args     Request arguments.
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
     * @param string                                                     $endpoint API Endpoint.
     * @param array<string, int|string|array<string, int|string>|string> $args     Request arguments.
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
