<?php

namespace RushApp\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Action
 *
 * @property int $id
 * @property string $action_name
 * @property string $entity_name
 *
 * @package App\Models
 */
class Action extends Model
{
    use HasFactory;

    protected $fillable = [
        'action_name',
        'entity_name',
    ];

    public $timestamps = false;
}
