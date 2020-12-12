<?php
use \mhamzeh\packageFp\Presenter\Contracts\Presenter;
class UserPresenter extends Presenter
{
    public function full_name()
    {
        return $this->entity->name." ".$this->entity->family;
    }
}