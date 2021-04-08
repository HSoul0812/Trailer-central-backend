<?php

namespace App\Traits;

trait GeospatialHelper
{
    /* http://dev.mysql.com/doc/refman/5.0/en/gis-wkb-format.html */

    protected function toWKB($latitude, $longitude) {
        return pack("cLdd", 1, 16777216, $latitude, $longitude);
    }

    protected function fromWKB($wkb) {
        $geolocation = array();

        $geometry = unpack('corder/Ltype/dlat/dlon', $wkb);

        $geolocation['lat'] = floatval($geometry['lat']);
        $geolocation['lon'] = floatval($geometry['lon']);

        return $geolocation;
    }
}
