<?php

namespace App\Models;

use App\Models\Post\Post;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Arr;

/**
 * Class Category
 *
 * @property int $id
 * @property string $name
 *
 * @package App\Models
 */
class Category extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'status',
    ];

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_category');
    }
}