<?php

declare(strict_types=1);

namespace App\DTOs\Lead;

use JetBrains\PhpStorm\Pure;

class TcApiResponseLead
{
    public int $id;
    public $website_id;
    public int $dealer_id;
    public ?string $name;
    public ?array $lead_types;
    public ?string $email_address;
    public ?string $phone;
    public ?string $comments;
    public ?string $created_at;
    public ?string $zip;

    #[Pure]
    public static function fromData(array $data): self
    {
        $obj = new self();
        $obj->id = $data['id'];
        $obj->website_id = $data['website_id'];
        $obj->dealer_id = $data['dealer_id'];
        $obj->name = $data['name'];
        $obj->lead_types = $data['lead_types'];
        $obj->email_address = $data['email'];
        $obj->phone_number = $data['phone'];
        $obj->comments = $data['comments'];
        $obj->created_at = $data['created_at'];
        $obj->zip = $data['zip'];

        return $obj;
    }
}
