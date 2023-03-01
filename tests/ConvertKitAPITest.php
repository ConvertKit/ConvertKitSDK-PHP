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
	 * @dataProvider inputGetResourcesArguments
	 * @expectedException InvalidArgumentException
	 *
	 * @param $input
	 */
	public function testGetResourcesArguments($input) {
		$this->api->get_resources($input);
	}

	/**
	 * Data provider for @testGetResourcesArguments
	 *
	 * @return array
	 */
	public function inputGetResourcesArguments() {
		return [
			[2],
			[['2', '1']],
			[new stdClass()]
		];
	}

	/**
	 * @dataProvider inputGetSubscriberId
	 * @expectedException InvalidArgumentException
	 *
	 * @param $input
	 */
	public function testArgumentsGetSubscriberId($input) {
		$this->api->get_subscriber_id($input);
	}

	/**
	 * Data provider for @testGetSubscriberId
	 *
	 * @return array
	 */
	public function inputGetSubscriberId() {
		return [
			[2],
			[['2', '1']],
			[new stdClass()],
			['teststring'],
			['teststring@']
		];
	}

	/**
	 * @dataProvider inputGetSubscriber
	 * @expectedException InvalidArgumentException
	 *
	 * @param $input
	 */
	public function testArgumentsGetSubscriber($input) {
		$this->api->get_subscriber($input);
	}

	/**
	 * Data provider for @testGetSubscriber
	 *
	 * @return array
	 */
	public function inputGetSubscriber() {
		return [
			[['2', '1']],
			[new stdClass()],
			['teststring'],
			[1.2],
			[-10],
		];
	}

	/**
	 * @dataProvider inputAddTag
	 * @expectedException InvalidArgumentException
	 *
	 * @param $tag
	 * @param $options
	 */
	public function testArgumentsAddTag($tag, $options) {
		$this->api->add_tag($tag, $options);
	}

	/**
	 * Data provider for @testAddTag
	 *
	 * @return array
	 */
	public function inputAddTag() {
		return [
			[['2', '1'], 1],
			[new stdClass(), 2],
			['teststring', 3],
			[3, 3],
		];
	}

	public function testIncorrectApiData() {
		$api_key = 'test';
		$api_secret =  'test';

		$test_client = new \ConvertKit_API\ConvertKit_API($api_key, $api_secret);
		$this->assertFalse($test_client->get_subscriber_id($this->test_email));
		$this->assertFalse($test_client->get_subscriber($this->test_user_id));
		$this->assertFalse($test_client->get_subscriber_tags($this->test_user_id));
	}

	/**
	 * Get subscriber id by email
	 */
	public function testGetSubscriberId() {
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

	/**
	 * Subscribe and unsubscribe from form
	 */
	public function testUserActions() {

		$random_email = str_shuffle('1234567890') . 'test@growdevelopment.com';

		/*
		 * Subscribe
		 */
		$options = [
			'email'      => $random_email,
			'name'       => 'Full Name',
			'first_name' => 'First Name',
			'tags'       => $this->test_tag_id,
			'fields'     => [
				'phone' => 134567891243,
				'shirt_size' => 'M',
				'website_url' => 'testurl.com'
			]
		];

		$subscribed = $this->api->form_subscribe($this->test_form_id, $options);
		$this->assertInstanceOf('stdClass', $subscribed);
		$this->assertArrayHasKey('subscription', get_object_vars($subscribed));
		$this->assertArrayHasKey('id', get_object_vars($subscribed->subscription));
		$this->assertEquals(get_object_vars($subscribed->subscription)['subscribable_id'], $this->test_form_id);

		/*
		 * Add tag
		 */
		$added_tag = $this->api->add_tag($this->test_tag_id, [
			'email' => $random_email
		]);
		$this->assertInstanceOf('stdClass', $added_tag);
		$this->assertArrayHasKey('subscription', get_object_vars($added_tag));
		$this->assertArrayHasKey('id', get_object_vars($added_tag->subscription));
		$this->assertEquals(get_object_vars($added_tag->subscription)['subscribable_id'], $this->test_tag_id);
		$this->assertEquals(get_object_vars($added_tag->subscription)['subscribable_type'], 'tag');

		/*
		 * Purchase
		 */
		$purchase_options = [
			'purchase' => [
				'email_address'  => $random_email,
				'transaction_id' => str_shuffle('wfervdrtgsdewrafvwefds'),
				'subtotal'       => 20.00,
				'tax'            => 2.00,
				'shipping'       => 2.00,
				'discount'       => 3.00,
				'total'          => 21.00,
				'status'         => 'paid',
				'products'       => array(
					0 => array(
						'name' => 'Floppy Disk (512k)',
						'sku' => '7890-ijkl',
						'unit_price' => 5.00,
						'quantity' => 2
					)
				)
			]
		];
		$purchase = $this->api->create_purchase($purchase_options);
		$this->assertInstanceOf('stdClass', $purchase);
		$this->assertArrayHasKey('transaction_id', get_object_vars($purchase));

		/*
		 * Unsubscribe
		 */
		$unsubscribed = $this->api->form_unsubscribe([
			'email' => $random_email
		]);

		$this->assertInstanceOf('stdClass', $unsubscribed);
		$this->assertArrayHasKey('subscriber', get_object_vars($unsubscribed));
		$this->assertArrayHasKey('email_address', get_object_vars($unsubscribed->subscriber));
		$this->assertEquals(get_object_vars($unsubscribed->subscriber)['email_address'], $random_email);

	}

	/**
	 * List purchases
	 */
	public function testListPurchases() {

		$list_purchases = $this->api->list_purchases(['page' => 1]);
		$this->assertInstanceOf('stdClass', $list_purchases);
		$this->assertArrayHasKey('total_purchases', get_object_vars($list_purchases));
		$this->assertArrayHasKey('page', get_object_vars($list_purchases));
		$this->assertArrayHasKey('total_pages', get_object_vars($list_purchases));
		$this->assertArrayHasKey('purchases', get_object_vars($list_purchases));

	}

	/**
	 * Get resources
	 */
	public function testGetResources() {

		$resources = ['forms', 'landing_pages', 'tags'];

		foreach ($resources as $resource) {
			$get_resources = $this->api->get_resources($resource);
			$this->assertTrue(is_array($get_resources) || empty($get_resources));
			if(count($get_resources) > 0) {
				$get_resource = $get_resources[0];
				$this->assertInstanceOf('stdClass', $get_resource);
				$this->assertArrayHasKey('id', get_object_vars($get_resource));
				$this->assertArrayHasKey('name', get_object_vars($get_resource));
			}
		}

	}

	/**
	 * Get subscription forms
	 */
	public function testGetLandingPages() {
		$landing_pages = $this->api->get_resources('subscription_forms');
		$this->assertTrue(is_array($landing_pages) || empty($landing_pages));
	}

	/**
	 * Get resource by url
	 */
	public function testGetResource() {
		$markup = $this->api->get_resource($this->test_form_url);
		$this->assertTrue($this->isHtml($markup));
	}

	/**
	 * Checks if string is html
	 *
	 * @param $string
	 *
	 * @return bool
	 */
	protected static function isHtml($string) {
		return preg_match("/<[^<]+>/",$string,$m) != 0;
	}

}