<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
    protected $fillable = [
        'title',
        'description',
        'post_id',
        'language_id',
    ];

    public $timestamps = false;
}
