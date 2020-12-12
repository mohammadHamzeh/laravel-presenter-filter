<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use \mhamzeh\packageFp\Filters\contracts\Filterable;

class Post extends Model
{
    use Filterable;

    protected $fillable = ['title'];
}