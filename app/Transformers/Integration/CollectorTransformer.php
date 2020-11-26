<?php

namespace App\Transformers\Integration;

use App\Models\Integration\Collector\Collector;
use League\Fractal\TransformerAbstract;

/**
 * Class CollectorTransformer
 * @package App\Transformers\Integration
 */
class CollectorTransformer extends TransformerAbstract
{
    /**
     * @param Collector $collector
     * @return array
     */
    public function transform(Collector $collector)
    {
        return [
            'id' => $collector->id,
            'dealer_id' => $collector->dealer_id,
            'dealer_location_id' => $collector->dealer_location_id,
            'process_name' => $collector->process_name,
            'ftp_host' => $collector->ftp_host,
            'ftp_path' => $collector->ftp_path,
            'ftp_login' => $collector->ftp_login,
            'ftp_password' => $collector->ftp_password,
            'file_format' => $collector->file_format,
            'length_format' => $collector->length_format,
            'width_format' => $collector->width_format,
            'height_format' => $collector->height_format,
            'show_on_rvtrader' => $collector->show_on_rvtrader,
            'title_format' => $collector->title_format,
            'import_prices' => $collector->import_prices,
            'import_description' => $collector->import_description,
            'images_delimiter' => $collector->images_delimiter,
            'active' => $collector->active,
        ];
    }
}
