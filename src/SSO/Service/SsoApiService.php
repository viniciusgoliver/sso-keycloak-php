<?php
/**
 * Created by PhpStorm.
 * User: lu052788
 * Date: 19/03/2018
 * Time: 10:52
 */

namespace SSO\Service;


use Doctrine\Common\Collections\ArrayCollection;
use SSO\Exception\BusinessSsoException;
use SSO\Exception\IdentitySsoException;
use SSO\Exception\InvalidPasswordException;
use SSO\Exception\SsoException;
use SSO\ValueObject\User\CredentialRepresentation;
use SSO\ValueObject\User\UserAttributes;
use SSO\ValueObject\User\User;
use Zend\Session\Storage\StorageInterface;

class SsoApiService
{

    const METODO_GET    = 'GET';
    const METODO_POST   = 'POST';
    const METODO_DELETE = 'DELETE';
    const METODO_PUT    = 'PUT';

    private $uri;

    private $app;

    private $realm;

    /** @var  StorageInterface */
    private $cache;

    private $tokenSSO;

    public function __construct($app = ENV_APPLICATION_ID_SSO, $realm = ENV_SSO_REALM, $uri = ENV_URI_SSO)
    {
        assert('! is_null($app)');
        assert('! is_null($realm)');
        assert('! is_null($uri)');

        $this->app   = $app;
        $this->realm = $realm;
        $this->uri   = $uri;
    }

    /**
     * @param $email
     * @return mixed
     * @throws Exception
     */
    public function getUsersByEmail($email)
    {
        /** @var ArrayCollection $usuarios */
        $usuarios = $this->getUsers(NULL, ['email' => $email]);

        return $usuarios->filter(
            function ($u) use ($email) {
                return strtolower($u->getEmail()) == strtolower($email);
            }
        );

    }

    /**
     * @param $username
     * @return User|null
     * @throws Exception
     */
    public function getUserByUsername($username)
    {
        /** @var ArrayCollection $usuarios */
        $usuarios = $this->getUsers(NULL, ['username' => $username]);
        if (!$usuarios->isEmpty() && $usuarios->count() == 1) {
            return $usuarios->first();
        }

        return NULL;
    }

    /**
     * @param $username
     * @return User
     * @throws Exception
     * @throws \Exception
     */
    public function forceGetUserByUsername($username)
    {
        /** @var ArrayCollection $usuarios */
        $usuarios = $this->getUsers(NULL, ['username' => $username]);

        if (!$usuarios->isEmpty()) {
            if ($usuarios->count() == 1) {
                return $usuarios->first();
            } else {
                /** @var User $usuario */
                foreach ($usuarios as $usuario) {
                    if ($usuario->getUsername() == $username) {
                        return $usuario;
                    }
                }
            }
        }

        throw new BusinessSsoException("O usuário {$username}, não foi encontrado no autenticador");
    }

    /**
     * @param $email
     * @return mixed|User
     * @throws Exception
     */
    public function forceGetUserByEmail($email)
    {
        /** @var ArrayCollection $usuarios */
        $usuarios = $this->getUsers(NULL, ['email' => $email]);

        if (!$usuarios->isEmpty()) {
            if ($usuarios->count() == 1) {
                $usuario = $usuarios->first();

                if (strtolower($usuario->getEmail()) == strtolower($email)) {
                    return $usuario;
                }
            } else {
                /** @var User $usuario */
                foreach ($usuarios as $usuario) {
                    if ($usuario->getEmail() == $email) {
                        return $usuario;
                    }
                }
            }
        }

        return NULL;

//        throw new \Exception("O usuário {$email}, não foi encontrado no autenticador");
    }

    private function getUserIdByUsername($username)
    {
        $user = $this->forceGetUserByUsername($username);

        return $user->getId();
    }

    private function getUserIdByEmail($email)
    {
        $user = $this->forceGetUserByEmail($email);

        return !empty($user) ? $user->getId() : null;
    }

    /**
     * @param null $search
     * @param null $paramsFilter
     * @return ArrayCollection
     * @throws Exception
     */
    public function getUsers($search = NULL, $paramsFilter = NULL)
    {

        $url = 'admin/realms/' . $this->realm . '/users';

        $filtros = [];
        if (!empty($search)) {
            $filtros['search'] = $search;
        }

        if (!empty($paramsFilter)) {
            $filtros = $filtros + $paramsFilter;
        }

        if (!empty($filtros)) {
            $url .= '?' . http_build_query($filtros);
        }

        $usuarios = $this->send($this->uri . $url);

        $usuarios = array_map(
            function ($e) {
                return User::setUserFromObject($e);
            },
            $usuarios
        );


        return new ArrayCollection($usuarios);
    }

