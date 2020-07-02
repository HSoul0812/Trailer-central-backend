<?php


namespace App\Utilities\Fractal;


use League\Fractal\Serializer\ArraySerializer;

/**
 * Class NoDataArraySerializer
 *
 * Like ArraySerializer but does not put 'data' for collections
 *
 * @package App\Utilities\Fractal
 */
class NoDataArraySerializer extends ArraySerializer
{
    public function collection($resourceKey, array $data)
    {
        return $data;
    }

}
