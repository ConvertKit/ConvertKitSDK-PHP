<?php

use PHPUnit\Framework\TestCase;

class ConvertKitAPITest extends TestCase {

	/**
	 * ConvertKit Class Object
	 *
	 * @var object
	 * 
	 * @since 	1.0.0
	 */
	protected $api;

	/**
	 * Load .env configuration into $_ENV superglobal, and initialize the API
	 * class before each test.
	 * 
	 * @since 	1.0.0
	 */
	protected function setUp(): void {

		// Load environment credentials from root folder.
		$dotenv = Dotenv\Dotenv::createImmutable(dirname(dirname(__FILE__)));
		$dotenv->load();

		// Setup API.
		$this->api = new \ConvertKit_API\ConvertKit_API($_ENV['CONVERTKIT_API_KEY'], $_ENV['CONVERTKIT_API_SECRET']);
	}

	/**
	 * Test that a ClientException is thrown when invalid API credentials are supplied.
	 * 
	 * @since 	1.0.0
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

	/**
	 * Test that add_subscriber_to_sequence() returns the expected data.
	 * 
	 * @since 	1.0.0
	 */
	public function testAddSubscriberToSequence()
	{
		$result = $this->api->add_subscriber_to_sequence($_ENV['CONVERTKIT_API_SEQUENCE_ID'], $this->generateEmailAddress());
		$this->assertInstanceOf('stdClass', $result);
		$this->assertArrayHasKey('subscription', get_object_vars($result));
	}

	/**
	 * Test that add_subscriber_to_sequence() throws a ClientException when an invalid
	 * sequence is specified.
	 * 
	 * @since 	1.0.0
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
	 * @since 	1.0.0
	 */
	public function testAddSubscriberToSequenceWithInvalidEmailAddress()
	{
		$this->expectException(GuzzleHttp\Exception\ClientException::class);
		$result = $this->api->add_subscriber_to_sequence($_ENV['CONVERTKIT_API_SEQUENCE_ID'], 'not-an-email-address');
	}

	/**
	 * Test that add_tag() returns the expected data.
	 * 
	 * @since 	1.0.0
	 */
	public function testAddTag()
	{
		$result = $this->api->add_tag((int) $_ENV['CONVERTKIT_API_TAG_ID'], [
			'email' => $this->generateEmailAddress(),
		]);
		$this->assertInstanceOf('stdClass', $result);
		$this->assertArrayHasKey('subscription', get_object_vars($result));
	}

	/**
	 * Test that get_resources() for Forms returns the expected data.
	 * 
	 * @since 	1.0.0
	 */
	public function testGetResourcesForms()
	{
		$result = $this->api->get_resources('forms');
		$this->assertIsArray($result);
	}

	/**
	 * Test that get_resources() for Landing Pages returns the expected data.
	 * 
	 * @since 	1.0.0
	 */
	public function testGetResourcesLandingPages()
	{
		$result = $this->api->get_resources('landing_pages');
		$this->assertIsArray($result);
	}

	/**
	 * Test that get_resources() for Subscription Forms returns the expected data.
	 * 
	 * @since 	1.0.0
	 */
	public function testGetResourcesSubscriptionForms()
	{
		$result = $this->api->get_resources('subscription_forms');
		$this->assertIsArray($result);
	}

	/**
	 * Test that get_resources() for Tags returns the expected data.
	 * 
	 * @since 	1.0.0
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
	 * @since 	1.0.0
	 */
	public function testGetResourcesInvalidResourceType()
	{
		$this->expectException(GuzzleHttp\Exception\ClientException::class);
		$result = $this->api->get_resources('invalid-resource-type');
		$this->assertIsArray($result);
	}

