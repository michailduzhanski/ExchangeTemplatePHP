<?php
namespace frontend\containers\usermenu;

class UserMenu extends \frontend\containers\Container
{
    public function run()
    {
        return $this->render('index');
    }
}