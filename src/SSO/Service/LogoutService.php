<?php
/**
 * Created by PhpStorm.
 * User: lu052788
 * Date: 19/03/2018
 * Time: 10:52
 */

namespace SSO\Service;


class LogoutService
{

    private $ssoApiService;

    public function __construct(SsoApiService $ssoApiService)
    {
        $this->ssoApiService = $ssoApiService;
    }

    public function doLogoutTo($loginSSO)
    {
        if(! empty($loginSSO)){
            $this->ssoApiService->logout($loginSSO);
        }

        unset($_SESSION[KeycloakIntelbrasService::SS_ID_OAUTH_STATE]);
    }

}