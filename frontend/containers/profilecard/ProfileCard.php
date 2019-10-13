<?php
/**
 * Контейнер редактирования профиля
 */
namespace frontend\containers\profilecard;

use frontend\containers\Container;

class ProfileCard extends Container
{
    function run()
    {
        $profileCard = $this->render('@frontend/modules/profile/views/default/profile_card');
        return $this->render('index', [
            'profileCard' => $profileCard
        ]);
    }
}