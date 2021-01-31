<?php

namespace App\Models\Post;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use RushApp\Core\Models\BaseModel;
use RushApp\Core\Models\BaseModelTrait;

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
class Post extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'published',
        'user_id',
    ];

    public function user()
    {
        $this->belongsTo(User::class);
    }
}
