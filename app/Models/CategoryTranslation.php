<?php

namespace App\Models;

use App\Models\Post\Post;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Class CategoryTranslation
 *
 * @property int $id
 * @property string $name
 *
 * @package App\Models
 */
class CategoryTranslation extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'name',
        'language_id',
        'category_id',
    ];
}
