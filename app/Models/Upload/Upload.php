<?php

namespace App\Models\Upload;

use App\Models\CRM\Dealer\DealerUpload;
use App\Models\User\NewDealerUser;
use App\Traits\CompactHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Upload extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'upload';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'upload_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'upload_id',
        'created_at',
        'filename',
        'title',
        'hash',
        'last_run_at',
        'last_run_state'
    ];

    public function dealerUpload()
    {
        return $this->hasOne(DealerUpload::class, 'user_id', 'upload_id');
    }

    public function getIdentifier()
    {
        return CompactHelper::shorten($this->getId());
    }

    public function getUrlAttribute()
    {
        return Storage::disk('s3')->url($this->filename);
    }

    public function getSizeInKbAttribute($size = 0)
    {
        return round($size / 1024, 2);
    }

    public function getUploadedTimeAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    public function dealer() {
        return $this->belongsToMany(NewDealerUser::class, 'dealer_upload', 'upload_id', 'upload_id');
    }
}
