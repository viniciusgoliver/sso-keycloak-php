<?php
/**
 * Created by PhpStorm.
 * User: lu052788
 * Date: 19/03/2018
 * Time: 10:51
 */

namespace SSO\Service\Factory;


use SSO\Service\SsoApiService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class SsoApiServiceFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $cache = $serviceLocator->get('Cache');

        $ssoApi = new SsoApiService();

        $ssoApi->setCache($cache);

        return $ssoApi;
    }

}