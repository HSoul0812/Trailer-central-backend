<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    public const TABLE_NAME = 'region';

    public $timestamps = false;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = self::TABLE_NAME;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'region_code';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "region_code",
        "region_name"
    ];


    /**
     * Get Table Name
     * 
     * @return const string
     */
    public static function getTableName(): string {
        return self::TABLE_NAME;
    }
}