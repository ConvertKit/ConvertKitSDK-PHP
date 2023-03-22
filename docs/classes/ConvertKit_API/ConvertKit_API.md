# ConvertKit_API

ConvertKit API Class
* Full name: `\ConvertKit_API\ConvertKit_API`

## Constants

| Constant | Visibility | Type | Value |
|:---------|:-----------|:-----|:------|
|`VERSION`|public|string|&#039;1.0.0&#039;|
## Properties

### api_key

ConvertKit API Key

```php
protected string $api_key
```

### api_secret

ConvertKit API Secret

```php
protected string $api_secret
```

### api_version

Version of ConvertKit API

```php
protected string $api_version
```

### api_url_base

ConvertKit API URL

```php
protected string $api_url_base
```

### debug

Debug

```php
protected bool $debug
```

### debug_logger

Debug

```php
protected \Monolog\Logger $debug_logger
```

### client

Guzzle Http Client

```php
protected \GuzzleHttp\Client $client
```

## Methods

### __construct

Constructor for ConvertKitAPI instance

```php
public __construct(string $api_key, string $api_secret, bool $debug = false): mixed
```


**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$api_key` | **string** | ConvertKit API Key. |
| `$api_secret` | **string** | ConvertKit API Secret. |
| `$debug` | **bool** | Log requests to debugger. |


---
### create_log

Add an entry to monologger.

```php
private create_log(string $message): void
```


**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$message` | **string** | Message. |


---
### get_account

Gets the current account

```php
public get_account(): false|mixed
```



---
### get_forms

Gets all forms.

```php
public get_forms(): false|mixed
```



---
### get_landing_pages

Gets all landing pages.

```php
public get_landing_pages(): false|mixed
```



---
### form_subscribe

Adds a subscriber to a form.

```php
public form_subscribe(int $form_id, array<string,string> $options): false|object
```
* **Warning:** this method is **deprecated**. This means that this method will likely be removed in a future version.


**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$form_id` | **int** | Form ID. |
| `$options` | **array<string,string>** | Array of user data (email, name). |


---
### add_subscriber_to_form

Adds a subscriber to a form by email address

```php
public add_subscriber_to_form(int $form_id, string $email, string $first_name = '', array<string,string> $fields = [], array<string,int> $tag_ids = []): false|mixed
```


**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$form_id` | **int** | Form ID. |
| `$email` | **string** | Email Address. |
| `$first_name` | **string** | First Name. |
| `$fields` | **array<string,string>** | Custom Fields. |
| `$tag_ids` | **array<string,int>** | Tag ID(s) to subscribe to. |

**See Also:**

* https://developers.convertkit.com/#add-subscriber-to-a-form 

---
### get_form_subscriptions

List subscriptions to a form

```php
public get_form_subscriptions(int $form_id, string $sort_order = 'asc', string $subscriber_state = 'active', int $page = 1): false|mixed
```


**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$form_id` | **int** | Form ID. |
| `$sort_order` | **string** | Sort Order (asc,desc). |
| `$subscriber_state` | **string** | Subscriber State (active,cancelled). |
| `$page` | **int** | Page. |

**See Also:**

* https://developers.convertkit.com/#list-subscriptions-to-a-form 

---
### get_sequences

Gets all sequences

```php
public get_sequences(): false|mixed
```


**See Also:**

* https://developers.convertkit.com/#list-sequences 

---
### add_subscriber_to_sequence

Adds a subscriber to a sequence by email address

```php
public add_subscriber_to_sequence(int $sequence_id, string $email, string $first_name = '', array<string,string> $fields = [], array<string,int> $tag_ids = []): false|mixed
```


**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$sequence_id` | **int** | Sequence ID. |
| `$email` | **string** | Email Address. |
| `$first_name` | **string** | First Name. |
| `$fields` | **array<string,string>** | Custom Fields. |
| `$tag_ids` | **array<string,int>** | Tag ID(s) to subscribe to. |

**See Also:**

* https://developers.convertkit.com/#add-subscriber-to-a-sequence 

---
### get_sequence_subscriptions

Gets subscribers to a sequence

```php
public get_sequence_subscriptions(int $sequence_id, string $sort_order = 'asc', string $subscriber_state = 'active', int $page = 1): false|mixed
```


**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$sequence_id` | **int** | Sequence ID. |
| `$sort_order` | **string** | Sort Order (asc,desc). |
| `$subscriber_state` | **string** | Subscriber State (active,cancelled). |
| `$page` | **int** | Page. |

**See Also:**

* https://developers.convertkit.com/#list-subscriptions-to-a-sequence 

---
### get_tags

Gets all tags.

```php
public get_tags(): false|mixed
```


**See Also:**

* https://developers.convertkit.com/#list-tags 

---
### create_tag

Creates a tag.

```php
public create_tag(string $tag): false|mixed
```


**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$tag` | **string** | Tag Name. |

