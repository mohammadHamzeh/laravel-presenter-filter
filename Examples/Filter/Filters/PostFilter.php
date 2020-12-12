<?php
namespace App\Filters;
use \mhamzeh\packageFp\Filters\contracts\QueryFilter;

class PostFilter extends QueryFilter
{
    public function title($value)
    {
        return $this->builder->where('title','LIKE',"%$value%");
    }
}