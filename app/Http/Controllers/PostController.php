<?php

namespace App\Http\Controllers;

use App\Models\Post\Post;
use Illuminate\Http\Request;
use RushApp\Core\Controllers\BaseController;

class PostController extends BaseController
{
    /**
     * the name of the model must be indicated in each controller
     * @var string
     */
    protected string $modelClassController = Post::class;
}