<?php
namespace App\DTOs\Inventory;

use Illuminate\Contracts\Support\Arrayable;
use JetBrains\PhpStorm\Pure;

class InventoryDealer implements Arrayable {
    use \App\DTOs\Arrayable;

    public string $name;
    public string $email;

    #[Pure] public static function fromData(array $data):self {
        $obj = new self();
        $obj->name = $data['name'];
        $obj->email = $data['email'];
        return $obj;
    }
}