	/**
	 * Test that form_subscribe() and form_unsubscribe() returns the expected data.
	 * 
	 * @since 	1.0.0
	 */
	public function testFormSubscribe()
	{
		// Subscribe.
		$email = $this->generateEmailAddress();
		$result = $this->api->form_subscribe((int) $_ENV['CONVERTKIT_API_FORM_ID'], [
			'email' =>  $email,
		]);
		$this->assertInstanceOf('stdClass', $result);
		$this->assertArrayHasKey('subscription', get_object_vars($result));
		$this->assertArrayHasKey('id', get_object_vars($result->subscription));
		$this->assertEquals(get_object_vars($result->subscription)['subscribable_id'], $_ENV['CONVERTKIT_API_FORM_ID']);

		// Unsubscribe.
		$result = $this->api->form_unsubscribe([
			'email' =>  $email,
		]);
		$this->assertInstanceOf('stdClass', $result);
		$this->assertArrayHasKey('subscriber', get_object_vars($result));
		$this->assertArrayHasKey('email_address', get_object_vars($result->subscriber));
		$this->assertEquals(get_object_vars($result->subscriber)['email_address'], $email);
	}

	/**
	 * Test that form_subscribe() throws a ClientException when an invalid
	 * form ID is specified.
	 * 
	 * @since 	1.0.0
	 */
	public function testFormSubscribeWithInvalidFormID()
	{
		$this->expectException(GuzzleHttp\Exception\ClientException::class);
		$result = $this->api->form_subscribe(12345, [
			'email' =>  $this->generateEmailAddress(),
		]);
	}



	/**
	 * Test that get_subscriber() returns the expected data.
	 * 
	 * @since 	1.0.0
	 */
	public function testGetSubscriber()
	{
		$subscriber = $this->api->get_subscriber((int) $_ENV['CONVERTKIT_API_SUBSCRIBER_ID']);
		$this->assertInstanceOf('stdClass', $subscriber);
		$this->assertArrayHasKey('subscriber', get_object_vars($subscriber));
		$this->assertArrayHasKey('id', get_object_vars($subscriber->subscriber));
		$this->assertEquals(get_object_vars($subscriber->subscriber)['id'], (int) $_ENV['CONVERTKIT_API_SUBSCRIBER_ID']);
	}

	/**
	 * Test that get_subscriber() throws a ClientException when an invalid
	 * subscriber ID is specified.
	 * 
	 * @since 	1.0.0
	 */
	public function testGetSubscriberWithInvalidSubscriberID()
	{
		$this->expectException(GuzzleHttp\Exception\ClientException::class);
		$subscriber = $this->api->get_subscriber(12345);
	}

	/**
	 * Test that get_subscriber_tags() returns the expected data.
	 * 
	 * @since 	1.0.0
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
	 * @since 	1.0.0
	 */
	public function testGetSubscriberTagsWithInvalidSubscriberID()
	{
		$this->expectException(GuzzleHttp\Exception\ClientException::class);
		$subscriber = $this->api->get_subscriber_tags(12345);
	}

	/**
	 * Test that list_purchases() returns the expected data.
	 * 
	 * @since 	1.0.0
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
	 * Test that create_purchase() returns the expected data.
	 * 
	 * @since 	1.0.0
	 */
	public function testCreatePurchase()
	{
		$purchase = $this->api->create_purchase([
			'purchase' => [
				'transaction_id' => str_shuffle('wfervdrtgsdewrafvwefds'),
				'email_address'  => $this->generateEmailAddress(),
				'first_name'     => 'John',
				'currency'		 => 'usd',
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
	 * Test that fetching a URL works.
	 * 
	 * @since 	1.0.0
	 */
	public function testGetResource()
	{
		$markup = $this->api->get_resource($_ENV['CONVERTKIT_API_LEGACY_LANDING_PAGE_URL']);
		$this->assertTrue($this->isHtml($markup));
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
	 */
	private function generateEmailAddress($domain = 'convertkit.com')
	{
		return 'wordpress-' . date( 'Y-m-d-H-i-s' ) . '-php-' . PHP_VERSION_ID . '@' . $domain;
	}

	/**
	 * Checks if string is html
	 * 
	 * @since 	1.0.0
	 *
	 * @param 	$string Possible HTML.
	 * @return 	bool
	 */
	private function isHtml($string) {
		return preg_match("/<[^<]+>/",$string,$m) != 0;
	}
}