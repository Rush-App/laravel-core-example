<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Post\Post;
use RushApp\Core\Controllers\BaseCrudController;

class PostController extends BaseCrudController
{
    /**
     * the name of the model must be indicated in each controller
     * @var string
     */
    protected string $modelClassController = Post::class;

    protected ?string $storeRequestClass = StorePostRequest::class;
    protected ?string $updateRequestClass = UpdatePostRequest::class;

    protected array $withRelationNames = [
        'user',
        'category',
    ];
}