    /**
     * @param $userName
     * @return User|null
     * @throws Exception
     */
    public function getUser($userName)
    {

        if (empty($userName)) {
            throw new BusinessSsoException("Username não foi informado na consulta ao usuário do SSO.");
        }

        $url = 'admin/realms/' . $this->realm . '/users/' . $this->getUserIdByUsername($userName);

        $usuario = $this->send($this->uri . $url);

        if (empty($usuario)) {
            return NULL;
        }

        return User::setUserFromObject($usuario);
    }

    /**
     * @param $email
     * @return null|User
     * @throws \Exception
     */
    public function getUserByEmail($email)
    {

        if (empty($email)) {
            throw new BusinessSsoException("Username não foi informado na consulta ao usuário do SSO.");
        }

        $url = 'admin/realms/' . $this->realm . '/users/' . $this->getUserIdByEmail($email);

        $usuario = $this->send($this->uri . $url);

        if (empty($usuario->id)) {
            return NULL;
        }

        return User::setUserFromObject($usuario);
    }

    public function getUserByToken($token)
    {

        $url = 'realms/' . $this->realm . '/protocol/openid-connect/userinfo';
        $this->tokenSSO = $token;

        $usuario = $this->send($this->uri . $url);

        if (empty($usuario)) {
            return NULL;
        }

        return User::setUserFromOpenId($usuario);
    }


    /**
     * @param null $search
     * @param null $paramsFilter
     * @return User|null
     * @throws Exception
     */
    public function getOneUser($search = NULL, $paramsFilter = NULL)
    {

        $usuario = $this->getUsers($search, $paramsFilter);
        if ($usuario->count() > 1) {
            throw new BusinessSsoException(sprintf("Mais de um usuário encontrado para os filtros (%s) utilizados.", $search));
        }

        if ($usuario->isEmpty()) {
            return NULL;
        }

        return $usuario->current();
    }

    /**
     * @param $email
     * @return User|null
     * @throws Exception
     */
    public function getOneUserByEmail($email)
    {
        $usuarios = $this->getUsersByEmail($email);
        if($usuarios->isEmpty()){
            return NULL;
        }

        if($usuarios->count() > 1){
            throw new BusinessSsoException(sprintf("Mais de um usuário encontrado para os filtros (%s) utilizados.", $email));
        }

        return $usuarios->first();


    }

