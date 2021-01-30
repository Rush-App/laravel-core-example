<?php

namespace RushApp\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Language
 *
 * @property int $id
 * @property string $name
 *
 * @package App\Models
 */
class Language extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'name',
    ];

    public $timestamps = false;
}
