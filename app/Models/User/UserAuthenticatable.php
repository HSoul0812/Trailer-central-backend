<?php

declare(strict_types=1);

namespace App\Models\User;

use App\Traits\WithFactory;
use App\Traits\WithGetter;

/**
 * @property-read string $type
 * @property-read int $id
 */
class UserAuthenticatable
{
    use WithFactory;
    use WithGetter;

    public const TYPE_DEALER = 'dealer';
    public const TYPE_USER = 'user';

    private const AVAILABLE_TYPES = [
        self::TYPE_DEALER,
        self::TYPE_USER,
    ];

    private const TABLENAMES_BY_TYPE = [
        self::TYPE_DEALER => 'dealers',
        self::TYPE_USER => 'dealer_users',
    ];

    /** @var string */
    private $type;

    /** @var int */
    private $id;

    /**
     * @param string $type
     * @throws \InvalidArgumentException when type is not a valid type
     */
    protected function setType(string $type): void
    {
        if (!in_array($type, self::AVAILABLE_TYPES)) {
            throw new \InvalidArgumentException('Invalid type');
        }

        $this->type = $type;
    }

    /**
     * @return string
     * @throws \Exception when type is not setup
     */
    public function getTableName(): string
    {
        if (is_null($this->type)) {
            throw new \Exception('Type is not defined');
        }

        return self::TABLENAMES_BY_TYPE[$this->type];
    }
}
