<?php

use PHPUnit\Framework\TestCase;

class ConvertKitAPITest extends TestCase {

	/**
	 * ConvertKit Class Object
	 *
	 * @var object
	 */
	protected $api;

	/**
	 * Test subscribed user email
	 *
	 * @var string
	 */
	protected $test_email;

	/**
	 * Test subscribed user id
	 *
	 * @var string
	 */
	protected $test_user_id;

	/**
	 * Test tag id
	 *
	 * @var int
	 */
	protected $test_tag_id;

	/**
	 * Form url
	 *
	 * @var int
	 */
	protected $test_form_url;

	/**
	 * Form id
	 *
	 * @var int
	 */
	protected $test_form_id;

	/**
	 * Set up the tests.
	 */
	protected function setUp(): void {

		// Load environment credentials from root folder.
		$dotenv = Dotenv\Dotenv::createImmutable(dirname(dirname(__FILE__)));
		$dotenv->load();

		$this->test_email    = $_ENV['CONVERTKIT_API_SUBSCRIBER_EMAIL'];
		$this->test_user_id  = $_ENV['CONVERTKIT_API_SUBSCRIBER_ID'];
		$this->test_form_id  = $_ENV['CONVERTKIT_API_FORM_ID'];
		$this->test_tag_id   = $_ENV['CONVERTKIT_API_TAG_ID'];
		$this->test_form_url = $_ENV['CONVERTKIT_API_LEGACY_FORM_ID'];

		$this->api = new \ConvertKit_API\ConvertKit_API($_ENV['CONVERTKIT_API_KEY'], $_ENV['CONVERTKIT_API_SECRET']);
	}

	/**
	 * Test that a ClientException is thrown when invalid API credentials are supplied,
	 * and that subsequent calls to API methods return false.
	 * 
	 * @since 	1.0.0
	 */
	public function testInvalidAPICredentials()
	{
		$this->expectException(GuzzleHttp\Exception\ClientException::class);
		$api = new \ConvertKit_API\ConvertKit_API('fakeApiKey', 'fakeApiSecret');

		$this->assertFalse($api->get_subscriber_id($this->test_email));
		$this->assertFalse($api->get_subscriber($this->test_user_id));
		$this->assertFalse($api->get_subscriber_tags($this->test_user_id));
	}

	/**
	 * Test that get_account() returns the expected data.
	 * 
	 * @since 	1.0.0
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
	 * Test that get_sequences() returns the expected data.
	 * 
	 * @since 	1.0.0
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
	 * Test that get_sequence_subscriptions() returns the expected data.
	 * 
	 * @since 	1.0.0
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
		$this->assertGreaterThan($result['subscriptions'][0]->created_at, $result['subscriptions'][1]->created_at);
	}

	/**
	 * Test that get_sequence_subscriptions() returns the expected data in descending order.
	 * 
	 * @since 	1.0.0
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
		$this->assertLessThan($result['subscriptions'][0]->created_at, $result['subscriptions'][1]->created_at);
	}

	/**
	 * Test that get_sequence_subscriptions() throws a ClientException when an invalid
	 * sequence ID is specified.
	 * 
	 * @since 	1.0.0
	 */
	public function testGetSequenceSubscriptionsWithInvalidSequenceID()
	{
		$this->expectException(GuzzleHttp\Exception\ClientException::class);
		$result = $this->api->get_sequence_subscriptions(12345);
	}

	/**
	 * Test that get_sequence_subscriptions() throws a ClientException when an invalid
	 * sort order is specified.
	 * 
	 * @since 	1.0.0
	 */
	public function testGetSequenceSubscriptionsWithInvalidSortOrder()
	{
		$this->expectException(GuzzleHttp\Exception\ClientException::class);
		$result = $this->api->get_sequence_subscriptions($_ENV['CONVERTKIT_API_SEQUENCE_ID'], 'invalidSortOrder');
	}

	public function testAddSubscriberToSequence()
	{

	}

	public function testAddSubscriberToSequenceWithInvalidSequenceID()
	{

	}

	public function testAddSubscriberToSequenceWithInvalidEmailAddress()
	{

	}

	public function testAddTag()
	{

	}

	public function testGetResourcesForms()
	{

	}

	public function testGetResourcesLandingPages()
	{
		
	}

	public function testGetResourcesSubscriptionForms()
	{
		
	}

	public function testGetResourcesTags()
	{
		
	}

	public function testGetResourcesInvalidResourceType()
	{
		
	}

	public function testFormSubscribe()
	{

	}

	public function testFormUnsubscribe()
	{

	}

	/**
	 * Get subscriber id by email
	 */
	public function testGetSubscriberID()
	{
		$subscriber_id = $this->api->get_subscriber_id($this->test_email);
		$this->assertInternalType("int", $subscriber_id);
	}

	/**
	 * Get subscriber by id
	 */
	public function testGetSubscriber() {
		$subscriber = $this->api->get_subscriber($this->test_user_id);
		$this->assertInstanceOf('stdClass', $subscriber);
		$this->assertArrayHasKey('subscriber', get_object_vars($subscriber));
		$this->assertArrayHasKey('id', get_object_vars($subscriber->subscriber));
		$this->assertEquals(get_object_vars($subscriber->subscriber)['id'], $this->test_user_id);
	}

	/**
	 * Get subscriber tags
	 */
	public function testGetSubscriberTags() {
		$subscriber = $this->api->get_subscriber_tags($this->test_user_id);
		$this->assertInstanceOf('stdClass', $subscriber);
		$this->assertArrayHasKey('tags', get_object_vars($subscriber));
	}

	public function testListPurchases()
	{

	}

	public function testCreatePurchase()
	{

	}

	public function testGetResource()
	{

	}
}