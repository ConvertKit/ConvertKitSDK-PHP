<?php

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Exception\RequestException;

/**
 * ConvertKit API class tests.
 */
class ConvertKitAPITest extends TestCase
{
    /**
     * ConvertKit Class Object
     *
     * @var object
     */
    protected $api;

    /**
     * Location of the monologger log file.
     *
     * @since   1.2.0
     *
     * @var     string
     */
    protected $logFile = '';

    /**
     * Load .env configuration into $_ENV superglobal, and initialize the API
     * class before each test.
     *
     * @since   1.0.0
     *
     * @return  void
     */
    protected function setUp(): void
    {
        // Load environment credentials from root folder.
        $dotenv = Dotenv\Dotenv::createImmutable(dirname(dirname(__FILE__)));
        $dotenv->load();

        // Set location where API class will create/write the log file.
        $this->logFile = dirname(dirname(__FILE__)) . '/src/logs/debug.log';

        // Delete any existing debug log file.
        $this->deleteLogFile();

        // Setup API.
        $this->api = new \ConvertKit_API\ConvertKit_API($_ENV['CONVERTKIT_API_KEY'], $_ENV['CONVERTKIT_API_SECRET']);
    }

    /**
     * Test that a ClientInterface can be injected.
     *
     *
     * @return  void
     */
    public function testClientInterfaceInjection()
    {
        // Setup API with a mock Guzzle client.
        $mock = new MockHandler([
            new Response(200, [], json_encode(
                [
                    'name' => 'Test Account for Guzzle Mock',
                    'plan_type' => 'free',
                    'primary_email_address' => 'mock@guzzle.mock',
                ]
            )),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $this->api->set_http_client($client);

        $result = $this->api->get_account();

        $this->assertSame('Test Account for Guzzle Mock', $result->name);
        $this->assertSame('free', $result->plan_type);
        $this->assertSame('mock@guzzle.mock', $result->primary_email_address);
    }

    /**
     * Test that debug logging works when enabled and an API call is made.
     *
     * @since   1.2.0
     *
     * @return  void
     */
    public function testDebugEnabled()
    {
        // Setup API with debugging enabled.
        $api = new \ConvertKit_API\ConvertKit_API($_ENV['CONVERTKIT_API_KEY'], $_ENV['CONVERTKIT_API_SECRET'], true);
        $result = $api->get_account();

        // Confirm that the log includes expected data.
        $this->assertStringContainsString('ck-debug.INFO: GET account', $this->getLogFileContents());
        $this->assertStringContainsString('ck-debug.INFO: Finish request successfully', $this->getLogFileContents());
    }

    /**
     * Test that debug logging is not performed when disabled and an API call is made.
     *
     * @since   1.2.0
     *
     * @return  void
     */
    public function testDebugDisabled()
    {
        $result = $this->api->get_account();

        // Confirm that the log is empty / doesn't exist.
        $this->assertEmpty($this->getLogFileContents());
    }

    /**
     * Test that a ClientException is thrown when invalid API credentials are supplied.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testInvalidAPICredentials()
    {
        $this->expectException(GuzzleHttp\Exception\ClientException::class);
        $api = new \ConvertKit_API\ConvertKit_API('fakeApiKey', 'fakeApiSecret');
        $result = $api->get_account();
    }

    /**
     * Test that get_account() returns the expected data.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testGetAccount()
    {
        $result = $this->api->get_account();
        $this->assertInstanceOf('stdClass', $result);

        // Convert to array to check for keys, as assertObjectHasAttribute() will be deprecated in PHPUnit 10.
        $result = get_object_vars($result);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('plan_type', $result);
        $this->assertArrayHasKey('primary_email_address', $result);
    }

    /**
     * Test that get_forms() returns the expected data.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testGetForms()
    {
        $result = $this->api->get_forms();
        $this->assertIsArray($result);

        // Convert to array to check for keys, as assertObjectHasAttribute() will be deprecated in PHPUnit 10.
        $form = get_object_vars($result[0]);
        $this->assertArrayHasKey('id', $form);
        $this->assertArrayHasKey('name', $form);
        $this->assertArrayHasKey('created_at', $form);
        $this->assertArrayHasKey('type', $form);
        $this->assertArrayHasKey('format', $form);
        $this->assertArrayHasKey('embed_js', $form);
        $this->assertArrayHasKey('embed_url', $form);
        $this->assertArrayHasKey('archived', $form);
    }

    /**
     * Test that get_landing_pages() returns the expected data.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testGetLandingPages()
    {
        $result = $this->api->get_landing_pages();
        $this->assertIsArray($result);

        // Convert to array to check for keys, as assertObjectHasAttribute() will be deprecated in PHPUnit 10.
        $landingPage = get_object_vars($result[0]);
        $this->assertArrayHasKey('id', $landingPage);
        $this->assertArrayHasKey('name', $landingPage);
        $this->assertArrayHasKey('created_at', $landingPage);
        $this->assertArrayHasKey('type', $landingPage);
        $this->assertEquals('hosted', $landingPage['type']);
        $this->assertArrayHasKey('format', $landingPage);
        $this->assertArrayHasKey('embed_js', $landingPage);
        $this->assertArrayHasKey('embed_url', $landingPage);
        $this->assertArrayHasKey('archived', $landingPage);
    }

    /**
     * Test that get_form_subscriptions() returns the expected data
     * when a valid Form ID is specified.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testGetFormSubscriptions()
    {
        $result = $this->api->get_form_subscriptions((int) $_ENV['CONVERTKIT_API_FORM_ID']);

        // Convert to array to check for keys, as assertObjectHasAttribute() will be deprecated in PHPUnit 10.
        $result = get_object_vars($result);
        $this->assertArrayHasKey('total_subscriptions', $result);
        $this->assertArrayHasKey('page', $result);
        $this->assertArrayHasKey('total_pages', $result);
        $this->assertArrayHasKey('subscriptions', $result);
        $this->assertIsArray($result['subscriptions']);

        // Assert sort order is ascending.
        $this->assertGreaterThanOrEqual(
            $result['subscriptions'][0]->created_at,
            $result['subscriptions'][1]->created_at
        );
    }

    /**
     * Test that get_form_subscriptions() returns the expected data
     * when a valid Form ID is specified and the sort order is descending.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testGetFormSubscriptionsWithDescSortOrder()
    {
        $result = $this->api->get_form_subscriptions((int) $_ENV['CONVERTKIT_API_FORM_ID'], 'desc');

        // Convert to array to check for keys, as assertObjectHasAttribute() will be deprecated in PHPUnit 10.
        $result = get_object_vars($result);
        $this->assertArrayHasKey('total_subscriptions', $result);
        $this->assertArrayHasKey('page', $result);
        $this->assertArrayHasKey('total_pages', $result);
        $this->assertArrayHasKey('subscriptions', $result);
        $this->assertIsArray($result['subscriptions']);

        // Assert sort order.
        $this->assertLessThanOrEqual(
            $result['subscriptions'][0]->created_at,
            $result['subscriptions'][1]->created_at
        );
    }

    /**
     * Test that get_form_subscriptions() returns the expected data
     * when a valid Form ID is specified and the subscription status
     * is cancelled.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testGetFormSubscriptionsWithCancelledSubscriberState()
    {
        $result = $this->api->get_form_subscriptions((int) $_ENV['CONVERTKIT_API_FORM_ID'], 'asc', 'cancelled');

        // Convert to array to check for keys, as assertObjectHasAttribute() will be deprecated in PHPUnit 10.
        $result = get_object_vars($result);
        $this->assertArrayHasKey('total_subscriptions', $result);
        $this->assertEquals($result['total_subscriptions'], 0);
        $this->assertArrayHasKey('page', $result);
        $this->assertArrayHasKey('total_pages', $result);
        $this->assertArrayHasKey('subscriptions', $result);
        $this->assertIsArray($result['subscriptions']);
    }

    /**
     * Test that get_form_subscriptions() returns the expected data
     * when a valid Form ID is specified and the page is set to 2.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testGetFormSubscriptionsWithPage()
    {
        $result = $this->api->get_form_subscriptions((int) $_ENV['CONVERTKIT_API_FORM_ID'], 'asc', 'active', 2);

        // Convert to array to check for keys, as assertObjectHasAttribute() will be deprecated in PHPUnit 10.
        $result = get_object_vars($result);
        $this->assertArrayHasKey('total_subscriptions', $result);
        $this->assertArrayHasKey('page', $result);
        $this->assertEquals($result['page'], 2);
        $this->assertArrayHasKey('total_pages', $result);
        $this->assertArrayHasKey('subscriptions', $result);
        $this->assertIsArray($result['subscriptions']);
    }

    /**
     * Test that get_form_subscriptions() returns the expected data
     * when a valid Form ID is specified.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testGetFormSubscriptionsWithInvalidFormID()
    {
        $this->expectException(GuzzleHttp\Exception\ClientException::class);
        $result = $this->api->get_form_subscriptions(12345);
    }

    /**
     * Test that get_sequences() returns the expected data.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testGetSequences()
    {
        $result = $this->api->get_sequences();
        $this->assertInstanceOf('stdClass', $result);

        // Check first sequence in resultset has expected data.
        $sequence = get_object_vars($result->courses[0]);
        $this->assertArrayHasKey('id', $sequence);
        $this->assertArrayHasKey('name', $sequence);
        $this->assertArrayHasKey('hold', $sequence);
        $this->assertArrayHasKey('repeat', $sequence);
        $this->assertArrayHasKey('created_at', $sequence);
    }

    /**
     * Test that add_subscriber_to_sequence() returns the expected data.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testAddSubscriberToSequence()
    {
        $result = $this->api->add_subscriber_to_sequence(
            $_ENV['CONVERTKIT_API_SEQUENCE_ID'],
            $this->generateEmailAddress()
        );
        $this->assertInstanceOf('stdClass', $result);
        $this->assertArrayHasKey('subscription', get_object_vars($result));
    }

    /**
     * Test that add_subscriber_to_sequence() throws a ClientException when an invalid
     * sequence is specified.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testAddSubscriberToSequenceWithInvalidSequenceID()
    {
        $this->expectException(GuzzleHttp\Exception\ClientException::class);
        $result = $this->api->add_subscriber_to_sequence(12345, $this->generateEmailAddress());
    }

    /**
     * Test that add_subscriber_to_sequence() throws a ClientException when an invalid
     * email address is specified.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testAddSubscriberToSequenceWithInvalidEmailAddress()
    {
        $this->expectException(GuzzleHttp\Exception\ClientException::class);
        $result = $this->api->add_subscriber_to_sequence($_ENV['CONVERTKIT_API_SEQUENCE_ID'], 'not-an-email-address');
    }

    /**
     * Test that add_subscriber_to_sequence() returns the expected data
     * when a first_name parameter is included.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testAddSubscriberToSequenceWithFirstName()
    {
        $emailAddress = $this->generateEmailAddress();
        $firstName = 'First Name';
        $result = $this->api->add_subscriber_to_sequence(
            $_ENV['CONVERTKIT_API_SEQUENCE_ID'],
            $emailAddress,
            $firstName
        );

        $this->assertInstanceOf('stdClass', $result);
        $this->assertArrayHasKey('subscription', get_object_vars($result));

        // Fetch subscriber from API to confirm the first name was saved.
        $subscriber = $this->api->get_subscriber($result->subscription->subscriber->id);
        $this->assertEquals($subscriber->subscriber->email_address, $emailAddress);
        $this->assertEquals($subscriber->subscriber->first_name, $firstName);
    }

    /**
     * Test that add_subscriber_to_sequence() returns the expected data
     * when custom field data is included.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testAddSubscriberToSequenceWithCustomFields()
    {
        $result = $this->api->add_subscriber_to_sequence(
            $_ENV['CONVERTKIT_API_SEQUENCE_ID'],
            $this->generateEmailAddress(),
            'First Name',
            [
                'last_name' => 'Last Name',
            ]
        );

        // Check subscription object returned.
        $this->assertInstanceOf('stdClass', $result);
        $this->assertArrayHasKey('subscription', get_object_vars($result));

        // Fetch subscriber from API to confirm the custom fields were saved.
        $subscriber = $this->api->get_subscriber($result->subscription->subscriber->id);
        $this->assertEquals($subscriber->subscriber->fields->last_name, 'Last Name');
    }

    /**
     * Test that add_subscriber_to_sequence() returns the expected data
     * when custom field data is included.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testAddSubscriberToSequenceWithTagID()
    {
        $result = $this->api->add_subscriber_to_sequence(
            $_ENV['CONVERTKIT_API_SEQUENCE_ID'],
            $this->generateEmailAddress(),
            'First Name',
            [],
            [
                (int) $_ENV['CONVERTKIT_API_TAG_ID']
            ]
        );

        // Check subscription object returned.
        $this->assertInstanceOf('stdClass', $result);
        $this->assertArrayHasKey('subscription', get_object_vars($result));

        // Fetch subscriber tags from API to confirm the tag saved.
        $subscriberTags = $this->api->get_subscriber_tags($result->subscription->subscriber->id);
        $this->assertEquals($subscriberTags->tags[0]->id, $_ENV['CONVERTKIT_API_TAG_ID']);
    }

    /**
     * Test that get_sequence_subscriptions() returns the expected data.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testGetSequenceSubscriptions()
    {
        $result = $this->api->get_sequence_subscriptions($_ENV['CONVERTKIT_API_SEQUENCE_ID']);
        $this->assertInstanceOf('stdClass', $result);

        // Assert expected keys exist.
        $result = get_object_vars($result);
        $this->assertArrayHasKey('total_subscriptions', $result);
        $this->assertArrayHasKey('page', $result);
        $this->assertArrayHasKey('total_pages', $result);
        $this->assertArrayHasKey('subscriptions', $result);

        // Assert subscriptions exist.
        $this->assertIsArray($result['subscriptions']);

        // Assert sort order is ascending.
        $this->assertGreaterThanOrEqual(
            $result['subscriptions'][0]->created_at,
            $result['subscriptions'][1]->created_at
        );
    }

    /**
     * Test that get_sequence_subscriptions() returns the expected data in descending order.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testGetSequenceSubscriptionsWithDescSortOrder()
    {
        $result = $this->api->get_sequence_subscriptions($_ENV['CONVERTKIT_API_SEQUENCE_ID'], 'desc');
        $this->assertInstanceOf('stdClass', $result);

        $result = get_object_vars($result);
        $this->assertArrayHasKey('total_subscriptions', $result);
        $this->assertArrayHasKey('page', $result);
        $this->assertArrayHasKey('total_pages', $result);
        $this->assertArrayHasKey('subscriptions', $result);

        // Assert subscriptions exist.
        $this->assertIsArray($result['subscriptions']);

        // Assert sort order.
        $this->assertLessThanOrEqual(
            $result['subscriptions'][0]->created_at,
            $result['subscriptions'][1]->created_at
        );
    }

    /**
     * Test that get_sequence_subscriptions() throws a ClientException when an invalid
     * sort order is specified.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testGetSequenceSubscriptionsWithInvalidSortOrder()
    {
        $this->expectException(GuzzleHttp\Exception\ClientException::class);
        $result = $this->api->get_sequence_subscriptions($_ENV['CONVERTKIT_API_SEQUENCE_ID'], 'invalidSortOrder');
    }

    /**
     * Test that get_sequence_subscriptions() throws a ClientException when an invalid
     * sequence ID is specified.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testGetSequenceSubscriptionsWithInvalidSequenceID()
    {
        $this->expectException(GuzzleHttp\Exception\ClientException::class);
        $result = $this->api->get_sequence_subscriptions(12345);
    }

    /**
     * Test that get_tags() returns the expected data.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testGetTags()
    {
        $result = $this->api->get_tags();
        $this->assertIsArray($result);

        // Convert to array to check for keys, as assertObjectHasAttribute() will be deprecated in PHPUnit 10.
        $tag = get_object_vars($result[0]);
        $this->assertArrayHasKey('id', $tag);
        $this->assertArrayHasKey('name', $tag);
        $this->assertArrayHasKey('created_at', $tag);
    }

    /**
     * Test that create_tag() returns the expected data.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testCreateTag()
    {
        $tagName = 'Tag Test ' . mt_rand();
        $result = $this->api->create_tag($tagName);

        // Convert to array to check for keys, as assertObjectHasAttribute() will be deprecated in PHPUnit 10.
        $tag = get_object_vars($result);
        $this->assertArrayHasKey('id', $tag);
        $this->assertArrayHasKey('name', $tag);
        $this->assertArrayHasKey('created_at', $tag);
        $this->assertEquals($tag['name'], $tagName);
    }

    /**
     * Test that create_tag() throws a ClientException when creating
     * a blank tag.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testCreateTagBlank()
    {
        $this->expectException(GuzzleHttp\Exception\ClientException::class);
        $result = $this->api->create_tag('');
    }

    /**
     * Test that create_tag() throws a ClientException when creating
     * a tag that already exists.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testCreateTagThatExists()
    {
        $this->expectException(GuzzleHttp\Exception\ClientException::class);
        $result = $this->api->create_tag($_ENV['CONVERTKIT_API_TAG_NAME']);
    }

    /**
     * Test that create_tags() returns the expected data.
     *
     * @since   1.1.0
     *
     * @return void
     */
    public function testCreateTags()
    {
        $tagNames = [
            'Tag Test ' . mt_rand(),
            'Tag Test ' . mt_rand(),
        ];
        $result = $this->api->create_tags($tagNames);

        // Iterate through the results to confirm the tags were created.
        foreach ($result as $i => $tag) {
            // Convert to array to check for keys, as assertObjectHasAttribute() will be deprecated in PHPUnit 10.
            $tag = get_object_vars($tag);
            $this->assertArrayHasKey('id', $tag);
            $this->assertArrayHasKey('name', $tag);
            $this->assertArrayHasKey('created_at', $tag);
            $this->assertEquals($tag['name'], $tagNames[$i]);
        }
    }

    /**
     * Test that create_tags() throws a ClientException when creating
     * blank tags.
     *
     * @since   1.1.0
     *
     * @return void
     */
    public function testCreateTagsBlank()
    {
        $this->expectException(GuzzleHttp\Exception\ClientException::class);
        $result = $this->api->create_tags([
            '',
            '',
        ]);
    }

    /**
     * Test that create_tags() throws a ClientException when creating
     * tags that already exists.
     *
     * @since   1.1.0
     *
     * @return void
     */
    public function testCreateTagsThatExist()
    {
        $this->expectException(GuzzleHttp\Exception\ClientException::class);
        $result = $this->api->create_tags([
            $_ENV['CONVERTKIT_API_TAG_NAME'],
            $_ENV['CONVERTKIT_API_TAG_NAME_2'],
        ]);
    }

    /**
     * Test that tag_subscriber() returns the expected data.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testTagSubscriber()
    {
        $result = $this->api->tag_subscriber(
            (int) $_ENV['CONVERTKIT_API_TAG_ID'],
            $this->generateEmailAddress()
        );
        $this->assertInstanceOf('stdClass', $result);
        $this->assertArrayHasKey('subscription', get_object_vars($result));
    }

    /**
     * Test that tag_subscriber() returns the expected data
     * when a first_name parameter is included.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testTagSubscriberWithFirstName()
    {
        $emailAddress = $this->generateEmailAddress();
        $firstName = 'First Name';
        $result = $this->api->tag_subscriber(
            (int) $_ENV['CONVERTKIT_API_TAG_ID'],
            $emailAddress,
            $firstName
        );

        $this->assertInstanceOf('stdClass', $result);
        $this->assertArrayHasKey('subscription', get_object_vars($result));

        // Fetch subscriber from API to confirm the first name was saved.
        $subscriber = $this->api->get_subscriber($result->subscription->subscriber->id);
        $this->assertEquals($subscriber->subscriber->email_address, $emailAddress);
        $this->assertEquals($subscriber->subscriber->first_name, $firstName);
    }

    /**
     * Test that tag_subscriber() returns the expected data
     * when custom field data is included.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testTagSubscriberWithCustomFields()
    {
        $result = $this->api->tag_subscriber(
            (int) $_ENV['CONVERTKIT_API_TAG_ID'],
            $this->generateEmailAddress(),
            'First Name',
            [
                'last_name' => 'Last Name',
            ]
        );

        // Check subscription object returned.
        $this->assertInstanceOf('stdClass', $result);
        $this->assertArrayHasKey('subscription', get_object_vars($result));

        // Fetch subscriber from API to confirm the custom fields were saved.
        $subscriber = $this->api->get_subscriber($result->subscription->subscriber->id);
        $this->assertEquals($subscriber->subscriber->fields->last_name, 'Last Name');
    }

    /**
     * Test that remove_tag_from_subscriber() works.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testRemoveTagFromSubscriber()
    {
        // Tag the subscriber first.
        $result = $this->api->tag_subscriber(
            (int) $_ENV['CONVERTKIT_API_TAG_ID'],
            $this->generateEmailAddress()
        );

        // Get subscriber ID.
        $subscriberID = $result->subscription->subscriber->id;

        // Remove tag from subscriber.
        $result = $this->api->remove_tag_from_subscriber(
            (int) $_ENV['CONVERTKIT_API_TAG_ID'],
            $subscriberID
        );

        // Confirm that the subscriber no longer has the tag.
        $result = $this->api->get_subscriber_tags($subscriberID);
        $this->assertIsArray($result->tags);
        $this->assertEmpty($result->tags);
    }

    /**
     * Test that remove_tag_from_subscriber() throws a ClientException when an invalid
     * tag ID is specified.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testRemoveTagFromSubscriberWithInvalidTagID()
    {
        $this->expectException(GuzzleHttp\Exception\ClientException::class);
        $result = $this->api->remove_tag_from_subscriber(
            12345,
            $_ENV['CONVERTKIT_API_SUBSCRIBER_ID']
        );
    }

    /**
     * Test that remove_tag_from_subscriber() throws a ClientException when an invalid
     * subscriber ID is specified.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testRemoveTagFromSubscriberWithInvalidSubscriberID()
    {
        $this->expectException(GuzzleHttp\Exception\ClientException::class);
        $result = $this->api->remove_tag_from_subscriber(
            (int) $_ENV['CONVERTKIT_API_TAG_ID'],
            12345
        );
    }

    /**
     * Test that remove_tag_from_subscriber() works.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testRemoveTagFromSubscriberByEmail()
    {
        // Tag the subscriber first.
        $email = $this->generateEmailAddress();
        $result = $this->api->tag_subscriber(
            (int) $_ENV['CONVERTKIT_API_TAG_ID'],
            $email
        );

        // Get subscriber ID.
        $subscriberID = $result->subscription->subscriber->id;

        // Remove tag from subscriber.
        $result = $this->api->remove_tag_from_subscriber_by_email(
            (int) $_ENV['CONVERTKIT_API_TAG_ID'],
            $email
        );

        // Confirm that the subscriber no longer has the tag.
        $result = $this->api->get_subscriber_tags($subscriberID);
        $this->assertIsArray($result->tags);
        $this->assertEmpty($result->tags);
    }

    /**
     * Test that remove_tag_from_subscriber() throws a ClientException when an invalid
     * tag ID is specified.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testRemoveTagFromSubscriberByEmailWithInvalidTagID()
    {
        $this->expectException(GuzzleHttp\Exception\ClientException::class);
        $result = $this->api->remove_tag_from_subscriber_by_email(
            12345,
            $_ENV['CONVERTKIT_API_SUBSCRIBER_EMAIL']
        );
    }

    /**
     * Test that get_tag_subscriptions() returns the expected data
     * when a valid Tag ID is specified.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testGetTagSubscriptions()
    {
        $result = $this->api->get_tag_subscriptions((int) $_ENV['CONVERTKIT_API_TAG_ID']);

        // Convert to array to check for keys, as assertObjectHasAttribute() will be deprecated in PHPUnit 10.
        $result = get_object_vars($result);
        $this->assertArrayHasKey('total_subscriptions', $result);
        $this->assertArrayHasKey('page', $result);
        $this->assertArrayHasKey('total_pages', $result);
        $this->assertArrayHasKey('subscriptions', $result);
        $this->assertIsArray($result['subscriptions']);

        // Assert sort order is ascending.
        $this->assertGreaterThanOrEqual(
            $result['subscriptions'][0]->created_at,
            $result['subscriptions'][1]->created_at
        );
    }

    /**
     * Test that get_tag_subscriptions() returns the expected data
     * when a valid Tag ID is specified and the sort order is descending.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testGetTagSubscriptionsWithDescSortOrder()
    {
        $result = $this->api->get_tag_subscriptions((int) $_ENV['CONVERTKIT_API_TAG_ID'], 'desc');

        // Convert to array to check for keys, as assertObjectHasAttribute() will be deprecated in PHPUnit 10.
        $result = get_object_vars($result);
        $this->assertArrayHasKey('total_subscriptions', $result);
        $this->assertArrayHasKey('page', $result);
        $this->assertArrayHasKey('total_pages', $result);
        $this->assertArrayHasKey('subscriptions', $result);
        $this->assertIsArray($result['subscriptions']);

        // Assert sort order.
        $this->assertLessThanOrEqual(
            $result['subscriptions'][0]->created_at,
            $result['subscriptions'][1]->created_at
        );
    }

    /**
     * Test that get_tag_subscriptions() returns the expected data
     * when a valid Tag ID is specified and the subscription status
     * is cancelled.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testGetTagSubscriptionsWithCancelledSubscriberState()
    {
        $result = $this->api->get_tag_subscriptions((int) $_ENV['CONVERTKIT_API_TAG_ID'], 'asc', 'cancelled');

        // Convert to array to check for keys, as assertObjectHasAttribute() will be deprecated in PHPUnit 10.
        $result = get_object_vars($result);
        $this->assertArrayHasKey('total_subscriptions', $result);
        $this->assertGreaterThan(1, $result['total_subscriptions']);
        $this->assertArrayHasKey('page', $result);
        $this->assertArrayHasKey('total_pages', $result);
        $this->assertArrayHasKey('subscriptions', $result);
        $this->assertIsArray($result['subscriptions']);
    }

    /**
     * Test that get_tag_subscriptions() returns the expected data
     * when a valid Tag ID is specified and the page is set to 2.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testGetTagSubscriptionsWithPage()
    {
        $result = $this->api->get_tag_subscriptions((int) $_ENV['CONVERTKIT_API_TAG_ID'], 'asc', 'active', 2);

        // Convert to array to check for keys, as assertObjectHasAttribute() will be deprecated in PHPUnit 10.
        $result = get_object_vars($result);
        $this->assertArrayHasKey('total_subscriptions', $result);
        $this->assertArrayHasKey('page', $result);
        $this->assertEquals($result['page'], 2);
        $this->assertArrayHasKey('total_pages', $result);
        $this->assertArrayHasKey('subscriptions', $result);
        $this->assertIsArray($result['subscriptions']);
    }

    /**
     * Test that get_tag_subscriptions() returns the expected data
     * when a valid Tag ID is specified.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testGetTagSubscriptionsWithInvalidFormID()
    {
        $this->expectException(GuzzleHttp\Exception\ClientException::class);
        $result = $this->api->get_tag_subscriptions(12345);
    }



    ///

    /**
     * Test that get_resources() for Forms returns the expected data.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testGetResourcesForms()
    {
        $result = $this->api->get_resources('forms');
        $this->assertIsArray($result);
    }

    /**
     * Test that get_resources() for Landing Pages returns the expected data.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testGetResourcesLandingPages()
    {
        $result = $this->api->get_resources('landing_pages');
        $this->assertIsArray($result);
    }

    /**
     * Test that get_resources() for Subscription Forms returns the expected data.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testGetResourcesSubscriptionForms()
    {
        $result = $this->api->get_resources('subscription_forms');
        $this->assertIsArray($result);
    }

    /**
     * Test that get_resources() for Tags returns the expected data.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testGetResourcesTags()
    {
        $result = $this->api->get_resources('tags');
        $this->assertIsArray($result);
    }

    /**
     * Test that get_resources() throws a ClientException when an invalid
     * resource type is specified.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testGetResourcesInvalidResourceType()
    {
        $this->expectException(GuzzleHttp\Exception\ClientException::class);
        $result = $this->api->get_resources('invalid-resource-type');
        $this->assertIsArray($result);
    }

    /**
     * Test that add_subscriber_to_form() returns the expected data.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testAddSubscriberToForm()
    {
        $email = $this->generateEmailAddress();
        $result = $this->api->add_subscriber_to_form(
            (int) $_ENV['CONVERTKIT_API_FORM_ID'],
            $email
        );
        $this->assertInstanceOf('stdClass', $result);
        $this->assertArrayHasKey('subscription', get_object_vars($result));
        $this->assertArrayHasKey('id', get_object_vars($result->subscription));
        $this->assertEquals(get_object_vars($result->subscription)['subscribable_id'], $_ENV['CONVERTKIT_API_FORM_ID']);

        // Unsubscribe.
        $this->api->unsubscribe($email);
    }

    /**
     * Test that add_subscriber_to_form() throws a ClientException when an invalid
     * form ID is specified.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testAddSubscriberToFormWithInvalidFormID()
    {
        $this->expectException(GuzzleHttp\Exception\ClientException::class);
        $result = $this->api->add_subscriber_to_form(12345, $this->generateEmailAddress());
    }

    /**
     * Test that add_subscriber_to_form() throws a ClientException when an invalid
     * email address is specified.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testAddSubscriberToFormWithInvalidEmailAddress()
    {
        $this->expectException(GuzzleHttp\Exception\ClientException::class);
        $result = $this->api->add_subscriber_to_form($_ENV['CONVERTKIT_API_FORM_ID'], 'not-an-email-address');
    }

    /**
     * Test that add_subscriber_to_form() returns the expected data
     * when a first_name parameter is included.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testAddSubscriberToFormWithFirstName()
    {
        $emailAddress = $this->generateEmailAddress();
        $firstName = 'First Name';
        $result = $this->api->add_subscriber_to_form(
            $_ENV['CONVERTKIT_API_FORM_ID'],
            $emailAddress,
            $firstName
        );

        $this->assertInstanceOf('stdClass', $result);
        $this->assertArrayHasKey('subscription', get_object_vars($result));

        // Fetch subscriber from API to confirm the first name was saved.
        $subscriber = $this->api->get_subscriber($result->subscription->subscriber->id);
        $this->assertEquals($subscriber->subscriber->email_address, $emailAddress);
        $this->assertEquals($subscriber->subscriber->first_name, $firstName);
    }

    /**
     * Test that add_subscriber_to_form() returns the expected data
     * when custom field data is included.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testAddSubscriberToFormWithCustomFields()
    {
        $result = $this->api->add_subscriber_to_form(
            $_ENV['CONVERTKIT_API_FORM_ID'],
            $this->generateEmailAddress(),
            'First Name',
            [
                'last_name' => 'Last Name',
            ]
        );

        // Check subscription object returned.
        $this->assertInstanceOf('stdClass', $result);
        $this->assertArrayHasKey('subscription', get_object_vars($result));

        // Fetch subscriber from API to confirm the custom fields were saved.
        $subscriber = $this->api->get_subscriber($result->subscription->subscriber->id);
        $this->assertEquals($subscriber->subscriber->fields->last_name, 'Last Name');
    }

    /**
     * Test that add_subscriber_to_form() returns the expected data
     * when custom field data is included.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testAddSubscriberToFormWithTagID()
    {
        $result = $this->api->add_subscriber_to_form(
            $_ENV['CONVERTKIT_API_FORM_ID'],
            $this->generateEmailAddress(),
            'First Name',
            [],
            [
                (int) $_ENV['CONVERTKIT_API_TAG_ID']
            ]
        );

        // Check subscription object returned.
        $this->assertInstanceOf('stdClass', $result);
        $this->assertArrayHasKey('subscription', get_object_vars($result));

        // Fetch subscriber tags from API to confirm the tag saved.
        $subscriberTags = $this->api->get_subscriber_tags($result->subscription->subscriber->id);
        $this->assertEquals($subscriberTags->tags[0]->id, $_ENV['CONVERTKIT_API_TAG_ID']);
    }

    /**
     * Test that get_subscriber_id() returns the expected data.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testGetSubscriberID()
    {
        $subscriber_id = $this->api->get_subscriber_id($_ENV['CONVERTKIT_API_SUBSCRIBER_EMAIL']);
        $this->assertIsInt($subscriber_id);
        $this->assertEquals($subscriber_id, (int) $_ENV['CONVERTKIT_API_SUBSCRIBER_ID']);
    }

    /**
     * Test that get_subscriber_id() throws a ClientException when an invalid
     * email address is specified.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testGetSubscriberIDWithInvalidEmailAddress()
    {
        $this->expectException(\InvalidArgumentException::class);
        $result = $this->api->get_subscriber_id('not-an-email-address');
    }

    /**
     * Test that get_subscriber_id() return false when no subscriber found
     * matching the given email address.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testGetSubscriberIDWithNotSubscribedEmailAddress()
    {
        $result = $this->api->get_subscriber_id('not-a-subscriber@test.com');
        $this->assertFalse($result);
    }

    /**
     * Test that get_subscriber() returns the expected data.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testGetSubscriber()
    {
        $subscriber = $this->api->get_subscriber((int) $_ENV['CONVERTKIT_API_SUBSCRIBER_ID']);
        $this->assertInstanceOf('stdClass', $subscriber);
        $this->assertArrayHasKey('subscriber', get_object_vars($subscriber));
        $this->assertArrayHasKey('id', get_object_vars($subscriber->subscriber));
        $this->assertEquals(
            get_object_vars($subscriber->subscriber)['id'],
            (int) $_ENV['CONVERTKIT_API_SUBSCRIBER_ID']
        );
    }

    /**
     * Test that get_subscriber() throws a ClientException when an invalid
     * subscriber ID is specified.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testGetSubscriberWithInvalidSubscriberID()
    {
        $this->expectException(GuzzleHttp\Exception\ClientException::class);
        $subscriber = $this->api->get_subscriber(12345);
    }

    /**
     * Test that update_subscriber() works when no changes are made.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testUpdateSubscriberWithNoChanges()
    {
        $result = $this->api->update_subscriber($_ENV['CONVERTKIT_API_SUBSCRIBER_ID']);
        $this->assertInstanceOf('stdClass', $result);
        $this->assertArrayHasKey('subscriber', get_object_vars($result));
        $this->assertArrayHasKey('id', get_object_vars($result->subscriber));
        $this->assertEquals(get_object_vars($result->subscriber)['id'], $_ENV['CONVERTKIT_API_SUBSCRIBER_ID']);
    }

    /**
     * Test that update_subscriber() works when updating the subscriber's first name.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testUpdateSubscriberFirstName()
    {
        // Add a subscriber.
        $email = $this->generateEmailAddress();
        $result = $this->api->add_subscriber_to_sequence(
            $_ENV['CONVERTKIT_API_SEQUENCE_ID'],
            $email
        );

        // Get subscriber ID.
        $subscriberID = $result->subscription->subscriber->id;

        // Update subscriber's first name.
        $result = $this->api->update_subscriber(
            $subscriberID,
            'First Name'
        );

        // Confirm the change is reflected in the subscriber.
        $this->assertInstanceOf('stdClass', $result);
        $this->assertArrayHasKey('subscriber', get_object_vars($result));
        $this->assertArrayHasKey('id', get_object_vars($result->subscriber));
        $this->assertEquals(get_object_vars($result->subscriber)['id'], $subscriberID);
        $this->assertEquals(get_object_vars($result->subscriber)['first_name'], 'First Name');

        // Unsubscribe.
        $this->api->unsubscribe($email);
    }

    /**
     * Test that update_subscriber() works when updating the subscriber's email address.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testUpdateSubscriberEmailAddress()
    {
        // Add a subscriber.
        $email = $this->generateEmailAddress();
        $result = $this->api->add_subscriber_to_sequence(
            $_ENV['CONVERTKIT_API_SEQUENCE_ID'],
            $email
        );

        // Get subscriber ID.
        $subscriberID = $result->subscription->subscriber->id;

        // Update subscriber's email address.
        $newEmail = $this->generateEmailAddress();
        $result = $this->api->update_subscriber(
            $subscriberID,
            '',
            $newEmail
        );

        // Confirm the change is reflected in the subscriber.
        $this->assertInstanceOf('stdClass', $result);
        $this->assertArrayHasKey('subscriber', get_object_vars($result));
        $this->assertArrayHasKey('id', get_object_vars($result->subscriber));
        $this->assertEquals(get_object_vars($result->subscriber)['id'], $subscriberID);
        $this->assertEquals(get_object_vars($result->subscriber)['email_address'], $newEmail);

        // Unsubscribe.
        $this->api->unsubscribe($newEmail);
    }

    /**
     * Test that update_subscriber() works when updating the subscriber's custom fields.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testUpdateSubscriberCustomFields()
    {
        // Add a subscriber.
        $email = $this->generateEmailAddress();
        $result = $this->api->add_subscriber_to_sequence(
            $_ENV['CONVERTKIT_API_SEQUENCE_ID'],
            $email
        );

        // Get subscriber ID.
        $subscriberID = $result->subscription->subscriber->id;

        // Update subscriber's email address.
        $result = $this->api->update_subscriber(
            $subscriberID,
            '',
            '',
            [
                'last_name' => 'Last Name',
            ]
        );

        // Confirm the change is reflected in the subscriber.
        $this->assertInstanceOf('stdClass', $result);
        $this->assertArrayHasKey('subscriber', get_object_vars($result));
        $this->assertArrayHasKey('id', get_object_vars($result->subscriber));
        $this->assertEquals(get_object_vars($result->subscriber)['id'], $subscriberID);
        $this->assertEquals($result->subscriber->fields->last_name, 'Last Name');

        // Unsubscribe.
        $this->api->unsubscribe($email);
    }

    /**
     * Test that update_subscriber() throws a ClientException when an invalid
     * subscriber ID is specified.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testUpdateSubscriberWithInvalidSubscriberID()
    {
        $this->expectException(GuzzleHttp\Exception\ClientException::class);
        $subscriber = $this->api->update_subscriber(12345);
    }

    /**
     * Test that unsubscribe() works with a valid subscriber email address.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testUnsubscribe()
    {
        // Add a subscriber.
        $email = $this->generateEmailAddress();
        $result = $this->api->add_subscriber_to_sequence(
            $_ENV['CONVERTKIT_API_SEQUENCE_ID'],
            $email
        );

        // Unsubscribe.
        $result = $this->api->unsubscribe($email);

        // Confirm the change is reflected in the subscriber.
        $this->assertInstanceOf('stdClass', $result);
        $this->assertArrayHasKey('subscriber', get_object_vars($result));
        $this->assertEquals($result->subscriber->email_address, $email);
        $this->assertEquals($result->subscriber->state, 'cancelled');
    }

    /**
     * Test that unsubscribe() throws a ClientException when an email
     * address is specified that is not subscribed.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testUnsubscribeWithNotSubscribedEmailAddress()
    {
        $this->expectException(GuzzleHttp\Exception\ClientException::class);
        $subscriber = $this->api->unsubscribe('not-subscribed@convertkit.com');
    }

    /**
     * Test that unsubscribe() throws a ClientException when an invalid
     * email address is specified.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testUnsubscribeWithInvalidEmailAddress()
    {
        $this->expectException(GuzzleHttp\Exception\ClientException::class);
        $subscriber = $this->api->unsubscribe('invalid-email');
    }

    /**
     * Test that get_subscriber_tags() returns the expected data.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testGetSubscriberTags()
    {
        $subscriber = $this->api->get_subscriber_tags((int) $_ENV['CONVERTKIT_API_SUBSCRIBER_ID']);
        $this->assertInstanceOf('stdClass', $subscriber);
        $this->assertArrayHasKey('tags', get_object_vars($subscriber));
    }

    /**
     * Test that get_subscriber_tags() throws a ClientException when an invalid
     * subscriber ID is specified.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testGetSubscriberTagsWithInvalidSubscriberID()
    {
        $this->expectException(GuzzleHttp\Exception\ClientException::class);
        $subscriber = $this->api->get_subscriber_tags(12345);
    }

    /**
     * Test that create_broadcast(), update_broadcast() and destroy_broadcast() works
     * when specifying valid published_at and send_at values.
     *
     * We do all tests in a single function, so we don't end up with unnecessary Broadcasts remaining
     * on the ConvertKit account when running tests, which might impact
     * other tests that expect (or do not expect) specific Broadcasts.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testCreateUpdateAndDestroyDraftBroadcast()
    {
        // Create a broadcast first.
        $result = $this->api->create_broadcast(
            'Test Subject',
            'Test Content',
            'Test Broadcast from PHP SDK',
        );
        $broadcastID = $result->broadcast->id;

        // Confirm the Broadcast saved.
        $result = get_object_vars($result->broadcast);
        $this->assertArrayHasKey('id', $result);
        $this->assertEquals('Test Subject', $result['subject']);
        $this->assertEquals('Test Content', $result['content']);
        $this->assertEquals('Test Broadcast from PHP SDK', $result['description']);
        $this->assertEquals(null, $result['published_at']);
        $this->assertEquals(null, $result['send_at']);

        // Update the existing broadcast.
        $result = $this->api->update_broadcast(
            $broadcastID,
            'New Test Subject',
            'New Test Content',
            'New Test Broadcast from PHP SDK'
        );

        // Confirm the changes saved.
        $result = get_object_vars($result->broadcast);
        $this->assertArrayHasKey('id', $result);
        $this->assertEquals('New Test Subject', $result['subject']);
        $this->assertEquals('New Test Content', $result['content']);
        $this->assertEquals('New Test Broadcast from PHP SDK', $result['description']);
        $this->assertEquals(null, $result['published_at']);
        $this->assertEquals(null, $result['send_at']);

        // Destroy the broadcast.
        $this->api->destroy_broadcast($broadcastID);
    }

    /**
     * Test that create_broadcast() and destroy_broadcast() works
     * when specifying valid published_at and send_at values.
     *
     * We do both, so we don't end up with unnecessary Broadcasts remaining
     * on the ConvertKit account when running tests, which might impact
     * other tests that expect (or do not expect) specific Broadcasts.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testCreateAndDestroyPublicBroadcastWithValidDates()
    {
        // Create DateTime object.
        $publishedAt = new \DateTime('now');
        $publishedAt->modify('+7 days');
        $sendAt = new \DateTime('now');
        $sendAt->modify('+14 days');

        // Create a broadcast first.
        $result = $this->api->create_broadcast(
            'Test Subject',
            'Test Content',
            'Test Broadcast from PHP SDK',
            true,
            $publishedAt,
            $sendAt
        );
        $broadcastID = $result->broadcast->id;

        // Confirm the Broadcast saved.
        $result = get_object_vars($result->broadcast);
        $this->assertArrayHasKey('id', $result);
        $this->assertEquals('Test Subject', $result['subject']);
        $this->assertEquals('Test Content', $result['content']);
        $this->assertEquals('Test Broadcast from PHP SDK', $result['description']);
        $this->assertEquals(
            $publishedAt->format('Y-m-d') . 'T' . $publishedAt->format('H:i:s') . '.000Z',
            $result['published_at']
        );
        $this->assertEquals(
            $sendAt->format('Y-m-d') . 'T' . $sendAt->format('H:i:s') . '.000Z',
            $result['send_at']
        );

        // Destroy the broadcast.
        $this->api->destroy_broadcast($broadcastID);
    }

    /**
     * Test that get_broadcast() returns the expected data.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testGetBroadcast()
    {
        $result = $this->api->get_broadcast($_ENV['CONVERTKIT_API_BROADCAST_ID']);
        $result = get_object_vars($result->broadcast);
        $this->assertEquals($result['id'], $_ENV['CONVERTKIT_API_BROADCAST_ID']);
    }

    /**
     * Test that get_broadcast() throws a ClientException when an invalid
     * broadcast ID is specified.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testGetBroadcastWithInvalidBroadcastID()
    {
        $this->expectException(GuzzleHttp\Exception\ClientException::class);
        $this->api->get_broadcast(12345);
    }

    /**
     * Test that get_broadcast_stats() returns the expected data.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testGetBroadcastStats()
    {
        $result = $this->api->get_broadcast_stats($_ENV['CONVERTKIT_API_BROADCAST_ID']);
        $result = get_object_vars($result->broadcast);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('stats', $result);
        $this->assertEquals($result['stats']->recipients, 1);
        $this->assertEquals($result['stats']->open_rate, 0);
        $this->assertEquals($result['stats']->click_rate, 0);
        $this->assertEquals($result['stats']->unsubscribes, 0);
        $this->assertEquals($result['stats']->total_clicks, 0);
    }

    /**
     * Test that get_broadcast_stats() throws a ClientException when an invalid
     * broadcast ID is specified.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testGetBroadcastStatsWithInvalidBroadcastID()
    {
        $this->expectException(GuzzleHttp\Exception\ClientException::class);
        $this->api->get_broadcast_stats(12345);
    }

    /**
     * Test that update_broadcast() throws a ClientException when an invalid
     * broadcast ID is specified.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testUpdateBroadcastWithInvalidBroadcastID()
    {
        $this->expectException(GuzzleHttp\Exception\ClientException::class);
        $this->api->update_broadcast(12345);
    }

    /**
     * Test that destroy_broadcast() throws a ClientException when an invalid
     * broadcast ID is specified.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testDestroyBroadcastWithInvalidBroadcastID()
    {
        $this->expectException(GuzzleHttp\Exception\ClientException::class);
        $this->api->destroy_broadcast(12345);
    }

    /**
     * Test that create_webhook() and destroy_webhook() works.
     *
     * We do both, so we don't end up with unnecessary webhooks remaining
     * on the ConvertKit account when running tests.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testCreateAndDestroyWebhook()
    {
        // Create a webhook first.
        $result = $this->api->create_webhook(
            'https://webhook.site/2705fef6-34ef-4252-9c78-d511c540b58d',
            'subscriber.subscriber_activate',
        );
        $ruleID = $result->rule->id;

        // Destroy the webhook.
        $result = $this->api->destroy_webhook($ruleID);
        $this->assertEquals($result->success, true);
    }

    /**
     * Test that create_webhook() and destroy_webhook() works with an event parameter.
     *
     * We do both, so we don't end up with unnecessary webhooks remaining
     * on the ConvertKit account when running tests.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testCreateAndDestroyWebhookWithEventParameter()
    {
        // Create a webhook first.
        $result = $this->api->create_webhook(
            'https://webhook.site/2705fef6-34ef-4252-9c78-d511c540b58d',
            'subscriber.form_subscribe',
            $_ENV['CONVERTKIT_API_FORM_ID']
        );
        $ruleID = $result->rule->id;

        // Destroy the webhook.
        $result = $this->api->destroy_webhook($ruleID);
        $this->assertEquals($result->success, true);
    }

    /**
     * Test that create_webhook() throws an InvalidArgumentException when an invalid
     * event is specified.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testCreateWebhookWithInvalidEvent()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->api->create_webhook('https://webhook.site/2705fef6-34ef-4252-9c78-d511c540b58d', 'invalid.event');
    }

    /**
     * Test that destroy_webhook() throws a ClientException when an invalid
     * rule ID is specified.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testDestroyWebhookWithInvalidRuleID()
    {
        $this->expectException(GuzzleHttp\Exception\ClientException::class);
        $this->api->destroy_webhook(12345);
    }

    /**
     * Test that get_custom_fields() returns the expected data.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testGetCustomFields()
    {
        $result = $this->api->get_custom_fields();
        $this->assertInstanceOf('stdClass', $result);
        $this->assertArrayHasKey('custom_fields', get_object_vars($result));

        // Inspect first custom field.
        $customField = get_object_vars($result->custom_fields[0]);
        $this->assertArrayHasKey('id', $customField);
        $this->assertArrayHasKey('name', $customField);
        $this->assertArrayHasKey('key', $customField);
        $this->assertArrayHasKey('label', $customField);
    }

    /**
     * Test that create_custom_field() works.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testCreateCustomField()
    {
        $label = 'Custom Field ' . mt_rand();
        $result = $this->api->create_custom_field($label);

        $result = get_object_vars($result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('key', $result);
        $this->assertArrayHasKey('label', $result);
        $this->assertEquals($result['label'], $label);

        // Delete custom field.
        $this->api->delete_custom_field($result['id']);
    }

    /**
     * Test that create_custom_field() throws a ClientException when a blank
     * label is specified.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testCreateCustomFieldWithBlankLabel()
    {
        $this->expectException(GuzzleHttp\Exception\ClientException::class);
        $this->api->create_custom_field('');
    }

    /**
     * Test that create_custom_fields() works.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testCreateCustomFields()
    {
        $labels = [
            'Custom Field ' . mt_rand(),
            'Custom Field ' . mt_rand(),
        ];
        $result = $this->api->create_custom_fields($labels);

        // Confirm result is an array comprising of each custom field that was created.
        $this->assertIsArray($result);
        foreach ($result as $index => $customField) {
            // Confirm individual custom field.
            $customField = get_object_vars($customField);
            $this->assertArrayHasKey('id', $customField);
            $this->assertArrayHasKey('name', $customField);
            $this->assertArrayHasKey('key', $customField);
            $this->assertArrayHasKey('label', $customField);

            // Confirm label is correct.
            $this->assertEquals($labels[$index], $customField['label']);

            // Delete custom field as tests passed.
            $this->api->delete_custom_field($customField['id']);
        }
    }

    /**
     * Test that update_custom_field() works.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testUpdateCustomField()
    {
        // Create custom field.
        $label = 'Custom Field ' . mt_rand();
        $result = $this->api->create_custom_field($label);
        $id = $result->id;

        // Change label.
        $newLabel = 'Custom Field ' . mt_rand();
        $this->api->update_custom_field($id, $newLabel);

        // Confirm label changed.
        $customFields = $this->api->get_custom_fields();
        foreach ($customFields->custom_fields as $customField) {
            if ($customField->id === $id) {
                $this->assertEquals($customField->label, $newLabel);
            }
        }

        // Delete custom field as tests passed.
        $this->api->delete_custom_field($id);
    }

    /**
     * Test that update_custom_field() throws a ClientException when an
     * invalid custom field ID is specified.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testUpdateCustomFieldWithInvalidID()
    {
        $this->expectException(GuzzleHttp\Exception\ClientException::class);
        $this->api->update_custom_field(12345, 'Something');
    }

    /**
     * Test that delete_custom_field() works.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testDeleteCustomField()
    {
        // Create custom field.
        $label = 'Custom Field ' . mt_rand();
        $result = $this->api->create_custom_field($label);
        $id = $result->id;

        // Delete custom field as tests passed.
        $this->api->delete_custom_field($id);

        // Confirm custom field no longer exists.
        $customFields = $this->api->get_custom_fields();
        foreach ($customFields->custom_fields as $customField) {
            $this->assertNotEquals($customField->id, $id);
        }
    }

    /**
     * Test that delete_custom_field() throws a ClientException when an
     * invalid custom field ID is specified.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testDeleteCustomFieldWithInvalidID()
    {
        $this->expectException(GuzzleHttp\Exception\ClientException::class);
        $this->api->delete_custom_field(12345);
    }

    /**
     * Test that list_purchases() returns the expected data.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testListPurchases()
    {
        $purchases = $this->api->list_purchases([
            'page' => 1,
        ]);
        $this->assertInstanceOf('stdClass', $purchases);
        $this->assertArrayHasKey('total_purchases', get_object_vars($purchases));
        $this->assertArrayHasKey('page', get_object_vars($purchases));
        $this->assertArrayHasKey('total_pages', get_object_vars($purchases));
        $this->assertArrayHasKey('purchases', get_object_vars($purchases));
    }

    /**
     * Test that get_purchases() returns the expected data.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testGetPurchase()
    {
        // Get ID of first purchase.
        $purchases = $this->api->list_purchases([
            'page' => 1,
        ]);
        $id = $purchases->purchases[0]->id;

        // Get purchase.
        $result = $this->api->get_purchase($id);
        $this->assertInstanceOf('stdClass', $result);
        $this->assertEquals($result->id, $id);
    }

    /**
     * Test that get_purchases() throws a ClientException when an invalid
     * purchase ID is specified.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testGetPurchaseWithInvalidID()
    {
        $this->expectException(GuzzleHttp\Exception\ClientException::class);
        $this->api->get_purchase(12345);
    }

    /**
     * Test that create_purchase() returns the expected data.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testCreatePurchase()
    {
        $purchase = $this->api->create_purchase([
            'purchase' => [
                'transaction_id' => str_shuffle('wfervdrtgsdewrafvwefds'),
                'email_address'  => $this->generateEmailAddress(),
                'first_name'     => 'John',
                'currency'       => 'usd',
                'transaction_time' => date('Y-m-d H:i:s'),
                'subtotal'       => 20.00,
                'tax'            => 2.00,
                'shipping'       => 2.00,
                'discount'       => 3.00,
                'total'          => 21.00,
                'status'         => 'paid',
                'products'       => [
                    [
                        'pid' => 9999,
                        'lid' => 7777,
                        'name' => 'Floppy Disk (512k)',
                        'sku' => '7890-ijkl',
                        'unit_price' => 5.00,
                        'quantity' => 2
                    ],
                    [
                        'pid' => 5555,
                        'lid' => 7778,
                        'name' => 'Telephone Cord (data)',
                        'sku' => 'mnop-1234',
                        'unit_price' => 10.00,
                        'quantity' => 1
                    ],
                ],
            ],
        ]);
        $this->assertInstanceOf('stdClass', $purchase);
        $this->assertArrayHasKey('transaction_id', get_object_vars($purchase));
    }

    /**
     * Test that create_purchase() throws a ClientException when an invalid
     * purchase data is specified.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testCreatePurchaseWithMissingData()
    {
        $this->expectException(GuzzleHttp\Exception\ClientException::class);
        $this->api->create_purchase([
            'invalid-key' => [
                'transaction_id' => str_shuffle('wfervdrtgsdewrafvwefds'),
            ],
        ]);
    }

    /**
     * Test that fetching a legacy form's markup works.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testGetResourceLegacyForm()
    {
        $markup = $this->api->get_resource($_ENV['CONVERTKIT_API_LEGACY_FORM_URL']);

        // Assert that the markup is HTML.
        $this->assertTrue($this->isHtml($markup));

        // Confirm that encoding works correctly.
        $this->assertStringContainsString('Vantar þinn ungling sjálfstraust í stærðfræði?', $markup);
    }

    /**
     * Test that fetching a landing page's markup works.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testGetResourceLandingPage()
    {
        $markup = $this->api->get_resource($_ENV['CONVERTKIT_API_LANDING_PAGE_URL']);

        // Assert that the markup is HTML.
        $this->assertTrue($this->isHtml($markup));

        // Confirm that encoding works correctly.
        $this->assertStringContainsString('Vantar þinn ungling sjálfstraust í stærðfræði?', $markup);
    }

    /**
     * Test that fetching a legacy landing page's markup works.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testGetResourceLegacyLandingPage()
    {
        $markup = $this->api->get_resource($_ENV['CONVERTKIT_API_LEGACY_LANDING_PAGE_URL']);

        // Assert that the markup is HTML.
        $this->assertTrue($this->isHtml($markup));

        // Confirm that encoding works correctly.
        $this->assertStringContainsString('Legacy Landing Page', $markup);
    }

    /**
     * Test that get_resource() throws an InvalidArgumentException when an invalid
     * URL is specified.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testGetResourceInvalidURL()
    {
        $this->expectException(\InvalidArgumentException::class);
        $markup = $this->api->get_resource('not-a-url');
    }

    /**
     * Test that get_resource() throws a ClientException when an inaccessible
     * URL is specified.
     *
     * @since   1.0.0
     *
     * @return void
     */
    public function testGetResourceInaccessibleURL()
    {
        $this->expectException(GuzzleHttp\Exception\ClientException::class);
        $markup = $this->api->get_resource('https://convertkit.com/a/url/that/does/not/exist');
    }

    /**
     * Deletes the src/logs/debug.log file, if it remains following a previous test.
     *
     * @since   1.2.0
     *
     * @return  void
     */
    private function deleteLogFile()
    {
        if (file_exists($this->logFile)) {
            unlink($this->logFile);
        }
    }

    /**
     * Returns the contents of the src/logs/debug.log file.
     *
     * @since   1.2.0
     *
     * @return  string
     */
    private function getLogFileContents()
    {
        // Return blank string if no log file.
        if (!file_exists($this->logFile)) {
            return '';
        }

        // Return log file contents.
        return file_get_contents($this->logFile);
    }

    /**
     * Generates a unique email address for use in a test, comprising of a prefix,
     * date + time and PHP version number.
     *
     * This ensures that if tests are run in parallel, the same email address
     * isn't used for two tests across parallel testing runs.
     *
     * @since   1.0.0
     *
     * @param   string $domain     Domain (default: convertkit.com).
     *
     * @return  string
     */
    private function generateEmailAddress($domain = 'convertkit.com')
    {
        return 'php-sdk-' . date('Y-m-d-H-i-s') . '-php-' . PHP_VERSION_ID . '@' . $domain;
    }

    /**
     * Checks if string is html
     *
     * @since   1.0.0
     *
     * @param   $string Possible HTML.
     * @return  bool
     */
    private function isHtml($string)
    {
        return preg_match("/<[^<]+>/", $string, $m) != 0;
    }
}
