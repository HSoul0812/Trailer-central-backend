<?php

namespace App\Models\Website;

use App\Models\Traits\TableAware;
use App\Models\Website\Config\WebsiteConfig;
use App\Traits\CompactHelper;
use Illuminate\Database\Eloquent\Model;
use App\Models\Website\Blog\Post;
use App\Models\User\User;

use Illuminate\Support\Facades\Log;
use Spatie\SslCertificate\SslCertificate;

/**
 * Class Website
 * @package App\Models\Website
 *
 * @property int $id
 * @property string $domain
 * @property string $canonical_host
 * @property string $render
 * @property bool $render_cms
 * @property bool $https_supported
 * @property string $type
 * @property string $template
 * @property bool $responsive
 * @property int $dealer_id
 * @property string $type_config
 * @property float $handling_fee
 * @property int $parts_fulfillment
 * @property \DateTimeInterface $date_created
 * @property int $date_updated
 * @property bool $is_active
 * @property bool $is_live
 * @property string $parts_email
 * @property bool $force_elastic
 * @property string $ssl_certificate
 */
class Website extends Model
{
    use TableAware;

    const TRAILERTRADER_ID = 284;

    const WEBSITE_TYPE_CUSTOM = 'custom';
    const WEBSITE_TYPE_WEBSITE = 'website';
    const WEBSITE_TYPE_CLASSIFIED = 'classified';

    protected $table = 'website';

    /**
     * The name of the "created at" column.
     *
     * @var string
     */
    const CREATED_AT = 'date_created';

    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    const UPDATED_AT = 'date_updated';

    public function getSslCertificateAttribute()
    {
        $ip = gethostbyname($this->domain);
        $certificate = false;

        if (filter_var($ip, FILTER_VALIDATE_IP) !== false) {
            try {
                $certificate = SslCertificate::createForHostName($this->domain);
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }
        }

        return $certificate;
    }

    /**
     * Get the website type config.
     *
     * @param  string|null  $value
     * @return string
     */
    public function getTypeConfigAttribute(?string $value) : string
    {
      $printData = '';

      try {
        $unserializedFilter = unserialize($value);

        $unserializedFilters = $unserializedFilter['filters'] ?? [];

        if (isset($unserializedFilter['dealer_id']) && is_array($unserializedFilter['dealer_id'])) {
          $printData = $this->unserializeDealerFilter($unserializedFilter['dealer_id']);
        }

        $printData .= $this->unserializeAllFilter($unserializedFilters);

        return $printData;
      } catch(\Exception $exception){
         return $printData;
      }
    }

    /**
     * Get the value for a specific type config
     * @param string $filter
     * @return mixed
     */
    public function getFilterValue(string $filter){
        $serializedData = $this->getOriginal('type_config');
        if (strpos($serializedData, ':') !== 0) {
            $filters = unserialize($serializedData, ['allowed_classes' => true]);
            if (isset($filters[$filter])) {
                return $filters[$filter];
            }
        }
        return null;
    }

    public function unserializeDealerFilter(array $dealer_ids): string
    {
        $printData = '';

        foreach ($dealer_ids as $dealer_id) {
            $printData .= 'dealer_id|eq|' . $dealer_id . PHP_EOL;
        }

        return $printData;
    }

    public function unserializeAllFilter(array $unserializedFilters) : string
    {
      $printData = '';

      foreach($unserializedFilters as $match => $filterData) {
          $prefix = "$match|";
          foreach($filterData as $operator => $operatorValue) {
              foreach($operatorValue as $actualValue) {
                  if (is_array($actualValue)) {
                      $printData .= $prefix."$operator|".current($actualValue).PHP_EOL;
                  } else {
                      $printData .= $prefix."$operator|".$actualValue.PHP_EOL;
                  }
              }
          }
      }

      return $printData;
    }

    public function setTypeConfigAttribute(?string $value)
    {
        $globalFilter = explode(\PHP_EOL, (string)$value);

        $filterLineData = [];
        $filterLineData['filters'] = [];

        foreach ($globalFilter as $filterLine) {
            $filterLine = trim($filterLine);

            if (strlen($filterLine) > 0 && $filterLine[0] !== '#') {
                if (substr($filterLine, 0, 16) === 'classic filter: ') {
                    $filterMode = 'classic';
                    $this->attributes['type_config'] = substr($filterLine, 16);

                    return;
                }

                $filterLineTmp = explode('|', $filterLine);
                if ($filterLineTmp[0] == 'dealer_id') {
                    if (!isset($filterLineData['dealer_id'])) {
                        $filterLineData['dealer_id'] = [];
                    }
                    $filterLineData['dealer_id'][] = $filterLineTmp[2];
                } else {
                    if (!isset($filterLineData['filters'][$filterLineTmp[0]][$filterLineTmp[1]])) {
                        $filterLineData['filters'][$filterLineTmp[0]][$filterLineTmp[1]] = [];
                    }
                    $filterLineData['filters'][$filterLineTmp[0]][$filterLineTmp[1]][] = [$filterLineTmp[2]];
                }
            }
        }

        $this->attributes['type_config'] = serialize($filterLineData);
    }

    public function getHeadScriptsAttribute() : string
    {
        return base64_decode(
            $this->websiteConfigs()->where('key', WebsiteConfig::GENERAL_HEAD_SCRIPT_KEY)->take(1)->value('value')
        );
    }

    public function setHeadScriptsAttribute($value) : void
    {
        $headScript = $this->websiteConfigs()->where('key', WebsiteConfig::GENERAL_HEAD_SCRIPT_KEY)->first();
        $value = base64_encode($value);

        if ($headScript) {
            $headScript->update(['value' => $value]);
        } else {
            $this->websiteConfigs()->create(['key' =>  WebsiteConfig::GENERAL_HEAD_SCRIPT_KEY, 'value' => $value]);
        }
    }

    public function dealer()
    {
        return $this->hasOne(User::class, 'dealer_id', 'dealer_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function websiteConfigs()
    {
        return $this->hasMany(WebsiteConfig::class, 'website_id', 'id');
    }

    public function blogPosts()
    {
        return $this->hasMany(Post::class, 'website_id', 'id');
    }

    /**
     * @param string $key
     * @return array
     */
    public function websiteConfigByKey(string $key)
    {
        return $this->websiteConfigs()->where('key', $key)->take(1)->value('value');
    }


    /**
     * Get website shorten identifier
     *
     * @return false|string
     */
    public function getIdentifierAttribute()
    {
        return CompactHelper::shorten($this->id);
    }

    /**
     * @return string
     */
    public function getBodyScriptsAttribute(): string
    {
        return base64_decode(
            $this->websiteConfigs()->where('key', WebsiteConfig::GENERAL_BODY_SCRIPT_KEY)->take(1)->value('value')
        );
    }

    /**
     * @return void
     */
    public function setBodyScriptsAttribute($value) : void
    {
        $script = $this->websiteConfigs()->where('key', WebsiteConfig::GENERAL_BODY_SCRIPT_KEY)->first();
        $value = base64_encode($value);

        if (!empty($script)) {
            $script->update(['value' => $value]);
        } else {
            $this->websiteConfigs()->create([
                'key' =>  WebsiteConfig::GENERAL_BODY_SCRIPT_KEY,
                'value' => $value,
            ]);
        }
    }
}
