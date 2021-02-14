<?php

namespace App\Models\Post;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class PostTranslation
 *
 * @property int $id
 * @property string $title
 * @property string $description
 * @property int $post_id
 * @property int $language_id
 *
 * @package App\Models
 */
class PostTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'post_id',
        'language_id',
    ];

    public $timestamps = false;

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
