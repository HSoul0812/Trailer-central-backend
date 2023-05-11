<?php

namespace App\DTOs\User;

use JetBrains\PhpStorm\Pure;

class TcApiResponseUser
{
    public int $id;
    public string $name;
    public string $email;

    #[Pure]
    public static function fromData(array $data): self
    {
        $obj = new self();
        $obj->id = $data['id'];
        $obj->name = $data['name'];
        $obj->email = $data['email'];

        return $obj;
    }
}
