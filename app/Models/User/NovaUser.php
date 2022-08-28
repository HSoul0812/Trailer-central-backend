<?php

namespace App\Models\User;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Laravel\Nova\Authorizable;
use Illuminate\Foundation\Auth\User;
use Illuminate\Notifications\Notifiable;

use Spatie\Permission\Traits\HasRoles;

class NovaUser extends User
{
    use HasRoles,
        Notifiable;

    protected $table = 'users';

    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    /**
     * @inheritDoc
     */
    public function can($ability, $arguments = [])
    {
        // TODO: Implement can() method.
        return true;
    }
}

