<?php

namespace ConvertKit_API;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;


class ConvertKit_API {

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
     * @var array
     */
    protected $resources = array();

    /**
     * Additional markup
     *
     * @var array
     */
    protected $markup = array();

    /**
     * Debug
     *
     * @var boolean
     */
    protected $debug;

    /**
     * Debug
     *
     * @var boolean
     */
    protected $debug_logger;

    /**
     * Guzzle Http Client
     *
     * @var object
     */
    protected $client;

    /**
     * Constructor for ConvertKitAPI instance
     *
     * @param string $api_key ConvertKit API Key.
     * @param string $api_secret ConvertKit API Secret.
     * @param boolean $debug if log debug info.
     */
    public function __construct( $api_key, $api_secret, $debug = false ) {

        $this->api_key = $api_key;
        $this->api_secret = $api_secret;
        $this->debug = $debug;
        $this->client = new Client();

        if( $debug ) {
            $this->debug_logger = new Logger('ck-debug');
            $this->debug_logger->pushHandler(new StreamHandler(__DIR__.'/logs/debug.log', Logger::DEBUG));
        }
    }

    private function create_log($message) {
        if($this->debug) {
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
        $request = $this->api_version . '/account';

        $options = array(
            'api_secret' => $this->api_secret,
        );

        $this->create_log(sprintf("GET account: %s, %s", $request, json_encode($options)));

        return $this->make_request( $request, 'GET', $options );
    }

    /**
     * Gets all sequences
     *
     * @return false|mixed
     */
    public function get_sequences()
    {
        $request = $this->api_version . '/sequences';

        $options = array(
            'api_key' => $this->api_key,
        );

        $this->create_log(sprintf("GET sequences: %s, %s", $request, json_encode($options)));

        return $this->make_request( $request, 'GET', $options );
    }

    /**
     * Gets subscribers to a sequence
     *
     * @param $sequence_id
     * @param string $sort_order
     *
     * @return false|mixed
     */
    public function get_sequence_subscriptions($sequence_id, $sort_order = 'asc')
    {
        $request = $this->api_version . sprintf('/sequences/%s/subscriptions', $sequence_id);

        $options = array(
            'api_secret' => $this->api_secret,
            'sort_order' => $sort_order
        );

        $this->create_log(sprintf("GET sequence subscriptions: %s, %s, %s", $request, json_encode($options), $sequence_id));

        return $this->make_request( $request, 'GET', $options );
    }

    /**
     * Adds a subscriber to a sequence by email address
     *
     * @param $sequence_id
     * @param $email
     *
     * @return false|mixed
     */
    public function add_subscriber_to_sequence($sequence_id, $email)
    {
        $request = $this->api_version . sprintf('/courses/%s/subscribe', $sequence_id);

        $options = array(
            'api_key' => $this->api_key,
            'email'   => $email
        );

        $this->create_log(sprintf("POST add subscriber to sequence: %s, %s, %s, %s", $request, json_encode($options), $sequence_id, $email));

        return $this->make_request( $request, 'POST', $options );
    }

    /**
     * Adds a tag to a subscriber
     *
     * @param int $tag Tag ID
     * @param array $options Array of user data
     * @return false|object
     */
    public function add_tag( $tag, $options ) {

        if( !is_int($tag) || !is_array($options) ) {
            throw new \InvalidArgumentException;
        }

        $request = $this->api_version . sprintf( '/tags/%s/subscribe', $tag );

        $options['api_key'] = $this->api_key;

        $this->create_log(sprintf("POST add tag: %s, %s, %s", $request, json_encode($options), $tag));

        return $this->make_request( $request, 'POST', $options );
    }

    /**
     * Gets a resource index
     * Possible resources: forms, landing_pages, subscription_forms, tags
     *
     * GET /{$resource}/
     *
     * @param string $resource Resource type.
     * @return object API response
     */
    public function get_resources( $resource ) {

        if( !is_string($resource) ) {
            throw new \InvalidArgumentException;
        }

        if ( ! array_key_exists( $resource, $this->resources ) ) {

            $options = array(
                'api_key' => $this->api_key,
                'timeout' => 10,
                'Accept-Encoding' => 'gzip',
            );

            $request = sprintf('/%s/%s', $this->api_version, $resource === 'landing_pages' ? 'forms' : $resource);

            $this->create_log(sprintf("GET request %s, %s", $request, json_encode($options)));

            $resources = $this->make_request( $request, 'GET', $options );

            if(!$resources) {
                $this->create_log("No resources");
                $this->resources[ $resource ] = array(
                    array(
                        'id' => '-2',
                        'name' => 'Error contacting API',
                    ),
                );
            } else {
                $_resource = array();

                if ( 'forms' === $resource ) {
                    $response = isset( $resources->forms ) ? $resources->forms : array();
                    $this->create_log(sprintf("forms response %s", json_encode($response)));
                    foreach ( $response as $form ) {
                        if ( isset( $form->archived ) && $form->archived ) {
                            continue;
                        }
                        $_resource[] = $form;
                    }
                } elseif ( 'landing_pages' === $resource ) {
                    $response = isset($resources->forms ) ? $resources->forms : array();
                    $this->create_log(sprintf("landing_pages response %s", json_encode($response)));
                    foreach ( $response as $landing_page ) {
                        if ( 'hosted' === $landing_page->type ) {
                            if ( isset( $landing_page->archived ) && $landing_page->archived ) {
                                continue;
                            }
                            $_resource[] = $landing_page;
                        }
                    }
                } elseif ( 'subscription_forms' === $resource ) {
                    $this->create_log("subscription_forms");
                    foreach ( $resources as $mapping ) {
                        if ( isset( $mapping->archived ) && $mapping->archived ) {
                            continue;
                        }
                        $_resource[ $mapping->id ] = $mapping->form_id;
                    }
                } elseif ( 'tags' === $resource ) {
                    $response = isset( $resources->tags ) ? $resources->tags : array();
                    $this->create_log(sprintf("tags response %s", json_encode($response)));
                    foreach ( $response as $tag ) {
                        $_resource[] = $tag;
                    }
                }

                $this->resources[ $resource ] = $_resource;
            }

        }

        return $this->resources[ $resource ];
    }

    /**
     * Adds a subscriber to a form.
     *
     * @param string $form_id Form ID.
     * @param array  $options Array of user data (email, name).
     *
     * @return false|object
     */
    public function form_subscribe( $form_id, $options ) {

        if( !is_int($form_id) || !is_array($options) ) {
            throw new \InvalidArgumentException;
        }

        $request = $this->api_version . sprintf( '/forms/%s/subscribe', $form_id );

        $options['api_key'] = $this->api_key;

        $this->create_log(sprintf("POST form subscribe: %s, %s, %s", $request, json_encode($options), $form_id));

        return $this->make_request( $request, 'POST', $options );

    }

    /**
     * Remove subscription from a form
     *
     * @param array $options Array of user data (email).
     *
     * @return false|object
     */
    public function form_unsubscribe( $options ) {

        if( !is_array($options) ) {
            throw new \InvalidArgumentException;
        }

        $request = $this->api_version . '/unsubscribe';

        $options['api_secret'] = $this->api_secret;

        $this->create_log(sprintf("PUT form unsubscribe: %s, %s", $request, json_encode($options)));

        return $this->make_request( $request, 'PUT', $options );
    }

    /**
     * Get the ConvertKit subscriber ID associated with email address if it exists.
     * Return false if subscriber not found.
     *
     * @param $email_address string
     * @return false|int $subscriber_id
     */
    public function get_subscriber_id( $email_address ) {

        if(  !is_string($email_address) || !filter_var($email_address, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException;
        }

        $request = $this->api_version . '/subscribers';

        $options = array(
            'api_secret' => $this->api_secret,
            'status' => 'all',
        );

        $this->create_log(sprintf("GET subscriber id from all subscribers: %s, %s, %s", $request, json_encode($options), $email_address));

        $subscribers = $this->make_request( $request, 'GET', $options );

        if( !$subscribers ) {
            $this->create_log("No subscribers");
            return false;
        }

        $subscriber_id = $this::check_if_subscriber_in_array($email_address, $subscribers->subscribers);

        if($subscriber_id) {
            return $subscriber_id;
        }

        $total_pages = $subscribers->total_pages;

        $this->create_log(sprintf("Total number of pages is %s", $total_pages));

        for ( $i = 2; $i <= $total_pages; $i++ ) {
            $options['page'] = $i;
            $this->create_log(sprintf("Go to page %s", $i));
            $subscribers = $this->make_request( $request, 'GET', $options );

            if( !$subscribers ) {
                return false;
            }

            $subscriber_id = $this::check_if_subscriber_in_array($email_address, $subscribers->subscribers);

            if($subscriber_id) {
                return $subscriber_id;
            }
        }

        $this->create_log("Subscriber not found anywhere");

        return false;

    }

    /**
     * Get subscriber by id
     *
     * @param $subscriber_id int
     *
     * @return false|int
     */
    public function get_subscriber( $subscriber_id ) {

        if(  !is_int($subscriber_id) || $subscriber_id < 1 ) {
            throw new \InvalidArgumentException;
        }

        $request = $this->api_version . sprintf( '/subscribers/%s', $subscriber_id );

        $options = array(
            'api_secret' => $this->api_secret,
        );

        $this->create_log(sprintf("GET subscriber tags: %s, %s, %s", $request, json_encode($options), $subscriber_id));

        return $this->make_request( $request, 'GET', $options );

    }

    /**
     * Get a list of the tags for a subscriber.
     *
     * @param $subscriber_id
     * @return false|array $subscriber_tags Array of tags for customer with key of tag_id
     */
    public function get_subscriber_tags( $subscriber_id ) {

        if(  !is_int($subscriber_id) || $subscriber_id < 1 ) {
            throw new \InvalidArgumentException;
        }

        $request = $this->api_version . sprintf( '/subscribers/%s/tags', $subscriber_id );

        $options = array(
            'api_key' => $this->api_key,
        );

        $this->create_log(sprintf("GET subscriber tags: %s, %s, %s", $request, json_encode($options), $subscriber_id));

        return $this->make_request( $request, 'GET', $options );

    }

    /**
     * @param $options
     *
     * @return false|object
     */
    public function list_purchases($options) {

        if( !is_array($options) ) {
            throw new \InvalidArgumentException;
        }

        $request = $this->api_version . '/purchases';

        $options['api_secret'] = $this->api_secret;

        $this->create_log(sprintf("GET list purchases: %s, %s", $request, json_encode($options)));

        return $this->make_request( $request, 'GET', $options );
    }

    /**
     * Creates a purchase.
     *
     * @param array  $options
     *
     * @return false|object
     */
    public function create_purchase($options) {

        if( !is_array($options) ) {
            throw new \InvalidArgumentException;
        }

        $request = $this->api_version . '/purchases';

        $options['api_secret'] = $this->api_secret;

        $this->create_log(sprintf("POST create purchase: %s, %s", $request, json_encode($options)));

        return $this->make_request( $request, 'POST', $options );
    }

    /**
     * Get markup from ConvertKit for the provided $url
     *
     * @param string $url URL of API action.
     * @return false|string
     */
    public function get_resource( $url ) {

        if( !is_string($url) ) {
            throw new \InvalidArgumentException;
        }

        if (strpos( $url, 'api_key' ) === false) {
            $url .= '?api_key=' . $this->api_key;
        }

        $resource = '';

        $this->create_log(sprintf("Getting resource %s", $url));

        if ( ! empty( $url ) && isset( $this->markup[ $url ] ) ) {
            $this->create_log("Resource already set");
            $resource = $this->markup[ $url ];
        } elseif ( ! empty( $url ) ) {

            if ( ! function_exists( 'str_get_html' ) ) {
                require_once( dirname( __FILE__ ) . '/lib/simple-html-dom.php' );
            }

            if ( ! function_exists( 'url_to_absolute' ) ) {
                require_once( dirname( __FILE__ ) . '/lib/url-to-absolute.php' );
            }

            $this->create_log("Getting html from url");
            $html = file_get_html($url);

            foreach ( $html->find( 'a, link' ) as $element ) {
                if ( isset( $element->href ) ) {
                    $this->create_log(sprintf("To absolute url: %s", $element->href));
                    echo url_to_absolute( $url, $element->href );
                    $element->href = url_to_absolute( $url, $element->href );
                }
            }

            foreach ( $html->find( 'img, script' ) as $element ) {
                if ( isset( $element->src ) ) {
                    $this->create_log(sprintf("To absolute src: %s", $element->src));
                    $element->src = url_to_absolute( $url, $element->src );
                }
            }

            foreach ( $html->find( 'form' ) as $element ) {
                if ( isset( $element->action ) ) {
                    $this->create_log(sprintf("To absolute form: %s", $element->action));
                    $element->action = url_to_absolute( $url, $element->action );
                } else {
                    $element->action = $url;
                }
            }

            $resource = $html->save();
            $this->markup[ $url ] = $resource;

        }

        return $resource;
    }

    /**
     * @param $endpoint    string, endpoint for request
     * @param $method      string, POST, GET, PUT, PATCH, DELETE
     * @param array $args  array, additional arguments for request
     *
     * @return false|mixed
     */
    private function make_request($endpoint, $method, $args = array()) {

        if( !is_string($endpoint) || !is_string($method) || !is_array($args) ) {
            throw new \InvalidArgumentException;
        }

        $url = $this->api_url_base . $endpoint;

        $this->create_log(sprintf("Making request on %s.", $url));

        $request_body = json_encode($args);

        $this->create_log(sprintf("%s, Request body: %s", $method, $request_body));

        if( $method === "GET" ){
            if($args) {
                $url .= '?' . http_build_query($args);
            }
            $request = new Request($method, $url);
        } else {
            $request = new Request($method, $url, array(
                'Content-Type'   => 'application/json',
                'Content-Length' => strlen($request_body)
            ), $request_body);
        }

        $response = $this->client->send($request, [
            'exceptions' => false
        ]);

        $status_code = $response->getStatusCode();

        // If not between 200 and 300
        if (!preg_match("/^[2-3][0-9]{2}/", $status_code)) {
            $this->create_log(sprintf("Response code is %s.", $status_code));
            return false;
        }

        $response_body = json_decode($response->getBody()->getContents());

        if($response_body) {
            $this->create_log("Finish request successfully.");
            return $response_body;
        }

        $this->create_log("Failed to finish request.");
        return false;

    }

    /**
     * Looks for subscriber with email in array
     *
     * @param $email_address
     * @param $subscribers
     *
     * @return false|int  false if not found, else subscriber object
     */
    private function check_if_subscriber_in_array($email_address, $subscribers) {

        foreach ($subscribers as $subscriber) {
            if ($subscriber->email_address === $email_address) {
                $this->create_log("Subscriber found!");
                return $subscriber->id;
            }
        }

        $this->create_log("Subscriber not found on current page.");
        return false;

    }

}