    /**
     * @param $email
     * @return bool
     * @throws Exception
     */
    public function existsUserByEmail($email)
    {
        /** @var ArrayCollection $users */
        $users = $this->getUsers(NULL, ['email' => $email]);
        if($users->isEmpty()){
            return FALSE;
        }

        /** @var User $user */
        foreach ($users as $user) {
            if($user->getEmail() == $email){
                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * @param null $search
     * @param null $paramsFilter
     * @return bool
     * @throws Exception
     */
    public function existsUser($search = NULL, $paramsFilter = NULL)
    {
        try {
            $users = $this->getUsers($search, $paramsFilter);
            if (! $users->isEmpty() && $users->count() > 0) {
                return TRUE;
            }

            return FALSE;

        } catch (\Exception $e) {
            return FALSE;
        }
    }

    /**
     * @param null $search
     * @param null $paramsFilter
     * @return bool
     * @throws Exception
     */
    public function existsOneUser($search = NULL, $paramsFilter = NULL)
    {
        try {
            $user = $this->getOneUser($search, $paramsFilter);
            if ($user instanceof User || $user instanceof ArrayCollection) {
                return TRUE;
            }

            return FALSE;

        } catch (\Exception $e) {
            return FALSE;
        }
    }

    /**
     * @param $name
     * @return bool
     */
    public function existsRole($name)
    {
        try {
            $this->getRole($name);

            return TRUE;
        } catch (\Exception $e) {
            return FALSE;
        }
    }

    public function createUser($username, $firstName, $lastName, $email, $locale = NULL)
    {

        $url = 'admin/realms/' . $this->realm . '/users';

        $user = [
            'username'  => $username,
            'firstName' => $firstName,
            'lastName'  => $lastName,
            'email'     => $email,
            'enabled'   => TRUE,

        ];


        if (!empty($locale)) {
            $user['attributes'] = ['locale' => [$locale]];
        }

        return $this->send($this->uri . $url, self::METODO_POST, $user);
    }

    public function cadastraSenhaTemporaria($username, $senha)
    {
        /** @var User $usuario */
        $usuario = $this->getUser($username);

        $url = "admin/realms/{$this->realm}/users/{$usuario->getId()}/reset-password";

        $credenciais = new CredentialRepresentation();
        $credenciais->setType(CredentialRepresentation::PASSWORD);
        $credenciais->setValue($senha);
        $credenciais->setTemporary(TRUE);

        return $this->send($this->uri . $url, self::METODO_PUT, $credenciais->jsonSerialize());
    }

    public function cadastrarSenha($username, $senha, $isSenhaTemporaria = TRUE)
    {
        /** @var User $usuario */
        $usuario = $this->getUser($username);

        $url = "admin/realms/{$this->realm}/users/{$usuario->getId()}/reset-password";

        $credenciais = new CredentialRepresentation();
        $credenciais->setType(CredentialRepresentation::PASSWORD);
        $credenciais->setValue($senha);
        $credenciais->setTemporary($isSenhaTemporaria);

        return $this->send($this->uri . $url, self::METODO_PUT, $credenciais->jsonSerialize());
    }

    /**
     * @param $username
     * @param $firstName
     * @param $lastName
     * @param $email
     * @param bool $enabled
     * @param UserAttributes|NULL $atributos
     * @return mixed
     * @throws \Exception
     */
    public function updateUser($username, $firstName, $lastName, $email, $enabled = TRUE, UserAttributes $atributos = NULL)
    {

        if (empty($username)) {
            throw new BusinessSsoException("Username não foi informando na consulta ao usuário do SSO.");
        }

        $url = 'admin/realms/' . $this->realm . '/users/' . $this->getUserIdByUsername($username);

        $user = [
            'username'  => $username,
            'firstName' => $firstName,
            'lastName'  => $lastName,
            'email'     => $email,
            'enabled'   => $enabled,
        ];

        if ($atributos instanceof UserAttributes) {
            $atributosList = [];
            if ($atributos->getLocale()) {
                $atributosList['locale'] = $atributos->getLocale();
            }
            if ($atributos->getLocale()) {
                $atributosList['LDAP_ID'] = $atributos->getLdapID();
            }
            if ($atributos->getLocale()) {
                $atributosList['LDAP_ENTRY_DN'] = $atributos->getLdapEntryDn();
            }

            if (!empty($atributosList)) {
                $user['attributes'] = $atributosList;
            }
        }

        return $this->send($this->uri . $url, self::METODO_PUT, $user);
    }

    public function resetPassword($username)
    {

        $url = 'admin/realms/' . $this->realm . '/users/' . $this->getUserIdByUsername($username) . '/reset-password-email';

        return $this->send($this->uri . $url, self::METODO_PUT);
    }

    public function getRolesByUser($username)
    {
        return $this->send(
            $this->uri . "admin/realms/" . $this->realm . "/users/{$this->getUserIdByUsername($username)}/role-mappings/clients/{$this->app}"
        );
    }

    public function addRoleByUser($username, $roleName)
    {
        $role = (array)$this->getRole($roleName);

        return $this->send(
            $this->uri . "admin/realms/" . $this->realm . "/users/{$this->getUserIdByUsername($username)}/role-mappings/clients/{$this->app}",
            self::METODO_POST,
            [$role]
        );
    }

    public function addRoleByUserEmail($email, $roleName)
    {
        $role = (array)$this->getRole($roleName);

        return $this->send(
            $this->uri . "admin/realms/" . $this->realm . "/users/{$this->getUserIdByEmail($email)}/role-mappings/clients/{$this->app}",
            self::METODO_POST,
            [$role]
        );
    }

    public function setRolesByUser($username, $roles)
    {
        $this->removeAllRolesByUser($username);

        foreach ($roles as $role) {
            $this->addRoleByUser($username, $role);
        }
    }

    public function removeRoleByUser($username, $roleName)
    {
        $role = (array)$this->getRole($roleName);

        return $this->send(
            $this->uri . "admin/realms/" . $this->realm . "/users/{$this->getUserIdByUsername($username)}/role-mappings/clients/{$this->app}",
            self::METODO_DELETE,
            [$role]
        );
    }

    public function removeAllRolesByUser($username)
    {
        $roles = $this->getRolesByUser($username);

        foreach ($roles as $role) {
            $this->removeRoleByUser(
                $username,
                $role->name
            );
        }
    }

    public function getRoles()
    {
        $url = 'admin/realms/' . $this->realm . '/clients/' . $this->app . '/roles';

        return $this->send($this->uri . $url);
    }

    public function getRole($nome)
    {
        $url = 'admin/realms/' . $this->realm . '/clients/' . $this->app . '/roles/' . urlencode($nome);

        return $this->send($this->uri . $url);
    }

    public function createRole($nome, $descricao)
    {
        $url = 'admin/realms/' . $this->realm . '/clients/' . $this->app . '/roles';

        $postFields = ['name' => $nome, 'description' => $descricao];

        return $this->send($this->uri . $url, self::METODO_POST, $postFields);
    }

    public function updateRole($nome, $descricao)
    {
        $url = 'admin/realms/' . $this->realm . '/clients/' . $this->app . '/roles/' . urlencode($nome);

        $postFields = ['name' => $nome, 'description' => $descricao];

        return $this->send($this->uri . $url, self::METODO_PUT, $postFields);
    }

    public function deleteRole($nome)
    {
        $url = 'admin/realms/' . $this->realm . '/clients/' . $this->app . '/roles/' . urlencode($nome);

        return $this->send($this->uri . $url, self::METODO_DELETE);
    }

    public function checkSessionAtiva($username)
    {

        // Retorno fixado até se definir forma de atualizar a sessão do usuario no sso.
        return TRUE;

        try {

            $url      = "admin/realms/{$this->realm}/users/{$this->getUserIdByUsername($username)}/sessions";
            $sessions = $this->send($this->uri . $url, self::METODO_GET);

            foreach ($sessions as $session) {

                $apps = (array)$session->clients;
                if (array_key_exists($this->app, $apps)) {
                    return TRUE;
                }
            }
        } catch (\Exception $e) {
            error_log($e->getMessage());
        }

        return FALSE;
    }

    public function session($username)
    {
        try {

            $url     = "admin/realms/{$this->realm}/users/{$this->getUserIdByUsername($username)}/sessions";
            $retorno = $this->send($this->uri . $url, self::METODO_GET);

            return $retorno;
        } catch (\Exception $e) {
            error_log($e->getMessage());
            error_log($e->getTraceAsString());
        }

    }

    public function logout($username)
    {
        try {

            $url = "admin/realms/{$this->realm}/users/{$this->getUserIdByUsername($username)}/logout";
            $this->send($this->uri . $url, self::METODO_POST);

        } catch (\Exception $e) {
            error_log($e->getMessage());
            error_log($e->getTraceAsString());
        }

    }

    public function logoutAll($username)
    {
        try {

            $url      = "admin/realms/{$this->realm}/users/{$this->getUserIdByUsername($username)}/sessions";
            $sessions = $this->send($this->uri . $url, self::METODO_GET);

            $sessionsId = [];

            foreach ($sessions as $session) {
                $clients = (array)$session->clients;
                if (array_key_exists($this->app, $clients)) {
                    $sessionsId[] = $session->id;
                }
            }

            foreach ($sessionsId as $id) {
                $urlDelete = "admin/realms/{$this->realm}/sessions/{$id}";
                $this->send($this->uri . $urlDelete, self::METODO_DELETE);
            }

        } catch (\Exception $e) {
            error_log($e->getMessage());
        }

    }

    /**
     *
     * Verifica se o usuário e senha informados não validos no SSO e se possui perfil associado.
     *
     * @param string $username login/email no SSO
     * @param string $password Deve ser passado a senha plana
     * @return bool|null|User
     * @throws Exception
     */
    public function autenticacaoValida($username, $password)
    {
        try {
            $token = $this->getToken($username, $password);

            if (!empty($token)) {
                /** @var User $usuario */
                $usuario = $this->getUserByToken($token);

                if($usuario instanceof User){

                    $this->tokenSSO = null;

                    $role = $this->getRolesByUser($usuario->getUsername());

                    if (!empty($role)) {
                        return $usuario;
                    }
                }

                return FALSE;
            }
        } catch (\Exception $e) {
            return FALSE;
        }
    }

    /**
     * Retorna os dados do usuário, caso tenha passado na autenticação e que possua perfil associado.
     * Caso, não seja possivel autenticar o usuário o sistema lança a IdentitySsoException
     * @param $username
     * @param $password
     * @return User
     * @throws Exception
     * @throws \Exception
     */
    public function getUserByAutenticacao($username, $password)
    {
        try {
            $token = $this->getToken($username, $password);

            if (!empty($token)) {

                /** @var User $user */
                $user = $this->getUser($username);
                if ($user == NULL) {
                    throw new SsoException('Nenhum usuário encontrado.');
                }

                $roles = $this->getRolesByUser($username);

                if (!empty($roles)) {
                    $user->setApplicationRoles($roles);

                    return $user;
                }
                throw new IdentitySsoException('Usuário não possui perfil associado.');

            }
        } catch (IdentitySsoException $e) {
            throw $e;
        }

        throw new IdentitySsoException("Não foi possivel autenticar o usuário.");
    }

    private function getCurl()
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_HEADER, TRUE);
        curl_setopt($curl, CURLINFO_HEADER_OUT, TRUE);

        //curl_setopt($curl, CURLOPT_SSLVERSION, 3);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);

        curl_setopt($curl, CURLOPT_VERBOSE, TRUE);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);

        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($curl, CURLOPT_TIMEOUT, 20);

        return $curl;
    }

