<?php

namespace RushApp\Core\Models;

use Illuminate\Database\Eloquent\Model;

abstract class BaseModel extends Model
{
    use BaseModelTrait;

    public function __construct(array $attributes = [])
    {
        $this->initBaseModel();
        parent::__construct($attributes);
    }
}
