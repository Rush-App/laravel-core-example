<?php

namespace App\Models\Post;

use App\Models\BaseModel;
use App\Models\Category;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Class Post
 *
 * @property int $id
 * @property bool $published
 * @property Carbon $published_at
 * @property int $user_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read User $user
 *
 * @package App\Models
 */
class Post extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'published',
        'published_at',
        'user_id',
    ];

    protected $dates = [
        'published_at',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'post_category');
    }
}
