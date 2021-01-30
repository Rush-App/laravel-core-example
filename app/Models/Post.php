<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Post
 *
 * @property int $id
 * @property bool $published
 * @property int $user_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read User $user
 *
 * @package App\Models
 */
class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'published',
        'user_id',
    ];

    protected $casts = [
        'published' => 'bool',
    ];

    public function user()
    {
        $this->belongsTo(User::class);
    }
}
