<?php

// @formatter:off
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models\Dealer{
/**
 * App\Models\Dealer\ViewedDealer
 *
 * @property int $id
 * @property string $name
 * @property int $dealer_id
 * @property int $inventory_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Database\Factories\Dealer\ViewedDealerFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|ViewedDealer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ViewedDealer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ViewedDealer query()
 * @method static \Illuminate\Database\Eloquent\Builder|ViewedDealer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ViewedDealer whereDealerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ViewedDealer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ViewedDealer whereInventoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ViewedDealer whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ViewedDealer whereUpdatedAt($value)
 */
	class ViewedDealer extends \Eloquent {}
}

namespace App\Models\Geolocation{
/**
 * App\Models\Geolocation\Geolocation
 *
 * @property int $id
 * @property string|null $zip
 * @property string|null $latitude
 * @property string|null $longitude
 * @property string|null $city
 * @property string|null $state
 * @property string $country
 * @method static \Illuminate\Database\Eloquent\Builder|Geolocation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Geolocation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Geolocation query()
 * @method static \Illuminate\Database\Eloquent\Builder|Geolocation whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Geolocation whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Geolocation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Geolocation whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Geolocation whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Geolocation whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Geolocation whereZip($value)
 */
	class Geolocation extends \Eloquent {}
}

namespace App\Models\Glossary{
/**
 * App\Models\Glossary\Glossary
 *
 * @property int $id
 * @property string $denomination
 * @property string|null $short_description
 * @property string|null $long_description
 * @property string|null $type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Glossary newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Glossary newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Glossary query()
 * @method static \Illuminate\Database\Eloquent\Builder|Glossary whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Glossary whereDenomination($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Glossary whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Glossary whereLongDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Glossary whereShortDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Glossary whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Glossary whereUpdatedAt($value)
 */
	class Glossary extends \Eloquent {}
}

namespace App\Models\Inventory{
/**
 * App\Models\Inventory\InventoryLog
 *
 * @property int                      $id
 * @property int                      $trailercentral_id the inventory id in the TrailerCentral DB
 * @property string                   $event             ['created'|'updated'|'price-changed']
 * @property string                   $status            ['available'|'sold']
 * @property string                   $vin
 * @property string                   $brand
 * @property string                   $manufacturer
 * @property numeric                  $price
 * @property array                    $meta              json data
 * @property DateTimeInterface|string $created_at
 * @method static InventoryLogFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|InventoryLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|InventoryLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|InventoryLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|InventoryLog whereBrand($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InventoryLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InventoryLog whereEvent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InventoryLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InventoryLog whereManufacturer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InventoryLog whereMeta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InventoryLog wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InventoryLog whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InventoryLog whereTrailercentralId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InventoryLog whereVin($value)
 */
	class InventoryLog extends \Eloquent {}
}

namespace App\Models\Leads{
/**
 * App\Models\Leads\LeadLog
 *
 * @property int                      $id
 * @property int                      $trailercentral_id the inventory id in the TrailerCentral DB
 * @property string                   $first_name
 * @property string                   $last_name
 * @property string                   $email_address
 * @property array                    $meta              json data
 * @property DateTimeInterface|string $submitted_at
 * @property DateTimeInterface|string $created_at
 * @method static LeadLogFactory factory(...$parameters)
 * @property string|null $brand
 * @property string $manufacturer
 * @property string|null $vin
 * @method static \Illuminate\Database\Eloquent\Builder|LeadLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadLog whereBrand($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadLog whereEmailAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadLog whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadLog whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadLog whereManufacturer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadLog whereMeta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadLog whereSubmittedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadLog whereTrailercentralId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadLog whereVin($value)
 */
	class LeadLog extends \Eloquent {}
}

namespace App\Models\Page{
/**
 * App\Models\Page\Page
 *
 * @property int $id
 * @property string $name
 * @property string $url
 * @property string|null $created_at
 * @property string|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Page newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Page newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Page query()
 * @method static \Illuminate\Database\Eloquent\Builder|Page whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Page whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Page whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Page whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Page whereUrl($value)
 */
	class Page extends \Eloquent {}
}

namespace App\Models\Parts{
/**
 * App\Models\Parts\Category
 *
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $description
 * @property-read \App\Models\Parts\CategoryMappings|null $category_mappings
 * @property-read \App\Models\Parts\CategoryImage|null $image
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Parts\Type> $types
 * @property-read int|null $types_count
 * @method static \Illuminate\Database\Eloquent\Builder|Category newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Category newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Category query()
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereUpdatedAt($value)
 */
	class Category extends \Eloquent {}
}

namespace App\Models\Parts{
/**
 * App\Models\Parts\CategoryImage
 *
 * @property int $id
 * @property int $category_id
 * @property string $image_url
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryImage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryImage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryImage query()
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryImage whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryImage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryImage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryImage whereImageUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryImage whereUpdatedAt($value)
 */
	class CategoryImage extends \Eloquent {}
}

namespace App\Models\Parts{
/**
 * App\Models\Parts\CategoryMappings
 *
 * @property int $id
 * @property int $category_id
 * @property string $map_from
 * @property string $map_to
 * @property string|null $type
 * @property-read \App\Models\Parts\Category|null $category
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryMappings newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryMappings newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryMappings query()
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryMappings whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryMappings whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryMappings whereMapFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryMappings whereMapTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryMappings whereType($value)
 */
	class CategoryMappings extends \Eloquent {}
}

