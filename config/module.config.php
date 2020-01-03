<?php
/**
 * Created by PhpStorm.
 * User: lu052788
 * Date: 19/03/2018
 * Time: 13:29
 */

use SSO\Service\KeycloakIntelbrasService;
use SSO\Service\LogoutService;
use SSO\Service\SsoApiService;
use SSO\Service\Factory\KeycloakIntelbrasServiceFactory;
use SSO\Service\Factory\LogoutServiceFactory;
use SSO\Service\Factory\SsoApiServiceFactory;

return [
    'service_manager' => [
        'factories' => [
            KeycloakIntelbrasService::class => KeycloakIntelbrasServiceFactory::class,
            LogoutService::class            => LogoutServiceFactory::class,
            SsoApiService::class            => SsoApiServiceFactory::class
        ]
    ]
];