**See Also:**

* https://developers.convertkit.com/#create-a-tag 

---
### tag_subscriber

Tags a subscriber with the given existing Tag.

```php
public tag_subscriber(int $tag_id, string $email, string $first_name = '', array<string,string> $fields = []): false|mixed
```


**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$tag_id` | **int** | Tag ID. |
| `$email` | **string** | Email Address. |
| `$first_name` | **string** | First Name. |
| `$fields` | **array<string,string>** | Custom Fields. |

**See Also:**

* https://developers.convertkit.com/#tag-a-subscriber 

---
### add_tag

Adds a tag to a subscriber.

```php
public add_tag(int $tag, array<string,mixed> $options): false|object
```
* **Warning:** this method is **deprecated**. This means that this method will likely be removed in a future version.


**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$tag` | **int** | Tag ID. |
| `$options` | **array<string,mixed>** | Array of user data. |

**See Also:**

* https://developers.convertkit.com/#tag-a-subscriber 

---
### remove_tag_from_subscriber

Removes a tag from a subscriber.

```php
public remove_tag_from_subscriber(int $tag_id, int $subscriber_id): false|mixed
```


**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$tag_id` | **int** | Tag ID. |
| `$subscriber_id` | **int** | Subscriber ID. |

**See Also:**

* https://developers.convertkit.com/#remove-tag-from-a-subscriber 

---
### remove_tag_from_subscriber_by_email

Removes a tag from a subscriber by email address.

```php
public remove_tag_from_subscriber_by_email(int $tag_id, string $email): false|mixed
```


**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$tag_id` | **int** | Tag ID. |
| `$email` | **string** | Subscriber email address. |

**See Also:**

* https://developers.convertkit.com/#remove-tag-from-a-subscriber-by-email 

---
### get_tag_subscriptions

List subscriptions to a tag

```php
public get_tag_subscriptions(int $tag_id, string $sort_order = 'asc', string $subscriber_state = 'active', int $page = 1): false|mixed
```


**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$tag_id` | **int** | Tag ID. |
| `$sort_order` | **string** | Sort Order (asc,desc). |
| `$subscriber_state` | **string** | Subscriber State (active,cancelled). |
| `$page` | **int** | Page. |

**See Also:**

* https://developers.convertkit.com/#list-subscriptions-to-a-tag 

---
### get_resources

Gets a resource index
Possible resources: forms, landing_pages, subscription_forms, tags

```php
public get_resources(string $resource): array<int|string,mixed|\stdClass>
```
GET /{$resource}/

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$resource` | **string** | Resource type. |

**Return Value:**

API response


---
### get_subscriber_id

Get the ConvertKit subscriber ID associated with email address if it exists.

```php
public get_subscriber_id(string $email_address): false|int
```
Return false if subscriber not found.

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$email_address` | **string** | Email Address. |


---
### get_subscriber

Get subscriber by id

```php
public get_subscriber(int $subscriber_id): false|int
```


**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$subscriber_id` | **int** | Subscriber ID. |

**See Also:**

* https://developers.convertkit.com/#view-a-single-subscriber 

---
### update_subscriber

Updates the information for a single subscriber.

```php
public update_subscriber(int $subscriber_id, string $first_name = '', string $email_address = '', array<string,string> $fields = []): false|mixed
```


**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$subscriber_id` | **int** | Existing Subscriber ID. |
| `$first_name` | **string** | New First Name. |
| `$email_address` | **string** | New Email Address. |
| `$fields` | **array<string,string>** | Updated Custom Fields. |

**See Also:**

* https://developers.convertkit.com/#update-subscriber 

---
### unsubscribe

Unsubscribe an email address from all forms and sequences.

```php
public unsubscribe(string $email): false|object
```


**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$email` | **string** | Email Address. |

**See Also:**

* https://developers.convertkit.com/#unsubscribe-subscriber 

---
### form_unsubscribe

Remove subscription from a form

```php
public form_unsubscribe(array<string,string> $options): false|object
```


**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$options` | **array<string,string>** | Array of user data (email). |

**See Also:**

* https://developers.convertkit.com/#unsubscribe-subscriber 

---
### get_subscriber_tags

Get a list of the tags for a subscriber.

```php
public get_subscriber_tags(int $subscriber_id): false|array<int,\stdClass>
```


**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$subscriber_id` | **int** | Subscriber ID. |

**See Also:**

* https://developers.convertkit.com/#list-tags-for-a-subscriber 

