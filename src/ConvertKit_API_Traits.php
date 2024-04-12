<?php
/**
 * ConvertKit API Traits
 *
 * @author ConvertKit
 */

namespace ConvertKit_API;

/**
 * ConvertKit API Traits
 */
trait ConvertKit_API_Traits
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
     * Gets the current account
     *
     * @see https://developers.convertkit.com/v4.html#get-current-account
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
            'account/colors',
            ['colors' => $colors]
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
     * Get forms.
     *
     * @param string  $status              Form status (active|archived|trashed|all).
     * @param boolean $include_total_count To include the total count of records in the response, use true.
     * @param string  $after_cursor        Return results after the given pagination cursor.
     * @param string  $before_cursor       Return results before the given pagination cursor.
     * @param integer $per_page            Number of results to return.
     *
     * @since 1.0.0
     *
     * @see https://developers.convertkit.com/v4.html#convertkit-api-forms
     *
     * @return false|array<int,\stdClass>
     */
    public function get_forms(
        string $status = 'active',
        bool $include_total_count = false,
        string $after_cursor = '',
        string $before_cursor = '',
        int $per_page = 100
    ) {
        return $this->get(
            'forms',
            $this->build_total_count_and_pagination_params(
                [
                    'type'   => 'embed',
                    'status' => $status,
                ],
                $include_total_count,
                $after_cursor,
                $before_cursor,
                $per_page
            )
        );
    }

    /**
     * Get landing pages.
     *
     * @param string  $status              Form status (active|archived|trashed|all).
     * @param boolean $include_total_count To include the total count of records in the response, use true.
     * @param string  $after_cursor        Return results after the given pagination cursor.
     * @param string  $before_cursor       Return results before the given pagination cursor.
     * @param integer $per_page            Number of results to return.
     *
     * @since 1.0.0
     *
     * @see https://developers.convertkit.com/v4.html#convertkit-api-forms
     *
     * @return false|array<int,\stdClass>
     */
    public function get_landing_pages(
        string $status = 'active',
        bool $include_total_count = false,
        string $after_cursor = '',
        string $before_cursor = '',
        int $per_page = 100
    ) {
        return $this->get(
            'forms',
            $this->build_total_count_and_pagination_params(
                [
                    'type'   => 'hosted',
                    'status' => $status,
                ],
                $include_total_count,
                $after_cursor,
                $before_cursor,
                $per_page
            )
        );
    }

    /**
     * Adds a subscriber to a form by email address
     *
     * @param integer $form_id       Form ID.
     * @param string  $email_address Email Address.
     *
     * @see https://developers.convertkit.com/v4.html#add-subscriber-to-form-by-email-address
     *
     * @return false|mixed
     */
    public function add_subscriber_to_form_by_email(int $form_id, string $email_address)
    {
        return $this->post(
            sprintf('forms/%s/subscribers', $form_id),
            ['email_address' => $email_address]
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
    public function add_subscriber_to_form(int $form_id, int $subscriber_id)
    {
        return $this->post(sprintf('forms/%s/subscribers/%s', $form_id, $subscriber_id));
    }

    /**
     * List subscribers for a form
     *
     * @param integer   $form_id             Form ID.
     * @param string    $subscriber_state    Subscriber State (active|bounced|cancelled|complained|inactive).
     * @param \DateTime $created_after       Filter subscribers who have been created after this date.
     * @param \DateTime $created_before      Filter subscribers who have been created before this date.
     * @param \DateTime $added_after         Filter subscribers who have been added to the form after this date.
     * @param \DateTime $added_before        Filter subscribers who have been added to the form before this date.
     * @param boolean   $include_total_count To include the total count of records in the response, use true.
     * @param string    $after_cursor        Return results after the given pagination cursor.
     * @param string    $before_cursor       Return results before the given pagination cursor.
     * @param integer   $per_page            Number of results to return.
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
        bool $include_total_count = false,
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

        // Send request.
        return $this->get(
            sprintf('forms/%s/subscribers', $form_id),
            $this->build_total_count_and_pagination_params(
                $options,
                $include_total_count,
                $after_cursor,
                $before_cursor,
                $per_page
            )
        );
    }

    /**
     * Gets sequences
     *
     * @param boolean $include_total_count To include the total count of records in the response, use true.
     * @param string  $after_cursor        Return results after the given pagination cursor.
     * @param string  $before_cursor       Return results before the given pagination cursor.
     * @param integer $per_page            Number of results to return.
     *
     * @see https://developers.convertkit.com/v4.html#list-sequences
     *
     * @return false|mixed
     */
    public function get_sequences(
        bool $include_total_count = false,
        string $after_cursor = '',
        string $before_cursor = '',
        int $per_page = 100
    ) {
        return $this->get(
            'sequences',
            $this->build_total_count_and_pagination_params(
                [],
                $include_total_count,
                $after_cursor,
                $before_cursor,
                $per_page
            )
        );
    }

    /**
     * Adds a subscriber to a sequence by email address
     *
     * @param integer $sequence_id   Sequence ID.
     * @param string  $email_address Email Address.
     *
     * @see https://developers.convertkit.com/v4.html#add-subscriber-to-sequence-by-email-address
     *
     * @return false|mixed
     */
    public function add_subscriber_to_sequence_by_email(int $sequence_id, string $email_address)
    {
        return $this->post(
            sprintf('sequences/%s/subscribers', $sequence_id),
            ['email_address' => $email_address]
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
    public function add_subscriber_to_sequence(int $sequence_id, int $subscriber_id)
    {
        return $this->post(sprintf('sequences/%s/subscribers/%s', $sequence_id, $subscriber_id));
    }

    /**
     * List subscribers for a sequence
     *
     * @param integer   $sequence_id         Sequence ID.
     * @param string    $subscriber_state    Subscriber State (active|bounced|cancelled|complained|inactive).
     * @param \DateTime $created_after       Filter subscribers who have been created after this date.
     * @param \DateTime $created_before      Filter subscribers who have been created before this date.
     * @param \DateTime $added_after         Filter subscribers who have been added to the form after this date.
     * @param \DateTime $added_before        Filter subscribers who have been added to the form before this date.
     * @param boolean   $include_total_count To include the total count of records in the response, use true.
     * @param string    $after_cursor        Return results after the given pagination cursor.
     * @param string    $before_cursor       Return results before the given pagination cursor.
     * @param integer   $per_page            Number of results to return.
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
        bool $include_total_count = false,
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

        // Send request.
        return $this->get(
            sprintf('sequences/%s/subscribers', $sequence_id),
            $this->build_total_count_and_pagination_params(
                $options,
                $include_total_count,
                $after_cursor,
                $before_cursor,
                $per_page
            )
        );
    }

    /**
     * List tags.
     *
     * @param boolean $include_total_count To include the total count of records in the response, use true.
     * @param string  $after_cursor        Return results after the given pagination cursor.
     * @param string  $before_cursor       Return results before the given pagination cursor.
     * @param integer $per_page            Number of results to return.
     *
     * @see https://developers.convertkit.com/v4.html#list-tags
     *
     * @return false|array<int,\stdClass>
     */
    public function get_tags(
        bool $include_total_count = false,
        string $after_cursor = '',
        string $before_cursor = '',
        int $per_page = 100
    ) {
        return $this->get(
            'tags',
            $this->build_total_count_and_pagination_params(
                [],
                $include_total_count,
                $after_cursor,
                $before_cursor,
                $per_page
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
            'tags',
            ['name' => $tag]
        );
    }

    /**
     * Creates multiple tags.
     *
     * @param array<int,string> $tags         Tag Names.
     * @param string            $callback_url URL to notify for large batch size when async processing complete.
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
            $options['tags'][] = [
                'name' => (string) $tag,
            ];
        }

        if (!empty($callback_url)) {
            $options['callback_url'] = $callback_url;
        }

        // Send request.
        return $this->post(
            'bulk/tags',
            $options
        );
    }

    /**
     * Tags a subscriber with the given existing Tag.
     *
     * @param integer $tag_id        Tag ID.
     * @param string  $email_address Email Address.
     *
     * @see https://developers.convertkit.com/v4.html#tag-a-subscriber-by-email-address
     *
     * @return false|mixed
     */
    public function tag_subscriber_by_email(int $tag_id, string $email_address)
    {
        return $this->post(
            sprintf('tags/%s/subscribers', $tag_id),
            ['email_address' => $email_address]
        );
    }

    /**
     * Tags a subscriber by subscriber ID with the given existing Tag.
     *
     * @param integer $tag_id        Tag ID.
     * @param integer $subscriber_id Subscriber ID.
     *
     * @see https://developers.convertkit.com/v4.html#tag-a-subscriber
     *
     * @return false|mixed
     */
    public function tag_subscriber(int $tag_id, int $subscriber_id)
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
     * @param integer $tag_id        Tag ID.
     * @param string  $email_address Subscriber email address.
     *
     * @since 1.0.0
     *
     * @see https://developers.convertkit.com/v4.html#remove-tag-from-subscriber-by-email-address
     *
     * @return false|mixed
     */
    public function remove_tag_from_subscriber_by_email(int $tag_id, string $email_address)
    {
        return $this->delete(
            sprintf('tags/%s/subscribers', $tag_id),
            ['email_address' => $email_address]
        );
    }

    /**
     * List subscribers for a tag
     *
     * @param integer   $tag_id              Tag ID.
     * @param string    $subscriber_state    Subscriber State (active|bounced|cancelled|complained|inactive).
     * @param \DateTime $created_after       Filter subscribers who have been created after this date.
     * @param \DateTime $created_before      Filter subscribers who have been created before this date.
     * @param \DateTime $tagged_after        Filter subscribers who have been tagged after this date.
     * @param \DateTime $tagged_before       Filter subscribers who have been tagged before this date.
     * @param boolean   $include_total_count To include the total count of records in the response, use true.
     * @param string    $after_cursor        Return results after the given pagination cursor.
     * @param string    $before_cursor       Return results before the given pagination cursor.
     * @param integer   $per_page            Number of results to return.
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
        bool $include_total_count = false,
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
        if (!is_null($tagged_after)) {
            $options['tagged_after'] = $tagged_after->format('Y-m-d');
        }
        if (!is_null($tagged_before)) {
            $options['tagged_before'] = $tagged_before->format('Y-m-d');
        }

        // Send request.
        return $this->get(
            sprintf('tags/%s/subscribers', $tag_id),
            $this->build_total_count_and_pagination_params(
                $options,
                $include_total_count,
                $after_cursor,
                $before_cursor,
                $per_page
            )
        );
    }

    /**
     * List email templates.
     *
     * @param boolean $include_total_count To include the total count of records in the response, use true.
     * @param string  $after_cursor        Return results after the given pagination cursor.
     * @param string  $before_cursor       Return results before the given pagination cursor.
     * @param integer $per_page            Number of results to return.
     *
     * @since 2.0.0
     *
     * @see https://developers.convertkit.com/v4.html#convertkit-api-email-templates
     *
     * @return false|mixed
     */
    public function get_email_templates(
        bool $include_total_count = false,
        string $after_cursor = '',
        string $before_cursor = '',
        int $per_page = 100
    ) {
        // Send request.
        return $this->get(
            'email_templates',
            $this->build_total_count_and_pagination_params(
                [],
                $include_total_count,
                $after_cursor,
                $before_cursor,
                $per_page
            )
        );
    }

    /**
     * List subscribers.
     *
     * @param string    $subscriber_state    Subscriber State (active|bounced|cancelled|complained|inactive).
     * @param string    $email_address       Search susbcribers by email address. This is an exact match search.
     * @param \DateTime $created_after       Filter subscribers who have been created after this date.
     * @param \DateTime $created_before      Filter subscribers who have been created before this date.
     * @param \DateTime $updated_after       Filter subscribers who have been updated after this date.
     * @param \DateTime $updated_before      Filter subscribers who have been updated before this date.
     * @param string    $sort_field          Sort Field (id|updated_at|cancelled_at).
     * @param string    $sort_order          Sort Order (asc|desc).
     * @param boolean   $include_total_count To include the total count of records in the response, use true.
     * @param string    $after_cursor        Return results after the given pagination cursor.
     * @param string    $before_cursor       Return results before the given pagination cursor.
     * @param integer   $per_page            Number of results to return.
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
        bool $include_total_count = false,
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

        // Send request.
        return $this->get(
            'subscribers',
            $this->build_total_count_and_pagination_params(
                $options,
                $include_total_count,
                $after_cursor,
                $before_cursor,
                $per_page
            )
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
            'subscribers',
            $options
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
            'bulk/subscribers',
            $options
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
     * @param string $email_address Email Address.
     *
     * @see https://developers.convertkit.com/v4.html#unsubscribe-subscriber
     *
     * @return false|object
     */
    public function unsubscribe_by_email(string $email_address)
    {
        return $this->post(
            sprintf(
                'subscribers/%s/unsubscribe',
                $this->get_subscriber_id($email_address)
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
    public function unsubscribe(int $subscriber_id)
    {
        return $this->post(sprintf('subscribers/%s/unsubscribe', $subscriber_id));
    }

    /**
     * Get a list of the tags for a subscriber.
     *
     * @param integer $subscriber_id       Subscriber ID.
     * @param boolean $include_total_count To include the total count of records in the response, use true.
     * @param string  $after_cursor        Return results after the given pagination cursor.
     * @param string  $before_cursor       Return results before the given pagination cursor.
     * @param integer $per_page            Number of results to return.
     *
     * @see https://developers.convertkit.com/v4.html#list-tags-for-a-subscriber
     *
     * @return false|array<int,\stdClass>
     */
    public function get_subscriber_tags(
        int $subscriber_id,
        bool $include_total_count = false,
        string $after_cursor = '',
        string $before_cursor = '',
        int $per_page = 100
    ) {
        return $this->get(
            sprintf('subscribers/%s/tags', $subscriber_id),
            $this->build_total_count_and_pagination_params(
                [],
                $include_total_count,
                $after_cursor,
                $before_cursor,
                $per_page
            )
        );
    }

    /**
     * List broadcasts.
     *
     * @param boolean $include_total_count To include the total count of records in the response, use true.
     * @param string  $after_cursor        Return results after the given pagination cursor.
     * @param string  $before_cursor       Return results before the given pagination cursor.
     * @param integer $per_page            Number of results to return.
     *
     * @see https://developers.convertkit.com/v4.html#list-broadcasts
     *
     * @return false|mixed
     */
    public function get_broadcasts(
        bool $include_total_count = false,
        string $after_cursor = '',
        string $before_cursor = '',
        int $per_page = 100
    ) {
        // Send request.
        return $this->get(
            'broadcasts',
            $this->build_total_count_and_pagination_params(
                [],
                $include_total_count,
                $after_cursor,
                $before_cursor,
                $per_page
            )
        );
    }

    /**
     * Creates a broadcast.
     *
     * @param string               $subject           The broadcast email's subject.
     * @param string               $content           The broadcast's email HTML content.
     * @param string               $description       An internal description of this broadcast.
     * @param boolean              $public            Specifies whether or not this is a public post.
     * @param \DateTime            $published_at      Specifies the time that this post was published (applicable
     *                                                only to public posts).
     * @param \DateTime            $send_at           Time that this broadcast should be sent; leave blank to create
     *                                                a draft broadcast. If set to a future time, this is the time that
     *                                                the broadcast will be scheduled to send.
     * @param string               $email_address     Sending email address; leave blank to use your account's
     *                                                default sending email address.
     * @param string               $email_template_id ID of the email template to use; leave blank to use your
     *                                                account's default email template.
     * @param string               $thumbnail_alt     Specify the ALT attribute of the public thumbnail image
     *                                                (applicable only to public posts).
     * @param string               $thumbnail_url     Specify the URL of the thumbnail image to accompany the broadcast
     *                                                post (applicable only to public posts).
     * @param string               $preview_text      Specify the preview text of the email.
     * @param array<string,string> $subscriber_filter Filter subscriber(s) to send the email to.
     *
     * @see https://developers.convertkit.com/v4.html#create-a-broadcast
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
        string $email_template_id = '',
        string $thumbnail_alt = '',
        string $thumbnail_url = '',
        string $preview_text = '',
        array $subscriber_filter = []
    ) {
        $options = [
            'email_template_id' => $email_template_id,
            'email_address'     => $email_address,
            'content'           => $content,
            'description'       => $description,
            'public'            => $public,
            'published_at'      => (!is_null($published_at) ? $published_at->format('Y-m-d H:i:s') : ''),
            'send_at'           => (!is_null($send_at) ? $send_at->format('Y-m-d H:i:s') : ''),
            'thumbnail_alt'     => $thumbnail_alt,
            'thumbnail_url'     => $thumbnail_url,
            'preview_text'      => $preview_text,
            'subject'           => $subject,
        ];
        if (count($subscriber_filter)) {
            $options['subscriber_filter'] = $subscriber_filter;
        }

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
        return $this->post(
            'broadcasts',
            $options
        );
    }

    /**
     * Retrieve a specific broadcast.
     *
     * @param integer $id Broadcast ID.
     *
     * @see https://developers.convertkit.com/v4.html#get-a-broadcast
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
     * @see https://developers.convertkit.com/v4.html#get-stats
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
     * @param integer              $id                Broadcast ID.
     * @param string               $subject           The broadcast email's subject.
     * @param string               $content           The broadcast's email HTML content.
     * @param string               $description       An internal description of this broadcast.
     * @param boolean              $public            Specifies whether or not this is a public post.
     * @param \DateTime            $published_at      Specifies the time that this post was published (applicable
     *                                                only to public posts).
     * @param \DateTime            $send_at           Time that this broadcast should be sent; leave blank to create
     *                                                a draft broadcast. If set to a future time, this is the time that
     *                                                the broadcast will be scheduled to send.
     * @param string               $email_address     Sending email address; leave blank to use your account's
     *                                                default sending email address.
     * @param string               $email_template_id ID of the email template to use; leave blank to use your
     *                                                account's default email template.
     * @param string               $thumbnail_alt     Specify the ALT attribute of the public thumbnail image
     *                                                (applicable only to public posts).
     * @param string               $thumbnail_url     Specify the URL of the thumbnail image to accompany the broadcast
     *                                                post (applicable only to public posts).
     * @param string               $preview_text      Specify the preview text of the email.
     * @param array<string,string> $subscriber_filter Filter subscriber(s) to send the email to.
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
        string $email_template_id = '',
        string $thumbnail_alt = '',
        string $thumbnail_url = '',
        string $preview_text = '',
        array $subscriber_filter = []
    ) {
        $options = [
            'email_template_id' => $email_template_id,
            'email_address'     => $email_address,
            'content'           => $content,
            'description'       => $description,
            'public'            => $public,
            'published_at'      => (!is_null($published_at) ? $published_at->format('Y-m-d H:i:s') : ''),
            'send_at'           => (!is_null($send_at) ? $send_at->format('Y-m-d H:i:s') : ''),
            'thumbnail_alt'     => $thumbnail_alt,
            'thumbnail_url'     => $thumbnail_url,
            'preview_text'      => $preview_text,
            'subject'           => $subject,
        ];
        if (count($subscriber_filter)) {
            $options['subscriber_filter'] = $subscriber_filter;
        }

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
     * @see https://developers.convertkit.com/v4.html#delete-a-broadcast
     *
     * @return false|object
     */
    public function delete_broadcast(int $id)
    {
        return $this->delete(sprintf('broadcasts/%s', $id));
    }

    /**
     * List webhooks.
     *
     * @param boolean $include_total_count To include the total count of records in the response, use true.
     * @param string  $after_cursor        Return results after the given pagination cursor.
     * @param string  $before_cursor       Return results before the given pagination cursor.
     * @param integer $per_page            Number of results to return.
     *
     * @since 2.0.0
     *
     * @see https://developers.convertkit.com/v4.html#list-webhooks
     *
     * @return false|mixed
     */
    public function get_webhooks(
        bool $include_total_count = false,
        string $after_cursor = '',
        string $before_cursor = '',
        int $per_page = 100
    ) {
        // Send request.
        return $this->get(
            'webhooks',
            $this->build_total_count_and_pagination_params(
                [],
                $include_total_count,
                $after_cursor,
                $before_cursor,
                $per_page
            )
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
     * @see https://developers.convertkit.com/v4.html#create-a-webhook
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
            case 'subscriber.subscriber_bounce':
            case 'subscriber.subscriber_complain':
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
            'webhooks',
            [
                'target_url' => $url,
                'event'      => $eventData,
            ]
        );
    }

    /**
     * Deletes an existing webhook.
     *
     * @param integer $id Webhook ID.
     *
     * @since 1.0.0
     *
     * @see https://developers.convertkit.com/v4.html#delete-a-webhook
     *
     * @return false|object
     */
    public function delete_webhook(int $id)
    {
        return $this->delete(sprintf('webhooks/%s', $id));
    }

    /**
     * List custom fields.
     *
     * @param boolean $include_total_count To include the total count of records in the response, use true.
     * @param string  $after_cursor        Return results after the given pagination cursor.
     * @param string  $before_cursor       Return results before the given pagination cursor.
     * @param integer $per_page            Number of results to return.
     *
     * @since 1.0.0
     *
     * @see https://developers.convertkit.com/v4.html#list-custom-fields
     *
     * @return false|mixed
     */
    public function get_custom_fields(
        bool $include_total_count = false,
        string $after_cursor = '',
        string $before_cursor = '',
        int $per_page = 100
    ) {
        // Send request.
        return $this->get(
            'custom_fields',
            $this->build_total_count_and_pagination_params(
                [],
                $include_total_count,
                $after_cursor,
                $before_cursor,
                $per_page
            )
        );
    }

    /**
     * Creates a custom field.
     *
     * @param string $label Custom Field label.
     *
     * @since 1.0.0
     *
     * @see https://developers.convertkit.com/v4.html#create-a-custom-field
     *
     * @return false|object
     */
    public function create_custom_field(string $label)
    {
        return $this->post(
            'custom_fields',
            ['label' => $label]
        );
    }

    /**
     * Creates multiple custom fields.
     *
     * @param array<string> $labels       Custom Fields labels.
     * @param string        $callback_url URL to notify for large batch size when async processing complete.
     *
     * @since 1.0.0
     *
     * @see https://developers.convertkit.com/v4.html#bulk-create-custom-fields
     *
     * @return false|object
     */
    public function create_custom_fields(array $labels, string $callback_url = '')
    {
        // Build parameters.
        $options = [
            'custom_fields' => [],
        ];
        foreach ($labels as $i => $label) {
            $options['custom_fields'][] = [
                'label' => (string) $label,
            ];
        }

        if (!empty($callback_url)) {
            $options['callback_url'] = $callback_url;
        }

        // Send request.
        return $this->post(
            'bulk/custom_fields',
            $options
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
     * @see https://developers.convertkit.com/v4.html#update-a-custom-field
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
     * @param boolean $include_total_count To include the total count of records in the response, use true.
     * @param string  $after_cursor        Return results after the given pagination cursor.
     * @param string  $before_cursor       Return results before the given pagination cursor.
     * @param integer $per_page            Number of results to return.
     *
     * @since 1.0.0
     *
     * @see https://developers.convertkit.com/v4.html#list-purchases
     *
     * @return false|mixed
     */
    public function get_purchases(
        bool $include_total_count = false,
        string $after_cursor = '',
        string $before_cursor = '',
        int $per_page = 100
    ) {
        // Send request.
        return $this->get(
            'purchases',
            $this->build_total_count_and_pagination_params(
                [],
                $include_total_count,
                $after_cursor,
                $before_cursor,
                $per_page
            )
        );
    }

    /**
     * Retuns a specific purchase.
     *
     * @param integer $purchase_id Purchase ID.
     *
     * @see https://developers.convertkit.com/v4.html#get-a-purchase
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
     * @param string                         $email_address    Email Address.
     * @param string                         $transaction_id   Transaction ID.
     * @param array<string,int|float|string> $products         Products.
     * @param string                         $currency         ISO Currency Code.
     * @param string                         $first_name       First Name.
     * @param string                         $status           Order Status.
     * @param float                          $subtotal         Subtotal.
     * @param float                          $tax              Tax.
     * @param float                          $shipping         Shipping.
     * @param float                          $discount         Discount.
     * @param float                          $total            Total.
     * @param \DateTime                      $transaction_time Transaction date and time.
     *
     * @see https://developers.convertkit.com/v4.html#create-a-purchase
     *
     * @return false|object
     */
    public function create_purchase(
        string $email_address,
        string $transaction_id,
        array $products,
        string $currency = 'USD',
        string $first_name = null,
        string $status = null,
        float $subtotal = 0,
        float $tax = 0,
        float $shipping = 0,
        float $discount = 0,
        float $total = 0,
        \DateTime $transaction_time = null
    ) {
        // Build parameters.
        $options = [
            // Required fields.
            'email_address'    => $email_address,
            'transaction_id'   => $transaction_id,
            'products'         => $products,
            'currency'         => $currency, // Required, but if not provided, API will default to USD.

            // Optional fields.
            'first_name'       => $first_name,
            'status'           => $status,
            'subtotal'         => $subtotal,
            'tax'              => $tax,
            'shipping'         => $shipping,
            'discount'         => $discount,
            'total'            => $total,
            'transaction_time' => (!is_null($transaction_time) ? $transaction_time->format('Y-m-d H:i:s') : ''),
        ];

        // Iterate through options, removing blank and null entries.
        foreach ($options as $key => $value) {
            if (is_null($value)) {
                unset($options[$key]);
                continue;
            }

            if (is_string($value) && strlen($value) === 0) {
                unset($options[$key]);
            }
        }

        return $this->post('purchases', $options);
    }

    /**
     * List segments.
     *
     * @param boolean $include_total_count To include the total count of records in the response, use true.
     * @param string  $after_cursor        Return results after the given pagination cursor.
     * @param string  $before_cursor       Return results before the given pagination cursor.
     * @param integer $per_page            Number of results to return.
     *
     * @since 2.0.0
     *
     * @see https://developers.convertkit.com/v4.html#convertkit-api-segments
     *
     * @return false|mixed
     */
    public function get_segments(
        bool $include_total_count = false,
        string $after_cursor = '',
        string $before_cursor = '',
        int $per_page = 100
    ) {
        // Send request.
        return $this->get(
            'segments',
            $this->build_total_count_and_pagination_params(
                [],
                $include_total_count,
                $after_cursor,
                $before_cursor,
                $per_page
            )
        );
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
    public function convert_relative_to_absolute_urls(\DOMNodeList $elements, string $attribute, string $url) // phpcs:ignore Squiz.Commenting.FunctionComment.IncorrectTypeHint, Generic.Files.LineLength.TooLong
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
    public function strip_html_head_body_tags(string $markup)
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
     * Adds total count and pagination parameters to the given array of existing API parameters.
     *
     * @param array<string, string|integer|bool> $params              API parameters.
     * @param boolean                            $include_total_count Return total count of records.
     * @param string                             $after_cursor        Return results after the given pagination cursor.
     * @param string                             $before_cursor       Return results before the given pagination cursor.
     * @param integer                            $per_page            Number of results to return.
     *
     * @since 2.0.0
     *
     * @return array<string, string|integer|bool>
     */
    private function build_total_count_and_pagination_params(
        array $params = [],
        bool $include_total_count = false,
        string $after_cursor = '',
        string $before_cursor = '',
        int $per_page = 100
    ) {
        $params['include_total_count'] = $include_total_count;
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
     * @param string                                                             $endpoint API Endpoint.
     * @param array<string, int|string|boolean|array<string, int|string>|string> $args     Request arguments.
     *
     * @return false|mixed
     */
    public function get(string $endpoint, array $args = [])
    {
        return $this->request($endpoint, 'GET', $args);
    }

    /**
     * Performs a POST request to the API.
     *
     * @param string                                                                                                     $endpoint API Endpoint.
     * @param array<string, bool|integer|float|string|null|array<int|string, float|integer|string|array<string|string>>> $args     Request arguments.
     *
     * @return false|mixed
     */
    public function post(string $endpoint, array $args = [])
    {
        return $this->request($endpoint, 'POST', $args);
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
        return $this->request($endpoint, 'PUT', $args);
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
        return $this->request($endpoint, 'DELETE', $args);
    }

    /**
     * Performs an API request.
     *
     * @param string                                                                                                     $endpoint API Endpoint.
     * @param string                                                                                                     $method   Request method.
     * @param array<string, bool|integer|float|string|null|array<int|string, float|integer|string|array<string|string>>> $args     Request arguments.
     *
     * @throws \Exception If JSON encoding arguments failed.
     *
     * @return false|mixed
     */
    abstract public function request(string $endpoint, string $method, array $args = []);

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
    abstract public function get_request_headers(string $type = 'application/json', bool $auth = true);

    /**
     * Returns the maximum amount of time to wait for
     * a response to the request before exiting.
     *
     * @since   2.0.0
     *
     * @return  int     Timeout, in seconds.
     */
    abstract public function get_timeout();

    /**
     * Returns the user agent string to use in all HTTP requests.
     *
     * @since 2.0.0
     *
     * @return string
     */
    abstract public function get_user_agent();
}
