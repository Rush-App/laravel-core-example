<?php

namespace App\Models;

use App\Models\Post\Post;
use Illuminate\Database\Eloquent\Relations\HasMany;
use RushApp\Core\Models\User as BaseUser;

class User extends BaseUser {

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
}