---
### get_broadcasts

Gets a list of broadcasts.

```php
public get_broadcasts(): false|array<int,\stdClass>
```


**See Also:**

* https://developers.convertkit.com/#list-broadcasts 

---
### create_broadcast

Creates a broadcast.

```php
public create_broadcast(string $subject = '', string $content = '', string $description = '', bool $public = false, \DateTime $published_at = null, \DateTime $send_at = null, string $email_address = '', string $email_layout_template = '', string $thumbnail_alt = '', string $thumbnail_url = ''): false|object
```


**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$subject` | **string** | The broadcast email&#039;s subject. |
| `$content` | **string** | The broadcast&#039;s email HTML content. |
| `$description` | **string** | An internal description of this broadcast. |
| `$public` | **bool** | Specifies whether or not this is a public post. |
| `$published_at` | **\DateTime** | Specifies the time that this post was published (applicable<br />only to public posts). |
| `$send_at` | **\DateTime** | Time that this broadcast should be sent; leave blank to create<br />a draft broadcast. If set to a future time, this is the time that<br />the broadcast will be scheduled to send. |
| `$email_address` | **string** | Sending email address; leave blank to use your account&#039;s<br />default sending email address. |
| `$email_layout_template` | **string** | Name of the email template to use; leave blank to use your<br />account&#039;s default email template. |
| `$thumbnail_alt` | **string** | Specify the ALT attribute of the public thumbnail image<br />(applicable only to public posts). |
| `$thumbnail_url` | **string** | Specify the URL of the thumbnail image to accompany the broadcast<br />post (applicable only to public posts). |

**See Also:**

* https://developers.convertkit.com/#create-a-broadcast 

---
### get_broadcast

Retrieve a specific broadcast.

```php
public get_broadcast(int $id): false|object
```


**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$id` | **int** | Broadcast ID. |

**See Also:**

* https://developers.convertkit.com/#retrieve-a-specific-broadcast 

---
### get_broadcast_stats

Get the statistics (recipient count, open rate, click rate, unsubscribe count,
total clicks, status, and send progress) for a specific broadcast.

```php
public get_broadcast_stats(int $id): false|object
```


**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$id` | **int** | Broadcast ID. |

**See Also:**

* https://developers.convertkit.com/#retrieve-a-specific-broadcast 

---
### update_broadcast

Updates a broadcast.

```php
public update_broadcast(int $id, string $subject = '', string $content = '', string $description = '', bool $public = false, \DateTime $published_at = null, \DateTime $send_at = null, string $email_address = '', string $email_layout_template = '', string $thumbnail_alt = '', string $thumbnail_url = ''): false|object
```


**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$id` | **int** | Broadcast ID. |
| `$subject` | **string** | The broadcast email&#039;s subject. |
| `$content` | **string** | The broadcast&#039;s email HTML content. |
| `$description` | **string** | An internal description of this broadcast. |
| `$public` | **bool** | Specifies whether or not this is a public post. |
| `$published_at` | **\DateTime** | Specifies the time that this post was published (applicable<br />only to public posts). |
| `$send_at` | **\DateTime** | Time that this broadcast should be sent; leave blank to create<br />a draft broadcast. If set to a future time, this is the time that<br />the broadcast will be scheduled to send. |
| `$email_address` | **string** | Sending email address; leave blank to use your account&#039;s<br />default sending email address. |
| `$email_layout_template` | **string** | Name of the email template to use; leave blank to use your<br />account&#039;s default email template. |
| `$thumbnail_alt` | **string** | Specify the ALT attribute of the public thumbnail image<br />(applicable only to public posts). |
| `$thumbnail_url` | **string** | Specify the URL of the thumbnail image to accompany the broadcast<br />post (applicable only to public posts). |

**See Also:**

* https://developers.convertkit.com/#create-a-broadcast 

---
### destroy_broadcast

Deletes an existing broadcast.

```php
public destroy_broadcast(int $id): false|object
```


**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$id` | **int** | Broadcast ID. |

**See Also:**

* https://developers.convertkit.com/#destroy-webhook 

---
### create_webhook

Creates a webhook that will be called based on the chosen event types.

```php
public create_webhook(string $url, string $event, string $parameter = ''): false|object
```


**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$url` | **string** | URL to receive event. |
| `$event` | **string** | Event to subscribe to. |
| `$parameter` | **string** | Optional parameter depending on the event. |

**See Also:**

* https://developers.convertkit.com/#create-a-webhook 

---
### destroy_webhook

Deletes an existing webhook.

```php
public destroy_webhook(int $rule_id): false|object
```


