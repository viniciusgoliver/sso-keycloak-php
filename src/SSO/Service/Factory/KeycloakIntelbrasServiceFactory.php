<?php
/**
 * Created by PhpStorm.
 * User: lu052788
 * Date: 19/03/2018
 * Time: 10:46
 */

namespace SSO\Service\Factory;


use SSO\Service\KeycloakIntelbrasService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Session\Container;

class KeycloakIntelbrasServiceFactory implements FactoryInterface
{

    /**
     * {@inheritDoc}
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $urlAuth = ENV_URI_SSO;
        if(substr($urlAuth, -1) == '/'){
            $urlAuth = substr_replace($urlAuth, '', -1);
        }

        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ? "https:" : "http:";
        $existeProtocolo = strpos(BASE_URL, 'http');

        $uriWithourParams = substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], '?'));
        $redirectUri = BASE_URL . $uriWithourParams;
        if($existeProtocolo === false){
            $redirectUri = $protocol . BASE_URL . $uriWithourParams;
        }

        $config = [
            'authServerUrl'       => $urlAuth,
            'realm'               => ENV_SSO_REALM,
            'clientId'            => ENV_CLIENT_ID_SSO_AUTH,
            'clientSecret'        => ENV_CLIENT_SECRET_SSO_AUTH,
            'redirectUri'         => $redirectUri,
            'encryptionAlgorithm' => NULL,
            'encryptionKey'       => NULL,
            'encryptionKeyPath'   => NULL,
            'verify'              => FALSE,
        ];
        $session = new Container('keycloakIntelbras');
        $provider = new KeycloakIntelbrasService($config, $session);

        return $provider;
    }

}