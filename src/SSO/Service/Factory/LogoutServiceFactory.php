<?php
/**
 * Created by PhpStorm.
 * User: lu052788
 * Date: 19/03/2018
 * Time: 10:47
 */

namespace SSO\Service\Factory;


use SSO\Service\LogoutService;
use SSO\Service\SsoApiService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class LogoutServiceFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $ssoApiService = $serviceLocator->get(SsoApiService::class);

        return new LogoutService($ssoApiService);
    }

}