    public function getDados($url, $method = self::METODO_GET, $postVals = NULL)
    {
        $url = $this->uri . $url;

        return $this->send($url, $method, $postVals);
    }

    /**
     * @param $url
     * @param string $method
     * @param null $postVals
     * @return mixed
     * @throws \Exception
     */
    public function send($url, $method = self::METODO_GET, $postVals = NULL)
    {

        try {

            $curl = $this->getCurl();

            if (empty($this->tokenSSO)) {
                $this->tokenSSO = $this->getToken();
            }

            $httpHeader = [
                'Authorization: Bearer ' . $this->tokenSSO,
                'Accept: application/json',
            ];

            if ($method != self::METODO_GET) {
                if (is_array($postVals) && !empty($postVals)) {
                    $postFields = json_encode($postVals);

                    $httpHeader[] = 'Content-Type: application/json';
                    $httpHeader[] = 'Content-Length: ' . strlen($postFields);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $postFields);
                }
            }

            if ($method == self::METODO_POST) {
                curl_setopt($curl, CURLOPT_POST, 1);
            } else {
                if ($method == self::METODO_PUT || $method == self::METODO_DELETE) {
                    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
                } else {
                    if (is_array($postVals) && !empty($postVals)) {
                        $url .= http_build_query($postVals);
                    }
                }
            }

            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $httpHeader);

