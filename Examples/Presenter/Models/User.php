<?php
use Illuminate\Foundation\Auth\User as Authenticatable;
use mhamzeh\packageFp\Presenter\Contracts\Presentable;

class User extends Authenticatable
{
    use Presentable;
    protected $presenter = UserPresenter::class;
}