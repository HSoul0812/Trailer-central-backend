<?php

declare(strict_types=1);

namespace App\DTOs\SubscribeEmailSearch;

use JetBrains\PhpStorm\Pure;

class SubscribeEmailSearchDTO
{
    public ?string $email;
    public ?string $url;

    #[Pure]
    public static function fromData(array $data): self
    {
        $obj = new self();
        $obj->email = $data['email'];
        $obj->url = $data['url'];
        $obj->subject = $data['subject'];

        return $obj;
    }
}