            return $this->trataRetorno($curl);

        } catch (\Exception $e) {
            throw $e;
        }

    }

    public function getToken($username = ENV_SSO_USERNAME, $password = ENV_SSO_PASSWORD)
    {
        $url = $this->uri . "realms/" . $this->realm . "/protocol/openid-connect/token";

        $curl = $this->getCurl();

        curl_setopt($curl, CURLOPT_URL, $url);

        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Accept: application/json', 'Content-Type: application/x-www-form-urlencoded']);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_USERPWD, ENV_CLIENT_ID_SSO . ":" . ENV_CLIENT_SECRET_SSO);

        curl_setopt($curl, CURLOPT_POST, 1);                //0 for a get request
        curl_setopt(
            $curl,
            CURLOPT_POSTFIELDS,
            http_build_query(
                [
                    'grant_type' => 'password',
                    'username'   => $username,
                    'password'   => $password,
                ]
            )
        );

        $retorno = $this->trataRetorno($curl);

        if (property_exists($retorno, 'access_token')) {
            return $retorno->access_token;
        }

        throw new SsoException('Não foi possível obter token de acesso');

    }

    private function trataRetorno($curl)
    {

        try {

            $response = curl_exec($curl);

            $respCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);

            $header = substr($response, 0, $headerSize);

            $retorno = json_decode(substr($response, $headerSize));

            if (in_array($respCode, [200, 201, 204])) {
                return $retorno;
            }

            if ($retorno === NULL) {

                $msgErro = substr($response, $headerSize);

                if (empty($msgErro)) {
                    throw new SsoException(curl_error($curl), curl_errno($curl));

                }
                throw new SsoException($msgErro);
            } else {
                if (property_exists($retorno, 'error') && $retorno->error == 'invalid_grant') {
                    throw new IdentitySsoException($retorno->error_description);
                }

                if (property_exists($retorno, 'error') &&
                    strpos($retorno->error, 'invalidPassword') !== FALSE) {
                    throw new InvalidPasswordException($retorno->error_description);
                }

                if (property_exists($retorno, 'errorMessage')) {
                    throw new SsoException($retorno->errorMessage);
                } else {
                    if (property_exists($retorno, 'error_description')) {
                        throw new SsoException($retorno->error_description);
                    } else {
                        if (property_exists($retorno, 'error')) {
                            throw new SsoException($retorno->error);
                        }
                    }
                }
            }

            throw new SsoException('Não foi possivel tratar retorno da requisição');

        } catch (\Exception $e) {
            throw $e;
        } finally {
            curl_close($curl);
        }

    }

    /**
     * @return StorageInterface
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * @param $cache
     * @return $this
     */
    public function setCache($cache)
    {
        $this->cache = $cache;

        return $this;
    }

}