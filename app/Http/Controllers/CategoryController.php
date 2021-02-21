<?php

namespace App\Http\Controllers;

use App\Models\Category;
use RushApp\Core\Controllers\BaseController;

class CategoryController extends BaseController
{
    /**
     * the name of the model must be indicated in each controller
     * @var string
     */
    protected string $modelClassController = Category::class;
}