<?php

namespace App\DTOs\User;

use JetBrains\PhpStorm\Pure;

class TcApiResponseUser
{
    public int $id;
    public string $name;
    public string $email;
    public string $access_token;

    #[Pure] public static function fromData(array $data): self {
        $obj = new self;
        $obj->id = $data['id'];
        $obj->name = $data['name'];
        $obj->email = $data['email'];
        $obj->access_token = $data['access_token'];
        return $obj;
    }
}
