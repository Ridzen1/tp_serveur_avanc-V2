<?php

use toubilib\api\actions\SigninAction;
use toubilib\api\actions\RegisterAction;
use toubilib\api\actions\RefreshTokenAction;
use toubilib\api\actions\ValidateTokenAction;
use toubilib\api\provider\AuthProvider;
use toubilib\core\application\usecases\ServiceAuth;

return [
    SigninAction::class => function($container) {
        return new SigninAction($container->get(AuthProvider::class));
    },

    RegisterAction::class => function($container) {
        return new RegisterAction($container->get(ServiceAuth::class));
    },

    RefreshTokenAction::class => function($container) {
        return new RefreshTokenAction($container->get(AuthProvider::class));
    },

    ValidateTokenAction::class => function($container) {
        return new ValidateTokenAction($container->get(AuthProvider::class));
    }
];