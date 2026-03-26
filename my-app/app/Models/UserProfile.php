<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    protected $table = 'user_profile';

    protected $fillable = [
        'name',
        'identity',
        'identity_label',
        'identity_icon',
    ];
}
