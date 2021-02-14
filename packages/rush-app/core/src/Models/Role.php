<?php

namespace RushApp\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * Class Role
 *
 * @property int $id
 * @property string $name
 *
 * @property-read Action[]|Collection $actions
 *
 * @package App\Models
 */
class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public $timestamps = false;

    public function actions(): BelongsToMany
    {
        return $this->belongsToMany(Action::class, 'role_action');
    }
}
