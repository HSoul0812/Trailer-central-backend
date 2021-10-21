<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Ecommerce\Payment\PaymentService;

use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PaymentServiceTestCase extends TestCase
{
    use WithFaker;

    protected function makeModel(string $class): callable
    {
        return static function (array $attributes) use ($class) {
            $order = new $class;

            foreach ($attributes as $attribute => $value) {
                $order->{$attribute} = $value;
            }

            return $order;
        };
    }
}