**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$rule_id` | **int** | Rule ID. |

**See Also:**

* https://developers.convertkit.com/#destroy-webhook 

---
### get_custom_fields

List custom fields.

```php
public get_custom_fields(): false|object
```


**See Also:**

* https://developers.convertkit.com/#list-fields 

---
### create_custom_field

Creates a custom field.

```php
public create_custom_field(string $label): false|object
```


**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$label` | **string** | Custom Field label. |

**See Also:**

* https://developers.convertkit.com/#create-field 

---
### create_custom_fields

Creates multiple custom fields.

```php
public create_custom_fields(string[] $labels): false|object
```


**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$labels` | **string[]** | Custom Fields labels. |

**See Also:**

* https://developers.convertkit.com/#create-field 

---
### update_custom_field

Updates an existing custom field.

```php
public update_custom_field(int $id, string $label): false|object
```


**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$id` | **int** | Custom Field ID. |
| `$label` | **string** | Updated Custom Field label. |

**See Also:**

* https://developers.convertkit.com/#update-field 

---
### delete_custom_field

Deletes an existing custom field.

```php
public delete_custom_field(int $id): false|object
```


**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$id` | **int** | Custom Field ID. |

**See Also:**

* https://developers.convertkit.com/#destroy-field 

---
### list_purchases

List purchases.

```php
public list_purchases(array<string,string> $options): false|object
```


**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$options` | **array<string,string>** | Request options. |

**See Also:**

* https://developers.convertkit.com/#list-purchases 

---
### get_purchase

Retuns a specific purchase.

```php
public get_purchase(int $purchase_id): false|object
```


**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$purchase_id` | **int** | Purchase ID. |

**See Also:**

* https://developers.convertkit.com/#retrieve-a-specific-purchase 

---
### create_purchase

Creates a purchase.

```php
public create_purchase(array<string,string> $options): false|object
```


**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$options` | **array<string,string>** | Purchase data. |

**See Also:**

* https://developers.convertkit.com/#create-a-purchase 

---
### get_resource

Get markup from ConvertKit for the provided $url.

```php
public get_resource(string $url): false|string
```
Supports legacy forms and legacy landing pages.
Forms and Landing Pages should be embedded using the supplied JS embed script in
the API response when using get_resources().

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$url` | **string** | URL of HTML page. |


---
### convert_relative_to_absolute_urls

Converts any relative URls to absolute, fully qualified HTTP(s) URLs for the given
DOM Elements.

```php
private convert_relative_to_absolute_urls(\DOMNodeList<\DOMElement> $elements, string $attribute, string $url): void
```


**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$elements` | **\DOMNodeList<\DOMElement>** | Elements. |
| `$attribute` | **string** | HTML Attribute. |
| `$url` | **string** | Absolute URL to prepend to relative URLs. |


---
### strip_html_head_body_tags

Strips <html>, <head> and <body> opening and closing tags from the given markup,
as well as the Content-Type meta tag we might have added in get_html().

```php
private strip_html_head_body_tags(string $markup): string
```


**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$markup` | **string** | HTML Markup. |

**Return Value:**

HTML Markup


---
### get

Performs a GET request to the API.

```php
public get(string $endpoint, array<string,int|string|array<string,int|string>> $args = []): false|mixed
```


**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$endpoint` | **string** | API Endpoint. |
| `$args` | **array<string,int|string|array<string,int|string>>** | Request arguments. |


---
### post

Performs a POST request to the API.

```php
public post(string $endpoint, array<string,bool|int|string|array<int|string,int|string>> $args = []): false|mixed
```


**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$endpoint` | **string** | API Endpoint. |
| `$args` | **array<string,bool|int|string|array<int|string,int|string>>** | Request arguments. |


---
### put

Performs a PUT request to the API.

```php
public put(string $endpoint, array<string,bool|int|string|array<string,int|string>> $args = []): false|mixed
```


**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$endpoint` | **string** | API Endpoint. |
| `$args` | **array<string,bool|int|string|array<string,int|string>>** | Request arguments. |


---
### delete

Performs a DELETE request to the API.

```php
public delete(string $endpoint, array<string,int|string|array<string,int|string>> $args = []): false|mixed
```


**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$endpoint` | **string** | API Endpoint. |
| `$args` | **array<string,int|string|array<string,int|string>>** | Request arguments. |


---
### make_request

Performs an API request using Guzzle.

```php
public make_request(string $endpoint, string $method, array<string,bool|int|string|array<int|string,int|string>> $args = []): false|mixed
```


**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$endpoint` | **string** | API Endpoint. |
| `$method` | **string** | Request method. |
| `$args` | **array<string,bool|int|string|array<int|string,int|string>>** | Request arguments. |


---


