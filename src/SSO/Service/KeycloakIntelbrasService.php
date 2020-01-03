<?php
/**
 * Created by PhpStorm.
 * User: lu052788
 * Date: 19/03/2018
 * Time: 10:52
 */

namespace SSO\Service;


use League\OAuth2\Client\Token\AccessToken;
use Stevenmaguire\OAuth2\Client\Provider\Keycloak;
use Zend\Session\Container;

class KeycloakIntelbrasService extends Keycloak
{

    const SS_ID_OAUTH_STATE = 'oauth2state';

    /**
     * @var Container
     */
    private $session;

    public function __construct(array $options = [], Container $session)
    {
        parent::__construct($options, []);
        $this->session = $session;
    }

    /**
     * @return Container
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * @param Container $session
     * @return KeycloakIntelbrasService
     */
    public function setSession($session)
    {
        $this->session = $session;

        return $this;
    }

    public function getAllowedClientOptions(array $options)
    {

        $client_options   = parent::getAllowedClientOptions($options);
        $client_options[] = 'verify';

        return $client_options;
    }

    public function isAuthenticated()
    {
        if ($this->session->offsetExists('token')) {

            try {
                /** @var AccessToken $token */
                $token = $this->session->offsetGet('token');

                $token = $this->getAccessToken('refresh_token', ['refresh_token' => $token->getRefreshToken()]);

                $this->session->offsetSet('token', $token);

                return TRUE;
            } catch (\Exception $ex) {

            }

        }

        return TRUE;
    }

    public function logout()
    {
        $this->session->getManager()->getStorage()->clear();
    }

    public function getTokenAuth()
    {
        if ($this->session->offsetExists('token')) {

            $agora = new \DateTime();

            /** @var AccessToken $token */
            $token       = $this->session->offsetGet('token');
            $valuesToken = $token->getValues();

            $tempoSessaoSSO = 0;
            if (array_key_exists('refresh_expires_in', $valuesToken)) {
                $tempoSessaoSSO = $valuesToken['refresh_expires_in'];
            }

            $expiracaoSessao = new \DateTime();
            $expiracaoSessao->setTimestamp($token->getExpires() + $tempoSessaoSSO);

            if ($agora->getTimestamp() < $expiracaoSessao->getTimestamp()) {

                // Atualiza token caso estaja expirado
                if ($agora->getTimestamp() > $token->getExpires()) {
                    $token = $this->getAccessToken('refresh_token', ['refresh_token' => $token->getRefreshToken()]);
                    $this->session->offsetSet('token', $token);
                }

                return $token->getToken();
            }
        }

        return NULL;
    }

}