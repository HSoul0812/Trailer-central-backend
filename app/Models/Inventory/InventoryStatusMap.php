<?php

namespace App\Models\Inventory;

class InventoryStatusMap
{
    public const ON_ORDER = 'on_order';
    public const SOLD = 'sold';
    public const AVAILABLE = 'available';
    public const PENDING_SALE = 'pending_sale';
    public const SPECIAL_ORDER = 'special_order';

    public const AVAILABLE_ID = 1;
    public const SOLD_ID = 2;
    public const ON_ORDER_ID = 3;
    public const PENDING_SALE_ID = 4;
    public const SPECIAL_ORDER_ID = 5;

    public const STATUS_MAP = [
        self::AVAILABLE_ID => self::AVAILABLE,
        self::SOLD_ID => self::SOLD,
        self::ON_ORDER_ID => self::ON_ORDER,
        self::PENDING_SALE_ID => self::PENDING_SALE,
        self::SPECIAL_ORDER_ID => self::SPECIAL_ORDER,
    ];

    public static function GetStatusId($_status): ?int
    {
        foreach (self::STATUS_MAP as $key => $status) {
            if ($status == $_status) {
                return $key;
            }
        }

        return null;
    }

    public static function GetStatus($statusId): string
    {
        return self::STATUS_MAP[$statusId];
    }
}
