<?php

namespace App\Http\Controllers;

use App\Models\Category;
use RushApp\Core\Controllers\BaseCrudController;

class CategoryController extends BaseCrudController
{
    /**
     * the name of the model must be indicated in each controller
     * @var string
     */
    protected string $modelClassController = Category::class;
}