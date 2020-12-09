<?php


namespace mhamzeh\packageFp\Filters\contracts;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class QueryFilter
{
    protected $request;
    protected $builder;

    public function __construct()
    {
        $this->request = Request::capture();
    }


    public function apply(Builder $builder)
    {
        $this->builder = $builder;
        foreach ($this->filters() as $key => $value) {
            if (!method_exists($this, $key)) {
                return $this->builder;
            }
            !empty($value) ? $this->{$key}($value) : $this->{$key};
        }
        return $this->builder;
    }

    private function filters()
    {
        return array_filter(
            $this->request->all()
        );
    }
}
