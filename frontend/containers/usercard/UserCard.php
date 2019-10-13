<?php
/**
 * Контейнер с информацией о пользователе
 */
namespace frontend\containers\usercard;

use frontend\containers\Container;

class UserCard extends Container
{
    function run()
    {
        $profileForm = $this->render('@frontend/modules/profile/views/default/profile_form');
        return $this->render('card', [
            'profileForm' => $profileForm
        ]);
    }
}