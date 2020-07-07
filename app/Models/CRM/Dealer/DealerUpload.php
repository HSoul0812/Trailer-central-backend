<?php

namespace App\Models\CRM\Dealer;

use App\Models\User\NewDealerUser;
use App\Models\Upload\Upload;
use Illuminate\Database\Eloquent\Model;

class DealerUpload extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dealer_upload';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "dealer_id",
        "upload_id",
        "is_parts_upload"
    ];

    public function dealer()
    {
        return $this->belongsTo(NewDealerUser::class, 'dealer_id', 'dealer_id');
    }

    public function upload()
    {
        return $this->belongsTo(Upload::class, 'upload_id', 'upload_id');
    }
}
