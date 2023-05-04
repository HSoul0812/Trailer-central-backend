<?php

namespace Tests\Unit\App\Repositories\SubscribeEmailSearch;

use App\Repositories\SubscribeEmailSearch\SubscribeEmailSearchRepository;
use App\Repositories\SubscribeEmailSearch\SubscribeEmailSearchRepositoryInterface;
use Carbon\Carbon;
use Tests\Common\TestCase;

class SubscribeEmailSearchRepositoryTest extends TestCase
{
    public function testCreate()
    {
        $data = [
            'email' => 'test@test.com',
            'url' => 'https://test',
            'subscribe_email_sent' => Carbon::now(),
        ];
        $repository = $this->getConcreteRepository();

        $repository->create($data);
        $this->assertDatabaseHas('subscribe_email_search', $data);
    }

    public function testCreateWithLongUrl()
    {
        $data = [
            'email' => 'test@test.com',
            'url' => 'https://www.google.com/search?q=long+url+&newwindow=1&ei=Dpk1Y_OjPPOy0PEP3IG28AE&ved=0ahUKEwjz4vO1ibr6AhVzGTQIHdyADR4Q4dUDCA4&uact=5&oq=long+url+&gs_lcp=Cgdnd3Mtd2l6EAMyBQgAEIAEMgUIABCABDIFCAAQgAQyBQgAEIAEMgUIABCABDIFCAAQgAQyBQgAEIAEMgUIABCABDIFCAAQgAQyBQgAEIAEOgQIABBDOgQILhBDOgsILhCABBCxAxCDAToKCAAQsQMQgwEQQzoRCC4QgAQQsQMQgwEQxwEQ0QM6CwguEIAEELEDENQCOgsILhCPARDUAhDqAjoICAAQjwEQ6gI6CAguEI8BEOoCOgoILhDHARDRAxADOg0ILhDHARDRAxDUAhADOggILhCxAxCDAToICAAQsQMQgwE6BAguEAM6BQgAEJECOgQIABADOg4ILhCxAxCDARDHARDRAzoHCC4Q1AIQAzoOCC4QgAQQsQMQxwEQ0QM6BQguEIAEOggIABCABBCxAzoOCC4QgAQQsQMQxwEQrwE6EQguEIAEELEDEMcBENEDENQCOgsIABCABBCxAxCDAToLCC4QgAQQxwEQrwE6CAguEIAEENQCSgQIQRgASgQIRhgAUABY8-cBYMDqAWgCcAF4AYAB7gOIAeQzkgEJMC4xLjAuOC44mAEAoAEBsAEKwAEB&sclient=gws-wiz',
            'subscribe_email_sent' => Carbon::now(),
        ];
        $repository = $this->getConcreteRepository();

        $repository->create($data);
        $this->assertDatabaseHas('subscribe_email_search', $data);
    }

    private function getConcreteRepository(): SubscribeEmailSearchRepository
    {
        return app()->make(SubscribeEmailSearchRepositoryInterface::class);
    }
}
