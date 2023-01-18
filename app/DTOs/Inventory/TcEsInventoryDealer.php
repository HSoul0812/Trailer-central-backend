<?php
namespace App\DTOs\Inventory;

use Illuminate\Contracts\Support\Arrayable;
use JetBrains\PhpStorm\Pure;

class TcEsInventoryDealer implements Arrayable {
    use \App\DTOs\Arrayable;

    public ?string $name;
    public ?string $email;
    public bool $is_private;

    #[Pure] public static function fromData(array $data):self {
        $obj = new self();
        $obj->name = $data['name'] ?? null;
        $obj->email = $data['email'] ?? null;
        $obj->is_private = (new PrivateDealerCheck)->checkArray($data);
        return $obj;
    }
}
