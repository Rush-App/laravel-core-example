<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

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
        'name',
    ];
}