namespace App\Models\Parts{
/**
 * App\Models\Parts\ListingCategoryMappings
 *
 * @property int $id
 * @property string $map_from
 * @property string $map_to
 * @property string|null $type
 * @property int $type_id
 * @property int $entity_type_id
 * @property-read \App\Models\Parts\Category|null $listingCategory
 * @method static \Illuminate\Database\Eloquent\Builder|ListingCategoryMappings newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ListingCategoryMappings newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ListingCategoryMappings query()
 * @method static \Illuminate\Database\Eloquent\Builder|ListingCategoryMappings whereEntityTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ListingCategoryMappings whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ListingCategoryMappings whereMapFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ListingCategoryMappings whereMapTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ListingCategoryMappings whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ListingCategoryMappings whereTypeId($value)
 */
	class ListingCategoryMappings extends \Eloquent {}
}

namespace App\Models\Parts{
/**
 * App\Models\Parts\Type
 *
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Parts\Category> $categories
 * @property-read int|null $categories_count
 * @method static \Illuminate\Database\Eloquent\Builder|Type newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Type newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Type query()
 * @method static \Illuminate\Database\Eloquent\Builder|Type whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Type whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Type whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Type whereUpdatedAt($value)
 */
	class Type extends \Eloquent {}
}

namespace App\Models\Payment{
/**
 * App\Models\Payment\PaymentLog
 *
 * @property int $id
 * @property string $payment_id
 * @property string $client_reference_id
 * @property string $full_response
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $plan_key
 * @property string|null $plan_name
 * @property int|null $plan_duration
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentLog whereClientReferenceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentLog whereFullResponse($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentLog wherePaymentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentLog wherePlanDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentLog wherePlanKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentLog wherePlanName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentLog whereUpdatedAt($value)
 */
	class PaymentLog extends \Eloquent {}
}

namespace App\Models\SubscribeEmailSearch{
/**
 * App\Models\SubscribeEmailSearch\SubscribeEmailSearch
 *
 * @property int $id
 * @property string $email
 * @property string $url
 * @property string|null $subscribe_email_sent
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|SubscribeEmailSearch newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SubscribeEmailSearch newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SubscribeEmailSearch query()
 * @method static \Illuminate\Database\Eloquent\Builder|SubscribeEmailSearch whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubscribeEmailSearch whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubscribeEmailSearch whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubscribeEmailSearch whereSubscribeEmailSent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubscribeEmailSearch whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubscribeEmailSearch whereUrl($value)
 */
	class SubscribeEmailSearch extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\SyncProcess
 *
 * @property int                      $id
 * @property string                   $name
 * @property string                   $status      ['working'|'finished'|'failed']
 * @property array                    $meta        json data
 * @property DateTimeInterface|string $created_at
 * @property DateTimeInterface|string $updated_at
 * @property DateTimeInterface|string $finished_at
 * @method static \Database\Factories\SyncProcessFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|SyncProcess newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SyncProcess newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SyncProcess query()
 * @method static \Illuminate\Database\Eloquent\Builder|SyncProcess whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SyncProcess whereFinishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SyncProcess whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SyncProcess whereMeta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SyncProcess whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SyncProcess whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SyncProcess whereUpdatedAt($value)
 */
	class SyncProcess extends \Eloquent {}
}

namespace App\Models\SysConfig{
/**
 * App\Models\SysConfig\SysConfig
 *
 * @property int $id
 * @property string $key
 * @property string $value
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|SysConfig newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SysConfig newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SysConfig query()
 * @method static \Illuminate\Database\Eloquent\Builder|SysConfig whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SysConfig whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SysConfig whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SysConfig whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SysConfig whereValue($value)
 */
	class SysConfig extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\User
 *
 * @method static UserFactory factory(...$parameters)
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 */
	class User extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\UserTracking
 *
 * @property int $id
 * @property string $visitor_id
 * @property int|null $website_user_id
 * @property string $event
 * @property string $url
 * @property array|null $meta
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\WebsiteUser\WebsiteUser|null $websiteUser
 * @method static \Database\Factories\UserTrackingFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|UserTracking newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserTracking newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserTracking query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserTracking whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserTracking whereEvent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserTracking whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserTracking whereMeta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserTracking whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserTracking whereUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserTracking whereVisitorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserTracking whereWebsiteUserId($value)
 */
	class UserTracking extends \Eloquent {}
}

namespace App\Models\WebsiteUser{
/**
 * App\Models\WebsiteUser\WebsiteUser
 *
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property string|null $address
 * @property string|null $zipcode
 * @property string|null $city
 * @property string|null $state
 * @property string $email
 * @property string|null $phone_number
 * @property string|null $mobile_number
 * @property string $password
 * @property string|null $registration_source
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $tc_user_id
 * @property int|null $tc_user_location_id
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @method static \Database\Factories\WebsiteUser\WebsiteUserFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|WebsiteUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WebsiteUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WebsiteUser query()
 * @method static \Illuminate\Database\Eloquent\Builder|WebsiteUser whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebsiteUser whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebsiteUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebsiteUser whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebsiteUser whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebsiteUser whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebsiteUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebsiteUser whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebsiteUser whereMobileNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebsiteUser wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebsiteUser wherePhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebsiteUser whereRegistrationSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebsiteUser whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebsiteUser whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebsiteUser whereTcUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebsiteUser whereTcUserLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebsiteUser whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebsiteUser whereZipcode($value)
 */
	class WebsiteUser extends \Eloquent implements \Illuminate\Contracts\Auth\CanResetPassword, \Illuminate\Contracts\Auth\MustVerifyEmail, \Tymon\JWTAuth\Contracts\JWTSubject, \Illuminate\Contracts\Auth\Authenticatable {}
}

