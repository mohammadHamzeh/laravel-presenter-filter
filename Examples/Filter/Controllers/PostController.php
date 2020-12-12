<?php
namespace App\Controllers;
use App\Models\Post;
use Illuminate\Routing\Controller as BaseController;

class PostController extends BaseController
{
    public function index()
    {
        return Post::filters(new PostFilter())->get();
    }
}